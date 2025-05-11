<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_Id = $_SESSION['user_Id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['applicationID'])) {
    $applicationID = intval($_POST['applicationID']);

    // Verify that the application belongs to the logged-in user
    $checkQuery = "SELECT applicationID FROM jobapplications WHERE applicationID = ? AND user_Id = ?";
    if ($stmt = $conn->prepare($checkQuery)) {
        $stmt->bind_param("ii", $applicationID, $user_Id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->close();

            // Update isHidden flag to 1 to hide application on dashboard
            $updateQuery = "UPDATE jobapplications SET isHidden = 1 WHERE applicationID = ?";
            if ($updateStmt = $conn->prepare($updateQuery)) {
                $updateStmt->bind_param("i", $applicationID);
                if ($updateStmt->execute()) {
                    $updateStmt->close();
                    echo json_encode(['success' => true, 'message' => 'Application hidden from dashboard.']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to hide application.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to prepare update statement.']);
                exit;
            }
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Application not found or unauthorized.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare check statement.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}
?>
