<?php

require_once "../core/auth.php";
require_once "../core/db.php";

/*
|--------------------------------------------------------------------------
| CREATE POS ORDER
|--------------------------------------------------------------------------
*/

if(isset($_POST['create_pos_order'])){

    $cart = json_decode($_POST['cart_data'], true);

    $cashReceived = (float)$_POST['cash_received'];

    if(!$cart || count($cart) <= 0){

        echo "<script>alert('Vui lòng chọn sản phẩm');</script>";

    }else{

        $totalPrice = 0;

        foreach($cart as $item){

            $totalPrice += (
                $item['price'] *
                $item['quantity']
            );
        }

        if($cashReceived < $totalPrice){

            echo "<script>alert('Tiền khách đưa không đủ');</script>";

        }else{

            try{

                $pdo->beginTransaction();

                /*
                |--------------------------------------------------------------------------
                | INSERT ORDER
                |--------------------------------------------------------------------------
                */

                $insertOrder = $pdo->prepare("
                    INSERT INTO orders(
                        customer_name,
                        phone,
                        address,
                        total_price,
                        shipping_fee,
                        status
                    )
                    VALUES(
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

                $insertOrder->execute([
                    'Khách tại quầy',
                    '',
                    'Tại quầy',
                    $totalPrice,
                    0,
                    'delivered'
                ]);

                $orderId = $pdo->lastInsertId();

                /*
                |--------------------------------------------------------------------------
                | INSERT ORDER DETAILS
                |--------------------------------------------------------------------------
                */

                $insertDetail = $pdo->prepare("
                    INSERT INTO order_details(
                        order_id,
                        product_id,
                        quantity,
                        price
                    )
                    VALUES(
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

                foreach($cart as $item){

                    $insertDetail->execute([
                        $orderId,
                        $item['id'],
                        $item['quantity'],
                        $item['price']
                    ]);
                }

                $pdo->commit();

                echo "
                    <script>
                        window.location.href='invoice.php?id=".$orderId."';
                    </script>
                ";
                exit;

            }catch(Exception $e){

                $pdo->rollBack();

                echo "
                    <script>
                        alert('Có lỗi xảy ra');
                    </script>
                ";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT 
        products.*,
        categories.name as category_name
    FROM products
    LEFT JOIN categories
    ON products.category_id = categories.id
    ORDER BY products.id DESC
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<style>

.pos-page{
    display:flex;
    gap:24px;
    align-items:flex-start;
}

.pos-products{
    flex:2;
}

.pos-cart{
    width:380px;
    background:#fff;
    border-radius:24px;
    padding:22px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
    position:sticky;
    top:20px;
}

.pos-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:24px;
    flex-wrap:wrap;
}

.pos-header h1{
    font-size:30px;
    font-weight:700;
    color:#111827;
}

.pos-header p{
    margin-top:6px;
    color:#6b7280;
}

.pos-search{
    width:320px;
    position:relative;
}

.pos-search i{
    position:absolute;
    top:50%;
    left:16px;
    transform:translateY(-50%);
    color:#9ca3af;
    font-size:20px;
}

.pos-search input{
    width:100%;
    height:52px;
    border:none;
    background:#fff;
    border-radius:16px;
    padding:0 18px 0 48px;
    font-size:14px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
    outline:none;
}

.product-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
}

.product-card{
    background:#fff;
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 4px 18px rgba(0,0,0,0.05);
    transition:0.25s;
}

.product-card:hover{
    transform:translateY(-4px);
}

.product-image{
    width:100%;
    height:190px;
    overflow:hidden;
    background:#f3f4f6;
}

.product-image img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.product-body{
    padding:18px;
}

.product-category{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#fff7ed;
    color:#ea580c;
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    margin-bottom:12px;
}

.product-name{
    font-size:17px;
    font-weight:700;
    color:#111827;
    margin-bottom:10px;
}

.product-price{
    font-size:22px;
    font-weight:700;
    color:#f59e0b;
    margin-bottom:16px;
}

.add-cart-btn{
    width:100%;
    height:50px;
    border:none;
    border-radius:14px;
    background:#111827;
    color:#fff;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    transition:0.25s;
}

.add-cart-btn:hover{
    background:#f59e0b;
}

.cart-title{
    font-size:22px;
    font-weight:700;
    margin-bottom:22px;
}

.cart-list{
    display:flex;
    flex-direction:column;
    gap:16px;
    max-height:420px;
    overflow:auto;
    margin-bottom:20px;
}

.cart-item{
    display:flex;
    gap:12px;
    align-items:center;
    border-bottom:1px solid #f3f4f6;
    padding-bottom:14px;
}

.cart-item img{
    width:65px;
    height:65px;
    border-radius:14px;
    object-fit:cover;
}

.cart-item-info{
    flex:1;
}

.cart-item-info h4{
    font-size:14px;
    margin-bottom:6px;
}

.cart-item-info p{
    color:#f59e0b;
    font-weight:700;
    font-size:14px;
}

.qty-box{
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:8px;
}

.qty-btn{
    width:28px;
    height:28px;
    border:none;
    border-radius:8px;
    background:#f3f4f6;
    cursor:pointer;
    font-size:18px;
}

.remove-btn{
    width:34px;
    height:34px;
    border:none;
    border-radius:10px;
    background:#fef2f2;
    color:#dc2626;
    cursor:pointer;
    font-size:18px;
}

.cart-summary{
    border-top:1px solid #f3f4f6;
    padding-top:20px;
}

.summary-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:12px;
    font-size:15px;
}

.summary-total{
    font-size:22px;
    font-weight:700;
}

.cash-input{
    width:100%;
    height:54px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:0 16px;
    margin-top:12px;
    outline:none;
    font-size:15px;
}

.change-box{
    margin-top:16px;
    background:#ecfdf5;
    color:#059669;
    padding:14px;
    border-radius:14px;
    font-weight:700;
    text-align:center;
    font-size:16px;
}

.checkout-btn{
    width:100%;
    height:56px;
    border:none;
    border-radius:16px;
    background:#f59e0b;
    color:#fff;
    font-size:16px;
    font-weight:700;
    margin-top:18px;
    cursor:pointer;
}

.empty-cart{
    text-align:center;
    color:#9ca3af;
    padding:30px 10px;
}

@media(max-width:1200px){

    .product-grid{
        grid-template-columns:repeat(2,1fr);
    }

}

@media(max-width:900px){

    .pos-page{
        flex-direction:column;
    }

    .pos-cart{
        width:100%;
        position:static;
    }

}

@media(max-width:600px){

    .product-grid{
        grid-template-columns:repeat(2,1fr);
        gap:14px;
    }

    .product-image{
        height:140px;
    }

    .product-name{
        font-size:15px;
    }

    .product-price{
        font-size:18px;
    }

    .pos-header{
        flex-direction:column;
        align-items:stretch;
    }

    .pos-search{
        width:100%;
    }

}

</style>

<div class="pos-page">

    <div class="pos-products">

        <div class="pos-header">

            <div>

                <h1>
                    Bán tại quầy
                </h1>

                <p>
                    Tạo bill nhanh cho khách mua trực tiếp
                </p>

            </div>

            <div class="pos-search">

                <i class='bx bx-search'></i>

                <input
                    type="text"
                    id="searchInput"
                    placeholder="Tìm sản phẩm..."
                >

            </div>

        </div>

        <div class="product-grid" id="productGrid">

            <?php foreach($products as $product): ?>

                <div 
                    class="product-card product-item"
                    data-name="<?= strtolower($product['name']) ?>"
                >

                    <div class="product-image">

                        <img
                            src="../uploads/products/<?= htmlspecialchars($product['image']) ?>"
                            alt=""
                        >

                    </div>

                    <div class="product-body">

                        <div class="product-category">

                            <i class='bx bx-category'></i>

                            <?= htmlspecialchars($product['category_name']) ?>

                        </div>

                        <div class="product-name">

                            <?= htmlspecialchars($product['name']) ?>

                        </div>

                        <div class="product-price">

                            <?= number_format($product['price']) ?>đ

                        </div>

                        <button
                            class="add-cart-btn"
                            onclick='addToCart(
                                <?= $product["id"] ?>,
                                `<?= htmlspecialchars($product["name"], ENT_QUOTES) ?>`,
                                <?= $product["price"] ?>,
                                `<?= htmlspecialchars($product["image"], ENT_QUOTES) ?>`
                            )'
                        >

                            <i class='bx bx-plus'></i>
                            Thêm vào bill

                        </button>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

    <div class="pos-cart">

        <h2 class="cart-title">
            Bill hiện tại
        </h2>

        <div class="cart-list" id="cartList">

            <div class="empty-cart">
                Chưa có sản phẩm
            </div>

        </div>

        <form method="POST" id="posForm">

            <input
                type="hidden"
                name="cart_data"
                id="cartData"
            >

            <div class="cart-summary">

                <div class="summary-row summary-total">

                    <span>Tổng</span>

                    <span id="totalPrice">0đ</span>

                </div>

                <input
                    type="number"
                    name="cash_received"
                    id="cashReceived"
                    class="cash-input"
                    placeholder="Tiền khách đưa"
                    required
                >

                <div class="change-box">

                    Tiền thối:
                    <span id="changeMoney">0đ</span>

                </div>

                <button
                    type="submit"
                    name="create_pos_order"
                    class="checkout-btn"
                >

                    <i class='bx bx-printer'></i>
                    Tạo bill & In hóa đơn

                </button>

            </div>

        </form>

    </div>

</div>

<script>

let cart = [];

function addToCart(id,name,price,image){

    const existing = cart.find(item => item.id == id);

    if(existing){

        existing.quantity++;

    }else{

        cart.push({
            id,
            name,
            price,
            image,
            quantity:1
        });
    }

    renderCart();
}

function renderCart(){

    const cartList = document.getElementById('cartList');

    const totalPrice = document.getElementById('totalPrice');

    const cartData = document.getElementById('cartData');

    if(cart.length <= 0){

        cartList.innerHTML = `
            <div class="empty-cart">
                Chưa có sản phẩm
            </div>
        `;

        totalPrice.innerHTML = '0đ';

        return;
    }

    let html = '';

    let total = 0;

    cart.forEach((item,index)=>{

        total += (
            item.price *
            item.quantity
        );

        html += `
        
            <div class="cart-item">

                <img
                    src="../uploads/products/${item.image}"
                >

                <div class="cart-item-info">

                    <h4>
                        ${item.name}
                    </h4>

                    <p>
                        ${formatMoney(item.price)}đ
                    </p>

                    <div class="qty-box">

                        <button
                            type="button"
                            class="qty-btn"
                            onclick="decreaseQty(${index})"
                        >
                            -
                        </button>

                        <span>
                            ${item.quantity}
                        </span>

                        <button
                            type="button"
                            class="qty-btn"
                            onclick="increaseQty(${index})"
                        >
                            +
                        </button>

                    </div>

                </div>

                <button
                    type="button"
                    class="remove-btn"
                    onclick="removeItem(${index})"
                >

                    <i class='bx bx-trash'></i>

                </button>

            </div>

        `;
    });

    cartList.innerHTML = html;

    totalPrice.innerHTML = formatMoney(total)+'đ';

    cartData.value = JSON.stringify(cart);

    calculateChange();
}

function increaseQty(index){

    cart[index].quantity++;

    renderCart();
}

function decreaseQty(index){

    if(cart[index].quantity > 1){

        cart[index].quantity--;

    }else{

        cart.splice(index,1);
    }

    renderCart();
}

function removeItem(index){

    cart.splice(index,1);

    renderCart();
}

function formatMoney(number){

    return new Intl.NumberFormat('vi-VN').format(number);
}

function calculateChange(){

    let total = 0;

    cart.forEach(item=>{

        total += (
            item.price *
            item.quantity
        );
    });

    const cash = parseFloat(
        document.getElementById('cashReceived').value
    ) || 0;

    const change = cash - total;

    document.getElementById('changeMoney').innerHTML =
        formatMoney(change > 0 ? change : 0)+'đ';
}

document
.getElementById('cashReceived')
.addEventListener('input',calculateChange);

document
.getElementById('searchInput')
.addEventListener('keyup',function(){

    const value = this.value.toLowerCase();

    const items = document.querySelectorAll('.product-item');

    items.forEach(item=>{

        const name = item.dataset.name;

        if(name.includes(value)){

            item.style.display = 'block';

        }else{

            item.style.display = 'none';
        }
    });

});

</script>

