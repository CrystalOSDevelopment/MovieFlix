<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "links_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$year = isset($_GET['year']) ? $_GET['year'] : '';
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

if ($year != "") {
    $stmt = $conn->prepare("SELECT * FROM links WHERE release_date = ?");
    $stmt->bind_param("s", $year);
} else {
    $stmt = $conn->prepare("SELECT * FROM links WHERE movie_title LIKE ?");
    $stmt->bind_param("s", $search);
}

$stmt->execute();
$result = $stmt->get_result();
$movies = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($movies);

$stmt->close();
$conn->close();
?>