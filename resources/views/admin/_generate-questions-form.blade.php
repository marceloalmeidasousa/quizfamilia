@php
    $oldCategories = old('categories', []);
    if (is_string($oldCategories)) {
        $oldCategories = preg_split('/[\n,;]+/', $oldCategories) ?: [];
    }
    $oldCategories = collect($oldCategories)
        ->map(fn ($c) => trim((string) $c))
        ->filter()
        ->values()
        ->all();
@endphp

<form method="POST" action="{{ $action }}" class="mt-5 space-y-4">
    @csrf
    @if ($errors->any())
        <div class="rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
            {{ $errors->first() }}
        </div>
    @endif

    @if (! empty($showNivel) && ! empty($levels))
        <div>
            <span class="text-sm font-bold text-ink/70">Nível</span>
            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                @foreach ($levels as $slug => $level)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-ink/10 bg-canvas px-4 py-3 transition has-[:checked]:border-brand-deep has-[:checked]:bg-brand-soft/40">
                        <input type="radio" name="nivel" value="{{ $slug }}"
                            class="h-4 w-4 border-gray-300 bg-gray-100 text-brand accent-coral focus:ring-2 focus:ring-brand/30"
                            @checked(old('nivel', 'crianca') === $slug) required>
                        <span>
                            <span class="font-bold text-ink">{{ $level['title'] }}</span>
                            <span class="mt-0.5 block text-xs font-semibold tracking-wide text-body-subtle uppercase">{{ $level['subtitle'] }} · {{ $level['age'] }}@if ($slug === 'crianca') · 2 opções@else · 4 opções@endif</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <label class="block">
        <span class="text-sm font-bold text-ink/70">Prompt</span>
        <textarea name="prompt" rows="5" required maxlength="4000"
            placeholder="{{ $promptPlaceholder ?? 'Criar perguntas sobre ciências, história, geografia...' }}"
            class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink outline-none focus:border-brand-deep">{{ old('prompt') }}</textarea>
    </label>

    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block">
            <span class="text-sm font-bold text-ink/70">Total de perguntas (1–100)</span>
            <input type="number" name="total" min="1" max="100" value="{{ old('total', 20) }}" required
                class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink outline-none focus:border-brand-deep">
        </label>

        <div class="block">
            <span class="text-sm font-bold text-ink/70">Categorias</span>
            <div data-category-tags
                data-initial='@json($oldCategories)'
                data-max="20"
                class="mt-1.5 flex min-h-[3.25rem] w-full cursor-text flex-wrap items-center gap-2 rounded-2xl border border-ink/10 bg-canvas px-3 py-2 focus-within:border-brand-deep">
                <input type="text"
                    name="categories"
                    data-tag-input
                    maxlength="80"
                    autocomplete="off"
                    placeholder="Digite e pressione Enter"
                    class="min-w-[8rem] flex-1 border-0 bg-transparent py-1 font-semibold text-ink outline-none">
                <div data-tag-hidden hidden></div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <fieldset>
            <legend class="text-sm font-bold text-ink/70">Usa emoji</legend>
            <div class="mt-2 flex gap-4 text-sm font-semibold text-ink/70">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="use_emoji" value="1" @checked(old('use_emoji', '1') === '1')>
                    Sim
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="use_emoji" value="0" @checked(old('use_emoji') === '0')>
                    Não
                </label>
            </div>
        </fieldset>
        <fieldset>
            <legend class="text-sm font-bold text-ink/70">Usa imagens</legend>
            <div class="mt-2 flex gap-4 text-sm font-semibold text-ink/70">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="use_images" value="1" @checked(old('use_images') === '1')>
                    Sim
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="use_images" value="0" @checked(old('use_images', '0') === '0')>
                    Não
                </label>
            </div>
        </fieldset>
    </div>

    <button type="submit" class="quiz-btn-primary w-full sm:w-auto">Gerar perguntas</button>
</form>
