<?php

$conn = require_once "includes/db.php";
require_once "includes/auth.php";


// Check product ID

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: shop.php");
    exit;

}

$product_id = (int) $_GET["id"];


// Fetch product

$stmt = mysqli_prepare(
    $conn,
    "SELECT products.*, categories.name AS category_name
     FROM products
     INNER JOIN categories
     ON products.category_id = categories.id
     WHERE products.id = ?
     AND products.status = 'active'"
);

mysqli_stmt_bind_param($stmt, "i", $product_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$product = mysqli_fetch_assoc($result);


if (!$product) {

    header("Location: shop.php");
    exit;

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
        <?php echo htmlspecialchars($product["name"]); ?> - EasyMart
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

</head>

<body class="bg-light">


<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">

        <a class="navbar-brand text-primary fw-bold"
           href="index.php">

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


        <div class="collapse navbar-collapse"
             id="navbarMenu">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">

                    <a class="nav-link"
                       href="index.php">

                        <i class="bi bi-house-door"></i>
                        Home

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link"
                       href="shop.php">

                        <i class="bi bi-shop"></i>
                        Shop

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link"
                       href="cart.php">

                        <i class="bi bi-cart"></i>
                        Cart

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link"
                       href="wishlist.php">

                        <i class="bi bi-heart"></i>
                        Wishlist

                    </a>

                </li>


                <?php if (isLoggedIn()): ?>

                    <li class="nav-item">

                        <a class="nav-link"
                           href="profile.php">

                            <i class="bi bi-person-circle text-primary"></i>

                            <?php
                            echo htmlspecialchars(
                                getCurrentUserName()
                            );
                            ?>

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

                        <a class="nav-link"
                           href="login.php">

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


<!-- ================= PRODUCT DETAILS ================= -->

<div class="container py-5">

    <div class="row g-5">


        <!-- Product Image -->

        <div class="col-md-6">

            <div class="card border-0 shadow-sm">

                <?php if (!empty($product["image"])): ?>

                    <img
                        src="assets/images/<?php echo htmlspecialchars($product["image"]); ?>"
                        class="img-fluid rounded"
                        style="width:100%; height:450px; object-fit:cover;"
                        alt="<?php echo htmlspecialchars($product["name"]); ?>"
                    >

                <?php else: ?>

                    <div
                        class="d-flex align-items-center justify-content-center bg-light"
                        style="height:450px;"
                    >

                        <i class="bi bi-image fs-1 text-secondary"></i>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- Product Information -->

        <div class="col-md-6">


            <!-- Category -->

            <span class="badge bg-primary mb-3">

                <?php
                echo htmlspecialchars(
                    $product["category_name"]
                );
                ?>

            </span>


            <!-- Product Name -->

            <h1 class="fw-bold">

                <?php
                echo htmlspecialchars(
                    $product["name"]
                );
                ?>

            </h1>


            <!-- Description -->

            <p class="text-muted mt-3">

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $product["description"]
                    )
                );
                ?>

            </p>


            <!-- Price -->

            <div class="my-4">

                <span class="fs-2 fw-bold text-success">

                    ₹<?php
                    echo number_format(
                        $product["price"],
                        2
                    );
                    ?>

                </span>


                <?php if (!empty($product["old_price"])): ?>

                    <del class="fs-5 text-muted ms-3">

                        ₹<?php
                        echo number_format(
                            $product["old_price"],
                            2
                        );
                        ?>

                    </del>

                <?php endif; ?>

            </div>


            <!-- Stock -->

            <?php if ($product["stock"] > 0): ?>

                <p class="text-success">

                    <i class="bi bi-check-circle-fill"></i>

                    <?php echo $product["stock"]; ?>
                    items available

                </p>

            <?php else: ?>

                <p class="text-danger">

                    <i class="bi bi-x-circle-fill"></i>

                    Out of Stock

                </p>

            <?php endif; ?>


            <!-- Quantity -->

            <?php if ($product["stock"] > 0): ?>

                <form
                    action="add_to_cart.php"
                    method="POST"
                    class="mt-4"
                >

                    <input
                        type="hidden"
                        name="product_id"
                        value="<?php echo $product["id"]; ?>"
                    >


                    <label class="form-label fw-semibold">

                        Quantity

                    </label>


                    <div
                        class="input-group mb-3"
                        style="max-width:180px;"
                    >

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="decreaseQty()"
                        >
                            −
                        </button>


                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            class="form-control text-center"
                            value="1"
                            min="1"
                            max="<?php echo $product["stock"]; ?>"
                        >


                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="increaseQty()"
                        >
                            +
                        </button>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary btn-lg"
                    >

                        <i class="bi bi-cart-plus"></i>

                        Add to Cart

                    </button>


                    <a
                        href="shop.php"
                        class="btn btn-outline-secondary btn-lg ms-2"
                    >

                        Continue Shopping

                    </a>

                </form>

            <?php endif; ?>

        </div>

    </div>

</div>


<script>

function decreaseQty()
{
    let quantity = document.getElementById("quantity");

    let value = parseInt(quantity.value);

    if (value > 1)
    {
        quantity.value = value - 1;
    }
}


function increaseQty()
{
    let quantity = document.getElementById("quantity");

    let max = parseInt(quantity.max);

    let value = parseInt(quantity.value);

    if (value < max)
    {
        quantity.value = value + 1;
    }
}

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>