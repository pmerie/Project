<?php
session_start();
require 'db_connect.php';

// ------------------- AUTH CHECK -------------------
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user role
$stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

$message = "";

//  HANDLE ADD / UPDATE 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category_name = trim($_POST['category_name']);
    $category_id   = $_POST['category_id'] ?? '';

    // Validate numericality of category_id
    if ($category_id !== '' && !ctype_digit($category_id)) {
        die("❌ Invalid category ID.");
    }
    $category_id = (int) $category_id;

    // Validate category name
    if ($category_name === '') {
        $message = "⚠️ Category name is required.";
    } elseif (strlen($category_name) < 3) {
        $message = "⚠️ Category name must be at least 3 characters.";
    } elseif (strlen($category_name) > 50) {
        $message = "⚠️ Category name cannot exceed 50 characters.";
    } elseif (!preg_match('/^[A-Za-z0-9\s\-]+$/', $category_name)) {
        $message = "⚠️ Category name contains invalid characters.";
    } else {
        // Check for duplicates
        $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ? AND category_id != ?");
        $stmt->execute([$category_name, $category_id]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $message = "❌ Category name already exists.";
        } else {
            if ($category_id) {
                $stmt = $db->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
                $stmt->execute([$category_name, $category_id]);
                $message = "✅ Category updated successfully!";
            } else {
                $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES (?)");
                $stmt->execute([$category_name]);
                $message = "✅ Category added successfully!";
            }
        }
    }
}

// LOAD CATEGORY FOR EDIT 
$editCategory = null;
if (isset($_GET['edit'])) {
    if (!ctype_digit($_GET['edit'])) {
        die("❌ Invalid category ID.");
    }
    $edit_id = (int) $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$edit_id]);
    $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}

// DELETE CATEGORY 
if (isset($_GET['delete'])) {
    if (!ctype_digit($_GET['delete'])) {
        die("❌ Invalid category ID.");
    }
    $delete_id = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$delete_id]);
    $message = "✅ Category deleted successfully";
}

// FETCH ALL CATEGORIES 
$stmt = $db->query("SELECT * FROM categories ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Categories</title>
<link rel="stylesheet" href="style.css">
<style>
    table { border-collapse: collapse; width: 50%; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #618264; }
    .message { font-weight: bold; }
</style>
</head>
<body>
<h1>Manage Categories</h1>

<p>
    <a href="list_characters.php">View All Characters</a> |
    <a href="admin.php">Add New Character</a> |
    <a href="index.php">Home</a>
</p>

<?php if ($message): ?>
    <p class="message" style="color: <?= str_starts_with($message, '✅') ? 'green' : 'red' ?>;">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<!-- Add / Edit Form -->
<form method="post">
    <input type="hidden" name="category_id" value="<?= htmlspecialchars($editCategory['category_id'] ?? '') ?>">
    <label>Category Name:
        <input type="text" name="category_name" value="<?= htmlspecialchars($editCategory['category_name'] ?? '') ?>" required>
    </label>
    <button type="submit"><?= $editCategory ? 'Update' : 'Add' ?> Category</button>
</form>

<h2>Existing Categories</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($categories as $c): ?>
    <tr>
        <td><?= $c['category_id'] ?></td>
        <td><?= htmlspecialchars($c['category_name']) ?></td>
        <td>
            <a href="?edit=<?= $c['category_id'] ?>">Edit</a> |
            <a href="?delete=<?= $c['category_id'] ?>" onclick="return confirm('Delete this category?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
