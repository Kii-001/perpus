<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();

// Proses peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_peminjaman'])) {
    $mahasiswa_id = $_POST['mahasiswa_id'];
    $buku_id = $_POST['buku_id'];
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));
    
    // Cek stok buku
    $sql = "SELECT stok FROM buku WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$buku_id]);
    $stok = $stmt->fetchColumn();
    
    if ($stok > 0) {
        // Insert peminjaman
        $sql = "INSERT INTO peminjaman (mahasiswa_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$mahasiswa_id, $buku_id, $tanggal_pinjam, $tanggal_jatuh_tempo])) {
            // Kurangi stok buku
            $sql = "UPDATE buku SET stok = stok - 1 WHERE id = ?";
            $pdo->prepare($sql)->execute([$buku_id]);
            
            flash('peminjaman_success', 'Peminjaman berhasil diproses!', 'alert alert-success');
        } else {
            flash('peminjaman_error', 'Gagal memproses peminjaman!', 'alert alert-danger');
        }
    } else {
        flash('peminjaman_error', 'Stok buku tidak tersedia!', 'alert alert-danger');
    }
}

// Proses pengembalian
if (isset($_GET['kembali'])) {
    $id = $_GET['kembali'];
    $tanggal_kembali = date('Y-m-d');
    
    // Ambil data peminjaman
    $sql = "SELECT * FROM peminjaman WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $peminjaman = $stmt->fetch();
    
    if ($peminjaman) {
        $status = 'dikembalikan';
        $denda = 0;
        
        // Cek keterlambatan
        if (strtotime($tanggal_kembali) > strtotime($peminjaman['tanggal_jatuh_tempo'])) {
            $status = 'terlambat';
            $denda = hitungDenda($peminjaman['tanggal_jatuh_tempo']);
        }
        
        // Update peminjaman
        $sql = "UPDATE peminjaman SET tanggal_kembali = ?, status = ?, denda = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$tanggal_kembali, $status, $denda, $id])) {
            // Tambah stok buku
            $sql = "UPDATE buku SET stok = stok + 1 WHERE id = ?";
            $pdo->prepare($sql)->execute([$peminjaman['buku_id']]);
            
            // Jika ada denda, buat record denda
            if ($denda > 0) {
                $sql = "INSERT INTO denda (peminjaman_id, jumlah_denda) VALUES (?, ?)";
                $pdo->prepare($sql)->execute([$id, $denda]);
            }
            
            flash('peminjaman_success', 'Buku berhasil dikembalikan!', 'alert alert-success');
        }
    }
}

// Ambil data peminjaman
$sql = "SELECT p.*, m.nama as nama_mahasiswa, m.nim, b.judul as judul_buku 
        FROM peminjaman p 
        JOIN mahasiswa m ON p.mahasiswa_id = m.id 
        JOIN buku b ON p.buku_id = b.id 
        ORDER BY p.tanggal_pinjam DESC";
$peminjaman = $pdo->query($sql)->fetchAll();

// Ambil data untuk dropdown
$mahasiswa = $pdo->query("SELECT * FROM mahasiswa WHERE status = 'aktif'")->fetchAll();
$buku_tersedia = $pdo->query("SELECT * FROM buku WHERE stok > 0")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman - Perpustakaan ITBI</title>
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
                <h1>Manajemen Peminjaman</h1>
                <p>Kelola proses peminjaman dan pengembalian buku</p>
            </div>
            
            <?php flash('peminjaman_success'); ?>
            <?php flash('peminjaman_error'); ?>
            
            <div class="form-container" style="margin-bottom: 2rem;">
                <h3>Proses Peminjaman Baru</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mahasiswa_id">Mahasiswa *</label>
                            <select name="mahasiswa_id" id="mahasiswa_id" required>
                                <option value="">Pilih Mahasiswa</option>
                                <?php foreach($mahasiswa as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= $m['nim'] ?> - <?= $m['nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="buku_id">Buku *</label>
                            <select name="buku_id" id="buku_id" required>
                                <option value="">Pilih Buku</option>
                                <?php foreach($buku_tersedia as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= $b['judul'] ?> (Stok: <?= $b['stok'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="proses_peminjaman" class="btn btn-primary">Proses Peminjaman</button>
                </form>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Riwayat Peminjaman</h3>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mahasiswa</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($peminjaman)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Tidak ada data peminjaman</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($peminjaman as $key => $p): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <strong><?= $p['nama_mahasiswa'] ?></strong><br>
                                    <small class="text-muted"><?= $p['nim'] ?></small>
                                </td>
                                <td><?= $p['judul_buku'] ?></td>
                                <td><?= date('d/m/Y', strtotime($p['tanggal_pinjam'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($p['tanggal_jatuh_tempo'])) ?></td>
                                <td>
                                    <?= $p['tanggal_kembali'] ? date('d/m/Y', strtotime($p['tanggal_kembali'])) : '-' ?>
                                </td>
                                <td>
                                    <?php if($p['status'] == 'dipinjam'): ?>
                                        <span class="badge badge-warning">Dipinjam</span>
                                    <?php elseif($p['status'] == 'dikembalikan'): ?>
                                        <span class="badge badge-success">Dikembalikan</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Terlambat</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($p['status'] == 'dipinjam' || $p['status'] == 'terlambat'): ?>
                                        <a href="peminjaman.php?kembali=<?= $p['id'] ?>" class="btn btn-sm btn-success"
                                           onclick="return confirm('Yakin buku sudah dikembalikan?')">
                                            <i class="fas fa-undo"></i> Kembalikan
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Selesai</span>
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