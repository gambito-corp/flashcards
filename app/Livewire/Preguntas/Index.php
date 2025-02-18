<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Question;
use App\Models\Category;

class Index extends Component
{
    use WithPagination;

    // Propiedades para la lista de preguntas
    public $perPage = 10;
    public $search = '';
    public $renPag;

    // Propiedad para controlar la visibilidad del modal de creación
    public $showModal = false;
    public $showModal2 = false;

    // Propiedades para crear una nueva pregunta
    public $newContent;
    public $newQuestionType = 'multiple_choice';
    public $newCategoryId;
    public $newExplanation;
    public $newOptions = [];
    public $newCorrectOption = null;
    public $categories = [];
    public $question;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->categories = Category::all();
    }

    // Reglas de validación para la creación de pregunta
    protected function rulesNewQuestion()
    {
        $rules = [
            'newContent'       => 'required|string',
            'newQuestionType'  => 'required|in:multiple_choice,boolean,range',
            'newCategoryId'    => 'required|exists:categories,id',
            'newExplanation'   => 'nullable|string',
        ];

        // Si el tipo es multiple_choice, se validan las opciones y la opción correcta
        if ($this->newQuestionType === 'multiple_choice') {
            $rules['newOptions'] = 'required|array|min:2';
            $rules['newCorrectOption'] = 'required|integer|min:0';
        }

        return $rules;
    }

    // Métodos para resetear y abrir el modal
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['newContent', 'newExplanation', 'newCategoryId', 'newOptions', 'newCorrectOption']);
        // Por defecto, inicializamos dos opciones para multiple_choice
        if ($this->newQuestionType === 'multiple_choice') {
            $this->newOptions = ['', ''];
        }
        $this->showModal = true;
    }

    public function openCreateModal2()
    {
        $this->resetValidation();
        $this->showModal2 = true;
    }

    public function addNewOption()
    {
        $this->newOptions[] = '';
    }

    public function removeNewOption($index)
    {
        unset($this->newOptions[$index]);
        $this->newOptions = array_values($this->newOptions);
        if ($this->newCorrectOption == $index) {
            $this->newCorrectOption = null;
        }
    }

    public function store()
    {
        $this->validate($this->rulesNewQuestion());

        // Crear la pregunta
        $question = Question::create([
            'content'       => $this->newContent,
            'question_type' => $this->newQuestionType,
            'explanation'   => $this->newExplanation,
            'approved'      => false,
        ]);

        // Asociar la pregunta a la categoría (suponiendo relación many-to-many)
        $question->categories()->attach($this->newCategoryId);

        // Si la pregunta es de tipo multiple_choice, crear las opciones
        if ($this->newQuestionType === 'multiple_choice') {
            foreach ($this->newOptions as $index => $optionContent) {
                $question->options()->create([
                    'content'    => $optionContent,
                    'is_correct' => ($index == $this->newCorrectOption),
                    'points'     => ($index == $this->newCorrectOption) ? 1 : 0,
                ]);
            }
        }

        session()->flash('message', 'Pregunta creada correctamente.');
        $this->showModal = false;
    }

    // Métodos para la lista de preguntas

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Construir la consulta y cargar relaciones 'universidades' y 'user'
        $query = Question::query()->with(['universidades', 'user']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('content', 'like', '%' . $this->search . '%')
                    ->orWhereHas('universidades', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('user', function ($q3) {
                        $q3->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->perPage === 'all') {
            $questions = $query->get();
        } else {
            $questions = $query->paginate($this->perPage);
        }

        return view('livewire.preguntas.index', [
            'questions' => $questions,
        ]);
    }
}
