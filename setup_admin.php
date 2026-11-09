<?php
// Jalankan SEKALI lewat browser: http://localhost/NAMA_FOLDER/setup_admin.php
// Membuat (atau mereset) akun admin dengan password admin123.
// HAPUS file ini setelah berhasil dipakai.
include "koneksi.php";

$username = "admin";
$password = "admin123";
$hash     = password_hash($password, PASSWORD_DEFAULT);

$cek = mysqli_prepare($koneksi, "SELECT id_user FROM user WHERE username = ? LIMIT 1");
mysqli_stmt_bind_param($cek, "s", $username);
mysqli_stmt_execute($cek);
$ada = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
mysqli_stmt_close($cek);

if ($ada) {
    $q = mysqli_prepare($koneksi, "UPDATE user SET Password = ? WHERE username = ?");
    mysqli_stmt_bind_param($q, "ss", $hash, $username);
    $pesan = "Password akun '$username' berhasil direset.";
} else {
    $q = mysqli_prepare($koneksi, "INSERT INTO user (username, Password) VALUES (?, ?)");
    mysqli_stmt_bind_param($q, "ss", $username, $hash);
    $pesan = "Akun '$username' berhasil dibuat.";
}

if (mysqli_stmt_execute($q)) {
    echo "<p style='font-family:sans-serif'><b>$pesan</b><br>Username: <code>$username</code> &nbsp; Password: <code>$password</code><br><br>"
       . "<a href='login.php'>Ke halaman login</a><br><br>"
       . "<span style='color:#b91c1c'>Penting: hapus file <code>setup_admin.php</code> setelah ini.</span></p>";
} else {
    echo "Gagal: " . htmlspecialchars(mysqli_error($koneksi));
}
