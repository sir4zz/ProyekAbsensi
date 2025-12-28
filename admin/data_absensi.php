<?php
$page_title = 'Data Absensi';
require_once 'includes/header.php';

// Filter
$filter_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$filter_jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Export Excel
if (isset($_GET['export'])) {
    $export_type = isset($_GET['export_type']) ? $_GET['export_type'] : 'harian';
    
    if ($export_type == 'tahunan') {
        // Export Tahunan (Semua bulan)
        $tahun = $_GET['tahun'];
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Rekap_Tahunan_' . $tahun . '.xls"');
        
        $where_tahun = "WHERE YEAR(a.tanggal) = '$tahun'";
        if ($filter_jurusan) $where_tahun .= " AND s.jurusan = '$filter_jurusan'";
        if ($filter_kelas) $where_tahun .= " AND s.kelas = '$filter_kelas'";
        
        // Get siswa
        $sql_siswa = "SELECT id_siswa, nis, nama_siswa, jurusan, kelas FROM siswa";
        $where_siswa = [];
        if ($filter_jurusan) $where_siswa[] = "jurusan = '$filter_jurusan'";
        if ($filter_kelas) $where_siswa[] = "kelas = '$filter_kelas'";
        if ($where_siswa) $sql_siswa .= " WHERE " . implode(" AND ", $where_siswa);
        $sql_siswa .= " ORDER BY jurusan, kelas, nama_siswa";
        
        $result_siswa = $conn->query($sql_siswa);
        
        // Get absensi
        $sql_absen = "SELECT s.id_siswa, MONTH(a.tanggal) as bulan,
                      COUNT(CASE WHEN a.status='Hadir' THEN 1 END) as h,
                      COUNT(CASE WHEN a.status='Izin' THEN 1 END) as i,
                      COUNT(CASE WHEN a.status='Sakit' THEN 1 END) as s,
                      COUNT(CASE WHEN a.status='Alfa' THEN 1 END) as a
                      FROM siswa s
                      LEFT JOIN absensi_lengkap a ON s.id_siswa=a.id_siswa AND YEAR(a.tanggal)='$tahun'";
        if ($where_siswa) $sql_absen .= " WHERE " . implode(" AND ", $where_siswa);
        $sql_absen .= " GROUP BY s.id_siswa, MONTH(a.tanggal)";
        
        $result_absen = $conn->query($sql_absen);
        
        $data = [];
        while($s = $result_siswa->fetch_assoc()) {
            $data[$s['id_siswa']] = [
                'nis' => $s['nis'],
                'nama' => $s['nama_siswa'],
                'jurusan' => $s['jurusan'],
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
        echo "<tr><td colspan='53' style='text-align:center'>Tahun $tahun</td></tr>";
        echo "<tr><td colspan='53'></td></tr>";
        
        echo "<tr style='background:#FFD700;font-weight:bold;text-align:center'>";
        echo "<td rowspan='2'>No</td><td rowspan='2'>NIS</td><td rowspan='2'>Nama</td><td rowspan='2'>Jurusan</td><td rowspan='2'>Kelas</td>";
        for($m=1;$m<=12;$m++) echo "<td colspan='4'>" . ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$m] . "</td>";
        echo "<td colspan='4'>Total</td></tr>";
        
        echo "<tr style='background:#FFD700;font-weight:bold;text-align:center'>";
        for($i=0;$i<13;$i++) echo "<td>H</td><td>I</td><td>S</td><td>A</td>";
        echo "</tr>";
        
        $no=1;
        $gt = array_fill(1,12,['h'=>0,'i'=>0,'s'=>0,'a'=>0]);
        $gh=$gi=$gs=$ga=0;
        
        foreach($data as $d) {
            echo "<tr><td>$no</td><td>{$d['nis']}</td><td>{$d['nama']}</td><td>{$d['jurusan']}</td><td>{$d['kelas']}</td>";
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
        
        echo "<tr style='background:#FFF3CD;font-weight:bold'><td colspan='5' align='right'>TOTAL:</td>";
        for($m=1;$m<=12;$m++) {
            $g=$gt[$m];
            echo "<td>{$g['h']}</td><td>{$g['i']}</td><td>{$g['s']}</td><td>{$g['a']}</td>";
        }
        echo "<td>$gh</td><td>$gi</td><td>$gs</td><td>$ga</td></tr>";
        echo "</table>";
        exit;
        
    } else if ($export_type == 'bulanan') {
        // Export Bulanan (Rekap per siswa)
        $bulan_parts = explode('-', $filter_bulan);
        $tahun = $bulan_parts[0];
        $bulan = $bulan_parts[1];
        
        $nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Rekap_Absensi_' . $nama_bulan[(int)$bulan] . '_' . $tahun . '.xls"');
        
        $where_rekap = "WHERE DATE_FORMAT(a.tanggal, '%Y-%m') = '$filter_bulan'";
        if ($filter_jurusan) $where_rekap .= " AND s.jurusan = '$filter_jurusan'";
        if ($filter_kelas) $where_rekap .= " AND s.kelas = '$filter_kelas'";
        
        $sql_rekap = "
            SELECT 
                s.nis, s.nama_siswa, s.jurusan, s.kelas,
                COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as h,
                COUNT(CASE WHEN a.status = 'Izin' THEN 1 END) as i,
                COUNT(CASE WHEN a.status = 'Sakit' THEN 1 END) as sk,
                COUNT(CASE WHEN a.status = 'Alfa' THEN 1 END) as a
            FROM siswa s
            LEFT JOIN absensi_lengkap a ON s.id_siswa = a.id_siswa 
                AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$filter_bulan'
        ";
        
        if ($filter_jurusan || $filter_kelas) {
            $sql_rekap .= " WHERE 1=1";
            if ($filter_jurusan) $sql_rekap .= " AND s.jurusan = '$filter_jurusan'";
            if ($filter_kelas) $sql_rekap .= " AND s.kelas = '$filter_kelas'";
        }
        
        $sql_rekap .= " GROUP BY s.id_siswa ORDER BY s.jurusan, s.kelas, s.nama_siswa";
        
        $result = $conn->query($sql_rekap);
        
        echo "<table border='1'>";
        echo "<tr><td colspan='8' style='text-align:center;font-weight:bold'>REKAP ABSENSI BULANAN</td></tr>";
        echo "<tr><td colspan='8' style='text-align:center'>{$nama_bulan[(int)$bulan]} $tahun</td></tr>";
        echo "<tr><td colspan='8'></td></tr>";
        echo "<tr style='background:#FFD700;font-weight:bold'>";
        echo "<th>No</th><th>NIS</th><th>Nama</th><th>Jurusan</th><th>Kelas</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alfa</th></tr>";
        
        $no=1; $th=$ti=$ts=$ta=0;
        while($r=$result->fetch_assoc()) {
            echo "<tr><td>$no</td><td>{$r['nis']}</td><td>{$r['nama_siswa']}</td><td>{$r['jurusan']}</td><td>{$r['kelas']}</td>";
            echo "<td>{$r['h']}</td><td>{$r['i']}</td><td>{$r['sk']}</td><td>{$r['a']}</td></tr>";
            $th+=$r['h']; $ti+=$r['i']; $ts+=$r['sk']; $ta+=$r['a'];
            $no++;
        }
        
        echo "<tr style='background:#FFF3CD;font-weight:bold'><td colspan='5' align='right'>TOTAL:</td>";
        echo "<td>$th</td><td>$ti</td><td>$ts</td><td>$ta</td></tr>";
        echo "</table>";
        exit;
        
    } else {
        // Export Harian (Detail per hari)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Absensi_' . $filter_date . '.xls"');
        
        $where = "WHERE a.tanggal = '$filter_date'";
        if ($filter_jurusan) $where .= " AND s.jurusan = '$filter_jurusan'";
        if ($filter_kelas) $where .= " AND s.kelas = '$filter_kelas'";
        if ($filter_status) $where .= " AND a.status = '$filter_status'";
        
        $sql = "SELECT a.*, s.nama_siswa, s.nis, s.jurusan, s.kelas 
                FROM absensi_lengkap a
                JOIN siswa s ON a.id_siswa = s.id_siswa
                $where
                ORDER BY s.jurusan, s.kelas, s.nama_siswa";
        
        $result = $conn->query($sql);
        
        echo "<table border='1'>";
        echo "<tr><td colspan='10' style='text-align:center;font-weight:bold'>DATA ABSENSI HARIAN</td></tr>";
        echo "<tr><td colspan='10' style='text-align:center'>" . formatTanggal($filter_date) . "</td></tr>";
        echo "<tr><td colspan='10'></td></tr>";
        echo "<tr style='background:#FFD700;font-weight:bold'>";
        echo "<th>No</th><th>NIS</th><th>Nama</th><th>Jurusan</th><th>Kelas</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th><th>Keterangan</th></tr>";
        
        $no=1;
        while($r=$result->fetch_assoc()) {
            echo "<tr><td>$no</td><td>{$r['nis']}</td><td>{$r['nama_siswa']}</td><td>{$r['jurusan']}</td><td>{$r['kelas']}</td>";
            echo "<td>" . ($r['jam_masuk'] ? date('H:i', strtotime($r['jam_masuk'])) : '-') . "</td>";
            echo "<td>" . ($r['jam_pulang'] ? date('H:i', strtotime($r['jam_pulang'])) : '-') . "</td>";
            echo "<td>{$r['status']}</td><td>{$r['keterangan']}</td></tr>";
            $no++;
        }
        echo "</table>";
        exit;
    }
}

// Build Query untuk tampilan
$where = "WHERE a.tanggal = '$filter_date'";
if ($filter_jurusan) $where .= " AND s.jurusan = '$filter_jurusan'";
if ($filter_kelas) $where .= " AND s.kelas = '$filter_kelas'";
if ($filter_status) $where .= " AND a.status = '$filter_status'";

$sql = "SELECT a.*, s.nama_siswa, s.nis, s.jurusan, s.kelas 
        FROM absensi_lengkap a
        JOIN siswa s ON a.id_siswa = s.id_siswa
        $where
        ORDER BY s.jurusan, s.kelas, s.nama_siswa ASC";
$absensi = $conn->query($sql);

// Get Lists
$jurusan_list = $conn->query("SELECT DISTINCT jurusan FROM siswa WHERE jurusan IS NOT NULL AND jurusan != '' ORDER BY jurusan ASC");
$kelas_list = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
?>

<div class="table-card">
    <div class="table-header">
        <h5><i class="fas fa-clipboard-list"></i> Data Absensi</h5>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalExport">
            <i class="fas fa-file-excel"></i> Export Excel
        </button>
    </div>
    
    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="date" id="filter_tanggal" class="form-control" value="<?php echo $filter_date; ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Jurusan</label>
            <select id="filter_jurusan" class="form-control">
                <option value="">Semua</option>
                <?php while($j = $jurusan_list->fetch_assoc()): ?>
                    <option value="<?php echo $j['jurusan']; ?>" <?php echo $filter_jurusan == $j['jurusan'] ? 'selected' : ''; ?>>
                        <?php echo $j['jurusan']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Kelas</label>
            <select id="filter_kelas" class="form-control">
                <option value="">Semua</option>
                <?php while($k = $kelas_list->fetch_assoc()): ?>
                    <option value="<?php echo $k['kelas']; ?>" <?php echo $filter_kelas == $k['kelas'] ? 'selected' : ''; ?>>
                        <?php echo $k['kelas']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select id="filter_status" class="form-control">
                <option value="">Semua</option>
                <option value="Hadir" <?php echo $filter_status == 'Hadir' ? 'selected' : ''; ?>>Hadir</option>
                <option value="Izin" <?php echo $filter_status == 'Izin' ? 'selected' : ''; ?>>Izin</option>
                <option value="Sakit" <?php echo $filter_status == 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                <option value="Alfa" <?php echo $filter_status == 'Alfa' ? 'selected' : ''; ?>>Alfa</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <button onclick="applyFilter()" class="btn btn-primary w-100">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
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
                        $badge_class = ['Hadir'=>'bg-success','Izin'=>'bg-info','Sakit'=>'bg-warning','Alfa'=>'bg-danger'][$row['status']] ?? 'bg-secondary';
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nis']; ?></td>
                        <td><?php echo $row['nama_siswa']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo $row['jurusan'] ?: '-'; ?></span></td>
                        <td><span class="badge bg-primary"><?php echo $row['kelas']; ?></span></td>
                        <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                        <td><?php echo $row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) : '-'; ?></td>
                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span></td>
                        <td><?php echo $row['keterangan'] ?: '-'; ?></td>
                        <td>
                            <?php if ($row['foto_masuk']): ?>
                                <button class="btn btn-sm btn-info" onclick="showPhoto('<?php echo UPLOAD_URL . $row['foto_masuk']; ?>', '<?php echo $row['foto_pulang'] ? UPLOAD_URL . $row['foto_pulang'] : ''; ?>')">
                                    <i class="fas fa-image"></i>
                                </button>
                            <?php else: echo '-'; endif; ?>
                        </td>
                        <td>
                            <?php if ($row['lokasi']): ?>
                                <button class="btn btn-sm btn-warning" onclick="showMap('<?php echo $row['lokasi']; ?>')">
                                    <i class="fas fa-map-marker-alt"></i>
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

<!-- Modal Export -->
<div class="modal fade" id="modalExport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #28a745; color: white;">
                <h5 class="modal-title"><i class="fas fa-file-excel"></i> Export Data Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pilih jenis export: <strong>Harian</strong> , <strong>Bulanan</strong> atau <strong>Tahunan</strong>
                </div>
                <form>
                    <div class="mb-3">
                        <label class="form-label"><strong>Jenis Export</strong></label>
                        <select id="jenis_export" class="form-control" onchange="toggleExportType()">
                            <option value="harian">Harian </option>
                            <option value="bulanan">Bulanan </option>
                            <option value="tahunan">Tahunan </option>
                        </select>
                    </div>
                    <div id="inp_harian">
                        <div class="mb-3">
                            <label class="form-label"><strong>Tanggal</strong></label>
                            <input type="date" id="tanggal_export" class="form-control" value="<?php echo $filter_date; ?>">
                        </div>
                    </div>
                    <div id="inp_bulanan" style="display:none">
                        <div class="mb-3">
                            <label class="form-label"><strong>Bulan</strong></label>
                            <input type="month" id="bulan_export" class="form-control" value="<?php echo $filter_bulan; ?>">
                        </div>
                    </div>
                    <div id="inp_tahunan" style="display:none">
                        <div class="mb-3">
                            <label class="form-label"><strong>Tahun</strong></label>
                            <select id="tahun_export" class="form-control">
                                <?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y==date('Y')?'selected':''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Jurusan </strong></label>
                        <select id="jurusan_export" class="form-control">
                            <option value="">Semua Jurusan</option>
                            <?php 
                            $jurusan_list->data_seek(0);
                            while($j = $jurusan_list->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $j['jurusan']; ?>"><?php echo $j['jurusan']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Kelas </strong></label>
                        <select id="kelas_export" class="form-control">
                            <option value="">Semua Kelas</option>
                            <?php 
                            $kelas_list->data_seek(0);
                            while($k = $kelas_list->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $k['kelas']; ?>"><?php echo $k['kelas']; ?></option>
                            <?php endwhile; ?>
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

<script>
function applyFilter() {
    var url = 'data_absensi.php?tanggal=' + document.getElementById('filter_tanggal').value;
    var j = document.getElementById('filter_jurusan').value;
    var k = document.getElementById('filter_kelas').value;
    var s = document.getElementById('filter_status').value;
    if(j) url += '&jurusan=' + j;
    if(k) url += '&kelas=' + k;
    if(s) url += '&status=' + s;
    window.location.href = url;
}

function toggleExportType() {
    var j = document.getElementById('jenis_export').value;
    document.getElementById('inp_harian').style.display = j=='harian' ? 'block' : 'none';
    document.getElementById('inp_bulanan').style.display = j=='bulanan' ? 'block' : 'none';
    document.getElementById('inp_tahunan').style.display = j=='tahunan' ? 'block' : 'none';
}

function exportExcel() {
    var j = document.getElementById('jenis_export').value;
    var url = 'data_absensi.php?export=1&export_type=' + j;
    
    if(j=='harian') {
        var t = document.getElementById('tanggal_export').value;
        if(!t) { alert('Pilih tanggal!'); return; }
        url += '&tanggal=' + t;
    } else if(j=='bulanan') {
        var b = document.getElementById('bulan_export').value;
        if(!b) { alert('Pilih bulan!'); return; }
        url += '&bulan=' + b;
    } else if(j=='tahunan') {
        var th = document.getElementById('tahun_export').value;
        if(!th) { alert('Pilih tahun!'); return; }
        url += '&tahun=' + th;
    }
    
    var jr = document.getElementById('jurusan_export').value;
    var kl = document.getElementById('kelas_export').value;
    if(jr) url += '&jurusan=' + jr;
    if(kl) url += '&kelas=' + kl;
    
    window.location.href = url;
    setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('modalExport')).hide(), 500);
}

function showPhoto(m, p) {
    var c = '<div class="row"><div class="col-md-6"><h6>Foto Masuk</h6><img src="' + m + '" class="img-fluid rounded"></div>';
    if(p) c += '<div class="col-md-6"><h6>Foto Pulang</h6><img src="' + p + '" class="img-fluid rounded"></div>';
    c += '</div>';
    Swal.fire({html: c, showCloseButton: true, showConfirmButton: false, width: 800});
}

function showMap(l) {
    var [lat,lng] = l.split(',');
    window.open('https://www.google.com/maps?q=' + lat + ',' + lng, '_blank');
}
</script>

<?php require_once 'includes/footer.php'; ?>