<?php
require_once '../../core/models.php';

if (!isHR()) {
    redirect('../forms/login.php');
}

$jobPosts = getJobPosts($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Dashboard</title>
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
    <h1>HR Dashboard</h1>
    <p class="welcome-message">Welcome, <?php echo $_SESSION['username']; ?>!</p>
</header>

<div class="main-content">
    <div class="dashboard-links">
        <a href="jobPosts.php" class="btn secondary-btn">Create Job Posts</a>
        <a href="messages.php" class="btn secondary-btn">View Messages</a>
    </div>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='message success'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='message error'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <div class="section job-posts">
        <h2>Job Posts</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobPosts as $jobPost) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($jobPost['title']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($jobPost['description'])); ?></td>
                        <td class="actions">
                            <a href="manageJobApplications.php?job_id=<?php echo $jobPost['id']; ?>" class="btn action-btn">Manage Applications</a>
                            <a href="editJob.php?job_id=<?php echo $jobPost['id']; ?>" class="btn action-btn">Edit</a>
                            <form action="deleteJob.php" method="POST" style="display:inline;">
                                <input type="hidden" name="job_id" value="<?php echo $jobPost['id']; ?>">
                                <button type="submit" class="btn action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this job post?');">Delete</button>
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