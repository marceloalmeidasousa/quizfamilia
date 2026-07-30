@extends('layouts.app')

@section('title', 'Desafiar — X1')

@section('hide_ads')
@endsection

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8">
    <div class="mx-auto w-full max-w-lg text-center">
        <div class="level-panel level-panel--{{ $level['accent'] ?? 'ocean' }}">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">X1 · Sua pontuação</p>
            <p class="mt-4 text-5xl" aria-hidden="true">⚔️</p>
            <h1 class="mt-3 font-display text-3xl text-ink">{{ $challenge->creator_name }}</h1>
            <p class="mt-2 font-display text-5xl text-ink">
                {{ $challenge->creator_score }}/{{ $challenge->totalQuestions() }}
            </p>
            <p class="mt-3 text-sm font-semibold text-ink/60">
                Tema: {{ $challenge->categoriaLabel() }} · {{ $level['title'] ?? $challenge->nivel }}
            </p>
            <p class="mt-4 text-base text-ink/70">
                Desafie alguém no WhatsApp com as mesmas perguntas. O primeiro a terminar conta no placar.
            </p>

            <a
                href="{{ $whatsappUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="quiz-btn-primary mt-6 inline-flex w-full items-center justify-center gap-2"
            >
                Desafiar no WhatsApp
            </a>

            <p class="mt-4 break-all text-xs text-ink/45">
                Ou copie o link:<br>
                <a href="{{ route('x1.show', $challenge->token) }}" class="font-semibold text-ink/70 underline">
                    {{ route('x1.show', $challenge->token) }}
                </a>
            </p>

            <a href="{{ route('home') }}" class="mt-6 inline-block text-sm font-bold text-ink/55 hover:text-ink">
                Voltar ao início
            </a>
        </div>
    </div>
</section>
@endsection
