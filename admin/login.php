<?php

session_start();
require_once "../includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password FROM admins WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($admin = mysqli_fetch_assoc($result)) {

            if (password_verify($password, $admin["password"])) {

                $_SESSION["admin_logged_in"] = true;
                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_name"] = $admin["name"];
                $_SESSION["admin_email"] = $admin["email"];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Invalid email or password.";
            }

        } else {
            $error = "Invalid email or password.";
        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - EasyMart</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 100px auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #0d6efd;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0b5ed7;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .back {
            text-align: center;
            margin-top: 20px;
        }

        .back a {
            color: #0d6efd;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="login-container">

    <h1>EasyMart Admin</h1>

    <p class="subtitle">Admin Login</p>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter admin email"
                required
            >
        </div>

        <div class="form-group">
            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter password"
                required
            >
        </div>

        <button type="submit">
            Login
        </button>

    </form>

    <div class="back">
        <a href="../index.php">← Back to EasyMart</a>
    </div>

</div>

</body>
</html>