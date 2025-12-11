<?php
// ==============================
// CONFIG KONEKSI SERVER MYSQL
// ==============================
$host = "localhost";
$user = "root";
$pass = "";

// Konek ke MySQL TANPA memilih DB dulu
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Gagal konek ke MySQL: " . mysqli_connect_error());
}

// ==============================
// 1. BUAT DATABASE
// ==============================
$sql_create_db = "CREATE DATABASE IF NOT EXISTS db_jadwal_kuliah
                  CHARACTER SET utf8mb4
                  COLLATE utf8mb4_unicode_ci";

if (!mysqli_query($conn, $sql_create_db)) {
    die("Gagal membuat database: " . mysqli_error($conn));
}

echo "✔ Database 'db_jadwal_kuliah' dibuat / sudah ada.<br>";

mysqli_select_db($conn, "db_jadwal_kuliah");

// ==============================
// 2. TABEL USER
// ==============================
$sql_user = "CREATE TABLE IF NOT EXISTS tb_user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role VARCHAR(30) DEFAULT 'sekretaris'
)";
runQuery($conn, $sql_user, "tb_user");

// ==============================
// 3. TABEL MATA KULIAH
// ==============================
$sql_mk = "CREATE TABLE IF NOT EXISTS mata_kuliah (
    id_mk INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(20) NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    semester TINYINT NOT NULL,
    sks TINYINT NOT NULL,
    jenis ENUM('wajib','pilihan') DEFAULT 'wajib'
)";
runQuery($conn, $sql_mk, "mata_kuliah");

// ==============================
// 4. TABEL DOSEN
// ==============================
$sql_dosen = "CREATE TABLE IF NOT EXISTS dosen (
    id_dosen INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30),
    nama VARCHAR(100),
    email VARCHAR(100),
    no_telp VARCHAR(20)
)";
runQuery($conn, $sql_dosen, "dosen");

// ==============================
// 5. TABEL RUANG
// ==============================
$sql_ruang = "CREATE TABLE IF NOT EXISTS ruang (
    id_ruang INT AUTO_INCREMENT PRIMARY KEY,
    nama_ruang VARCHAR(50),
    gedung VARCHAR(30),
    lantai TINYINT,
    jenis_ruang VARCHAR(30)
)";
runQuery($conn, $sql_ruang, "ruang");

// ==============================
// 6. TABEL JADWAL
// ==============================
$sql_jadwal = "CREATE TABLE IF NOT EXISTS jadwal (
    id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
    id_mk INT NOT NULL,
    id_dosen1 INT NOT NULL,
    id_dosen2 INT NULL,
    id_ruang INT NOT NULL,
    hari VARCHAR(10) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    semester TINYINT NOT NULL,
    kelas VARCHAR(10) NOT NULL,
    status ENUM('ok','bentrok') DEFAULT 'ok',
    keterangan VARCHAR(255),
    
    FOREIGN KEY (id_mk) REFERENCES mata_kuliah(id_mk),
    FOREIGN KEY (id_dosen1) REFERENCES dosen(id_dosen),
    FOREIGN KEY (id_dosen2) REFERENCES dosen(id_dosen),
    FOREIGN KEY (id_ruang) REFERENCES ruang(id_ruang)
)";
runQuery($conn, $sql_jadwal, "jadwal");

// ==============================
// 7. BUAT USER LOGIN AWAL
// ==============================
$cek = mysqli_query($conn, "SELECT * FROM tb_user WHERE username='sekjur' LIMIT 1");

if (mysqli_num_rows($cek) == 0) {
    $hash = password_hash("123456", PASSWORD_DEFAULT);
    mysqli_query($conn,
        "INSERT INTO tb_user (username, password_hash, nama_lengkap)
         VALUES ('sekjur', '$hash', 'Asrul Azhari Muin, S.Kom., M.Kom')"
    );
    echo "✔ User awal 'sekjur' dibuat. Password: 123456<br>";
} else {
    echo "✔ User 'sekjur' sudah ada.<br>";
}

echo "<br><b>SEMUA PROSES SELESAI.</b><br>";
echo "Kamu boleh hapus file setup_db.php setelah sukses.";

// ==============================
// FUNGSI HELPER
// ==============================
function runQuery($conn, $sql, $tableName) {
    if (mysqli_query($conn, $sql)) {
        echo "✔ Tabel $tableName siap.<br>";
    } else {
        die("❌ Gagal membuat tabel $tableName: " . mysqli_error($conn));
    }
}
?>
