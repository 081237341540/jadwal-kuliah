<?php
include "auth.php";     // cek login + ambil $nama_user, $inisial
include "config.php";   // koneksi database

$page_title = "Dashboard";
$active_menu = "dashboard";   

// =============================
// HITUNG DATA DASAR
// =============================

// Jumlah mata kuliah
$q_mk = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM mata_kuliah");
$row_mk = mysqli_fetch_assoc($q_mk);
$jumlah_mk = (int)($row_mk['jml'] ?? 0);

// Jumlah dosen
$q_dosen = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM dosen");
$row_dosen = mysqli_fetch_assoc($q_dosen);
$jumlah_dosen = (int)($row_dosen['jml'] ?? 0);

// Jumlah ruang
$q_ruang = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM ruang");
$row_ruang = mysqli_fetch_assoc($q_ruang);
$jumlah_ruang = (int)($row_ruang['jml'] ?? 0);

// Jumlah jadwal tersusun
$q_jadwal = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM jadwal");
$row_jadwal = mysqli_fetch_assoc($q_jadwal);
$jumlah_jadwal = (int)($row_jadwal['jml'] ?? 0);

// =============================
// STATUS KELENGKAPAN DATA
// =============================
$mk_lengkap     = $jumlah_mk > 0;
$dosen_lengkap  = $jumlah_dosen > 0;
$ruang_lengkap  = $jumlah_ruang > 0;
$jadwal_ada     = $jumlah_jadwal > 0;

// Progress penyusunan jadwal sederhana
if ($jumlah_mk > 0) {
    $persen_jadwal = min(100, round(($jumlah_jadwal / $jumlah_mk) * 100));
} else {
    $persen_jadwal = 0;
}

// =============================
// CEK MASALAH POTENSIAL
// - Bentrok dosen
// - Bentrok ruang
// =============================

// Cek bentrok dosen (dosen mengajar di hari dan jam yang sama)
$q_bentrok_dosen = mysqli_query(
    $koneksi,
    "SELECT 1
     FROM jadwal j1
     JOIN jadwal j2
       ON j1.id_jadwal <> j2.id_jadwal
      AND j1.id_dosen1 = j2.id_dosen1
      AND j1.hari = j2.hari
      AND NOT (j1.jam_selesai <= j2.jam_mulai OR j2.jam_selesai <= j1.jam_mulai)
     LIMIT 1"
);
$ada_bentrok_dosen = mysqli_num_rows($q_bentrok_dosen) > 0;

// Cek bentrok ruang (ruang dipakai dua kelas di waktu yang sama)
$q_bentrok_ruang = mysqli_query(
    $koneksi,
    "SELECT 1
     FROM jadwal j1
     JOIN jadwal j2
       ON j1.id_jadwal <> j2.id_jadwal
      AND j1.id_ruang = j2.id_ruang
      AND j1.hari = j2.hari
      AND NOT (j1.jam_selesai <= j2.jam_mulai OR j2.jam_selesai <= j1.jam_mulai)
     LIMIT 1"
);
$ada_bentrok_ruang = mysqli_num_rows($q_bentrok_ruang) > 0;

// Tentukan status jadwal secara umum
if ($jumlah_jadwal == 0) {
    $status_jadwal_text  = "Belum ada jadwal yang disusun.";
    $status_jadwal_badge = "status-badge status-warning";
} elseif ($ada_bentrok_dosen || $ada_bentrok_ruang) {
    $status_jadwal_text  = "Jadwal sudah tersusun, tetapi masih ada bentrok yang perlu diperbaiki.";
    $status_jadwal_badge = "status-badge status-danger";
} else {
    $status_jadwal_text  = "Jadwal sudah tersedia. Tidak ada konflik terdeteksi";
    $status_jadwal_badge = "status-badge status-success";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistem Penjadwalan Kuliah</title>
    <link rel="stylesheet" href="assets/CSS/style.css"> 
</head>
<body>

<div class="app-shell">

    <?php include "components/sidebar.php"; ?>
   

    <div class="app-main">
        <div class="app-wrapper">

            <?php include "components/header.php"; ?>
            <?php include "components/breadcrumb.php"; ?>

            <!-- ===== SECTION Status Sistem ===== -->
            <?php
                $section_title = "Status Sistem Penjadwalan";
                $icon = "🧩";
                include "components/section_title.php";
            ?>

                <div class="content-card">
                    <div class="grid-cards dashboard-status">

                        <!-- Kelengkapan Data -->
                        <div class="card-stat">
                            <div class="card-stat-header">
                                <div class="card-stat-icon">📚</div>
                                <h3>Kelengkapan Data</h3>
                            </div>

                            <ul class="status-list">
                                <li>
                                    <span>Mata Kuliah</span>
                                    <?php if ($mk_lengkap): ?>
                                        <span class="status-badge status-success">Siap (<?= $jumlah_mk; ?>)</span>
                                    <?php else: ?>
                                        <span class="status-badge status-warning">Belum ada data</span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    <span>Dosen</span>
                                    <?php if ($dosen_lengkap): ?>
                                        <span class="status-badge status-success">Siap (<?= $jumlah_dosen; ?>)</span>
                                    <?php else: ?>
                                        <span class="status-badge status-warning">Belum ada data</span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    <span>Ruang Kuliah</span>
                                    <?php if ($ruang_lengkap): ?>
                                        <span class="status-badge status-success">Siap (<?= $jumlah_ruang; ?>)</span>
                                    <?php else: ?>
                                        <span class="status-badge status-warning">Belum ada data</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>

                        <!-- Status Jadwal -->
                        <div class="card-stat">
                            <div class="card-stat-header">
                                <div class="card-stat-icon">📅</div>
                                <h3>Status Jadwal Kuliah</h3>
                            </div>

                            <p class="<?= $status_jadwal_badge; ?> status-main-text">
                                <?= htmlspecialchars($status_jadwal_text); ?>
                            </p>

                            <div class="status-metrics">
                                <div>
                                    <div class="label">Total jadwal</div>
                                    <div class="value"><?= $jumlah_jadwal; ?><span class="unit"> data</span></div>
                                </div>
                                <div>
                                    <div class="label">Progres</div>
                                    <div class="value"><?= $persen_jadwal; ?><span class="unit">%</span></div>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a href="penyusunan_jadwal.php" class="btn btn-primary btn-small">Kelola Jadwal</a>
                                <a href="finalisasi_jadwal.php" class="btn btn-outline btn-small">Lihat Jadwal Final</a>
                            </div>
                        </div>

                        <!-- Masalah Terdeteksi -->
                        <div class="card-stat">
                            <div class="card-stat-header">
                                <div class="card-stat-icon">⚠️</div>
                                <h3>Masalah Terdeteksi</h3>
                            </div>

                            <?php if (!$jadwal_ada): ?>
                                <p class="status-empty">
                                    Belum ada jadwal yang disusun. Mulai dari menu Penyusunan Jadwal.
                                </p>
                            <?php else: ?>
                                <ul class="status-list">
                                    <li>
                                        <span>Bentrok Dosen</span>
                                        <?php if ($ada_bentrok_dosen): ?>
                                            <span class="status-badge status-danger">Ada bentrok</span>
                                        <?php else: ?>
                                            <span class="status-badge status-success">Tidak ada</span>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <span>Bentrok Ruang</span>
                                        <?php if ($ada_bentrok_ruang): ?>
                                            <span class="status-badge status-danger">Ada bentrok</span>
                                        <?php else: ?>
                                            <span class="status-badge status-success">Tidak ada</span>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                                <p class="status-note">
                                    Bentrok harus diperbaiki.
                                </p>
                        <?php endif; ?>
                    </div>
                 </div>
            </div>

        </div><!-- .app-wrapper -->
    </div><!-- .app-main -->
    

</div><!-- .app-shell -->

<script src="assets/js/app.js"></script>
</body>
</html>


