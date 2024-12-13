<?php
require_once '../../core/models.php';

if (!isHR()) {
    redirect('../forms/login.php');
}

$jobID = $_GET['job_id'] ?? ''; 
if (empty($jobID)) {
    header("Location: dashboard.php");
    exit;
}

$jobPost = getJobPostById($pdo, $jobID);
if (!$jobPost) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateStatus'])) {
    $applicationID = $_POST['applicationID'];
    $newStatus = $_POST['status'];

    if (updateApplicationStatus($pdo, $applicationID, $newStatus)) {
        $_SESSION['message'] = "Application status updated successfully!";
        header("Location: manageJobApplications.php?job_id=$jobID");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update application status.";
    }
}

$applications = getApplicationsByJobId($pdo, $jobID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Job Applications</title>
    <link rel="stylesheet" href="../../styles/styles.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-brand">FindHire</div>
    <div class="navbar-links">
        <a href="dashboard.php" class="btn">Dashboard</a>
        <a href="../forms/logout.php" class="btn">Log Out</a>
    </div>
</nav>

<div class="main-content">
    <h1>Manage Applications for Job: <?php echo htmlspecialchars($jobPost['title']); ?></h1>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='message'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='error'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <div class="section job-posts">
        <h2>Applications</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Applicant ID</th>
                    <th>Applicant Name</th>
                    <th>Application Status</th>
                    <th>Resume</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($application['applicant_id']); ?></td>
                        <td><?php echo htmlspecialchars($application['username']); ?></td>
                        <td><?php echo htmlspecialchars($application['status']); ?></td>
                        <td>
                            <?php if (!empty($application['resume'])) { ?>
                                <a href="../../resumeUploads/<?php echo htmlspecialchars($application['resume']); ?>" target="_blank">View Resume</a>
                            <?php } else { ?>
                                No resume uploaded
                            <?php } ?>
                        </td>
                        <td><?php echo nl2br(htmlspecialchars($application['description'])); ?></td>
                        <td>
                            <form action="manageJobApplications.php?job_id=<?php echo $jobID; ?>" method="POST">
                                <input type="hidden" name="applicationID" value="<?php echo htmlspecialchars($application['application_id']); ?>">
                                <select name="status" class="form-control" required>
                                    <option value="pending" <?php echo ($application['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="accepted" <?php echo ($application['status'] == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                                    <option value="rejected" <?php echo ($application['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" name="updateStatus" class="btn secondary-btn">Update Status</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>