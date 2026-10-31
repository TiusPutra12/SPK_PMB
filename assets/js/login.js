

function togglePasswordVisibility() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (!pwd || !icon) return;

    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
}

function handleFormSubmit() {
    const btn = document.getElementById('btnLogin');
    if (!btn) return;

    const text = btn.querySelector('.btn-text');
    const icon = btn.querySelector('.btn-icon');
    const spinner = document.getElementById('btnSpinner');
    
    btn.disabled = true;
    if (text) text.textContent = 'Memproses...';
    if (icon) icon.style.display = 'none';
    if (spinner) spinner.style.display = 'inline-block';
}

function handleHelp() {
    alert("Informasi Bantuan IT PMB:\n\nSilakan hubungi Tim IT Kampus Universitas Triatma Mulya PSDKU Jembrana untuk verifikasi atau reset akun.");
}

window.togglePasswordVisibility = togglePasswordVisibility;
window.handleFormSubmit = handleFormSubmit;
window.handleHelp = handleHelp;

