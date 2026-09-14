<?php
require 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pendataan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        <a class="navbar-brand text-white fw-bold" href="#"><i class="fa fa-database"></i> App Pendataan</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="d-flex align-items-center mt-2 mt-lg-0">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="tambah_user.php" class="btn btn-light btn-sm me-3">
                        <i class="fa fa-user-plus"></i> Kelola User
                    </a>
                <?php endif; ?>
                
                <span class="navbar-text text-white me-3">
                    <i class="fa fa-user-circle-o"></i> 
                    Hai, <?= htmlspecialchars(ucfirst($_SESSION['username'])) ?> 
                    <span class="badge bg-light text-dark ms-1"><?= ucfirst($_SESSION['role']) ?></span>
                </span>
                
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Kolom Tabel Menampilkan Data (Sekarang 100% Lebar) -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    
                    <!-- Header Tabel & Tombol Tambah Data -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0"><i class="fa fa-list"></i> Daftar Pendataan</h5>
                        <a href="tambah_data.php" class="btn btn-orange fw-bold">
                            <i class="fa fa-plus-circle"></i> Tambah Data
                        </a>
                    </div>

                    <!-- FITUR FILTER KHUSUS ADMIN -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <form method="GET" action="" class="mb-3 w-50">
                        <div class="input-group">
                            <span class="input-group-text bg-orange text-white border-0"><i class="fa fa-filter"></i> Filter User</span>
                            <select name="filter_user" class="form-select shadow-none" onchange="this.form.submit()">
                                <option value="">-- Tampilkan Semua Data --</option>
                                <?php
                                $user_query = $conn->query("SELECT id, username FROM users");
                                while($u = $user_query->fetch_assoc()){
                                    $selected = (isset($_GET['filter_user']) && $_GET['filter_user'] == $u['id']) ? 'selected' : '';
                                    echo "<option value='{$u['id']}' {$selected}>{$u['username']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="bg-orange text-white">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="30%">Nama & Kelas</th>
                                    <th width="30%">Sekolah & Kota</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // LOGIKA PEMISAHAN DATA
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
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($row['kelas']) ?></span>
                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <br><small class="text-primary"><i class="fa fa-user"></i> Oleh: <?= htmlspecialchars($row['username'] ?? 'Tidak diketahui') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['sekolah']) ?><br>
                                        <small class="text-muted"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($row['kota']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <a href="https://wa.me/<?= htmlspecialchars($row['nomor_whatsapp']) ?>" target="_blank" class="btn btn-success btn-sm">
                                            <i class="fa fa-whatsapp"></i> Chat WA
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada data pendataan.</td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>