<?php

namespace App\Livewire\Exams;

use Livewire\Component;
use App\Models\{
    Area, Category, Tipo,
    Question, ExamResult,
    Universidad, Exam
};
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ExamBuilder extends Component
{
    public $selectedArea;
    public $selectedCategory;
    public $selectedTipo;
    public $selectedUniversity;
    public $quantity = 0;
    public $timeExam = 60;
    public $examTitle = "";
    public $examQuestions = [];

    protected $listeners = [
        'areaSelected' => 'loadCategories',
        'categorySelected' => 'loadTipos',
        'tipoSelected' => 'loadUniversities'
    ];

    public function render()
    {
        return view('livewire.exams.builder', [
            'areas' => Area::where('team_id', Auth::user()->current_team_id)
                ->select('id', 'name')
                ->get(),
            'universities' => $this->getUniversities(),
            'examLimitReached' => $this->checkExamLimit()
        ]);
    }

    #[On('area-selected')]
    public function loadCategories($areaId)
    {
        $this->selectedArea = $areaId;
        $this->selectedCategory = null;
        $this->selectedTipo = null;
        $this->dispatch('categories-loaded',
            categories: Category::where('area_id', $areaId)
                ->select('id', 'name')
                ->get()
        );
    }

    #[On('category-selected')]
    public function loadTipos($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->selectedTipo = null;
        $this->dispatch('tipos-loaded',
            tipos: Tipo::where('category_id', $categoryId)
                ->select('id', 'name')
                ->get()
        );
    }

    public function loadUniversities($tipoId)
    {
        $this->selectedTipo = $tipoId;
        $this->maxQuestions = Question::where('tipo_id', $tipoId)
            ->approved()
            ->when($this->selectedUniversity, fn($q) => $q->whereHas('universidades',
                fn($q) => $q->where('id', $this->selectedUniversity)
            ))
            ->count();
    }

    public function addToExam()
    {
        $this->validate([
            'quantity' => "required|numeric|min:1|max:{$this->maxQuestions}",
            'selectedTipo' => 'required|exists:tipos,id'
        ]);

        $this->examQuestions[] = [
            'tipo_id' => $this->selectedTipo,
            'university_id' => $this->selectedUniversity,
            'quantity' => $this->quantity,
            'tipo_name' => Tipo::find($this->selectedTipo)->name
        ];

        $this->reset(['selectedUniversity', 'quantity']);
    }

    public function realizarExamen()
    {
        $this->validate([
            'examTitle' => 'required|string|max:255',
            'timeExam' => 'required|numeric|min:1'
        ]);

        $exam = Exam::create([
            'user_id' => Auth::id(),
            'title' => $this->examTitle,
            'duration' => $this->timeExam,
            'questions' => $this->prepareQuestions()
        ]);

        return redirect()->route('examenes.show', $exam);
    }

    // Resto de métodos helper...
}
