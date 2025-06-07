<?php


use App\Http\Controllers\Api\Auth\AuthController;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('check', [AuthController::class, 'check'])->name('check');
Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

//Route::post('token', [AuthController::class, 'getToken'])->name('token');
//Route::post('register', [AuthController::class, 'register'])->name('register');
//Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
//Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
//Route::post('password/forgot', [AuthController::class, 'forgotPassword'])->name('password.forgot');
//Route::post('password/change', [AuthController::class, 'changePassword'])->name('password.change');
//Route::post('email/verify', [AuthController::class, 'verifyEmail'])->name('email.verify');
//Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])->name('email.resend');
//Route::post('email/change', [AuthController::class, 'changeEmail'])->name('email.change');
//Route::post('email/confirm', [AuthController::class, 'confirmEmailChange'])->name('email.confirm');
//Route::post('profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
//Route::post('profile/delete', [AuthController::class, 'deleteProfile'])->name('profile.delete');
