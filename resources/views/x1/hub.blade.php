@extends('layouts.app')

@section('title', 'X1 — Desafio')

@section('content')
<section class="flex flex-1 flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div
        class="mx-auto w-full max-w-6xl"
        data-x1-hub
        data-categories='@json($categoriesByLevel)'
    >
        <a href="{{ route('home') }}" class="back-link mb-8 inline-flex items-center">
            <svg class="me-1.5 h-3.5 w-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4"/>
            </svg>
            Voltar
        </a>

        <div class="hero-intro mb-8 max-w-2xl sm:mb-10">
            <h1 class="font-display text-3xl tracking-tight text-ink sm:text-4xl">
                Modo X1 — Desafio
            </h1>
            <p class="mt-2 text-base text-ink/60 sm:text-lg">
                Informe seu nome, escolha o nível e o tema. Depois desafie alguém no WhatsApp.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('x1.store') }}" class="space-y-6" data-x1-form>
            @csrf
            <input type="hidden" name="nivel" value="{{ old('nivel') }}" data-x1-nivel required>
            <input type="hidden" name="categoria" value="{{ old('categoria') }}" data-x1-categoria>

            <div class="rounded-xl bg-white p-5 shadow-sm sm:p-6">
                <label for="x1-name" class="mb-1.5 block text-xs font-bold uppercase tracking-[0.14em] text-ink/50">Seu nome</label>
                <input
                    id="x1-name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    maxlength="40"
                    required
                    placeholder="Como quer aparecer no placar"
                    class="w-full max-w-md rounded-2xl border border-ink/10 bg-canvas px-4 py-3 text-base font-semibold text-ink outline-none ring-brand-soft/40 focus:ring-2"
                >
            </div>

            <div>
                <h2 class="font-display text-2xl text-ink">Escolha o nível</h2>
                <p class="mt-1 text-sm text-ink/55">Somente Adolescente e Adulto</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 sm:gap-5">
                    @foreach ($levels as $slug => $level)
                        <button
                            type="button"
                            data-x1-pick-level="{{ $slug }}"
                            class="level-card level-card--{{ $level['accent'] }} group text-left"
                            style="--delay: {{ $loop->index * 60 }}ms"
                        >
                            <div class="relative flex h-full flex-col">
                                <div class="mb-5 flex items-start justify-between gap-3">
                                    <span class="level-icon" aria-hidden="true">
                                        @if ($slug === 'adolescente') 😎 @else 🧑 @endif
                                    </span>
                                    <span class="rounded-md bg-white/15 px-2 py-1 text-xs font-bold text-white/85">
                                        {{ $level['age'] }}
                                    </span>
                                </div>
                                <h3 class="font-display text-2xl text-white sm:text-3xl">{{ $level['title'] }}</h3>
                                <p class="mt-1 text-sm font-bold text-white/70">{{ $level['subtitle'] }}</p>
                                <span class="level-card__cta mt-6">Selecionar →</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div data-x1-category-panel class="hidden">
                <div class="rounded-xl bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="font-display text-2xl text-ink">Escolha a categoria</h2>
                    <p data-x1-category-hint class="mt-1 text-sm text-ink/55"></p>
                    <div data-x1-category-list class="mt-4 flex flex-wrap gap-2"></div>
                    <button type="submit" data-x1-submit class="quiz-btn-primary mt-6 hidden">
                        Começar o desafio
                    </button>
                </div>
            </div>
        </form>

        <x-adsense :slot="config('ads.slots.hub')" class="mt-8" />
    </div>
</section>
@endsection
