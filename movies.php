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
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : '';

if($orderBy != "") {
    switch($orderBy) {
        case '0':
            // Order by movie year in ascending order
            $orderBy = 'release_date ASC';
            break;
        case '1':
            // Order by movie year in descending order
            $orderBy = 'release_date DESC';
            break;
        case '2':
            // Order by movie title in ascending order
            $orderBy = 'release_date ASC';
            break;
        case '3':
            // Order by movie title in descending order
            $orderBy = 'release_date DESC';
            break;
    }
}

if ($year != "") {
    $stmt = $conn->prepare("SELECT * FROM links WHERE release_date = ?");
    $stmt->bind_param("s", $year);
}
else {
    $stmt = $conn->prepare("SELECT * FROM links WHERE movie_title LIKE ? " . ($orderBy != "" ? "ORDER BY " . $orderBy : ""));
    $stmt->bind_param("s", $search);
}

$stmt->execute();
$result = $stmt->get_result();
$movies = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($movies);

$stmt->close();
$conn->close();
?>