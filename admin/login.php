<?php

session_start();

require_once __DIR__ . "/../includes/db.php";

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $sql = "
            SELECT id, name, email, password
            FROM admins
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $error = "Database query failed: " . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param($stmt, "s", $email);

            if (!mysqli_stmt_execute($stmt)) {

                $error = "Query execution failed.";

            } else {

                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) === 1) {

                    mysqli_stmt_bind_result(
                        $stmt,
                        $admin_id,
                        $admin_name,
                        $admin_email,
                        $admin_password
                    );

                    mysqli_stmt_fetch($stmt);

                    /*
                     * Check that password column contains
                     * a valid password hash.
                     */

                    if (
                        !empty($admin_password) &&
                        is_string($admin_password) &&
                        password_verify($password, $admin_password)
                    ) {

                        session_regenerate_id(true);

                        $_SESSION["admin_logged_in"] = true;
                        $_SESSION["admin_id"] = $admin_id;
                        $_SESSION["admin_name"] = $admin_name;
                        $_SESSION["admin_email"] = $admin_email;

                        header("Location: dashboard.php");
                        exit;

                    } else {

                        $error = "Invalid email or password.";

                    }

                } else {

                    $error = "No admin account found with this email.";

                }
            }

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

    <title>Admin Login - EasyMart</title>

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
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(
                135deg,
                #0d6efd,
                #6610f2
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: Arial, sans-serif;
        }

        .login-card {

            width: 100%;
            max-width: 430px;

            background: white;

            border-radius: 16px;

            overflow: hidden;

            box-shadow:
                0 15px 40px rgba(0,0,0,0.20);
        }

        .login-header {

            background: #0d6efd;

            color: white;

            text-align: center;

            padding: 30px;
        }

        .login-header i {

            font-size: 55px;
        }

        .login-header h2 {

            margin-top: 10px;

            font-weight: bold;
        }

        .login-body {

            padding: 35px;
        }

        .form-control {

            padding: 12px;
        }

        .btn-login {

            padding: 12px;

            font-size: 17px;

            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="login-card">


    <!-- HEADER -->

    <div class="login-header">

        <i class="bi bi-shield-lock-fill"></i>

        <h2>Admin Login</h2>

        <p class="mb-0">
            EasyMart Administration
        </p>

    </div>


    <!-- BODY -->

    <div class="login-body">


        <?php if ($error !== ""): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle"></i>

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- EMAIL -->

            <div class="mb-3">

                <label class="form-label fw-bold">

                    Email

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-envelope"></i>

                    </span>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter admin email"
                        value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="mb-4">

                <label class="form-label fw-bold">

                    Password

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
                        placeholder="Enter admin password"
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


            <!-- LOGIN -->

            <button
                type="submit"
                class="btn btn-primary w-100 btn-login"
            >

                <i class="bi bi-box-arrow-in-right"></i>

                Login

            </button>

        </form>


        <div class="text-center mt-4">

            <a
                href="../index.php"
                class="text-decoration-none"
            >

                ← Back to EasyMart

            </a>

        </div>

    </div>

</div>


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

</body>

</html>