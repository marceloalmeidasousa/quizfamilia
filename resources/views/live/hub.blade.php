@extends('layouts.app')

@section('title', 'Ao Vivo — ' . $brand['name'])

@section('content')
<section class="flex flex-1 flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div
        class="mx-auto w-full max-w-5xl"
        data-live-picker
        data-categories='@json($categoriesByLevel)'
    >
        <a href="{{ route('home') }}" class="back-link mb-8 inline-flex items-center">
            <svg class="me-1.5 h-3.5 w-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4"/>
            </svg>
            Voltar
        </a>

        <div class="hero-intro mb-8 max-w-2xl">
            <h1 class="font-display text-3xl tracking-tight text-ink sm:text-4xl">
                Crie ou entre numa partida
            </h1>
            <p class="mt-2 text-base text-ink/60 sm:text-lg">
                Escolha o nível e a categoria, gere o PIN e os jogadores entram pelo celular.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 flex items-center rounded-2xl border border-danger-medium bg-danger-soft p-4 text-sm font-semibold text-fg-danger" role="alert">
                <svg class="me-2 h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6">
            <div class="level-panel level-panel--coral order-2 md:order-1">
                <h2 class="font-display text-2xl text-ink">Criar partida</h2>

                <form
                    method="POST"
                    action="{{ route('live.create') }}"
                    class="mt-6 space-y-4"
                    data-live-create-form
                >
                    @csrf
                    <input type="hidden" name="categoria" value="{{ old('categoria', 'todas') }}" data-live-categoria>

                    <label class="block text-sm font-bold text-body">1. Nível</label>
                    <div class="grid gap-2 sm:grid-cols-3">
                        @foreach ($levels as $slug => $level)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-ink/10 bg-canvas px-4 py-3 transition has-[:checked]:border-brand-deep has-[:checked]:bg-brand-soft/40">
                                <input
                                    type="radio"
                                    name="nivel"
                                    value="{{ $slug }}"
                                    class="h-4 w-4 border-gray-300 bg-gray-100 text-brand accent-coral focus:ring-2 focus:ring-brand/30"
                                    @checked(old('nivel', 'crianca') === $slug)
                                    required
                                >
                                <span>
                                    <span class="font-bold text-ink">{{ $level['title'] }}</span>
                                    <span class="mt-0.5 block text-xs font-semibold tracking-wide text-body-subtle uppercase">{{ $level['subtitle'] }} · {{ $level['age'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div data-category-panel class="hidden pt-2">
                        <div class="mb-3">
                            <p class="text-sm font-bold text-body">2. Categoria</p>
                            <p data-category-hint class="mt-1 text-sm text-body-subtle"></p>
                        </div>
                        <div data-category-list class="flex flex-wrap gap-2.5"></div>
                    </div>
                </form>
            </div>

            <div class="level-panel level-panel--ocean order-1 md:order-2">
                <h2 class="font-display text-2xl text-ink">Entrar na partida</h2>
                <p class="mt-2 text-sm text-body">Digite o PIN de 6 dígitos e o seu nome.</p>

                <form method="POST" action="{{ route('live.join') }}" class="mt-6 grid gap-4 sm:grid-cols-2 sm:items-end">
                    @csrf
                    <div>
                        <label for="pin" class="mb-2 block text-sm font-bold text-body">PIN</label>
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
                        <label for="name" class="mb-2 block text-sm font-bold text-body">Seu nome</label>
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
                    @include('live._stickers')
                    <button type="submit" class="quiz-btn-primary sm:col-span-2 sm:w-auto">
                        Entrar no jogo
                    </button>
                </form>
            </div>
        </div>

        <x-adsense :slot="config('ads.slots.hub')" class="mt-8" />
    </div>
</section>
@endsection
