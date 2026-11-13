@props(['target'])
<div class="mt-2" data-document-capture="{{ $target }}" hidden>
    <button type="button" class="btn btn-outline-primary btn-sm" data-take-photo>Scanner avec la caméra</button>
    <input type="file" accept="image/*" capture="environment" data-camera-file hidden>
    <p class="form-text mb-0" role="status" data-capture-status></p>
    <dialog class="gcd-scan-dialog border-0 rounded shadow p-3" aria-label="Scanner le document">
        <h2 class="h5">Scanner le document</h2>
        <p class="small" role="status" data-scan-message></p>
        <div data-camera-view hidden>
            <video autoplay playsinline muted class="w-100 rounded"></video>
            <button type="button" class="btn gcd-action mt-2" data-snapshot disabled>Prendre la photo</button>
        </div>
        <div data-preview-view hidden>
            <label class="form-label">
                Agrandissement <output data-zoom-label>100 %</output>
                <input class="form-range" type="range" min="100" max="250" step="25" value="100" data-zoom>
            </label>
            <div class="gcd-scan-preview border rounded" tabindex="0" aria-label="Aperçu de la page"><img alt="Page scannée" data-preview></div>
            <div class="d-flex flex-wrap align-items-center gap-2 my-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-prev>Précédente</button>
                <span class="small" aria-live="polite" data-page-count></span>
                <button type="button" class="btn btn-outline-primary btn-sm" data-next>Suivante</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-rotate>Pivoter</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-retake>Reprendre cette page</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-remove-page>Supprimer cette page</button>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-outline-primary btn-sm" data-add-page>Ajouter une page</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-native-camera hidden>Ouvrir l’appareil photo</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-back-to-preview hidden>Revenir à l’aperçu</button>
            <button type="button" class="btn gcd-action btn-sm" data-attach disabled>Utiliser le PDF</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-cancel-photo>Annuler</button>
        </div>
    </dialog>
</div>
