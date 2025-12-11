<div class="app-header-wrap">
    <header class="app-header">
        <div class="app-header-left">
            <div class="logo-small">
                <img src="assets/img/logo-uin.png" alt="Logo">
            </div>
            <div class="app-title">Sistem Penjadwalan Kuliah</div>
        </div>

        <div class="user-box">
            <div class="user-avatar">
                <?= htmlspecialchars($inisial ?? 'A'); ?>
            </div>
            <div class="user-info">
                <div class="user-name">
                    <?= htmlspecialchars($nama_user ?? 'User'); ?>
                </div>
                <div class="user-role">
                    <?= htmlspecialchars($_SESSION['role'] ?? 'sekretaris'); ?>
                </div>
            </div>

        </div>
    </header>
</div>
