{{--
    Buddy (Maskot) + Reminder Jam Rawan
    Variabel di-share otomatis via View Composer (AppServiceProvider):
      $buddyName, $buddyRiskStart, $buddyRiskEnd  (boleh null)
    Cara pakai di view student mana pun:
      @include('student.partials.buddy')
    Pastikan view sudah me-load css/riseup-ui.css untuk token warna.
--}}
@php
    $buddyName      = $buddyName      ?? 'Kamu';
    $buddyRiskStart = $buddyRiskStart ?? null;   // format "HH:MM" atau "HH:MM:SS"
    $buddyRiskEnd   = $buddyRiskEnd   ?? null;
@endphp

<link rel="stylesheet" href="{{ asset('css/buddy.css') }}">

<div class="buddy-wrap"
     data-risk-start="{{ $buddyRiskStart }}"
     data-risk-end="{{ $buddyRiskEnd }}"
     data-checkin-url="{{ route('student.dashboard') }}#checkin"
     data-quest-url="{{ route('student.quests.index') }}">

    <div class="buddy-bubble" id="buddyBubble">
        <button class="bb-close" id="buddyClose" aria-label="Tutup">&times;</button>
        <p class="bb-title" id="buddyTitle">Buddy</p>
        <p class="bb-text" id="buddyText">Halo! Aku Buddy, teman perjalananmu.</p>
        <div class="bb-actions" id="buddyActions"></div>
    </div>

    <button class="buddy-fab" id="buddyFab" aria-label="Buka Buddy">
        <span class="buddy-dot"></span>
        {{-- maskot SVG sederhana (tetes/embun ramah) --}}
        <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M32 6C32 6 14 26 14 40a18 18 0 0 0 36 0C50 26 32 6 32 6Z" fill="#ffffff"/>
            <circle cx="25" cy="40" r="3.4" fill="#1f2937"/>
            <circle cx="39" cy="40" r="3.4" fill="#1f2937"/>
            <circle cx="26.2" cy="39" r="1.1" fill="#ffffff"/>
            <circle cx="40.2" cy="39" r="1.1" fill="#ffffff"/>
            <path d="M26 47c3 3 9 3 12 0" fill="none" stroke="#1f2937" stroke-width="2.6" stroke-linecap="round"/>
            <circle cx="21" cy="45" r="2.6" fill="#ff8a00" opacity="0.5"/>
            <circle cx="43" cy="45" r="2.6" fill="#ff8a00" opacity="0.5"/>
        </svg>
    </button>
</div>

<script>
(function () {
    var wrap    = document.querySelector('.buddy-wrap');
    if (!wrap) return;

    var fab     = document.getElementById('buddyFab');
    var bubble  = document.getElementById('buddyBubble');
    var titleEl = document.getElementById('buddyTitle');
    var textEl  = document.getElementById('buddyText');
    var actEl   = document.getElementById('buddyActions');
    var closeEl = document.getElementById('buddyClose');

    var checkinUrl = wrap.getAttribute('data-checkin-url');
    var questUrl   = wrap.getAttribute('data-quest-url');

    var motivations = [
        'Satu keputusan baik hari ini lebih berharga dari seribu penyesalan besok.',
        'Dorongan itu sementara. Kamu lebih kuat dari sekadar godaan sesaat.',
        'Setiap jam yang kamu lewati tanpa judi adalah kemenangan kecil yang nyata.',
        'Coba tarik napas pelan. Kamu sedang membangun versi dirimu yang lebih baik.',
        'Ingat alasanmu memulai. Kamu layak untuk masa depan yang lebih tenang.'
    ];

    function pickMotivation() {
        return motivations[Math.floor(Math.random() * motivations.length)];
    }

    function toMinutes(t) {
        if (!t) return null;
        var p = t.split(':');
        if (p.length < 2) return null;
        return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
    }

    // Cek apakah waktu sekarang di dalam jam rawan
    function inRiskWindow() {
        var start = toMinutes(wrap.getAttribute('data-risk-start'));
        var end   = toMinutes(wrap.getAttribute('data-risk-end'));
        if (start === null || end === null) return false;

        var now = new Date();
        var cur = now.getHours() * 60 + now.getMinutes();

        // window normal (20:00-23:00) atau lintas tengah malam (22:00-02:00)
        if (start <= end) return cur >= start && cur <= end;
        return cur >= start || cur <= end;
    }

    function openBubble(opts) {
        titleEl.textContent = opts.title || 'Buddy';
        textEl.textContent  = opts.text || '';
        bubble.classList.toggle('is-alert', !!opts.alert);

        actEl.innerHTML = '';
        (opts.actions || []).forEach(function (a) {
            var el;
            if (a.href) { el = document.createElement('a'); el.href = a.href; }
            else { el = document.createElement('button'); el.type = 'button'; if (a.onClick) el.addEventListener('click', a.onClick); }
            el.className = 'bb-btn ' + (a.style || 'ghost');
            el.textContent = a.label;
            actEl.appendChild(el);
        });

        bubble.classList.add('show');
    }

    function closeBubble() { bubble.classList.remove('show'); fab.classList.remove('has-alert'); }

    function showMotivation() {
        openBubble({
            title: 'Buddy',
            text: pickMotivation(),
            alert: false,
            actions: [
                { label: 'Isi Jurnal / Quest', href: questUrl, style: 'primary' }
            ]
        });
    }

    function showRiskReminder() {
        fab.classList.add('has-alert');
        openBubble({
            title: 'Jam Rawan',
            text: 'Ini waktu yang biasanya rawan buat kamu. Yuk alihkan dorongan dengan aktivitas positif atau check-in dulu.',
            alert: true,
            actions: [
                { label: 'Check-in', href: checkinUrl, style: 'primary' },
                { label: 'Positive Quest', href: questUrl, style: 'ghost' }
            ]
        });
    }

    // Toggle saat maskot diklik
    fab.addEventListener('click', function () {
        if (bubble.classList.contains('show')) { closeBubble(); }
        else if (inRiskWindow()) { showRiskReminder(); }
        else { showMotivation(); }
    });

    closeEl.addEventListener('click', closeBubble);

    // Auto-munculkan reminder kalau sedang jam rawan (sekali per kunjungan halaman)
    window.addEventListener('load', function () {
        if (inRiskWindow()) {
            setTimeout(showRiskReminder, 1200);
        }
    });
})();
</script>
