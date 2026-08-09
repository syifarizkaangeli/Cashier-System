<?php
session_start();
include 'database.php';

if(isset($_POST['username'])){
    $u=$_POST['username'];
    $p=$_POST['password'];

    $d=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE username='$u'"));

    if($d && password_verify($p, $d['password'])){
        $_SESSION['admin']=$u;
        header('location: index.php'); exit;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir</title>
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist\css\bootstrap.min.css">
    <div class="card p-4 shadow" style="width:320px">
        <h5 class="text-center mb-3">Login Kasir</h5>
        <form method="post">
            <input name="username" placeholder="Username" class="form-control mb-2">
            <input name="password" type="password" placeholder="Password" class="form-control mb-2">
            <button name="login" class="btn btn-dark w-100">Login</button>
        </form>
    </div>
</body>
</html>