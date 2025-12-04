<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

// Check if user is admin
$stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

// Fetch categories
$categoryStmt = $db->query("SELECT * FROM categories ORDER BY category_name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id'];
$success = '';
$fieldErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $film_name = trim($_POST['film_name'] ?? '');
    $character_type = trim($_POST['character_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $category_id = $_POST['category_id'] ?? '';

    // FIELD VALIDATIONS 
    if ($name === '') $fieldErrors['name'] = "Name is required.";
    elseif (strlen($name) > 255) $fieldErrors['name'] = "Name cannot exceed 255 characters.";

    if ($film_name === '') $fieldErrors['film_name'] = "Film name is required.";
    elseif (strlen($film_name) > 255) $fieldErrors['film_name'] = "Film name cannot exceed 255 characters.";

    if ($character_type === '') $fieldErrors['character_type'] = "Character Type is required.";
    elseif (strlen($character_type) > 100) $fieldErrors['character_type'] = "Character Type cannot exceed 100 characters.";

    if ($description === '') $fieldErrors['description'] = "Description is required.";
    elseif (strlen($description) > 1000) $fieldErrors['description'] = "Description cannot exceed 1000 characters.";

    // Validate Image URL if provided
    if ($image_url && !filter_var($image_url, FILTER_VALIDATE_URL)) {
        $fieldErrors['image_url'] = "Invalid URL format.";
    }

    // IMAGE UPLOAD HANDLING 
    $uploadedImage = null;

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_file']['tmp_name'];
        $originalFileName = basename($_FILES['image_file']['name']);
        $fileExt = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExt, $allowedTypes)) {
            $fieldErrors['image_file'] = "❌ Only JPG, PNG, and GIF images are allowed.";
        } else {
            $uploadsDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);

            $newFilePath = $uploadsDir . $originalFileName;

            $imageInfo = getimagesize($fileTmpPath);
            list($origWidth, $origHeight) = $imageInfo;
            $maxWidth = 400;
            $maxHeight = 400;
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1);
            $newWidth = (int)($origWidth * $ratio);
            $newHeight = (int)($origHeight * $ratio);

            switch ($imageInfo['mime']) {
                case 'image/jpeg': $srcImage = imagecreatefromjpeg($fileTmpPath); break;
                case 'image/png': $srcImage = imagecreatefrompng($fileTmpPath); break;
                case 'image/gif': $srcImage = imagecreatefromgif($fileTmpPath); break;
                default: $srcImage = null; $fieldErrors['image_file'] = "❌ Unsupported image type.";
            }

            if ($srcImage) {
                $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                if ($imageInfo['mime'] === 'image/png' || $imageInfo['mime'] === 'image/gif') {
                    imagecolortransparent($dstImage, imagecolorallocatealpha($dstImage, 0,0,0,127));
                    imagealphablending($dstImage,false);
                    imagesavealpha($dstImage,true);
                }
                imagecopyresampled($dstImage, $srcImage,0,0,0,0,$newWidth,$newHeight,$origWidth,$origHeight);

                switch ($imageInfo['mime']) {
                    case 'image/jpeg': imagejpeg($dstImage, $newFilePath, 85); break;
                    case 'image/png': imagepng($dstImage, $newFilePath); break;
                    case 'image/gif': imagegif($dstImage, $newFilePath); break;
                }

                imagedestroy($srcImage);
                imagedestroy($dstImage);
                $uploadedImage = 'uploads/' . $originalFileName; 
            }
        }
    }

    //  DATABASE INSERT 
    if (empty($fieldErrors)) {
        // Check if film exists
        $stmt = $db->prepare("SELECT film_id FROM films WHERE film_name = ? LIMIT 1");
        $stmt->execute([$film_name]);
        $film = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($film) $film_id = $film['film_id'];
        else {
            $stmt = $db->prepare("INSERT INTO films (film_name, year, image_url, description, director_id, genre_id, user_id) VALUES (?, 0, NULL, '', 1, 1, ?)");
            $stmt->execute([$film_name, $user_id]);
            $film_id = $db->lastInsertId();
        }

        // Determine final image
        $finalImage = $uploadedImage ?? $image_url ?? null;

        $stmt = $db->prepare("
            INSERT INTO characters (name, film_id, character_type, description, image_url, category_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $film_id,
            $character_type ?: '',
            $description,
            $finalImage,
            $category_id
        ]);

        $success = "✅ Character added successfully!";
        $_POST = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Add Character</title>
<link rel="stylesheet" href="style.css">
<style>
    .error { color: red; font-size: 0.9em; margin-top: 2px; }
</style>
</head>
<body>
<div class="page-box">
<h1>Admin - Add New Character</h1>


<p>
    <a href="list_characters.php">View All Characters</a> |
    <a href="list_users.php">Manage Users</a> |
    <a href="index.php">Home</a> |
    <a href="categories.php">Manage Categories</a> |
    <a href="comments.php">Moderate Comments</a> 
    
</p>

<?php if ($success): ?>
<p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['name'] ?? '' ?></div>
    <br>

    <label>Film Name:</label><br>
    <input type="text" name="film_name" value="<?= htmlspecialchars($_POST['film_name'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['film_name'] ?? '' ?></div>
    <br>

    <label>Character Type:</label><br>
    <input type="text" name="character_type" value="<?= htmlspecialchars($_POST['character_type'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['character_type'] ?? '' ?></div>
    <br>

    <label>Description:</label><br>
    <textarea name="description" rows="5" cols="40"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    <div class="error"><?= $fieldErrors['description'] ?? '' ?></div>
    <br>

    <label>Image URL:</label><br>
    <input type="text" name="image_url" value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
    <div class="error"><?= $fieldErrors['image_url'] ?? '' ?></div>
    <br>

    <label>Upload Image:</label><br>
    <input type="file" name="image_file" accept="image/*">
    <div class="error"><?= $fieldErrors['image_file'] ?? '' ?></div>
    <br>

    <label>Category:</label>
    <select name="category_id">
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id']?>"
                <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : '' ?> >
                <?= htmlspecialchars($cat['category_name'])?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="error"><?= $fieldErrors['category_id'] ?? '' ?></div>
    <br><br>

    <button type="submit">Add Character</button>
</form>

<form action="logout.php" method="post" style="display:inline;"><br>
    <button type="submit">Logout</button>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fields = ['name','film_name','character_type','description','image_url','category_id'];
    fields.forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('input', () => {
                const errorDiv = field.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error')) errorDiv.textContent = '';
            });
            field.addEventListener('change', () => {
                const errorDiv = field.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error')) errorDiv.textContent = '';
            });
        }
    });
});
</script>
</body>
</html>
