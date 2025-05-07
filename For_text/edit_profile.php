<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

// Initialize variables
$fullName = $bio = $skills = $age = $gender = $address = "";
$resumeFileName = $profilePicFileName = "";
$errors = [];
$success = "";

// Fetch existing profile data if any
$query = "SELECT * FROM jobseeker_profiles WHERE user_Id = $user_Id LIMIT 1";
$result = mysqli_query($conn, $query);
$profileExists = false;
if ($result && mysqli_num_rows($result) > 0) {
    $profile = mysqli_fetch_assoc($result);
    $profileExists = true;
    $fullName = $profile['fullName'];
    $bio = $profile['bio'];
    $skills = $profile['skills'];
    $age = $profile['age'];
    $gender = $profile['gender'];
    $address = $profile['address'];
    $resumeFileName = $profile['resume'];
    $profilePicFileName = $profile['profilePic'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName']);
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills']);
    $age = trim($_POST['age']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);

    // Validate required fields
    if (empty($fullName)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($age) || !is_numeric($age)) {
        $errors[] = "Valid Age is required.";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required.";
    }

    // Handle resume upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $resumeTmpPath = $_FILES['resume']['tmp_name'];
        $resumeName = basename($_FILES['resume']['name']);
        $resumeExt = strtolower(pathinfo($resumeName, PATHINFO_EXTENSION));
        $allowedResumeExts = ['pdf', 'doc', 'docx'];
        if (!in_array($resumeExt, $allowedResumeExts)) {
            $errors[] = "Resume must be a PDF, DOC, or DOCX file.";
        } else {
            $resumeFileName = uniqid('resume_') . '.' . $resumeExt;
            $resumeDest = __DIR__ . '/uploads/' . $resumeFileName;
            if (!move_uploaded_file($resumeTmpPath, $resumeDest)) {
                $errors[] = "Failed to upload resume.";
            }
        }
    }

    // Handle profile picture upload
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        $picTmpPath = $_FILES['profilePic']['tmp_name'];
        $picName = basename($_FILES['profilePic']['name']);
        $picExt = strtolower(pathinfo($picName, PATHINFO_EXTENSION));
        $allowedPicExts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($picExt, $allowedPicExts)) {
            $errors[] = "Profile picture must be an image file (jpg, jpeg, png, gif).";
        } else {
            $profilePicFileName = uniqid('profile_') . '.' . $picExt;
            $picDest = __DIR__ . '/uploads/' . $profilePicFileName;
            if (!move_uploaded_file($picTmpPath, $picDest)) {
                $errors[] = "Failed to upload profile picture.";
            }
        }
    }

    if (empty($errors)) {
        // Insert or update profile
        if ($profileExists) {
            $stmt = $conn->prepare("UPDATE jobseeker_profile SET fullName=?, bio=?, skills=?, resume=?, profilePic=?, age=?, gender=?, address=? WHERE user_Id=?");
            $stmt->bind_param("ssssssssi", $fullName, $bio, $skills, $resumeFileName, $profilePicFileName, $age, $gender, $address, $user_Id);
        } else {
            $stmt = $conn->prepare("INSERT INTO jobseeker_profiles (user_Id, fullName, bio, skills, resume, profilePic, age, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssss", $user_Id, $fullName, $bio, $skills, $resumeFileName, $profilePicFileName, $age, $gender, $address);
        }

        if ($stmt->execute()) {
            $success = "Profile saved successfully.";
            header("Location: jobseeker_dashboard.php");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Jobseeker Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
    <div class="container mt-5 mb-5">
        <h2>Edit Profile</h2>
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
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
                <label for="fullName" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>" required />
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($bio); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Skills (comma separated)</label>
                <input type="text" class="form-control" id="skills" name="skills" value="<?php echo htmlspecialchars($skills); ?>" />
            </div>
            <div class="mb-3">
                <label for="age" class="form-label">Age *</label>
                <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" required />
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Gender *</label>
                <select class="form-select" id="gender" name="gender" required>
                    <option value="" <?php echo ($gender == '') ? 'selected' : ''; ?>>Select Gender</option>
                    <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="resume" class="form-label">Resume (PDF, DOC, DOCX)</label>
                <?php if ($resumeFileName): ?>
                    <p>Current Resume: <a href="uploads/<?php echo htmlspecialchars($resumeFileName); ?>" target="_blank"><?php echo htmlspecialchars($resumeFileName); ?></a></p>
                <?php endif; ?>
                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" />
            </div>
            <div class="mb-3">
                <label for="profilePic" class="form-label">Profile Picture (JPG, JPEG, PNG, GIF)</label>
                <?php if ($profilePicFileName): ?>
                    <p>Current Picture: <img src="uploads/<?php echo htmlspecialchars($profilePicFileName); ?>" alt="Profile Picture" style="max-width: 150px; max-height: 150px;" /></p>
                <?php endif; ?>
                <input type="file" class="form-control" id="profilePic" name="profilePic" accept=".jpg,.jpeg,.png,.gif" />
            </div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
            <a href="jobseeker_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</body>
</html>
