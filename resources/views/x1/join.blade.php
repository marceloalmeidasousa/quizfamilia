@extends('layouts.app')

@section('title', 'Aceitar desafio — X1')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8">
    <div class="mx-auto w-full max-w-lg">
        <div class="level-panel level-panel--{{ $level['accent'] ?? 'ocean' }}">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">Você foi desafiado</p>
            <h1 class="mt-2 font-display text-3xl text-ink">X1 — Desafio</h1>
            <p class="mt-3 text-base text-ink/70">
                <strong>{{ $challenge->creator_name }}</strong> fez
                <strong>{{ $challenge->creator_score }}/{{ $challenge->totalQuestions() }}</strong>
                no tema <strong>{{ $challenge->categoriaLabel() }}</strong>
                ({{ $level['title'] ?? $challenge->nivel }}).
            </p>
            <p class="mt-2 text-sm font-semibold text-ink/55">
                Jogue as mesmas perguntas e veja quem ganha.
            </p>

            @if ($errors->any())
                <div class="mt-4 rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('x1.join', $challenge->token) }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-[0.14em] text-ink/50">Seu nome</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="40"
                        required
                        autofocus
                        class="w-full rounded-2xl border border-ink/10 bg-white px-4 py-3 text-base font-semibold text-ink outline-none ring-brand-soft/40 focus:ring-2"
                    >
                </div>
                <button type="submit" class="quiz-btn-primary w-full">
                    Aceitar e jogar
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
