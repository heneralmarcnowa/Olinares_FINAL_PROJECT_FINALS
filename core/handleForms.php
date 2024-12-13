<?php  
require_once 'dbConfig.php';
require_once 'models.php';

// Login and register
if (isset($_POST['insertNewUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = strtolower($_POST['role']);
    $email = trim($_POST['email']);

    if (!empty($username) && !empty($password) && !empty($email)) {
        $insertQuery = insertNewUser($pdo, $username, password_hash($password, PASSWORD_DEFAULT), $role, $email);
        $_SESSION['message'] = $insertQuery['message'];

        if ($insertQuery['status'] == '200') {
            $_SESSION['status'] = $insertQuery['status'];
            header("Location: ../pages/forms/login.php");
        } else {
            $_SESSION['status'] = $insertQuery['status'];
            header("Location: ../pages/forms/register.php");
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../pages/forms/register.php");
    }
}

if (isset($_POST['loginUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $loginQuery = checkIfUserExists($pdo, $username);
        $userIDFromDB = $loginQuery['userInfoArray']['id'];
        $usernameFromDB = $loginQuery['userInfoArray']['username'];
        $passwordFromDB = $loginQuery['userInfoArray']['password'];
        $roleFromDB = $loginQuery['userInfoArray']['role'];

        if (password_verify($password, $passwordFromDB)) {
            $_SESSION['user_id'] = $userIDFromDB;
            $_SESSION['username'] = $usernameFromDB;
            $_SESSION['role'] = $roleFromDB;

            if (isHR()) {
                redirect('../pages/hr/dashboard.php');
            } elseif (isApplicant()) {
                redirect('../pages/applicants/dashboard.php');
            } else {
                redirect('../pages/forms/login.php');
            }
        } else {
            $_SESSION['message'] = "Username/password invalid";
            $_SESSION['status'] = "400";
            header("Location: ../pages/forms/login.php");
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../pages/forms/register.php");
    }
}

// HR - create job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createJob'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    if (createJobPost($pdo, $title, $description)) {
        $_SESSION['message'] = "Job post created successfully!";
        header("Location: ../pages/hr/jobPosts.php");
        exit;
    } else {
        $_SESSION['error'] = "Error creating job post...";
        header("Location: ../pages/hr/jobPosts.php");
        exit;
    }
}

// Job application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['applyToJobBtn'])) {
    $jobID = $_POST['jobID'] ?? '';
    $applicantID = $_POST['applicantID'] ?? $_SESSION['user_id'];
    $message = $_POST['message'] ?? '';
    $resume = $_FILES['resume'] ?? null;

    if (empty($jobID) || empty($message) || !$resume || $resume['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "All fields are required, please upload your resume in a PDF file.";
        header("Location: ../pages/applicants/applyJob.php?job_id=" . $jobID);
        exit;
    }

    $uploadDirectory = '../../resumeUploads/';
    $resumePath = $uploadDirectory . basename($resume['name']);

    if (move_uploaded_file($resume['tmp_name'], $resumePath)) {
        $isApplicationSaved = applyForJob($pdo, $applicantID, $jobID, $message, $resumePath);

        if ($isApplicationSaved) {
            $_SESSION['message'] = "Application submitted successfully!";
            header("Location: ../pages/applicants/dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Error submitting application...";
            header("Location: ../pages/applicants/dashboard.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Error uploading resume...";
        header("Location: ../pages/applicants/dashboard.php");
        exit;
    }
}

// Handle follow-up message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendFollowUpBtn'])) {
    $applicationID = $_POST['applicationID'] ?? '';
    $followUpMessage = $_POST['followUpMessage'] ?? '';
    $senderID = $_SESSION['user_id'];

    if (empty($applicationID) || empty($followUpMessage)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../pages/applicants/applications.php");
        exit;
    }

    if (sendFollowUpMessage($pdo, $senderID, $followUpMessage)) {
        $_SESSION['message'] = "Follow-up sent successfully!";
        header("Location: ../pages/applicants/applications.php");
        exit;
    } else {
        $_SESSION['error'] = "Error sending follow-up...";
        header("Location: ../pages/applicants/applications.php");
        exit;
    }
}
?>