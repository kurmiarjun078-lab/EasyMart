<?php
$conn = require __DIR__ . "/includes/db.php";
require_once "includes/auth.php";

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");

$products = mysqli_query(
    $conn,
    "SELECT products.*, categories.name AS category_name
     FROM products
     LEFT JOIN categories ON products.category_id = categories.id
     WHERE products.status = 'active'
     ORDER BY products.id DESC
     LIMIT 8"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EasyMart - Online Shopping</title>

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

        .navbar-brand {
            font-size: 28px;
            font-weight: bold;
        }

        .hero {
            min-height: 400px;
            display: flex;
            align-items: center;
            background: linear-gradient(
                135deg,
                #0d6efd,
                #6610f2
            );
            color: white;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: bold;
        }

        .category-card {
            transition: 0.3s;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .product-card {
            transition: 0.3s;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .product-image {
            height: 220px;
            object-fit: cover;
            background: #eee;
        }

        .price {
            color: #198754;
            font-size: 20px;
            font-weight: bold;
        }

        .old-price {
            color: #999;
            text-decoration: line-through;
            margin-left: 8px;
        }

        footer {
            background: #212529;
            color: white;
        }

    </style>
</head>

<body>
<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand text-primary fw-bold" href="index.php">
            <i class="bi bi-cart-check-fill"></i>
            EasyMart
        </a>

        <!-- Mobile Toggle -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu"
            aria-controls="navbarMenu"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Menu -->
        <div class="collapse navbar-collapse" id="navbarMenu">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <!-- Home -->
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-house-door"></i>
                        Home
                    </a>
                </li>

                <!-- Shop -->
                <li class="nav-item">
                    <a class="nav-link" href="shop.php">
                        <i class="bi bi-shop"></i>
                        Shop
                    </a>
                </li>

                <!-- Cart -->
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart"></i>
                        Cart
                    </a>
                </li>

                <!-- Wishlist -->
                <li class="nav-item">
                    <a class="nav-link" href="wishlist.php">
                        <i class="bi bi-heart"></i>
                        Wishlist
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>

                    <!-- Profile -->
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1"
                           href="profile.php">

                            <i class="bi bi-person-circle fs-5 text-primary"></i>

                            <span>
                                <?php echo htmlspecialchars(getCurrentUserName()); ?>
                            </span>

                        </a>
                
                    </li>

                    <!-- Logout -->
                    <li class="nav-item">
                        <a class="nav-link text-danger"
                           href="logout.php">

                            <i class="bi bi-box-arrow-right"></i>
                            Logout

                        </a>
                    </li>

                <?php else: ?>

                    <!-- Login -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">

                            <i class="bi bi-person"></i>
                            Login

                        </a>
                    </li>

                    <!-- Register -->
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-lg-2"
                           href="register.php">

                            <i class="bi bi-person-plus"></i>
                            Register

                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>


<!-- ================= HERO ================= -->

<section class="hero">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-7">

                <h1>
                    Welcome to EasyMart
                </h1>

                <p class="lead mt-3">
                    Shop the latest products at amazing prices.
                </p>

                <a href="shop.php" class="btn btn-light btn-lg mt-3">
                    <i class="bi bi-bag"></i>
                    Shop Now
                </a>

            </div>

            <div class="col-lg-5 text-center">

                <i
                    class="bi bi-cart4"
                    style="font-size: 180px;"
                ></i>

            </div>

        </div>

    </div>

</section>


<!-- ================= CATEGORIES ================= -->

<section class="container py-5">

    <div class="text-center mb-4">

        <h2 class="fw-bold">
            Shop By Category
        </h2>

        <p class="text-muted">
            Explore our popular categories
        </p>

    </div>


    <div class="row g-4">

        <?php while ($category = mysqli_fetch_assoc($categories)) { ?>

            <div class="col-6 col-md-4 col-lg-2">

                <div class="card category-card text-center h-100">

                    <div class="card-body">

                        <i
                            class="bi bi-grid-3x3-gap-fill text-primary"
                            style="font-size: 45px;"
                        ></i>

                        <h6 class="mt-3">
                            <?= htmlspecialchars($category['name']) ?>
                        </h6>

                        <a
                            href="shop.php?category=<?= $category['id'] ?>"
                            class="btn btn-outline-primary btn-sm"
                        >
                            View
                        </a>

                    </div>

                </div>

            </div>

        <?php } ?>

    </div>

</section>


<!-- ================= PRODUCTS ================= -->

<section class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">
                Featured Products
            </h2>

            <p class="text-muted mb-0">
                Check out our latest products
            </p>

        </div>

        <a href="shop.php" class="btn btn-primary">
            View All
        </a>

    </div>


    <div class="row g-4">

        <?php while ($product = mysqli_fetch_assoc($products)) { ?>

            <div class="col-md-6 col-lg-3">

                <div class="card product-card">

                    <?php if (!empty($product['image'])) { ?>

                        <img
                            src="assets/images/<?= htmlspecialchars($product['image']) ?>"
                            class="card-img-top product-image"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                        >

                    <?php } else { ?>

                        <div
                            class="product-image d-flex align-items-center justify-content-center"
                        >
                            <i
                                class="bi bi-image text-secondary"
                                style="font-size: 70px;"
                            ></i>
                        </div>

                    <?php } ?>


                    <div class="card-body">

                        <small class="text-muted">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </small>

                        <h5 class="card-title mt-2">
                            <?= htmlspecialchars($product['name']) ?>
                        </h5>

                        <p class="text-muted">
                            <?= htmlspecialchars(
                                substr($product['description'], 0, 70)
                            ) ?>
                        </p>

                        <div>

                            <span class="price">
                                ₹<?= number_format($product['price'], 2) ?>
                            </span>

                            <?php if ($product['old_price']) { ?>

                                <span class="old-price">
                                    ₹<?= number_format($product['old_price'], 2) ?>
                                </span>

                            <?php } ?>

                        </div>

                        <a
                            href="product.php?id=<?= $product['id'] ?>"
                            class="btn btn-primary w-100 mt-3"
                        >
                            View Product
                        </a>

                    </div>

                </div>

            </div>

        <?php } ?>

    </div>

</section>


<!-- ================= FOOTER ================= -->

<footer class="py-5 mt-5">

    <div class="container">

        <div class="row">

            <div class="col-md-4">

                <h4>
                    <i class="bi bi-cart-check-fill"></i>
                    EasyMart
                </h4>

                <p class="text-light">
                    Your trusted online shopping destination.
                </p>

            </div>


            <div class="col-md-4">

                <h5>
                    Quick Links
                </h5>

                <ul class="list-unstyled">

                    <li>
                        <a
                            href="index.php"
                            class="text-white text-decoration-none"
                        >
                            Home
                        </a>
                    </li>

                    <li>
                        <a
                            href="shop.php"
                            class="text-white text-decoration-none"
                        >
                            Shop
                        </a>
                    </li>

                    <li>
                        <a
                            href="contact.php"
                            class="text-white text-decoration-none"
                        >
                            Contact Us
                        </a>
                    </li>

                </ul>

            </div>


            <div class="col-md-4">

                <h5>
                    Contact
                </h5>

                <p>
                    <i class="bi bi-envelope"></i>
                    support@easymart.com
                </p>

                <p>
                    <i class="bi bi-telephone"></i>
                    +91 98765 43210
                </p>

            </div>

        </div>


        <hr>

        <div class="text-center">

            <p class="mb-0">
                © <?= date('Y') ?> EasyMart. All Rights Reserved.
            </p>

        </div>

    </div>

</footer>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>