<?php

namespace App\Services\Usuarios;


use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserService
{


    public function __construct(){}
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            if (isset($data['profile_photo'])) {
                $path = $data['profile_photo']->store('profile_photos', 'public');
                $user->profile_photo_path = $path;
                $user->save();
            } else {
                // Si no se sube foto, nos aseguramos de que profile_photo_path sea null
                $user->update(['profile_photo_path' => null]);
            }
            $user->teams()->sync($data['teams']);
            $roles = Role::query()->whereIn('id', $data['roles'])->get();
            foreach ($roles as $role) {
                $user->assignRole($role);
            }
            $subjectIds = array_keys($data['subjects']);
            $user->areas()->sync($subjectIds);

            return $user;
        });
    }

    public function update(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // Actualiza los datos básicos del usuario
            $user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);

            // Guarda la foto de perfil si se envía, o deja la existente
            if (isset($data['profile_photo'])) {
                $path = $data['profile_photo']->store('profile_photos', 'public');
                $user->update(['profile_photo_path' => $path]);
            } else {
                // Si no se envía nueva foto, se deja la actual o se establece null para usar el avatar por defecto
                $user->update(['profile_photo_path' => $user->profile_photo_path ?? null]);
            }

            // Sincroniza las relaciones
            $user->teams()->sync($data['teams']);
            $user->syncRoles($data['roles']);

            // Para asignaturas, se espera que $data['subjects'] sea un array asociativo [subject_id => subject_name]
            $subjectIds = array_keys($data['subjects']);
            $user->areas()->sync($subjectIds);

            return $user;
        });
    }

}
