<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID'])) {
    header("Location: login.php");
    exit;
}

$employerID = $_SESSION['employerID'];

if (!isset($_GET['interviewID']) || empty($_GET['interviewID'])) {
    header("Location: view_interviews.php?error=InvalidInterviewID");
    exit;
}

$interviewID = intval($_GET['interviewID']);

// Verify that the interview belongs to this employer
$sql = "SELECT i.interviewID FROM interview i
        JOIN jobapplications ja ON i.applicationID = ja.applicationID
        JOIN joblist j ON ja.jobID = j.jobID
        WHERE i.interviewID = ? AND j.employerID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $interviewID, $employerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Interview not found or does not belong to this employer
    $stmt->close();
    header("Location: view_interviews.php?error=Unauthorized");
    exit;
}
$stmt->close();

// Delete the interview
$deleteSql = "DELETE FROM interview WHERE interviewID = ?";
$stmt = $conn->prepare($deleteSql);
$stmt->bind_param("i", $interviewID);
if ($stmt->execute()) {
    $stmt->close();
    header("Location: view_interviews.php?success=InterviewDeleted");
    exit;
} else {
    $stmt->close();
    header("Location: view_interviews.php?error=DeleteFailed");
    exit;
}
?>
