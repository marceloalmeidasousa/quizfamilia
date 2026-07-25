@extends('layouts.app')

@section('title', 'Quiz — Escolha o nível')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8 sm:py-14">
    <div class="mx-auto w-full max-w-6xl">
        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-ink/60 transition hover:text-ink">
            ← Voltar
        </a>

        <div class="hero-intro mb-10 max-w-2xl sm:mb-14">
            <p class="mb-3 font-display text-lg text-ocean sm:text-xl">Modo Quiz</p>
            <h1 class="font-display text-4xl leading-[1.05] tracking-tight text-ink sm:text-5xl">
                Escolha o nível
            </h1>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
            @foreach ($levels as $slug => $level)
                <a
                    href="{{ route('game.level', $slug) }}"
                    class="level-card level-card--{{ $level['accent'] }} group"
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
                        <span class="mt-8 inline-flex items-center gap-2 text-sm font-bold text-ink transition group-hover:gap-3">
                            Jogar neste nível
                            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
