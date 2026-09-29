<?php

require_once "includes/auth.php";
$conn = require_once "includes/db.php";
requireLogin();


// Get order ID

$order_id = $_SESSION["last_order_id"] ?? 0;

if (!$order_id || !is_numeric($order_id)) {

    header("Location: index.php");
    exit;
}


$user_id = getCurrentUserId();


// Fetch order

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        total_amount,
        payment_method,
        payment_status,
        order_status,
        shipping_name,
        shipping_phone,
        shipping_address,
        shipping_city,
        shipping_state,
        shipping_pincode,
        created_at
     FROM orders
     WHERE id = ?
     AND user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $order_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$order = mysqli_fetch_assoc($result);


if (!$order) {

    header("Location: index.php");
    exit;
}


// Clear temporary session

unset($_SESSION["last_order_id"]);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Order Successful - EasyMart</title>


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
            data-bs-target="#navbarMenu"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div
            class="collapse navbar-collapse"
            id="navbarMenu"
        >

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php"
                    >

                        <i class="bi bi-house"></i>

                        Home

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="shop.php"
                    >

                        <i class="bi bi-shop"></i>

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
                        href="profile.php"
                    >

                        <i class="bi bi-person-circle text-primary"></i>

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

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>


<!-- ================= SUCCESS ================= -->

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5 px-4">


                    <!-- Success Icon -->

                    <i
                        class="bi bi-check-circle-fill text-success"
                        style="font-size:90px;"
                    ></i>


                    <h1 class="fw-bold text-success mt-4">

                        Order Placed Successfully!

                    </h1>


                    <p class="text-muted fs-5">

                        Thank you for shopping with EasyMart.

                    </p>


                    <!-- Order ID -->

                    <div class="alert alert-success mt-4">

                        <strong>
                            Order ID:
                        </strong>

                        #<?php echo $order["id"]; ?>

                    </div>


                    <hr>


                    <!-- Order Information -->

                    <div class="row g-3 text-start mt-3">


                        <div class="col-md-6">

                            <div class="border rounded p-3">

                                <small class="text-muted">
                                    Order Date
                                </small>

                                <div class="fw-semibold">

                                    <?php
                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $order["created_at"]
                                        )
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="border rounded p-3">

                                <small class="text-muted">
                                    Payment Method
                                </small>

                                <div class="fw-semibold">

                                    <?php
                                    echo htmlspecialchars(
                                        $order["payment_method"]
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="border rounded p-3">

                                <small class="text-muted">
                                    Order Status
                                </small>

                                <div>

                                    <span class="badge bg-warning text-dark">

                                        <?php
                                        echo htmlspecialchars(
                                            $order["order_status"]
                                        );
                                        ?>

                                    </span>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="border rounded p-3">

                                <small class="text-muted">
                                    Payment Status
                                </small>

                                <div>

                                    <span class="badge bg-secondary">

                                        <?php
                                        echo htmlspecialchars(
                                            $order["payment_status"]
                                        );
                                        ?>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Address -->

                    <div class="text-start mt-4">

                        <h5 class="fw-bold">

                            <i class="bi bi-geo-alt"></i>

                            Delivery Address

                        </h5>


                        <div class="border rounded p-3 bg-light">

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $order["shipping_name"]
                                );
                                ?>

                            </strong>

                            <br>

                            <?php
                            echo htmlspecialchars(
                                $order["shipping_phone"]
                            );
                            ?>

                            <br>

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $order["shipping_address"]
                                )
                            );
                            ?>

                            <br>

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

                        </div>

                    </div>


                    <!-- Total -->

                    <div
                        class="d-flex justify-content-between align-items-center bg-light rounded p-3 mt-4"
                    >

                        <span class="fw-bold fs-5">
                            Order Total
                        </span>

                        <strong class="text-success fs-3">

                            ₹<?php
                            echo number_format(
                                $order["total_amount"],
                                2
                            );
                            ?>

                        </strong>

                    </div>


                    <!-- Buttons -->

                    <div class="mt-4">

                        <a
                            href="shop.php"
                            class="btn btn-primary btn-lg me-2"
                        >

                            <i class="bi bi-shop"></i>

                            Continue Shopping

                        </a>


                        <a
                            href="index.php"
                            class="btn btn-outline-secondary btn-lg"
                        >

                            <i class="bi bi-house"></i>

                            Home

                        </a>

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