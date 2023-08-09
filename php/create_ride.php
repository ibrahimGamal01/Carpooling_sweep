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

// Retrieve ride details from the request
$requestData = json_decode(file_get_contents('php://input'), true);
$pickupLocation = isset($requestData['pickupLocation']) ? $requestData['pickupLocation'] : '';
$dropoffLocation = isset($requestData['dropoffLocation']) ? $requestData['dropoffLocation'] : '';
$seats = isset($requestData['seats']) ? $requestData['seats'] : '';

// Validate the required fields
if (empty($pickupLocation) || empty($dropoffLocation) || empty($seats)) {
  die("Error: Required fields are missing.");
}

// Function to get coordinates using PositionStack Geocoding API
function getCoordinates($query) {
  $apiKey = '54852a7462e1c40cc8fca727234d19cf';
  $apiUrl = 'http://api.positionstack.com/v1/forward?access_key=' . $apiKey . '&query=' . urlencode($query);

  // Make the API request using cURL
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);

  // Parse the API response to extract latitude and longitude
  $responseData = json_decode($response, true);
  if (isset($responseData['data'][0]['latitude']) && isset($responseData['data'][0]['longitude'])) {
    return array(
      'latitude' => $responseData['data'][0]['latitude'],
      'longitude' => $responseData['data'][0]['longitude']
    );
  }

  return null; // Return null if coordinates not found
}

// Get the driver's ID (You can modify this part to match your authentication system)
$driverId = 13; // Assuming the driver's ID is 13

// Get coordinates for pickup and dropoff locations
$pickupCoordinates = getCoordinates($pickupLocation);
$dropoffCoordinates = getCoordinates($dropoffLocation);

if (!$pickupCoordinates || !$dropoffCoordinates) {
  die("Error: Failed to get coordinates for pickup or drop-off location.");
}

// Prepare and execute the SQL query to create a new ride
$stmt = $conn->prepare("INSERT INTO rides (driver_id, pickup_location, pickup_latitude, pickup_longitude, dropoff_location, dropoff_latitude, dropoff_longitude, available_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'upcoming')");
$stmt->bind_param("issssssi", $driverId, $pickupLocation, $pickupCoordinates['latitude'], $pickupCoordinates['longitude'], $dropoffLocation, $dropoffCoordinates['latitude'], $dropoffCoordinates['longitude'], $seats);

if ($stmt->execute()) {
  // Get the newly created ride's ID
  $rideId = $stmt->insert_id;

  // Close the database connection
  $stmt->close();
  $conn->close();

  // Return the ride details as JSON response
  $response = array(
    'rideId' => $rideId,
    'driver' => 'John Doe', // Replace with the actual driver's name
    'pickup' => $pickupLocation,
    'pickupLatitude' => $pickupCoordinates['latitude'],
    'pickupLongitude' => $pickupCoordinates['longitude'],
    'dropoff' => $dropoffLocation,
    'dropoffLatitude' => $dropoffCoordinates['latitude'],
    'dropoffLongitude' => $dropoffCoordinates['longitude'],
    'seats' => $seats
  );

  echo json_encode($response);
} else {
  echo "Error: " . $stmt->error;
}
?>
