<?php

require_once "includes/auth.php";
require_once "includes/db.php";

requireLogin();

$user_id = getCurrentUserId();

$message = "";
$messageType = "";

// =========================
// UPDATE PROFILE
// =========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $state = trim($_POST["state"] ?? "");
    $pincode = trim($_POST["pincode"] ?? "");

    // Validation
    if (empty($name)) {

        $message = "Name is required.";
        $messageType = "danger";

    } elseif (strlen($name) < 2) {

        $message = "Name must contain at least 2 characters.";
        $messageType = "danger";

    } elseif (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Phone number must contain exactly 10 digits.";
        $messageType = "danger";

    } elseif (!empty($pincode) && !preg_match("/^[0-9]{6}$/", $pincode)) {

        $message = "Pincode must contain exactly 6 digits.";
        $messageType = "danger";

    } else {

        // Update user
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users
             SET name = ?,
                 phone = ?,
                 address = ?,
                 city = ?,
                 state = ?,
                 pincode = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssi",
            $name,
            $phone,
            $address,
            $city,
            $state,
            $pincode,
            $user_id
        );

        if (mysqli_stmt_execute($stmt)) {

            // Update session name
            $_SESSION["user_name"] = $name;

            $message = "Profile updated successfully.";
            $messageType = "success";

        } else {

            $message = "Unable to update profile.";
            $messageType = "danger";
        }

        mysqli_stmt_close($stmt);
    }
}


// =========================
// GET USER DATA
// =========================

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        name,
        email,
        phone,
        address,
        city,
        state,
        pincode,
        created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {

    session_destroy();

    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Profile - EasyMart</title>

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #f4f7fb;
        }

        .profile-container {
            padding: 50px 15px;
        }

        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #6610f2
            );

            color: white;
            text-align: center;
            padding: 35px;
        }

        .profile-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
            color: #0d6efd;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: auto;

            font-size: 45px;
        }

        .form-control {
            padding: 12px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }

    </style>

</head>

<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm">

    <div class="container">

        <a
            class="navbar-brand fw-bold text-primary fs-3"
            href="index.php"
        >
            <i class="bi bi-cart-check-fill"></i>
            EasyMart
        </a>

        <div>

            <a
                href="index.php"
                class="btn btn-outline-primary me-2"
            >
                <i class="bi bi-house"></i>
                Home
            </a>

            <a
                href="logout.php"
                class="btn btn-danger"
            >
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- ================= PROFILE ================= -->

<div class="container profile-container">

    <div class="row justify-content-center">

        <div class="col-lg-9">

            <div class="card profile-card">


                <!-- HEADER -->

                <div class="profile-header">

                    <div class="profile-icon">

                        <i class="bi bi-person"></i>

                    </div>

                    <h2 class="mt-3 mb-1">

                        <?= htmlspecialchars($user["name"]) ?>

                    </h2>

                    <p class="mb-0">

                        <?= htmlspecialchars($user["email"]) ?>

                    </p>

                </div>


                <!-- BODY -->

                <div class="card-body p-4 p-md-5">


                    <!-- MESSAGE -->

                    <?php if (!empty($message)) { ?>

                        <div
                            class="alert alert-<?= $messageType ?> alert-dismissible fade show"
                        >

                            <?= htmlspecialchars($message) ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>

                        </div>

                    <?php } ?>


                    <h4 class="mb-4">

                        <i class="bi bi-person-vcard"></i>

                        Personal Information

                    </h4>


                    <form
                        method="POST"
                        action=""
                    >

                        <div class="row g-3">


                            <!-- NAME -->

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user["name"]) ?>"
                                    required
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user["email"]) ?>"
                                    readonly
                                >

                                <small class="text-muted">
                                    Email cannot be changed.
                                </small>

                            </div>


                            <!-- PHONE -->

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user["phone"] ?? "") ?>"
                                    maxlength="10"
                                    placeholder="10 digit mobile number"
                                >

                            </div>


                            <!-- CITY -->

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    City
                                </label>

                                <input
                                    type="text"
                                    name="city"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user["city"] ?? "") ?>"
                                    placeholder="Enter city"
                                >

                            </div>


                            <!-- STATE -->

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    State
                                </label>

                                <input
                                    type="text"
                                    name="state"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user["state"] ?? "") ?>"
                                    placeholder="Enter state"
                                >

                            </div>


                            <!-- PINCODE -->

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Pincode
                                </label>

                                <input
                                    type="text"
                                    name="pincode"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user["pincode"] ?? "") ?>"
                                    maxlength="6"
                                    placeholder="6 digit pincode"
                                >

                            </div>


                            <!-- ADDRESS -->

                            <div class="col-12">

                                <label class="form-label fw-semibold">
                                    Address
                                </label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Enter your complete address"
                                ><?= htmlspecialchars($user["address"] ?? "") ?></textarea>

                            </div>


                            <!-- BUTTON -->

                            <div class="col-12 mt-4">

                                <button
                                    type="submit"
                                    class="btn btn-primary px-4"
                                >

                                    <i class="bi bi-save"></i>

                                    Update Profile

                                </button>

                                <a
                                    href="index.php"
                                    class="btn btn-outline-secondary ms-2"
                                >

                                    Cancel

                                </a>

                            </div>

                        </div>

                    </form>


                    <!-- ACCOUNT INFO -->

                    <hr class="my-5">


                    <h4 class="mb-4">

                        <i class="bi bi-info-circle"></i>

                        Account Information

                    </h4>


                    <div class="row g-3">

                        <div class="col-md-6">

                            <div class="info-box">

                                <small class="text-muted">
                                    Account ID
                                </small>

                                <div class="fw-bold">
                                    #<?= $user["id"] ?>
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="info-box">

                                <small class="text-muted">
                                    Registered On
                                </small>

                                <div class="fw-bold">

                                    <?= date(
                                        "d M Y",
                                        strtotime($user["created_at"])
                                    ) ?>

                                </div>

                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>