<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuizClientController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\ClientLiveController;
use App\Http\Controllers\Client\ClientPortalController;
use App\Http\Controllers\Client\ClientX1Controller;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\X1Controller;
use App\Models\QuizClient;
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

    Route::middleware('permission:view dashboard')->prefix('painel')->name('admin.')->group(function () {
        Route::get('/clientes', [QuizClientController::class, 'index'])->name('clients.index');
        Route::get('/clientes/novo', [QuizClientController::class, 'create'])->name('clients.create');
        Route::post('/clientes', [QuizClientController::class, 'store'])->name('clients.store');
        Route::get('/clientes/{client}', [QuizClientController::class, 'show'])->name('clients.show');
        Route::get('/clientes/{client}/perguntas', [QuizClientController::class, 'questions'])->name('clients.questions');
        Route::get('/clientes/{client}/editar', [QuizClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clientes/{client}', [QuizClientController::class, 'update'])->name('clients.update');
        Route::post('/clientes/{client}/gerar-perguntas', [QuizClientController::class, 'generate'])
            ->middleware('throttle:10,1')
            ->name('clients.generate');
    });
});

$reserved = implode('|', array_map('preg_quote', QuizClient::RESERVED_SLUGS));

Route::middleware('quiz.edu')
    ->prefix('{client}')
    ->where(['client' => '^(?!'.$reserved.')[a-z0-9]+(?:-[a-z0-9]+)*$'])
    ->group(function () {
        Route::get('/', [ClientPortalController::class, 'hub'])->name('client.hub');
        Route::get('/quiz', [ClientPortalController::class, 'quiz'])->name('client.quiz');
        Route::get('/jogo', [ClientPortalController::class, 'play'])->name('client.play');
        Route::post('/jogo/registrar-partida', [ClientPortalController::class, 'playStore'])
            ->middleware('throttle:30,1')
            ->name('client.play.store');

        Route::get('/ao-vivo', [ClientLiveController::class, 'hub'])->name('client.live.hub');
        Route::post('/ao-vivo/criar', [ClientLiveController::class, 'create'])->name('client.live.create');
        Route::get('/ao-vivo/sala/{pin}', [ClientLiveController::class, 'host'])->name('client.live.host')->where('pin', '[0-9]{6}');
        Route::post('/ao-vivo/entrar', [ClientLiveController::class, 'join'])->name('client.live.join');
        Route::get('/ao-vivo/jogar/{token}', [ClientLiveController::class, 'play'])->name('client.live.player');
        Route::post('/ao-vivo/sala/{pin}/iniciar', [ClientLiveController::class, 'start'])->name('client.live.start')->where('pin', '[0-9]{6}');
        Route::post('/ao-vivo/sala/{pin}/avancar', [ClientLiveController::class, 'advance'])->name('client.live.advance')->where('pin', '[0-9]{6}');
        Route::post('/ao-vivo/jogar/{token}/responder', [ClientLiveController::class, 'answer'])->name('client.live.answer');
        Route::get('/ao-vivo/sala/{pin}/estado', [ClientLiveController::class, 'hostState'])->name('client.live.host.state')->where('pin', '[0-9]{6}');
        Route::get('/ao-vivo/jogar/{token}/estado', [ClientLiveController::class, 'playerState'])->name('client.live.player.state');

        Route::get('/x1', [ClientX1Controller::class, 'hub'])->name('client.x1.hub');
        Route::post('/x1', [ClientX1Controller::class, 'store'])->middleware('throttle:20,1')->name('client.x1.store');
        Route::get('/x1/{token}', [ClientX1Controller::class, 'show'])->name('client.x1.show');
        Route::post('/x1/{token}/entrar', [ClientX1Controller::class, 'join'])->middleware('throttle:20,1')->name('client.x1.join');
        Route::post('/x1/{token}/finalizar', [ClientX1Controller::class, 'finish'])->middleware('throttle:30,1')->name('client.x1.finish');
        Route::get('/x1/{token}/placar', [ClientX1Controller::class, 'scoreboard'])->name('client.x1.scoreboard');
    });
