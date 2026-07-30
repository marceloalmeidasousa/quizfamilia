<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\X1Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'home'])->name('home');
Route::get('/privacidade', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/quiz', [GameController::class, 'quiz'])->name('quiz.levels');
Route::get('/jogo/{nivel}', [GameController::class, 'level'])
    ->whereIn('nivel', array_keys(GameController::LEVELS))
    ->name('game.level');
Route::post('/jogo/{nivel}/registrar-partida', [GamePlayController::class, 'store'])
    ->whereIn('nivel', array_keys(GameController::LEVELS))
    ->middleware('throttle:30,1')
    ->name('game.play.store');

Route::get('/x1', [X1Controller::class, 'hub'])->name('x1.hub');
Route::post('/x1', [X1Controller::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('x1.store');
Route::get('/x1/{token}', [X1Controller::class, 'show'])->name('x1.show');
Route::post('/x1/{token}/entrar', [X1Controller::class, 'join'])
    ->middleware('throttle:20,1')
    ->name('x1.join');
Route::post('/x1/{token}/finalizar', [X1Controller::class, 'finish'])
    ->middleware('throttle:30,1')
    ->name('x1.finish');
Route::get('/x1/{token}/placar', [X1Controller::class, 'scoreboard'])->name('x1.scoreboard');

Route::get('/ao-vivo', [LiveController::class, 'hub'])->name('live.hub');
Route::post('/ao-vivo/criar', [LiveController::class, 'create'])->name('live.create');
Route::get('/ao-vivo/sala/{pin}', [LiveController::class, 'host'])->name('live.host')->where('pin', '[0-9]{6}');
Route::post('/ao-vivo/entrar', [LiveController::class, 'join'])->name('live.join');
Route::get('/ao-vivo/jogar/{token}', [LiveController::class, 'play'])->name('live.player');
Route::post('/ao-vivo/sala/{pin}/iniciar', [LiveController::class, 'start'])->name('live.start')->where('pin', '[0-9]{6}');
Route::post('/ao-vivo/sala/{pin}/avancar', [LiveController::class, 'advance'])->name('live.advance')->where('pin', '[0-9]{6}');
Route::post('/ao-vivo/jogar/{token}/responder', [LiveController::class, 'answer'])->name('live.answer');
Route::get('/ao-vivo/sala/{pin}/estado', [LiveController::class, 'hostState'])->name('live.host.state')->where('pin', '[0-9]{6}');
Route::get('/ao-vivo/jogar/{token}/estado', [LiveController::class, 'playerState'])->name('live.player.state');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/painel', DashboardController::class)
        ->middleware('permission:view dashboard')
        ->name('admin.dashboard');
});
