<?php

require_once "includes/auth.php";
require_once "includes/db.php";

requireLogin();

$user_id = getCurrentUserId();


// Fetch user's orders
$query = "
    SELECT
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
        tracking_number,
        courier_name,
        current_location,
        expected_delivery,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders - EasyMart</title>

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

        <a class="navbar-brand text-primary fw-bold" href="index.php">

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


        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto">


                <li class="nav-item">

                    <a class="nav-link" href="index.php">

                        Home

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="shop.php">

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


                <li class="nav-item">

                    <a class="nav-link active" href="orders.php">

                        <i class="bi bi-box-seam"></i>

                        My Orders

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="profile.php">

                        <i class="bi bi-person-circle"></i>

                        <?php echo htmlspecialchars(getCurrentUserName()); ?>

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link text-danger" href="logout.php">

                        Logout

                    </a>

                </li>


            </ul>

        </div>

    </div>

</nav>



<!-- ================= MAIN CONTENT ================= -->

<div class="container py-5">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">

                <i class="bi bi-box-seam"></i>

                My Orders

            </h2>

            <p class="text-muted mb-0">

                View and track your orders

            </p>

        </div>

    </div>



    <?php if (empty($orders)): ?>


        <!-- ================= NO ORDERS ================= -->

        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <i
                    class="bi bi-box-seam text-muted"
                    style="font-size: 70px;"
                ></i>

                <h4 class="mt-3">

                    No Orders Found

                </h4>

                <p class="text-muted">

                    You have not placed any orders yet.

                </p>

                <a
                    href="shop.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-shop"></i>

                    Start Shopping

                </a>

            </div>

        </div>


    <?php else: ?>


        <!-- ================= ORDERS TABLE ================= -->

        <div class="card border-0 shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">


                        <thead class="table-light">

                            <tr>

                                <th class="px-4">
                                    Order ID
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Payment
                                </th>

                                <th>
                                    Payment Status
                                </th>

                                <th>
                                    Order Status
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Tracking
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>



                        <tbody>


                            <?php foreach ($orders as $order): ?>


                                <tr>


                                    <!-- ORDER ID -->

                                    <td class="px-4">

                                        <strong>

                                            #<?php echo (int) $order["id"]; ?>

                                        </strong>

                                    </td>



                                    <!-- DATE -->

                                    <td>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime($order["created_at"])
                                        );
                                        ?>

                                        <br>

                                        <small class="text-muted">

                                            <?php
                                            echo date(
                                                "h:i A",
                                                strtotime($order["created_at"])
                                            );
                                            ?>

                                        </small>

                                    </td>



                                    <!-- PAYMENT METHOD -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $order["payment_method"]
                                        );
                                        ?>

                                    </td>



                                    <!-- PAYMENT STATUS -->

                                    <td>

                                        <?php if ($order["payment_status"] === "paid"): ?>


                                            <span class="badge bg-success">

                                                <i class="bi bi-check-circle"></i>

                                                Paid

                                            </span>


                                        <?php elseif ($order["payment_status"] === "failed"): ?>


                                            <span class="badge bg-danger">

                                                <i class="bi bi-x-circle"></i>

                                                Failed

                                            </span>


                                        <?php else: ?>


                                            <span class="badge bg-warning text-dark">

                                                <i class="bi bi-clock"></i>

                                                Pending

                                            </span>


                                        <?php endif; ?>

                                    </td>



                                    <!-- ORDER STATUS -->

                                    <td>

                                        <?php

                                        $status = $order["order_status"];

                                        if ($status === "Delivered") {

                                            $badge = "success";

                                        } elseif ($status === "Cancelled") {

                                            $badge = "danger";

                                        } elseif ($status === "Shipped") {

                                            $badge = "info";

                                        } elseif ($status === "Confirmed") {

                                            $badge = "primary";

                                        } else {

                                            $badge = "warning";

                                        }

                                        ?>


                                        <span class="badge bg-<?php echo $badge; ?>">

                                            <?php
                                            echo htmlspecialchars($status);
                                            ?>

                                        </span>

                                    </td>



                                    <!-- TOTAL -->

                                    <td>

                                        <strong>

                                            ₹<?php
                                            echo number_format(
                                                $order["total_amount"],
                                                2
                                            );
                                            ?>

                                        </strong>

                                    </td>



                                    <!-- TRACKING -->

                                    <td>

                                        <?php if (!empty($order["tracking_number"])): ?>


                                            <div>

                                                <span class="badge bg-success mb-1">

                                                    <i class="bi bi-truck"></i>

                                                    Tracking Available

                                                </span>

                                            </div>


                                            <small class="text-muted">

                                                <?php
                                                echo htmlspecialchars(
                                                    $order["tracking_number"]
                                                );
                                                ?>

                                            </small>


                                        <?php else: ?>


                                            <span class="badge bg-secondary">

                                                <i class="bi bi-hourglass-split"></i>

                                                Not Available

                                            </span>


                                        <?php endif; ?>

                                    </td>



                                    <!-- ACTIONS -->

                                    <td>


                                        <div class="d-flex flex-column gap-2">


                                            <!-- VIEW DETAILS -->

                                            <a
                                                href="order_details.php?id=<?php echo (int) $order["id"]; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >

                                                <i class="bi bi-eye"></i>

                                                View Details

                                            </a>



                                            <!-- TRACK ORDER -->

                                            <a
                                                href="track_order.php?id=<?php echo (int) $order["id"]; ?>"
                                                class="btn btn-sm btn-primary"
                                            >

                                                <i class="bi bi-truck"></i>

                                                Track Order

                                            </a>


                                        </div>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>

            </div>

        </div>



        <!-- ================= TRACKING INFORMATION ================= -->

        <div class="row mt-4">


            <?php foreach ($orders as $order): ?>


                <?php if (!empty($order["tracking_number"])): ?>


                    <div class="col-lg-6 mb-4">


                        <div class="card border-0 shadow-sm h-100">


                            <div class="card-header bg-white">

                                <h5 class="mb-0">

                                    <i class="bi bi-truck text-primary"></i>

                                    Order #<?php echo (int) $order["id"]; ?>

                                </h5>

                            </div>


                            <div class="card-body">


                                <div class="row g-3">


                                    <!-- TRACKING NUMBER -->

                                    <div class="col-md-6">

                                        <small class="text-muted d-block">

                                            Tracking Number

                                        </small>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $order["tracking_number"]
                                            );
                                            ?>

                                        </strong>

                                    </div>



                                    <!-- COURIER -->

                                    <div class="col-md-6">

                                        <small class="text-muted d-block">

                                            Courier

                                        </small>

                                        <strong>

                                            <?php

                                            if (!empty($order["courier_name"])) {

                                                echo htmlspecialchars(
                                                    $order["courier_name"]
                                                );

                                            } else {

                                                echo "Not Assigned";

                                            }

                                            ?>

                                        </strong>

                                    </div>



                                    <!-- CURRENT LOCATION -->

                                    <div class="col-md-6">

                                        <small class="text-muted d-block">

                                            Current Location

                                        </small>

                                        <strong>

                                            <?php

                                            if (!empty($order["current_location"])) {

                                                echo htmlspecialchars(
                                                    $order["current_location"]
                                                );

                                            } else {

                                                echo "Not Available";

                                            }

                                            ?>

                                        </strong>

                                    </div>



                                    <!-- EXPECTED DELIVERY -->

                                    <div class="col-md-6">

                                        <small class="text-muted d-block">

                                            Expected Delivery

                                        </small>

                                        <strong>

                                            <?php

                                            if (!empty($order["expected_delivery"])) {

                                                echo date(
                                                    "d M Y",
                                                    strtotime(
                                                        $order["expected_delivery"]
                                                    )
                                                );

                                            } else {

                                                echo "Not Available";

                                            }

                                            ?>

                                        </strong>

                                    </div>


                                </div>



                                <hr>



                                <div class="d-flex justify-content-between align-items-center">


                                    <div>

                                        <small class="text-muted">

                                            Order Status

                                        </small>

                                        <br>


                                        <span class="badge bg-<?php echo $badge; ?>">

                                            <?php
                                            echo htmlspecialchars(
                                                $order["order_status"]
                                            );
                                            ?>

                                        </span>

                                    </div>



                                    <a
                                        href="track_order.php?id=<?php echo (int) $order["id"]; ?>"
                                        class="btn btn-primary"
                                    >

                                        <i class="bi bi-geo-alt"></i>

                                        View Tracking

                                    </a>


                                </div>


                            </div>

                        </div>

                    </div>


                <?php endif; ?>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>



<!-- ================= FOOTER ================= -->

<footer class="bg-dark text-white mt-5">

    <div class="container py-4 text-center">

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