<?php
session_start();
require 'koneksi.php';

// Jika user sudah login, langsung arahkan ke halaman utama
if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

$alert = '';

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek username di database menggunakan prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password hash
        if (password_verify($password, $user['password'])) {
            // Set session jika berhasil login
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: index.php");
            exit;
        } else {
            $alert = "<script>
                        Swal.fire({
                            title: 'Login Gagal!',
                            text: 'Password yang Anda masukkan salah.',
                            icon: 'error',
                            confirmButtonColor: '#fd7e14'
                        });
                      </script>";
        }
    } else {
        $alert = "<script>
                    Swal.fire({
                        title: 'Login Gagal!',
                        text: 'Username tidak ditemukan.',
                        icon: 'error',
                        confirmButtonColor: '#fd7e14'
                    });
                  </script>";
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
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-orange { background-color: #fd7e14 !important; color: white; }
        .btn-orange { background-color: #fd7e14; color: white; border: none; }
        .btn-orange:hover { background-color: #e86c0c; color: white; }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .input-group-text {
            background-color: #fd7e14;
            color: white;
            border: none;
        }
    </style>
</head>
<body>

<div class="card login-card border-0">
    <div class="card-header bg-orange text-center py-4" style="border-radius: 10px 10px 0 0;">
        <h4 class="mb-0"><i class="fa fa-database"></i> App Pendataan</h4>
        <small>Silakan login untuk melanjutkan</small>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-orange w-100 py-2 fw-bold">
                <i class="fa fa-sign-in"></i> Masuk
            </button>
        </form>
    </div>
</div>

<?= $alert; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>