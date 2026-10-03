"use strict";

// ─── Image preview ────────────────────────────────────────────────────────────
function previewAdImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var img = document.getElementById('imagePreview');
            var wrap = document.getElementById('imagePreviewWrap');
            img.src = e.target.result;
            wrap.classList.remove('hidden');

            // Also update the live preview panel image
            var previewImg = document.getElementById('previewBannerImg');
            var previewWrap = document.getElementById('previewImageWrap');
            if (previewImg && previewWrap) {
                previewImg.src = e.target.result;
                previewWrap.classList.remove('hidden');
            }
            showCurrentAdPreview();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ─── Live preview: reflect form fields into the right-hand panel ──────────────
function showCurrentAdPreview() {
    var preview = document.getElementById('currentAdPreview');
    if (preview) preview.classList.remove('hidden');
    document.getElementById('noPositionMsg').classList.add('hidden');
}

function syncPreviewFields() {
    var title    = document.getElementById('adTitle').value.trim();
    var desc     = document.getElementById('adDescription').value.trim();
    var link     = document.getElementById('adLink').value.trim();
    var posEl    = document.getElementById('adPosition');
    var posLabel = posEl.options[posEl.selectedIndex]
                    ? posEl.options[posEl.selectedIndex].text
                    : '';

    document.getElementById('previewTitle').textContent    = title    || '(no title)';
    document.getElementById('previewDesc').textContent     = desc     || '';
    document.getElementById('previewLink').textContent     = link     || '';
    document.getElementById('previewPosition').textContent = posLabel || '';

    if (title || desc || link) {
        showCurrentAdPreview();
    }
}

// ─── Load existing ads at selected position via AJAX ─────────────────────────
function loadExistingAds(position) {
    if (!position) return;

    var previewUrl = window._adPreviewUrl || '';
    if (!previewUrl) return;

    fetch(previewUrl + '?position=' + encodeURIComponent(position), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        var section = document.getElementById('existingAdsSection');
        var list    = document.getElementById('existingAdsList');
        var ads     = data.advertisements || [];

        list.innerHTML = '';

        if (ads.length === 0) {
            section.classList.add('hidden');
            return;
        }

        section.classList.remove('hidden');
        ads.forEach(function (ad) {
            var card = document.createElement('div');
            card.className = 'rounded border border-gray-200 overflow-hidden bg-white flex gap-2 p-2 text-xs';
            card.innerHTML =
                (ad.image
                    ? '<img src="' + ad.image + '" class="w-16 h-12 object-cover rounded flex-shrink-0">'
                    : '<div class="w-16 h-12 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center text-gray-300"><i class="fa-solid fa-image"></i></div>'
                ) +
                '<div class="min-w-0">' +
                    '<p class="font-medium text-gray-800 truncate">' + (ad.title || '') + '</p>' +
                    (ad.description ? '<p class="text-gray-500 truncate">' + ad.description + '</p>' : '') +
                    (ad.link ? '<a href="' + ad.link + '" target="_blank" class="text-primary truncate block">' + ad.link + '</a>' : '') +
                '</div>';
            list.appendChild(card);
        });
    })
    .catch(function () {
        // silently ignore preview fetch errors
    });
}

// ─── Wire up events ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    // Expose preview URL set by inline script in blade (or fall back)
    if (!window._adPreviewUrl) {
        // Try to read from meta if available
        var meta = document.querySelector('meta[name="ad-preview-url"]');
        window._adPreviewUrl = meta ? meta.getAttribute('content') : '';
    }

    var posSelect = document.getElementById('adPosition');
    var titleInput = document.getElementById('adTitle');
    var descInput  = document.getElementById('adDescription');
    var linkInput  = document.getElementById('adLink');

    if (posSelect) {
        posSelect.addEventListener('change', function () {
            syncPreviewFields();
            loadExistingAds(this.value);
        });
    }

    [titleInput, descInput, linkInput].forEach(function (el) {
        if (el) el.addEventListener('input', syncPreviewFields);
    });
});
