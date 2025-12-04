<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghibli World Archive</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
    <header>
        <h1><a href="index.php">Ghibli World Archive</a></h1>

        <!-- Navigation links -->
        <nav>
            <div class="nav-right">
            <a href="browse_characters.php">Browse Characters</a> |
            <a href="browse_categories.php">Browse by Category</a> |
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php">Login</a>
            <?php else: ?>
                <a href="admin.php">Admin Panel</a> |
                <form style="display:inline;" method="post" action="logout.php">
                    <button type="submit">Logout</button>
                </form>
            <?php endif; ?>
            </div>
        </nav>

        <?php
     
        require_once 'db_connect.php';
        $catStmt = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <!-- Search form with category filter -->
        <form method="get" action="search_results.php" style="margin-top: 10px;">
            <input type="text" name="q" placeholder="Search..." >

            <select name="category">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Search</button>
        </form>

        <hr>
    </header>
