<?php

namespace App\Livewire\Exams;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Models\Category;
use App\Models\Tipo;
use App\Models\ExamResult;
use Illuminate\Http\Request;



class Index extends Component
{
    public $areas;
    public $categorias;
    public $tipos;
    public $activeArea;     // Área activa por defecto
    public $activeCategoria; // Categoría activa por defecto
    public $activeTipo;     // Tipo activo por defecto
    public $activeTipoObject;
    public $filteredUniversities;
    public $filteredQuestionsCount;

    public $questionSelections = []; // Añadir esta propiedad
    public $timeExam = 60;
    public $examTitle = "examen de Prueba";
    public $totalSelected = 0;
    public $overLimit = false;

    public function setActiveArea($areaId)
    {
        $this->activeArea = $areaId;
        $this->categorias = Category::query()->where('area_id', $areaId)->get();
    }
    public function setActiveCategoria($categoryId)
    {
        $this->activeCategoria = $categoryId;
        $this->tipos = Tipo::query()->where('category_id', $categoryId)->get();
    }

    public function setActiveTipo($tipoId)
    {
        $this->activeTipo = $tipoId;
        $this->activeTipoObject = Tipo::with(['questions' => function($query) {
            $query->with(['universidades' => function($q) {
                $q->select('universidades.id as universidad_id', 'name');
            }]);
        }])->find($tipoId);

        // Obtener universidades únicas
        $this->filteredUniversities = $this->activeTipoObject->questions
            ->pluck('universidades')
            ->flatten()
            ->unique('universidad_id') // Usar el alias
            ->map(fn($uni) => [
                'id' => $uni->universidad_id,
                'name' => $uni->name
            ])
            ->values();

        // Actualizar conteo inicial
        $this->updateQuestionCount();
    }





    public function updatedQuestionSelections($value, $path)
    {
        if (Str::contains($path, 'university')) {
            $this->updateQuestionCount();
        }
        $this->calculateTotalSelected();
    }

    private function updateQuestionCount()
    {
        $this->filteredQuestionsCount = $this->activeTipoObject->questions()
            ->when($this->questionSelections[$this->activeTipo]['university'] ?? null,
                function ($query, $universityId) {
                    $query->whereHas('universidades', function ($q) use ($universityId) {
                        $q->where('universidades.id', $universityId);
                    });
                })
            ->count();
    }

    private function calculateTotalSelected()
    {
        $this->totalSelected = collect($this->questionSelections)
            ->sum('quantity');
    }


    public function mount($areas, $categorias, $tipos): void
    {
        $this->areas = $areas;
        $this->categorias = $categorias;
        $this->tipos = $tipos;
        $this->activeArea = $this->areas->first()->id;
        $this->activeCategoria = $this->categorias->where('area_id', $this->activeArea)->first()->id;
        $this->activeTipo = $this->tipos->where('category_id', $this->activeCategoria)->first()->id;
        $this->setActiveTipo($this->activeTipo);
        $user = Auth::user();
        // Si el usuario tiene status 0, se verifica el límite de exámenes del mes actual.
        if ($user->status == 0) {
            $currentWeekExamCount = ExamResult::query()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            if ($currentWeekExamCount >= 3) {
                $this->overLimit = true;
            }
        }
    }

    public function render()
    {
        return view('livewire.exams.index');
    }

    public function realizarExamen()
    {
        // Validar los datos del formulario
//        $this->validate([
//            'examTitle' => 'required|string|max:255',
//            'timeExam' => 'required|numeric|min:1',
//            'questionSelections.*.quantity' => 'required|numeric|min:1|max:100',
//            'totalSelected' => 'required|numeric|min:1'
//        ]);
//        dd($this->examTitle, $this->timeExam, $this->questionSelections, $this->totalSelected);

        // Redirigir al controlador con los datos necesarios
        return redirect()->route('examenes.store', [
            'examTitle' => $this->examTitle,
            'timeExam' => $this->timeExam,
            'questionSelections' => $this->questionSelections
        ]);
    }

}
