<?php

require_once "includes/auth.php";
require_once "includes/db.php";

requireLogin();

$user_id = getCurrentUserId();

$order_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($order_id <= 0) {
    header("Location: orders.php");
    exit;
}


// Get order
$orderQuery = "
    SELECT *
    FROM orders
    WHERE id = ?
    AND user_id = ?
";

$stmt = mysqli_prepare($conn, $orderQuery);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $order_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$orderResult = mysqli_stmt_get_result($stmt);

$order = mysqli_fetch_assoc($orderResult);

mysqli_stmt_close($stmt);


if (!$order) {
    header("Location: orders.php");
    exit;
}


// Get order items
$itemsQuery = "
    SELECT
        order_items.id,
        order_items.quantity,
        order_items.price,
        products.name,
        products.image
    FROM order_items
    INNER JOIN products
        ON order_items.product_id = products.id
    WHERE order_items.order_id = ?
";

$stmt = mysqli_prepare($conn, $itemsQuery);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $order_id
);

mysqli_stmt_execute($stmt);

$itemsResult = mysqli_stmt_get_result($stmt);

$orderItems = [];

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $orderItems[] = $item;
}

mysqli_stmt_close($stmt);


// Calculate subtotal
$subtotal = 0;

foreach ($orderItems as $item) {
    $subtotal += $item["price"] * $item["quantity"];
}

$shipping = $order["total_amount"] - $subtotal;

if ($shipping < 0) {
    $shipping = 0;
}


// Status badge
$status = $order["order_status"];

if ($status === "Delivered") {

    $statusClass = "success";

} elseif ($status === "Cancelled") {

    $statusClass = "danger";

} elseif ($status === "Shipped") {

    $statusClass = "info";

} elseif ($status === "Confirmed") {

    $statusClass = "primary";

} else {

    $statusClass = "warning";
}


// Payment badge
if ($order["payment_status"] === "paid") {

    $paymentClass = "success";

} elseif ($order["payment_status"] === "failed") {

    $paymentClass = "danger";

} else {

    $paymentClass = "warning";
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
        Order #<?php echo $order["id"]; ?> - EasyMart
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


<!-- Navbar -->

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
                        class="nav-link active"
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



<!-- Main -->

<div class="container py-5">


    <!-- Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">

                <i class="bi bi-box-seam"></i>

                Order #<?php echo $order["id"]; ?>

            </h2>

            <p class="text-muted mb-0">

                Placed on

                <?php
                echo date(
                    "d M Y, h:i A",
                    strtotime($order["created_at"])
                );
                ?>

            </p>

        </div>


        <a
            href="orders.php"
            class="btn btn-outline-primary"
        >

            <i class="bi bi-arrow-left"></i>

            My Orders

        </a>

    </div>



    <!-- Order Status -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row text-center">


                <div class="col-md-4 mb-3 mb-md-0">

                    <small class="text-muted d-block">
                        Order Status
                    </small>

                    <span
                        class="badge bg-<?php echo $statusClass; ?> fs-6 mt-2"
                    >

                        <?php
                        echo htmlspecialchars(
                            $order["order_status"]
                        );
                        ?>

                    </span>

                </div>


                <div class="col-md-4 mb-3 mb-md-0">

                    <small class="text-muted d-block">
                        Payment Method
                    </small>

                    <strong class="d-block mt-2">

                        <?php
                        echo htmlspecialchars(
                            $order["payment_method"]
                        );
                        ?>

                    </strong>

                </div>


                <div class="col-md-4">

                    <small class="text-muted d-block">
                        Payment Status
                    </small>

                    <span
                        class="badge bg-<?php echo $paymentClass; ?> fs-6 mt-2"
                    >

                        <?php
                        echo ucfirst(
                            htmlspecialchars(
                                $order["payment_status"]
                            )
                        );
                        ?>

                    </span>

                </div>

            </div>

        </div>

    </div>



    <div class="row">


        <!-- Products -->

        <div class="col-lg-8 mb-4">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-bag"></i>

                        Ordered Products

                    </h5>

                </div>


                <div class="card-body">


                    <?php if (empty($orderItems)): ?>

                        <div class="alert alert-warning mb-0">

                            No products found for this order.

                        </div>

                    <?php else: ?>


                        <?php foreach ($orderItems as $item): ?>

                            <div class="row align-items-center border-bottom py-3">


                                <!-- Image -->

                                <div class="col-3 col-md-2">

                                    <img
                                        src="assets/images/<?php echo htmlspecialchars($item["image"]); ?>"
                                        alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                        class="img-fluid rounded"
                                        style="width: 80px; height: 80px; object-fit: cover;"
                                    >

                                </div>


                                <!-- Product -->

                                <div class="col-9 col-md-5">

                                    <h6 class="mb-1">

                                        <?php
                                        echo htmlspecialchars(
                                            $item["name"]
                                        );
                                        ?>

                                    </h6>

                                    <small class="text-muted">

                                        Quantity:

                                        <?php
                                        echo $item["quantity"];
                                        ?>

                                    </small>

                                </div>


                                <!-- Price -->

                                <div class="col-6 col-md-2 mt-3 mt-md-0">

                                    <small class="text-muted d-block">
                                        Price
                                    </small>

                                    ₹<?php
                                    echo number_format(
                                        $item["price"],
                                        2
                                    );
                                    ?>

                                </div>


                                <!-- Total -->

                                <div class="col-6 col-md-3 text-md-end mt-3 mt-md-0">

                                    <small class="text-muted d-block">
                                        Total
                                    </small>

                                    <strong>

                                        ₹<?php
                                        echo number_format(
                                            $item["price"] * $item["quantity"],
                                            2
                                        );
                                        ?>

                                    </strong>

                                </div>

                            </div>

                        <?php endforeach; ?>


                    <?php endif; ?>


                </div>

            </div>

        </div>



        <!-- Order Summary -->

        <div class="col-lg-4 mb-4">

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-receipt"></i>

                        Order Summary

                    </h5>

                </div>


                <div class="card-body">


                    <div class="d-flex justify-content-between mb-2">

                        <span>
                            Subtotal
                        </span>

                        <span>

                            ₹<?php
                            echo number_format(
                                $subtotal,
                                2
                            );
                            ?>

                        </span>

                    </div>


                    <div class="d-flex justify-content-between mb-2">

                        <span>
                            Shipping
                        </span>

                        <span>

                            <?php if ($shipping <= 0): ?>

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

                        </span>

                    </div>


                    <hr>


                    <div class="d-flex justify-content-between">

                        <strong>
                            Grand Total
                        </strong>

                        <strong class="text-primary fs-5">

                            ₹<?php
                            echo number_format(
                                $order["total_amount"],
                                2
                            );
                            ?>

                        </strong>

                    </div>

                </div>

            </div>



            <!-- Delivery Address -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-0">

                        <i class="bi bi-geo-alt"></i>

                        Delivery Address

                    </h5>

                </div>


                <div class="card-body">

                    <h6>

                        <?php
                        echo htmlspecialchars(
                            $order["shipping_name"]
                        );
                        ?>

                    </h6>


                    <p class="mb-1">

                        <i class="bi bi-telephone"></i>

                        <?php
                        echo htmlspecialchars(
                            $order["shipping_phone"]
                        );
                        ?>

                    </p>


                    <p class="mb-1">

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $order["shipping_address"]
                            )
                        );
                        ?>

                    </p>


                    <p class="mb-0">

                        <?php
                        echo htmlspecialchars(
                            $order["shipping_city"]
                        );
                        ?>,

                        <?php
                        echo htmlspecialchars(
                            $order["shipping_state"]
                        );
                        ?>

                        -

                        <?php
                        echo htmlspecialchars(
                            $order["shipping_pincode"]
                        );
                        ?>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- Footer -->

<footer class="bg-dark text-white py-4">

    <div class="container text-center">

        <p class="mb-0">

            © <?php echo date("Y"); ?> EasyMart.
            All Rights Reserved.

        </p>

    </div>

</footer>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>