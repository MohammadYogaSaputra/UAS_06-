<?php
session_start();

// Hanya proses logout jika request method adalah POST.
// Ini mencegah logout tidak sengaja saat seseorang mengetik URL logout.php langsung.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
} else {
    // Jika ada yang mencoba mengakses via GET, arahkan ke halaman utama.
    header("Location: index.php");
    exit();
}
?>