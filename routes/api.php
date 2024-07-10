<?php

use App\Http\Controllers\Api\V1\CakeDaysController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function(){
    Route::get('today',[CakeDaysController::class, 'today']);
    Route::get('next',[CakeDaysController::class, 'next']);
    Route::get('upcoming',[CakeDaysController::class, 'upcoming']);
    Route::post('uploadFile',[CakeDaysController::class, 'uploadFile']);
});