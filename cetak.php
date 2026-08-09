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
| CEK ID TRANSAKSI
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? '';

if ($id === '' || !ctype_digit($id)) {
    die("ID transaksi tidak valid.");
}

$idPenjualan = (int) $id;


/*
|--------------------------------------------------------------------------
| AMBIL DATA TRANSAKSI
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        p.id_penjualan,
        p.jumlah,
        p.harga,
        p.subtotal,
        p.total_harga,
        p.waktu,

        pl.nama_pelanggan,
        pl.alamat,
        pl.no_hp,

        b.nama_barang

     FROM penjualan p

     INNER JOIN pelanggan pl
         ON p.id_pelanggan = pl.id_pelanggan

     INNER JOIN barang b
         ON p.id_barang = b.id_barang

     WHERE p.id_penjualan = ?

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idPenjualan
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$transaksi = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| TRANSAKSI TIDAK DITEMUKAN
|--------------------------------------------------------------------------
*/

if (!$transaksi) {
    die("Data transaksi tidak ditemukan.");
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Struk Transaksi #<?= e($transaksi['id_penjualan']); ?>
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f1f3f5;
            font-family: Arial, Helvetica, sans-serif;
            color: #212529;
        }

        .receipt {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0 0 5px;
            font-size: 28px;
        }

        .header p {
            margin: 0;
            color: #6c757d;
        }

        .line {
            border-top: 1px dashed #999;
            margin: 20px 0;
        }

        .info {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 8px;
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 8px;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            text-align: left;
            background: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            border-top: 2px solid #212529;
            border-bottom: none;
            font-size: 20px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }

        .actions {
            max-width: 700px;
            margin: 20px auto 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 576px) {

            body {
                padding: 12px;
            }

            .receipt {
                padding: 18px;
            }

            .header h1 {
                font-size: 22px;
            }

            .info {
                grid-template-columns: 100px 1fr;
                font-size: 14px;
            }

            th,
            td {
                padding: 9px 5px;
                font-size: 13px;
            }

            .total-row td {
                font-size: 17px;
            }

            .actions {
                flex-direction: column;
            }

            .actions .btn {
                width: 100%;
                text-align: center;
            }

        }

        @media print {

            body {
                padding: 0;
                background: white;
            }

            .receipt {
                max-width: none;
                box-shadow: none;
                border-radius: 0;
            }

            .actions {
                display: none;
            }

        }

    </style>

</head>


<body>


<div class="receipt">


    <!-- HEADER -->

    <div class="header">

        <h1>WEB KASIR</h1>

        <p>
            Struk Transaksi Penjualan
        </p>

    </div>


    <div class="line"></div>


    <!-- INFORMASI TRANSAKSI -->

    <div class="info">

        <div class="info-label">
            No. Transaksi
        </div>

        <div>
            #<?= e($transaksi['id_penjualan']); ?>
        </div>


        <div class="info-label">
            Tanggal
        </div>

        <div>
            <?= date(
                'd/m/Y H:i',
                strtotime($transaksi['waktu'])
            ); ?>
        </div>


        <div class="info-label">
            Pelanggan
        </div>

        <div>
            <?= e($transaksi['nama_pelanggan']); ?>
        </div>


        <div class="info-label">
            No. HP
        </div>

        <div>
            <?= e($transaksi['no_hp'] ?: '-'); ?>
        </div>


        <div class="info-label">
            Alamat
        </div>

        <div>
            <?= e($transaksi['alamat'] ?: '-'); ?>
        </div>

    </div>


    <!-- DETAIL BARANG -->

    <table>

        <thead>

            <tr>

                <th>
                    Barang
                </th>

                <th class="text-center">
                    Qty
                </th>

                <th class="text-right">
                    Harga
                </th>

                <th class="text-right">
                    Subtotal
                </th>

            </tr>

        </thead>


        <tbody>

            <tr>

                <td>
                    <?= e($transaksi['nama_barang']); ?>
                </td>

                <td class="text-center">
                    <?= e($transaksi['jumlah']); ?>
                </td>

                <td class="text-right">

                    Rp <?= number_format(
                        (float) $transaksi['harga'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </td>

                <td class="text-right">

                    Rp <?= number_format(
                        (float) $transaksi['subtotal'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </td>

            </tr>


            <tr class="total-row">

                <td
                    colspan="3"
                    class="text-right"
                >
                    TOTAL
                </td>

                <td class="text-right">

                    Rp <?= number_format(
                        (float) $transaksi['total_harga'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </td>

            </tr>

        </tbody>

    </table>


    <div class="footer">

        Terima kasih telah berbelanja.

        <br>

        Struk ini dibuat oleh Web Kasir.

    </div>


</div>


<!-- BUTTON -->

<div class="actions">

    <button
        onclick="window.print()"
        class="btn btn-primary"
    >
        Print Struk
    </button>


    <a
        href="histori.php"
        class="btn btn-secondary"
    >
        Kembali
    </a>

</div>


</body>

</html>

<?php

mysqli_close($conn);

?>