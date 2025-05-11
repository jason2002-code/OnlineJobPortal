<?php
session_start();
include("../Functions/db_connection.php");
if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}

// Handle delete interview request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_interviewID'])) {
    $deleteInterviewID = intval($_POST['delete_interviewID']);
    $deleteSql = "DELETE FROM interview WHERE interviewID = ? LIMIT 1";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $deleteInterviewID);
    $deleteStmt->execute();
    $deleteStmt->close();
    // Redirect to the same page to avoid form resubmission
    header("Location: view_interview.php");
    exit;
}

$employerID = $_SESSION['employerID'];
// Fetch interviews for this employer's applications
$sql = "SELECT i.interviewID, i.applicationID, i.interviewDate, i.status, i.feedback,
 js.fullName AS jobseekerName, j.jobTitle
 FROM interview i
 JOIN jobapplications ja ON i.applicationID = ja.applicationID
 JOIN joblist j ON ja.jobID = j.jobID
 JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
 WHERE j.employerID = ?
 ORDER BY i.interviewDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employerID);
$stmt->execute();
$result = $stmt->get_result();
$interviews = [];
while ($row = $result->fetch_assoc()) {
    $interviews[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Interviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="employer_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
            <a href="schedule_interview.php" class="btn btn-primary ms-auto"><i class="fas fa-calendar-plus me-1"></i>Schedule Interview</a>
        </div>
    </nav>
    <div class="container mt-5">
        <?php if (count($interviews) === 0): ?>
            <p>No interviews scheduled yet.</p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($interviews as $interview): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($interview['jobTitle']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">Applicant: <?php echo htmlspecialchars($interview['jobseekerName']); ?></h6>
                                <p class="card-text"><i class="fas fa-calendar-alt me-2"></i>Interview Date: <?php echo date('M j, Y, g:i A', strtotime($interview['interviewDate'])); ?></p>
                                <p class="card-text"><i class="fas fa-info-circle me-2"></i>Status: <span class="badge 
                       <?php
                        echo $interview['status'] === 'scheduled' ? 'bg-primary' : ($interview['status'] === 'completed' ? 'bg-success' : 'bg-danger');
                        ?>">
                                        <?php echo ucfirst($interview['status']); ?>
                                    </span></p>
                                <?php echo ucfirst($interview['status']); ?>
                                </span></p>
                                <p class="card-text"><i class="fas fa-comment me-2"></i>Feedback: <?php echo nl2br(htmlspecialchars($interview['feedback'] ?: 'No feedback yet.')); ?></p>
                                <a href="update_interview.php?interviewID=<?php echo $interview['interviewID']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i>Update</a>
                                <form method="POST" action="view_interview.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this interview?');">
                                    <input type="hidden" name="delete_interviewID" value="<?php echo $interview['interviewID']; ?>" />
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-2"><i class="fas fa-trash-alt me-1"></i>Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>