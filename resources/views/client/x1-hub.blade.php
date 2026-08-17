@extends('layouts.app')

@section('title', 'X1 — ' . $client->name)

@section('content')
<section class="flex flex-1 flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div class="mx-auto w-full max-w-xl">
        <a href="{{ route('client.hub', $client) }}" class="back-link mb-8 inline-flex items-center">Voltar</a>

        <div class="hero-intro mb-8">
            <h1 class="font-display text-3xl tracking-tight text-ink sm:text-4xl">Desafio X1</h1>
            <p class="mt-2 text-base text-ink/60">{{ $client->name }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.x1.store', $client) }}" class="level-panel level-panel--ocean space-y-5">
            @csrf
            <label class="block">
                <span class="text-sm font-bold text-ink/70">Seu nome</span>
                <input type="text" name="name" value="{{ old('name') }}" maxlength="40" required
                    class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink">
            </label>
            <label class="block">
                <span class="text-sm font-bold text-ink/70">Categoria</span>
                <select name="categoria" class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink">
                    <option value="todas">Todas</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat['nome'] }}" @selected(old('categoria') === $cat['nome'])>{{ $cat['nome'] }} ({{ $cat['total'] }})</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="quiz-btn-primary w-full" @disabled(count($categorias) === 0)>Começar desafio</button>
        </form>
    </div>
</section>
@endsection
