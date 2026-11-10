<?php
include "auth.php";
include "koneksi.php";

// Query sesuai struktur tabel asli Anda
$hasil = mysqli_query($koneksi, "SELECT aksi, waktu FROM riwayat ORDER BY waktu DESC, id_riwayat DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Aktivitas - Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar Modular -->
        <?php include "includes/sidebar.php"; ?>

        <main class="main-content">
            <header class="main-header">
                <button class="btn-toggle" onclick="toggleSidebar()">☰ Menu</button>
                <span>Beranda / <strong>Histori</strong></span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Histori Aktivitas Sistem</h1>
                <p class="page-sub">Catatan log aktivitas terbaru yang tercatat di dalam sistem.</p>

                <div class="table-container" style="margin-top: 20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Waktu</th>
                                <th>Aksi / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$hasil || mysqli_num_rows($hasil) === 0): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px;">Belum ada riwayat aktivitas.</td>
                                </tr>
                            <?php else: $no = 1; while ($row = mysqli_fetch_assoc($hasil)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row["waktu"] ?? "-") ?></td>
                                    <td><?= htmlspecialchars($row["aksi"] ?? "-") ?></td>
                                </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Logout -->
    <div id="modalLogout" class="modal-overlay">
        <div class="modal-box" style="max-width: 380px;">
            <div class="modal-header" style="color: #d32f2f; font-weight: bold;">Konfirmasi Keluar</div>
            <div class="modal-body">Apakah Anda yakin ingin keluar dari sesi ini?</div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                <button type="button" class="btn-modal-cancel" onclick="tutupModalLogout()">Batal</button>
                <a href="logout.php" class="btn-modal-confirm" style="background: #d32f2f; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px;">Ya, Keluar</a>
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
            if (dropup && dropup.classList.contains("active")) dropup.classList.remove("active"); 
        }
    </script>
</body>
</html>