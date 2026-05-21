<?php

session_start();

$host     = "localhost";
$user     = "root";
$password = "mysql";
$database = "milk_tea_shop";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại");
}

mysqli_set_charset($conn, "utf8");

$sessionId = session_id();

/*
|--------------------------------------------------------------------------
| PHÍ SHIP
|--------------------------------------------------------------------------
| Shop tự xác nhận sau
*/
define('SHIPPING_FEE', 0);

/*
|--------------------------------------------------------------------------
| LẤY HOẶC TẠO GIỎ HÀNG
|--------------------------------------------------------------------------
*/
$getCart = mysqli_query($conn, "
    SELECT id FROM carts
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
|--------------------------------------------------------------------------
| XỬ LÝ ĐẶT HÀNG
|--------------------------------------------------------------------------
*/
if (isset($_POST['place_order'])) {

    $customerName = mysqli_real_escape_string(
        $conn,
        trim($_POST['customer_name'])
    );

    $phone = mysqli_real_escape_string(
        $conn,
        trim($_POST['phone'])
    );

    $address = mysqli_real_escape_string(
        $conn,
        trim($_POST['address'])
    );

    // Tính tổng tiền hàng
    $cartItems = mysqli_query($conn, "
        SELECT
            cart_items.quantity,
            products.price
        FROM cart_items
        INNER JOIN products
            ON cart_items.product_id = products.id
        WHERE cart_items.cart_id = '$cartId'
    ");

    $totalPrice = 0;

    while ($row = mysqli_fetch_assoc($cartItems)) {

        $totalPrice += (
            $row['price'] * $row['quantity']
        );
    }

    $shippingFee = SHIPPING_FEE;

    $finalTotal = $totalPrice + $shippingFee;

    // Tạo đơn hàng
    $insertOrder = mysqli_query($conn, "
        INSERT INTO orders(
            customer_name,
            phone,
            address,
            total_price,
            shipping_fee,
            status,
            created_at
        )
        VALUES(
            '$customerName',
            '$phone',
            '$address',
            '$finalTotal',
            '$shippingFee',
            'pending',
            NOW()
        )
    ");

    if (!$insertOrder) {

        die(
            'Lỗi tạo đơn hàng: ' .
            mysqli_error($conn)
        );
    }

    $orderId = mysqli_insert_id($conn);

    // Lưu chi tiết đơn hàng
    $cartItems2 = mysqli_query($conn, "
        SELECT
            cart_items.quantity,
            products.id,
            products.price
        FROM cart_items
        INNER JOIN products
            ON cart_items.product_id = products.id
        WHERE cart_items.cart_id = '$cartId'
    ");

    while ($item = mysqli_fetch_assoc($cartItems2)) {

        $pid = (int)$item['id'];
        $qty = (int)$item['quantity'];
        $prc = (float)$item['price'];

        mysqli_query($conn, "
            INSERT INTO order_details(
                order_id,
                product_id,
                quantity,
                price
            )
            VALUES(
                '$orderId',
                '$pid',
                '$qty',
                '$prc'
            )
        ");
    }

    // Xóa giỏ hàng
    mysqli_query($conn, "
        DELETE FROM cart_items
        WHERE cart_id = '$cartId'
    ");

    $_SESSION['last_order_id'] = $orderId;

    header(
        "Location: success.php?order_id=" . $orderId
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| QUERY GIỎ HÀNG
|--------------------------------------------------------------------------
*/
$query = mysqli_query($conn, "
    SELECT
        cart_items.quantity,
        products.id,
        products.name,
        products.image,
        products.price
    FROM cart_items
    INNER JOIN products
        ON cart_items.product_id = products.id
    WHERE cart_items.cart_id = '$cartId'
    ORDER BY cart_items.id DESC
");

$total     = 0;
$itemCount = mysqli_num_rows($query);

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Giỏ hàng</title>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
/>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Be Vietnam Pro',sans-serif;
    background:#f5f5f5;
    color:#111827;
    min-height:100vh;
}

.container{
    width:100%;
    max-width:900px;
    margin:auto;
    padding:24px 20px 60px;
}

/* HEADER */

.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:28px;
}

.back-btn{
    width:46px;
    height:46px;
    border-radius:14px;
    background:#fff;
    border:1px solid #e5e7eb;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#111827;
    text-decoration:none;
    transition:.2s;
}

.back-btn:hover{
    background:#f3f4f6;
}

.title{
    font-size:28px;
    font-weight:800;
}

.badge{
    background:#fff;
    border:1px solid #e5e7eb;
    padding:8px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    color:#6b7280;
}

/* CART */

.cart-list{
    display:flex;
    flex-direction:column;
    gap:18px;
}

.cart-item{
    background:linear-gradient(
        180deg,
        #ffffff 0%,
        #fcfcfc 100%
    );

    border-radius:24px;
    padding:18px;

    display:flex;
    gap:18px;

    border:1px solid #ececec;

    box-shadow:
        0 10px 30px rgba(0,0,0,.04),
        0 2px 8px rgba(0,0,0,.03);

    transition:.25s ease;
}

.cart-item:hover{
    transform:translateY(-3px);

    box-shadow:
        0 18px 40px rgba(0,0,0,.08),
        0 4px 12px rgba(0,0,0,.05);
}

.cart-image{
    width:120px;
    height:120px;
    border-radius:18px;
    overflow:hidden;
    flex-shrink:0;
    background:#f3f4f6;
}

.cart-image img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.cart-content{
    flex:1;
}

.product-name{
    font-size:18px;
    font-weight:800;
    margin-bottom:6px;
}

.product-price{
    color:#6b7280;
    margin-bottom:16px;
}

.cart-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.quantity-box{
    display:flex;
    align-items:center;
    background:#f3f4f6;
    border-radius:14px;
    padding:4px;
}

.qty-btn{
    width:36px;
    height:36px;
    border:none;
    border-radius:10px;
    background:#fff;
    cursor:pointer;
    transition:.2s;
}

.qty-btn:hover{
    background:#111827;
    color:#fff;
}

.qty-number{
    width:42px;
    text-align:center;
    font-weight:700;
}

.subtotal{
    font-size:22px;
    font-weight:800;
}

.shipping-note{
    margin-top:14px;
    background:#f9fafb;
    border:1px dashed #d1d5db;
    border-radius:14px;
    padding:10px 12px;
    font-size:12px;
    color:#6b7280;
    line-height:1.5;
}

.remove-btn{
    margin-top:14px;
    border:none;
    background:#fff1f2;
    color:#ef4444;
    padding:10px 14px;
    border-radius:12px;
    cursor:pointer;
    font-size:13px;
    font-weight:700;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:.2s;
}

.remove-btn:hover{
    background:#ef4444;
    color:#fff;
}

/* SUMMARY */

.summary{
    background:#fff;
    margin-top:24px;
    border-radius:28px;
    padding:26px;
    border:1px solid #ececec;

    box-shadow:
        0 10px 30px rgba(0,0,0,.04),
        0 2px 8px rgba(0,0,0,.03);
}

.summary-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:14px 0;
    border-bottom:1px solid #f1f5f9;
}

.label{
    color:#6b7280;
    font-weight:500;
}

.value{
    color:#111827;
    font-weight:700;
}

.ship-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#ecfdf5;
    color:#10b981;
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
    margin-left:8px;
}

.summary-total{
    margin-top:22px;
    padding-top:18px;
    border-top:2px dashed #e5e7eb;

    display:flex;
    justify-content:space-between;
    align-items:center;
}

.total-label{
    font-size:16px;
    color:#6b7280;
    font-weight:600;
}

.total-value{
    font-size:30px;
    font-weight:900;
}

.checkout-btn{
    width:100%;
    height:58px;
    border:none;
    border-radius:18px;

    background:linear-gradient(
        135deg,
        #111827 0%,
        #1f2937 100%
    );

    color:#fff;
    font-size:15px;
    font-weight:800;

    margin-top:24px;

    cursor:pointer;

    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;

    transition:.2s;
}

.checkout-btn:hover{
    transform:translateY(-2px);
}

/* EMPTY */

.empty{
    background:#fff;
    border-radius:24px;
    padding:80px 20px;
    text-align:center;
}

.empty i{
    font-size:60px;
    color:#9ca3af;
    margin-bottom:18px;
}

.empty h2{
    margin-bottom:10px;
}

.empty p{
    color:#6b7280;
    margin-bottom:22px;
}

.empty a{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:#111827;
    color:#fff;
    padding:14px 22px;
    border-radius:14px;
    text-decoration:none;
    font-weight:700;
}

/* MODAL */

.modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    display:none;
    align-items:flex-end;
    justify-content:center;
    z-index:999;
    backdrop-filter:blur(3px);
}

.modal.active{
    display:flex;
}

.modal-box{
    width:100%;
    max-width:540px;
    background:#fff;
    border-radius:28px 28px 0 0;
    padding:28px 24px 34px;
    animation:slideUp .25s ease;
}

@keyframes slideUp{

    from{
        transform:translateY(30px);
        opacity:0;
    }

    to{
        transform:translateY(0);
        opacity:1;
    }
}

.modal-title{
    font-size:22px;
    font-weight:800;
    margin-bottom:20px;
}

.form-group{
    margin-bottom:14px;
}

.form-group label{
    display:block;
    margin-bottom:7px;
    font-weight:600;
}

.form-control{
    width:100%;
    height:52px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:0 14px;
    font-size:14px;
    outline:none;
}

textarea.form-control{
    height:110px;
    resize:none;
    padding-top:14px;
}

.submit-btn{
    width:100%;
    height:54px;
    border:none;
    border-radius:16px;
    background:#111827;
    color:#fff;
    font-size:15px;
    font-weight:800;
    cursor:pointer;
    margin-top:12px;
}

.close-btn{
    position:absolute;
    top:20px;
    right:20px;
    width:40px;
    height:40px;
    border:none;
    border-radius:12px;
    background:#f3f4f6;
    cursor:pointer;
}

@media(max-width:600px){

    .cart-item{
        flex-direction:column;
    }

    .cart-image{
        width:100%;
        height:220px;
    }
}

</style>

</head>

<body>

<div class="container">

<div class="header">

    <a href="./index.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="title">
        Giỏ hàng
    </div>

    <div class="badge">
        <?= $itemCount ?> SP
    </div>

</div>

<?php if($itemCount > 0): ?>

<div class="cart-list">

<?php while($item = mysqli_fetch_assoc($query)):

    $subTotal = (
        $item['price'] * $item['quantity']
    );

    $total += $subTotal;

?>

<div
    class="cart-item"
    id="item-<?= $item['id'] ?>"
>

    <div class="cart-image">

        <img
            src="<?= !empty($item['image'])
                ? './uploads/products/' .
                  htmlspecialchars($item['image'])
                : 'https://placehold.co/300x300'
            ?>"
            alt="<?= htmlspecialchars($item['name']) ?>"
        >

    </div>

    <div class="cart-content">

        <div class="product-name">
            <?= htmlspecialchars($item['name']) ?>
        </div>

        <div class="product-price">
            <?= number_format(
                $item['price'],
                0,
                ',',
                '.'
            ) ?>đ / ly
        </div>

        <div class="cart-footer">

            <div class="quantity-box">

                <button
                    class="qty-btn"
                    onclick="updateQuantity(
                        <?= $item['id'] ?>,
                        -1
                    )"
                >
                    <i class="fa-solid fa-minus"></i>
                </button>

                <div
                    class="qty-number"
                    id="qty-<?= $item['id'] ?>"
                >
                    <?= $item['quantity'] ?>
                </div>

                <button
                    class="qty-btn"
                    onclick="updateQuantity(
                        <?= $item['id'] ?>,
                        1
                    )"
                >
                    <i class="fa-solid fa-plus"></i>
                </button>

            </div>

            <div class="subtotal">

                <?= number_format(
                    $subTotal,
                    0,
                    ',',
                    '.'
                ) ?>đ

            </div>

        </div>

        <div class="shipping-note">

            <i class="fa-solid fa-truck"></i>

            Lưu ý:
            phí ship sẽ được cửa hàng
            xác nhận sau khi đặt hàng.

        </div>

        <button
            class="remove-btn"
            onclick="removeItem(
                <?= $item['id'] ?>
            )"
        >
            <i class="fa-solid fa-trash"></i>
            Xóa
        </button>

    </div>

</div>

<?php endwhile; ?>

</div>

<div class="summary">

    <div class="summary-row">

        <span class="label">
            Tiền hàng
        </span>

        <span class="value">

            <?= number_format(
                $total,
                0,
                ',',
                '.'
            ) ?>đ

        </span>

    </div>

    <div class="summary-row">

        <span class="label">
            Phí ship
        </span>

        <span class="value">

            Liên hệ xác nhận

            <span class="ship-badge">

                <i class="fa-solid fa-phone"></i>

                Xác nhận sau

            </span>

        </span>

    </div>

    <div class="summary-row">

        <span class="label">
            Thanh toán
        </span>

        <span class="value">
            COD — Tiền mặt
        </span>

    </div>

    <div class="summary-total">

        <span class="total-label">
            Tổng cộng
        </span>

        <span class="total-value">

            <?= number_format(
                $total,
                0,
                ',',
                '.'
            ) ?>đ

        </span>

    </div>

    <button
        class="checkout-btn"
        onclick="openModal()"
    >
        <i class="fa-solid fa-bag-shopping"></i>
        Đặt hàng ngay
    </button>

</div>

<?php else: ?>

<div class="empty">

    <i class="fa-solid fa-cart-shopping"></i>

    <h2>
        Giỏ hàng đang trống
    </h2>

    <p>
        Hãy chọn thêm đồ uống bạn nhé!
    </p>

    <a href="./index.php">

        <i class="fa-solid fa-store"></i>

        Xem thực đơn

    </a>

</div>

<?php endif; ?>

</div>

<!-- MODAL -->

<div class="modal" id="modal">

<div class="modal-box">

    <div class="modal-title">
        Thông tin đặt hàng
    </div>

    <form method="POST">

        <div class="form-group">

            <label>
                Họ và tên
            </label>

            <input
                type="text"
                name="customer_name"
                class="form-control"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Số điện thoại
            </label>

            <input
                type="tel"
                name="phone"
                class="form-control"
                required
            >

        </div>

        <div class="form-group">

            <label>
                Địa chỉ giao hàng
            </label>

            <textarea
                name="address"
                class="form-control"
                required
            ></textarea>

        </div>

        <button
            type="submit"
            name="place_order"
            class="submit-btn"
        >
            Xác nhận đặt hàng COD
        </button>

    </form>

</div>

</div>

<script>

async function updateQuantity(
    productId,
    change
){

    const qtyEl = document.getElementById(
        'qty-' + productId
    );

    const newQty = (
        parseInt(qtyEl.innerText) + change
    );

    if(newQty <= 0){

        removeItem(productId);

        return;
    }

    const fd = new FormData();

    fd.append('action','update');

    fd.append('product_id',productId);

    fd.append('quantity',newQty);

    const res = await fetch(
        './api/cart.php',
        {
            method:'POST',
            body:fd
        }
    );

    const data = await res.json();

    if(data.status === 'success'){

        location.reload();
    }
}

async function removeItem(productId){

    if(!confirm(
        'Bạn muốn xóa sản phẩm này?'
    )){
        return;
    }

    const fd = new FormData();

    fd.append('action','remove');

    fd.append('product_id',productId);

    const res = await fetch(
        './api/cart.php',
        {
            method:'POST',
            body:fd
        }
    );

    const data = await res.json();

    if(data.status === 'success'){

        location.reload();
    }
}

function openModal(){

    document
        .getElementById('modal')
        .classList.add('active');
}

function closeModal(){

    document
        .getElementById('modal')
        .classList.remove('active');
}

document
.getElementById('modal')
.addEventListener('click',function(e){

    if(e.target === this){

        closeModal();
    }
});

</script>

</body>
</html>
