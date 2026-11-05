<?php
include "auth.php";
include "koneksi.php";

$hasil = mysqli_query($koneksi, "SELECT id_data, waktu, file, nama_file, jenis_file, deskripsi FROM data ORDER BY waktu DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositori Dokumen - Pusat Data Telkom Property</title>
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
                    <li class="active"><a href="dokumen.php"><span>Dokumen</span></a></li>
                    <li><a href="unggah.php"><span>Unggah Baru</span></a></li>
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
                <span>Beranda / Dokumen</span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Repositori Dokumen – Pusat Data Telkom Property</h1>

                <?php if (($_GET["status"] ?? "") === "sukses"): ?>
                    <div style="background:#e8f5e9;color:#1b5e20;padding:10px 14px;border-radius:6px;margin-bottom:12px;">
                        Dokumen berhasil diunggah.
                    </div>
                <?php endif; ?>

                <div class="top-bar-controls">
                    <div class="search-box">
                        <input type="text" id="kotakPencarian" placeholder="Ketik untuk mencari dokumen..." onkeyup="jalankanPencarian()">
                    </div>
                    <button class="btn-filter" onclick="tampilkanModalNotif('Filter Data', 'Panel opsi filter kategori dokumen lanjutan akan ditampilkan di sini.')">Tampilkan Berdasarkan</button>
                </div>

                <div class="table-container">
                    <table id="tabelRepositori">
                        <thead>
                            <tr>
                                <th>Nama Dokumen</th>
                                <th>Tipe</th>
                                <th>Deskripsi</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($hasil) === 0): ?>
                                <tr><td colspan="5" style="text-align: center;">Belum ada dokumen. Silakan unggah dari menu "Unggah Baru".</td></tr>
                            <?php else: while ($d = mysqli_fetch_assoc($hasil)): 
                                // Menghitung ukuran file fisik di server
                                $filePath = __DIR__ . "/uploads/" . $d["file"];
                                $ukuranFormatted = "Tidak diketahui";
                                if (file_exists($filePath)) {
                                    $bytes = filesize($filePath);
                                    if ($bytes >= 1048576) {
                                        $ukuranFormatted = number_format($bytes / 1048576, 2) . " MB";
                                    } else {
                                        $ukuranFormatted = number_format($bytes / 1024, 2) . " KB";
                                    }
                                }

                                // Potong deskripsi agar bersih dan stabil 1 baris di tabel (tanpa teks [Detail])
                                $deskripsiAsli = $d["deskripsi"];
                                $deskripsiPendek = (mb_strlen($deskripsiAsli) > 40) ? mb_substr($deskripsiAsli, 0, 40) . "..." : $deskripsiAsli;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($d["nama_file"]) ?></td>
                                    <td><?= htmlspecialchars($d["jenis_file"]) ?></td>
                                    <td><?= htmlspecialchars($deskripsiPendek !== "" ? $deskripsiPendek : "-") ?></td>
                                    <td><?= date("d/m/Y", strtotime($d["waktu"])) ?></td>
                                    <td>
                                        <div class="aksi-container">
                                            <button type="button" class="btn-action btn-download" onclick="konfirmasiUnduh('uploads/<?= rawurlencode($d["file"]) ?>', '<?= htmlspecialchars($d["nama_file"], ENT_QUOTES) ?>')">Unduh</button>
                                            <button type="button" class="btn-action btn-detail-popup" onclick="bukaDetail('<?= htmlspecialchars($d["nama_file"], ENT_QUOTES) ?>', '<?= htmlspecialchars($d["jenis_file"], ENT_QUOTES) ?>', '<?= htmlspecialchars(str_replace(["\r", "\n"], "<br>", $deskripsiAsli), ENT_QUOTES) ?>', '<?= date("d/m/Y H:i", strtotime($d["waktu"])) ?>', '<?= $ukuranFormatted ?>')">Detail</button>
                                            <button type="button" class="btn-action btn-update" onclick="tampilkanModalNotif('Perbarui Dokumen', 'Fitur perbarui dokumen akan segera hadir.')">Perbarui</button>
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

    <!-- Modal Detail Dokumen Lengkap -->
    <div id="modalDetailDokumen" class="modal-overlay">
        <div class="modal-box" style="max-width: 480px;">
            <div class="modal-header" style="color: #8b0000; font-weight: bold; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 12px;">Detail Dokumen</div>
            <div class="modal-body" style="font-size: 14px; line-height: 1.6;">
                <p><strong>Nama Dokumen:</strong> <span id="detNama">-</span></p>
                <p><strong>Tipe File:</strong> <span id="detTipe">-</span></p>
                <p><strong>Ukuran Berkas:</strong> <span id="detUkuran" style="color: #2e7d32; font-weight: bold;">-</span></p>
                <p><strong>Tanggal Unggah:</strong> <span id="detTanggal">-</span></p>
                <p style="margin-top: 10px;"><strong>Deskripsi Lengkap:</strong></p>
                <div id="detDeskripsi" style="background: #f4f4f4; padding: 10px; border-radius: 6px; max-height: 150px; overflow-y: auto; color: #333; word-break: break-word;">-</div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; margin-top: 15px;">
                <button type="button" class="btn-modal-close" onclick="tutupDetail()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Unduh -->
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
        window.onclick = function() { let dropup = document.getElementById("dropupMenu"); if (dropup.classList.contains("active")) dropup.classList.remove("active"); }

        function jalankanPencarian() {
            let filter = document.getElementById("kotakPencarian").value.toLowerCase();
            let tr = document.getElementById("tabelRepositori").getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    let teks = (td.textContent || td.innerText).toLowerCase();
                    tr[i].style.display = teks.indexOf(filter) > -1 ? "" : "none";
                }
            }
        }

        function tampilkanModalNotif(judul, pesan) {
            document.getElementById("modalTitle").innerText = judul;
            document.getElementById("modalMessage").innerHTML = pesan;
            document.getElementById("modalBox").classList.add("active");
        }
        function tutupModalNotif() { document.getElementById("modalBox").classList.remove("active"); }

        function bukaDetail(nama, tipe, deskripsi, tanggal, ukuran) {
            document.getElementById("detNama").innerText = nama;
            document.getElementById("detTipe").innerText = tipe;
            document.getElementById("detUkuran").innerText = ukuran;
            document.getElementById("detTanggal").innerText = tanggal;
            document.getElementById("detDeskripsi").innerHTML = deskripsi !== "" ? deskripsi : "<i>Tidak ada deskripsi.</i>";
            document.getElementById("modalDetailDokumen").classList.add("active");
        }
        function tutupDetail() { document.getElementById("modalDetailDokumen").classList.remove("active"); }

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