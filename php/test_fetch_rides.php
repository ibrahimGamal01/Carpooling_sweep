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

function testFetchRides()
{
    $testCases = array(
        array(
            'test' => 'Same locations for pickup and drop-off',
            'pickup' => 'Bonn Central Station, Bonn, Germany',
            'dropoff' => 'Bonn Central Station, Bonn, Germany'
        ),
        array(
            'test' => 'Same direction of drop-off locations',
            'pickup' => 'Bonn Central Station, Bonn, Germany',
            'dropoff' => 'Beethoven House, Bonn, Germany'
        ),
        array(
            'test' => 'Same direction (passing by) pickup location',
            'pickup' => 'Bonn University, Bonn, Germany',
            'dropoff' => 'Bonn Central Station, Bonn, Germany'
        )
    );

    // Insert test data into the rides table
    $driverId = 1; // Assuming the driver's ID is 1

    // Test Case 1: Same locations for pickup and drop-off
    $pickup1 = 'Bonn Central Station, Bonn, Germany';
    $dropoff1 = 'Bonn Central Station, Bonn, Germany';
    insertRide($driverId, $pickup1, $dropoff1);

    // Test Case 2: Same direction of drop-off locations
    $pickup2 = 'Bonn Central Station, Bonn, Germany';
    $dropoff2 = 'Beethoven House, Bonn, Germany';
    insertRide($driverId, $pickup2, $dropoff2);

    // Test Case 3: Same direction (passing by) pickup location
    $pickup3 = 'Bonn University, Bonn, Germany';
    $dropoff3 = 'Bonn Central Station, Bonn, Germany';
    insertRide($driverId, $pickup3, $dropoff3);

    foreach ($testCases as $testCase) {
        $pickup = $testCase['pickup'];
        $dropoff = $testCase['dropoff'];

        $requestData = array(
            'pickup' => $pickup,
            'dropoff' => $dropoff
        );

        $jsonData = json_encode($requestData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/php/fetch_rides.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        $rides = json_decode($response, true);

        echo "Test: " . $testCase['test'] . "\n";
        echo "Pickup Location: " . $pickup . "\n";
        echo "Drop-off Location: " . $dropoff . "\n";

        if (!empty($rides)) {
            echo "Matching Rides:\n";
            foreach ($rides as $ride) {
                echo "Ride ID: " . $ride['ride_id'] . "\n";
                echo "Driver ID: " . $ride['driver_id'] . "\n";
                echo "Pickup Location: " . $ride['pickup_location'] . "\n";
                echo "Drop-off Location: " . $ride['dropoff_location'] . "\n";
                echo "Available Seats: " . $ride['available_seats'] . "\n";
                echo "Status: " . $ride['status'] . "\n";
                echo "\n";
            }
        } else {
            echo "No matching rides found.\n";
        }

        echo "---------------------------\n";
    }
}

function insertRide($driverId, $pickup, $dropoff)
{
    global $conn;

    $status = 'upcoming';
    $availableSeats = 4; // Assuming there are 4 available seats

    $stmt = $conn->prepare("INSERT INTO rides (driver_id, pickup_location, dropoff_location, available_seats, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $driverId, $pickup, $dropoff, $availableSeats, $status);
    $stmt->execute();
    $stmt->close();
}

// Run the test function
testFetchRides();

// Close the database connection
$conn->close();
?>
