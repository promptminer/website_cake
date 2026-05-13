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
| PHÍ SHIP CỐ ĐỊNH
|--------------------------------------------------------------------------
*/
define('SHIPPING_FEE', 7000);

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
    mysqli_query($conn, "INSERT INTO carts(session_id) VALUES('$sessionId')");
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

    $customerName = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
    $phone        = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address      = mysqli_real_escape_string($conn, trim($_POST['address']));

    // Tính tổng tiền hàng
    $cartItems = mysqli_query($conn, "
        SELECT cart_items.quantity, products.price
        FROM cart_items
        INNER JOIN products ON cart_items.product_id = products.id
        WHERE cart_items.cart_id = '$cartId'
    ");

    $totalPrice = 0;
    while ($row = mysqli_fetch_assoc($cartItems)) {
        $totalPrice += $row['price'] * $row['quantity'];
    }

    $shippingFee = SHIPPING_FEE; // Luôn luôn 7.000đ
    $finalTotal  = $totalPrice + $shippingFee;

    // Tạo đơn hàng
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

if(!$insertOrder){
    die("Lỗi tạo đơn hàng: " . mysqli_error($conn));
}

    $orderId = mysqli_insert_id($conn);

    // Lưu chi tiết đơn hàng
    $cartItems2 = mysqli_query($conn, "
        SELECT cart_items.quantity, products.id, products.price
        FROM cart_items
        INNER JOIN products ON cart_items.product_id = products.id
        WHERE cart_items.cart_id = '$cartId'
    ");

    while ($item = mysqli_fetch_assoc($cartItems2)) {
        $pid = (int)$item['id'];
        $qty = (int)$item['quantity'];
        $prc = (float)$item['price'];

        mysqli_query($conn, "
            INSERT INTO order_details(order_id, product_id, quantity, price)
            VALUES('$orderId', '$pid', '$qty', '$prc')
        ");
    }

    // Xóa giỏ hàng
    mysqli_query($conn, "DELETE FROM cart_items WHERE cart_id = '$cartId'");

    header("Location: success.php?order_id=" . $orderId);
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
    INNER JOIN products ON cart_items.product_id = products.id
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Giỏ hàng</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --dark:    #111827;
    --gray:    #6b7280;
    --light:   #9ca3af;
    --border:  #e5e7eb;
    --bg:      #f5f5f5;
    --white:   #ffffff;
    --danger:  #ef4444;
    --success: #10b981;
    --radius:  16px;
    --shadow:  0 2px 12px rgba(0,0,0,.07);
}

body {
    font-family: 'Be Vietnam Pro', sans-serif;
    background: var(--bg);
    color: var(--dark);
    min-height: 100vh;
}

/* ── LAYOUT ── */
.container {
    width: 100%;
    max-width: 860px;
    margin: auto;
    padding: 24px 20px 60px;
}

/* ── HEADER ── */
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
}

.back-btn {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--white);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: var(--dark);
    transition: background .15s;
}
.back-btn:hover { background: var(--border); }

.title { font-size: 26px; font-weight: 800; }

.badge {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray);
    background: var(--white);
    border: 1px solid var(--border);
    padding: 6px 12px;
    border-radius: 20px;
}

/* ── CART LIST ── */
.cart-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.cart-item {
    background: var(--white);
    border-radius: 20px;
    padding: 14px;
    display: flex;
    gap: 14px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    transition: box-shadow .2s;
}
.cart-item:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }

.cart-image {
    width: 110px;
    height: 110px;
    border-radius: 14px;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--bg);
}
.cart-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-content { flex: 1; min-width: 0; }

.product-name {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-price {
    font-size: 13px;
    color: var(--gray);
    margin-bottom: 14px;
}

.cart-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

/* ── QUANTITY ── */
.quantity-box {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    background: var(--bg);
}

.qty-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 13px;
    color: var(--dark);
    transition: background .15s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.qty-btn:hover { background: var(--border); }

.qty-number {
    width: 40px;
    text-align: center;
    font-weight: 700;
    font-size: 15px;
}

.subtotal {
    font-size: 17px;
    font-weight: 800;
    color: var(--dark);
}

.remove-btn {
    margin-top: 10px;
    border: none;
    background: #fef2f2;
    color: var(--danger);
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background .15s;
}
.remove-btn:hover { background: var(--danger); color: var(--white); }

/* ── SUMMARY ── */
.summary {
    background: var(--white);
    margin-top: 20px;
    border-radius: 20px;
    padding: 22px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
}

.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: 14px;
    color: var(--gray);
}
.summary-row:last-of-type { border-bottom: none; }

.summary-row .label { font-weight: 500; }
.summary-row .value { font-weight: 600; color: var(--dark); }

/* Chip phí ship cố định */
.ship-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #ecfdf5;
    color: var(--success);
    font-size: 12px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
}

.summary-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
}

.total-label { font-size: 15px; font-weight: 600; color: var(--gray); }
.total-value { font-size: 26px; font-weight: 800; }

/* ── CHECKOUT BUTTON ── */
.checkout-btn {
    width: 100%;
    height: 54px;
    border: none;
    border-radius: 14px;
    background: var(--dark);
    color: var(--white);
    font-size: 15px;
    font-weight: 700;
    margin-top: 18px;
    cursor: pointer;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: opacity .2s, transform .1s;
}
.checkout-btn:hover  { opacity: .88; }
.checkout-btn:active { transform: scale(.98); }

/* ── EMPTY ── */
.empty {
    background: var(--white);
    padding: 80px 20px;
    border-radius: 20px;
    text-align: center;
    border: 1px solid var(--border);
}
.empty i { font-size: 56px; color: var(--light); margin-bottom: 16px; display: block; }
.empty h2 { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
.empty p  { font-size: 14px; color: var(--gray); margin-bottom: 22px; }
.empty a  {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--dark);
    color: var(--white);
    padding: 12px 22px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
}

/* ── MODAL ── */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    display: none;
    align-items: flex-end;
    justify-content: center;
    padding: 0;
    z-index: 999;
    backdrop-filter: blur(3px);
}
.modal.active { display: flex; }

.modal-box {
    width: 100%;
    max-width: 540px;
    background: var(--white);
    border-radius: 28px 28px 0 0;
    padding: 28px 24px 34px;
    position: relative;
    animation: slideUp .25s ease;
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.modal-handle {
    width: 42px;
    height: 4px;
    background: var(--border);
    border-radius: 4px;
    margin: 0 auto 22px;
}

.modal-title {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 20px;
}

.form-group { margin-bottom: 14px; }
.form-group label {
    display: block;
    margin-bottom: 7px;
    font-weight: 600;
    font-size: 14px;
}

.form-control {
    width: 100%;
    height: 50px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 0 14px;
    font-family: inherit;
    font-size: 14px;
    transition: border-color .2s;
    outline: none;
}
.form-control:focus { border-color: var(--dark); }

textarea.form-control {
    height: 110px;
    resize: none;
    padding-top: 13px;
}

.submit-btn {
    width: 100%;
    height: 52px;
    border: none;
    border-radius: 14px;
    background: var(--dark);
    color: var(--white);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 12px;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: opacity .2s;
}
.submit-btn:hover { opacity: .85; }

.close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 38px;
    height: 38px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--bg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.close-btn:hover { background: var(--border); }

/* ── RESPONSIVE ── */
@media (max-width: 600px) {
    .cart-image { width: 90px; height: 90px; }
    .product-name { font-size: 15px; }
    .total-value { font-size: 22px; }
    .modal-box { border-radius: 24px 24px 0 0; }
}

@media (max-width: 420px) {
    .cart-item { flex-direction: column; }
    .cart-image { width: 100%; height: 200px; }
}

</style>
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <a href="./index.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="title">Giỏ hàng</div>
        <div class="badge"><?= $itemCount ?> SP</div>
    </div>

    <?php if ($itemCount > 0): ?>

    <!-- DANH SÁCH SẢN PHẨM -->
    <div class="cart-list">

        <?php while ($item = mysqli_fetch_assoc($query)):
            $subTotal = $item['price'] * $item['quantity'];
            $total   += $subTotal;
        ?>

        <div class="cart-item" id="item-<?= $item['id'] ?>" data-price="<?= $item['price'] ?>">

            <div class="cart-image">
                <img src="<?= !empty($item['image'])
                    ? './uploads/products/' . htmlspecialchars($item['image'])
                    : 'https://placehold.co/300x300/f5f5f5/9ca3af?text=SP' ?>"
                    alt="<?= htmlspecialchars($item['name']) ?>"
                >
            </div>

            <div class="cart-content">

                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="product-price"><?= number_format($item['price'], 0, ',', '.') ?>đ / ly</div>

                <div class="cart-footer">

                    <div class="quantity-box">
                        <button class="qty-btn" onclick="updateQuantity(<?= $item['id'] ?>, -1)">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <div class="qty-number" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></div>
                        <button class="qty-btn" onclick="updateQuantity(<?= $item['id'] ?>, 1)">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <div class="subtotal" id="subtotal-<?= $item['id'] ?>">
                        <?= number_format($subTotal, 0, ',', '.') ?>đ
                    </div>

                </div>
<div class="shipping-note" style="margin-top: 12px; display: block; font-size: 12px; color: var(--gray);">

    <i class="fa-solid fa-truck"></i>

    <span >
        Lưu ý: đơn hàng trong phạm vi 2km phí ship là 7.000đ.
        Sau mỗi 2km tiếp theo sẽ cộng thêm 5.000đ phí giao hàng.
    </span>

</div>

                <button class="remove-btn" onclick="removeItem(<?= $item['id'] ?>)">
                    <i class="fa-solid fa-trash"></i> Xóa
                </button>

            </div>
        </div>

        <?php endwhile; ?>

    </div>

    <!-- TỔNG KẾT -->
    <div class="summary">

        <div class="summary-row">
            <span class="label">Tiền hàng</span>
            <span class="value" id="subtotal-total"><?= number_format($total, 0, ',', '.') ?>đ</span>
        </div>

        <div class="summary-row">
            <span class="label">Phí ship</span>
            <span class="value">
                <?= number_format(SHIPPING_FEE, 0, ',', '.') ?>đ
                <span class="ship-badge"><i class="fa-solid fa-check"></i> Cố định</span>
            </span>
        </div>

        <div class="summary-row">
            <span class="label">Thanh toán</span>
            <span class="value">COD — Tiền mặt</span>
        </div>

        <div class="summary-total">
            <span class="total-label">Tổng cộng</span>
            <span class="total-value" id="final-total">
                <?= number_format($total + SHIPPING_FEE, 0, ',', '.') ?>đ
            </span>
        </div>

        <button class="checkout-btn" onclick="openModal()">
            <i class="fa-solid fa-bag-shopping"></i>
            Đặt hàng ngay
        </button>

    </div>

    <?php else: ?>

        
    <div class="empty">
        <i class="fa-solid fa-cart-shopping"></i>
        <h2>Giỏ hàng đang trống</h2>
        <p>Hãy chọn thêm đồ uống bạn nhé!</p>
        <a href="./index.php"><i class="fa-solid fa-store"></i> Xem thực đơn</a>
    </div>

    <?php endif; ?>

</div>

<!-- MODAL ĐẶT HÀNG -->
<div class="modal" id="modal">
    <div class="modal-box">

        <div class="modal-handle"></div>

        <button class="close-btn" onclick="closeModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="modal-title">Thông tin đặt hàng</div>

        <form method="POST">

            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="customer_name" class="form-control" placeholder="Nguyễn Văn A" required>
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" name="phone" class="form-control" placeholder="0912 345 678" required>
            </div>

            <div class="form-group">
                <label>Địa chỉ giao hàng</label>
                <textarea name="address" class="form-control" placeholder="Số nhà, đường, phường/xã, quận/huyện..." required></textarea>
            </div>

            <button type="submit" name="place_order" class="submit-btn">
                <i class="fa-solid fa-circle-check"></i>
                Xác nhận đặt hàng COD
            </button>

        </form>

    </div>
</div>

<script>

async function updateQuantity(productId, change) {
    const qtyEl  = document.getElementById('qty-' + productId);
    const newQty = parseInt(qtyEl.innerText) + change;

    if (newQty <= 0) { removeItem(productId); return; }

    const fd = new FormData();
    fd.append('action',     'update');
    fd.append('product_id', productId);
    fd.append('quantity',   newQty);

    const res  = await fetch('./api/cart.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.status === 'success') location.reload();
}

async function removeItem(productId) {
    const fd = new FormData();
    fd.append('action',     'remove');
    fd.append('product_id', productId);

    const res  = await fetch('./api/cart.php', { method: 'POST', body: fd });       
    const data = await res.json();

    if (data.status === 'success') location.reload();
}

function openModal()  { document.getElementById('modal').classList.add('active'); }
function closeModal() { document.getElementById('modal').classList.remove('active'); }

// Đóng modal khi click nền
document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

</script>
</body>
</html>