document.querySelectorAll('.story-editor').forEach((editor) => {
    const source = editor.querySelector('[data-editor-source]');
    const rich = editor.querySelector('[data-rich-editor]');
    const preview = editor.querySelector('[data-editor-preview]');
    const status = editor.querySelector('[data-editor-status]');
    const counter = editor.querySelector('[data-word-count]');
    const toolbar = editor.querySelector('.editor-toolbar');
    let mode = 'write';
    let savedRange;
    source.hidden = true;
    source.required = false;
    rich.hidden = false;

    function markdown(node) {
        if (node.nodeType === Node.TEXT_NODE) return node.textContent.replace(/([\\*_[\]`])/g, '\\$1');
        if (node.nodeType !== Node.ELEMENT_NODE) return '';
        const children = Array.from(node.childNodes).map(markdown).join('');
        const tag = node.tagName.toLowerCase();
        if (['script', 'style', 'iframe', 'object'].includes(tag)) return '';
        if (['b', 'strong'].includes(tag)) return '**' + children + '**';
        if (['i', 'em'].includes(tag)) return '*' + children + '*';
        if (tag === 'a') {
            const url = node.getAttribute('href') || '';
            return /^https?:\/\//i.test(url) ? '[' + children + '](' + url.replace(/[()\s]/g, encodeURIComponent) + ')' : children;
        }
        if (tag === 'img') {
            const url = node.getAttribute('src') || '';
            return /^https?:\/\//i.test(url) ? '![' + (node.alt || 'Photograph').replace(/[\[\]]/g, '') + '](' + url.replace(/[()\s]/g, encodeURIComponent) + ')' : '';
        }
        if (/^h[1-6]$/.test(tag)) return '\n\n' + '#'.repeat(Number(tag[1])) + ' ' + children.trim() + '\n\n';
        if (tag === 'br') return '\n';
        if (tag === 'blockquote') return '\n\n' + children.trim().split('\n').map(line => '> ' + line).join('\n') + '\n\n';
        if (['ul', 'ol'].includes(tag)) return '\n\n' + Array.from(node.children).map((li, i) => (tag === 'ul' ? '- ' : (i + 1) + '. ') + Array.from(li.childNodes).map(markdown).join('').trim()).join('\n') + '\n\n';
        if (['p', 'div'].includes(tag)) return '\n\n' + children + '\n\n';
        if (tag === 'hr') return '\n\n---\n\n';
        if (tag === 'code') return '`' + children + '`';
        return children;
    }
    function updateCount() {
        const text = mode === 'source' ? source.value : rich.innerText;
        const words = text.trim().split(/\s+/).filter(Boolean).length;
        counter.textContent = words + ' words · ' + Math.max(1, Math.ceil(words / 200)) + ' min read';
    }
    function sync() {
        if (mode === 'write') source.value = Array.from(rich.childNodes).map(markdown).join('').replace(/\n{3,}/g, '\n\n').trim();
        updateCount();
    }
    editor.addEventListener('story:sync', sync);
    rich.addEventListener('input', sync);
    source.addEventListener('input', updateCount);
    rich.addEventListener('paste', (event) => {
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
        sync();
    });
    rich.addEventListener('mouseup', saveSelection);
    rich.addEventListener('keyup', saveSelection);
    function saveSelection() {
        const selection = window.getSelection();
        if (selection.rangeCount && rich.contains(selection.anchorNode)) savedRange = selection.getRangeAt(0).cloneRange();
    }
    toolbar.addEventListener('mousedown', event => { if (event.target.closest('button')) event.preventDefault(); });
    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-command]');
        if (!button || mode !== 'write') return;
        rich.focus();
        if (savedRange) { const selection = window.getSelection(); selection.removeAllRanges(); selection.addRange(savedRange); }
        const command = button.dataset.command;
        if (command === 'heading' || command === 'quote') document.execCommand('formatBlock', false, command === 'heading' ? 'h2' : 'blockquote');
        else if (command === 'link' || command === 'image') {
            const url = window.prompt(command === 'link' ? 'Paste a link (https://...)' : 'Paste an image URL (https://...). You can also upload photos in the gallery.');
            if (!url) return;
            if (!/^https?:\/\//i.test(url.trim())) { status.textContent = 'Use a link starting with https:// or http://.'; return; }
            document.execCommand(command === 'link' ? 'createLink' : 'insertImage', false, url.trim());
        } else document.execCommand(command, false);
        sync(); saveSelection();
    });
    editor.querySelectorAll('[data-mode]').forEach(button => button.addEventListener('click', async () => {
        const next = button.dataset.mode;
        sync();
        if (next !== 'source' && (mode === 'source' || next === 'preview')) {
            status.textContent = 'Preparing your preview…';
            try {
                if (!source.value.trim()) { rich.replaceChildren(); preview.replaceChildren(); }
                else {
                    const response = await fetch(editor.dataset.previewUrl, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify({body:source.value})});
                    if (!response.ok) throw new Error('Preview unavailable');
                    const data = await response.json();
                    rich.innerHTML = data.html;
                    preview.innerHTML = data.html;
                }
                status.textContent = 'This is how your formatting will look when published.';
            } catch { status.textContent = 'Preview could not be loaded. Your writing is still here. Please try again.'; return; }
        }
        mode = next;
        source.hidden = mode !== 'source'; rich.hidden = mode !== 'write'; preview.hidden = mode !== 'preview';
        toolbar.hidden = mode !== 'write';
        editor.querySelectorAll('[data-mode]').forEach(tab => tab.setAttribute('aria-pressed', String(tab === button)));
        updateCount();
    }));
    source.form.addEventListener('submit', (event) => {
        sync();
        if (source.value.length < 20 || source.value.length > 30000) {
            event.preventDefault(); status.textContent = 'Your story needs between 20 and 30,000 characters. Keep writing before publishing.'; rich.focus();
        }
    });
    updateCount();
});

document.querySelectorAll('[data-image-input]').forEach(input => {
    let urls = [];
    input.addEventListener('change', () => {
        urls.forEach(url => URL.revokeObjectURL(url)); urls = [];
        const target = document.querySelector('[data-preview-for="' + input.id + '"]');
        target.replaceChildren();
        Array.from(input.files).forEach(file => {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 4 * 1024 * 1024) {
                const error = document.createElement('p'); error.className='error'; error.textContent=file.name + ': choose a JPG, PNG or WebP up to 4 MB.'; target.append(error); return;
            }
            const figure = document.createElement('figure'); const image = document.createElement('img'); const caption = document.createElement('figcaption');
            const url = URL.createObjectURL(file); urls.push(url); image.src=url; image.alt='Selected image preview'; caption.textContent=file.name; figure.append(image,caption); target.append(figure);
        });
    });
});

document.querySelectorAll('.account-menu').forEach(menu => document.addEventListener('click', event => { if (!menu.contains(event.target)) menu.open=false; }));
