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


   // Photo upload handling
$photoPath = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    
    // Verify directory exists and is writable
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $_SESSION['error'] = "Failed to create upload directory.";
            header("Location: employer_dashboard.php");
            exit();
        }
    }
    
    if (!is_writable($uploadDir)) {
        $_SESSION['error'] = "Upload directory is not writable.";
        header("Location: employer_dashboard.php");
        exit();
    }

    // Secure filename and create unique name
    $filename = preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES["photo"]["name"]);
    $targetFile = $uploadDir . uniqid() . "_" . $filename;

    // Validate file type using MIME
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedTypes)) {
        $_SESSION['error'] = "Invalid file type. Only JPEG, PNG, GIF allowed.";
        header("Location: employer_dashboard.php");
        exit();
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
        $photoPath = $targetFile;
    } else {
        error_log("Move uploaded file failed. Target: $targetFile");
        $_SESSION['error'] = "Failed to save uploaded file.";
        header("Location: employer_dashboard.php");
        exit();
    }
} elseif (isset($_FILES['photo'])) {
    // Handle specific upload errors
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File is too large',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
        UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $errorCode = $_FILES['photo']['error'];
    $errorMsg = $uploadErrors[$errorCode] ?? 'Unknown upload error';
    $_SESSION['error'] = "File upload error: $errorMsg";
    header("Location: employer_dashboard.php");
    exit();
}


    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO joblist ( employerID, jobTitle, description, Salary, Benefits, Schedule, requirements, Skills, posted_date, status, location, education, Experience,Photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("isssssssssssss", 
    $employerID, $jobTitle, $description, $salary, $benefits, 
    $schedule, $requirements, $skills, $posted_date, $status, 
    $location, $education, $experience, $photoPath
);




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
