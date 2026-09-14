<?php
require 'koneksi.php';

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_admin'])) {
    $username = 'admin';
    $password_plain = 'admin123';
    
    // Hash password baru sesuai standar PHP 8.1
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
    $role = 'admin';

    // Cek apakah akun admin sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Jika ada, lakukan UPDATE password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $password_hash, $username);
        
        if ($update_stmt->execute()) {
            $alert = "<script>
                        Swal.fire({
                            title: 'Berhasil Reset!',
                            html: 'Password akun admin telah direset menjadi: <b>admin123</b>',
                            icon: 'success',
                            confirmButtonColor: '#fd7e14'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            }
                        });
                      </script>";
        }
        $update_stmt->close();
    } else {
        // Jika belum ada, lakukan INSERT data admin baru
        $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $username, $password_hash, $role);
        
        if ($insert_stmt->execute()) {
            $alert = "<script>
                        Swal.fire({
                            title: 'Berhasil Dibuat!',
                            html: 'Akun admin baru berhasil dibuat dengan password: <b>admin123</b>',
                            icon: 'success',
                            confirmButtonColor: '#fd7e14'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            }
                        });
                      </script>";
        }
        $insert_stmt->close();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .bg-orange { background-color: #fd7e14 !important; color: white; }
        .btn-orange { background-color: #fd7e14; color: white; border: none; }
        .btn-orange:hover { background-color: #e86c0c; color: white; }
    </style>
</head>
<body>

<div class="card border-0 shadow" style="max-width: 450px; width: 100%;">
    <div class="card-header bg-danger text-white text-center py-3">
        <h5 class="mb-0"><i class="fa fa-exclamation-triangle"></i> Darurat: Reset Admin</h5>
    </div>
    <div class="card-body p-4 text-center">
        <p class="text-muted mb-4">
            Klik tombol di bawah ini untuk mereset password akun <strong>admin</strong> menjadi bawaan pabrik (<kbd>admin123</kbd>). Jika akun terhapus, sistem akan otomatis membuatnya kembali.
        </p>
        <form method="POST" action="">
            <button type="submit" name="reset_admin" class="btn btn-orange w-100 fw-bold py-2">
                <i class="fa fa-refresh"></i> Reset Sekarang
            </button>
        </form>
        <div class="mt-3">
            <a href="login.php" class="text-decoration-none text-secondary"><i class="fa fa-arrow-left"></i> Kembali ke Login</a>
        </div>
    </div>
</div>

<?= $alert; ?>

</body>
</html>