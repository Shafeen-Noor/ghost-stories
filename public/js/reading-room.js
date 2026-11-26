(() => {
    const toggle = document.querySelector('[data-reading-toggle]');
    if (!toggle) return;

    const storyBody = document.querySelector('.story-body');
    let fontSize = 20;

    toggle.addEventListener('click', () => {
        const active = document.body.classList.toggle('reading-room');
        toggle.textContent = active ? 'Exit reading room' : 'Reading room';
        toggle.setAttribute('aria-pressed', String(active));
    });

    document.querySelectorAll('[data-reading-size]').forEach(button => {
        button.addEventListener('click', () => {
            fontSize = Math.max(16, Math.min(28, fontSize + Number(button.dataset.readingSize) * 2));
            storyBody.style.fontSize = fontSize + 'px';
        });
    });
})();
