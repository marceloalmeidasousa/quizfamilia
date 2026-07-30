@extends('layouts.app')

@section('title', 'Desafio expirado')

@section('content')
<section class="flex flex-1 flex-col justify-center px-5 py-10 sm:px-8">
    <div class="mx-auto w-full max-w-lg text-center">
        <div class="level-panel level-panel--coral">
            <p class="text-5xl" aria-hidden="true">⏳</p>
            <h1 class="mt-4 font-display text-3xl text-ink">Desafio expirado</h1>
            <p class="mt-3 text-base text-ink/70">
                Este X1 não está mais disponível. Crie um novo desafio e mande de novo no WhatsApp.
            </p>
            <a href="{{ route('x1.hub') }}" class="quiz-btn-primary mt-6 inline-flex">Criar novo X1</a>
        </div>
    </div>
</section>
@endsection
