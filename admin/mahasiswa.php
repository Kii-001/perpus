<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
requireAdminLogin();

// Update status mahasiswa
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    
    $sql = "UPDATE mahasiswa SET status = CASE WHEN status = 'aktif' THEN 'nonaktif' ELSE 'aktif' END WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id])) {
        flash('mahasiswa_success', 'Status mahasiswa berhasil diupdate!', 'alert alert-success');
    } else {
        flash('mahasiswa_error', 'Gagal mengupdate status mahasiswa!', 'alert alert-danger');
    }
    redirect('mahasiswa.php');
}

// Reset password
if (isset($_GET['reset_password'])) {
    $id = $_GET['reset_password'];
    $hashed_password = password_hash('123456', PASSWORD_DEFAULT); // Password default
    
    $sql = "UPDATE mahasiswa SET password = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$hashed_password, $id])) {
        flash('mahasiswa_success', 'Password berhasil direset ke "123456"!', 'alert alert-success');
    } else {
        flash('mahasiswa_error', 'Gagal reset password!', 'alert alert-danger');
    }
    redirect('mahasiswa.php');
}

// Ambil data mahasiswa
$sql = "SELECT * FROM mahasiswa ORDER BY created_at DESC";
$mahasiswa = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - Perpustakaan ITBI</title>
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
                <h1>Data Mahasiswa</h1>
                <p>Kelola data mahasiswa yang terdaftar</p>
            </div>
            
            <?php flash('mahasiswa_success'); ?>
            <?php flash('mahasiswa_error'); ?>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Daftar Mahasiswa</h3>
                    <div class="table-actions">
                        <span class="text-muted">Total: <?= count($mahasiswa) ?> mahasiswa</span>
                    </div>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Jurusan</th>
                            <th>Angkatan</th>
                            <th>Status</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($mahasiswa)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center;">Tidak ada data mahasiswa</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($mahasiswa as $key => $m): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><strong><?= $m['nim'] ?></strong></td>
                                <td><?= $m['nama'] ?></td>
                                <td><?= $m['email'] ?></td>
                                <td><?= $m['jurusan'] ?></td>
                                <td><?= $m['angkatan'] ?></td>
                                <td>
                                    <span class="badge <?= $m['status'] == 'aktif' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= ucfirst($m['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($m['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="mahasiswa.php?toggle_status=<?= $m['id'] ?>" 
                                           class="btn btn-sm <?= $m['status'] == 'aktif' ? 'btn-warning' : 'btn-success' ?>"
                                           onclick="return confirm('Yakin ingin <?= $m['status'] == 'aktif' ? 'nonaktifkan' : 'aktifkan' ?> mahasiswa ini?')">
                                            <i class="fas <?= $m['status'] == 'aktif' ? 'fa-ban' : 'fa-check' ?>"></i>
                                            <?= $m['status'] == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </a>
                                        <a href="mahasiswa.php?reset_password=<?= $m['id'] ?>" 
                                           class="btn btn-sm btn-info"
                                           onclick="return confirm('Yakin reset password mahasiswa ini? Password akan direset ke \"123456\"')">
                                            <i class="fas fa-key"></i> Reset Password
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
</body>
</html>