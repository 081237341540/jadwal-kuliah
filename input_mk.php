<?php
include "auth.php";
include "config.php";

$page_title  = "Input Data Mata Kuliah";
$active_menu = "mk";

$errors = [];

// =========================
// MODE EDIT → AMBIL DATA
// =========================
$id_edit  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$kode_mk  = "";
$nama_mk  = "";
$semester = "";
$sks      = "";
$jenis    = "";

// kalau mode edit, ambil data lama untuk prefilling form
if ($id_edit > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM mata_kuliah WHERE id_mk = $id_edit");
    if ($row = mysqli_fetch_assoc($q)) {
        $kode_mk  = $row['kode_mk'];
        $nama_mk  = $row['nama_mk'];
        $semester = $row['semester'];
        $sks      = $row['sks'];
        $jenis    = $row['jenis'];   // 'wajib' / 'pilihan'
    }
}

// =========================
// HAPUS DATA
// =========================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    // CEK apakah mk sedang dipakai di jadwal
    $cek = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS jml FROM jadwal WHERE id_mk = $id_hapus"
    );
    $row = mysqli_fetch_assoc($cek);

    if ($row['jml'] > 0) {
        echo "<script>
                alert('Tidak bisa menghapus mata kuliah karena masih dipakai di jadwal ($row[jml] data). Hapus/ubah jadwalnya dulu.');
                window.location.href = 'input_mk.php';
              </script>";
        exit;
    }

    // jika aman → hapus
    mysqli_query($koneksi, "DELETE FROM mata_kuliah WHERE id_mk = $id_hapus");

    echo "<script>
            alert('Data mata kuliah berhasil dihapus.');
            window.location.href = 'input_mk.php';
          </script>";
    exit;
}


// =========================
// PROSES SIMPAN / UPDATE
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_mk_post = (int)($_POST['id_mk'] ?? 0);

    // ambil data dari form
    $kode_mk  = trim($_POST['kode_mk'] ?? '');
    $nama_mk  = trim($_POST['nama_mk'] ?? '');
    $semester = (int)($_POST['semester'] ?? 0);
    $sks      = (int)($_POST['sks'] ?? 0);
    $jenis    = strtolower(trim($_POST['jenis'] ?? '')); // wajib/pilihan

    // escape untuk SQL
    $kode_sql  = mysqli_real_escape_string($koneksi, $kode_mk);
    $nama_sql  = mysqli_real_escape_string($koneksi, $nama_mk);
    $jenis_sql = mysqli_real_escape_string($koneksi, $jenis);

    // supaya saat EDIT, dia tidak ngetrip duplikat dirinya sendiri
    $exclude = $id_mk_post ? "AND id_mk <> $id_mk_post" : "";

    // CEK DUPLIKAT KODE_MK
    $cekKode = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS jml FROM mata_kuliah 
         WHERE kode_mk = '$kode_sql' $exclude"
    );
    $rowKode = mysqli_fetch_assoc($cekKode);
    if ($rowKode['jml'] > 0) {
        $errors[] = "Kode Mata Kuliah sudah digunakan. Silakan gunakan kode lain.";
    }

    // CEK DUPLIKAT NAMA_MK
    $cekNama = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS jml FROM mata_kuliah 
         WHERE nama_mk = '$nama_sql' $exclude"
    );
    $rowNama = mysqli_fetch_assoc($cekNama);
    if ($rowNama['jml'] > 0) {
        $errors[] = "Nama Mata Kuliah sudah ada. Jangan buat dua mata kuliah dengan nama yang sama.";
    }

    // kalau TIDAK ada error → simpan (INSERT atau UPDATE)
    if (empty($errors)) {
        if ($id_mk_post) {
            // UPDATE
            $sql = "UPDATE mata_kuliah SET
                        kode_mk  = '$kode_sql',
                        nama_mk  = '$nama_sql',
                        semester = $semester,
                        sks      = $sks,
                        jenis    = '$jenis_sql'
                    WHERE id_mk = $id_mk_post";
        } else {
            // INSERT
            $sql = "INSERT INTO mata_kuliah (kode_mk, nama_mk, semester, sks, jenis)
                    VALUES ('$kode_sql', '$nama_sql', $semester, $sks, '$jenis_sql')";
        }

        mysqli_query($koneksi, $sql);
        header("Location: input_mk.php");
        exit;
    }
}

// =========================
// AMBIL DATA UNTUK TABEL
// =========================
$data_mk = mysqli_query($koneksi, "SELECT * FROM mata_kuliah ORDER BY semester, kode_mk");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title; ?></title>
    <link rel="stylesheet" href="assets/CSS/style.css">
</head>
<body>

<div class="app-shell">

    <?php include "components/sidebar.php"; ?>

    <div class="app-main">
        <div class="app-wrapper">

            <?php include "components/header.php"; ?>
            <?php include "components/breadcrumb.php"; ?>

            <?php
            $section_title = "Form Input Mata Kuliah";
            $icon = "📘";
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
                <form method="post">
                    <input type="hidden" name="id_mk" value="<?= $id_edit ?: 0; ?>">

                    <div class="form-row">
                        <div>
                            <label class="form-label">Kode Mata Kuliah*</label>
                            <input type="text" class="form-input"
                                   name="kode_mk"
                                   value="<?= htmlspecialchars($kode_mk); ?>"
                                   required>
                        </div>
                        <div>
                            <label class="form-label">Nama Mata Kuliah*</label>
                            <input type="text" class="form-input"
                                   name="nama_mk"
                                   value="<?= htmlspecialchars($nama_mk); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="form-label">Semester*</label>
                            <input type="number" class="form-input"
                                   name="semester"
                                   min="1" max="8"
                                   value="<?= htmlspecialchars($semester); ?>"
                                   required>
                        </div>
                        <div>
                            <label class="form-label">SKS*</label>
                            <input type="number" class="form-input"
                                   name="sks"
                                   min="1" max="4"
                                   value="<?= htmlspecialchars($sks); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="form-label">Jenis Mata Kuliah*</label>
                            <select class="form-input" name="jenis" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="wajib"   <?= $jenis === 'wajib'   ? 'selected' : ''; ?>>Wajib</option>
                                <option value="pilihan" <?= $jenis === 'pilihan' ? 'selected' : ''; ?>>Pilihan</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <?= $id_edit ? "Update Data" : "Simpan Data"; ?>
                    </button>
                </form>
            </div>

            <?php
            $section_title = "Data Mata Kuliah";
            $icon = "📚";
            include "components/section_title.php";
            ?>

            <div class="content-card">
                <div class="table-wrapper">
                    <table class="table-green">
                        <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Semester</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Jenis</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($m = mysqli_fetch_assoc($data_mk)): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['kode_mk']); ?></td>
                                <td><?= htmlspecialchars($m['semester']); ?></td>
                                <td><?= htmlspecialchars($m['nama_mk']); ?></td>
                                <td><?= htmlspecialchars($m['sks']); ?></td>
                                <td><?= htmlspecialchars(ucfirst($m['jenis'])); ?></td>
                                <td>
                                    <a href="input_mk.php?id=<?= $m['id_mk']; ?>"
                                       class="btn btn-primary btn-small">
                                        Edit
                                    </a>
                                    <a href="input_mk.php?hapus=<?= $m['id_mk']; ?>"
                                       class="btn btn-danger btn-small js-confirm-delete"
                                       data-item="<?= htmlspecialchars($m['nama_mk']); ?>">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($data_mk) == 0): ?>
                            <tr>
                                <td colspan="6" align="center">Belum ada data mata kuliah.</td>
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
