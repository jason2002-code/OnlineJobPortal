<?php
session_start();
include("../Functions/db_connection.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['categoryName']);
    $description = trim($_POST['description']);

    if (empty($categoryName)) {
        $error = "Category name cannot be empty.";
    } else {
        // Check if category already exists
        $stmt = $conn->prepare("SELECT categoryID FROM job_categories WHERE categoryName = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("s", $categoryName);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Category already exists.";
        } else {
            $stmt->close();
            // Insert new category with description
            $stmt = $conn->prepare("INSERT INTO job_categories (categoryName, description) VALUES (?, ?)");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("ss", $categoryName, $description);
            if ($stmt->execute()) {
                $success = "Category added successfully.";
            } else {
                $error = "Error adding category: " . htmlspecialchars($stmt->error);
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Modern Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../For_design/adminS.css">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            font-family: 'Poppins', sans-serif;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .card {
            margin-top: 40px;
            box-shadow: 0 0.15rem 1.75rem 0 rgb(58 59 69 / 15%);
        }

        .error-message {
            color: #e74a3b;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .success-message {
            color: #1cc88a;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .btn-submit {
            background-color: #4e73df;
            color: white;
            border: none;
        }

        .btn-submit:hover {
            background-color: #2e59d9;
            color: white;
        }

        .btn-cancel {
            margin-left: 10px;
            color: #858796;
            text-decoration: none;
        }

        .btn-cancel:hover {
            color: #5a5c69;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: rgba(78, 115, 223, 0.95);">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php"><i class="fas fa-user-shield me-2"></i>Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-briefcase me-1"></i> Management
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="managementDropdown">
                            <li><a class="dropdown-item" href="admin_post_job.php">Manage Job</a></li>
                            <li><a class="dropdown-item" href="admin_add_category.php">Job Category</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>" href="manage_users.php">
                            <i class="fas fa-users-cog me-1"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="application_log.php"><i class="fas fa-file-alt me-1"></i> Application Activity</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php"><i class="fas fa-bell me-1"></i> Send Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="site_analytics.php"><i class="fas fa-chart-line me-1"></i> Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>

                </ul>

            </div>
        </div>
    </nav>

    <!-- Form Container -->
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Add Job Category</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form action="admin_add_category.php" method="POST">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name *</label>
                        <input type="text" id="categoryName" name="categoryName" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="4" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-submit">Add Category</button>
                    <a href="admin.php" class="btn btn-link btn-cancel">Back to Admin Dashboard</a>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
