<?php

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

    header("Location: index.php?page=orders");
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

if(count($whereConditions) > 0){

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

if($pageNumber < 1){
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

    'delivered' => 'Đã giao',

    'cancelled' => 'Đã hủy'

];

?>

<link rel="stylesheet" href="../assets/css/orders.css">

<div class="orders-page">

    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1>

                <i class='bx bxs-cart'></i>

                Quản lý đơn hàng

            </h1>

            <p>
                Theo dõi và xử lý đơn hàng khách đặt
            </p>

        </div>

    </div>

    <!-- DASHBOARD -->

    <div class="dashboard-grid">

        <div class="dashboard-card">

            <div class="card-icon">

                <i class='bx bx-receipt'></i>

            </div>

            <h3>Tổng đơn hàng</h3>

            <p><?= $totalOrders ?></p>

        </div>

        <div class="dashboard-card">

            <div class="card-icon yellow">

                <i class='bx bx-time-five'></i>

            </div>

            <h3>Đang chờ</h3>

            <p><?= $pendingOrders ?></p>

        </div>

        <div class="dashboard-card">

            <div class="card-icon green">

                <i class='bx bx-check-circle'></i>

            </div>

            <h3>Đã giao</h3>

            <p><?= $deliveredOrders ?></p>

        </div>

        <div class="dashboard-card">

            <div class="card-icon black">

                <i class='bx bx-wallet'></i>

            </div>

            <h3>Doanh thu</h3>

            <p><?= number_format($totalRevenue) ?>đ</p>

        </div>

    </div>

    <!-- FILTER -->

    <div class="card">

        <form method="GET" class="filter-form">

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
                value="<?= $_GET['date'] ?? '' ?>"
            >

            <button type="submit">

                <i class='bx bx-filter-alt'></i>

                Lọc đơn hàng

            </button>

        </form>

    </div>

    <!-- TABLE -->

    <div class="card">

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Số món</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if(count($orders) > 0): ?>

                        <?php foreach($orders as $order): ?>

                            <?php

                            $currentStatus = $order['status'];

                            $allowedTransitions = [

                                'pending' => [
                                    'confirmed',
                                    'cancelled'
                                ],

                                'confirmed' => [
                                    'delivered'
                                ],

                                'delivered' => [],

                                'cancelled' => []

                            ];

                            ?>

                            <tr>

                                <td>

                                    <span class="order-id">

                                        #<?= str_pad(
                                            $order['id'],
                                            3,
                                            '0',
                                            STR_PAD_LEFT
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <div class="customer">

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

                                    <span class="status <?= $order['status'] ?>">

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

                                    <div class="actions">

                                        <!-- DETAIL -->

                                        <a 
                                            href="invoice.php?id=<?= $order['id'] ?>"
                                            class="btn-view"
                                        >

                                            <i class='bx bx-show'></i>

                                        </a>

                                        <!-- STATUS UPDATE -->

                                        <?php if(count($allowedTransitions[$currentStatus]) > 0): ?>

                                            <form method="POST">

                                                <input 
                                                    type="hidden"
                                                    name="order_id"
                                                    value="<?= $order['id'] ?>"
                                                >

                                                <select 
                                                    name="status"
                                                    class="status-select"
                                                    onchange="this.form.submit()"
                                                >

                                                    <option value="">

                                                        <?= $statusLabels[$currentStatus] ?>

                                                    </option>

                                                    <?php foreach($allowedTransitions[$currentStatus] as $nextStatus): ?>

                                                        <option value="<?= $nextStatus ?>">

                                                            <?= $statusLabels[$nextStatus] ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                                <input 
                                                    type="hidden"
                                                    name="update_status"
                                                    value="1"
                                                >

                                            </form>

                                        <?php else: ?>

                                            <span class="locked-status">

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

                                <div class="empty-data">

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

    <!-- PAGINATION -->

    <?php if($totalPages > 1): ?>

        <div class="pagination">

            <?php if($pageNumber > 1): ?>

                <a 
                    href="?page=orders&p=<?= $pageNumber - 1 ?>"
                >

                    <i class='bx bx-chevron-left'></i>

                </a>

            <?php endif; ?>

            <?php for($i = 1; $i <= $totalPages; $i++): ?>

                <a 
                    href="?page=orders&p=<?= $i ?>"
                    class="<?= $i == $pageNumber ? 'active' : '' ?>"
                >

                    <?= $i ?>

                </a>

            <?php endfor; ?>

            <?php if($pageNumber < $totalPages): ?>

                <a 
                    href="?page=orders&p=<?= $pageNumber + 1 ?>"
                >

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

    if(scrollPosition !== null){

        window.scrollTo(
            0,
            parseInt(scrollPosition)
        );

    }

});

</script>