function initQuizPicker(root) {
    let categoriesByLevel = {};
    try {
        categoriesByLevel = JSON.parse(root.dataset.categories || '{}');
    } catch (e) {
        categoriesByLevel = {};
    }

    const playUrl = (root.dataset.playUrl || '/jogo').replace(/\/$/, '');
    const panel = root.querySelector('[data-category-panel]');
    const list = root.querySelector('[data-category-list]');
    const hint = root.querySelector('[data-category-hint]');
    const levelButtons = [...root.querySelectorAll('[data-pick-level]')];

    let selectedLevel = null;

    function chip(label, href, { primary = false } = {}) {
        const a = document.createElement('a');
        a.href = href;
        a.className = primary ? 'quiz-cat-chip quiz-cat-chip--primary' : 'quiz-cat-chip';
        a.textContent = label;
        return a;
    }

    function renderCategories(nivel) {
        const cats = categoriesByLevel[nivel] || [];
        const levelTitle = levelButtons.find((b) => b.dataset.pickLevel === nivel)?.querySelector('h2')?.textContent || nivel;

        list.innerHTML = '';

        if (!cats.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-ink/60';
            empty.textContent = 'Nenhuma categoria encontrada. Rode o seed das perguntas no banco.';
            list.appendChild(empty);
        } else {
            const total = cats.reduce((s, c) => s + c.total, 0);
            list.appendChild(chip(`Todas (${total})`, `${playUrl}/${nivel}`, { primary: true }));
            cats.forEach((cat) => {
                const url = `${playUrl}/${nivel}?categoria=${encodeURIComponent(cat.nome)}`;
                list.appendChild(chip(`${cat.nome} (${cat.total})`, url));
            });
        }

        if (hint) {
            hint.textContent = `Nível ${levelTitle} · toque numa categoria para jogar`;
        }

        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    levelButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            selectedLevel = btn.dataset.pickLevel;
            levelButtons.forEach((b) => b.classList.toggle('level-card--selected', b === btn));
            renderCategories(selectedLevel);
        });
    });
}

document.querySelectorAll('[data-quiz-picker]').forEach(initQuizPicker);
