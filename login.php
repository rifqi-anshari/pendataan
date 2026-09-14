<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

$alert = '';
if (isset($_GET['pesan']) && $_GET['pesan'] == 'logout') {
    $alert = "<script>Swal.fire({ title: 'Berhasil Logout!', text: 'Anda telah keluar dari sistem.', icon: 'success', timer: 2000, showConfirmButton: false });</script>";
}
if (isset($_GET['pesan']) && $_GET['pesan'] == 'register_sukses') {
    $alert = "<script>Swal.fire({ title: 'Pendaftaran Berhasil!', text: 'Silakan login dengan akun baru Anda.', icon: 'success' });</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $alert = "<script>Swal.fire('Gagal!', 'Password yang Anda masukkan salah.', 'error');</script>";
        }
    } else {
        $alert = "<script>Swal.fire('Gagal!', 'Username tidak ditemukan.', 'error');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Pendataan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { border-radius: 24px; box-shadow: 0 20px 50px rgba(253, 126, 20, 0.15); border: none; max-width: 420px; width: 100%; background: #ffffff; }
        .icon-circle { width: 80px; height: 80px; background: linear-gradient(135deg, #fd7e14 0%, #ff5722 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; box-shadow: 0 10px 20px rgba(253, 126, 20, 0.3); }
        .btn-orange { background: linear-gradient(135deg, #fd7e14 0%, #ff5722 100%); color: white; border-radius: 12px; font-weight: 600; border: none; padding: 12px; transition: 0.3s; }
        .btn-orange:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(253, 126, 20, 0.3); color: white; }
        .form-control { border-radius: 12px; border: 1px solid #e0e0e0; padding: 12px 15px; }
        .form-control:focus { border-color: #fd7e14; box-shadow: 0 0 0 0.25rem rgba(253, 126, 20, 0.15); }
    </style>
</head>
<body>

<div class="card login-card p-4 p-md-5">
    <div class="text-center mb-4">
        <div class="icon-circle"><i class="fa fa-database fa-2x"></i></div>
        <h3 class="fw-bold text-dark mb-1">Selamat Datang</h3>
        <p class="text-muted small">Aplikasi Pendataan Digital</p>
    </div>
    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label text-muted fw-semibold small"><i class="fa fa-user me-1"></i> Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label text-muted fw-semibold small"><i class="fa fa-lock me-1"></i> Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" name="login" class="btn btn-orange w-100 mb-3">
            MASUK SEKARANG <i class="fa fa-arrow-right ms-2"></i>
        </button>
        <div class="text-center mt-3">
            <span class="text-muted small">Belum punya akun? </span>
            <a href="register.php" class="text-decoration-none fw-bold" style="color: #fd7e14;">Daftar Sekarang</a>
        </div>
    </form>
</div>

<?= $alert; ?>
</body>
</html>