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

$editMode = false;

$rowEdit = [
    'id_barang' => '',
    'nama_barang' => '',
    'harga' => '',
    'stok' => ''
];


/*
|--------------------------------------------------------------------------
| TAMBAH / UPDATE BARANG
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['simpan'])) {

    $id    = trim($_POST['id_barang'] ?? '');
    $nama  = trim($_POST['nama_barang'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $stok  = trim($_POST['stok'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if ($nama === '') {

        $error = "Nama barang wajib diisi.";

    } elseif ($harga === '' || !is_numeric($harga)) {

        $error = "Harga barang tidak valid.";

    } elseif ((float) $harga < 0) {

        $error = "Harga tidak boleh kurang dari 0.";

    } elseif ($stok === '' || !ctype_digit($stok)) {

        $error = "Stok harus berupa angka.";

    } elseif ((int) $stok < 0) {

        $error = "Stok tidak boleh kurang dari 0.";
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $hargaValue = (float) $harga;
        $stokValue  = (int) $stok;


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        if ($id !== '') {

            if (!ctype_digit($id)) {

                $error = "ID barang tidak valid.";

            } else {

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE barang
                     SET nama_barang = ?,
                         harga = ?,
                         stok = ?
                     WHERE id_barang = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sdii",
                    $nama,
                    $hargaValue,
                    $stokValue,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: stok.php?success=updated");
                    exit;

                } else {

                    $error = "Gagal mengupdate data barang.";
                }

                mysqli_stmt_close($stmt);
            }


        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO barang
                (nama_barang, harga, stok)
                VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sdi",
                $nama,
                $hargaValue,
                $stokValue
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: stok.php?success=added");
                exit;

            } else {

                $error = "Gagal menambahkan barang.";
            }

            mysqli_stmt_close($stmt);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PERTAHANKAN INPUT JIKA ERROR
    |--------------------------------------------------------------------------
    */

    if ($error !== '') {

        $rowEdit = [
            'id_barang' => $id,
            'nama_barang' => $nama,
            'harga' => $harga,
            'stok' => $stok
        ];

        $editMode = ($id !== '');
    }
}


/*
|--------------------------------------------------------------------------
| HAPUS BARANG
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hapus'])) {

    $id = trim($_POST['hapus']);

    if ($id === '' || !ctype_digit($id)) {

        $error = "ID barang tidak valid.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM barang
             WHERE id_barang = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header("Location: stok.php?success=deleted");
            exit;

        } else {

            $error = "Barang tidak dapat dihapus karena sudah digunakan dalam transaksi.";
        }

        mysqli_stmt_close($stmt);
    }
}


/*
|--------------------------------------------------------------------------
| PESAN SUKSES
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {

        $success = "Barang berhasil ditambahkan.";

    } elseif ($_GET['success'] === 'updated') {

        $success = "Data barang berhasil diperbarui.";

    } elseif ($_GET['success'] === 'deleted') {

        $success = "Barang berhasil dihapus.";
    }
}


/*
|--------------------------------------------------------------------------
| MODE EDIT
|--------------------------------------------------------------------------
*/

if (isset($_GET['edit'])) {

    $id = trim($_GET['edit']);

    if ($id !== '' && ctype_digit($id)) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                id_barang,
                nama_barang,
                harga,
                stok
             FROM barang
             WHERE id_barang = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );

        mysqli_stmt_execute($stmt);

        $resultEdit = mysqli_stmt_get_result($stmt);

        $barang = mysqli_fetch_assoc($resultEdit);

        mysqli_stmt_close($stmt);

        if ($barang) {

            $editMode = true;
            $rowEdit = $barang;

        } else {

            $error = "Data barang tidak ditemukan.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');


if ($search !== '') {

    $searchLike = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id_barang,
            nama_barang,
            harga,
            stok
         FROM barang
         WHERE nama_barang LIKE ?
         ORDER BY id_barang DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $searchLike
    );

} else {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id_barang,
            nama_barang,
            harga,
            stok
         FROM barang
         ORDER BY id_barang DESC"
    );
}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Stok Barang - Web Kasir</title>

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

        .form-control {
            min-height: 42px;
        }

        .btn {
            border-radius: 7px;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .action-buttons {
            white-space: nowrap;
        }

        .stock-low {
            color: #dc3545;
            font-weight: 700;
        }

        .stock-safe {
            color: #198754;
            font-weight: 600;
        }

        @media (max-width: 767.98px) {

            .container {
                padding-left: 14px;
                padding-right: 14px;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .action-buttons {
                white-space: normal;
            }

            .action-buttons .btn {
                margin-bottom: 4px;
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
            Stok Barang
        </h2>

        <p class="text-muted mb-0">
            Kelola data barang, harga, dan stok.
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


    <!-- FORM BARANG -->

    <div class="card shadow-sm mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                <?= $editMode
                    ? 'Edit Barang'
                    : 'Tambah Barang';
                ?>

            </h5>


            <form method="POST">


                <?php if ($editMode): ?>

                    <input
                        type="hidden"
                        name="id_barang"
                        value="<?= e($rowEdit['id_barang']); ?>"
                    >

                <?php endif; ?>


                <div class="row g-3">


                    <!-- NAMA BARANG -->

                    <div class="col-12 col-md-4">

                        <label
                            for="nama_barang"
                            class="form-label"
                        >
                            Nama Barang
                        </label>

                        <input
                            type="text"
                            id="nama_barang"
                            name="nama_barang"
                            class="form-control"
                            placeholder="Masukkan nama barang"
                            value="<?= e($rowEdit['nama_barang']); ?>"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- HARGA -->

                    <div class="col-12 col-md-4">

                        <label
                            for="harga"
                            class="form-label"
                        >
                            Harga
                        </label>

                        <input
                            type="number"
                            id="harga"
                            name="harga"
                            class="form-control"
                            placeholder="Masukkan harga"
                            value="<?= e($rowEdit['harga']); ?>"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>


                    <!-- STOK -->

                    <div class="col-12 col-md-4">

                        <label
                            for="stok"
                            class="form-label"
                        >
                            Stok
                        </label>

                        <input
                            type="number"
                            id="stok"
                            name="stok"
                            class="form-control"
                            placeholder="Masukkan jumlah stok"
                            value="<?= e($rowEdit['stok']); ?>"
                            min="0"
                            required
                        >

                    </div>


                    <!-- BUTTON -->

                    <div class="col-12">

                        <div class="d-flex flex-column flex-sm-row gap-2">

                            <button
                                type="submit"
                                name="simpan"
                                class="btn btn-success"
                            >
                                <?= $editMode
                                    ? 'Update'
                                    : 'Simpan';
                                ?>
                            </button>


                            <?php if ($editMode): ?>

                                <a
                                    href="stok.php"
                                    class="btn btn-secondary"
                                >
                                    Batal
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </form>

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
                        placeholder="Cari nama barang..."
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


    <!-- TABLE BARANG -->

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                Daftar Barang
            </h5>


            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-primary">

                        <tr>

                            <th class="text-center">
                                ID
                            </th>

                            <th>
                                Nama Barang
                            </th>

                            <th class="text-end">
                                Harga
                            </th>

                            <th class="text-center">
                                Stok
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


                                <td class="text-center">
                                    <?= e($row['id_barang']); ?>
                                </td>


                                <td>
                                    <?= e($row['nama_barang']); ?>
                                </td>


                                <td class="text-end">
                                    Rp <?= number_format(
                                        (float) $row['harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>
                                </td>


                                <td class="text-center">

                                    <?php if ((int) $row['stok'] <= 5): ?>

                                        <span class="stock-low">
                                            <?= e($row['stok']); ?>
                                        </span>

                                        <span class="badge bg-danger ms-1">
                                            Stok Menipis
                                        </span>

                                    <?php else: ?>

                                        <span class="stock-safe">
                                            <?= e($row['stok']); ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td class="text-center action-buttons">


                                    <a
                                        href="stok.php?edit=<?= e($row['id_barang']); ?>"
                                        class="btn btn-warning btn-sm"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus barang ini?');"
                                    >

                                        <button
                                            type="submit"
                                            name="hapus"
                                            value="<?= e($row['id_barang']); ?>"
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
                                colspan="5"
                                class="text-center text-muted py-4"
                            >
                                Belum ada data barang.
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