<?php
session_start(); 
require 'db_connect.php';
include 'header.php';


//  Pagination settings
$results_per_page = 5; // N = results 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Fetch characters with LIMIT for pagination
$stmt = $db->prepare("
    SELECT characters.*, films.film_name 
    FROM characters 
    LEFT JOIN films ON characters.film_id = films.film_id
    ORDER BY characters.created_at DESC
    LIMIT :start, :per_page
");
$stmt->bindValue(':start', $start_from, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $results_per_page, PDO::PARAM_INT);
$stmt->execute();
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Count total characters
$total_results_stmt = $db->query("SELECT COUNT(*) FROM characters");
$total_results = $total_results_stmt->fetchColumn();

// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);
?>



<main>

    <!-- Characters List -->
    <?php if (isset($_SESSION['user_id'])): ?>

    <?php if (empty($characters)): ?>
        <p>No characters added yet.</p>
    <?php else: ?>
        <div class="character-container">
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
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($char['name']) ?>">
                <?php endif; ?>

                <h2><?= htmlspecialchars($char['name']) ?></h2>
                <p><strong>Character Type:</strong> <?= htmlspecialchars($char['character_type']) ?></p>
                <p><?= htmlspecialchars($char['description']) ?></p>
                <p><em>Film:</em> <?= htmlspecialchars($char['film_name'] ?? 'Unknown') ?></p>
                <a href="view_character.php?id=<?= $char['character_id'] ?>">View Character</a>
            </div>
        <?php endforeach; ?>
        </div>

        <!-- Pagination Links -->
        <?php /* if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <strong><?= $i ?></strong>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; */?>

    <?php endif; ?>

    <?php endif;?>

    <!-- ⭐ Logout button at the very bottom (only shown when logged in) -->
    <!-- <?//php if (isset($_SESSION['user_id'])): ?>
        <div style="margin-top: 25px;">
            <form method="post" action="logout.php">
                <button type="submit">Logout</button>
            </form>
        </div>
    <?//php endif; ?> -->

</main>
<?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <p><a href="list_characters.php">←Back to character list</a></p>
<?php endif; ?>




    <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .character-card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin-bottom: 15px; max-width: 400px; }
    .character-card img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; border-radius: 5px; }

    /* Pagination styling */
    .pagination {
        margin-top: 20px;
        text-align: center;
        gap: 5px;
    }

    .pagination a, .pagination strong {
        display: inline-block;
        padding: 5px 10px;
        margin: 0 3px;
        border: 1px solid #618264;
        border-radius: 5px;
        text-decoration: none;
        color: #618264;
    }

    .pagination strong {
        background-color: #618264;
        color: #fff;
    }
    </style>
