<?php
include "auth.php";
include "koneksi.php";

$folderUpload  = __DIR__ . "/uploads/";
$ekstensiBoleh = ["pdf", "dwg", "xlsx", "xls", "docx", "doc", "png", "jpg", "jpeg"];
$ukuranMaks    = 10 * 1024 * 1024; // 10 MB

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

$stmt = mysqli_prepare($koneksi, "SELECT id_data, waktu, file, nama_file, jenis_file, deskripsi FROM data WHERE id_data = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$dok = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$dok) {
    header("Location: dokumen.php");
    exit;
}

if (isset($_GET["aksi"]) && $_GET["aksi"] === "hapus_versi" && isset($_GET["id_versi"])) {
    $idVersiHapus = (int) $_GET["id_versi"];
    
    $qV = mysqli_prepare($koneksi, "SELECT file, versi FROM versi_dokumen WHERE id_versi = ? AND id_data = ?");
    mysqli_stmt_bind_param($qV, "ii", $idVersiHapus, $id);
    mysqli_stmt_execute($qV);
    $resV = mysqli_stmt_get_result($qV);
    if ($rowV = mysqli_fetch_assoc($resV)) {
        if (!empty($rowV["file"]) && $rowV["file"] !== $dok["file"]) {
            @unlink($folderUpload . $rowV["file"]);
        }
        $delV = mysqli_prepare($koneksi, "DELETE FROM versi_dokumen WHERE id_versi = ?");
        mysqli_stmt_bind_param($delV, "i", $idVersiHapus);
        mysqli_stmt_execute($delV);
        mysqli_stmt_close($delV);
    }
    mysqli_stmt_close($qV);
    
    header("Location: update_dokumen.php?id=" . $id . "&status=hapus_versi_sukses");
    exit;
}

$namaLama      = pathinfo($dok["nama_file"], PATHINFO_FILENAME);
$ekstensiLama  = strtolower(pathinfo($dok["nama_file"], PATHINFO_EXTENSION));
$deskripsiLama = trim($dok["deskripsi"]);

$isiNama      = $namaLama;
$isiUnggah    = "ya";
$isiDeskripsi = $deskripsiLama;
$pesanError   = "";
$filterTanggal = $_GET['filter_tanggal'] ?? '';

function susun_kalimat(array $bagian) {
    $kalimat = count($bagian) > 1
        ? implode(", ", array_slice($bagian, 0, -1)) . " dan " . end($bagian)
        : $bagian[0];
    return ucfirst($kalimat);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nama_dokumen"])) {
    $isiNama      = trim(preg_replace('/[\x00-\x1F\x7F\/\\\\:*?"<>|]/', "", $_POST["nama_dokumen"] ?? ""));
    $isiUnggah    = (($_POST["unggah_ulang"] ?? "tidak") === "ya") ? "ya" : "tidak";
    $isiDeskripsi = trim($_POST["deskripsi"] ?? "");
    $gantiFile    = ($isiUnggah === "ya");

    $ekstensiBaru = null;
    $namaAsliBaru = null;

    if ($isiNama === "") {
        $pesanError = "Nama dokumen tidak boleh kosong.";
    } elseif (mb_strlen($isiNama) > 150) {
        $pesanError = "Nama dokumen maksimal 150 karakter.";
    } elseif (mb_strlen($isiDeskripsi) > 255) {
        $pesanError = "Deskripsi maksimal 255 karakter.";
    } elseif ($gantiFile) {
        if (!isset($_FILES["berkas"]) || $_FILES["berkas"]["error"] !== UPLOAD_ERR_OK) {
            $pesanError = "Pilih file pengganti, atau pilih \"Tidak, gunakan file lama\".";
        } else {
            $namaAsliBaru = basename($_FILES["berkas"]["name"]);
            $ekstensiBaru = strtolower(pathinfo($namaAsliBaru, PATHINFO_EXTENSION));
            if (!in_array($ekstensiBaru, $ekstensiBoleh)) {
                $pesanError = "Tipe berkas .$ekstensiBaru tidak diizinkan.";
            } elseif ($_FILES["berkas"]["size"] > $ukuranMaks) {
                $pesanError = "Ukuran file maksimal 10 MB.";
            }
        }
    }

    if ($pesanError === "") {
        $namaBerubah = ($isiNama !== $namaLama);
        $fileBerubah = $gantiFile;
        $deskBerubah = ($isiDeskripsi !== $deskripsiLama);

        if (!$namaBerubah && !$fileBerubah && !$deskBerubah) {
            $pesanError = "Tidak ada perubahan yang perlu disimpan.";
        }
    }

    if ($pesanError === "") {
        $ekstensiAkhir = $fileBerubah ? $ekstensiBaru : $ekstensiLama;
        $fileBaruDisk  = $dok["file"];
        $namaFileBaru  = $dok["nama_file"];
        $jenisBaru     = $dok["jenis_file"];

        if ($namaBerubah || $fileBerubah) {
            $namaFileBaru = $isiNama . "." . $ekstensiAkhir;
        }

        $fileDipindah = false;
        if ($fileBerubah) {
            if (!is_dir($folderUpload)) {
                mkdir($folderUpload, 0755, true);
            }
            $fileBaruDisk = uniqid("dok_", true) . "." . $ekstensiBaru;
            if (move_uploaded_file($_FILES["berkas"]["tmp_name"], $folderUpload . $fileBaruDisk)) {
                $fileDipindah = true;
                $jenisBaru    = strtoupper($ekstensiBaru);
            } else {
                $pesanError = "Gagal menyimpan file pengganti ke server.";
            }
        }

        if ($pesanError === "") {
            $bagian = [];
            if ($namaBerubah) $bagian[] = "mengubah nama dokumen menjadi \"" . $isiNama . "\"";
            if ($fileBerubah) $bagian[] = ($ekstensiBaru !== $ekstensiLama) ? "mengganti file" : "mengganti file dengan versi baru";
            if ($deskBerubah) $bagian[] = "memperbarui deskripsi";
            $keterangan = susun_kalimat($bagian);

            $q = mysqli_prepare($koneksi, "SELECT COUNT(*) FROM versi_dokumen WHERE id_data = ?");
            mysqli_stmt_bind_param($q, "i", $id);
            mysqli_stmt_execute($q);
            mysqli_stmt_bind_result($q, $jumlahVersi);
            mysqli_stmt_fetch($q);
            mysqli_stmt_close($q);

            mysqli_begin_transaction($koneksi);
            try {
                if ($jumlahVersi === 0) {
                    catat_versi($koneksi, $id, "v1.0", "Dokumen pertama kali diunggah", "-", $dok["waktu"], $dok["file"], $dok["nama_file"]);
                    $jumlahVersi = 1;
                }
                $versiBaru = "v1." . $jumlahVersi;

                $up = mysqli_prepare($koneksi, "UPDATE data SET file = ?, nama_file = ?, jenis_file = ?, deskripsi = ? WHERE id_data = ?");
                mysqli_stmt_bind_param($up, "ssssi", $fileBaruDisk, $namaFileBaru, $jenisBaru, $isiDeskripsi, $id);
                mysqli_stmt_execute($up);
                mysqli_stmt_close($up);

                catat_versi($koneksi, $id, $versiBaru, $keterangan, null, null, $fileBaruDisk, $namaFileBaru);

                mysqli_commit($koneksi);
            } catch (Throwable $e) {
                mysqli_rollback($koneksi);
                if ($fileDipindah) @unlink($folderUpload . $fileBaruDisk);
                $pesanError = "Gagal menyimpan perubahan ke database.";
            }

            if ($pesanError === "") {
                catat_riwayat($koneksi, "Memperbarui dokumen: " . $namaFileBaru . " (" . $versiBaru . ")");
                header("Location: dokumen.php?status=diperbarui&versi=" . urlencode($versiBaru));
                exit;
            }
        }
    }
}

$ukuranFile = "-";
if (is_file($folderUpload . $dok["file"])) {
    $b = filesize($folderUpload . $dok["file"]);
    $ukuranFile = ($b >= 1048576) ? number_format($b / 1048576, 2) . " MB" : number_format($b / 1024, 2) . " KB";
}

$sqlVersi = "SELECT id_versi, versi, username, waktu, perubahan, file, nama_file FROM versi_dokumen WHERE id_data = ?";
$paramsV = [$id];
$typesV = "i";

if ($filterTanggal !== '') {
    $sqlVersi .= " AND DATE(waktu) = ?";
    $paramsV[] = $filterTanggal;
    $typesV .= "s";
}
$sqlVersi .= " ORDER BY id_versi DESC";

$qv = mysqli_prepare($koneksi, $sqlVersi);
mysqli_stmt_bind_param($qv, $typesV, ...$paramsV);
mysqli_stmt_execute($qv);
$riwayatVersi = mysqli_fetch_all(mysqli_stmt_get_result($qv), MYSQLI_ASSOC);
mysqli_stmt_close($qv);

if (count($riwayatVersi) === 0 && $filterTanggal === '') {
    $riwayatVersi[] = [
        "id_versi" => null, "versi" => "v1.0", "username" => "-", "waktu" => $dok["waktu"],
        "perubahan" => "Dokumen pertama kali diunggah",
        "file" => $dok["file"], "nama_file" => $dok["nama_file"],
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Dokumen – Pusat Data Telkom Property</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/update.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="dashboard-container">
        <!-- SIDEBAR DIMUAT DARI FOLDER INCLUDES -->
        <?php include "includes/sidebar.php"; ?>

        <main class="main-content">
            <header class="main-header">
                <button class="btn-toggle" onclick="toggleSidebar()">☰ Menu</button>
                <span>Beranda / <a href="dokumen.php" class="crumb-link">Dokumen</a> / <strong>Update Dokumen</strong></span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Update Dokumen</h1>
                <p class="page-sub">Perbarui informasi dokumen, unggah file baru, dan lihat riwayat perubahan terakhir.</p>

                <?php if (($_GET["status"] ?? "") === "hapus_versi_sukses"): ?>
                    <div style="background:#ffebee;color:#c62828;padding:10px 14px;border-radius:6px;margin-bottom:12px;">
                        Riwayat versi berhasil dihapus.
                    </div>
                <?php endif; ?>

                <div class="kartu">
                    <?php if ($pesanError !== ""): ?>
                        <div class="pesan-error"><?= htmlspecialchars($pesanError) ?></div>
                    <?php endif; ?>

                    <form id="formUpdate" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= (int) $dok["id_data"] ?>">

                        <div class="grup">
                            <label class="judul-isian" for="nama_dokumen">Nama Dokumen</label>
                            <input type="text" id="nama_dokumen" name="nama_dokumen" class="isian"
                                   value="<?= htmlspecialchars($isiNama) ?>" maxlength="150" required>
                            <small class="bantuan">Ekstensi file (.<?= htmlspecialchars($ekstensiLama) ?>) ditambahkan otomatis.</small>
                        </div>

                        <div class="grup">
                            <span class="judul-isian">Unggah Ulang File</span>
                            <label class="pilihan">
                                <input type="radio" name="unggah_ulang" value="ya" <?= $isiUnggah === "ya" ? "checked" : "" ?>>
                                <span>Ya, unggah file baru</span>
                            </label>
                            <label class="pilihan">
                                <input type="radio" name="unggah_ulang" value="tidak" <?= $isiUnggah === "tidak" ? "checked" : "" ?>>
                                <span>Tidak, gunakan file lama</span>
                            </label>
                            <small class="bantuan">File saat ini: <strong><?= htmlspecialchars($dok["nama_file"]) ?></strong> (<?= $ukuranFile ?>)</small>
                        </div>

                        <div class="grup" id="grupFile">
                            <span class="judul-isian">File Pengganti</span>
                            <div class="kotak-file">
                                <span id="namaFileBaru" class="nama-file-baru">Belum ada file dipilih</span>
                                <input type="file" name="berkas" id="berkas" class="input-tersembunyi">
                                <label for="berkas" class="pilih-file-link">Pilih File</label>
                            </div>
                        </div>

                        <div class="grup">
                            <label class="judul-isian" for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="isian area" maxlength="255"><?= htmlspecialchars($isiDeskripsi) ?></textarea>
                        </div>

                        <div class="baris-tombol">
                            <a href="dokumen.php" class="tombol tombol-batal">Batal</a>
                            <button type="button" class="tombol tombol-simpan" onclick="konfirmasiSimpan()">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <div class="kartu">
                    <h2 class="judul-kartu">Riwayat Perubahan</h2>
                    <p class="sub-kartu">Lihat versi dokumen, user yang melakukan perubahan, dan waktu perubahan terakhir.</p>

                    <form method="GET" action="update_dokumen.php" style="display: flex; gap: 8px; margin-bottom: 15px; align-items: center;">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <label style="font-size: 13px; font-weight: 600;">Filter Tanggal:</label>
                        <input type="date" name="filter_tanggal" value="<?= htmlspecialchars($filterTanggal) ?>" style="padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;">
                        <button type="submit" style="padding: 6px 12px; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Filter</button>
                        <?php if ($filterTanggal !== ''): ?>
                            <a href="update_dokumen.php?id=<?= $id ?>" style="padding: 6px 10px; background: #e0e0e0; color: #333; text-decoration: none; border-radius: 4px; font-size: 13px;">Reset</a>
                        <?php endif; ?>
                    </form>

                    <div class="tabel-bungkus">
                        <table class="tabel-versi">
                            <thead>
                                <tr>
                                    <th>Versi</th>
                                    <th>User</th>
                                    <th>Tanggal &amp; Jam</th>
                                    <th>Perubahan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($riwayatVersi) === 0): ?>
                                    <tr><td colspan="5" style="text-align: center;">Tidak ada riwayat pada tanggal tersebut.</td></tr>
                                <?php else: foreach ($riwayatVersi as $v): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($v["versi"]) ?></strong></td>
                                        <td><?= htmlspecialchars($v["username"] ?? "-") ?></td>
                                        <td><?= format_waktu_indo($v["waktu"]) ?></td>
                                        <td><?= htmlspecialchars($v["perubahan"]) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 6px; align-items: center;">
                                                <?php if (!empty($v["file"]) && is_file($folderUpload . $v["file"])): ?>
                                                    <?php $linkUnduh = $v["id_versi"] ? "download_versi.php?id=" . (int) $v["id_versi"] : "uploads/" . rawurlencode($v["file"]); ?>
                                                    <button type="button" class="btn-action btn-download" style="padding: 4px 8px; font-size: 12px;" onclick="konfirmasiUnduhVersi('<?= $linkUnduh ?>', '<?= htmlspecialchars($v["nama_file"] ?? "Dokumen", ENT_QUOTES) ?>', '<?= htmlspecialchars($v["versi"]) ?>')">Unduh</button>
                                                <?php else: ?>
                                                    <span class="tidak-tersedia" style="font-size: 12px;">Tidak tersedia</span>
                                                <?php endif; ?>

                                                <?php if (!empty($v["id_versi"])): ?>
                                                    <button type="button" class="btn-action btn-hapus" style="padding: 4px 8px; font-size: 12px;" onclick="konfirmasiHapusVersi(<?= (int) $v["id_versi"] ?>, '<?= htmlspecialchars($v["versi"], ENT_QUOTES) ?>')">Hapus</button>
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 12px; margin-left: 4px;">Utama</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div id="modalBox" class="modal-overlay">
        <div class="modal-box">
            <div id="modalTitle" class="modal-header">Notifikasi</div>
            <div id="modalMessage" class="modal-body">Pesan informasi sistem.</div>
            <div class="modal-footer"><button class="btn-modal-close" onclick="tutupModalNotif()">Tutup</button></div>
        </div>
    </div>

    <div id="modalKonfirmasi" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">Konfirmasi Perubahan</div>
            <div class="modal-body">Simpan perubahan pada dokumen ini?<br><br><small id="ringkasanPerubahan" style="color: #555;"></small></div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="tutupKonfirmasi()">Batal</button>
                <button type="button" class="btn-modal-confirm" style="background:#f7841f;" onclick="document.getElementById('formUpdate').submit()">Ya, Simpan</button>
            </div>
        </div>
    </div>

    <div id="modalKonfirmasiUnduhVersi" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="color: #8b0000; font-weight: bold;">Konfirmasi Unduh Versi</div>
            <div class="modal-body">Unduh dokumen versi <strong id="versiUnduhText"></strong>?<br><span id="namaFileUnduhVersi" style="font-size: 13px; color: #555;"></span></div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-modal-cancel" onclick="tutupKonfirmasiUnduhVersi()">Batal</button>
                <a id="linkEksekusiUnduhVersi" href="#" class="btn-modal-confirm" style="text-decoration: none; padding: 8px 16px;">Ya, Unduh</a>
            </div>
        </div>
    </div>

    <div id="modalHapusPeringatan" class="modal-overlay hapus-warning">
        <div class="modal-box hapus-warning-box">
            <div class="hapus-warning-header"><div class="hapus-warning-icon">⚠️</div><span>Peringatan Penghapusan Riwayat</span></div>
            <div class="hapus-warning-body">Tindakan ini akan menghapus catatan riwayat versi dokumen terkait secara permanen.<div class="hapus-warning-target" id="teksVersiHapus">-</div></div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-modal-cancel" onclick="tutupKonfirmasiHapus()">Batal</button>
                <a id="linkEksekusiHapus" href="#" class="btn-modal-danger">Ya, Hapus Permanen</a>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() { document.getElementById("sidebarNav").classList.toggle("collapsed"); }
        function toggleDropup(event) { event.stopPropagation(); document.getElementById("dropupMenu").classList.toggle("active"); }
        window.onclick = function() { let dropup = document.getElementById("dropupMenu"); if (dropup.classList.contains("active")) dropup.classList.remove("active"); }
        function tampilkanModalNotif(judul, pesan) { document.getElementById("modalTitle").innerText = judul; document.getElementById("modalMessage").innerHTML = pesan; document.getElementById("modalBox").classList.add("active"); }
        function tutupModalNotif() { document.getElementById("modalBox").classList.remove("active"); }
        function tutupKonfirmasi() { document.getElementById("modalKonfirmasi").classList.remove("active"); }

        function konfirmasiUnduhVersi(url, fileName, versi) {
            document.getElementById("versiUnduhText").innerText = versi;
            document.getElementById("namaFileUnduhVersi").innerText = fileName;
            document.getElementById("linkEksekusiUnduhVersi").href = url;
            document.getElementById("modalKonfirmasiUnduhVersi").classList.add("active");
        }
        function tutupKonfirmasiUnduhVersi() { document.getElementById("modalKonfirmasiUnduhVersi").classList.remove("active"); }

        function konfirmasiHapusVersi(idVersi, versi) {
            document.getElementById("teksVersiHapus").innerText = "Versi: " + versi;
            document.getElementById("linkEksekusiHapus").href = "update_dokumen.php?id=<?= $id ?>&aksi=hapus_versi&id_versi=" + idVersi;
            document.getElementById("modalHapusPeringatan").classList.add("active");
        }
        function tutupKonfirmasiHapus() { document.getElementById("modalHapusPeringatan").classList.remove("active"); }

        const inputNama = document.getElementById("nama_dokumen");
        const inputDeskripsi = document.getElementById("deskripsi");
        const inputFile = document.getElementById("berkas");
        const namaFileBaru = document.getElementById("namaFileBaru");
        const grupFile = document.getElementById("grupFile");
        const radios = document.querySelectorAll('input[name="unggah_ulang"]');

        function gantiModeFile() {
            const ya = document.querySelector('input[name="unggah_ulang"]:checked').value === "ya";
            inputFile.disabled = !ya;
            inputFile.required = ya;
            grupFile.classList.toggle("nonaktif", !ya);
            if (!ya) { inputFile.value = ""; namaFileBaru.textContent = "Belum ada file dipilih"; }
        }
        radios.forEach(r => r.addEventListener("change", gantiModeFile));
        gantiModeFile();

        inputFile.addEventListener("change", function() {
            namaFileBaru.textContent = this.files.length ? this.files[0].name : "Belum ada file dipilih";
        });

        function konfirmasiSimpan() {
            const form = document.getElementById("formUpdate");
            if (!form.reportValidity()) return;
            document.getElementById("modalKonfirmasi").classList.add("active");
        }
    </script>
</body>
</html>