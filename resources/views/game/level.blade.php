@extends('layouts.app')

@section('title', $level['title'] . ' — Torta na Cara')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8 sm:py-14">
    <div class="mx-auto w-full max-w-3xl">
        <a href="{{ route('quiz.levels') }}" class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-ink/60 transition hover:text-ink">
            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M13 8H3M7 4L3 8l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Voltar aos níveis
        </a>

        <div
            class="level-panel level-panel--{{ $level['accent'] }} relative"
            data-quiz
            data-perguntas="{{ json_encode($perguntas, JSON_UNESCAPED_UNICODE) }}"
            data-nivel="{{ $level['title'] }}"
            data-nivel-slug="{{ $nivel }}"
            data-rodadas="{{ $rodadas }}"
        >
            <span data-quiz-splat class="quiz-splat" aria-hidden="true"></span>

            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-display text-lg text-ink/55">Torta na Cara · Nível</p>
                    <h1 class="mt-1 font-display text-4xl tracking-tight text-ink sm:text-5xl">
                        {{ $level['title'] }}
                    </h1>
                    <p class="mt-2 text-sm font-semibold uppercase tracking-[0.14em] text-ink/45">
                        {{ $level['subtitle'] }} · {{ $level['age'] }}
                    </p>
                </div>
                <button type="button" data-quiz-sound class="quiz-sound-toggle" aria-label="Alternar som">🔊</button>
            </div>

            @if (count($perguntas) === 0)
                <p class="mt-6 text-base text-ink/70">
                    Ainda não há perguntas cadastradas para este nível.
                </p>
            @else
                {{-- Tela inicial --}}
                <div data-quiz-start class="mt-6">
                    <p class="max-w-xl text-base leading-relaxed text-ink/70 sm:text-lg">
                        {{ $level['description'] }}
                    </p>
                    <ul class="mt-5 space-y-2 text-sm text-ink/70 sm:text-base">
                        <li>1. Em cada rodada aparece a figura, a pergunta e as opções</li>
                        <li>2. Marque a resposta que você acha certa</li>
                        <li>3. A resposta correta é revelada na hora</li>
                        <li>4. Errou? Torta na cara! 🥧 Acertou? Escapou! 🎉</li>
                        <li>5. Tem musiquinha de fundo — use o botão 🔊 se quiser silenciar</li>
                    </ul>
                    <p class="mt-4 text-sm font-semibold text-ink/60">
                        {{ $rodadas }} rodadas por partida · perguntas sorteadas de um baralho com {{ count($perguntas) }}
                    </p>
                    <button type="button" data-quiz-begin class="quiz-btn-primary mt-6">
                        Começar a partida
                    </button>
                </div>

                {{-- Rodada em andamento --}}
                <div data-quiz-play class="mt-6 hidden">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm font-semibold text-ink/60">
                        <span>Rodada <span data-quiz-current>1</span> de <span data-quiz-total>{{ $rodadas }}</span></span>
                        <span class="flex items-center gap-3">
                            <span data-quiz-streak class="text-coral"></span>
                            <span>Acertos: <span data-quiz-score>0</span></span>
                        </span>
                    </div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-ink/10">
                        <div data-quiz-progress class="h-full rounded-full bg-ink/60 transition-all duration-300" style="width: 0%"></div>
                    </div>

                    <div data-quiz-visual class="quiz-visual mt-6"></div>

                    <p data-quiz-category class="mt-5 text-center text-xs font-bold uppercase tracking-[0.16em] text-ink/45"></p>
                    <h2 data-quiz-question class="mt-2 text-center font-display text-2xl leading-snug text-ink sm:text-3xl"></h2>

                    <div data-quiz-options class="mt-6 grid gap-3"></div>

                    <div data-quiz-reveal class="quiz-reveal mt-6 hidden" aria-live="polite">
                        <p class="quiz-reveal__label" data-quiz-reveal-label></p>
                        <p class="quiz-reveal__answer" data-quiz-reveal-answer></p>
                        <p class="quiz-reveal__hint" data-quiz-reveal-hint></p>
                    </div>

                    <button type="button" data-quiz-next class="quiz-btn-primary mt-6 hidden">
                        Próxima rodada
                    </button>
                </div>

                {{-- Resultado final --}}
                <div data-quiz-result class="mt-6 hidden text-center">
                    <p data-quiz-final-emoji class="quiz-final-emoji">🏆</p>
                    <p class="mt-1 font-display text-lg text-ink/55">Fim da partida</p>
                    <p class="mt-1 font-display text-5xl text-ink">
                        <span data-quiz-final-score>0</span>/<span data-quiz-final-total>{{ $rodadas }}</span>
                    </p>
                    <p data-quiz-message class="mt-3 text-base text-ink/70"></p>
                    <button type="button" data-quiz-restart class="quiz-btn-primary mt-6">
                        Nova partida
                    </button>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
