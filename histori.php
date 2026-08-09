<?php
session_start();
include "database.php";
include "navbar.php";

if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit;
}

$s = mysqli_real_escape_string($conn, $_GET['search'] ?? '');

$q = mysqli_query($conn, "
  SELECT 
    penjualan.*,
    barang.nama_barang,
    pelanggan.nama_pelanggan
  FROM penjualan
  JOIN barang 
    ON penjualan.id_barang = barang.id_barang
  JOIN pelanggan 
    ON penjualan.id_pelanggan = pelanggan.id_pelanggan
  WHERE penjualan.id_penjualan LIKE '%$s%'
     OR barang.nama_barang LIKE '%$s%'
     OR pelanggan.nama_pelanggan LIKE '%$s%'
     OR penjualan.harga LIKE '%$s%'
     OR penjualan.subtotal LIKE '%$s%'
  ORDER BY penjualan.waktu DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Histori Penjualan</title>
  <link href="bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

  <!-- SEARCH -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-10">
      <input name="search"
             class="form-control"
             placeholder="Cari ID / Barang / Pelanggan"
             value="<?= htmlspecialchars($s) ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-primary">Cari</button>
    </div>
  </form>

  <!-- TABLE -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-3">Histori Penjualan</h4>

      <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-success text-center">
          <tr>
            <th>ID</th>
            <th>Waktu</th>
            <th>Pelanggan</th>
            <th>Barang</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($q) > 0): ?>
          <?php while ($r = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td><?= $r['id_penjualan'] ?></td>
            <td><?= $r['waktu'] ?></td>
            <td><?= $r['nama_pelanggan'] ?></td>
            <td><?= $r['nama_barang'] ?></td>
            <td class="text-center"><?= $r['jumlah'] ?></td>
            <td>Rp<?= number_format($r['harga'],0,',','.') ?></td>
            <td>Rp<?= number_format($r['subtotal'],0,',','.') ?></td>
            <td class="text-center">
              <a href="cetak.php?id=<?= $r['id_penjualan'] ?>"
                 target="_blank"
                 class="btn btn-sm btn-success">
                Cetak
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center text-muted">
              Data tidak ditemukan
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>

    </div>
  </div>
</div>

<script src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
