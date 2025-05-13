<?php

namespace App\Livewire\Exams\ExamTypes;

use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IaExam extends Component
{
    public $areas, $categories = [], $tipos = [];
    public $selectedArea, $selectedCategory, $selectedTipo;
    public $tema = '';
    public $dificultad = 'media';
    public $numPreguntas = 10;
    public $examTitle = 'Examen IA';
    public $examTime = 60;
    public $examCollection = [];

    public function mount()
    {
        $this->areas = Area::where('team_id', Auth::user()->current_team_id)->get();
        $this->categories = collect();
        $this->tipos = collect();

        if ($this->areas->count() > 0) {
            $this->autoSelectArea($this->areas->first());
        }
    }

    public function autoSelectArea($area)
    {
        $this->selectedArea = $area;
        $this->categories = Category::where('area_id', $area->id)->get();
        $this->autoSelectCategory($this->categories->first());
    }

    public function autoSelectCategory($category)
    {
        $this->selectedCategory = $category;
        if ($category) {
            $this->tipos = Tipo::where('category_id', $category->id)->get();
            $this->selectedTipo = $this->tipos->first();
        }
    }

    public function selectArea($areaId)
    {
        $area = Area::find($areaId);
        if ($area) $this->autoSelectArea($area);
    }

    public function selectCategory($categoryId)
    {
        $category = Category::find($categoryId);
        if ($category) $this->autoSelectCategory($category);
    }

    public function selectTipo($tipoId)
    {
        $tipo = Tipo::find($tipoId);
        if ($tipo) $this->selectedTipo = $tipo;
    }

    public function addCombination()
    {
        $this->validate([
            'selectedArea' => 'required',
            'selectedCategory' => 'required',
            'selectedTipo' => 'required',
            'tema' => 'required|string|min:3',
            'dificultad' => 'required|in:basica,media,avanzada',
            'numPreguntas' => 'required|integer|min:1|max:200',
        ]);

        $combinationKey = $this->selectedArea->id . '-' .
            $this->selectedCategory->id . '-' .
            $this->selectedTipo->id . '-' .
            $this->tema . '-' .
            $this->dificultad;

        $existingIndex = null;
        foreach ($this->examCollection as $index => $exam) {
            $existingKey = $exam['area_id'] . '-' .
                $exam['category_id'] . '-' .
                $exam['tipo_id'] . '-' .
                $exam['tema'] . '-' .
                $exam['dificultad'];
            if ($existingKey === $combinationKey) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            $this->examCollection[$existingIndex]['question_count'] = $this->numPreguntas;
            session()->flash('success', 'La combinación ya existía y se ha actualizado la cantidad de preguntas.');
        } else {
            $this->examCollection[] = [
                'area_id' => $this->selectedArea->id,
                'area_name' => $this->selectedArea->name,
                'category_id' => $this->selectedCategory->id,
                'category_name' => $this->selectedCategory->name,
                'tipo_id' => $this->selectedTipo->id,
                'tipo_name' => $this->selectedTipo->name,
                'tema' => $this->tema,
                'dificultad' => $this->dificultad,
                'question_count' => $this->numPreguntas,
            ];
            session()->flash('success', 'Combinación agregada correctamente.');
        }

        // Limpiar inputs para nueva combinación
        $this->tema = '';
        $this->dificultad = 'media';
        $this->numPreguntas = 10;
    }

    public function render()
    {
        return view('livewire.exams.exam-types.ia-exam');
    }
}
