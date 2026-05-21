<?php

require_once "../core/auth.php";
require_once "../core/db.php";

if(!isset($_GET['id'])){
    header("Location: index.php?page=orders");
    exit;
}

$orderId = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
");

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order){
    header("Location: index.php?page=orders");
    exit;
}

$detailStmt = $pdo->prepare("
    SELECT
        order_details.id,
        order_details.quantity,
        order_details.price,
        products.name
    FROM order_details
    LEFT JOIN products
    ON order_details.product_id = products.id
    WHERE order_details.order_id = ?
    ORDER BY order_details.id DESC
");

$detailStmt->execute([$orderId]);

$orderItems = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

$productTotal = 0;

foreach($orderItems as $item){

    $productTotal += (
        $item['price'] *
        $item['quantity']
    );

}

$finalTotal = $productTotal;

$statusLabels = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy'
];

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>In hóa đơn</title>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    background:#f3f4f6;
    padding:20px;
}

/* ACTION BUTTONS */

.top-actions{
    width:80mm;
    margin:0 auto 14px;
    display:flex;
    gap:10px;
}

.back-btn,
.print-btn{
    flex:1;
    border:none;
    border-radius:10px;
    padding:12px;
    font-size:14px;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    cursor:pointer;
    transition:.2s;
}

.back-btn{
    background:#e5e7eb;
    color:#111827;
}

.back-btn:hover{
    background:#d1d5db;
}

.print-btn{
    background:#111827;
    color:white;
}

.print-btn:hover{
    opacity:.9;
}

/* BILL */

.invoice-wrap{
    width:80mm;
    margin:auto;
    background:white;
    padding:12px;
    color:#000;
}

.shop-name{
    text-align:center;
    margin-bottom:10px;
}

.shop-name h1{
    font-size:18px;
    margin-bottom:4px;
}

.shop-name p{
    font-size:12px;
    line-height:1.5;
}

.bill-title{
    text-align:center;
    font-size:16px;
    font-weight:bold;
    margin:12px 0;
}

.info{
    font-size:12px;
    margin-bottom:10px;
    line-height:1.7;
}

.info div{
    margin-bottom:3px;
}

table{
    width:100%;
    border-collapse:collapse;
    font-size:12px;
}

thead{
    border-top:1px dashed #000;
    border-bottom:1px dashed #000;
}

th,
td{
    padding:6px 0;
    vertical-align:top;
}

th{
    font-weight:bold;
}

.qty,
.price,
.total{
    text-align:right;
    white-space:nowrap;
}

.summary{
    margin-top:10px;
    border-top:1px dashed #000;
    padding-top:10px;
}

.summary-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:6px;
    font-size:13px;
}

.final{
    font-size:16px;
    font-weight:bold;
}

.footer{
    text-align:center;
    margin-top:16px;
    font-size:12px;
    line-height:1.7;
}

/* PRINT */

@media print{

    body{
        background:white;
        padding:0;
    }

    .top-actions{
        display:none;
    }

    .invoice-wrap{
        width:80mm;
        padding:0;
        margin:0;
        box-shadow:none;
    }

    @page{
        size:80mm auto;
        margin:4mm;
    }

}

</style>

</head>

<body>

<div class="top-actions">

    <a href="index.php?page=orders" class="back-btn">
        <i class='bx bx-arrow-back'></i>
        Quay về
    </a>

    <button onclick="window.print()" class="print-btn">
        <i class='bx bx-printer'></i>
        In hóa đơn
    </button>

</div>

<div class="invoice-wrap">

    <div class="shop-name">

        <h1>ARPINA</h1>

        <p>Hotline: 0819.180.009</p>

        <p>Cảm ơn quý khách ❤️</p>

    </div>

    <div class="bill-title">
        HÓA ĐƠN THANH TOÁN
    </div>

    <div class="info">

        <div>
            <strong>Mã đơn:</strong>
            #<?= str_pad($order['id'], 3, '0', STR_PAD_LEFT) ?>
        </div>

        <div>
            <strong>Khách:</strong>
            <?= htmlspecialchars($order['customer_name']) ?>
        </div>

        <div>
            <strong>SĐT:</strong>
            <?= htmlspecialchars($order['phone']) ?>
        </div>

        <div>
            <strong>Địa chỉ:</strong>
            <?= htmlspecialchars($order['address']) ?>
        </div>

        <div>
            <strong>Ngày:</strong>
            <?= date("d/m/Y H:i", strtotime($order['created_at'])) ?>
        </div>

        <div>
            <strong>Trạng thái:</strong>
            <?= $statusLabels[$order['status']] ?>
        </div>

    </div>

    <table>

        <thead>

            <tr>

                <th>Sản phẩm</th>

                <th class="qty">SL</th>

                <th class="price">Giá</th>

                <th class="total">TT</th>

            </tr>

        </thead>

        <tbody>

        <?php foreach($orderItems as $item): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($item['name']) ?>
                </td>

                <td class="qty">
                    <?= $item['quantity'] ?>
                </td>

                <td class="price">
                    <?= number_format($item['price']) ?>
                </td>

                <td class="total">
                    <?= number_format($item['price'] * $item['quantity']) ?>đ
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

    <div class="summary">

        <div class="summary-row final">

            <span>TỔNG</span>

            <span>
                <?= number_format($finalTotal) ?>đ
            </span>

        </div>

    </div>

    <div class="footer">

        <p>Thanh toán COD</p>

        <p>Hẹn gặp lại quý khách ❤️</p>

    </div>

</div>

</body>
</html>