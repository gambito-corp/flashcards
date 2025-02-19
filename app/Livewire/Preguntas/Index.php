<?php

namespace App\Livewire\Preguntas;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Question;

class Index extends Component
{
    use WithPagination;

    // Propiedades para la lista de preguntas
    public $perPage = 10;
    public $search = '';
    // Nuevos Modales...
    public bool
        $modalCreate = false,
        $modalCsv = false,
        $modalCarrera = false,
        $modalArea = false,
        $modalCategoria = false,
        $modalTipo = false,
        $modalUniversidad = false;
    protected $paginationTheme = 'tailwind';

    public function openModal(string $type):void
    {
        $this->resetModals();
        match ($type) {
            'create'        => $this->modalCreate = true,
            'csv'           => $this->modalCsv = true,
            'carrera'       => $this->modalCarrera = true,
            'area'          => $this->modalArea = true,
            'categoria'     => $this->modalCategoria = true,
            'tipo'          => $this->modalTipo = true,
            'universidad'   => $this->modalUniversidad = true,
            default         => null,
        };
    }
    #[On('closeModal')]
    public function closeModal(string $type)
    {
        match ($type) {
            'create'        => $this->modalCreate = false,
            'csv'           => $this->modalCsv = false,
            'carrera'       => $this->modalCarrera = false,
            'area'          => $this->modalArea = false,
            'categoria'     => $this->modalCategoria = false,
            'tipo'          => $this->modalTipo = false,
            'universidad'   => $this->modalUniversidad = false,
            default         => null,
        };
    }
    private function resetModals()
    {
        $this->modalCreate = false;
        $this->modalCsv = false;
        $this->modalCarrera = false;
        $this->modalArea = false;
        $this->modalCategoria = false;
        $this->modalTipo = false;
        $this->modalUniversidad = false;
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
