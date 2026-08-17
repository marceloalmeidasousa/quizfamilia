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

        @if ($client->questions_generation_status)
            @php
                $generation = $client->generationStatusMeta();
            @endphp
            <div class="mt-5 rounded-2xl bg-white px-4 py-3 text-sm font-semibold ring-1 ring-ink/5">
                Geração:
                <span class="ml-1 inline-flex rounded-full px-2.5 py-0.5 font-bold {{ $generation['class'] ?? 'text-ink' }}">
                    {{ $generation['label'] ?? $client->questions_generation_status }}
                </span>
                @if ($client->questions_generation_total)
                    — {{ $client->questions_generation_done }}/{{ $client->questions_generation_total }}
                @endif
                @if ($client->questions_generation_error)
                    <p class="mt-2 text-sm font-semibold text-coral">{{ $client->questions_generation_error }}</p>
                @endif
            </div>
        @endif

        <div class="mt-6 grid flex-1 gap-6 lg:grid-cols-12 lg:items-start">
            <div class="rounded-3xl bg-white p-5 ring-1 ring-ink/5 sm:p-6 lg:col-span-4">
                <h2 class="font-display text-xl text-ink sm:text-2xl">Categorias</h2>
                @if ($categories->isNotEmpty())
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ route('admin.clients.questions', [$client, 'categoria' => $cat->categoria]) }}"
                                    class="inline-flex rounded-full bg-canvas px-3 py-1.5 text-sm font-bold text-ink/70 ring-1 ring-ink/5 hover:ring-ink/20">
                                    {{ $cat->categoria }} ({{ $cat->total }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm font-semibold text-ink/50">Nenhuma categoria ainda.</p>
                @endif
            </div>

            <div class="rounded-3xl bg-white p-5 ring-1 ring-ink/5 sm:p-6 lg:col-span-8">
                <h2 class="font-display text-xl text-ink sm:text-2xl">Gerar perguntas com IA</h2>

                <form method="POST" action="{{ route('admin.clients.generate', $client) }}" class="mt-5 space-y-4">
                    @csrf
                    @if ($errors->any())
                        <div class="rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <label class="block">
                        <span class="text-sm font-bold text-ink/70">Prompt</span>
                        <textarea name="prompt" rows="5" required maxlength="4000"
                            placeholder="Criar perguntas sobre medicina humana, anatomia e fisiologia..."
                            class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink outline-none focus:border-brand-deep">{{ old('prompt') }}</textarea>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
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

                        <div class="block">
                            <span class="text-sm font-bold text-ink/70">Categorias</span>
                            <div data-category-tags
                                data-initial='@json($oldCategories)'
                                data-max="20"
                                class="mt-1.5 flex min-h-[3.25rem] w-full cursor-text flex-wrap items-center gap-2 rounded-2xl border border-ink/10 bg-canvas px-3 py-2 focus-within:border-brand-deep">
                                <input type="text"
                                    name="categories"
                                    data-tag-input
                                    maxlength="80"
                                    autocomplete="off"
                                    placeholder="Digite e pressione Enter"
                                    class="min-w-[8rem] flex-1 border-0 bg-transparent py-1 font-semibold text-ink outline-none">
                                <div data-tag-hidden hidden></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset>
                            <legend class="text-sm font-bold text-ink/70">Usa emoji</legend>
                            <div class="mt-2 flex gap-4 text-sm font-semibold text-ink/70">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="use_emoji" value="1" @checked(old('use_emoji', '1') === '1')>
                                    Sim
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="use_emoji" value="0" @checked(old('use_emoji') === '0')>
                                    Não
                                </label>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm font-bold text-ink/70">Usa imagens</legend>
                            <div class="mt-2 flex gap-4 text-sm font-semibold text-ink/70">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="use_images" value="1" @checked(old('use_images') === '1')>
                                    Sim
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="use_images" value="0" @checked(old('use_images', '0') === '0')>
                                    Não
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <button type="submit" class="quiz-btn-primary w-full sm:w-auto">Gerar perguntas</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
