<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i>Dashboard Admin</h1>
                <p>Selamat datang, <?= $_SESSION['admin_nama'] ?></p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Buku</h3>
                        <p class="stat-number">
                            <?php
                            $sql = "SELECT COUNT(*) FROM buku";
                            $stmt = $pdo->query($sql);
                            echo $stmt->fetchColumn();
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Mahasiswa</h3>
                        <p class="stat-number">
                            <?php
                            $sql = "SELECT COUNT(*) FROM mahasiswa WHERE status = 'aktif'";
                            $stmt = $pdo->query($sql);
                            echo $stmt->fetchColumn();
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Peminjaman Aktif</h3>
                        <p class="stat-number">
                            <?php
                            $sql = "SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam'";
                            $stmt = $pdo->query($sql);
                            echo $stmt->fetchColumn();
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Denda Belum Bayar</h3>
                        <p class="stat-number">
                            Rp 
                            <?php
                            $sql = "SELECT COALESCE(SUM(jumlah_denda), 0) FROM denda WHERE status_bayar = 'belum'";
                            $stmt = $pdo->query($sql);
                            echo number_format($stmt->fetchColumn(), 0, ',', '.');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Peminjaman Bulan Ini</h3>
                    <canvas id="peminjamanChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Buku Populer</h3>
                    <canvas id="bukuChart"></canvas>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Chart Peminjaman
        const peminjamanCtx = document.getElementById('peminjamanChart').getContext('2d');
        const peminjamanChart = new Chart(peminjamanCtx, {
            type: 'line',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: [12, 19, 8, 15],
                    backgroundColor: 'rgba(255, 107, 53, 0.2)',
                    borderColor: 'rgba(255, 107, 53, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Chart Buku Populer
        const bukuCtx = document.getElementById('bukuChart').getContext('2d');
        const bukuChart = new Chart(bukuCtx, {
            type: 'bar',
            data: {
                labels: ['Buku A', 'Buku B', 'Buku C', 'Buku D', 'Buku E'],
                datasets: [{
                    label: 'Jumlah Dipinjam',
                    data: [12, 19, 8, 15, 7],
                    backgroundColor: [
                        'rgba(255, 107, 53, 0.8)',
                        'rgba(255, 165, 0, 0.8)',
                        'rgba(255, 69, 0, 0.8)',
                        'rgba(255, 140, 0, 0.8)',
                        'rgba(255, 99, 71, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>