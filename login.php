<?php
include 'includes/config.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Coba login sebagai admin terlebih dahulu
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_nama'] = $admin['nama'];
        redirect('admin/dashboard.php');
    }
    
    // Jika bukan admin, coba login sebagai mahasiswa
    $sql = "SELECT * FROM mahasiswa WHERE nim = ? AND status = 'aktif'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $mahasiswa = $stmt->fetch();
    
    if ($mahasiswa && password_verify($password, $mahasiswa['password'])) {
        $_SESSION['mahasiswa_id'] = $mahasiswa['id'];
        $_SESSION['mahasiswa_nim'] = $mahasiswa['nim'];
        $_SESSION['mahasiswa_nama'] = $mahasiswa['nama'];
        redirect('user/dashboard.php');
    }
    
    // Jika kedua login gagal
    flash('login_error', 'Username/NIM atau password salah', 'alert alert-danger');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <img src="assets/images/LOGO_ITBI.png" alt="logo_itbi">
                <h2>ITBI Library</h2>
                <p class="text-muted">Sistem Perpustakaan Digital</p>
            </div>
            
            <?php flash('login_error'); ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username / NIM
                    </label>
                    <input type="text" name="username" id="username" placeholder="Masukkan username admin atau NIM mahasiswa" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="login-info">
                <div class="info-card">
                    <h4><i class="fas fa-info-circle"></i> Informasi Login</h4>
                    <div class="info-item">
                        <strong>Admin:</strong> Gunakan username admin
                    </div>
                    <div class="info-item">
                        <strong>Mahasiswa:</strong> Gunakan NIM sebagai username
                    </div>
                </div>
            </div>
            
            <div class="login-links">
                <p>Belum punya akun mahasiswa? <a href="register.php">Daftar di sini</a></p>
                <p><a href="index.php"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a></p>
            </div>
        </div>
    </div>

    <style>
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
        }
        
        .login-info {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid var(--primary);
        }
        
        .info-card h4 {
            margin: 0 0 0.5rem 0;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .info-item {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
            color: #666;
        }
        
        .info-item strong {
            color: var(--dark);
        }
        
        .login-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .login-links p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }
        
        .text-muted {
            color: #6c757d;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h2 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
    </style>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Enter key to submit form
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>