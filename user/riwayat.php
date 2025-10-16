<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireMahasiswaLogin();

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil riwayat peminjaman
$sql = "SELECT p.*, b.judul, b.penulis, b.file_pdf 
        FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.mahasiswa_id = ? 
        ORDER BY p.tanggal_pinjam DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mahasiswa_id]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1><i class="fas fa-history"></i>Riwayat Peminjaman</h1>
                <p>Lihat history peminjaman buku Anda</p>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Daftar Peminjaman</h3>
                    <div class="table-actions">
                        <span class="text-muted">Total: <?= count($riwayat) ?> peminjaman</span>
                    </div>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($riwayat)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Belum ada riwayat peminjaman</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($riwayat as $key => $r): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <strong><?= $r['judul'] ?></strong><br>
                                    <small class="text-muted">Oleh: <?= $r['penulis'] ?></small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($r['tanggal_pinjam'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($r['tanggal_jatuh_tempo'])) ?></td>
                                <td>
                                    <?= $r['tanggal_kembali'] ? date('d/m/Y', strtotime($r['tanggal_kembali'])) : '-' ?>
                                </td>
                                <td>
                                    <?php if($r['status'] == 'dipinjam'): ?>
                                        <span class="badge badge-warning">Dipinjam</span>
                                    <?php elseif($r['status'] == 'dikembalikan'): ?>
                                        <span class="badge badge-success">Dikembalikan</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Terlambat</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($r['denda'] > 0): ?>
                                        <span class="text-danger">Rp <?= number_format($r['denda'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($r['file_pdf'] && ($r['status'] == 'dipinjam' || $r['status'] == 'terlambat')): ?>
                                        <a href="../pdf-viewer/view.php?id=<?= $r['buku_id'] ?>" 
                                           class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-book-open"></i> Baca
                                        </a>
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