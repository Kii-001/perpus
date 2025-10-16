<?php
include 'includes/config.php';
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital - Institut Teknologi dan Bisnis Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="floating-books">
                <div class="book book-1"><i class="fas fa-book"></i></div>
                <div class="book book-2"><i class="fas fa-book-open"></i></div>
                <div class="book book-3"><i class="fas fa-graduation-cap"></i></div>
                <div class="book book-4"><i class="fas fa-laptop-code"></i></div>
                <div class="book book-5"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-logo">
                    <div class="logo-circle">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h1>ITBI<span>Library</span></h1>
                </div>
                <h2 class="hero-title">
                    <span class="title-line">Perpustakaan Digital</span>
                    <span class="title-line highlight">ITB Indonesia</span>
                </h2>
                <p class="hero-description">
                    Akses ribuan buku digital dari berbagai bidang ilmu teknologi dan bisnis. 
                    Transformasi pembelajaran digital untuk masa depan yang lebih cerah.
                </p>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number" data-count="1000">0</span>
                        <span class="stat-label">Buku Digital</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" data-count="5000">0</span>
                        <span class="stat-label">Mahasiswa</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" data-count="24">0</span>
                        <span class="stat-label">Jam/7 Hari</span>
                    </div>
                </div>
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary btn-hero">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk ke Sistem</span>
                    </a>
                    <a href="register.php" class="btn btn-secondary btn-hero">
                        <i class="fas fa-user-plus"></i>
                        <span>Daftar Mahasiswa</span>
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-card card-1">
                    <i class="fas fa-book"></i>
                    <h4>E-Book</h4>
                </div>
                <div class="floating-card card-2">
                    <i class="fas fa-search"></i>
                    <h4>Pencarian</h4>
                </div>
                <div class="floating-card card-3">
                    <i class="fas fa-download"></i>
                    <h4>Download</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Mengapa Memilih ITBI Library?</h2>
                <p class="section-subtitle">Platform perpustakaan digital terdepan dengan fitur-fitur unggulan</p>
            </div>
            <div class="features-grid">
                <div class="feature-card" data-aos="fade-up">
                    <div class="feature-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Koleksi Lengkap</h3>
                    <p>Ribuan buku digital dari berbagai disiplin ilmu teknologi, bisnis, dan sains terkini</p>
                    <div class="feature-badge">1000+ Buku</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Akses Cepat</h3>
                    <p>Baca buku langsung online tanpa download dengan loading time yang optimal</p>
                    <div class="feature-badge">Instant Read</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Responsive Design</h3>
                    <p>Akses dari berbagai perangkat - desktop, tablet, atau smartphone</p>
                    <div class="feature-badge">Mobile Friendly</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Keamanan Terjamin</h3>
                    <p>Sistem keamanan terenkripsi untuk melindungi data dan privasi pengguna</p>
                    <div class="feature-badge">Secure</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Analitik Pembaca</h3>
                    <p>Pantau aktivitas membaca dan rekomendasi buku berdasarkan minat</p>
                    <div class="feature-badge">Smart Analytics</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Kolaborasi</h3>
                    <p>Fitur diskusi dan berbagi catatan dengan sesama mahasiswa</p>
                    <div class="feature-badge">Community</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Cara Menggunakan</h2>
                <p class="section-subtitle">Hanya dalam 3 langkah mudah</p>
            </div>
            <div class="steps">
                <div class="step" data-aos="zoom-in">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Daftar Akun</h3>
                    <p>Registrasi sebagai mahasiswa ITBI dengan NIM Anda</p>
                </div>
                <div class="step-connector"></div>
                <div class="step" data-aos="zoom-in" data-aos-delay="100">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Cari Buku</h3>
                    <p>Temukan buku yang diinginkan melalui fitur pencarian</p>
                </div>
                <div class="step-connector"></div>
                <div class="step" data-aos="zoom-in" data-aos-delay="200">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3>Baca & Pinjam</h3>
                    <p>Baca online atau pinjam buku digital dengan mudah</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2 data-aos="fade-right">Siap Memulai Perjalanan Membaca?</h2>
                <p data-aos="fade-right" data-aos-delay="100">
                    Bergabunglah dengan ribuan mahasiswa ITBI yang sudah merasakan kemudahan akses pengetahuan digital
                </p>
                <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
                    <a href="register.php" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        Daftar Sekarang
                    </a>
                    <a href="login.php" class="btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                </div>
            </div>
            <div class="cta-image" data-aos="fade-left">
                <div class="cta-book">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Counter Animation
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200;

            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-count');
                    const count = +counter.innerText;
                    const increment = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + increment);
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target;
                    }
                };

                // Start counter when in viewport
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCount();
                            observer.unobserve(entry.target);
                        }
                    });
                });

                observer.observe(counter);
            });

            // Floating animation for books
            const books = document.querySelectorAll('.book');
            books.forEach((book, index) => {
                book.style.animationDelay = `${index * 0.5}s`;
            });

            // Floating cards animation
            const cards = document.querySelectorAll('.floating-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.3}s`;
            });
        });
    </script>

    <style>
        /* Modern CSS Variables */
        :root {
            --primary: #FF6B35;
            --primary-dark: #E05A2B;
            --secondary: #FFA500;
            --accent: #FF4500;
            --white: #FFFFFF;
            --light: #F8F9FA;
            --dark: #2D3748;
            --gray: #718096;
            --light-gray: #E2E8F0;
            --success: #48BB78;
            --warning: #ED8936;
            --danger: #F56565;
            --info: #4299E1;
        }

        /* Hero Section */
        .hero {
            position: relative;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: var(--white);
            padding: 100px 0;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .floating-books {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .book {
            position: absolute;
            font-size: 2rem;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .book-1 { top: 10%; left: 10%; animation-delay: 0s; }
        .book-2 { top: 20%; right: 15%; animation-delay: 1s; }
        .book-3 { bottom: 30%; left: 15%; animation-delay: 2s; }
        .book-4 { bottom: 20%; right: 20%; animation-delay: 3s; }
        .book-5 { top: 50%; left: 50%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
        }

        .hero-logo {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .logo-circle {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .hero-logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .hero-logo span {
            font-weight: 300;
            opacity: 0.9;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .title-line {
            display: block;
        }

        .highlight {
            color: var(--secondary);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-description {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary);
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .hero-image {
            position: absolute;
            right: 10%;
            top: 50%;
            transform: translateY(-50%);
        }

        .floating-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            animation: float-card 4s ease-in-out infinite;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .floating-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--secondary);
        }

        .floating-card h4 {
            margin: 0;
            font-size: 0.9rem;
        }

        .card-1 { top: -50px; right: 100px; animation-delay: 0s; }
        .card-2 { top: 50px; right: -50px; animation-delay: 1s; }
        .card-3 { bottom: -30px; right: 50px; animation-delay: 2s; }

        @keyframes float-card {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }

        /* Section Styles */
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background: var(--light);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--white);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .feature-badge {
            display: inline-block;
            background: var(--primary);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* How It Works Section */
        .how-it-works {
            padding: 100px 0;
            background: var(--white);
        }

        .steps {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .step {
            text-align: center;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
        }

        .step-icon {
            width: 80px;
            height: 80px;
            background: var(--light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: var(--primary);
        }

        .step h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .step p {
            color: var(--gray);
            line-height: 1.6;
        }

        .step-connector {
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            margin: 0 1rem;
        }

        /* CTA Section */
        .cta {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--dark) 0%, #4A5568 100%);
            color: var(--white);
        }

        .cta .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 1.2rem 2.5rem;
            font-size: 1.1rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--white);
            color: var(--white);
        }

        .btn-outline:hover {
            background: var(--white);
            color: var(--dark);
        }

        .cta-image {
            text-align: center;
        }

        .cta-book {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 4rem;
            color: var(--white);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }

            .hero-image {
                display: none;
            }

            .steps {
                flex-direction: column;
            }

            .step-connector {
                width: 2px;
                height: 50px;
                margin: 1rem 0;
            }

            .cta .container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>