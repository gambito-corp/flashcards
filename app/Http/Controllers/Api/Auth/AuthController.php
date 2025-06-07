<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\Pais;
use App\Http\Controllers\Controller;
use App\Services\Api\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class AuthController extends Controller
{

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
            return response()->json($this->authService->login(['email' => $request->email, 'password' => $request->password]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Datos de autenticación inválidos',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al iniciar sesión',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getToken(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Usuario no autenticado'
                ], 401);
            }

            // Revocar tokens anteriores si quieres (opcional)
            // $user->tokens()->where('name', 'react-app')->delete();

            $token = $user->createToken('react-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar token',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function check(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json([
                    'error' => 'Usuario no autenticado'
                ], 401);
            }
            return response()->json([$this->authService->checkToken($token)]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al verificar usuario',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function refresh(Request $request)
    {
        try {
            return response()->json($this->authService->refreshToken());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al refrescar token',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'Usuario no autenticado'
                ], 401);
            }

            // Revocar todos los tokens del usuario
            $user->tokens()->delete();

            return response()->json(['message' => 'Sesión cerrada correctamente'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cerrar sesión',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'telefono' => 'nullable|string|max:15',
                'pais' => ['nullable', new Enum(Pais::class)],
            ]);
            return response()->json($this->authService->register($request->only('name', 'email', 'password', 'telefono', 'pais')));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar usuario',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
