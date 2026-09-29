<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['admin_id'] = 1;
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Admin Login</title></head>
<body><h1>Admin Login</h1><form method="post"><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Password" required><button type="submit">Login</button></form></body></html>
