<?php
require_once '../config.php';
requireLogin('guru');

$guru = $_SESSION['guru_nama'];

// Filter kelas
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Get statistics
$today = date('Y-m-d');

// Build WHERE clause
$where_kelas = $filter_kelas ? "AND s.kelas = '$filter_kelas'" : "";

$total_siswa = $conn->query("SELECT COUNT(*) as total FROM siswa" . ($filter_kelas ? " WHERE kelas = '$filter_kelas'" : ""))->fetch_assoc()['total'];

$hadir_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM absensi_lengkap a 
    JOIN siswa s ON a.id_siswa = s.id_siswa 
    WHERE a.tanggal = '$today' AND a.status = 'Hadir' $where_kelas
")->fetch_assoc()['total'];

$izin_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM absensi_lengkap a 
    JOIN siswa s ON a.id_siswa = s.id_siswa 
    WHERE a.tanggal = '$today' AND a.status = 'Izin' $where_kelas
")->fetch_assoc()['total'];

$sakit_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM absensi_lengkap a 
    JOIN siswa s ON a.id_siswa = s.id_siswa 
    WHERE a.tanggal = '$today' AND a.status = 'Sakit' $where_kelas
")->fetch_assoc()['total'];

$alfa_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM absensi_lengkap a 
    JOIN siswa s ON a.id_siswa = s.id_siswa 
    WHERE a.tanggal = '$today' AND a.status = 'Alfa' $where_kelas
")->fetch_assoc()['total'];

$belum_absen = $total_siswa - ($hadir_today + $izin_today + $sakit_today + $alfa_today);

// Get kelas list
$kelas_list = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");

// Latest absensi
$latest = $conn->query("
    SELECT a.*, s.nama_siswa, s.kelas 
    FROM absensi_lengkap a
    JOIN siswa s ON a.id_siswa = s.id_siswa
    WHERE a.tanggal = '$today' $where_kelas
    ORDER BY a.jam_masuk DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - <?php echo SITE_NAME; ?></title>
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
        
        .btn-logout {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
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
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .nav-pills .nav-link {
            color: var(--primary-black);
            border-radius: 10px;
            padding: 10px 20px;
            margin-right: 10px;
        }
        
        .nav-pills .nav-link.active {
            background: var(--primary-yellow);
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
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; color: var(--primary-yellow);">Dashboard Guru</h5>
                        <small style="color: #bbb;"><?php echo $guru; ?></small>
                    </div>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <div class="container" style="padding: 30px 15px;">
        <!-- Filter Kelas -->
        <div class="content-card mb-4">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label class="form-label" style="font-weight: 600; color: var(--primary-black);">
                        <i class="fas fa-filter"></i> Filter Kelas
                    </label>
                    <select id="filterKelas" class="form-control">
                        <option value="">Semua Kelas</option>
                        <?php while($k = $kelas_list->fetch_assoc()): ?>
                            <option value="<?php echo $k['kelas']; ?>" <?php echo $filter_kelas == $k['kelas'] ? 'selected' : ''; ?>>
                                Kelas <?php echo $k['kelas']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button onclick="applyFilter()" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($filter_kelas): ?>
                        <div class="alert alert-info mb-0" style="padding: 10px;">
                            <i class="fas fa-info-circle"></i> 
                            Menampilkan data kelas <strong><?php echo $filter_kelas; ?></strong>
                            <a href="dashboard.php" class="btn btn-sm btn-warning ms-2">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(0, 123, 255, 0.2); color: #007bff;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_siswa; ?></div>
                    <div class="stat-label">Total Siswa</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(40, 167, 69, 0.2); color: #28a745;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $hadir_today; ?></div>
                    <div class="stat-label">Hadir</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(23, 162, 184, 0.2); color: #17a2b8;">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $izin_today; ?></div>
                    <div class="stat-label">Izin</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="stat-value"><?php echo $sakit_today; ?></div>
                    <div class="stat-label">Sakit</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(220, 53, 69, 0.2); color: #dc3545;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $alfa_today; ?></div>
                    <div class="stat-label">Alfa</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(108, 117, 125, 0.2); color: #6c757d;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $belum_absen; ?></div>
                    <div class="stat-label">Belum Absen</div>
                </div>
            </div>
        </div>
        
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="lihat_absensi.php">
                    <i class="fas fa-clipboard-list"></i> Lihat Absensi
                </a>
            </li>
        </ul>
        
        <div class="content-card">
            <h4 style="color: var(--primary-black); margin-bottom: 20px;">
                <i class="fas fa-clock"></i> Absensi Terbaru Hari Ini
                <?php if ($filter_kelas): ?>
                    <span class="badge" style="background: var(--primary-yellow); color: var(--primary-black);">
                        Kelas <?php echo $filter_kelas; ?>
                    </span>
                <?php endif; ?>
            </h4>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: rgba(255, 215, 0, 0.2);">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($latest->num_rows > 0):
                            $no = 1;
                            while($row = $latest->fetch_assoc()): 
                                $badge_class = '';
                                switch($row['status']) {
                                    case 'Hadir': $badge_class = 'bg-success'; break;
                                    case 'Izin': $badge_class = 'bg-info'; break;
                                    case 'Sakit': $badge_class = 'bg-warning'; break;
                                    case 'Alfa': $badge_class = 'bg-danger'; break;
                                }
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nama_siswa']; ?></td>
                                <td><span class="badge bg-primary"><?php echo $row['kelas']; ?></span></td>
                                <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                                <td>
                                    <?php 
                                    if ($row['status'] == 'Hadir') {
                                        echo $row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) : '-';
                                    } else {
                                        echo '<span class="text-muted" style="font-size: 0.85rem;">-</span>';
                                    }
                                    ?>
                                </td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span></td>
                                <td><?php echo $row['keterangan'] ? '<small>' . $row['keterangan'] . '</small>' : '-'; ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada absensi hari ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilter() {
            const kelas = document.getElementById('filterKelas').value;
            if (kelas) {
                window.location.href = 'dashboard.php?kelas=' + kelas;
            } else {
                window.location.href = 'dashboard.php';
            }
        }
    </script>
</body>
</html>