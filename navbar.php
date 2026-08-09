<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);

function activeMenu($page, $currentPage)
{
    return $page === $currentPage ? 'active' : '';
}

?>

<nav class="navbar navbar-expand-lg navbar-warning bg-warning shadow-sm">

    <div class="container">

        <!-- BRAND -->
        <a
            class="navbar-brand fw-bold text-dark"
            href="index.php"
        >
            Web Kasir
        </a>


        <!-- MOBILE BUTTON -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarKasir"
            aria-controls="navbarKasir"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>


        <!-- MENU -->
        <div
            class="collapse navbar-collapse"
            id="navbarKasir"
        >

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <!-- HOME -->
                <li class="nav-item">

                    <a
                        class="nav-link <?= activeMenu('index.php', $currentPage); ?>"
                        href="index.php"
                    >
                        Home
                    </a>

                </li>


                <!-- TRANSAKSI -->
                <li class="nav-item">

                    <a
                        class="nav-link <?= activeMenu('transaksi.php', $currentPage); ?>"
                        href="transaksi.php"
                    >
                        Transaksi
                    </a>

                </li>


                <!-- PELANGGAN -->
                <li class="nav-item">

                    <a
                        class="nav-link <?= activeMenu('pelanggan.php', $currentPage); ?>"
                        href="pelanggan.php"
                    >
                        Pelanggan
                    </a>

                </li>


                <!-- HISTORI -->
                <li class="nav-item">

                    <a
                        class="nav-link <?= activeMenu('histori.php', $currentPage); ?>"
                        href="histori.php"
                    >
                        Histori
                    </a>

                </li>


                <!-- STOK -->
                <li class="nav-item">

                    <a
                        class="nav-link <?= activeMenu('stok.php', $currentPage); ?>"
                        href="stok.php"
                    >
                        Stok
                    </a>

                </li>

            </ul>


            <!-- ADMIN + LOGOUT -->
            <div class="d-flex align-items-center gap-2">

                <?php if (isset($_SESSION['username'])): ?>

                    <span class="fw-semibold text-dark">
                        <?= htmlspecialchars($_SESSION['username']); ?>
                    </span>

                <?php endif; ?>


                <a
                    href="logout.php"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Yakin ingin logout?');"
                >
                    Logout
                </a>

            </div>

        </div>

    </div>

</nav>


<style>

    .navbar-brand {
        letter-spacing: 0.3px;
    }

    .navbar-nav .nav-link {
        padding: 8px 14px;
        margin: 2px;
        border-radius: 7px;
        font-weight: 500;
        transition: 0.2s ease;
    }

    .navbar-nav .nav-link:hover {
        background: rgba(0, 0, 0, 0.08);
    }

    .navbar-nav .nav-link.active {
        background: rgba(0, 0, 0, 0.12);
        font-weight: 700;
    }

    @media (max-width: 991px) {

        .navbar-nav {
            margin-top: 10px;
        }

        .navbar-nav .nav-link {
            margin-bottom: 3px;
        }

        .navbar-collapse > .d-flex {
            margin-top: 10px;
            padding-bottom: 8px;
        }

    }

    @media (max-width: 575px) {

        .navbar-collapse > .d-flex {
            flex-direction: column;
            align-items: stretch !important;
        }

        .navbar-collapse > .d-flex .btn {
            width: 100%;
        }

        .navbar-collapse > .d-flex span {
            text-align: center;
            margin-bottom: 5px;
        }

    }

</style>