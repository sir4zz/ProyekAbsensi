<?php
$page_title = 'Data Absensi';
require_once 'includes/header.php';

// Filter
$filter_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build Query
$where = "WHERE a.tanggal = '$filter_date'";
if (!empty($filter_kelas)) {
    $where .= " AND s.kelas = '$filter_kelas'";
}
if (!empty($filter_status)) {
    $where .= " AND a.status = '$filter_status'";
}

// Get Absensi Data
$sql = "SELECT a.*, s.nama_siswa, s.nis, s.kelas 
        FROM absensi_lengkap a
        JOIN siswa s ON a.id_siswa = s.id_siswa
        $where
        ORDER BY s.nama_siswa ASC";
$absensi = $conn->query($sql);

// Get Kelas List
$kelas_list = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");

// Export to Excel
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Absensi_' . $filter_date . '.xls"');
    
    echo "<table border='1'>";
    echo "<tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Pulang</th>
            <th>Status</th>
            <th>Keterangan</th>
          </tr>";
    
    $absensi_export = $conn->query($sql);
    $no = 1;
    while($row = $absensi_export->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . $row['nis'] . "</td>";
        echo "<td>" . $row['nama_siswa'] . "</td>";
        echo "<td>" . $row['kelas'] . "</td>";
        echo "<td>" . formatTanggal($row['tanggal']) . "</td>";
        echo "<td>" . ($row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-') . "</td>";
        echo "<td>" . ($row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) : '-') . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['keterangan'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit();
}
?>

<div class="table-card">
    <div class="table-header">
        <h5><i class="fas fa-clipboard-list"></i> Data Absensi</h5>
        <div>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => '1'])); ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
    
    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-4">
            <label class="form-label">Tanggal</label>
            <input type="date" id="filter_tanggal" class="form-control" value="<?php echo $filter_date; ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Kelas</label>
            <select id="filter_kelas" class="form-control">
                <option value="">Semua Kelas</option>
                <?php while($k = $kelas_list->fetch_assoc()): ?>
                    <option value="<?php echo $k['kelas']; ?>" <?php echo $filter_kelas == $k['kelas'] ? 'selected' : ''; ?>>
                        <?php echo $k['kelas']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select id="filter_status" class="form-control">
                <option value="">Semua Status</option>
                <option value="Hadir" <?php echo $filter_status == 'Hadir' ? 'selected' : ''; ?>>Hadir</option>
                <option value="Izin" <?php echo $filter_status == 'Izin' ? 'selected' : ''; ?>>Izin</option>
                <option value="Sakit" <?php echo $filter_status == 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                <option value="Alfa" <?php echo $filter_status == 'Alfa' ? 'selected' : ''; ?>>Alfa</option>
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
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Status</th>
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
                        <td><span class="badge bg-primary"><?php echo $row['kelas']; ?></span></td>
                        <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                        <td><?php echo $row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) : '-'; ?></td>
                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <?php if ($row['foto_masuk']): ?>
                                <button class="btn btn-sm btn-info" onclick="showPhoto('<?php echo UPLOAD_URL . $row['foto_masuk']; ?>')">
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
                        <td colspan="9" class="text-center">Tidak ada data absensi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilter() {
    const tanggal = document.getElementById('filter_tanggal').value;
    const kelas = document.getElementById('filter_kelas').value;
    const status = document.getElementById('filter_status').value;
    
    let url = 'data_absensi.php?tanggal=' + tanggal;
    if (kelas) url += '&kelas=' + kelas;
    if (status) url += '&status=' + status;
    
    window.location.href = url;
}

function showPhoto(url) {
    Swal.fire({
        imageUrl: url,
        imageAlt: 'Foto Absensi',
        showCloseButton: true,
        showConfirmButton: false
    });
}

function showMap(lokasi) {
    const [lat, lng] = lokasi.split(',');
    const mapUrl = `https://www.google.com/maps?q=${lat},${lng}`;
    window.open(mapUrl, '_blank');
}
</script>

<?php require_once 'includes/footer.php'; ?>