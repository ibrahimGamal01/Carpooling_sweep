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
$rideId = isset($_POST['ride_id']) ? $_POST['ride_id'] : '';

// Validate the required ride ID
if (empty($rideId)) {
    die("Error: Ride ID is missing.");
}

// Retrieve the passenger name from the request
$passengerName = isset($_POST['passenger_name']) ? $_POST['passenger_name'] : '';

// Validate the required passenger name
if (empty($passengerName)) {
    die("Error: Passenger name is missing.");
}

// Check if the ride exists
$rideCheckStmt = $conn->prepare("SELECT * FROM rides WHERE ride_id = ?");
$rideCheckStmt->bind_param("i", $rideId);
$rideCheckStmt->execute();
$rideCheckResult = $rideCheckStmt->get_result();

if ($rideCheckResult->num_rows > 0) {
    $ride = $rideCheckResult->fetch_assoc();

    // Check if there are available seats in the ride
    if ($ride['available_seats'] > 0) {
        // Prepare and execute the SQL query to insert the passenger into the ride
        $insertStmt = $conn->prepare("INSERT INTO passengers (ride_id, name) VALUES (?, ?)");
        $insertStmt->bind_param("is", $rideId, $passengerName);

        if ($insertStmt->execute()) {
            // Update the available seats count
            $updatedSeats = $ride['available_seats'] - 1;

            // Prepare and execute the SQL query to update the available seats count
            $updateSeatsStmt = $conn->prepare("UPDATE rides SET available_seats = ? WHERE ride_id = ?");
            $updateSeatsStmt->bind_param("ii", $updatedSeats, $rideId);
            $updateSeatsStmt->execute();

            // Close the database connection
            $updateSeatsStmt->close();
            $insertStmt->close();
            $conn->close();

            // Return success response
            $response = array(
                'rideId' => $rideId,
                'passenger' => $passengerName,
                'seats' => $updatedSeats
            );

            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo "Error: Failed to join the ride.";
        }
    } else {
        echo "Error: No available seats in the ride.";
    }
} else {
    echo "Error: Ride not found.";
}

$rideCheckStmt->close();
?>
