<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['user_Id'])) {
    echo "Missing user_Id parameter.";
    exit;
}

$user_Id = intval($_GET['user_Id']);

// Fetch jobseeker profile data
$query = "SELECT * FROM jobseeker_profiles WHERE user_Id = ? LIMIT 1";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "Failed to prepare statement.";
    exit;
}
$stmt->bind_param("i", $user_Id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $profile = $result->fetch_assoc()) {
    // Parse skills into array
    $skills = array_filter(array_map('trim', explode(',', $profile['skills'])));
} else {
    echo "Profile not found.";
    exit;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Jobseeker Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="employer_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="employer_dashboard.php"><i class="fas fa-user me-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="application_review.php"><i class="fas fa-file-alt me-1"></i>Application Review</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-1"></i>Settings
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#">Account Settings</a></li>
                        <li><a class="dropdown-item" href="#">Privacy</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Jobseeker Profile</h2>
    <div class="profile-card">
        <div class="profile-summary d-flex align-items-center">
            <div class="profile-avatar me-3">
                <?php if (!empty($profile['profilePic'])): ?>
                    <img src="../For_text/uploads/<?php echo htmlspecialchars($profile['profilePic']); ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-user fa-5x"></i>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($profile['fullName']); ?></h3>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($profile['address']); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($profile['age']); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($profile['gender']); ?></p>
            </div>
        </div>
        <div class="profile-details mt-4">
            <h5>Bio</h5>
            <p><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
            <h5>Skills</h5>
            <div>
                <?php if (count($skills) > 0): ?>
                    <?php foreach ($skills as $skill): ?>
                        <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No skills listed.</p>
                <?php endif; ?>
            </div>
            <h5 class="mt-4">Status</h5>
            <p>
            <?php 
                // Determine active or inactive status without database field
                $isActive = !empty($profile['fullName']) && !empty($profile['bio']) && !empty($profile['skills']);
                echo $isActive ? 'Active' : 'Inactive';
            ?>
            </p>
            <h5 class="mt-4">Resume</h5>
            <?php if (!empty($profile['resume'])): ?>
                <a href="../For_text/uploads/<?php echo htmlspecialchars($profile['resume']); ?>" target="_blank" class="btn btn-outline-primary">View Resume</a>
            <?php else: ?>
                <p>No resume uploaded.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
