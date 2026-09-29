<?php

$conn = require __DIR__ . "/includes/db.php";

$message = "";
$messageType = "";

$name = "";
$email = "";
$phone = "";
$address = "";
$city = "";
$state = "";
$pincode = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $address = trim($_POST["address"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $state = trim($_POST["state"] ?? "");
    $pincode = trim($_POST["pincode"] ?? "");

    // =========================
    // VALIDATION
    // =========================

    if (
        empty($name) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message = "Please fill all required fields.";
        $messageType = "danger";

    } elseif (strlen($name) < 2) {

        $message = "Name must contain at least 2 characters.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Phone number must contain exactly 10 digits.";
        $messageType = "danger";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";
        $messageType = "danger";

    } elseif ($password !== $confirm_password) {

        $message = "Password and confirm password do not match.";
        $messageType = "danger";

    } else {

        // =========================
        // CHECK DUPLICATE EMAIL
        // =========================

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $email
        );

        mysqli_stmt_execute($check);

        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {

            $message = "This email is already registered.";
            $messageType = "warning";

        } else {

            // =========================
            // HASH PASSWORD
            // =========================

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // =========================
            // INSERT USER
            // =========================

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (
                    name,
                    email,
                    phone,
                    password,
                    address,
                    city,
                    state,
                    pincode
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssss",
                $name,
                $email,
                $phone,
                $hashedPassword,
                $address,
                $city,
                $state,
                $pincode
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Registration successful! You can now login.";
                $messageType = "success";

                // Clear form
                $name = "";
                $email = "";
                $phone = "";
                $address = "";
                $city = "";
                $state = "";
                $pincode = "";

            } else {

                $message = "Registration failed. Please try again.";
                $messageType = "danger";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
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

    <title>Register - EasyMart</title>

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
            background: #f4f7fb;
        }

        .register-container {
            min-height: calc(100vh - 75px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }

        .register-card {
            width: 100%;
            max-width: 850px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.10);
        }

        .register-header {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #6610f2
            );
            color: white;
            padding: 30px;
            text-align: center;
        }

        .register-header h2 {
            font-weight: bold;
        }

        .form-control {
            padding: 12px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .register-btn {
            padding: 12px;
            font-size: 17px;
            font-weight: 600;
        }

        .required {
            color: red;
        }

    </style>

</head>

<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm">

    <div class="container">

        <a
            class="navbar-brand fw-bold text-primary fs-3"
            href="index.php"
        >
            <i class="bi bi-cart-check-fill"></i>
            EasyMart
        </a>

        <div>

            <a
                href="index.php"
                class="btn btn-outline-primary me-2"
            >
                <i class="bi bi-house"></i>
                Home
            </a>

            <a
                href="login.php"
                class="btn btn-primary"
            >
                Login
            </a>

        </div>

    </div>

</nav>


<!-- ================= REGISTER ================= -->

<div class="register-container">

    <div class="card register-card">

        <!-- Header -->

        <div class="register-header">

            <i
                class="bi bi-person-plus-fill"
                style="font-size: 45px;"
            ></i>

            <h2 class="mt-2">
                Create Your Account
            </h2>

            <p class="mb-0">
                Join EasyMart and start shopping
            </p>

        </div>


        <div class="card-body p-4 p-md-5">


            <!-- MESSAGE -->

            <?php if (!empty($message)) { ?>

                <div
                    class="alert alert-<?= $messageType ?> alert-dismissible fade show"
                >

                    <?= htmlspecialchars($message) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php } ?>


            <form
                method="POST"
                action=""
            >

                <div class="row g-3">


                    <!-- NAME -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            Full Name

                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter your full name"
                            value="<?= htmlspecialchars($name) ?>"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            Email Address

                            <span class="required">*</span>

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="example@gmail.com"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                        >

                    </div>


                    <!-- PHONE -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            Phone Number

                            <span class="required">*</span>

                        </label>

                        <input
                            type="tel"
                            name="phone"
                            class="form-control"
                            placeholder="10 digit mobile number"
                            value="<?= htmlspecialchars($phone) ?>"
                            maxlength="10"
                            pattern="[0-9]{10}"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            Password

                            <span class="required">*</span>

                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            required
                        >

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            Confirm Password

                            <span class="required">*</span>

                        </label>

                        <input
                            type="password"
                            name="confirm_password"
                            class="form-control"
                            placeholder="Re-enter password"
                            minlength="8"
                            required
                        >

                    </div>


                    <!-- CITY -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            City
                        </label>

                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            placeholder="Enter city"
                            value="<?= htmlspecialchars($city) ?>"
                        >

                    </div>


                    <!-- STATE -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            State
                        </label>

                        <input
                            type="text"
                            name="state"
                            class="form-control"
                            placeholder="Enter state"
                            value="<?= htmlspecialchars($state) ?>"
                        >

                    </div>


                    <!-- PINCODE -->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Pincode
                        </label>

                        <input
                            type="text"
                            name="pincode"
                            class="form-control"
                            placeholder="6 digit pincode"
                            value="<?= htmlspecialchars($pincode) ?>"
                            maxlength="6"
                        >

                    </div>


                    <!-- ADDRESS -->

                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Address
                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="3"
                            placeholder="Enter your complete address"
                        ><?= htmlspecialchars($address) ?></textarea>

                    </div>


                    <!-- BUTTON -->

                    <div class="col-12 mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary w-100 register-btn"
                        >

                            <i class="bi bi-person-plus"></i>

                            Create Account

                        </button>

                    </div>


                </div>

            </form>


            <!-- LOGIN -->

            <div class="text-center mt-4">

                Already have an account?

                <a
                    href="login.php"
                    class="text-primary fw-semibold text-decoration-none"
                >
                    Login here
                </a>

            </div>


        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>