<?php
require_once "../core/auth.php";
require_once "../core/db.php";

/*
|--------------------------------------------------------------------------
| AUTO DELETE OLD ORDERS
|--------------------------------------------------------------------------
*/

$pdo->query("
    DELETE FROM orders
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH)
");

/*
|--------------------------------------------------------------------------
| UPDATE STATUS
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_status'])) {

    $orderId = (int) $_POST['order_id'];

    $status = $_POST['status'];

    /*
    |--------------------------------------------------------------------------
    | GET CURRENT STATUS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT status
        FROM orders
        WHERE id = ?
    ");

    $stmt->execute([$orderId]);

    $currentOrder = $stmt->fetch(PDO::FETCH_ASSOC);

    $currentStatus = $currentOrder['status'];

    /*
    |--------------------------------------------------------------------------
    | ALLOWED FLOW
    |--------------------------------------------------------------------------
    */

    $allowedTransitions = [

        'pending' => [
            'confirmed',
            'cancelled'
        ],

        'confirmed' => [
            'shipping',
            'cancelled'
        ],

        'shipping' => [
            'delivered'
        ],

        'delivered' => [],

        'cancelled' => []

    ];

    /*
    |--------------------------------------------------------------------------
    | VALIDATE TRANSITION
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $status,
            $allowedTransitions[$currentStatus]
        )
    ) {

        $update = $pdo->prepare("
            UPDATE orders
            SET status = ?
            WHERE id = ?
        ");

        $update->execute([
            $status,
            $orderId
        ]);
    }

    echo "
<script>
    window.location.href='index.php?page=orders';
</script>
";
    exit;
}

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$whereConditions = [];

/*
|--------------------------------------------------------------------------
| FILTER STATUS
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['status']) &&
    $_GET['status'] != ""
) {

    $status = $_GET['status'];

    $whereConditions[] = "
        orders.status = '$status'
    ";
}

/*
|--------------------------------------------------------------------------
| FILTER DATE
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['date']) &&
    $_GET['date'] != ""
) {

    $date = $_GET['date'];

    $whereConditions[] = "
        DATE(orders.created_at) = '$date'
    ";
}

/*
|--------------------------------------------------------------------------
| BUILD WHERE
|--------------------------------------------------------------------------
*/

$where = "";

if (count($whereConditions) > 0) {

    $where = "WHERE " . implode(
        " AND ",
        $whereConditions
    );
}

/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

$limit = 10;

$pageNumber = isset($_GET['p'])
    ? (int) $_GET['p']
    : 1;

if ($pageNumber < 1) {
    $pageNumber = 1;
}

$offset = ($pageNumber - 1) * $limit;

/*
|--------------------------------------------------------------------------
| TOTAL ROWS
|--------------------------------------------------------------------------
*/

$totalRowsQuery = $pdo->query("
    SELECT COUNT(*)
    FROM orders
    $where
");

$totalRows = $totalRowsQuery->fetchColumn();

$totalPages = ceil($totalRows / $limit);

/*
|--------------------------------------------------------------------------
| GET ORDERS
|--------------------------------------------------------------------------
*/

$orders = $pdo->query("
    SELECT 
        orders.*,
        COUNT(order_details.id) AS total_items
    FROM orders

    LEFT JOIN order_details
    ON orders.id = order_details.order_id

    $where

    GROUP BY orders.id

    ORDER BY orders.created_at DESC

    LIMIT $limit OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalOrders = $pdo->query("
    SELECT COUNT(*) FROM orders
")->fetchColumn();

$pendingOrders = $pdo->query("
    SELECT COUNT(*) FROM orders
    WHERE status = 'pending'
")->fetchColumn();

$shippingOrders = $pdo->query("
    SELECT COUNT(*) FROM orders
    WHERE status = 'shipping'
")->fetchColumn();

$deliveredOrders = $pdo->query("
    SELECT COUNT(*) FROM orders
    WHERE status = 'delivered'
")->fetchColumn();

$totalRevenue = $pdo->query("
    SELECT SUM(total_price)
    FROM orders
    WHERE status = 'delivered'
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| STATUS LABEL
|--------------------------------------------------------------------------
*/

$statusLabels = [

    'pending' => 'Chờ xác nhận',

    'confirmed' => 'Đã xác nhận',

    'shipping' => 'Đang giao',

    'delivered' => 'Đã giao',

    'cancelled' => 'Đã hủy'

];

?>

<style>
    .om-page {
        padding: 24px;
        background: #f5f7fb;
        min-height: 100vh;
    }

    .om-header {
        margin-bottom: 24px;
    }

    .om-header h1 {
        font-size: 32px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .om-header p {
        color: #6b7280;
    }

    .om-dashboard {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .om-card {
        background: #fff;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .om-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 14px;
        background: #111827;
        color: #fff;
    }

    .om-yellow {
        background: #f59e0b;
    }

    .om-blue {
        background: #3b82f6;
    }

    .om-green {
        background: #10b981;
    }

    .om-card h3 {
        font-size: 15px;
        color: #6b7280;
        margin-bottom: 10px;
    }

    .om-card p {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
    }

    .om-box {
        background: #fff;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .om-filter {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .om-filter select,
    .om-filter input {
        height: 46px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
    }

    .om-filter button {
        height: 46px;
        border: none;
        border-radius: 12px;
        padding: 0 18px;
        background: #111827;
        color: #fff;
        cursor: pointer;
        font-weight: 600;
    }

    .om-table-wrap {
        overflow-x: auto;
    }

    .om-table {
        width: 100%;
        border-collapse: collapse;
    }

    .om-table thead {
        background: #f3f4f6;
    }

    .om-table th {
        padding: 16px;
        text-align: left;
        font-size: 14px;
        color: #374151;
    }

    .om-table td {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .om-order-id {
        font-weight: 700;
        color: #111827;
    }

    .om-customer {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .om-status {
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
    }

    .om-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .om-status.confirmed {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .om-status.shipping {
        background: #ede9fe;
        color: #6d28d9;
    }

    .om-status.delivered {
        background: #d1fae5;
        color: #065f46;
    }

    .om-status.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .om-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .om-view-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #111827;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .om-status-select {
        height: 40px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        padding: 0 10px;
    }

    .om-lock {
        color: #9ca3af;
        font-size: 14px;
    }

    .om-empty {
        text-align: center;
        padding: 40px 20px;
    }

    .om-empty i {
        font-size: 48px;
        color: #9ca3af;
        margin-bottom: 10px;
    }

    .om-pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .om-pagination a {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #111827;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .om-pagination a.active {
        background: #111827;
        color: #fff;
    }

    @media(max-width:992px) {

        .om-dashboard {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media(max-width:768px) {

        .om-page {
            padding: 14px;
        }

        .om-dashboard {
            grid-template-columns: 1fr;
        }

        .om-header h1 {
            font-size: 24px;
        }

        .om-table th,
        .om-table td {
            padding: 12px;
            font-size: 13px;
        }

        .om-filter {
            flex-direction: column;
        }

        .om-filter select,
        .om-filter input,
        .om-filter button {
            width: 100%;
        }

    }

    .om-table {
        min-width: 700px;
    }

    .om-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        padding: 7px 12px;
        font-size: 12px;
    }

    .om-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
        min-width: 120px;
    }

    .om-actions form {
        width: 100%;
    }

    .om-status-select {
        width: 100%;
        font-size: 12px;
        height: 38px;
    }

    .om-view-btn {
        width: 100%;
        height: 38px;
        border-radius: 8px;
    }

    .om-lock {
        font-size: 12px;
        text-align: center;
    }

    .om-table td {
        white-space: nowrap;
    }

    .om-customer {
        min-width: 140px;
    }
</style>

<div class="om-page">

    <div class="om-header">

        <h1>
            <i class='bx bxs-cart'></i>
            Quản lý đơn hàng
        </h1>

        <p>
            Theo dõi trạng thái đơn hàng khách đặt
        </p>

    </div>

    <div class="om-dashboard">

        <div class="om-card">

            <div class="om-icon">
                <i class='bx bx-receipt'></i>
            </div>

            <h3>Tổng đơn</h3>

            <p><?= $totalOrders ?></p>

        </div>

        <div class="om-card">

            <div class="om-icon om-yellow">
                <i class='bx bx-time'></i>
            </div>

            <h3>Đang chờ</h3>

            <p><?= $pendingOrders ?></p>

        </div>

        <div class="om-card">

            <div class="om-icon om-blue">
                <i class='bx bx-package'></i>
            </div>

            <h3>Đang giao</h3>

            <p><?= $shippingOrders ?></p>

        </div>

        <div class="om-card">

            <div class="om-icon om-green">
                <i class='bx bx-check-circle'></i>
            </div>

            <h3>Đã giao</h3>

            <p><?= $deliveredOrders ?></p>

        </div>

    </div>

    <div class="om-box">

        <form method="GET" class="om-filter">

            <input type="hidden" name="page" value="orders">

            <select name="status">

                <option value="">
                    Tất cả trạng thái
                </option>

                <option value="pending">
                    Chờ xác nhận
                </option>

                <option value="confirmed">
                    Đã xác nhận
                </option>

                <option value="shipping">
                    Đang giao
                </option>

                <option value="delivered">
                    Đã giao
                </option>

                <option value="cancelled">
                    Đã hủy
                </option>

            </select>

            <input
                type="date"
                name="date"
                value="<?= $_GET['date'] ?? '' ?>">

            <button type="submit">

                <i class='bx bx-filter-alt'></i>

                Lọc

            </button>

        </form>

    </div>

    <div class="om-box">

        <div class="om-table-wrap">

            <table class="om-table">

                <thead>

                    <tr>

                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Số món</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (count($orders) > 0): ?>

                        <?php foreach ($orders as $order): ?>

                            <?php

                            $currentStatus = $order['status'];

                            $allowedTransitions = [

                                'pending' => [
                                    'confirmed',
                                    'cancelled'
                                ],

                                'confirmed' => [
                                    'shipping',
                                    'cancelled'
                                ],

                                'shipping' => [
                                    'delivered'
                                ],

                                'delivered' => [],

                                'cancelled' => []

                            ];

                            ?>

                            <tr>

                                <td>

                                    <span class="om-order-id">

                                        #<?= str_pad(
                                                $order['id'],
                                                3,
                                                '0',
                                                STR_PAD_LEFT
                                            ) ?>

                                    </span>

                                </td>

                                <td>

                                    <div class="om-customer">

                                        <i class='bx bx-user'></i>

                                        <?= htmlspecialchars($order['customer_name']) ?>

                                    </div>

                                </td>

                                <td>
                                    <?= $order['phone'] ?>
                                </td>

                                <td>
                                    <?= $order['total_items'] ?> món
                                </td>

                                <td>

                                    <strong>
                                        <?= number_format($order['total_price']) ?>đ
                                    </strong>

                                </td>

                                <td>

                                    <span class="om-status <?= $order['status'] ?>">

                                        <?= $statusLabels[$order['status']] ?>

                                    </span>

                                </td>

                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime($order['created_at'])
                                    ) ?>

                                </td>

                                <td>

                                    <div class="om-actions">

                                        <a
                                            href="invoice.php?id=<?= $order['id'] ?>"
                                            class="om-view-btn">

                                            <i class='bx bx-show'></i>

                                        </a>

                                        <?php if (count($allowedTransitions[$currentStatus]) > 0): ?>

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="order_id"
                                                    value="<?= $order['id'] ?>">

                                                <select
                                                    name="status"
                                                    class="om-status-select"
                                                    onchange="
sessionStorage.setItem('orders_scroll', window.scrollY);
this.form.submit();
">

                                                    <option value="">

                                                        <?= $statusLabels[$currentStatus] ?>

                                                    </option>

                                                    <?php foreach ($allowedTransitions[$currentStatus] as $nextStatus): ?>

                                                        <option value="<?= $nextStatus ?>">

                                                            <?= $statusLabels[$nextStatus] ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                                <input
                                                    type="hidden"
                                                    name="update_status"
                                                    value="1">

                                            </form>

                                        <?php else: ?>

                                            <span class="om-lock">

                                                <i class='bx bx-lock-alt'></i>

                                                Đã khóa

                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8">

                                <div class="om-empty">

                                    <i class='bx bx-package'></i>

                                    <p>
                                        Không có đơn hàng nào
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <?php if ($totalPages > 1): ?>

        <div class="om-pagination">

            <?php if ($pageNumber > 1): ?>

                <a
                    href="?page=orders&p=<?= $pageNumber - 1 ?>">
                    <i class='bx bx-chevron-left'></i>
                </a>

            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                <a
                    href="?page=orders&p=<?= $i ?>"
                    class="<?= $i == $pageNumber ? 'active' : '' ?>">

                    <?= $i ?>

                </a>

            <?php endfor; ?>

            <?php if ($pageNumber < $totalPages): ?>

                <a
                    href="?page=orders&p=<?= $pageNumber + 1 ?>">
                    <i class='bx bx-chevron-right'></i>
                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

<script>
    window.addEventListener('beforeunload', () => {

        sessionStorage.setItem(
            'orders_scroll',
            window.scrollY
        );

    });

    window.addEventListener('load', () => {

        const scrollPosition = sessionStorage.getItem(
            'orders_scroll'
        );

        if (scrollPosition !== null) {

            window.scrollTo(
                0,
                parseInt(scrollPosition)
            );

        }

    });
</script>