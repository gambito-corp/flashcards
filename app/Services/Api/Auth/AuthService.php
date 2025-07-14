<?php

namespace App\Services\Api\Auth;

use App\Models\User;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken as RefreshToken;
use D076\SanctumRefreshTokens\Services\TokenService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            // Revoca todos los tokens anteriores para sesión única
            $user->tokens()->delete();

            // Genera access y refresh token
            $tokens = (new TokenService($user))->createTokens();

            return [
                'success' => true,
                'access_token' => $tokens->access_token,
                'refresh_token' => $tokens->refresh_token,
                'expires_in' => config('sanctum.expiration') * 60,
                'refresh_expires_in' => config('sanctum.refresh_token_expiration') * 60,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'pais' => $user->pais,
                    'image' => $user->profile_photo_path,
                    'email_verified_at' => $user->email_verified_at,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'is_pro' => $user->status,
                    'current_team_id' => $user->current_team_id,
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

    public function register(array $data)
    {
        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'telefono' => $data['telefono'] ?? null,
                'pais' => $data['pais'] ?? null,
                'email_verified_at' => now(),
            ]);
            if ($user->email_verified_at === null) {
                $user->email_verified_at = now();
                $user->save();
            }
            // Genera tokens al registrar
            $tokens = (new TokenService($user))->createTokens();
            DB::commit();
            return [
                'success' => true,
                'access_token' => $tokens->access_token,
                'refresh_token' => $tokens->refresh_token,
                'expires_in' => config('sanctum.expiration') * 60,
                'refresh_expires_in' => config('sanctum.refresh_token_expiration') * 60,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'status' => 201
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => 'Error al registrar usuario',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function refreshTokens(string $refreshToken)
    {
        try {
            $refreshTokenModel = RefreshToken::where('token', $refreshToken)->first();

            if (!$refreshTokenModel || $refreshTokenModel->isExpired()) {
                return [
                    'success' => false,
                    'error' => 'Refresh token inválido o expirado',
                    'status' => 401
                ];
            }

            $user = $refreshTokenModel->user;
            // Revoca todos los tokens anteriores para sesión única
            $user->tokens()->delete();

            // Genera nuevos tokens
            $tokens = (new TokenService($user))->createTokens();

            // Invalida el refresh token viejo
            $refreshTokenModel->delete();

            return [
                'success' => true,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => config('sanctum.expiration') * 60,
                'refresh_expires_in' => config('sanctum.refresh_token_expiration') * 60,
                'status' => 200
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al renovar token',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function logout($user)
    {
        try {
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Usuario no autenticado',
                    'status' => 401
                ];
            }
            // Revoca todos los tokens del usuario
            $user->tokens()->delete();
            return [
                'success' => true,
                'message' => 'Sesión cerrada correctamente',
                'status' => 200
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al cerrar sesión',
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
                    'telefono' => $user->telefono,
                    'pais' => $user->pais,
                    'image' => $user->profile_photo_path,
                    'email_verified_at' => $user->email_verified_at,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'is_pro' => $user->status,
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

    public function resendVerificationEmail($user)
    {
        try {
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Usuario no autenticado',
                    'status' => 401
                ];
            }
            if ($user->hasVerifiedEmail()) {
                return [
                    'success' => true,
                    'message' => 'El correo electrónico ya está verificado.',
                    'status' => 200
                ];
            }
//            $user->sendEmailVerificationNotification();
            return [
                'success' => true,
                'message' => 'Correo electrónico de verificación enviado.',
                'status' => 200
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al reenviar correo electrónico de verificación',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function forgotPassword(string $email)
    {
        try {
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Usuario no encontrado',
                    'status' => 404
                ];
            }
            $user->sendPasswordResetNotification($user->createToken('react-app')->plainTextToken);
            return [
                'success' => true,
                'message' => 'Correo electrónico de restablecimiento de contraseña enviado.',
                'status' => 200
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al enviar correo electrónico de restablecimiento de contraseña',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();
            \Log::info('User data retrieved: ', ['user' => $user]);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Usuario no autenticado',
                    'status' => 401
                ];
            }
            return [
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'pais' => $user->pais,
                    'image' => $user->profile_photo_path,
                    'email_verified_at' => $user->email_verified_at,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'is_pro' => $user->status,
                ],
                'status' => 200
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener los datos del usuario',
                'message' => $e->getMessage(),
                'status' => 500
            ];
        }
    }
}
