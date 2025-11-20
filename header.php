


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghibli World Archive</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">Ghibli World Archive</a></h1>

        <!-- Navigation links -->
        <nav>
            <a href="browse_characters.php">Browse Characters</a> |
            <a href="browse_categories.php">Browse by Category</a> |
            <a href="login.php">Login</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                | <a href="admin.php">Admin Panel</a>
                | 
                <form style="display:inline;" method="post" action="logout.php">
                    <button type="submit">Logout</button>
                </form>
           <?php// else: ?>
                | <a href="login.php">Login</a>
            <?php endif; ?> 
        </nav>

        <!-- Search form -->
        <form method="get" action="search_results.php" style="margin-top: 10px;">
            <input type="text" name="q" placeholder="Search..." required>
            <button type="submit">Search</button>
        </form>
        <hr>
    </header>
