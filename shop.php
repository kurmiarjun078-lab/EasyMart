<?php

$conn = require_once "includes/db.php";
require_once "includes/auth.php";


// Fetch all active products
$sql = "SELECT products.*, categories.name AS category_name
        FROM products
        INNER JOIN categories
        ON products.category_id = categories.id
        WHERE products.status = 'active'
        ORDER BY products.id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shop - EasyMart</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

</head>

<body class="bg-light">


<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">

        <a class="navbar-brand text-primary fw-bold" href="index.php">
            <i class="bi bi-cart-check-fill"></i>
            EasyMart
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house-door"></i>
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="shop.php">
                        <i class="bi bi-shop"></i>
                        Shop
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
                        <a class="nav-link d-flex align-items-center gap-1"
                           href="profile.php">

                            <i class="bi bi-person-circle fs-5 text-primary"></i>

                            <?php echo htmlspecialchars(getCurrentUserName()); ?>

                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-danger"
                           href="logout.php">

                            <i class="bi bi-box-arrow-right"></i>
                            Logout

                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-person"></i>
                            Login
                        </a>
                    </li>

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


<!-- ================= SHOP HEADER ================= -->

<div class="container py-5">

    <div class="text-center mb-5">

        <h1 class="fw-bold">
            <i class="bi bi-shop text-primary"></i>
            Our Shop
        </h1>

        <p class="text-muted">
            Explore our latest products
        </p>

    </div>


    <!-- ================= PRODUCTS ================= -->

    <div class="row g-4">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>

                <div class="col-md-6 col-lg-4 col-xl-3">

                    <div class="card h-100 border-0 shadow-sm">

                        <!-- Product Image -->

                        <?php if (!empty($product["image"])): ?>

                            <img
                                src="assets/images/<?php echo htmlspecialchars($product["image"]); ?>"
                                class="card-img-top"
                                style="height:220px; object-fit:cover;"
                                alt="<?php echo htmlspecialchars($product["name"]); ?>"
                            >

                        <?php else: ?>

                            <div
                                class="d-flex align-items-center justify-content-center bg-light"
                                style="height:220px;"
                            >

                                <i class="bi bi-image fs-1 text-secondary"></i>

                            </div>

                        <?php endif; ?>


                        <div class="card-body d-flex flex-column">

                            <!-- Category -->

                            <small class="text-primary fw-semibold">
                                <?php echo htmlspecialchars($product["category_name"]); ?>
                            </small>


                            <!-- Product Name -->

                            <h5 class="card-title mt-2">
                                <?php echo htmlspecialchars($product["name"]); ?>
                            </h5>


                            <!-- Description -->

                            <p class="card-text text-muted small">

                                <?php
                                $description = $product["description"];

                                echo htmlspecialchars(
                                    strlen($description) > 80
                                    ? substr($description, 0, 80) . "..."
                                    : $description
                                );
                                ?>

                            </p>


                            <!-- Price -->

                            <div class="mb-3">

                                <span class="fw-bold fs-5 text-success">

                                    ₹<?php echo number_format($product["price"], 2); ?>

                                </span>


                                <?php if (!empty($product["old_price"])): ?>

                                    <del class="text-muted ms-2">

                                        ₹<?php
                                        echo number_format(
                                            $product["old_price"],
                                            2
                                        );
                                        ?>

                                    </del>

                                <?php endif; ?>

                            </div>


                            <!-- Buttons -->

                            <div class="mt-auto d-flex gap-2">

                                <a
                                    href="product.php?id=<?php echo $product["id"]; ?>"
                                    class="btn btn-outline-primary w-50"
                                >
                                    <i class="bi bi-eye"></i>
                                    View
                                </a>


                                <a
                                    href="add_to_cart.php?id=<?php echo $product["id"]; ?>"
                                    class="btn btn-primary w-50"
                                >
                                    <i class="bi bi-cart-plus"></i>
                                    Cart
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="alert alert-info text-center">

                    <i class="bi bi-info-circle"></i>

                    No products available right now.

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>