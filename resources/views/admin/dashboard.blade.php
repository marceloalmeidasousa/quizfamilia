@extends('layouts.app')

@section('title', 'Painel — ' . $brand['name'])
@section('hide_ads')
@endsection

@section('content')
@php
    $tz = 'America/Sao_Paulo';
@endphp
<section class="px-4 py-8 sm:px-8 sm:py-10">
    <div class="mx-auto w-full max-w-6xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">Administração</p>
                <h1 class="font-display text-3xl text-ink sm:text-4xl">Painel de estatísticas</h1>
                <p class="mt-1 text-sm font-semibold text-ink/55">
                    Olá, {{ auth()->user()->name }} · horários em Brasília ({{ $tz }})
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full border border-ink/10 bg-white px-4 py-2 text-sm font-bold text-ink/70 transition hover:border-ink/25 hover:text-ink">
                    Sair
                </button>
            </form>
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
            <div class="rounded-3xl bg-brand-deep px-5 py-4 text-white">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-white/60">Jogos iniciados</p>
                <p class="mt-1 font-display text-3xl">{{ number_format($total_plays) }}</p>
            </div>
            <div class="rounded-3xl bg-white px-5 py-4 shadow-sm ring-1 ring-ink/5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">Solo</p>
                <p class="mt-1 font-display text-3xl text-ink">{{ number_format($solo_plays) }}</p>
            </div>
            <div class="rounded-3xl bg-white px-5 py-4 shadow-sm ring-1 ring-ink/5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">Ao vivo</p>
                <p class="mt-1 font-display text-3xl text-ink">{{ number_format($live_plays) }}</p>
            </div>
            <div class="rounded-3xl bg-white px-5 py-4 shadow-sm ring-1 ring-ink/5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">X1 criados</p>
                <p class="mt-1 font-display text-3xl text-ink">{{ number_format($x1_total) }}</p>
            </div>
            <div class="rounded-3xl bg-white px-5 py-4 shadow-sm ring-1 ring-ink/5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">X1 finalizados</p>
                <p class="mt-1 font-display text-3xl text-ink">{{ number_format($x1_finished) }}</p>
            </div>
            <div class="rounded-3xl bg-white px-5 py-4 shadow-sm ring-1 ring-ink/5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">X1 aguardando</p>
                <p class="mt-1 font-display text-3xl text-ink">{{ number_format($x1_awaiting) }}</p>
            </div>
            <div class="rounded-3xl bg-white px-5 py-4 shadow-sm ring-1 ring-ink/5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">Acessos</p>
                <p class="mt-1 font-display text-3xl text-ink">{{ number_format($total_visits) }}</p>
            </div>
            <div class="rounded-3xl bg-white px-5 py-4 shadow-sm ring-1 ring-ink/5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-ink/45">Ao vivo finalizados</p>
                <p class="mt-1 font-display text-3xl text-ink">{{ number_format($live_finished) }}</p>
            </div>
        </div>

        <div class="mt-10">
            <h2 class="font-display text-2xl text-ink">Desafios X1 recentes</h2>
            <p class="mt-1 text-sm font-semibold text-ink/50">Criador, adversário, placar e status</p>

            <div class="mt-4 overflow-hidden rounded-3xl bg-white ring-1 ring-ink/5">
                <div class="max-h-[22rem] overflow-auto">
                    <table class="w-full min-w-[40rem] text-left text-sm">
                        <thead class="sticky top-0 bg-canvas text-xs font-bold uppercase tracking-[0.12em] text-ink/45">
                            <tr>
                                <th class="px-4 py-3">Quando</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Tema</th>
                                <th class="px-4 py-3">Desafiante</th>
                                <th class="px-4 py-3">Desafiado</th>
                                <th class="px-4 py-3">Placar</th>
                                <th class="px-4 py-3">Local</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recent_x1 as $x1)
                                @php
                                    $statusLabel = match ($x1->status) {
                                        'playing_creator' => 'Jogando (criador)',
                                        'awaiting_opponent' => 'Aguardando rival',
                                        'playing_opponent' => 'Jogando (rival)',
                                        'finished' => 'Finalizado',
                                        'expired' => 'Expirado',
                                        default => $x1->status,
                                    };
                                    $winner = $x1->winnerLabel();
                                    $placar = '—';
                                    if ($x1->creator_score !== null) {
                                        $totalQ = $x1->totalQuestions();
                                        $placar = $x1->creator_name.' '.$x1->creator_score;
                                        if ($x1->opponent_score !== null) {
                                            $placar .= ' × '.$x1->opponent_name.' '.$x1->opponent_score;
                                            $placar .= ' /'.$totalQ;
                                            if ($winner === 'empate') {
                                                $placar .= ' (empate)';
                                            } elseif ($winner === 'creator') {
                                                $placar .= ' (venceu '.$x1->creator_name.')';
                                            } elseif ($winner === 'opponent') {
                                                $placar .= ' (venceu '.$x1->opponent_name.')';
                                            }
                                        } else {
                                            $placar .= '/'.$totalQ;
                                        }
                                    }
                                @endphp
                                <tr class="border-t border-ink/5">
                                    <td class="px-4 py-3 font-semibold text-ink/80 whitespace-nowrap">
                                        {{ $x1->created_at?->timezone($tz)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold">{{ $statusLabel }}</td>
                                    <td class="px-4 py-3">{{ $x1->nivel }} · {{ $x1->categoriaLabel() }}</td>
                                    <td class="px-4 py-3">{{ $x1->creator_name }}</td>
                                    <td class="px-4 py-3">{{ $x1->opponent_name ?: '—' }}</td>
                                    <td class="px-4 py-3 text-ink/80">{{ $placar }}</td>
                                    <td class="px-4 py-3 text-ink/70">
                                        <span class="font-semibold text-ink">{{ $x1->city ?: '—' }}</span>
                                        @if ($x1->country)
                                            <span class="block text-xs text-ink/50">{{ $x1->country }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center font-semibold text-ink/45">
                                        Nenhum desafio X1 ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-2">
            <div>
                <h2 class="font-display text-2xl text-ink">Partidas recentes</h2>
                <p class="mt-1 text-sm font-semibold text-ink/50">Quem jogou, quando e de onde</p>

                <div class="mt-4 overflow-hidden rounded-3xl bg-white ring-1 ring-ink/5">
                    <div class="max-h-[28rem] overflow-auto">
                        <table class="w-full min-w-[32rem] text-left text-sm">
                            <thead class="sticky top-0 bg-canvas text-xs font-bold uppercase tracking-[0.12em] text-ink/45">
                                <tr>
                                    <th class="px-4 py-3">Quando</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Nível</th>
                                    <th class="px-4 py-3">Jogadores</th>
                                    <th class="px-4 py-3">Cidade / local</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recent_plays as $play)
                                    <tr class="border-t border-ink/5">
                                        <td class="px-4 py-3 font-semibold text-ink/80 whitespace-nowrap">
                                            {{ $play->started_at?->timezone($tz)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold">
                                            {{ $play->type === 'live' ? 'Ao vivo' : ($play->type === 'x1' ? 'X1' : 'Solo') }}
                                        </td>
                                        <td class="px-4 py-3">{{ $play->nivel }}@if($play->categoria) · {{ $play->categoria }}@endif</td>
                                        <td class="px-4 py-3">
                                            {{ collect($play->player_names ?? [])->filter()->implode(', ') ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-ink/70">
                                            <span class="font-semibold text-ink">{{ $play->city ?: '—' }}</span>
                                            @if ($play->country)
                                                <span class="block text-xs text-ink/50">{{ $play->country }}</span>
                                            @endif
                                            @if ($play->ip_address)
                                                <span class="block text-xs text-ink/40">{{ $play->ip_address }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center font-semibold text-ink/45">
                                            Nenhuma partida registrada ainda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="font-display text-2xl text-ink">Acessos recentes</h2>
                <p class="mt-1 text-sm font-semibold text-ink/50">Horário (Brasília), página e cidade</p>

                <div class="mt-4 overflow-hidden rounded-3xl bg-white ring-1 ring-ink/5">
                    <div class="max-h-[28rem] overflow-auto">
                        <table class="w-full min-w-[32rem] text-left text-sm">
                            <thead class="sticky top-0 bg-canvas text-xs font-bold uppercase tracking-[0.12em] text-ink/45">
                                <tr>
                                    <th class="px-4 py-3">Horário</th>
                                    <th class="px-4 py-3">Página</th>
                                    <th class="px-4 py-3">Cidade</th>
                                    <th class="px-4 py-3">Navegador</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recent_visits as $visit)
                                    <tr class="border-t border-ink/5">
                                        <td class="px-4 py-3 font-semibold text-ink/80 whitespace-nowrap">
                                            {{ $visit->visited_at?->timezone($tz)->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs">{{ $visit->path }}</td>
                                        <td class="px-4 py-3 text-ink/70">
                                            <span class="font-semibold text-ink">{{ $visit->city ?: '—' }}</span>
                                            @if ($visit->country)
                                                <span class="block text-xs text-ink/50">{{ $visit->country }}</span>
                                            @endif
                                            @if ($visit->ip_address)
                                                <span class="block text-xs text-ink/40">{{ $visit->ip_address }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-ink/55" title="{{ $visit->user_agent }}">{{ $visit->userAgentShort() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center font-semibold text-ink/45">
                                            Nenhum acesso registrado ainda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
