import { createSounds, isMobileClient } from './sounds';

function csrfHeaders(token) {
    return {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
    };
}

function bindSoundToggle(btn, sons, onEnable) {
    if (!btn) {
        return;
    }
    if (sons.bloqueado) {
        btn.classList.add('hidden');
        btn.setAttribute('aria-hidden', 'true');
        return;
    }
    const paint = () => {
        btn.textContent = sons.ativo ? '🔊' : '🔇';
        btn.setAttribute('aria-label', sons.ativo ? 'Desligar som' : 'Ligar som');
    };
    paint();
    btn.addEventListener('click', () => {
        sons.alternar();
        paint();
        if (sons.ativo && onEnable) {
            onEnable();
        }
    });
}

function syncLiveMusic(sons, status, { justFinished = false } = {}) {
    if (status === 'question' || status === 'reveal' || status === 'ranking') {
        // Só inicia se ainda não estiver tocando — o poll a cada 1s não pode
        // reiniciar/cortar a música junto com a contagem regressiva.
        if (!sons.tocando) {
            sons.iniciarMusica();
        }
        return;
    }
    if (status === 'finished') {
        sons.pararMusica();
        if (justFinished) {
            sons.fim();
        }
        return;
    }
    sons.pararMusica();
}

function formatTimer(ms) {
    return `${Math.ceil(Math.max(0, ms) / 1000)}s`;
}

function rankingKey(ranking) {
    return (ranking || []).map((r) => `${r.rank}:${r.name}:${r.score}`).join('|');
}

function playersKey(players) {
    return (players || []).map((p) => `${p.name}:${p.score ?? ''}`).join('|');
}

function span(className, text) {
    const el = document.createElement('span');
    el.className = className;
    el.textContent = text;
    return el;
}

function renderRanking(listEl, ranking) {
    if (!listEl) {
        return;
    }
    listEl.innerHTML = '';
    ranking.forEach((row) => {
        const li = document.createElement('li');
        li.className = 'live-rank-row';
        li.appendChild(span('live-rank-pos', `${row.rank}º`));
        li.appendChild(span('live-rank-name', row.name));
        li.appendChild(span('live-rank-score', `${row.score} pts`));
        listEl.appendChild(li);
    });
}

const PODIUM_MEDALS = { 1: '🥇', 2: '🥈', 3: '🥉' };

/**
 * Pódio final na ordem visual 3º · 1º · 2º; do 4º em diante vai para a lista.
 */
function renderPodium(podiumEl, listEl, ranking) {
    const rows = ranking || [];

    if (!podiumEl) {
        renderRanking(listEl, rows);
        return;
    }

    const top = rows.slice(0, 3);
    const rest = rows.slice(3);

    podiumEl.innerHTML = '';
    podiumEl.classList.toggle('hidden', top.length === 0);

    const byRank = (rank) => top.find((r) => r.rank === rank);
    const order = [3, 1, 2];
    const delays = { 3: 240, 1: 0, 2: 120 };

    order.forEach((rank) => {
        const row = byRank(rank);
        const step = document.createElement('div');
        step.className = `live-podium-step live-podium-step--${rank}`;
        step.style.setProperty('--delay', `${delays[rank]}ms`);

        if (!row) {
            step.style.visibility = 'hidden';
        }

        const medal = span('live-podium-medal', PODIUM_MEDALS[rank]);
        medal.setAttribute('aria-hidden', 'true');

        step.appendChild(medal);
        step.appendChild(span('live-podium-name', row?.name ?? ''));
        step.appendChild(span('live-podium-score', row ? `${row.score} pts` : ''));
        step.appendChild(span('live-podium-block', `${rank}º`));

        podiumEl.appendChild(step);
    });

    renderRanking(listEl, rest);
    listEl?.classList.toggle('hidden', rest.length === 0);
}

function renderOptions(container, question, { reveal = false, locked = false, onPick = null, myChoice = null } = {}) {
    container.innerHTML = '';
    const emojis = question.opcoesEmoji || [];
    const duo = (question.opcoes || []).length === 2;
    container.classList.toggle('quiz-options--duo', duo);
    container.classList.toggle('quiz-options--visual', duo || emojis.length > 0);

    (question.opcoes || []).forEach((texto, i) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'quiz-option';
        if (emojis.length) {
            btn.classList.add('quiz-option--illustrated');
            btn.innerHTML = `<span class="quiz-option__emoji">${emojis[i] || '❔'}</span><span class="quiz-option__text">${texto}</span>`;
        } else {
            btn.innerHTML = `<span class="quiz-option__letter">${String.fromCharCode(65 + i)}</span><span class="quiz-option__text">${texto}</span>`;
        }

        if (reveal && typeof question.correta === 'number') {
            btn.disabled = true;
            if (i === question.correta) {
                btn.classList.add('quiz-option--correct');
            } else if (myChoice === i) {
                btn.classList.add('quiz-option--wrong');
            } else {
                btn.classList.add('quiz-option--faded');
            }
        } else if (locked) {
            btn.disabled = true;
            if (myChoice === i) {
                btn.classList.add('quiz-option--correct');
            } else {
                btn.classList.add('quiz-option--faded');
            }
        } else if (onPick) {
            btn.addEventListener('click', () => onPick(i));
        } else {
            btn.disabled = true;
        }

        container.appendChild(btn);
    });
}

function initLiveHost(root) {
    const sons = createSounds({ preset: 'live', nivel: root.dataset.nivel || 'crianca' });
    const el = {
        lobby: root.querySelector('[data-live-lobby]'),
        play: root.querySelector('[data-live-play]'),
        ranking: root.querySelector('[data-live-ranking]'),
        count: root.querySelector('[data-live-count]'),
        players: root.querySelector('[data-live-players]'),
        empty: root.querySelector('[data-live-lobby-empty]'),
        start: root.querySelector('[data-live-start]'),
        advance: root.querySelector('[data-live-advance]'),
        qnum: root.querySelector('[data-live-qnum]'),
        qtotal: root.querySelector('[data-live-qtotal]'),
        timer: root.querySelector('[data-live-timer]'),
        timerBar: root.querySelector('[data-live-timer-bar]'),
        answers: root.querySelector('[data-live-answers]'),
        category: root.querySelector('[data-live-category]'),
        emoji: root.querySelector('[data-live-emoji]'),
        question: root.querySelector('[data-live-question]'),
        options: root.querySelector('[data-live-options]'),
        revealBox: root.querySelector('[data-live-reveal-box]'),
        correctText: root.querySelector('[data-live-correct-text]'),
        rankingTitle: root.querySelector('[data-live-ranking-title]'),
        rankingList: root.querySelector('[data-live-ranking-list]'),
        podium: root.querySelector('[data-live-podium]'),
        restLabel: root.querySelector('[data-live-rest-label]'),
        rankingNext: root.querySelector('[data-live-ranking-next]'),
        rankingCountdown: root.querySelector('[data-live-ranking-countdown]'),
        rankingSecs: root.querySelector('[data-live-ranking-secs]'),
        rankingBar: root.querySelector('[data-live-ranking-bar]'),
        soundToggle: root.querySelector('[data-live-sound]'),
    };

    let lastStatus = '';
    let lastIndex = -1;
    let lastPlayersKey = '';
    let lastRankingKey = '';
    const totalSeconds = 20;

    bindSoundToggle(el.soundToggle, sons, () => {
        if (lastStatus === 'question' || lastStatus === 'reveal' || lastStatus === 'ranking') {
            sons.iniciarMusica();
        }
    });
    root.addEventListener('pointerdown', () => sons.unlock(), { once: true });

    async function post(url) {
        const res = await fetch(url, {
            method: 'POST',
            headers: csrfHeaders(root.dataset.csrf),
            credentials: 'same-origin',
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            const firstError = data?.errors ? Object.values(data.errors).flat()[0] : null;
            alert(firstError || data?.message || 'Não foi possível continuar.');
            return null;
        }
        return res.json();
    }

    function paint(state) {
        const justFinished = state.status === 'finished' && lastStatus !== 'finished';
        syncLiveMusic(sons, state.status, { justFinished });
        root.classList.toggle('live-host--playing', state.status !== 'lobby' && state.status !== 'finished');

        if (state.status === 'lobby') {
            el.lobby.classList.remove('hidden');
            el.play.classList.add('hidden');
            el.ranking.classList.add('hidden');
            el.count.textContent = String(state.players_count);
            const key = playersKey(state.players);
            if (key !== lastPlayersKey) {
                lastPlayersKey = key;
                el.players.innerHTML = '';
                state.players.forEach((p) => {
                    const li = document.createElement('li');
                    li.className = 'live-player-chip';
                    li.textContent = p.name;
                    el.players.appendChild(li);
                });
            }
            el.empty.classList.toggle('hidden', state.players_count > 0);
            lastStatus = 'lobby';
            return;
        }

        if (state.status === 'finished' || state.status === 'ranking') {
            el.lobby.classList.add('hidden');
            el.play.classList.add('hidden');
            el.ranking.classList.remove('hidden');

            const finished = state.status === 'finished';

            if (finished) {
                el.rankingTitle.textContent = 'Pódio final';
                el.rankingNext?.classList.add('hidden');
                el.rankingCountdown?.classList.add('hidden');
            } else {
                el.rankingTitle.textContent = `Ranking parcial · Pergunta ${state.current_index + 1}/${state.total}`;
                el.rankingNext?.classList.remove('hidden');
                el.rankingCountdown?.classList.remove('hidden');

                const rankingRem = state.ranking_remaining_ms || 0;
                const rankingTotal = (state.ranking_seconds || 7) * 1000;
                const rankingSecs = Math.max(0, Math.ceil(rankingRem / 1000));
                if (el.rankingSecs) {
                    el.rankingSecs.textContent = String(rankingSecs);
                }
                if (el.rankingBar) {
                    el.rankingBar.style.width = `${Math.max(0, (rankingRem / rankingTotal) * 100)}%`;
                }
                if (el.rankingNext) {
                    el.rankingNext.textContent = 'Próxima pergunta';
                }
            }

            const key = rankingKey(state.ranking);
            if (key !== lastRankingKey || lastStatus !== state.status) {
                lastRankingKey = key;
                if (finished) {
                    renderPodium(el.podium, el.rankingList, state.ranking || []);
                    el.restLabel?.classList.toggle('hidden', (state.ranking || []).length <= 3);
                } else {
                    el.podium?.classList.add('hidden');
                    el.restLabel?.classList.add('hidden');
                    el.rankingList?.classList.remove('hidden');
                    renderRanking(el.rankingList, state.ranking || []);
                }
            }

            lastStatus = state.status;
            return;
        }

        el.lobby.classList.add('hidden');
        el.ranking.classList.add('hidden');
        el.play.classList.remove('hidden');

        const q = state.question;
        if (!q) {
            return;
        }

        sons.selecionarMusica(q.index);

        const reveal = state.status === 'reveal';
        const changed = state.status !== lastStatus || q.index !== lastIndex;

        if (changed && reveal && lastStatus === 'question') {
            sons.revelar();
        }

        el.qnum.textContent = String(q.index + 1);
        el.qtotal.textContent = String(q.total);
        el.answers.textContent = String(state.answers_count || 0);
        el.category.textContent = q.categoria || '';
        el.emoji.textContent = q.emoji || '';
        el.question.textContent = q.pergunta || '';

        if (changed) {
            renderOptions(el.options, q, { reveal, locked: true });
        }

        if (reveal) {
            el.revealBox.classList.remove('hidden');
            const correta = typeof q.correta === 'number' ? q.opcoes[q.correta] : '';
            const revealRem = state.reveal_remaining_ms || 0;
            const revealSecs = Math.max(1, Math.ceil(revealRem / 1000));
            el.correctText.textContent = correta
                ? `Resposta: ${correta} · Ranking em ${revealSecs}s`
                : `Ranking em ${revealSecs}s`;
            el.advance.classList.remove('hidden');
            el.advance.textContent = q.index + 1 >= q.total ? 'Ir ao ranking final agora' : 'Ir ao ranking agora';
            el.timer.textContent = `${revealSecs}s`;
            const revealTotal = (state.reveal_seconds || 4) * 1000;
            el.timerBar.style.width = `${(revealRem / revealTotal) * 100}%`;
        } else {
            el.revealBox.classList.add('hidden');
            el.advance.classList.remove('hidden');
            el.advance.textContent = 'Encerrar tempo / revelar';
            const rem = state.remaining_ms || 0;
            el.timer.textContent = formatTimer(rem);
            el.timerBar.style.width = `${(rem / (totalSeconds * 1000)) * 100}%`;
        }

        lastStatus = state.status;
        lastIndex = q.index;
    }

    async function poll() {
        try {
            const res = await fetch(root.dataset.stateUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (res.ok) {
                paint(await res.json());
            }
        } catch (e) {
            /* ignore */
        }
    }

    el.start?.addEventListener('click', async () => {
        sons.unlock();
        const state = await post(root.dataset.startUrl);
        if (state) {
            paint(state);
            sons.iniciarMusica();
        }
    });

    el.advance?.addEventListener('click', async () => {
        sons.unlock();
        const state = await post(root.dataset.advanceUrl);
        if (state) {
            paint(state);
        }
    });

    el.rankingNext?.addEventListener('click', async () => {
        sons.unlock();
        const state = await post(root.dataset.advanceUrl);
        if (state) {
            paint(state);
        }
    });

    poll();
    setInterval(poll, 1000);
}

function initLivePlayer(root) {
    // No celular o áudio fica sempre desligado — só o apresentador (TV) toca música.
    const muteMobile = isMobileClient();
    const sons = createSounds({
        preset: 'live',
        nivel: root.dataset.nivel || 'crianca',
        forcedOff: muteMobile,
    });
    const el = {
        wait: root.querySelector('[data-live-wait]'),
        qwrap: root.querySelector('[data-live-question-wrap]'),
        ranking: root.querySelector('[data-live-ranking]'),
        myscore: root.querySelector('[data-live-myscore]'),
        qnum: root.querySelector('[data-live-qnum]'),
        qtotal: root.querySelector('[data-live-qtotal]'),
        timer: root.querySelector('[data-live-timer]'),
        timerBar: root.querySelector('[data-live-timer-bar]'),
        category: root.querySelector('[data-live-category]'),
        emoji: root.querySelector('[data-live-emoji]'),
        question: root.querySelector('[data-live-question]'),
        options: root.querySelector('[data-live-options]'),
        feedback: root.querySelector('[data-live-feedback]'),
        rankingTitle: root.querySelector('[data-live-ranking-title]'),
        rankingList: root.querySelector('[data-live-ranking-list]'),
        podium: root.querySelector('[data-live-podium]'),
        restLabel: root.querySelector('[data-live-rest-label]'),
        backHub: root.querySelector('[data-live-back-hub]'),
        soundToggle: root.querySelector('[data-live-sound]'),
        musicHint: root.querySelector('[data-live-music-hint]'),
    };

    if (muteMobile && el.musicHint) {
        el.musicHint.classList.add('hidden');
    }

    let lastStatus = '';
    let lastIndex = -1;
    let lastAnswered = null;
    let lastRankingKey = '';
    let answering = false;
    const totalSeconds = 20;

    bindSoundToggle(el.soundToggle, sons, () => {
        if (lastStatus === 'question' || lastStatus === 'reveal' || lastStatus === 'ranking') {
            sons.iniciarMusica();
        }
    });
    root.addEventListener('pointerdown', () => {
        sons.unlock();
        if (lastStatus === 'question' || lastStatus === 'reveal' || lastStatus === 'ranking') {
            sons.iniciarMusica();
        }
    }, { once: true });

    async function sendAnswer(choice) {
        if (answering) {
            return;
        }
        answering = true;
        try {
            const res = await fetch(root.dataset.answerUrl, {
                method: 'POST',
                headers: csrfHeaders(root.dataset.csrf),
                credentials: 'same-origin',
                body: JSON.stringify({ choice }),
            });
            const data = await res.json();
            if (data.state) {
                paint(data.state);
            }
        } catch (e) {
            answering = false;
        }
    }

    function paint(state) {
        if (state.me) {
            el.myscore.textContent = String(state.me.score || 0);
        }

        const justFinished = state.status === 'finished' && lastStatus !== 'finished';
        syncLiveMusic(sons, state.status, { justFinished });

        if (state.status === 'lobby') {
            el.wait.classList.remove('hidden');
            el.qwrap.classList.add('hidden');
            el.ranking.classList.add('hidden');
            lastStatus = 'lobby';
            return;
        }

        if (state.status === 'finished') {
            el.wait.classList.add('hidden');
            el.qwrap.classList.add('hidden');
            el.ranking.classList.remove('hidden');
            el.rankingTitle.textContent = 'Pódio final';
            el.backHub?.classList.remove('hidden');
            const key = rankingKey(state.ranking);
            if (key !== lastRankingKey || lastStatus !== 'finished') {
                lastRankingKey = key;
                renderPodium(el.podium, el.rankingList, state.ranking || []);
                el.restLabel?.classList.toggle('hidden', (state.ranking || []).length <= 3);
            }
            lastStatus = 'finished';
            return;
        }

        if (state.status === 'ranking') {
            el.wait.classList.add('hidden');
            el.qwrap.classList.add('hidden');
            el.ranking.classList.remove('hidden');
            el.rankingTitle.textContent = 'Ranking parcial';
            el.backHub?.classList.add('hidden');
            const key = rankingKey(state.ranking);
            if (key !== lastRankingKey || lastStatus !== 'ranking') {
                lastRankingKey = key;
                el.podium?.classList.add('hidden');
                el.restLabel?.classList.add('hidden');
                el.rankingList?.classList.remove('hidden');
                renderRanking(el.rankingList, state.ranking || []);
            }
            lastStatus = 'ranking';
            return;
        }

        el.wait.classList.add('hidden');
        el.qwrap.classList.remove('hidden');
        el.ranking.classList.add('hidden');

        const q = state.question;
        if (!q) {
            return;
        }

        sons.selecionarMusica(q.index);

        const changed = state.status !== lastStatus || q.index !== lastIndex;
        const answered = Boolean(state.me?.answered);
        const answeredChanged = answered !== lastAnswered;

        el.qnum.textContent = String(q.index + 1);
        el.qtotal.textContent = String(q.total);
        el.category.textContent = q.categoria || '';
        el.emoji.textContent = q.emoji || '';
        el.question.textContent = q.pergunta || '';

        if (state.status === 'question') {
            const rem = state.remaining_ms || 0;
            el.timer.textContent = formatTimer(rem);
            el.timerBar.style.width = `${(rem / (totalSeconds * 1000)) * 100}%`;

            if (changed) {
                answering = false;
                el.feedback.classList.add('hidden');
            }

            if (changed || answeredChanged) {
                renderOptions(el.options, q, {
                    reveal: false,
                    locked: answered,
                    myChoice: state.me?.my_choice,
                    onPick: answered ? null : (i) => sendAnswer(i),
                });
            }

            if (answered) {
                el.feedback.classList.remove('hidden');
                el.feedback.textContent = 'Resposta enviada! Aguarde...';
            }
        } else if (state.status === 'reveal') {
            if (changed && lastStatus === 'question') {
                sons.revelar();
                if (state.me?.answered && state.me.my_correct) {
                    setTimeout(() => sons.acerto(), 450);
                } else {
                    setTimeout(() => sons.erro(), 450);
                }
            }

            if (changed) {
                renderOptions(el.options, q, {
                    reveal: true,
                    locked: true,
                    myChoice: state.me?.my_choice,
                });
            }
            el.feedback.classList.remove('hidden');
            if (state.me?.answered) {
                el.feedback.textContent = state.me.my_correct
                    ? `Acertou! +${state.me.my_points} pts`
                    : 'Errou desta vez.';
            } else {
                el.feedback.textContent = 'Tempo esgotado.';
            }
            el.timer.textContent = '0s';
            el.timerBar.style.width = '0%';
        }

        lastStatus = state.status;
        lastIndex = q.index;
        lastAnswered = answered;
    }

    async function poll() {
        try {
            const res = await fetch(root.dataset.stateUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (res.ok) {
                paint(await res.json());
            }
        } catch (e) {
            /* ignore */
        }
    }

    poll();
    setInterval(poll, 1000);
}

document.querySelectorAll('[data-live-host]').forEach(initLiveHost);
document.querySelectorAll('[data-live-player]').forEach(initLivePlayer);
