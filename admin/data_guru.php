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
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGuru">
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
                            <a href="?edit=<?php echo $row['id_guru']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete('?delete=<?php echo $row['id_guru']; ?>')" class="btn btn-sm btn-danger">
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
                    <?php echo $edit_data ? 'Edit Guru' : 'Tambah Guru'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_guru" value="<?php echo $edit_data['id_guru']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="nama_guru" class="form-control" value="<?php echo $edit_data['nama_guru'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran *</label>
                        <input type="text" name="mapel" class="form-control" placeholder="Contoh: Matematika" value="<?php echo $edit_data['mapel'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" value="<?php echo $edit_data['username'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <?php echo $edit_data ? '(Kosongkan jika tidak diubah)' : '*'; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo !$edit_data ? 'required' : ''; ?>>
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

<?php if ($edit_data): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('modalGuru'));
    myModal.show();
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>