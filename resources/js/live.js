function csrfHeaders(token) {
    return {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
    };
}

function formatTimer(ms) {
    return `${Math.ceil(Math.max(0, ms) / 1000)}s`;
}

function renderRanking(listEl, ranking) {
    if (!listEl) {
        return;
    }
    listEl.innerHTML = '';
    ranking.forEach((row) => {
        const li = document.createElement('li');
        li.className = 'live-rank-row';
        li.innerHTML =
            `<span class="live-rank-pos">${row.rank}º</span>` +
            `<span class="live-rank-name">${row.name}</span>` +
            `<span class="live-rank-score">${row.score} pts</span>`;
        listEl.appendChild(li);
    });
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
    };

    let lastStatus = '';
    let lastIndex = -1;
    const totalSeconds = 20;

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
        if (state.status === 'lobby') {
            el.lobby.classList.remove('hidden');
            el.play.classList.add('hidden');
            el.ranking.classList.add('hidden');
            el.count.textContent = String(state.players_count);
            el.players.innerHTML = '';
            state.players.forEach((p) => {
                const li = document.createElement('li');
                li.className = 'live-player-chip';
                li.textContent = p.name;
                el.players.appendChild(li);
            });
            el.empty.classList.toggle('hidden', state.players_count > 0);
            return;
        }

        if (state.status === 'finished') {
            el.lobby.classList.add('hidden');
            el.play.classList.add('hidden');
            el.ranking.classList.remove('hidden');
            el.rankingTitle.textContent = 'Ranking final';
            renderRanking(el.rankingList, state.ranking || []);
            return;
        }

        el.lobby.classList.add('hidden');
        el.ranking.classList.add('hidden');
        el.play.classList.remove('hidden');

        const q = state.question;
        if (!q) {
            return;
        }

        el.qnum.textContent = String(q.index + 1);
        el.qtotal.textContent = String(q.total);
        el.answers.textContent = String(state.answers_count || 0);
        el.category.textContent = q.categoria || '';
        el.emoji.textContent = q.emoji || '';
        el.question.textContent = q.pergunta || '';

        const reveal = state.status === 'reveal';
        const changed = state.status !== lastStatus || q.index !== lastIndex;

        if (changed) {
            renderOptions(el.options, q, { reveal, locked: true });
            lastStatus = state.status;
            lastIndex = q.index;
        } else if (reveal) {
            renderOptions(el.options, q, { reveal: true, locked: true });
        }

        if (reveal) {
            el.revealBox.classList.remove('hidden');
            const correta = typeof q.correta === 'number' ? q.opcoes[q.correta] : '';
            el.correctText.textContent = correta ? `Resposta: ${correta}` : '';
            el.advance.classList.remove('hidden');
            el.advance.textContent = q.index + 1 >= q.total ? 'Ver ranking final' : 'Próxima pergunta';
            el.timer.textContent = '0s';
            el.timerBar.style.width = '0%';

            // show partial ranking under reveal
            if (state.ranking?.length) {
                el.ranking.classList.remove('hidden');
                el.rankingTitle.textContent = 'Ranking parcial';
                renderRanking(el.rankingList, state.ranking);
            }
        } else {
            el.revealBox.classList.add('hidden');
            el.advance.classList.remove('hidden');
            el.advance.textContent = 'Encerrar tempo / revelar';
            el.ranking.classList.add('hidden');
            const rem = state.remaining_ms || 0;
            el.timer.textContent = formatTimer(rem);
            el.timerBar.style.width = `${(rem / (totalSeconds * 1000)) * 100}%`;
            renderOptions(el.options, q, { reveal: false, locked: true });
        }
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
        const state = await post(root.dataset.startUrl);
        if (state) {
            paint(state);
        }
    });

    el.advance?.addEventListener('click', async () => {
        const state = await post(root.dataset.advanceUrl);
        if (state) {
            paint(state);
        }
    });

    poll();
    setInterval(poll, 1000);
}

function initLivePlayer(root) {
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
    };

    let lastStatus = '';
    let lastIndex = -1;
    let answering = false;
    const totalSeconds = 20;

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
            el.rankingTitle.textContent = 'Ranking final';
            renderRanking(el.rankingList, state.ranking || []);
            return;
        }

        el.wait.classList.add('hidden');
        el.qwrap.classList.remove('hidden');

        const q = state.question;
        if (!q) {
            return;
        }

        const changed = state.status !== lastStatus || q.index !== lastIndex;
        el.qnum.textContent = String(q.index + 1);
        el.qtotal.textContent = String(q.total);
        el.category.textContent = q.categoria || '';
        el.emoji.textContent = q.emoji || '';
        el.question.textContent = q.pergunta || '';

        if (state.status === 'question') {
            el.ranking.classList.add('hidden');
            const rem = state.remaining_ms || 0;
            el.timer.textContent = formatTimer(rem);
            el.timerBar.style.width = `${(rem / (totalSeconds * 1000)) * 100}%`;

            const answered = state.me?.answered;
            if (changed) {
                answering = false;
                el.feedback.classList.add('hidden');
            }

            renderOptions(el.options, q, {
                reveal: false,
                locked: answered,
                myChoice: state.me?.my_choice,
                onPick: answered ? null : (i) => sendAnswer(i),
            });

            if (answered) {
                el.feedback.classList.remove('hidden');
                el.feedback.textContent = 'Resposta enviada! Aguarde...';
            }
        } else if (state.status === 'reveal') {
            renderOptions(el.options, q, {
                reveal: true,
                locked: true,
                myChoice: state.me?.my_choice,
            });
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

            if (state.ranking?.length) {
                el.ranking.classList.remove('hidden');
                el.rankingTitle.textContent = 'Ranking parcial';
                renderRanking(el.rankingList, state.ranking);
            }
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

    poll();
    setInterval(poll, 1000);
}

document.querySelectorAll('[data-live-host]').forEach(initLiveHost);
document.querySelectorAll('[data-live-player]').forEach(initLivePlayer);
