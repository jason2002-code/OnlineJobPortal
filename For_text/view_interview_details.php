<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

if (!isset($_GET['interviewID'])) {
    // Removed redirect to jobseeker_dashboard.php to keep page accessible without redirect
    // Optionally, you can show an error message or blank page here
    echo "<p>Interview ID is missing. Please access this page with a valid interview ID.</p>";
    exit;
    
}

$interviewID = intval($_GET['interviewID']);

// Fetch interview details for this user
$sql = "SELECT i.interviewID, i.interviewDate, i.status, i.feedback,
        j.jobTitle, e.companyName, js.fullName
        FROM interview i
        JOIN jobapplications ja ON i.applicationID = ja.applicationID
        JOIN joblist j ON ja.jobID = j.jobID
        JOIN employers e ON j.employerID = e.employerID
        JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
        WHERE i.interviewID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $interviewID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    // Removed redirect to jobseeker_dashboard.php to keep page accessible without redirect
    echo "<p>No interview found with the specified ID or you do not have permission to view it.</p>";
    exit;
}

$interview = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Interview Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="jobseeker_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
        <a href="jobseeker_dashboard.php" class="btn btn-secondary ms-auto"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
    </div>
</nav>

<div class="container mt-5">
    <h2>Interview Details</h2>
    <div class="card shadow-sm p-4">
        <h5 class="card-title"><?php echo htmlspecialchars($interview['jobTitle']); ?></h5>
        <h6 class="card-subtitle mb-2 text-muted">Company: <?php echo htmlspecialchars($interview['companyName']); ?></h6>
        <p><i class="fas fa-user me-2"></i>Applicant: <?php echo htmlspecialchars($interview['fullName']); ?></p>
        <p><i class="fas fa-calendar-alt me-2"></i>Interview Date: <?php echo date('M j, Y, g:i A', strtotime($interview['interviewDate'])); ?></p>
        <p><i class="fas fa-info-circle me-2"></i>Status: <span class="badge 
            <?php 
                echo $interview['status'] === 'scheduled' ? 'bg-primary' : 
                     ($interview['status'] === 'completed' ? 'bg-success' : 'bg-danger'); 
            ?>">
            <?php echo ucfirst($interview['status']); ?>
        </span></p>
        <p><i class="fas fa-comment me-2"></i>Feedback: <?php echo nl2br(htmlspecialchars($interview['feedback'] ?: 'No feedback yet.')); ?></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
