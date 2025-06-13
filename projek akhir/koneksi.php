<?php
$conn = mysqli_connect("localhost", "root", "", "movie_booking");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
