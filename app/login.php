<?php
$logo = '../assets/images/image.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login PMB</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <main class="login-page">
        <section class="brand-panel" aria-label="Identitas kampus">
            <div class="brand-content">
                <img
                    class="campus-logo"
                    src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Logo"
                >
                <div class="logo-fallback" id="logo-fallback" aria-hidden="true">STIE<br><span>NUSANTARA</span></div>
                <h1>UNIVERSITAS TRIATMA MULYA PSDKU JEMBRANA</h1>
                <h4>Selamat datang bagi pengguna, silahkan login untuk memasuki sistem kami.</h4>
            </div>
        </section>

        <section class="form-panel" aria-label="Form login">
            <div class="login-card">
                <header class="login-heading">
                    <h2>PENERIMAAN MAHASISWA BARU</h2>
                    <p>Universitas Triatma Mulya PSDKU Jembrana</p>
                </header>

                <form class="login-form" action="#" method="post">
                    <div class="form-group">
                        <label class="sr-only" for="username">NIM / Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Username"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="form-group password-group">
                        <label class="sr-only" for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button type="button" class="login-button">Login</button>
                </form>

                <p class="help-text">
                    Lupa Password?
                    <a href="#" onclick="return false;">Hubungi IT</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
