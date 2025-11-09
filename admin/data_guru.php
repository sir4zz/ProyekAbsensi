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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        echo "<script>showSuccess('Data guru berhasil diupdate!'); setTimeout(() => window.location.href='data_guru.php', 1500);</script>";
    } else {
        // Insert
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO guru (nama_guru, username, password, mapel) VALUES ('$nama', '$username', '$hashed', '$mapel')";
        $conn->query($sql);
        echo "<script>showSuccess('Data guru berhasil ditambahkan!'); setTimeout(() => window.location.href='data_guru.php', 1500);</script>";
    }
}

// Get Edit Data
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM guru WHERE id_guru = $id");
    $edit_data = $result->fetch_assoc();
}

// Get All Guru
$guru = $conn->query("SELECT * FROM guru ORDER BY nama_guru ASC");
?>

<div class="table-card">
    <div class="table-header">
        <h5><i class="fas fa-chalkboard-teacher"></i> Daftar Guru</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGuru" onclick="resetForm()">
            <i class="fas fa-plus"></i> Tambah Guru
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Guru</th>
                    <th>Mata Pelajaran</th>
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

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalGuru" tabindex="-1">
    <div class="modal-dialog">
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
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span id="passLabel">*</span></label>
                        <input type="password" name="password" id="password" class="form-control">
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
// Reset Form untuk Tambah Baru
function resetForm() {
    document.getElementById('formGuru').reset();
    document.getElementById('id_guru').value = '';
    document.getElementById('modalTitleText').textContent = 'Tambah Guru';
    document.getElementById('passLabel').textContent = '*';
    document.getElementById('password').required = true;
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