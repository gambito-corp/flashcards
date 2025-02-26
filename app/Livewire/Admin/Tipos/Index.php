<?php

namespace App\Livewire\Admin\Tipos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tipo;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    protected $paginationTheme = 'tailwind';

    // Reinicia la página al actualizar la búsqueda
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Tipo::query()->with('category.area.team');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhereHas('category', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('area', function ($q2) {
                            $q2->where('name', 'like', '%' . $this->search . '%')
                                ->orWhereHas('team', function ($q3) {
                                    $q3->where('name', 'like', '%' . $this->search . '%');
                                });
                        });
                });
        }

        if ($this->perPage === 'all') {
            $tipos = $query->get();
        } else {
            $tipos = $query->paginate($this->perPage);
        }

        return view('livewire.admin.tipos.index', compact('tipos'));
    }
}
