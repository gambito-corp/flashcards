<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    public string $name = '';
    public string $guard_name = 'web';
    public array $guards = [
        'web',
        'api'
    ];

    public function render()
    {
        return view('livewire.admin.roles.create');
    }

    public function save(){
        $this->validate([
            'name' => 'required',
            'guard_name' => 'required'
        ]);

        try {
            \DB::beginTransaction();
            Role::query()->create([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            session()->flash('message', 'Rol creado con éxito.');
            $this->name = '';
            $this->guard_name = 'web';
            \DB::commit();
        }catch (\Exception $exception){
            \DB::rollBack();
            session()->flash('error', $exception->getMessage());
        }
    }
}
