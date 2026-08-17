@extends('layouts.app')

@section('title', 'Quiz personalizados — ' . $brand['name'])
@section('hide_ads')
@endsection

@section('content')
<section class="px-4 py-8 sm:px-8 sm:py-10">
    <div class="mx-auto w-full max-w-5xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-ink/50 hover:text-ink">← Painel</a>
                <h1 class="mt-2 font-display text-3xl text-ink">Quiz personalizados</h1>
                <p class="mt-1 text-sm font-semibold text-ink/55">Clientes B2B com link em quizedu.com.br/{slug}</p>
            </div>
            <a href="{{ route('admin.clients.create') }}" class="quiz-btn-primary inline-flex">Novo cliente</a>
        </div>

        @if (session('status'))
            <p class="mt-6 rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-ink ring-1 ring-ink/5">{{ session('status') }}</p>
        @endif

        <div class="mt-8 overflow-hidden rounded-3xl bg-white ring-1 ring-ink/5">
            <table class="w-full min-w-[36rem] text-left text-sm">
                <thead class="bg-canvas text-xs font-bold uppercase tracking-[0.12em] text-ink/45">
                    <tr>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Perguntas</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/5">
                    @forelse ($clients as $client)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-ink">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-ink/70">/{{ $client->slug }}</td>
                            <td class="px-4 py-3 text-ink/70">{{ number_format($client->questions_count) }}</td>
                            <td class="px-4 py-3">
                                @if ($client->is_active)
                                    <span class="text-emerald-700">Ativo</span>
                                @else
                                    <span class="text-ink/40">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.clients.show', $client) }}" class="font-bold text-brand-deep hover:underline">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-ink/50">Nenhum cliente ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
