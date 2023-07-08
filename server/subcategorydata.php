<?php
include "../common/backendConnector.php";

// db connection in (lms) db
$con = mysqli_connect($host, $dbUserName, $dbPassword, $database);
if (!$con) {
    die("DB connection failed");
}

// for get subcategory content from db
$sqlFetch = "SELECT * FROM subcategory";
$resFetch = mysqli_query($con, $sqlFetch);

// Fetch subcategory data in an array
$subcategoryData = array();
while ($row = mysqli_fetch_assoc($resFetch)) {
    $subcategoryData[] = $row;
}

// Convert subcategory data to JSON format
$subcategoryJson = json_encode($subcategoryData);

// Close the database connection
mysqli_close($con);

// Output the JSON data
header('Content-Type: application/json');
echo $subcategoryJson;
exit;

?>
