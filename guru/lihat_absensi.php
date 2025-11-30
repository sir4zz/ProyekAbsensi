<?php
require_once '../config.php';
requireLogin('guru');

$id_guru = $_SESSION['guru_id'];

// Get kelas yang diajar oleh guru ini
$kelas_guru_query = $conn->query("
    SELECT DISTINCT gk.jurusan, gk.tingkat 
    FROM guru_kelas gk
    WHERE gk.id_guru = $id_guru 
    ORDER BY gk.jurusan ASC, gk.tingkat ASC
");

$kelas_list_detail = [];

while ($row = $kelas_guru_query->fetch_assoc()) {
    $jurusan = $conn->real_escape_string($row['jurusan']);
    $tingkat = $conn->real_escape_string($row['tingkat']);
    
    // Get semua rombel
    $rombel_query = $conn->query("
        SELECT DISTINCT kelas 
        FROM siswa 
        WHERE jurusan = '$jurusan' AND kelas LIKE '$tingkat%'
        ORDER BY kelas ASC
    ");
    
    while ($rombel = $rombel_query->fetch_assoc()) {
        $kelas_full = $jurusan . '-' . $rombel['kelas'];
        $kelas_list_detail[$kelas_full] = $jurusan . ' Kelas ' . $rombel['kelas'];
    }
}

// Jika guru tidak punya kelas
$has_kelas = !empty($kelas_list_detail);

if (!$has_kelas) {
    echo "<script>alert('Anda belum ditugaskan mengajar kelas apapun. Hubungi admin!'); window.location.href='dashboard.php';</script>";
    exit;
}

// Filter
$filter_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : array_keys($kelas_list_detail)[0];

// Validasi
if (!isset($kelas_list_detail[$filter_kelas])) {
    $filter_kelas = array_keys($kelas_list_detail)[0];
}

// Parse kelas
$parts = explode('-', $filter_kelas);
$filter_jurusan = $parts[0];
$filter_kelas_siswa = implode('-', array_slice($parts, 1));

// Export Excel
if (isset($_GET['export'])) {
    $export_type = isset($_GET['export_type']) ? $_GET['export_type'] : 'bulanan';
    
    $kelas_export = $_GET['kelas'];
    $parts_exp = explode('-', $kelas_export);
    $jurusan_exp = $parts_exp[0];
    $kelas_exp = implode('-', array_slice($parts_exp, 1));
    
    if ($export_type == 'tahunan') {
        $tahun = $_GET['tahun'];
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Rekap_Tahunan_' . $jurusan_exp . '_' . $kelas_exp . '_' . $tahun . '.xls"');
        
        $sql_siswa = "SELECT id_siswa, nis, nama_siswa, kelas FROM siswa WHERE jurusan='$jurusan_exp' AND kelas='$kelas_exp' ORDER BY nama_siswa";
        $result_siswa = $conn->query($sql_siswa);
        
        $sql_absen = "SELECT s.id_siswa, MONTH(a.tanggal) as bulan,
                      COUNT(CASE WHEN a.status='Hadir' THEN 1 END) as h,
                      COUNT(CASE WHEN a.status='Izin' THEN 1 END) as i,
                      COUNT(CASE WHEN a.status='Sakit' THEN 1 END) as s,
                      COUNT(CASE WHEN a.status='Alfa' THEN 1 END) as a
                      FROM siswa s
                      LEFT JOIN absensi_lengkap a ON s.id_siswa=a.id_siswa AND YEAR(a.tanggal)='$tahun'
                      WHERE s.jurusan='$jurusan_exp' AND s.kelas='$kelas_exp'
                      GROUP BY s.id_siswa, MONTH(a.tanggal)";
        $result_absen = $conn->query($sql_absen);
        
        $data = [];
        while($s = $result_siswa->fetch_assoc()) {
            $data[$s['id_siswa']] = [
                'nis' => $s['nis'],
                'nama' => $s['nama_siswa'],
                'kelas' => $s['kelas'],
                'bulan' => array_fill(1,12,['h'=>0,'i'=>0,'s'=>0,'a'=>0])
            ];
        }
        
        while($a = $result_absen->fetch_assoc()) {
            $id = $a['id_siswa'];
            $b = (int)$a['bulan'];
            if(isset($data[$id]) && $b>=1 && $b<=12) {
                $data[$id]['bulan'][$b] = ['h'=>$a['h'],'i'=>$a['i'],'s'=>$a['s'],'a'=>$a['a']];
            }
        }
        
        echo "<table border='1'>";
        echo "<tr><td colspan='53' style='text-align:center;font-weight:bold'>REKAP ABSENSI TAHUNAN</td></tr>";
        echo "<tr><td colspan='53' style='text-align:center'>$jurusan_exp Kelas $kelas_exp - Tahun $tahun</td></tr>";
        echo "<tr><td colspan='53'></td></tr>";
        
        echo "<tr style='background:#FFD700;font-weight:bold;text-align:center'>";
        echo "<td rowspan='2'>No</td><td rowspan='2'>NIS</td><td rowspan='2'>Nama</td><td rowspan='2'>Kelas</td>";
        for($m=1;$m<=12;$m++) echo "<td colspan='4'>" . ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$m] . "</td>";
        echo "<td colspan='4'>Total</td></tr>";
        
        echo "<tr style='background:#FFD700;font-weight:bold;text-align:center'>";
        for($i=0;$i<13;$i++) echo "<td>H</td><td>I</td><td>S</td><td>A</td>";
        echo "</tr>";
        
        $no=1;
        $gt = array_fill(1,12,['h'=>0,'i'=>0,'s'=>0,'a'=>0]);
        $gh=$gi=$gs=$ga=0;
        
        foreach($data as $d) {
            echo "<tr><td>$no</td><td>{$d['nis']}</td><td>{$d['nama']}</td><td>{$d['kelas']}</td>";
            $th=$ti=$ts=$ta=0;
            for($m=1;$m<=12;$m++) {
                $b=$d['bulan'][$m];
                echo "<td>{$b['h']}</td><td>{$b['i']}</td><td>{$b['s']}</td><td>{$b['a']}</td>";
                $th+=$b['h']; $ti+=$b['i']; $ts+=$b['s']; $ta+=$b['a'];
                $gt[$m]['h']+=$b['h']; $gt[$m]['i']+=$b['i']; $gt[$m]['s']+=$b['s']; $gt[$m]['a']+=$b['a'];
            }
            echo "<td><b>$th</b></td><td><b>$ti</b></td><td><b>$ts</b></td><td><b>$ta</b></td></tr>";
            $gh+=$th; $gi+=$ti; $gs+=$ts; $ga+=$ta;
            $no++;
        }
        
        echo "<tr style='background:#FFF3CD;font-weight:bold'><td colspan='4' align='right'>TOTAL:</td>";
        for($m=1;$m<=12;$m++) {
            $g=$gt[$m];
            echo "<td>{$g['h']}</td><td>{$g['i']}</td><td>{$g['s']}</td><td>{$g['a']}</td>";
        }
        echo "<td>$gh</td><td>$gi</td><td>$gs</td><td>$ga</td></tr>";
        echo "</table>";
        exit;
    } else {
        $bulan = $_GET['bulan'];
        list($thn,$bln) = explode('-',$bulan);
        $nb = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Rekap_' . $jurusan_exp . '_' . $kelas_exp . '_' . $nb[(int)$bln] . '_' . $thn . '.xls"');
        
        $sql = "SELECT s.nis, s.nama_siswa, s.kelas,
                COUNT(CASE WHEN a.status='Hadir' THEN 1 END) as h,
                COUNT(CASE WHEN a.status='Izin' THEN 1 END) as i,
                COUNT(CASE WHEN a.status='Sakit' THEN 1 END) as sk,
                COUNT(CASE WHEN a.status='Alfa' THEN 1 END) as a
                FROM siswa s
                LEFT JOIN absensi_lengkap a ON s.id_siswa=a.id_siswa AND DATE_FORMAT(a.tanggal,'%Y-%m')='$bulan'
                WHERE s.jurusan='$jurusan_exp' AND s.kelas='$kelas_exp'
                GROUP BY s.id_siswa ORDER BY s.nama_siswa";
        $result = $conn->query($sql);
        
        echo "<table border='1'>";
        echo "<tr><td colspan='8' style='text-align:center;font-weight:bold'>REKAP ABSENSI SISWA</td></tr>";
        echo "<tr><td colspan='8' style='text-align:center'>$jurusan_exp Kelas $kelas_exp - {$nb[(int)$bln]} $thn</td></tr>";
        echo "<tr><td colspan='8'></td></tr>";
        echo "<tr style='background:#FFD700;font-weight:bold'><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alfa</th></tr>";
        
        $no=1; $th=$ti=$ts=$ta=0;
        while($r=$result->fetch_assoc()) {
            echo "<tr><td>$no</td><td>{$r['nis']}</td><td>{$r['nama_siswa']}</td><td>{$r['kelas']}</td>";
            echo "<td>{$r['h']}</td><td>{$r['i']}</td><td>{$r['sk']}</td><td>{$r['a']}</td></tr>";
            $th+=$r['h']; $ti+=$r['i']; $ts+=$r['sk']; $ta+=$r['a'];
            $no++;
        }
        
        echo "<tr style='background:#FFF3CD;font-weight:bold'><td colspan='4' align='right'>TOTAL:</td>";
        echo "<td>$th</td><td>$ti</td><td>$ts</td><td>$ta</td></tr>";
        echo "</table>";
        exit;
    }
}

// Get Absensi Data
$where = "WHERE a.tanggal = '$filter_date' AND s.jurusan = '$filter_jurusan' AND s.kelas = '$filter_kelas_siswa'";
$sql = "SELECT a.*, s.nama_siswa, s.nis, s.kelas, s.jurusan
        FROM absensi_lengkap a
        JOIN siswa s ON a.id_siswa = s.id_siswa
        $where
        ORDER BY s.nama_siswa ASC";
$absensi = $conn->query($sql);
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
        body { background: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%); color: white; padding: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 15px; }
        .logo-icon { width: 50px; height: 50px; background: var(--primary-yellow); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .logo-icon i { font-size: 25px; color: var(--primary-black); }
        .btn-back { background: var(--primary-yellow); color: var(--primary-black); border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; }
        .content-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); margin-top: 20px; }
        .nav-pills .nav-link { color: var(--primary-black); border-radius: 10px; padding: 10px 20px; margin-right: 10px; }
        .nav-pills .nav-link.active { background: var(--primary-yellow); color: var(--primary-black); }
        .btn-success { background: #28a745; border: none; }
        .btn-primary { background: var(--primary-yellow); color: var(--primary-black); border: none; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <h5 style="margin: 0; color: var(--primary-yellow);">Lihat Absensi</h5>
                        <small style="color: #bbb;">Panel Guru - <?php echo $_SESSION['guru_nama']; ?></small>
                    </div>
                </div>
                <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </div>
    
    <div class="container" style="padding: 30px 15px;">
        <ul class="nav nav-pills mb-3">
            <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link active" href="lihat_absensi.php"><i class="fas fa-clipboard-list"></i> Lihat Absensi</a></li>
        </ul>
        
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 style="color: var(--primary-black); margin: 0;"><i class="fas fa-list-alt"></i> Data Absensi Siswa</h4>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalExport">
                    <i class="fas fa-file-excel"></i> Export Rekap Absensi
                </button>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-5">
                    <label class="form-label">Tanggal</label>
                    <input type="date" id="filter_tanggal" class="form-control" value="<?php echo $filter_date; ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Kelas</label>
                    <select id="filter_kelas" class="form-control">
                        <?php foreach ($kelas_list_detail as $k => $v): ?>
                            <option value="<?php echo $k; ?>" <?php echo $filter_kelas==$k?'selected':''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button onclick="applyFilter()" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead style="background: rgba(255, 215, 0, 0.2);">
                        <tr>
                            <th>No</th><th>NIS</th><th>Nama Siswa</th><th>Jurusan</th><th>Kelas</th>
                            <th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th><th>Keterangan</th><th>Foto</th><th>Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($absensi->num_rows > 0):
                            while($row = $absensi->fetch_assoc()): 
                                $badge = ['Hadir'=>'bg-success','Izin'=>'bg-info','Sakit'=>'bg-warning','Alfa'=>'bg-danger'][$row['status']] ?? 'bg-secondary';
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nis']; ?></td>
                                <td><?php echo $row['nama_siswa']; ?></td>
                                <td><span class="badge bg-secondary"><?php echo $row['jurusan']; ?></span></td>
                                <td><span class="badge bg-primary"><?php echo $row['kelas']; ?></span></td>
                                <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                                <td><?php echo $row['jam_pulang'] && $row['status']=='Hadir' ? date('H:i', strtotime($row['jam_pulang'])) : '-'; ?></td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo $row['status']; ?></span></td>
                                <td><?php echo $row['keterangan'] ?: '-'; ?></td>
                                <td>
                                    <?php if($row['foto_masuk']): ?>
                                    <button class="btn btn-sm btn-info" onclick="showPhoto('<?php echo UPLOAD_URL.$row['foto_masuk']; ?>','<?php echo $row['foto_pulang']?UPLOAD_URL.$row['foto_pulang']:''; ?>')">
                                        <i class="fas fa-image"></i> Lihat
                                    </button>
                                    <?php else: echo '-'; endif; ?>
                                </td>
                                <td>
                                    <?php if($row['lokasi']): ?>
                                    <button class="btn btn-sm btn-warning" onclick="showMap('<?php echo $row['lokasi']; ?>')">
                                        <i class="fas fa-map-marker-alt"></i> Map
                                    </button>
                                    <?php else: echo '-'; endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="11" class="text-center">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExport" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: #28a745; color: white;">
                    <h5 class="modal-title"><i class="fas fa-file-excel"></i> Export Rekap Absensi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Export rekap absensi. Pilih jenis: <strong>Bulanan</strong> atau <strong>Tahunan</strong>
                    </div>
                    <form id="formExport">
                        <div class="mb-3">
                            <label class="form-label"><strong>Jenis Rekap</strong></label>
                            <select id="jenis_rekap" class="form-control" onchange="toggleType()" required>
                                <option value="bulanan">Rekap Bulanan (1 Bulan)</option>
                                <option value="tahunan">Rekap Tahunan (Semua Bulan)</option>
                            </select>
                        </div>
                        <div id="inp_bulanan">
                            <div class="mb-3">
                                <label class="form-label"><strong>Pilih Bulan</strong></label>
                                <input type="month" id="bulan_export" class="form-control" value="<?php echo $filter_bulan; ?>">
                            </div>
                        </div>
                        <div id="inp_tahunan" style="display:none">
                            <div class="mb-3">
                                <label class="form-label"><strong>Pilih Tahun</strong></label>
                                <select id="tahun_export" class="form-control">
                                    <?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $y==date('Y')?'selected':''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Kelas</strong></label>
                            <select id="kelas_export" class="form-control" required>
                                <?php foreach ($kelas_list_detail as $k => $v): ?>
                                <option value="<?php echo $k; ?>" <?php echo $filter_kelas==$k?'selected':''; ?>><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="exportExcel()">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>

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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable({language: {url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'}});
        });
        
        function applyFilter() {
            window.location.href = 'lihat_absensi.php?tanggal=' + document.getElementById('filter_tanggal').value + '&kelas=' + document.getElementById('filter_kelas').value;
        }
        
        function toggleType() {
            var j = document.getElementById('jenis_rekap').value;
            document.getElementById('inp_bulanan').style.display = j=='tahunan' ? 'none' : 'block';
            document.getElementById('inp_tahunan').style.display = j=='tahunan' ? 'block' : 'none';
        }
        
        function exportExcel() {
            var j = document.getElementById('jenis_rekap').value;
            var k = document.getElementById('kelas_export').value;
            var url = 'lihat_absensi.php?export=1&export_type=' + j + '&kelas=' + k;
            
            if(j=='tahunan') {
                var t = document.getElementById('tahun_export').value;
                if(!t) { alert('Pilih tahun!'); return; }
                url += '&tahun=' + t;
            } else {
                var b = document.getElementById('bulan_export').value;
                if(!b) { alert('Pilih bulan!'); return; }
                url += '&bulan=' + b;
            }
            
            window.location.href = url;
            setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('modalExport')).hide(), 500);
        }
        
        function showPhoto(m, p) {
            var c = '<div class="row"><div class="col-md-6"><h6>Foto Masuk</h6><img src="' + m + '" class="img-fluid rounded"></div>';
            if(p) c += '<div class="col-md-6"><h6>Foto Pulang</h6><img src="' + p + '" class="img-fluid rounded"></div>';
            c += '</div>';
            document.getElementById('photoContent').innerHTML = c;
            new bootstrap.Modal(document.getElementById('modalPhoto')).show();
        }
        
        function showMap(l) {
            var [lat,lng] = l.split(',');
            window.open('https://www.google.com/maps?q=' + lat + ',' + lng, '_blank');
        }
    </script>
</body>
</html>