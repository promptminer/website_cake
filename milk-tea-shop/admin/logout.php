<?php

session_start();

session_unset();

session_destroy();

?>

<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Đăng xuất</title>

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

.logout-box{
    width:100%;
    max-width:420px;
    background:#fff;
    border-radius:28px;
    padding:40px 28px;
    text-align:center;
    box-shadow:0 20px 60px rgba(0,0,0,.08);
}

.logout-icon{
    width:82px;
    height:82px;
    border-radius:24px;
    background:#111827;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
    margin:0 auto 24px;
    animation:pop .5s ease;
}

@keyframes pop{

    0%{
        transform:scale(.7);
        opacity:0;
    }

    100%{
        transform:scale(1);
        opacity:1;
    }

}

.logout-title{
    font-size:30px;
    font-weight:700;
    color:#111827;
    margin-bottom:10px;
}

.logout-description{
    color:#6b7280;
    font-size:15px;
    line-height:1.7;
    margin-bottom:28px;
}

.loading{
    width:42px;
    height:42px;
    border:4px solid #e5e7eb;
    border-top-color:#111827;
    border-radius:50%;
    margin:auto;
    animation:spin 1s linear infinite;
}

@keyframes spin{

    to{
        transform:rotate(360deg);
    }

}

.redirect-text{
    margin-top:18px;
    color:#9ca3af;
    font-size:13px;
}

</style>

<meta
    http-equiv="refresh"
    content="2;url=login.php"
>

</head>

<body>

<div class="logout-box">

    <div class="logout-icon">
        <i class="fa-solid fa-right-from-bracket"></i>
    </div>

    <h1 class="logout-title">
        Đăng xuất thành công
    </h1>

    <p class="logout-description">
        Phiên đăng nhập đã được kết thúc an toàn.
        Đang chuyển về trang đăng nhập...
    </p>

    <div class="loading"></div>

    <div class="redirect-text">
        Vui lòng chờ vài giây...
    </div>

</div>

</body>
</html>