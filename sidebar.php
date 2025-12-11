<div class="sidebar">
    <div class="sidebar-logo">
        <img src="assets/img/logo-uin.png" alt="Logo">
        <span>Penjadwalan Kuliah</span>
    </div>

    <ul class="sidebar-menu">
        <li class="<?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>">
            <a href="dashboard.php">📊 Dashboard</a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'mk' ? 'active' : '' ?>">
            <a href="input_mk.php">📘 Mata Kuliah</a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'dosen' ? 'active' : '' ?>">
            <a href="input_dosen.php">👨‍🏫 Dosen</a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'ruang' ? 'active' : '' ?>">
            <a href="input_ruang.php">🏫 Ruang</a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'penyusunan' ? 'active' : '' ?>">
            <a href="penyusunan_jadwal.php">🗓️ Penyusunan Jadwal</a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'finalisasi' ? 'active' : '' ?>">
            <a href="finalisasi_jadwal.php">✅ Finalisasi Jadwal</a>
        </li>
    </ul>

    <div class="sidebar-bottom">
        <a href="logout.php" class="logout-btn" id="btn-logout">🚪 Keluar</a>
    </div>
</div>
