<?php
include "auth.php";
include "koneksi.php";

$idVersi = (int) ($_GET["id"] ?? 0);

$stmt = mysqli_prepare($koneksi, "SELECT versi, file, nama_file FROM versi_dokumen WHERE id_versi = ?");
mysqli_stmt_bind_param($stmt, "i", $idVersi);
mysqli_stmt_execute($stmt);
$v = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$path = $v && !empty($v["file"]) ? __DIR__ . "/uploads/" . basename($v["file"]) : "";

if ($path === "" || !is_file($path)) {
    http_response_code(404);
    echo "File untuk versi ini tidak ditemukan.";
    exit;
}

$namaUnduh = $v["nama_file"] !== null && $v["nama_file"] !== "" ? $v["nama_file"] : basename($v["file"]);
$namaAman  = str_replace(["\r", "\n", '"'], "", $namaUnduh);

catat_riwayat($koneksi, "Mengunduh " . $v["versi"] . " dokumen: " . $namaUnduh);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $namaAman . "\"; filename*=UTF-8''" . rawurlencode($namaAman));
header("Content-Length: " . filesize($path));
header("X-Content-Type-Options: nosniff");
readfile($path);
exit;
