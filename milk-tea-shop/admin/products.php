<?php
require_once "../core/auth.php";
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

    echo "
    <script>
    window.location.href='index.php?page=products';
    </script>
    ";
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

    $salePrice = 0;

    $description = trim($_POST['description']);

    $rating = (float) $_POST['rating_fake'];

    if($rating < 1){
        $rating = 1;
    }

    if($rating > 5){
        $rating = 5;
    }

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

    echo "
    <script>
    window.location.href='index.php?page=products';
    </script>
    ";
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
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    $image = $currentProduct['image'];

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

    echo "
    <script>
    window.location.href='index.php?page=products';
    </script>
    ";
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

<style>

.products-page-header{
    margin-bottom:24px;
}

.products-page-header h1{
    font-size:28px;
    margin-bottom:8px;
    color:#111827;
}

.products-page-header p{
    color:#6b7280;
}

.products-card{
    background:#fff;
    border-radius:20px;
    padding:24px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
    margin-bottom:24px;
}

.products-form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

.products-input-group{
    position:relative;
}

.products-input-group i{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:#9ca3af;
    font-size:20px;
}

.products-input-group input,
.products-input-group select{
    width:100%;
    height:52px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:0 16px 0 48px;
    font-size:15px;
    transition:0.25s;
    background:#fff;
}

.products-input-group input:focus,
.products-input-group select:focus,
.products-textarea textarea:focus{
    border-color:#f59e0b;
    outline:none;
}

.products-textarea{
    grid-column:1 / -1;
}

.products-textarea textarea{
    width:100%;
    min-height:130px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:16px;
    resize:none;
    font-size:15px;
}

.products-file{
    grid-column:1 / -1;
}

.products-file label{
    display:flex;
    align-items:center;
    gap:10px;
    background:#fff7ed;
    color:#ea580c;
    padding:16px;
    border-radius:14px;
    cursor:pointer;
    font-weight:600;
    border:2px dashed #fdba74;
}

.products-file input{
    display:none;
}

.products-preview{
    margin-top:18px;
}

.products-preview img{
    width:100%;
    max-width:350px;
    height:230px;
    object-fit:cover;
    border-radius:18px;
    border:2px solid #f1f5f9;
    background:#f9fafb;
    display:block;
}

.products-submit{
    grid-column:1 / -1;
}

.products-submit button{
    width:100%;
    height:54px;
    border:none;
    border-radius:14px;
    background:#f59e0b;
    color:#fff;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
    transition:0.25s;
}

.products-submit button:hover{
    background:#d97706;
}

.products-table-wrapper{
    overflow-x:auto;
}

.products-table{
    width:100%;
    border-collapse:collapse;
}

.products-table th{
    background:#f9fafb;
    padding:15px;
    text-align:left;
    color:#6b7280;
    font-size:14px;
}

.products-table td{
    padding:16px 15px;
    border-bottom:1px solid #f1f5f9;
    vertical-align:middle;
}

.products-info{
    display:flex;
    align-items:center;
    gap:14px;
    min-width:220px;
}

.products-info img{
    width:70px;
    height:70px;
    object-fit:cover;
    border-radius:16px;
    background:#f3f4f6;
}

.products-price{
    font-weight:700;
    color:#111827;
}

.products-sale{
    color:#dc2626;
    font-weight:700;
}

.products-rating{
    display:flex;
    align-items:center;
    gap:6px;
    color:#f59e0b;
    font-weight:700;
}

.products-actions{
    display:flex;
    align-items:center;
    gap:10px;
}

.products-actions a{
    width:42px;
    height:42px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:18px;
    transition:0.25s;
}

.products-edit{
    background:#2563eb;
}

.products-delete{
    background:#dc2626;
}

.products-pagination{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:10px;
    margin-top:24px;
    flex-wrap:wrap;
}

.products-pagination a{
    width:42px;
    height:42px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f3f4f6;
    color:#111827;
    font-weight:600;
}

.products-pagination a.active{
    background:#f59e0b;
    color:#fff;
}

.products-empty{
    text-align:center;
    padding:40px 20px;
    color:#6b7280;
}

.products-empty i{
    font-size:55px;
    margin-bottom:10px;
}

@media(max-width:768px){

    .products-form-grid{
        grid-template-columns:1fr;
    }

    .products-card{
        padding:18px;
    }

    .products-page-header h1{
        font-size:24px;
    }

    .products-table th,
    .products-table td{
        padding:12px 10px;
        font-size:13px;
    }

    .products-info{
        min-width:180px;
    }

    .products-info img{
        width:55px;
        height:55px;
    }

    .products-actions{
        flex-direction:column;
    }

    .products-preview img{
        max-width:100%;
        height:200px;
    }

}

</style>

<div class="products-page-header">

    <h1>
        <i class='bx bxs-package'></i>
        Quản lí sản phẩm
    </h1>

    <p>
        Quản lí menu trà sữa và bánh ngọt
    </p>

</div>

<div class="products-card">

    <form 
        method="POST"
        enctype="multipart/form-data"
        class="products-form-grid"
    >

        <?php if($editProduct): ?>

            <input 
                type="hidden"
                name="product_id"
                value="<?= $editProduct['id'] ?>"
            >

        <?php endif; ?>

        <div class="products-input-group">

            <i class='bx bx-coffee'></i>

            <input 
                type="text"
                name="name"
                placeholder="Tên sản phẩm"
                required
                value="<?= $editProduct['name'] ?? '' ?>"
            >

        </div>

        <div class="products-input-group">

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
                min="1"
                max="5"
                name="rating_fake"
                placeholder="Đánh giá từ 1 → 5"
                required
                value="<?= $editProduct['rating_fake'] ?? '5' ?>"
            >

        </div>

        <div class="products-textarea">

            <textarea 
                name="description"
                placeholder="Mô tả sản phẩm"
            ><?= $editProduct['description'] ?? '' ?></textarea>

        </div>

        <div class="products-file">

            <label>

                <i class='bx bx-image-add'></i>

                <?= $editProduct ? 'Đổi ảnh sản phẩm' : 'Chọn ảnh sản phẩm' ?>

                <input 
                    type="file"
                    name="image"
                    id="productImageInput"
                    accept="image/*"
                >

            </label>

            <div class="products-preview">

                <img 
                    id="previewImage"

                    src="<?= 
                        !empty($editProduct['image']) 
                        ? '../uploads/products/' . $editProduct['image']
                        : 'https://placehold.co/400x250?text=Preview'
                    ?>"
                >

            </div>

        </div>

        <div class="products-submit">

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

                                    <?= $product['rating_fake'] ?>

                                </div>

                            </td>

                            <td>

                                <div class="products-actions">

                                    <a 
                                        href="?page=products&edit=<?= $product['id'] ?>"
                                        class="products-edit"
                                    >

                                        <i class='bx bx-edit'></i>

                                    </a>

                                    <a 
                                        href="?page=products&delete=<?= $product['id'] ?>"
                                        class="products-delete"
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

                            <div class="products-empty">

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

    <?php if($totalPages > 1): ?>

        <div class="products-pagination">

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

<script>

const imageInput = document.getElementById('productImageInput');
const previewImage = document.getElementById('previewImage');

if(imageInput){

    imageInput.addEventListener('change', function(event){

        const file = event.target.files[0];

        if(file){

            previewImage.src = URL.createObjectURL(file);

        }

    });

}

</script>