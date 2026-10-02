<?php

session_start();

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
| IMPORTANT:
| Do NOT write:
| $conn = require ...
|
| db.php already creates $conn.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/includes/db.php";


/*
|--------------------------------------------------------------------------
| CHECK DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection is not available. Please check includes/db.php");
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$message = "";
$messageType = "";
$email = "";


/*
|--------------------------------------------------------------------------
| LOGIN PROCESS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($email === "" || $password === "") {

        $message = "Please enter email and password.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";

    } else {


        /*
        |--------------------------------------------------------------------------
        | FIND USER
        |--------------------------------------------------------------------------
        */

        $query = "
            SELECT
                id,
                name,
                email,
                password
            FROM users
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $query);


        /*
        |--------------------------------------------------------------------------
        | PREPARE ERROR
        |--------------------------------------------------------------------------
        */

        if ($stmt === false) {

            $message = "Database query failed: " . mysqli_error($conn);
            $messageType = "danger";

        } else {


            /*
            |--------------------------------------------------------------------------
            | BIND EMAIL
            |--------------------------------------------------------------------------
            */

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $email
            );


            /*
            |--------------------------------------------------------------------------
            | EXECUTE QUERY
            |--------------------------------------------------------------------------
            */

            if (!mysqli_stmt_execute($stmt)) {

                $message = "Unable to process login. Please try again.";
                $messageType = "danger";

            } else {


                /*
                |--------------------------------------------------------------------------
                | STORE RESULT
                |--------------------------------------------------------------------------
                */

                mysqli_stmt_store_result($stmt);


                /*
                |--------------------------------------------------------------------------
                | CHECK USER EXISTS
                |--------------------------------------------------------------------------
                */

                if (mysqli_stmt_num_rows($stmt) === 1) {


                    /*
                    |--------------------------------------------------------------------------
                    | GET USER DATA
                    |--------------------------------------------------------------------------
                    */

                    mysqli_stmt_bind_result(
                        $stmt,
                        $userId,
                        $userName,
                        $userEmail,
                        $userPassword
                    );

                    mysqli_stmt_fetch($stmt);


                    /*
                    |--------------------------------------------------------------------------
                    | VERIFY PASSWORD
                    |--------------------------------------------------------------------------
                    */

                    $storedPassword = (string) $userPassword;
                    $passwordInfo = password_get_info($storedPassword);
                    $isLegacyPassword = empty($passwordInfo["algo"]);
                    $passwordMatches = password_verify($password, $storedPassword);
 
                    if (!$passwordMatches && $isLegacyPassword) {
                        $passwordMatches = hash_equals($storedPassword, $password);
                    }

                    if ($passwordMatches) {

                        if ($isLegacyPassword) {
                            $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
                            $updateStmt = mysqli_prepare(
                                $conn,
                                "UPDATE users SET password = ? WHERE id = ?"
                            );

                            if ($updateStmt !== false) {
                                mysqli_stmt_bind_param($updateStmt, "si", $newPasswordHash, $userId);
                                mysqli_stmt_execute($updateStmt);
                                mysqli_stmt_close($updateStmt);
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | REGENERATE SESSION ID
                        |--------------------------------------------------------------------------
                        */

                        session_regenerate_id(true);


                        /*
                        |--------------------------------------------------------------------------
                        | CREATE USER SESSION
                        |--------------------------------------------------------------------------
                        */

                        $_SESSION["user_id"] = $userId;
                        $_SESSION["user_name"] = $userName;
                        $_SESSION["user_email"] = $userEmail;
                        $_SESSION["logged_in"] = true;


                        /*
                        |--------------------------------------------------------------------------
                        | REDIRECT HOME
                        |--------------------------------------------------------------------------
                        */

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
            }


            /*
            |--------------------------------------------------------------------------
            | CLOSE STATEMENT
            |--------------------------------------------------------------------------
            */

            mysqli_stmt_close($stmt);
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

    <title>Login - EasyMart</title>


    <!-- Bootstrap 5 -->

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
            min-height: 100vh;
        }


        /*
        |--------------------------------------------------------------------------
        | NAVBAR
        |--------------------------------------------------------------------------
        */

        .navbar-brand {
            letter-spacing: 0.5px;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN CONTAINER
        |--------------------------------------------------------------------------
        */

        .login-container {

            min-height: calc(100vh - 75px);

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 40px 15px;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN CARD
        |--------------------------------------------------------------------------
        */

        .login-card {

            width: 100%;

            max-width: 450px;

            border: none;

            border-radius: 15px;

            overflow: hidden;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.10);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN HEADER
        |--------------------------------------------------------------------------
        */

        .login-header {

            background:
                linear-gradient(
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

            margin-bottom: 8px;
        }


        .login-header p {

            opacity: 0.9;
        }


        /*
        |--------------------------------------------------------------------------
        | FORM
        |--------------------------------------------------------------------------
        */

        .form-control {

            padding: 12px;

            font-size: 15px;
        }


        .input-group-text {

            background: #f8f9fa;

            min-width: 45px;

            justify-content: center;
        }


        .form-control:focus {

            box-shadow: none;

            border-color: #0d6efd;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN BUTTON
        |--------------------------------------------------------------------------
        */

        .login-btn {

            padding: 12px;

            font-size: 17px;

            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | REQUIRED
        |--------------------------------------------------------------------------
        */

        .required {

            color: red;
        }


        /*
        |--------------------------------------------------------------------------
        | REGISTER LINK
        |--------------------------------------------------------------------------
        */

        .register-link {

            text-decoration: none;

            font-weight: 600;
        }


        .register-link:hover {

            text-decoration: underline;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 480px) {

            .login-container {

                padding: 25px 12px;
            }

            .card-body {

                padding: 25px !important;
            }
        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm">

    <div class="container">


        <!-- BRAND -->

        <a
            class="navbar-brand fw-bold text-primary fs-3"
            href="index.php"
        >

            <i class="bi bi-cart-check-fill"></i>

            EasyMart

        </a>


        <!-- NAV BUTTONS -->

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

                <i class="bi bi-person-plus"></i>

                Register

            </a>

        </div>

    </div>

</nav>



<!-- =========================================================
     LOGIN
========================================================= -->

<div class="login-container">


    <div class="card login-card">


        <!-- =================================================
             HEADER
        ================================================== -->

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



        <!-- =================================================
             BODY
        ================================================== -->

        <div class="card-body p-4 p-md-5">


            <!-- =================================================
                 MESSAGE
            ================================================== -->

            <?php if ($message !== ""): ?>

                <div
                    class="alert alert-<?= htmlspecialchars($messageType) ?> alert-dismissible fade show"
                    role="alert"
                >

                    <i class="bi bi-exclamation-circle me-1"></i>

                    <?= htmlspecialchars($message) ?>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 LOGIN FORM
            ================================================== -->

            <form
                method="POST"
                action=""
            >


                <!-- EMAIL -->

                <div class="mb-3">


                    <label
                        for="email"
                        class="form-label fw-semibold"
                    >

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
                            id="email"
                            class="form-control"
                            placeholder="Enter your email"
                            value="<?= htmlspecialchars($email) ?>"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>



                <!-- PASSWORD -->

                <div class="mb-4">


                    <label
                        for="password"
                        class="form-label fw-semibold"
                    >

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
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="togglePassword()"
                            title="Show/Hide Password"
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

                    <i class="bi bi-box-arrow-in-right me-1"></i>

                    Login

                </button>


            </form>



            <!-- =================================================
                 REGISTER
            ================================================== -->

            <div class="text-center mt-4">

                Don't have an account?

                <a
                    href="register.php"
                    class="text-primary register-link"
                >

                    Create Account

                </a>

            </div>


        </div>

    </div>

</div>



<!-- =========================================================
     PASSWORD TOGGLE
========================================================= -->

<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const eyeIcon =
        document.getElementById("eyeIcon");


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



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>