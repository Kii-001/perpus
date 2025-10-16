<?php
include 'includes/config.php';
include 'includes/functions.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = trim($_POST['nim']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $jurusan = $_POST['jurusan'];
    $angkatan = $_POST['angkatan'];
    
    // Validasi
    $errors = [];
    
    // PERBAIKAN: Ubah dari 10 digit menjadi 8 digit untuk konsistensi
    if (strlen($nim) != 8) {
        $errors[] = "NIM harus 8 digit";
    }
    
    // PERBAIKAN: Tambah validasi hanya angka
    if (!preg_match('/^[0-9]{8}$/', $nim)) {
        $errors[] = "NIM harus terdiri dari 8 digit angka";
    }
    
    if ($password != $confirm_password) {
        $errors[] = "Password tidak cocok";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Cek NIM sudah ada
    $sql = "SELECT id FROM mahasiswa WHERE nim = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nim]);
    if ($stmt->fetch()) {
        $errors[] = "NIM sudah terdaftar";
    }
    
    // Cek email sudah ada
    $sql = "SELECT id FROM mahasiswa WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email sudah terdaftar";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO mahasiswa (nim, password, nama, email, jurusan, angkatan) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nim, $hashed_password, $nama, $email, $jurusan, $angkatan])) {
            // Kirim email notifikasi (simulasi)
            $to = $email;
            $subject = "Pendaftaran Berhasil - Perpustakaan ITBI";
            $message = "
            <html>
            <head>
                <title>Pendaftaran Berhasil</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #FF6B35, #FF4500); color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .footer { padding: 20px; text-align: center; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Perpustakaan Digital ITBI</h1>
                        <p>Pendaftaran Berhasil</p>
                    </div>
                    <div class='content'>
                        <h3>Halo $nama,</h3>
                        <p>Pendaftaran akun perpustakaan digital ITBI Anda telah berhasil!</p>
                        <p><strong>Detail Akun:</strong></p>
                        <ul>
                            <li>NIM: $nim</li>
                            <li>Nama: $nama</li>
                            <li>Jurusan: $jurusan</li>
                            <li>Angkatan: $angkatan</li>
                        </ul>
                        <p>Anda sekarang dapat login menggunakan NIM dan password yang telah dibuat.</p>
                        <p style='text-align: center; margin-top: 30px;'>
                            <a href='http://" . $_SERVER['HTTP_HOST'] . "/perpustakaan-itbi/login.php' style='background: #FF6B35; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>Login Sekarang</a>
                        </p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Institut Teknologi dan Bisnis Indonesia. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: perpustakaan@itbi.ac.id" . "\r\n";
            
            // Uncomment line below untuk mengaktifkan pengiriman email
            // mail($to, $subject, $message, $headers);
            
            // PERBAIKAN: Ganti flash() dengan session langsung
            $_SESSION['register_success'] = 'Pendaftaran berhasil! Silakan login.';
            header('Location: login.php');
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat mendaftar";
        }
    }
    
    // PERBAIKAN: Simpan errors di session
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Perpustakaan ITBI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <h2><i class="fas fa-user-graduate"></i> Daftar Mahasiswa ITBI</h2>
                <p class="text-muted">Isi form berikut untuk membuat akun perpustakaan</p>
            </div>
            
            <?php
            // PERBAIKAN: Tampilkan errors dari session
            if (isset($_SESSION['register_errors'])) {
                foreach ($_SESSION['register_errors'] as $error) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
                }
                unset($_SESSION['register_errors']);
            }
            
            // Tampilkan success message
            if (isset($_SESSION['register_success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['register_success']) . '</div>';
                unset($_SESSION['register_success']);
            }
            ?>
            
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="nim">NIM *</label>
                    <input type="text" name="nim" id="nim" required maxlength="8" 
                           pattern="[0-9]{8}" title="NIM harus 8 digit angka"
                           value="<?php echo isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>">
                    <small class="text-muted">8 digit NIM ITBI (contoh: 20240001)</small>
                    <div id="nim-feedback" class="feedback"></div>
                </div>
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" name="nama" id="nama" required
                           value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    <div id="nama-feedback" class="feedback"></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <small class="text-muted">Gunakan email aktif</small>
                    <div id="email-feedback" class="feedback"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jurusan">Jurusan *</label>
                        <select name="jurusan" id="jurusan" required>
                            <option value="">Pilih Jurusan</option>
                            <option value="Teknik Informatika" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Teknik Informatika') ? 'selected' : ''; ?>>Teknik Informatika</option>
                            <option value="Sistem Informasi" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Sistem Informasi') ? 'selected' : ''; ?>>Sistem Informasi</option>
                            <option value="Manajemen" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Manajemen') ? 'selected' : ''; ?>>Manajemen</option>
                            <option value="Akuntansi" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Akuntansi') ? 'selected' : ''; ?>>Akuntansi</option>
                            <option value="Desain Komunikasi Visual" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Desain Komunikasi Visual') ? 'selected' : ''; ?>>Desain Komunikasi Visual</option>
                            <option value="Teknik Elektro" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Teknik Elektro') ? 'selected' : ''; ?>>Teknik Elektro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="angkatan">Angkatan *</label>
                        <select name="angkatan" id="angkatan" required>
                            <option value="">Pilih Angkatan</option>
                            <?php for($year = date('Y'); $year >= 2010; $year--): ?>
                                <option value="<?= $year ?>" <?php echo (isset($_POST['angkatan']) && $_POST['angkatan'] == $year) ? 'selected' : ''; ?>>
                                    <?= $year ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <small id="strength-text">Kekuatan password</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password *</label>
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="confirm-feedback" class="feedback"></div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="agree_terms" id="agree_terms" required
                               <?php echo (isset($_POST['agree_terms'])) ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        Saya menyetujui <a href="#" target="_blank">Syarat dan Ketentuan</a> serta <a href="#" target="_blank">Kebijakan Privasi</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>
            
            <div class="login-links">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
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
        }
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-bar {
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak {
            background: #dc3545;
            width: 33%;
        }
        
        .strength-medium {
            background: #ffc107;
            width: 66%;
        }
        
        .strength-strong {
            background: #28a745;
            width: 100%;
        }
        
        .feedback {
            font-size: 0.85rem;
            margin-top: 0.25rem;
            min-height: 1rem;
        }
        
        .feedback.valid {
            color: #28a745;
        }
        
        .feedback.invalid {
            color: #dc3545;
        }
        
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .checkbox-container input {
            margin-right: 0.5rem;
            margin-top: 0.2rem;
        }
        
        .checkbox-container a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .checkbox-container a:hover {
            text-decoration: underline;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .login-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .login-links p {
            margin: 0.5rem 0;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
    
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentNode.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const feedback = document.getElementById('strength-text');
            const fill = document.getElementById('strength-fill');
            
            // Reset
            fill.className = 'strength-fill';
            fill.style.width = '0%';
            
            if (password.length === 0) {
                feedback.textContent = 'Kekuatan password';
                return;
            }
            
            // Length check
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            
            // Character variety checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Update UI
            if (strength <= 2) {
                fill.className = 'strength-fill strength-weak';
                feedback.textContent = 'Lemah';
                feedback.style.color = '#dc3545';
            } else if (strength <= 4) {
                fill.className = 'strength-fill strength-medium';
                feedback.textContent = 'Cukup';
                feedback.style.color = '#ffc107';
            } else {
                fill.className = 'strength-fill strength-strong';
                feedback.textContent = 'Kuat';
                feedback.style.color = '#28a745';
            }
        }
        
        // Real-time validation
        function validateField(field, value) {
            const feedback = document.getElementById(field + '-feedback');
            
            switch(field) {
                case 'nim':
                    if (value.length !== 8) {
                        showFeedback(feedback, 'NIM harus 8 digit', 'invalid');
                        return false;
                    } else if (!/^\d+$/.test(value)) {
                        showFeedback(feedback, 'NIM harus angka', 'invalid');
                        return false;
                    } else {
                        showFeedback(feedback, 'NIM valid', 'valid');
                        return true;
                    }
                    break;
                    
                case 'nama':
                    if (value.length < 2) {
                        showFeedback(feedback, 'Nama terlalu pendek', 'invalid');
                        return false;
                    } else {
                        showFeedback(feedback, 'Nama valid', 'valid');
                        return true;
                    }
                    break;
                    
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        showFeedback(feedback, 'Format email tidak valid', 'invalid');
                        return false;
                    } else {
                        showFeedback(feedback, 'Email valid', 'valid');
                        return true;
                    }
                    break;
                    
                case 'confirm_password':
                    const password = document.getElementById('password').value;
                    if (value !== password) {
                        showFeedback(feedback, 'Password tidak cocok', 'invalid');
                        return false;
                    } else {
                        showFeedback(feedback, 'Password cocok', 'valid');
                        return true;
                    }
                    break;
            }
        }
        
        function showFeedback(element, message, type) {
            element.textContent = message;
            element.className = 'feedback ' + type;
        }
        
        // Check NIM availability
        function checkNimAvailability(nim) {
            if (nim.length === 8) {
                // Simulasi check ke server
                const feedback = document.getElementById('nim-feedback');
                showFeedback(feedback, 'Memeriksa ketersediaan NIM...', '');
                
                // Dalam implementasi real, gunakan AJAX untuk check ke server
                setTimeout(() => {
                    // Ini hanya simulasi - dalam implementasi real, gunakan AJAX
                    showFeedback(feedback, 'NIM tersedia', 'valid');
                }, 1000);
            }
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            
            // Real-time validation
            document.getElementById('nim').addEventListener('input', function() {
                validateField('nim', this.value);
                if (this.value.length === 8) {
                    checkNimAvailability(this.value);
                }
            });
            
            document.getElementById('nama').addEventListener('input', function() {
                validateField('nama', this.value);
            });
            
            document.getElementById('email').addEventListener('input', function() {
                validateField('email', this.value);
            });
            
            document.getElementById('password').addEventListener('input', function() {
                checkPasswordStrength(this.value);
                // Trigger confirm password validation
                const confirm = document.getElementById('confirm_password');
                if (confirm.value) {
                    validateField('confirm_password', confirm.value);
                }
            });
            
            document.getElementById('confirm_password').addEventListener('input', function() {
                validateField('confirm_password', this.value);
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate all fields
                const fields = ['nim', 'nama', 'email', 'password', 'confirm_password'];
                fields.forEach(field => {
                    const value = document.getElementById(field).value;
                    if (!validateField(field, value)) {
                        isValid = false;
                    }
                });
                
                // Check terms agreement
                if (!document.getElementById('agree_terms').checked) {
                    alert('Anda harus menyetujui syarat dan ketentuan');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Harap perbaiki error pada form sebelum mendaftar');
                }
            });
        });
    </script>
</body>
</html>