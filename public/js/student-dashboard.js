const slider = document.getElementById('urgeSlider');
const urgeText = document.getElementById('urgeText');

if (slider && urgeText) {
    slider.addEventListener('input', () => {
        urgeText.textContent = slider.value;
    });
}

const statusColor = document.getElementById('statusColor');
const relapseReason = document.getElementById('relapseReason');
const relapseHint = document.getElementById('relapseHint');

function updateRelapseRequirement() {
    if (!statusColor || !relapseReason) return;

    if (statusColor.value === 'red') {
        relapseReason.required = true;
        relapseReason.placeholder = 'Contoh: bosan, stres, teman';
        if (relapseHint) {
            relapseHint.textContent = 'Status Relapse dipilih, alasan wajib diisi.';
        }
    } else {
        relapseReason.required = false;
        relapseReason.value = '';
        relapseReason.placeholder = 'Boleh kosong kalau tidak relapse';
        if (relapseHint) {
            relapseHint.textContent = 'Jika status Relapse, alasan wajib diisi.';
        }
    }
}

if (statusColor) {
    statusColor.addEventListener('change', updateRelapseRequirement);
    updateRelapseRequirement();
}

async function loadStudentSummary() {
    const apiContainer = document.getElementById('studentSummaryApi');

    if (!apiContainer) return;

    const apiUrl = apiContainer.dataset.apiUrl;

    try {
        const response = await fetch(apiUrl);

        if (!response.ok) {
            throw new Error('Gagal mengambil data API');
        }

        const data = await response.json();

        document.getElementById('apiGreenDays').textContent = data.green_days;
        document.getElementById('apiRedDays').textContent = data.red_days;
        document.getElementById('apiTotalXp').textContent = data.total_xp;
        document.getElementById('apiLevel').textContent = data.level;
    } catch (error) {
        console.error(error);

        document.getElementById('apiGreenDays').textContent = 'Error';
        document.getElementById('apiRedDays').textContent = 'Error';
        document.getElementById('apiTotalXp').textContent = 'Error';
        document.getElementById('apiLevel').textContent = 'Error';
    }
}

loadStudentSummary();