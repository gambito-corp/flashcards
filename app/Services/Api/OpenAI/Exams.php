<?php

namespace App\Services\Api\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class Exams
{
    private const MODEL = 'gpt-4.1-nano-2025-04-14'; // Modelo recomendado
    private const MAX_TOKENS = 120000; // Límite seguro

    public function generateExam(string $content, array $options = []): array
    {
        $tokenCount = $this->estimateTokens($content);
        $tokenCount += $this->estimateTokens($options['pdf_content'] ?? '');
        $tokenCount += $this->estimateTokens($content);
        if ($tokenCount > self::MAX_TOKENS) {
            throw new \Exception("El prompt es demasiado largo ({$tokenCount} tokens). Máximo: " . self::MAX_TOKENS);
        }


        $response = OpenAI::chat()->create([
            'model' => self::MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getSystemPrompt(array_sum(array_column($options, 'num_questions')) ?? 10)
                ],
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ],
            'max_tokens' => 16000,
            'temperature' => 0.1,
            'response_format' => [
                'type' => 'json_object'
            ]
        ]);

        $examData = json_decode($response->choices[0]->message->content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al procesar la respuesta de OpenAI: ' . json_last_error_msg());
        }

        return $examData;
    }

    private function getSystemPrompt(int $numQuestions): string
    {
        return "Eres un experto en crear exámenes educativos de medicina. Genera exactamente {$numQuestions} preguntas basadas en el contenido tenemos 5 niveles de dificultas los cuales son
        easy: preguntas sencillas que podria responder un recien ingresado o alguien que ha leido por encima el Documento....
        medium: preguntas que requieren un conocimiento intermedio, como un estudiante de medicina de segundo año...
        hard: preguntas que requieren un conocimiento avanzado, como un estudiante de medicina de cuarto año o un residente...
        extreme: preguntas que requieren un conocimiento muy avanzado, como un estudiante de medicina de sexto año o un especialista
        suidice: preguntas que requieren un conocimiento extremadamente avanzado, como un especialista o un profesor universitario.

        FORMATO JSON OBLIGATORIO:
        {
          \"exam\": {
            \"title\": \"Título del examen\",
            \"description\": \"Descripción del contenido\",
            \"total_questions\": {$numQuestions},
            \"questions\": [
              {
                \"id\": 1,
                \"type\": \"multiple_choice\",
                \"question\": \"Pregunta aquí\",
                \"options\": [\"A\", \"B\", \"C\", \"D\"],
                \"correct_answer\": \"A\",
                \"explanation\": \"Explicación\",
                \"difficulty\": \"easy\"
              }
            ]
          }
        }";
    }

    private function buildPrompt(string $content, array $options): string
    {
        $numQuestions = $options['num_questions'] ?? 10;
        $difficulty = $options['difficulty'] ?? 'facil';
        $title = $options['title'] ?? 'Examen generado desde PDF';
        $description = 'Examen basado en el contenido proporcionado en el PDF.';
        $additionalContent = $options['pdf_content'] ?? '';

        return <<<PROMPT
Actúa como un experto en educación médica y generación de exámenes para estudiantes de medicina.

Tienes el siguiente contenido extraído de un PDF, sobre el que debes generar preguntas de examen:

---
{$content}
---

no importa en que idioma Venga el Texto a menos que el Usuario te lo pida Especificamente tu da Todas las preguntas
en Español de Latinoamérica, y las respuestas en Español de Latinoamérica.
si te pidieran otro idioma aplicalo a toda la estructura del usuario ...
enunciado:
opciones:
y explicación:
Además, tienes estas instrucciones adicionales (si las hubiera):
{$additionalContent}
**Configuración del examen:**
- Título: {$title}
- Descripción: {$description}
- Número de preguntas: {$numQuestions}
- Dificultad: {$difficulty}
**Criterios estrictos para la generación de preguntas:**
- Preguntas claras, concisas y relevantes para el contenido proporcionado.
- Una sola respuesta correcta, bien justificada.
- Varía el contenido, evita repeticiones.
- Originalidad: no copies de fuentes externas.
- Adapta la dificultad según el campo 'difficulty':
    - facil: Preguntas básicas y conceptos fundamentales.
    - medio: Preguntas de nivel intermedio que requieren comprensión de conceptos clave.
    - dificil: Preguntas avanzadas que requieren análisis crítico y conocimiento profundo.
    - experto: Preguntas de nivel profesional con casos complejos y razonamiento clínico avanzado.
    - suicida: Preguntas extremadamente desafiantes con casos raros y diagnósticos diferenciales complejos.
- Preguntas objetivas, alineadas con el nivel de estudiantes de medicina.
**Formato de respuesta requerido (en JSON):**
{
  "exam": {
    "title": "{$title}",
    "description": "{$description}",
    "total_questions": {$numQuestions},
    "questions": [
      {
        "enunciado": "Texto de la pregunta",
        "opciones": ["", "", "", ""], // si aplica
        "respuesta_correcta": integer del índice del Array opciones correcto,
        "explicacion": "Breve explicación de la respuesta"
      }
      // ...
    ]
  }
}
No incluyas explicaciones adicionales fuera de este formato. Genera preguntas de calidad, variadas y alineadas al contenido proporcionado.
PROMPT;
    }


    public function setBuildPrompt($data): string
    {
        return "
Actúa como un experto en educación médica y generación de exámenes para estudiantes de medicina.

Tienes el siguiente array de configuraciones para generar bloques temáticos de preguntas:

" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

Cada elemento representa un bloque temático. Para cada bloque, sigue estas instrucciones:

- area: Área médica (ej: Anatomía, Fisiología, Bioquímica).
- category: Categoría específica (ej: Cardiología, Neurología).
- tipo: Tipo de pregunta (ej: Opción múltiple, Verdadero/Falso).
- num_questions: Número de preguntas a generar.
- university: Universidad de referencia (ej: Universidad de Buenos Aires).
- difficulty: Nivel de dificultad (facil, medio, dificil, experto, suicida).

**Criterios estrictos para la generación de preguntas:**
- Las preguntas deben ser claras, concisas y relevantes para el área, categoría y tipo indicados.
- Una sola respuesta correcta, bien justificada.
- Varía el contenido, evita repeticiones.
- Originalidad: no copies de fuentes externas.
- Adapta la dificultad según el campo 'difficulty':
    - facil: Preguntas básicas y conceptos fundamentales. Ideal para estudiantes principiantes.
    - medio: Preguntas de nivel intermedio que requieren comprensión de conceptos clave.
    - dificil: Preguntas avanzadas que requieren análisis crítico y conocimiento profundo.
    - experto: Preguntas de nivel profesional con casos complejos y razonamiento clínico avanzado.
    - suicida: Preguntas extremadamente desafiantes con casos raros y diagnósticos diferenciales complejos. Solo para los más valientes.
- Si hay universidad, adapta el estilo según su perfil académico.
- Preguntas objetivas, alineadas con el nivel de estudiantes de medicina.

**Formato de respuesta requerido (en JSON):**
[
  {
    \"area\": \"...\",
    \"category\": \"...\",
    \"tipo\": \"...\",
    \"difficulty\": \"...\",
    \"num_questions\": ...,
    \"university\": \"...\",
    \"questions\": [
      {
        \"enunciado\": \"Texto de la pregunta\",
        \"opciones\": [\"\", \"\", \"\", \"\"], // si aplica
        \"respuesta_correcta\": \"integer del indice del Array opciones Correcto\",
        \"explicacion\": \"Breve explicación de la respuesta\"
      }
      // ...
    ]
  }
  // ...
]

No incluyas explicaciones adicionales fuera de este formato. Genera preguntas de calidad, variadas y alineadas a cada bloque de configuración.
fuera del Grupo de Array agregaras Informaciones adicionales como el título del examen, descripción y total de preguntas:

{
  \"exam\": {
    \"title\": \"Título del examen\",
    \"description\": \"Descripción del contenido\",
    \"total_questions\": todas las preguntas del array
    ...
  }
}
";

    }

    public function setBuildPromptForPDF(array $data): string
    {
        $options = [
            'title' => 'Examen generado desde PDF',
            'duration' => $data['duration'] ?? 60,
            'description' => 'Examen basado en el contenido proporcionado en el PDF.',
            'num_questions' => $data['num_questions'] ?? 10,
            'difficulty' => $data['difficulty'] ?? 'facil',
            'pdf_content' => $data['pdf_content'] ?? '',
        ];
        return $this->buildPrompt($data['pdf_file'], $options);
    }

    private function estimateTokens(string $text): int
    {
        return intval(strlen($text) / 4);
    }
}
