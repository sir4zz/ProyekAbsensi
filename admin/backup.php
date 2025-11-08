<?php
$page_title = 'Backup Database';
require_once 'includes/header.php';

if (isset($_POST['backup'])) {
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    $sql_dump = "-- Database Backup\n";
    $sql_dump .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        $result = $conn->query("SELECT * FROM $table");
        $num_fields = $result->field_count;
        
        $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
        $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
        $sql_dump .= $row2[1] . ";\n\n";
        
        for ($i = 0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $sql_dump .= "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) {
                    $sql_dump .= '"' . $row[$j] . '"';
                } else {
                    $sql_dump .= '""';
                }
                if ($j < ($num_fields - 1)) {
                    $sql_dump .= ',';
                }
            }
            $sql_dump .= ");\n";
        }
        $sql_dump .= "\n\n";
    }
    
    $filename = "backup_db_absensi_" . date('Y-m-d_H-i-s') . ".sql";
    
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    echo $sql_dump;
    exit();
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="table-card">
            <div class="table-header">
                <h5><i class="fas fa-database"></i> Backup Database</h5>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Informasi:</strong> Fitur ini akan membuat backup dari seluruh database sistem absensi dalam format SQL.
            </div>
            
            <div class="mb-4">
                <h6>Database Information:</h6>
                <table class="table table-bordered">
                    <tr>
                        <td width="200">Database Name</td>
                        <td><strong><?php echo DB_NAME; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Server</td>
                        <td><strong><?php echo DB_HOST; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal/Waktu</td>
                        <td><strong><?php echo date('d F Y H:i:s'); ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <form method="POST" action="">
                <button type="submit" name="backup" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download Backup Database
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </form>
            
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Tips:</strong> Simpan file backup di tempat yang aman. Lakukan backup secara berkala untuk menghindari kehilangan data.
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>