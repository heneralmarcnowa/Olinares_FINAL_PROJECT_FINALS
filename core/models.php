<?php
require_once 'dbConfig.php';

function isHR() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'hr';
}

function isApplicant() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'applicant';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function checkIfUserExists($pdo, $username) {
    $response = [];
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$username])) {
        $userInfoArray = $stmt->fetch();

        if ($stmt->rowCount() > 0) {
            $response = [
                "result" => true,
                "status" => "200",
                "userInfoArray" => $userInfoArray
            ];
        } else {
            $response = [
                "result" => false,
                "status" => "400",
                "message" => "User doesn't exist in the database"
            ];
        }
    }

    return $response;
}

function insertNewUser($pdo, $username, $password, $role, $email) {
    $response = [];
    $checkIfUserExists = checkIfUserExists($pdo, $username); 

    if (!$checkIfUserExists['result']) {
        $sql = "INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$username, $password, $role, $email])) {
            $response = [
                "status" => "200",
                "message" => "User successfully inserted!"
            ];
        } else {
            $response = [
                "status" => "400",
                "message" => "An error occurred with the query!"
            ];
        }
    } else {
        $response = [
            "status" => "400",
            "message" => "User already exists!"
        ];
    }

    return $response;
}

function createJobPost($pdo, $title, $description) {
    try {
        $stmt = $pdo->prepare("INSERT INTO job_posts (title, description) VALUES (?, ?)");
        $stmt->execute([$title, $description]);
        return true;
    } catch (PDOException $e) {
        error_log("Error creating job post: " . $e->getMessage());
        return false;
    }
}

function getJobPosts($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM job_posts ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching job posts: " . $e->getMessage());
        return [];
    }
}

function getJobPostById($pdo, $jobID) {
    $stmt = $pdo->prepare("SELECT * FROM job_posts WHERE id = :id");
    $stmt->execute(['id' => $jobID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateApplicationStatus($pdo, $applicationID, $newStatus) {
    $sql = "UPDATE applications SET status = :newStatus WHERE id = :applicationID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':newStatus', $newStatus, PDO::PARAM_STR);
    $stmt->bindParam(':applicationID', $applicationID, PDO::PARAM_INT);

    return $stmt->execute();
}

function getApplicationsByJobId($pdo, $jobId) {
    $sql = "SELECT ja.id AS application_id, u.username, ja.applicant_id, ja.status, ja.description, ja.resume 
            FROM applications ja
            JOIN users u ON ja.applicant_id = u.id
            WHERE ja.job_post_id = :jobId";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllJobPosts($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM job_posts");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getAllJobPosts: " . $e->getMessage());
    }
    return [];
}

function applyForJob($pdo, $applicantID, $jobID, $message, $resumePath) {
    try {
        $status = 'pending';

        $stmt = $pdo->prepare("INSERT INTO applications (applicant_id, job_post_id, status, applied_at, description, resume) 
                               VALUES (:applicant_id, :job_post_id, :status, CURRENT_TIMESTAMP, :description, :resume)");

        $stmt->bindParam(':applicant_id', $applicantID);
        $stmt->bindParam(':job_post_id', $jobID);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':description', $message);
        $stmt->bindParam(':resume', $resumePath);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error in applyForJob: " . $e->getMessage());
        return false;
    }
}

function getUserApplications($pdo, $userID) {
    $sql = "SELECT ja.id AS applicationID, jp.title AS job_title, ja.status
            FROM applications ja
            JOIN job_posts jp ON ja.job_post_id = jp.id
            WHERE ja.applicant_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sendFollowUpMessage($pdo, $senderID, $messageContent) {
    $sql = "INSERT INTO messages (sender_id, message) VALUES (:sender_id, :message_content)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sender_id', $senderID, PDO::PARAM_INT);
    $stmt->bindParam(':message_content', $messageContent, PDO::PARAM_STR);

    return $stmt->execute();
}

function getMessages($userId, $otherUserId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT messages.*, 
               sender.username AS sender_name, 
               receiver.username AS receiver_name 
        FROM messages 
        INNER JOIN users AS sender ON messages.sender_id = sender.id
        INNER JOIN users AS receiver ON messages.receiver_id = receiver.id
        WHERE (messages.sender_id = :userId AND messages.receiver_id = :otherUserId)
           OR (messages.sender_id = :otherUserId AND messages.receiver_id = :userId)
        ORDER BY messages.created_at ASC
    ");
    $stmt->execute(['userId' => $userId, 'otherUserId' => $otherUserId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sendMessage($senderId, $receiverId, $content) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    return $stmt->execute([$senderId, $receiverId, $content]);
}

function updateJobPost($pdo, $jobID, $title, $description) {
    $stmt = $pdo->prepare("UPDATE job_posts SET title = :title, description = :description WHERE id = :id");
    return $stmt->execute(['title' => $title, 'description' => $description, 'id' => $jobID]);
}
?>