<?php

use Lib\Route;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\FtpController;
use App\Controllers\AnteproyectoController;

Route::get('/', [HomeController::class, 'index']);

Route::get('/users', [UserController::class, 'show']);

Route::get('/login', [UserController::class, 'index'], true);

Route::post('/register', [UserController::class, 'store'], true);

//Ruta de prueva
Route::get('/ftp', [FtpController::class, 'index']);

//Rutas para el manejo del anteproyecto
Route::get('/anteproyecto', [AnteproyectoController::class, 'show']);
Route::get('/anteproyecto/:id/:ext', [AnteproyectoController::class, 'index']);
Route::post('/anteproyecto', [AnteproyectoController::class, 'store'], true);
Route::delete('/anteproyecto/delete', [AnteproyectoController::class, 'delete'], true);
Route::delete('/anteproyecto/:id/:ext', [AnteproyectoController::class, 'destroy']);


Route::dispatch();

