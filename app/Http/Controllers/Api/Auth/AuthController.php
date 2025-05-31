<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Obtener token de Sanctum para usuario autenticado via web
     */
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
}
