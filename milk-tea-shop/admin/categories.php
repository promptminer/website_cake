<?php
require_once "../core/auth.php";
require_once "../core/db.php";

$editData = null;

$message = "";
$messageType = "";

$currentPage = basename($_SERVER['PHP_SELF']);

/*
|--------------------------------------------------------------------------
| ADD CATEGORY
|--------------------------------------------------------------------------
*/

if(isset($_POST['add_category'])){

    $name = trim($_POST['name']);

    if($name != ""){

        /*
        |--------------------------------------------------------------------------
        | CHECK EXISTS
        |--------------------------------------------------------------------------
        */

        $checkSql = "
            SELECT id
            FROM categories
            WHERE LOWER(name) = LOWER(?)
        ";

        $checkStmt = $pdo->prepare($checkSql);

        $checkStmt->execute([$name]);

        $categoryExists = $checkStmt->fetch();

        /*
        |--------------------------------------------------------------------------
        | EXISTS
        |--------------------------------------------------------------------------
        */

        if($categoryExists){

            $message = "Danh mục đã tồn tại";

            $messageType = "error";

        }else{

            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO categories(name)
                VALUES(?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([$name]);

            $message = "Thêm danh mục thành công";

            $messageType = "success";
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE CATEGORY
|--------------------------------------------------------------------------
*/

if(isset($_GET['delete'])){

    $id = (int) $_GET['delete'];

    $sql = "
        DELETE FROM categories
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$id]);

    header("Location: categories.php");

    exit;
}

/*
|--------------------------------------------------------------------------
| EDIT CATEGORY
|--------------------------------------------------------------------------
*/

if(isset($_GET['edit'])){

    $id = (int) $_GET['edit'];

    $sql = "
        SELECT *
        FROM categories
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$id]);

    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| UPDATE CATEGORY
|--------------------------------------------------------------------------
*/

if(isset($_POST['update_category'])){

    $id = (int) $_POST['id'];

    $name = trim($_POST['name']);

    /*
    |--------------------------------------------------------------------------
    | CHECK EXISTS UPDATE
    |--------------------------------------------------------------------------
    */

    $checkSql = "
        SELECT id
        FROM categories
        WHERE LOWER(name) = LOWER(?)
        AND id != ?
    ";

    $checkStmt = $pdo->prepare($checkSql);

    $checkStmt->execute([
        $name,
        $id
    ]);

    $categoryExists = $checkStmt->fetch();

    /*
    |--------------------------------------------------------------------------
    | EXISTS
    |--------------------------------------------------------------------------
    */

    if($categoryExists){

        $message = "Tên danh mục đã tồn tại";

        $messageType = "error";

    }else{

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $sql = "
            UPDATE categories
            SET name = ?
            WHERE id = ?
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $name,
            $id
        ]);

        $message = "Cập nhật danh mục thành công";

        $messageType = "success";

        /*
        |--------------------------------------------------------------------------
        | REFRESH EDIT DATA
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT *
            FROM categories
            WHERE id = ?
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([$id]);

        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/*
|--------------------------------------------------------------------------
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM categories
    ORDER BY id DESC
";

$stmt = $pdo->query($sql);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Quản lí danh mục
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
                <a
                    href="categories.php"
                    class="active"
                >
                    <i class="fa-solid fa-layer-group"></i>
                    Danh mục
                </a>
            </li>

            <li>
                <a href="products.php">
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
                    Quản lí danh mục
                </h1>

                <?php if(!empty($message)): ?>

                    <div class="alert <?= $messageType ?>">

                        <?= $message ?>

                    </div>

                <?php endif; ?>

                <?php if($editData): ?>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $editData['id'] ?>"
                        >

                        <input
                            type="text"
                            name="name"
                            placeholder="Tên danh mục"
                            required
                            value="<?= htmlspecialchars($editData['name']) ?>"
                        >

                        <button
                            type="submit"
                            name="update_category"
                            class="btn-add"
                        >
                            Cập nhật danh mục
                        </button>

                    </form>

                <?php else: ?>

                    <form method="POST">

                        <input
                            type="text"
                            name="name"
                            placeholder="Nhập tên danh mục"
                            required
                        >

                        <button
                            type="submit"
                            name="add_category"
                            class="btn-add"
                        >
                            Thêm danh mục
                        </button>

                    </form>

                <?php endif; ?>

            </div>

            <!-- TABLE -->

            <div class="card">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Tên danh mục</th>

                            <th>Ngày tạo</th>

                            <th>Hành động</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if(count($categories) > 0): ?>

                            <?php foreach($categories as $item): ?>

                                <tr>

                                    <td>
                                        <?= $item['id'] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </td>

                                    <td>
                                        <?= $item['created_at'] ?>
                                    </td>

                                    <td>

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
                                                onclick="return confirm('Xóa danh mục này?')"
                                            >
                                                Xóa
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="4"
                                    style="text-align:center;"
                                >
                                    Chưa có danh mục nào
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>