<?php

require_once __DIR__ . "/includes/auth.php";
requireLogin();

require_once __DIR__ . "/includes/db.php";

$userId = getCurrentUserId();

$orderId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($orderId <= 0) {
    header("Location: orders.php");
    exit;
}


/* =========================================
   GET ORDER
========================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        orders.*,
        users.name AS customer_name,
        users.email AS customer_email
     FROM orders
     INNER JOIN users
        ON orders.user_id = users.id
     WHERE orders.id = ?
       AND orders.user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $orderId,
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$order = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$order) {
    header("Location: orders.php");
    exit;
}


/* =========================================
   GET TRACKING HISTORY
========================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM order_tracking
     WHERE order_id = ?
     ORDER BY tracking_date ASC, id ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $orderId
);

mysqli_stmt_execute($stmt);

$trackingResult = mysqli_stmt_get_result($stmt);

mysqli_stmt_close($stmt);

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
        Track Order #<?= (int) $order["id"] ?> - EasyMart
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
            background: #f5f7fb;
        }

        .tracking-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        .tracking-item {
            position: relative;
            padding-left: 50px;
            padding-bottom: 30px;
        }

        .tracking-item:last-child {
            padding-bottom: 0;
        }

        .tracking-item::before {
            content: "";
            position: absolute;
            left: 15px;
            top: 35px;
            width: 2px;
            height: calc(100% - 5px);
            background: #dee2e6;
        }

        .tracking-item:last-child::before {
            display: none;
        }

        .tracking-icon {
            position: absolute;
            left: 0;
            top: 0;

            width: 32px;
            height: 32px;

            border-radius: 50%;

            background: #198754;
            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            z-index: 2;
        }

        .tracking-content {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
        }

        .info-box {
            background: #fff;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .status-badge {
            font-size: 14px;
        }

    </style>

</head>


<body>


<!-- =========================================
     NAVBAR
========================================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">

        <a
            class="navbar-brand fw-bold text-primary"
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

                        Profile

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



<!-- =========================================
     PAGE
========================================= -->

<div class="container py-5">


    <!-- HEADER -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold">

                <i class="bi bi-truck"></i>

                Track Your Order

            </h2>

            <p class="text-muted mb-0">

                Order #<?= (int) $order["id"] ?>

            </p>

        </div>


        <a
            href="orders.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            My Orders

        </a>

    </div>



    <!-- =========================================
         ORDER SUMMARY
    ========================================= -->

    <div class="card tracking-card mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">

                Order Information

            </h5>


            <div class="row g-3">


                <div class="col-md-3">

                    <div class="info-box">

                        <small class="text-muted">
                            Order ID
                        </small>

                        <h5 class="mb-0">

                            #<?= (int) $order["id"] ?>

                        </h5>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="info-box">

                        <small class="text-muted">
                            Order Date
                        </small>

                        <h6 class="mb-0">

                            <?= date(
                                "d M Y, h:i A",
                                strtotime($order["created_at"])
                            ) ?>

                        </h6>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="info-box">

                        <small class="text-muted">
                            Order Status
                        </small>

                        <div class="mt-1">

                            <?php

                            $status = $order["order_status"];

                            $badgeClass = "bg-secondary";

                            if ($status === "Pending") {
                                $badgeClass = "bg-warning text-dark";
                            }

                            if ($status === "Confirmed") {
                                $badgeClass = "bg-info text-dark";
                            }

                            if ($status === "Shipped") {
                                $badgeClass = "bg-primary";
                            }

                            if ($status === "Delivered") {
                                $badgeClass = "bg-success";
                            }

                            if ($status === "Cancelled") {
                                $badgeClass = "bg-danger";
                            }

                            ?>

                            <span
                                class="badge <?= $badgeClass ?> status-badge"
                            >

                                <?= htmlspecialchars($status) ?>

                            </span>

                        </div>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="info-box">

                        <small class="text-muted">
                            Total Amount
                        </small>

                        <h5 class="text-success mb-0">

                            ₹<?= number_format(
                                $order["total_amount"],
                                2
                            ) ?>

                        </h5>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================
         DELIVERY INFORMATION
    ========================================= -->

    <div class="card tracking-card mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">

                <i class="bi bi-box-seam"></i>

                Delivery Information

            </h5>


            <div class="row g-3">


                <div class="col-md-3">

                    <strong>Tracking Number</strong>

                    <p class="text-muted mb-0">

                        <?= !empty($order["tracking_number"])
                            ? htmlspecialchars($order["tracking_number"])
                            : "Not assigned yet" ?>

                    </p>

                </div>


                <div class="col-md-3">

                    <strong>Courier</strong>

                    <p class="text-muted mb-0">

                        <?= !empty($order["courier_name"])
                            ? htmlspecialchars($order["courier_name"])
                            : "Not assigned yet" ?>

                    </p>

                </div>


                <div class="col-md-3">

                    <strong>Current Location</strong>

                    <p class="text-muted mb-0">

                        <?= !empty($order["current_location"])
                            ? htmlspecialchars($order["current_location"])
                            : "Not updated yet" ?>

                    </p>

                </div>


                <div class="col-md-3">

                    <strong>Expected Delivery</strong>

                    <p class="text-muted mb-0">

                        <?php if (!empty($order["expected_delivery"])): ?>

                            <span class="text-success fw-bold">

                                <?= date(
                                    "d M Y",
                                    strtotime($order["expected_delivery"])
                                ) ?>

                            </span>

                        <?php else: ?>

                            Not available yet

                        <?php endif; ?>

                    </p>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================
         TRACKING HISTORY
    ========================================= -->

    <div class="card tracking-card">

        <div class="card-body">

            <h5 class="fw-bold mb-4">

                <i class="bi bi-clock-history"></i>

                Tracking History

            </h5>


            <?php if (mysqli_num_rows($trackingResult) > 0): ?>


                <?php while ($tracking = mysqli_fetch_assoc($trackingResult)): ?>


                    <div class="tracking-item">


                        <div class="tracking-icon">

                            <i class="bi bi-check-lg"></i>

                        </div>


                        <div class="tracking-content">


                            <div
                                class="d-flex justify-content-between flex-wrap"
                            >

                                <h6 class="fw-bold mb-1">

                                    <?= htmlspecialchars(
                                        $tracking["status"]
                                    ) ?>

                                </h6>


                                <small class="text-muted">

                                    <?= date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $tracking["tracking_date"]
                                        )
                                    ) ?>

                                </small>

                            </div>


                            <?php if (!empty($tracking["location"])): ?>

                                <p class="mb-1">

                                    <i class="bi bi-geo-alt-fill text-danger"></i>

                                    <strong>Location:</strong>

                                    <?= htmlspecialchars(
                                        $tracking["location"]
                                    ) ?>

                                </p>

                            <?php endif; ?>


                            <?php if (!empty($tracking["description"])): ?>

                                <p class="text-muted mb-0">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $tracking["description"]
                                        )
                                    ) ?>

                                </p>

                            <?php endif; ?>


                        </div>

                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="text-center py-5">

                    <i
                        class="bi bi-truck fs-1 text-muted"
                    ></i>

                    <h5 class="mt-3">

                        Tracking information not available yet.

                    </h5>

                    <p class="text-muted">

                        The seller will update your order tracking soon.

                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>



<!-- =========================================
     FOOTER
========================================= -->

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