<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreguntasController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root|admin|colab');
    }

    public function index()
    {
        return view('preguntas.index');
    }

    public function create()
    {
        return view('preguntas.create');
    }

    public function cargar()
    {
        return view('preguntas.cargar');
    }

    public function carrera()
    {
        return view('preguntas.carrera');
    }

    public function asignatura()
    {
        return view('preguntas.asignatura');
    }

    public function categoria()
    {
        return view('preguntas.categorize');
    }

    public function tipo()
    {
        return view('preguntas.tipo');
    }

    public function universidad()
    {
        return view('preguntas.universidad');
    }

    public function downloadCsvModel()
    {
        $headers = [
            'content-type' => 'text/csv',
            'content-disposition' => 'attachment; filename=preguntas.csv',
        ];
        $columns = [
            'content',
            'iframe',
            'url',
            'explicacion',
            'tipos',
            'universidades',
            'answer1',
            'is_correct1',
            'answer2',
            'is_correct2',
            'answer3',
            'is_correct3',
            'answer4',
            'is_correct4'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            // Escribe la cabecera.
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->streamDownload($callback, 'modelo_preguntas.csv', $headers);
    }
}

