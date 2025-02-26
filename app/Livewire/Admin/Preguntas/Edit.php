<?php

namespace App\Livewire\Admin\Preguntas;

use App\Services\Preguntas\PreguntasSevices;
use Livewire\Component;
use App\Models\Question;
use App\Models\Team;
use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;
use App\Models\Universidad;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public $newContent;
    public $question, $selectedTeam, $teams, $selectedArea, $areas, $selectedCategory, $categories, $selectedTipo, $tipos, $selectedUniversidades, $universidades, $addedSelections, $newMediaUrl, $newMediaIframe, $newExplanation, $questionType = 'multiple_choice', $newOptions, $newCorrectOption;
    protected PreguntasSevices $preguntasServices;

    protected function rules()
    {
        $rules = [
            'newContent'            => 'required|string',
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
        return $rules;
    }



    public function boot(PreguntasSevices $preguntasServices)
    {
        $this->preguntasServices = $preguntasServices;
    }

    public function mount(Question $question)
    {
        $this->question = $question->load('options', 'tipos', 'categories.area.team', 'universidades');
        $this->newContent = $this->question->content;
        $tipos = $question->tipos->first()->load('category.area.team');
        $this->selectedTeam = $tipos?->category?->area?->team_id;
        $this->teams = Team::with('areas')->get();
        $this->selectedArea = $tipos?->category?->area_id;
        $this->areas = Area::query()->where('team_id', $this->selectedTeam)->get();
        $this->selectedCategory = $tipos?->category_id;
        $this->categories = Category::query()->where('area_id', $this->selectedArea)->get();
        $this->selectedTipo = $tipos?->id;
        $this->tipos = Tipo::query()->where('category_id', $this->selectedCategory)->get();
        $this->addTipoSelection();
        $this->selectedUniversidades = $this->question->universidades->pluck('id')->toArray();
        $this->universidades = Universidad::all();
        $this->newMediaUrl = $this->question->media_url;
        $this->newMediaIframe = $this->question->media_iframe;
        $this->newExplanation = $this->question->explanation;
        if ($this->questionType === 'multiple_choice') {
            foreach ($this->question->options as $option) {
                $this->newOptions[] = $option->content;
                if ($option->is_correct) {
                    $this->newCorrectOption = $option->id;
                }
            }
        }
    }

    public function updatedSelectedTeam($value)
    {
        $this->areas = Area::query()->where('team_id', $value)->get();
        $this->selectedArea = $this->areas->isNotEmpty() ? $this->areas->first()->id : null;
    }
    public function updatedSelectedArea($value)
    {
        $this->categories = Category::query()->where('area_id', $value)->get();
        $this->selectedCategory = $this->categories->isNotEmpty() ? $this->categories->first()->id : null;
    }
    public function updatedSelectedCategory($value)
    {
        $this->tipos = Tipo::query()->where('category_id', $value)->get();
        $this->selectedTipo = [];
    }

    public function addTipoSelection()
    {
        // Buscar los objetos correspondientes a las selecciones
        $teamObj = $this->teams->firstWhere('id', $this->selectedTeam);
        $areaObj = $this->areas->firstWhere('id', $this->selectedArea);
        $categoryObj = $this->categories->firstWhere('id', $this->selectedCategory);


        // Obtener los nombres de los tipos seleccionados
        $tipoNames = collect($this->tipos->whereIn('id', $this->selectedTipo))
            ->pluck('name')
            ->toArray();

        // Construir el array de selección con nombres y IDs
        $selection = [
            'team_id'       => $this->selectedTeam,
            'team_name'     => $teamObj ? $teamObj->name : 'Sin Carrera',
            'area_id'       => $this->selectedArea,
            'area_name'     => $areaObj ? $areaObj->name : 'Sin Asignatura',
            'category_id'   => $this->selectedCategory,
            'category_name' => $categoryObj ? $categoryObj->name : 'Sin Categoría',
            'tipo_ids'      => $this->selectedTipo,
            'tipo_names'    => $tipoNames,
        ];

        // Opcional: Evitar duplicados
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

    public function render()
    {
        return view('livewire.admin.preguntas.edit');
    }

    public function close()
    {
        return redirect()->route('admin.preguntas.index');
    }

    public function updateQuestion()
    {
        $this->validate();
        $approved = Auth::user()->hasRole('admin') || Auth::user()->hasRole('root');

        $this->preguntasServices->updatePregunta($this->question, $this, $approved, $this->addedSelections);
        session()->flash('message', 'Pregunta actualizada correctamente.');
        return redirect()->route('admin.preguntas.index');
    }






    // Propiedades individuales para los campos básicos
//    public $questionContent;
//    public $questionMediaUrl;
//    public $questionMediaIframe;
//    public $questionExplanation;
//    public $questionType;
//
//    // Propiedades individuales para la jerarquía anidada
//    public $selectedTeam;
//    public $selectedArea;
//    public $selectedCategory;
//    public $selectedTipo = []; // Sigue siendo array para múltiples valores
//
//    // Listados para los selects anidados
//    public $teams = [];
//    public $areas = [];
//    public $categories = [];
//    public $tipos = [];
//
//    // Para almacenar las selecciones agregadas (resumen de la jerarquía)
//    public $addedSelections = [];
//
//    // Universidades
//    public $selectedUniversidades = [];
//    public $universidades = [];
//
//    // Opciones de respuesta
//    public $answers = [];       // Cada respuesta es un string (dinámico)
//    public $correctAnswer;      // Índice de la respuesta correcta
//
//    // Servicio de Preguntas
//    protected PreguntasSevices $preguntasServices;
//
//    public function boot(PreguntasSevices $preguntasServices)
//    {
//        $this->preguntasServices = $preguntasServices;
//    }



//    public function mount(Question $question)
//    {
//        $question = $question->load('tipos', 'categories.area.team', 'universidades');
//        // Asignar campos básicos individualmente
//        $this->questionContent   = $question->content;
//        $this->questionType      = $question->question_type;
//        $this->questionExplanation = $question->explanation;
//        $this->questionMediaUrl  = $question->media_url;
//        $this->questionMediaIframe = $question->media_iframe;
//        $this->answers           = $question->answers ?? ['', ''];
//        $this->correctAnswer     = $question->correct_answer;
//
//        // Asignar jerarquía
//        if ($question->category && $question->category->area) {
//            $this->selectedCategory = $question->category->id;
//            $this->selectedArea = $question->category->area->id;
//            $this->selectedTeam = $question->category->area->team_id;
//        }
//
//        // Cargar listados para selects
//        $this->teams = Team::with('areas')->get();
//        $this->universidades = Universidad::all();
//        $this->areas = Area::where('team_id', $this->selectedTeam)->get();
//        $this->categories = Category::where('area_id', $this->selectedArea)->get();
//        $this->tipos = Tipo::where('category_id', $this->selectedCategory)->get();
//
//        // Universidades seleccionadas (relación Many-to-Many)
//        $this->selectedUniversidades = $question->universidades->pluck('id')->toArray();
//
//        // Pre-cargar resumen de la jerarquía (opcional)
//        $this->addedSelections[] = [
//            'team_id'       => $this->selectedTeam,
//            'team_name'     => optional($this->teams->firstWhere('id', $this->selectedTeam))->name,
//            'area_id'       => $this->selectedArea,
//            'area_name'     => optional($this->areas->firstWhere('id', $this->selectedArea))->name,
//            'category_id'   => $this->selectedCategory,
//            'category_name' => optional($this->categories->firstWhere('id', $this->selectedCategory))->name,
//            'tipo_ids'      => [],
//            'tipo_names'    => [],
//        ];
//    }

    // Actualiza las áreas al cambiar el equipo
//    public function updatedSelectedTeam($value)
//    {
//        $team = $this->teams->firstWhere('id', $value);
//        $this->areas = $team ? $team->areas : collect();
//        $this->selectedArea = $this->areas->isNotEmpty() ? $this->areas->first()->id : null;
//        $this->updatedSelectedArea($this->selectedArea);
//    }
//
//    // Actualiza las categorías al cambiar el área
//    public function updatedSelectedArea($value)
//    {
//        $this->categories = Category::where('area_id', $value)->get();
//        $this->selectedCategory = $this->categories->isNotEmpty() ? $this->categories->first()->id : null;
//        $this->updatedSelectedCategory($this->selectedCategory);
//    }
//
//    // Actualiza los tipos al cambiar la categoría
//    public function updatedSelectedCategory($value)
//    {
//        $this->tipos = Tipo::where('category_id', $value)->get();
//        $this->selectedTipo = [];
//    }
//
//    // Agrega la selección anidada actual al resumen
//    public function addTipoSelection()
//    {
//        $teamObj = $this->teams->firstWhere('id', $this->selectedTeam);
//        $areaObj = $this->areas->firstWhere('id', $this->selectedArea);
//        $categoryObj = $this->categories->firstWhere('id', $this->selectedCategory);
//        $tipoNames = collect($this->tipos->whereIn('id', $this->selectedTipo))->pluck('name')->toArray();
//
//        $selection = [
//            'team_id'       => $this->selectedTeam,
//            'team_name'     => $teamObj ? $teamObj->name : '',
//            'area_id'       => $this->selectedArea,
//            'area_name'     => $areaObj ? $areaObj->name : '',
//            'category_id'   => $this->selectedCategory,
//            'category_name' => $categoryObj ? $categoryObj->name : '',
//            'tipo_ids'      => $this->selectedTipo,
//            'tipo_names'    => $tipoNames,
//        ];
//
//        $exists = collect($this->addedSelections)->first(function ($sel) use ($selection) {
//            return $sel['team_id'] == $selection['team_id']
//                && $sel['area_id'] == $selection['area_id']
//                && $sel['category_id'] == $selection['category_id']
//                && $sel['tipo_ids'] == $selection['tipo_ids'];
//        });
//
//        if (!$exists) {
//            $this->addedSelections[] = $selection;
//        }
//    }
//
//    // Métodos para respuestas dinámicas
//    public function addAnswer()
//    {
//        $this->answers[] = '';
//    }
//
//    public function removeAnswer($index)
//    {
//        unset($this->answers[$index]);
//        $this->answers = array_values($this->answers);
//        if ($this->correctAnswer == $index) {
//            $this->correctAnswer = null;
//        }
//    }
//
//    // Elimina una selección anidada del resumen
//    public function removeTipoSelection($index)
//    {
//        unset($this->addedSelections[$index]);
//        $this->addedSelections = array_values($this->addedSelections);
//    }
//
//    // Método para actualizar la pregunta
//    public function updateQuestion()
//    {
//        $this->validate();
//        $approved = Auth::user()->hasRole('admin') || Auth::user()->hasRole('root');
//        $this->preguntasServices->updatePregunta($this->question, $this, $approved, $this->addedSelections);
//        session()->flash('message', 'Pregunta actualizada correctamente.');
//        return redirect()->route('admin.preguntas.index');
//    }
}
