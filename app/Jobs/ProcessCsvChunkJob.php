<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Preguntas\PreguntasSevices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCsvChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $rows;
    protected User $user;

    public function __construct(array $rows, User $user)
    {
        $this->rows = $rows;
        $this->user = $user;
    }

    public function handle(PreguntasSevices $preguntasSevices)
    {
        foreach ($this->rows as $row) {
            try {
                // Procesa cada fila utilizando tu servicio
                $preguntasSevices->crearPreguntaCSV($row, $this->user);
            } catch (\Exception $e) {
                \Log::channel('jobs')->error('Error procesando fila CSV.', [
                    'row'   => $row,
                    'error' => $e->getMessage()
                ]);
                \Log::error('Error procesando fila CSV. fallo anotado en el log de jobs.');
            }
        }
    }
}
