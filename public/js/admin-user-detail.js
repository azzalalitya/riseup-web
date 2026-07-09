const checkinSearch = document.getElementById('checkinSearch');
const checkinTable = document.getElementById('checkinTable');

function filterCheckins() {
    if (!checkinSearch || !checkinTable) return;

    const keyword = checkinSearch.value.toLowerCase().trim();
    const rows = checkinTable.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const isEmpty = row.querySelector('.empty-cell');

        if (isEmpty) {
            return;
        }

        const text = row.textContent.toLowerCase();
        const match = text.includes(keyword);

        row.classList.toggle('hidden-row', !match);
    });
}

if (checkinSearch) {
    checkinSearch.addEventListener('input', filterCheckins);
}