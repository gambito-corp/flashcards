<?php


use App\Http\Controllers\Api\Auth\AuthController;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::get('check', [AuthController::class, 'check'])->name('check');
Route::get('refresh', [AuthController::class, 'refresh'])->name('refresh');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->name('email.resend');
Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
Route::middleware('auth:sanctum')->get('me', [AuthController::class, 'me'])->name('me');
//Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
//Route::post('password/forgot', [AuthController::class, 'forgotPassword'])->name('password.forgot');
//Route::post('password/change', [AuthController::class, 'changePassword'])->name('password.change');
//Route::post('email/verify', [AuthController::class, 'verifyEmail'])->name('email.verify');
//Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])->name('email.resend');
//Route::post('email/change', [AuthController::class, 'changeEmail'])->name('email.change');
//Route::post('email/confirm', [AuthController::class, 'confirmEmailChange'])->name('email.confirm');
//Route::post('profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
//Route::post('profile/delete', [AuthController::class, 'deleteProfile'])->name('profile.delete');
