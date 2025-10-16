<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();

$id = $_GET['id'] ?? 0;

// Ambil data buku
$sql = "SELECT * FROM buku WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$buku = $stmt->fetch();

if (!$buku) {
    flash('buku_error', 'Buku tidak ditemukan!', 'alert alert-danger');
    redirect('buku.php');
}

// Ambil kategori
$kategori = $pdo->query("SELECT * FROM kategori")->fetchAll();

// Update buku
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_buku'])) {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $penerbit = trim($_POST['penerbit']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = trim($_POST['isbn']);
    $kategori_id = $_POST['kategori_id'];
    $stok = $_POST['stok'];
    $deskripsi = trim($_POST['deskripsi']);
    
    $sampul = $buku['sampul'];
    $file_pdf = $buku['file_pdf'];
    
    // Upload sampul baru
    if (isset($_FILES['sampul']) && $_FILES['sampul']['error'] == 0) {
        // Hapus sampul lama jika ada
        if ($sampul && file_exists('../uploads/buku/' . $sampul)) {
            unlink('../uploads/buku/' . $sampul);
        }
        
        $ext = pathinfo($_FILES['sampul']['name'], PATHINFO_EXTENSION);
        $sampul = 'sampul_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['sampul']['tmp_name'], '../uploads/buku/' . $sampul);
    }
    
    // Upload PDF baru
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] == 0) {
        // Hapus PDF lama jika ada
        if ($file_pdf && file_exists('../uploads/buku/' . $file_pdf)) {
            unlink('../uploads/buku/' . $file_pdf);
        }
        
        $ext = pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION);
        $file_pdf = 'buku_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['file_pdf']['tmp_name'], '../uploads/buku/' . $file_pdf);
    }
    
    $sql = "UPDATE buku SET judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, isbn = ?, 
            kategori_id = ?, stok = ?, file_pdf = ?, sampul = ?, deskripsi = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori_id, $stok, $file_pdf, $sampul, $deskripsi, $id])) {
        flash('buku_success', 'Buku berhasil diupdate!', 'alert alert-success');
        redirect('buku.php');
    } else {
        flash('buku_error', 'Gagal mengupdate buku!', 'alert alert-danger');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan ITBI</title>
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
                <h1>Edit Buku</h1>
                <p>Update informasi buku</p>
            </div>
            
            <?php flash('buku_error'); ?>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="judul">Judul Buku *</label>
                            <input type="text" name="judul" id="judul" value="<?= htmlspecialchars($buku['judul']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="penulis">Penulis *</label>
                            <input type="text" name="penulis" id="penulis" value="<?= htmlspecialchars($buku['penulis']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="penerbit">Penerbit *</label>
                            <input type="text" name="penerbit" id="penerbit" value="<?= htmlspecialchars($buku['penerbit']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tahun_terbit">Tahun Terbit *</label>
                            <input type="number" name="tahun_terbit" id="tahun_terbit" 
                                   value="<?= $buku['tahun_terbit'] ?>" min="1900" max="<?= date('Y') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" name="isbn" id="isbn" value="<?= htmlspecialchars($buku['isbn']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="kategori_id">Kategori</label>
                            <select name="kategori_id" id="kategori_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($kategori as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= $buku['kategori_id'] == $k['id'] ? 'selected' : '' ?>>
                                        <?= $k['nama_kategori'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="stok">Stok *</label>
                            <input type="number" name="stok" id="stok" value="<?= $buku['stok'] ?>" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="sampul">Sampul Buku</label>
                            <input type="file" name="sampul" id="sampul" accept="image/*">
                            <?php if($buku['sampul']): ?>
                                <small class="text-muted">
                                    Sampul saat ini: 
                                    <a href="../uploads/buku/<?= $buku['sampul'] ?>" target="_blank">Lihat</a>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="file_pdf">File PDF</label>
                        <input type="file" name="file_pdf" id="file_pdf" accept=".pdf">
                        <?php if($buku['file_pdf']): ?>
                            <small class="text-muted">
                                File saat ini: <?= $buku['file_pdf'] ?>
                                (<a href="../uploads/buku/<?= $buku['file_pdf'] ?>" target="_blank">Download</a>)
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4"><?= htmlspecialchars($buku['deskripsi']) ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="buku.php" class="btn btn-secondary">Kembali</a>
                        <button type="submit" name="update_buku" class="btn btn-primary">Update Buku</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>