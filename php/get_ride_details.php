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

// Fetch the latest ride and passenger details
$rideDetailsQuery = "SELECT r.ride_id, r.driver_id, r.pickup_location, r.dropoff_location, r.available_seats, 
                    p.id AS passenger_id, p.name AS passenger_name
                    FROM rides r
                    LEFT JOIN passengers p ON r.ride_id = p.ride_id
                    ORDER BY r.ride_id DESC";

$rideDetailsResult = $conn->query($rideDetailsQuery);

if ($rideDetailsResult->num_rows > 0) {
    $rides = array();
    while ($row = $rideDetailsResult->fetch_assoc()) {
        $rideId = $row['ride_id'];
        $driverId = $row['driver_id'];
        $pickupLocation = $row['pickup_location'];
        $dropoffLocation = $row['dropoff_location'];
        $availableSeats = $row['available_seats'];
        $passengerId = $row['passenger_id'];
        $passengerName = $row['passenger_name'];

        // Check if the ride already exists in the array
        if (!isset($rides[$rideId])) {
            $rides[$rideId] = array(
                'rideId' => $rideId,
                'driverId' => $driverId,
                'pickupLocation' => $pickupLocation,
                'dropoffLocation' => $dropoffLocation,
                'availableSeats' => $availableSeats,
                'passengers' => array()
            );
        }

        // Add the passenger to the ride's passengers list if available
        if ($passengerId && $passengerName) {
            $rides[$rideId]['passengers'][] = array(
                'id' => $passengerId,
                'name' => $passengerName
            );
        }
    }

    $conn->close();
    header('Content-Type: application/json');
    echo json_encode(array_values($rides)); // Convert the associative array to a numerical array and echo the JSON response
} else {
    $conn->close();
    echo json_encode(array()); // Return an empty array if no ride available
}
?>
