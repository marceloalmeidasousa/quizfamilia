@extends('layouts.app')

@section('title', 'Novo cliente — ' . $brand['name'])
@section('hide_ads')
@endsection

@section('content')
<section class="px-4 py-8 sm:px-8 sm:py-10">
    <div class="mx-auto w-full max-w-xl">
        <a href="{{ route('admin.clients.index') }}" class="text-sm font-bold text-ink/50 hover:text-ink">← Clientes</a>
        <h1 class="mt-2 font-display text-3xl text-ink">Novo cliente</h1>

        <form method="POST" action="{{ route('admin.clients.store') }}" enctype="multipart/form-data" class="mt-8 space-y-5 rounded-3xl bg-white p-6 ring-1 ring-ink/5">
            @csrf
            @include('admin.clients._form')
            <button type="submit" class="quiz-btn-primary w-full">Criar cliente</button>
        </form>
    </div>
</section>
@endsection
