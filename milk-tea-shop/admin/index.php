<?php

require_once "../core/auth.php";

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

?>

<!DOCTYPE html>
<html lang="vi">
<head>

    <meta charset="UTF-8">

    <title>Admin Panel</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">

    <!-- BOXICONS -->
    <link 
        href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' 
        rel='stylesheet'
    >

</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="logo">
            <i class='bx bxs-coffee'></i>
            ADMIN
        </div>

        <ul class="menu">

            <li>
                <a 
                    href="index.php?page=dashboard"
                    class="<?= $page == 'dashboard' ? 'active' : '' ?>"
                >
                    <i class='bx bxs-dashboard'></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a 
                    href="index.php?page=categories"
                    class="<?= $page == 'categories' ? 'active' : '' ?>"
                >
                    <i class='bx bxs-category'></i>
                    Danh mục
                </a>
            </li>

            <li>
                <a 
                    href="index.php?page=products"
                    class="<?= $page == 'products' ? 'active' : '' ?>"
                >
                    <i class='bx bxs-package'></i>
                    Sản phẩm
                </a>
            </li>

            <li>
                <a 
                    href="index.php?page=orders"
                    class="<?= $page == 'orders' ? 'active' : '' ?>"
                >
                    <i class='bx bxs-cart'></i>
                    Đơn hàng
                </a>
            </li>

            <li>
                <a 
                    href="index.php?page=promotions"
                    class="<?= $page == 'promotions' ? 'active' : '' ?>"
                >
                    <i class='bx bxs-discount'></i>
                    Khuyến mãi
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

                echo "
                    <div class='dashboard-grid'>

                        <div class='dashboard-card'>
                            <h3>Tổng sản phẩm</h3>
                            <p>0</p>
                        </div>

                        <div class='dashboard-card'>
                            <h3>Đơn hàng</h3>
                            <p>0</p>
                        </div>

                        <div class='dashboard-card'>
                            <h3>Doanh thu</h3>
                            <p>0đ</p>
                        </div>

                        <div class='dashboard-card'>
                            <h3>Khuyến mãi</h3>
                            <p>0</p>
                        </div>

                    </div>
                ";

                break;
        }

        ?>

    </main>

</div>

</body>
</html>