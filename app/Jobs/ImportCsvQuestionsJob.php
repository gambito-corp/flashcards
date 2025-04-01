<?php

namespace App\Jobs;

use App\Jobs\ProcessCsvChunkJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportCsvQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $chunkSize;

    public function __construct(string $filePath, int $chunkSize = 100)
    {
        $this->filePath = $filePath;
        $this->chunkSize = $chunkSize;
    }

    public function handle()
    {
        $fullPath = storage_path('app/' . $this->filePath);
        if (!file_exists($fullPath)) {
            \Log::error('El archivo CSV no existe.', ['path' => $fullPath]);
            return;
        }

        if (($handle = fopen($fullPath, 'r')) !== false) {
            // Aplica conversión de codificación
            stream_filter_append($handle, 'convert.iconv.WINDOWS-1252/UTF-8');
            // Lee la cabecera del CSV
            $header = fgetcsv($handle, 1000, ';');
            $rows = [];

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $rows[] = array_combine($header, $data);

                if (count($rows) >= $this->chunkSize) {
                    // Despacha un job para el bloque actual de filas
                    ProcessCsvChunkJob::dispatch($rows);
                    $rows = [];
                }
            }

            // Si quedan filas pendientes, despacha un último job
            if (!empty($rows)) {
                ProcessCsvChunkJob::dispatch($rows);
            }

            fclose($handle);
        }
    }
}
