
let maxStepAllowed = 1;

function updateSubScore(idK) {
    const selectEl = document.getElementById('sub_select_' + idK);
    const inputEl = document.getElementById('sub_nilai_' + idK);
    if (!selectEl || !inputEl) return;
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const score = selectedOpt ? selectedOpt.getAttribute('data-score') : '';
    if (score) {
        inputEl.value = parseFloat(score).toFixed(2);
    } else {
        inputEl.value = '';
    }
    if (typeof calculateCombinedScore === 'function') {
        calculateCombinedScore();
    }
}

function autoMatchSubKriteria(idK) {
    const input = document.getElementById('sub_nilai_' + idK);
    const select = document.getElementById('sub_select_' + idK);
    const badge = document.getElementById('kategori_badge_' + idK);
    const scoreInd = document.getElementById('score_indicator_' + idK);
    if (!input || !select) return;

    const rawVal = input.value.trim();
    if (rawVal === '' || isNaN(rawVal)) {
        select.value = '';
        if (badge) badge.innerHTML = '';
        if (scoreInd) scoreInd.textContent = '';
        if (typeof calculateCombinedScore === 'function') calculateCombinedScore();
        return;
    }

    const val = parseFloat(rawVal);
    let matchedSubId = null;
    let matchedSubName = '';
    let matchedScore = '';

    const options = Array.from(select.options).filter(opt => opt.value !== "");

    for (let opt of options) {
        const name = opt.getAttribute('data-nama') || opt.text;
        const rangeMatch = name.match(/(\d+(?:\.\d+)?)\s*<=\s*(?:nilai|[a-zA-Z]+)?\s*<\s*(\d+(?:\.\d+)?)/i);
        if (rangeMatch) {
            const min = parseFloat(rangeMatch[1]);
            const max = parseFloat(rangeMatch[2]);
            if (val >= min && val < max) {
                matchedSubId = opt.value;
                matchedSubName = name;
                matchedScore = opt.getAttribute('data-score');
                break;
            }
        }
        const dashMatch = name.match(/^(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)$/);
        if (dashMatch) {
            const min = parseFloat(dashMatch[1]);
            const max = parseFloat(dashMatch[2]);
            if (val >= min && val <= max) {
                matchedSubId = opt.value;
                matchedSubName = name;
                matchedScore = opt.getAttribute('data-score');
                break;
            }
        }
    }

    if (!matchedSubId) {
        for (let opt of options) {
            const name = opt.getAttribute('data-nama') || opt.text;
            const gteMatch = name.match(/>=\s*(\d+(?:\.\d+)?)/);
            if (gteMatch) {
                const threshold = parseFloat(gteMatch[1]);
                if (val >= threshold) {
                    matchedSubId = opt.value;
                    matchedSubName = name;
                    matchedScore = opt.getAttribute('data-score');
                    break;
                }
            }
        }
    }

    if (!matchedSubId) {
        for (let opt of options) {
            const name = opt.getAttribute('data-nama') || opt.text;
            const ltMatch = name.match(/^<\s*(\d+(?:\.\d+)?)/);
            if (ltMatch) {
                const threshold = parseFloat(ltMatch[1]);
                if (val < threshold) {
                    matchedSubId = opt.value;
                    matchedSubName = name;
                    matchedScore = opt.getAttribute('data-score');
                    break;
                }
            }
        }
    }

    if (!matchedSubId) {
        if (idK === 1) matchedSubId = val >= 80 ? '3' : (val >= 70 ? '2' : '1');
        else if (idK === 4) matchedSubId = val >= 75 ? '12' : (val >= 65 ? '11' : '10');
        else if (idK === 5) matchedSubId = val >= 350 ? '15' : (val >= 300 ? '14' : '13');

        if (matchedSubId) {
            const targetOpt = options.find(o => o.value == matchedSubId);
            if (targetOpt) {
                matchedSubName = targetOpt.getAttribute('data-nama') || targetOpt.text;
                matchedScore = targetOpt.getAttribute('data-score');
            }
        }
    }

    if (matchedSubId) {
        select.value = matchedSubId;
        if (scoreInd && matchedScore) scoreInd.textContent = 'Bobot SPK: ' + matchedScore;
        if (badge) {
            badge.className = "text-xs mt-1.5 font-semibold text-emerald-600 flex items-center gap-1";
            badge.innerHTML = `<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Kategori: <strong>${matchedSubName}</strong> (Skor: ${matchedScore || '-'})`;
        }
    } else {
        select.value = '';
        if (badge) badge.innerHTML = '';
        if (scoreInd) scoreInd.textContent = '';
    }

    if (typeof calculateCombinedScore === 'function') {
        calculateCombinedScore();
    }
}

function onManualSubSelectChange(idK) {
    const select = document.getElementById('sub_select_' + idK);
    const scoreInd = document.getElementById('score_indicator_' + idK);
    const badge = document.getElementById('kategori_badge_' + idK);
    if (!select) return;
    const opt = select.options[select.selectedIndex];
    if (opt && opt.value !== '') {
        const score = opt.getAttribute('data-score');
        const name = opt.getAttribute('data-nama') || opt.text;
        if (scoreInd && score) scoreInd.textContent = 'Bobot SPK: ' + score;
        if (badge) {
            badge.className = "text-xs mt-1.5 font-semibold text-emerald-600 flex items-center gap-1";
            badge.innerHTML = `<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Kategori: <strong>${name}</strong> (Skor: ${score || '-'})`;
        }
    } else {
        if (scoreInd) scoreInd.textContent = '';
        if (badge) badge.innerHTML = '';
    }

    if (typeof calculateCombinedScore === 'function') {
        calculateCombinedScore();
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

function goToStep(targetStep) {
    if (targetStep > maxStepAllowed) {
        if (maxStepAllowed === 1) {
            validateAndGoStep2();
        }
        return;
    }

    const sec1 = document.getElementById('section-step-1');
    const sec2 = document.getElementById('section-step-2');

    if (!sec1 || !sec2) return;

    sec1.classList.add('hidden');
    sec2.classList.add('hidden');

    const node1 = document.getElementById('step-node-1');
    const node2 = document.getElementById('step-node-2');
    const lbl1 = document.getElementById('step-label-1');
    const lbl2 = document.getElementById('step-label-2');
    const line1 = document.getElementById('step-line-1');

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

    if (targetStep === 1) {
        sec1.classList.remove('hidden');
        setNodeActive(node1, lbl1, '1');
        setNodeInactive(node2, lbl2, '2');
        if (line1) line1.style.width = '0%';
    } else if (targetStep === 2) {
        sec2.classList.remove('hidden');
        setNodeDone(node1, lbl1);
        setNodeActive(node2, lbl2, '2');
        if (line1) line1.style.width = '100%';
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

window.updateSubScore = updateSubScore;
window.autoMatchSubKriteria = autoMatchSubKriteria;
window.onManualSubSelectChange = onManualSubSelectChange;
window.syncPenghasilanInput = syncPenghasilanInput;
window.clearPenghasilan = clearPenghasilan;
window.goToStep = goToStep;
window.validateAndGoStep2 = validateAndGoStep2;
