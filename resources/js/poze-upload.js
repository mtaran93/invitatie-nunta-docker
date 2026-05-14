import './bootstrap';
import axios from 'axios';

/**
 * One file in the upload queue.
 */
class FileItem {
    static nextId = 1;

    constructor(file) {
        this.id = FileItem.nextId++;
        this.file = file;
        this.status = 'pending'; // pending | uploading | done | failed
        this.progress = 0;
        this.error = null;
        this.attempts = 0;
    }
}

/**
 * Strategy: uploads a single FileItem with exponential-backoff retry.
 * The retry policy is encapsulated here so the queue stays unaware of network details.
 */
class FileUploader {
    constructor({ url, maxRetries, csrfToken }) {
        this.url = url;
        this.maxRetries = maxRetries;
        this.csrfToken = csrfToken;
    }

    async upload(item, onProgress) {
        item.attempts = 0;

        while (true) {
            item.attempts += 1;
            try {
                await this.#postOnce(item, onProgress);
                return;
            } catch (err) {
                const { retryable, message } = this.#classify(err);
                if (!retryable || item.attempts > this.maxRetries) {
                    const finalErr = new Error(message);
                    finalErr.cause = err;
                    throw finalErr;
                }
                await this.#sleep(this.#backoffMs(item.attempts));
            }
        }
    }

    #postOnce(item, onProgress) {
        const form = new FormData();
        form.append('photo', item.file);

        return axios.post(this.url, form, {
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            onUploadProgress: (e) => {
                if (e.total) {
                    onProgress(Math.round((e.loaded * 100) / e.total));
                }
            },
        });
    }

    #classify(err) {
        if (!err.response) {
            return { retryable: true, message: 'Conexiune întreruptă' };
        }
        const s = err.response.status;
        if (s === 429 || s >= 500) {
            return { retryable: true, message: `Eroare server (${s})` };
        }
        const msg = err.response.data?.message || `Cerere invalidă (${s})`;
        return { retryable: false, message: msg };
    }

    #backoffMs(attempt) {
        // 1s, 3s
        return attempt === 1 ? 1000 : 3000;
    }

    #sleep(ms) {
        return new Promise((r) => setTimeout(r, ms));
    }
}

/**
 * Queue with bounded concurrency. Notifies observers on every state change.
 */
class UploadQueue {
    constructor({ concurrency, uploader }) {
        this.concurrency = concurrency;
        this.uploader = uploader;
        this.items = [];
        this.inFlight = 0;
        this.listeners = new Set();
    }

    subscribe(fn) {
        this.listeners.add(fn);
        return () => this.listeners.delete(fn);
    }

    add(files) {
        for (const file of files) {
            this.items.push(new FileItem(file));
        }
        this.#emit();
        this.#pump();
    }

    retry(itemId) {
        const item = this.items.find((i) => i.id === itemId);
        if (!item || item.status !== 'failed') return;
        item.status = 'pending';
        item.error = null;
        item.progress = 0;
        this.#emit();
        this.#pump();
    }

    retryAllFailed() {
        for (const item of this.items) {
            if (item.status === 'failed') {
                item.status = 'pending';
                item.error = null;
                item.progress = 0;
            }
        }
        this.#emit();
        this.#pump();
    }

    #pump() {
        while (this.inFlight < this.concurrency) {
            const next = this.items.find((i) => i.status === 'pending');
            if (!next) return;
            this.#run(next);
        }
    }

    async #run(item) {
        this.inFlight += 1;
        item.status = 'uploading';
        item.progress = 0;
        this.#emit();

        try {
            await this.uploader.upload(item, (pct) => {
                item.progress = pct;
                this.#emit();
            });
            item.status = 'done';
            item.progress = 100;
        } catch (err) {
            item.status = 'failed';
            item.error = err.message || 'Eșuat';
        } finally {
            this.inFlight -= 1;
            this.#emit();
            this.#pump();
        }
    }

    #emit() {
        const snapshot = {
            items: this.items,
            total: this.items.length,
            done: this.items.filter((i) => i.status === 'done').length,
            failed: this.items.filter((i) => i.status === 'failed').length,
            pending: this.items.filter((i) => i.status === 'pending' || i.status === 'uploading').length,
        };
        for (const fn of this.listeners) fn(snapshot);
    }
}

/**
 * Observer/view: renders the queue state into the DOM.
 */
class UploadUI {
    constructor(root) {
        this.root = root;
        this.listEl = root.querySelector('#poze-list');
        this.template = root.querySelector('#poze-item-template');
        this.summaryEl = root.querySelector('#poze-summary');
        this.doneEl = root.querySelector('#poze-done');
        this.totalEl = root.querySelector('#poze-total');
        this.failedEl = root.querySelector('#poze-failed');
        this.failedLineEl = root.querySelector('#poze-failed-line');
        this.retryAllBtn = root.querySelector('#poze-retry-all');
        this.messageEl = root.querySelector('#poze-message');
        this.itemNodes = new Map();
    }

    onRetryAll(fn) {
        this.retryAllBtn.addEventListener('click', fn);
    }

    onRetryOne(fn) {
        this.listEl.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-role="retry"]');
            if (!btn) return;
            const id = Number(btn.closest('li').dataset.itemId);
            fn(id);
        });
    }

    showMessage(text) {
        this.messageEl.textContent = text;
        this.messageEl.classList.remove('hidden');
    }

    clearMessage() {
        this.messageEl.classList.add('hidden');
    }

    render(state) {
        if (state.total === 0) {
            this.summaryEl.classList.add('hidden');
            return;
        }
        this.summaryEl.classList.remove('hidden');
        this.totalEl.textContent = String(state.total);
        this.doneEl.textContent = String(state.done);
        this.failedEl.textContent = String(state.failed);
        this.failedLineEl.classList.toggle('hidden', state.failed === 0);
        this.retryAllBtn.classList.toggle('hidden', state.failed === 0);

        for (const item of state.items) {
            let node = this.itemNodes.get(item.id);
            if (!node) {
                node = this.template.content.firstElementChild.cloneNode(true);
                node.dataset.itemId = String(item.id);
                node.querySelector('[data-role="name"]').textContent = item.file.name;
                this.listEl.appendChild(node);
                this.itemNodes.set(item.id, node);
            }
            const status = node.querySelector('[data-role="status"]');
            const bar = node.querySelector('[data-role="bar"]');
            const errEl = node.querySelector('[data-role="error"]');
            const retryBtn = node.querySelector('[data-role="retry"]');

            status.textContent = this.#statusLabel(item);
            bar.style.width = item.progress + '%';
            bar.style.backgroundColor = item.status === 'failed' ? '#b91c1c' : '#7d2e3d';
            if (item.status === 'failed') {
                errEl.textContent = item.error || 'Eșuat';
                errEl.classList.remove('hidden');
                retryBtn.classList.remove('hidden');
            } else {
                errEl.classList.add('hidden');
                retryBtn.classList.add('hidden');
            }
        }
    }

    #statusLabel(item) {
        switch (item.status) {
            case 'pending': return 'În așteptare';
            case 'uploading': return `${item.progress}%`;
            case 'done': return 'Gata';
            case 'failed': return 'Eșuat';
            default: return '';
        }
    }
}

/**
 * Client-side gatekeeper: validates the selection before any network work.
 */
function validateSelection(files, { maxFiles, maxSize }) {
    if (files.length === 0) return 'Nu ai selectat nimic.';
    if (files.length > maxFiles) return `Maxim ${maxFiles} poze odată.`;
    const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
    for (const f of files) {
        if (!allowed.includes(f.type) && !/\.(jpe?g|png|webp|heic|heif)$/i.test(f.name)) {
            return `Format neacceptat: ${f.name}`;
        }
        if (f.size > maxSize) {
            return `Fișier prea mare (max 3 MB): ${f.name}`;
        }
    }
    return null;
}

function boot() {
    const body = document.body;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const config = {
        url: body.dataset.uploadUrl,
        maxFiles: Number(body.dataset.maxFiles),
        maxSize: Number(body.dataset.maxSizeBytes),
        concurrency: Number(body.dataset.concurrency),
        maxRetries: Number(body.dataset.maxRetries),
    };

    const uploader = new FileUploader({
        url: config.url,
        maxRetries: config.maxRetries,
        csrfToken,
    });
    const queue = new UploadQueue({ concurrency: config.concurrency, uploader });
    const ui = new UploadUI(document);

    queue.subscribe((state) => ui.render(state));
    ui.onRetryOne((id) => queue.retry(id));
    ui.onRetryAll(() => queue.retryAllFailed());

    const input = document.getElementById('poze-input');
    input.addEventListener('change', () => {
        ui.clearMessage();
        const files = Array.from(input.files || []);
        const error = validateSelection(files, { maxFiles: config.maxFiles, maxSize: config.maxSize });
        if (error) {
            ui.showMessage(error);
            input.value = '';
            return;
        }
        queue.add(files);
        input.value = '';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
