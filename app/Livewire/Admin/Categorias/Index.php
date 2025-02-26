<?php

namespace App\Livewire\Admin\Categorias;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;

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
        $query = Category::query()->with('area.team');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhereHas('area', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('team', function ($q2) {
                            $q2->where('name', 'like', '%' . $this->search . '%');
                        });
                });
        }

        if ($this->perPage === 'all') {
            $categories = $query->get();
        } else {
            $categories = $query->paginate($this->perPage);
        }

        return view('livewire.admin.categorias.index', compact('categories'));
    }
}
