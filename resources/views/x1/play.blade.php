@extends('layouts.app')

@section('title', 'X1 — ' . $playerName)
@section('hide_footer')
@endsection
@section('hide_ads')
@endsection
@section('body_class', 'live-fit')
@section('shell_class', 'h-dvh max-h-dvh')
@section('shell_inner_class', 'h-dvh max-h-dvh overflow-hidden')
@section('header_class', 'live-fit-header')

@section('content')
<section class="live-fit-page flex min-h-0 flex-1 flex-col px-3 py-2 sm:px-6 sm:py-3">
    <div class="mx-auto flex min-h-0 w-full max-w-4xl flex-1 flex-col">
        <div
            class="level-panel level-panel--{{ $level['accent'] ?? 'ocean' }} quiz-fit-panel relative flex min-h-0 flex-1 flex-col overflow-hidden"
            data-x1-play
            data-perguntas="{{ json_encode($challenge->questions, JSON_UNESCAPED_UNICODE) }}"
            data-nivel-slug="{{ $challenge->nivel }}"
            data-role="{{ $role }}"
            data-finish-url="{{ route('x1.finish', $challenge->token) }}"
            data-csrf="{{ csrf_token() }}"
            data-player="{{ $playerName }}"
        >
            <span data-x1-splat class="quiz-splat" aria-hidden="true"></span>

            <div class="flex shrink-0 items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">X1 · {{ $role === 'creator' ? 'Desafiante' : 'Desafiado' }}</p>
                    <h1 class="font-display text-2xl text-ink sm:text-3xl">{{ $playerName }}</h1>
                    <p class="mt-1 text-sm font-semibold text-ink/50">
                        {{ $level['title'] ?? $challenge->nivel }} · {{ $challenge->categoriaLabel() }}
                    </p>
                </div>
                <button type="button" data-x1-sound class="quiz-sound-toggle" aria-label="Alternar som">🔊</button>
            </div>

            <div data-x1-play-area class="mt-2 flex min-h-0 flex-1 flex-col overflow-hidden">
                <div class="flex shrink-0 flex-wrap items-center justify-between gap-2 text-sm font-semibold text-ink/60">
                    <span>Rodada <span data-x1-current>1</span> de <span data-x1-total>{{ $challenge->totalQuestions() }}</span></span>
                    <span>Acertos: <span data-x1-score>0</span></span>
                </div>
                <div class="mt-1.5 h-1.5 w-full shrink-0 overflow-hidden rounded-full bg-ink/10">
                    <div data-x1-progress class="h-full rounded-full bg-ink/60 transition-[width] duration-300" style="width: 0%"></div>
                </div>

                <div class="mt-2 flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain">
                    <div data-x1-visual class="quiz-visual quiz-fit-visual shrink-0"></div>
                    <p data-x1-category class="mt-2 shrink-0 text-center text-[10px] font-bold uppercase tracking-[0.16em] text-ink/45"></p>
                    <h2 data-x1-question class="mt-1 shrink-0 text-center font-display text-lg leading-snug text-ink sm:text-2xl"></h2>
                    <div data-x1-options class="quiz-fit-options mt-2 grid min-h-0 flex-1 content-stretch gap-2"></div>
                </div>

                <div class="quiz-fit-actions mt-2 flex shrink-0 flex-col items-center gap-2 pb-1">
                    <div data-x1-reveal class="quiz-reveal w-full hidden" aria-live="polite">
                        <p class="quiz-reveal__label" data-x1-reveal-label></p>
                        <p class="quiz-reveal__answer" data-x1-reveal-answer></p>
                        <p class="quiz-reveal__hint" data-x1-reveal-hint></p>
                    </div>
                    <button type="button" data-x1-next class="quiz-btn-primary w-full max-w-sm self-center hidden sm:w-auto">
                        Próxima rodada
                    </button>
                </div>
            </div>

            <div data-x1-sending class="mt-4 hidden text-center text-sm font-semibold text-ink/60">
                Salvando pontuação…
            </div>
        </div>
    </div>
</section>
@endsection
