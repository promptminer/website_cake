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

$orderId = $_GET['order_id'] ?? '';

?>
<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Đặt hàng thành công</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Be Vietnam Pro',sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;

    background:
    linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),
    url('https://images.unsplash.com/photo-1515823064-d6e0c04616a7?q=80&w=1600&auto=format&fit=crop');

    background-size:cover;
    background-position:center;
}

.success-box{
    width:100%;
    max-width:520px;
    margin:20px;
    background:rgba(255,255,255,.95);
    backdrop-filter:blur(12px);
    border-radius:32px;
    padding:40px 32px;
    text-align:center;
    box-shadow:0 25px 60px rgba(0,0,0,.18);

    animation:fadeIn .6s ease;
}

@keyframes fadeIn{

    from{
        opacity:0;
        transform:translateY(20px) scale(.96);
    }

    to{
        opacity:1;
        transform:translateY(0) scale(1);
    }

}

.success-icon{
    width:110px;
    height:110px;
    border-radius:50%;
    margin:auto;
    margin-bottom:26px;

    background:linear-gradient(135deg,#22c55e,#16a34a);

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;
    font-size:48px;

    box-shadow:0 18px 40px rgba(34,197,94,.35);

    animation:pulse 1.6s infinite;
}

@keyframes pulse{

    0%{
        transform:scale(1);
    }

    50%{
        transform:scale(1.06);
    }

    100%{
        transform:scale(1);
    }

}

.title{
    font-size:34px;
    font-weight:800;
    color:#111827;
    margin-bottom:14px;
}

.desc{
    font-size:16px;
    line-height:1.7;
    color:#4b5563;
    margin-bottom:24px;
}

.order-id{
    display:inline-flex;
    align-items:center;
    gap:8px;

    padding:12px 18px;
    border-radius:999px;

    background:#f3f4f6;

    font-size:14px;
    font-weight:600;
    color:#111827;

    margin-bottom:28px;
}

.redirect{
    font-size:14px;
    color:#6b7280;
}

.loader{
    width:44px;
    height:44px;
    border:4px solid #e5e7eb;
    border-top-color:#111827;
    border-radius:50%;
    margin:22px auto 0;

    animation:spin .7s linear infinite;
}

@keyframes spin{

    to{
        transform:rotate(360deg);
    }

}

.progress{
    width:100%;
    height:8px;
    border-radius:999px;
    background:#e5e7eb;
    overflow:hidden;
    margin-top:26px;
}

.progress-bar{
    height:100%;
    width:100%;
    background:#111827;

    animation:progress 2s linear forwards;
}

@keyframes progress{

    from{
        width:100%;
    }

    to{
        width:0%;
    }

}

@media(max-width:600px){

    .success-box{
        padding:34px 22px;
        border-radius:26px;
    }

    .title{
        font-size:28px;
    }

}

</style>

</head>
<body>

<div class="success-box">

    <div class="success-icon">
        <i class="fa-solid fa-check"></i>
    </div>

    <h1 class="title">
        Cảm ơn bạn!
    </h1>

    <p class="desc">
        Đơn hàng của bạn đã được đặt thành công !<br>
        Tiệm sẽ liên hệ xác nhận và giao hàng sớm nhất.
    </p>

    <?php if($orderId): ?>

        <div class="order-id">
            <i class="fa-solid fa-receipt"></i>
            Mã đơn hàng #<?= $orderId ?>
        </div>

    <?php endif; ?>

    <div class="redirect">
        Đang quay về trang chủ...
    </div>

    <div class="loader"></div>

    <div class="progress">
        <div class="progress-bar"></div>
    </div>

</div>

<script>

setTimeout(()=>{

    window.location.href = './index.php';

},3000);

</script>

</body>
</html>