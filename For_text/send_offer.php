<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employerID = $_SESSION['employerID'];
    $applicationID = intval($_POST['applicationID']);
    $salary = floatval($_POST['salary']);
    $startDate = $_POST['startDate'];

    // Validate inputs
    if ($applicationID <= 0 || $salary <= 0 || empty($startDate)) {
        $_SESSION['error'] = "Invalid offer details.";
header("Location: my_offers.php");
exit;
    }

    // Check if application belongs to employer's job
    $checkSql = "SELECT j.employerID FROM jobapplications ja JOIN joblist j ON ja.jobID = j.jobID WHERE ja.applicationID = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $applicationID);
    $stmt->execute();
    $stmt->bind_result($jobEmployerID);
    if ($stmt->fetch()) {
        if ($jobEmployerID != $employerID) {
            $_SESSION['error'] = "Unauthorized action.";
            $stmt->close();
header("Location: my_offers.php");
exit;
        }
    } else {
        $_SESSION['error'] = "Application not found.";
        $stmt->close();
        header("Location: application_review.php");
        exit;
    }
    $stmt->close();

    // Insert offer into offers table
    $insertSql = "INSERT INTO job_offers (applicationID, salary, startDate, offerStatus) VALUES (?, ?, ?, 'pending')";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ids", $applicationID, $salary, $startDate);
    if ($insertStmt->execute()) {
        $insertStmt->close();

        // Update jobapplications.Status1 to 'offer received'
        $updateStatusSql = "UPDATE jobapplications SET Status1 = 'offer received' WHERE applicationID = ?";
        $updateStatusStmt = $conn->prepare($updateStatusSql);
        $updateStatusStmt->bind_param("i", $applicationID);
        $updateStatusStmt->execute();
        $updateStatusStmt->close();

        // Get user_Id of jobseeker for notification
        $userSql = "SELECT user_Id FROM jobapplications WHERE applicationID = ?";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param("i", $applicationID);
        $userStmt->execute();
        $userStmt->bind_result($userID);
        $userStmt->fetch();
        $userStmt->close();

        // Insert notification for jobseeker
        $offerLink = "<a href='my_offers.php'>You have received a job offer. Please check your offers.</a>";
        $dateSent = date("Y-m-d H:i:s");
        $notifSql = "INSERT INTO notifications (userID, receiverRole, message, isRead, dateSent) VALUES (?, 'jobseeker', ?, 0, ?)";
        $notifStmt = $conn->prepare($notifSql);
        $notifStmt->bind_param("iss", $userID, $offerLink, $dateSent);
        $notifStmt->execute();
        $notifStmt->close();

        $_SESSION['success'] = "Offer sent successfully.";
    } else {
        $_SESSION['error'] = "Failed to send offer: " . $conn->error;
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: application_review.php");
exit;
?>
