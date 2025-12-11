<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

// siapkan variabel untuk header
$nama_user = $_SESSION['nama_lengkap'] ?? "User";
$inisial   = $_SESSION['inisial'] ?? "U";
