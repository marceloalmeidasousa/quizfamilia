@php
    $stickers = \App\Support\LiveStickers::ALL;
    $selectedSticker = old('emoji', \App\Support\LiveStickers::DEFAULT);
@endphp
<fieldset class="{{ $stickerClass ?? 'sm:col-span-2' }}">
    <legend class="text-sm font-bold text-ink/70">Sua figurinha</legend>
    <p class="mt-1 text-sm text-body-subtle">Aparece na sala, no ranking e no pódio</p>
    <div class="mt-2 flex flex-wrap gap-2">
        @foreach ($stickers as $sticker)
            <label class="live-sticker">
                <input type="radio" name="emoji" value="{{ $sticker }}" class="sr-only" @checked($selectedSticker === $sticker) aria-label="Figurinha {{ $sticker }}">
                <span aria-hidden="true">{{ $sticker }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
