<?php
require_once '../config.php';
requireLogin('guru');

$id_guru = $_SESSION['guru_id'];

// Get kelas yang diajar oleh guru ini (Jurusan + Tingkat)
$kelas_guru_query = $conn->query("
    SELECT DISTINCT jurusan, tingkat 
    FROM guru_kelas 
    WHERE id_guru = $id_guru 
    ORDER BY jurusan ASC, tingkat ASC
");

$kelas_guru = [];
while ($row = $kelas_guru_query->fetch_assoc()) {
    $kelas_combo = $row['jurusan'] . '-' . $row['tingkat'];
    $kelas_guru[$kelas_combo] = $row['jurusan'] . ' Kelas ' . $row['tingkat'];
}

// Jika guru tidak punya kelas, redirect dengan pesan
if (empty($kelas_guru)) {
    echo "<script>alert('Anda belum ditugaskan mengajar kelas apapun. Hubungi admin!'); window.location.href='dashboard.php';</script>";
    exit;
}

// Filter
$filter_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m'); // Filter bulan untuk export
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : array_keys($kelas_guru)[0]; // Default kelas pertama

// Validasi: pastikan guru hanya akses kelas yang dia ajar
if (!isset($kelas_guru[$filter_kelas])) {
    $filter_kelas = array_keys($kelas_guru)[0];
}

// Parse jurusan dan tingkat dari filter
$parts = explode('-', $filter_kelas);
$filter_jurusan = $parts[0] ?? '';
$filter_tingkat = $parts[1] ?? '';

// Build Query dengan LIKE untuk match X, X-1, X-2, dst
$where = "WHERE a.tanggal = '$filter_date' AND s.jurusan = '$filter_jurusan' AND s.kelas LIKE '$filter_tingkat%'";

// Get Absensi Data
$sql = "SELECT a.*, s.nama_siswa, s.nis, s.kelas, s.jurusan
        FROM absensi_lengkap a
        JOIN siswa s ON a.id_siswa = s.id_siswa
        $where
        ORDER BY s.nama_siswa ASC";
$absensi = $conn->query($sql);

// Export to Excel - IMPROVED VERSION
if (isset($_GET['export'])) {
    // Get bulan & tahun dari filter
    $bulan_export = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
    $bulan_parts = explode('-', $bulan_export);
    $tahun = $bulan_parts[0];
    $bulan = $bulan_parts[1];
    
    // Nama bulan Indonesia
    $nama_bulan = array(
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    );
    
    $filename = "Rekap_Absensi_{$filter_jurusan}_Kelas_{$filter_tingkat}_{$nama_bulan[$bulan]}_{$tahun}.xls";
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Query untuk rekap per siswa
    $sql_rekap = "
        SELECT 
            s.nis,
            s.nama_siswa,
            s.jurusan,
            s.kelas,
            COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as total_hadir,
            COUNT(CASE WHEN a.status = 'Izin' THEN 1 END) as total_izin,
            COUNT(CASE WHEN a.status = 'Sakit' THEN 1 END) as total_sakit,
            COUNT(CASE WHEN a.status = 'Alfa' THEN 1 END) as total_alfa,
            COUNT(a.id_absensi) as total_absensi
        FROM siswa s
        LEFT JOIN absensi_lengkap a ON s.id_siswa = a.id_siswa 
            AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$bulan_export'
        WHERE s.jurusan = '$filter_jurusan' 
        AND s.kelas LIKE '$filter_tingkat%'
        GROUP BY s.id_siswa
        ORDER BY s.nama_siswa ASC
    ";
    
    $result_rekap = $conn->query($sql_rekap);
    
    // Header Excel
    echo "<table border='1'>";
    echo "<tr>";
    echo "<td colspan='9' style='text-align: center; font-size: 16px; font-weight: bold;'>";
    echo "REKAP ABSENSI SISWA";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan='9' style='text-align: center;'>";
    echo "{$filter_jurusan} Kelas {$filter_tingkat} - {$nama_bulan[$bulan]} {$tahun}";
    echo "</td>";
    echo "</tr>";
    echo "<tr><td colspan='9'></td></tr>"; // Baris kosong
    
    // Header tabel
    echo "<tr style='background-color: #FFD700; font-weight: bold;'>";
    echo "<th>No</th>";
    echo "<th>NIS</th>";
    echo "<th>Nama Siswa</th>";
    echo "<th>Jurusan</th>";
    echo "<th>Kelas</th>";
    echo "<th>Hadir</th>";
    echo "<th>Izin</th>";
    echo "<th>Sakit</th>";
    echo "<th>Alfa</th>";
    echo "</tr>";
    
    // Data siswa
    $no = 1;
    $grand_hadir = 0;
    $grand_izin = 0;
    $grand_sakit = 0;
    $grand_alfa = 0;
    
    while($row = $result_rekap->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . $row['nis'] . "</td>";
        echo "<td>" . $row['nama_siswa'] . "</td>";
        echo "<td>" . $row['jurusan'] . "</td>";
        echo "<td>" . $row['kelas'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['total_hadir'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['total_izin'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['total_sakit'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['total_alfa'] . "</td>";
        echo "</tr>";
        
        $grand_hadir += $row['total_hadir'];
        $grand_izin += $row['total_izin'];
        $grand_sakit += $row['total_sakit'];
        $grand_alfa += $row['total_alfa'];
    }
    
    // Total keseluruhan
    echo "<tr style='background-color: #FFF3CD; font-weight: bold;'>";
    echo "<td colspan='5' style='text-align: right;'>TOTAL KESELURUHAN:</td>";
    echo "<td style='text-align: center;'>$grand_hadir</td>";
    echo "<td style='text-align: center;'>$grand_izin</td>";
    echo "<td style='text-align: center;'>$grand_sakit</td>";
    echo "<td style='text-align: center;'>$grand_alfa</td>";
    echo "</tr>";
    
    echo "</table>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Absensi - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
        
        .btn-success {
            background: #28a745;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; color: var(--primary-yellow);">Lihat Absensi</h5>
                        <small style="color: #bbb;">Panel Guru - <?php echo $_SESSION['guru_nama']; ?></small>
                    </div>
                </div>
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    
    <div class="container" style="padding: 30px 15px;">
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="lihat_absensi.php">
                    <i class="fas fa-clipboard-list"></i> Lihat Absensi
                </a>
            </li>
        </ul>
        
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 style="color: var(--primary-black); margin: 0;">
                    <i class="fas fa-list-alt"></i> Data Absensi Siswa
                </h4>
                <div>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalExport">
                        <i class="fas fa-file-excel"></i> Export Rekap Bulanan
                    </button>
                </div>
            </div>
            
            <?php if (count($kelas_guru) > 1): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Info:</strong> Anda mengajar <?php echo count($kelas_guru); ?> kelas. 
                Pilih kelas di bawah untuk melihat data absensi.
            </div>
            <?php endif; ?>
            
            <!-- Filter -->
            <div class="row mb-4">
                <div class="col-md-5">
                    <label class="form-label">Tanggal</label>
                    <input type="date" id="filter_tanggal" class="form-control" value="<?php echo $filter_date; ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Kelas yang Anda Ajar</label>
                    <select id="filter_kelas" class="form-control">
                        <?php foreach ($kelas_guru as $kelas_combo => $kelas_display): ?>
                            <option value="<?php echo $kelas_combo; ?>" <?php echo $filter_kelas == $kelas_combo ? 'selected' : ''; ?>>
                                <?php echo $kelas_display; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button onclick="applyFilter()" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead style="background: rgba(255, 215, 0, 0.2);">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jurusan</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Foto</th>
                            <th>Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($absensi->num_rows > 0):
                            while($row = $absensi->fetch_assoc()): 
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
                                <td><?php echo $row['nis']; ?></td>
                                <td><?php echo $row['nama_siswa']; ?></td>
                                <td><span class="badge bg-secondary"><?php echo $row['jurusan'] ?: '-'; ?></span></td>
                                <td><span class="badge bg-primary"><?php echo $row['kelas']; ?></span></td>
                                <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                                <td>
                                    <?php 
                                    if ($row['status'] == 'Hadir') {
                                        echo $row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) : '-';
                                    } else {
                                        echo '<span class="text-muted" style="font-size: 0.85rem;">Tidak ada</span>';
                                    }
                                    ?>
                                </td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <?php if ($row['keterangan']): ?>
                                        <small class="text-muted"><?php echo $row['keterangan']; ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['foto_masuk']): ?>
                                        <button class="btn btn-sm btn-info" onclick="showPhoto('<?php echo UPLOAD_URL . $row['foto_masuk']; ?>', '<?php echo $row['foto_pulang'] ? UPLOAD_URL . $row['foto_pulang'] : ''; ?>')">
                                            <i class="fas fa-image"></i> Lihat
                                        </button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['lokasi']): ?>
                                        <button class="btn btn-sm btn-warning" onclick="showMap('<?php echo $row['lokasi']; ?>')">
                                            <i class="fas fa-map-marker-alt"></i> Map
                                        </button>
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
                                <td colspan="11" class="text-center">Tidak ada data absensi untuk <?php echo $kelas_guru[$filter_kelas]; ?> pada tanggal <?php echo formatTanggal($filter_date); ?></td>
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

    <!-- Modal Export Rekap Bulanan -->
    <div class="modal fade" id="modalExport" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: #28a745; color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-file-excel"></i> Export Rekap Absensi Bulanan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Export akan menghasilkan rekap absensi <strong>per siswa</strong> untuk bulan yang dipilih.<br>
                        <small>Format: 1 siswa = 1 baris dengan total Hadir, Izin, Sakit, Alfa</small>
                    </div>
                    
                    <form id="formExport">
                        <div class="mb-3">
                            <label class="form-label"><strong>Pilih Bulan & Tahun</strong></label>
                            <input type="month" id="bulan_export" class="form-control" value="<?php echo $filter_bulan; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Kelas</strong></label>
                            <select id="kelas_export" class="form-control" required>
                                <?php foreach ($kelas_guru as $kelas_combo => $kelas_display): ?>
                                    <option value="<?php echo $kelas_combo; ?>" <?php echo $filter_kelas == $kelas_combo ? 'selected' : ''; ?>>
                                        <?php echo $kelas_display; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="exportExcel()">
                        <i class="fas fa-download"></i> Download Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                }
            });
        });
        
        function applyFilter() {
            const tanggal = document.getElementById('filter_tanggal').value;
            const kelas = document.getElementById('filter_kelas').value;
            
            let url = 'lihat_absensi.php?tanggal=' + tanggal + '&kelas=' + kelas;
            window.location.href = url;
        }
        
        function exportExcel() {
            const bulan = document.getElementById('bulan_export').value;
            const kelas = document.getElementById('kelas_export').value;
            
            if (!bulan) {
                alert('Pilih bulan terlebih dahulu!');
                return;
            }
            
            // Build URL export
            let url = 'lihat_absensi.php?export=1&bulan=' + bulan + '&kelas=' + kelas;
            
            // Download
            window.location.href = url;
            
            // Close modal
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalExport')).hide();
            }, 500);
        }
        
        function showPhoto(fotoMasuk, fotoPulang) {
            let content = '<div class="row">';
            content += '<div class="col-md-6"><h6>Foto Masuk</h6><img src="' + fotoMasuk + '" class="img-fluid rounded" alt="Foto Masuk"></div>';
            
            if (fotoPulang) {
                content += '<div class="col-md-6"><h6>Foto Pulang</h6><img src="' + fotoPulang + '" class="img-fluid rounded" alt="Foto Pulang"></div>';
            }
            content += '</div>';
            
            document.getElementById('photoContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('modalPhoto')).show();
        }
        
        function showMap(lokasi) {
            const [lat, lng] = lokasi.split(',');
            const mapUrl = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(mapUrl, '_blank');
        }
    </script>
</body>
</html>