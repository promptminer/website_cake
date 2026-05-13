// /admin/index.php

<?php
require_once "../core/auth.php";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-container">

    <aside class="sidebar">
        <h2>Milk Tea Admin</h2>

        <a href="categories.php">Danh mục</a>
        <a href="products.php">Sản phẩm</a>
        <a href="orders.php">Đơn hàng</a>
        <a href="promotions.php">Khuyến mãi</a>
        <a href="logout.php">Đăng xuất</a>
    </aside>

    <main class="content">
        <h1>Dashboard</h1>

        <p>Quản trị hệ thống trà sữa.</p>
    </main>

</div>

<script>

    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if(menuToggle){

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