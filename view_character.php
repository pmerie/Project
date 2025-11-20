<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

// Get character ID
if (!isset($_GET['id'])) {
    die("Character not found.");
}
$character_id = $_GET['id'];

// Fetch character details
$stmt = $db->prepare("
    SELECT c.*, f.film_name
    FROM characters c
    LEFT JOIN films f ON c.film_id = f.film_id
    WHERE c.character_id = ?
");
$stmt->execute([$character_id]);
$character = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$character) {
    die("Character not found.");
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_text = trim($_POST['comment_text']);
    $user_name = isset($_SESSION['username']) ? $_SESSION['username'] : trim($_POST['user_name'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;
    $captcha_input = trim($_POST['captcha_input'] ?? '');

    // Check CAPTCHA
    // if (!isset($_SESSION['captcha_code']) || strcasecmp($captcha_input, $_SESSION['captcha_code']) !== 0) {
    //     $_SESSION['captcha_error'] = "Incorrect CAPTCHA. Please try again.";
    //     $_SESSION['saved_comment'] = $comment_text; // preserve comment
    //     $_SESSION['saved_name'] = $user_name; // preserve name
    //     header("Location: view_character.php?id=$character_id");
    //     exit;
    // }

    if ($comment_text !== '') {
        $stmt = $db->prepare("
            INSERT INTO comments (character_id, user_name, user_id, comment_text, is_visible)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([$character_id, $user_name, $user_id, $comment_text]);

        // Clear used CAPTCHA
        // unset($_SESSION['captcha_code']);
        // unset($_SESSION['saved_comment']);
        // unset($_SESSION['saved_name']);

        // Redirect back to avoid duplicate submission
        header("Location: view_character.php?id=$character_id");
        exit;
    } else {
        echo "<p style='color:red;'>Please enter a comment.</p>";
    }
}

// Fetch visible comments for this character (latest first)
$stmt = $db->prepare("
    SELECT * FROM comments
    WHERE character_id = ? AND is_visible = 1
    ORDER BY created_at DESC
");
$stmt->execute([$character_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($character['name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1><?= htmlspecialchars($character['name']) ?></h1>

    <?php if (!empty($character['image_url'])): ?>
        <img src="<?= htmlspecialchars($character['image_url']) ?>" 
             alt="<?= htmlspecialchars($character['name']) ?>" width="300">
    <?php endif; ?>

    <p><strong>Film:</strong> <?= htmlspecialchars($character['film_name']) ?></p>
    <p><strong>Type:</strong> <?= htmlspecialchars($character['character_type']) ?></p>
    <p><?= nl2br(htmlspecialchars($character['description'])) ?></p>

    <hr>
    <h2>Leave a Comment</h2>

    <form method="post">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <label>Your Name:</label>
            <input type="text" name="user_name" required value="<?= htmlspecialchars($_SESSION['saved_name'] ?? '') ?>"><br><br>
            <?php unset($_SESSION['saved_name']); ?>
        <?php endif; ?>

        <label>Comment:</label><br>
        <textarea name="comment_text" rows="4" cols="40" required><?= htmlspecialchars($_SESSION['saved_comment'] ?? '') ?></textarea><br><br>
        <?php unset($_SESSION['saved_comment']); ?>

        <!-- <label>Enter CAPTCHA:</label><br>
        <img src="captcha.php" alt="CAPTCHA Image"><br><br>
        <input type="text" name="captcha_input" required><br><br> -->

        <button type="submit">Submit Comment</button>
    </form>

    <hr>
    <h2>Comments</h2>
    <?php if (empty($comments)): ?>
        <p>No comments yet. Be the first to comment!</p>
    <?php else: ?>
        <?php foreach ($comments as $c): ?>
            <div style="border:1px solid #ccc; margin-bottom:10px; padding:10px;">
                <p><strong><?= htmlspecialchars($c['user_name'] ?? 'Guest') ?></strong></p>
                <p><?= nl2br(htmlspecialchars($c['comment_text'])) ?></p>
                <p><em>Posted on <?= $c['created_at'] ?></em></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<p><a href="index.php">← Back to Home</a></p>
<!-- <p><a href="list_characters.php">← Back to character list</a></p> -->

</body>
</html>
