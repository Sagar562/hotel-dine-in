<?php

// top filters (sagar)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    function get_filtered_restaurant($user_latitude, $user_longitude)
    {
        include_once '../../lib/location_distance.php';
        global $conn, $response;

        $page = isset($_POST['page'])? $_POST['page'] : 1;
        $page_size = 10;
        $offset = ($page - 1) * $page_size;

        $price_low_to_high = isset($_POST['price_low_to_high'])? $_POST['price_low_to_high'] : '';
        $price_high_to_low = isset($_POST['price_high_to_low']) ? $_POST['price_high_to_low'] : '';
        $rating_low_to_high = isset($_POST['rating_low_to_high']) ? $_POST['rating_low_to_high'] : '';
        $rating_high_to_low = isset($_POST['rating_high_to_low']) ? $_POST['rating_high_to_low'] : '';
        $nearest_restaurant = isset($_POST['nearest_restaurant']) ? $_POST['nearest_restaurant'] : ''; 


        // create query
        $query = "SELECT rest.restaurant_id, rest.restaurant_name, rest.restaurant_avg_price, rest.restaurant_food_type, rest_img.restaurant_image_url, rest_address.restaurant_street, rest_address.restaurant_city, rest_address.restaurant_latitude,rest_address.restaurant_longitude, AVG(r.rating) AS average_rating FROM `restaurant_master` AS rest
        LEFT JOIN ratings AS r
        ON rest.restaurant_id = r.restaurant_id
        LEFT JOIN `restaurant_images` AS rest_img
        ON rest_img.restaurant_id = rest.restaurant_id
        INNER JOIN `restaurant_address` AS rest_address
        ON rest_address.restaurant_id = rest.restaurant_id
        WHERE rest.restauarnt_approved_status = 1 AND rest_address.is_delete = 0 AND rest.restaurant_status = 1
        GROUP BY rest.restaurant_id";


        // qurery for getting restaurant based on filter
        if ($price_low_to_high)
        {
            $query .= " ORDER BY rest.restaurant_avg_price ASC";
        }
        else if ($price_high_to_low)
        {
            $query .= " ORDER BY rest.restaurant_avg_price DESC";
        }
        // for rating
        else if ($rating_low_to_high)
        {
            $query .= " ORDER BY average_rating ASC";
        }
        else if ($rating_high_to_low)
        {
            $query .= " ORDER BY average_rating DESC";
        }

        // and also for nearest restaurant
        if ($nearest_restaurant)
        {
            $query .= " HAVING (6371 * acos(cos(radians($user_latitude)) * cos(radians(rest_address.restaurant_latitude)) 
            * cos(radians(rest_address.restaurant_longitude) - radians($user_longitude)) 
            + sin(radians($user_latitude)) * sin(radians(rest_address.restaurant_latitude)))) <= 10";
        }

        // Add LIMIT and OFFSET for pagination
        $query .= " LIMIT $page_size OFFSET $offset";

        $query_stmt = mysqli_prepare($conn, $query);
        // mysqli_stmt_bind_param($query_stmt, "ddd", $user_latitude, $user_longitude, $user_latitude);
        mysqli_stmt_execute($query_stmt);
        $query_result = mysqli_stmt_get_result($query_stmt);
        
        if (mysqli_num_rows($query_result) > 0)
        {
            $filtered_restautants = [];
            $food_types = [0 => "Veg", 1 => "Non-veg", 2 => "Veg & Non-veg"];

            while ($data = mysqli_fetch_assoc($query_result))
            {
                $restaurant_latitude = $data['restaurant_latitude'];
                $restaurant_longitude = $data['restaurant_longitude'];
    
                $average_rating = number_format((float)$data['average_rating'], 1, '.', '');
    
                // function call for distance
                $distance = haversine($user_latitude, $user_longitude, $restaurant_latitude, $restaurant_longitude);
    
                $food_type = $food_types[$data['restaurant_food_type']];
    
                $address = $data['restaurant_street']. ','. $data['restaurant_city'];
    
                $filtered_restaurants[] = [
                    "restaurant_id" => $data['restaurant_id'],
                    "restaurant_name" => trim($data['restaurant_name']),
                    "restaurant_avg_price" => $data['restaurant_avg_price'],
                    "restaurant food type" => $food_type,
                    "restaurant_image_url" => $data['restaurant_image_url'],
                    "restaurant_small_address" => $address,
                    "distance" => round($distance, 2) . " km",
                    "rating" => $average_rating
                    ];
            }

            if ($nearest_restaurant) {
                usort($filtered_restaurants, function ($a, $b) {
                    // Extract the numeric part of the distance for comparison
                    $distance_a = floatval(explode(".", $a['distance'])[0]);
                    $distance_b = floatval(explode(".", $b['distance'])[0]);
            
                    return $distance_a - $distance_b;  // Sort by distance in ascending order
                });
            }
   
            // get total records
            $total_query = "SELECT COUNT(restaurant_id) AS total FROM `restaurant_master` AS rest
                            WHERE rest.restauarnt_approved_status = 1 
                            AND rest.restaurant_status = 1";  

            $total_result = mysqli_query($conn, $total_query);
            $total_records = mysqli_fetch_assoc($total_result)['total'];


                $response['All restaurants'] = $filtered_restaurants;
                $response['total_records'] = $total_records;
                $response['current_page'] = $page;
                $response['total_pages'] = ceil($total_records / $page_size);
                $response['message'] = "All restaurant fetched successfully";
                $response['status'] = 200;
        }
        else
        {
            $response['message'] = "Restaurant not found";
            $response['status'] = 201;
        }
        return $response;
    }

    // code start
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'top_filters')
        {
            if (
                isset($_POST['current_latitude']) && !empty($_POST['current_latitude'])
                && isset($_POST['current_longitude']) && !empty($_POST['current_longitude'])
                )   
            {
                $user_latitude = $_POST['current_latitude'];
                $user_longitude = $_POST['current_longitude'];

                $response = get_filtered_restaurant($user_latitude, $user_longitude);

            }
            else if (isset($_POST['user_address_id']) && !empty($_POST['user_address_id']))
            {
                $user_address_id = $_POST['user_address_id'];

                $get_address_query = "SELECT user_address_latitude, user_address_longitude FROM `user_address` 
                                    WHERE user_address_id = ?";
                $get_address_query_stmt = mysqli_prepare($conn, $get_address_query);
                if (!$get_address_query_stmt)
                {
                    $response['message'] = "Error while preparing get address query" . mysqli_error($conn);
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($get_address_query_stmt, "i", $user_address_id);
                if (!mysqli_stmt_execute($get_address_query_stmt))
                {
                    $response['message'] = "Error while executing get address query" . mysqli_error($conn);
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $get_address_query_result = mysqli_stmt_get_result($get_address_query_stmt);
                if (mysqli_num_rows($get_address_query_result) > 0)
                {
                    // get user latitude and longitude
                    $user_data = mysqli_fetch_assoc($get_address_query_result);
                    $user_latitude = $user_data['user_address_latitude'];
                    $user_longitude = $user_data['user_address_longitude'];
                }
                else
                {
                    $response['message'] = "User address not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                
                // function call for getting restaurants
                $response = get_filtered_restaurant($user_latitude, $user_longitude);
             
            }
            else
            {
                $response['message'] = "address id not available";
                $response['status'] = 201;
            }            

        }
        else
        {
            $response['message'] = "Invalid tag or tag not found";
            $response['status'] = 201;
        }
    }
    else
    {
        $response['message'] = "Only post method  allow";
        $response['status'] = 201;
    }

    echo json_encode($response);

?>