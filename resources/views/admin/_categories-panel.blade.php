<h2 class="font-display text-xl text-ink sm:text-2xl">{{ $title ?? 'Categorias' }}</h2>
@if ($categories->isNotEmpty())
    <ul class="mt-4 flex flex-wrap gap-2">
        @foreach ($categories as $cat)
            @php
                $linkParams = ['categoria' => $cat->categoria];
                $url = isset($client)
                    ? route('admin.clients.questions', [$client] + $linkParams)
                    : route('admin.questions.index', $linkParams);
            @endphp
            <li>
                <a href="{{ $url }}"
                    class="inline-flex rounded-full bg-canvas px-3 py-1.5 text-sm font-bold text-ink/70 ring-1 ring-ink/5 hover:ring-ink/20">
                    {{ $cat->categoria }} ({{ $cat->total }})
                </a>
            </li>
        @endforeach
    </ul>
@else
    <p class="mt-4 text-sm font-semibold text-ink/50">Nenhuma categoria ainda.</p>
@endif
