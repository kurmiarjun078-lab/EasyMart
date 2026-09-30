<?php

require_once __DIR__ . "/includes/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = "";
$error = "";

$name = "";
$email = "";
$subject = "";
$message = "";


// If user is logged in, get name and email
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    $name = $_SESSION["user_name"] ?? "";
    $email = $_SESSION["user_email"] ?? "";
}


// Submit contact form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");


    // Validation
    if ($name === "" || $email === "" || $message === "") {

        $error = "Please fill all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO contact_messages
            (name, email, subject, message)
            VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $name,
            $email,
            $subject,
            $message
        );


        if (mysqli_stmt_execute($stmt)) {

            $success = "Your message has been sent successfully!";

            // Clear form
            $subject = "";
            $message = "";

        } else {

            $error = "Something went wrong. Please try again.";

        }

        mysqli_stmt_close($stmt);
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

    <title>Contact Us - EasyMart</title>


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
            background: #f8f9fa;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 24px;
        }

        .contact-section {
            padding: 60px 0;
        }

        .contact-card {
            border: none;
            border-radius: 15px;
        }

        .contact-info {
            background: #212529;
            color: white;
            border-radius: 15px;
            height: 100%;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            color: #212529;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .form-control {
            padding: 12px;
        }

        textarea.form-control {
            min-height: 150px;
        }

    </style>

</head>


<body>


<!-- Navbar -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">


        <!-- Brand -->

        <a
            class="navbar-brand text-dark"
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
            data-bs-target="#navbarNav"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- Menu -->

        <div
            class="collapse navbar-collapse"
            id="navbarNav"
        >

            <ul class="navbar-nav ms-auto align-items-lg-center">


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
                        href="category.php"
                    >

                        Categories

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link active"
                        href="contact.php"
                    >

                        Contact

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


                <?php if (
                    isset($_SESSION["logged_in"]) &&
                    $_SESSION["logged_in"] === true
                ): ?>


                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                        >

                            <i class="bi bi-person-circle"></i>

                            <?= htmlspecialchars(
                                $_SESSION["user_name"] ?? "Account"
                            ); ?>

                        </a>


                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>

                                <a
                                    class="dropdown-item"
                                    href="profile.php"
                                >

                                    <i class="bi bi-person"></i>

                                    Profile

                                </a>

                            </li>


                            <li>

                                <a
                                    class="dropdown-item"
                                    href="orders.php"
                                >

                                    <i class="bi bi-box-seam"></i>

                                    My Orders

                                </a>

                            </li>


                            <li>

                                <a
                                    class="dropdown-item"
                                    href="wishlist.php"
                                >

                                    <i class="bi bi-heart"></i>

                                    Wishlist

                                </a>

                            </li>


                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            <li>

                                <a
                                    class="dropdown-item text-danger"
                                    href="logout.php"
                                >

                                    <i class="bi bi-box-arrow-right"></i>

                                    Logout

                                </a>

                            </li>

                        </ul>

                    </li>


                <?php else: ?>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="login.php"
                        >

                            <i class="bi bi-person"></i>

                            Login

                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="btn btn-dark ms-lg-2"
                            href="register.php"
                        >

                            Register

                        </a>

                    </li>


                <?php endif; ?>


            </ul>

        </div>

    </div>

</nav>



<!-- Contact Section -->

<section class="contact-section">

    <div class="container">


        <!-- Heading -->

        <div class="text-center mb-5">

            <h1 class="fw-bold">

                Contact Us

            </h1>

            <p class="text-muted">

                Have any questions? We would love to hear from you.

            </p>

        </div>



        <div class="row g-4">


            <!-- Contact Information -->

            <div class="col-lg-5">

                <div class="contact-info p-4 p-lg-5">

                    <h3 class="mb-4">

                        Get In Touch

                    </h3>


                    <p class="text-light">

                        If you have any questions about our products,
                        orders or services, feel free to contact us.

                    </p>


                    <div class="d-flex align-items-center mb-4 mt-4">

                        <div class="contact-icon me-3">

                            <i class="bi bi-geo-alt"></i>

                        </div>

                        <div>

                            <h6 class="mb-1">

                                Address

                            </h6>

                            <span class="text-light">

                                EasyMart Store, India

                            </span>

                        </div>

                    </div>


                    <div class="d-flex align-items-center mb-4">

                        <div class="contact-icon me-3">

                            <i class="bi bi-telephone"></i>

                        </div>

                        <div>

                            <h6 class="mb-1">

                                Phone

                            </h6>

                            <span class="text-light">

                                +91 98765 43210

                            </span>

                        </div>

                    </div>


                    <div class="d-flex align-items-center">

                        <div class="contact-icon me-3">

                            <i class="bi bi-envelope"></i>

                        </div>

                        <div>

                            <h6 class="mb-1">

                                Email

                            </h6>

                            <span class="text-light">

                                support@easymart.com

                            </span>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Contact Form -->

            <div class="col-lg-7">

                <div class="card contact-card shadow-sm">

                    <div class="card-body p-4 p-lg-5">


                        <?php if ($success !== ""): ?>

                            <div
                                class="alert alert-success"
                            >

                                <i class="bi bi-check-circle-fill"></i>

                                <?= htmlspecialchars($success); ?>

                            </div>

                        <?php endif; ?>


                        <?php if ($error !== ""): ?>

                            <div
                                class="alert alert-danger"
                            >

                                <i class="bi bi-exclamation-circle-fill"></i>

                                <?= htmlspecialchars($error); ?>

                            </div>

                        <?php endif; ?>


                        <form method="POST">


                            <!-- Name -->

                            <div class="mb-3">

                                <label class="form-label">

                                    Name
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter your name"
                                    value="<?= htmlspecialchars($name); ?>"
                                    required
                                >

                            </div>



                            <!-- Email -->

                            <div class="mb-3">

                                <label class="form-label">

                                    Email
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter your email"
                                    value="<?= htmlspecialchars($email); ?>"
                                    required
                                >

                            </div>



                            <!-- Subject -->

                            <div class="mb-3">

                                <label class="form-label">

                                    Subject

                                </label>

                                <input
                                    type="text"
                                    name="subject"
                                    class="form-control"
                                    placeholder="Enter subject"
                                    value="<?= htmlspecialchars($subject); ?>"
                                >

                            </div>



                            <!-- Message -->

                            <div class="mb-4">

                                <label class="form-label">

                                    Message
                                    <span class="text-danger">*</span>

                                </label>

                                <textarea
                                    name="message"
                                    class="form-control"
                                    placeholder="Write your message..."
                                    required
                                ><?= htmlspecialchars($message); ?></textarea>

                            </div>



                            <!-- Submit -->

                            <button
                                type="submit"
                                class="btn btn-dark px-4"
                            >

                                <i class="bi bi-send"></i>

                                Send Message

                            </button>


                        </form>


                    </div>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- Footer -->

<footer class="bg-dark text-white py-4">

    <div class="container text-center">

        <p class="mb-0">

            © <?= date("Y"); ?> EasyMart. All Rights Reserved.

        </p>

    </div>

</footer>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
