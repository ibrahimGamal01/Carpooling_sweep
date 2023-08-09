<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "carpooling"; 

try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert random rows into the table
    $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);

    $names = ["John Doe", "Jane Smith", "Michael Johnson"];
    $emails = ["john@example.com", "jane@example.com", "michael@example.com"];
    $passwords = ["password123", "pass456", "secret789"];

    for ($i = 0; $i < count($names); $i++) {
        $stmt->execute([$names[$i], $emails[$i], $passwords[$i]]);
        echo "Inserted row with name: " . $names[$i] . "<br>";
    }

    // Display the data
    $query = "SELECT * FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        echo "ID: " . $row['id'] . "<br>";
        echo "Name: " . $row['name'] . "<br>";
        echo "Email: " . $row['email'] . "<br><br>";
    }

    // Close the database connection
    $db = null;
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
