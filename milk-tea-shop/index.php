<?php

session_start();
/*
|---------------------------------------------------
| FILTER CATEGORY
|---------------------------------------------------
*/

$categoryId = isset($_GET['category'])
    ? (int)$_GET['category']
    : 0;
$keyword = isset($_GET['keyword'])
    ? trim($_GET['keyword'])
    : '';

$host     = "localhost";
$user     = "root";
$password = "mysql";
$database = "milk_tea_shop";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại");
}

mysqli_set_charset($conn, "utf8");

/*
|---------------------------------------------------
| SESSION + CART
|---------------------------------------------------
*/

$sessionId = session_id();

/*
|---------------------------------------------------
| TẠO GIỎ HÀNG NẾU CHƯA CÓ
|---------------------------------------------------
*/

$getCart = mysqli_query($conn, "
    SELECT id
    FROM carts
    WHERE session_id = '$sessionId'
    LIMIT 1
");

$cart = mysqli_fetch_assoc($getCart);

if (!$cart) {

    mysqli_query($conn, "
        INSERT INTO carts(session_id)
        VALUES('$sessionId')
    ");

    $cartId = mysqli_insert_id($conn);

} else {

    $cartId = $cart['id'];

}

/*
|---------------------------------------------------
| CATEGORY
|---------------------------------------------------
*/

$categories = mysqli_query($conn, "
    SELECT *
    FROM categories
    ORDER BY id DESC
");

/*
|---------------------------------------------------
| PRODUCTS
|---------------------------------------------------
*/

$where = [];

if($categoryId > 0){
    $where[] = "p.category_id = '$categoryId'";
}

if($keyword != ''){
    $keywordSql = mysqli_real_escape_string($conn, $keyword);
    $where[] = "p.name LIKE '%$keywordSql%'";
}

$whereSql = '';

if(count($where) > 0){
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$products = mysqli_query($conn, "
    SELECT 
        p.*,
        c.name as category_name,
        pr.discount_percent
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN promotions pr ON p.promotion_id = pr.id
    $whereSql
    ORDER BY p.id DESC
");

?>

<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Arpina coffee and bakery</title>

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
        font-size:18px;
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

    .admin-button{
        width:48px;
        height:48px;
        border-radius:14px;
        background:white;
        border:1px solid #e5e7eb;
        color:#111827;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:18px;
        transition:.2s;
    }

    .admin-button:hover{
        background:#111827;
        color:white;
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
        cursor:pointer;
        position:relative;
        z-index:10;
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
    /* TOAST */

#toast{
    position:fixed;
    top:24px;
    right:24px;
    background:#111827;
    color:white;
    padding:14px 18px;
    border-radius:16px;
    font-size:14px;
    font-weight:500;
    display:flex;
    align-items:center;
    gap:10px;
    box-shadow:0 10px 30px rgba(0,0,0,.15);
    opacity:0;
    pointer-events:none;
    transform:translateY(-20px);
    transition:.35s;
    z-index:9999;
}

#toast.show{
    opacity:1;
    transform:translateY(0);
}

#toast.success{
    background:#16a34a;
}

#toast.error{
    background:#dc2626;
}
.active-category{
    background:#111827 !important;
    color:white !important;
}
.orders-button{
    height:48px;
    padding:0 18px;
    border-radius:14px;
    background:white;
    border:1px solid #e5e7eb;
    color:#111827;
    display:flex;
    align-items:center;
    gap:8px;
    font-size:14px;
    font-weight:600;
    transition:.2s;
}

.orders-button:hover{
    background:#111827;
    color:white;
}
.search-form{
    margin:20px 0;
    display:flex;
    gap:12px;
}

.search-input{
    flex:1;
    height:52px;
    border:1px solid #e5e7eb;
    border-radius:16px;
    padding:0 18px;
    font-size:14px;
    outline:none;
    background:white;
}

.search-input:focus{
    border-color:#111827;
}

.search-btn{
    width:52px;
    height:52px;
    border:none;
    border-radius:16px;
    background:#111827;
    color:white;
    font-size:16px;
    cursor:pointer;
}
</style>

</head>
<body>

    <header class="header">

        <div class="container">

            <div class="header-wrapper">

                <a href="#" class="logo">
                    Arpina coffee and bakery
                </a>
                <div style="display:flex;align-items:center;gap:12px;">

            <!-- NÚT ADMIN -->
                <a href="./admin/login.php" class="admin-button">

                    <i class="fa-solid fa-user-shield"></i>

                </a>
               <a href="./my-orders.php" class="orders-button">

    <i class="fa-solid fa-receipt"></i>

    <span>Đơn hàng</span>

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

    <!-- SEARCH -->
<form method="GET" class="search-form">

    <?php if($categoryId > 0): ?>
        <input type="hidden" name="category" value="<?= $categoryId ?>">
    <?php endif; ?>

    <input
        type="text"
        name="keyword"
        placeholder="Tìm trà sữa, bánh ngọt..."
        value="<?= htmlspecialchars($keyword) ?>"
        class="search-input"
    >

    <button type="submit" class="search-btn">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

</form>
        <!-- DANH MỤC -->
        <div class="category-list">
            <a href="./index.php"
                class="category-item <?= $categoryId == 0 ? 'active-category' : '' ?>">
                    Tất cả
            </a>

            <?php if($categories && mysqli_num_rows($categories) > 0): ?>

                <?php while($category = mysqli_fetch_assoc($categories)): ?>

                    <a
    href="./index.php?category=<?= $category['id'] ?>"
    class="category-item <?= $categoryId == $category['id'] ? 'active-category' : '' ?>"
>
    <?= $category['name'] ?>
</a>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>

        <div class="section-title">
            <h2>Sản phẩm nổi bật</h2>
        </div>

        <div class="product-grid">

            <?php while($product = mysqli_fetch_assoc($products)): ?>

                <?php if(mysqli_num_rows($products) <= 0): ?>

<div style="
    grid-column:1/-1;
    background:white;
    padding:40px;
    border-radius:24px;
    text-align:center;
    border:1px solid #eee;
">
    Không tìm thấy sản phẩm
</div>

<?php endif; ?>
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
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    </section>

    <footer class="footer">

        <div class="container">
            © 2026 Arpina coffee and bakery
        </div>

    </footer>

<script>

    async function loadCartCount(){

        try{

            const formData = new FormData();

            formData.append('action','list');

            const response = await fetch('./api/cart.php',{

                method:'POST',
                body:formData,
                cache:'no-store'

            });

            const data = await response.json();

            if(data.status === 'success'){

                let total = 0;

                data.items.forEach(item=>{

                    total += parseInt(item.quantity);

                });

                document.getElementById('cart-count').innerText = total;

            }

        }catch(error){

            console.log(error);

        }

    }

    async function addToCart(productId){

        try{

            const formData = new FormData();

            formData.append('action','add');
            formData.append('product_id',productId);

            const response = await fetch('./api/cart.php',{

                method:'POST',
                body:formData,
                cache:'no-store'

            });

            const data = await response.json();

            if(data.status === 'success'){

                loadCartCount();

            showToast('Đã thêm vào giỏ hàng');

            }

        }catch(error){

            console.log(error);
           showToast('Có lỗi xảy ra','error');

        }

    }

/*
|---------------------------------------------------
| EVENT CLICK
|---------------------------------------------------
*/

    document.addEventListener('DOMContentLoaded', ()=>{

        loadCartCount();

    });

    document.addEventListener('click', async (e)=>{

        const button = e.target.closest('.add-cart');

        if(!button) return;

        if(button.disabled) return;

        button.disabled = true;

        const productId = button.dataset.id;

        await addToCart(productId);

        setTimeout(()=>{

            button.disabled = false;

        },500);

    });
function showToast(message,type='success'){

    const toast = document.getElementById('toast');

    toast.className = '';
    toast.id = 'toast';

    toast.classList.add('show');
    toast.classList.add(type);

    toast.innerHTML = `
        <i class="fa-solid fa-circle-check"></i>
        <span>${message}</span>
    `;

    clearTimeout(window.toastTimeout);

    window.toastTimeout = setTimeout(()=>{

        toast.classList.remove('show');

    },2500);

}
</script>
<!-- TOAST -->
<div id="toast"></div>
</body>
</html>