<?php
require 'koneksi.php';
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit; }

$alert = '';
$id_user = $_SESSION['id_user'];

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Jika kode referral belum ada, ubah menjadi username
if (empty($user['kode_referral']) || $user['kode_referral'] !== $user['username']) {
    $kode_baru = $user['username'];
    $conn->query("UPDATE users SET kode_referral = '$kode_baru' WHERE id = $id_user");
    $user['kode_referral'] = $kode_baru;
}

// Ambil daftar orang yang menggunakan kode referral user ini
$stmt_ref = $conn->prepare("SELECT nama_lengkap, username, role FROM users WHERE referred_by = ?");
$stmt_ref->bind_param("s", $user['kode_referral']);
$stmt_ref->execute();
$res_ref = $stmt_ref->get_result();
$referred_users = [];
while($r = $res_ref->fetch_assoc()) {
    $referred_users[] = $r;
}

// Proses Update Profil
if (isset($_POST['update_profil'])) {
    $nama = $_POST['nama_lengkap'];
    $wa = $_POST['nomor_whatsapp'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $provinsi = $_POST['provinsi'];
    $kota = $_POST['kota'];

    $upd = $conn->prepare("UPDATE users SET nama_lengkap=?, nomor_whatsapp=?, email=?, alamat=?, kota=?, provinsi=? WHERE id=?");
    $upd->bind_param("ssssssi", $nama, $wa, $email, $alamat, $kota, $provinsi, $id_user);
    
    if ($upd->execute()) {
        $alert = "<script>Swal.fire({ title: 'Tersimpan!', text: 'Profil berhasil diperbarui.', icon: 'success', confirmButtonColor: '#fd7e14' }).then(() => { window.location.href='profil.php'; });</script>";
    } else {
        $alert = "<script>Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');</script>";
    }
}

// Generate URL Link Referral
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']);
$link_referral = $base_url . "/register.php?ref=" . $user['kode_referral'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .card-premium { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none; background: #fff; }
        .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #eaeaea; background-color: #fafbfe; }
        .form-control:focus { background-color: #fff; border-color: #fd7e14; box-shadow: 0 0 0 4px rgba(253, 126, 20, 0.1); }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #6c757d; margin-bottom: 8px; text-transform: uppercase; }
        .btn-gradient { background: linear-gradient(135deg, #fd7e14, #ff5722); color: white; border-radius: 12px; font-weight: 600; transition: 0.3s; border: none; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(253, 126, 20, 0.3); color: white; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-arrow-left text-dark"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">Profil Saya</h4>
            <p class="text-muted small mb-0">Lengkapi data diri dan kelola referral Anda.</p>
        </div>
    </div>

    <div class="row">
        <!-- Panel Kiri: Info & Referral -->
        <div class="col-lg-4 mb-4">
            <div class="card card-premium p-4 text-center mb-4">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=fd7e14&color=fff&rounded=true&size=100" class="mx-auto mb-3 shadow-sm">
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['nama_lengkap'] ?? $user['username']) ?></h5>
                <p class="text-muted small mb-2">Role: <span class="badge bg-primary rounded-pill"><?= ucfirst($user['role']) ?></span></p>
                
                <div class="p-3 rounded-3 mt-3 text-start" style="background-color: #fff9f5; border: 1px dashed #fd7e14;">
                    <!-- Bagian Kode Referral -->
                    <p class="small fw-bold text-dark mb-1"><i class="fa fa-ticket text-warning me-1"></i> Kode Referral</p>
                    <input type="text" class="form-control text-center bg-white mb-3 fw-bold text-primary" value="<?= htmlspecialchars($user['kode_referral']) ?>" readonly style="font-size: 1.1rem; letter-spacing: 1px;">
                    
                    <!-- Bagian Link Referral -->
                    <p class="small fw-bold text-dark mb-1"><i class="fa fa-link text-warning me-1"></i> Link Referral</p>
                    <input type="text" id="linkRef" class="form-control text-center bg-white mb-3 fw-semibold text-muted" value="<?= $link_referral ?>" readonly style="font-size: 0.85rem;">
                    
                    <button onclick="copyReferral()" class="btn btn-orange w-100 fw-bold mb-2 shadow-sm" style="background-color: #fd7e14; color: white; border-radius: 10px; border:none; padding: 10px;">
                        <i class="fa fa-copy me-1"></i> Salin Link
                    </button>
                    
                    <button type="button" class="btn w-100 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalReferral" style="background-color: #ffffff; color: #fd7e14; border: 1px solid #fd7e14; border-radius: 10px; padding: 10px;">
                        <i class="fa fa-users me-1"></i> Referral Saya (<?= count($referred_users) ?>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Form Edit -->
        <div class="col-lg-8">
            <div class="card card-premium p-4 p-md-5">
                <h5 class="fw-bold border-bottom pb-3 mb-4"><i class="fa fa-user-circle-o text-warning me-2"></i> Lengkapi Data Profil</h5>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor WhatsApp</label>
                            <input type="number" name="nomor_whatsapp" class="form-control" value="<?= htmlspecialchars($user['nomor_whatsapp'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                    </div>

                    <!-- Input Manual Provinsi & Kota -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($user['provinsi'] ?? '') ?>" placeholder="Misal: Kalimantan Selatan" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota / Kabupaten</label>
                            <input type="text" name="kota" class="form-control" value="<?= htmlspecialchars($user['kota'] ?? '') ?>" placeholder="Misal: Banjarbaru" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profil" class="btn btn-gradient px-5 py-3 w-100"><i class="fa fa-save me-2"></i> SIMPAN PERUBAHAN PROFIL</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REFERRAL SAYA -->
<div class="modal fade" id="modalReferral" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #fd7e14, #ff5722); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-users me-2"></i> Daftar Referral Saya</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Berikut adalah pengguna yang mendaftar menggunakan kode <strong><?= htmlspecialchars($user['kode_referral']) ?></strong>.</p>
                
                <?php if (count($referred_users) > 0): ?>
                    <div class="list-group">
                        <?php foreach($referred_users as $ref): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center rounded-3 mb-2" style="border: 1px solid #eaeaea; background-color: #fafbfe;">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($ref['nama_lengkap'] ?? $ref['username']) ?>&background=random&color=fff&rounded=true" width="40" class="me-3 rounded-circle shadow-sm">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($ref['nama_lengkap'] ?? $ref['username']) ?></h6>
                                    <small class="text-muted">@<?= htmlspecialchars($ref['username']) ?></small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border rounded-pill px-3"><?= ucfirst($ref['role']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-user-times fa-3x mb-3 opacity-50"></i>
                        <h6>Belum ada referral</h6>
                        <p class="small">Bagikan link Anda untuk mengajak orang mendaftar.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0 bg-light" style="border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $alert; ?>

<!-- Bootstrap Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function copyReferral() {
        var copyText = document.getElementById("linkRef");
        copyText.select();
        copyText.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(copyText.value);
        Swal.fire({ icon: 'success', title: 'Tersalin!', text: 'Link referral berhasil disalin ke clipboard.', timer: 2000, showConfirmButton: false });
    }
</script>
</body>
</html>