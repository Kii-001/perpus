<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();

// Tambah kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);
    
    $sql = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nama_kategori, $deskripsi])) {
        flash('kategori_success', 'Kategori berhasil ditambahkan!', 'alert alert-success');
    } else {
        flash('kategori_error', 'Gagal menambahkan kategori!', 'alert alert-danger');
    }
}

// Edit kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_kategori'])) {
    $id = $_POST['id'];
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);
    
    $sql = "UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nama_kategori, $deskripsi, $id])) {
        flash('kategori_success', 'Kategori berhasil diupdate!', 'alert alert-success');
    } else {
        flash('kategori_error', 'Gagal mengupdate kategori!', 'alert alert-danger');
    }
}

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Cek apakah kategori digunakan oleh buku
    $sql = "SELECT COUNT(*) FROM buku WHERE kategori_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        flash('kategori_error', 'Tidak dapat menghapus kategori karena masih digunakan oleh buku!', 'alert alert-danger');
    } else {
        $sql = "DELETE FROM kategori WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            flash('kategori_success', 'Kategori berhasil dihapus!', 'alert alert-success');
        } else {
            flash('kategori_error', 'Gagal menghapus kategori!', 'alert alert-danger');
        }
    }
    redirect('kategori.php');
}

// Ambil data kategori
$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Perpustakaan ITBI</title>
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
                <h1>Manajemen Kategori</h1>
                <p>Kelola kategori buku perpustakaan</p>
            </div>
            
            <?php flash('kategori_success'); ?>
            <?php flash('kategori_error'); ?>
            
            <div class="form-container" style="margin-bottom: 2rem;">
                <h3>Tambah Kategori Baru</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_kategori">Nama Kategori *</label>
                            <input type="text" name="nama_kategori" id="nama_kategori" required>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="2" placeholder="Deskripsi singkat kategori..."></textarea>
                        </div>
                    </div>
                    <button type="submit" name="tambah_kategori" class="btn btn-primary">Tambah Kategori</button>
                </form>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Daftar Kategori</h3>
                    <div class="table-actions">
                        <span class="text-muted">Total: <?= count($kategori) ?> kategori</span>
                    </div>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Buku</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($kategori)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Belum ada kategori</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($kategori as $key => $k): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <strong><?= $k['nama_kategori'] ?></strong>
                                </td>
                                <td><?= $k['deskripsi'] ?: '-' ?></td>
                                <td>
                                    <?php
                                    $sql = "SELECT COUNT(*) FROM buku WHERE kategori_id = ?";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$k['id']]);
                                    $jumlah_buku = $stmt->fetchColumn();
                                    ?>
                                    <span class="badge badge-info"><?= $jumlah_buku ?> buku</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="editKategori(<?= $k['id'] ?>, '<?= $k['nama_kategori'] ?>', '<?= $k['deskripsi'] ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="kategori.php?hapus=<?= $k['id'] ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
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
    
    <!-- Modal Edit Kategori -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Kategori</h3>
                <button type="button" class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_nama_kategori">Nama Kategori *</label>
                        <input type="text" name="nama_kategori" id="edit_nama_kategori" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                        <button type="submit" name="edit_kategori" class="btn btn-primary">Update Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function editKategori(id, nama, deskripsi) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_kategori').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
    
    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--primary);
        }
        
        .modal-header .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
    </style>
</body>
</html>