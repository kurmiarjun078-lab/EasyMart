<?php

require_once __DIR__ . "/includes/auth.php";
requireLogin();

require_once __DIR__ . "/includes/db.php";

$userId = getCurrentUserId();

$message = "";
$error = "";

/* Get current user data */
$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email, phone, address, city, state, pincode
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {
    die("User not found.");
}


/* Update Profile */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $state = trim($_POST["state"] ?? "");
    $pincode = trim($_POST["pincode"] ?? "");

    if ($name === "") {

        $error = "Name is required.";

    } elseif ($phone === "") {

        $error = "Phone number is required.";

    } elseif ($pincode === "") {

        $error = "Pincode is required.";

    } else {

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
            $userId
        );

        if (mysqli_stmt_execute($stmt)) {

            // Update session name
            $_SESSION["user_name"] = $name;

            $message = "Profile updated successfully!";

            // Update displayed values
            $user["name"] = $name;
            $user["phone"] = $phone;
            $user["address"] = $address;
            $user["city"] = $city;
            $user["state"] = $state;
            $user["pincode"] = $pincode;

        } else {

            $error = "Failed to update profile.";
        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Update Profile - EasyMart</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body {
            background-color: #f8f9fa;
        }

        .profile-card {
            max-width: 800px;
            margin: 50px auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .profile-header {
            background: #212529;
            color: white;
            padding: 25px;
            border-radius: 15px 15px 0 0;
        }

        .profile-icon {
            width: 70px;
            height: 70px;
            background: white;
            color: #212529;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .form-control {
            padding: 12px;
        }

    </style>

</head>

<body>


<!-- Navbar -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm">

    <div class="container">

        <a class="navbar-brand fw-bold"
           href="index.php">

            <i class="bi bi-cart-check-fill"></i>
            EasyMart

        </a>

        <div class="ms-auto">

            <a href="index.php"
               class="btn btn-outline-dark me-2">

                <i class="bi bi-house"></i>
                Home

            </a>

            <a href="profile.php"
               class="btn btn-outline-primary">

                <i class="bi bi-person"></i>
                Profile

            </a>

        </div>

    </div>

</nav>


<!-- Profile Form -->

<div class="container">

    <div class="card profile-card">

        <div class="profile-header">

            <div class="profile-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <h3 class="mb-1">
                Update Profile
            </h3>

            <p class="mb-0">
                Update your EasyMart account information
            </p>

        </div>


        <div class="card-body p-4">

            <?php if ($message !== ""): ?>

                <div class="alert alert-success">

                    <i class="bi bi-check-circle-fill"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="bi bi-exclamation-circle-fill"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- Name -->

                <div class="mb-3">

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


                <!-- Email -->

                <div class="mb-3">

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


                <!-- Phone -->

                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        value="<?= htmlspecialchars($user["phone"] ?? "") ?>"
                        maxlength="20"
                        required
                    >

                </div>


                <!-- Address -->

                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Address
                    </label>

                    <textarea
                        name="address"
                        class="form-control"
                        rows="3"
                        placeholder="Enter your full address"
                    ><?= htmlspecialchars($user["address"] ?? "") ?></textarea>

                </div>


                <div class="row">


                    <!-- City -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            City
                        </label>

                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            value="<?= htmlspecialchars($user["city"] ?? "") ?>"
                        >

                    </div>


                    <!-- State -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            State
                        </label>

                        <input
                            type="text"
                            name="state"
                            class="form-control"
                            value="<?= htmlspecialchars($user["state"] ?? "") ?>"
                        >

                    </div>


                </div>


                <!-- Pincode -->

                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Pincode
                    </label>

                    <input
                        type="text"
                        name="pincode"
                        class="form-control"
                        value="<?= htmlspecialchars($user["pincode"] ?? "") ?>"
                        maxlength="10"
                        required
                    >

                </div>


                <!-- Buttons -->

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-dark px-4">

                        <i class="bi bi-save"></i>
                        Update Profile

                    </button>


                    <a
                        href="profile.php"
                        class="btn btn-outline-secondary">

                        Cancel

                    </a>

                </div>


            </form>

        </div>

    </div>

</div>


<!-- Footer -->

<footer class="text-center py-4 text-muted">

    © <?= date("Y") ?> EasyMart. All Rights Reserved.

</footer>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>