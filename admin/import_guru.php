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
                // Format Excel: Nama Guru | Mata Pelajaran | Username | Password | Kelas yang Diajar (TKJ-X,RPL-XI,AKL-XII)
                
                // Validasi data minimal
                if (empty($row[0]) || empty($row[1])) {
                    $failed++;
                    $errors[] = "Baris " . ($index + 2) . ": Data Nama atau Mata Pelajaran kosong";
                    continue;
                }
                
                $nama_guru = sanitize($row[0]);
                $mapel = sanitize($row[1]);
                $username = sanitize($row[2] ?? strtolower(str_replace(' ', '', $nama_guru)));
                $password = $row[3] ?? '12345678';
                $kelas_ajar = isset($row[4]) ? $row[4] : ''; // Format: TKJ-X,RPL-XI,AKL-XII
                
                // Cek apakah username sudah ada
                $check = $conn->query("SELECT id_guru FROM guru WHERE username = '$username'");
                if ($check->num_rows > 0) {
                    $failed++;
                    $errors[] = "Baris " . ($index + 2) . ": Username sudah terdaftar";
                    continue;
                }
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert guru ke database
                $sql = "INSERT INTO guru (nama_guru, mapel, username, password) 
                        VALUES ('$nama_guru', '$mapel', '$username', '$hashed_password')";
                
                if ($conn->query($sql)) {
                    $id_guru = $conn->insert_id;
                    
                    // Insert kelas yang diajar (format: TKJ-X,RPL-XI,AKL-XII)
                    if (!empty($kelas_ajar)) {
                        $kelas_array = array_map('trim', explode(',', $kelas_ajar));
                        
                        foreach ($kelas_array as $kelas_combo) {
                            if (!empty($kelas_combo)) {
                                // Split JURUSAN-TINGKAT
                                $parts = explode('-', $kelas_combo);
                                if (count($parts) == 2) {
                                    $jurusan = sanitize($parts[0]);
                                    $tingkat = sanitize($parts[1]);
                                    $conn->query("INSERT INTO guru_kelas (id_guru, jurusan, tingkat) VALUES ($id_guru, '$jurusan', '$tingkat')");
                                }
                            }
                        }
                    }
                    
                    $success++;
                } else {
                    $failed++;
                    $errors[] = "Baris " . ($index + 2) . ": " . $conn->error;
                }
            }
            
            $message = "Berhasil import $success data guru";
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