<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "carpooling";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all rides from the rides table
$sql = "SELECT * FROM rides";
$result = $conn->query($sql);
$rides = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rides[] = $row;
    }
}

// Return all rides as JSON response
header('Content-Type: application/json');
echo json_encode($rides);

// Close the database connection
$conn->close();
?>
