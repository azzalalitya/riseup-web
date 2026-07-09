const searchInput = document.getElementById('searchInput');
const userTable = document.getElementById('userTable');
const visibleCount = document.getElementById('visibleCount');

function filterUsers() {
    if (!searchInput || !userTable) return;

    const keyword = searchInput.value.toLowerCase().trim();
    const rows = userTable.querySelectorAll('tbody tr');
    let visibleRows = 0;

    rows.forEach(row => {
        const isEmptyRow = row.querySelector('.empty-cell');

        if (isEmptyRow) {
            return;
        }

        const rowText = row.textContent.toLowerCase();
        const isMatch = rowText.includes(keyword);

        row.classList.toggle('hidden-row', !isMatch);

        if (isMatch) {
            visibleRows++;
        }
    });

    if (visibleCount) {
        visibleCount.textContent = visibleRows;
    }
}

if (searchInput) {
    searchInput.addEventListener('input', filterUsers);
    filterUsers();
}

const deleteForms = document.querySelectorAll('.delete-form');

deleteForms.forEach(form => {
    form.addEventListener('submit', event => {
        const email = form.dataset.email || 'user ini';
        const confirmed = confirm(`Yakin ingin menghapus ${email}? Data user akan dihapus dari database.`);

        if (!confirmed) {
            event.preventDefault();
        }
    });
});
async function loadAdminSummary() {
    const apiContainer = document.getElementById('adminSummaryApi');

    if (!apiContainer) return;

    const apiUrl = apiContainer.dataset.apiUrl;

    try {
        const response = await fetch(apiUrl);

        if (!response.ok) {
            throw new Error('Gagal mengambil data admin API');
        }

        const data = await response.json();

        document.getElementById('apiActiveUsers').textContent = data.active_users;
        document.getElementById('apiInactiveUsers').textContent = data.inactive_users;
        document.getElementById('apiOnboardedUsers').textContent = data.onboarded_users;
        document.getElementById('apiTotalXp').textContent = data.total_xp;
        document.getElementById('apiGreenWeek').textContent = data.green_checkins_this_week;
        document.getElementById('apiRedWeek').textContent = data.red_checkins_this_week;
    } catch (error) {
        console.error(error);

        document.getElementById('apiActiveUsers').textContent = 'Error';
        document.getElementById('apiInactiveUsers').textContent = 'Error';
        document.getElementById('apiOnboardedUsers').textContent = 'Error';
        document.getElementById('apiTotalXp').textContent = 'Error';
        document.getElementById('apiGreenWeek').textContent = 'Error';
        document.getElementById('apiRedWeek').textContent = 'Error';
    }
}

loadAdminSummary();