<?php
require_once '../config.php';
requireLogin('siswa');

$id_siswa = $_SESSION['siswa_id'];
$siswa = $conn->query("SELECT * FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();

// Get riwayat absensi
$riwayat = $conn->query("SELECT * FROM absensi_lengkap WHERE id_siswa = $id_siswa ORDER BY tanggal DESC, jam_masuk DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi - <?php echo SITE_NAME; ?></title>
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
        
        .btn-back {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .table-responsive {
            margin-top: 20px;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn-view {
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; color: var(--primary-yellow);">Riwayat Absensi</h5>
                        <small style="color: #bbb;"><?php echo $siswa['nama_siswa']; ?></small>
                    </div>
                </div>
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    
    <div class="container" style="padding: 30px 15px;">
        <div class="content-card">
            <h4 style="color: var(--primary-black); margin-bottom: 20px;">
                <i class="fas fa-clipboard-list"></i> Daftar Riwayat Absensi
            </h4>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: rgba(255, 215, 0, 0.2);">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Foto</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($riwayat->num_rows > 0):
                            $no = 1;
                            while($row = $riwayat->fetch_assoc()): 
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
                                <td><?php echo formatTanggal($row['tanggal']); ?></td>
                                <td>
                                    <?php if ($row['jam_masuk']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('H:i', strtotime($row['jam_masuk'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($row['status'] == 'Hadir') {
                                        if ($row['jam_pulang']) {
                                            echo '<span class="badge bg-info"><i class="fas fa-clock"></i> ' . date('H:i', strtotime($row['jam_pulang'])) . '</span>';
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted" style="font-size: 0.85rem;">Tidak ada</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['foto_masuk']): ?>
                                        <button class="btn btn-sm btn-primary btn-view" onclick="viewPhoto('<?php echo UPLOAD_URL . $row['foto_masuk']; ?>', '<?php echo $row['foto_pulang'] ? UPLOAD_URL . $row['foto_pulang'] : ''; ?>')">
                                            <i class="fas fa-image"></i> Lihat
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['lokasi']): ?>
                                        <button class="btn btn-sm btn-warning btn-view" onclick="viewMap('<?php echo $row['lokasi']; ?>')">
                                            <i class="fas fa-map-marker-alt"></i> Map
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['keterangan']): ?>
                                        <small class="text-muted"><?php echo $row['keterangan']; ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div style="padding: 40px;">
                                        <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="mt-3" style="color: #666;">Belum ada riwayat absensi</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Photo -->
    <div class="modal fade" id="modalPhoto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary-yellow); color: var(--primary-black);">
                    <h5 class="modal-title">Foto Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="photoContent"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewPhoto(fotoMasuk, fotoPulang) {
            let content = '<div class="row">';
            content += '<div class="col-md-6"><h6>Foto Masuk</h6><img src="' + fotoMasuk + '" class="img-fluid rounded" alt="Foto Masuk"></div>';
            
            if (fotoPulang) {
                content += '<div class="col-md-6"><h6>Foto Pulang</h6><img src="' + fotoPulang + '" class="img-fluid rounded" alt="Foto Pulang"></div>';
            }
            content += '</div>';
            
            document.getElementById('photoContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('modalPhoto')).show();
        }
        
        function viewMap(lokasi) {
            const [lat, lng] = lokasi.split(',');
            const mapUrl = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(mapUrl, '_blank');
        }
    </script>
</body>
</html>