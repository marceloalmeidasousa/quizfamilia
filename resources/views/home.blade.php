@extends('layouts.app')

@section('title', 'Quiz em Família')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8 sm:py-14">
    <div class="mx-auto w-full max-w-5xl">
        <div class="hero-intro mb-10 max-w-2xl sm:mb-14">
            <p class="mb-3 font-display text-lg text-ocean sm:text-xl">Quiz em Família</p>
            <h1 class="font-display text-4xl leading-[1.05] tracking-tight text-ink sm:text-5xl lg:text-6xl">
                Escolha o modo
            </h1>
            <p class="mt-4 max-w-xl text-base leading-relaxed text-ink/70 sm:text-lg">
                Jogue solo no Quiz ou monte uma partida Ao Vivo com PIN, no estilo Kahoot.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:gap-6">
            <a href="{{ route('quiz.levels') }}" class="level-card level-card--sunshine group" style="--delay: 0ms">
                <div class="level-card__glow" aria-hidden="true"></div>
                <div class="relative flex h-full flex-col">
                    <span class="level-icon mb-6" aria-hidden="true">🥧</span>
                    <h2 class="font-display text-3xl tracking-tight text-ink">Quiz</h2>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-[0.14em] text-ink/45">Modo solo</p>
                    <p class="mt-4 flex-1 text-sm leading-relaxed text-ink/70 sm:text-base">
                        Torta na cara — escolha o nível e jogue sozinho ou revezando.
                    </p>
                    <span class="mt-8 inline-flex items-center gap-2 text-sm font-bold text-ink transition group-hover:gap-3">
                        Jogar Quiz
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('live.hub') }}" class="level-card level-card--coral group" style="--delay: 90ms">
                <div class="level-card__glow" aria-hidden="true"></div>
                <div class="relative flex h-full flex-col">
                    <span class="level-icon mb-6" aria-hidden="true">📡</span>
                    <h2 class="font-display text-3xl tracking-tight text-ink">Ao Vivo</h2>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-[0.14em] text-ink/45">Multiplayer</p>
                    <p class="mt-4 flex-1 text-sm leading-relaxed text-ink/70 sm:text-base">
                        Crie um PIN, todo mundo entra com o nome e compete em tempo real.
                    </p>
                    <span class="mt-8 inline-flex items-center gap-2 text-sm font-bold text-ink transition group-hover:gap-3">
                        Ir para Ao Vivo
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </a>
        </div>
    </div>
</section>
@endsection
