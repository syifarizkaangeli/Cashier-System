<?php

session_start();
require_once "database.php";

/*
|--------------------------------------------------------------------------
| CEK LOGIN
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
| VARIABLE
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";

$search = trim($_GET['search'] ?? '');


/*
|--------------------------------------------------------------------------
| HAPUS TRANSAKSI
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hapus'])) {

    $id_penjualan = trim($_POST['hapus']);

    if ($id_penjualan === '' || !ctype_digit($id_penjualan)) {

        $error = "ID transaksi tidak valid.";

    } else {

        $idPenjualanValue = (int) $id_penjualan;

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA TRANSAKSI TERLEBIH DAHULU
        |--------------------------------------------------------------------------
        */

        mysqli_begin_transaction($conn);

        try {

            $stmt = mysqli_prepare(
                $conn,
                "SELECT
                    id_penjualan,
                    id_barang,
                    jumlah
                 FROM penjualan
                 WHERE id_penjualan = ?
                 FOR UPDATE"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idPenjualanValue
            );

            mysqli_stmt_execute($stmt);

            $resultTransaksi = mysqli_stmt_get_result($stmt);

            $transaksi = mysqli_fetch_assoc($resultTransaksi);

            mysqli_stmt_close($stmt);


            if (!$transaksi) {

                throw new Exception(
                    "Transaksi tidak ditemukan."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | KEMBALIKAN STOK
            |--------------------------------------------------------------------------
            */

            $idBarangValue = (int) $transaksi['id_barang'];
            $jumlahValue   = (int) $transaksi['jumlah'];


            $stmtStok = mysqli_prepare(
                $conn,
                "UPDATE barang
                 SET stok = stok + ?
                 WHERE id_barang = ?"
            );

            mysqli_stmt_bind_param(
                $stmtStok,
                "ii",
                $jumlahValue,
                $idBarangValue
            );


            if (!mysqli_stmt_execute($stmtStok)) {

                throw new Exception(
                    "Gagal mengembalikan stok barang."
                );
            }


            mysqli_stmt_close($stmtStok);


            /*
            |--------------------------------------------------------------------------
            | HAPUS TRANSAKSI
            |--------------------------------------------------------------------------
            */

            $stmtDelete = mysqli_prepare(
                $conn,
                "DELETE FROM penjualan
                 WHERE id_penjualan = ?"
            );

            mysqli_stmt_bind_param(
                $stmtDelete,
                "i",
                $idPenjualanValue
            );


            if (!mysqli_stmt_execute($stmtDelete)) {

                throw new Exception(
                    "Gagal menghapus transaksi."
                );
            }


            mysqli_stmt_close($stmtDelete);


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);

            header(
                "Location: histori.php?success=deleted"
            );

            exit;

        } catch (Throwable $e) {

            mysqli_rollback($conn);

            $error = $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| PESAN SUKSES
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'deleted') {

        $success = "Transaksi berhasil dihapus dan stok telah dikembalikan.";
    }
}


/*
|--------------------------------------------------------------------------
| QUERY HISTORI
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $searchLike = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            p.id_penjualan,
            p.id_pelanggan,
            p.id_barang,
            p.jumlah,
            p.harga,
            p.subtotal,
            p.total_harga,
            p.waktu,

            pl.nama_pelanggan,

            b.nama_barang

         FROM penjualan p

         INNER JOIN pelanggan pl
             ON p.id_pelanggan = pl.id_pelanggan

         INNER JOIN barang b
             ON p.id_barang = b.id_barang

         WHERE
            pl.nama_pelanggan LIKE ?
            OR b.nama_barang LIKE ?

         ORDER BY p.waktu DESC,
                  p.id_penjualan DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $searchLike,
        $searchLike
    );

} else {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            p.id_penjualan,
            p.id_pelanggan,
            p.id_barang,
            p.jumlah,
            p.harga,
            p.subtotal,
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
                  p.id_penjualan DESC"
    );
}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| HITUNG TOTAL PENJUALAN
|--------------------------------------------------------------------------
*/

$totalQuery = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS jumlah_transaksi,
        COALESCE(SUM(total_harga), 0) AS total_penjualan
     FROM penjualan"
);

$totalData = mysqli_fetch_assoc($totalQuery);

$jumlahTransaksi = (int) $totalData['jumlah_transaksi'];
$totalPenjualan  = (float) $totalData['total_penjualan'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Histori Transaksi - Web Kasir</title>

    <link
        rel="stylesheet"
        href="bootstrap-5.3.8-dist/css/bootstrap.min.css"
    >

    <style>

        body {
            background: #f5f6f8;
        }

        .page-title {
            font-weight: 700;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .summary-card {
            height: 100%;
        }

        .summary-number {
            font-size: 1.7rem;
            font-weight: 700;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .action-column {
            white-space: nowrap;
        }

        @media (max-width: 767.98px) {

            .container {
                padding-left: 14px;
                padding-right: 14px;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .summary-number {
                font-size: 1.35rem;
            }

            .action-column {
                white-space: normal;
            }

        }

    </style>

</head>


<body>


<?php include "navbar.php"; ?>


<div class="container py-4">


    <!-- HEADER -->

    <div class="mb-4">

        <h2 class="page-title mb-1">
            Histori Transaksi
        </h2>

        <p class="text-muted mb-0">
            Lihat seluruh riwayat transaksi penjualan.
        </p>

    </div>


    <!-- ALERT ERROR -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <?= e($error); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- ALERT SUCCESS -->

    <?php if ($success !== ''): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <?= e($success); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- SUMMARY -->

    <div class="row g-3 mb-4">


        <!-- JUMLAH TRANSAKSI -->

        <div class="col-12 col-md-6">

            <div class="card shadow-sm summary-card">

                <div class="card-body">

                    <p class="text-muted mb-1">
                        Jumlah Transaksi
                    </p>

                    <div class="summary-number text-primary">
                        <?= $jumlahTransaksi; ?>
                    </div>

                </div>

            </div>

        </div>


        <!-- TOTAL PENJUALAN -->

        <div class="col-12 col-md-6">

            <div class="card shadow-sm summary-card">

                <div class="card-body">

                    <p class="text-muted mb-1">
                        Total Penjualan
                    </p>

                    <div class="summary-number text-success">

                        Rp <?= number_format(
                            $totalPenjualan,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </div>

                </div>

            </div>

        </div>


    </div>


    <!-- SEARCH -->

    <div class="card shadow-sm mb-4">

        <div class="card-body p-4">

            <form
                method="GET"
                class="row g-2"
            >

                <div class="col-12 col-md-9">

                    <input
                        type="search"
                        name="search"
                        class="form-control"
                        placeholder="Cari nama pelanggan atau barang..."
                        value="<?= e($search); ?>"
                    >

                </div>


                <div class="col-12 col-md-3">

                    <div class="d-grid">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Cari
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLE -->

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">

                <h5 class="fw-bold mb-0">
                    Daftar Transaksi
                </h5>


                <a
                    href="transaksi.php"
                    class="btn btn-success btn-sm"
                >
                    + Transaksi Baru
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
                                Harga
                            </th>

                            <th class="text-end">
                                Subtotal
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                            <th class="text-center">
                                Waktu
                            </th>

                            <th class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (mysqli_num_rows($result) > 0): ?>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- ID -->

                                <td class="text-center">
                                    <?= e($row['id_penjualan']); ?>
                                </td>


                                <!-- PELANGGAN -->

                                <td>
                                    <?= e($row['nama_pelanggan']); ?>
                                </td>


                                <!-- BARANG -->

                                <td>
                                    <?= e($row['nama_barang']); ?>
                                </td>


                                <!-- JUMLAH -->

                                <td class="text-center">
                                    <?= e($row['jumlah']); ?>
                                </td>


                                <!-- HARGA -->

                                <td class="text-end">

                                    Rp <?= number_format(
                                        (float) $row['harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </td>


                                <!-- SUBTOTAL -->

                                <td class="text-end">

                                    Rp <?= number_format(
                                        (float) $row['subtotal'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </td>


                                <!-- TOTAL -->

                                <td class="text-end fw-semibold">

                                    Rp <?= number_format(
                                        (float) $row['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </td>


                                <!-- WAKTU -->

                                <td class="text-center">

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($row['waktu'])
                                    ); ?>

                                </td>


                                <!-- AKSI -->

                                <td class="text-center action-column">

                                    <a
                                        href="cetak.php?id=<?= e($row['id_penjualan']); ?>"
                                        target="_blank"
                                        class="btn btn-primary btn-sm"
                                    >
                                        Cetak
                                    </a>


                                    <form
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.');"
                                    >

                                        <button
                                            type="submit"
                                            name="hapus"
                                            value="<?= e($row['id_penjualan']); ?>"
                                            class="btn btn-danger btn-sm"
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="9"
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

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>