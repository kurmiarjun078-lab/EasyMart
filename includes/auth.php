<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check whether user is logged in
function isLoggedIn()
{
    return isset($_SESSION["logged_in"]) &&
           $_SESSION["logged_in"] === true &&
           isset($_SESSION["user_id"]);
}

// Protect a page
function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Get current user ID
function getCurrentUserId()
{
    return $_SESSION["user_id"] ?? null;
}

// Get current user name
function getCurrentUserName()
{
    return $_SESSION["user_name"] ?? "";
}

// Get current user email
function getCurrentUserEmail()
{
    return $_SESSION["user_email"] ?? "";
}

/* =========================
   ADMIN AUTHENTICATION
   ========================= */

function isAdminLoggedIn()
{
    return isset($_SESSION["admin_logged_in"]) &&
           $_SESSION["admin_logged_in"] === true &&
           isset($_SESSION["admin_id"]);
}

function require_admin_login()
{
    if (!isAdminLoggedIn()) {
        header("Location: ../admin/login.php");
        exit;
    }
}

function getAdminId()
{
    return $_SESSION["admin_id"] ?? null;
}

function getAdminName()
{
    return $_SESSION["admin_name"] ?? "Admin";
}

function getAdminEmail()
{
    return $_SESSION["admin_email"] ?? "";
}

?>