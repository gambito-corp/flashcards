<?php

namespace App\Services\Api\Auth;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials)
    {
        try {
            if (!isset($credentials['email']) || !isset($credentials['password'])) {
                throw ValidationException::withMessages([
                    'email' => ['El campo email es obligatorio.'],
                    'password' => ['El campo password es obligatorio.'],
                ]);
            }

            if (!Auth::attempt($credentials)) {
                return [
                    'success' => false,
                    'error' => 'Credenciales inválidas',
                    'status' => 401
                ];
            }

            $user = Auth::user();
            $token = $user->createToken('react-app')->plainTextToken;

            return [
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'status' => 200
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'error' => 'Datos de autenticación inválidos',
                'message' => $e->getMessage(),
                'status' => 422
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al iniciar sesión',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function checkToken(string $token)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Token no válido o expirado',
                    'status' => 401
                ];
            }
            return [
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'status' => 200
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al verificar el token',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function refreshToken()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Usuario no autenticado',
                    'status' => 401
                ];
            }
            $token = $user->createToken('react-app')->plainTextToken;
            return [
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'status' => 200
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al refrescar el token',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }
}
