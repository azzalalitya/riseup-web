const form = document.getElementById('onboardingForm');
const trackedFields = document.querySelectorAll('.tracked-field');
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');
const stepNumber = document.getElementById('stepNumber');
const mascot = document.getElementById('onboardingMascot');
const tip = document.getElementById('onboardingTip');

const mascotImages = {
    normal: mascot?.dataset.normal,
    happy: mascot?.dataset.happy,
    wow: mascot?.dataset.wow,
    huh: mascot?.dataset.huh,
};

const tips = [
    'Isi pelan-pelan. Tidak harus sempurna, yang penting mulai.',
    'Bagus. Kamu sudah mulai mengenali pola awal.',
    'Setiap pilihan akan membantu dashboard membaca progress kamu.',
    'Sedikit lagi. Setelah ini kamu bisa mulai check-in harian.',
    'Mantap. Data baseline hampir lengkap.',
    'Siap naik lagi. Simpan dan masuk dashboard.'
];

function setMascot(type) {
    if (!mascot || !mascotImages[type]) return;
    mascot.src = mascotImages[type];
}

function calculateProgress() {
    let filled = 0;

    trackedFields.forEach(field => {
        if (field.value && field.value.trim() !== '') {
            filled++;
        }
    });

    const total = trackedFields.length;
    const percentage = Math.round((filled / total) * 100);

    if (progressBar) {
        progressBar.style.width = `${percentage}%`;
    }

    if (progressText) {
        progressText.textContent = `${percentage}%`;
    }

    if (stepNumber) {
        stepNumber.textContent = Math.min(filled + 1, total);
    }

    if (tip) {
        tip.textContent = tips[Math.min(filled, tips.length - 1)];
    }

    if (percentage === 0) {
        setMascot('normal');
    } else if (percentage < 50) {
        setMascot('huh');
    } else if (percentage < 100) {
        setMascot('wow');
    } else {
        setMascot('happy');
    }
}

trackedFields.forEach(field => {
    field.addEventListener('input', calculateProgress);
    field.addEventListener('change', calculateProgress);
});

if (form) {
    form.addEventListener('submit', event => {
        const moneyFields = document.querySelectorAll('.money-field');

        for (const field of moneyFields) {
            if (Number(field.value) > 9999999999) {
                event.preventDefault();
                alert('Nominal terlalu besar. Maksimal 9.999.999.999');
                field.focus();
                return;
            }
        }
    });
}

calculateProgress();