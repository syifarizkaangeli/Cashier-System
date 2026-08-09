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
    'id_pelanggan' => '',
    'nama_pelanggan' => '',
    'alamat' => '',
    'no_hp' => ''
];


/*
|--------------------------------------------------------------------------
| TAMBAH / UPDATE PELANGGAN
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['simpan'])) {

    $id     = trim($_POST['id_pelanggan'] ?? '');
    $nama   = trim($_POST['nama_pelanggan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_hp  = trim($_POST['no_hp'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if ($nama === '') {

        $error = "Nama pelanggan wajib diisi.";

    } elseif ($alamat === '') {

        $error = "Alamat pelanggan wajib diisi.";

    } elseif ($no_hp === '') {

        $error = "Nomor HP wajib diisi.";

    } elseif (!preg_match('/^[0-9+\-\s()]{8,20}$/', $no_hp)) {

        $error = "Nomor HP tidak valid.";

    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN DATA JIKA VALID
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        if ($id !== '') {

            if (!ctype_digit($id)) {

                $error = "ID pelanggan tidak valid.";

            } else {

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE pelanggan
                     SET nama_pelanggan = ?,
                         alamat = ?,
                         no_hp = ?
                     WHERE id_pelanggan = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssi",
                    $nama,
                    $alamat,
                    $no_hp,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: pelanggan.php?success=updated");
                    exit;

                } else {

                    $error = "Gagal mengupdate data pelanggan.";
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
                "INSERT INTO pelanggan
                (nama_pelanggan, alamat, no_hp)
                VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $nama,
                $alamat,
                $no_hp
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: pelanggan.php?success=added");
                exit;

            } else {

                $error = "Gagal menambahkan pelanggan.";
            }

            mysqli_stmt_close($stmt);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN INPUT JIKA ERROR
    |--------------------------------------------------------------------------
    */

    if ($error !== '') {

        $rowEdit = [
            'id_pelanggan' => $id,
            'nama_pelanggan' => $nama,
            'alamat' => $alamat,
            'no_hp' => $no_hp
        ];

        $editMode = ($id !== '');
    }
}


/*
|--------------------------------------------------------------------------
| HAPUS PELANGGAN
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hapus'])) {

    $id = trim($_POST['hapus']);

    if ($id === '' || !ctype_digit($id)) {

        $error = "ID pelanggan tidak valid.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM pelanggan
             WHERE id_pelanggan = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header("Location: pelanggan.php?success=deleted");
            exit;

        } else {

            $error = "Pelanggan tidak dapat dihapus karena sudah digunakan dalam transaksi.";
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

        $success = "Pelanggan berhasil ditambahkan.";

    } elseif ($_GET['success'] === 'updated') {

        $success = "Data pelanggan berhasil diperbarui.";

    } elseif ($_GET['success'] === 'deleted') {

        $success = "Pelanggan berhasil dihapus.";
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
                id_pelanggan,
                nama_pelanggan,
                alamat,
                no_hp
             FROM pelanggan
             WHERE id_pelanggan = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );

        mysqli_stmt_execute($stmt);

        $resultEdit = mysqli_stmt_get_result($stmt);

        $customer = mysqli_fetch_assoc($resultEdit);

        mysqli_stmt_close($stmt);

        if ($customer) {

            $editMode = true;
            $rowEdit = $customer;

        } else {

            $error = "Data pelanggan tidak ditemukan.";
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
            id_pelanggan,
            nama_pelanggan,
            alamat,
            no_hp
         FROM pelanggan
         WHERE nama_pelanggan LIKE ?
            OR alamat LIKE ?
            OR no_hp LIKE ?
         ORDER BY id_pelanggan DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $searchLike,
        $searchLike,
        $searchLike
    );

} else {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id_pelanggan,
            nama_pelanggan,
            alamat,
            no_hp
         FROM pelanggan
         ORDER BY id_pelanggan DESC"
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

    <title>Pelanggan - Web Kasir</title>

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
            Data Pelanggan
        </h2>

        <p class="text-muted mb-0">
            Kelola data pelanggan pada sistem kasir.
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


    <!-- FORM -->

    <div class="card shadow-sm mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                <?= $editMode
                    ? 'Edit Pelanggan'
                    : 'Tambah Pelanggan';
                ?>

            </h5>


            <form method="POST">


                <?php if ($editMode): ?>

                    <input
                        type="hidden"
                        name="id_pelanggan"
                        value="<?= e($rowEdit['id_pelanggan']); ?>"
                    >

                <?php endif; ?>


                <div class="row g-3">


                    <!-- NAMA -->

                    <div class="col-12 col-md-4">

                        <label
                            for="nama_pelanggan"
                            class="form-label"
                        >
                            Nama Pelanggan
                        </label>

                        <input
                            type="text"
                            id="nama_pelanggan"
                            name="nama_pelanggan"
                            class="form-control"
                            placeholder="Masukkan nama pelanggan"
                            value="<?= e($rowEdit['nama_pelanggan']); ?>"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- ALAMAT -->

                    <div class="col-12 col-md-4">

                        <label
                            for="alamat"
                            class="form-label"
                        >
                            Alamat
                        </label>

                        <input
                            type="text"
                            id="alamat"
                            name="alamat"
                            class="form-control"
                            placeholder="Masukkan alamat"
                            value="<?= e($rowEdit['alamat']); ?>"
                            required
                        >

                    </div>


                    <!-- NO HP -->

                    <div class="col-12 col-md-4">

                        <label
                            for="no_hp"
                            class="form-label"
                        >
                            No. HP
                        </label>

                        <input
                            type="text"
                            id="no_hp"
                            name="no_hp"
                            class="form-control"
                            placeholder="Masukkan nomor HP"
                            value="<?= e($rowEdit['no_hp']); ?>"
                            maxlength="20"
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
                                    href="pelanggan.php"
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
                        placeholder="Cari nama, alamat, atau nomor HP..."
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

            <h5 class="fw-bold mb-3">
                Daftar Pelanggan
            </h5>


            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-primary">

                        <tr>

                            <th class="text-center">
                                ID
                            </th>

                            <th>
                                Nama
                            </th>

                            <th>
                                Alamat
                            </th>

                            <th>
                                No. HP
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
                                    <?= e($row['id_pelanggan']); ?>
                                </td>


                                <td>
                                    <?= e($row['nama_pelanggan']); ?>
                                </td>


                                <td>
                                    <?= e($row['alamat']); ?>
                                </td>


                                <td>
                                    <?= e($row['no_hp']); ?>
                                </td>


                                <td class="text-center action-buttons">


                                    <a
                                        href="pelanggan.php?edit=<?= e($row['id_pelanggan']); ?>"
                                        class="btn btn-warning btn-sm"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?');"
                                    >

                                        <button
                                            type="submit"
                                            name="hapus"
                                            value="<?= e($row['id_pelanggan']); ?>"
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
                                Belum ada data pelanggan.
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