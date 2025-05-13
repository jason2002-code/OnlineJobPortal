<?php
session_start();
include("../Functions/db_connection.php");

if (!isset($_SESSION['employerID']) && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$isAdmin = isset($_SESSION['admin']);
$employerID = $isAdmin ? null : $_SESSION['employerID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationID = intval($_POST['applicationID']);
    $decision = $_POST['decision'];
    $feedback = trim($_POST['feedback']);

    // Validate decision
    $valid_decisions = ['accepted', 'rejected', 'shortlisted', 'pending'];
    if (!in_array($decision, $valid_decisions)) {
        $error = "Invalid decision selected.";
    } else {
        if ($isAdmin) {
            // For admin, find employerID from application_review or jobapplications
            $getEmployerSql = "SELECT employerID FROM joblist j JOIN jobapplications ja ON j.jobID = ja.jobID WHERE ja.applicationID = ?";
            $getEmployerStmt = $conn->prepare($getEmployerSql);
            $getEmployerStmt->bind_param("i", $applicationID);
            $getEmployerStmt->execute();
            $getEmployerStmt->bind_result($employerIDFromApp);
            $getEmployerStmt->fetch();
            $getEmployerStmt->close();
            $employerIDToUse = $employerIDFromApp;
        } else {
            $employerIDToUse = $employerID;
        }

        // Check if review exists
        $checkSql = "SELECT reviewID FROM application_review WHERE applicationID = ? AND employerID = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ii", $applicationID, $employerIDToUse);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update existing review
            $stmt->bind_result($reviewID);
            $stmt->fetch();
            $stmt->close();

            $updateSql = "UPDATE application_review SET decision = ?, feedback = ?, reviewDate = NOW() WHERE reviewID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $decision, $feedback, $reviewID);
            $updateStmt->execute();
            $updateStmt->close();

            // Update jobapplications.Status1 with decision
            $updateStatusSql = "UPDATE jobapplications SET Status1 = ? WHERE applicationID = ?";
            $updateStatusStmt = $conn->prepare($updateStatusSql);
            $updateStatusStmt->bind_param("si", $decision, $applicationID);
            $updateStatusStmt->execute();
            $updateStatusStmt->close();

            $success = "Review updated successfully.";
        } else {
            // Insert new review
            $stmt->close();
            $insertSql = "INSERT INTO application_review (applicationID, employerID, decision, feedback, reviewDate) VALUES (?, ?, ?, ?, NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iiss", $applicationID, $employerIDToUse, $decision, $feedback);
            $insertStmt->execute();
            $insertStmt->close();

            // Update jobapplications.Status1 with decision
            $updateStatusSql = "UPDATE jobapplications SET Status1 = ? WHERE applicationID = ?";
            $updateStatusStmt = $conn->prepare($updateStatusSql);
            $updateStatusStmt->bind_param("si", $decision, $applicationID);
            $updateStatusStmt->execute();
            $updateStatusStmt->close();

            $success = "Review submitted successfully.";
        }
    }
}

$showAccepted = isset($_GET['showAccepted']) && $_GET['showAccepted'] == 1;

// Fetch applications for this employer's jobs or all if admin
if ($showAccepted) {
    if ($isAdmin) {
        // Only accepted jobseekers for admin
        $sql = "SELECT ja.applicationID, ja.applicationDate, ja.Status1, ja.user_Id, js.fullName AS jobseekerName, j.jobTitle, e.companyName
                FROM jobapplications ja
                JOIN joblist j ON ja.jobID = j.jobID
                JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
                JOIN employers e ON j.employerID = e.employerID
                WHERE ja.Status1 = 'accepted'
                ORDER BY ja.applicationDate DESC";
        $stmt = $conn->prepare($sql);
    } else {
        // Only accepted jobseekers for employer
        $sql = "SELECT ja.applicationID, ja.applicationDate, ja.Status1, ja.user_Id, js.fullName AS jobseekerName, j.jobTitle
                FROM jobapplications ja
                JOIN joblist j ON ja.jobID = j.jobID
                JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
                WHERE j.employerID = ? AND ja.Status1 = 'accepted'
                ORDER BY ja.applicationDate DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $employerID);
    }
} else {
    if ($isAdmin) {
        // All applications for admin
        $sql = "SELECT ja.applicationID, ja.applicationDate, ja.Status1, ja.user_Id, js.fullName AS jobseekerName, j.jobTitle, e.companyName
                FROM jobapplications ja
                JOIN joblist j ON ja.jobID = j.jobID
                JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
                JOIN employers e ON j.employerID = e.employerID
                ORDER BY ja.applicationDate DESC";
        $stmt = $conn->prepare($sql);
    } else {
        // All applications for employer
        $sql = "SELECT ja.applicationID, ja.applicationDate, ja.Status1, ja.user_Id, js.fullName AS jobseekerName, j.jobTitle
                FROM jobapplications ja
                JOIN joblist j ON ja.jobID = j.jobID
                JOIN jobseeker_profiles js ON ja.user_Id = js.user_Id
                WHERE j.employerID = ?
                ORDER BY ja.applicationDate DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $employerID);
    }
}

$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();

// Fetch existing reviews for these applications
$applicationIDs = array_column($applications, 'applicationID');
$reviews = [];
if (count($applicationIDs) > 0) {
    $placeholders = implode(',', array_fill(0, count($applicationIDs), '?'));
    $types = str_repeat('i', count($applicationIDs));
    if ($isAdmin) {
        // Admin can see all reviews for these applications
        $sqlReviews = "SELECT * FROM application_review WHERE applicationID IN ($placeholders)";
        $stmt = $conn->prepare($sqlReviews);
        $stmt->bind_param($types, ...$applicationIDs);
    } else {
        // Employer sees only their reviews
        $sqlReviews = "SELECT * FROM application_review WHERE applicationID IN ($placeholders) AND employerID = ?";
        $stmt = $conn->prepare($sqlReviews);
        $params = array_merge($applicationIDs, [$employerID]);
        $stmt->bind_param($types . 'i', ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $reviews[$row['applicationID']] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Application Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../For_design/Jobseekstyle.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="employer_dashboard.php"><i class="fas fa-briefcase me-2"></i>Upwork.</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Buttons removed as per request -->
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Application Review</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (count($applications) === 0): ?>
        <p>No applications found for your jobs.</p>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
    <th>Application ID</th>
    <?php if ($isAdmin): ?>
    <th>Employer</th>
    <?php endif; ?>
    <th>Job Title</th>
    <th>Jobseeker</th>
    <th>Applied On</th>
    <th>Status</th>
    <th>Feedback</th>
    <th>Review Date</th>
    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): 
                    $review = $reviews[$app['applicationID']] ?? null;
                    $finalStatus = $review ? htmlspecialchars($review['decision']) : htmlspecialchars($app['Status1']);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($app['applicationID']); ?></td>
                    <?php if ($isAdmin): ?>
                    <td><?php echo htmlspecialchars($app['companyName']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($app['jobTitle']); ?></td>
                    <td><?php echo htmlspecialchars($app['jobseekerName']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($app['applicationDate'])); ?></td>
                    <td><?php echo $finalStatus; ?></td>
                    <td><?php echo $review ? nl2br(htmlspecialchars($review['feedback'])) : ''; ?></td>
                    <td><?php echo $review ? htmlspecialchars($review['reviewDate']) : ''; ?></td>
                    <td>
                        <a href="view_jobseeker_profile.php?user_Id=<?php echo urlencode($app['user_Id']); ?>" target="_blank" class="btn btn-sm btn-info me-1">View Profile</a>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal" 
                            data-applicationid="<?php echo $app['applicationID']; ?>"
                            data-decision="<?php echo $review ? $review['decision'] : 'pending'; ?>"
                            data-feedback="<?php echo $review ? htmlspecialchars($review['feedback']) : ''; ?>">
                            Review
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
       <a href="employer_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reviewModalLabel">Review Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" id="modalApplicationID" value="">
          <div class="row">
            <div class="col-12" id="reviewDetailsSection">
              <form method="POST" id="reviewForm">
                <input type="hidden" name="applicationID" id="applicationID" value="">
                <h6 class="mb-4"><i class="fas fa-edit me-2"></i>Review Details</h6>
                <div class="mb-3 d-flex align-items-center">
                    <label for="decision" class="form-label me-3 mb-0"><i class="fas fa-tasks me-1"></i>Status</label>
                    <select class="form-select w-auto" id="decision" name="decision" required>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="feedback" class="form-label"><i class="fas fa-comment-dots me-1"></i>Feedback</label>
                    <textarea class="form-control" id="feedback" name="feedback" rows="6" required placeholder="Enter your feedback here..."></textarea>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">Submit Review</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
            <div class="col-12" id="sendOfferSection" style="display:none;">
              <form id="offerForm" method="POST" action="send_offer.php">
                  <input type="hidden" name="applicationID" id="offerApplicationID" value="">
                  <h6 class="mb-3"><i class="fas fa-handshake me-2"></i>Send Offer</h6>
                  <div class="mb-3">
                      <label for="salary" class="form-label">Salary</label>
                      <input type="number" step="0.01" min="0" class="form-control" id="salary" name="salary" placeholder="Enter offered salary" required>
                  </div>
                  <div class="mb-3">
                      <label for="startDate" class="form-label">Start Date</label>
                      <input type="date" class="form-control" id="startDate" name="startDate" required>
                  </div>
                  <button type="submit" class="btn btn-success">Send Offer</button>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
              </form>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>

<script>
function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '<',
    '>': '>',
    '"': '"',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

var reviewModal = document.getElementById('reviewModal');
reviewModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var applicationID = button.getAttribute('data-applicationid');
  var decision = button.getAttribute('data-decision');
  var feedback = button.getAttribute('data-feedback');

  var modalApplicationID = document.getElementById('modalApplicationID');
  var reviewFormApplicationID = document.getElementById('applicationID');
  var decisionSelect = document.getElementById('decision');
  var feedbackTextarea = document.getElementById('feedback');
  var offerFormApplicationID = document.getElementById('offerApplicationID');
  var reviewDetailsSection = document.getElementById('reviewDetailsSection');
  var sendOfferSection = document.getElementById('sendOfferSection');

  modalApplicationID.value = applicationID;
  reviewFormApplicationID.value = applicationID;
  offerFormApplicationID.value = applicationID;

  // Determine if showAccepted=1 in URL (appointments)
  var urlParams = new URLSearchParams(window.location.search);
  var showAccepted = urlParams.get('showAccepted') === '1';

  if (showAccepted) {
    // Show Send Offer form only
    reviewDetailsSection.style.display = 'none';
    sendOfferSection.style.display = 'block';
  } else {
    // Show Review Details form only
    reviewDetailsSection.style.display = 'block';
    sendOfferSection.style.display = 'none';

    // Set decision and feedback values
    decisionSelect.value = decision;
    feedbackTextarea.value = feedback;
  }

  // Fetch profile details via AJAX
  fetch('get_jobseeker_profile.php?applicationID=' + encodeURIComponent(applicationID))
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        document.getElementById('profileDetails').innerHTML = '<p class="text-danger">' + data.error + '</p>';
        return;
      }
      document.getElementById('profileFullName').textContent = data.fullName || '';
      document.getElementById('profileBio').textContent = data.bio || '';
      document.getElementById('profileAge').textContent = data.age || '';
      document.getElementById('profileGender').textContent = data.gender || '';
      document.getElementById('profileAddress').textContent = data.address || '';
      // Render skills as tags
      const skillsContainer = document.getElementById('profileSkills');
      skillsContainer.innerHTML = '';
      if (data.skills_array && data.skills_array.length > 0) {
        data.skills_array.forEach(skill => {
          const span = document.createElement('span');
          span.className = 'badge bg-secondary me-1 mb-1';
          span.textContent = skill;
          skillsContainer.appendChild(span);
        });
      } else {
        skillsContainer.textContent = 'No skills listed.';
      }
      document.getElementById('profileSummary').textContent = data.summary || '';
    })
    .catch(() => {
      document.getElementById('profileDetails').innerHTML = '<p class="text-danger">Failed to load profile details.</p>';
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
