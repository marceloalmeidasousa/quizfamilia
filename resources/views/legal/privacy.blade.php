@extends('layouts.app')

@section('title', 'Privacidade — ' . $brand['name'])
@section('hide_ads')
@endsection

@section('content')
<section class="px-4 py-10 sm:px-8 sm:py-12">
    <div class="mx-auto w-full max-w-3xl">
        <a href="{{ route('home') }}" class="back-link mb-6 inline-flex items-center">Voltar</a>

        <h1 class="font-display text-3xl text-ink sm:text-4xl">Política de Privacidade</h1>
        <p class="mt-2 text-sm font-semibold text-ink/50">Última atualização: julho de 2026</p>

        <div class="prose-like mt-8 space-y-5 text-base leading-relaxed text-ink/75">
            <p>
                O <strong>{{ $brand['name'] }}</strong> (“nós”, “site”) respeita a sua privacidade.
                Esta página explica, de forma simples, quais dados podem ser coletados e como são usados.
            </p>

            <h2 class="font-display text-xl text-ink">1. Dados que podemos coletar</h2>
            <ul class="list-disc space-y-2 ps-5">
                <li>Nome informado ao jogar (Ao Vivo / X1), apenas para o placar da partida.</li>
                <li>Dados técnicos de acesso (IP, navegador, páginas visitadas e horário), para estatísticas do site.</li>
                <li>Cookies necessários ao funcionamento (sessão, preferência de som) e, se ativos, cookies de publicidade.</li>
            </ul>

            <h2 class="font-display text-xl text-ink">2. Publicidade (Google AdSense)</h2>
            <p>
                Podemos exibir anúncios do Google AdSense. O Google pode usar cookies ou identificadores
                semelhantes para mostrar anúncios com base em visitas anteriores a este ou a outros sites.
                Saiba mais em
                <a class="underline" href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener noreferrer">
                    Como o Google usa dados de sites
                </a>
                e gerencie preferências em
                <a class="underline" href="https://adssettings.google.com/" target="_blank" rel="noopener noreferrer">
                    Configurações de anúncios
                </a>.
            </p>

            <h2 class="font-display text-xl text-ink">3. Uso dos dados</h2>
            <p>
                Usamos os dados para operar o jogo, melhorar a experiência, medir acessos e (quando habilitado)
                exibir publicidade. Não vendemos sua lista de contatos.
            </p>

            <h2 class="font-display text-xl text-ink">4. Domínios</h2>
            <p>
                Esta política se aplica aos sites do projeto, incluindo quizemfamilia.com.br, animaquiz.com.br
                e quizedu.com.br, quando apontarem para este aplicativo.
            </p>

            <h2 class="font-display text-xl text-ink">5. Contato</h2>
            <p>
                Dúvidas sobre privacidade: use o e-mail do responsável pelo site cadastrado no AdSense / domínio.
            </p>
        </div>
    </div>
</section>
@endsection
