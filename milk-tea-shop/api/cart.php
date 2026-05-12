<?php

session_start();

header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "mysql";
$database = "milk_tea_shop";

$conn = mysqli_connect($host, $user, $password, $database);

mysqli_set_charset($conn,"utf8");

$sessionId = session_id();

$action = $_POST['action'] ?? '';

/*
|----------------------------------------------------------
| LẤY CART ID
|----------------------------------------------------------
*/

$getCart = mysqli_query($conn,"
    SELECT *
    FROM carts
    WHERE session_id = '$sessionId'
    LIMIT 1
");

$cart = mysqli_fetch_assoc($getCart);

if(!$cart){

    echo json_encode([
        'status' => 'error'
    ]);

    exit;

}

$cartId = $cart['id'];

/*
|----------------------------------------------------------
| UPDATE SỐ LƯỢNG
|----------------------------------------------------------
*/

if($action === 'update'){

    $productId = (int)$_POST['product_id'];

    $quantity = (int)$_POST['quantity'];

    if($quantity <= 0){

        $quantity = 1;

    }

    mysqli_query($conn,"
        UPDATE cart_items
        SET quantity = '$quantity'
        WHERE cart_id = '$cartId'
        AND product_id = '$productId'
    ");

    echo json_encode([
        'status' => 'success'
    ]);

    exit;

}

/*
|----------------------------------------------------------
| XÓA SẢN PHẨM
|----------------------------------------------------------
*/

if($action === 'remove'){

    $productId = (int)$_POST['product_id'];

    mysqli_query($conn,"
        DELETE FROM cart_items
        WHERE cart_id = '$cartId'
        AND product_id = '$productId'
    ");

    echo json_encode([
        'status' => 'success'
    ]);

    exit;

}

/*
|----------------------------------------------------------
| THÊM SẢN PHẨM
|----------------------------------------------------------
*/

if($action === 'add'){

    $productId = (int)$_POST['product_id'];

    $check = mysqli_query($conn,"
        SELECT *
        FROM cart_items
        WHERE cart_id = '$cartId'
        AND product_id = '$productId'
    ");

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,"
            UPDATE cart_items
            SET quantity = quantity + 1
            WHERE cart_id = '$cartId'
            AND product_id = '$productId'
        ");

    }else{

        mysqli_query($conn,"
            INSERT INTO cart_items(
                cart_id,
                product_id,
                quantity
            )
            VALUES(
                '$cartId',
                '$productId',
                1
            )
        ");

    }

    echo json_encode([
        'status' => 'success'
    ]);

    exit;

}

echo json_encode([
    'status' => 'error'
]);