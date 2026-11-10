<?php
include "auth.php";
include "koneksi.php";

$queryTotal = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data");
$dataTotal = mysqli_fetch_assoc($queryTotal);
$totalDokumen = $dataTotal['total'] ?? 0;

$queryHariIni = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data WHERE DATE(waktu) = CURDATE()");
$dataHariIni = mysqli_fetch_assoc($queryHariIni);
$totalHariIni = $dataHariIni['total'] ?? 0;

$folderUpload = "uploads/";
$totalBytes = 0;
if (is_dir($folderUpload)) {
    $files = scandir($folderUpload);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $folderUpload . $file;
            if (is_file($filePath)) {
                $totalBytes += filesize($filePath);
            }
        }
    }
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' Bytes';
    }
}
$ukuranTotal = formatSize($totalBytes);

$queryAktivitas = mysqli_query($koneksi, "SELECT id_data, waktu, file, nama_file, jenis_file, deskripsi FROM data ORDER BY waktu DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pusat Data Telkom Property</title>
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
                    <li class="active"><a href="index.php"><span>Beranda</span></a></li>
                    <li><a href="dokumen.php"><span>Dokumen</span></a></li>
                    <li><a href="unggah.php"><span>Unggah Baru</span></a></li>
                    <li><a href="histori.php"><span>Histori</span></a></li>
                </ul>
            </div>
            
            <div class="sidebar-footer">
                <div id="dropupMenu" class="dropup-menu">
                    <button class="dropup-item" onclick="tampilkanModal('Pengaturan Akun', 'Membuka pengaturan profil dan preferensi sistem.')">⚙️ Pengaturan</button>
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
                <span>Beranda /</span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Beranda – Pusat Data Telkom Property</h1>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Total Dokumen</h3>
                            <div class="value"><?= number_format($totalDokumen) ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Unggahan Hari Ini</h3>
                            <div class="value"><?= number_format($totalHariIni) ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Ukuran Total</h3>
                            <div class="value"><?= $ukuranTotal ?></div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-title">Aktivitas Terbaru</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Dokumen</th>
                                <th>Tipe</th>
                                <th>Deskripsi Singkat</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($queryAktivitas) === 0): ?>
                                <tr><td colspan="5" style="text-align: center;">Belum ada aktivitas unggahan dokumen.</td></tr>
                            <?php else: while ($d = mysqli_fetch_assoc($queryAktivitas)): 
                                $deskripsiAsli = $d["deskripsi"];
                                $deskripsiSingkat = (mb_strlen($deskripsiAsli) > 40) ? mb_substr($deskripsiAsli, 0, 40) . "..." : $deskripsiAsli;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($d["nama_file"]) ?></td>
                                    <td><?= htmlspecialchars($d["jenis_file"]) ?></td>
                                    <td><?= htmlspecialchars($deskripsiSingkat !== "" ? $deskripsiSingkat : "-") ?></td>
                                    <td><?= date("d/m/Y", strtotime($d["waktu"])) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <button type="button" class="btn-action btn-download" onclick="konfirmasiUnduh('uploads/<?= rawurlencode($d["file"]) ?>', '<?= htmlspecialchars($d["nama_file"], ENT_QUOTES) ?>')">Unduh</button>
                                            <button type="button" class="btn-action btn-update" onclick="tampilkanModal('Informasi', 'Fitur pembaruan dokumen akan segera diperbarui.')">Perbarui</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
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

    <div id="modalKonfirmasiUnduh" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="color: #8b0000; font-weight: bold;">Konfirmasi Unduh Berkas</div>
            <div class="modal-body">
                Apakah Anda yakin ingin mengunduh dokumen: <br>
                <strong id="namaFileUnduh" style="color: #111;"></strong>?
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-modal-cancel" onclick="tutupKonfirmasiUnduh()">Batal</button>
                <a id="linkEksekusiUnduh" href="#" class="btn-modal-confirm" style="text-decoration: none; display: inline-block; line-height: normal; padding: 8px 16px;" onclick="tutupKonfirmasiUnduh()">Ya, Unduh</a>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() { document.getElementById("sidebarNav").classList.toggle("collapsed"); }
        function toggleDropup(event) { event.stopPropagation(); document.getElementById("dropupMenu").classList.toggle("active"); }
        window.onclick = function() { let dropup = document.getElementById("dropupMenu"); if (dropup.classList.contains("active")) { dropup.classList.remove("active"); } }

        function tampilkanModal(judul, pesan) {
            document.getElementById("modalTitle").innerText = judul;
            document.getElementById("modalMessage").innerHTML = pesan;
            document.getElementById("modalBox").classList.add("active");
        }
        function tutupModal() { document.getElementById("modalBox").classList.remove("active"); }

        function konfirmasiUnduh(fileUrl, fileName) {
            document.getElementById("namaFileUnduh").innerText = fileName;
            document.getElementById("linkEksekusiUnduh").href = fileUrl;
            document.getElementById("linkEksekusiUnduh").setAttribute("download", fileName);
            document.getElementById("modalKonfirmasiUnduh").classList.add("active");
        }
        function tutupKonfirmasiUnduh() { document.getElementById("modalKonfirmasiUnduh").classList.remove("active"); }
    </script>
</body>
</html>