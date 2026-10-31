function toggleSubKriteria(id) {
    const subRow = document.getElementById('sub-row-' + id);
    const subContent = document.getElementById('sub-content-' + id);
    const chevron = document.getElementById('chevron-' + id);
    if (!subRow || !subContent) return;

    const isClosed = subRow.style.display === 'none' || subContent.classList.contains('grid-rows-[0fr]');

    if (isClosed) {
        subRow.style.display = '';
        requestAnimationFrame(() => {
            subContent.classList.remove('grid-rows-[0fr]');
            subContent.classList.add('grid-rows-[1fr]');
            if (chevron) chevron.classList.add('rotate-180');
        });
    } else {
        subContent.classList.remove('grid-rows-[1fr]');
        subContent.classList.add('grid-rows-[0fr]');
        if (chevron) chevron.classList.remove('rotate-180');
        setTimeout(() => {
            if (subContent.classList.contains('grid-rows-[0fr]')) {
                subRow.style.display = 'none';
            }
        }, 300);
    }
}

window.toggleSubKriteria = toggleSubKriteria;

