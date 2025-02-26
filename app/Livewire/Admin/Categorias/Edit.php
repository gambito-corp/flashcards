<?php

namespace App\Livewire\Admin\Categorias;

use Livewire\Component;
use App\Models\Category;
use App\Models\Team;
use App\Models\Area;

class Edit extends Component
{
    public $category;
    public $name, $team_id, $area_id, $description;
    public $teams;
    public $areas = [];
    public $categoryId;

    public function mount(Category $categoria)
    {

        $this->category = $categoria->load('area.team');
        $this->name = $this->category->name;
        $this->description = $this->category->description;
        $this->area_id = $this->category->area_id;
        $this->team_id = $this->category->area?->team_id;
        $this->categoryId = $this->category->id;
        $this->teams = Team::all();
        if ($this->team_id) {
            $this->areas = Area::query()->where('team_id', $this->team_id)->get();
        }
    }
    public function updatedCategoryTeamId($value)
    {
        $this->areas = Area::query()->where('team_id', $value)->get();
        $this->area_id = '';
    }

    public function update()
    {
        $this->category->name = $this->name;
        $this->category->area_id = $this->area_id;
        $this->category->area->team_id = $this->team_id;
        $this->category->description = $this->description;
        $this->category->save();

        session()->flash('message', 'Categoría actualizada con éxito.');
        return redirect()->route('admin.categorias.index');
    }

    public function render()
    {
        return view('livewire.admin.categorias.edit');
    }
}
