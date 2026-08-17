<!DOCTYPE html>
<html lang="pt-BR">
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
            <div class="mx-auto flex max-w-6xl items-center justify-between">
                <a href="{{ isset($client) ? route('client.hub', $client) : route('home') }}" class="group flex items-center gap-2.5">
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
        <footer class="border-t border-ink/5 px-4 py-5 sm:px-6">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-2 text-center text-sm font-semibold text-ink/45">
                <span>{{ $brand['tagline'] ?? 'Feito para jogar junto.' }}</span>
                <a href="{{ route('legal.privacy') }}" class="text-ink/55 underline decoration-ink/20 underline-offset-2 hover:text-ink">
                    Privacidade
                </a>
            </div>
        </footer>
        @endif
    </div>
</body>
</html>
