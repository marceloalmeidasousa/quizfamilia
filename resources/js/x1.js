import { createSounds, isMobileClient } from './sounds';

function initX1Hub(root) {
    let categoriesByLevel = {};
    try {
        categoriesByLevel = JSON.parse(root.dataset.categories || '{}');
    } catch (e) {
        categoriesByLevel = {};
    }

    const form = root.querySelector('[data-x1-form]');
    const nivelInput = root.querySelector('[data-x1-nivel]');
    const categoriaInput = root.querySelector('[data-x1-categoria]');
    const panel = root.querySelector('[data-x1-category-panel]');
    const list = root.querySelector('[data-x1-category-list]');
    const hint = root.querySelector('[data-x1-category-hint]');
    const submit = root.querySelector('[data-x1-submit]');
    const levelButtons = [...root.querySelectorAll('[data-x1-pick-level]')];

    function chip(label, categoriaValue, { primary = false } = {}) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = primary ? 'quiz-cat-chip quiz-cat-chip--primary' : 'quiz-cat-chip';
        btn.textContent = label;
        btn.addEventListener('click', () => {
            categoriaInput.value = categoriaValue;
            [...list.querySelectorAll('button')].forEach((b) => b.classList.remove('quiz-cat-chip--selected'));
            btn.classList.add('quiz-cat-chip--selected');
            submit.classList.remove('hidden');
        });
        return btn;
    }

    function renderCategories(nivel) {
        const cats = categoriesByLevel[nivel] || [];
        const levelTitle = levelButtons.find((b) => b.dataset.x1PickLevel === nivel)?.querySelector('h3')?.textContent || nivel;

        list.innerHTML = '';
        categoriaInput.value = '';
        submit.classList.add('hidden');

        const total = cats.reduce((s, c) => s + c.total, 0);
        list.appendChild(chip(`Todas (${total})`, '', { primary: true }));
        cats.forEach((cat) => {
            list.appendChild(chip(`${cat.nome} (${cat.total})`, cat.nome));
        });

        if (hint) {
            hint.textContent = `Nível ${levelTitle} · toque numa categoria e comece`;
        }

        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    levelButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            nivelInput.value = btn.dataset.x1PickLevel;
            levelButtons.forEach((b) => b.classList.toggle('level-card--selected', b === btn));
            renderCategories(nivelInput.value);
        });
    });

    form?.addEventListener('submit', (e) => {
        if (!nivelInput.value) {
            e.preventDefault();
            alert('Escolha o nível.');
        }
    });

    if (nivelInput.value) {
        const btn = levelButtons.find((b) => b.dataset.x1PickLevel === nivelInput.value);
        if (btn) {
            btn.classList.add('level-card--selected');
            renderCategories(nivelInput.value);
            if (categoriaInput.value !== undefined) {
                submit.classList.remove('hidden');
            }
        }
    }
}

function initX1Play(root) {
    let perguntas = [];
    try {
        perguntas = JSON.parse(root.dataset.perguntas || '[]');
    } catch (e) {
        perguntas = [];
    }

    if (!perguntas.length) {
        return;
    }

    const nivel = root.dataset.nivelSlug || 'adulto';
    const muteMobile = isMobileClient();
    const sons = createSounds({ preset: 'quiz', nivel, forcedOff: muteMobile });

    const el = {
        current: root.querySelector('[data-x1-current]'),
        total: root.querySelector('[data-x1-total]'),
        score: root.querySelector('[data-x1-score]'),
        progress: root.querySelector('[data-x1-progress]'),
        visual: root.querySelector('[data-x1-visual]'),
        category: root.querySelector('[data-x1-category]'),
        question: root.querySelector('[data-x1-question]'),
        options: root.querySelector('[data-x1-options]'),
        reveal: root.querySelector('[data-x1-reveal]'),
        revealLabel: root.querySelector('[data-x1-reveal-label]'),
        revealAnswer: root.querySelector('[data-x1-reveal-answer]'),
        revealHint: root.querySelector('[data-x1-reveal-hint]'),
        next: root.querySelector('[data-x1-next]'),
        splat: root.querySelector('[data-x1-splat]'),
        soundToggle: root.querySelector('[data-x1-sound]'),
        sending: root.querySelector('[data-x1-sending]'),
    };

    let indice = 0;
    let acertos = 0;
    let respondida = false;
    let enviando = false;

    if (el.total) {
        el.total.textContent = String(perguntas.length);
    }

    if (muteMobile && el.soundToggle) {
        el.soundToggle.classList.add('hidden');
    }

    function renderSoundToggle() {
        if (!el.soundToggle || muteMobile) {
            return;
        }
        el.soundToggle.textContent = sons.ativo ? '🔊' : '🔇';
    }

    function renderVisual(pergunta) {
        el.visual.innerHTML = '';
        el.visual.classList.remove('quiz-visual--shake');
        if (pergunta.emoji && nivel === 'crianca') {
            const span = document.createElement('span');
            span.className = 'quiz-visual__emoji';
            span.textContent = pergunta.emoji;
            el.visual.appendChild(span);
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
        const pergunta = perguntas[indice];
        sons.selecionarMusica(indice);

        el.current.textContent = String(indice + 1);
        el.category.textContent = pergunta.categoria || 'Pergunta';
        el.question.textContent = pergunta.pergunta;
        el.progress.style.width = `${(indice / perguntas.length) * 100}%`;

        renderVisual(pergunta);

        el.reveal.classList.add('hidden');
        el.reveal.classList.remove('quiz-reveal--hit', 'quiz-reveal--miss');
        el.next.classList.add('hidden');
        el.options.innerHTML = '';

        const duasOpcoes = pergunta.opcoes.length === 2;
        el.options.classList.toggle('quiz-options--visual', duasOpcoes);
        el.options.classList.toggle('quiz-options--duo', duasOpcoes);

        pergunta.opcoes.forEach((texto, i) => {
            const botao = document.createElement('button');
            botao.type = 'button';
            botao.className = 'quiz-option';
            botao.innerHTML =
                `<span class="quiz-option__letter">${String.fromCharCode(65 + i)}</span>` +
                `<span class="quiz-option__text">${texto}</span>`;
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
        const acertou = escolhido === correta;

        el.options.querySelectorAll('.quiz-option').forEach((botao, i) => {
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
            el.score.textContent = String(acertos);
            el.reveal.classList.add('quiz-reveal--hit');
            el.revealLabel.textContent = 'Acertou!';
            el.revealHint.textContent = 'Resposta correta nesta rodada.';
            animarSplat('🎉', 'quiz-splat--hit');
            sons.acerto();
        } else {
            el.reveal.classList.add('quiz-reveal--miss');
            el.revealLabel.textContent = 'Resposta incorreta!';
            el.revealHint.textContent = 'Confira a resposta certa abaixo.';
            el.visual.classList.add('quiz-visual--shake');
            animarSplat('❌', 'quiz-splat--miss');
            sons.erro();
        }

        el.revealAnswer.textContent = `Resposta: ${textoCorreto}`;
        el.reveal.classList.remove('hidden');
        el.next.textContent = indice + 1 === perguntas.length ? 'Ver resultado' : 'Próxima rodada';
        el.next.classList.remove('hidden');
    }

    async function finalizar() {
        if (enviando) {
            return;
        }
        enviando = true;
        sons.pararMusica();
        el.next?.classList.add('hidden');
        el.sending?.classList.remove('hidden');

        try {
            const res = await fetch(root.dataset.finishUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': root.dataset.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ score: acertos }),
            });
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Não foi possível salvar.');
            }
            window.location.href = data.redirect;
        } catch (e) {
            enviando = false;
            el.sending?.classList.add('hidden');
            el.next?.classList.remove('hidden');
            alert(e.message || 'Erro ao salvar pontuação.');
        }
    }

    function avancar() {
        indice += 1;
        if (indice >= perguntas.length) {
            finalizar();
            return;
        }
        renderRodada();
    }

    el.next?.addEventListener('click', avancar);
    el.soundToggle?.addEventListener('click', () => {
        sons.alternar();
        renderSoundToggle();
        if (sons.ativo) {
            sons.iniciarMusica();
        }
    });

    renderSoundToggle();
    sons.unlock();
    sons.iniciarMusica();
    renderRodada();
}

document.querySelectorAll('[data-x1-hub]').forEach(initX1Hub);
document.querySelectorAll('[data-x1-play]').forEach(initX1Play);
