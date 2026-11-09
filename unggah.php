<?php
include "auth.php";
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
            $namaDisimpan = uniqid("dok_", true) . "." . $ekstensi;

            if (move_uploaded_file($_FILES["berkas"]["tmp_name"], $folderUpload . $namaDisimpan)) {
                $jenis = strtoupper($ekstensi);
                $stmt = mysqli_prepare($koneksi,
                    "INSERT INTO data (file, nama_file, jenis_file, deskripsi) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssss", $namaDisimpan, $namaAsli, $jenis, $deskripsi);
                mysqli_stmt_execute($stmt);
                $idBaru = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt);

                // Versi pertama dokumen
                catat_versi($koneksi, $idBaru, "v1.0", "Dokumen pertama kali diunggah", null, null, $namaDisimpan, $namaAsli);

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
    <title>Unggah Baru – Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
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
                    <button class="dropup-item" onclick="tampilkanModalNotif('Pengaturan Akun', 'Membuka pengaturan profil dan preferensi sistem.')">⚙️ Pengaturan</button>
                    <a class="dropup-item logout" href="logout.php" onclick="return confirm('Anda yakin ingin keluar dari sesi ini?')">🚪 Keluar</a>
                </div>
                <div class="user-profile" onclick="toggleDropup(event)">
                    <div class="user-info">
                        <span>👤</span>
                        <span class="user-info-text"><?= htmlspecialchars($namaPengguna) ?></span>
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
                <h1 class="page-title">Unggah Baru – Pusat Data Telkom Property</h1>

                <?php if ($pesanError !== ""): ?>
                    <div class="pesan-error"><?= htmlspecialchars($pesanError) ?></div>
                <?php endif; ?>

                <form id="formUnggah" class="form-unggah-modern" method="post" enctype="multipart/form-data">
                    
                    <!-- Area Dropzone / Upload Modern -->
                    <div class="upload-dropzone" id="dropzoneArea">
                        <input type="file" name="berkas" id="berkas" class="file-input-hidden" required>
                        <div class="upload-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        </div>
                        <div class="upload-text-main">Seret & Lepas File ke Sini</div>
                        <div class="upload-text-sub">atau klik untuk menelusuri</div>
                        <div class="upload-text-info">Ukuran file maksimal 10MB. Format: PDF, DWG, XLSX, XLS, DOCX, DOC, PNG, JPG, JPEG</div>
                        <button type="button" class="btn-pilih-file" onclick="document.getElementById('berkas').click()">Pilih File</button>
                    </div>

                    <!-- Informasi Nama File Terpilih -->
                    <div id="fileInfoDisplay" class="file-info-display" style="display: none;">
                        <span>📄 File terpilih: <strong id="selectedFileName"></strong></span>
                    </div>

                    <!-- Deskripsi Dokumen di Bagian Paling Bawah -->
                    <div class="form-group-full">
                        <label for="deskripsi">Deskripsi Dokumen</label>
                        <textarea name="deskripsi" id="deskripsi" placeholder="Tambahkan deskripsi atau catatan tambahan mengenai dokumen ini..."></textarea>
                    </div>

                    <!-- Tombol Aksi Utama -->
                    <div class="form-action-container">
                        <button type="button" class="btn-finalisasi" onclick="konfirmasiSimpan()">Finalisasi & Simpan Dokumen</button>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <!-- Modal Notifikasi Umum -->
    <div id="modalBox" class="modal-overlay">
        <div class="modal-box">
            <div id="modalTitle" class="modal-header">Notifikasi</div>
            <div id="modalMessage" class="modal-body">Pesan informasi sistem.</div>
            <div class="modal-footer">
                <button class="btn-modal-close" onclick="tutupModalNotif()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Simpan -->
    <div id="modalKonfirmasi" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="color: #8b0000; font-weight: bold;">Konfirmasi Unggah Dokumen</div>
            <div class="modal-body">
                Apakah Anda yakin data dan file yang diunggah sudah sesuai? 
                <br><br>
                <small style="color: #555;">Pastikan file dan deskripsi sudah benar sebelum disimpan ke sistem.</small>
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-modal-cancel" onclick="tutupKonfirmasi()">Belum, Cek Lagi</button>
                <button type="button" class="btn-modal-confirm" onclick="prosesSimpan()">Ya, Sudah Sesuai</button>
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
        function tampilkanModalNotif(judul, pesan) {
            document.getElementById("modalTitle").innerText = judul;
            document.getElementById("modalMessage").innerHTML = pesan;
            document.getElementById("modalBox").classList.add("active");
        }
        function tutupModalNotif() {
            document.getElementById("modalBox").classList.remove("active");
        }

        // Script untuk menampilkan nama file terpilih secara otomatis
        const fileInput = document.getElementById('berkas');
        const fileInfoDisplay = document.getElementById('fileInfoDisplay');
        const selectedFileName = document.getElementById('selectedFileName');

        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                selectedFileName.innerText = fileInput.files[0].name;
                fileInfoDisplay.style.display = 'block';
            } else {
                fileInfoDisplay.style.display = 'none';
            }
        });

        // Logika Kotak Konfirmasi
        function konfirmasiSimpan() {
            if (fileInput.files.length === 0) {
                tampilkanModalNotif("Perhatian", "Silakan pilih berkas terlebih dahulu sebelum melakukan finalisasi!");
                return;
            }
            document.getElementById("modalKonfirmasi").classList.add("active");
        }

        function tutupKonfirmasi() {
            document.getElementById("modalKonfirmasi").classList.remove("active");
        }

        function prosesSimpan() {
            document.getElementById("formUnggah").submit();
        }
    </script>
</body>
</html>