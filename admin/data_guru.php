<?php
$page_title = 'Data Guru';
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM guru WHERE id_guru = $id");
    echo "<script>showSuccess('Data guru berhasil dihapus!'); setTimeout(() => window.location.href='data_guru.php', 1500);</script>";
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_FILES['file_excel'])) {
    $id = isset($_POST['id_guru']) ? (int)$_POST['id_guru'] : 0;
    $nama = sanitize($_POST['nama_guru']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $mapel = sanitize($_POST['mapel']);
    
    if ($id > 0) {
        // Update
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE guru SET nama_guru = '$nama', username = '$username', password = '$hashed', mapel = '$mapel' WHERE id_guru = $id";
        } else {
            $sql = "UPDATE guru SET nama_guru = '$nama', username = '$username', mapel = '$mapel' WHERE id_guru = $id";
        }
        $conn->query($sql);
        
        // Update kelas yang diajar
        $conn->query("DELETE FROM guru_kelas WHERE id_guru = $id");
        if (isset($_POST['kelas_ajar']) && is_array($_POST['kelas_ajar'])) {
            foreach ($_POST['kelas_ajar'] as $kelas) {
                $kelas = sanitize($kelas);
                $conn->query("INSERT INTO guru_kelas (id_guru, kelas) VALUES ($id, '$kelas')");
            }
        }
        
        echo "<script>showSuccess('Data guru berhasil diupdate!'); setTimeout(() => window.location.href='data_guru.php', 1500);</script>";
    } else {
        // Insert
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO guru (nama_guru, username, password, mapel) VALUES ('$nama', '$username', '$hashed', '$mapel')";
        $conn->query($sql);
        $id_guru = $conn->insert_id;
        
        // Insert kelas yang diajar
        if (isset($_POST['kelas_ajar']) && is_array($_POST['kelas_ajar'])) {
            foreach ($_POST['kelas_ajar'] as $kelas) {
                $kelas = sanitize($kelas);
                $conn->query("INSERT INTO guru_kelas (id_guru, kelas) VALUES ($id_guru, '$kelas')");
            }
        }
        
        echo "<script>showSuccess('Data guru berhasil ditambahkan!'); setTimeout(() => window.location.href='data_guru.php', 1500);</script>";
    }
}

// Get All Guru with Kelas
$guru = $conn->query("
    SELECT g.*, 
           GROUP_CONCAT(DISTINCT gk.kelas ORDER BY gk.kelas SEPARATOR ', ') as kelas_ajar
    FROM guru g
    LEFT JOIN guru_kelas gk ON g.id_guru = gk.id_guru
    GROUP BY g.id_guru
    ORDER BY g.nama_guru ASC
");

// Get All Kelas for Dropdown
$all_kelas = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
?>

<div class="table-card">
    <div class="table-header">
        <h5><i class="fas fa-chalkboard-teacher"></i> Daftar Guru</h5>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="fas fa-file-excel"></i> Import Excel
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGuru" onclick="resetForm()">
                <i class="fas fa-plus"></i> Tambah Guru
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Guru</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas yang Diajar</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = $guru->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nama_guru']; ?></td>
                        <td><span class="badge bg-success"><?php echo $row['mapel']; ?></span></td>
                        <td>
                            <?php if ($row['kelas_ajar']): ?>
                                <?php 
                                $kelas_arr = explode(', ', $row['kelas_ajar']);
                                foreach ($kelas_arr as $k):
                                ?>
                                    <span class="badge bg-info me-1"><?php echo $k; ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['username']; ?></td>
                        <td>
                            <button onclick="editGuru(<?php echo $row['id_guru']; ?>)" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete('?delete=<?php echo $row['id_guru']; ?>', 'Hapus guru <?php echo $row['nama_guru']; ?>?')" class="btn btn-sm btn-danger" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #28a745; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-file-excel"></i> Import Data Guru dari Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Format Excel:</strong>
                    <ol class="mb-0" style="padding-left: 20px;">
                        <li>Nama Guru</li>
                        <li>Mata Pelajaran</li>
                        <li>Username</li>
                        <li>Password</li>
                        <li>Kelas yang Diajar (pisah dengan koma, misal: X-1,X-2,XI-1)</li>
                    </ol>
                    
                </div>
                
                <form id="formImportGuru" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel</label>
                        <input type="file" name="file_excel" id="file_excel_guru" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format: .xlsx atau .xls</small>
                    </div>
                </form>
                
                <div id="importResult" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" onclick="importGuru()">
                    <i class="fas fa-upload"></i> Import
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalGuru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-yellow); color: var(--primary-black);">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> 
                    <span id="modalTitleText">Tambah Guru</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="formGuru">
                <div class="modal-body">
                    <input type="hidden" name="id_guru" id="id_guru">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="nama_guru" id="nama_guru" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran *</label>
                        <input type="text" name="mapel" id="mapel" class="form-control" placeholder="Contoh: Matematika" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kelas yang Diajar</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 10px;">
                            <?php 
                            $all_kelas->data_seek(0);
                            while($k = $all_kelas->fetch_assoc()): 
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input kelas-checkbox" type="checkbox" name="kelas_ajar[]" value="<?php echo $k['kelas']; ?>" id="kelas_<?php echo $k['kelas']; ?>">
                                    <label class="form-check-label" for="kelas_<?php echo $k['kelas']; ?>">
                                        <?php echo $k['kelas']; ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <small class="text-muted">Pilih kelas yang akan diajar oleh guru ini</small>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Akun Login</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span id="passLabel">*</span></label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Import Guru
function importGuru() {
    const fileInput = document.getElementById('file_excel_guru');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Pilih file Excel terlebih dahulu!');
        return;
    }
    
    const formData = new FormData();
    formData.append('file_excel', file);
    
    document.querySelector('#modalImport .btn-success').disabled = true;
    document.querySelector('#modalImport .btn-success').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
    
    fetch('import_guru.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('importResult');
        resultDiv.style.display = 'block';
        
        if (data.success) {
            let html = `<div class="alert alert-success">
                <strong>Berhasil!</strong> ${data.message}
            </div>`;
            
            if (data.errors && data.errors.length > 0) {
                html += `<div class="alert alert-warning">
                    <strong>Peringatan:</strong>
                    <ul class="mb-0" style="padding-left: 20px;">`;
                data.errors.forEach(err => {
                    html += `<li>${err}</li>`;
                });
                html += `</ul></div>`;
            }
            
            resultDiv.innerHTML = html;
            
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">
                <strong>Gagal!</strong> ${data.message}
            </div>`;
        }
        
        document.querySelector('#modalImport .btn-success').disabled = false;
        document.querySelector('#modalImport .btn-success').innerHTML = '<i class="fas fa-upload"></i> Import';
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error);
        document.querySelector('#modalImport .btn-success').disabled = false;
        document.querySelector('#modalImport .btn-success').innerHTML = '<i class="fas fa-upload"></i> Import';
    });
}

// Reset Form
function resetForm() {
    document.getElementById('formGuru').reset();
    document.getElementById('id_guru').value = '';
    document.getElementById('modalTitleText').textContent = 'Tambah Guru';
    document.getElementById('passLabel').textContent = '*';
    document.getElementById('password').required = true;
    
    // Uncheck all kelas
    document.querySelectorAll('.kelas-checkbox').forEach(cb => cb.checked = false);
}

// Edit Guru
function editGuru(id) {
    fetch('get_guru.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showError(data.error);
                return;
            }
            
            document.getElementById('id_guru').value = data.id_guru;
            document.getElementById('nama_guru').value = data.nama_guru;
            document.getElementById('mapel').value = data.mapel;
            document.getElementById('username').value = data.username;
            
            // Uncheck all first
            document.querySelectorAll('.kelas-checkbox').forEach(cb => cb.checked = false);
            
            // Check kelas yang diajar
            if (data.kelas_ajar) {
                const kelasArr = data.kelas_ajar.split(', ');
                kelasArr.forEach(kelas => {
                    const checkbox = document.getElementById('kelas_' + kelas);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            document.getElementById('modalTitleText').textContent = 'Edit Guru';
            document.getElementById('passLabel').textContent = '(Kosongkan jika tidak diubah)';
            document.getElementById('password').required = false;
            document.getElementById('password').value = '';
            
            var myModal = new bootstrap.Modal(document.getElementById('modalGuru'));
            myModal.show();
        })
        .catch(error => {
            showError('Gagal memuat data guru!');
            console.error('Error:', error);
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>