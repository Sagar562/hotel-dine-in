<?php

// sagar (restaurant details)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    function get_restaurant_details($user_latitude, $user_longitude, $restaurant_id)
    {
        global $conn, $response;
        include_once '../../lib/location_distance.php';

        $restaurant_details = "SELECT 
                                rest.restaurant_id, 
                                rest.restaurant_name, 
                                rest.restaurant_phone_number, 
                                rest.restaurant_avg_price, 
                                rest.restaurant_description, 
                                rest.restaurant_food_type, 
                                rest_img.restaurant_image_url, 
                                rest_address.restaurant_number, 
                                rest_address.restaurant_complex, 
                                rest_address.restaurant_street, 
                                rest_address.restaurant_area, 
                                rest_address.restaurant_landmark, 
                                rest_address.restaurant_city, 
                                rest_address.restaurant_state, 
                                rest_address.restaurant_country, 
                                rest_address.restaurant_latitude, 
                                rest_address.restaurant_longitude, 
                                rest_time.meal_start_time, 
                                AVG(rating.rating) AS 'avg_rating', 
                                COUNT(rating.rating) AS 'Total_rating' 
                            FROM `restaurant_master` AS rest
                            LEFT JOIN `restaurant_images` AS rest_img
                                ON rest_img.restaurant_id = rest.restaurant_id
                            LEFT JOIN `ratings` AS rating
                                ON rating.restaurant_id = rest.restaurant_id
                            INNER JOIN `restaurant_address` AS rest_address
                                ON rest_address.restaurant_id = rest.restaurant_id
                            INNER JOIN `restaurant_time` AS rest_time
                                ON rest_time.restaurant_id = rest.restaurant_id
                            WHERE rest.restauarnt_approved_status = 1 
                                AND rest.restaurant_status = 1 AND rest.restaurant_id = ?
                            GROUP BY rest.restaurant_id";

        $restaurant_details_stmt = mysqli_prepare($conn, $restaurant_details);
        if (!$restaurant_details_stmt)
        {
            $response['message'] = "Error while preparing restaurant deatails" . mysqli_error($conn);
            $response['status'] = 201;
            echo json_encode($response);
            exit();
        }
        mysqli_stmt_bind_param($restaurant_details_stmt, "i", $restaurant_id);
        if (!mysqli_stmt_execute($restaurant_details_stmt))
        {
            $response['message'] = "Error while executing restaurant deatails" . mysqli_error($conn);
            $response['status'] = 201;
            echo json_encode($response);
            exit();
        }
        $restaurant_details_result = mysqli_stmt_get_result($restaurant_details_stmt);
        
        // get restaurant details
        if (mysqli_num_rows($restaurant_details_result) > 0)
        {
            $food_types = [0 => "Veg", 1 => "Non-veg", 2 => "Veg & Non-veg"];

            $restaurant_data = [];
            while ($data = mysqli_fetch_assoc($restaurant_details_result))
            {
                $restaurant_latitude = $data['restaurant_latitude'];
                $restaurant_longitude = $data['restaurant_longitude'];

                $food_type = $food_types[$data['restaurant_food_type']];
                $average_rating = number_format((float)$data['avg_rating'], 1, '.', '');

                $address = $data['restaurant_number'].','. $data['restaurant_complex'].','.
                $data['restaurant_street'].','. $data['restaurant_area'].','.$data['restaurant_landmark'].','. $data['restaurant_city'].','. $data['restaurant_state'].','. $data['restaurant_country'];
            
                $distance = haversine($user_latitude, $user_longitude, $restaurant_latitude, $restaurant_longitude);

                // check for km less then 1km
                if ($distance < 1) {
                    // Convert distance to meters
                    $distance_in_meters = $distance * 1000;
                    $distance = round($distance_in_meters, 0) . " meters";
                } else {
                    $distance = round($distance, 2) . " km";
                }


                $restaurant_data[] = [
                    "restaurant_id" => $data['restaurant_id'],
                    "restaurant_name" => $data['restaurant_name'],
                    "restaurant_phone_number" => $data['restaurant_phone_number'],
                    "restaurant_avg_price" => $data['restaurant_avg_price'],
                    "restaurant_description" => $data['restaurant_description'],
                    "restaurant_food_type" => $food_type,
                    "restaurant_image_url" => $data['restaurant_image_url'],
                    "restaurant_address" => $address,
                    "distance" => $distance,
                    "average_rating" => $average_rating,
                    "total_rating" => $data['Total_rating']
                ];
            }
            $response['restaurant_data'] = $restaurant_data;
            $response['message'] = "Restaurant details fetched successfully";
            $response['status'] = 200;
        }
        else
        {
            $response['message'] = "Restaurant not approved yet";
            $response['status'] = 201;
        }
        return $response;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'restaurant_detail')
        {
            if (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
            {
                $restaurant_id = $_POST['restaurant_id'];

                if (
                    isset($_POST['current_latitude']) && !empty($_POST['current_latitude'])
                    && isset($_POST['current_longitude']) && !empty($_POST['current_longitude'])
                    )  
                {
                    $user_latitude = $_POST['current_latitude'];
                    $user_longitude = $_POST['current_longitude'];
    
                    // function call
                    $response = get_restaurant_details($user_latitude, $user_longitude, $restaurant_id);

                }
                else if (isset($_POST['user_address_id']) && !empty($_POST['user_address_id']))
                {
                    $user_address_id = $_POST['user_address_id'];

                    // find for address id in db
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

                    // function call
                    $response = get_restaurant_details($user_latitude, $user_longitude, $restaurant_id);
                }
                else
                {
                    $response['message'] = "address id and current location is not available";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "Restaurant id is required";
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
        $response['message'] = "Only post method allow";
        $response['status'] = 201;
    }


    echo json_encode($response);

?>