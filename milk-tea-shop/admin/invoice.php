<?php

require_once "../core/db.php";
require_once "../core/auth.php";

/*
|--------------------------------------------------------------------------
| GET ORDER ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {

    header("Location: index.php?page=orders");
    exit;
}

$orderId = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| GET ORDER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
");

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {

    header("Location: index.php?page=orders");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET ORDER DETAILS
|--------------------------------------------------------------------------
*/

$details = $pdo->prepare("
    SELECT 
        order_details.*,
        products.name,
        products.image
    FROM order_details

    LEFT JOIN products
    ON order_details.product_id = products.id

    WHERE order_details.order_id = ?
");

$details->execute([$orderId]);

$items = $details->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| SHIPPING CALCULATION
|--------------------------------------------------------------------------
|
| dưới 2km free
| trên 2km:
| phí mở đầu 7k
| sau đó mỗi 2km +5k
|--------------------------------------------------------------------------
*/

$distanceKm = $order['distance_km'] ?? 1;

if($distanceKm < 2){

    $shippingFee = 0;

}else{

    /*
    |--------------------------------------------------------------------------
    | BASE SHIP
    |--------------------------------------------------------------------------
    */

    $shippingFee = 7000;

    /*
    |--------------------------------------------------------------------------
    | EXTRA DISTANCE
    |--------------------------------------------------------------------------
    */

    $extraKm = $distanceKm - 2;

    if($extraKm > 0){

        $shippingFee += ceil($extraKm / 2) * 5000;
    }
}

/*
|--------------------------------------------------------------------------
| SUBTOTAL
|--------------------------------------------------------------------------
*/

$subtotal = 0;

foreach($items as $item){

    $subtotal += (
        $item['price'] *
        $item['quantity']
    );
}

/*
|--------------------------------------------------------------------------
| FINAL TOTAL
|--------------------------------------------------------------------------
*/

$finalTotal = $subtotal + $shippingFee;

/*
|--------------------------------------------------------------------------
| STATUS LABEL
|--------------------------------------------------------------------------
*/

$statusLabels = [

    'pending' => 'Chờ xác nhận',

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

    <title>
        Chi tiết đơn hàng
    </title>

    <!-- BOXICONS -->

    <link 
        href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
        rel='stylesheet'
    >

    <!-- CSS -->

    <link 
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<div class="invoice-page">

    <!-- HEADER -->

    <div class="invoice-header">

        <div>

            <a 
                href="index.php?page=orders"
                class="back-btn"
            >

                <i class='bx bx-arrow-back'></i>

                Quay lại

            </a>

            <h1>

                <i class='bx bx-receipt'></i>

                Chi tiết đơn hàng #<?= str_pad(
                    $order['id'],
                    3,
                    '0',
                    STR_PAD_LEFT
                ) ?>

            </h1>

        </div>

        <button onclick="window.print()" class="print-btn">

            <i class='bx bx-printer'></i>

            In hóa đơn

        </button>

    </div>

    <!-- INFO -->

    <div class="info-grid">

        <!-- CUSTOMER -->

        <div class="card">

            <h3>

                <i class='bx bx-user'></i>

                Thông tin khách hàng

            </h3>

            <ul>

                <li>

                    <span>Họ tên:</span>

                    <strong>
                        <?= htmlspecialchars($order['customer_name']) ?>
                    </strong>

                </li>

                <li>

                    <span>Số điện thoại:</span>

                    <strong>
                        <?= $order['phone'] ?>
                    </strong>

                </li>

                <li>

                    <span>Địa chỉ:</span>

                    <strong>
                        <?= htmlspecialchars($order['address']) ?>
                    </strong>

                </li>

            </ul>

        </div>

        <!-- ORDER -->

        <div class="card">

            <h3>

                <i class='bx bx-package'></i>

                Thông tin đơn hàng

            </h3>

            <ul>

                <li>

                    <span>Trạng thái:</span>

                    <span class="status <?= $order['status'] ?>">

                        <?= $statusLabels[$order['status']] ?>

                    </span>

                </li>

                <li>

                    <span>Ngày đặt:</span>

                    <strong>

                        <?= date(
                            "d/m/Y H:i",
                            strtotime($order['created_at'])
                        ) ?>

                    </strong>

                </li>

                <!-- <li>

                    <span>Khoảng cách:</span>

                    <strong>

                        <?= $distanceKm ?> km

                    </strong>

                </li> -->

                <li>

                    <span>Phí ship:</span>

                    <strong>

                        7000đ

                    </strong>

                </li>

            </ul>

            <!-- SHIPPING NOTE -->

            <div class="shipping-note">

                <i class='bx bx-info-circle'></i>

                <div>

                    <h4>
                        Chính sách giao hàng
                    </h4>

                    <p>
                        Đơn dưới 2km được miễn phí vận chuyển.
                        Từ 2km trở lên tính phí cố định 7.000đ,
                        sau đó cộng thêm 5.000đ mỗi 2km tiếp theo.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <!-- PRODUCT TABLE -->

    <div class="card">

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>Sản phẩm</th>
                        <th>Ảnh</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach($items as $item): ?>

                        <tr>

                            <!-- NAME -->

                            <td>

                                <div class="product-name">

                                    <i class='bx bxs-coffee'></i>

                                    <?= htmlspecialchars($item['name']) ?>

                                </div>

                            </td>

                            <!-- IMAGE -->

                            <td>

                                <img 
                                    src="../uploads/products/<?= $item['image'] ?>"
                                    class="product-image"
                                >

                            </td>

                            <!-- PRICE -->

                            <td>

                                <?= number_format($item['price']) ?>đ

                            </td>

                            <!-- QUANTITY -->

                            <td>

                                x<?= $item['quantity'] ?>

                            </td>

                            <!-- TOTAL -->

                            <td>

                                <strong>

                                    <?= number_format(
                                        $item['price'] * $item['quantity']
                                    ) ?>đ

                                </strong>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- TOTAL -->

    <div class="summary-card">

        <div class="summary-row">

            <span>Tạm tính</span>

            <strong>

                <?= number_format(
                    $subtotal
                ) ?>đ

            </strong>

        </div>

        <div class="summary-row">

            <span>Phí giao hàng</span>

            <strong>

                <?= number_format(
                    $shippingFee
                ) ?>đ

            </strong>

        </div>

        <div class="summary-row total">

            <span>Tổng thanh toán</span>

            <strong>

                <?= number_format(
                    $finalTotal
                ) ?>đ

            </strong>

        </div>

    </div>

</div>

</body>
</html>