<!-- # invoice.php (đã tối ưu in bill K80 - bỏ ảnh + bỏ phí ship)

```php -->
<?php
require_once "../core/auth.php";
require_once "../core/db.php";

if(!isset($_GET['id'])){
    header("Location: index.php?page=orders");
    exit;
}

$orderId = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
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
    background:#f5f5f5;
    padding:20px;
}

.invoice-wrap{
    width:80mm;
    margin:auto;
    background:#fff;
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
    line-height:1.6;
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
    text-align:left;
    vertical-align:top;
}

.qty,
.price,
.total{
    text-align:right;
}

.summary{
    margin-top:10px;
    border-top:1px dashed #000;
    padding-top:10px;
    font-size:13px;
}

.summary-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:6px;
}

.final{
    font-size:15px;
    font-weight:bold;
}

.footer{
    text-align:center;
    margin-top:16px;
    font-size:12px;
    line-height:1.6;
}

.print-btn{
    display:block;
    width:80mm;
    margin:15px auto;
    border:none;
    background:#111;
    color:#fff;
    padding:12px;
    cursor:pointer;
    border-radius:8px;
    font-size:14px;
}

@media print{

    body{
        background:#fff;
        padding:0;
    }

    .print-btn{
        display:none;
    }

    .invoice-wrap{
        width:80mm;
        padding:0;
        margin:0;
    }

    @page{
        size:80mm auto;
        margin:5mm;
    }

}

</style>

</head>
<body>

<button onclick="window.print()" class="print-btn">
    <i class='bx bx-printer'></i>
    In hóa đơn
</button>

<div class="invoice-wrap">

    <div class="shop-name">
        <h1>ARPINA</h1>
        <p>Hotline: 0819.180.009</p>
        <p>Cảm ơn quý khách</p>
    </div>

    <div class="bill-title">
        HÓA ĐƠN THANH TOÁN
    </div>

    <div class="info">

        <div>
            <strong>Mã đơn:</strong>
            #<?= str_pad($order['id'],3,'0',STR_PAD_LEFT) ?>
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
                    <?= number_format($item['price'] * $item['quantity']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

    <div class="summary">

        <div class="summary-row final">
            <span>TỔNG</span>
            <span><?= number_format($finalTotal) ?>đ</span>
        </div>

    </div>

    <div class="footer">
        <p>Thanh toán COD</p>
        <p>Hẹn gặp lại quý khách ❤️</p>
    </div>

</div>

</body>
</html>
<!-- ```

Các thay đổi chính:

* Khổ in chuẩn K80.
* Xóa hoàn toàn ảnh sản phẩm.
* Xóa phí vận chuyển.
* Layout tối giản để máy POS nhiệt in đẹp.
* Tự căn khổ khi `window.print()`.
* Font và spacing tối ưu cho bill nhiệt.
* Giữ nguyên logic lấy đơn hàng. -->
