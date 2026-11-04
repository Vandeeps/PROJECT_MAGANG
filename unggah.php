<?php
include "koneksi.php";

$folderUpload = __DIR__ . "/uploads/";
if (!is_dir($folderUpload)) {
    mkdir($folderUpload, 0755, true);
}

$ekstensiBoleh = ["pdf", "dwg", "xlsx", "xls", "docx", "doc", "png", "jpg", "jpeg"];
$ukuranMaks    = 10 * 1024 * 1024; // 10 MB

$pesanError = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $deskripsi = trim($_POST["deskripsi"] ?? "");

    if (!isset($_FILES["berkas"]) || $_FILES["berkas"]["error"] !== UPLOAD_ERR_OK) {
        $pesanError = "Pilih berkas terlebih dahulu (atau ukuran berkas melebihi batas server).";
    } else {
        $namaAsli = basename($_FILES["berkas"]["name"]);
        $ukuran   = $_FILES["berkas"]["size"];
        $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

        if (!in_array($ekstensi, $ekstensiBoleh)) {
            $pesanError = "Tipe berkas .$ekstensi tidak diizinkan. Boleh: " . implode(", ", $ekstensiBoleh) . ".";
        } elseif ($ukuran > $ukuranMaks) {
            $pesanError = "Ukuran berkas maksimal 10 MB.";
        } else {
            // Nama di server dibuat unik supaya tidak bentrok / menimpa file lain
            $namaDisimpan = uniqid("dok_", true) . "." . $ekstensi;

            if (move_uploaded_file($_FILES["berkas"]["tmp_name"], $folderUpload . $namaDisimpan)) {
                $jenis = strtoupper($ekstensi);
                $stmt = mysqli_prepare($koneksi,
                    "INSERT INTO data (file, nama_file, jenis_file, deskripsi) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssss", $namaDisimpan, $namaAsli, $jenis, $deskripsi);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                catat_riwayat($koneksi, "Mengunggah dokumen: " . $namaAsli);

                header("Location: dokumen.php?status=sukses");
                exit;
            } else {
                $pesanError = "Gagal menyimpan berkas ke server.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah Baru - Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-unggah { max-width: 520px; }
        .form-unggah label { display: block; margin: 14px 0 6px; font-weight: 600; }
        .form-unggah input[type="file"],
        .form-unggah textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .form-unggah textarea { min-height: 90px; resize: vertical; }
        .form-unggah button { margin-top: 18px; padding: 10px 22px; cursor: pointer; }
        .pesan-error { background: #fdecea; color: #b71c1c; padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <aside class="sidebar" id="sidebarNav">
            <div>
                <div class="sidebar-header">
                    <img src="img/Logo Telkom Dark.webp" alt="Logo Telkom Property" class="sidebar-logo">
                </div>
                <ul class="sidebar-menu">
                    <li><a href="index.php"><span>Beranda</span></a></li>
                    <li><a href="dokumen.php"><span>Dokumen</span></a></li>
                    <li class="active"><a href="unggah.php"><span>Unggah Baru</span></a></li>
                    <li><a href="histori.php"><span>Histori</span></a></li>
                </ul>
            </div>

            <div class="sidebar-footer">
                <div id="dropupMenu" class="dropup-menu">
                    <button class="dropup-item" onclick="tampilkanModal('Pengaturan Akun', 'Membuka pengaturan profil dan preferensi sistem.')">⚙️ Pengaturan</button>
                    <button class="dropup-item logout" onclick="tampilkanModal('Keluar Sistem', 'Anda yakin ingin keluar dari sesi ini?')">🚪 Keluar</button>
                </div>
                <div class="user-profile" onclick="toggleDropup(event)">
                    <div class="user-info">
                        <span>👤</span>
                        <span class="user-info-text">Telpro BJM</span>
                    </div>
                    <span class="user-arrow">▲</span>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <button class="btn-toggle" onclick="toggleSidebar()">☰ Menu</button>
                <span>Beranda / Unggah Baru</span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Unggah Dokumen Baru</h1>

                <div class="table-container">
                    <?php if ($pesanError !== ""): ?>
                        <div class="pesan-error"><?= htmlspecialchars($pesanError) ?></div>
                    <?php endif; ?>

                    <form class="form-unggah" method="post" enctype="multipart/form-data">
                        <label for="berkas">Pilih Berkas</label>
                        <input type="file" name="berkas" id="berkas" required>

                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" placeholder="Keterangan singkat dokumen..."></textarea>

                        <button type="submit" class="btn-action btn-download">Unggah</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <div id="modalBox" class="modal-overlay">
        <div class="modal-box">
            <div id="modalTitle" class="modal-header">Notifikasi</div>
            <div id="modalMessage" class="modal-body">Pesan informasi sistem.</div>
            <div class="modal-footer">
                <button class="btn-modal-close" onclick="tutupModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebarNav").classList.toggle("collapsed");
        }
        function toggleDropup(event) {
            event.stopPropagation();
            document.getElementById("dropupMenu").classList.toggle("active");
        }
        window.onclick = function() {
            let dropup = document.getElementById("dropupMenu");
            if (dropup.classList.contains("active")) dropup.classList.remove("active");
        }
        function tampilkanModal(judul, pesan) {
            document.getElementById("modalTitle").innerText = judul;
            document.getElementById("modalMessage").innerHTML = pesan;
            document.getElementById("modalBox").classList.add("active");
        }
        function tutupModal() {
            document.getElementById("modalBox").classList.remove("active");
        }
    </script>
</body>
</html>
