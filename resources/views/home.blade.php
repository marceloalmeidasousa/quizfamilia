@extends('layouts.app')

@section('title', 'Quiz em Família')

@section('content')
<section class="flex flex-1 flex-col justify-start px-4 pt-6 pb-10 sm:px-6 sm:pt-8 sm:pb-12 lg:px-8">
    <div class="mx-auto w-full max-w-5xl">
        <div class="hero-intro mb-5 sm:mb-6">
            <h1 class="font-display text-3xl tracking-tight text-ink sm:text-4xl">
                Escolha o modo
            </h1>
            <p class="mt-2 max-w-xl text-base text-ink/60 sm:text-lg">
                Solo ou Ao Vivo com PIN — diversão em família.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 sm:gap-5">
            <a href="{{ route('quiz.levels') }}" class="level-card level-card--sunshine group" style="--delay: 0ms">
                <div class="relative flex h-full flex-col">
                    <span class="level-icon mb-5" aria-hidden="true">❓</span>
                    <h2 class="font-display text-2xl text-white sm:text-3xl">Quiz</h2>
                    <p class="mt-1 text-sm font-bold text-white/70">Modo solo</p>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-white/80 sm:text-base">
                        Escolha o nível e jogue sozinho ou revezando.
                    </p>
                    <span class="level-card__cta mt-6">Jogar Quiz →</span>
                </div>
            </a>

            <a href="{{ route('live.hub') }}" class="level-card level-card--coral group" style="--delay: 60ms">
                <div class="relative flex h-full flex-col">
                    <span class="level-icon mb-5" aria-hidden="true">📡</span>
                    <h2 class="font-display text-2xl text-white sm:text-3xl">Ao Vivo</h2>
                    <p class="mt-1 text-sm font-bold text-white/70">Multiplayer</p>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-white/80 sm:text-base">
                        Crie um PIN e compete em tempo real.
                    </p>
                    <span class="level-card__cta mt-6">Ir para Ao Vivo →</span>
                </div>
            </a>
        </div>
    </div>
</section>
@endsection
