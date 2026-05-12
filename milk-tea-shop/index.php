<?php

$host = "localhost";
$user = "root";
$password = "mysql";
$database = "milk_tea_shop";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

$categories = mysqli_query($conn, "
    SELECT * FROM categories
    ORDER BY id DESC
");

$products = mysqli_query($conn, "
    SELECT 
        p.*,
        c.name as category_name,
        pr.discount_percent
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN promotions pr ON p.promotion_id = pr.id
    ORDER BY p.id DESC
");

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiệm Trà Sữa X</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Be Vietnam Pro',sans-serif;
            background:#fafafa;
            color:#111827;
        }

        a{
            text-decoration:none;
            color:inherit;
        }

        img{
            width:100%;
            display:block;
        }

        button{
            border:none;
            outline:none;
            cursor:pointer;
            font-family:inherit;
        }

        .container{
            width:100%;
            max-width:1280px;
            margin:auto;
            padding:0 16px;
        }

        .header{
            position:sticky;
            top:0;
            z-index:1000;
            background:rgba(255,255,255,.95);
            backdrop-filter:blur(10px);
            border-bottom:1px solid #ececec;
        }

        .header-wrapper{
            height:72px;
            display:flex;
            align-items:center;
            justify-content:space-between;
        }

        .logo{
            font-size:22px;
            font-weight:700;
        }

        .cart-button{
            width:48px;
            height:48px;
            border-radius:14px;
            background:#111827;
            color:white;
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:18px;
        }

        .cart-count{
            position:absolute;
            top:-4px;
            right:-4px;
            width:22px;
            height:22px;
            border-radius:50%;
            background:#ef4444;
            color:white;
            font-size:12px;
            font-weight:600;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .hero{
            padding:24px 0 10px;
        }

        .hero-banner{
            border-radius:28px;
            overflow:hidden;
            min-height:240px;
            padding:28px;
            display:flex;
            align-items:flex-end;

            background:
                linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),
                url('https://images.unsplash.com/photo-1515823064-d6e0c04616a7?q=80&w=1200&auto=format&fit=crop');

            background-size:cover;
            background-position:center;
        }

        .hero-content{
            color:white;
            max-width:520px;
        }

        .hero-content h1{
            font-size:32px;
            line-height:1.2;
            margin-bottom:12px;
        }

        .hero-content p{
            font-size:15px;
            line-height:1.6;
            opacity:.9;
        }

        .category-list{
            display:flex;
            gap:10px;
            overflow-x:auto;
            padding:18px 0 10px;
            scrollbar-width:none;
        }

        .category-list::-webkit-scrollbar{
            display:none;
        }

        .category-item{
            padding:12px 18px;
            border-radius:999px;
            border:1px solid #e5e7eb;
            background:white;
            white-space:nowrap;
            font-size:14px;
            font-weight:500;
            transition:.2s;
        }

        .category-item:hover{
            background:#111827;
            color:white;
        }

        .section-title{
            margin:24px 0 18px;
        }

        .section-title h2{
            font-size:24px;
        }

        .product-grid{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:16px;
            padding-bottom:40px;
        }

        .product-card{
            background:white;
            border-radius:24px;
            overflow:hidden;
            border:1px solid #ececec;
            transition:.25s;
        }

        .product-card:hover{
            transform:translateY(-4px);
            box-shadow:0 12px 30px rgba(0,0,0,.06);
        }

        .product-image{
            position:relative;
            aspect-ratio:1/1;
            overflow:hidden;
        }

        .product-image img{
            height:100%;
            object-fit:cover;
        }

        .discount-badge{
            position:absolute;
            top:12px;
            left:12px;
            background:#ef4444;
            color:white;
            font-size:12px;
            font-weight:600;
            padding:8px 10px;
            border-radius:999px;
        }

        .product-body{
            padding:16px;
        }

        .product-category{
            font-size:12px;
            color:#6b7280;
            margin-bottom:8px;
        }

        .product-name{
            font-size:16px;
            font-weight:600;
            line-height:1.4;
            margin-bottom:10px;
            min-height:44px;
        }

        .rating{
            display:flex;
            align-items:center;
            gap:6px;
            font-size:13px;
            color:#f59e0b;
            margin-bottom:14px;
        }

        .product-footer{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
        }

        .price-box{
            display:flex;
            flex-direction:column;
        }

        .old-price{
            text-decoration:line-through;
            color:#9ca3af;
            font-size:13px;
        }

        .new-price{
            font-size:18px;
            font-weight:700;
        }

        .add-cart{
            width:44px;
            height:44px;
            border-radius:14px;
            background:#111827;
            color:white;
            font-size:16px;
        }

        .floating-cart{
            position:fixed;
            left:16px;
            right:16px;
            bottom:18px;
            background:#111827;
            color:white;
            border-radius:20px;
            padding:16px;
            display:none;
            align-items:center;
            justify-content:space-between;
            z-index:999;
            box-shadow:0 20px 40px rgba(0,0,0,.2);
        }

        .floating-cart.active{
            display:flex;
        }

        .floating-cart button{
            padding:12px 18px;
            border-radius:12px;
            background:white;
            color:#111827;
            font-weight:600;
        }

        .modal{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.45);
            display:none;
            align-items:center;
            justify-content:center;
            padding:16px;
            z-index:9999;
        }

        .modal.active{
            display:flex;
        }

        .modal-content{
            width:100%;
            max-width:480px;
            background:white;
            border-radius:28px;
            padding:24px;
        }

        .modal-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:20px;
        }

        .modal-header h3{
            font-size:22px;
        }

        .close-modal{
            width:42px;
            height:42px;
            border-radius:12px;
            background:#f3f4f6;
        }

        .form-group{
            margin-bottom:16px;
        }

        .form-group label{
            display:block;
            font-size:14px;
            margin-bottom:8px;
            font-weight:500;
        }

        .form-control{
            width:100%;
            height:52px;
            border:1px solid #d1d5db;
            border-radius:14px;
            padding:0 16px;
            font-size:15px;
            font-family:inherit;
        }

        textarea.form-control{
            height:120px;
            resize:none;
            padding-top:14px;
        }

        .checkout-btn{
            width:100%;
            height:54px;
            border-radius:16px;
            background:#111827;
            color:white;
            font-size:15px;
            font-weight:600;
            margin-top:10px;
        }

        .footer{
            padding:40px 0 120px;
            text-align:center;
            color:#6b7280;
            font-size:14px;
        }

        @media(min-width:768px){

            .product-grid{
                grid-template-columns:repeat(4,minmax(0,1fr));
            }

            .hero-banner{
                min-height:360px;
            }

            .hero-content h1{
                font-size:48px;
            }

        }

    </style>

</head>
<body>

<header class="header">

    <div class="container">

        <div class="header-wrapper">

            <a href="#" class="logo">
                Tiệm Trà Sữa X
            </a>

            <a href="./cart.php" class="cart-button">

            <i class="fa-solid fa-bag-shopping"></i>

            <span class="cart-count" id="cart-count">0</span>
            </a>

        </div>

    </div>

</header>

<section class="hero">

    <div class="container">

        <div class="hero-banner">

            <div class="hero-content">

                <h1>Trà sữa thơm ngon mỗi ngày</h1>

                <p>
                    Đặt trà sữa và bánh ngọt nhanh chóng.
                    Giao tận nơi — thanh toán COD tiện lợi.
                </p>

            </div>

        </div>

    </div>

</section>

<section class="container">

    <div class="category-list">

        <a href="#" class="category-item">
            Tất cả
        </a>

        <?php if($categories && mysqli_num_rows($categories) > 0): ?>

            <?php while($category = mysqli_fetch_assoc($categories)): ?>

                <a href="#" class="category-item">
                    <?= $category['name'] ?>
                </a>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>

</section>

<section class="container">

    <div class="section-title">
        <h2>Sản phẩm nổi bật</h2>
    </div>

    <div class="product-grid">

        <?php if($products && mysqli_num_rows($products) > 0): ?>

            <?php while($product = mysqli_fetch_assoc($products)): ?>

                <?php

                    $price = $product['price'];
                    $discount = $product['discount_percent'];

                    if($discount){
                        $finalPrice = $price - ($price * $discount / 100);
                    }else{
                        $finalPrice = $price;
                    }

                ?>

                <div class="product-card">

                    <div class="product-image">

                        <img
                            src="<?= !empty($product['image']) ? './uploads/products/' . $product['image'] : 'https://placehold.co/600x600' ?>"
                            alt="<?= $product['name'] ?>"
                        >

                        <?php if($discount): ?>

                            <div class="discount-badge">
                                -<?= $discount ?>%
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="product-body">

                        <div class="product-category">
                            <?= $product['category_name'] ?>
                        </div>

                        <h3 class="product-name">
                            <?= $product['name'] ?>
                        </h3>

                        <div class="rating">
                            <i class="fa-solid fa-star"></i>
                            <span><?= $product['rating_fake'] ?></span>
                        </div>

                        <div class="product-footer">

                            <div class="price-box">

                                <?php if($discount): ?>

                                    <span class="old-price">
                                        <?= number_format($price,0,',','.') ?>đ
                                    </span>

                                <?php endif; ?>

                                <span class="new-price">
                                    <?= number_format($finalPrice,0,',','.') ?>đ
                                </span>

                            </div>

                            <button
                                class="add-cart"
                                data-id="<?= $product['id'] ?>"
                                data-name="<?= $product['name'] ?>"
                                data-price="<?= $finalPrice ?>"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>Chưa có sản phẩm.</p>

        <?php endif; ?>

    </div>

</section>

<div class="floating-cart" id="floating-cart">

    <div>
        <strong id="cart-total-items">0 sản phẩm</strong>
    </div>

    <button id="open-checkout">
        Đặt hàng
    </button>

</div>

<div class="modal" id="checkout-modal">

    <div class="modal-content">

        <div class="modal-header">

            <h3>Thông tin giao hàng</h3>

            <button class="close-modal" id="close-modal">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <form action="./checkout.php" method="POST">

            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" class="form-control" name="customer_name" required>
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" class="form-control" name="phone" required>
            </div>

            <div class="form-group">
                <label>Địa chỉ giao hàng</label>
                <textarea class="form-control" name="address" required></textarea>
            </div>

            <input type="hidden" name="cart_data" id="cart-data">

            <button type="submit" class="checkout-btn">
                Xác nhận đặt hàng COD
            </button>

        </form>

    </div>

</div>

<footer class="footer">

    <div class="container">
        © 2026 Tiệm Trà Sữa X
    </div>

</footer>

<script>

async function loadCartCount(){

    const formData = new FormData();

    formData.append('action','list');

    const response = await fetch('./api/cart.php',{

        method:'POST',
        body:formData

    });

    const data = await response.json();

    if(data.status === 'success'){

        let total = 0;

        data.items.forEach(item=>{

            total += parseInt(item.quantity);

        });

        document.getElementById('cart-count').innerText = total;

    }

}

// ====================
// LOAD CART COUNT
// ====================

loadCartCount();

// ====================
// ADD TO CART
// ====================

async function addToCart(productId){

    const formData = new FormData();

    formData.append('action','add');
    formData.append('product_id',productId);

    const response = await fetch('./api/cart.php',{

        method:'POST',
        body:formData

    });

    const data = await response.json();

    if(data.status === 'success'){

        loadCartCount();

        alert('Đã thêm vào giỏ hàng');

    }

}

// ====================
// BUTTON EVENT
// ====================

document.querySelectorAll('.add-cart').forEach(button=>{

    button.addEventListener('click',()=>{

        const productId = button.dataset.id;

        addToCart(productId);

    });

});

</script>


</body>
</html>