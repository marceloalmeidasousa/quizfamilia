@extends('layouts.app')

@section('title', 'Aguardando — X1')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8">
    <div class="mx-auto w-full max-w-lg text-center">
        <div class="level-panel level-panel--ocean">
            <p class="text-5xl" aria-hidden="true">⏳</p>
            <h1 class="mt-4 font-display text-3xl text-ink">Aguarde um instante</h1>
            <p class="mt-3 text-base text-ink/70">
                {{ $challenge->creator_name }} ainda está jogando este desafio. Peça o link de novo quando a pontuação estiver pronta.
            </p>
            <a href="{{ $homeUrl ?? route('home') }}" class="quiz-btn-primary mt-6 inline-flex">Voltar ao início</a>
        </div>
    </div>
</section>
@endsection
