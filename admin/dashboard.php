<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

// Get Statistics
$total_siswa = $conn->query("SELECT COUNT(*) as total FROM siswa")->fetch_assoc()['total'];
$total_guru = $conn->query("SELECT COUNT(*) as total FROM guru")->fetch_assoc()['total'];
$total_admin = $conn->query("SELECT COUNT(*) as total FROM admin")->fetch_assoc()['total'];

// Absensi hari ini
$today = date('Y-m-d');
$absensi_today = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE tanggal = '$today'")->fetch_assoc()['total'];
$hadir_today = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE tanggal = '$today' AND status = 'Hadir'")->fetch_assoc()['total'];
$izin_today = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE tanggal = '$today' AND status = 'Izin'")->fetch_assoc()['total'];
$sakit_today = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE tanggal = '$today' AND status = 'Sakit'")->fetch_assoc()['total'];
$alfa_today = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE tanggal = '$today' AND status = 'Alfa'")->fetch_assoc()['total'];

// Persentase kehadiran
$persentase_hadir = $total_siswa > 0 ? round(($hadir_today / $total_siswa) * 100, 1) : 0;

// Absensi terbaru
$latest_absensi = $conn->query("
    SELECT a.*, s.nama_siswa, s.kelas 
    FROM absensi_lengkap a
    JOIN siswa s ON a.id_siswa = s.id_siswa
    ORDER BY a.tanggal DESC, a.jam_masuk DESC
    LIMIT 10
");
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-label">Total Siswa</div>
            <div class="stat-value"><?php echo $total_siswa; ?></div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-label">Total Guru</div>
            <div class="stat-value"><?php echo $total_guru; ?></div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-label">Hadir Hari Ini</div>
            <div class="stat-value"><?php echo $hadir_today; ?></div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-label">Persentase Hadir</div>
            <div class="stat-value"><?php echo $persentase_hadir; ?>%</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="table-card">
            <div class="table-header">
                <h5><i class="fas fa-history"></i> Absensi Terbaru</h5>
                <a href="data_absensi.php" class="btn btn-sm btn-primary">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($latest_absensi->num_rows > 0): ?>
                            <?php while($row = $latest_absensi->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['nama_siswa']; ?></td>
                                    <td><?php echo $row['kelas']; ?></td>
                                    <td><?php echo formatTanggal($row['tanggal']); ?></td>
                                    <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $badge_class = '';
                                        switch($row['status']) {
                                            case 'Hadir': $badge_class = 'bg-success'; break;
                                            case 'Izin': $badge_class = 'bg-info'; break;
                                            case 'Sakit': $badge_class = 'bg-warning'; break;
                                            case 'Alfa': $badge_class = 'bg-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data absensi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <div class="table-header">
                <h5><i class="fas fa-chart-pie"></i> Statistik Hari Ini</h5>
            </div>
            
            <div class="stat-list">
                <div class="stat-item" style="padding: 15px 0; border-bottom: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <i class="fas fa-check-circle text-success"></i>
                            <span style="margin-left: 10px; font-weight: 500;">Hadir</span>
                        </div>
                        <span style="font-weight: bold; font-size: 1.2rem;"><?php echo $hadir_today; ?></span>
                    </div>
                </div>
                
                <div class="stat-item" style="padding: 15px 0; border-bottom: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <i class="fas fa-info-circle text-info"></i>
                            <span style="margin-left: 10px; font-weight: 500;">Izin</span>
                        </div>
                        <span style="font-weight: bold; font-size: 1.2rem;"><?php echo $izin_today; ?></span>
                    </div>
                </div>
                
                <div class="stat-item" style="padding: 15px 0; border-bottom: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <i class="fas fa-hospital text-warning"></i>
                            <span style="margin-left: 10px; font-weight: 500;">Sakit</span>
                        </div>
                        <span style="font-weight: bold; font-size: 1.2rem;"><?php echo $sakit_today; ?></span>
                    </div>
                </div>
                
                <div class="stat-item" style="padding: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <i class="fas fa-times-circle text-danger"></i>
                            <span style="margin-left: 10px; font-weight: 500;">Alfa</span>
                        </div>
                        <span style="font-weight: bold; font-size: 1.2rem;"><?php echo $alfa_today; ?></span>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: rgba(255, 215, 0, 0.1); border-radius: 10px;">
                <div style="text-align: center;">
                    <small style="color: #666;">Total Absensi Hari Ini</small>
                    <div style="font-size: 2rem; font-weight: bold; color: var(--primary-yellow);">
                        <?php echo $absensi_today; ?>
                    </div>
                    <small style="color: #666;">dari <?php echo $total_siswa; ?> siswa</small>
                </div>
            </div>
        </div>
        
        <div class="table-card" style="margin-top: 20px;">
            <div class="table-header">
                <h5><i class="fas fa-clock"></i> Waktu Server</h5>
            </div>
            
            <div style="text-align: center; padding: 20px;">
                <div id="serverTime" style="font-size: 2rem; font-weight: bold; color: var(--primary-yellow); font-family: 'Courier New', monospace;">
                    --:--:--
                </div>
                <div id="serverDate" style="margin-top: 10px; color: #666;">
                    -
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateServerTime() {
    const now = new Date();
    
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('serverTime').textContent = `${hours}:${minutes}:${seconds}`;
    
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    const dayName = days[now.getDay()];
    const day = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();
    
    document.getElementById('serverDate').textContent = `${dayName}, ${day} ${month} ${year}`;
}

updateServerTime();
setInterval(updateServerTime, 1000);
</script>

<?php require_once 'includes/footer.php'; ?>