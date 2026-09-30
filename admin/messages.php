<?php

require_once __DIR__ . "/../includes/auth.php";
require_admin_login();

require_once __DIR__ . "/../includes/db.php";


// Delete message
if (isset($_GET["delete"])) {

    $messageId = (int) $_GET["delete"];

    if ($messageId > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM contact_messages WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $messageId
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }

    header("Location: messages.php?deleted=1");
    exit;
}


// Fetch messages
$sql = "
    SELECT
        id,
        name,
        email,
        subject,
        message,
        created_at
    FROM contact_messages
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Messages - EasyMart Admin</title>


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

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2>

                <i class="bi bi-envelope-fill"></i>

                Contact Messages

            </h2>


            <p class="text-muted mb-0">

                View messages submitted by customers.

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



    <!-- Success Message -->

    <?php if (isset($_GET["deleted"])): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
        >

            <i class="bi bi-check-circle"></i>

            Message deleted successfully.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- Messages -->

    <div class="card shadow-sm">


        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-chat-left-text"></i>

                Customer Messages

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

                            <th>Customer</th>

                            <th>Email</th>

                            <th>Subject</th>

                            <th>Message</th>

                            <th>Date</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (mysqli_num_rows($result) > 0): ?>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <strong>

                                        #<?= (int) $row["id"]; ?>

                                    </strong>

                                </td>



                                <!-- Name -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row["name"]
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- Email -->

                                <td>

                                    <a
                                        href="mailto:<?= htmlspecialchars(
                                            $row["email"]
                                        ); ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $row["email"]
                                        ); ?>

                                    </a>

                                </td>



                                <!-- Subject -->

                                <td>

                                    <?= !empty($row["subject"])
                                        ? htmlspecialchars($row["subject"])
                                        : '<span class="text-muted">No subject</span>'; ?>

                                </td>



                                <!-- Message -->

                                <td style="min-width: 300px;">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $row["message"]
                                        )
                                    ); ?>

                                </td>



                                <!-- Date -->

                                <td>

                                    <?= date(
                                        "d M Y",
                                        strtotime($row["created_at"])
                                    ); ?>

                                    <br>

                                    <small class="text-muted">

                                        <?= date(
                                            "h:i A",
                                            strtotime($row["created_at"])
                                        ); ?>

                                    </small>

                                </td>



                                <!-- Delete -->

                                <td>

                                    <a
                                        href="messages.php?delete=<?= (int) $row["id"]; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this message?');"
                                    >

                                        <i class="bi bi-trash"></i>

                                        Delete

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-envelope-open fs-1 text-muted"
                                ></i>


                                <h5 class="mt-3">

                                    No Messages Found

                                </h5>


                                <p class="text-muted">

                                    Customer contact messages will appear here.

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
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>