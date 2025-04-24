<?php

namespace App\Livewire\Exams;

use App\Models\Area;
use Livewire\Component;
use App\Models\Category;
use App\Models\Tipo;
use App\Models\Question;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamBuilder extends Component
{
    public $areas;
    public $categories = [];
    public $tipos = [];
    public $universities = [];
    public $questions = [];
    public $selectedArea = null;
    public $selectedCategory = null;
    public $selectedTipo = null;
    public $selectedUniversity = null;
    public $selectedQuestionCount;
    public $examCollection = [];
    public $examTitle = 'Examen de Prueba';
    public $examTime = 60;

    public function mount()
    {
        $this->areas = Area::query()->select('id', 'team_id', 'name')->where('team_id', Auth::user()->current_team_id)->get();
        $this->selectedArea = $this->areas->first();
        $this->categories = Category::query()->select('id', 'area_id', 'name')->where('area_id', $this->selectedArea?->id)->get();
        $this->selectedCategory = $this->categories->first();
        $this->tipos = Tipo::query()->select('id', 'category_id', 'name')->where('category_id',  $this->selectedCategory?->id)->get();
        $this->selectedTipo = $this->tipos->first();

        $this->loadQuestionsAndUniversities();

    }

    public function render()
    {
        return view('livewire.exams.builder');
    }

    public function getCategories($area_id)
    {
        $area = Area::query()->find($area_id);
        if (!$area) {
            return;
        }
        $this->selectedArea = $area;
        $this->categories = Category::query()->with('tipos')->where('area_id', $area_id)->get();
        $this->selectedCategory = $this->categories->first();
        $this->selectedTipo = $this->selectedCategory?->tipos->first();
        $this->loadQuestionsAndUniversities();
    }

    public function getTypes($category_id)
    {
        $category = Category::query()->find($category_id);
        if (!$category) {
            return;
        }
        $this->selectedCategory = $category;
        $this->tipos = Tipo::query()->where('category_id', $category_id)->get();
        $this->selectedTipo = $this->tipos->first();
        // Cargamos las preguntas y universidades disponibles para el tipo seleccionado
        $this->loadQuestionsAndUniversities();
    }
    public function setTypes($tipo_id)
    {
        $tipo = Tipo::query()->find($tipo_id);
        if (!$tipo) {
            return;
        }
        $this->selectedTipo = $tipo;
    }

    public function loadQuestionsAndUniversities()
    {
        if (!$this->selectedTipo) {
            return;
        }

        // Primer Query: Conteo de preguntas agrupadas por universidad, aplicando filtro
        $query = DB::table('questions')
            ->join('question_tipo', 'questions.id', '=', 'question_tipo.question_id')
            ->join('question_universidad', 'questions.id', '=', 'question_universidad.question_id')
            ->where('question_tipo.tipo_id', $this->selectedTipo->id);

        if ($this->selectedUniversity) {
            $query->where('question_universidad.universidad_id', $this->selectedUniversity);
        }

        // Se agrupa por universidad y cuenta las preguntas (usando COUNT DISTINCT para evitar duplicados)
        $countData = $query->select(
            'question_universidad.universidad_id',
            DB::raw('COUNT(DISTINCT questions.id) as question_count')
        )
            ->groupBy('question_universidad.universidad_id')
            ->get();

        // Query para obtener el total global de preguntas para el tipo seleccionado (sin filtro de universidad)
        $totalCount = DB::table('questions')
            ->join('question_tipo', 'questions.id', '=', 'question_tipo.question_id')
            ->where('question_tipo.tipo_id', $this->selectedTipo->id)
            ->select(DB::raw('COUNT(DISTINCT questions.id) as total_count'))
            ->value('total_count');

        // Agrega un elemento a la colección para representar el conteo global (universidad_id null)
        $countData->push((object)[
            'universidad_id' => null,
            'question_count' => $totalCount,
        ]);

        // Almacena el resultado en la propiedad $questions
        $this->questions = $countData;

        // Segundo Query: Obtener todas las universidades disponibles para el tipo seleccionado
        // (ignora el filtro actual, así el select siempre muestra todas)
        $universityIds = DB::table('questions')
            ->join('question_tipo', 'questions.id', '=', 'question_tipo.question_id')
            ->join('question_universidad', 'questions.id', '=', 'question_universidad.question_id')
            ->where('question_tipo.tipo_id', $this->selectedTipo->id)
            ->whereNotNull('question_universidad.universidad_id')
            ->select('question_universidad.universidad_id')
            ->distinct()
            ->pluck('question_universidad.universidad_id')
            ->toArray();

        $this->universities = DB::table('universidades')
            ->whereIn('id', $universityIds)
            ->get();
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

