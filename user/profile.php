<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireMahasiswaLogin();

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil data mahasiswa
$sql = "SELECT * FROM mahasiswa WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mahasiswa_id]);
$mahasiswa = $stmt->fetch();

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $jurusan = $_POST['jurusan'];
    
    // Handle upload foto profil
    $foto_profil = $mahasiswa['foto_profil']; // Default ke foto lama
    
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            // Hapus foto lama jika ada
            if ($mahasiswa['foto_profil'] && file_exists('../' . $mahasiswa['foto_profil'])) {
                unlink('../' . $mahasiswa['foto_profil']);
            }
            
            $new_filename = 'profile_' . $mahasiswa_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_path)) {
                $foto_profil = 'assets/uploads/profiles/' . $new_filename;
            }
        }
    }
    
    $sql = "UPDATE mahasiswa SET nama = ?, email = ?, jurusan = ?, foto_profil = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nama, $email, $jurusan, $foto_profil, $mahasiswa_id])) {
        $_SESSION['mahasiswa_nama'] = $nama;
        $_SESSION['mahasiswa_foto'] = $foto_profil;
        flash('profile_success', 'Profile berhasil diupdate!', 'alert alert-success');
    } else {
        flash('profile_error', 'Gagal mengupdate profile!', 'alert alert-danger');
    }
    redirect('profile.php');
}

// Update password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verifikasi password saat ini
    if (!password_verify($current_password, $mahasiswa['password'])) {
        flash('password_error', 'Password saat ini salah!', 'alert alert-danger');
    } elseif ($new_password != $confirm_password) {
        flash('password_error', 'Password baru tidak cocok!', 'alert alert-danger');
    } elseif (strlen($new_password) < 6) {
        flash('password_error', 'Password minimal 6 karakter!', 'alert alert-danger');
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE mahasiswa SET password = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$hashed_password, $mahasiswa_id])) {
            flash('password_success', 'Password berhasil diubah!', 'alert alert-success');
        } else {
            flash('password_error', 'Gagal mengubah password!', 'alert alert-danger');
        }
    }
    redirect('profile.php');
}

// Hapus foto profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_foto'])) {
    if ($mahasiswa['foto_profil'] && file_exists('../' . $mahasiswa['foto_profil'])) {
        unlink('../' . $mahasiswa['foto_profil']);
    }
    
    $sql = "UPDATE mahasiswa SET foto_profil = NULL WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$mahasiswa_id])) {
        $_SESSION['mahasiswa_foto'] = null;
        flash('profile_success', 'Foto profil berhasil dihapus!', 'alert alert-success');
    } else {
        flash('profile_error', 'Gagal menghapus foto profil!', 'alert alert-danger');
    }
    redirect('profile.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-picture-section {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .profile-picture-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            margin: 0 auto 1rem auto;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .btn-file {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-file:hover {
            background: #0056b3;
        }
        
        .btn-remove {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-remove:hover {
            background: #c82333;
        }
        
        .file-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .photo-preview {
            display: none;
            max-width: 200px;
            margin: 1rem auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1><i class="fas fa-user"></i> Profile Saya</h1>
                <p>Kelola informasi profile dan akun Anda</p>
            </div>
            
            <?php flash('profile_success'); ?>
            <?php flash('profile_error'); ?>
            <?php flash('password_success'); ?>
            <?php flash('password_error'); ?>
            
            <div class="charts-grid">
                <!-- Foto Profil -->
                <div class="chart-card">
                    <h3>Foto Profil</h3>
                    <div class="profile-picture-section">
                        <?php if ($mahasiswa['foto_profil']): ?>
                            <img src="../<?= htmlspecialchars($mahasiswa['foto_profil']) ?>" 
                                 alt="Foto Profil" 
                                 class="profile-picture"
                                 id="currentPhoto">
                        <?php else: ?>
                            <div class="profile-picture-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <img id="photoPreview" class="photo-preview" alt="Preview">
                        
                        <div style="margin-top: 1rem;">
                            <div class="file-input-wrapper">
                                <button type="button" class="btn-file">
                                    <i class="fas fa-camera"></i> Pilih Foto Baru
                                </button>
                                <input type="file" name="foto_profil" id="foto_profil" 
                                       accept="image/*" form="profileForm">
                            </div>
                            
                            <?php if ($mahasiswa['foto_profil']): ?>
                                <form method="POST" style="display: inline-block;">
                                    <button type="submit" name="hapus_foto" class="btn-remove"
                                            onclick="return confirm('Yakin ingin menghapus foto profil?')">
                                        <i class="fas fa-trash"></i> Hapus Foto
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="file-info">
                            Format: JPG, PNG, GIF (Maks. 2MB)
                        </div>
                    </div>
                </div>
                
                <!-- Informasi Profile -->
                <div class="chart-card">
                    <h3>Informasi Profile</h3>
                    <form method="POST" id="profileForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nim">NIM</label>
                            <input type="text" id="nim" value="<?= $mahasiswa['nim'] ?>" readonly>
                            <small class="text-muted">NIM tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="nama">Nama Lengkap *</label>
                            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($mahasiswa['nama']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($mahasiswa['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jurusan">Jurusan *</label>
                            <select name="jurusan" id="jurusan" required>
                                <option value="Teknik Informatika" <?= $mahasiswa['jurusan'] == 'Teknik Informatika' ? 'selected' : '' ?>>Teknik Informatika</option>
                                <option value="Sistem Informasi" <?= $mahasiswa['jurusan'] == 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
                                <option value="Manajemen" <?= $mahasiswa['jurusan'] == 'Manajemen' ? 'selected' : '' ?>>Manajemen</option>
                                <option value="Akuntansi" <?= $mahasiswa['jurusan'] == 'Akuntansi' ? 'selected' : '' ?>>Akuntansi</option>
                                <option value="Desain Komunikasi Visual" <?= $mahasiswa['jurusan'] == 'Desain Komunikasi Visual' ? 'selected' : '' ?>>Desain Komunikasi Visual</option>
                                <option value="Teknik Elektro" <?= $mahasiswa['jurusan'] == 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="angkatan">Angkatan</label>
                            <input type="text" id="angkatan" value="<?= $mahasiswa['angkatan'] ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <input type="text" id="status" value="<?= ucfirst($mahasiswa['status']) ?>" readonly>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <!-- Ubah Password -->
                <div class="chart-card">
                    <h3>Ubah Password</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini *</label>
                            <input type="password" name="current_password" id="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Password Baru *</label>
                            <input type="password" name="new_password" id="new_password" required>
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru *</label>
                            <input type="password" name="confirm_password" id="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="update_password" class="btn btn-primary">Ubah Password</button>
                    </form>
                </div>
            </div>
            
            <!-- Statistik Pribadi -->
            <div class="stats-grid" style="margin-top: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
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
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Sedang Dipinjam</h3>
                        <p class="stat-number">
                            <?php
                            $sql = "SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ? AND status IN ('dipinjam', 'terlambat')";
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
                        <h3>Total Denda</h3>
                        <p class="stat-number">
                            Rp 
                            <?php
                            $sql = "SELECT COALESCE(SUM(d.jumlah_denda), 0) 
                                    FROM denda d 
                                    JOIN peminjaman p ON d.peminjaman_id = p.id 
                                    WHERE p.mahasiswa_id = ?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$mahasiswa_id]);
                            echo number_format($stmt->fetchColumn(), 0, ',', '.');
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Member Sejak</h3>
                        <p class="stat-number">
                            <?= date('M Y', strtotime($mahasiswa['created_at'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Preview foto sebelum upload
        document.getElementById('foto_profil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('photoPreview');
            const currentPhoto = document.getElementById('currentPhoto');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    
                    // Sembunyikan foto lama jika ada
                    if (currentPhoto) {
                        currentPhoto.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(file);
                
                // Validasi ukuran file (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    this.value = '';
                    preview.style.display = 'none';
                    if (currentPhoto) {
                        currentPhoto.style.display = 'block';
                    }
                }
            } else {
                preview.style.display = 'none';
                if (currentPhoto) {
                    currentPhoto.style.display = 'block';
                }
            }
        });
        
        // Validasi form sebelum submit
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('foto_profil');
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Hanya file gambar (JPG, PNG, GIF) yang diizinkan!');
                    e.preventDefault();
                    return false;
                }
            }
            return true;
        });
    </script>
</body>
</html>