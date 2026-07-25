@extends('layouts.app')

@section('title', 'Sala ' . $session->pin . ' — Ao Vivo')

@section('content')
<section class="flex flex-1 flex-col px-5 py-8 sm:px-8 sm:py-10">
    <div class="mx-auto w-full max-w-4xl">
        <a href="{{ route('live.hub') }}" class="mb-6 inline-flex text-sm font-semibold text-ink/60 hover:text-ink">← Hub Ao Vivo</a>

        <div
            class="level-panel level-panel--{{ $level['accent'] ?? 'ocean' }}"
            data-live-host
            data-pin="{{ $session->pin }}"
            data-state-url="{{ route('live.host.state', $session->pin) }}"
            data-start-url="{{ route('live.start', $session->pin) }}"
            data-advance-url="{{ route('live.advance', $session->pin) }}"
            data-csrf="{{ csrf_token() }}"
        >
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-display text-lg text-ink/55">Apresentador · {{ $level['title'] ?? '' }}</p>
                    <h1 class="mt-1 font-display text-3xl text-ink sm:text-4xl">Sala Ao Vivo</h1>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-ink/45">PIN</p>
                    <p data-live-pin class="font-display text-5xl tracking-[0.12em] text-ink sm:text-6xl">{{ $session->pin }}</p>
                </div>
            </div>

            <p class="mt-4 text-sm text-ink/65">
                Jogadores entram em <strong>{{ $joinUrl }}</strong> com este PIN.
            </p>

            {{-- Lobby --}}
            <div data-live-lobby class="mt-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-xl text-ink">Jogadores (<span data-live-count>0</span>)</h2>
                    <button type="button" data-live-start class="quiz-btn-primary">Iniciar partida</button>
                </div>
                <ul data-live-players class="mt-4 grid gap-2 sm:grid-cols-2"></ul>
                <p data-live-lobby-empty class="mt-4 text-sm text-ink/55">Aguardando jogadores...</p>
            </div>

            {{-- Question / reveal --}}
            <div data-live-play class="mt-8 hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm font-semibold text-ink/60">
                    <span>Pergunta <span data-live-qnum>1</span>/<span data-live-qtotal>10</span></span>
                    <span data-live-timer class="live-timer">20s</span>
                    <span><span data-live-answers>0</span> respostas</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-ink/10">
                    <div data-live-timer-bar class="h-full rounded-full bg-ink/70 transition-all duration-200" style="width:100%"></div>
                </div>

                <p data-live-category class="mt-6 text-center text-xs font-bold uppercase tracking-[0.16em] text-ink/45"></p>
                <div data-live-emoji class="mt-3 text-center text-5xl"></div>
                <h2 data-live-question class="mt-3 text-center font-display text-2xl text-ink sm:text-3xl"></h2>
                <div data-live-options class="mt-6 grid gap-3"></div>

                <div data-live-reveal-box class="quiz-reveal mt-6 hidden">
                    <p class="quiz-reveal__label">Resposta revelada</p>
                    <p class="quiz-reveal__answer" data-live-correct-text></p>
                </div>

                <button type="button" data-live-advance class="quiz-btn-primary mt-6 hidden">Próxima</button>
            </div>

            {{-- Ranking --}}
            <div data-live-ranking class="mt-8 hidden">
                <h2 class="font-display text-2xl text-ink" data-live-ranking-title>Ranking</h2>
                <ol data-live-ranking-list class="mt-4 space-y-2"></ol>
                <a href="{{ route('live.hub') }}" class="quiz-btn-primary mt-8 inline-flex">Nova partida</a>
            </div>
        </div>
    </div>
</section>
@endsection
