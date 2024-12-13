<?php
require_once '../../core/models.php';

if (!isHR()) {
    redirect('../forms/login.php');
}

$hrId = $_SESSION['user_id'];
$applicantId = $_GET['applicant_id'] ?? null;

$messages = $applicantId ? getMessages($hrId, $applicantId) : [];

$stmt = $pdo->prepare("
    SELECT DISTINCT sender.id, sender.username 
    FROM messages 
    INNER JOIN users AS sender ON messages.sender_id = sender.id
    WHERE messages.receiver_id = ?
");
$stmt->execute([$hrId]);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = $_POST['applicant_id'];
    $content = trim($_POST['content']);
    if (!empty($content)) {
        sendMessage($hrId, $receiverId, $content);
        $success = "Reply sent successfully.";
        $messages = getMessages($hrId, $receiverId);
    } else {
        $error = "Reply cannot be empty.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR - Messages</title>
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
    <h1>View Messages</h1>
</header>

<div class="main-content">
    <h1>Message Applicants</h1>

    <?php if (!empty($error)): ?>
        <p class="error"> <?= htmlspecialchars($error) ?> </p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p class="success"> <?= htmlspecialchars($success) ?> </p>
    <?php endif; ?>

    <form method="GET">
        <div class="form-group">
            <label for="applicant_id">Applicant:</label>
            <select name="applicant_id" id="applicant_id" required onchange="this.form.submit()">
                <option value="">Select Applicant</option>
                <?php foreach ($applicants as $applicant): ?>
                    <option value="<?= $applicant['id'] ?>" <?= $applicantId == $applicant['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($applicant['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($applicantId): ?>
        <div>
            <h2>Messages with <?= htmlspecialchars($applicants[array_search($applicantId, array_column($applicants, 'id'))]['username']) ?></h2>
            <div class="messages">
                <?php foreach ($messages as $msg): ?>
                    <p>
                        <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong>
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        <small>(<?= htmlspecialchars($msg['created_at']) ?>)</small>
                    </p>
                <?php endforeach; ?>
            </div>
            <form method="POST">
                <input type="hidden" name="applicant_id" value="<?= $applicantId ?>">
                <div class="form-group">
                    <textarea name="content" placeholder="Type your reply here..." required></textarea>
                </div>
                <button type="submit" class="btn">Send Reply</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>