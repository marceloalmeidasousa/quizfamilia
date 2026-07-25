function shuffle(array) {
    const copy = [...array];
    for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
}

/**
 * Sons e música de fundo gerados com Web Audio (sem arquivos externos).
 */
function createSounds() {
    let ctx = null;
    let ligado = localStorage.getItem('quiz-som') !== 'off';
    let musicGain = null;
    let musicTimer = null;
    let nextNoteAt = 0;
    let musicPlaying = false;

    // Melodia alegre em C maior (~120 BPM), estilo festa infantil.
    const BEAT = 0.28;
    const LOOP = [
        523.25, 659.25, 783.99, 659.25,
        587.33, 698.46, 880.00, 698.46,
        659.25, 783.99, 987.77, 783.99,
        523.25, 659.25, 783.99, 1046.5,
    ];
    const BASS = [
        130.81, null, 130.81, null,
        146.83, null, 146.83, null,
        164.81, null, 164.81, null,
        130.81, null, 196.00, null,
    ];

    function ensureCtx() {
        if (!ctx) {
            ctx = new (window.AudioContext || window.webkitAudioContext)();
            musicGain = ctx.createGain();
            musicGain.gain.value = ligado ? 0.07 : 0;
            musicGain.connect(ctx.destination);
        }
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        return ctx;
    }

    function tocar(notas) {
        if (!ligado) {
            return;
        }
        try {
            ensureCtx();
            notas.forEach(({ freq, start, dur, tipo = 'sine', vol = 0.22 }) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = tipo;
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, ctx.currentTime + start);
                gain.gain.exponentialRampToValueAtTime(vol, ctx.currentTime + start + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + dur);
                osc.connect(gain).connect(ctx.destination);
                osc.start(ctx.currentTime + start);
                osc.stop(ctx.currentTime + start + dur + 0.02);
            });
        } catch (e) {
            /* navegador sem suporte */
        }
    }

    function scheduleNote(freq, when, dur, tipo, vol) {
        if (!freq) {
            return;
        }
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = tipo;
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, when);
        gain.gain.exponentialRampToValueAtTime(vol, when + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, when + dur);
        osc.connect(gain).connect(musicGain);
        osc.start(when);
        osc.stop(when + dur + 0.02);
    }

    function scheduleLoop() {
        if (!musicPlaying || !ctx) {
            return;
        }
        const horizon = ctx.currentTime + 1.2;
        while (nextNoteAt < horizon) {
            const absStep = Math.round(nextNoteAt / BEAT) % LOOP.length;
            scheduleNote(LOOP[absStep], nextNoteAt, BEAT * 0.85, 'triangle', 0.55);
            scheduleNote(BASS[absStep], nextNoteAt, BEAT * 0.9, 'sine', 0.35);
            if (absStep % 2 === 0) {
                scheduleNote(180, nextNoteAt, 0.06, 'square', 0.12);
            }
            nextNoteAt += BEAT;
        }
    }

    function iniciarMusica() {
        try {
            ensureCtx();
            if (!ligado) {
                musicGain.gain.setTargetAtTime(0, ctx.currentTime, 0.05);
                musicPlaying = false;
                return;
            }
            if (musicPlaying) {
                musicGain.gain.setTargetAtTime(0.07, ctx.currentTime, 0.15);
                return;
            }
            musicPlaying = true;
            nextNoteAt = ctx.currentTime + 0.05;
            musicGain.gain.setValueAtTime(0.0001, ctx.currentTime);
            musicGain.gain.exponentialRampToValueAtTime(0.07, ctx.currentTime + 0.6);
            scheduleLoop();
            clearInterval(musicTimer);
            musicTimer = setInterval(scheduleLoop, 400);
        } catch (e) {
            /* ignore */
        }
    }

    function pararMusica({ fade = true } = {}) {
        if (!ctx || !musicGain) {
            musicPlaying = false;
            clearInterval(musicTimer);
            musicTimer = null;
            return;
        }
        musicPlaying = false;
        clearInterval(musicTimer);
        musicTimer = null;
        const now = ctx.currentTime;
        if (fade) {
            musicGain.gain.cancelScheduledValues(now);
            musicGain.gain.setValueAtTime(Math.max(musicGain.gain.value, 0.0001), now);
            musicGain.gain.exponentialRampToValueAtTime(0.0001, now + 0.8);
        } else {
            musicGain.gain.setValueAtTime(0, now);
        }
    }

    return {
        acerto: () => tocar([
            { freq: 660, start: 0, dur: 0.12 },
            { freq: 880, start: 0.1, dur: 0.18 },
            { freq: 1180, start: 0.22, dur: 0.2 },
        ]),
        erro: () => tocar([
            { freq: 220, start: 0, dur: 0.22, tipo: 'square', vol: 0.16 },
            { freq: 140, start: 0.16, dur: 0.3, tipo: 'square', vol: 0.14 },
        ]),
        fim: () => tocar([
            { freq: 523, start: 0, dur: 0.16 },
            { freq: 659, start: 0.14, dur: 0.16 },
            { freq: 784, start: 0.28, dur: 0.16 },
            { freq: 1046, start: 0.42, dur: 0.3 },
        ]),
        iniciarMusica,
        pararMusica,
        alternar() {
            ligado = !ligado;
            localStorage.setItem('quiz-som', ligado ? 'on' : 'off');
            try {
                ensureCtx();
                musicGain.gain.cancelScheduledValues(ctx.currentTime);
                if (ligado && musicPlaying) {
                    musicGain.gain.setTargetAtTime(0.07, ctx.currentTime, 0.1);
                    scheduleLoop();
                    clearInterval(musicTimer);
                    musicTimer = setInterval(scheduleLoop, 400);
                } else {
                    musicGain.gain.setTargetAtTime(0, ctx.currentTime, 0.08);
                    clearInterval(musicTimer);
                    musicTimer = null;
                }
            } catch (e) {
                /* ignore */
            }
            return ligado;
        },
        get ativo() {
            return ligado;
        },
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
    }

    function atualizarSequencia() {
        if (!el.streak) {
            return;
        }
        el.streak.textContent = sequencia > 1 ? `🔥 ${sequencia} seguidas` : '';
    }

    function iniciar() {
        // Cada partida sorteia um conjunto novo de perguntas do baralho completo.
        ordem = shuffle(perguntas).slice(0, rodadasPorPartida);
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
            el.revealHint.textContent = 'Escapou da torta nesta rodada.';
            animarSplat(sequencia >= 3 ? '🌟' : '🎉', 'quiz-splat--hit');
            sons.acerto();
        } else {
            sequencia = 0;
            el.reveal.classList.add('quiz-reveal--miss');
            el.revealLabel.textContent = 'Torta na cara!';
            el.revealHint.textContent = 'Olha a resposta certa aí embaixo.';
            el.visual.classList.add('quiz-visual--shake');
            animarSplat('🥧', 'quiz-splat--miss');
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
        let msg = 'Levou várias tortas, mas o importante é a diversão!';
        let emoji = '🥧';

        if (pct === 1) {
            msg = infantil ? 'Uau! Você acertou tudinho!' : 'Zero tortas! Você acertou todas as rodadas.';
            emoji = '🏆';
        } else if (pct >= 0.7) {
            msg = 'Quase limpo! Poucas tortas na cara.';
            emoji = '🌟';
        } else if (pct >= 0.4) {
            msg = 'Teve torta, mas também teve acerto. Joga de novo!';
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
