<?php
include "auth.php";     // cek login
include "config.php";   // koneksi database

$page_title = "Input Data Ruang Kuliah";

// =========================
// MODE EDIT → AMBIL DATA RUANG
// =========================
$id_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nama_ruang  = "";
$gedung      = "";
$lantai      = "";
$jenis_ruang = "";

if ($id_edit > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM ruang WHERE id_ruang = $id_edit");
    if ($row = mysqli_fetch_assoc($q)) {
        $nama_ruang  = $row['nama_ruang'];
        $gedung      = $row['gedung'];
        $lantai      = $row['lantai'];
        $jenis_ruang = $row['jenis_ruang'];
    }
}

// array untuk menampung error
$errors = [];

// =========================
// SIMPAN / UPDATE DATA RUANG
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ruang   = $_POST['id_ruang'] ?? "";

    // ambil data mentah dulu
    $nama_f_raw   = trim($_POST['nama_ruang']  ?? '');
    $gedung_f_raw = trim($_POST['gedung']      ?? '');
    $lantai_f_raw = trim($_POST['lantai']      ?? '');
    $jenis_f_raw  = trim($_POST['jenis_ruang'] ?? '');

    // ========== VALIDASI WAJIB ISI ==========
    if ($nama_f_raw === '') {
        $errors[] = "Nama ruang wajib diisi.";
    }
    if ($gedung_f_raw === '') {
        $errors[] = "Gedung wajib diisi.";
    }
    if ($lantai_f_raw === '') {
        $errors[] = "Lantai wajib diisi.";
    }
    if ($jenis_f_raw === '') {
        $errors[] = "Jenis ruang wajib diisi.";
    }

    // ========== VALIDASI LANTAI HARUS ANGKA DAN RANGE ==========
    if ($lantai_f_raw !== '') {
        if (!ctype_digit($lantai_f_raw)) {
            $errors[] = "Lantai harus berupa angka bulat.";
        } else {
            $lantai_int = (int)$lantai_f_raw;
            if ($lantai_int < 1 || $lantai_int > 10) {
                $errors[] = "Lantai harus di antara 1 sampai 10.";
            }
        }
    }

    // kalau ada error, jangan simpan dan isi ulang nilai form dari input user
    if (!empty($errors)) {
        $nama_ruang  = $nama_f_raw;
        $gedung      = $gedung_f_raw;
        $lantai      = $lantai_f_raw;
        $jenis_ruang = $jenis_f_raw;
    } else {
        // aman, baru escape dan simpan
        $nama_f   = mysqli_real_escape_string($koneksi, $nama_f_raw);
        $gedung_f = mysqli_real_escape_string($koneksi, $gedung_f_raw);
        $lantai_f = (int)$lantai_f_raw;
        $jenis_f  = mysqli_real_escape_string($koneksi, $jenis_f_raw);

        if ($id_ruang) {
            // UPDATE
            $sql = "UPDATE ruang SET
                        nama_ruang='$nama_f',
                        gedung='$gedung_f',
                        lantai=$lantai_f,
                        jenis_ruang='$jenis_f'
                    WHERE id_ruang=$id_ruang";
        } else {
            // INSERT
            $sql = "INSERT INTO ruang (nama_ruang, gedung, lantai, jenis_ruang)
                    VALUES ('$nama_f', '$gedung_f', $lantai_f, '$jenis_f')";
        }

        mysqli_query($koneksi, $sql);
        header("Location: input_ruang.php");
        exit;
    }
}


// =========================
// HAPUS DATA
// =========================
if (isset($_GET['hapus'])) {
    $hapus = (int)$_GET['hapus'];

    // cek apakah ruang dipakai di jadwal
    $cek = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS jml
         FROM jadwal
         WHERE id_ruang = $hapus"
    );
    $row = mysqli_fetch_assoc($cek);

    if ($row['jml'] > 0) {
        // ruang sedang dipakai, jangan dihapus
        echo "<script>
                alert('Tidak bisa menghapus ruang karena masih dipakai di jadwal (" . $row['jml'] . " data). Hapus/ubah jadwalnya dulu.');
                window.location.href = 'input_ruang.php';
              </script>";
        exit;
    }

    // kalau tidak dipakai → baru hapus
    mysqli_query($koneksi, "DELETE FROM ruang WHERE id_ruang = $hapus");
    echo "<script>
            alert('Data ruang berhasil dihapus.');
            window.location.href = 'input_ruang.php';
          </script>";
    exit;
}


// =========================
// TAMPILKAN DATA DALAM TABEL
// =========================
$data_ruang = mysqli_query($koneksi, "SELECT * FROM ruang ORDER BY gedung, nama_ruang");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/JS/app.js"></script>
</head>
<body>
<div class="app-shell">

    <?php include "components/sidebar.php"; ?>

    <div class="app-main">
        <div class="app-wrapper">
            <?php include "components/header.php"; ?>
            <?php include "components/breadcrumb.php"; ?>

            <?php
            $section_title = "Form Input Data Ruangan";
            $icon = "🏫";
            include "components/section_title.php";
            ?>

            <?php if (!empty($errors)): ?>
                <div style="background:#fee2e2;color:#991b1b;padding:10px 12px;
                            border-radius:8px;font-size:13px;margin-bottom:12px;">
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <form method="post" class="need-validation">
                    <input type="hidden" name="id_ruang" value="<?= $id_edit ?: ""; ?>">

                    <div class="form-row">
                        <div>
                            <label class="form-label">Nama Ruang*</label>
                            <input type="text" class="form-input"
                                   name="nama_ruang"
                                   value="<?= htmlspecialchars($nama_ruang); ?>"
                                   required>
                        </div>
                        <div>
                            <label class="form-label">Gedung*</label>
                            <input type="text" class="form-input"
                                   name="gedung"
                                   value="<?= htmlspecialchars($gedung); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="form-label">Lantai*</label>
                            <input type="number" class="form-input" min="1" max="10"
                                   name="lantai"
                                   value="<?= htmlspecialchars($lantai); ?>"
                                   required>
                        </div>
                        <div>
                            <label class="form-label">Jenis Ruang*</label>
                            <input type="text" class="form-input"
                                   name="jenis_ruang"
                                   value="<?= htmlspecialchars($jenis_ruang); ?>"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <?= $id_edit ? "Update Data" : "Simpan Data"; ?>
                    </button>
                </form>
            </div>

            <?php
            $section_title = "Data Ruangan";
            $icon = "📦";
            include "components/section_title.php";
            ?>

            <div class="content-card">
                <div class="table-wrapper">
                    <table class="table-green">
                        <thead>
                        <tr>
                            <th>Nama Ruang</th>
                            <th>Gedung</th>
                            <th>Lantai</th>
                            <th>Jenis Ruang</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($r = mysqli_fetch_assoc($data_ruang)): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nama_ruang']); ?></td>
                                <td><?= htmlspecialchars($r['gedung']); ?></td>
                                <td><?= htmlspecialchars($r['lantai']); ?></td>
                                <td><?= htmlspecialchars($r['jenis_ruang']); ?></td>
                                <td>
                                    <a href="input_ruang.php?id=<?= $r['id_ruang']; ?>"
                                       class="btn btn-primary btn-small">
                                        Edit
                                    </a>

                                    <a href="input_ruang.php?hapus=<?= $r['id_ruang']; ?>"
                                       class="btn btn-danger btn-small js-confirm-delete"
                                       data-item="<?= htmlspecialchars($r['nama_ruang']); ?>">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($data_ruang) == 0): ?>
                            <tr>
                                <td colspan="5" align="center">Belum ada data ruangan.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
