<?php

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";

$productId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($productId <= 0) {
    header("Location: shop.php");
    exit;
}

/* =========================
   ADD TO WISHLIST
========================= */
if (isset($_GET["wishlist"]) && $_GET["wishlist"] === "add") {

    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }

    $userId = getCurrentUserId();

    // Check product exists
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM products 
         WHERE id = ? AND status = 'active' 
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ($product) {

        // Check already in wishlist
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM wishlist 
             WHERE user_id = ? AND product_id = ? 
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        // Add only if not already present
        if (!$exists) {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO wishlist (user_id, product_id)
                 VALUES (?, ?)"
            );

            mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    // Redirect back to product page
    header("Location: product.php?id=" . $productId . "&wishlist=added");
    exit;
}


/* =========================
   GET PRODUCT
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT 
        products.*,
        categories.name AS category_name
     FROM products
     INNER JOIN categories 
        ON products.category_id = categories.id
     WHERE products.id = ?
       AND products.status = 'active'
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    header("Location: shop.php");
    exit;
}


/* =========================
   CHECK WISHLIST STATUS
========================= */

$inWishlist = false;

if (isLoggedIn()) {

    $userId = getCurrentUserId();

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id 
         FROM wishlist 
         WHERE user_id = ? AND product_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "ii", $userId, $productId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_fetch_assoc($result)) {
        $inWishlist = true;
    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($product["name"]) ?> - EasyMart
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #f8f9fa;
        }

        .product-image {
            width: 100%;
            height: 450px;
            object-fit: contain;
            background: white;
            border-radius: 15px;
            padding: 20px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .price {
            font-size: 30px;
            font-weight: bold;
        }

        .old-price {
            text-decoration: line-through;
            color: #888;
            font-size: 20px;
        }

        .category-badge {
            font-size: 14px;
        }

    </style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">

        <a class="navbar-brand fw-bold text-primary" href="index.php">

            <i class="bi bi-cart-check-fill"></i>
            EasyMart

        </a>


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">

                    <a class="nav-link" href="index.php">
                        Home
                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="shop.php">
                        Shop
                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="category.php">
                        Categories
                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="cart.php">

                        <i class="bi bi-cart"></i>
                        Cart

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="wishlist.php">

                        <i class="bi bi-heart"></i>
                        Wishlist

                    </a>

                </li>


                <?php if (isLoggedIn()): ?>

                    <li class="nav-item">

                        <a class="nav-link" href="profile.php">

                            <i class="bi bi-person-circle"></i>
                            Profile

                        </a>

                    </li>


                    <li class="nav-item">

                        <a class="nav-link text-danger" href="logout.php">

                            <i class="bi bi-box-arrow-right"></i>
                            Logout

                        </a>

                    </li>

                <?php else: ?>

                    <li class="nav-item">

                        <a class="nav-link" href="login.php">
                            Login
                        </a>

                    </li>


                    <li class="nav-item">

                        <a class="nav-link" href="register.php">
                            Register
                        </a>

                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>



<!-- =========================
     PRODUCT DETAILS
========================= -->

<div class="container py-5">

    <?php if (isset($_GET["wishlist"]) && $_GET["wishlist"] === "added"): ?>

        <div class="alert alert-success">

            <i class="bi bi-heart-fill"></i>

            Product added to your wishlist successfully.

        </div>

    <?php endif; ?>


    <div class="row g-4">


        <!-- PRODUCT IMAGE -->

        <div class="col-md-6">

            <div class="product-card">

                <?php if (!empty($product["image"])): ?>

                    <img
                        src="assets/images/<?= htmlspecialchars($product["image"]) ?>"
                        alt="<?= htmlspecialchars($product["name"]) ?>"
                        class="product-image"
                    >

                <?php else: ?>

                    <div
                        class="product-image d-flex align-items-center justify-content-center"
                    >

                        <div class="text-center text-muted">

                            <i class="bi bi-image fs-1"></i>

                            <p>No Image Available</p>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>



        <!-- PRODUCT INFORMATION -->

        <div class="col-md-6">

            <div class="product-card h-100">

                <span class="badge bg-primary category-badge mb-3">

                    <?= htmlspecialchars($product["category_name"]) ?>

                </span>


                <h1 class="fw-bold mb-3">

                    <?= htmlspecialchars($product["name"]) ?>

                </h1>


                <div class="mb-3">

                    <span class="price text-success">

                        ₹<?= number_format($product["price"], 2) ?>

                    </span>


                    <?php if (!empty($product["old_price"])): ?>

                        <span class="old-price ms-3">

                            ₹<?= number_format($product["old_price"], 2) ?>

                        </span>

                    <?php endif; ?>

                </div>


                <!-- STOCK -->

                <?php if ($product["stock"] > 0): ?>

                    <p class="text-success">

                        <i class="bi bi-check-circle-fill"></i>

                        In Stock:
                        <?= (int) $product["stock"] ?>

                    </p>

                <?php else: ?>

                    <p class="text-danger fw-bold">

                        <i class="bi bi-x-circle-fill"></i>

                        Out of Stock

                    </p>

                <?php endif; ?>


                <hr>


                <!-- DESCRIPTION -->

                <h5 class="fw-bold">
                    Product Description
                </h5>

                <p class="text-muted">

                    <?= nl2br(htmlspecialchars($product["description"] ?? "")) ?>

                </p>


                <hr>


                <!-- BUTTONS -->

                <div class="d-flex flex-wrap gap-2">


                    <?php if ($product["stock"] > 0): ?>

                        <a
                            href="add_to_cart.php?id=<?= $product["id"] ?>"
                            class="btn btn-primary btn-lg"
                        >

                            <i class="bi bi-cart-plus"></i>

                            Add to Cart

                        </a>

                    <?php else: ?>

                        <button
                            class="btn btn-secondary btn-lg"
                            disabled
                        >

                            <i class="bi bi-cart-x"></i>

                            Out of Stock

                        </button>

                    <?php endif; ?>


                    <!-- WISHLIST BUTTON -->

                    <?php if (isLoggedIn()): ?>

                        <?php if ($inWishlist): ?>

                            <a
                                href="wishlist.php"
                                class="btn btn-danger btn-lg"
                            >

                                <i class="bi bi-heart-fill"></i>

                                In Wishlist

                            </a>

                        <?php else: ?>

                            <a
                                href="product.php?id=<?= $product["id"] ?>&wishlist=add"
                                class="btn btn-outline-danger btn-lg"
                            >

                                <i class="bi bi-heart"></i>

                                Add to Wishlist

                            </a>

                        <?php endif; ?>

                    <?php else: ?>

                        <a
                            href="login.php"
                            class="btn btn-outline-danger btn-lg"
                        >

                            <i class="bi bi-heart"></i>

                            Add to Wishlist

                        </a>

                    <?php endif; ?>


                    <a
                        href="shop.php"
                        class="btn btn-outline-secondary btn-lg"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Back to Shop

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- =========================
     FOOTER
========================= -->

<footer class="bg-dark text-white text-center py-4 mt-5">

    <p class="mb-0">

        © <?= date("Y") ?> EasyMart.
        All Rights Reserved.

    </p>

</footer>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>