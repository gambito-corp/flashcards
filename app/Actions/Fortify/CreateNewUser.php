<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = tap(User::create([
                'name'            => $input['name'],
                'email'           => $input['email'],
                'password'        => Hash::make($input['password']),
                'current_team_id' => Team::query()->where('id', 1)->first()->id,
                'status'          => '0'
            ]), function (User $user) {
                $rol = Role::query()->where('name', 'user')->first();
                $user->assignRole($rol);
            });

            $user->sendEmailVerificationNotification();

            return $user;
        });
    }
}
