<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";

$error = "";
$success = "";


/* =========================
   DELETE CATEGORY
   ========================= */

if (isset($_GET["delete"])) {

    $deleteId = (int)$_GET["delete"];

    if ($deleteId > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM categories WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $deleteId
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: categories.php?deleted=1");
            exit;

        } else {

            $error = "Category could not be deleted.";

        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   EDIT CATEGORY
   ========================= */

$editId = 0;
$editName = "";
$editDescription = "";
$editImage = "";


if (isset($_GET["edit"])) {

    $editId = (int)$_GET["edit"];

    if ($editId > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, description, image
             FROM categories
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $editId
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($category = mysqli_fetch_assoc($result)) {

            $editName = $category["name"];
            $editDescription = $category["description"];
            $editImage = $category["image"];

        } else {

            $editId = 0;
            $error = "Category not found.";

        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   ADD / UPDATE CATEGORY
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $categoryId = (int)($_POST["category_id"] ?? 0);

    if ($name === "") {

        $error = "Category name is required.";

    } else {


        /* =====================
           UPDATE CATEGORY
           ===================== */

        if ($action === "update" && $categoryId > 0) {

            /* Get old image */

            $oldImage = "";

            $stmt = mysqli_prepare(
                $conn,
                "SELECT image FROM categories WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $categoryId
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $oldImage = $row["image"];
            }

            mysqli_stmt_close($stmt);


            $imageName = $oldImage;


            /* Upload new image */

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                    $error = "Image upload failed.";

                } else {

                    $allowedTypes = [
                        "image/jpeg",
                        "image/png",
                        "image/webp",
                        "image/gif"
                    ];

                    $fileType = mime_content_type(
                        $_FILES["image"]["tmp_name"]
                    );

                    if (!in_array($fileType, $allowedTypes, true)) {

                        $error =
                            "Only JPG, PNG, WEBP and GIF images are allowed.";

                    } elseif (
                        $_FILES["image"]["size"] > 5 * 1024 * 1024
                    ) {

                        $error =
                            "Image size must be less than 5 MB.";

                    } else {

                        $extension = strtolower(
                            pathinfo(
                                $_FILES["image"]["name"],
                                PATHINFO_EXTENSION
                            )
                        );

                        $imageName =
                            "category_" .
                            time() .
                            "_" .
                            uniqid() .
                            "." .
                            $extension;

                        $uploadDir =
                            __DIR__ . "/../assets/images/";

                        if (!is_dir($uploadDir)) {

                            mkdir(
                                $uploadDir,
                                0777,
                                true
                            );
                        }

                        $uploadPath =
                            $uploadDir . $imageName;

                        if (!move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $uploadPath
                        )) {

                            $error =
                                "Unable to save category image.";

                        }

                    }
                }
            }


            if ($error === "") {

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE categories
                     SET name = ?, description = ?, image = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssi",
                    $name,
                    $description,
                    $imageName,
                    $categoryId
                );

                if (mysqli_stmt_execute($stmt)) {

                    /* Delete old image */

                    if (
                        $imageName !== $oldImage &&
                        $oldImage !== ""
                    ) {

                        $oldFile =
                            __DIR__ .
                            "/../assets/images/" .
                            $oldImage;

                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    header(
                        "Location: categories.php?updated=1"
                    );

                    exit;

                } else {

                    $error =
                        "Category could not be updated.";

                }

                mysqli_stmt_close($stmt);
            }


        /* =====================
           ADD CATEGORY
           ===================== */

        } elseif ($action === "add") {

            $imageName = "";


            /* Upload image */

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                    $error = "Image upload failed.";

                } else {

                    $allowedTypes = [
                        "image/jpeg",
                        "image/png",
                        "image/webp",
                        "image/gif"
                    ];

                    $fileType = mime_content_type(
                        $_FILES["image"]["tmp_name"]
                    );

                    if (!in_array($fileType, $allowedTypes, true)) {

                        $error =
                            "Only JPG, PNG, WEBP and GIF images are allowed.";

                    } elseif (
                        $_FILES["image"]["size"] > 5 * 1024 * 1024
                    ) {

                        $error =
                            "Image size must be less than 5 MB.";

                    } else {

                        $extension = strtolower(
                            pathinfo(
                                $_FILES["image"]["name"],
                                PATHINFO_EXTENSION
                            )
                        );

                        $imageName =
                            "category_" .
                            time() .
                            "_" .
                            uniqid() .
                            "." .
                            $extension;

                        $uploadDir =
                            __DIR__ . "/../assets/images/";

                        if (!is_dir($uploadDir)) {

                            mkdir(
                                $uploadDir,
                                0777,
                                true
                            );
                        }

                        $uploadPath =
                            $uploadDir . $imageName;

                        if (!move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $uploadPath
                        )) {

                            $error =
                                "Unable to save category image.";

                        }

                    }
                }
            }


            if ($error === "") {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO categories
                     (name, description, image)
                     VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sss",
                    $name,
                    $description,
                    $imageName
                );

                if (mysqli_stmt_execute($stmt)) {

                    header(
                        "Location: categories.php?added=1"
                    );

                    exit;

                } else {

                    if ($imageName !== "") {

                        $uploadedFile =
                            __DIR__ .
                            "/../assets/images/" .
                            $imageName;

                        if (file_exists($uploadedFile)) {
                            unlink($uploadedFile);
                        }
                    }

                    $error =
                        "Category could not be added.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}


/* =========================
   SUCCESS MESSAGES
   ========================= */

if (isset($_GET["added"])) {
    $success = "Category added successfully.";
}

if (isset($_GET["updated"])) {
    $success = "Category updated successfully.";
}

if (isset($_GET["deleted"])) {
    $success = "Category deleted successfully.";
}


/* =========================
   FETCH CATEGORIES
   ========================= */

$categories = mysqli_query(
    $conn,
    "SELECT *
     FROM categories
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Categories - EasyMart Admin</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .navbar {
            background: #212529;
            color: white;
            padding: 15px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 18px;
        }

        .container {
            padding: 30px;
        }

        .grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input,
        textarea {
            width: 100%;
            padding: 11px;

            border: 1px solid #ccc;
            border-radius: 6px;

            font-size: 14px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;

            border: none;
            border-radius: 6px;

            text-decoration: none;
            color: white;

            cursor: pointer;
        }

        .btn-add {
            background: #198754;
        }

        .btn-edit {
            background: #0d6efd;
        }

        .btn-delete {
            background: #dc3545;
        }

        .btn-cancel {
            background: #6c757d;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .error {
            background: #f8d7da;
            color: #842029;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #212529;
            color: white;
        }

        .category-image {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 7px;
        }

        .no-image {
            width: 65px;
            height: 65px;
            background: #eee;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 7px;
            font-size: 11px;
            color: #777;
        }

        .actions {
            white-space: nowrap;
        }

        .actions a {
            margin-right: 5px;
        }

        .edit-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 7px;
            margin-top: 8px;
        }

        @media (max-width: 900px) {

            .grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;
                gap: 10px;
            }

            .container {
                padding: 15px;
            }

        }

    </style>

</head>

<body>


<!-- Navbar -->

<div class="navbar">

    <h2>EasyMart Admin</h2>

    <div>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="../index.php">
            Store
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<div class="container">

    <h1>Category Management</h1>


    <?php if ($success !== ""): ?>

        <div class="message success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <div class="grid">


        <!-- =====================
             ADD / EDIT FORM
             ===================== -->

        <div class="card">

            <?php if ($editId > 0): ?>

                <h2>Edit Category</h2>

            <?php else: ?>

                <h2>Add Category</h2>

            <?php endif; ?>


            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <?php if ($editId > 0): ?>

                    <input
                        type="hidden"
                        name="action"
                        value="update"
                    >

                    <input
                        type="hidden"
                        name="category_id"
                        value="<?= $editId ?>"
                    >

                <?php else: ?>

                    <input
                        type="hidden"
                        name="action"
                        value="add"
                    >

                <?php endif; ?>


                <div class="form-group">

                    <label>
                        Category Name *
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars(
                            $editName
                        ) ?>"
                        placeholder="Enter category name"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Enter category description"
                    ><?= htmlspecialchars(
                        $editDescription
                    ) ?></textarea>

                </div>


                <div class="form-group">

                    <label>
                        Category Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp,.gif"
                    >

                    <?php if (
                        $editId > 0 &&
                        $editImage !== ""
                    ): ?>

                        <br>

                        <img
                            src="../assets/images/<?= htmlspecialchars(
                                $editImage
                            ) ?>"
                            class="edit-image"
                            alt="Category"
                        >

                    <?php endif; ?>

                </div>


                <?php if ($editId > 0): ?>

                    <button
                        type="submit"
                        class="btn btn-edit"
                    >
                        Update Category
                    </button>

                    <a
                        href="categories.php"
                        class="btn btn-cancel"
                    >
                        Cancel
                    </a>

                <?php else: ?>

                    <button
                        type="submit"
                        class="btn btn-add"
                    >
                        Add Category
                    </button>

                <?php endif; ?>


            </form>

        </div>


        <!-- =====================
             CATEGORY LIST
             ===================== -->

        <div class="card">

            <h2>All Categories</h2>

            <div style="overflow-x:auto;">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Image</th>

                            <th>Name</th>

                            <th>Description</th>

                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (
                        $categories &&
                        mysqli_num_rows($categories) > 0
                    ): ?>

                        <?php while (
                            $category =
                            mysqli_fetch_assoc($categories)
                        ): ?>

                            <tr>

                                <td>
                                    <?= (int)$category["id"] ?>
                                </td>


                                <td>

                                    <?php if (
                                        !empty($category["image"])
                                    ): ?>

                                        <img
                                            src="../assets/images/<?= htmlspecialchars(
                                                $category["image"]
                                            ) ?>"
                                            class="category-image"
                                            alt="<?= htmlspecialchars(
                                                $category["name"]
                                            ) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="no-image">
                                            No Image
                                        </div>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $category["name"]
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        substr(
                                            $category["description"] ?? "",
                                            0,
                                            70
                                        )
                                    ) ?>

                                    <?php if (
                                        strlen(
                                            $category["description"] ?? ""
                                        ) > 70
                                    ): ?>

                                        ...

                                    <?php endif; ?>

                                </td>


                                <td class="actions">

                                    <a
                                        href="categories.php?edit=<?= (int)$category["id"] ?>"
                                        class="btn btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="categories.php?delete=<?= (int)$category["id"] ?>"
                                        class="btn btn-delete"
                                        onclick="return confirm('Are you sure you want to delete this category? Products belonging to this category may also be affected. Continue?');"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="5"
                                style="text-align:center;padding:30px;"
                            >
                                No categories found.
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