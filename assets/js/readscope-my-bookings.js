window.ReadscopeMyBookings = {
    nonce: readscopeMyBookingsConfig.nonce,
    ajaxUrl: readscopeMyBookingsConfig.ajaxUrl,
    targetBookingId: 0,

    init: function() {
        // Tab switching
        var tabBtns = document.querySelectorAll('.readscope-tab-btn');
        tabBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                tabBtns.forEach(function(b) { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
                ReadscopeMyBookings.filterCards(btn.getAttribute('data-tab'));
            });
        });

        // Open Cancel Modal
        document.querySelectorAll('.btnOpenCancelModal').forEach(function(btn) {
            btn.addEventListener('click', function() {
                ReadscopeMyBookings.targetBookingId = parseInt(btn.getAttribute('data-booking-id'), 10);
                document.getElementById('trvCancelReason').value = '';
                var feedback = document.getElementById('trvCancelFeedback');
                feedback.style.display = 'none';
                feedback.innerHTML = '';
                document.getElementById('readscopeCancelModal').style.display = 'flex';
            });
        });

        // Confirm Cancellation
        var btnConfirm = document.getElementById('btnConfirmCancel');
        if (btnConfirm) {
            btnConfirm.addEventListener('click', function() {
                ReadscopeMyBookings.submitCancellation();
            });
        }

        // View Details
        document.querySelectorAll('.btnViewDetails').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = parseInt(btn.getAttribute('data-booking-id'), 10);
                ReadscopeMyBookings.fetchDetails(id);
            });
        });
    },

    filterCards: function(tab) {
        var cards = document.querySelectorAll('.readscope-booking-card');
        cards.forEach(function(card) {
            var category = card.getAttribute('data-category');
            if (tab === 'all' || category === tab) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    },

    closeCancelModal: function() {
        document.getElementById('readscopeCancelModal').style.display = 'none';
        ReadscopeMyBookings.targetBookingId = 0;
    },

    closeDetailsModal: function() {
        document.getElementById('readscopeDetailsModal').style.display = 'none';
    },

    submitCancellation: function() {
        var btn = document.getElementById('btnConfirmCancel');
        var reason = document.getElementById('trvCancelReason').value.trim();
        var feedback = document.getElementById('trvCancelFeedback');

        btn.disabled = true;
        btn.textContent = readscopeMyBookingsConfig.i18n.processing;

        var formData = new FormData();
        formData.append('action', 'readscope_my_bookings_cancel');
        formData.append('nonce', ReadscopeMyBookings.nonce);
        formData.append('booking_id', ReadscopeMyBookings.targetBookingId);
        formData.append('reason', reason);

        fetch(ReadscopeMyBookings.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.textContent = readscopeMyBookingsConfig.i18n.confirmCancellation;

            if (res && res.success) {
                feedback.style.display = 'block';
                feedback.style.background = '#dcfce7';
                feedback.style.color = '#15803d';
                feedback.style.border = '1px solid #86efac';
                feedback.textContent = res.data && res.data.message ? res.data.message : readscopeMyBookingsConfig.i18n.cancellationSubmittedSuccessfully;

                setTimeout(function() {
                    window.location.reload();
                }, 1200);
            } else {
                feedback.style.display = 'block';
                feedback.style.background = '#fee2e2';
                feedback.style.color = '#b91c1c';
                feedback.style.border = '1px solid #fca5a5';
                feedback.textContent = res && res.data && res.data.message ? res.data.message : readscopeMyBookingsConfig.i18n.failedToProcessCancellation;
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.textContent = readscopeMyBookingsConfig.i18n.confirmCancellation;
            feedback.style.display = 'block';
            feedback.style.background = '#fee2e2';
            feedback.style.color = '#b91c1c';
            feedback.textContent = readscopeMyBookingsConfig.i18n.aNetworkErrorOccurred;
        });
    },

    fetchDetails: function(bookingId) {
        var modal = document.getElementById('readscopeDetailsModal');
        var body = document.getElementById('readscopeDetailsBody');
        modal.style.display = 'flex';
        body.innerHTML = '<div style="text-align:center; padding:30px; color:#64748b;">' + readscopeMyBookingsConfig.i18n.loadingBookingDetails + '</div>';

        var formData = new FormData();
        formData.append('action', 'readscope_my_bookings_details');
        formData.append('nonce', ReadscopeMyBookings.nonce);
        formData.append('booking_id', bookingId);

        fetch(ReadscopeMyBookings.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res && res.success && res.data) {
                var b = res.data.booking;
                var items = res.data.items || [];
                var esc = function(value) {
                    return String(value == null ? '' : value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };
                var html = '<div style="margin-bottom:16px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">';
                html += '<div style="font-size: var(--trv-text-xs); color:#64748b; text-transform:uppercase;">' + readscopeMyBookingsConfig.i18n.bookingReference + '</div>';
                html += '<strong style="font-size: var(--trv-text-xl); color:#0f172a;">#' + esc(b.booking_code) + '</strong>';
                html += '<div style="margin-top:4px; font-size: var(--trv-text-sm); color:#64748b;">' + readscopeMyBookingsConfig.i18n.placedOn + ' ' + esc(b.created_at) + '</div>';
                html += '</div>';

                html += '<h4 style="margin:16px 0 8px; font-size: var(--trv-text-base); color:#0f172a;">' + readscopeMyBookingsConfig.i18n.reservedItems + '</h4>';
                items.forEach(function(it) {
                    var range = it.check_out ? (esc(it.check_in) + ' → ' + esc(it.check_out)) : esc(it.check_in);
                    html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:8px;">';
                    html += '<div style="display:flex; justify-content:space-between; font-weight:700; color:#0f172a;">';
                    html += '<span>' + esc(it.item_title) + '</span>';
                    html += '<span>' + esc(it.total_price) + '</span>';
                    html += '</div>';
                    html += '<div style="font-size: var(--trv-text-sm); color:#64748b; margin-top:4px;">';
                    html += '📅 ' + range + (it.time_slot ? (' &bull; ⏰ ' + esc(it.time_slot)) : '') + ' &bull; 👥 Qty: ' + esc(it.quantity);
                    html += '</div>';
                    html += '</div>';
                });

                html += '<h4 style="margin:16px 0 8px; font-size: var(--trv-text-base); color:#0f172a;">' + readscopeMyBookingsConfig.i18n.paymentFinancialSummary + '</h4>';
                html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; font-size: var(--trv-text-sm);">';
                html += '<div style="display:flex; justify-content:space-between; margin-bottom:4px;"><span>' + readscopeMyBookingsConfig.i18n.paymentMethod + '</span><strong>' + esc(b.payment_method) + '</strong></div>';
                html += '<div style="display:flex; justify-content:space-between; margin-bottom:4px;"><span>' + readscopeMyBookingsConfig.i18n.paymentStatus + '</span><strong>' + esc(b.payment_status) + '</strong></div>';
                html += '<div style="display:flex; justify-content:space-between; margin-bottom:4px;"><span>' + readscopeMyBookingsConfig.i18n.tax + '</span><span>' + esc(b.tax_amount) + '</span></div>';
                html += '<div style="display:flex; justify-content:space-between; font-size: var(--trv-text-base); font-weight: 700; color:#0f172a; border-top:1px solid #e2e8f0; padding-top:6px; margin-top:6px;"><span>' + readscopeMyBookingsConfig.i18n.total + '</span><span>' + esc(b.total_amount) + '</span></div>';
                html += '</div>';

                if (b.customer_notes) {
                    html += '<h4 style="margin:16px 0 8px; font-size: var(--trv-text-base); color:#0f172a;">' + readscopeMyBookingsConfig.i18n.yourNotes + '</h4>';
                    html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px; font-size: var(--trv-text-sm); color:#475569;">' + esc(b.customer_notes) + '</div>';
                }

                body.innerHTML = html;
            } else {
                body.innerHTML = '<div style="color:#b91c1c; padding:20px; text-align:center;">' + readscopeMyBookingsConfig.i18n.failedToLoadBooking + '</div>';
            }
        })
        .catch(function(err) {
            body.innerHTML = '<div style="color:#b91c1c; padding:20px; text-align:center;">' + readscopeMyBookingsConfig.i18n.networkErrorShort + '</div>';
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.ReadscopeMyBookings.init);
} else {
    window.ReadscopeMyBookings.init();
}

document.addEventListener('click', function(e) {
    var trigger = e.target.closest('[data-readscope-action]');
    if (!trigger) {
        return;
    }
    var action = trigger.getAttribute('data-readscope-action');
    if (action === 'close-details') {
        window.ReadscopeMyBookings.closeDetailsModal();
    } else if (action === 'close-cancel') {
        window.ReadscopeMyBookings.closeCancelModal();
    }
});
