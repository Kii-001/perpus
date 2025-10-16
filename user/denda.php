<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireMahasiswaLogin();

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil data denda
$sql = "SELECT d.*, p.buku_id, b.judul, p.tanggal_jatuh_tempo, p.tanggal_kembali
        FROM denda d
        JOIN peminjaman p ON d.peminjaman_id = p.id
        JOIN buku b ON p.buku_id = b.id
        WHERE p.mahasiswa_id = ?
        ORDER BY d.status_bayar, d.jumlah_denda DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mahasiswa_id]);
$denda = $stmt->fetchAll();

// Hitung total
$sql_total = "SELECT 
    SUM(CASE WHEN status_bayar = 'belum' THEN jumlah_denda ELSE 0 END) as total_belum_bayar,
    SUM(CASE WHEN status_bayar = 'lunas' THEN jumlah_denda ELSE 0 END) as total_lunas
    FROM denda d
    JOIN peminjaman p ON d.peminjaman_id = p.id
    WHERE p.mahasiswa_id = ?";
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute([$mahasiswa_id]);
$total = $stmt_total->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denda - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1><i class="fas fa-money-bill-wave"></i>Manajemen Denda</h1>
                <p>Lihat dan kelola denda keterlambatan Anda</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Denda Belum Bayar</h3>
                        <p class="stat-number">Rp <?= number_format($total['total_belum_bayar'], 0, ',', '.') ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Denda Lunas</h3>
                        <p class="stat-number">Rp <?= number_format($total['total_lunas'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Informasi Pembayaran Denda:</strong> Silakan hubungi admin perpustakaan untuk proses pembayaran denda. 
                Setelah pembayaran, status akan diupdate oleh admin.
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Detail Denda</h3>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Buku</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Jumlah Denda</th>
                            <th>Status</th>
                            <th>Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($denda)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Tidak ada data denda</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($denda as $key => $d): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <strong><?= $d['judul'] ?></strong>
                                </td>
                                <td><?= date('d/m/Y', strtotime($d['tanggal_jatuh_tempo'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($d['tanggal_kembali'])) ?></td>
                                <td>
                                    <strong class="text-danger">Rp <?= number_format($d['jumlah_denda'], 0, ',', '.') ?></strong>
                                </td>
                                <td>
                                    <?php if($d['status_bayar'] == 'belum'): ?>
                                        <span class="badge badge-danger">Belum Bayar</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $d['tanggal_bayar'] ? date('d/m/Y', strtotime($d['tanggal_bayar'])) : '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>