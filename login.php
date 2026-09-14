<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

$alert = '';

// Notifikasi jika redirect dari logout
if (isset($_GET['pesan']) && $_GET['pesan'] == 'logout') {
    $alert = "<script>Swal.fire({ title: 'Berhasil Logout!', text: 'Anda telah keluar dari sistem.', icon: 'success', timer: 2000, showConfirmButton: false });</script>";
}

// Proses Login
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none; overflow: hidden; max-width: 420px; width: 100%; }
        .bg-gradient-orange { background: linear-gradient(135deg, #fd7e14 0%, #d96408 100%); color: white; }
        .btn-orange { background-color: #fd7e14; color: white; border-radius: 10px; font-weight: bold; transition: all 0.3s; }
        .btn-orange:hover { background-color: #d96408; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(253, 126, 20, 0.4); }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header bg-gradient-orange text-center py-5 border-0">
        <i class="fa fa-database fa-3x mb-3"></i>
        <h3 class="mb-0 fw-bold">App Pendataan</h3>
        <p class="text-white-50 mb-0">Silakan masuk ke akun Anda</p>
    </div>
    <div class="card-body p-4 p-md-5 bg-white">
        <form method="POST" action="">
            <div class="form-floating mb-3">
                <input type="text" name="username" class="form-control rounded-3" id="floatingInput" placeholder="Username" required autofocus>
                <label for="floatingInput"><i class="fa fa-user text-muted"></i> Username</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control rounded-3" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword"><i class="fa fa-lock text-muted"></i> Password</label>
            </div>
            <button type="submit" name="login" class="btn btn-orange w-100 py-3">
                <i class="fa fa-sign-in"></i> MASUK SEKARANG
            </button>
        </form>
    </div>
</div>

<?= $alert; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>