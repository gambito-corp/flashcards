<?php

namespace App\Http\Controllers\Api\Questions;

use App\Http\Controllers\Controller;
use App\Services\Api\Questions\PdfExtractionService;
use App\Services\Api\Questions\QuestionsServices;
use Illuminate\Http\Request;

class QuestionsController extends Controller
{
    public function __construct(protected QuestionsServices $questionsServices, protected PdfExtractionService $pdfExtractionService)
    {
    }

    public function importQuestionsFromCsv(Request $request)
    {
        // Solo validación de entrada
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            // Delegar toda la lógica al service
            $result = $this->questionsServices->importFromCsv($request->file('csv_file'));

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error procesando el CSV: ' . $e->getMessage()
            ], 422);
        }
    }

    public function extractQuestionsFromPdf(Request $request)
    {
        try {
            $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:20480', // 20MB máximo
                'save_to_database' => 'boolean',
                'generate_explanations' => 'boolean'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación: ' . $e->getMessage()
            ], 422);
        }

        try {
            $result = $this->pdfExtractionService->extractAndProcessQuestions(
                $request->file('pdf_file'),
                $request->boolean('save_to_database', false),
                $request->boolean('generate_explanations', true)
            );
            dd($result);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error procesando el PDF: ' . $e->getMessage()
            ], 422);
        }
    }
}
