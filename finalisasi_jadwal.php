<?php
include "auth.php";     // cek login
include "config.php";   // koneksi database

$page_title   = "Finalisasi Jadwal Kuliah";
$active_menu  = "finalisasi";

// =========================
// 1. PERSIAPAN FILTER
// =========================
$filter_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$filter_kelas    = $_GET['kelas'] ?? "";
$filter_hari     = $_GET['hari'] ?? "";

$hari_options = ["Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];

// ambil daftar semester dan kelas dari jadwal yang sudah ada
$semesters = mysqli_query($koneksi, "
    SELECT DISTINCT semester 
    FROM jadwal 
    ORDER BY semester
");

$kelas_list = mysqli_query($koneksi, "
    SELECT DISTINCT kelas 
    FROM jadwal 
    ORDER BY kelas
");

// =========================
// 2. QUERY DATA JADWAL
// =========================
$where = " WHERE 1=1 ";

if ($filter_semester > 0) {
    $where .= " AND j.semester = $filter_semester ";
}

if ($filter_kelas !== "") {
    $kelas_safe = mysqli_real_escape_string($koneksi, $filter_kelas);
    $where .= " AND j.kelas = '$kelas_safe' ";
}

if ($filter_hari !== "") {
    $hari_safe = mysqli_real_escape_string($koneksi, $filter_hari);
    $where .= " AND j.hari = '$hari_safe' ";
}

$sql_jadwal = "
    SELECT 
        j.*,
        mk.kode_mk,
        mk.nama_mk,
        mk.sks,
        d1.nama AS nama_dosen1,
        d2.nama AS nama_dosen2,
        r.nama_ruang
    FROM jadwal j
    JOIN mata_kuliah mk ON j.id_mk = mk.id_mk
    JOIN dosen d1       ON j.id_dosen1 = d1.id_dosen
    LEFT JOIN dosen d2  ON j.id_dosen2 = d2.id_dosen
    JOIN ruang r        ON j.id_ruang   = r.id_ruang
    $where
    ORDER BY 
        j.semester,
        j.kelas,
        j.hari,
        j.jam_mulai
";
$data_jadwal = mysqli_query($koneksi, $sql_jadwal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-shell">

    <?php include "components/sidebar.php"; ?>

    <div class="app-main">
        <div class="app-wrapper">

            <?php include "components/header.php"; ?>
            <?php include "components/breadcrumb.php"; ?>

            <?php
            $section_title = "Filter Jadwal Kuliah";
            $icon = "🔍";
            include "components/section_title.php";
            ?>

            <!-- FILTER CARD -->
            <div class="content-card">
                <form method="get" class="need-validation">
                    <div class="form-row">
                        <div>
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-input">
                                <option value="0">Semua Semester</option>
                                <?php while ($s = mysqli_fetch_assoc($semesters)): ?>
                                    <option value="<?= $s['semester']; ?>"
                                        <?= $filter_semester == $s['semester'] ? "selected" : ""; ?>>
                                        Semester <?= $s['semester']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Kelas</label>
                            <select name="kelas" class="form-input">
                                <option value="">Semua Kelas</option>
                                <?php while ($k = mysqli_fetch_assoc($kelas_list)): ?>
                                    <option value="<?= htmlspecialchars($k['kelas']); ?>"
                                        <?= $filter_kelas === $k['kelas'] ? "selected" : ""; ?>>
                                        Kelas <?= htmlspecialchars($k['kelas']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Hari</label>
                            <select name="hari" class="form-input">
                                <option value="">Semua Hari</option>
                                <?php foreach ($hari_options as $h): ?>
                                    <option value="<?= $h; ?>"
                                        <?= $filter_hari === $h ? "selected" : ""; ?>>
                                        <?= $h; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 12px;">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                Terapkan Filter
                            </button>
                            <a href="finalisasi_jadwal.php"
                               class="btn btn-secondary btn-small"
                               style="margin-left: 8px;">
                                Reset
                            </a>
                            <a href="penyusunan_jadwal.php"
                               class="btn btn-outline btn-small"
                               style="margin-left: 8px;">
                                Kembali ke Penyusunan Jadwal
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <?php
            $section_title = "Tabel Jadwal Kuliah Terfinalisasi";
            $icon = "📅";
            include "components/section_title.php";
            ?>

            <!-- TABEL JADWAL -->
            <div class="content-card">
                <div class="table-wrapper">
                    <table class="table-green">
                        <thead>
                        <tr>
                            <th>Semester</th>
                            <th>Kelas</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Kode MK</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Dosen</th>
                            <th>Ruang</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($data_jadwal) > 0): ?>
                            <?php while ($j = mysqli_fetch_assoc($data_jadwal)): ?>
                                <tr>
                                    <td>S<?= $j['semester']; ?></td>
                                    <td><?= htmlspecialchars($j['kelas']); ?></td>
                                    <td><?= htmlspecialchars($j['hari']); ?></td>
                                    <td><?= substr($j['jam_mulai'], 0, 5) . " - " . substr($j['jam_selesai'], 0, 5); ?></td>
                                    <td><?= htmlspecialchars($j['kode_mk']); ?></td>
                                    <td><?= htmlspecialchars($j['nama_mk']); ?></td>
                                    <td><?= htmlspecialchars($j['sks']); ?></td>
                                    <td>
                                        <?= htmlspecialchars($j['nama_dosen1']); ?>
                                        <?php if (!empty($j['nama_dosen2'])): ?>
                                            <br><small>co: <?= htmlspecialchars($j['nama_dosen2']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($j['nama_ruang']); ?></td>
                                    <td><?= htmlspecialchars($j['keterangan']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align:center;">
                                    Belum ada jadwal yang sesuai filter atau jadwal belum disusun.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- .app-wrapper -->
    </div><!-- .app-main -->

</div><!-- .app-shell -->

<script src="assets/js/app.js"></script>
</body>
</html>
