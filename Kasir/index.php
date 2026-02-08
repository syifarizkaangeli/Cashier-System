<?php
session_start();
include 'database.php';
include 'navbar.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$q_pelanggan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggan");
$total_pelanggan = mysqli_fetch_assoc($q_pelanggan)['total'];

$q_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan");
$total_transaksi = mysqli_fetch_assoc($q_transaksi)['total'];

$q_subtotal = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penjualan");
$total_subtotal = mysqli_fetch_assoc($q_subtotal)['total'];

$q_stok = mysqli_query($conn, "SELECT SUM(stok) as total FROM barang");
$total_stok = mysqli_fetch_assoc($q_stok)['total'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h4>Dashboard</h4>

    <div class="row mt-3">

        <div class="col-md-5">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h6>Total Transaksi</h6>
                    <h2><?= $total_transaksi; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h6>Total Pelanggan</h6>
                    <h2><?= $total_pelanggan; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h6>Total Pendapatan</h6>
                    <h2>Rp<?= number_format ($total_subtotal); ?></h2>
                </div>
            </div>
        </div>

         <div class="col-md-5">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h6>Total Stok Barang</h6>
                    <h2><?= $total_stok; ?></h2>
                </div>
            </div>
        </div>

    </div>
</div>