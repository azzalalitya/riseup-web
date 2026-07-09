/* ================================================================
   RiseUp — Polish JS (Phase 3)
   Modular, no dependency. Aman kalau elemen tidak ada di halaman.
   Menghormati prefers-reduced-motion.
   ================================================================ */

(function () {
    'use strict';

    var reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- 1. Reveal on scroll (fade-up) ---------- */
    function initReveal() {
        // Kandidat elemen yang layak dianimasi (tanpa perlu ubah blade).
        var selectors = [
            '.card', '.saveup-card', '.saveup-vault',
            '.quick-action', '.learning-card', '.quest-card',
            '.badge-card', '.jrn-entry', '.jrn-card', '.jrn-reflection',
            '.lb-row', '.wdr-table',
            '.riseup-stat-card', 'section > h2', 'section > h3'
        ].join(',');

        var els = document.querySelectorAll(selectors);
        if (!els.length) return;

        els.forEach(function (el, i) {
            el.classList.add('reveal');
            el.setAttribute('data-stagger', '');
            // Stagger relatif per parent (max ~10 supaya nggak overshoot)
            el.style.setProperty('--i', Math.min(i, 10));
        });

        if (reduceMotion || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-in'); });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('is-in');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        els.forEach(function (el) { io.observe(el); });
    }

    /* ---------- 2. Count-up angka ---------- */
    function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

    function animateNumber(el, target, duration) {
        if (reduceMotion) { el.textContent = formatNumberLike(el, target); return; }
        var start = performance.now();
        var initial = 0;
        function frame(now) {
            var p = Math.min(1, (now - start) / duration);
            var val = Math.round(initial + (target - initial) * easeOutCubic(p));
            el.textContent = formatNumberLike(el, val);
            if (p < 1) requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    }

    // Pertahankan gaya angka aslinya (Rp / persen / plain)
    function formatNumberLike(el, val) {
        var pattern = el.getAttribute('data-format') || 'plain';
        if (pattern === 'rupiah') return 'Rp ' + val.toLocaleString('id-ID');
        if (pattern === 'percent') return val + '%';
        return val.toLocaleString('id-ID');
    }

    function initCountUps() {
        // Ambil semua elemen dengan class .count-up ATAU angka besar bawaan
        // yang menampilkan data numerik (dashboard stats).
        var els = document.querySelectorAll(
            '.count-up, [data-countup]'
        );
        if (!els.length) return;

        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) {
                var target = parseInt(el.getAttribute('data-target') || el.textContent.replace(/[^\d]/g, ''), 10) || 0;
                el.textContent = formatNumberLike(el, target);
            });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                var el = e.target;
                var raw = el.getAttribute('data-target');
                var target = raw !== null
                    ? parseInt(raw, 10)
                    : parseInt(el.textContent.replace(/[^\d]/g, ''), 10);
                if (isNaN(target)) return;
                animateNumber(el, target, 1200);
                io.unobserve(el);
            });
        }, { threshold: 0.4 });

        els.forEach(function (el) { io.observe(el); });
    }

    /* ---------- 3. Progress bar animate-on-view ---------- */
    function initProgressBars() {
        var bars = document.querySelectorAll(
            '.saveup-progress span, .b-bar span, .progress-bar span, .activity-progress span'
        );
        if (!bars.length) return;

        bars.forEach(function (span) {
            // Simpan target width dari inline style, mulai dari 0
            var target = span.style.width || getComputedStyle(span).width;
            span.dataset.targetWidth = target;
            span.style.width = '0%';

            // Tandai bar penuh untuk shimmer
            var pct = parseFloat(target);
            if (!isNaN(pct) && pct >= 100) {
                var parent = span.parentElement;
                if (parent) parent.classList.add('is-full');
            }
        });

        if (reduceMotion || !('IntersectionObserver' in window)) {
            bars.forEach(function (s) { s.style.width = s.dataset.targetWidth; });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                var span = e.target;
                setTimeout(function () { span.style.width = span.dataset.targetWidth; }, 120);
                io.unobserve(span);
            });
        }, { threshold: 0.3 });

        bars.forEach(function (s) { io.observe(s); });
    }

    /* ---------- 4. Kalender: pasang custom prop --i untuk stagger ---------- */
    function initCalendar() {
        var cells = document.querySelectorAll('.calendar-grid > *');
        cells.forEach(function (c, i) { c.style.setProperty('--i', i); });
    }

    /* ---------- 5. Active nav link marker ---------- */
    function initActiveNav() {
        var links = document.querySelectorAll('.riseup-nav-link');
        var here = window.location.pathname.replace(/\/$/, '');
        links.forEach(function (a) {
            var href = (a.getAttribute('href') || '').replace(/\/$/, '');
            if (!href) return;
            if (href === here) a.classList.add('is-active');
        });
    }

    /* ---------- 6. Toast dari flash session (kalau ada data-flash) ---------- */
    function initToast() {
        var success = document.body.getAttribute('data-flash-success');
        var error   = document.body.getAttribute('data-flash-error');
        if (!success && !error) return;

        var wrap = document.createElement('div');
        wrap.className = 'toast-wrap';
        document.body.appendChild(wrap);

        function pushToast(text, kind) {
            var t = document.createElement('div');
            t.className = 'toast ' + (kind === 'success' ? 'is-success' : 'is-error');
            t.textContent = text;
            wrap.appendChild(t);
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 4800);
        }
        if (success) pushToast(success, 'success');
        if (error)   pushToast(error,   'error');
    }

    /* ---------- 7. Micro-animate: level up angka pas load ---------- */
    function initFocusRing() {
        // Tambahkan class 'has-value' pada input yang sudah terisi
        var inputs = document.querySelectorAll('input, textarea');
        inputs.forEach(function (inp) {
            if (inp.value && inp.value.length) inp.classList.add('has-value');
            inp.addEventListener('input', function () {
                inp.classList.toggle('has-value', inp.value.length > 0);
            });
        });
    }

    /* ---------- BOOT ---------- */
    function boot() {
        try { initReveal(); }        catch (e) {}
        try { initCountUps(); }      catch (e) {}
        try { initProgressBars(); }  catch (e) {}
        try { initCalendar(); }      catch (e) {}
        try { initActiveNav(); }     catch (e) {}
        try { initToast(); }         catch (e) {}
        try { initFocusRing(); }     catch (e) {}
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
