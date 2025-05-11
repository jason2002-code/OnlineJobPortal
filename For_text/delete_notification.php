<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificationID'])) {
    $notificationID = intval($_POST['notificationID']);

    // Fetch the notification to check if it is related to an interview
    $query = "SELECT n.notificationID, i.interviewID
              FROM notifications n
              LEFT JOIN interview i ON n.message LIKE CONCAT('%', i.interviewID, '%') OR n.message LIKE CONCAT('%', i.applicationID, '%')
              WHERE n.notificationID = ? AND n.user_Id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $notificationID, $user_Id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($notif = $result->fetch_assoc()) {
            // If notification is related to an interview, do not delete interview data, only delete notification
            $deleteQuery = "DELETE FROM notifications WHERE notificationID = ? AND user_Id = ?";
            if ($deleteStmt = $conn->prepare($deleteQuery)) {
                $deleteStmt->bind_param("ii", $notificationID, $user_Id);
                $deleteStmt->execute();
                $deleteStmt->close();
            }
        }
        $stmt->close();
    }
}

header("Location: jobseeker_dashboard.php");
exit;
?>
