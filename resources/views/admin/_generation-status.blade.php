@if ($subject->questions_generation_status)
    @php
        $generation = $subject->generationStatusMeta();
    @endphp
    <div class="mt-5 rounded-2xl bg-white px-4 py-3 text-sm font-semibold ring-1 ring-ink/5">
        Geração:
        <span class="ml-1 inline-flex rounded-full px-2.5 py-0.5 font-bold {{ $generation['class'] ?? 'text-ink' }}">
            {{ $generation['label'] ?? $subject->questions_generation_status }}
        </span>
        @if ($subject->questions_generation_total)
            — {{ $subject->questions_generation_done }}/{{ $subject->questions_generation_total }}
        @endif
        @if ($subject->questions_generation_error)
            <p class="mt-2 text-sm font-semibold text-coral">{{ $subject->questions_generation_error }}</p>
        @endif
    </div>
@endif
