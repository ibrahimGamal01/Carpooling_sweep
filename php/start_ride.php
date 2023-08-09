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

// Retrieve the ride ID from the request
$requestData = json_decode(file_get_contents("php://input"), true);
$rideId = $requestData['rideId'];

// Prepare and execute the SQL query to update the ride status
$stmt = $conn->prepare("UPDATE rides SET status = 'in progress' WHERE ride_id = ?");
$stmt->bind_param("i", $rideId);
$stmt->execute();

// Close the database connection
$stmt->close();
$conn->close();

// Return success response as JSON
$response = array('status' => 'success');
echo json_encode($response);
?>
