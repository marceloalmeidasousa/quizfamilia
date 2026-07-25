<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\LiveController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'home'])->name('home');
Route::get('/quiz', [GameController::class, 'quiz'])->name('quiz.levels');
Route::get('/jogo/{nivel}', [GameController::class, 'level'])
    ->whereIn('nivel', array_keys(GameController::LEVELS))
    ->name('game.level');

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
