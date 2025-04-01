<?php

namespace App\Livewire\Preguntas;

use App\Services\Preguntas\PreguntasSevices;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCsvQuestions extends Component
{
    use WithFileUploads;

    protected PreguntasSevices $preguntasSevices;
    public $csvFile;

    public function boot(PreguntasSevices $preguntasSevices)
    {
        $this->preguntasSevices = $preguntasSevices;
    }
    public function importCsv()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
        ]);

        // Guarda el archivo en la carpeta 'csv_imports' del storage
        $filePath = $this->csvFile->store('csv_imports');

        // Despacha el job principal de importación con procesamiento por chunks puedo agregar un segundo parametro con la cantidad de filas a procesar
        \App\Jobs\ImportCsvQuestionsJob::dispatch($filePath);

        session()->flash('message', 'La importación se encuentra en proceso.');
    }


    public function render()
    {
        return view('livewire.preguntas.create-csv-questions');
    }


}
