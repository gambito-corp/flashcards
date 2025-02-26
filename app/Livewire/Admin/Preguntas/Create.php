<?php

namespace App\Livewire\Admin\Preguntas;

use App\Services\Preguntas\PreguntasSevices;
use Livewire\Component;
use App\Models\Team;
use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;
use App\Models\Universidad;

class Create extends Component
{
    // Enunciado y medios
    public $newContent;
    public $newMediaUrl;
    public $newMediaIframe;
    public $newExplanation;
    public $newQuestionType = 'multiple_choice';

    // Select anidados: Carrera > Área > Categoría > Tipo(s)
    public $selectedTeam;
    public $selectedArea;
    public $selectedCategory;
    public $selectedTipo = []; // array de IDs (select multiple)

    public $teams = [];
    public $areas = [];
    public $categories = [];
    public $tipos = [];

    // Para almacenar la selección anidada agregada (cadena o arreglo de IDs)
    public $addedSelections = [];

    // Universidades (checkbox group)
    public $universidades = [];
    public $selectedUniversidades = [];

    // Opciones de respuesta (dinámicos)
    public $newOptions = []; // Cada elemento es el texto de la respuesta
    public $newCorrectOption; // índice de la respuesta correcta

    protected PreguntasSevices $preguntasServices;

    public function boot(PreguntasSevices $preguntasServices)
    {
        $this->preguntasServices = $preguntasServices;
    }

    protected function rules()
    {
        $rules = [
            'newContent'            => 'required|string',
            'newQuestionType'       => 'required|in:multiple_choice,boolean,range',
            'selectedTeam'          => 'required|exists:teams,id',
            'selectedArea'          => 'required|exists:areas,id',
            'selectedCategory'      => 'required|exists:categories,id',
            'selectedTipo'          => 'required|array|min:1',
            'selectedTipo.*'        => 'required|integer|exists:tipos,id',
            'selectedUniversidades' => 'nullable|array',
            'selectedUniversidades.*' => 'integer|exists:universidades,id',
            'newMediaUrl'           => 'nullable|url',
            'newMediaIframe'        => 'nullable|string',
            'newExplanation'        => 'nullable|string',
        ];
        if ($this->newQuestionType === 'multiple_choice') {
            $rules['newOptions'] = 'required|array|min:2';
            $rules['newCorrectOption'] = 'required|integer|min:0';
        }
        return $rules;
    }

    public function mount()
    {
        // Cargar equipos con sus áreas
        $this->teams = Team::with('areas')->get();
        // Cargar todas las universidades, categorías y tipos (para filtrado)
        $this->universidades = Universidad::all();
        // Inicializar listas dependientes como colecciones vacías
        $this->areas = collect();
        $this->categories = collect();
        $this->tipos = collect();

        // Valor por defecto: si existen equipos, usar el primero
        if ($this->teams->isNotEmpty()) {
            $firstTeam = $this->teams->first();
            $this->selectedTeam = $firstTeam->id;
            $this->areas = $firstTeam->areas;
            if ($this->areas->isNotEmpty()) {
                $firstArea = $this->areas->first();
                $this->selectedArea = $firstArea->id;
                $this->categories = Category::where('area_id', $this->selectedArea)->get();
                if ($this->categories->isNotEmpty()) {
                    $firstCategory = $this->categories->first();
                    $this->selectedCategory = $firstCategory->id;
                    $this->tipos = Tipo::where('category_id', $this->selectedCategory)->get();
                    $this->selectedTipo = [];
                }
            }
        }
        // Inicializar respuestas para multiple_choice
        if ($this->newQuestionType === 'multiple_choice') {
            $this->newOptions = ['', ''];
        }
    }

    // Actualiza áreas al cambiar el equipo
    public function updatedSelectedTeam($value)
    {
        $team = $this->teams->firstWhere('id', $value);
        $this->areas = $team ? $team->areas : collect();
        $this->selectedArea = $this->areas->isNotEmpty() ? $this->areas->first()->id : null;
        $this->updatedSelectedArea($this->selectedArea);
    }

    // Actualiza categorías al cambiar el área
    public function updatedSelectedArea($value)
    {
        $filteredCategories = Category::where('area_id', $value)->get();
        $this->categories = $filteredCategories;
        $this->selectedCategory = $filteredCategories->isNotEmpty() ? $filteredCategories->first()->id : null;
        $this->updatedSelectedCategory($this->selectedCategory);
    }

    // Actualiza tipos al cambiar la categoría
    public function updatedSelectedCategory($value)
    {
        $this->tipos = Tipo::where('category_id', $value)->get();
        $this->selectedTipo = [];
    }

    // Agrega la selección anidada actual a "addedSelections"
    public function addTipoSelection()
    {
        $team = $this->teams->firstWhere('id', $this->selectedTeam);
        $area = $this->areas->firstWhere('id', $this->selectedArea);
        $category = $this->categories->firstWhere('id', $this->selectedCategory);
        $tipoNames = collect($this->tipos->whereIn('id', $this->selectedTipo))->pluck('name')->toArray();

        $selection = [
            'team_id'       => $this->selectedTeam,
            'team_name'     => $team ? $team->name : 'Sin Carrera',
            'area_id'       => $this->selectedArea,
            'area_name'     => $area ? $area->name : 'Sin Asignatura',
            'category_id'   => $this->selectedCategory,
            'category_name' => $category ? $category->name : 'Sin Categoría',
            'tipo_ids'      => $this->selectedTipo,
            'tipo_names'    => $tipoNames,
        ];

        $exists = collect($this->addedSelections)->first(function ($sel) use ($selection) {
            return $sel['team_id'] == $selection['team_id']
                && $sel['area_id'] == $selection['area_id']
                && $sel['category_id'] == $selection['category_id']
                && $sel['tipo_ids'] == $selection['tipo_ids'];
        });

        if (!$exists) {
            $this->addedSelections[] = $selection;
        }
    }


    // Métodos para respuestas dinámicas
    public function addAnswer()
    {
        $this->newOptions[] = '';
    }

    public function removeAnswer($index)
    {
        unset($this->newOptions[$index]);
        $this->newOptions = array_values($this->newOptions);
        if ($this->newCorrectOption == $index) {
            $this->newCorrectOption = null;
        }
    }
    public function removeTipoSelection($index)
    {
        unset($this->addedSelections[$index]);
        $this->addedSelections = array_values($this->addedSelections);
    }


    // Método store: valida y procesa el formulario (aquí se delega la lógica al servicio o se procesa directamente)
    public function store()
    {
        $this->validate();
        // Aquí podrías crear la pregunta en la base de datos, asociar las selecciones, respuestas, etc.
        $this->preguntasServices->crearPregunta($this, true, $this->addedSelections);
        // Para este ejemplo, simulamos el guardado:
        session()->flash('message', 'Pregunta creada correctamente.');
        return redirect()->route('admin.preguntas.index');
    }

    public function render()
    {
        return view('livewire.admin.preguntas.create');
    }
}
