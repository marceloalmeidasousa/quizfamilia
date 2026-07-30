@extends('layouts.app')

@section('title', 'Entrar — ' . $brand['name'])

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8">
    <div class="mx-auto w-full max-w-md">
        <div class="level-panel level-panel--ocean">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-ink/45">Área restrita</p>
            <h1 class="mt-1 font-display text-3xl text-ink sm:text-4xl">Entrar</h1>
            <p class="mt-2 text-sm font-semibold text-ink/60">
                Acesse o painel de estatísticas do {{ $brand['name'] }}.
            </p>

            @if ($errors->any())
                <div class="mt-4 rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-[0.14em] text-ink/50">E-mail</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full rounded-2xl border border-ink/10 bg-white px-4 py-3 text-base font-semibold text-ink outline-none ring-brand-soft/40 focus:ring-2"
                    >
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-[0.14em] text-ink/50">Senha</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-2xl border border-ink/10 bg-white px-4 py-3 text-base font-semibold text-ink outline-none ring-brand-soft/40 focus:ring-2"
                    >
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-ink/70">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="rounded border-ink/20">
                    Lembrar de mim
                </label>
                <button type="submit" class="quiz-btn-primary w-full">
                    Entrar no painel
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
