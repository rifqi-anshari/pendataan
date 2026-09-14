<?php
require 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$alert = '';

// Proses Tambah Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_data'])) {
    $id_user = $_SESSION['id_user'];
    $tanggal = $_POST['tanggal'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $sekolah = $_POST['sekolah'];
    $nomor_whatsapp = $_POST['nomor_whatsapp'];
    $kota = $_POST['kota'];

    $stmt = $conn->prepare("INSERT INTO data_pendataan (id_user, tanggal, nama, kelas, sekolah, nomor_whatsapp, kota) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $id_user, $tanggal, $nama, $kelas, $sekolah, $nomor_whatsapp, $kota);

    if ($stmt->execute()) {
        $alert = "<script>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Data pendataan berhasil ditambahkan.',
                        icon: 'success',
                        confirmButtonColor: '#fd7e14'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'index.php';
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data - Aplikasi Pendataan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
            <a href="index.php" class="btn btn-light btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-orange text-white text-center py-3">
                    <h5 class="mb-0"><i class="fa fa-plus-circle"></i> Form Tambah Pendataan</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kelas</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Contoh: XII IPA 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sekolah</label>
                            <input type="text" name="sekolah" class="form-control" placeholder="Nama Sekolah" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-whatsapp"></i></span>
                                <input type="number" name="nomor_whatsapp" class="form-control" placeholder="08..." required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Kota</label>
                            <input type="text" name="kota" class="form-control" placeholder="Asal kota" required>
                        </div>
                        <button type="submit" name="tambah_data" class="btn btn-orange w-100 fw-bold py-2">
                            <i class="fa fa-save"></i> Simpan Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $alert; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>