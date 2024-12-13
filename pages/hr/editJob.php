<?php
require_once '../../core/models.php';

if (!isHR()) {
    redirect('../forms/login.php');
}

$jobID = $_GET['job_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    if (updateJobPost($pdo, $jobID, $title, $description)) {
        $_SESSION['message'] = "Job post updated successfully.";
        header("Location: hrDashboard.php");
        exit;
    } else {
        $error = "Failed to update job post.";
    }
}

$jobPost = getJobPostById($pdo, $jobID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Job Post</title>
    <link rel="stylesheet" href="../../styles/styles.css">
</head>
<body>

<header class="header">
    <h1>Edit Job Post</h1>
</header>

<div class="main-content">
    <?php if (isset($error)) { echo "<div class='message error'>$error</div>"; } ?>
    <form method="POST">
        <div class="form-group">
            <label for="title">Job Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($jobPost['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Job Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($jobPost['description']); ?></textarea>
        </div>
        <button type="submit" class="btn">Update Job</button>
    </form>
</div>

</body>
</html>