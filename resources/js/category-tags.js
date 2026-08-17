function initCategoryTags(root) {
    const input = root.querySelector('[data-tag-input]');
    const hiddenWrap = root.querySelector('[data-tag-hidden]');
    const form = root.closest('form');
    const max = Number(root.dataset.max || 20);

    let tags = [];
    try {
        tags = JSON.parse(root.dataset.initial || '[]');
    } catch {
        tags = [];
    }

    if (!Array.isArray(tags)) {
        tags = [];
    }

    tags = tags.map((tag) => String(tag).replace(/\s+/g, ' ').trim()).filter(Boolean);

    function syncHidden() {
        hiddenWrap.innerHTML = '';
        tags.forEach((tag) => {
            const el = document.createElement('input');
            el.type = 'hidden';
            el.name = 'categories[]';
            el.value = tag;
            hiddenWrap.appendChild(el);
        });
    }

    function render() {
        root.querySelectorAll('[data-tag]').forEach((el) => el.remove());

        tags.forEach((tag) => {
            const chip = document.createElement('span');
            chip.dataset.tag = tag;
            chip.className =
                'inline-flex max-w-full items-center gap-1 rounded-full bg-brand-deep/10 py-1 pl-3 pr-1.5 text-sm font-bold text-brand-deep';

            const label = document.createElement('span');
            label.className = 'truncate';
            label.textContent = tag;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.removeTag = tag;
            btn.setAttribute('aria-label', `Remover ${tag}`);
            btn.className =
                'inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-base leading-none text-brand-deep/70 hover:bg-brand-deep/15 hover:text-brand-deep';
            btn.textContent = '×';

            chip.append(label, btn);
            root.insertBefore(chip, input);
        });

        syncHidden();
    }

    function add(raw) {
        const tag = String(raw || '')
            .replace(/\s+/g, ' ')
            .trim();

        if (!tag || tags.length >= max) {
            return;
        }

        if (tags.some((item) => item.toLowerCase() === tag.toLowerCase())) {
            return;
        }

        tags.push(tag);
        render();
    }

    function remove(tag) {
        tags = tags.filter((item) => item !== tag);
        render();
    }

    function commitInput() {
        add(input.value);
        input.value = '';
    }

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ',' || event.key === ';') {
            event.preventDefault();
            commitInput();
        } else if (event.key === 'Backspace' && input.value === '' && tags.length) {
            remove(tags[tags.length - 1]);
        }
    });

    input.addEventListener('input', () => {
        if (!/[;,]/.test(input.value)) {
            return;
        }

        const parts = input.value.split(/[;,]/);
        input.value = parts.pop() ?? '';
        parts.forEach(add);
    });

    input.addEventListener('blur', commitInput);

    root.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-remove-tag]');
        if (btn) {
            event.preventDefault();
            remove(btn.dataset.removeTag);
            input.focus();
            return;
        }

        input.focus();
    });

    form?.addEventListener('submit', commitInput);

    render();
}

document.querySelectorAll('[data-category-tags]').forEach(initCategoryTags);
