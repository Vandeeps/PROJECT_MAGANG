<?php
include "koneksi.php";

$hasil = mysqli_query($koneksi, "SELECT id_data, waktu, file, nama_file, jenis_file, deskripsi FROM data ORDER BY waktu DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositori Dokumen - Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="css/style.css">
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
                    <button class="btn-filter" onclick="tampilkanModal('Filter Data', 'Panel opsi filter kategori dokumen lanjutan akan ditampilkan di sini.')">Tampilkan Berdasarkan</button>
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
                                <tr><td colspan="5">Belum ada dokumen. Silakan unggah dari menu "Unggah Baru".</td></tr>
                            <?php else: while ($d = mysqli_fetch_assoc($hasil)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d["nama_file"]) ?></td>
                                    <td><?= htmlspecialchars($d["jenis_file"]) ?></td>
                                    <td><?= htmlspecialchars($d["deskripsi"]) ?></td>
                                    <td><?= date("d/m/Y", strtotime($d["waktu"])) ?></td>
                                    <td>
                                        <a class="btn-action btn-download"
                                           href="uploads/<?= rawurlencode($d["file"]) ?>"
                                           download="<?= htmlspecialchars($d["nama_file"]) ?>">Unduh</a>
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

        function jalankanPencarian() {
            let filter = document.getElementById("kotakPencarian").value.toLowerCase();
            let tr = document.getElementById("tabelRepositori").getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    let teks = (td.textContent || td.innerText).toLowerCase();
                    // Perbaikan: baris yang tidak cocok disembunyikan ("none")
                    tr[i].style.display = teks.indexOf(filter) > -1 ? "" : "none";
                }
            }
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
