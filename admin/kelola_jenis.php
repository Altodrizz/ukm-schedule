<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}
require '../config/db.php';

$pesan = ''; $error = '';

// TAMBAH
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama = trim($_POST['nama_jenis']);
    $cek  = $pdo->prepare("SELECT id_jenis_jadwal FROM jenis_jadwal WHERE nama_jenis = ?");
    $cek->execute([$nama]);
    if ($cek->fetch()) {
        $error = "Jenis kegiatan '$nama' sudah ada!";
    } else {
        $pdo->prepare("INSERT INTO jenis_jadwal (nama_jenis) VALUES (?)")->execute([$nama]);
        $pesan = "Jenis kegiatan '$nama' berhasil ditambahkan!";
    }
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Cek apakah jenis dipakai di jadwal
    $cek = $pdo->prepare("SELECT COUNT(*) FROM detail_jadwal WHERE id_jenis_jadwal = ?");
    $cek->execute([$id]);
    if ($cek->fetchColumn() > 0) {
        $error = "Jenis kegiatan ini tidak bisa dihapus karena sudah dipakai di jadwal!";
    } else {
        $pdo->prepare("DELETE FROM jenis_jadwal WHERE id_jenis_jadwal = ?")->execute([$id]);
        header("Location: kelola_jenis.php?deleted=1"); exit;
    }
}

// EDIT — simpan
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id   = intval($_POST['id_jenis_jadwal']);
    $nama = trim($_POST['nama_jenis']);
    $pdo->prepare("UPDATE jenis_jadwal SET nama_jenis=? WHERE id_jenis_jadwal=?")->execute([$nama, $id]);
    $pesan = "Jenis kegiatan berhasil diperbarui!";
}

if (isset($_GET['deleted'])) $pesan = "Jenis kegiatan berhasil dihapus!";

// Ambil semua jenis + hitung pemakaian
$jenis_list = $pdo->query("
    SELECT jj.*, COUNT(dj.id_detail_jadwal) AS jumlah_dipakai
    FROM jenis_jadwal jj
    LEFT JOIN detail_jadwal dj ON jj.id_jenis_jadwal = dj.id_jenis_jadwal
    GROUP BY jj.id_jenis_jadwal
    ORDER BY jj.nama_jenis
")->fetchAll();

// Edit mode
$edit_data = null;
if (isset($_GET['edit'])) {
    $q = $pdo->prepare("SELECT * FROM jenis_jadwal WHERE id_jenis_jadwal=?");
    $q->execute([intval($_GET['edit'])]);
    $edit_data = $q->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Jenis Kegiatan - Admin</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">
        <img src="../assets/ukm_cendikia.png" alt="Logo" style="width:38px;height:38px;object-fit:contain;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <span style="display:none;font-size:1.1rem;">🗓️</span>
      </div>
      <div class="brand-text">
        <div class="brand-name">JADWAL UKM</div>
        <div class="brand-sub">Kewirausahaan</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <p class="nav-label">Menu Utama</p>
      <a href="dashboard.php" class="nav-item">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <a href="tambah_jadwal.php" class="nav-item">
        <span class="nav-icon">📅</span> Jadwal
      </a>
      <p class="nav-label">Master Data</p>
      <a href="kelola_anggota.php" class="nav-item">
        <span class="nav-icon">👥</span> Kelola Anggota
      </a>
      <a href="kelola_jenis.php" class="nav-item active">
        <span class="nav-icon">🏷️</span> Jenis Kegiatan
      </a>
      <p class="nav-label">Sistem</p>
      <a href="../logout.php" class="nav-item">
        <span class="nav-icon">🚪</span> Logout
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
        <div class="user-detail">
          <div class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
          <div class="user-role">Administrator</div>
        </div>
      </div>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <div class="topbar-title">
        <h2>Jenis Kegiatan</h2>
        <p>Kelola jenis-jenis kegiatan UKM (Piket, Belanja, Rapat, dll)</p>
      </div>
    </header>

    <main class="page-content">
      <?php if ($pesan): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($pesan) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start;">

        <!-- FORM TAMBAH / EDIT -->
        <div class="card">
          <div class="card-header">
            <h3><?= $edit_data ? '✏️ Edit Jenis' : '➕ Tambah Jenis' ?></h3>
            <?php if ($edit_data): ?>
              <a href="kelola_jenis.php" class="btn btn-sm" style="background:var(--gray-light);color:var(--text-dark);border:1px solid var(--gray-mid);">Batal</a>
            <?php endif; ?>
          </div>
          <form method="POST">
            <input type="hidden" name="aksi" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
            <?php if ($edit_data): ?>
              <input type="hidden" name="id_jenis_jadwal" value="<?= $edit_data['id_jenis_jadwal'] ?>">
            <?php endif; ?>

            <div class="form-group">
              <label>Nama Jenis Kegiatan</label>
              <input type="text" name="nama_jenis"
                placeholder="Contoh: Piket, Belanja, Rapat..."
                value="<?= $edit_data ? htmlspecialchars($edit_data['nama_jenis']) : '' ?>"
                required maxlength="20">
              <small style="color:var(--gray-text);font-size:0.75rem;">Maksimal 20 karakter</small>
            </div>

            <button type="submit" class="btn btn-blue" style="width:100%;justify-content:center;">
              <?= $edit_data ? '💾 Simpan Perubahan' : '➕ Tambah Jenis' ?>
            </button>
          </form>

          <!-- Info contoh jenis -->
          <div style="margin-top:20px;padding:14px;background:var(--blue-soft);border-radius:8px;border:1px solid #c7d9f5;">
            <p style="font-size:0.78rem;font-weight:700;color:var(--blue-dark);margin-bottom:8px;">💡 Contoh Jenis Kegiatan:</p>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
              <?php foreach (['Piket','Belanja','Rapat','Jaga Ruko','Promosi','Dokumentasi'] as $contoh): ?>
                <span style="background:white;border:1px solid var(--gray-mid);padding:3px 10px;border-radius:50px;font-size:0.75rem;color:var(--blue-mid);font-weight:600;"><?= $contoh ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- TABEL JENIS -->
        <div class="card">
          <div class="card-header">
            <h3>🏷️ Daftar Jenis Kegiatan (<?= count($jenis_list) ?>)</h3>
          </div>
          <div class="table-wrapper">
            <?php if (empty($jenis_list)): ?>
              <p style="text-align:center;padding:32px;color:var(--gray-text);">Belum ada jenis kegiatan.</p>
            <?php else: ?>
            <table>
              <thead>
                <tr><th>No</th><th>Nama Jenis</th><th>Dipakai di Jadwal</th><th>Aksi</th></tr>
              </thead>
              <tbody>
                <?php foreach ($jenis_list as $i => $j): ?>
                <tr>
                  <td style="color:var(--gray-text);font-weight:600;"><?= $i+1 ?></td>
                  <td>
                    <span style="background:var(--blue-soft);border:1px solid #c7d9f5;padding:4px 12px;border-radius:50px;font-size:0.82rem;font-weight:700;color:var(--blue-dark);">
                      <?= htmlspecialchars($j['nama_jenis']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($j['jumlah_dipakai'] > 0): ?>
                      <span class="badge green"><?= $j['jumlah_dipakai'] ?> jadwal</span>
                    <?php else: ?>
                      <span class="badge red">Belum dipakai</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="kelola_jenis.php?edit=<?= $j['id_jenis_jadwal'] ?>" class="action-link edit">Edit</a>
                    <?php if ($j['jumlah_dipakai'] == 0): ?>
                      <a href="kelola_jenis.php?hapus=<?= $j['id_jenis_jadwal'] ?>" class="action-link delete"
                         onclick="return confirm('Hapus jenis <?= htmlspecialchars($j['nama_jenis']) ?>?')">Hapus</a>
                    <?php else: ?>
                      <span style="font-size:0.75rem;color:var(--gray-text);padding:5px 8px;">🔒 Terkunci</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </main>
  </div>
</div>
</body>
</html>
