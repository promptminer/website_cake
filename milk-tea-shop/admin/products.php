<?php

require_once "../core/db.php";

/*
|--------------------------------------------------------------------------
| DELETE PRODUCT
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $delete = $pdo->prepare("
        DELETE FROM products
        WHERE id = ?
    ");

    $delete->execute([$id]);

    header("Location: index.php?page=products");
    exit;
}

/*
|--------------------------------------------------------------------------
| EDIT MODE
|--------------------------------------------------------------------------
*/

$editProduct = null;

if (isset($_GET['edit'])) {

    $editId = (int) $_GET['edit'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM products
        WHERE id = ?
    ");

    $stmt->execute([$editId]);

    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| ADD PRODUCT
|--------------------------------------------------------------------------
*/

if (isset($_POST['add_product'])) {

    $name = trim($_POST['name']);

    $categoryId = (int) $_POST['category_id'];

    $price = $_POST['price'];

    $salePrice = $_POST['sale_price'];

    $description = trim($_POST['description']);

    $rating = $_POST['rating_fake'];

    /*
    |--------------------------------------------------------------------------
    | IMAGE
    |--------------------------------------------------------------------------
    */

    $image = "";

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['name'] != ""
    ) {

        if (!is_dir("../uploads/products")) {

            mkdir(
                "../uploads/products",
                0777,
                true
            );
        }

        $image = time() . "_" . basename(
            $_FILES['image']['name']
        );

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            "../uploads/products/" . $image
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $insert = $pdo->prepare("
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
            ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $insert->execute([
        $name,
        $categoryId,
        $price,
        $salePrice,
        $image,
        $description,
        $rating
    ]);

    header("Location: index.php?page=products");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE PRODUCT
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_product'])) {

    $id = (int) $_POST['product_id'];

    $name = trim($_POST['name']);

    $categoryId = (int) $_POST['category_id'];

    $price = $_POST['price'];

    $salePrice = $_POST['sale_price'];

    $description = trim($_POST['description']);

    $rating = $_POST['rating_fake'];

    /*
    |--------------------------------------------------------------------------
    | CURRENT IMAGE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT image
        FROM products
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    $image = $currentProduct['image'];

    /*
    |--------------------------------------------------------------------------
    | NEW IMAGE
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['name'] != ""
    ) {

        if (!is_dir("../uploads/products")) {

            mkdir(
                "../uploads/products",
                0777,
                true
            );
        }

        $image = time() . "_" . basename(
            $_FILES['image']['name']
        );

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            "../uploads/products/" . $image
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    $update = $pdo->prepare("
        UPDATE products
        SET
            name = ?,
            category_id = ?,
            price = ?,
            sale_price = ?,
            image = ?,
            description = ?,
            rating_fake = ?
        WHERE id = ?
    ");

    $update->execute([
        $name,
        $categoryId,
        $price,
        $salePrice,
        $image,
        $description,
        $rating,
        $id
    ]);

    header("Location: index.php?page=products");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

$limit = 8;

$pageNumber = isset($_GET['p'])
    ? (int) $_GET['p']
    : 1;

if($pageNumber < 1){
    $pageNumber = 1;
}

$offset = ($pageNumber - 1) * $limit;

/*
|--------------------------------------------------------------------------
| TOTAL ROWS
|--------------------------------------------------------------------------
*/

$totalRowsQuery = $pdo->query("
    SELECT COUNT(*)
    FROM products
");

$totalRows = $totalRowsQuery->fetchColumn();

$totalPages = ceil($totalRows / $limit);

/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
*/

$products = $pdo->query("
    SELECT 
        products.*,
        categories.name AS category_name
    FROM products

    LEFT JOIN categories
    ON products.category_id = categories.id

    ORDER BY products.id DESC

    LIMIT $limit OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);

?>

<link 
    rel="stylesheet"
    href="../assets/css/admin.css"
>

<div class="page-header">

    <div>

        <h1>

            <i class='bx bxs-coffee-alt'></i>

            Quản lí sản phẩm

        </h1>

        <p>
            Quản lí menu trà sữa và bánh ngọt
        </p>

    </div>

</div>

<!-- FORM -->

<div class="card">

    <form 
        method="POST"
        enctype="multipart/form-data"
        class="product-form"
    >

        <?php if($editProduct): ?>

            <input 
                type="hidden"
                name="product_id"
                value="<?= $editProduct['id'] ?>"
            >

        <?php endif; ?>

        <!-- NAME -->

        <div class="input-group">

            <i class='bx bx-coffee'></i>

            <input 
                type="text"
                name="name"
                placeholder="Tên sản phẩm"
                required

                value="<?= $editProduct['name'] ?? '' ?>"
            >

        </div>

        <!-- CATEGORY -->

        <div class="input-group">

            <i class='bx bx-category'></i>

            <select name="category_id" required>

                <option value="">
                    Chọn danh mục
                </option>

                <?php foreach($categories as $category): ?>

                    <option 
                        value="<?= $category['id'] ?>"

                        <?= 
                            isset($editProduct) &&
                            $editProduct['category_id'] == $category['id']
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= $category['name'] ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <!-- PRICE -->

        <div class="input-group">

            <i class='bx bx-money'></i>

            <input 
                type="number"
                name="price"
                placeholder="Giá gốc"
                required

                value="<?= $editProduct['price'] ?? '' ?>"
            >

        </div>

        <!-- SALE PRICE -->

        <div class="input-group">

            <i class='bx bx-purchase-tag'></i>

            <input 
                type="number"
                name="sale_price"
                placeholder="Giá khuyến mãi"

                value="<?= $editProduct['sale_price'] ?? '' ?>"
            >

        </div>

        <!-- RATING -->

        <div class="input-group">

            <i class='bx bx-star'></i>

            <input 
                type="number"
                step="0.1"
                name="rating_fake"
                placeholder="Đánh giá giả"

                value="<?= $editProduct['rating_fake'] ?? '' ?>"
            >

        </div>

        <!-- DESCRIPTION -->

        <div class="textarea-group">

            <textarea 
                name="description"
                placeholder="Mô tả sản phẩm"
            ><?= $editProduct['description'] ?? '' ?></textarea>

        </div>

        <!-- IMAGE -->

        <div class="input-file">

            <label>

                <i class='bx bx-image-add'></i>

                <?= $editProduct ? 'Đổi ảnh sản phẩm' : 'Chọn ảnh sản phẩm' ?>

                <input 
                    type="file"
                    name="image"
                    accept="image/*"
                >

            </label>

        </div>

        <!-- BUTTON -->

        <?php if($editProduct): ?>

            <button 
                type="submit"
                name="update_product"
            >

                <i class='bx bx-save'></i>

                Cập nhật sản phẩm

            </button>

        <?php else: ?>

            <button 
                type="submit"
                name="add_product"
            >

                <i class='bx bx-plus'></i>

                Thêm sản phẩm

            </button>

        <?php endif; ?>

    </form>

</div>

<!-- PRODUCT TABLE -->

<div class="card">

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Khuyến mãi</th>
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

                                <div class="product-name">

                                    <img 
                                        src="../uploads/products/<?= $product['image'] ?>"
                                        class="product-image"
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

                            <td>

                                <?= number_format($product['price']) ?>đ

                            </td>

                            <td>

                                <?= number_format($product['sale_price']) ?>đ

                            </td>

                            <td>

                                <div class="rating">

                                    <i class='bx bxs-star'></i>

                                    <?= $product['rating_fake'] ?>

                                </div>

                            </td>

                            <td>

                                <div class="actions">

                                    <!-- EDIT -->

                                    <a 
                                        href="?page=products&edit=<?= $product['id'] ?>"
                                        class="btn-edit"
                                    >

                                        <i class='bx bx-edit'></i>

                                    </a>

                                    <!-- DELETE -->

                                    <a 
                                        href="?page=products&delete=<?= $product['id'] ?>"
                                        class="btn-delete"

                                        onclick="return confirm('Xóa sản phẩm này?')"
                                    >

                                        <i class='bx bx-trash'></i>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">

                            <div class="empty-data">

                                <i class='bx bx-package'></i>

                                <p>
                                    Chưa có sản phẩm nào
                                </p>

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- PAGINATION -->

    <?php if($totalPages > 1): ?>

        <div class="pagination">

            <?php if($pageNumber > 1): ?>

                <a 
                    href="?page=products&p=<?= $pageNumber - 1 ?>"
                >

                    <i class='bx bx-chevron-left'></i>

                </a>

            <?php endif; ?>

            <?php for($i = 1; $i <= $totalPages; $i++): ?>

                <a 
                    href="?page=products&p=<?= $i ?>"
                    class="<?= $i == $pageNumber ? 'active' : '' ?>"
                >

                    <?= $i ?>

                </a>

            <?php endfor; ?>

            <?php if($pageNumber < $totalPages): ?>

                <a 
                    href="?page=products&p=<?= $pageNumber + 1 ?>"
                >

                    <i class='bx bx-chevron-right'></i>

                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>