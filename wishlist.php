<?php

require_once __DIR__ . "/includes/auth.php";
requireLogin();

require_once __DIR__ . "/includes/db.php";

$userId = getCurrentUserId();


// ======================================================
// REMOVE PRODUCT FROM WISHLIST
// ======================================================

if (isset($_GET["remove"])) {

    $productId = (int) $_GET["remove"];

    if ($productId > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM wishlist
             WHERE user_id = ?
             AND product_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $userId,
            $productId
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }

    header("Location: wishlist.php");
    exit;
}


// ======================================================
// ADD PRODUCT TO CART
// ======================================================

if (isset($_GET["cart"])) {

    $productId = (int) $_GET["cart"];

    if ($productId > 0) {

        // Check product
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, stock
             FROM products
             WHERE id = ?
             AND status = 'active'
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $productId
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $product = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if ($product && $product["stock"] > 0) {

            // Check if already in cart

            $stmt = mysqli_prepare(
                $conn,
                "SELECT id, quantity
                 FROM cart
                 WHERE user_id = ?
                 AND product_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $userId,
                $productId
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $cartItem = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);


            if ($cartItem) {

                // Increase quantity

                $newQuantity = $cartItem["quantity"] + 1;

                // Do not exceed stock

                if ($newQuantity > $product["stock"]) {
                    $newQuantity = $product["stock"];
                }

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE cart
                     SET quantity = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ii",
                    $newQuantity,
                    $cartItem["id"]
                );

                mysqli_stmt_execute($stmt);

                mysqli_stmt_close($stmt);

            } else {

                // Add new item

                $quantity = 1;

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO cart
                     (user_id, product_id, quantity)
                     VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $userId,
                    $productId,
                    $quantity
                );

                mysqli_stmt_execute($stmt);

                mysqli_stmt_close($stmt);
            }
        }
    }

    header("Location: cart.php");
    exit;
}


// ======================================================
// FETCH WISHLIST PRODUCTS
// ======================================================

$sql = "SELECT
            wishlist.id AS wishlist_id,
            products.id AS product_id,
            products.name,
            products.description,
            products.price,
            products.old_price,
            products.stock,
            products.image,
            categories.name AS category_name
        FROM wishlist

        INNER JOIN products
            ON wishlist.product_id = products.id

        LEFT JOIN categories
            ON products.category_id = categories.id

        WHERE wishlist.user_id = ?

        ORDER BY wishlist.id DESC";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Wishlist - EasyMart</title>


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
            background-color: #f8f9fa;
        }


        .wishlist-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: 0.3s;
        }


        .wishlist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
        }


        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }


        .no-image {
            height: 220px;
            background: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
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

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
====================================================== -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">


        <!-- Logo -->

        <a
            class="navbar-brand text-primary fw-bold"
            href="index.php"
        >

            <i class="bi bi-cart-check-fill"></i>

            EasyMart

        </a>


        <!-- Mobile Button -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div
            class="collapse navbar-collapse"
            id="navbarMenu"
        >


            <!-- Navigation -->

            <ul class="navbar-nav ms-auto align-items-lg-center">


                <!-- Home -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php"
                    >

                        <i class="bi bi-house-door"></i>

                        Home

                    </a>

                </li>


                <!-- Shop -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="shop.php"
                    >

                        <i class="bi bi-shop"></i>

                        Shop

                    </a>

                </li>


                <!-- Categories -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="category.php"
                    >

                        <i class="bi bi-grid"></i>

                        Categories

                    </a>

                </li>


                <!-- Cart -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="cart.php"
                    >

                        <i class="bi bi-cart"></i>

                        Cart

                    </a>

                </li>


                <!-- Wishlist -->

                <li class="nav-item">

                    <a
                        class="nav-link active text-danger"
                        href="wishlist.php"
                    >

                        <i class="bi bi-heart-fill"></i>

                        Wishlist

                    </a>

                </li>


                <!-- Profile -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="profile.php"
                    >

                        <i class="bi bi-person-circle"></i>

                        <?= htmlspecialchars(
                            getCurrentUserName()
                        ) ?>

                    </a>

                </li>


                <!-- Logout -->

                <li class="nav-item">

                    <a
                        class="nav-link text-danger"
                        href="logout.php"
                    >

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>

                </li>


            </ul>

        </div>

    </div>

</nav>


<!-- =====================================================
     MAIN CONTENT
====================================================== -->

<div class="container py-5">


    <!-- Heading -->

    <div class="text-center mb-5">

        <h1 class="fw-bold">

            <i class="bi bi-heart-fill text-danger"></i>

            My Wishlist

        </h1>

        <p class="text-muted">

            Products you saved for later

        </p>

    </div>


    <!-- =================================================
         WISHLIST PRODUCTS
    ================================================== -->

    <?php if (mysqli_num_rows($result) > 0): ?>


        <div class="row g-4">


            <?php while ($product = mysqli_fetch_assoc($result)): ?>


                <div class="col-md-6 col-lg-4 col-xl-3">


                    <!-- Product Card -->

                    <div
                        class="card wishlist-card h-100 shadow-sm"
                    >


                        <!-- Product Image -->

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


                        <!-- Card Body -->

                        <div
                            class="card-body d-flex flex-column"
                        >


                            <!-- Category -->

                            <?php if (!empty($product["category_name"])): ?>

                                <small
                                    class="text-primary fw-semibold"
                                >

                                    <i class="bi bi-tag"></i>

                                    <?= htmlspecialchars(
                                        $product["category_name"]
                                    ) ?>

                                </small>

                            <?php endif; ?>


                            <!-- Product Name -->

                            <h5 class="card-title mt-2">

                                <?= htmlspecialchars(
                                    $product["name"]
                                ) ?>

                            </h5>


                            <!-- Description -->

                            <p class="card-text text-muted small">

                                <?php

                                $description =
                                    $product["description"] ?? "";

                                if (strlen($description) > 70) {

                                    echo htmlspecialchars(
                                        substr(
                                            $description,
                                            0,
                                            70
                                        ) . "..."
                                    );

                                } else {

                                    echo htmlspecialchars(
                                        $description
                                    );

                                }

                                ?>

                            </p>


                            <!-- Price -->

                            <div class="mb-2">


                                <span class="price text-success">

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

                                    <span class="old-price">

                                        ₹<?= number_format(
                                            (float)$product["old_price"],
                                            2
                                        ) ?>

                                    </span>

                                <?php endif; ?>


                            </div>


                            <!-- Stock -->

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


                            <!-- =================================================
                                 BUTTONS
                            ================================================== -->

                            <div
                                class="mt-auto d-flex flex-column gap-2"
                            >


                                <!-- View -->

                                <a
                                    href="product.php?id=<?= (int)$product["product_id"] ?>"
                                    class="btn btn-outline-primary"
                                >

                                    <i class="bi bi-eye"></i>

                                    View Product

                                </a>


                                <!-- Add To Cart -->

                                <?php if ($product["stock"] > 0): ?>


                                    <a
                                        href="wishlist.php?cart=<?= (int)$product["product_id"] ?>"
                                        class="btn btn-primary"
                                    >

                                        <i
                                            class="bi bi-cart-plus"
                                        ></i>

                                        Add to Cart

                                    </a>


                                <?php else: ?>


                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        disabled
                                    >

                                        <i
                                            class="bi bi-x-circle"
                                        ></i>

                                        Out of Stock

                                    </button>


                                <?php endif; ?>


                                <!-- Remove -->

                                <a
                                    href="wishlist.php?remove=<?= (int)$product["product_id"] ?>"
                                    class="btn btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to remove this product from wishlist?');"
                                >

                                    <i
                                        class="bi bi-trash"
                                    ></i>

                                    Remove

                                </a>


                            </div>


                        </div>

                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <!-- =================================================
             EMPTY WISHLIST
        ================================================== -->

        <div class="text-center py-5">


            <i
                class="bi bi-heart"
                style="font-size: 80px; color: #dc3545;"
            ></i>


            <h3 class="mt-4">

                Your Wishlist is Empty

            </h3>


            <p class="text-muted">

                You haven't added any products to your wishlist yet.

            </p>


            <a
                href="shop.php"
                class="btn btn-primary mt-2"
            >

                <i class="bi bi-shop"></i>

                Continue Shopping

            </a>


        </div>


    <?php endif; ?>


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


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>

<?php

mysqli_stmt_close($stmt);

?>