<?php
require_once '../config.php';
requireLogin('guru');

$id_guru = $_SESSION['guru_id'];
$guru = $_SESSION['guru_nama'];

// Get kelas yang diajar oleh guru ini (Jurusan + Tingkat)
$kelas_guru_query = $conn->query("
    SELECT DISTINCT jurusan, tingkat 
    FROM guru_kelas 
    WHERE id_guru = $id_guru 
    ORDER BY jurusan ASC, tingkat ASC
");

$kelas_guru = [];
$kelas_guru_display = [];
while ($row = $kelas_guru_query->fetch_assoc()) {
    $kelas_guru[] = $row['jurusan'] . '-' . $row['tingkat'];
    $kelas_guru_display[] = $row['jurusan'] . ' Kelas ' . $row['tingkat'];
}

// Jika guru tidak punya kelas
$has_kelas = !empty($kelas_guru);

// Build WHERE clause untuk kelas yang diajar (Jurusan + Tingkat dengan LIKE)
$kelas_conditions = [];
if ($has_kelas) {
    foreach ($kelas_guru as $kelas_combo) {
        $parts = explode('-', $kelas_combo);
        if (count($parts) == 2) {
            $jurusan = $conn->real_escape_string($parts[0]);
            $tingkat = $conn->real_escape_string($parts[1]);
            // LIKE untuk match X, X-1, X-2, X-TKJ-1, dll
            $kelas_conditions[] = "(s.jurusan = '$jurusan' AND s.kelas LIKE '$tingkat%')";
        }
    }
}

$kelas_condition = "";
if (!empty($kelas_conditions)) {
    $kelas_condition = "AND (" . implode(" OR ", $kelas_conditions) . ")";
}

// Get statistics - HANYA untuk kelas yang diajar
$today = date('Y-m-d');

$total_siswa = 0;
$hadir_today = 0;
$izin_today = 0;
$sakit_today = 0;
$alfa_today = 0;

if ($has_kelas && !empty($kelas_conditions)) {
    // Hitung total siswa berdasarkan kelas yang diajar
    $where_siswa = "WHERE (" . implode(" OR ", $kelas_conditions) . ")";
    
    $result_total = $conn->query("
        SELECT COUNT(*) as total 
        FROM siswa s
        $where_siswa
    ");
    if ($result_total) {
        $total_siswa = $result_total->fetch_assoc()['total'];
    }

    // Hitung statistik absensi
    $result_hadir = $conn->query("
        SELECT COUNT(*) as total 
        FROM absensi_lengkap a 
        JOIN siswa s ON a.id_siswa = s.id_siswa 
        WHERE a.tanggal = '$today' AND a.status = 'Hadir' $kelas_condition
    ");
    if ($result_hadir) {
        $hadir_today = $result_hadir->fetch_assoc()['total'];
    }

    $result_izin = $conn->query("
        SELECT COUNT(*) as total 
        FROM absensi_lengkap a 
        JOIN siswa s ON a.id_siswa = s.id_siswa 
        WHERE a.tanggal = '$today' AND a.status = 'Izin' $kelas_condition
    ");
    if ($result_izin) {
        $izin_today = $result_izin->fetch_assoc()['total'];
    }

    $result_sakit = $conn->query("
        SELECT COUNT(*) as total 
        FROM absensi_lengkap a 
        JOIN siswa s ON a.id_siswa = s.id_siswa 
        WHERE a.tanggal = '$today' AND a.status = 'Sakit' $kelas_condition
    ");
    if ($result_sakit) {
        $sakit_today = $result_sakit->fetch_assoc()['total'];
    }

    $result_alfa = $conn->query("
        SELECT COUNT(*) as total 
        FROM absensi_lengkap a 
        JOIN siswa s ON a.id_siswa = s.id_siswa 
        WHERE a.tanggal = '$today' AND a.status = 'Alfa' $kelas_condition
    ");
    if ($result_alfa) {
        $alfa_today = $result_alfa->fetch_assoc()['total'];
    }
}

$belum_absen = $total_siswa - ($hadir_today + $izin_today + $sakit_today + $alfa_today);

// Latest absensi - HANYA dari kelas yang diajar
$latest = null;
if ($has_kelas) {
    $latest = $conn->query("
        SELECT a.*, s.nama_siswa, s.kelas, s.jurusan
        FROM absensi_lengkap a
        JOIN siswa s ON a.id_siswa = s.id_siswa
        WHERE a.tanggal = '$today' $kelas_condition
        ORDER BY a.jam_masuk DESC
        LIMIT 10
    ");
}

// Statistik per kelas yang diajar (Jurusan + Tingkat dengan LIKE)
$stats_per_kelas = [];
if ($has_kelas) {
    foreach ($kelas_guru as $kelas_combo) {
        $parts = explode('-', $kelas_combo);
        if (count($parts) == 2) {
            $jurusan = $conn->real_escape_string($parts[0]);
            $tingkat = $conn->real_escape_string($parts[1]);
            
            // LIKE untuk match semua variasi kelas (X, X-1, X-2, dst)
            $query_total = "
                SELECT COUNT(*) as total 
                FROM siswa 
                WHERE jurusan = '$jurusan' AND kelas LIKE '$tingkat%'
            ";
            $result_total = $conn->query($query_total);
            $total = $result_total ? $result_total->fetch_assoc()['total'] : 0;
            
            $query_hadir = "
                SELECT COUNT(*) as total 
                FROM absensi_lengkap a 
                JOIN siswa s ON a.id_siswa = s.id_siswa 
                WHERE a.tanggal = '$today' 
                AND a.status = 'Hadir' 
                AND s.jurusan = '$jurusan' 
                AND s.kelas LIKE '$tingkat%'
            ";
            $result_hadir = $conn->query($query_hadir);
            $hadir = $result_hadir ? $result_hadir->fetch_assoc()['total'] : 0;
            
            $display_name = $jurusan . ' Kelas ' . $tingkat;
            $stats_per_kelas[$display_name] = [
                'total' => $total,
                'hadir' => $hadir,
                'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0
            ];
        }
    }
}
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
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: var(--dark-yellow);
            transform: translateY(-2px);
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
            transition: all 0.3s ease;
        }
        
        .nav-pills .nav-link:hover {
            background: rgba(255, 215, 0, 0.2);
        }
        
        .nav-pills .nav-link.active {
            background: var(--primary-yellow);
            color: var(--primary-black);
        }
        
        .kelas-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .kelas-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .kelas-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .kelas-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .progress-custom {
            height: 8px;
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-bar-custom {
            height: 100%;
            background: var(--primary-yellow);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .alert-warning-custom {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 12px;
        }
        
        .table-latest {
            margin-top: 10px;
        }
        
        .table-latest th {
            background: rgba(255, 215, 0, 0.2);
            font-weight: 600;
            color: var(--primary-black);
        }
        
        .badge-custom {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
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
        
        <?php if (!$has_kelas): ?>
            <!-- Jika guru belum punya kelas -->
            <div class="alert alert-warning-custom text-center" style="padding: 40px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 20px;"></i>
                <h4>Anda Belum Ditugaskan Mengajar Kelas Apapun</h4>
                <p class="mb-0">Silakan hubungi administrator untuk mendapatkan penugasan kelas.</p>
            </div>
        <?php else: ?>
            
            <!-- Info Kelas yang Diajar -->
            <div class="content-card mb-4">
                <h5 style="color: var(--primary-black); margin-bottom: 20px;">
                    <i class="fas fa-school"></i> Kelas yang Anda Ajar
                </h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($kelas_guru_display as $kelas_display): ?>
                        <span class="badge bg-primary badge-custom" style="font-size: 1rem;">
                            <i class="fas fa-users"></i> <?php echo $kelas_display; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Statistik Kehadiran Hari Ini -->
            <h5 style="color: var(--primary-black); margin-bottom: 20px;">
                <i class="fas fa-chart-bar"></i> Statistik Kehadiran Hari Ini
                <small class="text-muted" style="font-size: 0.85rem;">
                    (<?php echo formatTanggal($today); ?>)
                </small>
            </h5>
            
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
            
            <!-- Statistik Per Kelas -->
            <div class="content-card mb-4">
                <h5 style="color: var(--primary-black); margin-bottom: 20px;">
                    <i class="fas fa-chart-pie"></i> Kehadiran Per Kelas
                </h5>
                <div class="row">
                    <?php foreach ($stats_per_kelas as $kelas => $stat): ?>
                        <div class="col-md-4 mb-3">
                            <div class="kelas-card">
                                <div class="kelas-name">
                                    <i class="fas fa-door-open"></i> <?php echo $kelas; ?>
                                </div>
                                <div class="kelas-stats">
                                    <div>
                                        <small>Hadir: <?php echo $stat['hadir']; ?> / <?php echo $stat['total']; ?> siswa</small>
                                    </div>
                                    <div style="font-size: 1.5rem; font-weight: bold;">
                                        <?php echo $stat['persentase']; ?>%
                                    </div>
                                </div>
                                <div class="progress-custom">
                                    <div class="progress-bar-custom" style="width: <?php echo $stat['persentase']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Absensi Terbaru -->
            <div class="content-card">
                <h5 style="color: var(--primary-black); margin-bottom: 20px;">
                    <i class="fas fa-clock"></i> Absensi Terbaru Hari Ini
                </h5>
                
                <?php if ($latest && $latest->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-latest">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Nama Siswa</th>
                                    <th>Jurusan</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $latest->fetch_assoc()): 
                                    $badge_class = '';
                                    switch($row['status']) {
                                        case 'Hadir': $badge_class = 'bg-success'; break;
                                        case 'Izin': $badge_class = 'bg-info'; break;
                                        case 'Sakit': $badge_class = 'bg-warning'; break;
                                        case 'Alfa': $badge_class = 'bg-danger'; break;
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-clock text-primary"></i>
                                            <?php echo date('H:i', strtotime($row['jam_masuk'])); ?>
                                        </td>
                                        <td><strong><?php echo $row['nama_siswa']; ?></strong></td>
                                        <td><span class="badge bg-secondary"><?php echo $row['jurusan'] ?: '-'; ?></span></td>
                                        <td><span class="badge bg-primary"><?php echo $row['kelas']; ?></span></td>
                                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <?php if ($row['keterangan']): ?>
                                                <small class="text-muted"><?php echo $row['keterangan']; ?></small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i>
                        Belum ada absensi hari ini untuk kelas yang Anda ajar.
                    </div>
                <?php endif; ?>
            </div>
            
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>