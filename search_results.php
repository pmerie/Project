<?php
require 'db_connect.php';
include 'header.php'; // header with search form & navbar

$search = trim($_GET['q'] ?? '');
$searchWildcard = "%$search%";

// Search characters and films
$stmt = $db->prepare("
    SELECT characters.*, films.film_name 
    FROM characters 
    LEFT JOIN films ON characters.film_id = films.film_id
    WHERE characters.name LIKE ? 
       OR characters.character_type LIKE ?
       OR films.film_name LIKE ?
       OR characters.description LIKE ?
    ORDER BY characters.created_at DESC
");
$stmt->execute([$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="page-box">
    <h1>Search Results for "<?= htmlspecialchars($search) ?>"</h1>

    <?php if (empty($results)): ?>
        <p>No results found for your search.</p>
    <?php else: ?>
        <?php foreach ($results as $char): ?>
            <div class="character-card">
                <?php if (!empty($char['image_url'])): ?>
                    <img src="<?= htmlspecialchars($char['image_url']) ?>" alt="<?= htmlspecialchars($char['name']) ?>" width="150">
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

<style>
.page-box {
    background-color: #D0E7D2;
    border: 2px solid #79AC78;
    border-radius: 14px;
    padding: 25px;
    max-width: 900px;
    margin: 30px auto;
}

.character-card {
    border: 1px solid #B0D9B1;
    background-color: #ffffff;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.character-card h2 {
    color: #618264; /* dark green title */
}

.character-card p {
    color: #333; /* description & text */
}

.character-card a {
    color: #79AC78;
    font-weight: bold;
    text-decoration: none;
}

.character-card a:hover {
    text-decoration: underline;
}
</style>
