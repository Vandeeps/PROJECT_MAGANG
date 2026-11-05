<?php
include 'auth.php';

if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    }

    $errorMessage = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Telkom Property</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <div class="login-shell">
        <header class="login-header">
            <div class="login-brand" aria-label="Telkom Property logo">
                <img src="img/Logo Telkom Dark.webp" alt="Logo Telkom Property" class="login-brand-logo">
            </div>
            <a href="index.php" class="login-website-link">Website Utama</a>
        </header>

        <main class="login-main">
            <div class="login-map-overlay" aria-hidden="true"></div>

            <div class="login-card">
                <h1 class="login-title">LOGIN</h1>
                <p class="login-subtitle">Silakan masuk untuk mengakses layanan.</p>

                <?php if ($errorMessage !== ''): ?>
                    <div class="login-error"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <form method="post" action="login.php" class="login-form">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" placeholder="Masukkan username" autocomplete="username" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required>
                    </div>

                    <button type="submit" class="login-btn">Masuk</button>
                    <p class="login-help">Lupa password? Hubungi administrator.</p>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
