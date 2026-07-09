const tabButtons = document.querySelectorAll('.tab-btn');
const forms = document.querySelectorAll('.form-card');

const authMascot = document.getElementById('authMascot');
const mainMascot = document.getElementById('mainMascot');
const authSubtitle = document.getElementById('authSubtitle');

const mascots = {
    normal: mainMascot?.dataset.normal,
    happy: mainMascot?.dataset.happy,
    wow: mainMascot?.dataset.wow,
    huh: mainMascot?.dataset.huh,
    sad: mainMascot?.dataset.sad,
    no: mainMascot?.dataset.no,
    crossed: mainMascot?.dataset.crossed,
};

function setMascot(imageKey, target = 'both') {
    const imagePath = mascots[imageKey];

    if (!imagePath) return;

    if ((target === 'main' || target === 'both') && mainMascot) {
        mainMascot.src = imagePath;
    }

    if ((target === 'auth' || target === 'both') && authMascot) {
        authMascot.src = imagePath;
    }
}

function activateForm(targetId) {
    tabButtons.forEach(item => item.classList.remove('active'));
    forms.forEach(form => form.classList.remove('active'));

    const activeButton = document.querySelector(`[data-target="${targetId}"]`);
    const activeForm = document.getElementById(targetId);

    if (activeButton && activeForm) {
        activeButton.classList.add('active');
        activeForm.classList.add('active');
    }

    if (targetId === 'registerForm') {
        setMascot('wow', 'auth');
        setMascot('happy', 'main');

        if (authSubtitle) {
            authSubtitle.textContent = 'Mulai dari langkah kecil hari ini.';
        }
    } else {
        setMascot('normal', 'auth');
        setMascot('happy', 'main');

        if (authSubtitle) {
            authSubtitle.textContent = 'Lanjutkan perjalananmu hari ini.';
        }
    }
}

tabButtons.forEach(button => {
    button.addEventListener('click', () => {
        activateForm(button.dataset.target);
    });
});

const goRegister = document.getElementById('goRegister');
const goLogin = document.getElementById('goLogin');
const authPanel = document.querySelector('.auth-panel');

/* ---------- Modal open/close ---------- */
const authModal = document.getElementById('authModal');
const authBackdrop = document.getElementById('authBackdrop');
const authClose = document.getElementById('authClose');

function openAuthModal(targetId) {
    if (targetId) activateForm(targetId);
    authModal?.classList.add('open');
    authBackdrop?.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    authModal?.classList.remove('open');
    authBackdrop?.classList.remove('open');
    document.body.style.overflow = '';
}

if (goRegister) {
    goRegister.addEventListener('click', () => openAuthModal('registerForm'));
}
if (goLogin) {
    goLogin.addEventListener('click', () => openAuthModal('loginForm'));
}
authClose?.addEventListener('click', closeAuthModal);
authBackdrop?.addEventListener('click', closeAuthModal);
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAuthModal();
});

/* Auto-buka modal kalau ada error validasi */
if (document.body.getAttribute('data-open-modal') === '1') {
    openAuthModal('loginForm');
}

const registerPassword = document.getElementById('registerPassword');
const passwordMeterBar = document.getElementById('passwordMeterBar');
const passwordHint = document.getElementById('passwordHint');

if (registerPassword) {
    registerPassword.addEventListener('input', () => {
        const value = registerPassword.value;
        let score = 0;

        if (value.length >= 8) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const width = [0, 30, 55, 78, 100][score];

        if (passwordMeterBar) {
            passwordMeterBar.style.width = `${width}%`;
        }

        if (!passwordHint || !passwordMeterBar) return;

        if (score <= 1) {
            passwordMeterBar.style.background = '#ef4444';
            passwordHint.textContent = 'Password masih lemah. Minimal 8 karakter.';
            setMascot('huh', 'auth');
        } else if (score === 2 || score === 3) {
            passwordMeterBar.style.background = '#f59e0b';
            passwordHint.textContent = 'Cukup baik. Tambahkan angka atau huruf besar agar lebih kuat.';
            setMascot('normal', 'auth');
        } else {
            passwordMeterBar.style.background = '#22c55e';
            passwordHint.textContent = 'Password kuat.';
            setMascot('happy', 'auth');
        }
    });
}

const emailInputs = document.querySelectorAll('input[type="email"]');

emailInputs.forEach(input => {
    input.addEventListener('input', () => {
        if (input.value.includes('@')) {
            setMascot('wow', 'main');
        } else {
            setMascot('happy', 'main');
        }
    });
});