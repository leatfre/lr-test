/**
 * Hosting Compatibility Dashboard — interactive probes.
 *
 * Every button with the `probe-trigger` class POSTs to its `data-probe-url`
 * and renders the JSON result (status badge + value + detail) inline, next
 * to the button, inside the matching `[data-probe-result]` element.
 */

const STATUS_CLASSES = {
    pass: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 ring-1 ring-inset ring-emerald-500/30',
    warn: 'bg-amber-500/15 text-amber-700 dark:text-amber-300 ring-1 ring-inset ring-amber-500/30',
    fail: 'bg-rose-500/15 text-rose-700 dark:text-rose-300 ring-1 ring-inset ring-rose-500/30',
    skipped: 'bg-slate-500/15 text-slate-700 dark:text-slate-300 ring-1 ring-inset ring-slate-500/30',
};

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function renderResult(container, payload) {
    container.classList.remove('hidden');
    container.className = 'probe-result mt-3 rounded-lg p-2 text-sm ' + (STATUS_CLASSES[payload.status] || STATUS_CLASSES.skipped);

    const status = (payload.status || 'unknown').toUpperCase();
    const value = payload.value ?? '';
    const detail = payload.detail ?? '';

    container.innerHTML = '';

    const line = document.createElement('div');
    line.className = 'font-semibold';
    line.textContent = `${status} — ${value}`;
    container.appendChild(line);

    if (detail) {
        const detailLine = document.createElement('div');
        detailLine.className = 'mt-1 font-normal opacity-90';
        detailLine.textContent = detail;
        container.appendChild(detailLine);
    }
}

function renderError(container, message) {
    container.classList.remove('hidden');
    container.className = 'probe-result mt-3 rounded-lg p-2 text-sm ' + STATUS_CLASSES.fail;
    container.textContent = `FAIL — ${message}`;
}

async function runJsonProbe(button) {
    const url = button.dataset.probeUrl;
    const id = button.dataset.probeId;
    const resultEl = document.querySelector(`[data-probe-result="${id}"]`);

    let body = null;

    if (id === 'mail') {
        const input = document.getElementById('probe-mail-to');
        body = JSON.stringify({ to: input ? input.value : '' });
    }

    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = 'Running…';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                ...(body ? { 'Content-Type': 'application/json' } : {}),
            },
            body,
        });

        const payload = await response.json();

        if (!response.ok && response.status !== 422) {
            renderError(resultEl, payload.message || `HTTP ${response.status}`);
        } else if (response.status === 422) {
            const message = payload.errors
                ? Object.values(payload.errors).flat().join(' ')
                : (payload.message || 'Validation failed.');
            renderError(resultEl, message);
        } else {
            renderResult(resultEl, payload);
        }
    } catch (error) {
        renderError(resultEl, error.message || 'Network error.');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function runUploadProbe(button) {
    const id = 'upload';
    const url = button.dataset.probeUrl;
    const resultEl = document.querySelector(`[data-probe-result="${id}"]`);
    const fileInput = document.getElementById('probe-upload-file');

    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        renderError(resultEl, 'Choose a file first.');
        return;
    }

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = 'Running…';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: formData,
        });

        const payload = await response.json();

        if (response.status === 422) {
            const message = payload.errors
                ? Object.values(payload.errors).flat().join(' ')
                : (payload.message || 'Validation failed.');
            renderError(resultEl, message);
        } else if (!response.ok) {
            renderError(resultEl, payload.message || `HTTP ${response.status}`);
        } else {
            renderResult(resultEl, payload);
        }
    } catch (error) {
        renderError(resultEl, error.message || 'Network error.');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.probe-trigger').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.probeId === 'upload') {
                runUploadProbe(button);
            } else {
                runJsonProbe(button);
            }
        });
    });
});
