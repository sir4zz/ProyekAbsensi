<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_absensi');

// Konfigurasi Umum
define('BASE_URL', 'http://localhost/ProyekAbsensi/');
define('SITE_NAME', 'Sistem Absensi Sekolah ');

// Konfigurasi Upload
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/ProyekAbsensi/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Konfigurasi GPS (Koordinat SMKN 11 Kabupaten Tangerang)
define('SCHOOL_LAT', -6.2011);      // Latitude SMKN 11
define('SCHOOL_LNG', 106.393);      // Longitude SMKN 11
define('MAX_DISTANCE', 0.5);        // Radius dalam kilometer
define('SCHOOL_NAME', 'SMKN 11 Kabupaten Tangerang');
define('SCHOOL_ADDRESS', 'Kp. Saradan RT. 03/01, Pangkat, Kec. Jayanti, Kab. Tangerang, Banten');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Koneksi Database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Fungsi Helper
function isLoggedIn($role) {
    return isset($_SESSION[$role . '_id']);
}

function requireLogin($role) {
    if (!isLoggedIn($role)) {
        header("Location: " . BASE_URL . "login_" . $role . ".php");
        exit();
    }
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // dalam kilometer
    
    $latDiff = deg2rad($lat2 - $lat1);
    $lonDiff = deg2rad($lon2 - $lon1);
    
    $a = sin($latDiff / 2) * sin($latDiff / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lonDiff / 2) * sin($lonDiff / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

session_start();
?>