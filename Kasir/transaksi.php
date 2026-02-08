<?php
session_start();
include "database.php";
include "navbar.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['add'])) {
    $id_barang = (int)$_GET['add'];

    $q = mysqli_query($conn, "SELECT stok FROM barang WHERE id_barang=$id_barang");
    $b = mysqli_fetch_assoc($q);

    if ($b) {
        $stok = $b['stok'];
        $qty_sekarang = $_SESSION['cart'][$id_barang] ?? 0;

        if ($qty_sekarang < $stok) {
            $_SESSION['cart'][$id_barang] = $qty_sekarang + 1;
        }
    }
    header("Location: transaksi.php");
    exit;
}

if (isset($_POST['update_qty'])) {
    $id_barang = (int)$_POST['id_barang'];
    $qty       = (int)$_POST['qty'];

    $q = mysqli_query($conn, "SELECT stok FROM barang WHERE id_barang=$id_barang");
    $b = mysqli_fetch_assoc($q);

    if ($b) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id_barang]);
        } elseif ($qty > $b['stok']) {
            $_SESSION['cart'][$id_barang] = $b['stok'];
        } else {
            $_SESSION['cart'][$id_barang] = $qty;
        }
    }
    header("Location: transaksi.php");
    exit;
}

$kembalian = null;
$error_bayar = null;

if (isset($_POST['proses_bayar'])) {

    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $uang_bayar   = (int)$_POST['bayar'];
    $total = 0;

    foreach ($_SESSION['cart'] as $id_barang => $qty) {
        $q = mysqli_query($conn, "
            SELECT harga, stok
            FROM barang
            WHERE id_barang=$id_barang
        ");
        $b = mysqli_fetch_assoc($q);

        if ($qty > $b['stok']) {
            $error_bayar = "Stok tidak cukup";
            break;
        }

        $total += $b['harga'] * $qty;
    }

    if (!$error_bayar) {
        if ($uang_bayar < $total) {
            $error_bayar = "Uang bayar kurang";
        } else {

            foreach ($_SESSION['cart'] as $id_barang => $qty) {

                $q = mysqli_query($conn, "
                    SELECT harga
                    FROM barang
                    WHERE id_barang=$id_barang
                ");
                $b = mysqli_fetch_assoc($q);

                $harga    = $b['harga'];
                $subtotal = $harga * $qty;

                mysqli_query($conn, "
                    INSERT INTO penjualan
                    (id_pelanggan, id_barang, jumlah, harga, subtotal, total_harga, waktu)
                    VALUES
                    ($id_pelanggan, $id_barang, $qty, $harga, $subtotal, $total, NOW())
                ");

                mysqli_query($conn, "
                    UPDATE barang
                    SET stok = stok - $qty
                    WHERE id_barang = $id_barang
                ");
            }

            $kembalian = $uang_bayar - $total;
            unset($_SESSION['cart']);
        }
    }
}

$search = $_GET['search'] ?? '';
$search_safe = mysqli_real_escape_string($conn, $search);

$barang = mysqli_query($conn, "
    SELECT *
    FROM barang
    WHERE nama_barang LIKE '%$search_safe%'
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaksi</title>
    <link rel="stylesheet" href="bootstrap-5.3.8-dist\css\bootstrap.min.css">
</head>
<body>

<div class="container mt-3">
<div class="row">

<!-- DAFTAR BARANG -->
<div class="col-md-7">
    <h5>Daftar Barang</h5>

    <form method="GET" class="mb-2">
        <input type="text" name="search" class="form-control"
               placeholder="Cari barang"
               value="<?= htmlspecialchars($search); ?>">
    </form>

    <table class="table table-bordered table-sm">
        <thead class="table-secondary">
        <tr>
            <th>Nama</th>
            <th>Stok</th>
            <th>Harga</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
        <tr>
            <td><?= $b['nama_barang']; ?></td>
            <td><?= $b['stok']; ?></td>
            <td>Rp <?= number_format($b['harga']); ?></td>
            <td>
                <a href="?add=<?= $b['id_barang']; ?>" class="btn btn-sm btn-success">
                    Beli
                </a>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- TRANSAKSI -->
<div class="col-md-5">
    <h5>Transaksi</h5>

    <table class="table table-bordered table-sm">
        <thead class="table-secondary">
        <tr>
            <th>Barang</th>
            <th width="120">Qty</th>
            <th>Subtotal</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $total_view = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id_barang => $qty) {
                $q = mysqli_query($conn, "
                    SELECT *
                    FROM barang
                    WHERE id_barang=$id_barang
                ");
                $item = mysqli_fetch_assoc($q);
                $sub = $item['harga'] * $qty;
                $total_view += $sub;
        ?>
        <tr>
            <td><?= $item['nama_barang']; ?></td>
            <td>
                <form method="POST" class="d-flex">
                    <input type="hidden" name="id_barang" value="<?= $id_barang; ?>">
                    <input type="number" name="qty" min="0"
                           value="<?= $qty; ?>"
                           class="form-control form-control-sm">
                    <button name="update_qty"
                            class="btn btn-sm btn-secondary ms-1">✓</button>
                </form>
            </td>
            <td>Rp <?= number_format($sub); ?></td>
        </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='3' class='text-center'>Belum ada transaksi</td></tr>";
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="2">Total</th>
            <th>Rp <?= number_format($total_view); ?></th>
        </tr>
        </tfoot>
    </table>

    <form method="POST">
        <select name="id_pelanggan" class="form-control mb-2" required>
            <option value="">-- Pilih Pelanggan --</option>
            <?php
            $plg = mysqli_query($conn, "SELECT * FROM pelanggan");
            while ($p = mysqli_fetch_assoc($plg)) {
                echo "<option value='{$p['id_pelanggan']}'>
                        {$p['nama_pelanggan']} - {$p['no_hp']}
                      </option>";
            }
            ?>
        </select>

        <input type="number" name="bayar" class="form-control mb-2"
               placeholder="Uang Bayar" required>

        <button name="proses_bayar"
                class="btn btn-primary w-100"
                <?= empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
            Bayar
        </button>
    </form>

    <?php if ($error_bayar) { ?>
        <div class="alert alert-danger mt-2"><?= $error_bayar; ?></div>
    <?php } ?>

    <?php if ($kembalian !== null) { ?>
        <div class="alert alert-success mt-2">
            Kembalian: Rp <?= number_format($kembalian); ?>
        </div>
    <?php } ?>
</div>

</div>
</div>
</body>
</html>
