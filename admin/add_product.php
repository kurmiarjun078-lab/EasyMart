<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";

$error = "";
$success = "";

$name = "";
$category_id = "";
$description = "";
$price = "";
$old_price = "";
$stock = "";
$status = "active";


/* =========================
   FETCH CATEGORIES
   ========================= */

$categories = mysqli_query(
    $conn,
    "SELECT id, name FROM categories ORDER BY name ASC"
);


/* =========================
   ADD PRODUCT
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0);
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $old_price = trim($_POST["old_price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $status = $_POST["status"] ?? "active";


    /* Validation */

    if ($name === "") {

        $error = "Product name is required.";

    } elseif ($category_id <= 0) {

        $error = "Please select a category.";

    } elseif ($price === "" || !is_numeric($price) || $price < 0) {

        $error = "Please enter a valid price.";

    } elseif (
        $old_price !== "" &&
        (!is_numeric($old_price) || $old_price < 0)
    ) {

        $error = "Please enter a valid old price.";

    } elseif ($stock === "" || !ctype_digit($stock)) {

        $error = "Please enter a valid stock quantity.";

    } elseif (!in_array($status, ["active", "inactive"], true)) {

        $error = "Invalid product status.";

    } else {

        $imageName = "";


        /* =========================
           IMAGE UPLOAD
           ========================= */

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

                    $error = "Only JPG, PNG, WEBP and GIF images are allowed.";

                } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {

                    $error = "Image size must be less than 5 MB.";

                } else {

                    $extension = strtolower(
                        pathinfo(
                            $_FILES["image"]["name"],
                            PATHINFO_EXTENSION
                        )
                    );

                    $imageName =
                        "product_" .
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

                        $error = "Unable to save product image.";

                    }

                }

            }

        }


        /* =========================
           INSERT PRODUCT
           ========================= */

        if ($error === "") {

            $oldPriceValue =
                ($old_price === "")
                ? null
                : (float)$old_price;

            $priceValue = (float)$price;
            $stockValue = (int)$stock;

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO products
                (
                    category_id,
                    name,
                    description,
                    price,
                    old_price,
                    stock,
                    image,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "issddiss",
                $category_id,
                $name,
                $description,
                $priceValue,
                $oldPriceValue,
                $stockValue,
                $imageName,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: products.php");
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

                $error = "Product could not be added: " .
                         mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Product - EasyMart Admin</title>

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
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;

            box-shadow:
                0 3px 12px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px;

            border: 1px solid #ccc;
            border-radius: 6px;

            font-size: 15px;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .image-note {
            color: #777;
            font-size: 13px;
            margin-top: 6px;
        }

        .error {
            background: #f8d7da;
            color: #842029;

            padding: 12px;
            border-radius: 6px;

            margin-bottom: 20px;
        }

        .buttons {
            margin-top: 25px;
        }

        .btn {
            display: inline-block;

            padding: 12px 20px;

            border: none;
            border-radius: 6px;

            text-decoration: none;

            cursor: pointer;

            font-size: 15px;
        }

        .btn-save {
            background: #198754;
            color: white;
        }

        .btn-save:hover {
            background: #157347;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;

            margin-left: 8px;
        }

        @media (max-width: 700px) {

            .row {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                gap: 10px;
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

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<!-- Main -->

<div class="container">

    <div class="form-card">

        <h1>Add New Product</h1>


        <?php if ($error !== ""): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              enctype="multipart/form-data">


            <!-- Product Name -->

            <div class="form-group">

                <label>
                    Product Name *
                </label>

                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($name) ?>"
                    placeholder="Enter product name"
                    required
                >

            </div>


            <!-- Category -->

            <div class="form-group">

                <label>
                    Category *
                </label>

                <select
                    name="category_id"
                    required
                >

                    <option value="">
                        -- Select Category --
                    </option>

                    <?php if ($categories): ?>

                        <?php while (
                            $category =
                            mysqli_fetch_assoc($categories)
                        ): ?>

                            <option
                                value="<?= (int)$category["id"] ?>"
                                <?= (
                                    $category_id ==
                                    $category["id"]
                                )
                                ? "selected"
                                : ""
                                ?>
                            >
                                <?= htmlspecialchars(
                                    $category["name"]
                                ) ?>
                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <!-- Description -->

            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    placeholder="Enter product description"
                ><?= htmlspecialchars($description) ?></textarea>

            </div>


            <!-- Price -->

            <div class="row">

                <div class="form-group">

                    <label>
                        Price *
                    </label>

                    <input
                        type="number"
                        name="price"
                        value="<?= htmlspecialchars($price) ?>"
                        placeholder="Enter price"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>


                <!-- Old Price -->

                <div class="form-group">

                    <label>
                        Old Price
                    </label>

                    <input
                        type="number"
                        name="old_price"
                        value="<?= htmlspecialchars($old_price) ?>"
                        placeholder="Enter old price"
                        min="0"
                        step="0.01"
                    >

                </div>

            </div>


            <!-- Stock + Status -->

            <div class="row">

                <div class="form-group">

                    <label>
                        Stock *
                    </label>

                    <input
                        type="number"
                        name="stock"
                        value="<?= htmlspecialchars($stock) ?>"
                        placeholder="Enter stock"
                        min="0"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Status *
                    </label>

                    <select name="status">

                        <option
                            value="active"
                            <?= $status === "active"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $status === "inactive"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>

            </div>


            <!-- Image -->

            <div class="form-group">

                <label>
                    Product Image
                </label>

                <input
                    type="file"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp,.gif"
                >

                <div class="image-note">
                    Allowed: JPG, PNG, WEBP, GIF.
                    Maximum size: 5 MB.
                </div>

            </div>


            <!-- Buttons -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn btn-save"
                >
                    Add Product
                </button>

                <a
                    href="products.php"
                    class="btn btn-cancel"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>

</body>

</html>