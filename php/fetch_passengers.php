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

// Retrieve pickup and drop-off locations from the request
$requestData = json_decode(file_get_contents("php://input"), true);
$pickup = $requestData['pickup'];
$dropoff = $requestData['dropoff'];

// Create the OpenStreetMap Nominatim API request URLs for pickup and dropoff locations
$pickupUrl = "https://nominatim.openstreetmap.org/search?q=" . urlencode($pickup) . "&format=json";
$dropoffUrl = "https://nominatim.openstreetmap.org/search?q=" . urlencode($dropoff) . "&format=json";

// Fetch the geocoding data for pickup location from the OpenStreetMap Nominatim API
$pickupData = file_get_contents($pickupUrl);
$pickupData = json_decode($pickupData, true);

// Fetch the geocoding data for dropoff location from the OpenStreetMap Nominatim API
$dropoffData = file_get_contents($dropoffUrl);
$dropoffData = json_decode($dropoffData, true);

// Extract the latitude and longitude of the pickup location
$pickupLat = $pickupData[0]['lat'];
$pickupLon = $pickupData[0]['lon'];

// Extract the latitude and longitude of the dropoff location
$dropoffLat = $dropoffData[0]['lat'];
$dropoffLon = $dropoffData[0]['lon'];

// Define the maximum allowed distance between the pickup and dropoff points in kilometers
$maxDistance = 10;

// Prepare and execute the SQL query to fetch rides with a similar route
$stmt = $conn->prepare("SELECT * FROM rides WHERE status = 'upcoming' AND dropoff_location <> ?");
$stmt->bind_param("s", $dropoff);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the rides and store them in an array
$rides = array();
while ($row = $result->fetch_assoc()) {
  // Calculate the distance between the pickup location and ride's pickup location
  $pickupDistance = calculateDistance($pickupLat, $pickupLon, $row['pickup_latitude'], $row['pickup_longitude']);

  // Calculate the distance between the dropoff location and ride's pickup location
  $dropoffDistance = calculateDistance($dropoffLat, $dropoffLon, $row['pickup_latitude'], $row['pickup_longitude']);

  // If the total distance is within the allowed range, add the ride to the result
  if (($pickupDistance + $dropoffDistance) <= $maxDistance) {
    $rides[] = $row;
  }
}

// Close the database connection
$stmt->close();
$conn->close();

// Return the rides as JSON response
echo json_encode($rides);

// Calculate the distance between two coordinates using the Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
  $earthRadius = 6371; // Radius of the Earth in kilometers

  $deltaLat = deg2rad($lat2 - $lat1);
  $deltaLon = deg2rad($lon2 - $lon1);

  $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLon / 2) * sin($deltaLon / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  $distance = $earthRadius * $c;
  return $distance;
}
?>
