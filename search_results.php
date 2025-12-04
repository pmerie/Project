<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';
include 'header.php';

$resultsPerPage = 2;

$search = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? 'all';
$searchWildcard = "%$search%";
$minLength = 1;

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$startFrom = ($page - 1) * $resultsPerPage;

$totalResults = 0;
$results = [];

if (strlen($search) >= $minLength) {

    if ($category === 'all') {

        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM characters
            WHERE characters.name LIKE :searchWildcard
        ");
        $countStmt->execute([':searchWildcard' => $searchWildcard]);
        $totalResults = $countStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT characters.*, films.film_name
            FROM characters
            LEFT JOIN films ON characters.film_id = films.film_id
            WHERE characters.name LIKE :searchWildcard
            ORDER BY characters.created_at DESC
            LIMIT :start, :limit
        ");
        $stmt->bindValue(':searchWildcard', $searchWildcard, PDO::PARAM_STR);
        $stmt->bindValue(':start', $startFrom, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
        $stmt->execute();

    } else {

        $categoryId = (int)$category;

        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM characters
            WHERE characters.name LIKE :searchWildcard
              AND category_id = :category
        ");
        $countStmt->execute([
            ':searchWildcard' => $searchWildcard,
            ':category' => $categoryId
        ]);
        $totalResults = $countStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT characters.*, films.film_name
            FROM characters
            LEFT JOIN films ON characters.film_id = films.film_id
            WHERE characters.name LIKE :searchWildcard
              AND category_id = :category
            ORDER BY characters.created_at DESC
            LIMIT :start, :limit
        ");
        $stmt->bindValue(':searchWildcard', $searchWildcard, PDO::PARAM_STR);
        $stmt->bindValue(':category', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':start', $startFrom, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
        $stmt->execute();
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$totalPages = ceil($totalResults / $resultsPerPage);
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

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?q=<?= urlencode($search) ?>&category=<?= $category ?>&page=<?= $page - 1 ?>">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <strong><?= $i ?></strong>
                    <?php else: ?>
                        <a href="?q=<?= urlencode($search) ?>&category=<?= $category ?>&page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?q=<?= urlencode($search) ?>&category=<?= $category ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <p><a href="index.php">← Back to Home</a></p>
</main>
