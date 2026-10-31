
let maxStepAllowed = 1;

function updateSubScore(idK) {
    const selectEl = document.getElementById('sub_select_' + idK);
    const inputEl = document.getElementById('sub_nilai_' + idK);
    if (!selectEl || !inputEl) return;
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const score = selectedOpt ? selectedOpt.getAttribute('data-score') : '';
    if (score) {
        inputEl.value = parseFloat(score).toFixed(2);
    }
}

function syncPenghasilanInput() {
    const selectEl = document.getElementById('penghasilanSelect');
    const inputEl = document.getElementById('penghasilanInput');
    if (selectEl && inputEl && selectEl.value) {
        inputEl.value = selectEl.value;
    }
}

function clearPenghasilan() {
    const inputEl = document.getElementById('penghasilanInput');
    if (inputEl) inputEl.value = '';
}

function toggleBeasiswaAutoNotice() {
    const golSelect = document.getElementById('golonganSelect');
    if (golSelect && golSelect.value === 'Beasiswa') {
        const node3 = document.getElementById('step-node-3');
        if (node3) {
            node3.classList.add('ring-4', 'ring-amber-300');
        }
    }
}

function goToStep(targetStep) {

    if (targetStep > maxStepAllowed) {
        if (maxStepAllowed === 1) {
            validateAndGoStep2();
        } else if (maxStepAllowed === 2) {
            validateAndGoStep3();
        }
        return;
    }

    const sec1 = document.getElementById('section-step-1');
    const sec2 = document.getElementById('section-step-2');
    const sec3 = document.getElementById('section-step-3');

    if (!sec1 || !sec2 || !sec3) return;

    sec1.classList.add('hidden');
    sec2.classList.add('hidden');
    sec3.classList.add('hidden');

    const node1 = document.getElementById('step-node-1');
    const node2 = document.getElementById('step-node-2');
    const node3 = document.getElementById('step-node-3');
    const node4 = document.getElementById('step-node-4');

    const lbl1 = document.getElementById('step-label-1');
    const lbl2 = document.getElementById('step-label-2');
    const lbl3 = document.getElementById('step-label-3');
    const lbl4 = document.getElementById('step-label-4');

    const line1 = document.getElementById('step-line-1');
    const line2 = document.getElementById('step-line-2');
    const line3 = document.getElementById('step-line-3');

    function setNodeActive(node, label, numText) {
        if (!node || !label) return;
        node.className = "w-12 h-12 rounded-full border-4 border-purple-600 bg-purple-600 text-white font-bold text-base flex items-center justify-center shadow-lg transition-all duration-300 scale-105";
        node.innerHTML = numText;
        label.className = "text-xs font-bold text-purple-700 mt-2 text-center transition-colors";
    }

    function setNodeDone(node, label) {
        if (!node || !label) return;
        node.className = "w-12 h-12 rounded-full border-4 border-emerald-500 bg-emerald-500 text-white font-bold text-base flex items-center justify-center shadow-md transition-all duration-300";
        node.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>';
        label.className = "text-xs font-bold text-emerald-600 mt-2 text-center transition-colors";
    }

    function setNodeInactive(node, label, numText) {
        if (!node || !label) return;
        node.className = "w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-400 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300";
        node.innerHTML = numText;
        label.className = "text-xs font-semibold text-gray-400 mt-2 text-center transition-colors";
    }

    const checkIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>';

    if (targetStep === 1) {
        sec1.classList.remove('hidden');
        setNodeActive(node1, lbl1, '1');
        setNodeInactive(node2, lbl2, '2');
        setNodeInactive(node3, lbl3, '3');
        setNodeInactive(node4, lbl4, checkIcon);

        if (line1) line1.style.width = '0%';
        if (line2) line2.style.width = '0%';
        if (line3) line3.style.width = '0%';
    } else if (targetStep === 2) {
        sec2.classList.remove('hidden');
        setNodeDone(node1, lbl1);
        setNodeActive(node2, lbl2, '2');
        setNodeInactive(node3, lbl3, '3');
        setNodeInactive(node4, lbl4, checkIcon);

        if (line1) line1.style.width = '100%';
        if (line2) line2.style.width = '0%';
        if (line3) line3.style.width = '0%';
    } else if (targetStep === 3) {
        sec3.classList.remove('hidden');
        setNodeDone(node1, lbl1);
        setNodeDone(node2, lbl2);

        if (node3 && lbl3) {
            node3.className = "w-12 h-12 rounded-full border-4 border-amber-500 bg-amber-500 text-white font-bold text-base flex items-center justify-center shadow-lg transition-all duration-300 scale-105";
            node3.innerHTML = '3';
            lbl3.className = "text-xs font-bold text-amber-700 mt-2 text-center transition-colors";
        }
        setNodeInactive(node4, lbl4, checkIcon);

        if (line1) line1.style.width = '100%';
        if (line2) line2.style.width = '100%';
        if (line3) line3.style.width = '0%';
    }

    window.scrollTo({ top: 120, behavior: 'smooth' });
}

function validateAndGoStep2() {
    const nimEl = document.getElementById('nim');
    const namaEl = document.getElementById('nama');
    if (nimEl && !nimEl.value.trim()) {
        alert('Silakan lengkapi NIM / Nomor Pendaftaran terlebih dahulu.');
        nimEl.focus();
        goToStep(1);
        return;
    }
    if (namaEl && !namaEl.value.trim()) {
        alert('Silakan masukkan Nama Lengkap calon mahasiswa terlebih dahulu.');
        namaEl.focus();
        goToStep(1);
        return;
    }
    maxStepAllowed = Math.max(maxStepAllowed, 2);
    goToStep(2);
}

function validateAndGoStep3() {
    if (maxStepAllowed < 2) {
        validateAndGoStep2();
        return;
    }
    maxStepAllowed = Math.max(maxStepAllowed, 3);
    goToStep(3);
}

window.updateSubScore = updateSubScore;
window.syncPenghasilanInput = syncPenghasilanInput;
window.clearPenghasilan = clearPenghasilan;
window.toggleBeasiswaAutoNotice = toggleBeasiswaAutoNotice;
window.goToStep = goToStep;
window.validateAndGoStep2 = validateAndGoStep2;
window.validateAndGoStep3 = validateAndGoStep3;



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
        const normalizedClass = (status.toLowerCase() === 'diterima') ? 'diterima' : 'tidak-diterima';
        badgeHeaderEl.className = `status-pill ${normalizedClass}`;
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

    if (!filterProdi && !filterTahun && !searchNama && dataRows.length === 0) return;

    function applyFilter() {
        const selectedProdi = filterProdi ? filterProdi.value.trim().toLowerCase() : '';
        const selectedTahun = filterTahun ? filterTahun.value.trim().toLowerCase() : '';
        const searchKeyword = searchNama ? searchNama.value.trim().toLowerCase() : '';

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
            if (filterProdi) filterProdi.value = '';
            if (filterTahun) filterTahun.value = '';
            if (searchNama) searchNama.value = '';
            applyFilter();
        });
    }
});


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
        const normalizedClass = (status.toLowerCase() === 'diterima') ? 'diterima' : 'tidak-diterima';
        badgeHeaderEl.className = `status-pill ${normalizedClass}`;
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

    if (!filterProdi && !filterTahun && !searchNama && dataRows.length === 0) return;

    function applyFilter() {
        const selectedProdi = filterProdi ? filterProdi.value.trim().toLowerCase() : '';
        const selectedTahun = filterTahun ? filterTahun.value.trim().toLowerCase() : '';
        const searchKeyword = searchNama ? searchNama.value.trim().toLowerCase() : '';

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
            if (filterProdi) filterProdi.value = '';
            if (filterTahun) filterTahun.value = '';
            if (searchNama) searchNama.value = '';
            applyFilter();
        });
    }
});
