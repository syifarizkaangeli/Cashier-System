<?php
session_start();
include "database.php";
include "navbar.php";

if(!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit;
}

if(isset($_POST['tambah'])) {
  $nama  = mysqli_real_escape_string($conn,$_POST['nama']);
  $harga = mysqli_real_escape_string($conn,$_POST['harga']);
  $stok  = mysqli_real_escape_string($conn,$_POST['stok']);

  mysqli_query($conn,"INSERT INTO barang VALUES(NULL,'$nama','$harga','$stok')");
  header("Location: stok.php");
}

if(isset($_POST['update'])) {
  $id    = $_POST['id'];
  $nama  = mysqli_real_escape_string($conn,$_POST['nama']);
  $harga = mysqli_real_escape_string($conn,$_POST['harga']);
  $stok  = mysqli_real_escape_string($conn,$_POST['stok']);

  mysqli_query($conn,"
    UPDATE barang SET 
      nama_barang='$nama',
      harga='$harga',
      stok='$stok'
    WHERE id_barang='$id'
  ");
  header("Location: stok.php");
}

if(isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn,"DELETE FROM barang WHERE id_barang='$id'");
  header("Location: stok.php");
}

$s = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$q = mysqli_query($conn,"
  SELECT * FROM barang 
  WHERE nama_barang LIKE '%$s%' 
     OR harga LIKE '%$s%'
     OR stok LIKE '%$s%'
");

$edit = null;
if(isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $edit = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM barang WHERE id_barang='$id'")
  );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Barang</title>

  <link href="bootstrap-5.3.8-dist\css\bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

  <!-- CRUD -->
  <h4 class="mb-3"><?= $edit ? "Edit Barang" : "Tambah Barang" ?></h4>

  <form method="POST" class="card p-3 shadow-sm mb-4">
    <input type="hidden" name="id" value="<?= $edit['id_barang'] ?? '' ?>">

    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <input name="nama" class="form-control"
               placeholder="Nama Barang"
               value="<?= $edit['nama_barang'] ?? '' ?>" required>
      </div>

      <div class="col-md-3">
        <input name="harga" type="number" class="form-control"
               placeholder="Harga"
               value="<?= $edit['harga'] ?? '' ?>" required>
      </div>

      <div class="col-md-3">
        <input name="stok" type="number" class="form-control"
               placeholder="Stok"
               value="<?= $edit['stok'] ?? '' ?>" required>
      </div>

      <div class="col-md-2 d-grid">
        <?php if($edit): ?>
          <button class="btn btn-primary" name="update">Update</button>
          <a href="barang.php" class="btn btn-secondary mt-1">Batal</a>
        <?php else: ?>
          <button class="btn btn-success" name="tambah">Tambah</button>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <!-- SEARCH -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-10">
      <input name="search" class="form-control"
             placeholder="Cari Data Barang"
             value="<?= $s ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-primary">Cari</button>
    </div>
  </form>

  <!-- TABLE -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-3">Data Barang</h4>

      <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nama Barang</th>
            <th>Harga</th>
            <th>Stok</th>
            <th width="150">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td><?= $r['id_barang'] ?></td>
            <td><?= $r['nama_barang'] ?></td>
            <td>Rp<?= number_format($r['harga']) ?></td>
            <td><?= $r['stok'] ?></td>
            <td>
              <a href="?edit=<?= $r['id_barang'] ?>" 
                 class="btn btn-warning btn-sm">Edit</a>

              <a href="?hapus=<?= $r['id_barang'] ?>" 
                 onclick="return confirm('Hapus data?')" 
                 class="btn btn-danger btn-sm">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="bootstrap-5.3.8-dist\js\bootstrap.bundle.min.js"></script>
</body>
</html>
