@extends('layouts.app')

@section('title', $brand['name'].' — Quiz interativo para famílias e escolas')

@php
    $company = config('brand.company');
    $whatsappUrl = 'https://wa.me/'.$company['whatsapp'].'?text='.rawurlencode($company['whatsapp_message']);
@endphp

@section('header_actions')
    <a href="#modos" class="hidden text-sm font-bold text-white/75 hover:text-white sm:inline">Modos</a>
    <a href="#instituicoes" class="hidden text-sm font-bold text-white/75 hover:text-white sm:inline sm:ml-4">Instituições</a>
    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="corp-btn corp-btn--light corp-btn--sm sm:ml-4">
        Fale conosco
    </a>
@endsection

@section('content')
{{-- Hero --}}
<section class="corp-hero">
    <div class="mx-auto grid w-full max-w-6xl items-center gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-2 lg:gap-14 lg:px-8 lg:py-20">
        <div class="hero-intro">
            <span class="corp-kicker">
                <span aria-hidden="true">🎉</span> Plataforma de quiz interativo
            </span>
            <h1 class="mt-5 font-display text-4xl leading-[1.08] tracking-tight text-ink sm:text-5xl">
                Aprender jogando,
                <span class="corp-highlight">todo mundo junto.</span>
            </h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-ink/65 sm:text-lg">
                Quizzes para famílias, salas de aula e eventos: jogue sozinho, desafie amigos no
                WhatsApp ou projete uma partida ao vivo com PIN para toda a turma.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="#modos" class="corp-btn corp-btn--primary">Começar a jogar</a>
                <a href="#instituicoes" class="corp-btn corp-btn--ghost">Para instituições de ensino</a>
            </div>
            <p class="mt-8 text-sm font-semibold text-ink/50">
                Um produto
                <a href="{{ $company['url'] }}" target="_blank" rel="noopener" class="font-extrabold text-brand-deep hover:underline">{{ $company['domain'] }}</a>
            </p>
        </div>

        <div class="corp-hero__visual" aria-hidden="true">
            <div class="corp-mock">
                <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wide text-ink/45">
                    <span>Pergunta 3 de 10</span>
                    <span class="corp-mock__timer normal-case">⏱ 12s</span>
                </div>
                <div class="corp-mock__progress"><span style="width: 30%"></span></div>
                <p class="mt-5 font-display text-xl leading-snug text-ink sm:text-2xl">
                    Qual é o maior planeta do Sistema Solar?
                </p>
                <div class="mt-5 grid grid-cols-2 gap-2.5">
                    <span class="corp-answer corp-answer--red">▲ Marte</span>
                    <span class="corp-answer corp-answer--blue">◆ Júpiter</span>
                    <span class="corp-answer corp-answer--yellow">● Terra</span>
                    <span class="corp-answer corp-answer--green">■ Saturno</span>
                </div>
            </div>
            <span class="corp-float corp-float--a">🏆 +120 pts</span>
            <span class="corp-float corp-float--b">👨‍👩‍👧 4 jogando</span>
            <span class="corp-float corp-float--c">PIN 4821</span>
        </div>
    </div>
</section>

{{-- Destaques --}}
<section class="border-y border-ink/5 bg-white">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-4 py-8 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div class="corp-stat">
            <strong>3</strong>
            <span>modos de jogo</span>
        </div>
        <div class="corp-stat">
            <strong>{{ $questionCount > 0 ? '+'.number_format($questionCount, 0, ',', '.') : 'Centenas de' }}</strong>
            <span>perguntas</span>
        </div>
        <div class="corp-stat">
            <strong>3 níveis</strong>
            <span>criança, adolescente e adulto</span>
        </div>
        <div class="corp-stat">
            <strong>0</strong>
            <span>instalação — roda no navegador</span>
        </div>
    </div>
</section>

{{-- Modos --}}
<section id="modos" class="scroll-mt-6 px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
    <div class="mx-auto w-full max-w-6xl">
        <div class="corp-section-head">
            <span class="corp-eyebrow">Escolha o modo</span>
            <h2 class="corp-section-title">Um quiz para cada momento</h2>
            <p class="corp-section-sub">Do jogo rápido no sofá à competição com a turma inteira no telão.</p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            <a href="{{ route('quiz.levels') }}" class="mode-card mode-card--blue group" style="--delay: 0ms">
                <span class="mode-card__shape" aria-hidden="true">◆</span>
                <div class="flex items-start justify-between gap-3">
                    <span class="mode-card__icon" aria-hidden="true">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                            <path d="M12 17h.01" />
                        </svg>
                    </span>
                    <span class="mode-card__tag">Modo solo</span>
                </div>
                <h3 class="mode-card__title">Quiz</h3>
                <p class="mode-card__text">Escolha o nível e jogue sozinho ou revezando.</p>
                <span class="mode-card__cta">Jogar Quiz →</span>
            </a>

            <a href="{{ route('x1.hub') }}" class="mode-card mode-card--red group" style="--delay: 60ms">
                <span class="mode-card__shape" aria-hidden="true">▲</span>
                <div class="flex items-start justify-between gap-3">
                    <span class="mode-card__icon" aria-hidden="true">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5" />
                            <line x1="13" x2="19" y1="19" y2="13" />
                            <line x1="16" x2="20" y1="16" y2="20" />
                            <line x1="19" x2="21" y1="21" y2="19" />
                            <polyline points="14.5 6.5 18 3 21 3 21 6 17.5 9.5" />
                            <line x1="5" x2="9" y1="14" y2="18" />
                            <line x1="7" x2="4" y1="17" y2="20" />
                            <line x1="3" x2="5" y1="19" y2="21" />
                        </svg>
                    </span>
                    <span class="mode-card__tag">Desafio</span>
                </div>
                <h3 class="mode-card__title">X1</h3>
                <p class="mode-card__text">Jogue, desafie no WhatsApp e veja quem marca mais pontos.</p>
                <span class="mode-card__cta">Modo X1 →</span>
            </a>

            <a href="{{ route('live.hub') }}" class="mode-card mode-card--green group" style="--delay: 120ms">
                <span class="mode-card__shape" aria-hidden="true">■</span>
                <div class="flex items-start justify-between gap-3">
                    <span class="mode-card__icon" aria-hidden="true">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="2" fill="currentColor" />
                            <path d="M16.24 7.76a6 6 0 0 1 0 8.49" />
                            <path d="M7.76 16.24a6 6 0 0 1 0-8.49" />
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14" />
                            <path d="M4.93 19.07a10 10 0 0 1 0-14.14" />
                        </svg>
                    </span>
                    <span class="mode-card__tag"><span class="mode-card__live" aria-hidden="true"></span>Multiplayer</span>
                </div>
                <h3 class="mode-card__title">Ao Vivo</h3>
                <p class="mode-card__text">Crie um PIN e compita em tempo real.</p>
                <span class="mode-card__cta">Ir para Ao Vivo →</span>
            </a>
        </div>

        <x-adsense :slot="config('ads.slots.home')" class="mt-10" />
    </div>
</section>

{{-- Benefícios --}}
<section class="bg-white px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
    <div class="mx-auto w-full max-w-6xl">
        <div class="corp-section-head">
            <span class="corp-eyebrow">Por que usar</span>
            <h2 class="corp-section-title">Engajamento de verdade, sem complicação</h2>
            <p class="corp-section-sub">Pensado para famílias, professores e equipes que querem aprender se divertindo.</p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="corp-feature">
                <span class="corp-feature__icon corp-feature__icon--violet" aria-hidden="true">🎯</span>
                <h3>Perguntas por faixa etária</h3>
                <p>Níveis criança, adolescente e adulto, com categorias variadas de conhecimentos gerais.</p>
            </div>
            <div class="corp-feature">
                <span class="corp-feature__icon corp-feature__icon--blue text-[#1d4ed8]" aria-hidden="true">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="2" fill="currentColor" />
                        <path d="M16.24 7.76a6 6 0 0 1 0 8.49" />
                        <path d="M7.76 16.24a6 6 0 0 1 0-8.49" />
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14" />
                        <path d="M4.93 19.07a10 10 0 0 1 0-14.14" />
                    </svg>
                </span>
                <h3>Ao Vivo</h3>
                <p>O professor projeta a partida, os alunos entram com um PIN pelo celular e o ranking aparece na hora.</p>
            </div>
            <div class="corp-feature">
                <span class="corp-feature__icon corp-feature__icon--amber" aria-hidden="true">⚡</span>
                <h3>Sem cadastro para jogar</h3>
                <p>Funciona direto no navegador, no celular ou no computador, sem instalar nada.</p>
            </div>
            <div class="corp-feature">
                <span class="corp-feature__icon corp-feature__icon--green" aria-hidden="true">🏫</span>
                <h3>Quiz da sua instituição</h3>
                <p>Link próprio, logo e perguntas geradas por IA sobre o conteúdo que você ensina.</p>
            </div>
        </div>
    </div>
</section>

{{-- Como funciona --}}
<section class="px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
    <div class="mx-auto w-full max-w-6xl">
        <div class="corp-section-head">
            <span class="corp-eyebrow">Como funciona</span>
            <h2 class="corp-section-title">Em três passos a partida começa</h2>
        </div>

        <ol class="mt-10 grid gap-5 sm:grid-cols-3">
            <li class="corp-step">
                <span class="corp-step__num">1</span>
                <h3>Escolha o modo</h3>
                <p>Solo, X1 ou Ao Vivo — e o nível das perguntas.</p>
            </li>
            <li class="corp-step">
                <span class="corp-step__num">2</span>
                <h3>Chame a galera</h3>
                <p>Compartilhe o PIN ou o link do desafio pelo WhatsApp.</p>
            </li>
            <li class="corp-step">
                <span class="corp-step__num">3</span>
                <h3>Jogue e compare</h3>
                <p>Pontuação por acerto e velocidade, com ranking no final.</p>
            </li>
        </ol>
    </div>
</section>

{{-- CTA instituições --}}
<section id="instituicoes" class="scroll-mt-6 px-4 pb-16 sm:px-6 sm:pb-20 lg:px-8">
    <div class="corp-cta mx-auto w-full max-w-6xl">
        <div class="relative grid items-center gap-8 lg:grid-cols-[1fr_auto]">
            <div>
                <span class="corp-eyebrow corp-eyebrow--on-dark">Para escolas, faculdades e cursos</span>
                <h2 class="mt-3 font-display text-3xl leading-tight text-white sm:text-4xl">
                    Leve este quiz para a sua Instituição de Ensino
                </h2>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-white/75">
                    Criamos um quiz personalizado com a marca da sua instituição, link exclusivo e perguntas
                    sobre as disciplinas que você escolher — pronto para usar em sala, em eventos ou no vestibular.
                </p>
                <ul class="mt-5 grid gap-2 text-sm font-semibold text-white/85 sm:grid-cols-2">
                    <li>✔ Logo e link próprios</li>
                    <li>✔ Até 100 perguntas por IA</li>
                    <li>✔ Modos Solo, X1 e Ao Vivo</li>
                    <li>✔ Suporte da equipe ti3</li>
                </ul>
            </div>
            <div class="flex flex-col items-start gap-3 lg:items-end">
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="corp-btn corp-btn--whatsapp lg:whitespace-nowrap">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.05 21.5h-.01a9.4 9.4 0 0 1-4.8-1.31l-.34-.2-3.57.93.95-3.48-.22-.36a9.4 9.4 0 0 1-1.44-5.02c0-5.2 4.23-9.43 9.44-9.43 2.52 0 4.89.98 6.67 2.77a9.37 9.37 0 0 1 2.76 6.67c0 5.2-4.23 9.43-9.44 9.43m8.03-17.46A11.27 11.27 0 0 0 12.05.72C5.8.72.7 5.8.7 12.06c0 2 .52 3.95 1.52 5.67L.6 23.64l6.05-1.59a11.33 11.33 0 0 0 5.4 1.38h.01c6.25 0 11.34-5.09 11.35-11.35 0-3.03-1.18-5.88-3.33-8.03"/>
                    </svg>
                    Conversar com {{ $company['whatsapp_display'] }} no WhatsApp
                </a>
                <p class="text-xs font-semibold text-white/55">
                    Atendimento
                    <a href="{{ $company['url'] }}" target="_blank" rel="noopener" class="text-white/80 underline decoration-white/30 underline-offset-2 hover:text-white">{{ $company['domain'] }}</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
