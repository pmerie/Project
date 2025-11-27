<?php
require 'db_connect.php';
include 'header.php';

// Fetch all characters with film name
$stmt = $db->query("
    SELECT characters.*, films.film_name 
    FROM characters 
    LEFT JOIN films ON characters.film_id = films.film_id
    ORDER BY characters.created_at DESC
");
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>

    <!-- Characters List -->
    <?php if (empty($characters)): ?>
        <p>No characters added yet.</p>
    <?php else: ?>
        <?php foreach ($characters as $char): ?>
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
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($char['name']) ?>" width="200">
                <?php endif; ?>

                <h2><?= htmlspecialchars($char['name']) ?></h2>
                <p><strong>Character Type:</strong> <?= htmlspecialchars($char['character_type']) ?></p>
                <p><?= htmlspecialchars($char['description']) ?></p>
                <p><em>Film:</em> <?= htmlspecialchars($char['film_name'] ?? 'Unknown') ?></p>
                <a href="view_character.php?id=<?= $char['character_id'] ?>">View Character</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</main>

<p><a href="list_characters.php">← Back to character list</a></p>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.character-card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin-bottom: 15px; max-width: 400px; }
.character-card img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; border-radius: 5px; }
</style>
