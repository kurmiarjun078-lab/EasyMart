<?php

require_once "includes/db.php";
require_once "includes/auth.php";


// ======================================================
// SEARCH KEYWORD
// ======================================================

$search = trim($_GET["q"] ?? "");


// ======================================================
// FETCH PRODUCTS
// ======================================================

if ($search !== "") {

    $searchTerm = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            products.*,
            categories.name AS category_name
         FROM products
         INNER JOIN categories
            ON products.category_id = categories.id
         WHERE products.status = 'active'
         AND (
            products.name LIKE ?
            OR products.description LIKE ?
            OR categories.name LIKE ?
         )
         ORDER BY products.id DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    // Show all active products

    $sql = "SELECT
                products.*,
                categories.name AS category_name
            FROM products
            INNER JOIN categories
                ON products.category_id = categories.id
            WHERE products.status = 'active'
            ORDER BY products.id DESC";

    $result = mysqli_query($conn, $sql);
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

    <title>Shop - EasyMart</title>


    <!-- ==================================================
         BOOTSTRAP
    =================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ==================================================
         BOOTSTRAP ICONS
    =================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- ==================================================
         CUSTOM CSS
    =================================================== -->

    <style>

        body {
            background-color: #f8f9fa;
        }


        /* Search Box */

        .search-box {
            width: 280px;
        }


        /* Product Card */

        .product-card {
            border: none;
            transition: 0.3s ease;
        }


        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
        }


        /* Product Image */

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }


        /* No Image */

        .no-image {
            height: 220px;
            background-color: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        /* Search Result Heading */

        .search-result-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }


        /* Mobile Search */

        @media (max-width: 991px) {

            .search-box {
                width: 100%;
                margin-top: 10px;
                margin-bottom: 10px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
====================================================== -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">


        <!-- =================================================
             LOGO
        ================================================== -->

        <a
            class="navbar-brand text-primary fw-bold"
            href="index.php"
        >

            <i class="bi bi-cart-check-fill"></i>

            EasyMart

        </a>


        <!-- =================================================
             MOBILE MENU BUTTON
        ================================================== -->

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


        <!-- =================================================
             NAVBAR MENU
        ================================================== -->

        <div
            class="collapse navbar-collapse"
            id="navbarMenu"
        >


            <!-- =================================================
                 MAIN NAVIGATION
            ================================================== -->

            <ul class="navbar-nav ms-auto align-items-lg-center">


                <!-- HOME -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php"
                    >

                        <i class="bi bi-house-door"></i>

                        Home

                    </a>

                </li>


                <!-- SHOP -->

                <li class="nav-item">

                    <a
                        class="nav-link active"
                        href="shop.php"
                    >

                        <i class="bi bi-shop"></i>

                        Shop

                    </a>

                </li>


                <!-- CATEGORIES -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="category.php"
                    >

                        <i class="bi bi-grid"></i>

                        Categories

                    </a>

                </li>


                <!-- CART -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="cart.php"
                    >

                        <i class="bi bi-cart"></i>

                        Cart

                    </a>

                </li>


                <!-- WISHLIST -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="wishlist.php"
                    >

                        <i class="bi bi-heart"></i>

                        Wishlist

                    </a>

                </li>


            </ul>


            <!-- =================================================
                 SEARCH FORM
                 
                 IMPORTANT:
                 Search same shop.php page par hoga.
            ================================================== -->

            <form
                action="shop.php"
                method="GET"
                class="d-flex search-box ms-lg-3"
            >


                <input
                    type="search"
                    name="q"
                    class="form-control"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search products..."
                    autocomplete="off"
                >


                <button
                    type="submit"
                    class="btn btn-primary ms-2"
                    title="Search"
                >

                    <i class="bi bi-search"></i>

                </button>


            </form>


            <!-- =================================================
                 USER SECTION
            ================================================== -->

            <ul
                class="navbar-nav align-items-lg-center ms-lg-2"
            >


                <?php if (isLoggedIn()): ?>


                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >

                            <i class="bi bi-person-circle fs-5 text-primary"></i>

                            <?= htmlspecialchars(getCurrentUserName()) ?>

                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="bi bi-person"></i>
                                    Profile
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="orders.php">
                                    <i class="bi bi-box-seam"></i>
                                    My Orders
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="wishlist.php">
                                    <i class="bi bi-heart"></i>
                                    Wishlist
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Logout
                                </a>
                            </li>

                        </ul>

                    </li>


                <?php else: ?>


                    <!-- LOGIN -->

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="login.php"
                        >

                            <i class="bi bi-person"></i>

                            Login

                        </a>

                    </li>


                    <!-- REGISTER -->

                    <li class="nav-item">

                        <a
                            class="btn btn-primary btn-sm ms-lg-2"
                            href="register.php"
                        >

                            <i class="bi bi-person-plus"></i>

                            Register

                        </a>

                    </li>


                <?php endif; ?>


            </ul>


        </div>

    </div>

</nav>


<!-- =====================================================
     MAIN SHOP CONTAINER
====================================================== -->

<div class="container py-5">


    <!-- =================================================
         SHOP HEADER
    ================================================== -->

    <?php if ($search !== ""): ?>


        <!-- SEARCH RESULTS HEADER -->

        <div class="search-result-box">


            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">


                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-search text-primary"></i>

                        Search Results

                    </h2>


                    <p class="text-muted mb-0">

                        Showing results for:

                        <strong>
                            <?= htmlspecialchars($search) ?>
                        </strong>

                    </p>

                </div>


                <!-- SHOW ALL -->

                <a
                    href="shop.php"
                    class="btn btn-outline-primary"
                >

                    <i class="bi bi-grid"></i>

                    Show All Products

                </a>


            </div>


        </div>


    <?php else: ?>


        <!-- NORMAL SHOP HEADER -->

        <div class="text-center mb-5">


            <h1 class="fw-bold">

                <i class="bi bi-shop text-primary"></i>

                Our Shop

            </h1>


            <p class="text-muted">

                Explore our latest products

            </p>


        </div>


    <?php endif; ?>


    <!-- =================================================
         PRODUCT SECTION
    ================================================== -->

    <div class="row g-4">


        <?php if (mysqli_num_rows($result) > 0): ?>


            <?php while ($product = mysqli_fetch_assoc($result)): ?>


                <!-- =================================================
                     PRODUCT COLUMN
                ================================================== -->

                <div class="col-md-6 col-lg-4 col-xl-3">


                    <!-- PRODUCT CARD -->

                    <div
                        class="card h-100 shadow-sm product-card"
                    >


                        <!-- =============================================
                             PRODUCT IMAGE
                        ============================================== -->

                        <?php if (!empty($product["image"])): ?>


                            <img
                                src="assets/images/<?= htmlspecialchars(
                                    $product["image"]
                                ) ?>"
                                class="card-img-top product-image"
                                alt="<?= htmlspecialchars(
                                    $product["name"]
                                ) ?>"
                            >


                        <?php else: ?>


                            <div class="no-image">

                                <i
                                    class="bi bi-image fs-1 text-secondary"
                                ></i>

                            </div>


                        <?php endif; ?>


                        <!-- =============================================
                             CARD BODY
                        ============================================== -->

                        <div
                            class="card-body d-flex flex-column"
                        >


                            <!-- CATEGORY -->

                            <small
                                class="text-primary fw-semibold"
                            >

                                <i class="bi bi-tag"></i>

                                <?= htmlspecialchars(
                                    $product["category_name"]
                                ) ?>

                            </small>


                            <!-- PRODUCT NAME -->

                            <h5 class="card-title mt-2">

                                <?= htmlspecialchars(
                                    $product["name"]
                                ) ?>

                            </h5>


                            <!-- DESCRIPTION -->

                            <p class="card-text text-muted small">

                                <?php

                                $description =
                                    $product["description"] ?? "";

                                if (strlen($description) > 80) {

                                    echo htmlspecialchars(
                                        substr(
                                            $description,
                                            0,
                                            80
                                        ) . "..."
                                    );

                                } else {

                                    echo htmlspecialchars(
                                        $description
                                    );

                                }

                                ?>

                            </p>


                            <!-- PRICE -->

                            <div class="mb-2">


                                <span
                                    class="fw-bold fs-5 text-success"
                                >

                                    ₹<?= number_format(
                                        (float)$product["price"],
                                        2
                                    ) ?>

                                </span>


                                <?php

                                if (
                                    !empty($product["old_price"]) &&
                                    $product["old_price"] >
                                    $product["price"]
                                ):

                                ?>


                                    <del
                                        class="text-muted ms-2"
                                    >

                                        ₹<?= number_format(
                                            (float)$product["old_price"],
                                            2
                                        ) ?>

                                    </del>


                                <?php endif; ?>


                            </div>


                            <!-- STOCK STATUS -->

                            <?php if ($product["stock"] > 0): ?>


                                <small
                                    class="text-success mb-3"
                                >

                                    <i
                                        class="bi bi-check-circle-fill"
                                    ></i>

                                    In Stock

                                </small>


                            <?php else: ?>


                                <small
                                    class="text-danger mb-3"
                                >

                                    <i
                                        class="bi bi-x-circle-fill"
                                    ></i>

                                    Out of Stock

                                </small>


                            <?php endif; ?>


                            <!-- =============================================
                                 BUTTONS
                            ============================================== -->

                            <div
                                class="mt-auto d-flex gap-2"
                            >


                                <!-- VIEW PRODUCT -->

                                <a
                                    href="product.php?id=<?= (int)$product["id"] ?>"
                                    class="btn btn-outline-primary w-50"
                                >

                                    <i class="bi bi-eye"></i>

                                    View

                                </a>


                                <!-- ADD TO CART -->

                                <?php if ($product["stock"] > 0): ?>


                                    <a
                                        href="add_to_cart.php?id=<?= (int)$product["id"] ?>"
                                        class="btn btn-primary w-50"
                                    >

                                        <i
                                            class="bi bi-cart-plus"
                                        ></i>

                                        Cart

                                    </a>


                                <?php else: ?>


                                    <button
                                        type="button"
                                        class="btn btn-secondary w-50"
                                        disabled
                                    >

                                        <i class="bi bi-x-circle"></i>

                                        Out

                                    </button>


                                <?php endif; ?>


                            </div>


                        </div>

                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =================================================
                 NO PRODUCTS / NO SEARCH RESULTS
            ================================================== -->

            <div class="col-12">


                <div
                    class="alert alert-warning text-center py-5"
                >


                    <?php if ($search !== ""): ?>


                        <i
                            class="bi bi-search fs-1 text-warning"
                        ></i>


                        <h4 class="mt-3">

                            No Products Found

                        </h4>


                        <p class="text-muted">

                            No products matched:

                            <strong>
                                <?= htmlspecialchars($search) ?>
                            </strong>

                        </p>


                        <a
                            href="shop.php"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-shop"></i>

                            Show All Products

                        </a>


                    <?php else: ?>


                        <i
                            class="bi bi-info-circle fs-1 text-primary"
                        ></i>


                        <h4 class="mt-3">

                            No Products Available

                        </h4>


                        <p class="text-muted">

                            No products are available right now.

                        </p>


                    <?php endif; ?>


                </div>


            </div>


        <?php endif; ?>


    </div>


</div>


<!-- =====================================================
     FOOTER
====================================================== -->

<footer
    class="bg-dark text-white text-center py-4 mt-5"
>


    <div class="container">


        <p class="mb-0">

            © <?= date("Y") ?> EasyMart.
            All Rights Reserved.

        </p>


    </div>


</footer>


<!-- =====================================================
     BOOTSTRAP JS
====================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>