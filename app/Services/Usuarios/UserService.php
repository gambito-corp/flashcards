<?php

namespace App\Services\Usuarios;


use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserService
{


    public function __construct()
    {
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'current_team_id' => $data['teams'][0],
                'status' => $data['is_premium'],
                'email_verified_at' => now(),
            ]);

            if ($data['is_premium'] == 1) {
                Purchase::create([
                    'user_id' => $user->id,
                    'product_id' => 2,
                    'purchase_at' => now(),
                ]);
            }

            if (isset($data['profile_photo'])) {
                $path = $data['profile_photo']->store('avatars', 's3');
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
//            if (isset($data['pwd_generate']) && $data['pwd_generate'] === true) {
//                $user->notify(new \App\Notifications\CustomVerifyEmail($data['password']));
//            } else {
//                $user->sendEmailVerificationNotification();
//            }

            return $user;
        });
    }

    public function update(array $data, User $user): User
    {
        return DB::transaction(function () use ($data, $user) {

            /* ─── Datos básicos ───────────────────────────────────────────── */
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => (bool)($data['is_premium'] ?? false),
            ]);

            /* ─── Registro / actualización de compra premium ─────────────── */
            if (!empty($data['is_premium'])) {
                Purchase::firstOrCreate(
                    ['user_id' => $user->id, 'product_id' => 2],
                    ['purchase_at' => now()]
                );
            }

            /* ─── Foto de perfil ──────────────────────────────────────────── */
            if (!empty($data['profile_photo'])) {
                $path = $data['profile_photo']->store('avatars', 's3');
                $user->update(['profile_photo_path' => $path]);
            }

            /* ─── Sincronizar equipos ─────────────────────────────────────── */
            $user->teams()->sync($data['teams'] ?? []);

            /* ─── Sincronizar roles (ids → modelos Role) ──────────────────── */
            $roleIds = Arr::wrap($data['roles'] ?? []);
            $roles = Role::whereIn('id', $roleIds)->get();
            $user->syncRoles($roles);

            /* ─── Sincronizar asignaturas/áreas ───────────────────────────── */
            if (isset($data['subjects'])) {
                $user->areas()->sync(array_keys($data['subjects']));
            }

            return $user->fresh(['roles', 'teams', 'areas']);
        });
    }

}
