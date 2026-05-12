<?php
session_start();

require_once "../core/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user['username'];

        header("Location: index.php");
        exit;

    } else {
        $error = "Sai tài khoản hoặc mật khẩu";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>

    <style>
        body{
            font-family: Arial;
            background:#f5f5f5;
        }

        .box{
            width:350px;
            margin:100px auto;
            background:#fff;
            padding:25px;
            border-radius:10px;
        }

        input{
            width:100%;
            padding:12px;
            margin-bottom:15px;
            box-sizing:border-box;
        }

        button{
            width:100%;
            padding:12px;
            border:none;
            background:black;
            color:white;
            cursor:pointer;
        }

        .error{
            color:red;
            margin-bottom:10px;
        }
    </style>
</head>
<body>

<div class="box">

    <h2>Admin Login</h2>

    <?php if($error): ?>
        <div class="error">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input 
            type="text" 
            name="username" 
            placeholder="Username"
            required
        >

        <input 
            type="password" 
            name="password" 
            placeholder="Password"
            required
        >

        <button type="submit">
            Đăng nhập
        </button>

    </form>

</div>

</body>
</html>