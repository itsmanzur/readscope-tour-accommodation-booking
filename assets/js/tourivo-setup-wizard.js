var TourivoWizard = {
    currentStep: 1,
    nonce: tourivoWizardConfig.nonce,
    ajaxUrl: tourivoWizardConfig.ajaxUrl,

    goToStep: function(step) {
        for (var i = 1; i <= 4; i++) {
            var card = document.getElementById('wizard-card-' + i);
            var nav = document.getElementById('step-nav-' + i);
            if (card) card.style.display = (i === step) ? 'block' : 'none';
            if (nav) {
                nav.classList.remove('active');
                if (i < step) nav.classList.add('completed');
                if (i === step) nav.classList.add('active');
            }
        }
        this.currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    var currSelect = document.getElementById('wz_currency_code');
    var currSymbol = document.getElementById('wz_currency_symbol');
    if (currSelect && currSymbol) {
        currSelect.addEventListener('change', function() {
            var opt = currSelect.options[currSelect.selectedIndex];
            var symbol = opt.getAttribute('data-symbol');
            if (symbol) currSymbol.value = symbol;
        });
    }

    // Step 1 Save
    var btn1 = document.getElementById('btnSaveStep1');
    if (btn1) {
        btn1.addEventListener('click', function() {
            btn1.disabled = true;
            btn1.textContent = tourivoWizardConfig.i18n.saving;

            var formData = new FormData();
            formData.append('action', 'tourivo_wizard_save_step1');
            formData.append('nonce', TourivoWizard.nonce);
            formData.append('agency_name', document.getElementById('wz_agency_name').value);
            formData.append('notify_email', document.getElementById('wz_notify_email').value);
            formData.append('currency', document.getElementById('wz_currency_code').value);
            formData.append('currency_symbol', document.getElementById('wz_currency_symbol').value);
            formData.append('currency_position', document.getElementById('wz_currency_pos').value);

            fetch(TourivoWizard.ajaxUrl, { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn1.disabled = false;
                btn1.textContent = tourivoWizardConfig.i18n.continueToPages;
                if (data.success) {
                    TourivoWizard.goToStep(2);
                } else {
                    alert(data.data && data.data.message ? data.data.message : 'Error saving settings.');
                }
            })
            .catch(function() {
                btn1.disabled = false;
                btn1.textContent = tourivoWizardConfig.i18n.continueToPages;
                alert('Connection error.');
            });
        });
    }

    // Step 2 Create Pages
    var btn2 = document.getElementById('btnCreatePages');
    if (btn2) {
        btn2.addEventListener('click', function() {
            btn2.disabled = true;
            btn2.textContent = tourivoWizardConfig.i18n.creatingPages;

            var checkedPages = [];
            document.querySelectorAll('input[name="pages[]"]:checked').forEach(function(cb) {
                checkedPages.push(cb.value);
            });

            var formData = new FormData();
            formData.append('action', 'tourivo_wizard_create_pages');
            formData.append('nonce', TourivoWizard.nonce);
            formData.append('pages', JSON.stringify(checkedPages));

            fetch(TourivoWizard.ajaxUrl, { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn2.disabled = false;
                btn2.textContent = tourivoWizardConfig.i18n.generatePagesNext;
                TourivoWizard.goToStep(3);
            })
            .catch(function() {
                btn2.disabled = false;
                btn2.textContent = tourivoWizardConfig.i18n.generatePagesNext;
                TourivoWizard.goToStep(3);
            });
        });
    }

    // Step 3 Import Demo Data
    var btn3 = document.getElementById('btnImportDemo');
    var progBox = document.getElementById('demoImportProgress');
    if (btn3) {
        btn3.addEventListener('click', function() {
            btn3.disabled = true;
            if (progBox) progBox.style.display = 'block';

            var formData = new FormData();
            formData.append('action', 'tourivo_wizard_import_demo');
            formData.append('nonce', TourivoWizard.nonce);

            fetch(TourivoWizard.ajaxUrl, { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (progBox) progBox.style.display = 'none';
                TourivoWizard.goToStep(4);
            })
            .catch(function() {
                if (progBox) progBox.style.display = 'none';
                TourivoWizard.goToStep(4);
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-wizard-step]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            TourivoWizard.goToStep(parseInt(btn.getAttribute('data-wizard-step'), 10));
        });
    });
    ['wizardFormStep1', 'wizardFormStep2'].forEach(function(id) {
        var form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', function(e) { e.preventDefault(); });
        }
    });
});
