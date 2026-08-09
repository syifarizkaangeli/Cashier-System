<?php

session_start();

require_once "database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = "Username dan password wajib diisi.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id_admin, username, password
             FROM admin
             WHERE username = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $username
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $admin = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if ($admin && password_verify($password, $admin['password'])) {

            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_username'] = $admin['username'];

            header("Location: index.php");
            exit;

        } else {

            $error = "Username atau password salah.";
        }
    }
}

?>