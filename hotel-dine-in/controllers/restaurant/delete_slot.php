<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'restaurant_slots')
        {
            if (
                (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                && (isset($_POST['restaurant_time_id']) && !empty($_POST['restaurant_time_id']))
                ) 
            {
                $restaurant_id = $_POST['restaurant_id'];
                $restaurant_time_id = $_POST['restaurant_time_id'];

                // Check for restaurant existence
                $check_restaurant = "SELECT restaurant_id FROM `restaurant_master`
                                    WHERE restaurant_id = ?";
                $check_restaurant_stmt = mysqli_prepare($conn, $check_restaurant);
                if (!$check_restaurant_stmt)
                {
                    $response['message'] = "Error while preparing check restaurant query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_restaurant_stmt, "i", $restaurant_id);
                if (!mysqli_stmt_execute($check_restaurant_stmt))
                {
                    $response['message'] = "Error while executing check restaurant query";
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

                // check for restaurant id with restaurant time id
                $check_restaurant_with_time = "SELECT restaurant_time_id FROM `restaurant_time`
                                               WHERE restaurant_id = ? AND restaurant_time_id = ?";

                $check_restaurant_with_time_stmt = mysqli_prepare($conn, $check_restaurant_with_time);
                mysqli_stmt_bind_param($check_restaurant_with_time_stmt, "ii", $restaurant_id, $restaurant_time_id);
                mysqli_stmt_execute($check_restaurant_with_time_stmt);
                $check_restaurant_with_time_result = mysqli_stmt_get_result($check_restaurant_with_time_stmt);

                if (mysqli_num_rows($check_restaurant_with_time_result) > 0)
                {
                   $delete_time_query_1 = "UPDATE `restaurant_time`
                                           SET is_delete = 1
                                           WHERE restaurant_time_id = ?";
                    $delete_time_stmt_1 = mysqli_prepare($conn, $delete_time_query_1);
                    mysqli_stmt_bind_param($delete_time_stmt_1, "i", $restaurant_time_id);
                        
                    // Execute the first query
                    if (!mysqli_stmt_execute($delete_time_stmt_1)) {
                        $response['message'] = "Error while executing delete time query for restaurant_time" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    
                    $delete_time_query_2 = "UPDATE `restaurant_slots`
                                            SET is_delete = 1
                                            WHERE restaurant_time_id = ?";
                    $delete_time_stmt_2 = mysqli_prepare($conn, $delete_time_query_2);
                    mysqli_stmt_bind_param($delete_time_stmt_2, "i", $restaurant_time_id);
                    
                    // Execute the second query
                    if (!mysqli_stmt_execute($delete_time_stmt_2)) {
                        $response['message'] = "Error while executing delete time query for restaurant_slots" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    $response['message'] = "Restaurant time is deleted successfully";
                    $response['status'] = 200;
                }
                else
                {
                    $response['message'] = "Restaurant time id is not mapped with restaurant id";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "Restaurant id and restaurant time id both are required";
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