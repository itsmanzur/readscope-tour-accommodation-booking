document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-tourivo-action]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (btn.getAttribute('data-tourivo-action') === 'print') {
                window.print();
            } else {
                window.close();
            }
        });
    });
});
