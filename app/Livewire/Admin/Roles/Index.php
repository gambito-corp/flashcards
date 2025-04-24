<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public int $paginate = 10;
    public string $search = "";
    public function render()
    {
        $data = Role::query()->where('name', 'LIKE', '%'.$this->search.'%')->paginate($this->paginate);
        return view('livewire.admin.roles.index', compact('data'));
    }
}
