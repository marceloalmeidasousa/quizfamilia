<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Quiz em Família — perguntas em três níveis: criança, adolescente e adulto.">
    <title>@yield('title', 'Quiz em Família')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-ink antialiased">
    <div class="relative min-h-screen overflow-hidden bg-scene">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="blob blob-a"></div>
            <div class="blob blob-b"></div>
            <div class="blob blob-c"></div>
            <div class="pattern-dots"></div>
        </div>

        <div class="relative z-10 flex min-h-screen flex-col">
            <header class="px-5 pt-6 sm:px-8 sm:pt-8">
                <div class="mx-auto flex max-w-6xl items-center justify-between">
                    <a href="{{ route('home') }}" class="group inline-flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-ink text-xl text-white shadow-lift transition duration-300 group-hover:rotate-6 group-hover:scale-105 sm:h-12 sm:w-12">
                            ?
                        </span>
                        <span class="font-display text-2xl tracking-tight text-ink sm:text-3xl">
                            Quiz em <span class="text-coral">Família</span>
                        </span>
                    </a>
                </div>
            </header>

            <main class="flex flex-1 flex-col">
                @yield('content')
            </main>

            <footer class="px-5 py-6 text-center text-sm text-ink/55 sm:px-8">
                Feito para jogar junto — criança (3–6), adolescente (7–14) e adulto (15+).
            </footer>
        </div>
    </div>
</body>
</html>
