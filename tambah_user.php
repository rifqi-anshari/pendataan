<?php
require 'koneksi.php';

// Proteksi halaman: Jika yang login BUKAN admin, kembalikan ke halaman utama
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$alert = '';

// Proses Tambah User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $role = $_POST['role'];

    // Cek apakah username sudah ada di database
    $cek_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $cek_stmt->bind_param("s", $username);
    $cek_stmt->execute();
    $cek_stmt->store_result();

    if ($cek_stmt->num_rows > 0) {
        // Jika username sudah dipakai
        $alert = "<script>
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Username sudah digunakan, silakan pilih yang lain.',
                        icon: 'warning',
                        confirmButtonColor: '#fd7e14'
                    });
                  </script>";
    } else {
        // Jika username tersedia, hash password dan simpan
        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password_hash, $role);

        if ($stmt->execute()) {
            $alert = "<script>
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'User baru berhasil ditambahkan.',
                            icon: 'success',
                            confirmButtonColor: '#fd7e14'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'tambah_user.php';
                            }
                        });
                      </script>";
        } else {
            $alert = "<script>
                        Swal.fire({
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan sistem saat menyimpan data.',
                            icon: 'error'
                        });
                      </script>";
        }
        $stmt->close();
    }
    $cek_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Aplikasi Pendataan</title>
    <!-- Framework Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icon FontAwesome 4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .bg-orange { background-color: #fd7e14 !important; color: white; }
        .btn-orange { background-color: #fd7e14; color: white; border: none; }
        .btn-orange:hover { background-color: #e86c0c; color: white; }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-orange shadow-sm">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php"><i class="fa fa-database"></i> App Pendataan</a>
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-light btn-sm me-3">
                <i class="fa fa-home"></i> Kembali ke Beranda
            </a>
            <span class="navbar-text text-white me-3">
                <i class="fa fa-user-circle-o"></i> Admin: <?= htmlspecialchars($_SESSION['username']) ?>
            </span>
            <a href="logout.php" class="btn btn-danger btn-sm">
                <i class="fa fa-sign-out"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Kolom Form Tambah User -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-orange text-white">
                    <h5 class="mb-0"><i class="fa fa-user-plus"></i> Tambah User Baru</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Role Akses</label>
                            <select name="role" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Role --</option>
                                <option value="pegawai">Pegawai</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" name="tambah_user" class="btn btn-orange w-100 fw-bold">
                            <i class="fa fa-save"></i> Simpan User
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Tabel Daftar User -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa fa-users"></i> Daftar Pengguna Sistem</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="bg-orange text-white">
                                <tr>
                                    <th class="text-center" width="10%">No</th>
                                    <th>Username</th>
                                    <th class="text-center">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
                                $no = 1;
                                
                                if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['username']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['role'] === 'admin'): ?>
                                            <span class="badge bg-danger"><i class="fa fa-star"></i> Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary"><i class="fa fa-user"></i> Pegawai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Belum ada user terdaftar.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $alert; // Menjalankan popup SweetAlert jika ada trigger ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>