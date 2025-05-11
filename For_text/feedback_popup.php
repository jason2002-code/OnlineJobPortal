<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../Functions/db_connection.php");

$showPopup = false;
$reviewerID = null;
$reviewerRole = null;

if (isset($_SESSION['employerID'])) {
    $reviewerID = $_SESSION['employerID'];
    $reviewerRole = 'employer';
} elseif (isset($_SESSION['user_Id'])) {
    $reviewerID = $_SESSION['user_Id'];
    $reviewerRole = 'jobseeker';
}

if ($reviewerID !== null && $reviewerRole !== null) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE reviewerID = ? AND reviewerRole = ?");
    $stmt->bind_param("is", $reviewerID, $reviewerRole);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        $showPopup = true;
    }
}
?>

<?php if ($showPopup): ?>
<style>
/* Modal styles */
#feedbackModal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

#feedbackModalContent {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
}

#closeFeedbackModal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

#closeFeedbackModal:hover,
#closeFeedbackModal:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.feedback-textarea {
    width: 100%;
    height: 100px;
    margin-bottom: 10px;
    padding: 8px;
    font-size: 1rem;
    border-radius: 4px;
    border: 1px solid #ccc;
    resize: vertical;
}

.feedback-submit-btn {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
}

.feedback-submit-btn:hover {
    background-color: #0056b3;
}
</style>

<div id="feedbackModal">
    <div id="feedbackModalContent">
        <span id="closeFeedbackModal">&times;</span>
        <h2>Submit Your Review</h2>
        <form id="feedbackForm" action="submit_feedback.php" method="POST">
            <label for="rating">Rate (1 to 5):</label>
            <select name="rating" id="rating" required class="feedback-textarea" style="width: 100%; margin-bottom: 10px; padding: 8px; font-size: 1rem; border-radius: 4px; border: 1px solid #ccc;">
                <option value="" disabled selected>Select rating</option>
                <option value="1">1 - Very Poor</option>
                <option value="2">2 - Poor</option>
                <option value="3">3 - Fair</option>
                <option value="4">4 - Good</option>
                <option value="5">5 - Excellent</option>
            </select>
            <textarea name="message" class="feedback-textarea" placeholder="Write your review here..." required></textarea>
            <button type="submit" class="feedback-submit-btn">Submit Review</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('feedbackModal');
    var closeBtn = document.getElementById('closeFeedbackModal');

    // Show the modal
    modal.style.display = "block";

    // Close modal on clicking close button
    closeBtn.onclick = function() {
        modal.style.display = "none";
    };

    // Close modal on clicking outside the modal content
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Optional: After form submission, modal will close on page reload if feedback submitted
});
</script>
<?php endif; ?>
