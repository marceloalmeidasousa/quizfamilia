@extends('layouts.app')

@section('title', $player->name . ' — Ao Vivo')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-8 sm:px-8 sm:py-10">
    <div class="mx-auto w-full max-w-lg">
        <div
            class="level-panel level-panel--{{ $level['accent'] ?? 'coral' }}"
            data-live-player
            data-token="{{ $player->token }}"
            data-nivel="{{ $session->nivel }}"
            data-state-url="{{ route('live.player.state', $player->token) }}"
            data-answer-url="{{ route('live.answer', $player->token) }}"
            data-csrf="{{ csrf_token() }}"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">Jogando como</p>
                    <h1 class="font-display text-2xl text-ink">{{ $player->name }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" data-live-sound class="quiz-sound-toggle" aria-label="Alternar som">🔊</button>
                    <div class="text-right">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">PIN</p>
                        <p class="font-display text-2xl tracking-widest text-ink">{{ $session->pin }}</p>
                    </div>
                </div>
            </div>

            <p class="mt-2 text-sm font-semibold text-ink/55">
                Pontos: <span data-live-myscore>0</span>
            </p>

            <div data-live-wait class="mt-10 text-center">
                <p class="text-5xl" aria-hidden="true">⏳</p>
                <p class="mt-4 font-display text-xl text-ink">Aguardando o apresentador iniciar...</p>
                <p class="mt-2 text-sm text-ink/60" data-live-music-hint>Toque na tela para ativar a música da festa.</p>
            </div>

            <div data-live-question-wrap class="mt-8 hidden">
                <div class="flex items-center justify-between text-sm font-semibold text-ink/60">
                    <span><span data-live-qnum>1</span>/<span data-live-qtotal>10</span></span>
                    <span data-live-timer class="live-timer">20s</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-ink/10">
                    <div data-live-timer-bar class="h-full rounded-full bg-ink/70" style="width:100%"></div>
                </div>
                <p data-live-category class="mt-5 text-center text-xs font-bold uppercase tracking-[0.16em] text-ink/45"></p>
                <div data-live-emoji class="mt-2 text-center text-4xl"></div>
                <h2 data-live-question class="mt-3 text-center font-display text-xl text-ink sm:text-2xl"></h2>
                <div data-live-options class="mt-5 grid gap-3"></div>
                <p data-live-feedback class="mt-4 hidden text-center font-display text-lg"></p>
            </div>

            <div data-live-ranking class="mt-8 hidden">
                <h2 class="font-display text-2xl text-ink" data-live-ranking-title>Ranking</h2>
                <div data-live-podium class="live-podium mt-6 hidden"></div>
                <p data-live-rest-label class="mt-6 hidden text-xs font-bold uppercase tracking-[0.16em] text-ink/45">Demais participantes</p>
                <ol data-live-ranking-list class="mt-4 space-y-2"></ol>
                <a href="{{ route('live.hub') }}" data-live-back-hub class="quiz-btn-primary mt-8 hidden inline-flex w-full justify-center">Voltar ao hub</a>
            </div>
        </div>
    </div>
</section>
@endsection
