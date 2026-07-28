<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Quiz em Família — diversão para criança, adolescente e adulto jogarem juntos.">
    <title>@yield('title', 'Quiz em Família')</title>

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

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased @yield('body_class')">
    <div class="relative flex min-h-screen flex-col @yield('shell_class') @yield('shell_inner_class')">
        <nav class="bg-brand-deep px-4 py-3.5 sm:px-6 @yield('header_class')">
            <div class="mx-auto flex max-w-6xl items-center justify-between">
                <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                    <span class="brand-mark" aria-hidden="true">?</span>
                    <span class="brand-title text-xl text-white sm:text-2xl">
                        Quiz em <span class="text-brand-soft">Família</span>
                    </span>
                </a>
            </div>
        </nav>

        <main class="flex min-h-0 flex-1 flex-col">
            @yield('content')
        </main>

        @hasSection('hide_footer')
        @else
        <footer class="border-t border-ink/5 px-4 py-5 sm:px-6">
            <div class="mx-auto max-w-6xl text-center text-sm font-semibold text-ink/45">
                Feito para jogar junto.
            </div>
        </footer>
        @endif
    </div>
</body>
</html>
