<?php
session_start();
include "database.php";
include "navbar.php";

if(!isset($_SESSION['admin'])) header("Location: login.php");

if(isset($_POST['simpan'])){
  $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
  $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
  $hp     = mysqli_real_escape_string($conn, $_POST['hp']);
  $id     = $_POST['id'];

  if($id == ""){
    mysqli_query($conn,"
      INSERT INTO pelanggan VALUES(NULL,'$nama','$alamat','$hp')
    ");
  } else {
    mysqli_query($conn,"
      UPDATE pelanggan SET
        nama_pelanggan='$nama',
        alamat='$alamat',
        no_hp='$hp'
      WHERE id_pelanggan='$id'
    ");
  }
  header("Location: pelanggan.php");
}

if(isset($_GET['hapus'])){
  $id = $_GET['hapus'];
  mysqli_query($conn,"DELETE FROM pelanggan WHERE id_pelanggan='$id'");
  header("Location: pelanggan.php");
}

$edit = false;
$data = ['id_pelanggan'=>'','nama_pelanggan'=>'','alamat'=>'','no_hp'=>''];

if(isset($_GET['edit'])){
  $edit = true;
  $id = $_GET['edit'];
  $qEdit = mysqli_query($conn,"SELECT * FROM pelanggan WHERE id_pelanggan='$id'");
  $data = mysqli_fetch_assoc($qEdit);
}

$s = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$q = mysqli_query($conn,"
  SELECT * FROM pelanggan 
  WHERE nama_pelanggan LIKE '%$s%'
     OR alamat LIKE '%$s%'
     OR no_hp LIKE '%$s%'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Pelanggan</title>

  <link href="bootstrap-5.3.8-dist\css\bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

  <!-- CRUD -->
  <h4 class="mb-3"><?= $edit ? "Edit Pelanggan" : "Tambah Pelanggan" ?></h4>

  <form method="POST" class="card p-3 shadow-sm mb-4">
    <input type="hidden" name="id" value="<?= $data['id_pelanggan'] ?>">

    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <input type="text" name="nama" class="form-control"
               placeholder="Nama"
               value="<?= $data['nama_pelanggan'] ?>" required>
      </div>

      <div class="col-md-4">
        <input type="text" name="alamat" class="form-control"
               placeholder="Alamat"
               value="<?= $data['alamat'] ?>" required>
      </div>

      <div class="col-md-3">
        <input type="text" name="hp" class="form-control"
               placeholder="No HP"
               value="<?= $data['no_hp'] ?>" required>
      </div>

      <div class="col-md-2 d-grid">
        <button class="btn btn-success" name="simpan">
          <?= $edit ? "Update" : "Tambah" ?>
        </button>

        <?php if($edit): ?>
          <a href="pelanggan.php" class="btn btn-secondary mt-1">Batal</a>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <!-- SEARCH -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-10">
      <input name="search" class="form-control"
             placeholder="Cari nama / alamat / no hp"
             value="<?= $s ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-primary">Cari</button>
    </div>
  </form>

  <!-- TABLE -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-3">Data Pelanggan</h4>

      <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>No HP</th>
            <th width="150">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td><?= $r['id_pelanggan'] ?></td>
            <td><?= $r['nama_pelanggan'] ?></td>
            <td><?= $r['alamat'] ?></td>
            <td><?= $r['no_hp'] ?></td>
            <td>
              <a href="pelanggan.php?edit=<?= $r['id_pelanggan'] ?>"
                 class="btn btn-warning btn-sm">Edit</a>

              <a href="pelanggan.php?hapus=<?= $r['id_pelanggan'] ?>"
                 onclick="return confirm('Yakin hapus data?')"
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
