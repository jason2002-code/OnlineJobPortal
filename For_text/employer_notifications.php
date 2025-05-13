<?php
session_start();
include("../Functions/db_connection.php");

// Check if employer is logged in
if (!isset($_SESSION['employerID'])) {
    header('location:login.php');
    exit();
}

$employer_id = $_SESSION['employerID'];

// Fetch notifications for the logged-in employer
$notifications = [];
$unreadCount = 0;
$notifQuery = "SELECT notificationID, message, isRead, dateSent
               FROM notifications
               WHERE user_Id = ? AND receiverRole = 'employer' AND (isHidden IS NULL OR isHidden = 0)
               ORDER BY dateSent DESC";
if ($stmt = $conn->prepare($notifQuery)) {
    $stmt->bind_param("i", $employer_id);
    $stmt->execute();
    $notifResult = $stmt->get_result();
    while ($notif = $notifResult->fetch_assoc()) {
        $notifications[] = $notif;
        if ($notif['isRead'] == 0) {
            $unreadCount++;
        }
    }
    $stmt->close();
}

// Mark notification as read if notificationID is passed via GET
if (isset($_GET['notificationID'])) {
    $notificationID = intval($_GET['notificationID']);
    $updateReadSql = "UPDATE notifications SET isRead = 1 WHERE notificationID = ? AND user_Id = ? AND receiverRole = 'employer'";
    if ($stmt = $conn->prepare($updateReadSql)) {
        $stmt->bind_param("ii", $notificationID, $employer_id);
        $stmt->execute();
        $stmt->close();
    }
    // Redirect to avoid resubmission
    header("Location: employer_notifications.php");
    exit();
}

// Handle hide notification POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hide_notification']) && isset($_POST['notificationID'])) {
    $hideNotificationID = intval($_POST['notificationID']);
    $hideSql = "UPDATE notifications SET isHidden = 1 WHERE notificationID = ? AND user_Id = ? AND receiverRole = 'employer'";
    if ($stmt = $conn->prepare($hideSql)) {
        $stmt->bind_param("ii", $hideNotificationID, $employer_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success'] = "Notification hidden successfully.";
    } else {
        $_SESSION['error'] = "Failed to hide notification.";
    }
    header("Location: employer_notifications.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Employer Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../For_design/SampleStyle/new.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

   

    <style>

    </style>
    
</head>

<body>

    <?php include 'feedback_popup.php'; ?>
    <div class="container">
        <h2>Notifications</h2>
        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        <?php if (count($notifications) > 0): ?>
            <table class="table">
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Message</th>
                    <th>Date Sent</th>
                    <th>Status</th>
                    <th>Actions</th>
                    </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notif): ?>
                    <tr class="<?php echo $notif['isRead'] == 0 ? 'unread' : ''; ?>">
                        <td>Admin</td>
                        <td><?php echo nl2br(htmlspecialchars($notif['message'])); ?></td>
                        <td><?php echo date('M j, Y H:i', strtotime($notif['dateSent'])); ?></td>
                        <td><?php echo $notif['isRead'] == 0 ? '<span class="badge bg-warning">Unread</span>' : '<span class="badge bg-success">Read</span>'; ?></td>
                        <td>
                            <?php if ($notif['isRead'] == 0): ?>
                                <a href="employer_notifications.php?notificationID=<?php echo $notif['notificationID']; ?>" class="btn btn-sm btn-primary">Mark as Read</a>
                            <?php endif; ?>
                            <form method="post" action="employer_notifications.php" style="display:inline;" onsubmit="return confirm('Hide this notification?');">
                                <input type="hidden" name="notificationID" value="<?php echo $notif['notificationID']; ?>" />
                                <button type="submit" name="hide_notification" class="btn btn-sm btn-danger">Hide</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        <?php else: ?>
            <p>No notifications found.</p>
        <?php endif; ?>
        <a href="employer_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fade out alerts after 3 seconds
        window.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            });
        });
    </script>
</body>

</html>