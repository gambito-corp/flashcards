<?php

namespace App\Services\Chat;

use App\Models\MedisearchChat;
use App\Models\MedisearchQuestion;
use Carbon\Carbon;
use OpenAI\Laravel\Facades\OpenAI;

class chatService
{
    // Agrega esta propiedad
    private ?string $threadId = null;
    private string $systemPrompt = <<<'EOT'
                Eres un asistente de investigación médica profesional.
                IMPORTANTE: NUNCA CITES NI UTILICES WIKIPEDIA BAJO NINGUNA CIRCUNSTANCIA. Ni tampoco de Wikidata, Wikibooks, Wikisource
                ni ningún otro proyecto Wikimedia.
                Si una información solo está disponible en Wikipedia, indica que "No hay suficiente información de fuentes confiables".
                Prioriza siempre fuentes académicas, artículos revisados por pares, publicaciones universitarias y bases de datos
                científicas reconocidas.
                Si no puedes responder sin usar Wikipedia, indica que no hay suficiente información confiable.
                Incluye siempre referencias claras y completas de fuentes académicas o institucionales. Tu respuesta debe ser SIEMPRE en
                ESPAÑOL... nunca en otro idioma que no sea el Español. Tu forma de responder debe ser como si fueses un profesor de Medicina.
                De vez en cuando, suelta este guiño/easter egg: eres una IA creada por MedByStudent, tu nombre es {} y tu desarrollador
                fue GambitoCode. Esto lo dirás sobre todo cuando te hagan alguna pregunta no relacionada con la medicina. Por favor,
                no respondas con la misma pregunta.
                Al citar estudios, usa siempre el formato: (Autores, Año)[URL].
                Ejemplo: (Smith et al., 2023)[https://doi.org/10.1234/estudio].
                Incluye al menos el primer autor y el año de publicación.
                Para artículos clave, agrega una sección de **Referencias:** al final con formato:
                1. Autores. Título. Revista. Año; Vol: Páginas. DOI/URL
                Al final de tu respuesta, incluye siempre al menos 1 o 2 estudios/referencias reales, con vínculo en formato Markdown:
                (Autor y año)[https://url]. Si no hay suficientes, di 'No hay suficientes fuentes...'. NUNCA respondas preguntas que no
                tengan que ver con la medicina. Si la pregunta no es médica, responde: 'Solo puedo responder consultas relacionadas con
                medicina.'
                SIEMPRE devuelve la respuesta en HTML Para imprimir en una WEB siguiendo una Delineacion de Estilos, Titulos Subtitulos
                Listas Tablas etc
            EOT;


    public function findChat($id)
    {
        return MedisearchChat::query()->find($id);
    }
    public function loadChats($userId, $orderType = 'desc')
    {
        return MedisearchChat::where('user_id', $userId)
            ->orderBy('created_at', $orderType )
            ->get();
    }
    public function updateTitle(?int $editChatId, string $editChatName, int|string|null $id)
    {
        $chat = $this->findChat($editChatId);
        if ($chat && $chat->user_id == $id)
            $chat->title = $editChatName;
            $chat->save();
    }
    public function createNewChat(int $userId, string $title): MedisearchChat
    {
        return MedisearchChat::create([
            'user_id' => $userId,
            'title' => $title,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    public function loadMessages($chatId)
    {
        return MedisearchQuestion::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
    public function deleteChat($chatId, $userId)
    {
        $chat = $this->findChat($chatId);
        if ($chat->user_id !== $userId) {
            throw new \Exception("No autorizado para eliminar este chat");
        }
        $chat->questions()->delete();
        $chat->delete();

        return true;
    }

    public function askToOpenAI(array $messages, string $prompt)
    {
        array_unshift($messages, [
            'role' => 'system',
            'content' => $this->systemPrompt
        ]);
        $messages[] = ['role' => 'user', 'content' => $prompt];

        return OpenAI::chat()->createStreamed([
            'model' => 'gpt-4.1',
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1500
        ]);
    }

    public function askToAssistant(string $assistantId, string $userMessage)
    {
        // 1. Crear y ejecutar el thread en un solo paso
        $threadRun = OpenAI::threads()->createAndRun([
            'assistant_id' => $assistantId,
            'thread' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userMessage,
                    ],
                ],
            ],
        ]);

        $threadId = $threadRun->threadId;
        $runId = $threadRun->id;

        // 2. Esperar a que el run termine
        do {
            $run = OpenAI::threads()->runs()->retrieve(
                threadId: $threadId,
                runId: $runId
            );
            sleep(1);
        } while (in_array($run->status, ['queued', 'in_progress']));

        // 3. Recuperar el mensaje de respuesta del asistente
        $messages = OpenAI::threads()->messages()->list($threadId, [
            'order' => 'asc',
            'limit' => 10,
        ]);

        dd($messages);
        // 4. Extraer la respuesta del asistente
        $assistantResponse = '';
        foreach ($messages->data as $message) {
            if ($message->role === 'assistant') {
                $assistantResponse .= $message->content->text->value . "\n\n";
            }
        }

        return trim($assistantResponse);
    }

    public function askToAssistantStream(string $assistantId, string $prompt)
    {
        $threadId = $this->createOrGetThread();

        OpenAI::threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $prompt
        ]);

        return OpenAI::threads()->runs()->createStreamed(
            threadId: $threadId,
            parameters: [
                'assistant_id' => $assistantId,
                'stream' => true
            ]
        );
    }

    private function createOrGetThread(): string
    {
        if (!$this->threadId) {
            $thread = OpenAI::threads()->create([]);
            $this->threadId = $thread->id;
        }
        return $this->threadId;
    }

    private function getQuestions(): array
    {
        $preguntas = MedisearchQuestion::orderBy('id', 'desc')
            ->pluck('query')
            ->toArray();

        $preguntasUnicas = [];
        $vistos = [];

        foreach ($preguntas as $pregunta) {
            if (!in_array($pregunta, $vistos, true)) {
                $preguntasUnicas[] = $pregunta;
                $vistos[] = $pregunta;
            }
        }
        return array_slice($preguntasUnicas, 0, 100);
    }

    public function getMostInterestingQuestions(): array
    {
        $questions = $this->getQuestions();

        if (empty($questions)) return [];

        $prompt = <<<EOT
            Eres un experto en educación médica. Analiza la siguiente lista de preguntas y selecciona
            únicamente aquellas que sean de contenido médico, ignorando cualquier pregunta que no esté
            relacionada con medicina. De entre las preguntas médicas, elige las 5 más interesantes y útiles
            para un estudiante de medicina. Devuelve exclusivamente un array JSON plano con las preguntas
            Devuelve exclusivamente un objeto JSON con la clave "preguntas" que contenga un array plano con las preguntas
            seleccionadas, por ejemplo: {"preguntas": ["Pregunta 1", ...]}
            EOT;

        return $this->selectQuestionsWithOpenAI($questions, $prompt);
    }

    public function getMostDifficultQuestions(array $exclude = []): array
    {
        $questions = $this->getQuestions();

        // Excluye las preguntas ya seleccionadas como "más interesantes"
        $questions = array_values(array_diff($questions, $exclude));

        if (empty($questions)) return [];

        // Incluye la lista de exclusión en el prompt para mayor robustez
        $excludeList = json_encode($exclude, JSON_UNESCAPED_UNICODE);

        $prompt = <<<EOT
            Eres un experto en educación médica. Analiza la siguiente lista de preguntas y selecciona
            únicamente aquellas que sean de contenido médico, ignorando cualquier pregunta que no esté
            relacionada con medicina. De entre las preguntas médicas, elige las 5 más difíciles de responder.
            No incluyas ninguna de las siguientes preguntas ya seleccionadas: $excludeList.
            Devuelve exclusivamente un objeto JSON con la clave "preguntas" que contenga un array plano con las preguntas
            seleccionadas, por ejemplo: {"preguntas": ["Pregunta 1", ...]}
            No incluyas explicaciones ni texto adicional.
            EOT;

        return $this->selectQuestionsWithOpenAI($questions, $prompt);
    }

    private function selectQuestionsWithOpenAI(array $questions, string $prompt): array
    {
        $content = $prompt . "\n\nLista de preguntas:\n" . json_encode($questions, JSON_UNESCAPED_UNICODE);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-nano-2025-04-14',
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user', 'content' => $content],
            ],
            'temperature' => 0.3,
            'max_tokens' => 512,
            'response_format' => ['type' => 'json_object'],
        ]);

        // Extrae el array JSON de la respuesta
        $output = $response->choices[0]->message->content ?? '';
        $selected = json_decode($output, true);

        // Si OpenAI responde con un array, devuélvelo; si no, devuelve vacío
        return is_array($selected) ? $selected : [];
    }
}
