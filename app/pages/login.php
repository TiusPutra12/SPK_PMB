<?php
// app/pages/login.php
session_start();

// PERBAIKAN 1: Arahkan langsung ke dashboard.php (karena satu folder di dalam pages)
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

// Arahkan ke lokasi controller yang benar
require_once __DIR__ . '/../controllers/process.php';

$logo = '../../assets/images/image.png'; // Path logo disesuaikan
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $authController = new AuthController();
    $result = $authController->handleLogin($username, $password);

    if ($result['success']) {
        $success_message = $result['message'];
        echo "<script>
            setTimeout(function() {
                // PERBAIKAN 2: Redirect JavaScript ke dashboard.php
                window.location.href = 'dashboard.php'; 
            }, 1000);
        </script>";
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login PMB - Universitas Triatma Mulya PSDKU Jembrana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/login.css?v=<?= time() ?>">
</head>
<body>
    <main class="login-page">
        <div class="glow-orb glow-orb-1" aria-hidden="true"></div>
        <div class="glow-orb glow-orb-2" aria-hidden="true"></div>

        <section class="brand-panel" aria-label="Identitas kampus">
            <div class="brand-content">
                <div class="logo-wrapper">
                    <img
                        class="campus-logo"
                        src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Logo Universitas Triatma Mulya"
                    >
                </div>
                <h1>UNIVERSITAS TRIATMA MULYA<br><span>PSDKU JEMBRANA</span></h1>
                <h4>Selamat datang bagi pengguna, silakan masuk untuk mengakses sistem pendukung keputusan penerimaan mahasiswa baru.</h4>
            </div>
        </section>

        <!-- Right Form Section -->
        <section class="form-panel" aria-label="Form login">
            <div class="login-card">
                <header class="login-heading">
                    <h2>PENERIMAAN MAHASISWA BARU</h2>
                    <p>Universitas Triatma Mulya PSDKU Jembrana</p>
                </header>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= htmlspecialchars($success_message) ?></span>
                    </div>
                <?php endif; ?>

                <!-- PERBAIKAN 3: action diubah menjadi kosong agar mengirim ke file ini sendiri -->
                <form class="login-form" action="" method="post" id="loginForm" onsubmit="handleFormSubmit()">
                    <!-- Username Input -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user input-icon"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Masukkan username"
                                autocomplete="username"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group password-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="btn-toggle-pwd" id="togglePasswordBtn" onclick="togglePasswordVisibility()" title="Lihat password">
                                <i class="fa-regular fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember & Hint -->
                    <div class="form-meta">
                        <label class="remember-check">
                            <input type="checkbox" name="remember">
                            <span>Ingat saya</span>
                        </label>
                        <a href="javascript:void(0)" class="forgot-link" onclick="handleHelp()">Lupa Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="login-button" id="btnLogin">
                        <span class="btn-text">Login ke Sistem</span>
                        <i class="fa-solid fa-arrow-right-to-bracket btn-icon"></i>
                        <span class="spinner" id="btnSpinner"></span>
                    </button>
                </form>

                <p class="help-text">
                    Butuh bantuan akses akun?
                    <a href="javascript:void(0)" onclick="handleHelp()">Hubungi IT</a>
                </p>
            </div>
        </section>
    </main>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
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
            const text = btn.querySelector('.btn-text');
            const icon = btn.querySelector('.btn-icon');
            const spinner = document.getElementById('btnSpinner');
            
            if (btn && spinner) {
                btn.disabled = true;
                if (text) text.textContent = 'Memproses...';
                if (icon) icon.style.display = 'none';
                spinner.style.display = 'inline-block';
            }
        }

        function handleHelp() {
            alert("Informasi Bantuan IT PMB:\n\nSilakan hubungi Tim IT Kampus Universitas Triatma Mulya PSDKU Jembrana untuk verifikasi atau reset akun.");
        }
    </script>
</body>
</html>