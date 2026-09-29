<?php

$conn = require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$user_id = getCurrentUserId();

$message = "";
$error = "";

// Get user details
$userQuery = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($stmt);

// Get cart items
$cartQuery = "
    SELECT 
        cart.product_id,
        cart.quantity,
        products.name,
        products.price,
        products.stock,
        products.image
    FROM cart
    INNER JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = ?
";

$stmt = mysqli_prepare($conn, $cartQuery);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$cartResult = mysqli_stmt_get_result($stmt);

$cartItems = [];
$subtotal = 0;

while ($item = mysqli_fetch_assoc($cartResult)) {

    if ($item["stock"] <= 0) {
        continue;
    }

    $quantity = min((int)$item["quantity"], (int)$item["stock"]);

    $item["quantity"] = $quantity;

    $itemTotal = $item["price"] * $quantity;

    $subtotal += $itemTotal;

    $cartItems[] = $item;
}

mysqli_stmt_close($stmt);

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

// Shipping
$shipping = ($subtotal < 500) ? 50 : 0;

$grandTotal = $subtotal + $shipping;


// Place Order
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $shipping_name = trim($_POST["shipping_name"] ?? "");
    $shipping_phone = trim($_POST["shipping_phone"] ?? "");
    $shipping_address = trim($_POST["shipping_address"] ?? "");
    $shipping_city = trim($_POST["shipping_city"] ?? "");
    $shipping_state = trim($_POST["shipping_state"] ?? "");
    $shipping_pincode = trim($_POST["shipping_pincode"] ?? "");
    $payment_method = $_POST["payment_method"] ?? "Cash on Delivery";

    $allowedPayments = [
        "Cash on Delivery",
        "UPI",
        "Credit/Debit Card"
    ];

    // Validation
    if ($shipping_name === "") {
        $error = "Please enter your name.";
    } elseif (!preg_match("/^[0-9]{10}$/", $shipping_phone)) {
        $error = "Please enter a valid 10 digit phone number.";
    } elseif ($shipping_address === "") {
        $error = "Please enter your address.";
    } elseif ($shipping_city === "") {
        $error = "Please enter your city.";
    } elseif ($shipping_state === "") {
        $error = "Please enter your state.";
    } elseif (!preg_match("/^[0-9]{6}$/", $shipping_pincode)) {
        $error = "Please enter a valid 6 digit pincode.";
    } elseif (!in_array($payment_method, $allowedPayments)) {
        $error = "Invalid payment method.";
    }

    if ($error === "") {

        mysqli_begin_transaction($conn);

        try {

            // Re-check cart and stock
            $cartQuery = "
                SELECT 
                    cart.product_id,
                    cart.quantity,
                    products.price,
                    products.stock
                FROM cart
                INNER JOIN products ON cart.product_id = products.id
                WHERE cart.user_id = ?
                FOR UPDATE
            ";

            $stmt = mysqli_prepare($conn, $cartQuery);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $orderItems = [];
            $subtotal = 0;

            while ($item = mysqli_fetch_assoc($result)) {

                if ($item["stock"] <= 0) {
                    throw new Exception("One product is out of stock.");
                }

                $quantity = (int)$item["quantity"];

                if ($quantity > $item["stock"]) {
                    throw new Exception("Some product quantity is greater than available stock.");
                }

                $subtotal += $item["price"] * $quantity;

                $orderItems[] = [
                    "product_id" => $item["product_id"],
                    "quantity" => $quantity,
                    "price" => $item["price"]
                ];
            }

            mysqli_stmt_close($stmt);

            if (empty($orderItems)) {
                throw new Exception("Your cart is empty.");
            }

            $shipping = ($subtotal < 500) ? 50 : 0;
            $grandTotal = $subtotal + $shipping;


            // Insert order
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
                (?, ?, ?, 'pending', 'Pending', ?, ?, ?, ?, ?, ?)
            ";

            $stmt = mysqli_prepare($conn, $orderQuery);

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
                throw new Exception("Order creation failed.");
            }

            $order_id = mysqli_insert_id($conn);

            mysqli_stmt_close($stmt);


            // Insert order items
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

                $stmt = mysqli_prepare($conn, $itemQuery);

                mysqli_stmt_bind_param(
                    $stmt,
                    "iiid",
                    $order_id,
                    $item["product_id"],
                    $item["quantity"],
                    $item["price"]
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Order item insertion failed.");
                }

                mysqli_stmt_close($stmt);


                // Reduce stock
                $stockQuery = "
                    UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?
                    AND stock >= ?
                ";

                $stmt = mysqli_prepare($conn, $stockQuery);

                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $item["quantity"],
                    $item["product_id"],
                    $item["quantity"]
                );

                if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
                    throw new Exception("Stock update failed.");
                }

                mysqli_stmt_close($stmt);
            }


            // Clear cart
            $deleteCart = "DELETE FROM cart WHERE user_id = ?";

            $stmt = mysqli_prepare($conn, $deleteCart);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);


            mysqli_commit($conn);

            $_SESSION["last_order_id"] = $order_id;

            header("Location: order_success.php");
            exit;

        } catch (Exception $e) {

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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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


<!-- Navbar -->

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


<!-- Checkout -->

<div class="container py-5">

    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <h3 class="mb-4">

                        <i class="bi bi-credit-card"></i>

                        Checkout

                    </h3>


                    <?php if ($error !== ""): ?>

                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <h5 class="mb-3">
                            Delivery Information
                        </h5>


                        <div class="mb-3">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="shipping_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST["shipping_name"] ?? $user["name"]); ?>"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Phone Number
                            </label>

                            <input
                                type="text"
                                name="shipping_phone"
                                class="form-control"
                                maxlength="10"
                                value="<?php echo htmlspecialchars($_POST["shipping_phone"] ?? $user["phone"]); ?>"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Address
                            </label>

                            <textarea
                                name="shipping_address"
                                class="form-control"
                                rows="3"
                                required
                            ><?php echo htmlspecialchars($_POST["shipping_address"] ?? $user["address"]); ?></textarea>

                        </div>


                        <div class="row">

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    City
                                </label>

                                <input
                                    type="text"
                                    name="shipping_city"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($_POST["shipping_city"] ?? $user["city"]); ?>"
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
                                    value="<?php echo htmlspecialchars($_POST["shipping_state"] ?? $user["state"]); ?>"
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
                                    value="<?php echo htmlspecialchars($_POST["shipping_pincode"] ?? $user["pincode"]); ?>"
                                    required
                                >

                            </div>

                        </div>


                        <hr class="my-4">


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
                                checked
                            >

                            <label class="form-check-label" for="cod">

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
                            >

                            <label class="form-check-label" for="upi">

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
                            >

                            <label class="form-check-label" for="card">

                                <i class="bi bi-credit-card"></i>

                                Credit/Debit Card

                            </label>

                        </div>


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


        <!-- Order Summary -->

        <div class="col-lg-4 mt-4 mt-lg-0">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h4 class="mb-4">
                        Order Summary
                    </h4>


                    <?php foreach ($cartItems as $item): ?>

                        <div class="d-flex align-items-center mb-3">

                            <img
                                src="assets/images/<?php echo htmlspecialchars($item["image"]); ?>"
                                width="60"
                                height="60"
                                class="rounded object-fit-cover"
                                alt=""
                            >

                            <div class="ms-3">

                                <div class="fw-semibold">
                                    <?php echo htmlspecialchars($item["name"]); ?>
                                </div>

                                <small class="text-muted">

                                    <?php echo $item["quantity"]; ?>

                                    × ₹<?php echo number_format($item["price"], 2); ?>

                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>


                    <hr>


                    <div class="d-flex justify-content-between mb-2">

                        <span>
                            Subtotal
                        </span>

                        <strong>
                            ₹<?php echo number_format($subtotal, 2); ?>
                        </strong>

                    </div>


                    <div class="d-flex justify-content-between mb-2">

                        <span>
                            Shipping
                        </span>

                        <strong>

                            <?php if ($shipping == 0): ?>

                                <span class="text-success">
                                    Free
                                </span>

                            <?php else: ?>

                                ₹<?php echo number_format($shipping, 2); ?>

                            <?php endif; ?>

                        </strong>

                    </div>


                    <hr>


                    <div class="d-flex justify-content-between">

                        <h5>
                            Grand Total
                        </h5>

                        <h5 class="text-primary">

                            ₹<?php echo number_format($grandTotal, 2); ?>

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