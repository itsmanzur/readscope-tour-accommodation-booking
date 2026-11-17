document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-readscope-action]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (btn.getAttribute('data-readscope-action') === 'print') {
                window.print();
            } else {
                window.close();
            }
        });
    });
});
