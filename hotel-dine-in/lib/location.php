<?php
    function getDetailedAddressFromLatLon($lat, $lon)
    {
        // Define the URL for Nominatim API
        $url = "https://nominatim.openstreetmap.org/reverse?lat=$lat&lon=$lon&format=json";

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);  // Set the URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
        curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0');  // Set a valid User-Agent header

        // Execute the request
        $response = curl_exec($ch);

        // Check for cURL errors
        if ($response === false) {
            return 'Error while fetching address details: ' . curl_error($ch);
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Check if 'address' is present in the response
        if (isset($data['address'])) {
            $address = $data['address'];

            // Return the address details in an array
            return [
                'Street' => isset($address['suburb']) ? $address['suburb'] : '',
                'City' => isset($address['state_district']) ? $address['state_district'] : '',
                'State' => isset($address['state']) ? $address['state'] : '',
                'Country' => isset($address['country']) ? $address['country'] : 'Not available',
            ];
        } else {
            return 'Address not found';
        }
    }
?>
