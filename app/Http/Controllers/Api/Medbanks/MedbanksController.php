<?php
// app/Http/Controllers/Api/Medbanks/MedbanksController.php

namespace App\Http\Controllers\Api\Medbanks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MedBanks\{GetAreasRequest, GetCategoriesRequest, GetDifficultiesRequest, GetTiposRequest};
use App\Http\Requests\Api\MedBanks\CountingQuestionsRequest;
use App\Http\Requests\Api\MedBanks\ProcessDocumentRequest;
use App\Http\Requests\Api\MedBanks\ProcessPdfRequest;
use App\Services\Api\MedBank\Factories\ExamGenerationStrategyFactory;
use App\Services\Api\MedBank\MedBankService;
use App\Traits\Api\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedbanksController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly MedBankService $medBankService
    )
    {
    }

    public function getAreas(GetAreasRequest $request): JsonResponse
    {
        try {
            $areas = $this->medBankService->getAreas($request->getDataType());

            return $this->successResponse(
                $areas->toArray(),
                'Áreas obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener las áreas');
        }
    }

    public function getCategories(GetCategoriesRequest $request): JsonResponse
    {
        try {
            $categories = $this->medBankService->getCategories(
                $request->getAreaId(),
                $request->getDataType()
            );

            return $this->successResponse(
                $categories->toArray(),
                'Categorías obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener las categorías');
        }
    }

    public function getTipos(GetTiposRequest $request): JsonResponse
    {
        try {
            $tipos = $this->medBankService->getTipos(
                $request->getCategoryId(),
                $request->getDataType()
            );

            return $this->successResponse(
                $tipos->toArray(),
                'Tipos obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los tipos');
        }
    }

    public function getUniversities(): JsonResponse
    {
        try {
            $universities = $this->medBankService->getUniversities();

            return $this->successResponse(
                $universities->toArray(),
                'Universidades obtenidas correctamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener las universidades');
        }
    }


    public function getDifficulties(GetDifficultiesRequest $request)
    {
        try {
            // Obtener las dificultades del servicio
            $difficulties = $this->medBankService->getDifficulties();

            return response()->json([
                'success' => true,
                'message' => 'Dificultades obtenidas correctamente',
                'data' => $difficulties,
                'meta' => [
                    'total' => count($difficulties),
                    'available' => count(array_filter($difficulties, fn($d) => $d['unlocked'])),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener dificultades: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al obtener las dificultades',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function countingQuestions(CountingQuestionsRequest $request)
    {
        try {
            $filters = array_filter([
                'tipo' => $request->input('tipo'),
                'area_id' => $request->integer('area_id'),
                'category_id' => $request->integer('category_id'),
                'tipo_id' => $request->integer('tipo_id'),
                'university_id' => $request->integer('university_id'),
            ]);

            \Log::info('🔢 Contando preguntas', $filters);


            $result = $this->medBankService->countAvailableQuestions($filters);

            return response()->json([
                'success' => true,
                'message' => 'Conteo obtenido correctamente',
                'data' => [
                    'count' => $result['count'],
                    'filters_applied' => $result['filters'],
                    'query_type' => !empty($filters['tipo']) ? 'area_or_category' : 'tipo_specific',
                    'cached' => true // Si usas cache
                ]
            ], 200);

        } catch (\InvalidArgumentException $e) {
            // Errores de validación específicos
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => 'validation_error'
            ], 422);

        } catch (\Exception $e) {
            \Log::error('❌ Error al contar preguntas: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al obtener el conteo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function processPdf(ProcessPdfRequest $request)
    {
        try {
            $pdfFile = $request->file('pdf');

            // Procesar el PDF y obtener resumen
            $summary = $this->medBankService->generatePdfSummary($pdfFile);
            return response()->json([
                'success' => true,
                'message' => 'PDF procesado correctamente',
                'data' => [
                    'summary' => $summary,
                    'fileName' => $pdfFile->getClientOriginalName(),
                    'fileSize' => $pdfFile->getSize(),
                    'processedAt' => now()->format('Y-m-d H:i:s')
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al procesar PDF: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo PDF',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    public function processDocument(ProcessDocumentRequest $request): JsonResponse
    {
        try {
            // 1. Extraer texto del documento
            $textData = $this->medBankService->convertDocumentToText($request->file('document'));

            // 2. Configurar opciones del examen
            $options = [
                'num_questions' => $request->integer('num_questions', 10),
                'difficulty' => $request->input('difficulty', 'mixed'),
            ];

            // 3. Generar examen con OpenAI
            $examData = $this->medBankService->generateExamFromText(
                $textData['extracted_text'],
                $options
            );

            // 4. Combinar información del archivo con el examen
            $response = [
                'exam' => $examData
            ];

            return $this->successResponse(
                $response,
                'Examen generado correctamente'
            );

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al generar el examen');
        }
    }

    public function processPdfExam(ProcessPdfRequest $request): JsonResponse
    {
        try {
            // 1. Extraer texto del PDF
            $textData = $this->medBankService->convertDocumentToText($request->file('pdf'));
            // 2. Configurar opciones del examen
            $options = [
                'num_questions' => $request->integer('num_questions', 10),
                'difficulty' => $request->input('difficulty', 'easy'),
                'pdf_content' => $request->input('pdf_content', null),
            ];
            // 3. Generar examen con OpenAI
            $examData = $this->medBankService->generateExamFromText(
                $textData['extracted_text'],
                $options
            );
            // 4. Combinar información del archivo con el examen
            $response = [
                'exam' => $examData,
                'file_info' => [
                    'original_name' => $textData['original_name'],
                    'file_type' => $textData['file_type'],
                    'file_size_mb' => $textData['file_size_mb'],
                    'estimated_tokens' => $textData['estimated_tokens'],
                    'word_count' => $textData['word_count'],
                    'character_count' => $textData['character_count'],
                ]
            ];
            return $this->successResponse(
                $response,
                'Examen generado correctamente a partir del PDF'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al generar el examen a partir del PDF');

        }
    }

    public function generateExam(string $type, Request $request): JsonResponse
    {
        $strategy = ExamGenerationStrategyFactory::create($type);
        $exam = $strategy->generateExam($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Examen generado correctamente',
            'data' => $exam
        ], 200);
    }

    public function getExam($examId)
    {
        return response()->json([
            'success' => true,
            'message' => 'Examen obtenido correctamente',
            'data' => $this->medBankService->getExam($examId)
        ], 200);
    }

    public function resolveExam(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Examen resuelto correctamente',
            'data' => $this->medBankService->resolveExam(
                $request->input('exam_id'),
                $request->input('answers', [])
            ),
        ], 200);
    }
}
