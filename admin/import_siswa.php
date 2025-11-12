<?php
require_once '../config.php';
requireLogin('admin');

// Library untuk parsing Excel
require_once 'vendor/SimpleXLSX.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_excel'])) {
    $file = $_FILES['file_excel'];
    
    // Validasi file
    $allowed_ext = ['xlsx', 'xls'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode(['success' => false, 'message' => 'File harus berformat Excel (.xlsx atau .xls)']);
        exit;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        exit;
    }
    
    try {
        // Parse Excel menggunakan SimpleXLSX
        if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
            $rows = $xlsx->rows();
            
            $success = 0;
            $failed = 0;
            $errors = [];
            
            // Skip header (baris pertama)
            array_shift($rows);
            
            foreach ($rows as $index => $row) {
                // Format Excel: NIS | Nama | Kelas | Jurusan | JK | Tempat Lahir | Tanggal Lahir | Alamat | No Telp | Email | Nama Wali | No Telp Wali | Username | Password
                
                // Validasi data minimal
                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) {
                    $failed++;
                    $errors[] = "Baris " . ($index + 2) . ": Data NIS, Nama, Kelas, atau Jurusan kosong";
                    continue;
                }
                
                $nis = sanitize($row[0]);
                $nama = sanitize($row[1]);
                $kelas = sanitize($row[2]);
                $jurusan = sanitize($row[3]);
                $jk = sanitize($row[4] ?? 'L');
                $tempat_lahir = sanitize($row[5] ?? '');
                $tanggal_lahir = sanitize($row[6] ?? '');
                $alamat = sanitize($row[7] ?? '');
                $no_telp = sanitize($row[8] ?? '');
                $email = sanitize($row[9] ?? '');
                $nama_wali = sanitize($row[10] ?? '');
                $no_telp_wali = sanitize($row[11] ?? '');
                $username = sanitize($row[12] ?? strtolower(str_replace(' ', '', $nama)));
                $password = $row[13] ?? '12345678';
                
                // Validasi jenis kelamin
                if (!in_array($jk, ['L', 'P'])) {
                    $jk = 'L';
                }
                
                // Cek apakah NIS atau username sudah ada
                $check = $conn->query("SELECT id_siswa FROM siswa WHERE nis = '$nis' OR username = '$username'");
                if ($check->num_rows > 0) {
                    $failed++;
                    $errors[] = "Baris " . ($index + 2) . ": NIS atau Username sudah terdaftar";
                    continue;
                }
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert ke database
                $sql = "INSERT INTO siswa (
                    nis, nama_siswa, kelas, jurusan, username, password, 
                    jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, 
                    no_telp, email, nama_wali, no_telp_wali
                ) VALUES (
                    '$nis', '$nama', '$kelas', '$jurusan', '$username', '$hashed_password',
                    '$jk', '$tempat_lahir', '$tanggal_lahir', '$alamat',
                    '$no_telp', '$email', '$nama_wali', '$no_telp_wali'
                )";
                
                if ($conn->query($sql)) {
                    $success++;
                } else {
                    $failed++;
                    $errors[] = "Baris " . ($index + 2) . ": " . $conn->error;
                }
            }
            
            $message = "Berhasil import $success data siswa";
            if ($failed > 0) {
                $message .= ", $failed data gagal";
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'imported' => $success,
                'failed' => $failed,
                'errors' => $errors
            ]);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError()]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
}
?>