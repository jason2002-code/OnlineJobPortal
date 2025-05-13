<?php
session_start();
include("../Functions/db_connection.php");

// Check if employer is logged in
if (!isset($_SESSION['employerID'])) {
    header('location:login.php');
    exit();
}

$employerID = $_SESSION['employerID'];
$error = '';
$success = '';

// Fetch employer info
$employer = null;
$select_employer = "SELECT * FROM employers WHERE employerID = ?";
if ($stmt = $conn->prepare($select_employer)) {
    $stmt->bind_param("i", $employerID);
    $stmt->execute();
    $result = $stmt->get_result();
    $employer = $result->fetch_assoc();
    $stmt->close();
} else {
    $error = "Failed to fetch employer information.";
}

// Handle form submission to update employer info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employer'])) {
    $companyName = trim($_POST['companyName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $description = trim($_POST['empdescription'] ?? '');
    $dateStab = trim($_POST['DateStab'] ?? '');

    // Handle logo upload
    $logoPath = $employer['Logo'] ?? '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName = basename($_FILES['logo']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = 'logo_' . $employerID . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $logoPath = 'uploads/logos/' . $newFileName;
            } else {
                $logoPath = $employer['Logo']; // Retain the old logo path
            }
        } else {
            $error = "Invalid logo file type. Allowed types: jpg, jpeg, png, gif.";
        }
    }

    if (empty($companyName) || empty($email)) {
        $error = "Company name and email are required.";
    }

    if (!$error) {
        $updateSql = "UPDATE employers SET companyName = ?, Emp_email = ?, empdescription = ?, DateStab = ?, Logo = ? WHERE employerID = ?";
        if ($stmt = $conn->prepare($updateSql)) {
            $stmt->bind_param("sssssi", $companyName, $email, $description, $dateStab, $logoPath, $employerID);
            if ($stmt->execute()) {
                $success = "Information updated successfully.";
                // Refresh employer info
                $stmt->close();
                if ($stmt = $conn->prepare($select_employer)) {
                    $stmt->bind_param("i", $employerID);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $employer = $result->fetch_assoc();
                    $stmt->close();
                }
            } else {
                $error = "Failed to update information.";
            }
        } else {
            $error = "Failed to prepare update statement.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Settings - Employer Information</title>
    <link rel="stylesheet" href="../For_design/SampleStyle/settings.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
      
    </style>
</head>

<body>
    <div class="settings-container">
        <h2>Employer Settings</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="settings.php" enctype="multipart/form-data" novalidate>
            <label for="companyName">Company Name<span style="color:red;">*</span></label>
            <input type="text" id="companyName" name="companyName" required value="<?php echo htmlspecialchars($employer['companyName'] ?? ''); ?>" />

            <label for="email">Email<span style="color:red;">*</span></label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($employer['Emp_email'] ?? ''); ?>" />

            <label for="empdescription">Description</label>
            <textarea id="empdescription" name="empdescription" rows="4"><?php echo htmlspecialchars($employer['empdescription'] ?? ''); ?></textarea>

            <label for="DateStab">Date Established</label>
            <input type="date" id="DateStab" name="DateStab" value="<?php echo htmlspecialchars($employer['DateStab'] ?? ''); ?>" />

            <label for="logo">Logo</label>
            <?php if (!empty($employer['Logo'])): ?>
                <img src="<?php echo htmlspecialchars($employer['Logo']); ?>" alt="Logo" class="logo-preview" />
            <?php endif; ?>
            <input type="file" id="logo" name="logo" accept="image/*" />

            <button type="submit" name="update_employer" class="btn">Update Information</button>
        </form>
        <a href="employer_dashboard.php" class="btn-secondary">Back to Dashboard</a>
    </div>
</body>

</html>