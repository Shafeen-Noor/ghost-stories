(() => {
    const progress = document.querySelector('[data-case-progress]');
    if (!progress) return;

    const status = document.querySelector('[data-case-status]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let pending = false;

    progress.addEventListener('submit', async event => {
        const form = event.target.closest('[data-case-action]');
        if (!form) return;

        event.preventDefault();
        if (pending) return;
        pending = true;

        const data = new FormData(form);
        if (event.submitter?.name) {
            data.set(event.submitter.name, event.submitter.value);
        }

        const buttons = progress.querySelectorAll('button');
        buttons.forEach(button => button.disabled = true);
        status.textContent = 'Opening the case…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: data,
            });
            if (!response.ok) throw new Error('Request failed');

            const result = await response.json();
            progress.innerHTML = result.html;
            status.textContent = 'Case updated.';

            const target = progress.querySelector('.case-verdict')
                || progress.querySelector('.case-piece:last-of-type');
            target?.scrollIntoView({
                behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
                block: 'center',
            });
        } catch {
            status.textContent = 'The case could not update. Your progress is saved. Refresh and try again.';
            buttons.forEach(button => button.disabled = false);
        } finally {
            pending = false;
        }
    });
})();
