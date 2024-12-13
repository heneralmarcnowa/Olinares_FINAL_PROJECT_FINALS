<?php
require_once '../../core/models.php';

if (!isApplicant()) {
    redirect('../forms/login.php');
}

if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    die('Job ID not provided. Please go back to the dashboard.');
}

$jobId = intval($_GET['job_id']);
$job = getJobPostById($pdo, $jobId);

if (!$job) {
    die('Job not found. Please go back to the dashboard.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Job</title>
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

<header class="header">
    <h1>Apply for Job</h1>
</header>

<div class="main-content">
    <?php  
    if (isset($_SESSION['message'])) {
        echo "<div class='message success'>{$_SESSION['message']}</div>";
        unset($_SESSION['message']);
    }
    ?>

    <div class="section job-details">
        <h2>Job Details</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Job ID</th>
                    <th>Title</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($job['id']); ?></td>
                    <td><?php echo htmlspecialchars($job['title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($job['description'])); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section application-form">
        <h2>Apply for This Job</h2>
        <form action="../../core/handleForms.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="jobID" value="<?php echo htmlspecialchars($job['id']); ?>">
            <input type="hidden" name="applicantID" value="<?php echo $_SESSION['user_id']; ?>">

            <div class="form-group">
                <label for="message" class="form-label">To what extent does your past work and expertise fulfill the qualifications needed for this job?</label>
                <textarea name="message" id="message" class="form-control" placeholder="Write your response here..." required></textarea>
            </div>

            <div class="form-group">
                <label for="resume" class="form-label">Upload Resume (PDF):</label>
                <input type="file" name="resume" id="resume" class="form-control file-input" accept="application/pdf" required>
            </div>

            <div class="form-actions">
                <button type="submit" name="applyToJobBtn" class="btn primary-btn">Apply</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
