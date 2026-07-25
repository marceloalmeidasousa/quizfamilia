@extends('layouts.app')

@section('title', $level['title'] . ' — Torta na Cara')
@section('hide_footer')
@endsection
@section('body_class', 'live-fit')
@section('shell_class', 'h-dvh max-h-dvh')
@section('shell_inner_class', 'h-dvh max-h-dvh overflow-hidden')
@section('header_class', 'live-fit-header')

@section('content')
<section class="live-fit-page flex min-h-0 flex-1 flex-col px-3 py-2 sm:px-6 sm:py-3">
    <div class="mx-auto flex min-h-0 w-full max-w-4xl flex-1 flex-col">
        <a href="{{ route('quiz.levels') }}" data-quiz-back class="mb-1 inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-ink/60 transition hover:text-ink">
            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M13 8H3M7 4L3 8l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Voltar aos níveis
        </a>

        <div
            class="level-panel level-panel--{{ $level['accent'] }} quiz-fit-panel relative flex min-h-0 flex-1 flex-col overflow-hidden"
            data-quiz
            data-perguntas="{{ json_encode($perguntas, JSON_UNESCAPED_UNICODE) }}"
            data-nivel="{{ $level['title'] }}"
            data-nivel-slug="{{ $nivel }}"
            data-rodadas="{{ $rodadas }}"
        >
            <span data-quiz-splat class="quiz-splat" aria-hidden="true"></span>

            <div data-quiz-topbar class="flex shrink-0 items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-display text-sm text-ink/55 sm:text-base">Torta na Cara · Nível</p>
                    <h1 class="font-display text-2xl tracking-tight text-ink sm:text-4xl">
                        {{ $level['title'] }}
                    </h1>
                    <p class="mt-0.5 text-xs font-semibold uppercase tracking-[0.14em] text-ink/45">
                        {{ $level['subtitle'] }} · {{ $level['age'] }}
                    </p>
                </div>
                <button type="button" data-quiz-sound class="quiz-sound-toggle" aria-label="Alternar som">🔊</button>
            </div>

            @if (count($perguntas) === 0)
                <p class="mt-4 text-base text-ink/70">
                    Ainda não há perguntas cadastradas para este nível.
                </p>
            @else
                {{-- Tela inicial --}}
                <div data-quiz-start class="mt-3 flex min-h-0 flex-1 flex-col overflow-y-auto">
                    <p class="max-w-xl text-sm leading-relaxed text-ink/70 sm:text-base">
                        {{ $level['description'] }}
                    </p>
                    <ul class="mt-3 space-y-1 text-sm text-ink/70">
                        <li>1. Em cada rodada aparece a figura, a pergunta e as opções</li>
                        <li>2. Marque a resposta que você acha certa</li>
                        <li>3. A resposta correta é revelada na hora</li>
                        <li>4. Errou? Torta na cara! 🥧 Acertou? Escapou! 🎉</li>
                        <li>5. Tem musiquinha de fundo — use o botão 🔊 se quiser silenciar</li>
                    </ul>
                    <p class="mt-3 text-sm font-semibold text-ink/60">
                        {{ $rodadas }} rodadas por partida · perguntas sorteadas de um baralho com {{ count($perguntas) }}
                    </p>
                    <button type="button" data-quiz-begin class="quiz-btn-primary mt-4 shrink-0 self-start">
                        Começar a partida
                    </button>
                </div>

                {{-- Rodada em andamento --}}
                <div data-quiz-play class="mt-2 flex min-h-0 flex-1 flex-col overflow-hidden hidden">
                    <div class="flex shrink-0 flex-wrap items-center justify-between gap-2 text-sm font-semibold text-ink/60">
                        <span>Rodada <span data-quiz-current>1</span> de <span data-quiz-total>{{ $rodadas }}</span></span>
                        <span class="flex items-center gap-3">
                            <span data-quiz-streak class="text-coral"></span>
                            <span>Acertos: <span data-quiz-score>0</span></span>
                        </span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full shrink-0 overflow-hidden rounded-full bg-ink/10">
                        <div data-quiz-progress class="h-full rounded-full bg-ink/60 transition-[width] duration-300" style="width: 0%"></div>
                    </div>

                    <div data-quiz-visual class="quiz-visual quiz-fit-visual mt-2 shrink-0"></div>

                    <p data-quiz-category class="mt-2 shrink-0 text-center text-[10px] font-bold uppercase tracking-[0.16em] text-ink/45"></p>
                    <h2 data-quiz-question class="mt-1 shrink-0 text-center font-display text-lg leading-snug text-ink sm:text-2xl"></h2>

                    <div data-quiz-options class="quiz-fit-options mt-2 grid min-h-0 flex-1 content-stretch gap-2"></div>

                    <div data-quiz-reveal class="quiz-reveal mt-2 shrink-0 hidden" aria-live="polite">
                        <p class="quiz-reveal__label" data-quiz-reveal-label></p>
                        <p class="quiz-reveal__answer" data-quiz-reveal-answer></p>
                        <p class="quiz-reveal__hint" data-quiz-reveal-hint></p>
                    </div>

                    <button type="button" data-quiz-next class="quiz-btn-primary mt-2 shrink-0 self-center hidden">
                        Próxima rodada
                    </button>
                </div>

                {{-- Resultado final --}}
                <div data-quiz-result class="mt-3 flex min-h-0 flex-1 flex-col items-center justify-center overflow-hidden text-center hidden">
                    <p data-quiz-final-emoji class="quiz-final-emoji">🏆</p>
                    <p class="mt-1 font-display text-base text-ink/55">Fim da partida</p>
                    <p class="mt-1 font-display text-4xl text-ink sm:text-5xl">
                        <span data-quiz-final-score>0</span>/<span data-quiz-final-total>{{ $rodadas }}</span>
                    </p>
                    <p data-quiz-message class="mt-2 max-w-md text-sm text-ink/70 sm:text-base"></p>
                    <button type="button" data-quiz-restart class="quiz-btn-primary mt-4">
                        Nova partida
                    </button>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
