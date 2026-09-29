<?php

session_start();

// Remove all session data
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to home page
header("Location: index.php");
exit;

?>