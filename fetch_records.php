<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=links_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM links");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($movies);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
}
?>