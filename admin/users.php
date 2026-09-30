<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";


// Fetch all users
$sql = "
    SELECT
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
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users - EasyMart Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

</head>

<body class="bg-light">


<!-- Navbar -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid">

        <a
            href="dashboard.php"
            class="navbar-brand"
        >
            <i class="bi bi-cart-check-fill"></i>
            EasyMart Admin
        </a>


        <div>

            <span class="text-white me-3">

                <i class="bi bi-person-circle"></i>

                <?= htmlspecialchars(getAdminName()); ?>

            </span>


            <a
                href="logout.php"
                class="btn btn-danger btn-sm"
            >
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>

        </div>

    </div>

</nav>



<!-- Main Content -->

<div class="container-fluid py-4">


    <!-- Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>

                <i class="bi bi-people-fill"></i>

                Registered Users

            </h2>

            <p class="text-muted mb-0">

                Manage registered EasyMart customers.

            </p>

        </div>


        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Dashboard

        </a>

    </div>



    <!-- User Count -->

    <div class="row mb-4">

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6 class="text-muted">
                                Total Users
                            </h6>

                            <h3>

                                <?= mysqli_num_rows($result); ?>

                            </h3>

                        </div>

                        <div>

                            <i
                                class="bi bi-people fs-1 text-primary">
                            </i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- Users Table -->

    <div class="card shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-person-lines-fill"></i>

                Customer List

            </h5>

        </div>


        <div class="card-body">


            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover align-middle"
                >

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Phone</th>

                            <th>Address</th>

                            <th>City</th>

                            <th>State</th>

                            <th>Pincode</th>

                            <th>Registered</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (mysqli_num_rows($result) > 0): ?>


                        <?php while ($user = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <strong>

                                        #<?= (int) $user["id"]; ?>

                                    </strong>

                                </td>



                                <!-- Name -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $user["name"]
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- Email -->

                                <td>

                                    <a
                                        href="mailto:<?= htmlspecialchars(
                                            $user["email"]
                                        ); ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $user["email"]
                                        ); ?>

                                    </a>

                                </td>



                                <!-- Phone -->

                                <td>

                                    <?php if (!empty($user["phone"])): ?>

                                        <a
                                            href="tel:<?= htmlspecialchars(
                                                $user["phone"]
                                            ); ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $user["phone"]
                                            ); ?>

                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Not provided

                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- Address -->

                                <td>

                                    <?php if (!empty($user["address"])): ?>

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $user["address"]
                                            )
                                        ); ?>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Not provided

                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- City -->

                                <td>

                                    <?= !empty($user["city"])
                                        ? htmlspecialchars($user["city"])
                                        : '<span class="text-muted">-</span>'; ?>

                                </td>



                                <!-- State -->

                                <td>

                                    <?= !empty($user["state"])
                                        ? htmlspecialchars($user["state"])
                                        : '<span class="text-muted">-</span>'; ?>

                                </td>



                                <!-- Pincode -->

                                <td>

                                    <?= !empty($user["pincode"])
                                        ? htmlspecialchars($user["pincode"])
                                        : '<span class="text-muted">-</span>'; ?>

                                </td>



                                <!-- Date -->

                                <td>

                                    <?= date(
                                        "d M Y",
                                        strtotime($user["created_at"])
                                    ); ?>

                                    <br>

                                    <small class="text-muted">

                                        <?= date(
                                            "h:i A",
                                            strtotime($user["created_at"])
                                        ); ?>

                                    </small>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="9"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-people fs-1 text-muted"
                                ></i>

                                <h5 class="mt-3">

                                    No Users Found

                                </h5>

                                <p class="text-muted">

                                    Registered customers will appear here.

                                </p>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>


        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>