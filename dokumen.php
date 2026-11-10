<?php
include "auth.php";
include "koneksi.php";

$ekstensiBoleh = ["pdf", "dwg", "xlsx", "xls", "docx", "doc", "png", "jpg", "jpeg"];
$folderUpload  = __DIR__ . "/uploads/";

// Proses Hapus Dokumen jika ada parameter aksi=hapus
if (isset($_GET["aksi"]) && $_GET["aksi"] === "hapus" && isset($_GET["id"])) {
    $idHapus = (int) $_GET["id"];

    $qCek = mysqli_prepare($koneksi, "SELECT file FROM data WHERE id_data = ?");
    mysqli_stmt_bind_param($qCek, "i", $idHapus);
    mysqli_stmt_execute($qCek);
    $resCek = mysqli_stmt_get_result($qCek);
    if ($rowCek = mysqli_fetch_assoc($resCek)) {
        if (!empty($rowCek["file"]) && is_file($folderUpload . $rowCek["file"])) {
            @unlink($folderUpload . $rowCek["file"]);
        }
    }
    mysqli_stmt_close($qCek);

    $qVer = mysqli_prepare($koneksi, "SELECT file FROM versi_dokumen WHERE id_data = ?");
    mysqli_stmt_bind_param($qVer, "i", $idHapus);
    mysqli_stmt_execute($qVer);
    $resVer = mysqli_stmt_get_result($qVer);
    while ($rowVer = mysqli_fetch_assoc($resVer)) {
        if (!empty($rowVer["file"]) && is_file($folderUpload . $rowVer["file"])) {
            @unlink($folderUpload . $rowVer["file"]);
        }
    }
    mysqli_stmt_close($qVer);

    mysqli_query($koneksi, "DELETE FROM versi_dokumen WHERE id_data = $idHapus");
    mysqli_query($koneksi, "DELETE FROM data WHERE id_data = $idHapus");

    catat_riwayat($koneksi, "Menghapus dokumen ID: " . $idHapus);

    header("Location: dokumen.php?status=dihapus");
    exit;
}

$keyword       = trim($_GET['keyword'] ?? '');
$tanggalDari   = $_GET['tanggal_dari'] ?? '';
$tanggalSampai = $_GET['tanggal_sampai'] ?? '';
$tipeFile      = strtolower(trim($_GET['tipe_file'] ?? ''));

$sql = "SELECT id_data, waktu, file, nama_file, jenis_file, deskripsi FROM data WHERE 1=1";
$params = [];
$types  = "";

if ($keyword !== '') {
    $sql .= " AND (nama_file LIKE ? OR deskripsi LIKE ?)";
    $likeKeyword = "%" . $keyword . "%";
    $params[] = $likeKeyword;
    $params[] = $likeKeyword;
    $types  .= "ss";
}

if ($tanggalDari !== '') {
    $sql .= " AND DATE(waktu) >= ?";
    $params[] = $tanggalDari;
    $types  .= "s";
}

if ($tanggalSampai !== '') {
    $sql .= " AND DATE(waktu) <= ?";
    $params[] = $tanggalSampai;
    $types  .= "s";
}

if ($tipeFile !== '') {
    $sql .= " AND (LOWER(jenis_file) LIKE ? OR LOWER(file) LIKE ? OR LOWER(nama_file) LIKE ?)";
    $params[] = "%" . $tipeFile . "%";
    $params[] = "%." . $tipeFile;
    $params[] = "%." . $tipeFile;
    $types  .= "sss";
}

$sql .= " ORDER BY waktu DESC";

$stmt = mysqli_prepare($koneksi, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
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
        <!-- SIDEBAR DIMUAT DARI FOLDER INCLUDES -->
        <?php include "includes/sidebar.php"; ?>

        <main class="main-content">
            <header class="main-header">
                <button class="btn-toggle" onclick="toggleSidebar()">☰ Menu</button>
                <span>Beranda / Dokumen</span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Repositori Dokumen – Pusat Data Telkom Property</h1>

                <?php if (($_GET["status"] ?? "") === "dihapus"): ?>
                    <div style="background:#ffebee;color:#c62828;padding:10px 14px;border-radius:6px;margin-bottom:12px;">
                        Dokumen berhasil dihapus dari sistem.
                    </div>
                <?php endif; ?>

                <div class="top-bar-controls">
                    <div class="search-box">
                        <input type="text" id="kotakPencarian" placeholder="Ketik untuk mencari dokumen..." value="<?= htmlspecialchars($keyword) ?>" onkeyup="jalankanPencarian()">
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn-filter" onclick="bukaModalFilter()">Tampilkan Berdasarkan</button>
                        <?php if($keyword !== '' || $tanggalDari !== '' || $tanggalSampai !== '' || $tipeFile !== ''): ?>
                            <a href="dokumen.php" style="padding: 8px 12px; background: #e0e0e0; color: #333; text-decoration: none; border-radius: 4px; font-size: 13px; display: inline-flex; align-items: center;">Reset Filter</a>
                        <?php endif; ?>
                    </div>
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
                                <tr><td colspan="5" style="text-align: center;">Tidak ada dokumen yang ditemukan sesuai filter/pencarian.</td></tr>
                            <?php else: while ($d = mysqli_fetch_assoc($hasil)): 
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

                                $deskripsiAsli = $d["deskripsi"];
                                $deskripsiPendek = (mb_strlen($deskripsiAsli) > 40) ? mb_substr($deskripsiAsli, 0, 40) . "..." : $deskripsiAsli;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($d["nama_file"]) ?></td>
                                    <td><?= htmlspecialchars($d["jenis_file"]) ?></td>
                                    <td><?= htmlspecialchars($deskripsiPendek !== "" ? $deskripsiPendek : "-") ?></td>
                                    <td><?= date("d/m/Y", strtotime($d["waktu"])) ?></td>
                                    <td>
                                        <div class="aksi-container" style="display: flex; gap: 6px; align-items: center;">
                                            <button type="button" class="btn-action btn-download" onclick="konfirmasiUnduh('uploads/<?= rawurlencode($d["file"]) ?>', '<?= htmlspecialchars($d["nama_file"], ENT_QUOTES) ?>')">Unduh</button>
                                            <button type="button" class="btn-action btn-detail-popup" onclick="bukaDetail('<?= htmlspecialchars($d["nama_file"], ENT_QUOTES) ?>', '<?= htmlspecialchars($d["jenis_file"], ENT_QUOTES) ?>', '<?= htmlspecialchars(str_replace(["\r", "\n"], "<br>", $deskripsiAsli), ENT_QUOTES) ?>', '<?= date("d/m/Y H:i", strtotime($d["waktu"])) ?>', '<?= $ukuranFormatted ?>')">Detail</button>
                                            <a href="update_dokumen.php?id=<?= (int) $d["id_data"] ?>" class="btn-action btn-update" style="text-decoration: none; display: inline-block;">Perbarui</a>
                                            <button type="button" class="btn-action btn-hapus" onclick="bukaModalHapus(<?= (int) $d["id_data"] ?>, '<?= htmlspecialchars($d["nama_file"], ENT_QUOTES) ?>')">Hapus</button>
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

    <!-- Modal Filter Lanjutan -->
    <div id="modalFilter" class="modal-overlay">
        <div class="modal-box" style="max-width: 420px;">
            <div class="modal-header" style="color: #8b0000; font-weight: bold; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 12px;">Filter Dokumen Lanjutan</div>
            <form method="GET" action="dokumen.php">
                <div class="modal-body" style="font-size: 14px; line-height: 1.6;">
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Cari Nama / Deskripsi:</label>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Kata kunci..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Dari Tanggal:</label>
                        <input type="date" name="tanggal_dari" value="<?= htmlspecialchars($tanggalDari) ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Sampai Tanggal:</label>
                        <input type="date" name="tanggal_sampai" value="<?= htmlspecialchars($tanggalSampai) ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 4px;">Tipe / Ekstensi File:</label>
                        <select name="tipe_file" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">-- Semua Ekstensi --</option>
                            <?php foreach ($ekstensiBoleh as $ekstensi): ?>
                                <option value="<?= $ekstensi ?>" <?= ($tipeFile === $ekstensi) ? 'selected' : '' ?>><?= strtoupper($ekstensi) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 15px;">
                    <button type="button" class="btn-modal-cancel" onclick="tutupModalFilter()">Batal</button>
                    <button type="submit" class="btn-modal-confirm" style="padding: 8px 16px; border: none; cursor: pointer; border-radius: 4px;">Terapkan Filter</button>
                </div>
            </form>
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

    <!-- Modal Konfirmasi Hapus Khusus -->
    <div id="modalHapusPeringatan" class="modal-overlay hapus-warning">
        <div class="modal-box hapus-warning-box">
            <div class="hapus-warning-header">
                <div class="hapus-warning-icon">⚠️</div>
                <span>Peringatan Penghapusan Permanen</span>
            </div>
            <div class="hapus-warning-body">
                Tindakan ini bersifat permanen dan akan menghapus data dokumen beserta file fisiknya dari server secara utuh.
                <div class="hapus-warning-target" id="teksNamaFileHapus">-</div>
                Apakah Anda benar-benar yakin ingin menghapus dokumen ini?
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-modal-cancel" onclick="tutupModalHapus()">Batal</button>
                <a id="linkEksekusiHapus" href="#" class="btn-modal-danger">Ya, Hapus Permanen</a>
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

        function bukaModalFilter() { document.getElementById("modalFilter").classList.add("active"); }
        function tutupModalFilter() { document.getElementById("modalFilter").classList.remove("active"); }

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

        function bukaModalHapus(idData, namaFile) {
            document.getElementById("teksNamaFileHapus").innerText = namaFile;
            document.getElementById("linkEksekusiHapus").href = "dokumen.php?aksi=hapus&id=" + idData;
            document.getElementById("modalHapusPeringatan").classList.add("active");
        }
        function tutupModalHapus() {
            document.getElementById("modalHapusPeringatan").classList.remove("active");
        }
    </script>
</body>
</html>