<?php
session_start();
include("../Functions/db_connection.php");

header('Content-Type: application/json');

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

            // Check for related records that prevent deletion
            $relatedTables = [
                'interview' => 'applicationID',
                'job_offers' => 'applicationID',
                // Add other related tables and their foreign key columns here if any
            ];

            foreach ($relatedTables as $table => $column) {
                $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM $table WHERE $column = ?");
                $checkStmt->bind_param("i", $applicationID);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                $countData = $checkResult->fetch_assoc();
                $checkStmt->close();

                if ($countData['count'] > 0) {
                    echo json_encode(['success' => false, 'message' => "Cannot delete application because related records exist in $table. Please delete those first."]);
                    exit;
                }
            }

            // Delete the application
            $deleteQuery = "DELETE FROM jobapplications WHERE applicationID = ?";
            if ($delStmt = $conn->prepare($deleteQuery)) {
                $delStmt->bind_param("i", $applicationID);
                if ($delStmt->execute()) {
                    $delStmt->close();
                    echo json_encode(['success' => true, 'message' => 'Application deleted successfully.']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete application.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to prepare delete statement.']);
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
