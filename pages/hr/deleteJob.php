<?php
require_once '../../core/models.php';

if (!isHR()) {
    redirect('../forms/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobID = $_POST['job_id'];

    if (deleteJobPost($pdo, $jobID)) {
        $_SESSION['message'] = "Job post deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete job post.";
    }
    header("Location: dashboard.php");
    exit;
}

function deleteJobPost($pdo, $jobID) {
    $stmt = $pdo->prepare("DELETE FROM job_posts WHERE id = :id");
    return $stmt->execute(['id' => $jobID]);
}
?>