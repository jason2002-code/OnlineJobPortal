<?php
include_once '../Functions/db_connection.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_type = $_POST['recipient_type'] ?? '';
    $userID = trim($_POST['userID'] ?? '');
    $message_body = trim($_POST['message_body'] ?? '');

    if (empty($recipient_type) || empty($userID) || empty($message_body)) {
        $error = "All fields are required.";
    } else {
        $recipient_type = mysqli_real_escape_string($conn, $recipient_type);
        $userID = mysqli_real_escape_string($conn, $userID);
        $message_body = mysqli_real_escape_string($conn, $message_body);
        $dateSent = date("Y-m-d H:i:s");

        $sql = "INSERT INTO notifications (userID, receiverRole, message, isRead, dateSent)
                VALUES ('$userID', '$recipient_type', '$message_body', 0, '$dateSent')";

        if (mysqli_query($conn, $sql)) {
            $message = "✅ Notification sent successfully to $recipient_type (ID: $userID)";
        } else {
            $error = "❌ Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Notification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    font-family: 'Poppins', sans-serif;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.notification-form {
    max-width: 600px;
    margin: 50px auto;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    padding: 30px;
    backdrop-filter: blur(10px);
}

        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .form-header i {
            font-size: 2rem;
            color: #4e73df;
        }
        .alert {
            margin-bottom: 20px;
        }
        button[type="submit"] {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="notification-form">
    <div class="form-header">
        <i class="fas fa-paper-plane"></i>
        <h4 class="mt-2">Send Notification</h4>
        <p class="text-muted">Send a message to jobseekers or employers.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="notifications.php">
        <div class="mb-3">
            <label for="recipient_type" class="form-label">Recipient Type</label>
            <select class="form-select" id="recipient_type" name="recipient_type" required>
                <option value="">-- Select Role --</option>
                <option value="jobseeker" <?= (isset($recipient_type) && $recipient_type == 'jobseeker') ? 'selected' : '' ?>>Jobseeker</option>
                <option value="employer" <?= (isset($recipient_type) && $recipient_type == 'employer') ? 'selected' : '' ?>>Employer</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="userID" class="form-label">User ID</label>
            <input type="text" class="form-control" id="userID" name="userID" value="<?= isset($userID) ? htmlspecialchars($userID) : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="message_body" class="form-label">Message</label>
            <textarea class="form-control" id="message_body" name="message_body" rows="5" required><?= isset($message_body) ? htmlspecialchars($message_body) : ''; ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Send Notification</button>
    </form>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <?php if ($message): ?>
        <div class="toast align-items-center text-bg-success show" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?= htmlspecialchars($message); ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php elseif ($error): ?>
        <div class="toast align-items-center text-bg-danger show" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?= htmlspecialchars($error); ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</div>

</body>
</html>
