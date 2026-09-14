<?php
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$alert = '';

if (isset($_POST['hapus_user'])) {
    $id_hapus = $_POST['id_hapus'];
    if ($id_hapus == $_SESSION['id_user']) {
        $alert = "<script>Swal.fire('Ditolak!', 'Anda tidak bisa menghapus akun Anda sendiri.', 'warning');</script>";
    } else {
        $stmt_hapus = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_hapus->bind_param("i", $id_hapus);
        if ($stmt_hapus->execute()) $alert = "<script>Swal.fire('Terhapus!', 'Pengguna berhasil dihapus.', 'success');</script>";
    }
}

if (isset($_POST['tambah_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $alert = "<script>Swal.fire('Gagal!', 'Username sudah digunakan!', 'warning');</script>";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hash, $role);
        if ($stmt->execute()) $alert = "<script>Swal.fire('Berhasil!', 'User baru ditambahkan.', 'success').then(()=>window.location='tambah_user.php');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - Pendataan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .card-premium { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none; background: #fff; padding: 1.5rem; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #eaeaea; background-color: #fafbfe; }
        .form-control:focus, .form-select:focus { background-color: #fff; border-color: #fd7e14; box-shadow: 0 0 0 4px rgba(253, 126, 20, 0.1); }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #6c757d; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .btn-gradient { background: linear-gradient(135deg, #fd7e14, #ff5722); color: white; border-radius: 12px; font-weight: 600; transition: 0.3s; border: none; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(253, 126, 20, 0.3); color: white; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-arrow-left text-dark"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">Manajemen Pengguna</h4>
            <p class="text-muted small mb-0">Halaman khusus Administrator.</p>
        </div>
    </div>

    <div class="row">
        <!-- Form Tambah User -->
        <div class="col-lg-4 mb-4">
            <div class="card card-premium">
                <h6 class="fw-bold mb-4 border-bottom pb-3"><i class="fa fa-user-plus text-primary me-2"></i>Buat Akun Baru</h6>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Akses / Role</label>
                        <select name="role" class="form-select" required>
                            <option value="pegawai">Pegawai Biasa</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <button type="submit" name="tambah_user" class="btn btn-gradient w-100 py-3"><i class="fa fa-check me-2"></i> Tambahkan Akun</button>
                </form>
            </div>
        </div>

        <!-- Tabel User -->
        <div class="col-lg-8">
            <div class="card card-premium">
                <h6 class="fw-bold mb-4 border-bottom pb-3"><i class="fa fa-users text-primary me-2"></i>Daftar Pengguna Sistem</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-top-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th width="10%" class="py-3">No</th>
                                <th class="py-3">Info Pengguna</th>
                                <th class="text-center py-3">Hak Akses</th>
                                <th class="text-end py-3 px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php
                            $res = $conn->query("SELECT * FROM users ORDER BY role ASC, id DESC");
                            $no=1; while($row = $res->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['username']) ?>&background=random&color=fff&rounded=true" width="35" class="me-3 shadow-sm">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($row['username']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($row['role'] == 'admin') echo '<span class="badge bg-danger rounded-pill px-3 shadow-sm">Admin</span>'; else echo '<span class="badge bg-primary rounded-pill px-3 shadow-sm">Pegawai</span>'; ?>
                                </td>
                                <td class="text-end px-4">
                                    <?php if($row['id'] != $_SESSION['id_user']): ?>
                                    <form method="POST" class="d-inline" id="hapusUser<?= $row['id'] ?>">
                                        <input type="hidden" name="id_hapus" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="hapus_user" value="1">
                                        <button type="button" onclick="hapusUser(<?= $row['id'] ?>)" class="btn btn-light text-danger btn-sm rounded-circle shadow-sm" style="width:35px; height:35px;"><i class="fa fa-trash"></i></button>
                                    </form>
                                    <?php else: ?>
                                        <span class="badge bg-light text-success border px-3 py-2 rounded-pill"><i class="fa fa-circle text-success me-1"></i> Aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $alert; ?>
<script>
function hapusUser(id) {
    Swal.fire({
        title: 'Cabut Akses?', text: "Akun ini tidak akan bisa login lagi!", icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#e74c3c', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
    }).then((result) => { if (result.isConfirmed) document.getElementById('hapusUser'+id).submit(); })
}
</script>
</body>
</html>