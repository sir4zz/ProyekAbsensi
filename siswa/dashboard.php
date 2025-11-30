<?php
require_once '../config.php';
requireLogin('siswa');

// Get siswa data
$id_siswa = $_SESSION['siswa_id'];
$siswa = $conn->query("SELECT * FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();

// Get today's absensi
$today = date('Y-m-d');
$absensi_today = $conn->query("SELECT * FROM absensi_lengkap WHERE id_siswa = $id_siswa AND tanggal = '$today'")->fetch_assoc();

// Get statistics
$total_hadir = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE id_siswa = $id_siswa AND status = 'Hadir'")->fetch_assoc()['total'];
$total_izin = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE id_siswa = $id_siswa AND status = 'Izin'")->fetch_assoc()['total'];
$total_sakit = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE id_siswa = $id_siswa AND status = 'Sakit'")->fetch_assoc()['total'];
$total_alfa = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE id_siswa = $id_siswa AND status = 'Alfa'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - <?php echo SITE_NAME; ?></title>
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
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-icon i {
            font-size: 25px;
            color: var(--primary-black);
        }
        
        .logo-text h5 {
            margin: 0;
            color: var(--primary-yellow);
            font-weight: bold;
        }
        
        .logo-text p {
            margin: 0;
            font-size: 0.85rem;
            color: #bbb;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .btn-logout {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }
        
        .btn-logout:hover {
            background: var(--dark-yellow);
            color: var(--primary-black);
        }
        
        .nav-tabs {
            border-bottom: 2px solid var(--primary-yellow);
            margin-top: 20px;
        }
        
        .nav-tabs .nav-link {
            color: var(--primary-black);
            border: none;
            padding: 12px 25px;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border-radius: 8px 8px 0 0;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .profile-header {
            display: flex;
            gap: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid var(--primary-yellow);
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
            background: var(--primary-yellow);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: var(--primary-black);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            color: white;
            font-size: 14px;
        }
        
        .profile-photo:hover .profile-photo-overlay {
            opacity: 1;
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-info h3 {
            color: var(--primary-black);
            margin-bottom: 10px;
        }
        
        .info-badge {
            display: inline-block;
            background: rgba(255, 215, 0, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .profile-details {
            margin-top: 25px;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        
        .detail-value {
            color: var(--primary-black);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 28px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-black);
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .alert-info-custom {
            background: rgba(255, 215, 0, 0.1);
            border-left: 4px solid var(--primary-yellow);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .btn-absen {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-absen:hover {
            background: var(--dark-yellow);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .form-control:focus {
            border-color: var(--primary-yellow);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        .btn-yellow {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-yellow:hover {
            background: var(--dark-yellow);
            color: var(--primary-black);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="logo-text">
                        <h5><?php echo SITE_NAME; ?></h5>
                        <p>Portal Siswa</p>
                    </div>
                </div>
                <div class="user-menu">
                    <div style="text-align: right;">
                        <div style="font-weight: 600;"><?php echo $siswa['nama_siswa']; ?></div>
                        <small style="color: #bbb;">Kelas <?php echo $siswa['kelas']; ?></small>
                    </div>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" style="margin-top: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" style="margin-top: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#profil">
                    <i class="fas fa-user"></i> Profil Saya
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#pengaturan">
                    <i class="fas fa-cog"></i> Pengaturan Akun
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="absen.php">
                    <i class="fas fa-camera"></i> Absensi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="riwayat.php">
                    <i class="fas fa-history"></i> Riwayat
                </a>
            </li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="profil">
                <!-- Status Absensi Hari Ini -->
                <?php if ($absensi_today): ?>
                    <div class="alert alert-success alert-info-custom">
                        <i class="fas fa-check-circle" style="font-size: 1.5rem; color: #28a745;"></i>
                        <strong>Absensi Hari Ini:</strong> Anda sudah melakukan absensi hari ini
                        <br>
                        <small>
                            Status: <strong><?php echo $absensi_today['status']; ?></strong> | 
                            Jam Masuk: <?php echo $absensi_today['jam_masuk'] ? date('H:i', strtotime($absensi_today['jam_masuk'])) : '-'; ?>
                            <?php if ($absensi_today['status'] == 'Hadir'): ?>
                                | Jam Pulang: <?php echo $absensi_today['jam_pulang'] ? date('H:i', strtotime($absensi_today['jam_pulang'])) : 'Belum pulang'; ?>
                            <?php endif; ?>
                        </small>
                        <?php if ($absensi_today['keterangan']): ?>
                            <br><small>Keterangan: <em><?php echo $absensi_today['keterangan']; ?></em></small>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert-info-custom">
                        <i class="fas fa-exclamation-circle" style="font-size: 1.5rem; color: var(--primary-yellow);"></i>
                        <strong>Anda belum melakukan absensi hari ini!</strong>
                        <br>
                        <small>Silakan klik menu "Absensi" untuk melakukan absensi</small>
                        <br>
                        <a href="absen.php" class="btn btn-absen mt-3">
                            <i class="fas fa-camera"></i> Absen Sekarang
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Profil Lengkap -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-photo" onclick="document.getElementById('uploadFoto').click()">
                            <?php if ($siswa['foto_profil'] && file_exists('../uploads/profile/' . $siswa['foto_profil'])): ?>
                                <img src="../uploads/profile/<?php echo $siswa['foto_profil']; ?>" alt="Foto Profil">
                            <?php else: ?>
                                <i class="fas fa-user-graduate"></i>
                            <?php endif; ?>
                            <div class="profile-photo-overlay">
                                <div>
                                    <i class="fas fa-camera"></i><br>
                                    Ubah Foto
                                </div>
                            </div>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo $siswa['nama_siswa']; ?></h3>
                            <span class="info-badge">
                                <i class="fas fa-id-card"></i> NIS: <?php echo $siswa['nis']; ?>
                            </span>
                            <span class="info-badge">
                                <i class="fas fa-school"></i> Kelas: <?php echo $siswa['kelas']; ?>
                            </span>
                            <span class="info-badge">
                                <i class="fas fa-venus-mars"></i> <?php echo $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        <h5 style="color: var(--primary-yellow); margin-bottom: 20px;">
                            <i class="fas fa-info-circle"></i> Informasi Pribadi
                        </h5>
                        
                        <div class="detail-row">
                            <div class="detail-label">Tempat, Tanggal Lahir</div>
                            <div class="detail-value">
                                <?php echo $siswa['tempat_lahir'] ? $siswa['tempat_lahir'] . ', ' : '-'; ?>
                                <?php echo $siswa['tanggal_lahir'] ? formatTanggal($siswa['tanggal_lahir']) : '-'; ?>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Alamat Lengkap</div>
                            <div class="detail-value"><?php echo $siswa['alamat'] ?: '-'; ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">No. Telepon</div>
                            <div class="detail-value"><?php echo $siswa['no_telp'] ?: '-'; ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo $siswa['email'] ?: '-'; ?></div>
                        </div>
                        
                        <h5 style="color: var(--primary-yellow); margin: 30px 0 20px;">
                            <i class="fas fa-users"></i> Data Orang Tua / Wali
                        </h5>
                        
                        <div class="detail-row">
                            <div class="detail-label">Nama Wali</div>
                            <div class="detail-value"><?php echo $siswa['nama_wali'] ?: '-'; ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">No. Telepon Wali</div>
                            <div class="detail-value"><?php echo $siswa['no_telp_wali'] ?: '-'; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistik Kehadiran -->
                <h5 style="margin: 30px 0 20px; color: var(--primary-black);">
                    <i class="fas fa-chart-bar"></i> Statistik Kehadiran
                </h5>
                
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: rgba(40, 167, 69, 0.2); color: #28a745;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-value"><?php echo $total_hadir; ?></div>
                            <div class="stat-label">Hadir</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: rgba(23, 162, 184, 0.2); color: #17a2b8;">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="stat-value"><?php echo $total_izin; ?></div>
                            <div class="stat-label">Izin</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <div class="stat-value"><?php echo $total_sakit; ?></div>
                            <div class="stat-label">Sakit</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: rgba(220, 53, 69, 0.2); color: #dc3545;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-value"><?php echo $total_alfa; ?></div>
                            <div class="stat-label">Alfa</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Pengaturan Akun -->
            <div class="tab-pane fade" id="pengaturan">
                <div class="settings-card">
                    <h5 style="color: var(--primary-yellow); margin-bottom: 25px;">
                        <i class="fas fa-user-edit"></i> Ubah Username
                    </h5>
                    
                    <form action="update_username.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username Saat Ini</label>
                            <input type="text" class="form-control" value="<?php echo $siswa['username']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username Baru</label>
                            <input type="text" class="form-control" name="username_baru" required minlength="4">
                            <small class="text-muted">Minimal 4 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (untuk konfirmasi)</label>
                            <input type="password" class="form-control" name="password_konfirmasi" required>
                        </div>
                        <button type="submit" class="btn btn-yellow">
                            <i class="fas fa-save"></i> Simpan Username Baru
                        </button>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h5 style="color: var(--primary-yellow); margin-bottom: 25px;">
                        <i class="fas fa-key"></i> Ubah Password
                    </h5>
                    
                    <form action="update_password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" class="form-control" name="password_lama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="password_baru" id="password_baru" required minlength="6">
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="password_konfirmasi" id="password_konfirmasi" required>
                        </div>
                        <button type="submit" class="btn btn-yellow">
                            <i class="fas fa-save"></i> Simpan Password Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden File Input for Photo Upload -->
    <form id="uploadFotoForm" enctype="multipart/form-data" style="display: none;">
        <input type="file" id="uploadFoto" name="foto_profil" accept="image/*">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Upload foto profil
        document.getElementById('uploadFoto').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('foto_profil', this.files[0]);
                
                fetch('upload_foto.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Gagal upload foto');
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan saat upload foto');
                });
            }
        });
        
        // Validasi password match
        document.querySelector('form[action="update_password.php"]').addEventListener('submit', function(e) {
            const passwordBaru = document.getElementById('password_baru').value;
            const passwordKonfirmasi = document.getElementById('password_konfirmasi').value;
            
            if (passwordBaru !== passwordKonfirmasi) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
            }
        });
    </script>
</body>
</html>