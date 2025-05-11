<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jobseekerID'])) {
    $employerID = $_SESSION['employerID'];
    $jobseekerID = intval($_POST['jobseekerID']);

    // Insert a new interview or recruitment record
    // Assuming there is an interviews table with columns: interviewID, employerID, jobseekerID, status, interviewDate, etc.
    // Adjust the table and columns as per your database schema

    $insertSql = "INSERT INTO recruitment (employerID, user_Id, status, interviewDate) VALUES (?, ?, 'Scheduled', NOW())";
    $stmt = $conn->prepare($insertSql);
    if ($stmt) {
        $stmt->bind_param("ii", $employerID, $jobseekerID);
        if ($stmt->execute()) {
            $interviewID = $stmt->insert_id;
            $stmt->close();
            // Redirect to view_interview_details.php with the new interviewID
            header("Location: view_interview_details.php?interviewID=" . $interviewID);
            exit;
        } else {
            $stmt->close();
            $_SESSION['error'] = "Failed to schedule interview.";
        }
    } else {
        $_SESSION['error'] = "Failed to prepare statement.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: recruitment.php");
exit;
?>
