@extends('layouts.app')

@section('title', 'Quiz — Escolha o nível')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8 sm:py-14">
    <div
        class="mx-auto w-full max-w-6xl"
        data-quiz-picker
        data-levels='@json($levels)'
        data-categories='@json($categoriesByLevel)'
        data-play-url="{{ url('/jogo') }}"
    >
        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-ink/60 transition hover:text-ink">
            ← Voltar
        </a>

        <div class="hero-intro mb-10 max-w-2xl sm:mb-14">
            <p class="mb-3 font-display text-lg text-ocean sm:text-xl">Modo Quiz</p>
            <h1 class="font-display text-4xl leading-[1.05] tracking-tight text-ink sm:text-5xl">
                Escolha o nível
            </h1>
            <p class="mt-4 text-base text-ink/70 sm:text-lg">
                Depois escolha uma categoria — só entram perguntas desse tema.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
            @foreach ($levels as $slug => $level)
                <button
                    type="button"
                    data-pick-level="{{ $slug }}"
                    class="level-card level-card--{{ $level['accent'] }} group text-left"
                    style="--delay: {{ $loop->index * 90 }}ms"
                >
                    <div class="level-card__glow" aria-hidden="true"></div>
                    <div class="relative flex h-full flex-col">
                        <div class="mb-6 flex items-start justify-between gap-3">
                            <span class="level-icon text-2xl" aria-hidden="true">
                                @if ($slug === 'crianca') 🧒
                                @elseif ($slug === 'adolescente') 😎
                                @else 🧑
                                @endif
                            </span>
                            <span class="rounded-xl bg-white/55 px-2.5 py-1 text-xs font-semibold tracking-wide text-ink/65 backdrop-blur-sm">
                                {{ $level['age'] }}
                            </span>
                        </div>
                        <h2 class="font-display text-3xl tracking-tight text-ink">{{ $level['title'] }}</h2>
                        <p class="mt-1 text-sm font-semibold uppercase tracking-[0.14em] text-ink/45">{{ $level['subtitle'] }}</p>
                        <span class="mt-8 inline-flex items-center gap-2 text-sm font-bold text-ink">
                            Selecionar nível
                        </span>
                    </div>
                </button>
            @endforeach
        </div>

        <div data-category-panel class="mt-10 hidden">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl text-ink sm:text-3xl">Escolha a categoria</h2>
                    <p data-category-hint class="mt-1 text-sm text-ink/60 sm:text-base"></p>
                </div>
            </div>

            <div data-category-list class="flex flex-wrap gap-2.5"></div>
        </div>
    </div>
</section>
@endsection
