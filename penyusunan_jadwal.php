<?php
include "auth.php";     // cek login
include "config.php";   // koneksi database

$page_title = "Penyusunan Jadwal Mata Kuliah";

// =========================
// 1. PERSIAPAN DROPDOWN
// =========================
$mk_list    = mysqli_query($koneksi, "SELECT id_mk, kode_mk, nama_mk, semester FROM mata_kuliah ORDER BY semester, kode_mk");
$dosen_list = mysqli_query($koneksi, "SELECT id_dosen, nama FROM dosen ORDER BY nama");
$ruang_list = mysqli_query($koneksi, "SELECT id_ruang, nama_ruang, gedung FROM ruang ORDER BY gedung, nama_ruang");

$hari_options = ["Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];

// =========================
// 2. MODE EDIT → AMBIL DATA JADWAL
// =========================
$id_edit      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_mk        = "";
$id_dosen1    = "";
$id_dosen2    = "";
$id_ruang     = "";
$hari         = "";
$jam_mulai    = "";
$jam_selesai  = "";
$semester     = "";
$kelas        = "";
$keterangan   = "";
$status       = "ok";

if ($id_edit > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM jadwal WHERE id_jadwal = $id_edit");
    if ($row = mysqli_fetch_assoc($q)) {
        $id_mk       = $row['id_mk'];
        $id_dosen1   = $row['id_dosen1'];
        $id_dosen2   = $row['id_dosen2'];
        $id_ruang    = $row['id_ruang'];
        $hari        = $row['hari'];
        $jam_mulai   = $row['jam_mulai'];
        $jam_selesai = $row['jam_selesai'];
        $semester    = $row['semester'];
        $kelas       = $row['kelas'];
        $keterangan  = $row['keterangan'];
        $status      = $row['status'];
    }
}

// =========================
// 3. SIMPAN / UPDATE JADWAL
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jadwal   = $_POST['id_jadwal'] ?? "";
    $id_mk_f     = (int)$_POST['id_mk'];
    $id_d1_f     = (int)$_POST['id_dosen1'];
    $id_d2_f     = !empty($_POST['id_dosen2']) ? (int)$_POST['id_dosen2'] : null;
    $id_ruang_f  = (int)$_POST['id_ruang'];
    $hari_f      = mysqli_real_escape_string($koneksi, $_POST['hari']);
    $jm_f        = mysqli_real_escape_string($koneksi, $_POST['jam_mulai']);
    $js_f        = mysqli_real_escape_string($koneksi, $_POST['jam_selesai']);
    $sem_f       = (int)$_POST['semester'];
    $kelas_f     = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $ket_f       = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? "");
    $status_f    = "ok"; // untuk sekarang anggap semua ok

    if ($id_jadwal) {
        // UPDATE
        $sql = "UPDATE jadwal SET
                    id_mk      = $id_mk_f,
                    id_dosen1  = $id_d1_f,
                    id_dosen2  = ".($id_d2_f ? $id_d2_f : "NULL").",
                    id_ruang   = $id_ruang_f,
                    hari       = '$hari_f',
                    jam_mulai  = '$jm_f',
                    jam_selesai= '$js_f',
                    semester   = $sem_f,
                    kelas      = '$kelas_f',
                    status     = '$status_f',
                    keterangan = '$ket_f'
                WHERE id_jadwal = $id_jadwal";
    } else {
        // INSERT
        $sql = "INSERT INTO jadwal
                (id_mk, id_dosen1, id_dosen2, id_ruang, hari, jam_mulai, jam_selesai, semester, kelas, status, keterangan)
                VALUES
                ($id_mk_f, $id_d1_f, ".($id_d2_f ? $id_d2_f : "NULL").", $id_ruang_f,
                 '$hari_f', '$jm_f', '$js_f', $sem_f, '$kelas_f', '$status_f', '$ket_f')";
    }

    mysqli_query($koneksi, $sql);
    header("Location: penyusunan_jadwal.php");
    exit;
}

// =========================
// 4. HAPUS JADWAL
// =========================
if (isset($_GET['hapus'])) {
    $hapus = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM jadwal WHERE id_jadwal = $hapus");
    header("Location: penyusunan_jadwal.php");
    exit;
}
// =========================
// 5. DATA JADWAL UNTUK TABEL
// =========================
$sql_jadwal = "
    SELECT 
        j.*,
        mk.kode_mk,
        mk.nama_mk,
        d1.nama AS nama_dosen1,
        d2.nama AS nama_dosen2,
        r.nama_ruang
    FROM jadwal j
    JOIN mata_kuliah mk ON j.id_mk = mk.id_mk
    JOIN dosen d1       ON j.id_dosen1 = d1.id_dosen
    LEFT JOIN dosen d2  ON j.id_dosen2 = d2.id_dosen
    JOIN ruang r        ON j.id_ruang = r.id_ruang
    ORDER BY j.semester, j.kelas, j.hari, j.jam_mulai
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
            $section_title = "Form Penyusunan Jadwal";
            $icon = "🗓️";
            include "components/section_title.php";
            ?>

            <div class="content-card">
            <form method="post" class="need-validation">
                <input type="hidden" name="id_jadwal" value="<?= $id_edit ?: ""; ?>">

                <div class="form-grid">

                    <!-- SEMESTER -->
                    <div class="form-group">
                        <label class="form-label">Semester*</label>
                        <select name="semester" id="select-semester" class="form-input" required>
                            <option value="">-- Pilih Semester --</option>
                            <option value="2" <?= $semester == 2 ? "selected" : ""; ?>>Semester 2</option>
                            <option value="3" <?= $semester == 3 ? "selected" : ""; ?>>Semester 3</option>
                            <option value="4" <?= $semester == 4 ? "selected" : ""; ?>>Semester 4</option>
                            <option value="5" <?= $semester == 5 ? "selected" : ""; ?>>Semester 5</option>
                            <option value="6" <?= $semester == 6 ? "selected" : ""; ?>>Semester 6</option>
                        </select>
                    </div>

                    <!-- MATA KULIAH -->
            <div class="form-group">
                <label class="form-label">Mata Kuliah*</label>
                <select name="id_mk" id="select-mk" class="form-input" required>
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php
                    mysqli_data_seek($mk_list, 0);
                    while ($mk = mysqli_fetch_assoc($mk_list)) : ?>
                        <option
                            value="<?= $mk['id_mk']; ?>"
                            data-semester="<?= $mk['semester']; ?>"
                            <?= $mk['id_mk'] == $id_mk ? "selected" : ""; ?>
                        >
                            <?= $mk['kode_mk']; ?> - <?= $mk['nama_mk']; ?> (S<?= $mk['semester']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- DOSEN 1 -->
            <div class="form-group">
                <label class="form-label">Dosen 1*</label>
                <select name="id_dosen1" class="form-input" required>
                    <option value="">-- Pilih Dosen 1 --</option>
                    <?php mysqli_data_seek($dosen_list, 0); ?>
                    <?php while ($d = mysqli_fetch_assoc($dosen_list)): ?>
                        <option value="<?= $d['id_dosen']; ?>"
                            <?= $d['id_dosen'] == $id_dosen1 ? "selected" : ""; ?>>
                            <?= $d['nama']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- DOSEN 2 -->
            <div class="form-group">
                <label class="form-label">Dosen 2 (opsional)</label>
                <select name="id_dosen2" class="form-input">
                    <option value="">-- Tidak ada / Co-Teach --</option>
                    <?php mysqli_data_seek($dosen_list, 0); ?>
                    <?php while ($d = mysqli_fetch_assoc($dosen_list)): ?>
                        <option value="<?= $d['id_dosen']; ?>"
                            <?= $d['id_dosen'] == $id_dosen2 ? "selected" : ""; ?>>
                            <?= $d['nama']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- RUANG -->
            <div class="form-group">
                <label class="form-label">Ruang Kuliah*</label>
                <select name="id_ruang" class="form-input" required>
                    <option value="">-- Pilih Ruang --</option>
                    <?php mysqli_data_seek($ruang_list, 0); ?>
                    <?php while ($r = mysqli_fetch_assoc($ruang_list)): ?>
                        <option value="<?= $r['id_ruang']; ?>"
                            <?= $r['id_ruang'] == $id_ruang ? "selected" : ""; ?>>
                            <?= $r['nama_ruang']." - ".$r['gedung']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- KELAS -->
            <div class="form-group">
                <label class="form-label">Kelas*</label>
                <input type="text" name="kelas" class="form-input"
                       placeholder="A / B / C" value="<?= htmlspecialchars($kelas); ?>" required>
            </div>

            <!-- HARI -->
            <div class="form-group">
                <label class="form-label">Hari*</label>
                <select name="hari" class="form-input" required>
                    <option value="">-- Pilih Hari --</option>
                    <?php foreach ($hari_options as $h): ?>
                        <option value="<?= $h; ?>" <?= $h == $hari ? "selected" : ""; ?>>
                            <?= $h; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- JAM MULAI -->
            <div class="form-group">
                <label class="form-label">Jam Mulai*</label>
                <input type="time" name="jam_mulai" class="form-input"
                       value="<?= $jam_mulai; ?>" required>
            </div>

            <!-- JAM SELESAI -->
            <div class="form-group">
                <label class="form-label">Jam Selesai*</label>
                <input type="time" name="jam_selesai" class="form-input"
                       value="<?= $jam_selesai; ?>" required>
            </div>

            <!-- KETERANGAN (FULL WIDTH) -->
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" class="form-input"
                       placeholder="Catatan tambahan (opsional)"
                       value="<?= htmlspecialchars($keterangan); ?>">
            </div>

        </div> <!-- end .form-grid -->

            <button type="submit" class="btn btn-primary mt-3">
                <?= $id_edit ? "Update Jadwal" : "Simpan Jadwal"; ?>
            </button>
        </form>
    </div>
 <?php
    $section_title = "Daftar Jadwal Tersusun";
    $icon = "📅";
    include "components/section_title.php";
    ?>

    <div class="content-card">
        <div class="table-wrapper">
            <table class="table-green">
                <thead>
                <tr>
                    <th>Semester</th>
                    <th>Kelas</th>
                    <th>Mata Kuliah</th>
                    <th>Dosen</th>
                    <th>Ruang</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($j = mysqli_fetch_assoc($data_jadwal)): ?>
                    <tr>
                        <td>S<?= $j['semester']; ?></td>
                        <td><?= htmlspecialchars($j['kelas']); ?></td>
                        <td><?= htmlspecialchars($j['kode_mk']." - ".$j['nama_mk']); ?></td>
                        <td>
                            <?= htmlspecialchars($j['nama_dosen1']); ?>
                            <?php if (!empty($j['nama_dosen2'])): ?>
                                <br><small>co: <?= htmlspecialchars($j['nama_dosen2']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= str_replace("\n", " ", htmlspecialchars($j['nama_ruang'])); ?>
                        </td>
                        
                        <td><?= htmlspecialchars($j['hari']); ?></td>
                        <td class="col-jam">
                         <?= substr($j['jam_mulai'],0,5) . " - " . substr($j['jam_selesai'],0,5); ?>
                        </td>

                        
                        <td>
                            <div class="table-actions">
                            <a href="penyusunan_jadwal.php?id=<?= $j['id_jadwal']; ?>"
                               class="btn btn-primary btn-small">
                                Edit
                            </a>
                            <a href="penyusunan_jadwal.php?hapus=<?= $j['id_jadwal']; ?>"
                               class="btn btn-danger btn-small js-confirm-delete"
                               data-item="<?= htmlspecialchars($j['kode_mk']." ".$j['kelas']); ?>">
                                Hapus
                            </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($data_jadwal) == 0): ?>
                    <tr>
                        <td colspan="9" align="center">Belum ada jadwal tersimpan.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>


<script src="assets/JS/app.js"></script>
</body>
</html>
