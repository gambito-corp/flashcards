<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use App\Models\Area;
use App\Models\Team;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;
use App\Services\Usuarios\UserService;

class Edit extends Component
{
    use WithFileUploads;

    public $usuario;
    public $name, $email, $password, $autoPassword = false, $profile_photo, $isPremium = false;
    public $selectedTeams = [], $selectedRoles = [];
    public $selectedSubjects = [], $availableSubjects = [], $selectedToAdd = [];
    public $deleteAsignature = [];

    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function mount(User $usuario)
    {
        $this->usuario = $usuario;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        // Dejar la contraseña vacía; si se quiere cambiar se ingresa una nueva.
        $this->password = null;
        $this->autoPassword = false;
        $this->isPremium = (bool) $usuario->status;
        // Cargar teams y roles disponibles
        $this->selectedTeams = $usuario->teams->pluck('id')->toArray();
        $this->selectedRoles = $usuario->roles->pluck('id')->toArray();
        // Cargar asignaturas actuales (como array asociativo id => nombre)
        $this->selectedSubjects = $usuario->areas->pluck('name', 'id')->toArray();
        // Cargar asignaturas disponibles según los teams seleccionados
        $this->availableSubjects = Area::query()
            ->whereIn('team_id', $this->selectedTeams)
            ->get();
    }

    public function updatedSelectedTeams($value)
    {
        $this->availableSubjects = Area::query()
            ->whereIn('team_id', $this->selectedTeams)
            ->get();
    }

    public function addSelected()
    {
        foreach ($this->selectedToAdd as $id) {
            $subject = $this->availableSubjects->firstWhere('id', $id);
            if ($subject && !isset($this->selectedSubjects[$id])) {
                $this->selectedSubjects[$id] = $subject->name;
            }
        }
        $this->selectedToAdd = [];
    }

    public function addAll()
    {
        foreach ($this->availableSubjects as $subject) {
            if (!isset($this->selectedSubjects[$subject->id])) {
                $this->selectedSubjects[$subject->id] = $subject->name;
            }
        }
        $this->selectedToAdd = [];
    }

    public function toggleDeleteSubject($id)
    {
        if (in_array($id, $this->deleteAsignature)) {
            $this->deleteAsignature = array_diff($this->deleteAsignature, [$id]);
        } else {
            $this->deleteAsignature[] = $id;
        }
    }

    public function removeSelected()
    {
        foreach ($this->deleteAsignature as $id) {
            unset($this->selectedSubjects[$id]);
        }
        $this->deleteAsignature = [];
    }

    public function removeAll()
    {
        $this->selectedSubjects = [];
        $this->deleteAsignature = [];
    }

    public function updateUser()
    {
        $this->validate([
            'name'            => 'required|min:3',
            'email'           => "required|email|unique:users,email,{$this->usuario->id}",
            'selectedTeams'   => 'required|array',
            'selectedRoles'   => 'required|array',
            'selectedSubjects'=> 'required|array',
            'isPremium'       => 'boolean',
        ]);

        $data = [
            'userId'        => $this->usuario->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'profile_photo' => $this->profile_photo,
            'teams'         => $this->selectedTeams,
            'roles'         => $this->selectedRoles,
            'subjects'      => $this->selectedSubjects, // [subject_id => subject_name]
            'is_premium'    => $this->isPremium,
        ];

        $this->userService->update($data, $this->usuario);

        session()->flash('message', 'Usuario actualizado con éxito.');
        $this->resetForm();

        return redirect()->route('admin.usuarios.index');
    }

    protected function resetForm()
    {
        $this->reset([
            'name', 'email', 'password', 'autoPassword', 'profile_photo',
            'selectedTeams', 'selectedRoles', 'selectedSubjects',
            'availableSubjects', 'selectedToAdd', 'deleteAsignature', 'isPremium'
        ]);
    }

    public function render()
    {
        $this->teams = Team::all();
        $this->roles = Role::all();
        return view('livewire.admin.usuarios.edit', [
            'roles' => $this->roles,
            'teams' => $this->teams,
        ]);
    }

}
