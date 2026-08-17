@if ($errors->any())
    <div class="rounded-2xl bg-coral/15 px-4 py-3 text-sm font-semibold text-ink">
        {{ $errors->first() }}
    </div>
@endif

<label class="block">
    <span class="text-sm font-bold text-ink/70">Nome</span>
    <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" required maxlength="120"
        class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink outline-none focus:border-brand-deep">
</label>

<label class="block">
    <span class="text-sm font-bold text-ink/70">Slug (URL)</span>
    <input type="text" name="slug" value="{{ old('slug', $client->slug ?? '') }}" required maxlength="64" pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
        placeholder="unifenas"
        class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 font-semibold text-ink outline-none focus:border-brand-deep">
    <span class="mt-1 block text-xs text-ink/45">quizedu.com.br/<strong>slug</strong></span>
</label>

<label class="block">
    <span class="text-sm font-bold text-ink/70">Logo</span>
    <input type="file" name="logo" accept="image/*"
        class="mt-1.5 w-full rounded-2xl border border-ink/10 bg-canvas px-4 py-3 text-sm font-semibold text-ink">
    @isset($client)
        @if ($client->logoUrl())
            <div class="mt-3 flex items-center gap-3">
                <img src="{{ $client->logoUrl() }}" alt="" class="h-12 w-auto rounded-lg object-contain">
                <label class="flex items-center gap-2 text-sm font-semibold text-ink/60">
                    <input type="checkbox" name="remove_logo" value="1"> Remover logo
                </label>
            </div>
        @endif
    @endisset
</label>

<label class="flex items-center gap-2 text-sm font-bold text-ink/70">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $client->is_active ?? true))>
    Cliente ativo
</label>
