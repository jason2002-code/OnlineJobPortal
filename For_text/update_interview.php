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

if (!isset($_GET['interviewID']) && !isset($_POST['interviewID'])) {
    header("Location: view_interviews.php");
    exit;
}

$interviewID = isset($_GET['interviewID']) ? intval($_GET['interviewID']) : intval($_POST['interviewID']);

// Verify interview belongs to employer
$sqlCheck = "SELECT i.interviewID, i.applicationID, i.interviewDate, i.status, i.feedback,
             js.fullName AS jobseekerName, j.jobTitle
             FROM interview i
             JOIN jobapplications ja ON i.applicationID = ja.applicationID
             JOIN joblist j ON ja.jobID = j.jobID
             JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
             WHERE i.interviewID = ? AND j.employerID = ?";

$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("ii", $interviewID, $employerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: view_interviews.php");
    exit;
}

$interview = $result->fetch_assoc();
$stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $interviewDate = trim($_POST['interviewDate']);
        $status = $_POST['status'];
        $feedback = trim($_POST['feedback']);

        $valid_statuses = ['scheduled', 'completed', 'cancelled'];
        if (empty($interviewDate) || !in_array($status, $valid_statuses)) {
            $error = "Please provide valid interview date and status.";
        } else {
            $updateSql = "UPDATE interview SET interviewDate = ?, status = ?, feedback = ? WHERE interviewID = ?";
            $updateStmt = $conn->prepare($updateSql);
            if ($updateStmt) {
                $updateStmt->bind_param("sssi", $interviewDate, $status, $feedback, $interviewID);
                if ($updateStmt->execute()) {
                    // Insert or update notification for jobseeker
                    $notificationMsg = "Your interview:  " . ucfirst($status) . " on " . date('M j, Y, g:i A', strtotime($interviewDate)) . ".";
                    // Get user_Id from applicationID
                    $userIdSql = "SELECT applicationID FROM interview WHERE interviewID = ?";
                    $userIdStmt = $conn->prepare($userIdSql);
                    $userIdStmt->bind_param("i", $interviewID);
                    $userIdStmt->execute();
                    $userIdResult = $userIdStmt->get_result();
                    if ($userIdResult && $userIdResult->num_rows > 0) {
                        $userRow = $userIdResult->fetch_assoc();
                        $applicationID = $userRow['applicationID'];
                        $userIdStmt->close();

                        $userSql = "SELECT user_Id FROM jobapplications WHERE applicationID = ?";
                        $userStmt = $conn->prepare($userSql);
                        $userStmt->bind_param("i", $applicationID);
                        $userStmt->execute();
                        $userResult = $userStmt->get_result();
                        if ($userResult && $userResult->num_rows > 0) {
                            $user = $userResult->fetch_assoc();
                            $user_Id = $user['user_Id'];
                            $userStmt->close();

                    // Check if the same notification message already exists to avoid duplicates
                    $checkNotifSql = "SELECT notificationID FROM notifications WHERE user_Id = ? AND message = ?";
                    $checkNotifStmt = $conn->prepare($checkNotifSql);
                    if ($checkNotifStmt) {
                        $checkNotifStmt->bind_param("is", $user_Id, $notificationMsg);
                        $checkNotifStmt->execute();
                        $checkNotifResult = $checkNotifStmt->get_result();
                        if ($checkNotifResult && $checkNotifResult->num_rows == 0) {
                            // Insert notification only if it does not exist
                            $notifInsertSql = "INSERT INTO notifications (user_Id, message, isRead, dateSent) VALUES (?, ?, 0, NOW())";
                            $notifStmt = $conn->prepare($notifInsertSql);
                            if ($notifStmt) {
                                $notifStmt->bind_param("is", $user_Id, $notificationMsg);
                                $notifStmt->execute();
                                $notifStmt->close();
                            }
                        }
                        $checkNotifStmt->close();
                    }
                        } else {
                            $userStmt->close();
                        }
                    } else {
                        $userIdStmt->close();
                    }

                    $success = "Interview updated successfully.";
                    // Refresh interview data
                    $interview['interviewDate'] = $interviewDate;
                    $interview['status'] = $status;
                    $interview['feedback'] = $feedback;
                } else {
                    $error = "Failed to update interview. Please try again.";
                }
                $updateStmt->close();
            } else {
                $error = "Failed to prepare update statement.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Update Interview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="employer_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
        <a href="view_interview_details.php" class="btn btn-secondary ms-auto"><i class="fas fa-arrow-left me-1"></i>Back to Interviews</a>
    </div>
</nav>

<div class="container mt-5">
    <h2>Update Interview</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <h5 class="mb-3"><?php echo htmlspecialchars($interview['jobTitle']); ?> - <?php echo htmlspecialchars($interview['jobseekerName']); ?></h5>
        <form method="POST">
            <input type="hidden" name="interviewID" value="<?php echo $interviewID; ?>" />
            <div class="mb-3">
                <label for="interviewDate" class="form-label"><i class="fas fa-calendar-alt me-1"></i>Interview Date & Time</label>
                <input type="datetime-local" class="form-control" id="interviewDate" name="interviewDate" required
                    value="<?php echo date('Y-m-d\TH:i', strtotime($interview['interviewDate'])); ?>" />
            </div>
            <div class="mb-3">
                <label for="status" class="form-label"><i class="fas fa-tasks me-1"></i>Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="scheduled" <?php if ($interview['status'] === 'scheduled') echo 'selected'; ?>>Scheduled</option>
                    <option value="completed" <?php if ($interview['status'] === 'completed') echo 'selected'; ?>>Completed</option>
                    <option value="cancelled" <?php if ($interview['status'] === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="feedback" class="form-label"><i class="fas fa-comment-dots me-1"></i>Feedback</label>
                <textarea class="form-control" id="feedback" name="feedback" rows="6" placeholder="Enter feedback"><?php echo htmlspecialchars($interview['feedback']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Interview</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
