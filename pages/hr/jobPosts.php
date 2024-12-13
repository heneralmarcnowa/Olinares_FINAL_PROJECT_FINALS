<?php
require_once '../../core/models.php';

if (!isHR()) {
    redirect('../forms/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Job Posts</title>
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
    <h1>Create Job Posts</h1>
</header>
<div class="main-content">
    <form action="../../core/handleForms.php" method="POST" class="form-group">
        <label for="title">Job Title:</label>
        <input type="text" name="title" required>

        <label for="description">Job Description:</label>
        <textarea name="description" required></textarea>

        <div class="actions">
            <button type="submit" name="createJob" class="btn">Create Job Post</button>
        </div>
    </form>
</div>

<?php  
    if (isset($_SESSION['message'])) {
        echo "<div class='message success'>{$_SESSION['message']}</div>";
    }
    unset($_SESSION['message']);
?>

</body>
</html>