@php
    $running = $generationSummary['running'] ?? null;
    $queued = (int) ($generationSummary['queued'] ?? 0);
    $latest = $generationSummary['latest'] ?? null;
    $display = $running ?? (($queued === 0 && $latest) ? $latest : null);
@endphp

@if ($running || $queued > 0 || ($latest && in_array($latest->status, ['done', 'failed'], true)))
    <div class="mt-5 rounded-2xl bg-white px-4 py-3 text-sm font-semibold ring-1 ring-ink/5">
        @if ($running)
            @php($generation = $running->statusMeta())
            Geração em execução:
            <span class="ml-1 inline-flex rounded-full px-2.5 py-0.5 font-bold {{ $generation['class'] ?? 'text-ink' }}">
                {{ $generation['label'] ?? $running->status }}
            </span>
            — {{ $running->done }}/{{ $running->total }}
        @elseif ($queued > 0)
            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 font-bold text-amber-700">
                {{ $queued }} {{ $queued === 1 ? 'geração na fila' : 'gerações na fila' }}
            </span>
            <span class="ml-1 text-ink/60">aguardando o worker processar</span>
        @elseif ($display)
            @php($generation = $display->statusMeta())
            Última geração:
            <span class="ml-1 inline-flex rounded-full px-2.5 py-0.5 font-bold {{ $generation['class'] ?? 'text-ink' }}">
                {{ $generation['label'] ?? $display->status }}
            </span>
            @if ($display->total)
                — {{ $display->done }}/{{ $display->total }}
            @endif
        @endif

        @if ($running && $queued > 0)
            <span class="ml-2 text-ink/60">· mais {{ $queued }} na fila</span>
        @endif

        @if ($display?->error)
            <p class="mt-2 text-sm font-semibold text-coral">{{ $display->error }}</p>
        @endif
    </div>
@endif
