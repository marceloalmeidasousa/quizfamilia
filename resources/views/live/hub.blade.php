@extends('layouts.app')

@section('title', 'Ao Vivo — Quiz em Família')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8 sm:py-14">
    <div
        class="mx-auto w-full max-w-5xl"
        data-live-picker
        data-categories='@json($categoriesByLevel)'
    >
        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-ink/60 transition hover:text-ink">
            ← Voltar
        </a>

        <div class="hero-intro mb-10 max-w-2xl">
            <p class="mb-3 font-display text-lg text-coral sm:text-xl">Modo Ao Vivo</p>
            <h1 class="font-display text-4xl leading-[1.05] tracking-tight text-ink sm:text-5xl">
                Crie ou entre numa partida
            </h1>
            <p class="mt-4 text-base text-ink/70 sm:text-lg">
                Escolha o nível e a categoria, gere o PIN e os jogadores entram pelo celular.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-coral/30 bg-peach/60 px-4 py-3 text-sm font-semibold text-ink">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="level-panel level-panel--coral lg:col-span-2">
                <h2 class="font-display text-2xl text-ink">Criar partida</h2>
                <p class="mt-2 text-sm text-ink/65">Você apresenta na tela grande. Não responde. Depois do nível, escolha a categoria.</p>

                <form
                    method="POST"
                    action="{{ route('live.create') }}"
                    class="mt-6 space-y-4"
                    data-live-create-form
                >
                    @csrf
                    <input type="hidden" name="categoria" value="{{ old('categoria', 'todas') }}" data-live-categoria>

                    <label class="block text-sm font-bold text-ink/70">1. Nível</label>
                    <div class="grid gap-2 sm:grid-cols-3">
                        @foreach ($levels as $slug => $level)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-ink/10 bg-white/60 px-4 py-3 has-[:checked]:border-ink/40 has-[:checked]:bg-white">
                                <input
                                    type="radio"
                                    name="nivel"
                                    value="{{ $slug }}"
                                    class="accent-ink"
                                    @checked(old('nivel', 'crianca') === $slug)
                                    required
                                >
                                <span>
                                    <span class="font-bold text-ink">{{ $level['title'] }}</span>
                                    <span class="mt-0.5 block text-xs font-semibold uppercase tracking-wide text-ink/45">{{ $level['subtitle'] }} · {{ $level['age'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div data-category-panel class="hidden pt-2">
                        <div class="mb-3">
                            <p class="text-sm font-bold text-ink/70">2. Categoria</p>
                            <p data-category-hint class="mt-1 text-sm text-ink/60"></p>
                        </div>
                        <div data-category-list class="flex flex-wrap gap-2.5"></div>
                    </div>
                </form>
            </div>

            <div class="level-panel level-panel--ocean lg:col-span-2">
                <h2 class="font-display text-2xl text-ink">Entrar na partida</h2>
                <p class="mt-2 text-sm text-ink/65">Digite o PIN de 6 dígitos e o seu nome.</p>

                <form method="POST" action="{{ route('live.join') }}" class="mt-6 grid gap-4 sm:grid-cols-2 sm:items-end">
                    @csrf
                    <div>
                        <label for="pin" class="mb-1 block text-sm font-bold text-ink/70">PIN</label>
                        <input
                            id="pin"
                            name="pin"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            placeholder="000000"
                            value="{{ old('pin') }}"
                            required
                            class="live-input tracking-[0.35em]"
                        >
                    </div>
                    <div>
                        <label for="name" class="mb-1 block text-sm font-bold text-ink/70">Seu nome</label>
                        <input
                            id="name"
                            name="name"
                            maxlength="40"
                            placeholder="Como você quer aparecer"
                            value="{{ old('name') }}"
                            required
                            class="live-input"
                        >
                    </div>
                    <button type="submit" class="quiz-btn-primary sm:col-span-2 sm:w-auto">Entrar no jogo</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
