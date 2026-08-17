@extends('layouts.app')

@section('title', $client->name . ' — Painel')
@section('hide_ads')
@endsection

@section('content')
<section class="px-4 py-8 sm:px-8 sm:py-10">
    <div class="mx-auto w-full max-w-3xl">
        <a href="{{ route('admin.clients.index') }}" class="text-sm font-bold text-ink/50 hover:text-ink">← Clientes</a>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                @if ($client->logoUrl())
                    <img src="{{ $client->logoUrl() }}" alt="" class="h-14 w-auto rounded-xl bg-white object-contain p-1 ring-1 ring-ink/10">
                @endif
                <div>
                    <h1 class="font-display text-3xl text-ink">{{ $client->name }}</h1>
                    <p class="mt-1 text-sm font-semibold text-ink/55">
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
            <p class="mt-6 rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-ink ring-1 ring-ink/5">{{ session('status') }}</p>
        @endif

        @if ($client->questions_generation_status)
            <div class="mt-6 rounded-2xl bg-white px-4 py-3 text-sm font-semibold ring-1 ring-ink/5">
                Geração:
                <strong>{{ $client->questions_generation_status }}</strong>
                @if ($client->questions_generation_total)
                    — {{ $client->questions_generation_done }}/{{ $client->questions_generation_total }}
                @endif
                @if ($client->questions_generation_error)
                    <p class="mt-2 text-sm font-semibold text-coral">{{ $client->questions_generation_error }}</p>
                @endif
            </div>
        @endif

        @if ($categories->isNotEmpty())
            <div class="mt-8">
                <h2 class="font-display text-xl text-ink">Categorias</h2>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($categories as $cat)
                        <li>
                            <a href="{{ route('admin.clients.questions', [$client, 'categoria' => $cat->categoria]) }}"
                                class="inline-flex rounded-full bg-white px-3 py-1.5 text-sm font-bold text-ink/70 ring-1 ring-ink/5 hover:ring-ink/20">
                                {{ $cat->categoria }} ({{ $cat->total }})
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-10 rounded-3xl bg-white p-6 ring-1 ring-ink/5">
            <h2 class="font-display text-2xl text-ink">Gerar perguntas com IA</h2>

            <form method="POST" action="{{ route('admin.clients.generate', $client) }}" class="mt-6 space-y-4">
                @csrf
                @if ($errors->any())
                    <div class="rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
                        {{ $errors->first() }}
                    </div>
                @endif

                <label class="block">
                    <span class="text-sm font-bold text-ink/70">Prompt</span>
                    <textarea name="prompt" rows="4" required maxlength="4000"
                        placeholder="Criar perguntas sobre medicina humana, anatomia e fisiologia..."
                        class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink outline-none focus:border-brand-deep">{{ old('prompt') }}</textarea>
                </label>

                <label class="block">
                    <span class="text-sm font-bold text-ink/70">Total de perguntas (1–100)</span>
                    <input type="number" name="total" min="1" max="100" value="{{ old('total', 20) }}" required
                        class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink outline-none focus:border-brand-deep">
                </label>

                @php
                    $oldCategories = old('categories', []);
                    if (is_string($oldCategories)) {
                        $oldCategories = preg_split('/[\n,;]+/', $oldCategories) ?: [];
                    }
                    $oldCategories = collect($oldCategories)
                        ->map(fn ($c) => trim((string) $c))
                        ->filter()
                        ->values()
                        ->all();
                @endphp

                <div>
                    <span class="text-sm font-bold text-ink/70">Categorias</span>
                    <div data-category-tags
                        data-initial='@json($oldCategories)'
                        data-max="20"
                        class="mt-1.5 flex min-h-[3.25rem] w-full cursor-text flex-wrap items-center gap-2 rounded-2xl border border-ink/10 bg-canvas px-3 py-2 focus-within:border-brand-deep">
                        <input type="text"
                            data-tag-input
                            maxlength="80"
                            autocomplete="off"
                            class="min-w-[8rem] flex-1 border-0 bg-transparent py-1 font-semibold text-ink outline-none">
                        <div data-tag-hidden hidden></div>
                    </div>
                </div>

                <button type="submit" class="quiz-btn-primary w-full sm:w-auto">Gerar perguntas</button>
            </form>
        </div>
    </div>
</section>
@endsection
