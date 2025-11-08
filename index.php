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
        
        body {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            padding: 80px 0;
            text-align: center;
            color: white;
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
        }
        
        .logo-container i {
            font-size: 80px;
            color: var(--primary-black);
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-yellow);
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: #ddd;
            margin-bottom: 40px;
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
        
        .login-cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 50px;
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
        
        .info-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 50px;
            margin-top: 80px;
            color: white;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .info-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }
        
        .info-icon i {
            font-size: 30px;
            color: var(--primary-black);
        }
        
        footer {
            background: var(--primary-black);
            color: var(--primary-yellow);
            text-align: center;
            padding: 20px;
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section">
            <div class="logo-container">
                <i class="fas fa-school"></i>
            </div>
            
            <h1 class="hero-title"><?php echo SITE_NAME; ?></h1>
            <p class="hero-subtitle">SMKN 11 Kabupaten Tangerang <br>Sistem Manajemen Absensi Digital Berbasis GPS & Kamera</p>
            
            <div class="clock-container">
                <div class="clock" id="clock">00:00:00</div>
                <div class="date" id="date">Loading...</div>
            </div>
            
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
        
        <div class="info-section">
            <h2 class="text-center mb-5" style="color: var(--primary-yellow);">Fitur Sistem</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div>
                            <h5>Absensi Foto</h5>
                            <p class="mb-0">Validasi kehadiran dengan selfie realtime</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h5>GPS Tracking</h5>
                            <p class="mb-0">Pastikan siswa absen dari area sekolah</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h5>Laporan Real-time</h5>
                            <p class="mb-0">Monitoring kehadiran siswa secara langsung</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <p class="mb-0">&copy; 2025 <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>

    <script>
        function updateClock() {
            const now = new Date();
            
            // Format jam
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
            
            // Format tanggal
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const dayName = days[now.getDay()];
            const day = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            
            document.getElementById('date').textContent = `${dayName}, ${day} ${month} ${year}`;
        }
        
        // Update setiap detik
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>