<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";

$error = "";

$productId = (int)($_GET["id"] ?? $_POST["product_id"] ?? 0);

if ($productId <= 0) {
    header("Location: products.php");
    exit;
}


/* =========================
   FETCH PRODUCT
   ========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM products
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    die("Product not found.");
}


/* =========================
   FETCH CATEGORIES
   ========================= */

$categories = mysqli_query(
    $conn,
    "SELECT id, name
     FROM categories
     ORDER BY name ASC"
);


/* =========================
   UPDATE PRODUCT
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $categoryId = (int)($_POST["category_id"] ?? 0);
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $oldPrice = trim($_POST["old_price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $status = $_POST["status"] ?? "active";

    /* Validation */

    if ($name === "") {

        $error = "Product name is required.";

    } elseif ($categoryId <= 0) {

        $error = "Please select a category.";

    } elseif (
        $price === "" ||
        !is_numeric($price) ||
        $price < 0
    ) {

        $error = "Please enter a valid price.";

    } elseif (
        $oldPrice !== "" &&
        (!is_numeric($oldPrice) || $oldPrice < 0)
    ) {

        $error = "Please enter a valid old price.";

    } elseif (
        $stock === "" ||
        !ctype_digit($stock)
    ) {

        $error = "Please enter a valid stock quantity.";

    } elseif (
        !in_array(
            $status,
            ["active", "inactive"],
            true
        )
    ) {

        $error = "Invalid product status.";

    } else {

        $imageName = $product["image"];


        /* =========================
           IMAGE UPLOAD
           ========================= */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES["image"]["error"] !==
                UPLOAD_ERR_OK
            ) {

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

                if (
                    !in_array(
                        $fileType,
                        $allowedTypes,
                        true
                    )
                ) {

                    $error =
                        "Only JPG, PNG, WEBP and GIF images are allowed.";

                } elseif (
                    $_FILES["image"]["size"] >
                    5 * 1024 * 1024
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

                    $newImageName =
                        "product_" .
                        time() .
                        "_" .
                        uniqid() .
                        "." .
                        $extension;

                    $uploadDir =
                        __DIR__ .
                        "/../assets/images/";

                    if (!is_dir($uploadDir)) {

                        mkdir(
                            $uploadDir,
                            0777,
                            true
                        );
                    }

                    $uploadPath =
                        $uploadDir .
                        $newImageName;

                    if (
                        move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $uploadPath
                        )
                    ) {

                        $imageName = $newImageName;

                    } else {

                        $error =
                            "Unable to save new image.";
                    }
                }
            }
        }


        /* =========================
           UPDATE DATABASE
           ========================= */

        if ($error === "") {

            $priceValue = (float)$price;

            $oldPriceValue =
                ($oldPrice === "")
                ? null
                : (float)$oldPrice;

            $stockValue = (int)$stock;

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE products
                 SET
                    category_id = ?,
                    name = ?,
                    description = ?,
                    price = ?,
                    old_price = ?,
                    stock = ?,
                    image = ?,
                    status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "issddissi",
                $categoryId,
                $name,
                $description,
                $priceValue,
                $oldPriceValue,
                $stockValue,
                $imageName,
                $status,
                $productId
            );

            if (mysqli_stmt_execute($stmt)) {

                /* Delete old image if replaced */

                if (
                    $imageName !== $product["image"] &&
                    !empty($product["image"])
                ) {

                    $oldImagePath =
                        __DIR__ .
                        "/../assets/images/" .
                        $product["image"];

                    if (
                        file_exists($oldImagePath)
                    ) {
                        unlink($oldImagePath);
                    }
                }

                header(
                    "Location: products.php?updated=1"
                );

                exit;

            } else {

                /* Delete newly uploaded image
                   if database update failed */

                if (
                    $imageName !== $product["image"]
                ) {

                    $newImagePath =
                        __DIR__ .
                        "/../assets/images/" .
                        $imageName;

                    if (
                        file_exists($newImagePath)
                    ) {
                        unlink($newImagePath);
                    }
                }

                $error =
                    "Product could not be updated: " .
                    mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        }
    }


    /* Keep entered values in form */

    $product["name"] = $name;
    $product["category_id"] = $categoryId;
    $product["description"] = $description;
    $product["price"] = $price;
    $product["old_price"] = $oldPrice;
    $product["stock"] = $stock;
    $product["status"] = $status;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Product - EasyMart Admin</title>

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

        .card {
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

        .current-image {
            margin-top: 10px;
        }

        .current-image img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
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

        .btn-update {
            background: #0d6efd;
            color: white;
        }

        .btn-update:hover {
            background: #0b5ed7;
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

        <a href="categories.php">
            Categories
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<!-- Main -->

<div class="container">

    <div class="card">

        <h1>Edit Product</h1>


        <?php if ($error !== ""): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <input
                type="hidden"
                name="product_id"
                value="<?= $productId ?>"
            >


            <!-- Product Name -->

            <div class="form-group">

                <label>
                    Product Name *
                </label>

                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars(
                        $product["name"]
                    ) ?>"
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
                                    (int)$product["category_id"] ===
                                    (int)$category["id"]
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
                ><?= htmlspecialchars(
                    $product["description"] ?? ""
                ) ?></textarea>

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
                        value="<?= htmlspecialchars(
                            $product["price"]
                        ) ?>"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Old Price
                    </label>

                    <input
                        type="number"
                        name="old_price"
                        value="<?= htmlspecialchars(
                            $product["old_price"] ?? ""
                        ) ?>"
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
                        value="<?= htmlspecialchars(
                            $product["stock"]
                        ) ?>"
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
                            <?= $product["status"] === "active"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $product["status"] === "inactive"
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
                    Leave empty to keep the current image.
                    Maximum size: 5 MB.
                </div>


                <?php if (
                    !empty($product["image"])
                ): ?>

                    <div class="current-image">

                        <p>
                            <strong>
                                Current Image:
                            </strong>
                        </p>

                        <img
                            src="../assets/images/<?= htmlspecialchars(
                                $product["image"]
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $product["name"]
                            ) ?>"
                        >

                    </div>

                <?php endif; ?>

            </div>


            <!-- Buttons -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn btn-update"
                >
                    Update Product
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