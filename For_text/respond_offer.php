<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['user_Id'])) {
    header("Location: login.php");
    exit;
}

$user_Id = $_SESSION['user_Id'];

if (!isset($_GET['offerID'])) {
    header("Location: my_offers.php");
    exit;
}

$offerID = intval($_GET['offerID']);
$error = "";
$success = "";

// Fetch offer details and verify ownership
$sql = "SELECT o.offerID, o.applicationID, o.salary, o.startDate, o.offerStatus, ja.user_Id
        FROM job_offers o
        JOIN jobapplications ja ON o.applicationID = ja.applicationID
        WHERE o.offerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $offerID);
$stmt->execute();
$result = $stmt->get_result();
$offer = $result->fetch_assoc();
$stmt->close();

if (!$offer || $offer['user_Id'] != $user_Id) {
    $error = "Offer not found or unauthorized access.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $response = $_POST['response'] ?? '';
    if (!in_array($response, ['accepted', 'rejected'])) {
        $error = "Invalid response.";
    } else {
        // Update offerStatus
        $updateSql = "UPDATE job_offers SET offerStatus = ? WHERE offerID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $response, $offerID);
        $updateStmt->execute();
        $updateStmt->close();

        // Update jobapplications.Status1 accordingly
        $updateAppSql = "UPDATE jobapplications SET Status1 = ? WHERE applicationID = ?";
        $updateAppStmt = $conn->prepare($updateAppSql);
        $updateAppStmt->bind_param("si", $response, $offer['applicationID']);
        $updateAppStmt->execute();
        $updateAppStmt->close();

        $success = "Offer " . htmlspecialchars($response) . " successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Respond to Offer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<div class="container mt-5">
    <h2>Respond to Job Offer</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <a href="my_offers.php" class="btn btn-secondary">Back to Offers</a>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <a href="my_offers.php" class="btn btn-primary">Back to Offers</a>
    <?php else: ?>
        <div class="card p-3 mb-3">
            <p><strong>Salary:</strong> <?php echo htmlspecialchars(number_format($offer['salary'], 2)); ?></p>
            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($offer['startDate']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($offer['offerStatus']); ?></p>
        </div>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="response" class="form-label">Your Response</label>
                <select class="form-select" id="response" name="response" required>
                    <option value="">-- Select --</option>
                    <option value="accepted">Accept</option>
                    <option value="rejected">Reject</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Submit Response</button>
            <a href="my_offers.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
