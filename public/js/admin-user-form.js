const passwordInput = document.getElementById('passwordInput');
const passwordMeterBar = document.getElementById('passwordMeterBar');
const passwordHint = document.getElementById('passwordHint');

function updatePasswordMeter() {
    if (!passwordInput || !passwordMeterBar || !passwordHint) return;

    const value = passwordInput.value;

    if (!value) {
        passwordMeterBar.style.width = '0';
        passwordHint.textContent = passwordInput.required
            ? 'Gunakan minimal 8 karakter.'
            : 'Kosongkan jika tidak ingin mengganti password.';
        return;
    }

    let score = 0;

    if (value.length >= 8) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;

    const width = [0, 30, 55, 78, 100][score];
    passwordMeterBar.style.width = `${width}%`;

    if (score <= 1) {
        passwordMeterBar.style.background = '#ef4444';
        passwordHint.textContent = 'Password masih lemah. Minimal 8 karakter.';
    } else if (score === 2 || score === 3) {
        passwordMeterBar.style.background = '#f59e0b';
        passwordHint.textContent = 'Cukup baik. Tambahkan angka, huruf besar, atau simbol.';
    } else {
        passwordMeterBar.style.background = '#22c55e';
        passwordHint.textContent = 'Password kuat.';
    }
}

if (passwordInput) {
    passwordInput.addEventListener('input', updatePasswordMeter);
    updatePasswordMeter();
}