<?php
session_start();
include("../Functions/db_connection.php");

// Check if employer is logged in
if (!isset($_SESSION['employerID'])) {
    header('location:login.php');
    exit();
}

$employerID = $_SESSION['employerID'];
$jobID = isset($_GET['jobID']) ? intval($_GET['jobID']) : 0;

$job = null;
if ($jobID > 0) {
    // Fetch job details for editing
    $stmt = $conn->prepare("SELECT * FROM joblist WHERE jobID = ? AND employerID = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("ii", $jobID, $employerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $job = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "Job not found or you don't have permission to edit this job";
        // Redirect based on admin parameter
        if (isset($_GET['admin']) && $_GET['admin'] == 1) {
            header("Location: manage_jobs.php");
        } else {
            header("Location: employer_dashboard.php");
        }
        exit();
    }
    $stmt->close();
}

// If POST request, handle create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize POST data
    $jobTitle = trim($_POST['jobTitle']);
    $description = trim($_POST['description']);
    $salary = trim($_POST['salary']);
    $benefits = trim($_POST['benefits']);
    $schedule = trim($_POST['schedule']);
    $requirements = trim($_POST['requirements']);
    $skills = trim($_POST['skills']);
    $posted_date = $_POST['posted_date'];
    $status = $_POST['status'];
    $location = trim($_POST['location']);
    $education = trim($_POST['education']);
    $experience = trim($_POST['experience']);

    // Basic validation
    if (empty($jobTitle) || empty($description) || empty($salary) || 
        empty($benefits) || empty($schedule) || empty($requirements) || 
        empty($skills) || empty($location) || empty($education) || 
        empty($posted_date) || empty($status) || empty($experience)) {
        
        $_SESSION['error'] = "Please fill in all required fields";
        if ($jobID > 0) {
            header("Location: post_job.php?jobID=$jobID");
        } else {
            header("Location: employer_dashboard.php");
        }
        exit();
    }

    // Photo upload handling
    $photoPath = (isset($job) && isset($job['Photo'])) ? $job['Photo'] : ''; // Keep existing photo if editing, empty if new

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        
        // Verify directory exists and is writable
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $_SESSION['error'] = "Failed to create upload directory";
                if ($jobID > 0) {
                    header("Location: post_job.php?jobID=$jobID");
                } else {
                    header("Location: employer_dashboard.php");
                }
                exit();
            }
        }
        
        if (!is_writable($uploadDir)) {
            $_SESSION['error'] = "Upload directory is not writable";
            if ($jobID > 0) {
                header("Location: post_job.php?jobID=$jobID");
            } else {
                header("Location: employer_dashboard.php");
            }
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
            $_SESSION['error'] = "Invalid file type. Only JPEG, PNG, GIF allowed";
            if ($jobID > 0) {
                header("Location: post_job.php?jobID=$jobID");
            } else {
                header("Location: employer_dashboard.php");
            }
            exit();
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            // Delete old photo if it exists
            if (!empty($photoPath) && file_exists($photoPath)) {
                unlink($photoPath);
            }
            $photoPath = $targetFile;
        } else {
            error_log("Move uploaded file failed. Target: $targetFile");
            $_SESSION['error'] = "Failed to save uploaded file";
            if ($jobID > 0) {
                header("Location: post_job.php?jobID=$jobID");
            } else {
                header("Location: employer_dashboard.php");
            }
            exit();
        }
    }

    if ($jobID > 0) {
        // Update existing job
        $stmt = $conn->prepare("UPDATE joblist SET 
            jobTitle=?, description=?, Salary=?, Benefits=?, Schedule=?, 
            requirements=?, Skills=?, posted_date=?, status=?, location=?, 
            education=?, Experience=?, Photo=?, categoryID=? 
            WHERE jobID=? AND employerID=?");
        
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        
        $stmt->bind_param("sssssssssssssiii", 
            $jobTitle, $description, $salary, $benefits, $schedule, 
            $requirements, $skills, $posted_date, $status, $location, 
            $education, $experience, $photoPath, $_POST['categoryID'], $jobID, $employerID
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Job updated successfully";
        } else {
            $_SESSION['error'] = "Error updating job: " . htmlspecialchars($stmt->error);
        }
        
        $stmt->close();
    } else {
        // Insert new job
        $stmt = $conn->prepare("INSERT INTO joblist 
            (employerID, jobTitle, description, Salary, Benefits, Schedule, 
            requirements, Skills, posted_date, status, location, education, Experience, Photo, categoryID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        
        $stmt->bind_param("isssssssssssssi", 
            $employerID, $jobTitle, $description, $salary, $benefits, $schedule, 
            $requirements, $skills, $posted_date, $status, $location, $education, $experience, $photoPath, $_POST['categoryID']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Job posted successfully";
        } else {
            $_SESSION['error'] = "Error posting job: " . htmlspecialchars($stmt->error);
        }
        
        $stmt->close();
    }
    
    $conn->close();
    
    if (isset($_GET['admin']) && $_GET['admin'] == 1) {
        header("Location: admin.php");
    } else {
        header("Location: employer_dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - <?php echo isset($job['jobTitle']) ? htmlspecialchars($job['jobTitle']) : ''; ?></title>
    <link rel="stylesheet" href="../For_design/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      
    </style>
</head>
<body>
    <div class="sidebar">
        <!-- Your sidebar content here -->
    </div>
    
    <div class="main">
        <div class="edit-job-container">
            <div class="edit-job-header">
                <h1>Edit Job: <?php echo isset($job['jobTitle']) ? htmlspecialchars($job['jobTitle']) : ''; ?></h1>
                <a href="employer_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
<form action="post_job.php?jobID=<?php echo $jobID; ?>" method="POST" enctype="multipart/form-data">
    <div class="job-form-grid">
        <div class="form-group">
            <label for="jobTitle">Job Title *</label>
            <input type="text" id="jobTitle" name="jobTitle" value="<?php echo isset($job['jobTitle']) ? htmlspecialchars($job['jobTitle']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="categoryID">Category *</label>
            <select id="categoryID" name="categoryID" required>
                <option value="">Select Category</option>
                <?php
                include("../Functions/Category.php");
                $categoryObj = new Category($conn);
                $categories = $categoryObj->getAllCategories();
                foreach ($categories as $category):
                ?>
                <option value="<?= htmlspecialchars($category['categoryID']) ?>" <?= (isset($job) && isset($job['categoryID']) && $job['categoryID'] == $category['categoryID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['categoryName']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" required>
                <option value="Open" <?php echo (isset($job['status']) && $job['status'] === 'Open') ? 'selected' : ''; ?>>Open</option>
                <option value="Closed" <?php echo (isset($job['status']) && $job['status'] === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                <option value="On Hold" <?php echo (isset($job['status']) && $job['status'] === 'On Hold') ? 'selected' : ''; ?>>On Hold</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="salary">Salary *</label>
            <input type="text" id="salary" name="salary" value="<?php echo isset($job['Salary']) ? htmlspecialchars($job['Salary']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="location">Location *</label>
            <input type="text" id="location" name="location" value="<?php echo isset($job['location']) ? htmlspecialchars($job['location']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="posted_date">Posted Date *</label>
            <input type="date" id="posted_date" name="posted_date" value="<?php echo isset($job['posted_date']) ? htmlspecialchars($job['posted_date']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="education">Education Required *</label>
            <input type="text" id="education" name="education" value="<?php echo isset($job['education']) ? htmlspecialchars($job['education']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="experience">Experience Required *</label>
            <input type="text" id="experience" name="experience" value="<?php echo isset($job['Experience']) ? htmlspecialchars($job['Experience']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="schedule">Schedule *</label>
            <input type="text" id="schedule" name="schedule" value="<?php echo isset($job['Schedule']) ? htmlspecialchars($job['Schedule']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="benefits">Benefits *</label>
            <input type="text" id="benefits" name="benefits" value="<?php echo isset($job['Benefits']) ? htmlspecialchars($job['Benefits']) : ''; ?>" required>
        </div>
        
        <div class="form-group full-width">
            <label for="description">Job Description *</label>
            <textarea id="description" name="description" required><?php echo isset($job['description']) ? htmlspecialchars($job['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group full-width">
            <label for="requirements">Requirements *</label>
            <textarea id="requirements" name="requirements" required><?php echo isset($job['requirements']) ? htmlspecialchars($job['requirements']) : ''; ?></textarea>
        </div>
        
        <div class="form-group full-width">
            <label for="skills">Required Skills *</label>
            <textarea id="skills" name="skills" required><?php echo isset($job['Skills']) ? htmlspecialchars($job['Skills']) : ''; ?></textarea>
        </div>
        
        <div class="form-group full-width">
            <label for="photo">Job Photo</label>
            <input type="file" id="photo" name="photo" accept="image/*">
            
            <?php if (!empty($job['Photo'])): ?>
                <div class="photo-preview">
                    <img src="<?php echo htmlspecialchars($job['Photo']); ?>" alt="Current Job Photo">
                    <div>
                        <p>Current photo</p>
                        <div class="photo-actions">
                            <a href="<?php echo htmlspecialchars($job['Photo']); ?>" target="_blank" class="btn">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="form-group full-width" style="text-align: right;">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Update Job
            </button>
        </div>
    </div>
</form>

<?php
if (isset($_GET['admin']) && $_GET['admin'] == 1) {
    echo "<script>
        document.querySelector('form').addEventListener('submit', function(event) {
            // Allow normal form submission, no AJAX
        });
    </script>";
}
?>
        </div>
    </div>
</body>
</html>
