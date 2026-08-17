@extends('layouts.app')

@section('title', 'Ao Vivo — ' . $client->name)

@section('content')
<section class="flex flex-1 flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div class="mx-auto w-full max-w-3xl">
        <a href="{{ route('client.hub', $client) }}" class="back-link mb-8 inline-flex items-center">Voltar</a>

        <div class="hero-intro mb-8 max-w-2xl">
            <h1 class="font-display text-3xl tracking-tight text-ink sm:text-4xl">Crie ou entre numa partida</h1>
            <p class="mt-2 text-base text-ink/60 sm:text-lg">{{ $client->name }} · Ao Vivo com PIN</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6">
            <div class="level-panel level-panel--coral">
                <h2 class="font-display text-2xl text-ink">Criar partida</h2>
                <form method="POST" action="{{ route('client.live.create', $client) }}" class="mt-6 space-y-4">
                    @csrf
                    <label class="block text-sm font-bold text-body">Categoria</label>
                    <select name="categoria" class="w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink">
                        <option value="todas">Todas</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat['nome'] }}">{{ $cat['nome'] }} ({{ $cat['total'] }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="quiz-btn-primary" @disabled(count($categorias) === 0)>Gerar PIN</button>
                </form>
            </div>

            <div class="level-panel level-panel--ocean">
                <h2 class="font-display text-2xl text-ink">Entrar com PIN</h2>
                <form method="POST" action="{{ route('client.live.join', $client) }}" class="mt-6 grid gap-4 sm:grid-cols-2 sm:items-end">
                    @csrf
                    <label class="block">
                        <span class="text-sm font-bold text-body">PIN</span>
                        <input type="text" name="pin" maxlength="6" required inputmode="numeric" pattern="[0-9]{6}"
                            class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-display text-2xl tracking-widest text-ink">
                    </label>
                    <label class="block">
                        <span class="text-sm font-bold text-body">Seu nome</span>
                        <input type="text" name="name" maxlength="40" required
                            class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink">
                    </label>
                    <button type="submit" class="quiz-btn-primary sm:col-span-2">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
