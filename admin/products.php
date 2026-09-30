<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";

$query = "
    SELECT 
        products.id,
        products.name,
        products.description,
        products.price,
        products.old_price,
        products.stock,
        products.image,
        products.status,
        categories.name AS category_name
    FROM products
    LEFT JOIN categories
        ON products.category_id = categories.id
    ORDER BY products.id DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Products - EasyMart Admin</title>

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
            margin-left: 18px;
        }

        .container {
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-add {
            background: #198754;
        }

        .btn-add:hover {
            background: #157347;
        }

        .btn-edit {
            background: #0d6efd;
        }

        .btn-delete {
            background: #dc3545;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #212529;
            color: white;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .no-image {
            width: 70px;
            height: 70px;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #777;
            font-size: 12px;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
        }

        .active {
            background: #d1e7dd;
            color: #0f5132;
        }

        .inactive {
            background: #f8d7da;
            color: #842029;
        }

        .actions {
            white-space: nowrap;
        }

        .actions a {
            margin-right: 5px;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }

        @media (max-width: 700px) {

            .navbar {
                flex-direction: column;
                gap: 10px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

        }

    </style>

</head>

<body>


<!-- Navbar -->

<div class="navbar">

    <h2>EasyMart Admin</h2>

    <div>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="../index.php">
            Store
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<!-- Main -->

<div class="container">

    <div class="header">

        <h1>Manage Products</h1>

        <a href="add_product.php" class="btn btn-add">
            + Add Product
        </a>

    </div>


    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Image</th>

                    <th>Product</th>

                    <th>Category</th>

                    <th>Price</th>

                    <th>Old Price</th>

                    <th>Stock</th>

                    <th>Status</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($product = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?= (int)$product["id"] ?>
                        </td>


                        <td>

                            <?php if (!empty($product["image"])): ?>

                                <img
                                    src="../assets/images/<?= htmlspecialchars($product["image"]) ?>"
                                    class="product-image"
                                    alt="<?= htmlspecialchars($product["name"]) ?>"
                                >

                            <?php else: ?>

                                <div class="no-image">
                                    No Image
                                </div>

                            <?php endif; ?>

                        </td>


                        <td>

                            <strong>
                                <?= htmlspecialchars($product["name"]) ?>
                            </strong>

                            <?php if (!empty($product["description"])): ?>

                                <br>

                                <small>
                                    <?= htmlspecialchars(
                                        substr($product["description"], 0, 60)
                                    ) ?>
                                    <?php
                                    if (strlen($product["description"]) > 60) {
                                        echo "...";
                                    }
                                    ?>
                                </small>

                            <?php endif; ?>

                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $product["category_name"] ?? "No Category"
                            ) ?>
                        </td>


                        <td>
                            ₹<?= number_format(
                                (float)$product["price"],
                                2
                            ) ?>
                        </td>


                        <td>

                            <?php if (
                                $product["old_price"] !== null &&
                                $product["old_price"] > 0
                            ): ?>

                                ₹<?= number_format(
                                    (float)$product["old_price"],
                                    2
                                ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td>
                            <?= (int)$product["stock"] ?>
                        </td>


                        <td>

                            <?php if ($product["status"] === "active"): ?>

                                <span class="status active">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="status inactive">
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </td>


                        <td class="actions">

                            <a
                                href="edit_product.php?id=<?= (int)$product["id"] ?>"
                                class="btn btn-edit"
                            >
                                Edit
                            </a>

                            <a
                                href="delete_product.php?id=<?= (int)$product["id"] ?>"
                                class="btn btn-delete"
                                onclick="return confirm('Are you sure you want to delete this product?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="9" class="empty">

                        No products found.

                        <br><br>

                        <a
                            href="add_product.php"
                            class="btn btn-add"
                        >
                            Add Your First Product
                        </a>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>