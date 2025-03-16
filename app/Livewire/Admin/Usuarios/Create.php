<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\Area;
use App\Models\Team;
use App\Services\Usuarios\UserService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    use WithFileUploads;

    public $name, $email, $password, $autoPassword = false, $profile_photo, $isPremium = false;
    public $teams, $selectedTeams = [];
    public $roles, $selectedRoles = [];
    public $selectedSubjects = [], $availableSubjects = [], $selectedToAdd = [];
    public $deleteAsignature = [];

    protected UserService $UserService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function render()
    {
        $this->teams = Team::all();
        $this->roles = Role::all();
        return view('livewire.admin.usuarios.create');
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

    public function store()
    {
        $this->validate([
            'name'  => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required_if:autoPassword,false|min:6|nullable',
            'teams' => 'required|array',
            'roles' => 'required|array',
            'selectedSubjects' => 'required|array',
            'isPremium' => 'boolean',
        ]);

        $pwd_generate = false;
        if ($this->autoPassword) {
            $this->password = \Str::random(8);
            $pwd_generate = true;
        }

        // Preparamos los datos a enviar al servicio
        $data = [
            'name'          => $this->name,
            'email'         => $this->email,
            'password'      => $this->password,
            'profile_photo' => $this->profile_photo,
            'teams'         => $this->selectedTeams,
            'roles'         => $this->selectedRoles,
            'subjects'      => $this->selectedSubjects,
            'autoPassword'  => $this->autoPassword,
            'pwd_generate'  => $pwd_generate,
            'is_premium'    => $this->isPremium,
        ];

        // Se delega la creación del usuario en el servicio
        $this->userService->create($data);

        session()->flash('message', 'Usuario creado con éxito.');

        $this->resetForm();

        return redirect()->route('admin.usuarios.index');
    }

    protected function resetForm()
    {
        $this->name = null;
        $this->email = null;
        $this->password = null;
        $this->autoPassword = false;
        $this->profile_photo = null;
        $this->selectedTeams = [];
        $this->selectedRoles = [];
        $this->selectedSubjects = [];
        $this->availableSubjects = [];
        $this->selectedToAdd = [];
        $this->deleteAsignature = [];
        $this->isPremium = false;
    }
}
