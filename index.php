<?php
require 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// PROSES EXPORT CSV
// ==========================================
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Data_Pendataan_'.date('Ymd').'.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('No', 'Tanggal', 'Nama', 'Kelas', 'Sekolah', 'No WhatsApp', 'Kota'));

    if ($_SESSION['role'] === 'admin') {
        if (isset($_GET['filter_user']) && !empty($_GET['filter_user'])) {
            $filter = $_GET['filter_user'];
            $stmt = $conn->prepare("SELECT * FROM data_pendataan WHERE id_user = ? ORDER BY id DESC");
            $stmt->bind_param("i", $filter);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $conn->query("SELECT * FROM data_pendataan ORDER BY id DESC");
        }
    } else {
        $id_user = $_SESSION['id_user'];
        $stmt = $conn->prepare("SELECT * FROM data_pendataan WHERE id_user = ? ORDER BY id DESC");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $res = $stmt->get_result();
    }

    $no_csv = 1;
    while ($row = $res->fetch_assoc()) {
        fputcsv($output, array($no_csv++, date('d-m-Y', strtotime($row['tanggal'])), $row['nama'], $row['kelas'], $row['sekolah'], $row['nomor_whatsapp'], $row['kota']));
    }
    fclose($output);
    exit; 
}

$alert = '';

// ==========================================
// PROSES TAMBAH DATA (MODAL)
// ==========================================
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
        $alert = "<script>Swal.fire({ title: 'Berhasil!', text: 'Data telah tersimpan di sistem.', icon: 'success', confirmButtonColor: '#fd7e14' }).then((r) => { if (r.isConfirmed) window.location.href = 'index.php'; });</script>";
    } else {
        $alert = "<script>Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');</script>";
    }
    $stmt->close();
}

// ==========================================
// PROSES HAPUS DATA
// ==========================================
if (isset($_POST['hapus_data'])) {
    $id_hapus = $_POST['id_hapus'];
    if ($_SESSION['role'] === 'admin') {
        $stmt_hapus = $conn->prepare("DELETE FROM data_pendataan WHERE id = ?");
        $stmt_hapus->bind_param("i", $id_hapus);
    } else {
        $stmt_hapus = $conn->prepare("DELETE FROM data_pendataan WHERE id = ? AND id_user = ?");
        $stmt_hapus->bind_param("ii", $id_hapus, $_SESSION['id_user']);
    }
    if ($stmt_hapus->execute()) {
        $alert = "<script>Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');</script>";
    }
    $stmt_hapus->close();
}

// ==========================================
// KONFIGURASI PAGINATION
// ==========================================
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = 1;

if ($_SESSION['role'] === 'admin') {
    if (isset($_GET['filter_user']) && !empty($_GET['filter_user'])) {
        $filter = $_GET['filter_user'];
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM data_pendataan WHERE id_user = ?");
        $stmt_count->bind_param("i", $filter);
        $stmt_count->execute();
        $total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
        
        $stmt_data = $conn->prepare("SELECT d.*, u.username FROM data_pendataan d LEFT JOIN users u ON d.id_user = u.id WHERE d.id_user = ? ORDER BY d.id DESC LIMIT ? OFFSET ?");
        $stmt_data->bind_param("iii", $filter, $limit, $offset);
        $stmt_data->execute();
        $result = $stmt_data->get_result();
    } else {
        $total_rows = $conn->query("SELECT COUNT(*) as total FROM data_pendataan")->fetch_assoc()['total'];
        $stmt_data = $conn->prepare("SELECT d.*, u.username FROM data_pendataan d LEFT JOIN users u ON d.id_user = u.id ORDER BY d.id DESC LIMIT ? OFFSET ?");
        $stmt_data->bind_param("ii", $limit, $offset);
        $stmt_data->execute();
        $result = $stmt_data->get_result();
    }
} else {
    $id_user = $_SESSION['id_user'];
    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM data_pendataan WHERE id_user = ?");
    $stmt_count->bind_param("i", $id_user);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
    
    $stmt_data = $conn->prepare("SELECT d.*, u.username FROM data_pendataan d LEFT JOIN users u ON d.id_user = u.id WHERE d.id_user = ? ORDER BY d.id DESC LIMIT ? OFFSET ?");
    $stmt_data->bind_param("iii", $id_user, $limit, $offset);
    $stmt_data->execute();
    $result = $stmt_data->get_result();
}
$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Pendataan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #2d3436; }
        
        /* Glassmorphism Navbar */
        .navbar-glass { background: rgba(253, 126, 20, 0.9) !important; backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); }
        
        /* Premium Card */
        .card-premium { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none; background: #fff; padding: 1.5rem; }
        
        /* Buttons */
        .btn-gradient { background: linear-gradient(135deg, #fd7e14 0%, #ff5722 100%); color: white; border: none; border-radius: 10px; font-weight: 500; transition: 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(253, 126, 20, 0.3); color: white; }
        .btn-action { border-radius: 10px; font-weight: 500; transition: 0.3s; }
        .btn-action:hover { transform: scale(1.05); }

        /* Modern Table */
        .table-custom { margin-bottom: 0; }
        .table-custom th { background-color: #f8f9fa !important; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; }
        .table-custom td { vertical-align: middle; font-size: 0.95rem; border-bottom: 1px solid #f1f3f5; padding: 15px 10px; }
        .table-custom tbody tr:hover { background-color: #fff9f5; transition: 0.2s; }
        
        /* Badges */
        .badge-soft { padding: 6px 12px; font-weight: 500; border-radius: 8px; font-size: 0.8rem; }
        .bg-soft-orange { background-color: #ffe5d0; color: #d96408; }
        .bg-soft-blue { background-color: #e3f2fd; color: #0d47a1; }
        
        /* Pagination */
        .pagination .page-link { border-radius: 8px; margin: 0 4px; color: #555; border: none; font-weight: 500; }
        .pagination .page-item.active .page-link { background: #fd7e14; color: #fff; box-shadow: 0 4px 10px rgba(253, 126, 20, 0.3); }
        
        /* Modal styling */
        .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
        .modal-header { border-radius: 20px 20px 0 0; background: linear-gradient(135deg, #fd7e14, #ff5722); color: white; border-bottom: none; }
        
        /* Form dalam Modal */
        .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #eaeaea; background-color: #fafbfe; }
        .form-control:focus { background-color: #fff; border-color: #fd7e14; box-shadow: 0 0 0 4px rgba(253, 126, 20, 0.1); }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #6c757d; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top py-3">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#"><i class="fa fa-cube me-2"></i> App Pendataan</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center gap-3 mt-3 mt-lg-0">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="tambah_user.php" class="btn btn-light btn-sm fw-semibold rounded-pill px-3 text-primary shadow-sm">
                            <i class="fa fa-users"></i> Kelola User
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- TOMBOL PROFIL SAYA -->
                <li class="nav-item">
                    <a href="profil.php" class="btn btn-light btn-sm fw-semibold rounded-pill px-3 text-warning shadow-sm">
                        <i class="fa fa-user-circle"></i> Profil Saya
                    </a>
                </li>
                
                <li class="nav-item text-white fw-medium d-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=ffffff&color=fd7e14&rounded=true" alt="User" width="32" class="me-2 shadow-sm">
                    <?= htmlspecialchars(ucfirst($_SESSION['username'])) ?> 
                    <span class="badge bg-white text-dark rounded-pill ms-2 shadow-sm"><?= ucfirst($_SESSION['role']) ?></span>
                </li>
                <li class="nav-item">
                    <button onclick="konfirmasiLogout()" class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm fw-semibold">
                        <i class="fa fa-sign-out"></i> Keluar
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    
    <!-- Welcome Banner -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Halo, <?= htmlspecialchars(ucfirst($_SESSION['username'])) ?>! 👋</h3>
            <p class="text-muted mb-0">Total <strong class="text-dark"><?= $total_rows ?></strong> data telah tercatat di dalam sistem.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="?export=csv<?= isset($_GET['filter_user']) ? '&filter_user='.$_GET['filter_user'] : '' ?>" class="btn btn-success fw-semibold px-4 py-2 btn-action shadow-sm">
                <i class="fa fa-file-excel-o me-2"></i> Unduh CSV
            </a>
            <button type="button" class="btn btn-gradient fw-semibold px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahData">
                <i class="fa fa-plus me-2"></i> Tambah Data
            </button>
        </div>
    </div>

    <div class="card card-premium">
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <form method="GET" action="" class="mb-4" style="max-width: 300px;">
            <label class="form-label text-muted small fw-bold">Filter Berdasarkan Petugas</label>
            <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <span class="input-group-text bg-white border-0 text-muted"><i class="fa fa-filter"></i></span>
                <select name="filter_user" class="form-select border-0 bg-white" onchange="this.form.submit()" style="box-shadow: none;">
                    <option value="">Semua Data</option>
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
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th width="15%">Tanggal</th>
                        <th width="30%">Informasi Siswa</th>
                        <th width="25%">Instansi</th>
                        <th width="25%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            $no_wa = preg_replace('/[^0-9]/', '', $row['nomor_whatsapp']);
                            if (substr($no_wa, 0, 1) === '0') { $no_wa = '62' . substr($no_wa, 1); }
                    ?>
                    <tr>
                        <td class="text-center fw-semibold text-muted"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fa fa-calendar-o me-2 text-primary"></i> 
                                <?= date('d M Y', strtotime($row['tanggal'])) ?>
                            </div>
                        </td>
                        <td>
                            <strong class="text-dark d-block mb-1"><?= htmlspecialchars($row['nama']) ?></strong>
                            <span class="badge badge-soft bg-soft-orange"><?= htmlspecialchars($row['kelas']) ?></span>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <span class="badge badge-soft bg-soft-blue ms-1"><i class="fa fa-user me-1"></i><?= htmlspecialchars($row['username'] ?? 'Sistem') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-medium text-dark text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['sekolah']) ?>">
                                <?= htmlspecialchars($row['sekolah']) ?>
                            </div>
                            <small class="text-muted"><i class="fa fa-map-marker text-danger me-1"></i><?= htmlspecialchars($row['kota']) ?></small>
                        </td>
                        <td class="text-center">
                            <!-- Group Buttons Elegan -->
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-info text-white btn-sm btn-action px-3" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id'] ?>" title="Detail">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <a href="https://wa.me/<?= $no_wa ?>" target="_blank" class="btn btn-success btn-sm btn-action px-3" title="Chat WhatsApp">
                                    <i class="fa fa-whatsapp"></i>
                                </a>
                                <form action="" method="POST" id="hapusForm<?= $row['id'] ?>" class="m-0">
                                    <input type="hidden" name="id_hapus" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="hapus_data" value="1">
                                    <button type="button" class="btn btn-danger btn-sm btn-action px-3" onclick="konfirmasiHapus(<?= $row['id'] ?>)" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- MODAL DETAIL DATA -->
                            <div class="modal fade" id="modalDetail<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header py-4 px-4">
                                    <h5 class="modal-title fw-bold"><i class="fa fa-id-card-o me-2"></i>Profil Data Siswa</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body p-4 text-start">
                                    <div class="row mb-3">
                                        <div class="col-5 text-muted fw-semibold small">Tanggal Input</div>
                                        <div class="col-7 fw-medium text-dark"><?= date('d F Y', strtotime($row['tanggal'])) ?></div>
                                    </div>
                                    <hr class="text-muted opacity-25">
                                    <div class="row mb-3">
                                        <div class="col-5 text-muted fw-semibold small">Nama Lengkap</div>
                                        <div class="col-7 fw-bold text-primary"><?= htmlspecialchars($row['nama']) ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-5 text-muted fw-semibold small">Kelas</div>
                                        <div class="col-7 fw-medium"><span class="badge bg-soft-orange"><?= htmlspecialchars($row['kelas']) ?></span></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-5 text-muted fw-semibold small">Asal Instansi</div>
                                        <div class="col-7 fw-medium text-dark"><?= htmlspecialchars($row['sekolah']) ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-5 text-muted fw-semibold small">Kota</div>
                                        <div class="col-7 fw-medium text-dark"><i class="fa fa-map-marker text-danger me-1"></i> <?= htmlspecialchars($row['kota']) ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-5 text-muted fw-semibold small">WhatsApp</div>
                                        <div class="col-7 fw-medium text-dark"><?= htmlspecialchars($row['nomor_whatsapp']) ?></div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- AKHIR MODAL DETAIL -->

                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa fa-inbox fa-3x mb-3 opacity-50"></i>
                                <h5>Data Kosong</h5>
                                <p class="small">Belum ada data pendataan yang tersedia saat ini.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
          <ul class="pagination justify-content-center mb-0">
            <?php $filter_url = isset($_GET['filter_user']) ? '&filter_user='.$_GET['filter_user'] : ''; ?>
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
              <a class="page-link px-3" href="?page=<?= $page - 1 ?><?= $filter_url ?>">«</a>
            </li>
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $filter_url ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
              <a class="page-link px-3" href="?page=<?= $page + 1 ?><?= $filter_url ?>">»</a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL FORM TAMBAH DATA (MUNCUL SAAT DIKLIK) -->
<!-- ========================================== -->
<div class="modal fade" id="modalTambahData" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-4 px-4">
                <h5 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i>Form Tambah Data Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body p-4 p-md-5 text-start">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">Tanggal Registrasi</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-success" style="border-radius: 12px 0 0 12px; border-color:#eaeaea;"><i class="fa fa-whatsapp"></i></span>
                                <input type="number" name="nomor_whatsapp" class="form-control border-start-0 ps-0" placeholder="Contoh: 0812..." required style="border-radius: 0 12px 12px 0;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama siswa" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Misal: XII IPA 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota Asal</label>
                            <input type="text" name="kota" class="form-control" placeholder="Misal: Banjarbaru" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Asal Instansi / Sekolah</label>
                        <input type="text" name="sekolah" class="form-control" placeholder="Nama sekolah lengkap" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4" style="border-radius: 0 0 20px 20px;">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_data" class="btn btn-gradient rounded-pill px-4"><i class="fa fa-save me-2"></i>Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- AKHIR MODAL TAMBAH DATA -->

<?= $alert; ?>

<script>
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Hapus Data?', text: "Data tidak dapat dikembalikan setelah dihapus!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#e74c3c', cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', borderRadius: '20px'
        }).then((result) => { if (result.isConfirmed) document.getElementById('hapusForm' + id).submit(); })
    }
    function konfirmasiLogout() {
        Swal.fire({
            title: 'Keluar Aplikasi?', text: "Sesi Anda akan diakhiri.", icon: 'question',
            showCancelButton: true, confirmButtonColor: '#fd7e14', cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Ya, Logout', cancelButtonText: 'Batal'
        }).then((result) => { if (result.isConfirmed) window.location.href = 'logout.php'; })
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>