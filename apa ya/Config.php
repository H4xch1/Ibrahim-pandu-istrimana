<?php// Konfigurasi koneksi ke database$host = "localhost";
// Konfigurasi koneksi ke database
$host = "localhost";    // server database (biasanya localhost)
$user = "root";         // username MySQL (default: root)
$pass = "";             // password MySQL (kosong di XAMPP)
$db   = "biodata_db";   // nama database kamu

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>