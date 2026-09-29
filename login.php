<?php

session_start();

$conn = require __DIR__ . "/includes/db.php";

$message = "";
$messageType = "";

$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // =========================
    // VALIDATION
    // =========================

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";

    } else {

        // =========================
        // FIND USER
        // =========================

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        // =========================
        // CHECK USER
        // =========================

        if (mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            // =========================
            // VERIFY PASSWORD
            // =========================

            if (password_verify($password, $user["password"])) {

                // Regenerate session ID for security
                session_regenerate_id(true);

                // =========================
                // CREATE SESSION
                // =========================

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["logged_in"] = true;

                // Redirect to home
                header("Location: index.php");
                exit;

            } else {

                $message = "Incorrect password.";
                $messageType = "danger";
            }

        } else {

            $message = "No account found with this email.";
            $messageType = "danger";
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

    <title>Login - EasyMart</title>

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

        .login-container {
            min-height: calc(100vh - 75px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.10);
        }

        .login-header {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #6610f2
            );

            color: white;
            text-align: center;
            padding: 35px 20px;
        }

        .login-header h2 {
            font-weight: bold;
        }

        .form-control {
            padding: 12px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .login-btn {
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
                href="register.php"
                class="btn btn-primary"
            >
                Register
            </a>

        </div>

    </div>

</nav>


<!-- ================= LOGIN ================= -->

<div class="login-container">

    <div class="card login-card">

        <!-- Header -->

        <div class="login-header">

            <i
                class="bi bi-person-circle"
                style="font-size: 55px;"
            ></i>

            <h2 class="mt-2">
                Welcome Back
            </h2>

            <p class="mb-0">
                Login to your EasyMart account
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


            <!-- LOGIN FORM -->

            <form
                method="POST"
                action=""
            >


                <!-- EMAIL -->

                <div class="mb-3">

                    <label class="form-label fw-semibold">

                        Email Address

                        <span class="required">*</span>

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="mb-4">

                    <label class="form-label fw-semibold">

                        Password

                        <span class="required">*</span>

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="togglePassword()"
                        >
                            <i
                                class="bi bi-eye"
                                id="eyeIcon"
                            ></i>
                        </button>

                    </div>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="btn btn-primary w-100 login-btn"
                >

                    <i class="bi bi-box-arrow-in-right"></i>

                    Login

                </button>


            </form>


            <!-- REGISTER -->

            <div class="text-center mt-4">

                Don't have an account?

                <a
                    href="register.php"
                    class="text-primary fw-semibold text-decoration-none"
                >
                    Create Account
                </a>

            </div>


        </div>

    </div>

</div>


<!-- ================= JAVASCRIPT ================= -->

<script>

function togglePassword() {

    const password = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");

    if (password.type === "password") {

        password.type = "text";

        eyeIcon.classList.remove("bi-eye");
        eyeIcon.classList.add("bi-eye-slash");

    } else {

        password.type = "password";

        eyeIcon.classList.remove("bi-eye-slash");
        eyeIcon.classList.add("bi-eye");

    }

}

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>