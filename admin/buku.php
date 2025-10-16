<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();

// Tambah buku
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_buku'])) {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $penerbit = trim($_POST['penerbit']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = trim($_POST['isbn']);
    $kategori_id = $_POST['kategori_id'];
    $stok = $_POST['stok'];
    $deskripsi = trim($_POST['deskripsi']);
    
    // Upload sampul
    $sampul = '';
    if (isset($_FILES['sampul']) && $_FILES['sampul']['error'] == 0) {
        $ext = pathinfo($_FILES['sampul']['name'], PATHINFO_EXTENSION);
        $sampul = 'sampul_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['sampul']['tmp_name'], '../uploads/buku/' . $sampul);
    }
    
    // Upload PDF
    $file_pdf = '';
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] == 0) {
        $ext = pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION);
        $file_pdf = 'buku_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['file_pdf']['tmp_name'], '../uploads/buku/' . $file_pdf);
    }
    
    $sql = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori_id, stok, file_pdf, sampul, deskripsi) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori_id, $stok, $file_pdf, $sampul, $deskripsi])) {
        flash('buku_success', 'Buku berhasil ditambahkan!', 'alert alert-success');
    } else {
        flash('buku_error', 'Gagal menambahkan buku!', 'alert alert-danger');
    }
}

// Hapus buku
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $sql = "DELETE FROM buku WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id])) {
        flash('buku_success', 'Buku berhasil dihapus!', 'alert alert-success');
    } else {
        flash('buku_error', 'Gagal menghapus buku!', 'alert alert-danger');
    }
    redirect('buku.php');
}

// Ambil data buku
$sql = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id ORDER BY b.created_at DESC";
$buku = $pdo->query($sql)->fetchAll();

// Ambil kategori
$kategori = $pdo->query("SELECT * FROM kategori")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Buku - Perpustakaan ITBI</title>
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
                <h1><i class="fas fa-book"></i>Manajemen Buku</h1>
                <p>Kelola koleksi buku perpustakaan</p>
            </div>
            
            <?php flash('buku_success'); ?>
            <?php flash('buku_error'); ?>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Daftar Buku</h3>
                    <button class="btn btn-primary" onclick="toggleForm()">
                        <i class="fas fa-plus"></i> Tambah Buku
                    </button>
                </div>
                
                <!-- Form Tambah Buku -->
                <div id="formTambah" style="display: none; padding: 2rem;">
                    <form method="POST" enctype="multipart/form-data" class="form-container">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="judul">Judul Buku *</label>
                                <input type="text" name="judul" id="judul" required>
                            </div>
                            <div class="form-group">
                                <label for="penulis">Penulis *</label>
                                <input type="text" name="penulis" id="penulis" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="penerbit">Penerbit *</label>
                                <input type="text" name="penerbit" id="penerbit" required>
                            </div>
                            <div class="form-group">
                                <label for="tahun_terbit">Tahun Terbit *</label>
                                <input type="number" name="tahun_terbit" id="tahun_terbit" min="1900" max="<?= date('Y') ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="isbn">ISBN</label>
                                <input type="text" name="isbn" id="isbn">
                            </div>
                            <div class="form-group">
                                <label for="kategori_id">Kategori</label>
                                <select name="kategori_id" id="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach($kategori as $k): ?>
                                        <option value="<?= $k['id'] ?>"><?= $k['nama_kategori'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="stok">Stok *</label>
                                <input type="number" name="stok" id="stok" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="sampul">Sampul Buku</label>
                                <input type="file" name="sampul" id="sampul" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="file_pdf">File PDF *</label>
                            <input type="file" name="file_pdf" id="file_pdf" accept=".pdf" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="4"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="toggleForm()">Batal</button>
                            <button type="submit" name="tambah_buku" class="btn btn-primary">Simpan Buku</button>
                        </div>
                    </form>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Sampul</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($buku)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Tidak ada data buku</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($buku as $key => $b): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <?php if($b['sampul']): ?>
                                        <img src="../uploads/buku/<?= $b['sampul'] ?>" alt="Sampul" style="width: 50px; height: 70px; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 70px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-book" style="color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $b['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $b['penerbit'] ?> (<?= $b['tahun_terbit'] ?>)</small>
                                </td>
                                <td><?= $b['penulis'] ?></td>
                                <td><?= $b['nama_kategori'] ?></td>
                                <td>
                                    <span class="badge <?= $b['stok'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $b['stok'] ?> tersedia
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_buku.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="buku.php?hapus=<?= $b['id'] ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php if($b['file_pdf']): ?>
                                        <a href="../pdf-viewer/view.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
        function toggleForm() {
            const form = document.getElementById('formTambah');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>