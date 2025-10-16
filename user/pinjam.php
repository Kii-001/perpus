<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireMahasiswaLogin();

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Konfigurasi tetap
$denda_per_hari = 5000;
$maks_peminjaman = 5;
$lama_peminjaman = 7;
$sanksi_hari = 7;

// Cek status mahasiswa
$terkena_sanksi = cekSanksi($mahasiswa_id);

// Hitung jumlah buku yang sedang dipinjam untuk cek maksimal
$sql_maks = "SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ? AND status IN ('dipinjam', 'terlambat')";
$stmt_maks = $pdo->prepare($sql_maks);
$stmt_maks->execute([$mahasiswa_id]);
$jumlah_pinjaman_sekarang = $stmt_maks->fetchColumn();
$maksimal_pinjaman = $jumlah_pinjaman_sekarang >= $maks_peminjaman;

// Proses peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses pinjam buku
    if (isset($_POST['pinjam_buku'])) {
        if ($terkena_sanksi) {
            flash('pinjam_error', 'Anda terkena sanksi dan tidak dapat meminjam buku!', 'alert alert-danger');
        } elseif ($maksimal_pinjaman) {
            flash('pinjam_error', 'Anda sudah mencapai batas maksimal peminjaman (' . $maks_peminjaman . ' buku)!', 'alert alert-danger');
        } else {
            $buku_ids = $_POST['buku_id'] ?? [];
            
            if (empty($buku_ids)) {
                flash('pinjam_error', 'Pilih minimal 1 buku untuk dipinjam!', 'alert alert-danger');
            } else {
                $success_count = 0;
                $error_count = 0;
                $error_messages = [];
                
                foreach ($buku_ids as $buku_id) {
                    // Cek stok buku
                    $sql = "SELECT stok, judul FROM buku WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$buku_id]);
                    $buku = $stmt->fetch();
                    
                    if ($buku && $buku['stok'] > 0) {
                        $tanggal_pinjam = date('Y-m-d');
                        $tanggal_jatuh_tempo = date('Y-m-d', strtotime('+' . $lama_peminjaman . ' days'));
                        
                        // Insert peminjaman
                        $sql = "INSERT INTO peminjaman (mahasiswa_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo) 
                                VALUES (?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        
                        if ($stmt->execute([$mahasiswa_id, $buku_id, $tanggal_pinjam, $tanggal_jatuh_tempo])) {
                            // Kurangi stok buku
                            $sql = "UPDATE buku SET stok = stok - 1 WHERE id = ?";
                            $pdo->prepare($sql)->execute([$buku_id]);
                            $success_count++;
                        } else {
                            $error_count++;
                            $error_messages[] = "Gagal meminjam buku: " . $buku['judul'];
                        }
                    } else {
                        $error_count++;
                        $error_messages[] = "Stok tidak tersedia untuk buku: " . ($buku ? $buku['judul'] : 'ID ' . $buku_id);
                    }
                }
                
                if ($success_count > 0) {
                    flash('pinjam_success', 'Berhasil meminjam ' . $success_count . ' buku!', 'alert alert-success');
                }
                if ($error_count > 0) {
                    $error_message = 'Gagal meminjam ' . $error_count . ' buku. ' . implode(', ', $error_messages);
                    flash('pinjam_error', $error_message, 'alert alert-danger');
                }
                
                redirect('pinjam.php');
            }
        }
    }
    
    // Proses pengembalian buku
    if (isset($_POST['kembalikan_buku'])) {
        $peminjaman_id = $_POST['peminjaman_id'];
        $tanggal_kembali = date('Y-m-d');
        
        // Ambil data peminjaman
        $sql = "SELECT p.*, b.judul, b.id as buku_id 
                FROM peminjaman p 
                JOIN buku b ON p.buku_id = b.id 
                WHERE p.id = ? AND p.mahasiswa_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$peminjaman_id, $mahasiswa_id]);
        $peminjaman = $stmt->fetch();
        
        if ($peminjaman) {
            $status = 'dikembalikan';
            $denda = 0;
            
            // Cek keterlambatan
            if (strtotime($tanggal_kembali) > strtotime($peminjaman['tanggal_jatuh_tempo'])) {
                $hari_terlambat = max(0, (strtotime($tanggal_kembali) - strtotime($peminjaman['tanggal_jatuh_tempo'])) / (60 * 60 * 24));
                $denda = $hari_terlambat * $denda_per_hari;
                $status = 'terlambat';
            }
            
            // Update peminjaman
            $sql = "UPDATE peminjaman SET tanggal_kembali = ?, status = ?, denda = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$tanggal_kembali, $status, $denda, $peminjaman_id])) {
                // Tambah stok buku
                $sql = "UPDATE buku SET stok = stok + 1 WHERE id = ?";
                $pdo->prepare($sql)->execute([$peminjaman['buku_id']]);
                
                // Jika ada denda, buat record denda
                if ($denda > 0) {
                    $sql = "INSERT INTO denda (peminjaman_id, jumlah_denda) VALUES (?, ?)";
                    $pdo->prepare($sql)->execute([$peminjaman_id, $denda]);
                    
                    flash('kembali_success', 'Buku "' . $peminjaman['judul'] . '" berhasil dikembalikan! Denda keterlambatan: Rp ' . number_format($denda, 0, ',', '.'), 'alert alert-warning');
                } else {
                    flash('kembali_success', 'Buku "' . $peminjaman['judul'] . '" berhasil dikembalikan!', 'alert alert-success');
                }
            } else {
                flash('kembali_error', 'Gagal mengembalikan buku!', 'alert alert-danger');
            }
        } else {
            flash('kembali_error', 'Data peminjaman tidak ditemukan!', 'alert alert-danger');
        }
        
        redirect('pinjam.php');
    }
}

// Ambil buku yang tersedia
$sql = "SELECT b.*, k.nama_kategori 
        FROM buku b 
        LEFT JOIN kategori k ON b.kategori_id = k.id 
        WHERE b.stok > 0 
        ORDER BY b.judul ASC";
$buku_tersedia = $pdo->query($sql)->fetchAll();

// Ambil peminjaman aktif
$sql = "SELECT p.*, b.judul, b.penulis, b.file_pdf,
               DATEDIFF(p.tanggal_jatuh_tempo, CURDATE()) as sisa_hari
        FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.mahasiswa_id = ? AND p.status IN ('dipinjam', 'terlambat')
        ORDER BY p.tanggal_jatuh_tempo ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mahasiswa_id]);
$peminjaman_aktif = $stmt->fetchAll();

// Hitung statistik
$total_dipinjam = count($peminjaman_aktif);
$bisa_pinjam_lagi = $maks_peminjaman - $total_dipinjam;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam & Kembalikan Buku - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1><i class="fas fa-hand-holding"></i>Pinjam & Kembalikan Buku</h1>
                <p>Kelola peminjaman dan pengembalian buku Anda</p>
            </div>
            
            <?php flash('pinjam_success'); ?>
            <?php flash('pinjam_error'); ?>
            <?php flash('kembali_success'); ?>
            <?php flash('kembali_error'); ?>
            
            <!-- Informasi Status -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?= $terkena_sanksi ? 'rgba(220, 53, 69, 0.1)' : 'rgba(40, 167, 69, 0.1)' ?>; color: <?= $terkena_sanksi ? '#dc3545' : '#28a745' ?>;">
                        <i class="fas fa-<?= $terkena_sanksi ? 'ban' : 'check-circle' ?>"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Status Peminjaman</h3>
                        <p class="stat-number" style="color: <?= $terkena_sanksi ? '#dc3545' : '#28a745' ?>;">
                            <?php if($terkena_sanksi): ?>
                                Terkena Sanksi
                            <?php elseif($maksimal_pinjaman): ?>
                                Maksimal Terpenuhi
                            <?php else: ?>
                                Dapat Meminjam
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Sedang Dipinjam</h3>
                        <p class="stat-number"><?= $total_dipinjam ?> / <?= $maks_peminjaman ?></p>
                        <small><?= $bisa_pinjam_lagi > 0 ? $bisa_pinjam_lagi . ' buku bisa dipinjam lagi' : 'Tidak bisa pinjam lagi' ?></small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Lama Pinjam</h3>
                        <p class="stat-number"><?= $lama_peminjaman ?> Hari</p>
                        <small>Maksimal waktu pinjam</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Denda per Hari</h3>
                        <p class="stat-number">Rp <?= number_format($denda_per_hari, 0, ',', '.') ?></p>
                        <small>Keterlambatan pengembalian</small>
                    </div>
                </div>
            </div>
            
            <?php if($terkena_sanksi): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Anda terkena sanksi!</strong> Anda tidak dapat meminjam buku selama <?= $sanksi_hari ?> hari karena keterlambatan pengembalian.
                </div>
            <?php elseif($maksimal_pinjaman): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <strong>Batas peminjaman tercapai!</strong> Anda sudah meminjam <?= $maks_peminjaman ?> buku. Kembalikan buku yang sedang dipinjam untuk dapat meminjam lagi.
                </div>
            <?php endif; ?>
            
            <div class="charts-grid">
                <!-- Form Peminjaman -->
                <div class="chart-card" style="flex: 2;">
                    <div class="table-header">
                        <h3><i class="fas fa-hand-holding"></i> Pinjam Buku Baru</h3>
                        <div class="table-actions">
                            <span class="text-muted"><?= count($buku_tersedia) ?> buku tersedia</span>
                        </div>
                    </div>
                    
                    <?php if(empty($buku_tersedia)): ?>
                        <div class="no-books">
                            <i class="fas fa-book-open" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <h3>Tidak ada buku tersedia</h3>
                            <p>Semua buku sedang dipinjam atau tidak tersedia</p>
                            <a href="buku.php" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-search"></i> Lihat Katalog Lengkap
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" id="pinjamForm">
                            <div class="books-grid">
                                <?php foreach($buku_tersedia as $b): ?>
                                <div class="book-card-select" data-buku-id="<?= $b['id'] ?>">
                                    <div class="book-checkbox">
                                        <input type="checkbox" name="buku_id[]" value="<?= $b['id'] ?>" 
                                               id="buku_<?= $b['id'] ?>" 
                                               <?= $terkena_sanksi || $maksimal_pinjaman ? 'disabled' : '' ?>
                                               class="buku-checkbox">
                                        <label for="buku_<?= $b['id'] ?>" class="checkbox-label"></label>
                                    </div>
                                    <div class="book-cover">
                                        <?php if($b['sampul']): ?>
                                            <img src="../uploads/buku/<?= $b['sampul'] ?>" alt="<?= $b['judul'] ?>">
                                        <?php else: ?>
                                            <div class="book-cover-placeholder">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="book-info">
                                        <h4><?= htmlspecialchars($b['judul']) ?></h4>
                                        <p class="book-author"><i class="fas fa-user-edit"></i> <?= htmlspecialchars($b['penulis']) ?></p>
                                        <p class="book-publisher"><i class="fas fa-building"></i> <?= htmlspecialchars($b['penerbit']) ?> (<?= $b['tahun_terbit'] ?>)</p>
                                        <p class="book-category">
                                            <i class="fas fa-tag"></i> <?= $b['nama_kategori'] ?>
                                        </p>
                                        
                                        <?php if($b['deskripsi']): ?>
                                        <p class="book-description"><?= nl2br(htmlspecialchars(substr($b['deskripsi'], 0, 100))) ?><?= strlen($b['deskripsi']) > 100 ? '...' : '' ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="book-stock">
                                            <span class="badge <?= $b['stok'] > 3 ? 'badge-success' : ($b['stok'] > 0 ? 'badge-warning' : 'badge-danger') ?>">
                                                <i class="fas fa-copy"></i> <?= $b['stok'] ?> tersedia
                                            </span>
                                        </div>
                                        
                                        <div class="book-actions">
                                            <?php if($b['file_pdf']): ?>
                                                <a href="../pdf-viewer/view.php?id=<?= $b['id'] ?>" 
                                                   class="btn btn-sm btn-info" target="_blank" 
                                                   onclick="event.stopPropagation()">
                                                    <i class="fas fa-eye"></i> Preview
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if(!$terkena_sanksi && !$maksimal_pinjaman): ?>
                                <div class="form-actions" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                                    <button type="submit" name="pinjam_buku" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-hand-holding"></i> Pinjam Buku Terpilih
                                    </button>
                                    <span id="selectedCount" class="text-muted" style="margin-left: 1rem;">
                                        0 buku terpilih
                                    </span>
                                </div>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Pengembalian Buku -->
                <div class="sidebar-cards">
                    <!-- Peminjaman Aktif -->
                    <div class="chart-card">
                        <h3>
                            <i class="fas fa-clock"></i> 
                            Buku Sedang Dipinjam
                            <span class="badge badge-primary"><?= count($peminjaman_aktif) ?></span>
                        </h3>
                        <?php if(empty($peminjaman_aktif)): ?>
                            <div class="no-books-small">
                                <i class="fas fa-book-open" style="font-size: 2rem; color: #ccc;"></i>
                                <p>Tidak ada buku yang sedang dipinjam</p>
                            </div>
                        <?php else: ?>
                            <div class="borrowed-books-list">
                                <?php foreach($peminjaman_aktif as $p): ?>
                                <div class="borrowed-book">
                                    <div class="borrowed-book-cover">
                                        <?php if($p['file_pdf']): ?>
                                            <div class="book-cover-small">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="book-cover-small">
                                                <i class="fas fa-book text-primary"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="borrowed-book-info">
                                        <strong class="book-title"><?= htmlspecialchars($p['judul']) ?></strong>
                                        <div class="book-dates">
                                            <small>
                                                <i class="fas fa-calendar-alt"></i>
                                                Pinjam: <?= date('d/m/Y', strtotime($p['tanggal_pinjam'])) ?>
                                            </small>
                                            <small class="<?= $p['sisa_hari'] < 0 ? 'text-danger' : ($p['sisa_hari'] <= 2 ? 'text-warning' : 'text-muted') ?>">
                                                <i class="fas fa-hourglass-half"></i>
                                                Jatuh Tempo: <?= date('d/m/Y', strtotime($p['tanggal_jatuh_tempo'])) ?>
                                                <?php if($p['sisa_hari'] < 0): ?>
                                                    <br><span class="text-danger">(Terlambat <?= abs($p['sisa_hari']) ?> hari)</span>
                                                <?php elseif($p['sisa_hari'] <= 2): ?>
                                                    <br><span class="text-warning">(<?= $p['sisa_hari'] ?> hari lagi)</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="borrowed-book-actions">
                                        <form method="POST" class="kembalikan-form" onsubmit="return confirmKembalikan('<?= htmlspecialchars($p['judul']) ?>')">
                                            <input type="hidden" name="peminjaman_id" value="<?= $p['id'] ?>">
                                            <button type="submit" name="kembalikan_buku" class="btn btn-success btn-sm">
                                                <i class="fas fa-undo"></i> Kembalikan
                                            </button>
                                        </form>
                                        <?php if($p['file_pdf']): ?>
                                            <a href="../pdf-viewer/view.php?id=<?= $p['buku_id'] ?>" 
                                               class="btn btn-primary btn-sm" target="_blank"
                                               title="Baca Buku">
                                                <i class="fas fa-book-open"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Informasi Penting -->
                    <div class="chart-card">
                        <h3><i class="fas fa-info-circle"></i> Informasi Penting</h3>
                        <div class="info-list">
                            <div class="info-item">
                                <i class="fas fa-exchange-alt text-primary"></i>
                                <div>
                                    <strong>Pinjam & Kembali</strong>
                                    <p>Pilih buku yang ingin dipinjam dan kembalikan sebelum jatuh tempo</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock text-warning"></i>
                                <div>
                                    <strong>Waktu Peminjaman</strong>
                                    <p>Lama pinjam: <?= $lama_peminjaman ?> hari. Denda: Rp <?= number_format($denda_per_hari, 0, ',', '.') ?>/hari</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-book-reader text-success"></i>
                                <div>
                                    <strong>Baca Online</strong>
                                    <p>Gunakan fitur baca online untuk buku yang tersedia format PDF</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <style>
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1rem;
            max-height: 600px;
            overflow-y: auto;
            padding: 1rem;
        }
        
        .book-card-select {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .book-card-select:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .book-card-select.selected {
            border-color: var(--primary);
            background: rgba(255, 107, 53, 0.05);
        }
        
        .book-checkbox {
            display: flex;
            align-items: flex-start;
        }
        
        .buku-checkbox {
            display: none;
        }
        
        .checkbox-label {
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .buku-checkbox:checked + .checkbox-label {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .buku-checkbox:checked + .checkbox-label::after {
            content: '✓';
            color: white;
            font-size: 14px;
            font-weight: bold;
        }
        
        .buku-checkbox:disabled + .checkbox-label {
            background: #f8f9fa;
            border-color: #e9ecef;
            cursor: not-allowed;
        }
        
        .book-cover {
            width: 80px;
            height: 100px;
            flex-shrink: 0;
        }
        
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .book-cover-placeholder {
            width: 100%;
            height: 100%;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 1.5rem;
        }
        
        .book-info {
            flex: 1;
            min-width: 0;
        }
        
        .book-info h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            line-height: 1.3;
            color: var(--dark);
        }
        
        .book-author, .book-publisher, .book-category {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        
        .book-description {
            color: #777;
            font-size: 0.9rem;
            line-height: 1.4;
            margin: 0.5rem 0;
        }
        
        .book-stock {
            margin: 0.75rem 0;
        }
        
        .book-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .sidebar-cards {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .borrowed-books-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .borrowed-book {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
        
        .borrowed-book:last-child {
            border-bottom: none;
        }
        
        .book-cover-small {
            width: 40px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .borrowed-book-info {
            flex: 1;
            min-width: 0;
        }
        
        .book-title {
            display: block;
            font-size: 0.9rem;
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }
        
        .book-dates {
            font-size: 0.8rem;
        }
        
        .book-dates small {
            display: block;
            margin-bottom: 0.1rem;
        }
        
        .borrowed-book-actions {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            align-items: flex-end;
        }
        
        .kembalikan-form {
            margin: 0;
        }
        
        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }
        
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
        
        .info-item i {
            font-size: 1.2rem;
            margin-top: 0.1rem;
            flex-shrink: 0;
        }
        
        .info-item div {
            flex: 1;
        }
        
        .info-item strong {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        .info-item p {
            margin: 0;
            font-size: 0.8rem;
            color: #666;
            line-height: 1.3;
        }
        
        .no-books {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .no-books-small {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
    </style>
    
    <script>
        function confirmKembalikan(judulBuku) {
            return confirm('Yakin ingin mengembalikan buku "' + judulBuku + '"?');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.buku-checkbox');
            const selectedCount = document.getElementById('selectedCount');
            const bookCards = document.querySelectorAll('.book-card-select');
            const submitBtn = document.getElementById('submitBtn');
            
            // Update selected count
            function updateSelectedCount() {
                const selected = document.querySelectorAll('.buku-checkbox:checked').length;
                selectedCount.textContent = selected + ' buku terpilih';
                
                // Update submit button text
                if (submitBtn) {
                    if (selected > 0) {
                        submitBtn.innerHTML = '<i class="fas fa-hand-holding"></i> Pinjam ' + selected + ' Buku';
                    } else {
                        submitBtn.innerHTML = '<i class="fas fa-hand-holding"></i> Pinjam Buku Terpilih';
                    }
                }
            }
            
            // Add click event to book cards
            bookCards.forEach(card => {
                const checkbox = card.querySelector('.buku-checkbox');
                
                card.addEventListener('click', function(e) {
                    if (!e.target.closest('.book-actions') && !checkbox.disabled) {
                        checkbox.checked = !checkbox.checked;
                        card.classList.toggle('selected', checkbox.checked);
                        updateSelectedCount();
                    }
                });
                
                // Sync checkbox state with card style
                checkbox.addEventListener('change', function() {
                    card.classList.toggle('selected', this.checked);
                    updateSelectedCount();
                });
                
                // Initialize card state
                card.classList.toggle('selected', checkbox.checked);
            });
            
            // Form submission confirmation
            const pinjamForm = document.getElementById('pinjamForm');
            if (pinjamForm) {
                pinjamForm.addEventListener('submit', function(e) {
                    const selected = document.querySelectorAll('.buku-checkbox:checked').length;
                    if (selected === 0) {
                        e.preventDefault();
                        alert('Pilih minimal 1 buku untuk dipinjam!');
                        return;
                    }
                    
                    if (!confirm(`Yakin ingin meminjam ${selected} buku?`)) {
                        e.preventDefault();
                    }
                });
            }
            
            // Initialize count
            updateSelectedCount();
            
            // Add loading state to return buttons
            const returnForms = document.querySelectorAll('.kembalikan-form');
            returnForms.forEach(form => {
                form.addEventListener('submit', function() {
                    const button = this.querySelector('button');
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    button.disabled = true;
                });
            });
        });
        
        // Auto-scroll to messages
        const messages = document.querySelectorAll('.alert');
        if (messages.length > 0) {
            messages[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
</body>
</html>