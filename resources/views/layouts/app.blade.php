<!DOCTYPE html>
<html lang="pt-BR" @if (isset($client) && request()->routeIs('client.*')) style="{{ $client->paletteStyle() }}" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $brand['description'] ?? 'Quiz' }}">
    <title>@yield('title', $brand['name'] ?? 'Quiz')</title>

    @if (app()->environment('production'))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-E7MZ62DHNQ"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-E7MZ62DHNQ');
    </script>
    @endif

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2230318270974880"
         crossorigin="anonymous"></script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased @yield('body_class')">
    <div class="relative flex min-h-screen flex-col @yield('shell_class') @yield('shell_inner_class')">
        <nav class="bg-brand-deep px-4 py-3.5 sm:px-6 @yield('header_class')">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-3">
                <a href="{{ isset($client) ? route('client.hub', $client) : route('home') }}" class="group flex min-w-0 items-center gap-2.5">
                    @if (isset($client) && $client->logoUrl())
                        <img src="{{ $client->logoUrl() }}" alt="" class="h-8 w-auto max-w-[7rem] rounded object-contain bg-white/10 p-0.5">
                    @else
                        <span class="brand-mark" aria-hidden="true">?</span>
                    @endif
                    <span class="brand-title text-xl text-white sm:text-2xl">
                        @if (isset($client))
                            {{ $client->name }}
                        @else
                            {!! $brand['name_html'] ?? ($brand['name'] ?? 'Quiz') !!}
                        @endif
                    </span>
                </a>
                @hasSection('header_actions')
                    <div class="flex shrink-0 items-center gap-2">
                        @yield('header_actions')
                    </div>
                @endif
            </div>
        </nav>

        <main class="flex min-h-0 flex-1 flex-col">
            @yield('content')
        </main>

        @hasSection('hide_footer')
        @else
            @unless(View::hasSection('hide_ads'))
                <x-adsense :slot="config('ads.slots.footer')" class="border-t border-ink/5" />
            @endunless
        @php
            $company = config('brand.company');
            $companyWhatsappUrl = 'https://wa.me/'.$company['whatsapp'].'?text='.rawurlencode($company['whatsapp_message']);
        @endphp
        @if (isset($client) && request()->routeIs('client.*'))
            <footer class="border-t border-ink/5 px-4 py-5 sm:px-6">
                <div class="mx-auto flex max-w-6xl flex-col items-center gap-2 text-center text-sm font-semibold text-ink/45">
                    <p>
                        Um produto
                        <a href="{{ $company['url'] }}" target="_blank" rel="noopener" class="font-extrabold text-ink/60 hover:text-ink">{{ $company['domain'] }}</a>
                    </p>
                    <a href="{{ route('legal.privacy') }}" class="text-ink/55 underline decoration-ink/20 underline-offset-2 hover:text-ink">
                        Privacidade
                    </a>
                </div>
            </footer>
        @else
            <footer class="corp-footer">
                <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                    <div class="lg:col-span-2">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                            <span class="brand-mark" aria-hidden="true">?</span>
                            <span class="brand-title text-xl text-white">{!! $brand['name_html'] ?? ($brand['name'] ?? 'Quiz') !!}</span>
                        </a>
                        <p class="mt-3 max-w-sm text-sm leading-relaxed text-white/60">
                            {{ $brand['description'] ?? '' }}
                        </p>
                        <p class="mt-4 text-sm font-semibold text-white/60">
                            Um produto
                            <a href="{{ $company['url'] }}" target="_blank" rel="noopener" class="font-extrabold text-white hover:underline">{{ $company['domain'] }}</a>
                        </p>
                    </div>
                    <div>
                        <h2 class="corp-footer__title">Jogar</h2>
                        <ul class="corp-footer__links">
                            <li><a href="{{ route('quiz.levels') }}">Quiz solo</a></li>
                            <li><a href="{{ route('x1.hub') }}">Desafio X1</a></li>
                            <li><a href="{{ route('live.hub') }}">Ao Vivo com PIN</a></li>
                        </ul>
                    </div>
                    <div>
                        <h2 class="corp-footer__title">Institucional</h2>
                        <ul class="corp-footer__links">
                            <li><a href="{{ $companyWhatsappUrl }}" target="_blank" rel="noopener">Para instituições de ensino</a></li>
                            <li><a href="{{ $company['url'] }}" target="_blank" rel="noopener">{{ $company['name'] }}</a></li>
                            <li><a href="{{ route('legal.privacy') }}">Privacidade</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/10">
                    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs font-semibold text-white/45 sm:flex-row sm:px-6 lg:px-8">
                        <p>© {{ date('Y') }} {{ $brand['name'] ?? 'Quiz' }} · {{ $brand['tagline'] ?? '' }}</p>
                        <p>
                            Desenvolvido por
                            <a href="{{ $company['url'] }}" target="_blank" rel="noopener" class="text-white/70 hover:text-white">{{ $company['name'] }}</a>
                        </p>
                    </div>
                </div>
            </footer>
        @endif
        @endif
    </div>
</body>
</html>
