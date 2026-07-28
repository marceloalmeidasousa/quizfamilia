import { createSounds } from './sounds';

function shuffle(array) {
    const copy = [...array];
    for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
}

/** Embaralha opções (e emojis) e atualiza o índice da correta. */
function withShuffledOptions(pergunta) {
    const opcoes = [...(pergunta.opcoes || [])];
    if (opcoes.length < 2) {
        return pergunta;
    }

    const emojis = Array.isArray(pergunta.opcoesEmoji) ? [...pergunta.opcoesEmoji] : null;
    const hasEmojis = emojis && emojis.length === opcoes.length;
    const corretaAtual = typeof pergunta.correta === 'number' ? pergunta.correta : 0;

    const indices = shuffle(opcoes.map((_, i) => i));
    const novasOpcoes = [];
    const novosEmojis = [];
    let novaCorreta = 0;

    indices.forEach((antigo, novoIndice) => {
        novasOpcoes.push(opcoes[antigo]);
        if (hasEmojis) {
            novosEmojis.push(emojis[antigo]);
        }
        if (antigo === corretaAtual) {
            novaCorreta = novoIndice;
        }
    });

    return {
        ...pergunta,
        opcoes: novasOpcoes,
        opcoesEmoji: hasEmojis ? novosEmojis : pergunta.opcoesEmoji,
        correta: novaCorreta,
    };
}

function initQuiz(root) {
    let perguntas = [];
    try {
        perguntas = JSON.parse(root.dataset.perguntas || '[]');
    } catch (e) {
        perguntas = [];
    }

    if (!perguntas.length) {
        return;
    }

    const infantil = root.dataset.nivelSlug === 'crianca';
    const rodadasPorPartida = Math.min(parseInt(root.dataset.rodadas, 10) || 10, perguntas.length);
    const sons = createSounds();

    const el = {
        start: root.querySelector('[data-quiz-start]'),
        play: root.querySelector('[data-quiz-play]'),
        result: root.querySelector('[data-quiz-result]'),
        begin: root.querySelector('[data-quiz-begin]'),
        current: root.querySelector('[data-quiz-current]'),
        score: root.querySelector('[data-quiz-score]'),
        streak: root.querySelector('[data-quiz-streak]'),
        progress: root.querySelector('[data-quiz-progress]'),
        visual: root.querySelector('[data-quiz-visual]'),
        category: root.querySelector('[data-quiz-category]'),
        question: root.querySelector('[data-quiz-question]'),
        options: root.querySelector('[data-quiz-options]'),
        reveal: root.querySelector('[data-quiz-reveal]'),
        revealLabel: root.querySelector('[data-quiz-reveal-label]'),
        revealAnswer: root.querySelector('[data-quiz-reveal-answer]'),
        revealHint: root.querySelector('[data-quiz-reveal-hint]'),
        next: root.querySelector('[data-quiz-next]'),
        splat: root.querySelector('[data-quiz-splat]'),
        finalScore: root.querySelector('[data-quiz-final-score]'),
        finalEmoji: root.querySelector('[data-quiz-final-emoji]'),
        message: root.querySelector('[data-quiz-message]'),
        restart: root.querySelector('[data-quiz-restart]'),
        soundToggle: root.querySelector('[data-quiz-sound]'),
    };

    let ordem = [];
    let indice = 0;
    let acertos = 0;
    let sequencia = 0;
    let respondida = false;

    function show(section) {
        [el.start, el.play, el.result].forEach((node) => node && node.classList.add('hidden'));
        section && section.classList.remove('hidden');
        root.classList.toggle('quiz--playing', section === el.play);
    }

    function atualizarSequencia() {
        if (!el.streak) {
            return;
        }
        el.streak.textContent = sequencia > 1 ? `🔥 ${sequencia} seguidas` : '';
    }

    function iniciar() {
        // Cada partida sorteia perguntas novas e embaralha as opções.
        ordem = shuffle(perguntas)
            .slice(0, rodadasPorPartida)
            .map(withShuffledOptions);
        indice = 0;
        acertos = 0;
        sequencia = 0;
        el.score.textContent = '0';
        atualizarSequencia();
        show(el.play);
        sons.iniciarMusica();
        renderRodada();
    }

    function renderVisual(pergunta) {
        el.visual.innerHTML = '';
        el.visual.classList.remove('quiz-visual--shake');

        if (pergunta.imagem) {
            const img = document.createElement('img');
            img.src = pergunta.imagem.startsWith('http') ? pergunta.imagem : `/${pergunta.imagem.replace(/^\//, '')}`;
            img.alt = '';
            el.visual.appendChild(img);
        } else if (pergunta.emoji) {
            const span = document.createElement('span');
            span.className = 'quiz-visual__emoji';
            span.textContent = pergunta.emoji;
            el.visual.appendChild(span);
        }

        el.visual.classList.toggle('hidden', !el.visual.childElementCount);
    }

    function renderRodada() {
        respondida = false;
        const pergunta = ordem[indice];

        el.current.textContent = String(indice + 1);
        el.category.textContent = pergunta.categoria || 'Pergunta';
        el.question.textContent = pergunta.pergunta;
        el.progress.style.width = `${(indice / ordem.length) * 100}%`;

        renderVisual(pergunta);

        el.reveal.classList.add('hidden');
        el.reveal.classList.remove('quiz-reveal--hit', 'quiz-reveal--miss');
        el.next.classList.add('hidden');
        el.options.innerHTML = '';

        const comEmoji = Array.isArray(pergunta.opcoesEmoji) && pergunta.opcoesEmoji.length > 0;
        const duasOpcoes = pergunta.opcoes.length === 2;
        el.options.classList.toggle('quiz-options--visual', comEmoji || duasOpcoes);
        el.options.classList.toggle('quiz-options--duo', duasOpcoes);

        pergunta.opcoes.forEach((texto, i) => {
            const botao = document.createElement('button');
            botao.type = 'button';
            botao.className = 'quiz-option';
            botao.style.setProperty('--delay', `${i * 70}ms`);

            if (comEmoji) {
                botao.classList.add('quiz-option--illustrated');
                botao.innerHTML =
                    `<span class="quiz-option__emoji">${pergunta.opcoesEmoji[i] || '❔'}</span>` +
                    `<span class="quiz-option__text">${texto}</span>`;
            } else {
                botao.innerHTML =
                    `<span class="quiz-option__letter">${String.fromCharCode(65 + i)}</span>` +
                    `<span class="quiz-option__text">${texto}</span>`;
            }

            botao.addEventListener('click', () => marcar(i, pergunta));
            el.options.appendChild(botao);
        });
    }

    function animarSplat(emoji, classe) {
        if (!el.splat) {
            return;
        }
        el.splat.textContent = emoji;
        el.splat.className = `quiz-splat ${classe}`;
        // reinicia a animação
        void el.splat.offsetWidth;
        el.splat.classList.add('quiz-splat--on');
        setTimeout(() => el.splat.classList.remove('quiz-splat--on'), 900);
    }

    function marcar(escolhido, pergunta) {
        if (respondida) {
            return;
        }
        respondida = true;

        const correta = pergunta.correta;
        const textoCorreto = pergunta.opcoes[correta];
        const emojiCorreto = Array.isArray(pergunta.opcoesEmoji) ? pergunta.opcoesEmoji[correta] : '';
        const acertou = escolhido === correta;

        const botoes = el.options.querySelectorAll('.quiz-option');
        botoes.forEach((botao, i) => {
            botao.disabled = true;
            if (i === correta) {
                botao.classList.add('quiz-option--correct');
            } else if (i === escolhido) {
                botao.classList.add('quiz-option--wrong');
            } else {
                botao.classList.add('quiz-option--faded');
            }
        });

        if (acertou) {
            acertos += 1;
            sequencia += 1;
            el.score.textContent = String(acertos);
            el.reveal.classList.add('quiz-reveal--hit');
            el.revealLabel.textContent = sequencia >= 3 ? `Acertou de novo! ${sequencia} seguidas 🔥` : 'Acertou!';
            el.revealHint.textContent = 'Resposta correta nesta rodada.';
            animarSplat(sequencia >= 3 ? '🌟' : '🎉', 'quiz-splat--hit');
            sons.acerto();
        } else {
            sequencia = 0;
            el.reveal.classList.add('quiz-reveal--miss');
            el.revealLabel.textContent = 'Resposta incorreta!';
            el.revealHint.textContent = 'Você errou — confira a resposta certa abaixo.';
            el.visual.classList.add('quiz-visual--shake');
            animarSplat('❌', 'quiz-splat--miss');
            sons.erro();
        }

        atualizarSequencia();
        el.revealAnswer.textContent = `${emojiCorreto ? emojiCorreto + ' ' : ''}Resposta: ${textoCorreto}`;
        el.reveal.classList.remove('hidden');

        el.next.textContent = indice + 1 === ordem.length ? 'Ver resultado final' : 'Próxima rodada';
        el.next.classList.remove('hidden');
    }

    function avancar() {
        indice += 1;
        if (indice >= ordem.length) {
            finalizar();
            return;
        }
        renderRodada();
    }

    function finalizar() {
        el.progress.style.width = '100%';
        el.finalScore.textContent = String(acertos);

        const pct = acertos / ordem.length;
        let msg = 'Errou bastante, mas o importante é a diversão!';
        let emoji = '😅';

        if (pct === 1) {
            msg = infantil ? 'Uau! Você acertou tudinho!' : 'Perfeito! Você acertou todas as rodadas.';
            emoji = '🏆';
        } else if (pct >= 0.7) {
            msg = 'Mandou bem! Poucos erros nesta partida.';
            emoji = '🌟';
        } else if (pct >= 0.4) {
            msg = 'Teve erro, mas também teve acerto. Joga de novo!';
            emoji = '😄';
        }

        if (el.finalEmoji) {
            el.finalEmoji.textContent = emoji;
        }
        el.message.textContent = msg;
        sons.pararMusica();
        show(el.result);
        sons.fim();
    }

    function renderSoundToggle() {
        if (!el.soundToggle) {
            return;
        }
        el.soundToggle.textContent = sons.ativo ? '🔊' : '🔇';
        el.soundToggle.setAttribute('aria-label', sons.ativo ? 'Desligar som' : 'Ligar som');
    }

    el.begin && el.begin.addEventListener('click', iniciar);
    el.next && el.next.addEventListener('click', avancar);
    el.restart && el.restart.addEventListener('click', iniciar);
    el.soundToggle && el.soundToggle.addEventListener('click', () => {
        sons.alternar();
        renderSoundToggle();
    });

    renderSoundToggle();
}

document.querySelectorAll('[data-quiz]').forEach(initQuiz);
