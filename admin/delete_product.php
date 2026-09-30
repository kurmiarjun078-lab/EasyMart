<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";


// Get product ID
$productId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($productId <= 0) {
    header("Location: products.php?error=invalid_id");
    exit;
}


// Get product image before deleting
$stmt = mysqli_prepare(
    $conn,
    "SELECT image FROM products WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// Product not found
if (!$product) {
    header("Location: products.php?error=not_found");
    exit;
}


// Delete product from database
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM products WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $productId);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    // Delete product image
    if (!empty($product["image"])) {

        $imagePath = __DIR__ . "/../assets/images/" . $product["image"];

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    header("Location: products.php?deleted=1");
    exit;

} else {

    mysqli_stmt_close($stmt);

    header("Location: products.php?error=delete_failed");
    exit;
}

?>