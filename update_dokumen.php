<?php
include "auth.php";
include "koneksi.php";

$folderUpload  = __DIR__ . "/uploads/";
$ekstensiBoleh = ["pdf", "dwg", "xlsx", "xls", "docx", "doc", "png", "jpg", "jpeg"];
$ukuranMaks    = 10 * 1024 * 1024; // 10 MB

// ---------- 1. Ambil dokumen yang akan diperbarui ----------
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

$namaLama      = pathinfo($dok["nama_file"], PATHINFO_FILENAME);          // nama tanpa ekstensi
$ekstensiLama  = strtolower(pathinfo($dok["nama_file"], PATHINFO_EXTENSION));
$deskripsiLama = trim($dok["deskripsi"]);

// Nilai awal form (berisi data saat ini)
$isiNama      = $namaLama;
$isiUnggah    = "ya";            // sesuai desain: default "Ya, unggah file baru"
$isiDeskripsi = $deskripsiLama;
$pesanError   = "";

// Fungsi kecil: gabungkan daftar perubahan menjadi satu kalimat
function susun_kalimat(array $bagian) {
    $kalimat = count($bagian) > 1
        ? implode(", ", array_slice($bagian, 0, -1)) . " dan " . end($bagian)
        : $bagian[0];
    return ucfirst($kalimat);
}

// ---------- 2. Proses simpan perubahan ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isiNama      = trim(preg_replace('/[\x00-\x1F\x7F\/\\\\:*?"<>|]/', "", $_POST["nama_dokumen"] ?? ""));
    $isiUnggah    = (($_POST["unggah_ulang"] ?? "tidak") === "ya") ? "ya" : "tidak";
    $isiDeskripsi = trim($_POST["deskripsi"] ?? "");
    $gantiFile    = ($isiUnggah === "ya");

    $ekstensiBaru = null;
    $namaAsliBaru = null;

    // Validasi masukan
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
                $pesanError = "Tipe berkas .$ekstensiBaru tidak diizinkan. Boleh: " . implode(", ", $ekstensiBoleh) . ".";
            } elseif ($_FILES["berkas"]["size"] > $ukuranMaks) {
                $pesanError = "Ukuran file maksimal 10 MB.";
            }
        }
    }

    // Deteksi apa saja yang berubah
    if ($pesanError === "") {
        $namaBerubah = ($isiNama !== $namaLama);
        $fileBerubah = $gantiFile;
        $deskBerubah = ($isiDeskripsi !== $deskripsiLama);

        if (!$namaBerubah && !$fileBerubah && !$deskBerubah) {
            $pesanError = "Tidak ada perubahan yang perlu disimpan.";
        }
    }

    // Simpan
    if ($pesanError === "") {
        $ekstensiAkhir = $fileBerubah ? $ekstensiBaru : $ekstensiLama;
        $fileBaruDisk  = $dok["file"];
        $namaFileBaru  = $dok["nama_file"];
        $jenisBaru     = $dok["jenis_file"];

        if ($namaBerubah || $fileBerubah) {
            $namaFileBaru = $isiNama . "." . $ekstensiAkhir;
        }

        // 2a. Simpan file pengganti ke folder uploads (nama unik)
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
            // 2b. Susun keterangan perubahan untuk riwayat versi
            $bagian = [];
            if ($namaBerubah) {
                $bagian[] = "mengubah nama dokumen menjadi \"" . $isiNama . "\"";
            }
            if ($fileBerubah) {
                $bagian[] = ($ekstensiBaru !== $ekstensiLama)
                    ? "mengganti file (." . $ekstensiLama . " → ." . $ekstensiBaru . ")"
                    : "mengganti file dengan versi baru";
            }
            if ($deskBerubah) {
                $bagian[] = ($deskripsiLama === "") ? "menambah deskripsi"
                          : (($isiDeskripsi === "") ? "menghapus deskripsi" : "memperbarui deskripsi");
            }
            $keterangan = susun_kalimat($bagian);

            // 2c. Tentukan nomor versi berikutnya
            $q = mysqli_prepare($koneksi, "SELECT COUNT(*) FROM versi_dokumen WHERE id_data = ?");
            mysqli_stmt_bind_param($q, "i", $id);
            mysqli_stmt_execute($q);
            mysqli_stmt_bind_result($q, $jumlahVersi);
            mysqli_stmt_fetch($q);
            mysqli_stmt_close($q);

            mysqli_begin_transaction($koneksi);
            try {
                // Dokumen lama yang belum punya riwayat: buat versi awal v1.0 dulu
                if ($jumlahVersi === 0) {
                    catat_versi($koneksi, $id, "v1.0", "Dokumen pertama kali diunggah", "-", $dok["waktu"], $dok["file"], $dok["nama_file"]);
                    $jumlahVersi = 1;
                }
                $versiBaru = "v1." . $jumlahVersi;

                // 2d. Perbarui baris dokumen
                $up = mysqli_prepare($koneksi,
                    "UPDATE data SET file = ?, nama_file = ?, jenis_file = ?, deskripsi = ? WHERE id_data = ?");
                mysqli_stmt_bind_param($up, "ssssi", $fileBaruDisk, $namaFileBaru, $jenisBaru, $isiDeskripsi, $id);
                mysqli_stmt_execute($up);
                mysqli_stmt_close($up);

                // 2e. Catat versi baru
                catat_versi($koneksi, $id, $versiBaru, $keterangan, null, null, $fileBaruDisk, $namaFileBaru);

                mysqli_commit($koneksi);
            } catch (Throwable $e) {
                mysqli_rollback($koneksi);
                if ($fileDipindah) {
                    @unlink($folderUpload . $fileBaruDisk);   // batalkan file baru
                }
                $pesanError = "Gagal menyimpan perubahan ke database.";
            }

            if ($pesanError === "") {
                // File lama TIDAK dihapus: tetap tersimpan di folder uploads dan bisa
                // diunduh dari tabel Riwayat Perubahan (per versi).

                catat_riwayat($koneksi, "Memperbarui dokumen: " . $namaFileBaru . " (" . $versiBaru . ")");

                header("Location: dokumen.php?status=diperbarui&versi=" . urlencode($versiBaru));
                exit;
            }
        }
    }
}

// ---------- 3. Data untuk tampilan ----------
$ukuranFile = "-";
if (is_file($folderUpload . $dok["file"])) {
    $b = filesize($folderUpload . $dok["file"]);
    $ukuranFile = ($b >= 1048576) ? number_format($b / 1048576, 2) . " MB" : number_format($b / 1024, 2) . " KB";
}

$qv = mysqli_prepare($koneksi, "SELECT id_versi, versi, username, waktu, perubahan, file, nama_file FROM versi_dokumen WHERE id_data = ? ORDER BY id_versi DESC");
mysqli_stmt_bind_param($qv, "i", $id);
mysqli_stmt_execute($qv);
$riwayatVersi = mysqli_fetch_all(mysqli_stmt_get_result($qv), MYSQLI_ASSOC);
mysqli_stmt_close($qv);

// Dokumen lama tanpa riwayat: tampilkan versi awal sebagai keterangan
if (count($riwayatVersi) === 0) {
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
                <span>Beranda / <a href="dokumen.php" class="crumb-link">Dokumen</a> / <strong>Update Dokumen</strong></span>
            </header>

            <div class="content-body">
                <h1 class="page-title">Update Dokumen</h1>
                <p class="page-sub">Perbarui informasi dokumen, unggah file baru, dan lihat riwayat perubahan terakhir.</p>

                <!-- Kartu form -->
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
                            <small class="bantuan">Jika tidak diganti, file lama tetap digunakan dan tidak akan diubah. File versi sebelumnya selalu tetap tersimpan dan bisa diunduh dari Riwayat Perubahan.
                                File saat ini: <strong><?= htmlspecialchars($dok["nama_file"]) ?></strong> (<?= $ukuranFile ?>)</small>
                        </div>

                        <div class="grup" id="grupFile">
                            <span class="judul-isian">File Pengganti</span>
                            <div class="kotak-file">
                                <span id="namaFileBaru" class="nama-file-baru">Belum ada file dipilih</span>
                                <input type="file" name="berkas" id="berkas" class="input-tersembunyi">
                                <label for="berkas" class="pilih-file-link">Pilih File</label>
                            </div>
                            <small class="bantuan">Maksimal 10 MB. Format: PDF, DWG, XLSX, XLS, DOCX, DOC, PNG, JPG, JPEG.</small>
                        </div>

                        <div class="grup">
                            <label class="judul-isian" for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="isian area" maxlength="255"
                                      placeholder="Masukkan deskripsi singkat untuk perubahan dokumen ini."><?= htmlspecialchars($isiDeskripsi) ?></textarea>
                        </div>

                        <div class="baris-tombol">
                            <a href="dokumen.php" class="tombol tombol-batal">Batal</a>
                            <button type="button" class="tombol tombol-simpan" onclick="konfirmasiSimpan()">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <!-- Kartu riwayat perubahan -->
                <div class="kartu">
                    <h2 class="judul-kartu">Riwayat Perubahan</h2>
                    <p class="sub-kartu">Lihat versi dokumen, user yang melakukan perubahan, dan waktu perubahan terakhir.</p>

                    <div class="tabel-bungkus">
                        <table class="tabel-versi">
                            <thead>
                                <tr>
                                    <th>Versi</th>
                                    <th>User</th>
                                    <th>Tanggal &amp; Jam</th>
                                    <th>Perubahan</th>
                                    <th>File</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayatVersi as $v): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($v["versi"]) ?></strong></td>
                                        <td><?= htmlspecialchars($v["username"]) ?></td>
                                        <td><?= format_waktu_indo($v["waktu"]) ?></td>
                                        <td><?= htmlspecialchars($v["perubahan"]) ?></td>
                                        <td>
                                            <?php if (!empty($v["file"]) && is_file($folderUpload . $v["file"])): ?>
                                                <?php $linkUnduh = $v["id_versi"]
                                                    ? "download_versi.php?id=" . (int) $v["id_versi"]
                                                    : "uploads/" . rawurlencode($v["file"]); ?>
                                                <a href="<?= $linkUnduh ?>" class="btn-unduh-versi"
                                                   title="<?= htmlspecialchars($v["nama_file"] ?? "") ?>">Unduh</a>
                                            <?php else: ?>
                                                <span class="tidak-tersedia">Tidak tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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

    <!-- Modal Konfirmasi Simpan -->
    <div id="modalKonfirmasi" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">Konfirmasi Perubahan</div>
            <div class="modal-body">
                Simpan perubahan pada dokumen ini? Versi baru akan dicatat di riwayat perubahan.
                <br><br>
                <small id="ringkasanPerubahan" style="color: #555;"></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="tutupKonfirmasi()">Belum, Cek Lagi</button>
                <button type="button" class="btn-modal-confirm" style="background:#f7841f;" onclick="document.getElementById('formUpdate').submit()">Ya, Simpan</button>
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
        function tutupKonfirmasi() {
            document.getElementById("modalKonfirmasi").classList.remove("active");
        }

        // ---- Logika form ----
        const namaAwal      = <?= json_encode($namaLama) ?>;
        const deskripsiAwal = <?= json_encode($deskripsiLama) ?>;
        const inputNama     = document.getElementById("nama_dokumen");
        const inputDeskripsi = document.getElementById("deskripsi");
        const inputFile     = document.getElementById("berkas");
        const namaFileBaru  = document.getElementById("namaFileBaru");
        const grupFile      = document.getElementById("grupFile");
        const radios        = document.querySelectorAll('input[name="unggah_ulang"]');

        function gantiModeFile() {
            const ya = document.querySelector('input[name="unggah_ulang"]:checked').value === "ya";
            inputFile.disabled = !ya;
            inputFile.required = ya;
            grupFile.classList.toggle("nonaktif", !ya);
            if (!ya) {
                inputFile.value = "";
                namaFileBaru.textContent = "Belum ada file dipilih";
            }
        }
        radios.forEach(r => r.addEventListener("change", gantiModeFile));
        gantiModeFile();

        inputFile.addEventListener("change", function() {
            namaFileBaru.textContent = this.files.length ? this.files[0].name : "Belum ada file dipilih";
        });

        // Ringkasan perubahan di kotak konfirmasi
        function konfirmasiSimpan() {
            const form = document.getElementById("formUpdate");
            if (!form.reportValidity()) return;

            const daftar = [];
            if (inputNama.value.trim() !== namaAwal) daftar.push("nama dokumen");
            if (inputFile.files.length > 0) daftar.push("file");
            if (inputDeskripsi.value.trim() !== deskripsiAwal) daftar.push("deskripsi");

            if (daftar.length === 0) {
                tampilkanModalNotif("Tidak Ada Perubahan", "Belum ada data yang diubah. Ubah nama, file, atau deskripsi terlebih dahulu.");
                return;
            }
            document.getElementById("ringkasanPerubahan").textContent = "Yang diubah: " + daftar.join(", ") + ".";
            document.getElementById("modalKonfirmasi").classList.add("active");
        }
    </script>
</body>
</html>
