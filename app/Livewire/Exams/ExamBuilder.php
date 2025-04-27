<?php

namespace App\Livewire\Exams;

use App\Models\Area;
use Livewire\Component;
use App\Models\Category;
use App\Models\Tipo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamBuilder extends Component
{
    public $areas, $categories = [], $tipos = [], $universities = [], $questions = [];
    public $selectedArea, $selectedCategory, $selectedTipo, $selectedUniversity;
    public $examCollection = [], $selectedQuestionCount, $examTitle = 'Examen de Prueba', $examTime = 60;

    public function mount()
    {
        $this->areas = Area::where('team_id', Auth::user()->current_team_id)->get();
        $this->autoSelectArea($this->areas->first());
    }
    public function render()
    {
        return view('livewire.exams.builder');
    }
    public function autoSelectArea($area)
    {
        $this->selectedArea = $area;
        $this->categories = Category::where('area_id', $area->id)->get();
        $this->autoSelectCategory($this->categories->first());
    }
    // AutoSeleccionar Tipo al seleccionar categoría
    public function autoSelectCategory($category)
    {
        $this->selectedCategory = $category;
        if ($category) {
            $this->tipos = Tipo::where('category_id', $category->id)->get();
            $this->selectedTipo = $this->tipos->first();
            $this->selectedUniversity = null;
            $this->loadQuestionsAndUniversities();
        }
    }
    // Eventos click:
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
        $this->selectedUniversity = null;
        $this->loadQuestionsAndUniversities();
    }




//
//    public function getCategories($area_id)
//    {
//        $area = Area::query()->find($area_id);
//        if (!$area) {
//            return;
//        }
//        $this->selectedArea = $area;
//        $this->categories = Category::query()->with('tipos')->where('area_id', $area_id)->get();
//        $this->selectedCategory = $this->categories->first();
//        $this->selectedTipo = $this->selectedCategory?->tipos->first();
//        $this->loadQuestionsAndUniversities();
//    }
//
//    public function getTypes($category_id)
//    {
//        $category = Category::query()->find($category_id);
//        if (!$category) {
//            return;
//        }
//        $this->selectedCategory = $category;
//        $this->tipos = Tipo::query()->where('category_id', $category_id)->get();
//        $this->selectedTipo = $this->tipos->first();
//        // Cargamos las preguntas y universidades disponibles para el tipo seleccionado
//        $this->loadQuestionsAndUniversities();
//    }
//    public function setTypes($tipo_id)
//    {
//        $tipo = Tipo::query()->find($tipo_id);
//        if (!$tipo) {
//            return;
//        }
//        $this->selectedTipo = $tipo;
//    }




    public function loadQuestionsAndUniversities()
    {
        if (!$this->selectedTipo) return;

        $query = DB::table('questions')
            ->join('question_tipo', 'questions.id', '=', 'question_tipo.question_id')
            ->join('question_universidad', 'questions.id', '=', 'question_universidad.question_id')
            ->where('question_tipo.tipo_id', $this->selectedTipo->id);

        if ($this->selectedUniversity)
            $query->where('question_universidad.universidad_id', $this->selectedUniversity);

        $countData = $query->select(
            'question_universidad.universidad_id',
            DB::raw('COUNT(DISTINCT questions.id) as question_count')
        )->groupBy('question_universidad.universidad_id')->get();

        $totalCount = DB::table('questions')
            ->join('question_tipo', 'questions.id', '=', 'question_tipo.question_id')
            ->where('question_tipo.tipo_id', $this->selectedTipo->id)
            ->count(DB::raw('DISTINCT questions.id'));

        $countData->push((object)['universidad_id' => null, 'question_count' => $totalCount]);

        $this->questions = $countData;

        $this->universities = DB::table('universidades')
            ->whereIn('id', DB::table('question_universidad')
                ->whereIn('question_id', DB::table('question_tipo')->where('tipo_id', $this->selectedTipo->id)->pluck('question_id'))
                ->pluck('universidad_id'))->distinct()->get();
    }



    public function filterQuestions()
    {
        // Si el select es "" se considera null
        $this->selectedUniversity = $this->selectedUniversity ?: null;
        $this->loadQuestionsAndUniversities();
    }

    public function addCombination()
    {
        // Sumar el número de preguntas de la colección actual
        $total = collect($this->examCollection)->sum('question_count');
        if (($total + $this->selectedQuestionCount) > 200) {
            session()->flash('error', 'La suma total de preguntas no puede superar 200.');
            return;
        }

        // Crear la clave única para la combinación actual ignorando la universidad
        $combinationKey = $this->selectedArea->id . '-' .
            $this->selectedCategory->id . '-' .
            $this->selectedTipo->id;

        // Busca si ya existe una combinación con la misma área, categoría y tipo
        $existingIndex = null;
        foreach ($this->examCollection as $index => $exam) {
            $existingKey = $exam['area_id'] . '-' .
                $exam['category_id'] . '-' .
                $exam['tipo_id'];
            if ($existingKey === $combinationKey) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Si la combinación ya existe, se calcula la suma total sustituyendo la cantidad anterior por la nueva
            $oldCount = $this->examCollection[$existingIndex]['question_count'];
            $currentTotal = collect($this->examCollection)->sum('question_count');
            $newTotal = ($currentTotal - $oldCount) + $this->selectedQuestionCount;
            if ($newTotal > 200) {
                session()->flash('error', 'La suma total de preguntas no puede superar 200.');
                return;
            }
            // Actualiza la cantidad de preguntas para la combinación duplicada
            $this->examCollection[$existingIndex]['question_count'] = $this->selectedQuestionCount;
            session()->flash('success', 'La combinación ya existía y se ha actualizado la cantidad de preguntas.');
        } else {
            // Si la combinación no existe, se verifica la suma total y se agrega la nueva combinación
            $total = collect($this->examCollection)->sum('question_count');
            if (($total + $this->selectedQuestionCount) > 200) {
                session()->flash('error', 'La suma total de preguntas no puede superar 200.');
                return;
            }
            $this->examCollection[] = [
                'area_id'       => $this->selectedArea->id,
                'area_name'     => $this->selectedArea->name,
                'category_id'   => $this->selectedCategory->id,
                'category_name' => $this->selectedCategory->name,
                'tipo_id'       => $this->selectedTipo->id,
                'tipo_name'     => $this->selectedTipo->name,
                'university_id' => $this->selectedUniversity, // Puede ser null
                'question_count'=> $this->selectedQuestionCount,
            ];
            session()->flash('success', 'Combinación agregada correctamente.');
        }
    }
    public function createExam()
    {
        if (!$this->examTitle || !$this->examTime || count($this->examCollection) === 0) {
            session()->flash('error', 'Debe completar el título, el tiempo y agregar al menos una combinación.');
            return;
        }

        $request = [
            'title' => $this->examTitle,
            'time' => $this->examTime,
            'questions' => $this->examCollection,
            'user_id' => Auth::user()->id,
        ];

        session()->flash('success', 'Examen creado correctamente: ' . $this->examTitle);

        // Reiniciar las propiedades para volver al estado inicial.
        $this->examTitle = '';
        $this->examTime = null;
        $this->selectedQuestionCount = null;
        $this->examCollection = [];

        $this->loadQuestionsAndUniversities();



        // Finalmente, se redirige.
        return redirect()->route('examenes.create', $request);
    }

}

