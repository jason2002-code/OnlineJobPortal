<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}

$employerID = $_SESSION['employerID'];
$error = '';
$success = '';

// Fetch only accepted applications for this employer's jobs to populate select options
$sql = "SELECT ja.applicationID, j.jobTitle, js.fullName 
        FROM jobapplications ja
        JOIN joblist j ON ja.jobID = j.jobID
        JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
        WHERE j.employerID = ? AND ja.Status1 = 'accepted'
        ORDER BY ja.applicationDate DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employerID);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $applicationID = intval($_POST['applicationID']);
        $interviewDate = trim($_POST['interviewDate']);
        $status = 'scheduled'; // default status
        $feedback = '';

        if (empty($applicationID) || empty($interviewDate)) {
            $error = "Please select an application and specify interview date/time.";
        } else {
            $insertSql = "INSERT INTO interview (applicationID, interviewDate, status, feedback) VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            if ($insertStmt) {
                $insertStmt->bind_param("isss", $applicationID, $interviewDate, $status, $feedback);
                if ($insertStmt->execute()) {
                    // Insert notification for jobseeker
                    $notificationMsg = "You have a new interview scheduled on " . date('M j, Y, g:i A', strtotime($interviewDate)) . ".";
                    // Get user_Id from applicationID
                    $userIdSql = "SELECT user_Id FROM jobapplications WHERE applicationID = ?";
                    $userIdStmt = $conn->prepare($userIdSql);
                    $userIdStmt->bind_param("i", $applicationID);
                    $userIdStmt->execute();
                    $userIdResult = $userIdStmt->get_result();
                    if ($userIdResult && $userIdResult->num_rows > 0) {
                        $userRow = $userIdResult->fetch_assoc();
                        $user_Id = $userRow['user_Id'];
                        $userIdStmt->close();

                        $notifInsertSql = "INSERT INTO notifications (user_Id, message, isRead, dateSent) VALUES (?, ?, 0, NOW())";
                        $notifStmt = $conn->prepare($notifInsertSql);
                        if ($notifStmt) {
                            $notifStmt->bind_param("is", $user_Id, $notificationMsg);
                            $notifStmt->execute();
                            $notifStmt->close();
                        }
                    } else {
                        $userIdStmt->close();
                    }

                    $success = "Interview scheduled successfully.";
                } else {
                    $error = "Failed to schedule interview. Please try again.";
                }
                $insertStmt->close();
            } else {
                $error = "Failed to prepare statement.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Schedule Interview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="employer_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
    </div>
</nav>

<div class="container mt-5">
    <h2>Schedule Interview</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="applicationID" class="form-label">Select Application</label>
            <select class="form-select" id="applicationID" name="applicationID" required>
                <option value="">-- Select Application --</option>
                <?php foreach ($applications as $app): ?>
                    <option value="<?php echo $app['applicationID']; ?>">
                        <?php echo htmlspecialchars($app['jobTitle'] . " - " . $app['fullName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="interviewDate" class="form-label">Interview Date & Time</label>
            <input type="datetime-local" class="form-control" id="interviewDate" name="interviewDate" required />
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check me-1"></i>Schedule Interview</button>
        <a href="view_interview.php" class="btn btn-secondary ms-2">View Interviews</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
