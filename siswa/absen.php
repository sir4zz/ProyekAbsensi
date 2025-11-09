<?php
require_once '../config.php';
requireLogin('siswa');

$id_siswa = $_SESSION['siswa_id'];
$siswa = $conn->query("SELECT * FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();

// Check today's absensi
$today = date('Y-m-d');
$absensi_today = $conn->query("SELECT * FROM absensi_lengkap WHERE id_siswa = $id_siswa AND tanggal = '$today'")->fetch_assoc();

$error = '';
$success = '';

// Handle Absensi Masuk
if (isset($_POST['absen_masuk'])) {
    $status = sanitize($_POST['status']);
    $keterangan = sanitize($_POST['keterangan']);
    $foto = $_POST['foto'];
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $lokasi = $latitude . ',' . $longitude;
    
    // Validasi GPS untuk status Hadir
    if ($status == 'Hadir') {
        $distance = calculateDistance($latitude, $longitude, SCHOOL_LAT, SCHOOL_LNG);
        if ($distance > MAX_DISTANCE) {
            $error = "Anda berada di luar area sekolah! Jarak: " . number_format($distance, 2) . " km. Tidak dapat absen dengan status Hadir.";
        }
    }
    
    if (!isset($error) || $error == '') {
        // Save photo
        $foto = str_replace('data:image/png;base64,', '', $foto);
        $foto = str_replace(' ', '+', $foto);
        $fotoData = base64_decode($foto);
        $filename = 'absen_' . $id_siswa . '_' . date('YmdHis') . '.png';
        file_put_contents(UPLOAD_PATH . $filename, $fotoData);
        
        // Insert absensi
        $jam_masuk = date('H:i:s');
        $sql = "INSERT INTO absensi_lengkap (id_siswa, tanggal, jam_masuk, foto_masuk, lokasi, status, keterangan) 
                VALUES ($id_siswa, '$today', '$jam_masuk', '$filename', '$lokasi', '$status', '$keterangan')";
        
        if ($conn->query($sql)) {
            $success = "Absensi masuk berhasil! Status: $status";
            $absensi_today = $conn->query("SELECT * FROM absensi_lengkap WHERE id_siswa = $id_siswa AND tanggal = '$today'")->fetch_assoc();
        } else {
            $error = "Gagal menyimpan absensi!";
        }
    }
}

// Handle Absensi Pulang
if (isset($_POST['absen_pulang'])) {
    $foto = $_POST['foto'];
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    
    // Save photo
    $foto = str_replace('data:image/png;base64,', '', $foto);
    $foto = str_replace(' ', '+', $foto);
    $fotoData = base64_decode($foto);
    $filename = 'pulang_' . $id_siswa . '_' . date('YmdHis') . '.png';
    file_put_contents(UPLOAD_PATH . $filename, $fotoData);
    
    // Update absensi
    $jam_pulang = date('H:i:s');
    $sql = "UPDATE absensi_lengkap SET jam_pulang = '$jam_pulang', foto_pulang = '$filename' 
            WHERE id_siswa = $id_siswa AND tanggal = '$today'";
    
    if ($conn->query($sql)) {
        $success = "Absensi pulang berhasil!";
        $absensi_today = $conn->query("SELECT * FROM absensi_lengkap WHERE id_siswa = $id_siswa AND tanggal = '$today'")->fetch_assoc();
    } else {
        $error = "Gagal menyimpan absensi pulang!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi - <?php echo SITE_NAME; ?></title>
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
        
        .camera-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        #video {
            width: 100%;
            max-width: 640px;
            height: auto;
            border-radius: 15px;
            background: #000;
            display: block;
            margin: 0 auto;
        }
        
        #canvas {
            display: none;
        }
        
        .preview-img {
            width: 100%;
            max-width: 640px;
            border-radius: 15px;
            margin: 20px auto;
            display: block;
        }
        
        .btn-capture {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-capture:hover {
            background: var(--dark-yellow);
            transform: translateY(-2px);
        }
        
        .btn-capture:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .info-box {
            background: rgba(255, 215, 0, 0.1);
            border-left: 4px solid var(--primary-yellow);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .gps-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 15px;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-yellow);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status-badge {
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            margin: 10px 0;
        }
        
        .form-control, .form-select {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 12px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-yellow);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; color: var(--primary-yellow);">Absensi</h5>
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
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Status Hari Ini -->
                <div class="info-box">
                    <h5 style="color: var(--primary-black); margin-bottom: 15px;">
                        <i class="fas fa-calendar-day"></i> Status Absensi Hari Ini
                    </h5>
                    <?php if ($absensi_today): ?>
                        <div>
                            <span class="status-badge bg-success">
                                <i class="fas fa-check-circle"></i> Sudah Absen Masuk
                            </span>
                            <div style="margin-top: 15px;">
                                <strong>Status:</strong> <span class="badge bg-primary"><?php echo $absensi_today['status']; ?></span><br>
                                <strong>Jam Masuk:</strong> <?php echo date('H:i:s', strtotime($absensi_today['jam_masuk'])); ?>
                                <?php if ($absensi_today['keterangan']): ?>
                                    <br><strong>Keterangan:</strong> <?php echo $absensi_today['keterangan']; ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($absensi_today['status'] == 'Hadir'): ?>
                                <!-- Hanya tampilkan info absen pulang jika status HADIR -->
                                <?php if ($absensi_today['jam_pulang']): ?>
                                    <div style="margin-top: 10px;">
                                        <strong>Jam Pulang:</strong> <?php echo date('H:i:s', strtotime($absensi_today['jam_pulang'])); ?>
                                    </div>
                                    <span class="status-badge bg-info mt-2">
                                        <i class="fas fa-check-double"></i> Sudah Absen Pulang
                                    </span>
                                <?php else: ?>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="fas fa-info-circle"></i> Jangan lupa absen pulang nanti!
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Status Izin/Sakit tidak perlu absen pulang -->
                                <div class="alert alert-success mt-3 mb-0">
                                    <i class="fas fa-check-circle"></i> Absensi selesai. Status <strong><?php echo $absensi_today['status']; ?></strong> tidak memerlukan absen pulang.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="status-badge bg-warning">
                            <i class="fas fa-exclamation-triangle"></i> Belum Absen
                        </span>
                        <p class="mt-2 mb-0">Silakan lakukan absensi masuk terlebih dahulu</p>
                    <?php endif; ?>
                </div>
                
                <!-- Camera Container -->
                <?php if (!$absensi_today): ?>
                    <!-- Absen Masuk -->
                    <div class="camera-container">
                        <h4 style="text-align: center; color: var(--primary-black); margin-bottom: 20px;">
                            <i class="fas fa-sign-in-alt"></i> Absensi Masuk
                        </h4>
                        
                        <!-- Pilihan Status -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600; color: var(--primary-black);">
                                <i class="fas fa-clipboard-check"></i> Pilih Status Kehadiran *
                            </label>
                            <select id="statusAbsen" class="form-select" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Hadir">✓ Hadir (Wajib di area sekolah)</option>
                                <option value="Izin">ℹ Izin (Bisa dari mana saja)</option>
                                <option value="Sakit">+ Sakit (Bisa dari mana saja)</option>
                            </select>
                        </div>
                        
                        <div id="keteranganContainer" style="display:none;" class="mb-3">
                            <label class="form-label" style="font-weight: 600; color: var(--primary-black);">
                                <i class="fas fa-comment"></i> Keterangan *
                            </label>
                            <textarea id="keteranganText" class="form-control" rows="3" placeholder="Masukkan alasan izin/sakit (minimal 5 karakter)..."></textarea>
                            <small class="text-muted">Wajib diisi untuk status Izin atau Sakit</small>
                        </div>
                        
                        <div id="cameraSection" style="display:none;">
                            <video id="video" autoplay playsinline></video>
                            <canvas id="canvas"></canvas>
                            
                            <div id="preview" style="display:none;">
                                <img id="previewImg" class="preview-img" alt="Preview">
                            </div>
                            
                            <div class="gps-status" id="gpsStatus">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Mendeteksi lokasi GPS...</span>
                            </div>
                            
                            <div class="alert" id="infoStatus" style="display:none; margin-top:15px;">
                                <i class="fas fa-info-circle"></i>
                                <span id="infoText"></span>
                            </div>
                        </div>
                        
                        <form method="POST" id="formAbsen">
                            <input type="hidden" name="status" id="statusInput">
                            <input type="hidden" name="keterangan" id="keteranganInput">
                            <input type="hidden" name="foto" id="fotoData">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            
                            <button type="button" id="btnCapture" class="btn-capture" style="display:none;">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            
                            <button type="submit" name="absen_masuk" id="btnSubmit" class="btn-capture" style="display:none; background: #28a745;">
                                <i class="fas fa-check"></i> Kirim Absensi Masuk
                            </button>
                        </form>
                        
                        <div class="loading" id="loading">
                            <div class="spinner"></div>
                            <p>Menyimpan absensi...</p>
                        </div>
                    </div>
                <?php elseif (!$absensi_today['jam_pulang']): ?>
                    <!-- Absen Pulang -->
                    <div class="camera-container">
                        <h4 style="text-align: center; color: var(--primary-black); margin-bottom: 20px;">
                            <i class="fas fa-sign-out-alt"></i> Absensi Pulang
                        </h4>
                        
                        <video id="video" autoplay playsinline></video>
                        <canvas id="canvas"></canvas>
                        
                        <div id="preview" style="display:none;">
                            <img id="previewImg" class="preview-img" alt="Preview">
                        </div>
                        
                        <div class="gps-status" id="gpsStatus">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Mendeteksi lokasi GPS...</span>
                        </div>
                        
                        <form method="POST" id="formAbsen">
                            <input type="hidden" name="foto" id="fotoData">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            
                            <button type="button" id="btnCapture" class="btn-capture" onclick="capturePhotoPulang()">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            
                            <button type="submit" name="absen_pulang" id="btnSubmit" class="btn-capture" style="display:none; background: #17a2b8;">
                                <i class="fas fa-check"></i> Kirim Absensi Pulang
                            </button>
                        </form>
                        
                        <div class="loading" id="loading">
                            <div class="spinner"></div>
                            <p>Menyimpan absensi...</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let stream = null;
        let latitude = null;
        let longitude = null;
        let selectedStatus = '';
        let isInSchoolArea = false;
        
        // Handle Status Change
        document.getElementById('statusAbsen')?.addEventListener('change', function() {
            selectedStatus = this.value;
            document.getElementById('statusInput').value = selectedStatus;
            
            if (selectedStatus === '') {
                document.getElementById('cameraSection').style.display = 'none';
                document.getElementById('keteranganContainer').style.display = 'none';
                return;
            }
            
            // Show keterangan for Izin/Sakit
            if (selectedStatus === 'Izin' || selectedStatus === 'Sakit') {
                document.getElementById('keteranganContainer').style.display = 'block';
                document.getElementById('keteranganText').required = true;
                
                // Set info message
                const infoStatus = document.getElementById('infoStatus');
                infoStatus.className = 'alert alert-info';
                infoStatus.style.display = 'block';
                
                if (selectedStatus === 'Izin') {
                    document.getElementById('infoText').innerHTML = '<strong>Status Izin:</strong> Anda dapat melakukan absensi dari mana saja. GPS tidak dibatasi.';
                } else {
                    document.getElementById('infoText').innerHTML = '<strong>Status Sakit:</strong> Anda dapat melakukan absensi dari mana saja. GPS tidak dibatasi.';
                }
            } else {
                document.getElementById('keteranganContainer').style.display = 'none';
                document.getElementById('keteranganText').required = false;
                
                const infoStatus = document.getElementById('infoStatus');
                infoStatus.className = 'alert alert-warning';
                infoStatus.style.display = 'block';
                document.getElementById('infoText').innerHTML = '<strong>Status Hadir:</strong> Anda WAJIB berada di area sekolah untuk melakukan absensi.';
            }
            
            // Show camera section
            document.getElementById('cameraSection').style.display = 'block';
            startCamera();
            getLocation();
        });
        
        // Start Camera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    } 
                });
                document.getElementById('video').srcObject = stream;
                document.getElementById('btnCapture').style.display = 'block';
            } catch (err) {
                alert('Tidak dapat mengakses kamera: ' + err.message);
            }
        }
        
        // Get GPS Location
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        latitude = position.coords.latitude;
                        longitude = position.coords.longitude;
                        
                        document.getElementById('latitude').value = latitude;
                        document.getElementById('longitude').value = longitude;
                        
                        // Calculate distance from school
                        const schoolLat = <?php echo SCHOOL_LAT; ?>;
                        const schoolLng = <?php echo SCHOOL_LNG; ?>;
                        const distance = calculateDistance(latitude, longitude, schoolLat, schoolLng);
                        
                        const gpsStatus = document.getElementById('gpsStatus');
                        
                        if (distance <= <?php echo MAX_DISTANCE; ?>) {
                            isInSchoolArea = true;
                            gpsStatus.innerHTML = `
                                <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                <span>Lokasi GPS terdeteksi - Anda berada di area sekolah (${distance.toFixed(2)} km)</span>
                            `;
                        } else {
                            isInSchoolArea = false;
                            gpsStatus.innerHTML = `
                                <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                                <span>Anda berada di luar area sekolah (${distance.toFixed(2)} km dari sekolah)</span>
                            `;
                        }
                    },
                    error => {
                        document.getElementById('gpsStatus').innerHTML = `
                            <i class="fas fa-times-circle" style="color: #dc3545;"></i>
                            <span>Gagal mendapatkan lokasi GPS</span>
                        `;
                        alert('Izinkan akses lokasi untuk melakukan absensi!');
                    }
                );
            } else {
                alert('Browser Anda tidak mendukung GPS!');
            }
        }
        
        // Calculate Distance
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth radius in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }
        
        // Capture Photo (Masuk)
        document.getElementById('btnCapture')?.addEventListener('click', function() {
            // Validate status
            if (!selectedStatus) {
                alert('Pilih status kehadiran terlebih dahulu!');
                return;
            }
            
            // Validate keterangan for Izin/Sakit
            if ((selectedStatus === 'Izin' || selectedStatus === 'Sakit')) {
                const keterangan = document.getElementById('keteranganText').value.trim();
                if (!keterangan || keterangan.length < 5) {
                    alert('Keterangan wajib diisi minimal 5 karakter untuk status ' + selectedStatus + '!');
                    return;
                }
                document.getElementById('keteranganInput').value = keterangan;
            }
            
            // Validate GPS for Hadir
            if (selectedStatus === 'Hadir' && !isInSchoolArea) {
                alert('Anda harus berada di area sekolah untuk absen dengan status HADIR!\n\nJika Anda tidak di sekolah, silakan pilih status Izin atau Sakit.');
                return;
            }
            
            if (!latitude || !longitude) {
                alert('Tunggu hingga lokasi GPS terdeteksi!');
                return;
            }
            
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            
            const dataURL = canvas.toDataURL('image/png');
            document.getElementById('fotoData').value = dataURL;
            document.getElementById('previewImg').src = dataURL;
            
            // Show preview
            document.getElementById('video').style.display = 'none';
            document.getElementById('preview').style.display = 'block';
            document.getElementById('btnCapture').style.display = 'none';
            document.getElementById('btnSubmit').style.display = 'block';
            
            // Stop camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
        
        // Capture Photo Pulang
        function capturePhotoPulang() {
            if (!latitude || !longitude) {
                alert('Tunggu hingga lokasi GPS terdeteksi!');
                return;
            }
            
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            
            const dataURL = canvas.toDataURL('image/png');
            document.getElementById('fotoData').value = dataURL;
            document.getElementById('previewImg').src = dataURL;
            
            // Show preview
            document.getElementById('video').style.display = 'none';
            document.getElementById('preview').style.display = 'block';
            document.getElementById('btnCapture').style.display = 'none';
            document.getElementById('btnSubmit').style.display = 'block';
            
            // Stop camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        }
        
        // Form Submit
        document.getElementById('formAbsen')?.addEventListener('submit', function(e) {
            // Final validation for absen masuk
            if (document.querySelector('button[name="absen_masuk"]')) {
                if (selectedStatus === 'Hadir' && !isInSchoolArea) {
                    e.preventDefault();
                    alert('GAGAL! Anda berada di luar area sekolah.\n\nUntuk status HADIR, Anda harus berada di area sekolah.\nGunakan status Izin/Sakit jika Anda tidak di sekolah.');
                    return false;
                }
            }
            document.getElementById('loading').style.display = 'block';
        });
        
        // Initialize untuk absen pulang
        if (document.getElementById('video') && !document.getElementById('statusAbsen')) {
            startCamera();
            getLocation();
        }
    </script>
</body>
</html>