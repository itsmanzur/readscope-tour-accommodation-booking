document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('tourivoLookupForm');
    var btn = document.getElementById('tourivoLookupBtn');
    var errorBox = document.getElementById('tourivoLookupError');
    var resultBox = document.getElementById('tourivoLookupResult');
    var cancelBtn = document.getElementById('resCancelBtn');
    var cancelBox = document.getElementById('tourivoCancelBox');
    var confirmCancelBtn = document.getElementById('tourivoConfirmCancelBtn');
    var dismissCancelBtn = document.getElementById('tourivoDismissCancelBtn');
    var cancelAlert = document.getElementById('tourivoCancelAlert');

    var currentBooking = null;
    var trvAjaxUrl = tourivoLookupConfig.ajaxUrl;

    // POST to admin-ajax with the lookup nonce. A cached page may carry a stale nonce, so on a
    // `nonce_expired` reply fetch a fresh one once and transparently retry.
    function trvPost(formData, retried) {
        formData.set('nonce', document.getElementById('tourivo_lookup_nonce').value);

        return fetch(trvAjaxUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (retried || !data || data.success || !data.data || data.data.code !== 'nonce_expired') {
                return data;
            }

            var nonceRequest = new FormData();
            nonceRequest.append('action', 'tourivo_get_lookup_nonce');

            return fetch(trvAjaxUrl, { method: 'POST', body: nonceRequest })
                .then(function(res) { return res.json(); })
                .then(function(fresh) {
                    if (fresh && fresh.success && fresh.data && fresh.data.nonce) {
                        document.getElementById('tourivo_lookup_nonce').value = fresh.data.nonce;
                        return trvPost(formData, true);
                    }
                    return data;
                });
        });
    }

    if (!form || !btn) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var codeInput = document.getElementById('tourivo_lookup_code');
        var emailInput = document.getElementById('tourivo_lookup_email');

        var code = codeInput ? codeInput.value.trim() : '';
        var email = emailInput ? emailInput.value.trim() : '';

        if (!code || !email) {
            showError(tourivoLookupConfig.i18n.pleaseEnterBothYour);
            return;
        }

        // UI Loading state
        btn.disabled = true;
        btn.querySelector('.btn-text').style.display = 'none';
        btn.querySelector('.btn-spinner').style.display = 'inline';
        errorBox.style.display = 'none';
        resultBox.style.display = 'none';
        if (cancelBox) cancelBox.style.display = 'none';

        var formData = new FormData();
        formData.append('action', 'tourivo_lookup_booking');
        formData.append('nonce', document.getElementById('tourivo_lookup_nonce').value);
        formData.append('tourivo_hp_check', document.getElementById('tourivo_hp_check').value);
        formData.append('booking_code', code);
        formData.append('customer_email', email);

        trvPost(formData)
        .then(function(data) {
            btn.disabled = false;
            btn.querySelector('.btn-text').style.display = 'inline';
            btn.querySelector('.btn-spinner').style.display = 'none';

            if (data.success && data.data) {
                currentBooking = data.data;
                renderResult(data.data);
            } else {
                showError((data.data && data.data.message) ? data.data.message : tourivoLookupConfig.i18n.couldNotRetrieveReservation);
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.querySelector('.btn-text').style.display = 'inline';
            btn.querySelector('.btn-spinner').style.display = 'none';
            showError(tourivoLookupConfig.i18n.connectionErrorPleaseTry);
        });
    });

    if (cancelBtn && cancelBox) {
        cancelBtn.addEventListener('click', function() {
            cancelBox.style.display = cancelBox.style.display === 'none' ? 'block' : 'none';
            if (cancelAlert) cancelAlert.style.display = 'none';
        });
    }

    if (dismissCancelBtn && cancelBox) {
        dismissCancelBtn.addEventListener('click', function() {
            cancelBox.style.display = 'none';
        });
    }

    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', function() {
            if (!currentBooking) return;

            var reasonInput = document.getElementById('tourivo_cancel_reason');
            var reason = reasonInput ? reasonInput.value.trim() : '';

            confirmCancelBtn.disabled = true;
            confirmCancelBtn.textContent = tourivoLookupConfig.i18n.processing;
            if (cancelAlert) cancelAlert.style.display = 'none';

            var formData = new FormData();
            formData.append('action', 'tourivo_request_booking_cancellation');
            formData.append('booking_code', currentBooking.booking_code);
            formData.append('customer_email', currentBooking.customer_email);
            formData.append('reason', reason);
            formData.append('tourivo_hp_check', document.getElementById('tourivo_hp_check').value);

            trvPost(formData)
            .then(function(data) {
                confirmCancelBtn.disabled = false;
                confirmCancelBtn.textContent = tourivoLookupConfig.i18n.confirmCancellation;

                if (cancelAlert) {
                    cancelAlert.style.display = 'block';
                    if (data.success) {
                        cancelAlert.style.color = '#15803d';
                        cancelAlert.textContent = '✓ ' + (data.data.message || tourivoLookupConfig.i18n.requestSubmittedSuccessfully);

                        if (data.data.mode === 'cancelled') {
                            var statusBadge = document.getElementById('resBookingStatus');
                            if (statusBadge) {
                                statusBadge.textContent = 'Cancelled';
                                statusBadge.style.backgroundColor = '#ef4444';
                            }
                            if (cancelBtn) cancelBtn.style.display = 'none';
                        } else {
                            var reqBadge = document.getElementById('resCancelRequestedBadge');
                            if (reqBadge) reqBadge.style.display = 'inline-block';
                            if (cancelBtn) cancelBtn.disabled = true;
                        }
                    } else {
                        cancelAlert.style.color = '#b91c1c';
                        cancelAlert.textContent = '✕ ' + ((data.data && data.data.message) ? data.data.message : tourivoLookupConfig.i18n.failedToSubmitCancellation);
                    }
                }
            })
            .catch(function() {
                confirmCancelBtn.disabled = false;
                confirmCancelBtn.textContent = tourivoLookupConfig.i18n.confirmCancellation;
                if (cancelAlert) {
                    cancelAlert.style.display = 'block';
                    cancelAlert.style.color = '#b91c1c';
                    cancelAlert.textContent = '✕ ' + tourivoLookupConfig.i18n.connectionErrorPleaseTry;
                }
            });
        });
    }

    function showError(msg) {
        if (!errorBox) return;
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
    }

    function renderResult(b) {
        document.getElementById('resBookingCode').textContent = '#' + b.booking_code;
        document.getElementById('resCreatedAt').textContent = tourivoLookupConfig.i18n.bookedOn + ' ' + b.created_at;
        
        var statusBadge = document.getElementById('resBookingStatus');
        statusBadge.textContent = b.booking_status;
        statusBadge.style.backgroundColor = b.status_color;

        var payBadge = document.getElementById('resPaymentStatus');
        payBadge.textContent = b.payment_status;
        payBadge.style.backgroundColor = b.payment_color;

        var reqBadge = document.getElementById('resCancelRequestedBadge');
        if (reqBadge) {
            reqBadge.style.display = b.cancel_requested ? 'inline-block' : 'none';
        }

        document.getElementById('resCustomerName').textContent = b.customer_name;
        document.getElementById('resCustomerEmail').textContent = b.customer_email;
        document.getElementById('resPaymentMethod').textContent = b.payment_method;
        document.getElementById('resTotalAmount').textContent = b.total_amount;
        document.getElementById('resItemsContainer').innerHTML = b.items_html;

        var voucherBtn = document.getElementById('resVoucherBtn');
        if (voucherBtn && b.voucher_url) {
            voucherBtn.href = b.voucher_url;
        }

        if (cancelBtn) {
            cancelBtn.style.display = (b.allow_cancel && b.booking_status !== 'Cancelled') ? 'inline-block' : 'none';
            if (b.cancel_requested) {
                cancelBtn.disabled = true;
            }
        }

        resultBox.style.display = 'block';
    }
});
