<?php
$current_role = '';
$user_name = '';

if (isset($_SESSION['admin_id'])) {
    $current_role = 'admin';
    $user_name = $_SESSION['admin_nama'];
} elseif (isset($_SESSION['mahasiswa_id'])) {
    $current_role = 'mahasiswa';
    $user_name = $_SESSION['mahasiswa_nama'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital - ITBI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="navbar">
                <!-- Logo -->
                <div class="nav-brand">
                    <a href="index.php" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="logo-text">
                            <span class="logo-main">ITB Indonesia</span>
                            <span class="logo-sub">Library</span>
                        </div>
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>

                <!-- Navigation Links -->
                <div class="nav-menu" id="navMenu">
                    <ul class="nav-links">
                        <?php if($current_role == 'admin'): ?>
                            <!-- Menu untuk Admin -->
                            <li class="nav-item">
                                <a href="admin/dashboard.php" class="nav-link">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="admin/buku.php" class="nav-link">
                                    <i class="fas fa-book"></i>
                                    <span>Kelola Buku</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="admin/peminjaman.php" class="nav-link">
                                    <i class="fas fa-hand-holding"></i>
                                    <span>Peminjaman</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="admin/mahasiswa.php" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <span>Mahasiswa</span>
                                </a>
                            </li>
                            
                        <?php elseif($current_role == 'mahasiswa'): ?>
                            <!-- Menu untuk Mahasiswa -->
                            <li class="nav-item">
                                <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="buku.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'buku.php' ? 'active' : '' ?>">
                                    <i class="fas fa-book"></i>
                                    <span>Katalog Buku</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="pinjam.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'pinjam.php' ? 'active' : '' ?>">
                                    <i class="fas fa-hand-holding"></i>
                                    <span>Pinjam Buku</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="riwayat.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : '' ?>">
                                    <i class="fas fa-history"></i>
                                    <span>Riwayat</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="denda.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'denda.php' ? 'active' : '' ?>">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Denda</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="profile.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                                    <i class="fas fa-user"></i>
                                    <span>Profile</span>
                                </a>
                            </li>

                        <?php else: ?>
                            <!-- Menu untuk Guest -->
                            <li class="nav-item">
                                <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                                    <i class="fas fa-home"></i>
                                    <span>Beranda</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="login.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span>Login</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="register.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : '' ?>">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Daftar</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- User Info & Logout -->
                    <?php if($current_role): ?>
<div class="nav-user">
    <div class="user-dropdown">
        <button class="user-trigger" id="userTrigger">
            <div class="user-avatar">
                <?php if(isset($_SESSION['mahasiswa_foto']) && !empty($_SESSION['mahasiswa_foto'])): ?>
                    <img src="../<?= htmlspecialchars($_SESSION['mahasiswa_foto']) ?>" 
                         alt="Foto Profil" 
                         class="user-photo">
                <?php else: ?>
                    <div class="user-photo-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                <span class="user-role"><?= ucfirst($current_role) ?></span>
            </div>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
        </button>
        <div class="dropdown-menu" id="userDropdown">
            <a href="<?= $current_role == 'admin' ? 'admin/profile.php' : 'profile.php' ?>" class="dropdown-item">
                <i class="fas fa-user"></i>
                <span>Profile Saya</span>
            </a>
            <a href="<?= $current_role == 'admin' ? 'admin/dashboard.php' : 'dashboard.php' ?>" class="dropdown-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <style>
        /* User Photo Styles */
.user-avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-photo {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.user-photo:hover {
    border-color: rgba(255, 255, 255, 0.6);
    transform: scale(1.05);
}

.user-photo-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.2rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

/* Responsive for user photo */
@media (max-width: 480px) {
    .user-avatar {
        width: 35px;
        height: 35px;
    }
    
    .user-photo-placeholder {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .user-info {
        display: none;
    }
    
    .user-trigger {
        padding: 0.5rem;
        justify-content: center;
    }
    
    .dropdown-arrow {
        display: none;
    }
}
        /* Modern Header Styles */
        .main-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
            position: relative;
        }

        /* Logo Styles */
        .nav-brand {
            display: flex;
            align-items: center;
            z-index: 1001;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--white);
            gap: 0.75rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .logo-main {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo-sub {
            font-size: 0.8rem;
            opacity: 0.9;
            font-weight: 300;
        }

        /* Navigation Menu */
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 0.5rem;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            opacity: 0.9;
            white-space: nowrap;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            opacity: 1;
            transform: translateY(-1px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            opacity: 1;
        }

        .nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        /* User Dropdown */
        .nav-user {
            margin-left: 1rem;
        }

        .user-dropdown {
            position: relative;
        }

        .user-trigger {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 50px;
            color: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .user-trigger:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            font-size: 1.5rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.2;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .dropdown-arrow {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .user-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .user-dropdown.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(5px);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 0.9rem;
        }

        .dropdown-item:hover {
            background: var(--light);
            color: var(--primary);
        }

        .dropdown-item.logout {
            color: var(--danger);
        }

        .dropdown-item.logout:hover {
            background: var(--danger);
            color: var(--white);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--light-gray);
            margin: 0.5rem 0;
        }

        /* Mobile Toggle */
        .nav-toggle {
            display: none;
            flex-direction: column;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            gap: 4px;
            z-index: 1001;
        }

        .hamburger {
            width: 25px;
            height: 3px;
            background: var(--white);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .nav-toggle {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                top: 100%;
                left: 0;
                width: 100%;
                background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
                flex-direction: column;
                padding: 2rem 1rem;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                height: auto;
                max-height: calc(100vh - 100%);
                overflow-y: auto;
            }

            .nav-menu.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
                gap: 0;
            }

            .nav-item {
                width: 100%;
            }

            .nav-link {
                padding: 1rem;
                border-radius: 8px;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                justify-content: flex-start;
            }

            .nav-user {
                margin: 1rem 0 0 0;
                width: 100%;
            }

            .user-trigger {
                width: 100%;
                justify-content: space-between;
                border-radius: 8px;
                padding: 1rem;
            }

            .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                background: rgba(255,255,255,0.1);
                margin-top: 0.5rem;
                border-radius: 8px;
                display: none;
            }

            .user-dropdown.active .dropdown-menu {
                display: block;
            }

            .dropdown-item {
                color: var(--white);
                padding: 0.75rem 1rem;
            }

            .dropdown-item:hover {
                background: rgba(255,255,255,0.2);
                color: var(--white);
            }

            .nav-toggle.active .hamburger:nth-child(1) {
                transform: rotate(45deg) translate(6px, 6px);
            }

            .nav-toggle.active .hamburger:nth-child(2) {
                opacity: 0;
            }

            .nav-toggle.active .hamburger:nth-child(3) {
                transform: rotate(-45deg) translate(6px, -6px);
            }
        }

        @media (max-width: 480px) {
            .logo-text {
                display: none;
            }

            .logo-icon {
                width: 35px;
                height: 35px;
            }

            .user-info {
                display: none;
            }

            .user-trigger {
                padding: 0.5rem;
                justify-content: center;
            }

            .dropdown-arrow {
                display: none;
            }
        }

        /* Prevent body scroll when menu is open */
        body.menu-open {
            overflow: hidden;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.getElementById('navToggle');
            const navMenu = document.getElementById('navMenu');
            const userTrigger = document.getElementById('userTrigger');
            const userDropdown = document.getElementById('userDropdown');
            const body = document.body;

            // Mobile Navigation Toggle
            if (navToggle && navMenu) {
                navToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navMenu.classList.toggle('active');
                    navToggle.classList.toggle('active');
                    body.classList.toggle('menu-open');
                });

                // Close menu when clicking on links (mobile)
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 968) {
                            navMenu.classList.remove('active');
                            navToggle.classList.remove('active');
                            body.classList.remove('menu-open');
                        }
                    });
                });
            }

            // User Dropdown Toggle
            if (userTrigger) {
                userTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const userDropdownParent = this.closest('.user-dropdown');
                    userDropdownParent.classList.toggle('active');
                });

                // Close user dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (userTrigger && !userTrigger.contains(event.target) && !userDropdown.contains(event.target)) {
                        const userDropdownParent = userTrigger.closest('.user-dropdown');
                        userDropdownParent.classList.remove('active');
                    }
                });
            }

            // Close menus when clicking outside (general)
            document.addEventListener('click', function(event) {
                // Close mobile menu
                if (navToggle && navMenu && !navToggle.contains(event.target) && !navMenu.contains(event.target)) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                    body.classList.remove('menu-open');
                }
            });

            // Close menus when window is resized above breakpoint
            window.addEventListener('resize', function() {
                if (window.innerWidth > 968) {
                    if (navMenu) navMenu.classList.remove('active');
                    if (navToggle) navToggle.classList.remove('active');
                    body.classList.remove('menu-open');
                    
                    // Reset user dropdown for desktop
                    if (userTrigger) {
                        const userDropdownParent = userTrigger.closest('.user-dropdown');
                        userDropdownParent.classList.remove('active');
                    }
                }
            });

            // Add active class based on current page
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                if (linkHref) {
                    const linkPage = linkHref.split('/').pop();
                    if (linkPage === currentPage || 
                        (currentPage === '' && linkPage === 'index.php') ||
                        (linkHref.includes(currentPage.replace('.php', '')))) {
                        link.classList.add('active');
                    }
                }
            });

            // Handle escape key to close menus
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (navMenu) navMenu.classList.remove('active');
                    if (navToggle) navToggle.classList.remove('active');
                    body.classList.remove('menu-open');
                    
                    if (userTrigger) {
                        const userDropdownParent = userTrigger.closest('.user-dropdown');
                        userDropdownParent.classList.remove('active');
                    }
                }
            });
        });
    </script>