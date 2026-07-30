@props([
    'slot' => null,
    'format' => 'auto',
    'fullWidth' => true,
])

@php
    $enabled = config('ads.enabled')
        && filled(config('ads.client'))
        && filled($slot)
        && ! View::hasSection('hide_ads');
@endphp

@if ($enabled)
    <div {{ $attributes->class(['adsense-wrap mx-auto w-full max-w-5xl px-4 py-4 sm:px-6']) }}>
        <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-[0.16em] text-ink/35">Publicidade</p>
        <ins
            class="adsbygoogle"
            style="display:block"
            data-ad-client="{{ config('ads.client') }}"
            data-ad-slot="{{ $slot }}"
            data-ad-format="{{ $format }}"
            @if ($fullWidth) data-full-width-responsive="true" @endif
            @if (config('ads.test_mode')) data-adtest="on" @endif
        ></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
@endif
