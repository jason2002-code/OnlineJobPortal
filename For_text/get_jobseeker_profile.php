<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['applicationID'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing applicationID']);
    exit;
}

$applicationID = intval($_GET['applicationID']);
$employerID = $_SESSION['employerID'];

// Query to get jobseeker profile details by applicationID and employerID
$sql = "SELECT js.fullName, js.bio, js.skills, js.resume, js.profilePic, js.age, js.gender, js.address, js.summary
        FROM jobapplications ja
        JOIN joblist j ON ja.jobID = j.jobID
        JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
        WHERE ja.applicationID = ? AND j.employerID = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit;
}
$stmt->bind_param("ii", $applicationID, $employerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    // Adjust resume and profilePic paths if needed
    if (!empty($row['resume'])) {
        $row['resume_url'] = '../uploads/' . basename($row['resume']);
    } else {
        $row['resume_url'] = '';
    }
    if (!empty($row['profilePic'])) {
        $row['profilePic_url'] = '../uploads/' . basename($row['profilePic']);
    } else {
        $row['profilePic_url'] = '';
    }
    // Convert skills string to array
    $row['skills_array'] = array_filter(array_map('trim', explode(',', $row['skills'])));
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Profile not found']);
}

$stmt->close();
$conn->close();
?>
