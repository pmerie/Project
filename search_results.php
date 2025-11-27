<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
include 'header.php';

$search = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? 'all';
$searchWildcard = "%$search%";
$results = [];
$minLength = 3;

// Only perform search if minimum length is met
if (strlen($search) >= $minLength) {

    if ($category === 'all') {
        // Search across all categories
        $stmt = $db->prepare("
            SELECT characters.*, films.film_name
            FROM characters
            LEFT JOIN films ON characters.film_id = films.film_id
            WHERE characters.name LIKE ?
               OR characters.character_type LIKE ?
               OR characters.description LIKE ?
               OR films.film_name LIKE ?
            ORDER BY characters.created_at DESC
        ");
        $stmt->execute([$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Search only within a specific category
        $categoryId = (int)$category;
        $stmt = $db->prepare("
            SELECT characters.*, films.film_name
            FROM characters
            LEFT JOIN films ON characters.film_id = films.film_id
            WHERE (characters.name LIKE ?
               OR characters.character_type LIKE ?
               OR characters.description LIKE ?
               OR films.film_name LIKE ?)
              AND characters.category_id = ?
            ORDER BY characters.created_at DESC
        ");
        $stmt->execute([$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $categoryId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>

<main class="page-box">
    <h1>Search Results for "<?= htmlspecialchars($search) ?>"</h1>

    <?php if (empty($results)): ?>
        <p>No results found for your search.</p>
    <?php else: ?>
        <?php foreach ($results as $char): ?>
            <div class="character-card">
                <?php
                $imgSrc = '';
                if (!empty($char['uploaded_image']) && file_exists('uploads/' . $char['uploaded_image'])) {
                    $imgSrc = 'uploads/' . htmlspecialchars($char['uploaded_image']);
                } elseif (!empty($char['image_url'])) {
                    $imgSrc = htmlspecialchars($char['image_url']);
                }
                ?>
                <?php if ($imgSrc !== ''): ?>
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($char['name']) ?>" width="150">
                <?php endif; ?>

                <h2><?= htmlspecialchars($char['name']) ?></h2>
                <p><strong>Character Type:</strong> <?= htmlspecialchars($char['character_type']) ?></p>
                <p><?= nl2br(htmlspecialchars($char['description'])) ?></p>
                <p><em>Film:</em> <?= htmlspecialchars($char['film_name'] ?? 'Unknown') ?></p>

                <a href="view_character.php?id=<?= $char['character_id'] ?>">View Character</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="index.php">← Back to Home</a></p>
</main>
