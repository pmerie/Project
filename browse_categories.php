<?php
session_start();
require 'db_connect.php';

// Fetch all categories
$stmt = $db->prepare("SELECT * FROM categories ORDER BY category_name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Categories</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Browse by Category</h1>

<?php if (empty($categories)): ?>
    <p>No categories available.</p>
<?php else: ?>
    <form id="categoryForm">
        <label for="categorySelect">Select a category:</label>
        <select id="categorySelect" name="category">
            <option value="">-- Choose a category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="category_view.php?id=<?= $cat['category_id'] ?>">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Go</button>
    </form>

    <script>
        const form = document.getElementById('categoryForm');
        const select = document.getElementById('categorySelect');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = select.value;
            if (url) {
                window.location.href = url;
            }
        });
    </script>
<?php endif; ?>

<p><a href="index.php">Back to Home</a></p>
</body>
</html>
