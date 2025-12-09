<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/auth/login');
});

Route::middleware('auth')->group(function () {
    Route::get('/api/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/api/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
});
