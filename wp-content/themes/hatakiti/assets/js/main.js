(function () {
    'use strict';

    var nav = document.getElementById('hk-nav');
    var toggle = document.getElementById('hk-nav-toggle');

    if (!nav || !toggle) {
        return;
    }

    toggle.addEventListener('click', function () {
        var isOpen = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
})();
