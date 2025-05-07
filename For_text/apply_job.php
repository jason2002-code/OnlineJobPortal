<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['jobID'])) {
    echo "No job selected.";
    exit();
}

$jobID = intval($_GET['jobID']);

// Fetch job details
$query = "SELECT * FROM joblist WHERE jobID = $jobID";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo "Job not found.";
    exit();
}
$job = mysqli_fetch_assoc($result);

$user_Id = $_SESSION['user_Id'];

// Fetch existing resume from profile
$resumeFileName = '';
$profileQuery = "SELECT resume FROM jobseeker_profiles WHERE user_Id = $user_Id LIMIT 1";
$profileResult = mysqli_query($conn, $profileQuery);
if ($profileResult && mysqli_num_rows($profileResult) > 0) {
    $profileData = mysqli_fetch_assoc($profileResult);
    $resumeFileName = $profileData['resume'];
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Optionally allow user to upload a new resume or use existing
    $useExistingResume = isset($_POST['use_existing_resume']) && $_POST['use_existing_resume'] === 'yes';
    $resumeToUse = $resumeFileName;

    if (!$useExistingResume) {
        // Handle resume upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $resumeTmpPath = $_FILES['resume']['tmp_name'];
            $resumeName = basename($_FILES['resume']['name']);
            $resumeExt = strtolower(pathinfo($resumeName, PATHINFO_EXTENSION));
            $allowedResumeExts = ['pdf', 'doc', 'docx'];
            if (!in_array($resumeExt, $allowedResumeExts)) {
                $errors[] = "Resume must be a PDF, DOC, or DOCX file.";
            } else {
                $resumeToUse = uniqid('resume_') . '.' . $resumeExt;
                $resumeDest = __DIR__ . '/uploads/' . $resumeToUse;
                if (!move_uploaded_file($resumeTmpPath, $resumeDest)) {
                    $errors[] = "Failed to upload resume.";
                }
            }
        } else {
            $errors[] = "Please upload a resume or choose to use existing resume.";
        }
    }

    if (empty($errors)) {
    $status = 'pending';
        $applicationDate = date('Y-m-d H:i:s');
        $employerID = $job['employerID'];

        $stmt = $conn->prepare("INSERT INTO jobapplications (jobID, user_Id, resume, applicationDate, Status1, employerID) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issssi", $jobID, $user_Id, $resumeToUse, $applicationDate, $status, $employerID);
            if ($stmt->execute()) {
                $success = "Application submitted successfully.";
            } else {
                $errors[] = "Failed to submit application: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $errors[] = "Failed to prepare application statement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Apply for Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
    <div class="container mt-5 mb-5">
        <h2>Apply for: <?php echo htmlspecialchars($job['jobTitle']); ?></h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="jobseeker_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        <?php else: ?>
        <form action="" method="post" enctype="multipart/form-data" novalidate>
            <?php if ($resumeFileName): ?>
                <div class="mb-3 form-check">
                    <input type="radio" class="form-check-input" id="useExistingResume" name="use_existing_resume" value="yes" checked>
                    <label class="form-check-label" for="useExistingResume">Use existing resume: <a href="uploads/<?php echo htmlspecialchars($resumeFileName); ?>" target="_blank"><?php echo htmlspecialchars($resumeFileName); ?></a></label>
                </div>
                <div class="mb-3 form-check">
                    <input type="radio" class="form-check-input" id="uploadNewResume" name="use_existing_resume" value="no">
                    <label class="form-check-label" for="uploadNewResume">Upload new resume</label>
                </div>
                <div class="mb-3" id="uploadResumeDiv" style="display:none;">
                    <label for="resume" class="form-label">Upload Resume (PDF, DOC, DOCX)</label>
                    <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" />
                </div>
            <?php else: ?>
                <div class="mb-3">
                    <label for="resume" class="form-label">Upload Resume (PDF, DOC, DOCX)</label>
                    <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required />
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Submit Application</button>
            <a href="view_job.php?jobID=<?php echo urlencode($jobID); ?>" class="btn btn-secondary ms-2">Cancel</a>
        </form>
        <?php endif; ?>
    </div>

    <script>
        const useExistingRadio = document.getElementById('useExistingResume');
        const uploadNewRadio = document.getElementById('uploadNewResume');
        const uploadDiv = document.getElementById('uploadResumeDiv');

        if (useExistingRadio && uploadNewRadio && uploadDiv) {
            useExistingRadio.addEventListener('change', () => {
                uploadDiv.style.display = 'none';
            });
            uploadNewRadio.addEventListener('change', () => {
                uploadDiv.style.display = 'block';
            });
        }
    </script>
</body>
</html>
