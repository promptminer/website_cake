<?php
require_once "../core/auth.php";
require_once "../core/db.php";

$editData = null;

$currentPage = basename($_SERVER['PHP_SELF']);

/*
|--------------------------------------------------------------------------
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$categoryQuery = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
");

$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| ADD PRODUCT
|--------------------------------------------------------------------------
*/
if (isset($_POST['add_product'])) {

    $name = trim($_POST['name']);

    $category_id = (int) $_POST['category_id'];

    $price = $_POST['price'];

    $salePrice = 0;

    $description = trim($_POST['description']);

    $rating_fake = !empty($_POST['rating_fake'])
        ? $_POST['rating_fake']
        : 4.5;

    $imageName = "";

    if(
        isset($_FILES['image'])
        &&
        $_FILES['image']['error'] == 0
    ){

        $tmpName = $_FILES['image']['tmp_name'];

        $extension = strtolower(
            pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allow = ['jpg', 'jpeg', 'png', 'webp'];

        if(
            in_array($extension, $allow)
            &&
            $_FILES['image']['size'] <= 2 * 1024 * 1024
        ){

            $imageName =
                time()
                . '_'
                . uniqid()
                . '.'
                . $extension;

            move_uploaded_file(
                $tmpName,
                "../uploads/products/" . $imageName
            );
        }
    }

    $sql = "
        INSERT INTO products(
            name,
            category_id,
            price,
            sale_price,
            image,
            description,
            rating_fake
        )
        VALUES(
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $name,
        $category_id,
        $price,
        $sale_price,
        $imageName,
        $description,
        $rating_fake
    ]);

    header("Location: products.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE PRODUCT
|--------------------------------------------------------------------------
*/

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    $categoryId = (int) $_POST['category_id'];

    $price = $_POST['price'];

    $salePrice = 0;

    $description = trim($_POST['description']);

    $rating = (float) $_POST['rating_fake'];

    if($rating < 1){
        $rating = 1;
    }

    if($rating > 5){
        $rating = 5;
    }

    $stmt = $pdo->prepare("
        SELECT image
        FROM products
        WHERE id=?
    ");

    $getImage->execute([$id]);

    $imageData = $getImage->fetch(PDO::FETCH_ASSOC);

    if(
        $imageData
        &&
        !empty($imageData['image'])
    ){

        $imagePath =
            "../uploads/products/"
            . $imageData['image'];

        if(file_exists($imagePath)){
            unlink($imagePath);
        }
    }

    $sql = "
        DELETE FROM products
        WHERE id=?
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$id]);

    header("Location: products.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| EDIT PRODUCT
|--------------------------------------------------------------------------
*/

if(isset($_GET['edit'])){

    $id = (int) $_GET['edit'];

    $sql = "
        SELECT *
        FROM products
        WHERE id=?
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$id]);

    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| UPDATE PRODUCT
|--------------------------------------------------------------------------
*/

if(isset($_POST['update_product'])){

    $id = (int) $_POST['id'];

    $name = trim($_POST['name']);

    $category_id = (int) $_POST['category_id'];

    $price = $_POST['price'];

    $sale_price = !empty($_POST['sale_price'])
        ? $_POST['sale_price']
        : null;

    $description = trim($_POST['description']);

    $rating_fake = !empty($_POST['rating_fake'])
        ? $_POST['rating_fake']
        : 4.5;

    $oldImage = $_POST['old_image'];

    $imageName = $oldImage;

    if(
        isset($_FILES['image'])
        &&
        $_FILES['image']['error'] == 0
    ){

        $tmpName = $_FILES['image']['tmp_name'];

        $extension = strtolower(
            pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allow = ['jpg', 'jpeg', 'png', 'webp'];

        if(
            in_array($extension, $allow)
            &&
            $_FILES['image']['size'] <= 2 * 1024 * 1024
        ){

            if(!empty($oldImage)){

                $oldPath =
                    "../uploads/products/"
                    . $oldImage;

                if(file_exists($oldPath)){
                    unlink($oldPath);
                }
            }

            $imageName =
                time()
                . '_'
                . uniqid()
                . '.'
                . $extension;

            move_uploaded_file(
                $tmpName,
                "../uploads/products/" . $imageName
            );
        }
    }

    $sql = "
        UPDATE products
        SET
            name=?,
            category_id=?,
            price=?,
            sale_price=?,
            image=?,
            description=?,
            rating_fake=?
        WHERE id=?
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $name,
        $category_id,
        $price,
        $sale_price,
        $imageName,
        $description,
        $rating_fake,
        $id
    ]);

    header("Location: products.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        products.*,
        categories.name AS category_name
    FROM products

    LEFT JOIN categories
    ON categories.id = products.category_id

    ORDER BY products.id DESC
";

$stmt = $pdo->query($sql);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Quản lí sản phẩm
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->

    <div class="sidebar">

        <div class="logo">
            Milk Tea
        </div>

        <ul class="menu">

            <li>
                <a href="index.php">
                    <i class="fa-solid fa-house"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="categories.php">
                    <i class="fa-solid fa-layer-group"></i>
                    Danh mục
                </a>
            </li>

            <li>
                <a
                    href="products.php"
                    class="active"
                >
                    <i class="fa-solid fa-mug-hot"></i>
                    Sản phẩm
                </a>
            </li>

            <li>
                <a href="orders.php">
                    <i class="fa-solid fa-cart-shopping"></i>
                    Đơn hàng
                </a>
            </li>

            <li>
                <a href="promotions.php">
                    <i class="fa-solid fa-tags"></i>
                    Khuyến mãi
                </a>
            </li>

        </ul>

</div>

    <!-- MAIN -->

    <div class="main-content">

        <div class="container">

            <!-- FORM -->

            <div class="card">

                <h1>
                    Quản lí sản phẩm
                </h1>

                <?php if($editData): ?>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $editData['id'] ?>"
                        >

                        <input
                            type="hidden"
                            name="old_image"
                            value="<?= $editData['image'] ?>"
                        >

                        <input
                            type="text"
                            name="name"
                            placeholder="Tên sản phẩm"
                            required
                            value="<?= htmlspecialchars($editData['name']) ?>"
                        >

                        <select
                            name="category_id"
                            required
                        >

                            <option value="">
                                Chọn danh mục
                            </option>

                            <?php foreach($categories as $category): ?>

                                <option
                                    value="<?= $category['id'] ?>"
                                    <?= $editData['category_id'] == $category['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

        </div>

        <div class="products-input-group">

            <i class='bx bx-money'></i>

            <input 
                type="number"
                name="price"
                placeholder="Giá gốc"
                required
                value="<?= $editProduct['price'] ?? '' ?>"
            >

        </div>

        <!-- <div class="products-input-group">

            <i class='bx bx-purchase-tag'></i>

            <input 
                type="number"
                name="sale_price"
                placeholder="Giá khuyến mãi"
                value="<?= $editProduct['sale_price'] ?? '' ?>"
            >

        </div> -->

        <div class="products-input-group">

            <i class='bx bx-star'></i>

                        <input
                            type="number"
                            step="0.1"
                            name="rating_fake"
                            placeholder="Rating"
                            value="<?= $editData['rating_fake'] ?>"
                        >

                        <textarea
                            name="description"
                            placeholder="Mô tả sản phẩm"
                        ><?= htmlspecialchars($editData['description']) ?></textarea>

                        <input
                            type="file"
                            name="image"
                            accept="image/*"
                            onchange="previewImage(event)"
                        >

                        <img
                            id="preview"
                            class="preview-image"
                            src="../uploads/products/<?= $editData['image'] ?>"
                        >

                        <button
                            type="submit"
                            name="update_product"
                            class="btn-add"
                        >
                            Cập nhật sản phẩm
                        </button>

                    </form>

                <?php else: ?>

                <button 
                    type="submit"
                    name="add_product"
                >

                    <i class='bx bx-plus'></i>

                    Thêm sản phẩm

                </button>

            <?php endif; ?>

        </div>

    </form>

</div>

<div class="products-card">

    <div class="products-table-wrapper">

        <table class="products-table">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <!-- <th>Khuyến mãi</th> -->
                    <th>Rating</th>
                    <th>Hành động</th>

                </tr>

            </thead>

            <tbody>

                <?php if(count($products) > 0): ?>

                    <?php foreach($products as $product): ?>

                        <tr>

                            <td>
                                #<?= $product['id'] ?>
                            </td>

                            <td>

                                <div class="products-info">

                                    <img 
                                        src="../uploads/products/<?= $product['image'] ?>"
                                    >

                                    <div>

                                        <strong>
                                            <?= htmlspecialchars($product['name']) ?>
                                        </strong>

                                    </div>

                                </div>

                            </td>

                            <td>
                                <?= $product['category_name'] ?>
                            </td>

                            <td class="products-price">
                                <?= number_format($product['price']) ?>đ
                            </td>

                            

                            <td>

                                <div class="products-rating">

                                    <i class='bx bxs-star'></i>

                                    <?php endif; ?>

                                </div>

                                <p class="rating">
                                    ⭐ <?= $item['rating_fake'] ?>
                                </p>

                                <div class="action">

                                    <a
                                        href="?edit=<?= $item['id'] ?>"
                                        class="btn-edit"
                                    >
                                        Sửa
                                    </a>

                                    <a
                                        href="?delete=<?= $item['id'] ?>"
                                        class="btn-delete"
                                        onclick="return confirm('Xóa sản phẩm này?')"
                                    >
                                        Xóa
                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-product">
                    Chưa có sản phẩm nào
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<script>

function previewImage(event){

    const file = event.target.files[0];

    if(!file) return;

    const reader = new FileReader();

    reader.onload = function(){

        const preview =
            document.getElementById('preview');

        preview.src = reader.result;

        preview.style.display = 'block';
    };

    reader.readAsDataURL(file);
}

</script>

</body>
</html>

