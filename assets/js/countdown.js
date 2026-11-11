(function () {
    function start() {
        var el = document.getElementById('ago-countdown');
        if (!el) return;
        var target = parseInt(el.getAttribute('data-target'), 10);
        if (isNaN(target)) { el.style.display = 'none'; return; }
        var d = document.getElementById('ago-cd-days'),
            h = document.getElementById('ago-cd-hours'),
            m = document.getElementById('ago-cd-minutes'),
            s = document.getElementById('ago-cd-seconds');
        function tick() {
            var diff = target - Date.now();
            if (diff <= 0) { d.textContent = h.textContent = m.textContent = s.textContent = '0'; return; }
            d.textContent = Math.floor(diff / 86400000);
            h.textContent = Math.floor((diff % 86400000) / 3600000);
            m.textContent = Math.floor((diff % 3600000) / 60000);
            s.textContent = Math.floor((diff % 60000) / 1000);
        }
        tick();
        setInterval(tick, 1000);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
