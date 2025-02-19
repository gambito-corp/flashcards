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
        $path = $this->csvFile->getRealPath();
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ';');
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $row = array_combine($header, $data);
                $this->preguntasSevices->crearPreguntaCSV($row);
            }
            fclose($handle);
            session()->flash('message', 'CSV importado correctamente.');
        }
    }

    public function render()
    {
        return view('livewire.preguntas.create-csv-questions');
    }


}
