<?php
include "database.php";

$id = mysqli_real_escape_string($conn, $_GET['id'] ?? '');

$q = mysqli_query($conn, "
  SELECT 
    penjualan.*,
    barang.nama_barang,
    pelanggan.nama_pelanggan
  FROM penjualan
  JOIN barang ON penjualan.id_barang = barang.id_barang
  JOIN pelanggan ON penjualan.id_pelanggan = pelanggan.id_pelanggan
  WHERE penjualan.id_penjualan = '$id'
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
  echo "Data tidak ditemukan";
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk Penjualan</title>

<style>
body {
  font-family: monospace;
}
.struk {
  width: 300px;
  margin: auto;
}
hr {
  border: 1px dashed #000;
}
.text-center {
  text-align: center;
}
@media print {
  body {
    margin: 0;
  }
}
</style>
</head>

<body onload="window.print()">

<div class="struk">
  <h3 class="text-center">TOKO ANDA</h3>
  <p class="text-center">
    Jl. Contoh No. 123<br>
    Telp: 08123456789
  </p>

  <hr>

  <p>
    ID Transaksi : <?= $data['id_penjualan'] ?><br>
    Tanggal      : <?= $data['waktu'] ?><br>
    Pelanggan    : <?= $data['nama_pelanggan'] ?>
  </p>

  <hr>

  <p>
    <?= $data['nama_barang'] ?><br>
    <?= $data['jumlah'] ?> x Rp<?= number_format($data['harga'],0,',','.') ?>
  </p>

  <hr>

  <p>
    <strong>Total : Rp<?= number_format($data['subtotal'],0,',','.') ?></strong>
  </p>

  <hr>
  <p class="text-center">Terima Kasih 🙏</p>
</div>

</body>
</html>
