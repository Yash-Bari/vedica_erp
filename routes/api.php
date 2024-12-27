<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

// Define API routes
Route::middleware(['api'])->group(function () {
    Route::get('/projects/{project}/worked-time', [ProjectController::class, 'getWorkedTime']);
});
