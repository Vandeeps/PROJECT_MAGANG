<?php
session_set_cookie_params(["httponly" => true, "samesite" => "Lax"]);
session_start();
include "koneksi.php";

// Sudah login? langsung ke beranda
if (!empty($_SESSION["id_user"])) {
    header("Location: index.php");
    exit;
}

$pesanError = "";
$usernameIsi = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usernameIsi = trim($_POST["username"] ?? "");
    $password    = $_POST["password"] ?? "";

    if ($usernameIsi === "" || $password === "") {
        $pesanError = "Username dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id_user, username, Password FROM user WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $usernameIsi);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        $cocok = false;
        if ($user) {
            $tersimpan = $user["Password"];
            if (!empty(password_get_info($tersimpan)["algo"])) {
                // Password tersimpan sebagai hash (cara yang benar)
                $cocok = password_verify($password, $tersimpan);
            } else {
                // Password lama tersimpan sebagai teks biasa: cocokkan, lalu ubah otomatis jadi hash
                $cocok = hash_equals($tersimpan, $password);
                if ($cocok) {
                    $hashBaru = password_hash($password, PASSWORD_DEFAULT);
                    $up = mysqli_prepare($koneksi, "UPDATE user SET Password = ? WHERE id_user = ?");
                    mysqli_stmt_bind_param($up, "si", $hashBaru, $user["id_user"]);
                    mysqli_stmt_execute($up);
                    mysqli_stmt_close($up);
                }
            }
        }

        if ($cocok) {
            session_regenerate_id(true);
            $_SESSION["id_user"]  = $user["id_user"];
            $_SESSION["username"] = $user["username"];

            catat_riwayat($koneksi, "Masuk ke sistem");

            header("Location: index.php");
            exit;
        }
        // Pesan sengaja disamakan agar tidak membocorkan apakah username ada atau tidak
        $pesanError = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">

    <header class="login-header">
        <img src="img/logo-telkom-light.png" alt="Logo Telkom Property" class="login-logo">
        <a href="#" class="login-link-utama">Website Utama</a>
    </header>

    <main class="login-main">
        <section class="login-card">
            <h1>LOGIN</h1>
            <p class="login-sub">Silakan masuk untuk mengakses layanan.</p>

            <?php if ($pesanError !== ""): ?>
                <div class="login-error" role="alert"><?= htmlspecialchars($pesanError) ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username"
                       value="<?= htmlspecialchars($usernameIsi) ?>" required autofocus>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>

                <button type="submit" class="btn-masuk">Masuk</button>
            </form>

            <p class="login-bantuan">Lupa password? Hubungi administrator.</p>
        </section>
    </main>

</body>
</html>
