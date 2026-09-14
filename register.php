<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

$alert = '';
$ref_code = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $referred_by = $_POST['referred_by'];
    
    // Auto Generate Kode Referral unik untuk pendaftar
    $kode_referral_baru = 'REF-' . strtoupper(substr(md5(uniqid()), 0, 6));

    $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $alert = "<script>Swal.fire('Gagal!', 'Username sudah digunakan, pilih yang lain.', 'warning');</script>";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'pegawai'; // Pendaftar default menjadi pegawai
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, nama_lengkap, kode_referral, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $hash, $role, $nama_lengkap, $kode_referral_baru, $referred_by);
        
        if ($stmt->execute()) {
            header("Location: login.php?pesan=register_sukses");
            exit;
        } else {
            $alert = "<script>Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .reg-card { border-radius: 24px; box-shadow: 0 20px 50px rgba(253, 126, 20, 0.15); border: none; max-width: 500px; width: 100%; background: #ffffff; }
        .btn-orange { background: linear-gradient(135deg, #fd7e14 0%, #ff5722 100%); color: white; border-radius: 12px; font-weight: 600; border: none; padding: 12px; transition: 0.3s; }
        .btn-orange:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(253, 126, 20, 0.3); color: white; }
        .form-control { border-radius: 12px; border: 1px solid #e0e0e0; padding: 12px 15px; }
        .form-control:focus { border-color: #fd7e14; box-shadow: 0 0 0 0.25rem rgba(253, 126, 20, 0.15); }
    </style>
</head>
<body>

<div class="card reg-card p-4 p-md-5">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark mb-1">Buat Akun Baru</h3>
        <p class="text-muted small">Lengkapi data Anda di bawah ini</p>
    </div>
    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label text-muted fw-semibold small">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap Anda" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted fw-semibold small">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Untuk login" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted fw-semibold small">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>
        
        <div class="mb-4 p-3 rounded" style="background-color: #fff9f5; border: 1px dashed #fd7e14;">
            <label class="form-label fw-bold small" style="color: #fd7e14;"><i class="fa fa-ticket me-1"></i> Kode Referral (Opsional)</label>
            <input type="text" name="referred_by" class="form-control border-warning bg-white" placeholder="Masukkan kode jika ada" value="<?= $ref_code ?>">
        </div>
        
        <button type="submit" name="register" class="btn btn-orange w-100 mb-3">DAFTAR SEKARANG</button>
        
        <div class="text-center mt-3">
            <span class="text-muted small">Sudah punya akun? </span>
            <a href="login.php" class="text-decoration-none fw-bold" style="color: #fd7e14;">Masuk di sini</a>
        </div>
    </form>
</div>

<?= $alert; ?>
</body>
</html>