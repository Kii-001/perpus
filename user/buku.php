<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireMahasiswaLogin();

$mahasiswa_id = $_SESSION['mahasiswa_id'];
$terkena_sanksi = cekSanksi($mahasiswa_id);

// Proses peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pinjam'])) {
    if ($terkena_sanksi) {
        flash('buku_error', 'Anda terkena sanksi dan tidak dapat meminjam buku!', 'alert alert-danger');
    } else {
        $buku_id = $_POST['buku_id'];
        $tanggal_pinjam = date('Y-m-d');
        $tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));
        
        // Cek stok
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
                // Kurangi stok
                $sql = "UPDATE buku SET stok = stok - 1 WHERE id = ?";
                $pdo->prepare($sql)->execute([$buku_id]);
                
                flash('buku_success', 'Buku berhasil dipinjam!', 'alert alert-success');
            }
        } else {
            flash('buku_error', 'Stok buku tidak tersedia!', 'alert alert-danger');
        }
    }
    redirect('buku.php');
}

// Pencarian
$search = $_GET['search'] ?? '';
$kategori = $_GET['kategori'] ?? '';

$sql = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.judul LIKE ? OR b.penulis LIKE ? OR b.penerbit LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($kategori)) {
    $sql .= " AND b.kategori_id = ?";
    $params[] = $kategori;
}

$sql .= " ORDER BY b.judul ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$buku = $stmt->fetchAll();

// Ambil kategori untuk filter
$kategori_list = $pdo->query("SELECT * FROM kategori")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1><i class="fas fa-book"></i>Katalog Buku</h1>
                <p>Temukan dan pinjam buku digital yang Anda butuhkan</p>
            </div>
            
            <?php flash('buku_success'); ?>
            <?php flash('buku_error'); ?>
            
            <!-- Form Pencarian -->
            <div class="form-container" style="margin-bottom: 2rem;">
                <form method="GET">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Cari judul, penulis, atau penerbit..." 
                                   value="<?= htmlspecialchars($search) ?>" style="width: 100%;">
                        </div>
                        <div class="form-group">
                            <select name="kategori">
                                <option value="">Semua Kategori</option>
                                <?php foreach($kategori_list as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= $kategori == $k['id'] ? 'selected' : '' ?>>
                                        <?= $k['nama_kategori'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            <a href="buku.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Daftar Buku -->
            <div class="books-grid">
                <?php if(empty($buku)): ?>
                    <div class="no-books">
                        <i class="fas fa-book-open" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>Tidak ada buku ditemukan</h3>
                        <p>Coba gunakan kata kunci pencarian yang berbeda</p>
                    </div>
                <?php else: ?>
                    <?php foreach($buku as $b): ?>
                    <div class="book-card">
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
                            <h3><?= $b['judul'] ?></h3>
                            <p class="book-author">Oleh: <?= $b['penulis'] ?></p>
                            <p class="book-publisher"><?= $b['penerbit'] ?> (<?= $b['tahun_terbit'] ?>)</p>
                            <p class="book-category">Kategori: <?= $b['nama_kategori'] ?></p>
                            
                            <div class="book-stock">
                                <span class="badge <?= $b['stok'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $b['stok'] ?> tersedia
                                </span>
                            </div>
                            
                            <div class="book-actions">
                                <?php if($b['file_pdf']): ?>
                                    <a href="../pdf-viewer/view.php?id=<?= $b['id'] ?>" 
                                       class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-eye"></i> Preview
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($b['stok'] > 0 && !$terkena_sanksi): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="buku_id" value="<?= $b['id'] ?>">
                                        <button type="submit" name="pinjam" class="btn btn-sm btn-primary"
                                                onclick="return confirm('Yakin ingin meminjam buku ini?')">
                                            <i class="fas fa-hand-holding"></i> Pinjam
                                        </button>
                                    </form>
                                <?php elseif($terkena_sanksi): ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Anda terkena sanksi">
                                        <i class="fas fa-ban"></i> Tidak Dapat Meminjam
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-times"></i> Stok Habis
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <style>
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .book-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
        }
        
        .book-cover {
            height: 200px;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .book-cover-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 3rem;
        }
        
        .book-info {
            padding: 1.5rem;
        }
        
        .book-info h3 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            line-height: 1.4;
        }
        
        .book-author, .book-publisher, .book-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .book-stock {
            margin: 1rem 0;
        }
        
        .book-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .no-books {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    </style>
</body>
</html>