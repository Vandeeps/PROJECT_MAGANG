<?php
include "koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar -->
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
            
            <!-- Drop-Up Account Menu di Footer Sidebar -->
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

        <!-- Main Content -->
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
                            <div class="value">1,490</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Total Gedung</h3>
                            <div class="value">320</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Pembaruan Bulan Ini</h3>
                            <div class="value">45</div>
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
                                <th>Data Lokasi</th>
                                <th>Tanggal</th>
                                <th>Pemilik</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Dokumen_Gedung_A.pdf</td>
                                <td>PDF</td>
                                <td>Banjarmasin</td>
                                <td>03/11/2026</td>
                                <td>Admin</td>
                                <td>
                                    <button class="btn-action btn-download" onclick="tampilkanModal('Unduh Berkas', 'Memproses unduhan untuk dokumen: <b>Dokumen_Gedung_A.pdf</b>')">Unduh</button>
                                    <button class="btn-action btn-update" onclick="tampilkanModal('Perbarui Dokumen', 'Membuka panel pembaruan untuk dokumen: <b>Dokumen_Gedung_A.pdf</b>')">Perbarui</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Denah_Lt2_Telpro.dwg</td>
                                <td>CAD</td>
                                <td>Banjarmasin</td>
                                <td>02/11/2026</td>
                                <td>Admin</td>
                                <td>
                                    <button class="btn-action btn-download" onclick="tampilkanModal('Unduh Berkas', 'Memproses unduhan untuk dokumen: <b>Denah_Lt2_Telpro.dwg</b>')">Unduh</button>
                                    <button class="btn-action btn-update" onclick="tampilkanModal('Perbarui Dokumen', 'Membuka panel pembaruan untuk dokumen: <b>Denah_Lt2_Telpro.dwg</b>')">Perbarui</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Custom Modal Box -->
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
            if (dropup.classList.contains("active")) {
                dropup.classList.remove("active");
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