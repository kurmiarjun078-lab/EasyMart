<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$user_id = getCurrentUserId();

$message = "";
$error = "";


// ================= USER DETAILS =================

$userQuery = "SELECT * FROM users WHERE id = ?";

$stmt = mysqli_prepare($conn, $userQuery);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$userResult = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($userResult);

mysqli_stmt_close($stmt);


// ================= CART ITEMS =================

$cartQuery = "
    SELECT
        cart.product_id,
        cart.quantity,
        products.name,
        products.price,
        products.stock,
        products.image
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
";

$stmt = mysqli_prepare($conn, $cartQuery);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$cartResult = mysqli_stmt_get_result($stmt);

$cartItems = [];

$subtotal = 0;


while ($item = mysqli_fetch_assoc($cartResult)) {

    // Skip out-of-stock products
    if ((int)$item["stock"] <= 0) {
        continue;
    }

    // Do not allow quantity greater than stock
    $quantity = min(
        (int)$item["quantity"],
        (int)$item["stock"]
    );

    $item["quantity"] = $quantity;

    $itemTotal = (float)$item["price"] * $quantity;

    $subtotal += $itemTotal;

    $cartItems[] = $item;
}

mysqli_stmt_close($stmt);


// If cart is empty
if (empty($cartItems)) {

    header("Location: cart.php");

    exit;
}


// ================= SHIPPING =================

$shipping = ($subtotal < 500) ? 50 : 0;

$grandTotal = $subtotal + $shipping;


// ================= PLACE ORDER =================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $shipping_name = trim(
        $_POST["shipping_name"] ?? ""
    );

    $shipping_phone = trim(
        $_POST["shipping_phone"] ?? ""
    );

    $shipping_address = trim(
        $_POST["shipping_address"] ?? ""
    );

    $shipping_city = trim(
        $_POST["shipping_city"] ?? ""
    );

    $shipping_state = trim(
        $_POST["shipping_state"] ?? ""
    );

    $shipping_pincode = trim(
        $_POST["shipping_pincode"] ?? ""
    );

    $payment_method = $_POST["payment_method"]
        ?? "Cash on Delivery";


    $allowedPayments = [
        "Cash on Delivery",
        "UPI",
        "Credit/Debit Card"
    ];


    // ================= VALIDATION =================

    if ($shipping_name === "") {

        $error = "Please enter your name.";

    } elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $shipping_phone
        )
    ) {

        $error = "Please enter a valid 10 digit phone number.";

    } elseif ($shipping_address === "") {

        $error = "Please enter your address.";

    } elseif ($shipping_city === "") {

        $error = "Please enter your city.";

    } elseif ($shipping_state === "") {

        $error = "Please enter your state.";

    } elseif (
        !preg_match(
            "/^[0-9]{6}$/",
            $shipping_pincode
        )
    ) {

        $error = "Please enter a valid 6 digit pincode.";

    } elseif (
        !in_array(
            $payment_method,
            $allowedPayments,
            true
        )
    ) {

        $error = "Invalid payment method.";

    }


    // ================= TRANSACTION =================

    if ($error === "") {

        mysqli_begin_transaction($conn);

        try {

            // ==========================================
            // RE-CHECK CART AND STOCK
            // ==========================================

            $cartQuery = "
                SELECT
                    cart.product_id,
                    cart.quantity,
                    products.price,
                    products.stock
                FROM cart
                INNER JOIN products
                    ON cart.product_id = products.id
                WHERE cart.user_id = ?
                FOR UPDATE
            ";

            $stmt = mysqli_prepare(
                $conn,
                $cartQuery
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $user_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $orderItems = [];

            $subtotal = 0;


            while ($item = mysqli_fetch_assoc($result)) {

                // Product out of stock
                if ((int)$item["stock"] <= 0) {

                    throw new Exception(
                        "One product is out of stock."
                    );
                }


                $quantity = (int)$item["quantity"];

                $stock = (int)$item["stock"];


                // Quantity greater than stock
                if ($quantity > $stock) {

                    throw new Exception(
                        "Some product quantity is greater than available stock."
                    );
                }


                $subtotal +=
                    (float)$item["price"] * $quantity;


                $orderItems[] = [
                    "product_id" => (int)$item["product_id"],
                    "quantity" => $quantity,
                    "price" => (float)$item["price"]
                ];
            }


            mysqli_stmt_close($stmt);


            if (empty($orderItems)) {

                throw new Exception(
                    "Your cart is empty."
                );
            }


            // Recalculate totals
            $shipping = ($subtotal < 500) ? 50 : 0;

            $grandTotal = $subtotal + $shipping;


            // ==========================================
            // INSERT ORDER
            // ==========================================

            $orderQuery = "
                INSERT INTO orders
                (
                    user_id,
                    total_amount,
                    payment_method,
                    payment_status,
                    order_status,
                    shipping_name,
                    shipping_phone,
                    shipping_address,
                    shipping_city,
                    shipping_state,
                    shipping_pincode
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    'pending',
                    'Pending',
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ";


            $stmt = mysqli_prepare(
                $conn,
                $orderQuery
            );


            mysqli_stmt_bind_param(
                $stmt,
                "idsssssss",
                $user_id,
                $grandTotal,
                $payment_method,
                $shipping_name,
                $shipping_phone,
                $shipping_address,
                $shipping_city,
                $shipping_state,
                $shipping_pincode
            );


            if (!mysqli_stmt_execute($stmt)) {

                throw new Exception(
                    "Order creation failed."
                );
            }


            // Get new order ID
            $order_id = mysqli_insert_id($conn);


            mysqli_stmt_close($stmt);


            // ==========================================
            // INSERT FIRST TRACKING RECORD
            // ==========================================

            $tracking_status = "Order Placed";

            $tracking_location = $shipping_city;

            $tracking_description =
                "Your order has been successfully placed.";


            $trackingQuery = "
                INSERT INTO order_tracking
                (
                    order_id,
                    status,
                    location,
                    description
                )
                VALUES
                (?, ?, ?, ?)
            ";


            $trackingStmt = mysqli_prepare(
                $conn,
                $trackingQuery
            );


            if (!$trackingStmt) {

                throw new Exception(
                    "Tracking record preparation failed."
                );
            }


            mysqli_stmt_bind_param(
                $trackingStmt,
                "isss",
                $order_id,
                $tracking_status,
                $tracking_location,
                $tracking_description
            );


            if (!mysqli_stmt_execute($trackingStmt)) {

                mysqli_stmt_close($trackingStmt);

                throw new Exception(
                    "Tracking record creation failed."
                );
            }


            mysqli_stmt_close($trackingStmt);


            // ==========================================
            // INSERT ORDER ITEMS
            // ==========================================

            foreach ($orderItems as $item) {

                $itemQuery = "
                    INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        quantity,
                        price
                    )
                    VALUES (?, ?, ?, ?)
                ";


                $stmt = mysqli_prepare(
                    $conn,
                    $itemQuery
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "iiid",
                    $order_id,
                    $item["product_id"],
                    $item["quantity"],
                    $item["price"]
                );


                if (!mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    throw new Exception(
                        "Order item insertion failed."
                    );
                }


                mysqli_stmt_close($stmt);


                // ======================================
                // REDUCE PRODUCT STOCK
                // ======================================

                $stockQuery = "
                    UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?
                    AND stock >= ?
                ";


                $stmt = mysqli_prepare(
                    $conn,
                    $stockQuery
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $item["quantity"],
                    $item["product_id"],
                    $item["quantity"]
                );


                if (
                    !mysqli_stmt_execute($stmt) ||
                    mysqli_stmt_affected_rows($stmt) !== 1
                ) {

                    mysqli_stmt_close($stmt);

                    throw new Exception(
                        "Stock update failed."
                    );
                }


                mysqli_stmt_close($stmt);
            }


            // ==========================================
            // CLEAR CART
            // ==========================================

            $deleteCart = "
                DELETE FROM cart
                WHERE user_id = ?
            ";


            $stmt = mysqli_prepare(
                $conn,
                $deleteCart
            );


            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $user_id
            );


            if (!mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                throw new Exception(
                    "Cart clearing failed."
                );
            }


            mysqli_stmt_close($stmt);


            // ==========================================
            // COMMIT TRANSACTION
            // ==========================================

            mysqli_commit($conn);


            // Save order ID in session
            $_SESSION["last_order_id"] = $order_id;


            // Redirect
            header(
                "Location: order_success.php"
            );

            exit;


        } catch (Exception $e) {

            // Rollback everything
            mysqli_rollback($conn);

            $error = $e->getMessage();
        }
    }
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

    <title>Checkout - EasyMart</title>


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

        <a
            class="navbar-brand text-primary fw-bold"
            href="index.php"
        >

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


        <div
            class="collapse navbar-collapse"
            id="navbarNav"
        >

            <ul class="navbar-nav ms-auto">


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
                        class="nav-link"
                        href="cart.php"
                    >

                        <i class="bi bi-cart"></i>

                        Cart

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
                        href="orders.php"
                    >

                        <i class="bi bi-box-seam"></i>

                        My Orders

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="profile.php"
                    >

                        <i class="bi bi-person-circle"></i>

                        <?php
                        echo htmlspecialchars(
                            getCurrentUserName()
                        );
                        ?>

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link text-danger"
                        href="logout.php"
                    >

                        Logout

                    </a>

                </li>


            </ul>

        </div>

    </div>

</nav>



<!-- ================= CHECKOUT ================= -->

<div class="container py-5">

    <div class="row">


        <!-- ================= FORM ================= -->

        <div class="col-lg-8">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">


                    <h3 class="mb-4">

                        <i class="bi bi-credit-card"></i>

                        Checkout

                    </h3>


                    <?php if ($error !== ""): ?>

                        <div class="alert alert-danger">

                            <i class="bi bi-exclamation-triangle"></i>

                            <?php
                            echo htmlspecialchars($error);
                            ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <!-- DELIVERY -->

                        <h5 class="mb-3">

                            Delivery Information

                        </h5>


                        <!-- NAME -->

                        <div class="mb-3">

                            <label class="form-label">

                                Full Name

                            </label>


                            <input
                                type="text"
                                name="shipping_name"
                                class="form-control"
                                value="<?php
                                echo htmlspecialchars(
                                    $_POST["shipping_name"]
                                    ?? $user["name"]
                                );
                                ?>"
                                required
                            >

                        </div>


                        <!-- PHONE -->

                        <div class="mb-3">

                            <label class="form-label">

                                Phone Number

                            </label>


                            <input
                                type="text"
                                name="shipping_phone"
                                class="form-control"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                value="<?php
                                echo htmlspecialchars(
                                    $_POST["shipping_phone"]
                                    ?? $user["phone"]
                                );
                                ?>"
                                required
                            >

                        </div>


                        <!-- ADDRESS -->

                        <div class="mb-3">

                            <label class="form-label">

                                Address

                            </label>


                            <textarea
                                name="shipping_address"
                                class="form-control"
                                rows="3"
                                required
                            ><?php
                            echo htmlspecialchars(
                                $_POST["shipping_address"]
                                ?? $user["address"]
                            );
                            ?></textarea>

                        </div>


                        <!-- CITY STATE PINCODE -->

                        <div class="row">


                            <div class="col-md-4 mb-3">

                                <label class="form-label">

                                    City

                                </label>


                                <input
                                    type="text"
                                    name="shipping_city"
                                    class="form-control"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["shipping_city"]
                                        ?? $user["city"]
                                    );
                                    ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-4 mb-3">

                                <label class="form-label">

                                    State

                                </label>


                                <input
                                    type="text"
                                    name="shipping_state"
                                    class="form-control"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["shipping_state"]
                                        ?? $user["state"]
                                    );
                                    ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-4 mb-3">

                                <label class="form-label">

                                    Pincode

                                </label>


                                <input
                                    type="text"
                                    name="shipping_pincode"
                                    class="form-control"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["shipping_pincode"]
                                        ?? $user["pincode"]
                                    );
                                    ?>"
                                    required
                                >

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- PAYMENT -->

                        <h5 class="mb-3">

                            Payment Method

                        </h5>


                        <div class="form-check mb-2">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="payment_method"
                                value="Cash on Delivery"
                                id="cod"
                                <?php
                                echo (
                                    ($_POST["payment_method"]
                                    ?? "Cash on Delivery")
                                    === "Cash on Delivery"
                                )
                                    ? "checked"
                                    : "";
                                ?>
                            >


                            <label
                                class="form-check-label"
                                for="cod"
                            >

                                <i class="bi bi-cash"></i>

                                Cash on Delivery

                            </label>

                        </div>


                        <div class="form-check mb-2">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="payment_method"
                                value="UPI"
                                id="upi"
                                <?php
                                echo (
                                    ($_POST["payment_method"]
                                    ?? "")
                                    === "UPI"
                                )
                                    ? "checked"
                                    : "";
                                ?>
                            >


                            <label
                                class="form-check-label"
                                for="upi"
                            >

                                <i class="bi bi-phone"></i>

                                UPI

                            </label>

                        </div>


                        <div class="form-check mb-4">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="payment_method"
                                value="Credit/Debit Card"
                                id="card"
                                <?php
                                echo (
                                    ($_POST["payment_method"]
                                    ?? "")
                                    === "Credit/Debit Card"
                                )
                                    ? "checked"
                                    : "";
                                ?>
                            >


                            <label
                                class="form-check-label"
                                for="card"
                            >

                                <i class="bi bi-credit-card"></i>

                                Credit/Debit Card

                            </label>

                        </div>


                        <!-- PLACE ORDER -->

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100"
                        >

                            <i class="bi bi-check-circle"></i>

                            Place Order

                        </button>


                    </form>

                </div>

            </div>

        </div>



        <!-- ================= ORDER SUMMARY ================= -->

        <div class="col-lg-4 mt-4 mt-lg-0">

            <div class="card shadow-sm border-0">

                <div class="card-body">


                    <h4 class="mb-4">

                        Order Summary

                    </h4>


                    <?php foreach ($cartItems as $item): ?>


                        <div class="d-flex align-items-center mb-3">


                            <?php if (!empty($item["image"])): ?>

                                <img
                                    src="assets/images/<?php
                                    echo htmlspecialchars(
                                        $item["image"]
                                    );
                                    ?>"
                                    width="60"
                                    height="60"
                                    class="rounded object-fit-cover"
                                    alt=""
                                >

                            <?php else: ?>

                                <div
                                    class="bg-light rounded d-flex align-items-center justify-content-center"
                                    style="width:60px;height:60px;"
                                >

                                    <i class="bi bi-image text-muted"></i>

                                </div>

                            <?php endif; ?>


                            <div class="ms-3">


                                <div class="fw-semibold">

                                    <?php
                                    echo htmlspecialchars(
                                        $item["name"]
                                    );
                                    ?>

                                </div>


                                <small class="text-muted">

                                    <?php
                                    echo (int)$item["quantity"];
                                    ?>

                                    × ₹<?php
                                    echo number_format(
                                        $item["price"],
                                        2
                                    );
                                    ?>

                                </small>


                            </div>

                        </div>


                    <?php endforeach; ?>


                    <hr>


                    <!-- SUBTOTAL -->

                    <div
                        class="d-flex justify-content-between mb-2"
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


                    <!-- SHIPPING -->

                    <div
                        class="d-flex justify-content-between mb-2"
                    >

                        <span>
                            Shipping
                        </span>


                        <strong>


                            <?php if ($shipping == 0): ?>

                                <span class="text-success">

                                    Free

                                </span>

                            <?php else: ?>

                                ₹<?php
                                echo number_format(
                                    $shipping,
                                    2
                                );
                                ?>

                            <?php endif; ?>


                        </strong>

                    </div>


                    <hr>


                    <!-- GRAND TOTAL -->

                    <div
                        class="d-flex justify-content-between"
                    >

                        <h5>

                            Grand Total

                        </h5>


                        <h5 class="text-primary">

                            ₹<?php
                            echo number_format(
                                $grandTotal,
                                2
                            );
                            ?>

                        </h5>

                    </div>


                </div>

            </div>

        </div>


    </div>

</div>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>