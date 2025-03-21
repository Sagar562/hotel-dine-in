<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    // function for calling database and get data
    function get_restaurants($user_latitude ,$user_longitude, $page, $page_size)
    {
        include_once '../../lib/location_distance.php';
        global $conn, $response;

        $offset = ($page - 1) * $page_size;

        $total_count_query = "SELECT COUNT(DISTINCT rest.restaurant_id) AS total_count
                              FROM `restaurant_master` AS rest
                              LEFT JOIN ratings AS r ON rest.restaurant_id = r.restaurant_id
                              LEFT JOIN `restaurant_images` AS rest_img ON rest_img.restaurant_id = rest.restaurant_id
                              INNER JOIN `restaurant_address` AS rest_address ON rest_address.restaurant_id = rest.restaurant_id
                              WHERE rest.restauarnt_approved_status = 1 AND rest_address.is_delete = 0 AND rest.restaurant_status = 1";
        $total_count_result = mysqli_query($conn, $total_count_query);
        $total_count = 0;

        if (mysqli_num_rows($total_count_result) > 0) {
            $total_count_data = mysqli_fetch_assoc($total_count_result);
            $total_count = $total_count_data['total_count'];
        }

        // Calculate total pages
        $total_pages = ceil($total_count / $page_size);


        $restaurant_details = "SELECT rest.restaurant_id, rest.restaurant_name, rest.restaurant_avg_price, rest.restaurant_food_type, rest_img.restaurant_image_url, rest_address.restaurant_street, rest_address.restaurant_city, rest_address.restaurant_latitude,rest_address.restaurant_longitude, AVG(r.rating) AS average_rating FROM `restaurant_master` AS rest
        LEFT JOIN ratings AS r
        ON rest.restaurant_id = r.restaurant_id
        LEFT JOIN `restaurant_images` AS rest_img
        ON rest_img.restaurant_id = rest.restaurant_id
        INNER JOIN `restaurant_address` AS rest_address
        ON rest_address.restaurant_id = rest.restaurant_id
        WHERE rest.restauarnt_approved_status = 1 AND rest_address.is_delete = 0 AND rest.restaurant_status = 1
        GROUP BY rest.restaurant_id
        LIMIT $page_size OFFSET $offset";

        $restaurant_details_result = mysqli_query($conn, $restaurant_details);

        if (mysqli_num_rows($restaurant_details_result) > 0)
        {
            $all_restaurants = [];
            $food_types = [0 => "Veg", 1 => "Non-veg", 2 => "Veg & Non-veg"];

            while ($data = mysqli_fetch_assoc($restaurant_details_result))
            {
            $restaurant_latitude = $data['restaurant_latitude'];
            $restaurant_longitude = $data['restaurant_longitude'];

            $average_rating = number_format((float)$data['average_rating'], 1, '.', '');

            // function call for distance
            $distance = haversine($user_latitude, $user_longitude, $restaurant_latitude, $restaurant_longitude);

            $food_type = $food_types[$data['restaurant_food_type']];

            $address = $data['restaurant_street']. ','. $data['restaurant_city'];

            $all_restaurants[] = [
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

            $response['All restaurants'] = $all_restaurants;
            $response['message'] = "All restaurant fetched successfully";
            $response['status'] = 200;
            $response['total_count'] = $total_count;
            $response['total_pages'] = $total_pages;
            $response['current_page'] = $page;
        }
        else
        {
            $response['message'] = "Restaurant not found";
            $response['status'] = 201;
        }
        return $response;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
       if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'all_restaurant')
       {
            if (
                isset($_POST['current_latitude']) && !empty($_POST['current_latitude'])
                && isset($_POST['current_longitude']) && !empty($_POST['current_longitude'])
                )   
            {
                $user_latitude = $_POST['current_latitude'];
                $user_longitude = $_POST['current_longitude'];

                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                $page_size = isset($_POST['page_size']) ? (int)$_POST['page_size'] : 10;

                $response = get_restaurants($user_latitude, $user_longitude, $page, $page_size);

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
                
                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                $page_size = isset($_POST['page_size']) ? (int)$_POST['page_size'] : 10;


                // function call for getting restaurants
                $response = get_restaurants($user_latitude, $user_longitude, $page, $page_size);
             

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
        $response['message'] = "Only post method allow";
        $response['status'] = 201;
    }

    echo json_encode($response);
?>