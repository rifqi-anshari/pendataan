<?php
require 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$alert = '';

// Proses Hapus User
if (isset($_POST['hapus_user'])) {
    $id_hapus = $_POST['id_hapus'];
    // Cegah admin menghapus dirinya sendiri saat login
    if ($id_hapus == $_SESSION['id_user']) {
        $alert = "<script>Swal.fire('Ditolak!', 'Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif.', 'warning');</script>";
    } else {
        $stmt_hapus = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_hapus->bind_param("i", $id_hapus);
        if ($stmt_hapus->execute()) {
            $alert = "<script>Swal.fire('Terhapus!', 'Pengguna berhasil dihapus.', 'success');</script>";
        }
    }
}

// Proses Tambah User
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
        if ($stmt->execute()) {
            $alert = "<script>Swal.fire('Berhasil!', 'User baru ditambahkan.', 'success').then(()=>window.location='tambah_user.php');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fc; }
        .bg-gradient-orange { background: linear-gradient(135deg, #fd7e14 0%, #d96408 100%); color: white; }
        .btn-orange { background-color: #fd7e14; color: white; border-radius: 8px; }
        .btn-orange:hover { background-color: #d96408; color: white; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-gradient-orange shadow-sm py-3 mb-4">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php"><i class="fa fa-arrow-left me-2"></i> Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <!-- Form Tambah User -->
        <div class="col-md-4 mb-4">
            <div class="card card-custom">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold"><i class="fa fa-user-plus text-warning"></i> Tambah User</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="form-floating mb-3">
                            <input type="text" name="username" class="form-control rounded-3" id="u" placeholder="Username" required>
                            <label for="u">Username Baru</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control rounded-3" id="p" placeholder="Password" required>
                            <label for="p">Password</label>
                        </div>
                        <div class="form-floating mb-4">
                            <select name="role" class="form-select rounded-3" id="r" required>
                                <option value="pegawai">Pegawai</option>
                                <option value="admin">Admin</option>
                            </select>
                            <label for="r">Hak Akses</label>
                        </div>
                        <button type="submit" name="tambah_user" class="btn btn-orange w-100 fw-bold py-2"><i class="fa fa-check"></i> Buat Akun</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel User -->
        <div class="col-md-8">
            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="fa fa-users text-warning"></i> Daftar Sistem User</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th width="10%">No</th>
                                <th>Username</th>
                                <th class="text-center">Role</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT * FROM users ORDER BY role ASC, id DESC");
                            $no=1; while($row = $res->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= $no++ ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['username']) ?></td>
                                <td class="text-center">
                                    <?php if($row['role'] == 'admin') echo '<span class="badge bg-danger rounded-pill px-3">Admin</span>'; else echo '<span class="badge bg-primary rounded-pill px-3">Pegawai</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <?php if($row['id'] != $_SESSION['id_user']): ?>
                                    <form method="POST" class="d-inline" id="hapusUser<?= $row['id'] ?>">
                                        <input type="hidden" name="id_hapus" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="hapus_user" value="1">
                                        <button type="button" onclick="hapusUser(<?= $row['id'] ?>)" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fa fa-trash"></i></button>
                                    </form>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Anda (Aktif)</span>
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
        title: 'Hapus Akses?', text: "Akun ini tidak akan bisa login lagi!", icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus'
    }).then((result) => { if (result.isConfirmed) document.getElementById('hapusUser'+id).submit(); })
}
</script>
</body>
</html>