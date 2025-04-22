<?php
include_once("../config/db_connect.php");

$locations = [
    ["SRM University AP", 16.5738, 80.3575],
    ["Vijayawada Railway Station", 16.5193, 80.6305],
    ["Benz Circle", 16.5062, 80.6480],
    ["Guntur Bus Stand", 16.3067, 80.4365],
    ["Mangalagiri", 16.4308, 80.5687],
    ["Undavalli Caves", 16.4965, 80.5976],
    ["Prakasam Barrage", 16.5186, 80.6307],
    ["NTR University", 16.5062, 80.6480],
    ["Vijayawada Airport", 16.5304, 80.7969],
    ["PVP Mall", 16.5093, 80.6321],
    ["Guntur Railway Station", 16.2986, 80.4542],
    ["Tenali", 16.2428, 80.6400],
    ["Amaravati", 16.5735, 80.3575],
    ["Tadepalli", 16.4821, 80.6018],
    ["Chilakaluripet", 16.0895, 80.1670],
    ["Pedakakani", 16.3214, 80.4427],
    ["Namburu", 16.3481, 80.4423],
    ["Kaza Toll Plaza", 16.4372, 80.5662],
    ["Autonagar", 16.4896, 80.6767],
    ["Vijayawada Bus Stand", 16.5193, 80.6305]
];

foreach ($locations as $loc) {
    $name = $conn->real_escape_string($loc[0]);
    $lat = $loc[1];
    $lng = $loc[2];
    $sql = "INSERT INTO locations (name, latitude, longitude) VALUES ('$name', $lat, $lng)";
    if ($conn->query($sql) === TRUE) {
        echo "Inserted: $name<br>";
    } else {
        echo "Error inserting $name: " . $conn->error . "<br>";
    }
}

$conn->close();
?>