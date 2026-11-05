<?php
// Penjaga halaman: sertakan file ini di setiap halaman yang wajib login.
// Letakkan SEBELUM include "koneksi.php" supaya sesi sudah aktif saat catat_riwayat() dipanggil.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(["httponly" => true, "samesite" => "Lax"]);
    session_start();
}

if (empty($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

$namaPengguna = $_SESSION["username"] ?? "Pengguna";
?>
