<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Team;
use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;
use App\Models\Universidad;
use App\Models\Question;
use App\Services\Preguntas\PreguntasSevices;
use Illuminate\Support\Facades\Auth;

class CreateQuestionModal extends Component
{
    protected PreguntasSevices $preguntasSevices;

    // Control del modal
    public $showModal = false;

    /*
     * Variables para la selección anidada:
     * Carrera (Team) → Área → Categoría → Tipo(s)
     */
    public $selectedTeam = null;
    public $selectedArea = null;
    public $selectedCategory = null;
    // Para tipos permitimos seleccionar varios (guardamos un array de IDs)
    public $selectedTipo = [];
    // Para universidades: usamos checkbox (varios)
    public $selectedUniversidades = [];

    // Listados para los selects (se cargan inicialmente)
    public $teams = [];
    public $areas = [];
    public $categories = [];
    public $tipos = [];
    public $universidades = [];

    // Propiedades para tener todos los registros (para filtrar en tiempo real)
    public $allCategories = [];
    public $allTipos = [];

    // Para almacenar las combinaciones agregadas (cuando se hace "Agregar Selección")
    public $addedSelections = [];

    // Campos para la pregunta
    public $newContent;
    public $newQuestionType = 'multiple_choice'; // Solo se usa 'multiple_choice' por defecto
    public $newExplanation;
    public $newOptions = [];
    public $newCorrectOption = null;
    public $newMediaUrl;
    public $newMediaIframe;

    protected function rules()
    {
        $rules = [
            'newContent'         => 'required|string',
            'newQuestionType'    => 'required|in:multiple_choice,boolean,range',
            'selectedTeam'       => 'required|exists:teams,id',
            'selectedArea'       => 'required|exists:areas,id',
            'selectedCategory'   => 'required|exists:categories,id',
            'selectedTipo'       => 'required|array|min:1',
            'selectedTipo.*'     => 'required|integer|exists:tipos,id',
            'newExplanation'     => 'nullable|string',
            'selectedUniversidades' => 'nullable|array',
            'selectedUniversidades.*' => 'integer|exists:universidades,id',
            'newMediaUrl'        => 'nullable|url',
            'newMediaIframe'     => 'nullable|string',
        ];

        if ($this->newQuestionType === 'multiple_choice') {
            $rules['newOptions'] = 'required|array|min:2';
            $rules['newCorrectOption'] = 'required|integer|min:0';
        }

        return $rules;
    }

    // Inyección de dependencias mediante boot
    public function boot(PreguntasSevices $preguntasSevices)
    {
        $this->preguntasSevices = $preguntasSevices;
    }

    public function mount()
    {
        // Cargar equipos con sus áreas
        $this->teams = Team::with('areas')->get();

        // Cargar todas las áreas, categorías, tipos y universidades
        $this->areas = Area::all();
        $this->allCategories = Category::all();
        $this->allTipos = Tipo::all();
        $this->universidades = Universidad::all();

        // Valor por defecto: usar el primer equipo y actualizar los selects anidados
        if ($this->teams->isNotEmpty()) {
            $firstTeam = $this->teams->first();
            $this->selectedTeam = $firstTeam->id;
            // Actualizamos las áreas según el equipo seleccionado
            $this->areas = $firstTeam->areas;
            if ($this->areas->isNotEmpty()) {
                $firstArea = $this->areas->first();
                $this->selectedArea = $firstArea->id;
                $filteredCategories = $this->allCategories->where('area_id', $this->selectedArea);
                $this->categories = $filteredCategories;
                if ($filteredCategories->isNotEmpty()) {
                    $firstCategory = $filteredCategories->first();
                    $this->selectedCategory = $firstCategory->id;
                    $filteredTipos = $this->allTipos->where('category_id', $this->selectedCategory);
                    $this->tipos = $filteredTipos;
                    if ($filteredTipos->isNotEmpty()) {
                        // Por defecto, dejamos el array vacío para que el usuario seleccione los tipos deseados
                        $this->selectedTipo = [];
                    }
                }
            }
        }

        // Inicializar opciones para multiple_choice
        if ($this->newQuestionType === 'multiple_choice') {
            $this->newOptions = ['', ''];
        }
    }

    // Actualiza las áreas al cambiar el equipo (Team)
    public function updatedSelectedTeam($value)
    {
        $team = $this->teams->firstWhere('id', $value);
        $this->areas = $team ? $team->areas : collect();
        $this->selectedArea = $this->areas->isNotEmpty() ? $this->areas->first()->id : null;
        $this->updatedSelectedArea($this->selectedArea);
    }

    // Actualiza las categorías al cambiar el área
    public function updatedSelectedArea($value)
    {
        $filteredCategories = $this->allCategories->where('area_id', $value);
        $this->categories = $filteredCategories;
        $this->selectedCategory = $filteredCategories->isNotEmpty() ? $filteredCategories->first()->id : null;
        $this->updatedSelectedCategory($this->selectedCategory);
    }

    // Actualiza los tipos al cambiar la categoría
    public function updatedSelectedCategory($value)
    {
        $filteredTipos = $this->allTipos->where('category_id', $value);
        $this->tipos = $filteredTipos;
        // Dejar vacío para que el usuario pueda seleccionar los tipos que desee
        $this->selectedTipo = [];
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

//    public function openModal()
//    {
//        $this->resetValidation();
//        $this->reset(['newContent', 'newExplanation', 'newOptions', 'newCorrectOption']);
//        if ($this->newQuestionType === 'multiple_choice') {
//            $this->newOptions = ['', ''];
//        }
//        $this->showModal = true;
//    }

    // Agrega la selección anidada actual al array de selecciones
    public function addTipoSelection()
    {
        $selection = [
            'team_id'       => $this->selectedTeam,
            'team_name'     => $this->teams->firstWhere('id', $this->selectedTeam)->name,
            'area_id'       => $this->selectedArea,
            'area_name'     => optional($this->areas->firstWhere('id', $this->selectedArea))->name,
            'category_id'   => $this->selectedCategory,
            'category_name' => optional($this->categories->firstWhere('id', $this->selectedCategory))->name,
            'tipo_ids'      => $this->selectedTipo,
            'tipo_names'    => collect($this->tipos->whereIn('id', $this->selectedTipo))->pluck('name')->toArray(),
            'universidad_ids' => $this->selectedUniversidades,
        ];

        // Evitar duplicados según criterio: compara por IDs de team, area, category y tipo_ids
        $exists = collect($this->addedSelections)->first(function ($sel) use ($selection) {
            return $sel['team_id'] == $selection['team_id'] &&
                $sel['area_id'] == $selection['area_id'] &&
                $sel['category_id'] == $selection['category_id'] &&
                $sel['tipo_ids'] == $selection['tipo_ids'];
        });
        if (!$exists) {
            $this->addedSelections[] = $selection;
        }

        $this->resetSelections();
    }

    // Reinicia los selects anidados a los valores por defecto
    public function resetSelections()
    {
        if ($this->teams->isNotEmpty()) {
            $firstTeam = $this->teams->first();
            $this->selectedTeam = $firstTeam->id;
            $this->areas = $firstTeam->areas;
            if ($this->areas->isNotEmpty()) {
                $firstArea = $this->areas->first();
                $this->selectedArea = $firstArea->id;
                $filteredCategories = $this->allCategories->where('area_id', $this->selectedArea);
                $this->categories = $filteredCategories;
                if ($filteredCategories->isNotEmpty()) {
                    $firstCategory = $filteredCategories->first();
                    $this->selectedCategory = $firstCategory->id;
                    $filteredTipos = $this->allTipos->where('category_id', $this->selectedCategory);
                    $this->tipos = $filteredTipos;
                    $this->selectedTipo = $filteredTipos->isNotEmpty() ? [$filteredTipos->first()->id] : [];
                }
            }
        }
        $this->selectedUniversidades = [];
    }

    public function removeTipoSelection($index)
    {
        unset($this->addedSelections[$index]);
        $this->addedSelections = array_values($this->addedSelections);
    }

    public function store()
    {
        $this->validate($this->rules());
        $approved = Auth::user()->hasRole('admin') || Auth::user()->hasRole('root');
        $this->preguntasSevices->crearPregunta($this, $approved, $this->addedSelections);
        session()->flash('message', 'Pregunta creada correctamente.');
        $this->showModal = false;
    }

    public function close()
    {
        $this->dispatch('closeModal', 'create');
    }

    public function render()
    {
        return view('livewire.preguntas.create-question-modal');
    }
}
