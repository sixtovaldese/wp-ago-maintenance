/* aGo Maintenance Admin JS */
(function () {
    'use strict';

    var enabledToggle = document.getElementById('ago-enabled');
    var saveBtn       = document.getElementById('ago-save-btn');
    var previewBtn    = document.getElementById('ago-preview-btn');
    var statusBox     = document.getElementById('ago-maint-status');
    var masterCard    = document.querySelector('.ago-master-toggle-card');

    if (!saveBtn) return;

    var settings = (typeof agoMaintenance !== 'undefined') ? agoMaintenance.settings : {};

    // ───── Hydrate fields from saved settings ─────

    function hydrate() {
        if (enabledToggle) {
            enabledToggle.checked = !!settings.enabled;
            updateMasterCard();
        }


        var modeRadios = document.querySelectorAll('input[name="ago_mode"]');
        modeRadios.forEach(function (radio) {
            radio.checked = (radio.value === (settings.mode || 'maintenance'));
        });

        var textFields = { 'ago-title': 'title', 'ago-message': 'message', 'ago-whitelist': 'ip_whitelist' };
        Object.keys(textFields).forEach(function (id) {
            var el = document.getElementById(id);
            if (el && settings[textFields[id]] !== undefined) {
                el.value = settings[textFields[id]];
            }
        });

        var colorFields = { 'ago-bg-color': 'bg_color', 'ago-text-color': 'text_color' };
        Object.keys(colorFields).forEach(function (id) {
            var el = document.getElementById(id);
            if (el && settings[colorFields[id]]) {
                el.value = settings[colorFields[id]];
            }
        });

        var countdownEl = document.getElementById('ago-countdown');
        if (countdownEl && settings.countdown_datetime) {
            countdownEl.value = settings.countdown_datetime;
        }

        // Media fields
        setMedia('logo_url', settings.logo_url || '');
        setMedia('bg_image_url', settings.bg_image_url || '');

        // Overlay opacity
        var overlay = document.getElementById('ago-overlay-opacity');
        if (overlay) {
            overlay.value = (settings.overlay_opacity != null) ? settings.overlay_opacity : 60;
            updateOverlayLabel();
        }
    }

    function updateMasterCard() {
        if (masterCard && enabledToggle) {
            masterCard.classList.toggle('active', enabledToggle.checked);
        }
    }

    function updateOverlayLabel() {
        var overlay = document.getElementById('ago-overlay-opacity');
        var label   = document.getElementById('ago-overlay-value');
        if (overlay && label) label.textContent = overlay.value + '%';
    }

    function setMedia(key, url) {
        var inputId   = key === 'logo_url' ? 'ago-logo-url' : 'ago-bg-image-url';
        var previewId = key === 'logo_url' ? 'ago-logo-preview' : 'ago-bg-preview';
        var input     = document.getElementById(inputId);
        var preview   = document.getElementById(previewId);
        if (input)  input.value = url;
        if (preview) {
            var img   = preview.querySelector('img');
            var empty = preview.querySelector('.ago-media-empty');
            if (url) {
                img.src = url;
                img.style.display = 'block';
                if (empty) empty.style.display = 'none';
            } else {
                img.removeAttribute('src');
                img.style.display = 'none';
                if (empty) empty.style.display = 'inline';
            }
        }
    }

    // ───── Gather settings ─────

    function gatherSettings() {
        var data = {};
        data.enabled = enabledToggle ? enabledToggle.checked : false;

        var checkedMode = document.querySelector('input[name="ago_mode"]:checked');
        data.mode = checkedMode ? checkedMode.value : 'maintenance';

        data.title              = (document.getElementById('ago-title') || {}).value || '';
        data.message            = (document.getElementById('ago-message') || {}).value || '';
        data.ip_whitelist       = (document.getElementById('ago-whitelist') || {}).value || '';
        data.countdown_datetime = (document.getElementById('ago-countdown') || {}).value || '';
        data.bg_color           = (document.getElementById('ago-bg-color') || {}).value || '#1d2327';
        data.text_color         = (document.getElementById('ago-text-color') || {}).value || '#ffffff';
        data.logo_url           = (document.getElementById('ago-logo-url') || {}).value || '';
        data.bg_image_url       = (document.getElementById('ago-bg-image-url') || {}).value || '';
        data.overlay_opacity    = parseInt((document.getElementById('ago-overlay-opacity') || {}).value || '60', 10);

        return data;
    }

    // ───── Save settings ─────

    function saveSettings(callback) {
        var data = gatherSettings();

        statusBox.style.display = 'block';
        statusBox.className = '';
        statusBox.textContent = 'Saving…';

        fetch(agoMaintenance.restUrl + '/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': agoMaintenance.nonce,
            },
            body: JSON.stringify(data),
        })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            if (resp.saved) {
                settings = resp.settings;
                statusBox.className = 'success';
                statusBox.textContent = 'Settings saved.';
            } else {
                statusBox.className = 'error';
                statusBox.textContent = 'Could not save settings.';
            }
            setTimeout(function () { statusBox.style.display = 'none'; }, 3000);
            if (callback) callback();
        })
        .catch(function (err) {
            statusBox.className = 'error';
            statusBox.textContent = 'Error: ' + err.message;
        });
    }

    // ───── Media uploader (WP) ─────

    function openMediaPicker(targetKey) {
        if (typeof wp === 'undefined' || !wp.media) return;

        var frame = wp.media({
            title: 'Select image',
            button: { text: 'Use this image' },
            library: { type: 'image' },
            multiple: false,
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            setMedia(targetKey, attachment.url);
        });

        frame.open();
    }

    document.querySelectorAll('.ago-media-pick').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openMediaPicker(btn.getAttribute('data-target'));
        });
    });

    document.querySelectorAll('.ago-media-clear').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            setMedia(btn.getAttribute('data-target'), '');
        });
    });

    // Overlay live label
    var overlayInput = document.getElementById('ago-overlay-opacity');
    if (overlayInput) overlayInput.addEventListener('input', updateOverlayLabel);

    // ───── Event listeners ─────

    if (enabledToggle) {
        enabledToggle.addEventListener('change', function () {
            updateMasterCard();
            saveSettings();
        });
    }


    saveBtn.addEventListener('click', function () { saveSettings(); });

    if (previewBtn) {
        previewBtn.addEventListener('click', function () {
            saveSettings(function () {
                window.open(agoMaintenance.siteUrl + '?ago_maintenance_preview=1', '_blank');
            });
        });
    }

    hydrate();
})();
