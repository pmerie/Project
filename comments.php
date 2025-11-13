<?php
session_start();
require 'db_connect.php';

//logged in users (admin) can acess
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
}

//Delete comment
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->execute([$_GET['delete']]);
}

//Hide comment
if (isset($_GET['hide'])) {
    $stmt = $db->prepare("UPDATE comments SET is_hidden = 1 WHERE comment_id = ?");
    $stmt->execute([$_GET['hide']]);
}

//Unhide comment
if (isset($_GET['unhide'])) {
    $stmt = $db->prepare("UPDATE comments SET is_hidden = 0 WHERE comment_id = ?");
    $stmt->execute([$_GET['unhide']]);
}

//Fetch all comments with character/page info
$stmt = $db->query("
    SELECT c.*, ch.name AS character_name
    FROM comments c
    JOIN characters ch ON c.character_id = ch.character_id
    ORDER BY c.created_at DESC
");
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments</title>
</head>
<body>
    <h1>Moderate Comments</h1>
    <p>
        <a href="admin.php">Back to Admin Panel</a>
    </p>
    <?php if (empty($comments)):?>
        <p>No comments submitted yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>ID</th>
                <th>Character/Page</th>
                <th>User</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($comments as $c): ?>
                <tr>
                    <td><?= $c['comment_id']?></td>
                    <td><?= htmlspecialchars($c['character_name'])?></td>
                    <td><?= htmlspecialchars($c['user_name'] ?? 'Guest')?></td>
                    <td><?= htmlspecialchars($c['comment_text'])?></td>
                    <td><?= $c['is_hidden'] ? 'Hidden' : 'Visible' ?></td>
                    <td>
                        <?php if (!$c['is_hidden']): ?>
                            <a href="?hide=<?= $c['comment_id'] ?>">Hide</a> |
                        <?php else: ?>
                            <a href="?unhide=<?= $c['comment_id'] ?>">Unhide</a> |
                        <?php endif;?>
                            <a href="?delete=<?= $c['comment_id'] ?>" onclick="return confirm('Delete this comment?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach;?>
        </table>
    <?php endif;?>
</body>
</html>