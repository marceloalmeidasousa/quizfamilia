@extends('layouts.app')

@section('title', 'Quiz — ' . $client->name)

@section('content')
<section class="flex flex-1 flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div class="mx-auto w-full max-w-3xl">
        <a href="{{ route('client.hub', $client) }}" class="back-link mb-8 inline-flex items-center">
            <svg class="me-1.5 h-3.5 w-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4"/>
            </svg>
            Voltar
        </a>

        <div class="hero-intro mb-8">
            <h1 class="font-display text-3xl tracking-tight text-ink sm:text-4xl">Escolha a categoria</h1>
            <p class="mt-2 text-base text-ink/60">{{ $client->name }} · modo solo</p>
        </div>

        @if (count($categorias) === 0)
            <p class="rounded-2xl bg-white px-4 py-6 text-center font-semibold text-ink/55 ring-1 ring-ink/5">
                Ainda não há perguntas neste quiz.
            </p>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('client.play', $client) }}" class="rounded-3xl bg-brand-deep px-5 py-5 text-white transition hover:opacity-95">
                    <p class="font-display text-xl">Todas</p>
                    <p class="mt-1 text-sm text-white/70">Mistura todas as categorias</p>
                </a>
                @foreach ($categorias as $cat)
                    <a href="{{ route('client.play', [$client, 'categoria' => $cat['nome']]) }}"
                        class="rounded-3xl bg-white px-5 py-5 ring-1 ring-ink/5 transition hover:ring-ink/15">
                        <p class="font-display text-xl text-ink">{{ $cat['nome'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-ink/50">{{ $cat['total'] }} perguntas</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
