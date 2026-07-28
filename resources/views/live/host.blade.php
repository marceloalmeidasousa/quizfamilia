@extends('layouts.app')

@section('title', 'Sala ' . $session->pin . ' — Ao Vivo')
@section('hide_footer')
@endsection
@section('body_class', 'live-fit')
@section('shell_class', 'h-dvh max-h-dvh')
@section('shell_inner_class', 'h-dvh max-h-dvh overflow-hidden')
@section('header_class', 'live-fit-header')

@section('content')
<section class="live-fit-page flex min-h-0 flex-1 flex-col px-3 py-2 sm:px-6 sm:py-3">
    <div class="mx-auto flex min-h-0 w-full max-w-5xl flex-1 flex-col">
        <a href="{{ route('live.hub') }}" data-live-back class="mb-1 inline-flex shrink-0 text-sm font-semibold text-ink/60 hover:text-ink">← Hub Ao Vivo</a>

        <div
            class="level-panel level-panel--{{ $level['accent'] ?? 'ocean' }} live-host-panel flex min-h-0 flex-1 flex-col overflow-hidden"
            data-live-host
            data-pin="{{ $session->pin }}"
            data-nivel="{{ $session->nivel }}"
            data-state-url="{{ route('live.host.state', $session->pin) }}"
            data-start-url="{{ route('live.start', $session->pin) }}"
            data-advance-url="{{ route('live.advance', $session->pin) }}"
            data-csrf="{{ csrf_token() }}"
        >
            <div data-live-topbar class="flex shrink-0 flex-wrap items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-display text-sm text-ink/55 sm:text-base">
                        Apresentador · {{ $level['title'] ?? '' }} · {{ $session->categoriaLabel() }}
                    </p>
                    <h1 class="font-display text-xl text-ink sm:text-3xl">Sala Ao Vivo</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-live-sound class="quiz-sound-toggle" aria-label="Alternar som">🔊</button>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-ink/45">PIN</p>
                        <p data-live-pin class="font-display text-3xl tracking-[0.12em] text-ink sm:text-5xl">{{ $session->pin }}</p>
                    </div>
                </div>
            </div>

            <p data-live-join class="mt-1 shrink-0 text-xs text-ink/65 sm:text-sm">
                Jogadores entram em <strong>{{ $joinUrl }}</strong> com este PIN.
            </p>

            {{-- Lobby --}}
            <div data-live-lobby class="mt-3 flex min-h-0 flex-1 flex-col overflow-hidden">
                <div class="flex shrink-0 flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-lg text-ink sm:text-xl">Jogadores (<span data-live-count>0</span>)</h2>
                    <button type="button" data-live-start class="quiz-btn-primary">Iniciar partida</button>
                </div>
                <ul data-live-players class="mt-3 grid min-h-0 flex-1 content-start gap-2 overflow-y-auto sm:grid-cols-2"></ul>
                <p data-live-lobby-empty class="mt-3 shrink-0 text-sm text-ink/55">Aguardando jogadores...</p>
            </div>

            {{-- Question / reveal --}}
            <div data-live-play class="mt-2 flex min-h-0 flex-1 flex-col overflow-hidden hidden">
                <div class="flex shrink-0 flex-wrap items-center justify-between gap-2 text-sm font-semibold text-ink/60">
                    <span>Pergunta <span data-live-qnum>1</span>/<span data-live-qtotal>10</span></span>
                    <span data-live-timer class="live-timer">20s</span>
                    <span><span data-live-answers>0</span> respostas</span>
                </div>
                <div class="mt-1.5 h-1.5 shrink-0 overflow-hidden rounded-full bg-ink/10">
                    <div data-live-timer-bar class="h-full rounded-full bg-ink/70 transition-[width] duration-200" style="width:100%"></div>
                </div>

                <div class="mt-2 flex min-h-0 flex-1 flex-col overflow-hidden">
                    <p data-live-category class="shrink-0 text-center text-[10px] font-bold uppercase tracking-[0.16em] text-ink/45"></p>
                    <div data-live-emoji class="mt-1 shrink-0 text-center text-3xl sm:text-4xl"></div>
                    <h2 data-live-question class="mt-1 shrink-0 text-center font-display text-lg leading-snug text-ink sm:text-2xl"></h2>
                    <div data-live-options class="live-host-options mt-2 grid min-h-0 flex-1 content-stretch gap-2"></div>

                    <div data-live-reveal-box class="quiz-reveal mt-2 shrink-0 hidden">
                        <p class="quiz-reveal__label">Resposta revelada</p>
                        <p class="quiz-reveal__answer" data-live-correct-text></p>
                    </div>

                    <button type="button" data-live-advance class="quiz-btn-primary mt-2 shrink-0 self-center hidden">Próxima</button>
                </div>
            </div>

            {{-- Ranking / pódio --}}
            <div data-live-ranking class="mt-2 flex min-h-0 flex-1 flex-col overflow-hidden hidden">
                <h2 class="shrink-0 font-display text-xl text-ink sm:text-2xl" data-live-ranking-title>Ranking</h2>
                <div class="mt-2 min-h-0 flex-1 overflow-y-auto">
                    <div data-live-podium class="live-podium hidden"></div>
                    <p data-live-rest-label class="mt-4 hidden text-xs font-bold uppercase tracking-[0.16em] text-ink/45">Demais participantes</p>
                    <ol data-live-ranking-list class="mt-2 space-y-2"></ol>
                </div>
                <div data-live-ranking-countdown class="mt-3 hidden shrink-0 text-center" aria-live="polite">
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-ink/45">Próxima pergunta em</p>
                    <p data-live-ranking-secs class="font-display text-5xl text-brand-deep sm:text-6xl">7</p>
                    <div class="mx-auto mt-2 h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-ink/10">
                        <div data-live-ranking-bar class="h-full rounded-full bg-brand-deep transition-[width] duration-200" style="width:100%"></div>
                    </div>
                </div>
                <button type="button" data-live-ranking-next class="quiz-btn-primary mt-3 shrink-0 self-center hidden">Próxima pergunta</button>
            </div>
        </div>
    </div>
</section>
@endsection
