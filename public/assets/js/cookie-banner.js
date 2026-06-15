(function () {
    if (localStorage.getItem('cookie_consent') === 'accepted') return;

    var banner = document.getElementById('cookie-banner');
    if (!banner) return;

    setTimeout(function () {
        banner.classList.add('cookie-banner--visible');
    }, 600);

    document.getElementById('cookie-accept').addEventListener('click', function () {
        localStorage.setItem('cookie_consent', 'accepted');
        banner.classList.remove('cookie-banner--visible');
        setTimeout(function () { banner.remove(); }, 400);
    });

    document.getElementById('cookie-decline').addEventListener('click', function () {
        banner.classList.remove('cookie-banner--visible');
        setTimeout(function () { banner.remove(); }, 400);
    });
})();
