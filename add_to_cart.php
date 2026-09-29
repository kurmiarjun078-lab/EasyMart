<?php

$conn = require_once "includes/db.php";
require_once "includes/auth.php";


// User must be logged in

if (!isLoggedIn()) {

    header("Location: login.php");

    exit;

}


// Get product ID

$product_id = 0;
$quantity = 1;


// Product ID from POST

if (isset($_POST["product_id"])) {

    $product_id = (int) $_POST["product_id"];

}


// Product ID from GET

elseif (isset($_GET["id"])) {

    $product_id = (int) $_GET["id"];

}


// Quantity

if (isset($_POST["quantity"])) {

    $quantity = (int) $_POST["quantity"];

}


// Validate

if ($product_id <= 0) {

    header("Location: shop.php");

    exit;

}


if ($quantity < 1) {

    $quantity = 1;

}


$user_id = getCurrentUserId();


// Check product

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, stock
     FROM products
     WHERE id = ?
     AND status = 'active'"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $product_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$product = mysqli_fetch_assoc($result);


if (!$product) {

    header("Location: shop.php");

    exit;

}


// Check stock

if ($product["stock"] <= 0) {

    header(
        "Location: product.php?id=" . $product_id
    );

    exit;

}


if ($quantity > $product["stock"]) {

    $quantity = $product["stock"];

}


// Check whether item already exists in cart

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, quantity
     FROM cart
     WHERE user_id = ?
     AND product_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $product_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$cart_item = mysqli_fetch_assoc($result);


// Already in cart

if ($cart_item) {

    $new_quantity =
        $cart_item["quantity"] + $quantity;


    if ($new_quantity > $product["stock"]) {

        $new_quantity = $product["stock"];

    }


    $update = mysqli_prepare(
        $conn,
        "UPDATE cart
         SET quantity = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "ii",
        $new_quantity,
        $cart_item["id"]
    );

    mysqli_stmt_execute($update);

}


// New cart item

else {

    $insert = mysqli_prepare(
        $conn,
        "INSERT INTO cart
        (user_id, product_id, quantity)
        VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $insert,
        "iii",
        $user_id,
        $product_id,
        $quantity
    );

    mysqli_stmt_execute($insert);

}


// Go to cart

header("Location: cart.php");

exit;

?>