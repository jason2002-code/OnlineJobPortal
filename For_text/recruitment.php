<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}

$employerID = $_SESSION['employerID'];

// Fetch all jobseeker profiles
$sql = "SELECT user_Id, fullName, bio, age, gender, address FROM jobseeker_profiles ORDER BY fullName ASC";
$result = $conn->query($sql);

$profiles = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $profiles[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recruitment - Jobseeker Profiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
     <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="#">Employer Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="container mt-4">
    <h2>Recruitment - Jobseeker Profiles</h2>
    <?php if (count($profiles) === 0): ?>
        <p>No jobseeker profiles found.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($profiles as $profile): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($profile['fullName']); ?></h5>
                            <p><strong>Age:</strong> <?php echo htmlspecialchars($profile['age']); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars($profile['gender']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($profile['address']); ?></p>
                            <p><strong>Summary:</strong> <?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                            <a href="view_jobseeker_profile.php?user_Id=<?php echo urlencode($profile['user_Id']); ?>" class="btn btn-primary me-2" target="_blank">View Full Profile</a>
                            <form method="POST" action="recruit_jobseeker.php" style="display:inline;">
                                <input type="hidden" name="jobseekerID" value="<?php echo htmlspecialchars($profile['user_Id']); ?>">
                                <button type="submit" class="btn btn-success">Recruit</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
        <a href="employer_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
