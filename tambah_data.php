<?php
require 'koneksi.php';
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit; }
$alert = '';

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
            Swal.fire({ title: 'Tersimpan!', text: 'Data berhasil ditambahkan.', icon: 'success', confirmButtonColor: '#fd7e14' })
            .then((result) => { if (result.isConfirmed) { window.location.href = 'index.php'; } });
        </script>";
    } else {
        $alert = "<script>Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data - Pendataan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fc; }
        .bg-gradient-orange { background: linear-gradient(135deg, #fd7e14 0%, #d96408 100%); color: white; }
        .btn-orange { background-color: #fd7e14; color: white; border-radius: 8px; font-weight: bold; }
        .btn-orange:hover { background-color: #d96408; color: white; }
        .card-custom { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-custom overflow-hidden">
                <div class="card-header bg-gradient-orange text-center py-4 border-0">
                    <h4 class="mb-0 fw-bold"><i class="fa fa-pencil-square-o"></i> Form Tambah Data</h4>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted fw-bold small">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted fw-bold small">Nomor WhatsApp</label>
                                <input type="number" name="nomor_whatsapp" class="form-control rounded-3" placeholder="Contoh: 0812..." required>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="nama" class="form-control rounded-3" id="nama" placeholder="Nama" required>
                            <label for="nama">Nama Lengkap</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="kelas" class="form-control rounded-3" id="kelas" placeholder="Kelas" required>
                            <label for="kelas">Kelas (Contoh: XII IPA)</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="sekolah" class="form-control rounded-3" id="sekolah" placeholder="Instansi" required>
                            <label for="sekolah">Nama Sekolah / Instansi</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" name="kota" class="form-control rounded-3" id="kota" placeholder="Kota" required>
                            <label for="kota">Asal Kota</label>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-light w-50 py-2 border rounded-3 text-secondary fw-bold">
                                <i class="fa fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" name="tambah_data" class="btn btn-orange w-50 py-2 rounded-3">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $alert; ?>
</body>
</html>