<?php

namespace App\Livewire\Admin\Preguntas;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Question;

class Index extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        session()->flash('message', 'Pregunta eliminada correctamente.');
    }

    public function render()
    {
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

        $questions = $this->perPage === 'all'
            ? $query->get()
            : $query->paginate($this->perPage);

        return view('livewire.admin.preguntas.index', compact('questions'));
    }
}
