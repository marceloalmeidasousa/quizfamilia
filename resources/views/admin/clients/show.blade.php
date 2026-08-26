@extends('layouts.app')

@section('title', $client->name . ' — Painel')
@section('hide_ads')
@endsection

@section('content')
<section class="flex flex-1 flex-col px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
    <div class="mx-auto flex w-full max-w-[90rem] flex-1 flex-col">
        <a href="{{ route('admin.clients.index') }}" class="text-sm font-bold text-ink/50 hover:text-ink">← Clientes</a>

        <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                @if ($client->logoUrl())
                    <img src="{{ $client->logoUrl() }}" alt="" class="h-12 w-auto shrink-0 rounded-xl bg-white object-contain p-1 ring-1 ring-ink/10 sm:h-14">
                @endif
                <div class="min-w-0">
                    <h1 class="font-display text-2xl text-ink sm:text-3xl">{{ $client->name }}</h1>
                    <p class="mt-1 break-all text-sm font-semibold text-ink/55">
                        <a href="{{ $publicUrl }}" class="underline decoration-ink/20 hover:text-ink" target="_blank" rel="noopener">{{ $publicUrl }}</a>
                        · {{ number_format($client->questions_count) }} perguntas
                        · {{ $client->is_active ? 'Ativo' : 'Inativo' }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.clients.questions', $client) }}" class="rounded-full border border-ink/10 bg-white px-4 py-2 text-sm font-bold text-ink/70 hover:border-ink/25">Ver perguntas</a>
                <a href="{{ route('admin.clients.edit', $client) }}" class="rounded-full border border-ink/10 bg-white px-4 py-2 text-sm font-bold text-ink/70 hover:border-ink/25">Editar</a>
            </div>
        </div>

        @if (session('status'))
            <p class="mt-5 rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-ink ring-1 ring-ink/5">{{ session('status') }}</p>
        @endif

        @include('admin._generation-status', ['generationSummary' => $generationSummary])

        <div class="mt-6 grid flex-1 gap-6 lg:grid-cols-12 lg:items-start">
            <div class="rounded-3xl bg-white p-5 ring-1 ring-ink/5 sm:p-6 lg:col-span-4">
                @include('admin._categories-panel', ['categories' => $categories, 'client' => $client])
            </div>

            <div class="rounded-3xl bg-white p-5 ring-1 ring-ink/5 sm:p-6 lg:col-span-8">
                <h2 class="font-display text-xl text-ink sm:text-2xl">Gerar perguntas com IA</h2>
                @include('admin._generate-questions-form', [
                    'action' => route('admin.clients.generate', $client),
                    'promptPlaceholder' => 'Criar perguntas sobre medicina humana, anatomia e fisiologia...',
                ])
            </div>
        </div>
    </div>
</section>
@endsection
