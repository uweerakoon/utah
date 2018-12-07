<?php

$servername = "localhost";
$username = "utahsms";
$password = "utahsms";
$dbname = "utahsms";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    echo "New records created successfully\n\n";
    
    $hash = crypt('Smoke1234', '$2a$13$2QPV0OxEtYxPTXPK/9If8A==');
    echo $hash. "\n";
    
//     $stmt = $conn->query('SELECT name FROM airsheds');
//     while ($row = $stmt->fetch())
//     {
//         echo $row['name'] . "\n";
//     }
}
catch(PDOException $e)
{
    echo "Error: " . $e->getMessage();
}
$conn = null;

?>