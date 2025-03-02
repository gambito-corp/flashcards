<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class NewUpdateProfileInformationForm extends Component
{
    use WithFileUploads;

    public $user;
    public $state = [];
    public $photo;

    public function mount()
    {
        // Asumimos que el usuario autenticado es el que se va a editar.
        $this->user = auth()->user();

        // Inicializamos el estado con la información actual del usuario.
        $this->state = [
            'name'  => $this->user->name,
            'email' => $this->user->email,
        ];
    }

    public function updateProfileInformation()
    {
        // Limpiamos errores previos.
        $this->resetErrorBag();

        // Validamos la información del formulario.
        $validatedData = $this->validate([
            'state.name'  => 'required|string|max:255',
            'state.email' => 'required|email|max:255',
            'photo'       => 'nullable|image|max:2048', // máximo 2MB, ajusta según necesites.
        ]);

        // Si se subió una nueva foto, la procesamos y la subimos a S3.
        if ($this->photo) {
            $fileName = uniqid() . '.' . $this->photo->getClientOriginalExtension();
            $path = Storage::disk('s3')->putFileAs('avatars', $this->photo, $fileName);
            // Actualizamos la ruta de la foto de perfil en el modelo del usuario.
            $this->user->profile_photo_path = $path;
        }

        // Actualizamos la información básica del usuario.
        $this->user->update($this->state);
        $this->user->save();

        // Emitir evento para mostrar el mensaje de "Saved".
        $this->dispatch('saved');
        session()->flash('status', 'Perfil actualizado correctamente.');
    }

    public function deleteProfilePhoto()
    {
        if ($this->user->profile_photo_path) {
            Storage::disk('s3')->delete($this->user->profile_photo_path);
            $this->user->profile_photo_path = null;
            $this->user->save();
        }
    }

    public function render()
    {
        return view('livewire.profile.new-update-profile-information-form');
    }
}
