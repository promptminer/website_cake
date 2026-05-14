<?php

require_once "../core/auth.php";
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
        rel='stylesheet'
    >

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, Helvetica, sans-serif;
        }

        body{
            background:#f5f7fb;
            color:#111827;
        }

        a{
            text-decoration:none;
        }

        ul{
            list-style:none;
        }

        .admin-layout{
            display:flex;
            min-height:100vh;
        }

        /*
        |--------------------------------------------------------------------------
        | SIDEBAR
        |--------------------------------------------------------------------------
        */

        .sidebar{
            width:260px;
            background:#111827;
            color:#fff;
            position:fixed;
            top:0;
            left:0;
            height:100vh;
            padding:24px 18px;
            overflow-y:auto;
            z-index:999;
        }

        .logo{
            display:flex;
            align-items:center;
            gap:10px;
            font-size:24px;
            font-weight:700;
            margin-bottom:35px;
            padding:0 8px;
        }

        .logo i{
            font-size:30px;
            color:#f59e0b;
        }

        .menu{
            display:flex;
            flex-direction:column;
            gap:10px;
        }

        .menu li a{
            display:flex;
            align-items:center;
            gap:12px;
            padding:14px 16px;
            border-radius:14px;
            color:#d1d5db;
            transition:0.25s;
            font-size:15px;
            font-weight:500;
        }

        .menu li a:hover{
            background:#1f2937;
            color:#fff;
        }

        .menu li a.active{
            background:#f59e0b;
            color:#fff;
        }

        .menu li a i{
            font-size:22px;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */

        .content{
            margin-left:260px;
            width:calc(100% - 260px);
            padding:28px;
        }

        /*
        |--------------------------------------------------------------------------
        | TOPBAR
        |--------------------------------------------------------------------------
        */

        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:20px;
            margin-bottom:30px;
            flex-wrap:wrap;
        }

        .topbar-left h1{
            font-size:28px;
            margin-bottom:8px;
        }

        .topbar-left p{
            color:#6b7280;
            font-size:15px;
        }

        .topbar-right{
            display:flex;
            align-items:center;
            gap:15px;
        }

        .admin-profile{
            background:#fff;
            padding:12px 16px;
            border-radius:14px;
            display:flex;
            align-items:center;
            gap:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }

        .admin-profile i{
            font-size:22px;
            color:#f59e0b;
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD GRID
        |--------------------------------------------------------------------------
        */

        .dashboard-grid{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:20px;
            margin-bottom:25px;
        }

        .dashboard-card{
            background:#fff;
            border-radius:18px;
            padding:22px;
            box-shadow:0 4px 15px rgba(0,0,0,0.04);
            position:relative;
            overflow:hidden;
        }

        .dashboard-card::before{
            content:'';
            position:absolute;
            right:-25px;
            top:-25px;
            width:100px;
            height:100px;
            background:rgba(245,158,11,0.08);
            border-radius:50%;
        }

        .dashboard-card .card-top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }

        .dashboard-card .card-top i{
            width:50px;
            height:50px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:14px;
            font-size:24px;
            background:#fff7ed;
            color:#f59e0b;
        }

        .dashboard-card h3{
            font-size:15px;
            color:#6b7280;
            margin-bottom:10px;
            font-weight:500;
        }

        .dashboard-card p{
            font-size:30px;
            font-weight:700;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT GRID
        |--------------------------------------------------------------------------
        */

        .content-grid{
            display:grid;
            grid-template-columns:2fr 1fr;
            gap:22px;
        }

        /*
        |--------------------------------------------------------------------------
        | CARD BOX
        |--------------------------------------------------------------------------
        */

        .box{
            background:#fff;
            border-radius:18px;
            padding:22px;
            box-shadow:0 4px 15px rgba(0,0,0,0.04);
        }

        .box-title{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:22px;
            gap:15px;
        }

        .box-title h2{
            font-size:20px;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE
        |--------------------------------------------------------------------------
        */

        .table-wrapper{
            width:100%;
            overflow-x:auto;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th{
            text-align:left;
            background:#f9fafb;
            padding:14px;
            font-size:14px;
            color:#6b7280;
        }

        table td{
            padding:16px 14px;
            border-bottom:1px solid #f1f5f9;
            font-size:14px;
        }

        .status{
            padding:7px 12px;
            border-radius:999px;
            font-size:13px;
            font-weight:600;
            display:inline-block;
        }

        .pending{
            background:#fff7ed;
            color:#ea580c;
        }

        .confirmed{
            background:#eff6ff;
            color:#2563eb;
        }

        .delivered{
            background:#ecfdf5;
            color:#059669;
        }

        .cancelled{
            background:#fef2f2;
            color:#dc2626;
        }

        /*
        |--------------------------------------------------------------------------
        | TOP PRODUCT
        |--------------------------------------------------------------------------
        */

        .top-product-list{
            display:flex;
            flex-direction:column;
            gap:16px;
        }

        .top-product-item{
            display:flex;
            align-items:center;
            gap:14px;
            padding-bottom:14px;
            border-bottom:1px solid #f1f5f9;
        }

        .top-product-item:last-child{
            border-bottom:none;
            padding-bottom:0;
        }

        .top-product-item img{
            width:65px;
            height:65px;
            object-fit:cover;
            border-radius:14px;
            background:#f3f4f6;
        }

        .top-product-info{
            flex:1;
        }

        .top-product-info h4{
            font-size:15px;
            margin-bottom:6px;
        }

        .top-product-info span{
            color:#6b7280;
            font-size:14px;
        }

        .empty-box{
            text-align:center;
            padding:35px 20px;
            color:#6b7280;
        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE HEADER
        |--------------------------------------------------------------------------
        */

        .mobile-header{
            display:none;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }

        .menu-toggle{
            width:45px;
            height:45px;
            border:none;
            border-radius:12px;
            background:#111827;
            color:#fff;
            font-size:22px;
            cursor:pointer;
        }

        /*
        |--------------------------------------------------------------------------
        | OVERLAY
        |--------------------------------------------------------------------------
        */

        .sidebar-overlay{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,0.5);
            z-index:998;
            display:none;
        }

        .sidebar-overlay.show{
            display:block;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media(max-width:1200px){

            .dashboard-grid{
                grid-template-columns:repeat(2,1fr);
            }

            .content-grid{
                grid-template-columns:1fr;
            }

        }

        @media(max-width:900px){

            .sidebar{
                left:-100%;
                transition:0.3s;
            }

            .sidebar.show{
                left:0;
            }

            .content{
                width:100%;
                margin-left:0;
                padding:18px;
            }

            .mobile-header{
                display:flex;
            }

        }

        @media(max-width:600px){

            .dashboard-grid{
                grid-template-columns:1fr;
                gap:15px;
            }

            .dashboard-card{
                padding:18px;
            }

            .dashboard-card p{
                font-size:26px;
            }

            .topbar{
                flex-direction:column;
                align-items:flex-start;
            }

            .topbar-left h1{
                font-size:24px;
            }

            .box{
                padding:18px;
            }

            table th,
            table td{
                font-size:13px;
                padding:12px 10px;
            }

        }
        .menu-user-link{
    background:#1f2937;
}

.menu-user-link:hover{
    background:#2563eb !important;
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
        href="../index.php"
        target="_blank"
        class="menu-user-link"
    >
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