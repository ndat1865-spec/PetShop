import '../css/product-content.css';

document.querySelectorAll('[data-product-editor]').forEach((editor) => {
    const form = editor.querySelector('.admin-product-form');
    if (!form) return;

    const shortDescription = form.querySelector('[data-short-description]');
    const characterCount = form.querySelector('[data-character-count]');
    const updateCount = () => {
        if (characterCount && shortDescription) characterCount.textContent = `${shortDescription.value.length} / 500`;
    };
    shortDescription?.addEventListener('input', updateCount);
    updateCount();

    const preview = editor.querySelector('[data-product-preview]');
    const updatePreview = () => {
        if (!preview) return;
        const name = form.elements.namedItem('name')?.value.trim();
        const description = form.elements.namedItem('short_description')?.value.trim();
        preview.querySelector('[data-preview-heading]').textContent = name || 'Tên sản phẩm';
        preview.querySelector('[data-preview-description]').textContent = description || 'Mô tả ngắn sẽ hiển thị tại đây.';

        const storySection = preview.querySelector('[data-preview-story]');
        const detailHeading = form.elements.namedItem('detail_heading')?.value.trim();
        const story = form.elements.namedItem('description')?.value.trim();
        preview.querySelector('[data-preview-detail-heading]').textContent = detailHeading || '';
        preview.querySelector('[data-preview-story-copy]').textContent = story || '';
        storySection.hidden = !detailHeading && !story;

        const imageInput = form.querySelector('[data-preview-image-input]');
        const image = preview.querySelector('[data-preview-image]');
        const media = preview.querySelector('[data-preview-media]');
        if (imageInput?.value.trim()) {
            image.src = imageInput.value.trim();
            media.hidden = false;
        } else media.hidden = true;

        const highlightList = preview.querySelector('[data-preview-highlights]');
        const highlightSection = preview.querySelector('[data-preview-highlights-section]');
        const highlights = [...form.querySelectorAll('[name^="highlights["]')].map((input) => input.value.trim()).filter(Boolean);
        highlightList.replaceChildren(...highlights.map((value) => Object.assign(document.createElement('li'), { textContent: value })));
        highlightSection.hidden = highlights.length === 0;

        const specificationList = preview.querySelector('[data-preview-specifications]');
        const specificationSection = preview.querySelector('[data-preview-specifications-section]');
        const labels = [...form.querySelectorAll('[name^="specifications["][name$="[label]"]')];
        const specificationNodes = labels.map((label) => {
            const valueName = label.name.replace('[label]', '[value]');
            const value = form.elements.namedItem(valueName)?.value.trim();
            if (!label.value.trim() || !value) return null;
            const row = document.createElement('div');
            row.append(
                Object.assign(document.createElement('dt'), { textContent: label.value.trim() }),
                Object.assign(document.createElement('dd'), { textContent: value }),
            );
            return row;
        }).filter(Boolean).slice(0, 4);
        specificationList.replaceChildren(...specificationNodes);
        specificationSection.hidden = specificationNodes.length === 0;

        const sizeList = preview.querySelector('[data-preview-sizes]');
        const sizeSection = preview.querySelector('[data-preview-sizes-section]');
        const sizes = [...form.querySelectorAll('[name^="size_guide["][name$="[size]"]')].map((size) => {
            const guidanceName = size.name.replace('[size]', '[guidance]');
            const guidance = form.elements.namedItem(guidanceName)?.value.trim();
            if (!size.value.trim() || !guidance) return null;
            const row = document.createElement('div');
            row.append(
                Object.assign(document.createElement('dt'), { textContent: size.value.trim() }),
                Object.assign(document.createElement('dd'), { textContent: guidance }),
            );
            return row;
        }).filter(Boolean);
        sizeList.replaceChildren(...sizes);
        sizeSection.hidden = sizes.length === 0;

        const care = form.elements.namedItem('care_instructions')?.value.trim();
        const careSection = preview.querySelector('[data-preview-care-section]');
        preview.querySelector('[data-preview-care]').textContent = care || '';
        careSection.hidden = !care;
    };

    form.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const rows = repeater.querySelector('[data-repeater-rows]');
        const template = repeater.querySelector('[data-repeater-template]');
        const addButton = repeater.querySelector('[data-repeater-add]');
        const maxItems = Number(repeater.dataset.maxItems) || 20;
        let nextIndex = rows.querySelectorAll('[data-repeater-row]').length;

        const sync = () => {
            const currentRows = [...rows.querySelectorAll('[data-repeater-row]')];
            addButton.disabled = currentRows.length >= maxItems;
            currentRows.forEach((row, index) => {
                const remove = row.querySelector('[data-repeater-remove]');
                if (!remove) return;
                const base = remove.getAttribute('aria-label')?.replace(/ \d+$/, '') || 'Xóa dòng';
                remove.setAttribute('aria-label', `${base} ${index + 1}`);
            });
        };

        addButton?.addEventListener('click', () => {
            if (!template || rows.querySelectorAll('[data-repeater-row]').length >= maxItems) return;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
            const row = wrapper.firstElementChild;
            rows.append(row);
            row.querySelector('input')?.focus();
            sync();
            updatePreview();
        });

        rows.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-repeater-remove]');
            if (!remove) return;
            const row = remove.closest('[data-repeater-row]');
            if (rows.querySelectorAll('[data-repeater-row]').length === 1) {
                row.querySelectorAll('input, textarea').forEach((input) => { input.value = ''; });
            } else row.remove();
            sync();
            updatePreview();
        });
        sync();
    });

    form.addEventListener('input', updatePreview);
    updatePreview();
});

const productErrorSummary = document.querySelector('[data-error-summary]');
if (productErrorSummary) requestAnimationFrame(() => productErrorSummary.focus());
