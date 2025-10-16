<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();

// Proses pembayaran denda
if (isset($_GET['bayar'])) {
    $id = $_GET['bayar'];
    $tanggal_bayar = date('Y-m-d');
    
    $sql = "UPDATE denda SET status_bayar = 'lunas', tanggal_bayar = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$tanggal_bayar, $id])) {
        flash('denda_success', 'Pembayaran denda berhasil dicatat!', 'alert alert-success');
    } else {
        flash('denda_error', 'Gagal mencatat pembayaran denda!', 'alert alert-danger');
    }
    redirect('denda.php');
}

// Ambil data denda
$sql = "SELECT d.*, p.mahasiswa_id, m.nama as nama_mahasiswa, m.nim, b.judul as judul_buku,
               p.tanggal_jatuh_tempo, p.tanggal_kembali
        FROM denda d
        JOIN peminjaman p ON d.peminjaman_id = p.id
        JOIN mahasiswa m ON p.mahasiswa_id = m.id
        JOIN buku b ON p.buku_id = b.id
        ORDER BY d.status_bayar, d.jumlah_denda DESC";
$denda = $pdo->query($sql)->fetchAll();

// Hitung total denda
$sql_total = "SELECT 
    SUM(CASE WHEN status_bayar = 'belum' THEN jumlah_denda ELSE 0 END) as total_belum_bayar,
    SUM(CASE WHEN status_bayar = 'lunas' THEN jumlah_denda ELSE 0 END) as total_lunas
    FROM denda";
$total_denda = $pdo->query($sql_total)->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Denda - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1>Manajemen Denda</h1>
                <p>Kelola pembayaran denda keterlambatan</p>
            </div>
            
            <?php flash('denda_success'); ?>
            <?php flash('denda_error'); ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Denda Belum Bayar</h3>
                        <p class="stat-number">Rp <?= number_format($total_denda['total_belum_bayar'], 0, ',', '.') ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Denda Lunas</h3>
                        <p class="stat-number">Rp <?= number_format($total_denda['total_lunas'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Data Denda</h3>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mahasiswa</th>
                            <th>Buku</th>
                            <th>Jatuh Tempo</th>
                            <th>Kembali</th>
                            <th>Jumlah Denda</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($denda)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Tidak ada data denda</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($denda as $key => $d): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <strong><?= $d['nama_mahasiswa'] ?></strong><br>
                                    <small class="text-muted"><?= $d['nim'] ?></small>
                                </td>
                                <td><?= $d['judul_buku'] ?></td>
                                <td><?= date('d/m/Y', strtotime($d['tanggal_jatuh_tempo'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($d['tanggal_kembali'])) ?></td>
                                <td>Rp <?= number_format($d['jumlah_denda'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if($d['status_bayar'] == 'belum'): ?>
                                        <span class="badge badge-danger">Belum Bayar</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($d['status_bayar'] == 'belum'): ?>
                                        <a href="denda.php?bayar=<?= $d['id'] ?>" class="btn btn-sm btn-success"
                                           onclick="return confirm('Yakin menandai denda sudah dibayar?')">
                                            <i class="fas fa-check"></i> Bayar
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($d['tanggal_bayar'])) ?>
                                        </small>
                                    <?php endif; ?>
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