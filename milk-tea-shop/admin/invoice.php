<?php
require_once "../core/auth.php";
require_once "../core/db.php";

/*
|--------------------------------------------------------------------------
| GET ORDER
|--------------------------------------------------------------------------
*/

if(!isset($_GET['id'])){

    header("Location: index.php?page=orders");
    exit;

}

$orderId = (int)$_GET['id'];

/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
|
| lấy đúng dữ liệu từ bảng orders
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| ORDER DETAILS
|--------------------------------------------------------------------------
|
| lấy đúng giá từ order_details.price
|--------------------------------------------------------------------------
*/

$detailStmt = $pdo->prepare("
    SELECT

        order_details.id,
        order_details.quantity,
        order_details.price,

        products.name,
        products.image

    FROM order_details

    LEFT JOIN products
    ON order_details.product_id = products.id

    WHERE order_details.order_id = ?

    ORDER BY order_details.id DESC
");

$detailStmt->execute([$orderId]);

$orderItems = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| PRODUCT TOTAL
|--------------------------------------------------------------------------
*/

$productTotal = 0;

foreach($orderItems as $item){

    $productTotal += (
        $item['price'] *
        $item['quantity']
    );

}

/*
|--------------------------------------------------------------------------
| SHIPPING
|--------------------------------------------------------------------------
*/

$shippingFee = 5000; // Cố định 5.000đ phí ship

/*
|--------------------------------------------------------------------------
| FINAL TOTAL
|--------------------------------------------------------------------------
*/

$finalTotal = (
    $productTotal +
    $shippingFee
);

/*
|--------------------------------------------------------------------------
| STATUS LABEL
|--------------------------------------------------------------------------
*/

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

    <meta 
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Chi tiết đơn hàng
    </title>

    <link 
        href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
        rel='stylesheet'
    >

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

        <button 
            onclick="window.print()"
            class="print-btn"
        >

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

                    <span>Khách hàng:</span>

                    <strong>

                        <?= htmlspecialchars(
                            $order['customer_name']
                        ) ?>

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

                        <?= htmlspecialchars(
                            $order['address']
                        ) ?>

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

                        <?= $statusLabels[
                            $order['status']
                        ] ?>

                    </span>

                </li>

                <li>

                    <span>Ngày tạo:</span>

                    <strong>

                        <?= date(
                            "d/m/Y H:i",
                            strtotime(
                                $order['created_at']
                            )
                        ) ?>

                    </strong>

                </li>

                <li>

                    <span>Phí vận chuyển:</span>

                    <strong>

                       .....

                    </strong>

                </li>

            </ul>

            <!-- SHIPPING NOTE -->

            <div class="shipping-note">

                <i class='bx bx-info-circle'></i>

                <div>

                    <h4>
                        Chính sách vận chuyển
                    </h4>

                    <p>
                        Đơn dưới 2km miễn phí vận chuyển.
                        Trên 2km tính 5000đ,
                        sau đó mỗi 2km tiếp theo cộng thêm 5.000đ.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <!-- TABLE -->

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

                    <?php foreach($orderItems as $item): ?>

                        <tr>

                            <!-- PRODUCT -->

                            <td>

                                <div class="product-name">

                                    <i class='bx bxs-coffee'></i>

                                    <?= htmlspecialchars(
                                        $item['name']
                                    ) ?>

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

                                <?= number_format(
                                    $item['price']
                                ) ?>đ

                            </td>

                            <!-- QUANTITY -->

                            <td>

                                x<?= $item['quantity'] ?>

                            </td>

                            <!-- ITEM TOTAL -->

                            <td>

                                <strong>

                                    <?= number_format(
                                        $item['price'] *
                                        $item['quantity']
                                    ) ?>đ

                                </strong>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- SUMMARY -->

    <div class="summary-card">

        <!-- PRODUCT TOTAL -->

        <div class="summary-row">

            <span>
                Tiền sản phẩm
            </span>

            <strong>

                <?= number_format(
                    $productTotal
                ) ?>đ

            </strong>

        </div>

        <!-- SHIPPING -->

        <div class="summary-row">

            <span>
                Phí vận chuyển
            </span>

            <strong>

                <?= number_format(
                    $shippingFee
                ) ?>đ

            </strong>

        </div>

        <!-- FINAL -->

        <div class="summary-row total">

            <span>
                Tổng thanh toán
            </span>

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