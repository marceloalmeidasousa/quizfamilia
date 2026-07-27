function initLivePicker(root) {
    let categoriesByLevel = {};
    try {
        categoriesByLevel = JSON.parse(root.dataset.categories || '{}');
    } catch (e) {
        categoriesByLevel = {};
    }

    const form = root.querySelector('[data-live-create-form]');
    if (!form) {
        return;
    }

    const panel = root.querySelector('[data-category-panel]');
    const list = root.querySelector('[data-category-list]');
    const hint = root.querySelector('[data-category-hint]');
    const categoriaInput = form.querySelector('[data-live-categoria]');
    const levelRadios = [...form.querySelectorAll('input[name="nivel"]')];

    function selectedNivel() {
        const checked = levelRadios.find((r) => r.checked);
        return checked ? checked.value : null;
    }

    function levelTitle(nivel) {
        const label = form.querySelector(`input[name="nivel"][value="${nivel}"]`)?.closest('label');
        return label?.querySelector('.font-bold')?.textContent?.trim() || nivel;
    }

    function chip(label, categoria, { primary = false } = {}) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = primary ? 'quiz-cat-chip quiz-cat-chip--primary' : 'quiz-cat-chip';
        btn.textContent = label;
        btn.addEventListener('click', () => {
            if (categoriaInput) {
                categoriaInput.value = categoria;
            }
            form.requestSubmit();
        });
        return btn;
    }

    function renderCategories(nivel) {
        if (!panel || !list || !nivel) {
            return;
        }

        const cats = categoriesByLevel[nivel] || [];
        list.innerHTML = '';

        if (!cats.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-ink/60';
            empty.textContent = 'Nenhuma categoria encontrada. Rode o seed das perguntas no banco.';
            list.appendChild(empty);
        } else {
            const total = cats.reduce((s, c) => s + c.total, 0);
            list.appendChild(chip(`Todas (${total})`, 'todas', { primary: true }));
            cats.forEach((cat) => {
                list.appendChild(chip(`${cat.nome} (${cat.total})`, cat.nome));
            });
        }

        if (hint) {
            hint.textContent = `Nível ${levelTitle(nivel)} · toque numa categoria para gerar o PIN`;
        }

        panel.classList.remove('hidden');
    }

    levelRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            renderCategories(radio.value);
        });
    });

    const initial = selectedNivel();
    if (initial) {
        renderCategories(initial);
    }
}

document.querySelectorAll('[data-live-picker]').forEach(initLivePicker);
