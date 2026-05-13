<?php
require_once "../core/auth.php";
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

         echo "<script>window.location.href='index.php?page=categories';</script>";
exit;
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

   echo "<script>window.location.href='index.php?page=categories';</script>";
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

        header("Location: index.php?page=categories");
        exit;
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

<style>

.category-page{
    display:flex;
    flex-direction:column;
    gap:24px;
}

.category-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    flex-wrap:wrap;
}

.category-header h1{
    font-size:30px;
    font-weight:700;
    color:#111827;
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:8px;
}

.category-header h1 i{
    color:#f59e0b;
}

.category-header p{
    color:#6b7280;
    font-size:15px;
}

.category-alert{
    padding:16px 18px;
    border-radius:16px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
    font-weight:500;
}

.category-alert.success{
    background:#ecfdf5;
    color:#059669;
}

.category-alert.error{
    background:#fef2f2;
    color:#dc2626;
}

.category-card{
    background:#fff;
    border-radius:22px;
    padding:24px;
    box-shadow:0 4px 18px rgba(0,0,0,0.05);
}

.category-form{
    display:flex;
    gap:16px;
    align-items:center;
}

.category-input{
    flex:1;
    position:relative;
}

.category-input i{
    position:absolute;
    left:16px;
    top:50%;
    transform:translateY(-50%);
    color:#9ca3af;
    font-size:20px;
}

.category-input input{
    width:100%;
    height:56px;
    border:1px solid #e5e7eb;
    border-radius:16px;
    padding:0 18px 0 50px;
    font-size:15px;
    transition:0.25s;
    outline:none;
    background:#f9fafb;
}

.category-input input:focus{
    border-color:#f59e0b;
    background:#fff;
}

.category-submit{
    height:56px;
    border:none;
    border-radius:16px;
    background:#f59e0b;
    color:#fff;
    padding:0 24px;
    font-size:15px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:10px;
    cursor:pointer;
    transition:0.25s;
}

.category-submit:hover{
    background:#d97706;
}

.category-table-wrapper{
    overflow-x:auto;
}

.category-table{
    width:100%;
    border-collapse:collapse;
    min-width:700px;
}

.category-table thead th{
    background:#f9fafb;
    padding:16px;
    text-align:left;
    color:#6b7280;
    font-size:14px;
    font-weight:600;
}

.category-table tbody td{
    padding:18px 16px;
    border-bottom:1px solid #f1f5f9;
    font-size:14px;
    vertical-align:middle;
}

.category-id{
    font-weight:700;
    color:#111827;
}

.category-name-box{
    display:flex;
    align-items:center;
    gap:12px;
    font-weight:600;
    color:#111827;
}

.category-dot{
    width:12px;
    height:12px;
    border-radius:50%;
    background:#f59e0b;
    flex-shrink:0;
}

.category-date{
    color:#6b7280;
}

.category-actions{
    display:flex;
    align-items:center;
    gap:10px;
}

.category-btn{
    width:42px;
    height:42px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    transition:0.25s;
}

.category-btn-edit{
    background:#eff6ff;
    color:#2563eb;
}

.category-btn-edit:hover{
    background:#2563eb;
    color:#fff;
}

.category-btn-delete{
    background:#fef2f2;
    color:#dc2626;
}

.category-btn-delete:hover{
    background:#dc2626;
    color:#fff;
}

.category-empty{
    padding:50px 20px;
    text-align:center;
}

.category-empty i{
    font-size:55px;
    color:#d1d5db;
    margin-bottom:14px;
}

.category-empty p{
    color:#6b7280;
    font-size:15px;
}

@media(max-width:768px){

    .category-header h1{
        font-size:24px;
    }

    .category-card{
        padding:18px;
        border-radius:18px;
    }

    .category-form{
        flex-direction:column;
        align-items:stretch;
    }

    .category-submit{
        width:100%;
        justify-content:center;
    }

    .category-table{
        min-width:650px;
    }

}

</style>

<div class="category-page">

    <div class="category-header">

        <div>

            <h1>
                <i class='bx bxs-category'></i>
                Quản lý danh mục
            </h1>

            <p>
                Quản lý danh mục sản phẩm hệ thống
            </p>

        </div>

    </div>

    <?php if($message): ?>

        <div class="category-alert <?= $messageType ?>">

            <i class='bx bx-info-circle'></i>

            <?= $message ?>

        </div>

    <?php endif; ?>

    <div class="category-card">

        <?php if($editCategory): ?>

            <form method="POST" class="category-form">

                <input 
                    type="hidden"
                    name="id"
                    value="<?= $editCategory['id'] ?>"
                >

                <div class="category-input">

                    <i class='bx bx-category'></i>

                    <input 
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars($editCategory['name']) ?>"
                        placeholder="Nhập tên danh mục"
                        required
                    >

                </div>

                <button 
                    type="submit" 
                    name="update_category"
                    class="category-submit"
                >

                    <i class='bx bx-save'></i>
                    Cập nhật

                </button>

            </form>

        <?php else: ?>

            <form method="POST" class="category-form">

                <div class="category-input">

                    <i class='bx bx-category'></i>

                    <input 
                        type="text"
                        name="name"
                        placeholder="Nhập tên danh mục"
                        required
                    >

                </div>

                <button 
                    type="submit"
                    name="add_category"
                    class="category-submit"
                >

                    <i class='bx bx-plus'></i>
                    Thêm danh mục

                </button>

            </form>

        <?php endif; ?>

    </div>

    <div class="category-card">

        <div class="category-table-wrapper">

            <table class="category-table">

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

                                    <span class="category-id">
                                        #<?= $category['id'] ?>
                                    </span>

                                </td>

                                <td>

                                    <div class="category-name-box">

                                        <span class="category-dot"></span>

                                        <?= htmlspecialchars($category['name']) ?>

                                    </div>

                                </td>

                                <td>

                                    <span class="category-date">
                                        <?= $category['created_at'] ?>
                                    </span>

                                </td>

                                <td>

                                    <div class="category-actions">

                                        <a 
                                            href="index.php?page=categories&edit=<?= $category['id'] ?>"
                                            class="category-btn category-btn-edit"
                                        >
                                            <i class='bx bx-edit'></i>
                                        </a>

                                        <a 
                                            href="index.php?page=categories&delete=<?= $category['id'] ?>"
                                            class="category-btn category-btn-delete"
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

                                <div class="category-empty">

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

</div>