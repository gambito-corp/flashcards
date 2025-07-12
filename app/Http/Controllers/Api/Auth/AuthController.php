<?php

namespace App\Http\Controllers\Api\Auth;

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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        $result = $this->authService->login([
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function register(Request $request)
    {
        \Log::info('Register request data: ', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'telefono' => 'nullable|string|max:15',
            'pais' => ['nullable', new Enum(\App\Enums\Pais::class)],
        ]);
        $result = $this->authService->register($request->only('name', 'email', 'password', 'telefono', 'pais'));
        return response()->json($result, $result['status'] ?? 201);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);
        $result = $this->authService->refreshTokens($request->input('refresh_token'));
        return response()->json($result, $result['status'] ?? 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $result = $this->authService->logout($user);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function check(Request $request)
    {
        $token = $request->bearerToken();
        $result = $this->authService->checkToken($token);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();
        $result = $this->authService->resendVerificationEmail($user);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $result = $this->authService->forgotPassword($request->email);
        return response()->json($result, $result['status'] ?? 200);
    }

    public function me(Request $request)
    {
        \Log::info('Fetching authenticated user data', ['Request' => $request->header('Authorization')]);
        $response = $this->authService->me();
        return response()->json($response, 200);
    }
}
