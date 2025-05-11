<?php
session_start();
include("../Functions/db_connection.php");

// Check if user is logged in as employer or jobseeker
if (!isset($_SESSION['employerID']) && !isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $rate = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

    if (empty($message)) {
        $_SESSION['feedback_error'] = "Please enter your review message.";
        header("Location: about.php");
        exit();
    }

    if ($rate < 1 || $rate > 5) {
        $_SESSION['feedback_error'] = "Please select a valid rating between 1 and 5.";
        header("Location: about.php");
        exit();
    }

    if (isset($_SESSION['employerID'])) {
        $reviewerID = $_SESSION['employerID'];
        $reviewerRole = 'employer';
    } else {
        $reviewerID = $_SESSION['user_Id'];
        $reviewerRole = 'jobseeker';
    }

    $stmt = $conn->prepare("INSERT INTO feedback (reviewerID, reviewerRole, message, rate, dateSubmitted) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("isss", $reviewerID, $reviewerRole, $message, $rate);
        if ($stmt->execute()) {
            $_SESSION['feedback_success'] = "Thank you for your review!";
        } else {
            $_SESSION['feedback_error'] = "Failed to submit your review. Please try again.";
        }
        $stmt->close();
    } else {
        $_SESSION['feedback_error'] = "Failed to prepare the database statement.";
    }
} else {
    $_SESSION['feedback_error'] = "Invalid request method.";
}

header("Location: about.php");
exit();
?>
