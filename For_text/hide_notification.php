<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificationID'])) {
    $user_Id = $_SESSION['user_Id'];
    $notificationID = intval($_POST['notificationID']);

    // Update the notification to set isHidden = 1
    $sql = "UPDATE notifications SET isHidden = 1 WHERE notificationID = ? AND user_Id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $notificationID, $user_Id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: jobseeker_dashboard.php");
exit;
?>
