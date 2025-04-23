<?php
session_start();
include("../Functions/db_connection.php");

// Check if employer is logged in
if (!isset($_SESSION['employerID'])) {
    header('location:login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize POST data
    $employerID = $_SESSION['employerID'];
    $jobTitle = trim($_POST['jobTitle']);
    $description = trim($_POST['description']);
    $salary = trim($_POST['salary']);
    $benefits = trim($_POST['benefits']);
    $schedule = trim($_POST['schedule']);
    $requirements = trim($_POST['requirements']);
    $skills = trim($_POST['skills']);
    $posted_date = $_POST['posted_date'];
    $status = $_POST['status'];
    $experience = $_POST['experience'];


    // Basic validation
    if (empty($jobTitle) || empty($description) ||
        empty($salary) || empty($benefits) || empty($schedule) || 
        empty($requirements) || empty($skills) || empty($_POST['location']) || 
        empty($_POST['education']) || empty($posted_date) || empty($status) || empty($experience)) {
       
            $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: employer_dashboard.php");
        exit();
    }

    $location = trim($_POST['location']);
    $education = trim($_POST['education']);


    $photoPath = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $uploadDir = "../uploads/";
    $filename = basename($_FILES["photo"]["name"]);
    $targetFile = $uploadDir . time() . "_" . $filename;

    // Validate file type (optional)
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed)) {
        $_SESSION['error'] = "Invalid image format. Only JPG, PNG, GIF allowed.";
        header("Location: employer_dashboard.php");
        exit();
    }

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
        $photoPath = $targetFile;
    } else {
        $_SESSION['error'] = "Failed to upload photo.";
        header("Location: employer_dashboard.php");
        exit();
    }
}


    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO joblist ( employerID, jobTitle, description, Salary, Benefits, Schedule, requirements, Skills, posted_date, status, location, education, Experience,Photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("isssssssssss", $employerID, $jobTitle, $description, $salary, $benefits, $schedule, $requirements, $skills, $posted_date, $status, $location, $education,$photoPath);


    if ($stmt->execute()) {
        $_SESSION['success'] = "Job posted successfully.";
    } else {
        $_SESSION['error'] = "Error posting job: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
    $conn->close();

    header("Location: employer_dashboard.php");
    exit();
} else {
    header("Location: employer_dashboard.php");
    exit();
}
?>
