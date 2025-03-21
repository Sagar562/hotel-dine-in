<?php

// sagar (add cuisine for restaurant)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['cuisines']) && is_array($data['cuisines']) 
        && isset($data['restaurant_id']) && !empty($data['restaurant_id']))
    {
        $cuisines = $data['cuisines'];
        $restaurant_id = $data['restaurant_id'];
        $inserted = 0;
        $errors = []; 

        // check for restaurant exist in db or not
        $check_restaurant = "SELECT restaurant_id FROM `restaurant_master`
                             WHERE restaurant_id = ?";
        $check_restaurant_stmt = mysqli_prepare($conn, $check_restaurant);
        if (!$check_restaurant_stmt)
        {
            $response['message'] = "Error while preparing check restaurant query" . mysqli_error($conn);
            $response['status'] = 201;
            echo json_encode($response);
            exit();
        }
        mysqli_stmt_bind_param($check_restaurant_stmt, "i", $restaurant_id);
        if (!mysqli_stmt_execute($check_restaurant_stmt))
        {
            $response['message'] = "Error while executing check restaurant query" . mysqli_error($conn);
            $response['status'] = 201;
            echo json_encode($response);
            exit();
        }
        $check_restaurant_result = mysqli_stmt_get_result($check_restaurant_stmt);
        if (mysqli_num_rows($check_restaurant_result) === 0)
        {
            $response['message'] = "Restaurant not found";
            $response['status'] = 201;
            echo json_encode($response);
            exit();
        }

        // get data of each cuisine
        foreach ($cuisines as $cuisine_id) 
        {
            if (is_int($cuisine_id) && $cuisine_id > 0) 
            {
                // check for cuisineId is exist in cuisine master
                $check_cuisine = "SELECT cuisine_id FROM `cuisines_master`
                                  WHERE cuisine_id = ?";
                $check_cuisine_stmt = mysqli_prepare($conn, $check_cuisine);
                if (!$check_cuisine_stmt)
                {
                    $response['message'] = "Error while preparing check cuisine query" . mysqli_error($conn);
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_cuisine_stmt, "i", $cuisine_id);
                if (!mysqli_stmt_execute($check_cuisine_stmt))
                {
                    $response['message'] = "Error while executing check cuisine query" . mysqli_error($conn);
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $check_cuisine_result = mysqli_stmt_get_result($check_cuisine_stmt);
                if (mysqli_num_rows($check_cuisine_result) === 0)
                {
                    $response['message'] = "Cuisine id is not found in cuisine master cuisine id:{$cuisine_id}";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check for cuisines already there for restaurant
                $check_for_same_cuisine = "SELECT restaurant_cuisine_id FROM `restaurant_cuisines` 
                                           WHERE cuisine_id = ? AND restaurant_id = ?";
                $check_for_same_cuisine_stmt = mysqli_prepare($conn, $check_for_same_cuisine);
                mysqli_stmt_bind_param($check_for_same_cuisine_stmt, "ii", $cuisine_id, $restaurant_id);
                mysqli_stmt_execute($check_for_same_cuisine_stmt);
                $check_for_same_cuisine_result = mysqli_stmt_get_result($check_for_same_cuisine_stmt);

                if (mysqli_num_rows($check_for_same_cuisine_result) === 0)
                {
                    // $response['message'] = "Cuisine already exist in restaurant";
                    // $response['status'] = 201;
                    // echo json_encode($response);
                    // exit();

                    // insert cuisine
                    $insert_resutaurant_cuisine = "INSERT INTO restaurant_cuisines (cuisine_id, restaurant_id)
                    VALUES (?, ?)";
                    $insert_resutaurant_cuisine_stmt = mysqli_prepare($conn, $insert_resutaurant_cuisine);

                    if (!$insert_resutaurant_cuisine_stmt) {
                        $response['message'] = "Error while preparing restaurant cuisine" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }

                    mysqli_stmt_bind_param($insert_resutaurant_cuisine_stmt, "ii", $cuisine_id, $restaurant_id);
                    if (mysqli_stmt_execute($insert_resutaurant_cuisine_stmt))
                    {
                        $inserted++;
                    } else
                    {
                        $response['message'] = "Error while executing restaurant cuisine" . mysqli_error($conn);
                        $response['status'] = 201;
                    }
                }
            }
            else
            {
                $response['message'] = "Invalid cuisine_id: {$cuisine_id}. It must be an integer greater than 0.";
                $response['status'] = 201;
            }
        }

        if ($inserted > 0) {
            $response['message'] = "{$inserted} cuisines linked to the restaurant successfully.";
            $response['status'] = 200;
        } else {
            $response['message'] = "No cuisines were added or cuisines are already their";
            $response['status'] = 201;
        }
    } else {
        $response['message'] = "Invalid input format. Please provide a valid restaurant_id and cuisines array.";
        $response['status'] = 201;
    }
} else {
    $response['message'] = "Only POST method is allowed.";
    $response['status'] = 201;
}

echo json_encode($response);

?>
