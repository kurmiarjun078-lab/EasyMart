<?php

$conn = require_once "includes/db.php";
require_once "includes/auth.php";


// User must be logged in
requireLogin();

$user_id = getCurrentUserId();


// ================= REMOVE ITEM =================

if (isset($_GET["remove"]) && is_numeric($_GET["remove"])) {

    $cart_id = (int) $_GET["remove"];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM cart
         WHERE id = ?
         AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $cart_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    header("Location: cart.php");
    exit;
}


// ================= UPDATE CART =================

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_cart"])) {

    if (isset($_POST["quantities"])
        && is_array($_POST["quantities"])) {

        foreach ($_POST["quantities"] as $cart_id => $quantity) {

            $cart_id = (int) $cart_id;
            $quantity = (int) $quantity;

            if ($quantity < 1) {
                $quantity = 1;
            }


            // Get available stock
            $stmt = mysqli_prepare(
                $conn,
                "SELECT products.stock
                 FROM cart
                 INNER JOIN products
                 ON cart.product_id = products.id
                 WHERE cart.id = ?
                 AND cart.user_id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $cart_id,
                $user_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $stock_data = mysqli_fetch_assoc($result);


            if ($stock_data) {

                $stock = (int) $stock_data["stock"];

                if ($stock <= 0) {
                    $quantity = 1;
                } elseif ($quantity > $stock) {
                    $quantity = $stock;
                }


                // Update quantity
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE cart
                     SET quantity = ?
                     WHERE id = ?
                     AND user_id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "iii",
                    $quantity,
                    $cart_id,
                    $user_id
                );

                mysqli_stmt_execute($update);
            }
        }
    }

    header("Location: cart.php");
    exit;
}


// ================= FETCH CART =================

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        cart.id AS cart_id,
        cart.quantity,
        products.id AS product_id,
        products.name,
        products.price,
        products.old_price,
        products.image,
        products.stock
     FROM cart
     INNER JOIN products
     ON cart.product_id = products.id
     WHERE cart.user_id = ?
     ORDER BY cart.id DESC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// Calculate totals
$subtotal = 0;
$cart_items = [];

while ($item = mysqli_fetch_assoc($result)) {

    $item["item_total"] =
        $item["price"] * $item["quantity"];

    $subtotal += $item["item_total"];

    $cart_items[] = $item;
}


// Shipping
$shipping = ($subtotal > 0 && $subtotal < 500) ? 50 : 0;


// Grand Total
$grand_total = $subtotal + $shipping;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Shopping Cart - EasyMart</title>


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


        <!-- Menu -->

        <div
            class="collapse navbar-collapse"
            id="navbarMenu"
        >

            <ul
                class="navbar-nav ms-auto align-items-lg-center"
            >

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


                <!-- Cart -->

                <li class="nav-item">

                    <a
                        class="nav-link active text-primary"
                        href="cart.php"
                    >

                        <i class="bi bi-cart"></i>

                        Cart

                    </a>

                </li>


                <!-- Wishlist -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="wishlist.php"
                    >

                        <i class="bi bi-heart"></i>

                        Wishlist

                    </a>

                </li>


                <?php if (isLoggedIn()): ?>

                    <!-- Profile -->

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="profile.php"
                        >

                            <i
                                class="bi bi-person-circle text-primary"
                            ></i>

                            <?php
                            echo htmlspecialchars(
                                getCurrentUserName()
                            );
                            ?>

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

                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>


<!-- ================= CART HEADER ================= -->

<div class="container py-5">


    <div class="mb-4">

        <h1 class="fw-bold">

            <i class="bi bi-cart3 text-primary"></i>

            Shopping Cart

        </h1>

        <p class="text-muted">

            Review your products before checkout.

        </p>

    </div>


    <?php if (count($cart_items) > 0): ?>


        <div class="row g-4">


            <!-- ================= CART ITEMS ================= -->

            <div class="col-lg-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-0">


                        <form
                            method="POST"
                            action="cart.php"
                        >

                            <div class="table-responsive">

                                <table
                                    class="table table-hover align-middle mb-0"
                                >

                                    <thead class="table-light">

                                        <tr>

                                            <th class="p-3">
                                                Product
                                            </th>

                                            <th>
                                                Price
                                            </th>

                                            <th>
                                                Quantity
                                            </th>

                                            <th>
                                                Total
                                            </th>

                                            <th>
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php foreach ($cart_items as $item): ?>

                                        <tr>


                                            <!-- Product -->

                                            <td class="p-3">

                                                <div
                                                    class="d-flex align-items-center"
                                                >


                                                    <?php if (!empty($item["image"])): ?>

                                                        <img
                                                            src="assets/images/<?php echo htmlspecialchars($item["image"]); ?>"
                                                            alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                                            style="
                                                                width:80px;
                                                                height:80px;
                                                                object-fit:cover;
                                                            "
                                                            class="rounded me-3"
                                                        >

                                                    <?php else: ?>

                                                        <div
                                                            class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                            style="
                                                                width:80px;
                                                                height:80px;
                                                            "
                                                        >

                                                            <i
                                                                class="bi bi-image fs-3 text-secondary"
                                                            ></i>

                                                        </div>

                                                    <?php endif; ?>


                                                    <div>

                                                        <h6 class="mb-1">

                                                            <?php
                                                            echo htmlspecialchars(
                                                                $item["name"]
                                                            );
                                                            ?>

                                                        </h6>


                                                        <?php if ($item["stock"] <= 0): ?>

                                                            <small class="text-danger">

                                                                Out of Stock

                                                            </small>

                                                        <?php else: ?>

                                                            <small class="text-success">

                                                                In Stock

                                                            </small>

                                                        <?php endif; ?>

                                                    </div>

                                                </div>

                                            </td>


                                            <!-- Price -->

                                            <td>

                                                ₹<?php
                                                echo number_format(
                                                    $item["price"],
                                                    2
                                                );
                                                ?>

                                            </td>


                                            <!-- Quantity -->

                                            <td>

                                                <div
                                                    class="input-group"
                                                    style="width:130px;"
                                                >

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary"
                                                        onclick="changeQty(
                                                            <?php echo $item["cart_id"]; ?>,
                                                            -1
                                                        )"
                                                    >

                                                        −

                                                    </button>


                                                    <input
                                                        type="number"
                                                        name="quantities[<?php echo $item["cart_id"]; ?>]"
                                                        id="qty-<?php echo $item["cart_id"]; ?>"
                                                        value="<?php echo $item["quantity"]; ?>"
                                                        min="1"
                                                        max="<?php echo max(1, $item["stock"]); ?>"
                                                        class="form-control text-center"
                                                    >


                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary"
                                                        onclick="changeQty(
                                                            <?php echo $item["cart_id"]; ?>,
                                                            1
                                                        )"
                                                    >

                                                        +

                                                    </button>

                                                </div>

                                            </td>


                                            <!-- Item Total -->

                                            <td class="fw-bold">

                                                ₹<?php
                                                echo number_format(
                                                    $item["item_total"],
                                                    2
                                                );
                                                ?>

                                            </td>


                                            <!-- Remove -->

                                            <td>

                                                <a
                                                    href="cart.php?remove=<?php echo $item["cart_id"]; ?>"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Remove this product from cart?');"
                                                >

                                                    <i
                                                        class="bi bi-trash"
                                                    ></i>

                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>


                                    </tbody>

                                </table>

                            </div>


                            <!-- Update Button -->

                            <div class="p-3 text-end">

                                <button
                                    type="submit"
                                    name="update_cart"
                                    class="btn btn-outline-primary"
                                >

                                    <i class="bi bi-arrow-repeat"></i>

                                    Update Cart

                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- Continue Shopping -->

                <div class="mt-3">

                    <a
                        href="shop.php"
                        class="btn btn-outline-secondary"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Continue Shopping

                    </a>

                </div>

            </div>


            <!-- ================= CART SUMMARY ================= -->

            <div class="col-lg-4">

                <div
                    class="card border-0 shadow-sm"
                >

                    <div class="card-body">

                        <h4 class="fw-bold mb-4">

                            Order Summary

                        </h4>


                        <!-- Subtotal -->

                        <div
                            class="d-flex justify-content-between mb-3"
                        >

                            <span>
                                Subtotal
                            </span>

                            <strong>

                                ₹<?php
                                echo number_format(
                                    $subtotal,
                                    2
                                );
                                ?>

                            </strong>

                        </div>


                        <!-- Shipping -->

                        <div
                            class="d-flex justify-content-between mb-3"
                        >

                            <span>
                                Shipping
                            </span>

                            <strong>

                                <?php if ($shipping > 0): ?>

                                    ₹<?php
                                    echo number_format(
                                        $shipping,
                                        2
                                    );
                                    ?>

                                <?php else: ?>

                                    <span class="text-success">
                                        FREE
                                    </span>

                                <?php endif; ?>

                            </strong>

                        </div>


                        <hr>


                        <!-- Grand Total -->

                        <div
                            class="d-flex justify-content-between mb-4"
                        >

                            <span class="fs-5 fw-bold">
                                Grand Total
                            </span>

                            <strong class="fs-4 text-success">

                                ₹<?php
                                echo number_format(
                                    $grand_total,
                                    2
                                );
                                ?>

                            </strong>

                        </div>


                        <!-- Free Shipping Message -->

                        <?php if ($subtotal > 0 && $subtotal < 500): ?>

                            <div
                                class="alert alert-info small"
                            >

                                <i class="bi bi-truck"></i>

                                Add ₹<?php
                                echo number_format(
                                    500 - $subtotal,
                                    2
                                );
                                ?>

                                more to get
                                <strong>FREE shipping</strong>.

                            </div>

                        <?php elseif ($subtotal >= 500): ?>

                            <div
                                class="alert alert-success small"
                            >

                                <i class="bi bi-check-circle"></i>

                                You got
                                <strong>FREE shipping!</strong>

                            </div>

                        <?php endif; ?>


                        <!-- Checkout -->

                        <a
                            href="checkout.php"
                            class="btn btn-primary w-100 btn-lg"
                        >

                            <i class="bi bi-credit-card"></i>

                            Proceed to Checkout

                        </a>

                    </div>

                </div>

            </div>

        </div>


    <?php else: ?>


        <!-- ================= EMPTY CART ================= -->

        <div
            class="card border-0 shadow-sm"
        >

            <div class="card-body text-center py-5">


                <i
                    class="bi bi-cart-x text-secondary"
                    style="font-size:80px;"
                ></i>


                <h2 class="fw-bold mt-4">

                    Your Cart is Empty

                </h2>


                <p class="text-muted">

                    Looks like you haven't added any products yet.

                </p>


                <a
                    href="shop.php"
                    class="btn btn-primary mt-3"
                >

                    <i class="bi bi-shop"></i>

                    Start Shopping

                </a>

            </div>

        </div>


    <?php endif; ?>


</div>


<!-- ================= JAVASCRIPT ================= -->

<script>

function changeQty(cartId, change)
{
    const input =
        document.getElementById("qty-" + cartId);

    let value =
        parseInt(input.value) || 1;

    let min =
        parseInt(input.min) || 1;

    let max =
        parseInt(input.max) || 999;

    value = value + change;

    if (value < min)
    {
        value = min;
    }

    if (value > max)
    {
        value = max;
    }

    input.value = value;
}

</script>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>