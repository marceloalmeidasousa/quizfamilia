@extends('layouts.app')

@section('title', 'Placar X1')
@section('hide_ads')
@endsection

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8">
    <div class="mx-auto w-full max-w-lg text-center">
        <div class="level-panel level-panel--{{ $level['accent'] ?? 'ocean' }}">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">Placar final</p>
            <h1 class="mt-2 font-display text-3xl text-ink">X1 — Desafio</h1>
            <p class="mt-2 text-sm font-semibold text-ink/55">
                {{ $challenge->categoriaLabel() }} · {{ $level['title'] ?? $challenge->nivel }}
            </p>

            @if ($challenge->status === 'finished')
                @php
                    $headline = match ($winner) {
                        'empate' => 'Empate!',
                        'creator' => $challenge->creator_name.' venceu!',
                        'opponent' => $challenge->opponent_name.' venceu!',
                        default => 'Resultado',
                    };
                @endphp
                <p class="mt-6 font-display text-3xl text-ink">{{ $headline }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-white/80 px-4 py-4 ring-1 ring-ink/5 {{ $winner === 'creator' ? 'ring-2 ring-emerald-500' : '' }}">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">Desafiante</p>
                        <p class="mt-1 font-display text-xl text-ink">{{ $challenge->creator_name }}</p>
                        <p class="mt-2 font-display text-4xl text-ink">{{ $challenge->creator_score }}/{{ $challenge->totalQuestions() }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/80 px-4 py-4 ring-1 ring-ink/5 {{ $winner === 'opponent' ? 'ring-2 ring-emerald-500' : '' }}">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">Desafiado</p>
                        <p class="mt-1 font-display text-xl text-ink">{{ $challenge->opponent_name }}</p>
                        <p class="mt-2 font-display text-4xl text-ink">{{ $challenge->opponent_score }}/{{ $challenge->totalQuestions() }}</p>
                    </div>
                </div>
            @else
                <p class="mt-6 text-base text-ink/70">
                    O desafio ainda não foi concluído pelos dois jogadores.
                </p>
                @if ($challenge->creator_score !== null)
                    <p class="mt-3 font-semibold text-ink">
                        {{ $challenge->creator_name }}: {{ $challenge->creator_score }}/{{ $challenge->totalQuestions() }}
                    </p>
                @endif
            @endif

            <a href="{{ $x1HubUrl ?? route('x1.hub') }}" class="quiz-btn-primary mt-8 inline-flex">
                Novo X1
            </a>
            <a href="{{ $homeUrl ?? route('home') }}" class="mt-4 block text-sm font-bold text-ink/55 hover:text-ink">
                Voltar ao início
            </a>
        </div>
    </div>
</section>
@endsection
