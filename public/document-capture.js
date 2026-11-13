function scanPdf(pages, quality) {
    const parts = [];
    const offsets = [0];
    let size = 0;
    function append(part) {
        parts.push(part);
        size += typeof part === 'string' ? part.length : part.byteLength;
    }
    function object(...content) {
        offsets.push(size);
        append(`${offsets.length - 1} 0 obj\n`);
        content.forEach(append);
        append('\nendobj\n');
    }

    append('%PDF-1.4\n');
    object('<< /Type /Catalog /Pages 2 0 R >>');
    object(`<< /Type /Pages /Kids [${pages.map((_, index) => `${3 + index * 3} 0 R`).join(' ')}] /Count ${pages.length} >>`);
    pages.forEach((canvas, index) => {
        const page = 3 + index * 3;
        const {width, height} = canvas;
        const jpeg = Uint8Array.from(atob(canvas.toDataURL('image/jpeg', quality).split(',')[1]), char => char.charCodeAt(0));
        const scale = Math.min(571 / width, 818 / height);
        const w = width * scale;
        const h = height * scale;
        const paint = `q ${w.toFixed(2)} 0 0 ${h.toFixed(2)} ${((595 - w) / 2).toFixed(2)} ${((842 - h) / 2).toFixed(2)} cm /Photo Do Q\n`;

        object(`<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /XObject << /Photo ${page + 1} 0 R >> >> /Contents ${page + 2} 0 R >>`);
        object(`<< /Type /XObject /Subtype /Image /Width ${width} /Height ${height} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${jpeg.length} >>\nstream\n`, jpeg, '\nendstream');
        object(`<< /Length ${paint.length} >>\nstream\n${paint}endstream`);
    });

    const start = size;
    append(`xref\n0 ${offsets.length}\n0000000000 65535 f \n`);
    for (const offset of offsets.slice(1)) append(`${String(offset).padStart(10, '0')} 00000 n \n`);
    append(`trailer\n<< /Size ${offsets.length} /Root 1 0 R >>\nstartxref\n${start}\n%%EOF\n`);
    return new File(parts, 'document-scanné.pdf', {type: 'application/pdf'});
}

function bindCapture(panel) {
    if (!panel.hidden || typeof DataTransfer === 'undefined') return;
    const input = document.getElementById(panel.dataset.documentCapture);
    const find = selector => panel.querySelector(selector);
    const dialog = find('dialog');
    if (!input || typeof dialog.showModal !== 'function') return;
    panel.hidden = false;

    const video = find('video');
    const cameraFile = find('[data-camera-file]');
    const cameraView = find('[data-camera-view]');
    const previewView = find('[data-preview-view]');
    const message = find('[data-scan-message]');
    const status = find('[data-capture-status]');
    const nativeCamera = find('[data-native-camera]');
    const snapshot = find('[data-snapshot]');
    const attach = find('[data-attach]');
    const add = find('[data-add-page]');
    const back = find('[data-back-to-preview]');
    const image = find('[data-preview]');
    const zoom = find('[data-zoom]');
    const pages = [];
    const pageLimit = 10;
    let current = 0;
    let replacing = null;
    let captureId = 0;

    function stopCamera() {
        ++captureId;
        video.srcObject?.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        cameraView.hidden = true;
        snapshot.disabled = true;
    }

    function updateZoom() {
        image.style.width = zoom.value + '%';
        find('[data-zoom-label]').textContent = zoom.value + ' %';
    }

    function showPreview() {
        const page = pages[current];
        previewView.hidden = !page;
        attach.disabled = !page;
        back.hidden = true;
        add.disabled = pages.length >= pageLimit;
        find('[data-prev]').disabled = current === 0;
        find('[data-next]').disabled = current >= pages.length - 1;
        if (!page) {
            image.removeAttribute('src');
            return;
        }
        image.src = page.toDataURL('image/jpeg', .82);
        find('[data-page-count]').textContent = `Page ${current + 1} sur ${pages.length}`;
        zoom.value = '100';
        updateZoom();
        image.parentElement.scrollTo(0, 0);
    }

    function savePhoto(source, width, height) {
        if (!width || !height) throw new Error('Image vide');
        const canvas = document.createElement('canvas');
        const scale = Math.min(1, 1800 / Math.max(width, height));
        canvas.width = Math.max(1, Math.round(width * scale));
        canvas.height = Math.max(1, Math.round(height * scale));
        const context = canvas.getContext('2d');
        context.fillStyle = '#fff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(source, 0, 0, canvas.width, canvas.height);

        current = replacing ?? pages.length;
        pages[current] = canvas;
        replacing = null;
        stopCamera();
        nativeCamera.hidden = true;
        message.textContent = '';
        showPreview();
    }

    async function startCamera(index = null) {
        if (index === null && pages.length >= pageLimit) return;
        stopCamera();
        const attempt = captureId;
        replacing = index;
        previewView.hidden = true;
        attach.disabled = true;
        back.hidden = pages.length === 0;
        add.disabled = true;
        nativeCamera.hidden = true;

        if (!navigator.mediaDevices?.getUserMedia) {
            nativeCamera.hidden = false;
            message.textContent = 'Ouvrez l’appareil photo ou sélectionnez une photo du document.';
            cameraFile.click();
            return;
        }
        cameraView.hidden = false;
        message.textContent = 'Autorisez l’accès à la caméra pour photographier la page.';
        try {
            const stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: 'environment'}}, audio: false});
            if (attempt !== captureId || !dialog.open || !dialog.isConnected) {
                stream.getTracks().forEach(track => track.stop());
                return;
            }
            video.srcObject = stream;
            message.textContent = 'Placez la page entière dans le cadre.';
        } catch {
            if (attempt !== captureId) return;
            stopCamera();
            nativeCamera.hidden = false;
            message.textContent = 'Caméra indisponible. Ouvrez l’appareil photo, choisissez une photo ou importez un PDF.';
        }
    }

    find('[data-take-photo]').addEventListener('click', () => {
        dialog.showModal();
        startCamera();
    });
    find('[data-native-camera]').addEventListener('click', () => cameraFile.click());
    find('[data-cancel-photo]').addEventListener('click', () => dialog.close());
    back.addEventListener('click', () => {
        stopCamera();
        replacing = null;
        nativeCamera.hidden = true;
        message.textContent = '';
        showPreview();
    });
    add.addEventListener('click', () => startCamera());
    find('[data-retake]').addEventListener('click', () => startCamera(current));
    find('[data-prev]').addEventListener('click', () => { current--; showPreview(); });
    find('[data-next]').addEventListener('click', () => { current++; showPreview(); });
    find('[data-remove-page]').addEventListener('click', () => {
        pages.splice(current, 1);
        current = Math.max(0, Math.min(current, pages.length - 1));
        showPreview();
    });
    find('[data-rotate]').addEventListener('click', () => {
        const page = pages[current];
        const rotated = document.createElement('canvas');
        rotated.width = page.height;
        rotated.height = page.width;
        const context = rotated.getContext('2d');
        context.translate(rotated.width, 0);
        context.rotate(Math.PI / 2);
        context.drawImage(page, 0, 0);
        pages[current] = rotated;
        showPreview();
    });
    zoom.addEventListener('input', updateZoom);
    video.addEventListener('loadeddata', () => { snapshot.disabled = !video.srcObject; });
    dialog.addEventListener('close', () => {
        stopCamera();
        pages.length = 0;
        current = 0;
        replacing = null;
        nativeCamera.hidden = true;
        message.textContent = '';
        showPreview();
    });
    snapshot.addEventListener('click', () => {
        try {
            savePhoto(video, video.videoWidth, video.videoHeight);
        } catch {
            message.textContent = 'La photo n’a pas pu être préparée. Réessayez.';
        }
    });
    cameraFile.addEventListener('cancel', () => {
        if (pages.length) back.click();
    });
    cameraFile.addEventListener('change', async () => {
        const photo = cameraFile.files[0];
        cameraFile.value = '';
        if (!photo) return;
        const attempt = captureId;
        const url = URL.createObjectURL(photo);
        try {
            const photoImage = new Image();
            photoImage.src = url;
            await photoImage.decode();
            if (attempt === captureId && dialog.open) savePhoto(photoImage, photoImage.naturalWidth, photoImage.naturalHeight);
        } catch {
            if (attempt === captureId) message.textContent = 'Photo illisible. Réessayez ou importez un PDF.';
        } finally {
            URL.revokeObjectURL(url);
        }
    });
    attach.addEventListener('click', () => {
        const maxMb = Number(input.dataset.maxSizeMb || 5);
        let pdf = scanPdf(pages, .86);
        if (pdf.size > maxMb * 1024 ** 2) pdf = scanPdf(pages, .55);
        if (pdf.size > maxMb * 1024 ** 2) {
            message.textContent = `Le PDF dépasse ${maxMb} Mo. Retirez des pages ou importez un PDF plus léger.`;
            return;
        }
        const files = new DataTransfer();
        if (input.multiple) {
            for (const file of input.files) files.items.add(file);
        }
        files.items.add(pdf);
        input.files = files.files;
        input.dispatchEvent(new Event('change', {bubbles: true}));
        status.textContent = `PDF de ${pages.length} ${pages.length === 1 ? 'page' : 'pages'} prêt à être envoyé avec le formulaire.`;
        dialog.close();
    });
    input.addEventListener('change', () => { status.textContent = ''; });
    panel.addEventListener('htmx:beforeCleanupElement', stopCamera);
    window.addEventListener('pagehide', stopCamera);
}

function initDocumentCapture() {
    document.querySelectorAll('[data-document-capture]').forEach(bindCapture);
}
initDocumentCapture();
document.addEventListener('htmx:afterSwap', initDocumentCapture);
