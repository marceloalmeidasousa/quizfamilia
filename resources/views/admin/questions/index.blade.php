@extends('layouts.app')

@section('title', 'Perguntas — Quiz em Família')
@section('hide_ads')
@endsection

@section('content')
<section class="px-4 py-8 sm:px-8 sm:py-10">
    <div class="mx-auto w-full max-w-3xl">
        <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-ink/50 hover:text-ink">← Painel</a>
        <h1 class="mt-2 font-display text-3xl text-ink">Perguntas do Quiz em Família</h1>
        <p class="mt-1 text-sm font-semibold text-ink/55">
            {{ number_format($questions->total()) }}
            {{ $questions->total() === 1 ? 'pergunta' : 'perguntas' }}
            @if ($categoria !== '')
                em {{ $categoria }}
            @endif
            @if ($nivel !== '' && isset($levels[$nivel]))
                · {{ $levels[$nivel]['title'] }}
            @endif
        </p>

        @include('admin._generation-status', ['subject' => $generation])

        @if ($categories->isNotEmpty())
            <div class="mt-6">
                <h2 class="text-sm font-bold text-ink/70">Categorias</h2>
                <ul class="mt-3 flex flex-wrap gap-2">
                    <li>
                        <a href="{{ route('admin.questions.index', array_filter(['nivel' => $nivel ?: null])) }}"
                            class="inline-flex rounded-full px-3 py-1.5 text-sm font-bold ring-1 {{ $categoria === '' ? 'bg-brand-deep text-white ring-brand-deep' : 'bg-white text-ink/70 ring-ink/5 hover:ring-ink/20' }}">
                            Todas ({{ number_format($questionsCount) }})
                        </a>
                    </li>
                    @foreach ($categories as $cat)
                        <li>
                            <a href="{{ route('admin.questions.index', array_filter([
                                'categoria' => $cat->categoria,
                                'nivel' => $nivel ?: null,
                            ])) }}"
                                class="inline-flex rounded-full px-3 py-1.5 text-sm font-bold ring-1 {{ $categoria === $cat->categoria ? 'bg-brand-deep text-white ring-brand-deep' : 'bg-white text-ink/70 ring-ink/5 hover:ring-ink/20' }}">
                                {{ $cat->categoria }} ({{ $cat->total }})
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6">
            <h2 class="text-sm font-bold text-ink/70">Nível</h2>
            <ul class="mt-3 flex flex-wrap gap-2">
                <li>
                    <a href="{{ route('admin.questions.index', array_filter(['categoria' => $categoria ?: null])) }}"
                        class="inline-flex rounded-full px-3 py-1.5 text-sm font-bold ring-1 {{ $nivel === '' ? 'bg-brand-deep text-white ring-brand-deep' : 'bg-white text-ink/70 ring-ink/5 hover:ring-ink/20' }}">
                        Todos
                    </a>
                </li>
                @foreach ($levels as $slug => $level)
                    <li>
                        <a href="{{ route('admin.questions.index', array_filter([
                            'nivel' => $slug,
                            'categoria' => $categoria ?: null,
                        ])) }}"
                            class="inline-flex rounded-full px-3 py-1.5 text-sm font-bold ring-1 {{ $nivel === $slug ? 'bg-brand-deep text-white ring-brand-deep' : 'bg-white text-ink/70 ring-ink/5 hover:ring-ink/20' }}">
                            {{ $level['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-8 space-y-4">
            @forelse ($questions as $question)
                <article class="rounded-3xl bg-white p-5 ring-1 ring-ink/5">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-ink/40">
                        {{ $levels[$question->nivel]['title'] ?? $question->nivel }}
                        · {{ $question->categoria }}
                        @if ($question->code)
                            · {{ $question->code }}
                        @endif
                    </p>
                    <h2 class="mt-2 font-display text-xl text-ink">
                        @if ($question->emoji)
                            <span class="mr-1">{{ $question->emoji }}</span>
                        @endif
                        {{ $question->pergunta }}
                    </h2>
                    @if ($question->imagem)
                        <img src="{{ $question->imagem }}" alt="" class="mt-3 max-h-40 w-auto rounded-2xl object-contain">
                    @endif
                    <ol class="mt-4 space-y-2">
                        @foreach ($question->options as $option)
                            <li class="flex items-start gap-2 rounded-2xl px-3 py-2 text-sm font-semibold {{ $option->is_correct ? 'bg-emerald-50 text-emerald-800' : 'bg-canvas text-ink/70' }}">
                                <span class="mt-0.5 text-ink/35">{{ chr(65 + $loop->index) }}.</span>
                                <span>
                                    @if ($option->emoji)
                                        {{ $option->emoji }}
                                    @endif
                                    {{ $option->texto }}
                                </span>
                                @if ($option->is_correct)
                                    <span class="ml-auto shrink-0 text-xs font-bold uppercase tracking-wide">Correta</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </article>
            @empty
                <p class="rounded-3xl bg-white px-5 py-10 text-center font-semibold text-ink/50 ring-1 ring-ink/5">
                    Nenhuma pergunta do Quiz em Família ainda.
                </p>
            @endforelse
        </div>

        @if ($questions->hasPages())
            <div class="mt-8">
                {{ $questions->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
