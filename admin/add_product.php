<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Add Product</title></head><body><h1>Add Product</h1><form method="post"><input name="name" placeholder="Product name" required><input name="price" type="number" step="0.01" placeholder="Price" required><button type="submit">Save Product</button></form></body></html>
