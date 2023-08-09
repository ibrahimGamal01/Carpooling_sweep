<?php
function getRouteData($apiKey, $startLat, $startLng, $endLat, $endLng)
{
    $apiUrl = "https://api.openrouteservice.org/v2/directions/driving-car";

    // Create cURL resource
    $ch = curl_init();

    // Set cURL options
    $url = "{$apiUrl}?api_key={$apiKey}&start={$startLng},{$startLat}&end={$endLng},{$endLat}";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request and get the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    // Close cURL resource
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Return the route data
    return $data;
}

$apiKey = "5b3ce3597851110001cf6248be0e4460401f440e838f122fa8bab5da";

// Test route coordinates for rides 1 and 2
$ride1StartLatitude = 50.704129514100735;
$ride1StartLongitude = 7.16153100070237;
$ride1EndLatitude = 50.712129514100735;
$ride1EndLongitude = 7.17153100070237;

// Set fixed start and end times for ride 1
$ride1StartTime = '2023-07-20T13:00:00Z';
$ride1EndTime = '2023-07-20T13:10:00Z';

// Set fixed start and end times for ride 2 that match with ride 1
$ride2StartTime = '2023-07-20T13:00:00Z';
$ride2EndTime = '2023-07-20T13:10:00Z';

$ride2StartLatitude = 50.707129514100735;
$ride2StartLongitude = 7.15953100070237;
$ride2EndLatitude = 50.716129514100735;
$ride2EndLongitude = 7.16953100070237;

// Get the route data for rides 1 and 2
$ride1Data = getRouteData($apiKey, $ride1StartLatitude, $ride1StartLongitude, $ride1EndLatitude, $ride1EndLongitude);
$ride2Data = getRouteData($apiKey, $ride2StartLatitude, $ride2StartLongitude, $ride2EndLatitude, $ride2EndLongitude);

// // Extract the start and end times for each ride
// $ride1StartTime = null;
// $ride1EndTime = null;


if (isset($ride1Data['features'][0]['properties']['segments'][0]['steps'][0]['annotation']['metadata']['datasources'][0]['departure_time'])) {
    $ride1StartTime = $ride1Data['features'][0]['properties']['segments'][0]['steps'][0]['annotation']['metadata']['datasources'][0]['departure_time'];
}

if (isset($ride1Data['features'][0]['properties']['segments'][0]['steps'][count($ride1Data['features'][0]['properties']['segments'][0]['steps'])-1]['annotation']['metadata']['datasources'][0]['arrival_time'])) {
    $ride1EndTime = $ride1Data['features'][0]['properties']['segments'][0]['steps'][count($ride1Data['features'][0]['properties']['segments'][0]['steps'])-1]['annotation']['metadata']['datasources'][0]['arrival_time'];
}

// $ride2StartTime = null;
// $ride2EndTime = null;

if (isset($ride2Data['features'][0]['properties']['segments'][0]['steps'][0]['annotation']['metadata']['datasources'][0]['departure_time'])) {
    $ride2StartTime = $ride2Data['features'][0]['properties']['segments'][0]['steps'][0]['annotation']['metadata']['datasources'][0]['departure_time'];
}

if (isset($ride2Data['features'][0]['properties']['segments'][0]['steps'][count($ride2Data['features'][0]['properties']['segments'][0]['steps'])-1]['annotation']['metadata']['datasources'][0]['arrival_time'])) {
    $ride2EndTime = $ride2Data['features'][0]['properties']['segments'][0]['steps'][count($ride2Data['features'][0]['properties']['segments'][0]['steps'])-1]['annotation']['metadata']['datasources'][0]['arrival_time'];
}

// Calculate the time difference between the end of ride 1 and the start of ride 2
$timeDiff = strtotime($ride2StartTime) - strtotime($ride1EndTime);

// If the time difference is less than a certain threshold (e.g. 10 minutes), the rides can be matched for carpooling
if ($ride1EndTime && $ride2StartTime && $timeDiff <= 1000) {
    echo "Ride 1 and Ride 2 can be matched for carpooling.\n";
    echo "Ride 1 start time: " . ($ride1StartTime ? $ride1StartTime : "not available") . "\n";
    echo "Ride 1 end time:" . ($ride1EndTime ? $ride1EndTime : "not available") . "\n";
    echo "Ride 2 start time: " . ($ride2StartTime ? $ride2StartTime : "not available") . "\n";
    echo "Ride 2 end time: " . ($ride2EndTime ? $ride2EndTime : "not available") . "\n";
    echo "Time difference: {$timeDiff} seconds\n";
} else {
    echo "Ride 1 and Ride 2 cannot be matched for carpooling.\n";
    if (!$ride1EndTime) {
        echo "Ride 1 end time not available.\n";
    }
    if (!$ride2StartTime) {
        echo "Ride 2 start time not available.\n";
    }
    if ($timeDiff > 1000) {
        echo "Time difference too large: {$timeDiff} seconds.\n";
    }
}
// Calculate the midpoint between the start and end points of the two rides
$meetingPointData = getRouteData($apiKey, ($ride1StartLatitude + $ride2StartLatitude) / 2, ($ride1StartLongitude + $ride2StartLongitude) / 2, ($ride1EndLatitude + $ride2EndLatitude) / 2, ($ride1EndLongitude + $ride2EndLongitude) / 2);

// Extract the meeting point coordinates
$meetingPointLatitude = null;
$meetingPointLongitude = null;

if (isset($meetingPointData['features'])) {
    foreach ($meetingPointData['features'] as $feature) {
        if (isset($feature['geometry']['coordinates'])) {
            $segment = $feature['properties']['segments'][0];
            $point = $segment['steps'][0]['way_points'][0];
            $meetingPointLatitude = $point[1];
            $meetingPointLongitude = $point[0];
            break;
        }
    }
}

if ($meetingPointLatitude && $meetingPointLongitude) {
    echo "Meeting point latitude: {$meetingPointLatitude}\n";
    echo "Meeting point longitude: {$meetingPointLongitude}\n";
} else {
    echo "Unable to find a meeting point.\n";
}

// function getRouteData($apiKey, $startLat, $startLng, $endLat, $endLng)
// {
//     $apiUrl = "https://api.openrouteservice.org/v2/directions/driving-car";

//     // Create cURL resource
//     $ch = curl_init();

//     // Set cURL options
//     $url = "{$apiUrl}?api_key={$apiKey}&start={$startLng},{$startLat}&end={$endLng},{$endLat}";
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//     // Execute cURL request and get the response
//     $response = curl_exec($ch);

//     // Check for cURL errors
//     if (curl_errno($ch)) {
//         echo 'Error: ' . curl_error($ch);
//         curl_close($ch);
//         return null;
//     }

//     // Close cURL resource
//     curl_close($ch);

//     // Decode the JSON response
//     $data = json_decode($response, true);

//     // Return the route data
//     return $data;
// }

// // Replace with your actual API key
// $apiKey = "5b3ce3597851110001cf6248be0e4460401f440e838f122fa8bab5da";

// // Test route coordinates
// $startLatitude = 50.704129514100735;
// $startLongitude = 7.16153100070237;
// $endLatitude = 50.712129514100735;
// $endLongitude = 7.17153100070237;

// // Get the route data
// $routeData = getRouteData($apiKey, $startLatitude, $startLongitude, $endLatitude, $endLongitude);

// // Display the route data for testing purposes
// echo '<pre>';
// print_r($routeData);
// echo '</pre>';
