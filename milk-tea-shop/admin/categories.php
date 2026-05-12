<?php

require_once "../core/db.php";

$message = "";
$messageType = "success";

/*
|--------------------------------------------------------------------------
| ADD CATEGORY
|--------------------------------------------------------------------------
*/

if (isset($_POST['add_category'])) {

    $name = trim($_POST['name']);

    if ($name != "") {

        $check = $pdo->prepare("
            SELECT id 
            FROM categories 
            WHERE name = ?
        ");

        $check->execute([$name]);

        if ($check->rowCount() > 0) {

            $message = "Danh mục đã tồn tại";
            $messageType = "error";

        } else {

            $insert = $pdo->prepare("
                INSERT INTO categories(name)
                VALUES(?)
            ");

            $insert->execute([$name]);

            $message = "Thêm danh mục thành công";
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $delete = $pdo->prepare("
        DELETE FROM categories
        WHERE id = ?
    ");

    $delete->execute([$id]);

    header("Location: index.php?page=categories");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_category'])) {

    $id = (int) $_POST['id'];

    $name = trim($_POST['name']);

    $check = $pdo->prepare("
        SELECT id
        FROM categories
        WHERE name = ?
        AND id != ?
    ");

    $check->execute([$name, $id]);

    if ($check->rowCount() > 0) {

        $message = "Tên danh mục đã tồn tại";
        $messageType = "error";

    } else {

        $update = $pdo->prepare("
            UPDATE categories
            SET name = ?
            WHERE id = ?
        ");

        $update->execute([$name, $id]);

        $message = "Cập nhật thành công";
    }
}

/*
|--------------------------------------------------------------------------
| EDIT
|--------------------------------------------------------------------------
*/

$editCategory = null;

if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM categories
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| GET ALL
|--------------------------------------------------------------------------
*/

$categories = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<link rel="stylesheet" href="../assets/css/admin.css">

<div class="page-header">

    <div>
        <h1>
            <i class='bx bxs-category'></i>
            Quản lí danh mục
        </h1>

        <p>
            CRUD danh mục sản phẩm
        </p>
    </div>

</div>

<?php if($message): ?>

    <div class="alert <?= $messageType ?>">
        <i class='bx bx-info-circle'></i>
        <?= $message ?>
    </div>

<?php endif; ?>

<div class="card">

    <?php if($editCategory): ?>

        <form method="POST" class="category-form">

            <input 
                type="hidden"
                name="id"
                value="<?= $editCategory['id'] ?>"
            >

            <div class="input-group">

                <i class='bx bx-category'></i>

                <input 
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($editCategory['name']) ?>"
                    required
                >

            </div>

            <button type="submit" name="update_category">

                <i class='bx bx-save'></i>
                Cập nhật

            </button>

        </form>

    <?php else: ?>

        <form method="POST" class="category-form">

            <div class="input-group">

                <i class='bx bx-category'></i>

                <input 
                    type="text"
                    name="name"
                    placeholder="Nhập tên danh mục"
                    required
                >

            </div>

            <button type="submit" name="add_category">

                <i class='bx bx-plus'></i>
                Thêm danh mục

            </button>

        </form>

    <?php endif; ?>

</div>

<div class="card">

    <div class="table-wrapper">

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

                    <?php foreach($categories as $category): ?>

                        <tr>

                            <td>
                                #<?= $category['id'] ?>
                            </td>

                            <td>

                                <div class="category-name">

                                    <i></i>

                                    <?= htmlspecialchars($category['name']) ?>

                                </div>

                            </td>

                            <td>
                                <?= $category['created_at'] ?>
                            </td>

                            <td>

                                <div class="actions">

                                    <a 
                                        href="index.php?page=categories&edit=<?= $category['id'] ?>"
                                        class="btn-edit"
                                    >
                                        <i class='bx bx-edit'></i>
                                    </a>

                                    <a 
                                        href="index.php?page=categories&delete=<?= $category['id'] ?>"
                                        class="btn-delete"
                                        onclick="return confirm('Xóa danh mục này?')"
                                    >
                                        <i class='bx bx-trash'></i>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="4">

                            <div class="empty-data">

                                <i class='bx bx-folder-open'></i>

                                <p>
                                    Chưa có danh mục nào
                                </p>

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>