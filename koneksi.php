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
