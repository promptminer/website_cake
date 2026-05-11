<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

    <div class="logo">
        Milk Tea
    </div>

    <ul class="menu">

        <li>
            <a
                href="index.php"
                class="<?= $currentPage == 'index.php' ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-house"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a
                href="categories.php"
                class="<?= $currentPage == 'categories.php' ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-layer-group"></i>
                Danh mục
            </a>
        </li>

        <li>
            <a
                href="products.php"
                class="<?= $currentPage == 'products.php' ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-mug-hot"></i>
                Sản phẩm
            </a>
        </li>

        <li>
            <a
                href="orders.php"
                class="<?= $currentPage == 'orders.php' ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-cart-shopping"></i>
                Đơn hàng
            </a>
        </li>

        <li>
            <a
                href="promotions.php"
                class="<?= $currentPage == 'promotions.php' ? 'active' : '' ?>"
            >
                <i class="fa-solid fa-tags"></i>
                Khuyến mãi
            </a>
        </li>

    </ul>

</div>