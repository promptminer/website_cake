<?php

session_start();

$host     = "localhost";
$user     = "root";
$password = "mysql";
$database = "milk_tea_shop";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại");
}

mysqli_set_charset($conn, "utf8");

$phone = '';

if(isset($_GET['phone'])){
    $phone = mysqli_real_escape_string($conn, trim($_GET['phone']));
}

$orders = false;

if($phone != ''){

    $orders = mysqli_query($conn, "
        SELECT *
        FROM orders
        WHERE phone LIKE '%$phone%'
        ORDER BY id DESC
    ");

}

?>

<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Đơn hàng của tôi</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#f5f5f5;
    padding:20px;
    color:#111827;
}

.container{
    max-width:900px;
    margin:auto;
}

.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:20px;
}

.back-btn{
    width:45px;
    height:45px;
    border-radius:12px;
    background:white;
    border:1px solid #ddd;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    color:#111827;
}

.title{
    font-size:28px;
    font-weight:bold;
}

.search-box{
    display:flex;
    gap:10px;
    margin-bottom:24px;
}

.search-input{
    flex:1;
    height:52px;
    border-radius:14px;
    border:1px solid #ddd;
    padding:0 16px;
    font-size:15px;
}

.search-btn{
    padding:0 24px;
    border:none;
    border-radius:14px;
    background:#111827;
    color:white;
    font-weight:bold;
    cursor:pointer;
}

.card{
    background:white;
    border-radius:18px;
    padding:20px;
    margin-bottom:16px;
    border:1px solid #eee;
}

.top{
    display:flex;
    justify-content:space-between;
    gap:10px;
    margin-bottom:12px;
}

.order-id{
    font-size:20px;
    font-weight:bold;
    margin-bottom:5px;
}

.customer{
    color:#6b7280;
}

.status{
    padding:8px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:bold;
    height:fit-content;
}

.pending{
    background:#fef3c7;
    color:#92400e;
}

.shipping{
    background:#dbeafe;
    color:#1d4ed8;
}

.delivered{
    background:#dcfce7;
    color:#166534;
}

.cancelled{
    background:#fee2e2;
    color:#b91c1c;
}

.info{
    margin-top:8px;
    color:#374151;
    line-height:1.6;
}

.price{
    font-size:26px;
    font-weight:bold;
    margin-top:18px;
}

.item{
    margin-top:12px;
    padding-top:12px;
    border-top:1px dashed #ddd;
    color:#374151;
}

.empty{
    background:white;
    padding:40px;
    border-radius:18px;
    text-align:center;
    border:1px solid #eee;
}

.empty i{
    font-size:50px;
    color:#9ca3af;
    margin-bottom:12px;
}

.empty h2{
    margin-bottom:8px;
}

.empty p{
    color:#6b7280;
}

</style>

</head>
<body>

<div class="container">

    <div class="header">

        <a href="./index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="title">
            Đơn hàng của tôi
        </div>

        <div></div>

    </div>

    <!-- FORM SEARCH -->
    <form method="GET" class="search-box">

        <input
            type="text"
            name="phone"
            class="search-input"
            placeholder="Nhập số điện thoại..."
            value="<?= htmlspecialchars($phone) ?>"
            required
        >

        <button type="submit" class="search-btn">
            Xem đơn
        </button>

    </form>

    <?php if($phone == ''): ?>

        <div class="empty">

            <i class="fa-solid fa-receipt"></i>

            <h2>Tra cứu đơn hàng</h2>

            <p>
                Nhập số điện thoại để xem đơn hàng của bạn
            </p>

        </div>

    <?php elseif($orders && mysqli_num_rows($orders) > 0): ?>

        <?php while($order = mysqli_fetch_assoc($orders)): ?>

            <?php

                $statusClass = $order['status'];

            ?>

            <div class="card">

                <div class="top">

                    <div>

                        <div class="order-id">
                            Đơn #<?= $order['id'] ?>
                        </div>

                        <div class="customer">
                            <?= htmlspecialchars($order['customer_name']) ?>
                        </div>

                    </div>

                    <div class="status <?= $statusClass ?>">
                        <?= $order['status'] ?>
                    </div>

                </div>

                <div class="info">
                    <strong>SĐT:</strong>
                    <?= htmlspecialchars($order['phone']) ?>
                </div>

                <div class="info">
                    <strong>Địa chỉ:</strong>
                    <?= htmlspecialchars($order['address']) ?>
                </div>

                <div class="price">
                    <?= number_format($order['total_price'],0,',','.') ?>đ
                </div>

                <?php

                $orderId = $order['id'];

                $details = mysqli_query($conn, "
                    SELECT od.*, p.name
                    FROM order_details od
                    INNER JOIN products p ON od.product_id = p.id
                    WHERE od.order_id = '$orderId'
                ");

                ?>

                <?php while($item = mysqli_fetch_assoc($details)): ?>

                    <div class="item">

                        <?= htmlspecialchars($item['name']) ?>

                        x <?= $item['quantity'] ?>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">

            <i class="fa-solid fa-circle-xmark"></i>

            <h2>Không tìm thấy đơn hàng</h2>

            <p>
                Không có đơn hàng nào với số điện thoại này
            </p>

        </div>

    <?php endif; ?>

</div>

</body>
</html>