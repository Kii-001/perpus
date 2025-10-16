<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireMahasiswaLogin();

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil data peminjaman aktif
$sql = "SELECT p.*, b.judul, b.penulis, b.file_pdf 
        FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.mahasiswa_id = ? AND p.status IN ('dipinjam', 'terlambat')
        ORDER BY p.tanggal_jatuh_tempo ASC";
$peminjaman_aktif = $pdo->prepare($sql);
$peminjaman_aktif->execute([$mahasiswa_id]);
$peminjaman_aktif = $peminjaman_aktif->fetchAll();

// Ambil riwayat peminjaman
$sql = "SELECT p.*, b.judul, b.penulis 
        FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.mahasiswa_id = ? AND p.status = 'dikembalikan'
        ORDER BY p.tanggal_kembali DESC 
        LIMIT 5";
$riwayat = $pdo->prepare($sql);
$riwayat->execute([$mahasiswa_id]);
$riwayat = $riwayat->fetchAll();

// Cek sanksi
$terkena_sanksi = cekSanksi($mahasiswa_id);

// Hitung denda belum bayar
$sql = "SELECT COALESCE(SUM(d.jumlah_denda), 0) as total_denda 
        FROM denda d 
        JOIN peminjaman p ON d.peminjaman_id = p.id 
        WHERE p.mahasiswa_id = ? AND d.status_bayar = 'belum'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mahasiswa_id]);
$total_denda = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
    
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1><i class="fas fa-user-graduate"></i>Dashboard Mahasiswa</h1>
                <p>Selamat datang, <?= $_SESSION['mahasiswa_nama'] ?> (<?= $_SESSION['mahasiswa_nim'] ?>)</p>
            </div>
            
            <?php if($terkena_sanksi): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Anda terkena sanksi!</strong> Anda tidak dapat meminjam buku selama 7 hari karena keterlambatan pengembalian.
            </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Sedang Dipinjam</h3>
                        <p class="stat-number"><?= count($peminjaman_aktif) ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Peminjaman</h3>
                        <p class="stat-number">
                            <?php
                            $sql = "SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$mahasiswa_id]);
                            echo $stmt->fetchColumn();
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Denda Belum Bayar</h3>
                        <p class="stat-number">Rp <?= number_format($total_denda, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="charts-grid">
                <div class="chart-card" style="flex: 2;">
                    <h3>Peminjaman Aktif</h3>
                    <?php if(empty($peminjaman_aktif)): ?>
                        <p style="text-align: center; color: #666; padding: 2rem;">Tidak ada peminjaman aktif</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Buku</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($peminjaman_aktif as $p): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $p['judul'] ?></strong><br>
                                            <small class="text-muted">Oleh: <?= $p['penulis'] ?></small>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($p['tanggal_pinjam'])) ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($p['tanggal_jatuh_tempo'])) ?>
                                            <?php if(strtotime($p['tanggal_jatuh_tempo']) < time()): ?>
                                                <br><small class="text-danger">Terlambat!</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($p['status'] == 'dipinjam'): ?>
                                                <span class="badge badge-warning">Dipinjam</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Terlambat</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($p['file_pdf']): ?>
                                                <a href="../pdf-viewer/view.php?id=<?= $p['buku_id'] ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-book-open"></i> Baca
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="chart-card">
                    <h3>Riwayat Terbaru</h3>
                    <?php if(empty($riwayat)): ?>
                        <p style="text-align: center; color: #666; padding: 2rem;">Belum ada riwayat</p>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach($riwayat as $r): ?>
                            <div style="padding: 1rem; border-bottom: 1px solid #f0f0f0;">
                                <strong><?= $r['judul'] ?></strong><br>
                                <small class="text-muted">
                                    Dikembalikan: <?= date('d/m/Y', strtotime($r['tanggal_kembali'])) ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>