<?php

namespace App\Livewire\Exams\ExamTypes;

use App\Models\Area;
use App\Models\Category;
use App\Models\ExamUserAnswer;
use App\Models\Tipo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Analisis extends Component
{
    public $estadisticas = [];
    public $global = [];

    public function mount(): void
    {
        $userId = Auth::id();

        // 1. IDs de preguntas respondidas por el usuario
        $preguntasRespondidasIds = ExamUserAnswer::where('user_id', $userId)
            ->pluck('question_id')
            ->toArray();

        // 2. Carga todas las áreas posibles
        $todas_areas = Area::select('id', 'name')->get();

        // 3. Carga todas las categorías por área
        $categoriasPorArea = Category::select('id', 'name', 'area_id')->get()->groupBy('area_id');

        // 4. Carga todos los tipos por categoría
        $tiposPorCategoria = Tipo::select('id', 'name', 'category_id')->get()->groupBy('category_id');

        // 5. Carga las áreas con solo las preguntas respondidas por el usuario
        $areas = Area::with([
            'categories.tipos.questions' => function ($q) use ($preguntasRespondidasIds) {
                $q->whereIn('questions.id', $preguntasRespondidasIds);
            }
        ])
            ->whereHas('categories.tipos.questions', function ($q) use ($preguntasRespondidasIds) {
                $q->whereIn('questions.id', $preguntasRespondidasIds);
            })
            ->get();

        // 6. Estadísticas por área/categoría/tipo
        $estadisticas = [];

        // Para calcular el total global de respondidas por nivel
        $total_respondidas_area = [];
        $total_respondidas_categoria = [];
        $total_respondidas_tipo = [];

        foreach ($todas_areas as $area) {
            $areaRespondida = $areas->firstWhere('id', $area->id);

            $total_area = 0;
            $correctas_area = 0;
            $categorias_res = [];
            $categorias = $categoriasPorArea->get($area->id, collect());

            foreach ($categorias as $categoria) {
                $categoriaRespondida = $areaRespondida
                    ? $areaRespondida->categories->firstWhere('id', $categoria->id)
                    : null;

                $total_categoria = 0;
                $correctas_categoria = 0;
                $tipos_res = [];

                $tipos = $tiposPorCategoria->get($categoria->id, collect());

                foreach ($tipos as $tipo) {
                    $tipoRespondido = $categoriaRespondida
                        ? $categoriaRespondida->tipos->firstWhere('id', $tipo->id)
                        : null;

                    $total_tipo = 0;
                    $correctas_tipo = 0;

                    if ($tipoRespondido) {
                        foreach ($tipoRespondido->questions as $pregunta) {
                            $total_tipo++;
                            $respuesta = ExamUserAnswer::where('user_id', $userId)
                                ->where('question_id', $pregunta->id)
                                ->latest('id')
                                ->first();
                            if ($respuesta && $respuesta->is_correct) {
                                $correctas_tipo++;
                            }
                        }
                    }

                    // Guarda el total respondidas por tipo
                    $total_respondidas_tipo[$tipo->id] = ($total_respondidas_tipo[$tipo->id] ?? 0) + $total_tipo;

                    $tipos_res[] = [
                        'tipo_id' => $tipo->id,
                        'tipo_name' => $tipo->name,
                        'total_respondidas' => $total_tipo,
                        'total_correctas' => $correctas_tipo,
                        'total_incorrectas' => $total_tipo - $correctas_tipo,
                        'porcentaje' => $total_tipo > 0 ? round($correctas_tipo / $total_tipo * 100, 2) : 0,
                        // El favoritismo de tipo se calcula después
                    ];

                    $total_categoria += $total_tipo;
                    $correctas_categoria += $correctas_tipo;
                }

                // Guarda el total respondidas por categoría
                $total_respondidas_categoria[$categoria->id] = ($total_respondidas_categoria[$categoria->id] ?? 0) + $total_categoria;

                $categorias_res[] = [
                    'category_id' => $categoria->id,
                    'category_name' => $categoria->name,
                    'total_respondidas' => $total_categoria,
                    'total_correctas' => $correctas_categoria,
                    'total_incorrectas' => $total_categoria - $correctas_categoria,
                    'porcentaje' => $total_categoria > 0 ? round($correctas_categoria / $total_categoria * 100, 2) : 0,
                    // El favoritismo de categoría se calcula después
                    'tipos' => $tipos_res,
                ];

                $total_area += $total_categoria;
                $correctas_area += $correctas_categoria;
            }

            // Guarda el total respondidas por área
            $total_respondidas_area[$area->id] = ($total_respondidas_area[$area->id] ?? 0) + $total_area;

            $estadisticas[] = [
                'area_id' => $area->id,
                'area_name' => $area->name,
                'total_respondidas' => $total_area,
                'total_correctas' => $correctas_area,
                'total_incorrectas' => $total_area - $correctas_area,
                'porcentaje' => $total_area > 0 ? round($correctas_area / $total_area * 100, 2) : 0,
                // El favoritismo de área se calcula después
                'categorias' => $categorias_res,
            ];
        }

        // Totales globales para el cálculo de favoritismo
        $totalRespondidas = array_sum($total_respondidas_area);
        $totalRespondidasCategoria = array_sum($total_respondidas_categoria);
        $totalRespondidasTipo = array_sum($total_respondidas_tipo);

        // Ahora añade el favoritismo a cada nivel
        foreach ($estadisticas as &$area) {
            $area['favoritismo'] = $totalRespondidas > 0
                ? round(($area['total_respondidas'] / $totalRespondidas) * 100, 2)
                : 0;

            foreach ($area['categorias'] as &$categoria) {
                $categoria['favoritismo'] = $totalRespondidas > 0
                    ? round(($categoria['total_respondidas'] / $totalRespondidas) * 100, 2)
                    : 0;

                foreach ($categoria['tipos'] as &$tipo) {
                    $tipo['favoritismo'] = $totalRespondidas > 0
                        ? round(($tipo['total_respondidas'] / $totalRespondidas) * 100, 2)
                        : 0;
                }
                unset($tipo);
            }
            unset($categoria);
        }
        unset($area);

        // Estadística global
        $totalCorrectas = ExamUserAnswer::where('user_id', $userId)->where('is_correct', true)->count();
        $porcentajeGlobal = $totalRespondidas > 0
            ? round($totalCorrectas / $totalRespondidas * 100, 2)
            : 0;

        $this->estadisticas = $estadisticas;
        $this->global = [
            'total_respondidas' => $totalRespondidas,
            'total_correctas' => $totalCorrectas,
            'total_incorrectas' => $totalRespondidas - $totalCorrectas,
            'porcentaje' => $porcentajeGlobal,
        ];
//        dd($this->estadisticas, $this->global);
    }


    public function render()
    {
        return view('livewire.exams.exam-types.analisis');
    }
}
