<?php

session_start();

$host = "localhost";
$user = "root";
$password = "mysql";
$database = "milk_tea_shop";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại");
}

mysqli_set_charset($conn, "utf8");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $passwordInput = trim($_POST['password']);

    if (empty($username) || empty($passwordInput)) {

        $error = "Vui lòng nhập đầy đủ thông tin";

    } else {

        $stmt = mysqli_prepare($conn, "
            SELECT * FROM users
            WHERE username = ?
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmt, "s", $username);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($passwordInput, $user['password'])) {

                session_regenerate_id(true);

                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];

                header("Location: ./index.php");
                exit;

            } else {

                $error = "Sai tài khoản hoặc mật khẩu";

            }

        } else {

            $error = "Sai tài khoản hoặc mật khẩu";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="vi">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Đăng nhập quản trị</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Be Vietnam Pro',sans-serif;
            background:#f5f5f5;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }

        .login-box{
            width:100%;
            max-width:420px;
            background:white;
            border-radius:28px;
            padding:32px 24px;
            box-shadow:0 20px 60px rgba(0,0,0,.08);
        }

        .logo{
            width:72px;
            height:72px;
            border-radius:20px;
            background:#111827;
            color:white;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
            margin:auto auto 22px;
        }

        .title{
            text-align:center;
            margin-bottom:8px;
            font-size:28px;
            font-weight:700;
            color:#111827;
        }

        .description{
            text-align:center;
            color:#6b7280;
            font-size:14px;
            line-height:1.6;
            margin-bottom:28px;
        }

        .form-group{
            margin-bottom:18px;
        }

        .form-label{
            display:block;
            margin-bottom:8px;
            font-size:14px;
            font-weight:500;
            color:#374151;
        }

        .form-control{
            width:100%;
            height:54px;
            border:1px solid #d1d5db;
            border-radius:16px;
            padding:0 16px;
            font-size:15px;
            outline:none;
            transition:.2s;
        }

        .form-control:focus{
            border-color:#111827;
        }

        .login-btn{
            width:100%;
            height:56px;
            border:none;
            border-radius:18px;
            background:#111827;
            color:white;
            font-size:15px;
            font-weight:600;
            cursor:pointer;
            transition:.2s;
        }

        .login-btn:hover{
            opacity:.92;
        }

        .error-box{
            background:#fef2f2;
            color:#dc2626;
            padding:14px 16px;
            border-radius:14px;
            margin-bottom:18px;
            font-size:14px;
        }

        .footer{
            text-align:center;
            margin-top:22px;
            color:#9ca3af;
            font-size:13px;
        }

    </style>

</head>
<body>

    <div class="login-box">

        <div class="logo">
            <i class="fa-solid fa-lock"></i>
        </div>

        <h1 class="title">
            Admin Login
        </h1>

        <p class="description">
            Đăng nhập quản trị hệ thống Tiệm Trà Sữa X
        </p>

        <?php if(!empty($error)): ?>

            <div class="error-box">
                <?= $error ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label class="form-label">
                    Tài khoản
                </label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Nhập tài khoản"
                    required
                >

            </div>

            <div class="form-group">

                <label class="form-label">
                    Mật khẩu
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Nhập mật khẩu"
                    required
                >

            </div>

            <button type="submit" class="login-btn">
                Đăng nhập quản trị
            </button>

        </form>

        <div class="footer">
            © 2026 Tiệm Trà Sữa X
        </div>

    </div>

</body>
</html>