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


/*
|--------------------------------------------------------------------------
| SIMPAN TRANSAKSI
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['simpan'])) {

    $id_pelanggan = trim($_POST['id_pelanggan'] ?? '');
    $id_barang    = trim($_POST['id_barang'] ?? '');
    $jumlah       = trim($_POST['jumlah'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */

    if ($id_pelanggan === '' || !ctype_digit($id_pelanggan)) {

        $error = "Silakan pilih pelanggan.";

    } elseif ($id_barang === '' || !ctype_digit($id_barang)) {

        $error = "Silakan pilih barang.";

    } elseif ($jumlah === '' || !ctype_digit($jumlah)) {

        $error = "Jumlah barang harus berupa angka.";

    } elseif ((int) $jumlah <= 0) {

        $error = "Jumlah barang harus lebih dari 0.";
    }


    /*
    |--------------------------------------------------------------------------
    | PROSES TRANSAKSI
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $idPelangganValue = (int) $id_pelanggan;
        $idBarangValue    = (int) $id_barang;
        $jumlahValue      = (int) $jumlah;


        /*
        |--------------------------------------------------------------------------
        | MULAI TRANSACTION DATABASE
        |--------------------------------------------------------------------------
        */

        mysqli_begin_transaction($conn);

        try {

            /*
            |--------------------------------------------------------------------------
            | CEK PELANGGAN
            |--------------------------------------------------------------------------
            */

            $stmtPelanggan = mysqli_prepare(
                $conn,
                "SELECT id_pelanggan
                 FROM pelanggan
                 WHERE id_pelanggan = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmtPelanggan,
                "i",
                $idPelangganValue
            );

            mysqli_stmt_execute($stmtPelanggan);

            $resultPelanggan = mysqli_stmt_get_result($stmtPelanggan);

            if (mysqli_num_rows($resultPelanggan) !== 1) {

                throw new Exception("Data pelanggan tidak ditemukan.");
            }

            mysqli_stmt_close($stmtPelanggan);


            /*
            |--------------------------------------------------------------------------
            | AMBIL BARANG + KUNCI ROW
            |--------------------------------------------------------------------------
            |
            | FOR UPDATE mencegah stok berubah oleh transaksi lain
            | selama proses transaksi berlangsung.
            |
            */

            $stmtBarang = mysqli_prepare(
                $conn,
                "SELECT
                    id_barang,
                    nama_barang,
                    harga,
                    stok
                 FROM barang
                 WHERE id_barang = ?
                 FOR UPDATE"
            );

            mysqli_stmt_bind_param(
                $stmtBarang,
                "i",
                $idBarangValue
            );

            mysqli_stmt_execute($stmtBarang);

            $resultBarang = mysqli_stmt_get_result($stmtBarang);

            $barang = mysqli_fetch_assoc($resultBarang);

            mysqli_stmt_close($stmtBarang);


            if (!$barang) {

                throw new Exception("Barang tidak ditemukan.");
            }


            /*
            |--------------------------------------------------------------------------
            | CEK STOK
            |--------------------------------------------------------------------------
            */

            $stokSekarang = (int) $barang['stok'];

            if ($jumlahValue > $stokSekarang) {

                throw new Exception(
                    "Stok {$barang['nama_barang']} tidak mencukupi. " .
                    "Stok tersedia: {$stokSekarang}."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | HITUNG HARGA
            |--------------------------------------------------------------------------
            */

            $harga = (float) $barang['harga'];

            $subtotal = $harga * $jumlahValue;

            $totalHarga = $subtotal;


            /*
            |--------------------------------------------------------------------------
            | SIMPAN PENJUALAN
            |--------------------------------------------------------------------------
            */

            $stmtPenjualan = mysqli_prepare(
                $conn,
                "INSERT INTO penjualan
                (
                    id_pelanggan,
                    id_barang,
                    jumlah,
                    harga,
                    subtotal,
                    total_harga
                )
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmtPenjualan,
                "iiiddd",
                $idPelangganValue,
                $idBarangValue,
                $jumlahValue,
                $harga,
                $subtotal,
                $totalHarga
            );

            if (!mysqli_stmt_execute($stmtPenjualan)) {

                throw new Exception(
                    "Gagal menyimpan transaksi."
                );
            }

            mysqli_stmt_close($stmtPenjualan);


            /*
            |--------------------------------------------------------------------------
            | KURANGI STOK
            |--------------------------------------------------------------------------
            */

            $stokBaru = $stokSekarang - $jumlahValue;

            $stmtStok = mysqli_prepare(
                $conn,
                "UPDATE barang
                 SET stok = ?
                 WHERE id_barang = ?"
            );

            mysqli_stmt_bind_param(
                $stmtStok,
                "ii",
                $stokBaru,
                $idBarangValue
            );

            if (!mysqli_stmt_execute($stmtStok)) {

                throw new Exception(
                    "Gagal memperbarui stok barang."
                );
            }

            mysqli_stmt_close($stmtStok);


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);

            header("Location: transaksi.php?success=1");
            exit;

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK
            |--------------------------------------------------------------------------
            */

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

if (isset($_GET['success']) && $_GET['success'] === '1') {

    $success = "Transaksi berhasil disimpan dan stok telah diperbarui.";
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PELANGGAN
|--------------------------------------------------------------------------
*/

$resultPelanggan = mysqli_query(
    $conn,
    "SELECT
        id_pelanggan,
        nama_pelanggan
     FROM pelanggan
     ORDER BY nama_pelanggan ASC"
);


/*
|--------------------------------------------------------------------------
| AMBIL DATA BARANG
|--------------------------------------------------------------------------
*/

$resultBarang = mysqli_query(
    $conn,
    "SELECT
        id_barang,
        nama_barang,
        harga,
        stok
     FROM barang
     ORDER BY nama_barang ASC"
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

    <title>Transaksi - Web Kasir</title>

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

        .form-control,
        .form-select {
            min-height: 44px;
        }

        .btn {
            border-radius: 7px;
        }

        .summary-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 16px;
        }

        .total-value {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .stock-info {
            font-size: 0.9rem;
        }

        @media (max-width: 767.98px) {

            .container {
                padding-left: 14px;
                padding-right: 14px;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .total-value {
                font-size: 1.35rem;
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
            Transaksi Penjualan
        </h2>

        <p class="text-muted mb-0">
            Buat transaksi penjualan dan perbarui stok secara otomatis.
        </p>

    </div>


    <!-- ERROR -->

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


    <!-- SUCCESS -->

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


    <!-- FORM TRANSAKSI -->

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-4">
                Form Transaksi
            </h5>


            <form method="POST">


                <div class="row g-4">


                    <!-- PELANGGAN -->

                    <div class="col-12 col-md-6">

                        <label
                            for="id_pelanggan"
                            class="form-label"
                        >
                            Pelanggan
                        </label>

                        <select
                            name="id_pelanggan"
                            id="id_pelanggan"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Pilih Pelanggan --
                            </option>


                            <?php while ($pelanggan = mysqli_fetch_assoc($resultPelanggan)): ?>

                                <option
                                    value="<?= e($pelanggan['id_pelanggan']); ?>"
                                    <?= (
                                        isset($_POST['id_pelanggan']) &&
                                        $_POST['id_pelanggan'] == $pelanggan['id_pelanggan']
                                    )
                                        ? 'selected'
                                        : '';
                                    ?>
                                >

                                    <?= e($pelanggan['nama_pelanggan']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- BARANG -->

                    <div class="col-12 col-md-6">

                        <label
                            for="id_barang"
                            class="form-label"
                        >
                            Barang
                        </label>

                        <select
                            name="id_barang"
                            id="id_barang"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Pilih Barang --
                            </option>


                            <?php while ($barang = mysqli_fetch_assoc($resultBarang)): ?>

                                <option
                                    value="<?= e($barang['id_barang']); ?>"
                                    data-harga="<?= e($barang['harga']); ?>"
                                    data-stok="<?= e($barang['stok']); ?>"
                                    <?= (
                                        isset($_POST['id_barang']) &&
                                        $_POST['id_barang'] == $barang['id_barang']
                                    )
                                        ? 'selected'
                                        : '';
                                    ?>
                                >

                                    <?= e($barang['nama_barang']); ?>

                                    -
                                    Rp <?= number_format(
                                        (float) $barang['harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>


                        <div
                            id="stockInfo"
                            class="stock-info text-muted mt-2"
                        >
                            Pilih barang untuk melihat stok.
                        </div>

                    </div>


                    <!-- JUMLAH -->

                    <div class="col-12 col-md-4">

                        <label
                            for="jumlah"
                            class="form-label"
                        >
                            Jumlah
                        </label>

                        <input
                            type="number"
                            name="jumlah"
                            id="jumlah"
                            class="form-control"
                            min="1"
                            value="<?= e($_POST['jumlah'] ?? '1'); ?>"
                            required
                        >

                    </div>


                    <!-- HARGA -->

                    <div class="col-12 col-md-4">

                        <label
                            class="form-label"
                        >
                            Harga Satuan
                        </label>

                        <div class="summary-box">

                            <div
                                id="hargaTampil"
                                class="fw-semibold"
                            >
                                Rp 0
                            </div>

                        </div>

                    </div>


                    <!-- TOTAL -->

                    <div class="col-12 col-md-4">

                        <label
                            class="form-label"
                        >
                            Total
                        </label>

                        <div class="summary-box">

                            <div
                                id="totalTampil"
                                class="total-value text-success"
                            >
                                Rp 0
                            </div>

                        </div>

                    </div>


                    <!-- BUTTON -->

                    <div class="col-12">

                        <hr>


                        <div class="d-flex flex-column flex-sm-row gap-2">

                            <button
                                type="submit"
                                name="simpan"
                                class="btn btn-success"
                            >
                                Simpan Transaksi
                            </button>


                            <a
                                href="index.php"
                                class="btn btn-secondary"
                            >
                                Batal
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


</div>


<script
    src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"
></script>


<script>

const barangSelect = document.getElementById("id_barang");
const jumlahInput = document.getElementById("jumlah");

const hargaTampil = document.getElementById("hargaTampil");
const totalTampil = document.getElementById("totalTampil");
const stockInfo = document.getElementById("stockInfo");


function formatRupiah(value) {

    return new Intl.NumberFormat(
        "id-ID"
    ).format(value);

}


function updateTransaksi() {

    const selectedOption =
        barangSelect.options[
            barangSelect.selectedIndex
        ];


    if (!selectedOption || !selectedOption.value) {

        hargaTampil.textContent = "Rp 0";
        totalTampil.textContent = "Rp 0";

        stockInfo.textContent =
            "Pilih barang untuk melihat stok.";

        stockInfo.className =
            "stock-info text-muted mt-2";

        return;
    }


    const harga =
        parseFloat(
            selectedOption.dataset.harga || 0
        );


    const stok =
        parseInt(
            selectedOption.dataset.stok || 0
        );


    const jumlah =
        parseInt(
            jumlahInput.value || 0
        );


    const total =
        harga * jumlah;


    hargaTampil.textContent =
        "Rp " + formatRupiah(harga);


    totalTampil.textContent =
        "Rp " + formatRupiah(total);


    stockInfo.textContent =
        "Stok tersedia: " + stok;


    if (stok <= 5) {

        stockInfo.className =
            "stock-info text-danger fw-semibold mt-2";

    } else {

        stockInfo.className =
            "stock-info text-success mt-2";
    }


    jumlahInput.max = stok > 0 ? stok : 1;
}


barangSelect.addEventListener(
    "change",
    updateTransaksi
);


jumlahInput.addEventListener(
    "input",
    updateTransaksi
);


updateTransaksi();

</script>


</body>

</html>

<?php

mysqli_close($conn);

?>