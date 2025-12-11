<?php
include "auth.php";     // cek login
include "config.php";   // koneksi database

$page_title  = "Input Data Dosen";
$active_menu = "dosen";

// MODE EDIT → AMBIL DATA DOSEN
$id_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nip = "";
$nama = "";
$email = "";
$no_telp = "";

if ($id_edit > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM dosen WHERE id_dosen = $id_edit");
    if ($row = mysqli_fetch_assoc($q)) {
        $nip     = $row['nip'];
        $nama    = $row['nama'];
        $email   = $row['email'];
        $no_telp = $row['no_telp'];
    }
}

$errors = [];   // taruh di atas sebelum if POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_dosen = $_POST['id_dosen'] ?? "";
    $nip_f    = trim($_POST['nip'] ?? '');
    $nama_f   = trim($_POST['nama'] ?? '');
    $email_f  = trim($_POST['email'] ?? '');
    $telp_f   = trim($_POST['no_telp'] ?? '');

    // 1. Validasi wajib isi
        // 1. Validasi wajib isi
    if ($nip_f === '') {
        $errors[] = "NIP wajib diisi.";
    }
    if ($nama_f === '') {
        $errors[] = "Nama dosen wajib diisi.";
    }

    // 2. Validasi format NIP (hanya angka, min 8 digit, max 20 misalnya)
    if ($nip_f !== '' && !preg_match('/^[0-9]{8,20}$/', $nip_f)) {
        $errors[] = "NIP harus berupa angka dengan panjang 8 sampai 20 digit.";
    }

    // 3. Validasi format email (jika diisi)
    if ($email_f !== '' && !filter_var($email_f, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // 4. Validasi nomor telepon (opsional tapi rapi)
    if ($telp_f !== '' && !preg_match('/^[0-9+ ]+$/', $telp_f)) {
        $errors[] = "No. telepon hanya boleh berisi angka, spasi, dan tanda +.";
    }

    // 5. Cek duplikasi NIP di database
    if ($nip_f !== '') {
        $id_dosen_int = (int) $id_dosen;

        $sql_cek_nip = "SELECT COUNT(*) AS jml 
                        FROM dosen 
                        WHERE nip = '$nip_f'";

        // kalau mode edit, jangan hitung dirinya sendiri
        if ($id_dosen_int > 0) {
            $sql_cek_nip .= " AND id_dosen <> $id_dosen_int";
        }

        $q_cek_nip = mysqli_query($koneksi, $sql_cek_nip);
        $row_cek   = mysqli_fetch_assoc($q_cek_nip);

        if ($row_cek['jml'] > 0) {
            $errors[] = "NIP sudah digunakan oleh dosen lain. Gunakan NIP yang berbeda.";
        }
    }

    if (count($errors) === 0) {
        // aman, boleh disimpan
        $nip_f   = mysqli_real_escape_string($koneksi, $nip_f);
        $nama_f  = mysqli_real_escape_string($koneksi, $nama_f);
        $email_f = mysqli_real_escape_string($koneksi, $email_f);
        $telp_f  = mysqli_real_escape_string($koneksi, $telp_f);


        if ($id_dosen) {
            $sql = "UPDATE dosen SET
                        nip='$nip_f',
                        nama='$nama_f',
                        email='$email_f',
                        no_telp='$telp_f'
                    WHERE id_dosen=$id_dosen";
        } else {
            $sql = "INSERT INTO dosen (nip, nama, email, no_telp)
                    VALUES ('$nip_f', '$nama_f', '$email_f', '$telp_f')";
        }

        $ok = mysqli_query($koneksi, $sql);
        if (!$ok) {
            $errors[] = "Gagal menyimpan data dosen ke database.";
        } else {
            header("Location: input_dosen.php");
            exit;
        }
    }
}

// HAPUS DATA DOSEN
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    // cek apakah dosen dipakai di jadwal
    $cek = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS jml 
         FROM jadwal 
         WHERE id_dosen1 = $id OR id_dosen2 = $id"
    );
    $row = mysqli_fetch_assoc($cek);

    if ($row['jml'] > 0) {
        echo "<script>
                alert('Tidak bisa menghapus dosen karena masih dipakai di jadwal ($row[jml] data). Hapus/ubah jadwalnya dulu.');
                window.location.href = 'input_dosen.php';
              </script>";
        exit;
    }

    mysqli_query($koneksi, "DELETE FROM dosen WHERE id_dosen = $id");
    echo "<script>
            alert('Data dosen berhasil dihapus.');
            window.location.href = 'input_dosen.php';
          </script>";
    exit;
}

// TAMPILKAN DATA DALAM TABEL
$data_dosen = mysqli_query($koneksi, "SELECT * FROM dosen ORDER BY nama");
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
            $section_title = "Form Input Data Dosen";
            $icon = "👨‍🏫";
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
                    <input type="hidden" name="id_dosen" value="<?= $id_edit ?: ""; ?>">

                    <div class="form-row">
                        <div>
                            <label class="form-label">NIP*</label>
                            <input type="text"
                                class="form-input"
                                name="nip"
                                value="<?= htmlspecialchars($nip); ?>"
                                required
                                minlength="8"
                                maxlength="20"
                                pattern="[0-9]{8,20}"
                                title="NIP harus berupa angka 8 sampai 20 digit">

                        </div>
                        <div>
                            <label class="form-label">Nama Dosen*</label>
                            <input type="text"
                                   class="form-input"
                                   name="nama"
                                   value="<?= htmlspecialchars($nama); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email"
                                   class="form-input"
                                   name="email"
                                   value="<?= htmlspecialchars($email); ?>">
                        </div>
                        <div>
                            <label class="form-label">No. Telp / WA</label>
                            <input type="text"
                                class="form-input"
                                name="no_telp"
                                value="<?= htmlspecialchars($no_telp); ?>"
                                pattern="[0-9+ ]+"
                                title="Hanya boleh angka, spasi, dan tanda +">

                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <?= $id_edit ? "Update Data" : "Simpan Data"; ?>
                    </button>
                </form>
            </div>

            <?php
            $section_title = "Data Dosen";
            $icon = "📋";
            include "components/section_title.php";
            ?>

            <div class="content-card">
                <div class="table-wrapper">
                    <table class="table-green">
                        <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telp</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($d = mysqli_fetch_assoc($data_dosen)): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['nip']); ?></td>
                                <td><?= htmlspecialchars($d['nama']); ?></td>
                                <td><?= htmlspecialchars($d['email']); ?></td>
                                <td><?= htmlspecialchars($d['no_telp']); ?></td>
                                <td>
                                    <a href="input_dosen.php?id=<?= $d['id_dosen']; ?>"
                                       class="btn btn-primary btn-small">
                                        Edit
                                    </a>
                                    <a href="input_dosen.php?hapus=<?= $d['id_dosen']; ?>"
                                       class="btn btn-danger btn-small js-confirm-delete"
                                       data-item="<?= htmlspecialchars($d['nama']); ?>">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($data_dosen) == 0): ?>
                            <tr>
                                <td colspan="5" align="center">Belum ada data dosen.</td>
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
