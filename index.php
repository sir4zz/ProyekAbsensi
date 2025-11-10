<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-yellow: #FFD700;
            --dark-yellow: #FFC700;
            --primary-black: #1a1a1a;
            --secondary-black: #2d2d2d;
        }
        
        * {
            scroll-behavior: smooth;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }
        
        .navbar {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-yellow) !important;
            font-weight: bold;
            font-size: 1.3rem;
        }
        
        .navbar-brand .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .navbar-brand .logo-icon i {
            font-size: 20px;
            color: var(--primary-black);
        }
        
        .nav-link {
            color: #ddd !important;
            font-weight: 500;
            padding: 8px 15px !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-yellow) !important;
        }
        
        .hero-section {
            padding: 100px 0 80px;
            text-align: center;
        }
        
        .logo-container {
            width: 150px;
            height: 150px;
            background: var(--primary-yellow);
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .logo-container i {
            font-size: 80px;
            color: var(--primary-black);
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-yellow);
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .hero-description {
            font-size: 1rem;
            color: #bbb;
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }
        
        .clock-container {
            background: rgba(255, 215, 0, 0.1);
            border: 2px solid var(--primary-yellow);
            border-radius: 15px;
            padding: 30px;
            margin: 40px auto;
            max-width: 500px;
        }
        
        .clock {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-yellow);
            font-family: 'Courier New', monospace;
        }
        
        .date {
            font-size: 1.2rem;
            color: #ddd;
            margin-top: 10px;
        }
        
        .login-section {
            padding: 60px 0;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-yellow);
            margin-bottom: 20px;
        }
        
        .section-subtitle {
            text-align: center;
            color: #bbb;
            margin-bottom: 50px;
            font-size: 1.1rem;
        }
        
        .login-cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--primary-yellow);
            border-radius: 20px;
            padding: 40px 30px;
            width: 280px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: white;
            display: block;
        }
        
        .login-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 215, 0, 0.1);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.3);
            color: white;
        }
        
        .login-card-icon {
            font-size: 60px;
            color: var(--primary-yellow);
            margin-bottom: 20px;
        }
        
        .login-card-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .login-card-desc {
            font-size: 0.9rem;
            color: #bbb;
        }
        
        .features-section {
            padding: 80px 0;
            background: rgba(255, 255, 255, 0.02);
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            border-color: var(--primary-yellow);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.2);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .feature-icon i {
            font-size: 35px;
            color: var(--primary-black);
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-yellow);
            margin-bottom: 15px;
        }
        
        .feature-desc {
            color: #bbb;
            line-height: 1.6;
        }
        
        .about-section {
            padding: 80px 0;
        }
        
        .about-content {
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid var(--primary-yellow);
            padding: 40px;
            border-radius: 15px;
        }
        
        .about-list {
            list-style: none;
            padding: 0;
        }
        
        .about-list li {
            padding: 10px 0;
            color: #ddd;
            font-size: 1.1rem;
        }
        
        .about-list li i {
            color: var(--primary-yellow);
            margin-right: 15px;
            font-size: 1.3rem;
        }
        
        .stats-section {
            padding: 60px 0;
            background: rgba(255, 215, 0, 0.05);
        }
        
        .stat-box {
            text-align: center;
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-yellow);
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #bbb;
            margin-top: 10px;
        }
        
        .contact-section {
            padding: 80px 0;
        }
        
        .contact-card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            border-color: var(--primary-yellow);
            transform: translateX(10px);
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            float: left;
            margin-right: 20px;
        }
        
        .contact-icon i {
            font-size: 25px;
            color: var(--primary-black);
        }
        
        .contact-info h5 {
            color: var(--primary-yellow);
            margin-bottom: 5px;
        }
        
        .contact-info p {
            color: #ddd;
            margin: 0;
        }
        
        .contact-info a {
            color: var(--primary-yellow);
            text-decoration: none;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
        
        footer {
            background: var(--primary-black);
            color: #bbb;
            text-align: center;
            padding: 30px 0;
            border-top: 2px solid var(--primary-yellow);
        }
        
        footer .social-links {
            margin: 20px 0;
        }
        
        footer .social-links a {
            color: var(--primary-yellow);
            font-size: 1.5rem;
            margin: 0 15px;
            transition: all 0.3s ease;
        }
        
        footer .social-links a:hover {
            transform: scale(1.2);
            color: var(--dark-yellow);
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .login-card {
                width: 100%;
                max-width: 350px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <div class="logo-icon">
                    <i class="fas fa-school"></i>
                </div>
                <span>SMKN 11 Kab. Tangerang</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section">
        <div class="container">
            <div class="logo-container">
                <i class="fas fa-school"></i>
            </div>
            
            <h1 class="hero-title"><?php echo SITE_NAME; ?></h1>
            <p class="hero-subtitle">SMKN 11 Kabupaten Tangerang </p>
            <p class="hero-description">
                Sistem manajemen absensi digital modern berbasis GPS dan kamera untuk meningkatkan 
                efisiensi dan akurasi pencatatan kehadiran siswa.
            </p>
            
            <div class="clock-container">
                <div class="clock" id="clock">00:00:00</div>
                <div class="date" id="date">Loading...</div>
            </div>
        </div>
    </section>

    <section id="login" class="login-section">
        <div class="container">
            <h2 class="section-title">Portal Login</h2>
            <p class="section-subtitle">Pilih portal sesuai dengan peran Anda</p>
            
            <div class="login-cards">
                <a href="login_admin.php" class="login-card">
                    <div class="login-card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="login-card-title">Login Admin</div>
                    <div class="login-card-desc">Kelola sistem, data guru & siswa</div>
                </a>
                
                <a href="login_guru.php" class="login-card">
                    <div class="login-card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="login-card-title">Login Guru</div>
                    <div class="login-card-desc">Monitoring absensi siswa</div>
                </a>
                
                <a href="login_siswa.php" class="login-card">
                    <div class="login-card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="login-card-title">Login Siswa</div>
                    <div class="login-card-desc">Absensi & lihat riwayat</div>
                </a>
            </div>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Fitur Unggulan</h2>
            <p class="section-subtitle">Teknologi terkini untuk sistem absensi yang lebih baik</p>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3 class="feature-title">Absensi Foto</h3>
                        <p class="feature-desc">
                            Validasi kehadiran dengan foto selfie real-time menggunakan kamera.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="feature-title">GPS Tracking</h3>
                        <p class="feature-desc">
                            Sistem GPS memastikan siswa melakukan absensi dari area sekolah.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="feature-title">Real-Time</h3>
                        <p class="feature-desc">
                            Data absensi terekam secara real-time dengan timestamp akurat.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Mobile Friendly</h3>
                        <p class="feature-desc">
                            Responsive design yang dapat diakses dari semua device.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <h3 class="feature-title">Export Excel</h3>
                        <p class="feature-desc">
                            Export data absensi ke format Excel untuk pelaporan.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Keamanan Data</h3>
                        <p class="feature-desc">
                            Sistem keamanan berlapis dengan enkripsi password.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">
                            <i class="fas fa-check-circle"></i> 100%
                        </div>
                        <div class="stat-label">Akurasi Data</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">
                            <i class="fas fa-bolt"></i> Real-Time
                        </div>
                        <div class="stat-label">Monitoring Langsung</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">
                            <i class="fas fa-users"></i> Multi-User
                        </div>
                        <div class="stat-label">3 Role Pengguna</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title" style="text-align: left;">Tentang Sistem</h2>
                    <p style="color: #bbb; font-size: 1.1rem; line-height: 1.8;">
                        Sistem Absensi Digital SMKN 11 Kabupaten Tangerang adalah platform modern 
                        yang dirancang untuk meningkatkan efisiensi pencatatan kehadiran siswa.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="about-content">
                        <h4 style="color: var(--primary-yellow); margin-bottom: 20px;">
                            <i class="fas fa-check-double"></i> Keunggulan Sistem
                        </h4>
                        <ul class="about-list">
                            <li><i class="fas fa-check"></i> Validasi kehadiran foto & GPS</li>
                            <li><i class="fas fa-check"></i> Status Hadir, Izin, Sakit</li>
                            <li><i class="fas fa-check"></i> Dashboard monitoring real-time</li>
                            <li><i class="fas fa-check"></i> Riwayat absensi lengkap</li>
                            <li><i class="fas fa-check"></i> Export data ke Excel</li>
                            <li><i class="fas fa-check"></i> Multi-device support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <h2 class="section-title">Hubungi Kami</h2>
            <p class="section-subtitle">Butuh bantuan atau informasi lebih lanjut? Hubungi kami</p>
            
            <div class="row mt-5">
                <div class="col-lg-6 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-info">
                            <h5>Alamat Sekolah</h5>
                            <p><?php echo SCHOOL_ADDRESS; ?></p>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-info">
                            <h5>Telepon</h5>
                            <p><a href="tel:+622154393249">(021) 5439-3249</a></p>
                            <p><small>Senin - Jumat: 07:00 - 16:00 WIB</small></p>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-info">
                            <h5>Email</h5>
                            <p><a href="mailto:smkn11kabtng@gmail.com">smkn11kabtng@gmail.com</a></p>
                            <p><small>Respon dalam 1x24 jam</small></p>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="contact-info">
                            <h5>Website Sekolah</h5>
                            <p><a href="http://smkn11kabtangerang.sch.id" target="_blank">smkn11kabtangerang.sch.id</a></p>
                            <p><small>Info lengkap sekolah</small></p>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="social-links">
                <a href="https://www.facebook.com/smkn11kabtangerang" target="_blank" title="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://www.instagram.com/smkn11kabtangerang" target="_blank" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.youtube.com/@smkn11kabtangerang" target="_blank" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="mailto:smkn11kabtng@gmail.com" title="Email">
                    <i class="fas fa-envelope"></i>
                </a>
            </div>
            
            <p style="margin: 20px 0; color: #bbb;">
                <i class="fas fa-map-marker-alt"></i> 
                Kp. Saradan RT. 03/01, Pangkat, Kec. Jayanti, Kab. Tangerang, Banten 15610
            </p>
            
            <p style="color: var(--primary-yellow); font-weight: 600;">
                &copy; 2025 <?php echo SITE_NAME; ?>. All rights reserved.
            </p>
            
            <p style="margin-top: 10px; font-size: 0.9rem;">
                Developed with <i class="fas fa-heart" style="color: #ff0000;"></i> for Indonesian Education
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
            
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const dayName = days[now.getDay()];
            const day = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            
            document.getElementById('date').textContent = `${dayName}, ${day} ${month} ${year}`;
        }
        
        updateClock();
        setInterval(updateClock, 1000);
        
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(26, 26, 26, 1)';
            } else {
                navbar.style.background = 'rgba(26, 26, 26, 0.95)';
            }
        });
    </script>
</body>
</html>