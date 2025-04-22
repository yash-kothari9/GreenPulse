<?php
include_once("../config/db_connect.php");
$sql = "ALTER TABLE rides CHANGE seats_available passenger_seats INT NOT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column renamed to passenger_seats successfully!";
} else {
    echo "Error renaming column: " . $conn->error;
}
$conn->close();
?>