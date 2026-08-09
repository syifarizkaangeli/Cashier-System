<?php

session_start();
require_once "database.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}


/*
|--------------------------------------------------------------------------
| JUMLAH PELANGGAN
|--------------------------------------------------------------------------
*/

$queryPelanggan = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM pelanggan"
);

$dataPelanggan = mysqli_fetch_assoc($queryPelanggan);

$totalPelanggan = (int) $dataPelanggan['total'];


/*
|--------------------------------------------------------------------------
| JUMLAH BARANG
|--------------------------------------------------------------------------
*/

$queryBarang = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM barang"
);

$dataBarang = mysqli_fetch_assoc($queryBarang);

$totalBarang = (int) $dataBarang['total'];


/*
|--------------------------------------------------------------------------
| STOK MENIPIS
|--------------------------------------------------------------------------
|
| Stok <= 5 dianggap menipis.
|
*/

$queryStokMenipis = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM barang
     WHERE stok <= 5"
);

$dataStokMenipis = mysqli_fetch_assoc($queryStokMenipis);

$totalStokMenipis = (int) $dataStokMenipis['total'];


/*
|--------------------------------------------------------------------------
| TOTAL TRANSAKSI
|--------------------------------------------------------------------------
*/

$queryTransaksi = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM penjualan"
);

$dataTransaksi = mysqli_fetch_assoc($queryTransaksi);

$totalTransaksi = (int) $dataTransaksi['total'];


/*
|--------------------------------------------------------------------------
| TOTAL PENJUALAN
|--------------------------------------------------------------------------
*/

$queryPenjualan = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_harga), 0) AS total
     FROM penjualan"
);

$dataPenjualan = mysqli_fetch_assoc($queryPenjualan);

$totalPenjualan = (float) $dataPenjualan['total'];


/*
|--------------------------------------------------------------------------
| TRANSAKSI TERBARU
|--------------------------------------------------------------------------
*/

$queryTerbaru = mysqli_query(
    $conn,
    "SELECT
        p.id_penjualan,
        p.jumlah,
        p.total_harga,
        p.waktu,
        pl.nama_pelanggan,
        b.nama_barang
     FROM penjualan p
     INNER JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
     INNER JOIN barang b
        ON p.id_barang = b.id_barang
     ORDER BY p.waktu DESC,
              p.id_penjualan DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Web Kasir</title>

    <link
        rel="stylesheet"
        href="bootstrap-5.3.8-dist/css/bootstrap.min.css"
    >

    <style>

        body {
            background: #f5f6f8;
        }

        .dashboard-title {
            font-weight: 700;
        }

        .stat-card {
            border: none;
            border-radius: 14px;
            height: 100%;
            transition: 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .quick-card {
            border: none;
            border-radius: 14px;
        }

        .quick-link {
            text-decoration: none;
        }

        .quick-link .card {
            transition: 0.2s ease;
        }

        .quick-link .card:hover {
            transform: translateY(-3px);
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        @media (max-width: 767.98px) {

            .container {
                padding-left: 14px;
                padding-right: 14px;
            }

            .dashboard-title {
                font-size: 1.45rem;
            }

            .stat-number {
                font-size: 1.4rem;
            }

        }

    </style>

</head>


<body>


<?php include "navbar.php"; ?>


<div class="container py-4">


    <!-- HEADER -->

    <div class="mb-4">

        <h2 class="dashboard-title mb-1">
            Dashboard
        </h2>

        <p class="text-muted mb-0">
            Selamat datang di sistem kasir.
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="row g-3 mb-4">


        <!-- PELANGGAN -->

        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card shadow-sm stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="text-muted mb-1">
                                Pelanggan
                            </p>

                            <div class="stat-number text-primary">
                                <?= $totalPelanggan; ?>
                            </div>

                            <small class="text-muted">
                                Pelanggan terdaftar
                            </small>

                        </div>


                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            P
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- BARANG -->

        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card shadow-sm stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="text-muted mb-1">
                                Barang
                            </p>

                            <div class="stat-number text-success">
                                <?= $totalBarang; ?>
                            </div>

                            <small class="text-muted">
                                Jenis barang
                            </small>

                        </div>


                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            B
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- STOK MENIPIS -->

        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card shadow-sm stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="text-muted mb-1">
                                Stok Menipis
                            </p>

                            <div class="stat-number text-danger">
                                <?= $totalStokMenipis; ?>
                            </div>

                            <small class="text-muted">
                                Stok ≤ 5
                            </small>

                        </div>


                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            !
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- TRANSAKSI -->

        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card shadow-sm stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="text-muted mb-1">
                                Transaksi
                            </p>

                            <div class="stat-number text-warning">
                                <?= $totalTransaksi; ?>
                            </div>

                            <small class="text-muted">
                                Total transaksi
                            </small>

                        </div>


                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            T
                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>


    <!-- TOTAL PENJUALAN -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body p-4">

            <div class="row align-items-center">

                <div class="col-12 col-md-8">

                    <p class="text-muted mb-1">
                        Total Penjualan
                    </p>

                    <h2 class="fw-bold text-success mb-0">

                        Rp <?= number_format(
                            $totalPenjualan,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </h2>

                </div>


                <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">

                    <a
                        href="histori.php"
                        class="btn btn-success"
                    >
                        Lihat Histori
                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- QUICK MENU -->

    <div class="mb-4">

        <h5 class="fw-bold mb-3">
            Menu Cepat
        </h5>


        <div class="row g-3">


            <!-- PELANGGAN -->

            <div class="col-12 col-sm-6 col-lg-3">

                <a
                    href="pelanggan.php"
                    class="quick-link text-dark"
                >

                    <div class="card shadow-sm h-100">

                        <div class="card-body">

                            <h5 class="fw-bold">
                                Pelanggan
                            </h5>

                            <p class="text-muted mb-0">
                                Kelola data pelanggan.
                            </p>

                        </div>

                    </div>

                </a>

            </div>


            <!-- BARANG -->

            <div class="col-12 col-sm-6 col-lg-3">

                <a
                    href="stok.php"
                    class="quick-link text-dark"
                >

                    <div class="card shadow-sm h-100">

                        <div class="card-body">

                            <h5 class="fw-bold">
                                Stok Barang
                            </h5>

                            <p class="text-muted mb-0">
                                Kelola barang dan stok.
                            </p>

                        </div>

                    </div>

                </a>

            </div>


            <!-- TRANSAKSI -->

            <div class="col-12 col-sm-6 col-lg-3">

                <a
                    href="transaksi.php"
                    class="quick-link text-dark"
                >

                    <div class="card shadow-sm h-100">

                        <div class="card-body">

                            <h5 class="fw-bold">
                                Transaksi
                            </h5>

                            <p class="text-muted mb-0">
                                Buat transaksi baru.
                            </p>

                        </div>

                    </div>

                </a>

            </div>


            <!-- HISTORI -->

            <div class="col-12 col-sm-6 col-lg-3">

                <a
                    href="histori.php"
                    class="quick-link text-dark"
                >

                    <div class="card shadow-sm h-100">

                        <div class="card-body">

                            <h5 class="fw-bold">
                                Histori
                            </h5>

                            <p class="text-muted mb-0">
                                Lihat riwayat transaksi.
                            </p>

                        </div>

                    </div>

                </a>

            </div>


        </div>

    </div>


    <!-- TRANSAKSI TERBARU -->

    <div class="card shadow-sm">

        <div class="card-body p-4">


            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">

                <h5 class="fw-bold mb-0">
                    Transaksi Terbaru
                </h5>


                <a
                    href="histori.php"
                    class="btn btn-outline-primary btn-sm"
                >
                    Lihat Semua
                </a>

            </div>


            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-primary">

                        <tr>

                            <th class="text-center">
                                ID
                            </th>

                            <th>
                                Pelanggan
                            </th>

                            <th>
                                Barang
                            </th>

                            <th class="text-center">
                                Jumlah
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                            <th class="text-center">
                                Waktu
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (mysqli_num_rows($queryTerbaru) > 0): ?>


                        <?php while ($row = mysqli_fetch_assoc($queryTerbaru)): ?>


                            <tr>


                                <td class="text-center">
                                    <?= e($row['id_penjualan']); ?>
                                </td>


                                <td>
                                    <?= e($row['nama_pelanggan']); ?>
                                </td>


                                <td>
                                    <?= e($row['nama_barang']); ?>
                                </td>


                                <td class="text-center">
                                    <?= e($row['jumlah']); ?>
                                </td>


                                <td class="text-end">

                                    Rp <?= number_format(
                                        (float) $row['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </td>


                                <td class="text-center">

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($row['waktu'])
                                    ); ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted py-4"
                            >
                                Belum ada transaksi.
                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>


        </div>

    </div>


</div>


<script
    src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>

<?php

mysqli_close($conn);

?>