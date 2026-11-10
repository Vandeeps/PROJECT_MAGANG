<?php
include "auth.php";
include "koneksi.php";

$pesanError = "";
$pesanSukses = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $passLama     = $_POST["pass_lama"] ?? "";
    $passBaru     = $_POST["pass_baru"] ?? "";
    $konfirmPass  = $_POST["konfirm_pass"] ?? "";

    if (empty($passLama) || empty($passBaru) || empty($konfirmPass)) {
        $pesanError = "Semua kolom password wajib diisi.";
    } elseif ($passBaru !== $konfirmPass) {
        $pesanError = "Konfirmasi password baru tidak cocok.";
    } elseif (strlen($passBaru) < 6) {
        $pesanError = "Password baru minimal 6 karakter.";
    } else {
        $usernameSession = $_SESSION["username"] ?? $namaPengguna;
        $stmt = mysqli_prepare($koneksi, "SELECT id_user, password FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $usernameSession);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $userData = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$userData || !password_verify($passLama, $userData["password"])) {
            $pesanError = "Password lama Anda salah.";
        } else {
            $hashBaru = password_hash($passBaru, PASSWORD_DEFAULT);
            $up = mysqli_prepare($koneksi, "UPDATE user SET password = ? WHERE id_user = ?");
            mysqli_stmt_bind_param($up, "si", $hashBaru, $userData["id_user"]);
            
            if (mysqli_stmt_execute($up)) {
                catat_riwayat($koneksi, "Mengubah password akun");
                $pesanSukses = "Password berhasil diperbarui!";
            } else {
                $pesanError = "Gagal memperbarui password di database.";
            }
            mysqli_stmt_close($up);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar Modular -->
        <?php include "includes/sidebar.php"; ?>

        <main class="main-content">
            <header class="main-header">
                <button class="btn-toggle" onclick="toggleSidebar()">☰ Menu</button>
                <span>Beranda / <strong>Ubah Password</strong></span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Keamanan Akun</h1>
                <p class="page-sub" style="margin-bottom: 24px; color: var(--text-muted);">Perbarui password akun Anda secara berkala untuk menjaga keamanan akses sistem.</p>

                <!-- Menggunakan .table-container agar desain kotaknya sama persis dengan tabel/card lain di sistemmu -->
                <div class="table-container" style="max-width: 600px;">
                    <div class="table-title" style="margin-bottom: 6px;">Form Ubah Password</div>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">Masukkan password saat ini dan password baru Anda.</p>

                    <?php if ($pesanError !== ""): ?>
                        <div class="pesan-error">
                            <?= htmlspecialchars($pesanError) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($pesanSukses !== ""): ?>
                        <div style="background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; padding:10px 14px; border-radius:6px; margin-bottom:15px; font-size:14px;">
                            <?= htmlspecialchars($pesanSukses) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="ubah_password.php" style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="form-group-full">
                            <label for="pass_lama" style="display: block; font-weight: 600; margin-bottom: 6px;">Password Saat Ini</label>
                            <input type="password" id="pass_lama" name="pass_lama" class="search-box input" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>

                        <div class="form-group-full">
                            <label for="pass_baru" style="display: block; font-weight: 600; margin-bottom: 6px;">Password Baru</label>
                            <input type="password" id="pass_baru" name="pass_baru" required minlength="6" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main); outline: none;">
                            <small style="display: block; margin-top: 4px; color: var(--text-muted); font-size: 12px;">Minimal 6 karakter.</small>
                        </div>

                        <div class="form-group-full">
                            <label for="konfirm_pass" style="display: block; font-weight: 600; margin-bottom: 6px;">Konfirmasi Password Baru</label>
                            <input type="password" id="konfirm_pass" name="konfirm_pass" required minlength="6" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main); outline: none;">
                        </div>

                        <div style="margin-top: 10px;">
                            <button type="submit" class="btn-finalisasi" style="cursor: pointer;">Simpan Password Baru</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Logout -->
    <div id="modalLogout" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="color: #d32f2f;">Konfirmasi Keluar</div>
            <div class="modal-body">Apakah Anda yakin ingin keluar dari sesi ini?</div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="tutupModalLogout()">Batal</button>
                <a href="logout.php" class="btn-modal-danger" style="text-decoration: none;">Ya, Keluar</a>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() { document.getElementById("sidebarNav").classList.toggle("collapsed"); }
        function toggleDropup(event) { event.stopPropagation(); document.getElementById("dropupMenu").classList.toggle("active"); }
        window.onclick = function() { let dropup = document.getElementById("dropupMenu"); if (dropup && dropup.classList.contains("active")) dropup.classList.remove("active"); }
        function tutupModalLogout() { document.getElementById("modalLogout").classList.remove("active"); }
        function bukaModalLogout() { document.getElementById("modalLogout").classList.add("active"); }
    </script>
</body>
</html>