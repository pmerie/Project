<?php
// Database connection
define('DB_DSN', 'mysql:host=localhost;dbname=studio_ghibli_cms;charset=utf8');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
