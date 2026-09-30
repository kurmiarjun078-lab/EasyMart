<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";

$users = 0;
$products = 0;
$categories = 0;
$orders = 0;
$messages = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($result) {
    $users = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
if ($result) {
    $products = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
if ($result) {
    $categories = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders");
if ($result) {
    $orders = mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_messages");
if ($result) {
    $messages = mysqli_fetch_assoc($result)["total"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - EasyMart</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .navbar {
            background: #212529;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            padding: 30px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .welcome h1 {
            margin-bottom: 5px;
        }

        .welcome p {
            color: #666;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        .card h3 {
            margin: 0 0 10px;
            color: #555;
        }

        .card .number {
            font-size: 32px;
            font-weight: bold;
            color: #0d6efd;
        }

        .menu {
            margin-top: 35px;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        .menu h2 {
            margin-top: 0;
        }

        .menu a {
            display: inline-block;
            padding: 12px 18px;
            margin: 5px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .menu a:hover {
            background: #0b5ed7;
        }

        .logout {
            background: #dc3545 !important;
        }

        @media (max-width: 900px) {

            .cards {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .cards {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                gap: 10px;
            }

            .navbar a {
                margin-left: 8px;
            }

        }

    </style>

</head>

<body>

<!-- Navbar -->

<div class="navbar">

    <h2>EasyMart Admin</h2>

    <div>

        <span>
            Welcome, <?= htmlspecialchars(getAdminName()) ?>
        </span>

        <a href="../index.php">View Store</a>

        <a href="logout.php">Logout</a>

    </div>

</div>


<!-- Main -->

<div class="container">

    <div class="welcome">

        <h1>Admin Dashboard</h1>

        <p>
            Manage your EasyMart e-commerce website.
        </p>

    </div>


    <!-- Statistics -->

    <div class="cards">

        <div class="card">

            <h3>Users</h3>

            <div class="number">
                <?= $users ?>
            </div>

        </div>


        <div class="card">

            <h3>Products</h3>

            <div class="number">
                <?= $products ?>
            </div>

        </div>


        <div class="card">

            <h3>Categories</h3>

            <div class="number">
                <?= $categories ?>
            </div>

        </div>


        <div class="card">

            <h3>Orders</h3>

            <div class="number">
                <?= $orders ?>
            </div>

        </div>


        <div class="card">

            <h3>Messages</h3>

            <div class="number">
                <?= $messages ?>
            </div>

        </div>

    </div>


    <!-- Management Menu -->

    <div class="menu">

        <h2>Management</h2>

        <a href="products.php">
            Products
        </a>

        <a href="categories.php">
            Categories
        </a>

        <a href="users.php">
            Users
        </a>

        <a href="orders.php">
            Orders
        </a>

        <a href="messages.php">
            Messages
        </a>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>

</body>

</html>