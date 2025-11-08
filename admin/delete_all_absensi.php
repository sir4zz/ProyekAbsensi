<?php
$page_title = 'Reset Data Absensi';
require_once 'includes/header.php';

if (isset($_POST['confirm_delete'])) {
    $password = $_POST['password'];
    
    // Verify admin password
    $admin_id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id_admin = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    if (password_verify($password, $admin['password'])) {
        // Delete all absensi records
        $conn->query("DELETE FROM absensi_lengkap");
        
        // Delete all photos in upload folder
        $upload_dir = UPLOAD_PATH;
        if (is_dir($upload_dir)) {
            $files = glob($upload_dir . '*');
            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        echo "<script>showSuccess('Semua data absensi berhasil dihapus!'); setTimeout(() => window.location.href='data_absensi.php', 2000);</script>";
    } else {
        echo "<script>showError('Password salah!');</script>";
    }
}

// Get statistics
$total_absensi = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap")->fetch_assoc()['total'];
$total_hadir = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE status = 'Hadir'")->fetch_assoc()['total'];
$total_izin = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE status = 'Izin'")->fetch_assoc()['total'];
$total_sakit = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE status = 'Sakit'")->fetch_assoc()['total'];
$total_alfa = $conn->query("SELECT COUNT(*) as total FROM absensi_lengkap WHERE status = 'Alfa'")->fetch_assoc()['total'];
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="table-card">
            <div class="table-header">
                <h5><i class="fas fa-trash-alt"></i> Reset Data Absensi</h5>
            </div>
            
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>PERINGATAN!</strong> Tindakan ini akan menghapus SEMUA data absensi secara permanen dan tidak dapat dikembalikan.
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card mb-3">
                        <h6>Total Semua Absensi</h6>
                        <h2 class="text-danger"><?php echo $total_absensi; ?></h2>
                        <small>Record yang akan dihapus</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-check-circle text-success"></i> Hadir</span>
                            <strong><?php echo $total_hadir; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-info-circle text-info"></i> Izin</span>
                            <strong><?php echo $total_izin; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-hospital text-warning"></i> Sakit</span>
                            <strong><?php echo $total_sakit; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-times-circle text-danger"></i> Alfa</span>
                            <strong><?php echo $total_alfa; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <form method="POST" action="" onsubmit="return confirmReset()">
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Masukkan Password Admin untuk Konfirmasi
                    </label>
                    <input type="password" name="password" class="form-control" placeholder="Password Anda" required>
                    <small class="text-muted">Untuk keamanan, Anda harus memasukkan password admin Anda</small>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Ya, Hapus Semua Data Absensi
                    </button>
                    <a href="data_absensi.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
            
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle"></i>
                <strong>Tips:</strong> Sebaiknya lakukan backup database terlebih dahulu sebelum menghapus data.
                <a href="backup.php" class="alert-link">Backup Sekarang</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    return confirm('PERHATIAN! Anda yakin ingin menghapus SEMUA data absensi? Tindakan ini tidak dapat dibatalkan!');
}
</script>

<?php require_once 'includes/footer.php'; ?>