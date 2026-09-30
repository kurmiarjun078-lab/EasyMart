<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";

$message = "";
$error = "";


/* =========================================
   UPDATE TRACKING
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_tracking"])) {

    $orderId = (int) ($_POST["order_id"] ?? 0);

    $trackingNumber = trim($_POST["tracking_number"] ?? "");
    $courierName = trim($_POST["courier_name"] ?? "");
    $currentLocation = trim($_POST["current_location"] ?? "");
    $expectedDelivery = trim($_POST["expected_delivery"] ?? "");

    $trackingStatus = trim($_POST["tracking_status"] ?? "");
    $description = trim($_POST["description"] ?? "");


    if ($orderId <= 0) {

        $error = "Invalid Order ID.";

    } else {

        /* -----------------------------------------
           Update orders table
        ----------------------------------------- */

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE orders
             SET tracking_number = ?,
                 courier_name = ?,
                 current_location = ?,
                 expected_delivery = ?,
                 order_status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssssi",
            $trackingNumber,
            $courierName,
            $currentLocation,
            $expectedDelivery,
            $trackingStatus,
            $orderId
        );

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);


            /* -----------------------------------------
               Add tracking history
            ----------------------------------------- */

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO order_tracking
                (order_id, status, location, description)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "isss",
                $orderId,
                $trackingStatus,
                $currentLocation,
                $description
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: orders.php?updated=1"
                );

                exit;

            } else {

                mysqli_stmt_close($stmt);

                $error = "Order updated but tracking history could not be saved.";
            }

        } else {

            mysqli_stmt_close($stmt);

            $error = "Failed to update order tracking.";
        }
    }
}


/* =========================================
   SUCCESS MESSAGE
========================================= */

if (isset($_GET["updated"]) && $_GET["updated"] == "1") {

    $message = "Order tracking updated successfully.";
}


/* =========================================
   GET ALL ORDERS
========================================= */

$query = "
    SELECT
        orders.*,
        users.name AS customer_name,
        users.email AS customer_email
    FROM orders

    INNER JOIN users
        ON orders.user_id = users.id

    ORDER BY orders.id DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Orders - EasyMart Admin</title>


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

        .navbar-brand {
            font-weight: bold;
        }

        .order-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .tracking-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .status-badge {
            font-size: 13px;
        }

    </style>

</head>


<body>


<!-- =========================================
     NAVBAR
========================================= -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >

            <i class="bi bi-cart-check-fill"></i>

            EasyMart Admin

        </a>


        <div class="d-flex align-items-center">

            <span class="text-white me-3">

                <i class="bi bi-person-circle"></i>

                <?= htmlspecialchars(getAdminName()) ?>

            </span>


            <a
                href="logout.php"
                class="btn btn-danger btn-sm"
            >

                <i class="bi bi-box-arrow-right"></i>

                Logout

            </a>

        </div>

    </div>

</nav>



<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="container py-4">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">

                <i class="bi bi-box-seam"></i>

                Manage Orders

            </h2>

            <p class="text-muted mb-0">

                Manage orders and update delivery tracking.

            </p>

        </div>


        <a
            href="dashboard.php"
            class="btn btn-outline-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Dashboard

        </a>

    </div>



    <!-- SUCCESS -->

    <?php if ($message !== ""): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle-fill"></i>

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>



    <!-- ERROR -->

    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>



    <!-- =========================================
         ORDERS
    ========================================= -->

    <?php if (mysqli_num_rows($result) > 0): ?>


        <div class="row g-4">


            <?php while ($order = mysqli_fetch_assoc($result)): ?>


                <div class="col-12">

                    <div class="card order-card">

                        <div class="card-body">


                            <!-- ORDER HEADER -->

                            <div
                                class="d-flex justify-content-between align-items-center flex-wrap mb-3"
                            >

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        Order #<?= (int) $order["id"] ?>

                                    </h5>

                                    <small class="text-muted">

                                        <?= htmlspecialchars($order["created_at"]) ?>

                                    </small>

                                </div>


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



                            <!-- ORDER INFORMATION -->

                            <div class="row g-3">


                                <div class="col-md-4">

                                    <strong>Customer</strong>

                                    <p class="mb-0">

                                        <?= htmlspecialchars($order["customer_name"]) ?>

                                    </p>

                                    <small class="text-muted">

                                        <?= htmlspecialchars($order["customer_email"]) ?>

                                    </small>

                                </div>


                                <div class="col-md-4">

                                    <strong>Total Amount</strong>

                                    <p class="text-success fw-bold mb-0">

                                        ₹<?= number_format(
                                            $order["total_amount"],
                                            2
                                        ) ?>

                                    </p>

                                </div>


                                <div class="col-md-4">

                                    <strong>Payment</strong>

                                    <p class="mb-0">

                                        <?= htmlspecialchars(
                                            $order["payment_method"]
                                        ) ?>

                                    </p>

                                    <small>

                                        <?= htmlspecialchars(
                                            $order["payment_status"]
                                        ) ?>

                                    </small>

                                </div>

                            </div>



                            <hr>



                            <!-- CURRENT TRACKING -->

                            <div class="tracking-box mb-3">

                                <h6 class="fw-bold">

                                    <i class="bi bi-truck"></i>

                                    Current Tracking

                                </h6>


                                <div class="row g-3">


                                    <div class="col-md-3">

                                        <small class="text-muted">
                                            Tracking Number
                                        </small>

                                        <div>

                                            <?= !empty($order["tracking_number"])
                                                ? htmlspecialchars($order["tracking_number"])
                                                : "Not assigned" ?>

                                        </div>

                                    </div>


                                    <div class="col-md-3">

                                        <small class="text-muted">
                                            Courier
                                        </small>

                                        <div>

                                            <?= !empty($order["courier_name"])
                                                ? htmlspecialchars($order["courier_name"])
                                                : "Not assigned" ?>

                                        </div>

                                    </div>


                                    <div class="col-md-3">

                                        <small class="text-muted">
                                            Current Location
                                        </small>

                                        <div>

                                            <?= !empty($order["current_location"])
                                                ? htmlspecialchars($order["current_location"])
                                                : "Not updated" ?>

                                        </div>

                                    </div>


                                    <div class="col-md-3">

                                        <small class="text-muted">
                                            Expected Delivery
                                        </small>

                                        <div>

                                            <?php if (!empty($order["expected_delivery"])): ?>

                                                <?= date(
                                                    "d M Y",
                                                    strtotime($order["expected_delivery"])
                                                ) ?>

                                            <?php else: ?>

                                                Not set

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </div>

                            </div>



                            <!-- BUTTONS -->

                            <div class="d-flex gap-2 flex-wrap">


                                <a
                                    href="../order_details.php?id=<?= (int) $order["id"] ?>"
                                    class="btn btn-outline-primary"
                                    target="_blank"
                                >

                                    <i class="bi bi-eye"></i>

                                    View Order

                                </a>


                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#trackingModal<?= (int) $order["id"] ?>"
                                >

                                    <i class="bi bi-truck"></i>

                                    Update Tracking

                                </button>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- =========================================
                     TRACKING MODAL
                ========================================= -->

                <div
                    class="modal fade"
                    id="trackingModal<?= (int) $order["id"] ?>"
                    tabindex="-1"
                >

                    <div class="modal-dialog modal-lg">

                        <div class="modal-content">


                            <div class="modal-header">

                                <h5 class="modal-title">

                                    <i class="bi bi-truck"></i>

                                    Update Tracking -
                                    Order #<?= (int) $order["id"] ?>

                                </h5>


                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                ></button>

                            </div>



                            <form method="POST">


                                <div class="modal-body">


                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?= (int) $order["id"] ?>"
                                    >


                                    <!-- TRACKING NUMBER -->

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">

                                            Tracking Number

                                        </label>

                                        <input
                                            type="text"
                                            name="tracking_number"
                                            class="form-control"
                                            value="<?= htmlspecialchars(
                                                $order["tracking_number"] ?? ""
                                            ) ?>"
                                            placeholder="Example: EM123456789"
                                        >

                                    </div>



                                    <div class="row g-3">


                                        <!-- COURIER -->

                                        <div class="col-md-6">

                                            <label class="form-label fw-bold">

                                                Courier Name

                                            </label>

                                            <input
                                                type="text"
                                                name="courier_name"
                                                class="form-control"
                                                value="<?= htmlspecialchars(
                                                    $order["courier_name"] ?? ""
                                                ) ?>"
                                                placeholder="Example: EasyMart Express"
                                            >

                                        </div>



                                        <!-- LOCATION -->

                                        <div class="col-md-6">

                                            <label class="form-label fw-bold">

                                                Current Location

                                            </label>

                                            <input
                                                type="text"
                                                name="current_location"
                                                class="form-control"
                                                value="<?= htmlspecialchars(
                                                    $order["current_location"] ?? ""
                                                ) ?>"
                                                placeholder="Example: Rajkot Hub"
                                            >

                                        </div>



                                        <!-- DELIVERY DATE -->

                                        <div class="col-md-6">

                                            <label class="form-label fw-bold">

                                                Expected Delivery

                                            </label>

                                            <input
                                                type="date"
                                                name="expected_delivery"
                                                class="form-control"
                                                value="<?= htmlspecialchars(
                                                    $order["expected_delivery"] ?? ""
                                                ) ?>"
                                            >

                                        </div>



                                        <!-- STATUS -->

                                        <div class="col-md-6">

                                            <label class="form-label fw-bold">

                                                Tracking Status

                                            </label>

                                            <select
                                                name="tracking_status"
                                                class="form-select"
                                                required
                                            >

                                                <option value="Pending"
                                                    <?= $order["order_status"] === "Pending" ? "selected" : "" ?>>
                                                    Pending
                                                </option>

                                                <option value="Confirmed"
                                                    <?= $order["order_status"] === "Confirmed" ? "selected" : "" ?>>
                                                    Confirmed
                                                </option>

                                                <option value="Shipped"
                                                    <?= $order["order_status"] === "Shipped" ? "selected" : "" ?>>
                                                    Shipped
                                                </option>

                                                <option value="Delivered"
                                                    <?= $order["order_status"] === "Delivered" ? "selected" : "" ?>>
                                                    Delivered
                                                </option>

                                                <option value="Cancelled"
                                                    <?= $order["order_status"] === "Cancelled" ? "selected" : "" ?>>
                                                    Cancelled
                                                </option>

                                            </select>

                                        </div>

                                    </div>



                                    <!-- DESCRIPTION -->

                                    <div class="mt-3">

                                        <label class="form-label fw-bold">

                                            Tracking Description

                                        </label>

                                        <textarea
                                            name="description"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Example: Package has reached Rajkot Hub."
                                            required
                                        ></textarea>

                                    </div>


                                </div>



                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal"
                                    >

                                        Cancel

                                    </button>


                                    <button
                                        type="submit"
                                        name="update_tracking"
                                        class="btn btn-primary"
                                    >

                                        <i class="bi bi-save"></i>

                                        Save Tracking

                                    </button>

                                </div>


                            </form>

                        </div>

                    </div>

                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="alert alert-info">

            <i class="bi bi-info-circle"></i>

            No orders found.

        </div>


    <?php endif; ?>


</div>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>