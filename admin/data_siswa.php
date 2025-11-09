<?php
$page_title = 'Data Siswa';
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM siswa WHERE id_siswa = $id");
    echo "<script>showSuccess('Data siswa berhasil dihapus!'); setTimeout(() => window.location.href='data_siswa.php', 1500);</script>";
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
    $nama = sanitize($_POST['nama_siswa']);
    $nis = sanitize($_POST['nis']);
    $kelas = sanitize($_POST['kelas']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $jk = sanitize($_POST['jenis_kelamin']);
    $tempat_lahir = sanitize($_POST['tempat_lahir']);
    $tanggal_lahir = sanitize($_POST['tanggal_lahir']);
    $alamat = sanitize($_POST['alamat']);
    $no_telp = sanitize($_POST['no_telp']);
    $email = sanitize($_POST['email']);
    $nama_wali = sanitize($_POST['nama_wali']);
    $no_telp_wali = sanitize($_POST['no_telp_wali']);
    
    if ($id > 0) {
        // Update
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE siswa SET 
                    nama_siswa = '$nama', 
                    nis = '$nis', 
                    kelas = '$kelas', 
                    username = '$username', 
                    password = '$hashed',
                    jenis_kelamin = '$jk',
                    tempat_lahir = '$tempat_lahir',
                    tanggal_lahir = '$tanggal_lahir',
                    alamat = '$alamat',
                    no_telp = '$no_telp',
                    email = '$email',
                    nama_wali = '$nama_wali',
                    no_telp_wali = '$no_telp_wali'
                    WHERE id_siswa = $id";
        } else {
            $sql = "UPDATE siswa SET 
                    nama_siswa = '$nama', 
                    nis = '$nis', 
                    kelas = '$kelas', 
                    username = '$username',
                    jenis_kelamin = '$jk',
                    tempat_lahir = '$tempat_lahir',
                    tanggal_lahir = '$tanggal_lahir',
                    alamat = '$alamat',
                    no_telp = '$no_telp',
                    email = '$email',
                    nama_wali = '$nama_wali',
                    no_telp_wali = '$no_telp_wali'
                    WHERE id_siswa = $id";
        }
        $conn->query($sql);
        echo "<script>showSuccess('Data siswa berhasil diupdate!'); setTimeout(() => window.location.href='data_siswa.php', 1500);</script>";
    } else {
        // Insert
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO siswa (nama_siswa, nis, kelas, username, password, jenis_kelamin, 
                tempat_lahir, tanggal_lahir, alamat, no_telp, email, nama_wali, no_telp_wali) 
                VALUES ('$nama', '$nis', '$kelas', '$username', '$hashed', '$jk', 
                '$tempat_lahir', '$tanggal_lahir', '$alamat', '$no_telp', '$email', '$nama_wali', '$no_telp_wali')";
        $conn->query($sql);
        echo "<script>showSuccess('Data siswa berhasil ditambahkan!'); setTimeout(() => window.location.href='data_siswa.php', 1500);</script>";
    }
}

// Get Edit Data
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM siswa WHERE id_siswa = $id");
    $edit_data = $result->fetch_assoc();
}

// Get All Siswa
$siswa = $conn->query("SELECT * FROM siswa ORDER BY nama_siswa ASC");
?>

<div class="table-card">
    <div class="table-header">
        <h5><i class="fas fa-user-graduate"></i> Daftar Siswa</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSiswa" onclick="resetForm()">
            <i class="fas fa-plus"></i> Tambah Siswa
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>JK</th>
                    <th>No. Telp</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = $siswa->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nis']; ?></td>
                        <td><?php echo $row['nama_siswa']; ?></td>
                        <td><span class="badge bg-primary"><?php echo $row['kelas']; ?></span></td>
                        <td><?php echo $row['jenis_kelamin']; ?></td>
                        <td><?php echo $row['no_telp']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td>
                            <button onclick="editSiswa(<?php echo $row['id_siswa']; ?>)" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete('?delete=<?php echo $row['id_siswa']; ?>', 'Hapus siswa <?php echo $row['nama_siswa']; ?>?')" class="btn btn-sm btn-danger" title="Hapus">
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
<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-yellow); color: var(--primary-black);">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-user-plus"></i> 
                    <span id="modalTitleText">Tambah Siswa</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="formSiswa">
                <div class="modal-body">
                    <input type="hidden" name="id_siswa" id="id_siswa">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_siswa" id="nama_siswa" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIS *</label>
                            <input type="text" name="nis" id="nis" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kelas *</label>
                            <input type="text" name="kelas" id="kelas" class="form-control" placeholder="X-1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                <option value="">Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telp" id="no_telp" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control">
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Data Wali/Orang Tua</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Wali</label>
                            <input type="text" name="nama_wali" id="nama_wali" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon Wali</label>
                            <input type="text" name="no_telp_wali" id="no_telp_wali" class="form-control">
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Akun Login</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span id="passLabel">(Kosongkan jika tidak diubah)</span></label>
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
// Reset Form untuk Tambah Baru
function resetForm() {
    document.getElementById('formSiswa').reset();
    document.getElementById('id_siswa').value = '';
    document.getElementById('modalTitleText').textContent = 'Tambah Siswa';
    document.getElementById('passLabel').textContent = '*';
    document.getElementById('password').required = true;
}

// Edit Siswa
function editSiswa(id) {
    fetch('get_siswa.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('id_siswa').value = data.id_siswa;
            document.getElementById('nama_siswa').value = data.nama_siswa;
            document.getElementById('nis').value = data.nis;
            document.getElementById('kelas').value = data.kelas;
            document.getElementById('jenis_kelamin').value = data.jenis_kelamin;
            document.getElementById('tempat_lahir').value = data.tempat_lahir || '';
            document.getElementById('tanggal_lahir').value = data.tanggal_lahir || '';
            document.getElementById('alamat').value = data.alamat || '';
            document.getElementById('no_telp').value = data.no_telp || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('nama_wali').value = data.nama_wali || '';
            document.getElementById('no_telp_wali').value = data.no_telp_wali || '';
            document.getElementById('username').value = data.username;
            
            document.getElementById('modalTitleText').textContent = 'Edit Siswa';
            document.getElementById('passLabel').textContent = '(Kosongkan jika tidak diubah)';
            document.getElementById('password').required = false;
            
            var myModal = new bootstrap.Modal(document.getElementById('modalSiswa'));
            myModal.show();
        })
        .catch(error => {
            showError('Gagal memuat data siswa!');
            console.error('Error:', error);
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>