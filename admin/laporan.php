<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();

// Filter tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Statistik umum
$sql = "SELECT 
    COUNT(*) as total_peminjaman,
    SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as total_dikembalikan,
    SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat,
    SUM(denda) as total_denda_dihasilkan
    FROM peminjaman 
    WHERE tanggal_pinjam BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$stats = $stmt->fetch();

// Buku paling populer
$sql = "SELECT b.judul, b.penulis, COUNT(p.id) as jumlah_pinjam
        FROM peminjaman p
        JOIN buku b ON p.buku_id = b.id
        WHERE p.tanggal_pinjam BETWEEN ? AND ?
        GROUP BY b.id
        ORDER BY jumlah_pinjam DESC
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$buku_populer = $stmt->fetchAll();

// Mahasiswa paling aktif
$sql = "SELECT m.nim, m.nama, m.jurusan, COUNT(p.id) as jumlah_pinjam
        FROM peminjaman p
        JOIN mahasiswa m ON p.mahasiswa_id = m.id
        WHERE p.tanggal_pinjam BETWEEN ? AND ?
        GROUP BY m.id
        ORDER BY jumlah_pinjam DESC
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$mahasiswa_aktif = $stmt->fetchAll();

// Trend peminjaman per bulan
$sql = "SELECT 
    DATE_FORMAT(tanggal_pinjam, '%Y-%m') as bulan,
    COUNT(*) as jumlah
    FROM peminjaman 
    WHERE tanggal_pinjam >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal_pinjam, '%Y-%m')
    ORDER BY bulan";
$trend_peminjaman = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Perpustakaan ITBI</title>
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
                <h1>Laporan dan Analytics</h1>
                <p>Analisis data dan statistik perpustakaan</p>
            </div>
            
            <!-- Filter -->
            <div class="form-container" style="margin-bottom: 2rem;">
                <h3>Filter Periode</h3>
                <form method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Dari Tanggal</label>
                            <input type="date" name="start_date" id="start_date" value="<?= $start_date ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="end_date" value="<?= $end_date ?>" required>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="laporan.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Statistik -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Peminjaman</h3>
                        <p class="stat-number"><?= $stats['total_peminjaman'] ?></p>
                        <small>Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Dikembalikan</h3>
                        <p class="stat-number"><?= $stats['total_dikembalikan'] ?></p>
                        <small><?= $stats['total_peminjaman'] > 0 ? round(($stats['total_dikembalikan'] / $stats['total_peminjaman']) * 100, 1) : 0 ?>%</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Keterlambatan</h3>
                        <p class="stat-number"><?= $stats['total_terlambat'] ?></p>
                        <small><?= $stats['total_peminjaman'] > 0 ? round(($stats['total_terlambat'] / $stats['total_peminjaman']) * 100, 1) : 0 ?>%</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Denda Dihasilkan</h3>
                        <p class="stat-number">Rp <?= number_format($stats['total_denda_dihasilkan'], 0, ',', '.') ?></p>
                        <small>Total pendapatan denda</small>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Trend Peminjaman 6 Bulan Terakhir</h3>
                    <canvas id="trendChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Distribusi Status Peminjaman</h3>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            
            <!-- Buku Populer -->
            <div class="table-container" style="margin-top: 2rem;">
                <div class="table-header">
                    <h3>10 Buku Paling Populer</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Jumlah Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($buku_populer)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Tidak ada data</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($buku_populer as $key => $b): ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $key < 3 ? 'badge-success' : 'badge-secondary' ?>">
                                        #<?= $key + 1 ?>
                                    </span>
                                </td>
                                <td><strong><?= $b['judul'] ?></strong></td>
                                <td><?= $b['penulis'] ?></td>
                                <td><?= $b['jumlah_pinjam'] ?> kali</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Mahasiswa Aktif -->
            <div class="table-container" style="margin-top: 2rem;">
                <div class="table-header">
                    <h3>10 Mahasiswa Paling Aktif</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Jurusan</th>
                            <th>Jumlah Pinjam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($mahasiswa_aktif)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Tidak ada data</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($mahasiswa_aktif as $key => $m): ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $key < 3 ? 'badge-success' : 'badge-secondary' ?>">
                                        #<?= $key + 1 ?>
                                    </span>
                                </td>
                                <td><strong><?= $m['nim'] ?></strong></td>
                                <td><?= $m['nama'] ?></td>
                                <td><?= $m['jurusan'] ?></td>
                                <td><?= $m['jumlah_pinjam'] ?> kali</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($trend_peminjaman, 'bulan')) ?>,
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: <?= json_encode(array_column($trend_peminjaman, 'jumlah')) ?>,
                    backgroundColor: 'rgba(255, 107, 53, 0.2)',
                    borderColor: 'rgba(255, 107, 53, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
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

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Dikembalikan', 'Masih Dipinjam', 'Terlambat'],
                datasets: [{
                    data: [
                        <?= $stats['total_dikembalikan'] ?>,
                        <?= $stats['total_peminjaman'] - $stats['total_dikembalikan'] - $stats['total_terlambat'] ?>,
                        <?= $stats['total_terlambat'] ?>
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>