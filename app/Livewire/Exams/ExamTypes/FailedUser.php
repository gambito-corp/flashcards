<?php

namespace App\Livewire\Exams\ExamTypes;

use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FailedUser extends Component
{
    public $areas, $categories = [], $tipos = [], $universities = [], $questions = [];
    public $selectedArea, $selectedCategory, $selectedTipo, $selectedUniversity;
    public $examCollection = [], $selectedQuestionCount, $examTitle = 'Examen Personalizado', $examTime = 60;

    public function mount()
    {
        $userId = Auth::id();
        // Solo áreas con preguntas falladas por el usuario
        $this->areas = Area::whereHas('categories.tipos.questions.examUserAnswers', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('fail_weight', '>', 0);
        })
            ->where('team_id', Auth::user()->current_team_id)
            ->get();

        $this->categories = collect();
        $this->tipos = collect();

        if ($this->areas->count() > 0) {
            $this->autoSelectArea($this->areas->first());
        }
    }

    public function render()
    {
        return view('livewire.exams.exam-types.failed-user');
    }

    public function autoSelectArea($area)
    {
        $userId = Auth::id();
        $this->selectedArea = $area;
        $this->categories = Category::where('area_id', $area->id)
            ->whereHas('tipos.questions.examUserAnswers', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('fail_weight', '>', 0);
            })
            ->get();

        $this->autoSelectCategory($this->categories->first());
    }

    public function autoSelectCategory($category)
    {
        $userId = Auth::id();
        $this->selectedCategory = $category;
        if ($category) {
            $this->tipos = Tipo::where('category_id', $category->id)
                ->whereHas('questions.examUserAnswers', function ($q) use ($userId) {
                    $q->where('user_id', $userId)->where('fail_weight', '>', 0);
                })
                ->get();

            $this->selectedTipo = $this->tipos->first();
            $this->selectedUniversity = null;
            $this->loadQuestionsAndUniversities();
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
        $this->selectedUniversity = null;
        $this->loadQuestionsAndUniversities();
    }

    public function loadQuestionsAndUniversities()
    {
        $userId = Auth::id();
        if (!$this->selectedTipo) return;

        // Solo preguntas falladas por el usuario
        $query = DB::table('questions')
            ->join('question_tipo', 'questions.id', '=', 'question_tipo.question_id')
            ->join('question_universidad', 'questions.id', '=', 'question_universidad.question_id')
            ->join('exam_user_answers as eua', function ($join) use ($userId) {
                $join->on('questions.id', '=', 'eua.question_id')
                    ->where('eua.user_id', '=', $userId)
                    ->where('eua.fail_weight', '>', 0);
            })
            ->where('question_tipo.tipo_id', $this->selectedTipo->id);

        if ($this->selectedUniversity)
            $query->where('question_universidad.universidad_id', $this->selectedUniversity);

        $countData = $query->select(
            'question_universidad.universidad_id',
            DB::raw('COUNT(DISTINCT questions.id) as question_count')
        )->groupBy('question_universidad.universidad_id')->get();

        // Total solo de preguntas falladas por el usuario
        $totalCount = DB::table('questions')
            ->join('question_tipo', 'questions.id', '=', 'question_tipo.question_id')
            ->join('exam_user_answers as eua', function ($join) use ($userId) {
                $join->on('questions.id', '=', 'eua.question_id')
                    ->where('eua.user_id', '=', $userId)
                    ->where('eua.fail_weight', '>', 0);
            })
            ->where('question_tipo.tipo_id', $this->selectedTipo->id)
            ->count(DB::raw('DISTINCT questions.id'));

        $countData->push((object)['universidad_id' => null, 'question_count' => $totalCount]);

        $this->questions = $countData;

        // Solo universidades con preguntas falladas por el usuario
        $this->universities = DB::table('universidades')
            ->whereIn('id', function ($sub) use ($userId) {
                $sub->select('question_universidad.universidad_id')
                    ->from('question_universidad')
                    ->join('questions', 'questions.id', '=', 'question_universidad.question_id')
                    ->join('exam_user_answers as eua', function ($join) use ($userId) {
                        $join->on('questions.id', '=', 'eua.question_id')
                            ->where('eua.user_id', '=', $userId)
                            ->where('eua.fail_weight', '>', 0);
                    });
            })
            ->distinct()
            ->get();
    }

    public function addCombination()
    {
        $availableCount = $this->selectedUniversity
            ? optional($this->questions->firstWhere('universidad_id', $this->selectedUniversity))->question_count
            : optional($this->questions->firstWhere('universidad_id', null))->question_count;

        $maxQuestions = $availableCount ? $availableCount : 0;

        if ($this->selectedQuestionCount > $maxQuestions) {
            session()->flash('error', "No puedes seleccionar más de {$maxQuestions} preguntas disponibles para esta combinación.");
            return;
        }

        if ($this->selectedQuestionCount < 1) {
            session()->flash('error', "Debes seleccionar al menos 1 pregunta.");
            return;
        }

        // Lógica para agregar la combinación (igual que en tu builder normal)
        $combinationKey = $this->selectedArea->id . '-' .
            $this->selectedCategory->id . '-' .
            $this->selectedTipo->id;

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
            $this->examCollection[$existingIndex]['question_count'] = $this->selectedQuestionCount;
            session()->flash('success', 'La combinación ya existía y se ha actualizado la cantidad de preguntas.');
        } else {
            $this->examCollection[] = [
                'area_id' => $this->selectedArea->id,
                'area_name' => $this->selectedArea->name,
                'category_id' => $this->selectedCategory->id,
                'category_name' => $this->selectedCategory->name,
                'tipo_id' => $this->selectedTipo->id,
                'tipo_name' => $this->selectedTipo->name,
                'university_id' => $this->selectedUniversity, // Puede ser null
                'question_count' => $this->selectedQuestionCount,
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

        // Aquí puedes redirigir o emitir evento para que el Controller cree el examen
        // Por ejemplo:
        // return redirect()->route('examenes.create-user-failed', $request);

        session()->flash('success', 'Examen creado correctamente: ' . $this->examTitle);

        // Reiniciar las propiedades para volver al estado inicial.
        $this->examTitle = '';
        $this->examTime = null;
        $this->selectedQuestionCount = null;
        $this->examCollection = [];

        $this->loadQuestionsAndUniversities();
    }
}
