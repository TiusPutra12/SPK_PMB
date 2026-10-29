function selectStudent(id) {
    const splitContainer = document.getElementById('splitContainer');
    const targetRow = document.querySelector(`tr.data-row[data-id="${id}"]`);
    const targetPane = document.getElementById(`pane-${id}`);

    if (!splitContainer || !targetRow || !targetPane) return;

    splitContainer.classList.add('is-split');
    document.querySelectorAll('tr.data-row').forEach(row => row.classList.remove('is-active'));
    targetRow.classList.add('is-active');
    document.querySelectorAll('.student-detail-pane').forEach(pane => {
        pane.classList.remove('is-active-pane');
        pane.classList.add('hidden');
    });
    targetPane.classList.remove('hidden');
    targetPane.classList.add('is-active-pane');

    const nama = targetPane.getAttribute('data-nama') || '';
    const inisial = targetPane.getAttribute('data-inisial') || 'A';
    const sub = targetPane.getAttribute('data-sub') || '';
    const status = targetPane.getAttribute('data-status') || 'Diterima';
    const statusClass = targetPane.getAttribute('data-statusclass') || 'diterima';
    const avatarEl = document.getElementById('detailAvatar');
    const namaHeaderEl = document.getElementById('detailNamaHeader');
    const subHeaderEl = document.getElementById('detailSubHeader');
    const badgeHeaderEl = document.getElementById('detailBadgeHeader');

    if (avatarEl) avatarEl.textContent = inisial;
    if (namaHeaderEl) namaHeaderEl.textContent = nama;
    if (subHeaderEl) subHeaderEl.textContent = sub;
    if (badgeHeaderEl) {
        badgeHeaderEl.textContent = status;
        badgeHeaderEl.className = `status-pill ${statusClass}`;
    }
    const detailBody = document.getElementById('detailBodyContainer');
    if (detailBody) detailBody.scrollTop = 0;
}
function closeStudentDetail() {
    const splitContainer = document.getElementById('splitContainer');
    if (splitContainer) {
        splitContainer.classList.remove('is-split');
    }

    document.querySelectorAll('tr.data-row').forEach(row => row.classList.remove('is-active'));
    document.querySelectorAll('.student-detail-pane').forEach(pane => {
        pane.classList.remove('is-active-pane');
        pane.classList.add('hidden');
    });
}

window.selectStudent = selectStudent;
window.closeStudentDetail = closeStudentDetail;
window.toggleDetailRow = selectStudent;
document.addEventListener('DOMContentLoaded', () => {
    const filterProdi = document.getElementById('filterProdi');
    const filterTahun = document.getElementById('filterTahun');
    const searchNama = document.getElementById('searchNama');
    const btnResetFilter = document.getElementById('btnResetFilter');
    const counterData = document.getElementById('counterData');
    const noDataNotice = document.getElementById('noDataNotice');
    const dataRows = document.querySelectorAll('tr.data-row');

    function applyFilter() {
        if (!filterProdi || !filterTahun || !searchNama) return;

        const selectedProdi = filterProdi.value.trim().toLowerCase();
        const selectedTahun = filterTahun.value.trim().toLowerCase();
        const searchKeyword = searchNama.value.trim().toLowerCase();

        let visibleCount = 0;
        let activeRowStillVisible = false;

        dataRows.forEach(row => {
            const prodi = (row.getAttribute('data-prodi') || '').toLowerCase();
            const tahun = (row.getAttribute('data-tahun') || '').toLowerCase();
            const nama = (row.getAttribute('data-nama') || '').toLowerCase();

            const matchProdi = selectedProdi === '' || prodi === selectedProdi;
            const matchTahun = selectedTahun === '' || tahun === selectedTahun;
            const matchNama = searchKeyword === '' || nama.includes(searchKeyword);

            if (matchProdi && matchTahun && matchNama) {
                row.style.display = '';
                visibleCount++;
                const numCell = row.querySelector('.row-number');
                if (numCell) numCell.textContent = visibleCount;

                if (row.classList.contains('is-active')) {
                    activeRowStillVisible = true;
                }
            } else {
                row.style.display = 'none';
            }
        });

        if (!activeRowStillVisible) {
            closeStudentDetail();
        }

        if (counterData) {
            counterData.textContent = `${visibleCount} Data`;
        }

        if (noDataNotice) {
            if (visibleCount === 0) {
                noDataNotice.classList.remove('hidden');
            } else {
                noDataNotice.classList.add('hidden');
            }
        }
    }

    if (filterProdi) filterProdi.addEventListener('change', applyFilter);
    if (filterTahun) filterTahun.addEventListener('change', applyFilter);
    if (searchNama) searchNama.addEventListener('input', applyFilter);

    if (btnResetFilter) {
        btnResetFilter.addEventListener('click', () => {
            filterProdi.value = '';
            filterTahun.value = '';
            searchNama.value = '';
            applyFilter();
        });
    }
});
