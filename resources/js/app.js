import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.querySelectorAll('[data-variant-list]').forEach((list) => {
    const template = document.querySelector('[data-variant-template]');
    const addButton = document.querySelector('[data-add-variant]');

    if (!template || !addButton) {
        return;
    }

    let nextIndex = Number(list.dataset.nextIndex);

    addButton.addEventListener('click', () => {
        list.insertAdjacentHTML(
            'beforeend',
            template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)),
        );
    });

    list.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const removeButton = event.target.closest('[data-remove-variant]');
        removeButton?.closest('[data-variant-row]')?.remove();
    });
});
