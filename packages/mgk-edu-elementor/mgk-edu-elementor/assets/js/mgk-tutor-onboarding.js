(function () {
    const storageKey = 'mgkTutorUploadedDocument';

    const sizeLabel = (bytes) => {
        if (!bytes) return '0KB';
        if (bytes >= 1024 * 1024) return `${(bytes / 1024 / 1024).toFixed(1)}MB`;
        return `${Math.max(1, Math.round(bytes / 1024))}KB`;
    };

    const saveUpload = (file, kind, preview) => {
        const data = {
            kind,
            name: file.name,
            type: file.type || 'document',
            size: sizeLabel(file.size),
            preview: preview || '',
            savedAt: new Date().toISOString(),
        };
        sessionStorage.setItem(storageKey, JSON.stringify(data));
        return data;
    };

    const renderApplyPreview = (root, input, data) => {
        const box = input.closest('.mgk-tutor-apply-upload, .mgk-tutor-apply-doc-upload');
        const status = root.querySelector(`[data-mgk-file-status="${input.dataset.mgkTutorUpload}"]`);
        const preview = root.querySelector('[data-mgk-doc-preview]');

        if (box) box.classList.add('is-uploaded');
        if (status) status.textContent = `${data.name} · ${data.size} · ready for OCR`;
        if (preview) {
            preview.classList.add('is-uploaded');
            preview.innerHTML = '';
            if (data.preview) {
                const img = document.createElement('img');
                img.src = data.preview;
                img.alt = data.name;
                preview.appendChild(img);
            }
            const label = document.createElement('span');
            label.textContent = data.name;
            preview.appendChild(label);
        }

        const autosave = root.querySelector('.mgk-tutor-apply-actions span');
        if (autosave) autosave.textContent = 'DOCUMENT SAVED · CONTINUE TO VERIFICATION';
    };

    const initApply = () => {
        const root = document.querySelector('[data-mgk-tutor-apply]');
        if (!root) return;

        root.querySelectorAll('[data-mgk-tutor-upload]').forEach((input) => {
            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) return;

                const kind = input.dataset.mgkTutorUpload || 'document';
                if (file.type && file.type.indexOf('image/') === 0) {
                    const reader = new FileReader();
                    reader.addEventListener('load', () => {
                        const data = saveUpload(file, kind, String(reader.result || ''));
                        renderApplyPreview(root, input, data);
                    });
                    reader.readAsDataURL(file);
                    return;
                }

                const data = saveUpload(file, kind, '');
                renderApplyPreview(root, input, data);
            });
        });

        const cta = root.querySelector('[data-mgk-apply-continue]');
        if (!cta) return;

        cta.addEventListener('click', (event) => {
            const uploaded = sessionStorage.getItem(storageKey);
            const step = Number(root.dataset.step || 0);
            const verificationUrl = root.dataset.verificationUrl || '/tutor/verification/';
            if (!uploaded && step < 6) return;

            event.preventDefault();
            window.location.href = verificationUrl;
        });
    };

    const initVerification = () => {
        const root = document.querySelector('[data-mgk-tutor-verification]');
        if (!root) return;

        let data = null;
        try {
            data = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
        } catch (error) {
            data = null;
        }
        if (!data || !data.name) return;

        const upload = root.querySelector('[data-mgk-verification-upload]');
        const preview = root.querySelector('[data-mgk-verification-preview]');
        const progress = root.querySelector('[data-mgk-verification-progress]');
        const meta = root.querySelector('[data-mgk-verification-meta]');
        if (!upload || !preview || !progress || !meta) return;

        upload.classList.add('is-document-ready');
        preview.innerHTML = '';
        if (data.preview) {
            const img = document.createElement('img');
            img.src = data.preview;
            img.alt = data.name;
            preview.appendChild(img);
        }
        const chip = document.createElement('strong');
        chip.textContent = 'Document preview · OCR ready';
        preview.appendChild(chip);
        const name = document.createElement('small');
        name.textContent = data.name;
        preview.appendChild(name);
        progress.style.width = '100%';
        meta.textContent = `${data.name} · ${data.size || 'uploaded'} · received from S19`;
    };

    document.addEventListener('DOMContentLoaded', () => {
        initApply();
        initVerification();
    });
}());
