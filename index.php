<?php
require 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$alert = '';

// --- PROSES HAPUS DATA ---
if (isset($_POST['hapus_data'])) {
    $id_hapus = $_POST['id_hapus'];
    
    // Keamanan tambahan: Pastikan data yang dihapus adalah milik user tersebut (kecuali admin)
    if ($_SESSION['role'] === 'admin') {
        $stmt_hapus = $conn->prepare("DELETE FROM data_pendataan WHERE id = ?");
        $stmt_hapus->bind_param("i", $id_hapus);
    } else {
        $stmt_hapus = $conn->prepare("DELETE FROM data_pendataan WHERE id = ? AND id_user = ?");
        $stmt_hapus->bind_param("ii", $id_hapus, $_SESSION['id_user']);
    }

    if ($stmt_hapus->execute()) {
        $alert = "<script>Swal.fire('Terhapus!', 'Data berhasil dihapus dari sistem.', 'success');</script>";
    } else {
        $alert = "<script>Swal.fire('Gagal!', 'Data gagal dihapus.', 'error');</script>";
    }
    $stmt_hapus->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Pendataan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fc; }
        .bg-gradient-orange { background: linear-gradient(135deg, #fd7e14 0%, #d96408 100%); color: white; }
        .btn-orange { background-color: #fd7e14; color: white; border-radius: 8px; transition: 0.3s; }
        .btn-orange:hover { background-color: #d96408; color: white; }
        .card-custom { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: none; }
        .table-custom th { background-color: #fd7e14 !important; color: white; border: none; }
        .table-custom td { vertical-align: middle; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-gradient-orange shadow-sm sticky-top py-3">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#"><i class="fa fa-database me-2"></i> App Pendataan</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item me-2">
                        <a href="tambah_user.php" class="btn btn-light btn-sm fw-bold text-dark rounded-pill px-3">
                            <i class="fa fa-users text-warning"></i> Kelola User
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item me-3 text-white fw-medium">
                    <i class="fa fa-user-circle-o fa-lg me-1"></i> <?= htmlspecialchars(ucfirst($_SESSION['username'])) ?> 
                    <span class="badge bg-white text-dark rounded-pill ms-1"><?= ucfirst($_SESSION['role']) ?></span>
                </li>
                <li class="nav-item">
                    <button onclick="konfirmasiLogout()" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fa fa-sign-out"></i> Logout
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="card card-custom p-3 p-md-4">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark"><i class="fa fa-list text-warning"></i> Daftar Data Pendataan</h4>
                <p class="text-muted small mb-0">Kelola dan pantau data pendataan Anda di sini.</p>
            </div>
            <a href="tambah_data.php" class="btn btn-orange fw-bold px-4 py-2 mt-3 mt-md-0 shadow-sm">
                <i class="fa fa-plus-circle me-1"></i> Tambah Data Baru
            </a>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <form method="GET" action="" class="mb-4 w-100 w-md-50">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-gradient-orange border-0"><i class="fa fa-filter"></i></span>
                <select name="filter_user" class="form-select border-0 bg-light" onchange="this.form.submit()">
                    <option value="">Semua Data Pengguna</option>
                    <?php
                    $user_query = $conn->query("SELECT id, username FROM users");
                    while($u = $user_query->fetch_assoc()){
                        $selected = (isset($_GET['filter_user']) && $_GET['filter_user'] == $u['id']) ? 'selected' : '';
                        echo "<option value='{$u['id']}' {$selected}>Oleh: {$u['username']}</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
        <?php endif; ?>

        <div class="table-responsive rounded-3 shadow-sm border">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr class="text-center">
                        <th width="5%">No</th>
                        <th width="15%">Tanggal</th>
                        <th width="30%" class="text-start">Nama & Kelas</th>
                        <th width="25%" class="text-start">Instansi & Kota</th>
                        <th width="25%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php
                    if ($_SESSION['role'] === 'admin') {
                        if (isset($_GET['filter_user']) && !empty($_GET['filter_user'])) {
                            $filter = $_GET['filter_user'];
                            $stmt_data = $conn->prepare("SELECT d.*, u.username FROM data_pendataan d LEFT JOIN users u ON d.id_user = u.id WHERE d.id_user = ? ORDER BY d.id DESC");
                            $stmt_data->bind_param("i", $filter);
                            $stmt_data->execute();
                            $result = $stmt_data->get_result();
                        } else {
                            $result = $conn->query("SELECT d.*, u.username FROM data_pendataan d LEFT JOIN users u ON d.id_user = u.id ORDER BY d.id DESC");
                        }
                    } else {
                        $id_user = $_SESSION['id_user'];
                        $stmt_data = $conn->prepare("SELECT d.*, u.username FROM data_pendataan d LEFT JOIN users u ON d.id_user = u.id WHERE d.id_user = ? ORDER BY d.id DESC");
                        $stmt_data->bind_param("i", $id_user);
                        $stmt_data->execute();
                        $result = $stmt_data->get_result();
                    }

                    $no = 1;
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                        <td class="text-center"><span class="badge bg-light text-dark border"><i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?></span></td>
                        <td>
                            <strong class="text-dark"><?= htmlspecialchars($row['nama']) ?></strong><br>
                            <small class="text-muted"><i class="fa fa-tag"></i> <?= htmlspecialchars($row['kelas']) ?></small>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <br><span class="badge bg-info mt-1"><i class="fa fa-user"></i> <?= htmlspecialchars($row['username']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="fw-medium text-dark"><?= htmlspecialchars($row['sekolah']) ?></span><br>
                            <small class="text-muted"><i class="fa fa-map-marker text-danger"></i> <?= htmlspecialchars($row['kota']) ?></small>
                        </td>
                        <td class="text-center">
                            <a href="https://wa.me/<?= htmlspecialchars($row['nomor_whatsapp']) ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 me-1 mb-1">
                                <i class="fa fa-whatsapp"></i> Chat
                            </a>
                            
                            <!-- Form Hapus Data -->
                            <form action="" method="POST" id="hapusForm<?= $row['id'] ?>" class="d-inline">
                                <input type="hidden" name="id_hapus" value="<?= $row['id'] ?>">
                                <input type="hidden" name="hapus_data" value="1">
                                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 mb-1" onclick="konfirmasiHapus(<?= $row['id'] ?>)">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="fa fa-folder-open-o fa-3x mb-2"></i><br>
                            Belum ada data pendataan yang tersedia.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $alert; ?>

<script>
    // JS Konfirmasi Hapus Data
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('hapusForm' + id).submit();
            }
        })
    }

    // JS Konfirmasi Logout
    function konfirmasiLogout() {
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: "Anda yakin ingin keluar dari sistem?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#fd7e14',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        })
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>