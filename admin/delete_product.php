<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
header('Location: products.php');
exit;
