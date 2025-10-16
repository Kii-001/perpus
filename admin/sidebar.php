<?php
$current_page = basename($_SERVER['PHP_SELF']);

?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-book"></i> Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="kategori.php" class="<?= $current_page == 'kategori.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Kategori Buku
            </a>
        </li>
        <li>
            <a href="buku.php" class="<?= $current_page == 'buku.php' ? 'active' : '' ?>">
                <i class="fas fa-book"></i> Manajemen Buku
            </a>
        </li>
        <li>
            <a href="peminjaman.php" class="<?= $current_page == 'peminjaman.php' ? 'active' : '' ?>">
                <i class="fas fa-exchange-alt"></i> Peminjaman
            </a>
        </li>
        <li>
            <a href="denda.php" class="<?= $current_page == 'denda.php' ? 'active' : '' ?>">
                <i class="fas fa-money-bill-wave"></i> Manajemen Denda
            </a>
        </li>
        <li>
            <a href="mahasiswa.php" class="<?= $current_page == 'mahasiswa.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Data Mahasiswa
            </a>
        </li>
        <li>
            <a href="laporan.php" class="<?= $current_page == 'laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>