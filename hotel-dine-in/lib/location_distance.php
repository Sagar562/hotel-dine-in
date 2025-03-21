<?php

function haversine($lat1, $lon1, $lat2, $lon2) {
    // Convert latitude and longitude from degrees to radians
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // Haversine formula
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    
    $a = sin($dlat / 2) * sin($dlat / 2) +
         cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Radius of the Earth in kilometers
    $R = 6371;

    // Calculate the distance
    $distance = $R * $c;
    
    return $distance;
}


?>