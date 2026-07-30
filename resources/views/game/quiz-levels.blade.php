@extends('layouts.app')

@section('title', 'Quiz — Escolha o nível')

@section('content')
<section class="flex flex-1 flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div
        class="mx-auto w-full max-w-6xl"
        data-quiz-picker
        data-levels='@json($levels)'
        data-categories='@json($categoriesByLevel)'
        data-play-url="{{ url('/jogo') }}"
    >
        <a href="{{ route('home') }}" class="back-link mb-8 inline-flex items-center">
            <svg class="me-1.5 h-3.5 w-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4"/>
            </svg>
            Voltar
        </a>

        <div class="hero-intro mb-8 max-w-2xl sm:mb-10">
            <h1 class="font-display text-3xl tracking-tight text-ink sm:text-4xl">
                Escolha o nível
            </h1>
            <p class="mt-2 text-base text-ink/60 sm:text-lg">
                Depois escolha uma categoria para selecionar perguntas dos temas.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5">
            @foreach ($levels as $slug => $level)
                <button
                    type="button"
                    data-pick-level="{{ $slug }}"
                    class="level-card level-card--{{ $level['accent'] }} group"
                    style="--delay: {{ $loop->index * 60 }}ms"
                >
                    <div class="relative flex h-full flex-col">
                        <div class="mb-5 flex items-start justify-between gap-3">
                            <span class="level-icon" aria-hidden="true">
                                @if ($slug === 'crianca') 🧒
                                @elseif ($slug === 'adolescente') 😎
                                @else 🧑
                                @endif
                            </span>
                            <span class="rounded-md bg-white/15 px-2 py-1 text-xs font-bold text-white/85">
                                {{ $level['age'] }}
                            </span>
                        </div>
                        <h2 class="font-display text-2xl text-white sm:text-3xl">{{ $level['title'] }}</h2>
                        <p class="mt-1 text-sm font-bold text-white/70">{{ $level['subtitle'] }}</p>
                        <span class="level-card__cta mt-6">Selecionar →</span>
                    </div>
                </button>
            @endforeach
        </div>

        <div data-category-panel class="mt-8 hidden">
            <div class="rounded-xl bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-display text-2xl text-ink">Escolha a categoria</h2>
                <p data-category-hint class="mt-1 text-sm text-ink/55"></p>
                <div data-category-list class="mt-4 flex flex-wrap gap-2"></div>
            </div>
        </div>

        <x-adsense :slot="config('ads.slots.hub')" class="mt-8" />
    </div>
</section>
@endsection
