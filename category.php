<?php

require_once __DIR__ . "/includes/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Get selected category
$categoryId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;


// Fetch categories
$categoryQuery = "
    SELECT id, name, description, image
    FROM categories
    ORDER BY name ASC
";

$categoryResult = mysqli_query($conn, $categoryQuery);


// Selected category information
$selectedCategory = null;

if ($categoryId > 0) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, description, image
         FROM categories
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $selectedCategory = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}


// Fetch products of selected category
$products = [];

if ($categoryId > 0 && $selectedCategory) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            p.id,
            p.name,
            p.description,
            p.price,
            p.old_price,
            p.stock,
            p.image,
            p.status,
            c.name AS category_name
         FROM products p
         INNER JOIN categories c
            ON p.category_id = c.id
         WHERE p.category_id = ?
         AND p.status = 'active'
         ORDER BY p.id DESC"
    );

    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    mysqli_stmt_close($stmt);
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

    <title>
        <?= $selectedCategory
            ? htmlspecialchars($selectedCategory["name"]) . " - EasyMart"
            : "Categories - EasyMart"; ?>
    </title>


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


    <style>

        body {
            background: #f8f9fa;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 24px;
        }

        .category-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: 0.3s;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .category-image {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }

        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
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
            width: 100%;
        }

        .price {
            font-size: 20px;
            font-weight: bold;
        }

        .old-price {
            text-decoration: line-through;
            color: #888;
            margin-left: 8px;
        }

        .category-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
        }

    </style>

</head>


<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">


        <!-- Brand -->

        <a
            class="navbar-brand text-dark"
            href="index.php"
        >

            <i class="bi bi-cart-check-fill"></i>

            EasyMart

        </a>


        <!-- Mobile button -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- Menu -->

        <div
            class="collapse navbar-collapse"
            id="navbarNav"
        >

            <ul class="navbar-nav ms-auto align-items-lg-center">


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php"
                    >
                        Home
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="shop.php"
                    >
                        Shop
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link active"
                        href="category.php"
                    >
                        Categories
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="contact.php"
                    >
                        Contact
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="wishlist.php"
                    >

                        <i class="bi bi-heart"></i>

                        Wishlist

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="cart.php"
                    >

                        <i class="bi bi-cart"></i>

                        Cart

                    </a>

                </li>


                <?php if (
                    isset($_SESSION["logged_in"]) &&
                    $_SESSION["logged_in"] === true
                ): ?>


                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                        >

                            <i class="bi bi-person-circle"></i>

                            <?= htmlspecialchars(
                                $_SESSION["user_name"] ?? "Account"
                            ); ?>

                        </a>


                        <ul class="dropdown-menu dropdown-menu-end">


                            <li>

                                <a
                                    class="dropdown-item"
                                    href="profile.php"
                                >

                                    <i class="bi bi-person"></i>

                                    Profile

                                </a>

                            </li>


                            <li>

                                <a
                                    class="dropdown-item"
                                    href="orders.php"
                                >

                                    <i class="bi bi-box-seam"></i>

                                    My Orders

                                </a>

                            </li>


                            <li>

                                <a
                                    class="dropdown-item"
                                    href="wishlist.php"
                                >

                                    <i class="bi bi-heart"></i>

                                    Wishlist

                                </a>

                            </li>


                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            <li>

                                <a
                                    class="dropdown-item text-danger"
                                    href="logout.php"
                                >

                                    <i class="bi bi-box-arrow-right"></i>

                                    Logout

                                </a>

                            </li>


                        </ul>

                    </li>


                <?php else: ?>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="login.php"
                        >

                            <i class="bi bi-person"></i>

                            Login

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            href="register.php"
                            class="btn btn-dark ms-lg-2"
                        >

                            Register

                        </a>

                    </li>


                <?php endif; ?>


            </ul>

        </div>

    </div>

</nav>



<!-- ================= MAIN ================= -->

<div class="container py-5">


<?php if ($categoryId === 0): ?>


    <!-- ALL CATEGORIES -->

    <div class="text-center mb-5">

        <h1 class="fw-bold">

            Shop by Category

        </h1>

        <p class="text-muted">

            Explore our products by category.

        </p>

    </div>


    <div class="row g-4">


        <?php if (mysqli_num_rows($categoryResult) > 0): ?>


            <?php while ($category = mysqli_fetch_assoc($categoryResult)): ?>


                <div class="col-md-6 col-lg-4">


                    <div class="card category-card shadow-sm h-100">


                        <?php if (!empty($category["image"])): ?>

                            <img
                                src="assets/images/<?= htmlspecialchars(
                                    $category["image"]
                                ); ?>"
                                class="category-image"
                                alt="<?= htmlspecialchars(
                                    $category["name"]
                                ); ?>"
                            >

                        <?php else: ?>

                            <div
                                class="category-image bg-secondary d-flex align-items-center justify-content-center"
                            >

                                <i
                                    class="bi bi-grid fs-1 text-white"
                                ></i>

                            </div>

                        <?php endif; ?>


                        <div class="card-body text-center">


                            <h4 class="fw-bold">

                                <?= htmlspecialchars(
                                    $category["name"]
                                ); ?>

                            </h4>


                            <?php if (!empty($category["description"])): ?>

                                <p class="text-muted">

                                    <?= htmlspecialchars(
                                        $category["description"]
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <a
                                href="category.php?id=<?= (int) $category["id"]; ?>"
                                class="btn btn-dark"
                            >

                                View Products

                                <i class="bi bi-arrow-right"></i>

                            </a>


                        </div>

                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="col-12">

                <div class="text-center py-5">

                    <i
                        class="bi bi-grid fs-1 text-muted"
                    ></i>

                    <h4 class="mt-3">

                        No Categories Found

                    </h4>

                    <p class="text-muted">

                        Categories will appear here when added by admin.

                    </p>

                </div>

            </div>


        <?php endif; ?>


    </div>



<?php elseif ($selectedCategory): ?>


    <!-- SELECTED CATEGORY -->

    <div class="category-header shadow-sm mb-5">


        <div class="row align-items-center">


            <div class="col-md-3">


                <?php if (!empty($selectedCategory["image"])): ?>

                    <img
                        src="assets/images/<?= htmlspecialchars(
                            $selectedCategory["image"]
                        ); ?>"
                        class="img-fluid rounded"
                        alt="<?= htmlspecialchars(
                            $selectedCategory["name"]
                        ); ?>"
                    >

                <?php else: ?>

                    <div
                        class="bg-secondary rounded p-5 text-center"
                    >

                        <i
                            class="bi bi-grid fs-1 text-white"
                        ></i>

                    </div>

                <?php endif; ?>


            </div>


            <div class="col-md-9">


                <h1 class="fw-bold">

                    <?= htmlspecialchars(
                        $selectedCategory["name"]
                    ); ?>

                </h1>


                <?php if (!empty($selectedCategory["description"])): ?>

                    <p class="text-muted">

                        <?= htmlspecialchars(
                            $selectedCategory["description"]
                        ); ?>

                    </p>

                <?php endif; ?>


                <a
                    href="category.php"
                    class="btn btn-outline-dark"
                >

                    <i class="bi bi-arrow-left"></i>

                    All Categories

                </a>


            </div>


        </div>


    </div>



    <!-- PRODUCTS -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="fw-bold mb-0">

            Products

        </h2>


        <span class="text-muted">

            <?= count($products); ?> product(s)

        </span>

    </div>


    <div class="row g-4">


        <?php if (count($products) > 0): ?>


            <?php foreach ($products as $product): ?>


                <div class="col-sm-6 col-lg-3">


                    <div class="card product-card shadow-sm">


                        <?php if (!empty($product["image"])): ?>

                            <img
                                src="assets/images/<?= htmlspecialchars(
                                    $product["image"]
                                ); ?>"
                                class="product-image"
                                alt="<?= htmlspecialchars(
                                    $product["name"]
                                ); ?>"
                            >

                        <?php else: ?>

                            <div
                                class="product-image bg-light d-flex align-items-center justify-content-center"
                            >

                                <i
                                    class="bi bi-image fs-1 text-muted"
                                ></i>

                            </div>

                        <?php endif; ?>


                        <div class="card-body d-flex flex-column">


                            <small class="text-muted">

                                <?= htmlspecialchars(
                                    $product["category_name"]
                                ); ?>

                            </small>


                            <h5 class="fw-bold mt-1">

                                <?= htmlspecialchars(
                                    $product["name"]
                                ); ?>

                            </h5>


                            <p class="text-muted small">

                                <?= htmlspecialchars(
                                    mb_strimwidth(
                                        $product["description"] ?? "",
                                        0,
                                        80,
                                        "..."
                                    )
                                ); ?>

                            </p>


                            <div class="mb-3">


                                <span class="price">

                                    ₹<?= number_format(
                                        (float) $product["price"],
                                        2
                                    ); ?>

                                </span>


                                <?php if (
                                    !empty($product["old_price"]) &&
                                    $product["old_price"] > $product["price"]
                                ): ?>

                                    <span class="old-price">

                                        ₹<?= number_format(
                                            (float) $product["old_price"],
                                            2
                                        ); ?>

                                    </span>

                                <?php endif; ?>


                            </div>


                            <?php if ((int) $product["stock"] > 0): ?>

                                <span
                                    class="badge bg-success mb-3 align-self-start"
                                >

                                    In Stock

                                </span>

                            <?php else: ?>

                                <span
                                    class="badge bg-danger mb-3 align-self-start"
                                >

                                    Out of Stock

                                </span>

                            <?php endif; ?>


                            <div class="mt-auto">


                                <a
                                    href="product.php?id=<?= (int) $product["id"]; ?>"
                                    class="btn btn-outline-dark w-100"
                                >

                                    <i class="bi bi-eye"></i>

                                    View Product

                                </a>


                            </div>


                        </div>

                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="col-12">

                <div class="text-center py-5">

                    <i
                        class="bi bi-box-seam fs-1 text-muted"
                    ></i>


                    <h4 class="mt-3">

                        No Products Found

                    </h4>


                    <p class="text-muted">

                        There are no active products in this category.

                    </p>


                    <a
                        href="category.php"
                        class="btn btn-dark"
                    >

                        Browse Categories

                    </a>

                </div>

            </div>


        <?php endif; ?>


    </div>


<?php else: ?>


    <!-- INVALID CATEGORY -->

    <div class="text-center py-5">

        <i
            class="bi bi-exclamation-circle fs-1 text-danger"
        ></i>


        <h3 class="mt-3">

            Category Not Found

        </h3>


        <p class="text-muted">

            The selected category does not exist.

        </p>


        <a
            href="category.php"
            class="btn btn-dark"
        >

            View All Categories

        </a>

    </div>


<?php endif; ?>


</div>



<!-- ================= FOOTER ================= -->

<footer class="bg-dark text-white py-4">

    <div class="container text-center">

        <p class="mb-0">

            © <?= date("Y"); ?> EasyMart. All Rights Reserved.

        </p>

    </div>

</footer>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>