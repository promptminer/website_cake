<?php

require_once "../core/auth.php";
require_once "../core/db.php";

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'categories',
    'products',
    'orders',
    'promotions'
];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

/*
|--------------------------------------------------------------------------
| DASHBOARD STATS
|--------------------------------------------------------------------------
*/

$totalProducts = 0;
$totalOrders = 0;
$totalRevenue = 0;
$totalPromotions = 0;
$pendingOrders = 0;

/*
|--------------------------------------------------------------------------
| REVENUE
|--------------------------------------------------------------------------
*/

$todayRevenue = 0;
$weekRevenue = 0;
$monthRevenue = 0;

try {

    $totalProducts = $pdo
        ->query("SELECT COUNT(*) FROM products")
        ->fetchColumn();

    $totalOrders = $pdo
        ->query("SELECT COUNT(*) FROM orders")
        ->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | TOTAL REVENUE
    |--------------------------------------------------------------------------
    | Chỉ tính đơn đã giao
    */

    $totalRevenue = $pdo
        ->query("
            SELECT COALESCE(SUM(total_price),0)
            FROM orders
            WHERE status = 'delivered'
        ")
        ->fetchColumn();

    $totalPromotions = $pdo
        ->query("SELECT COUNT(*) FROM promotions")
        ->fetchColumn();

    $pendingOrders = $pdo
        ->query("
            SELECT COUNT(*)
            FROM orders
            WHERE status = 'pending'
        ")
        ->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | TODAY REVENUE
    |--------------------------------------------------------------------------
    | Chỉ tính đơn đã giao
    */

    $todayRevenue = $pdo
        ->query("
            SELECT COALESCE(SUM(total_price),0)
            FROM orders
            WHERE DATE(created_at) = CURDATE()
            AND status = 'delivered'
        ")
        ->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | WEEK REVENUE
    |--------------------------------------------------------------------------
    | Chỉ tính đơn đã giao
    */

    $weekRevenue = $pdo
        ->query("
            SELECT COALESCE(SUM(total_price),0)
            FROM orders
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            AND status = 'delivered'
        ")
        ->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | MONTH REVENUE
    |--------------------------------------------------------------------------
    | Chỉ tính đơn đã giao
    */

    $monthRevenue = $pdo
        ->query("
            SELECT COALESCE(SUM(total_price),0)
            FROM orders
            WHERE MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
            AND status = 'delivered'
        ")
        ->fetchColumn();
} catch (Exception $e) {
}

/*
|--------------------------------------------------------------------------
| TOP PRODUCTS
|--------------------------------------------------------------------------
*/

$topProducts = [];

try {

    $stmt = $pdo->query("
        SELECT 
            products.name,
            products.image,
            SUM(order_details.quantity) as total_sold
        FROM order_details
        INNER JOIN products 
            ON products.id = order_details.product_id
        INNER JOIN orders 
            ON orders.id = order_details.order_id
        WHERE orders.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND orders.status = 'delivered'
        GROUP BY products.id
        ORDER BY total_sold DESC
        LIMIT 5
    ");

    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

/*
|--------------------------------------------------------------------------
| RECENT ORDERS
|--------------------------------------------------------------------------
*/

$recentOrders = [];

try {

    $stmt = $pdo->query("
        SELECT *
        FROM orders
        ORDER BY id DESC
        LIMIT 6
    ");

    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>Admin Panel</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- BOXICONS -->
    <link
        href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
        rel='stylesheet'>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f5f7fb;
            color: #111827;
        }

        a {
            text-decoration: none;
        }

        ul {
            list-style: none;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /*
        |--------------------------------------------------------------------------
        | SIDEBAR
        |--------------------------------------------------------------------------
        */

        .sidebar {
            width: 260px;
            background: #111827;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            padding: 24px 18px;
            overflow-y: auto;
            z-index: 999;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 35px;
            padding: 0 8px;
        }

        .logo i {
            font-size: 30px;
            color: #f59e0b;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 14px;
            color: #d1d5db;
            transition: 0.25s;
            font-size: 15px;
            font-weight: 500;
        }

        .menu li a:hover {
            background: #1f2937;
            color: #fff;
        }

        .menu li a.active {
            background: #f59e0b;
            color: #fff;
        }

        .menu li a i {
            font-size: 22px;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */

        .content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 28px;
        }

        /*
        |--------------------------------------------------------------------------
        | TOPBAR
        |--------------------------------------------------------------------------
        */

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .topbar-left h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .topbar-left p {
            color: #6b7280;
            font-size: 15px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-profile {
            background: #fff;
            padding: 12px 16px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .admin-profile i {
            font-size: 22px;
            color: #f59e0b;
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD GRID
        |--------------------------------------------------------------------------
        */

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .dashboard-card {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            right: -25px;
            top: -25px;
            width: 100px;
            height: 100px;
            background: rgba(245, 158, 11, 0.08);
            border-radius: 50%;
        }

        .dashboard-card .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .dashboard-card .card-top i {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 24px;
            background: #fff7ed;
            color: #f59e0b;
        }

        .dashboard-card h3 {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .dashboard-card p {
            font-size: 30px;
            font-weight: 700;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT GRID
        |--------------------------------------------------------------------------
        */

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 22px;
        }

        /*
        |--------------------------------------------------------------------------
        | CARD BOX
        |--------------------------------------------------------------------------
        */

        .box {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        }

        .box-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            gap: 15px;
        }

        .box-title h2 {
            font-size: 20px;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE
        |--------------------------------------------------------------------------
        */

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            text-align: left;
            background: #f9fafb;
            padding: 14px;
            font-size: 14px;
            color: #6b7280;
        }

        table td {
            padding: 16px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .status {
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .pending {
            background: #fff7ed;
            color: #ea580c;
        }

        .confirmed {
            background: #eff6ff;
            color: #2563eb;
        }

        .delivered {
            background: #ecfdf5;
            color: #059669;
        }

        .cancelled {
            background: #fef2f2;
            color: #dc2626;
        }

        /*
        |--------------------------------------------------------------------------
        | TOP PRODUCT
        |--------------------------------------------------------------------------
        */

        .top-product-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .top-product-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .top-product-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .top-product-item img {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 14px;
            background: #f3f4f6;
        }

        .top-product-info {
            flex: 1;
        }

        .top-product-info h4 {
            font-size: 15px;
            margin-bottom: 6px;
        }

        .top-product-info span {
            color: #6b7280;
            font-size: 14px;
        }

        .empty-box {
            text-align: center;
            padding: 35px 20px;
            color: #6b7280;
        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE HEADER
        |--------------------------------------------------------------------------
        */

        .mobile-header {
            display: none;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .menu-toggle {
            width: 45px;
            height: 45px;
            border: none;
            border-radius: 12px;
            background: #111827;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
        }

        /*
        |--------------------------------------------------------------------------
        | OVERLAY
        |--------------------------------------------------------------------------
        */

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media(max-width:1200px) {

            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

        }

        @media(max-width:900px) {

            .sidebar {
                left: -100%;
                transition: 0.3s;
            }

            .sidebar.show {
                left: 0;
            }

            .content {
                width: 100%;
                margin-left: 0;
                padding: 18px;
            }

            .mobile-header {
                display: flex;
            }

        }

        @media(max-width:600px) {

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .dashboard-card {
                padding: 18px;
            }

            .dashboard-card p {
                font-size: 26px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-left h1 {
                font-size: 24px;
            }

            .box {
                padding: 18px;
            }

            table th,
            table td {
                font-size: 13px;
                padding: 12px 10px;
            }

        }

        .menu-user-link {
            background: #1f2937;
        }

        .menu-user-link:hover {
            background: #2563eb !important;
        }
        .shipping{
    background:#ede9fe;
    color:#6d28d9;
}
    </style>

</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-layout">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">

            <div class="logo">
                <i class='bx bxs-coffee'></i>
                ADMIN
            </div>

            <ul class="menu">

                <li>
                    <a
                        href="index.php?page=dashboard"
                        class="<?= $page == 'dashboard' ? 'active' : '' ?>">
                        <i class='bx bxs-dashboard'></i>
                        Dashboard
                    </a>
                </li>

                <li>
                    <a
                        href="index.php?page=categories"
                        class="<?= $page == 'categories' ? 'active' : '' ?>">
                        <i class='bx bxs-category'></i>
                        Danh mục
                    </a>
                </li>

                <li>
                    <a
                        href="index.php?page=products"
                        class="<?= $page == 'products' ? 'active' : '' ?>">
                        <i class='bx bxs-package'></i>
                        Sản phẩm
                    </a>
                </li>

                <li>
                    <a
                        href="index.php?page=orders"
                        class="<?= $page == 'orders' ? 'active' : '' ?>">
                        <i class='bx bxs-cart'></i>
                        Đơn hàng
                    </a>
                </li>


                <li>
                    <a
                        href="../index.php"
                        target="_blank"
                        class="menu-user-link">
                        <i class='bx bx-home-alt'></i>
                        Trang người dùng
                    </a>
                </li>

                <li>
                    <a href="logout.php">
                        <i class='bx bx-log-out'></i>
                        Đăng xuất
                    </a>
                </li>

            </ul>

        </aside>

        <!-- CONTENT -->
        <main class="content">

            <!-- MOBILE HEADER -->
            <div class="mobile-header">

                <button class="menu-toggle" id="menuToggle">
                    <i class='bx bx-menu'></i>
                </button>

                <div class="admin-profile">
                    <i class='bx bxs-user-circle'></i>
                    <span>Admin</span>
                </div>

            </div>

            <?php

            switch ($page) {

                case 'categories':
                    require_once "categories.php";
                    break;

                case 'products':
                    require_once "products.php";
                    break;

                case 'orders':
                    require_once "orders.php";
                    break;

                case 'promotions':
                    require_once "promotions.php";
                    break;

                default:
            ?>

                    <!-- TOPBAR -->
                    <div class="topbar">

                        <div class="topbar-left">
                            <h1>Dashboard</h1>
                            <p>Quản lý cửa hàng trà sữa và bánh ngọt</p>
                        </div>

                        <div class="topbar-right">

                            <div class="admin-profile">
                                <i class='bx bxs-user-circle'></i>
                                <span>Xin chào Admin</span>
                            </div>

                        </div>

                    </div>

                    <!-- MAIN STATS -->
                    <div class="dashboard-grid">

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Tổng sản phẩm</h3>
                                <i class='bx bxs-package'></i>
                            </div>

                            <p><?= $totalProducts ?></p>

                        </div>

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Đơn hàng</h3>
                                <i class='bx bxs-cart'></i>
                            </div>

                            <p><?= $totalOrders ?></p>

                        </div>

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Khuyến mãi</h3>
                                <i class='bx bxs-discount'></i>
                            </div>

                            <p><?= $totalPromotions ?></p>

                        </div>

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Đơn chờ xử lý</h3>
                                <i class='bx bx-time-five'></i>
                            </div>

                            <p><?= $pendingOrders ?></p>

                        </div>

                    </div>

                    <!-- REVENUE -->
                    <div class="dashboard-grid">

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Doanh thu hôm nay</h3>
                                <i class='bx bx-money'></i>
                            </div>

                            <p><?= number_format($todayRevenue, 0, ',', '.') ?>đ</p>

                        </div>

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Doanh thu tuần</h3>
                                <i class='bx bx-line-chart'></i>
                            </div>

                            <p><?= number_format($weekRevenue, 0, ',', '.') ?>đ</p>

                        </div>

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Doanh thu tháng</h3>
                                <i class='bx bx-bar-chart'></i>
                            </div>

                            <p><?= number_format($monthRevenue, 0, ',', '.') ?>đ</p>

                        </div>

                        <div class="dashboard-card">

                            <div class="card-top">
                                <h3>Tổng doanh thu</h3>
                                <i class='bx bxs-wallet'></i>
                            </div>

                            <p><?= number_format($totalRevenue, 0, ',', '.') ?>đ</p>

                        </div>

                    </div>

                    <!-- CONTENT GRID -->
                    <div class="content-grid">

                        <!-- ORDERS -->
                        <div class="box">

                            <div class="box-title">

                                <h2>Đơn hàng gần đây</h2>

                                <span>
                                    <?= $pendingOrders ?> đơn chờ xử lý
                                </span>

                            </div>

                            <div class="table-wrapper">

                                <table>

                                    <thead>

                                        <tr>
                                            <th>Mã</th>
                                            <th>Khách hàng</th>
                                            <th>SĐT</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php if (count($recentOrders) > 0): ?>

                                            <?php foreach ($recentOrders as $order): ?>

                                                <tr>

                                                    <td>
                                                        #<?= $order['id'] ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($order['customer_name']) ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($order['phone']) ?>
                                                    </td>

                                                    <td>
                                                        <?= number_format($order['total_price'], 0, ',', '.') ?>đ
                                                    </td>

                                                    <td>

                                                        <span class="status <?= $order['status'] ?>">

                                                            <?php

                                                            switch ($order['status']) {

                                                                case 'pending':
                                                                    echo 'Chờ xác nhận';
                                                                    break;

                                                                case 'confirmed':
                                                                    echo 'Đã xác nhận';
                                                                    break;
                                                                case 'shipping':
                                                                    echo 'Đang giao';
                                                                    break;

                                                                case 'delivered':
                                                                    echo 'Đã giao';
                                                                    break;

                                                                case 'cancelled':
                                                                    echo 'Đã hủy';
                                                                    break;
                                                            }

                                                            ?>

                                                        </span>

                                                    </td>

                                                </tr>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <tr>
                                                <td colspan="5">

                                                    <div class="empty-box">
                                                        Chưa có đơn hàng nào
                                                    </div>

                                                </td>
                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                        <!-- TOP PRODUCTS -->
                        <div class="box">

                            <div class="box-title">
                                <h2>Bán chạy tuần</h2>
                            </div>

                            <div class="top-product-list">

                                <?php if (count($topProducts) > 0): ?>

                                    <?php foreach ($topProducts as $item): ?>

                                        <div class="top-product-item">

                                            <img
                                                src="../uploads/products/<?= htmlspecialchars($item['image']) ?>"
                                                alt="">

                                            <div class="top-product-info">

                                                <h4>
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </h4>

                                                <span>
                                                    Đã bán <?= $item['total_sold'] ?> sản phẩm
                                                </span>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <div class="empty-box">
                                        Chưa có dữ liệu thống kê
                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

            <?php

                    break;
            }

            ?>

        </main>

    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (menuToggle) {

            menuToggle.addEventListener('click', () => {

                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');

            });

        }

        overlay.addEventListener('click', () => {

            sidebar.classList.remove('show');
            overlay.classList.remove('show');

        });
    </script>

</body>

</html>