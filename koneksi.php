<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tlpro";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");

// Tabel riwayat versi dokumen (dibuat otomatis jika belum ada)
// Kolom file & nama_file menyimpan "foto" file pada versi tersebut, supaya versi lama tetap bisa diunduh.
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS versi_dokumen (
    id_versi   INT(10) NOT NULL AUTO_INCREMENT,
    id_data    INT(5) NOT NULL,
    versi      VARCHAR(10) NOT NULL,
    username   VARCHAR(255) NOT NULL,
    waktu      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    perubahan  TEXT NOT NULL,
    file       VARCHAR(255) NULL,
    nama_file  VARCHAR(255) NULL,
    PRIMARY KEY (id_versi),
    KEY idx_id_data (id_data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Migrasi: tabel versi_dokumen buatan versi sebelumnya belum punya kolom file & nama_file
$cekKolom = mysqli_query($koneksi, "SHOW COLUMNS FROM versi_dokumen LIKE 'file'");
if ($cekKolom && mysqli_num_rows($cekKolom) === 0) {
    mysqli_query($koneksi, "ALTER TABLE versi_dokumen
        ADD COLUMN file VARCHAR(255) NULL AFTER perubahan,
        ADD COLUMN nama_file VARCHAR(255) NULL AFTER file");
}

// Fungsi bantu: catat satu versi dokumen (v1.0, v1.1, ...)
// $file / $namaFile = file di server dan nama tampilannya pada versi ini
function catat_versi($koneksi, $id_data, $versi, $perubahan, $username = null, $waktu = null, $file = null, $namaFile = null) {
    $username = $username ?? ($_SESSION["username"] ?? "Sistem");
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO versi_dokumen (id_data, versi, username, waktu, perubahan, file, nama_file)
         VALUES (?, ?, ?, COALESCE(?, CURRENT_TIMESTAMP), ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssss", $id_data, $versi, $username, $waktu, $perubahan, $file, $namaFile);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Fungsi bantu: format waktu gaya Indonesia, contoh "12 Jan 2026, 14:32 WIB"
function format_waktu_indo($waktu) {
    $bulan = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    $t = strtotime($waktu);
    return date("d", $t) . " " . $bulan[(int) date("n", $t)] . " " . date("Y, H:i", $t) . " WIB";
}

// Fungsi bantu: catat aktivitas ke tabel riwayat
function catat_riwayat($koneksi, $aksi) {
    // Awali dengan nama pengguna yang sedang login (jika ada)
    $pelaku = $_SESSION["username"] ?? "";
    if ($pelaku !== "") {
        $aksi = "[" . $pelaku . "] " . $aksi;
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO riwayat (aksi) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $aksi);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
