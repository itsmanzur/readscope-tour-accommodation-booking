document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('tourivo-submit-ref-form');
    var btn = document.getElementById('tourivo-submit-ref-btn');
    var alertEl = document.getElementById('tourivo-ref-alert');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        btn.disabled = true;
        btn.textContent = 'Submitting...';
        alertEl.style.display = 'none';

        var fd = new FormData(form);
        var ajaxEndpoint = (window.tourivoData && window.tourivoData.ajaxUrl) ? window.tourivoData.ajaxUrl : tourivoThankYouConfig.ajaxUrl;

        fetch(ajaxEndpoint, {
            method: 'POST',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.textContent = tourivoThankYouConfig.i18n.submitReference;
            alertEl.style.display = 'block';

            if (res && res.success) {
                alertEl.style.background = '#dcfce7';
                alertEl.style.color = '#15803d';
                alertEl.textContent = res.data.message || 'Reference submitted successfully!';
                setTimeout(function() { window.location.reload(); }, 1200);
            } else {
                alertEl.style.background = '#fee2e2';
                alertEl.style.color = '#991b1b';
                alertEl.textContent = (res && res.data && res.data.message) ? res.data.message : 'Submission failed. Please try again.';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = tourivoThankYouConfig.i18n.submitReference;
            alertEl.style.display = 'block';
            alertEl.style.background = '#fee2e2';
            alertEl.style.color = '#991b1b';
            alertEl.textContent = 'Connection error. Please try again.';
        });
    });
});

// Conversion tracking (dataLayer): once per booking and browser session.
(function() {
    var d = tourivoThankYouConfig.dataLayer;
    if (!d) return;
    var sKey = 'tourivo_tracked_' + d.code;

    if (!window.sessionStorage || !window.sessionStorage.getItem(sKey)) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'tourivo_booking',
            booking_id: d.code,
            value: d.value,
            currency: d.currency
        });
        if (window.sessionStorage) {
            window.sessionStorage.setItem(sKey, '1');
        }
    }
})();
