<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'delete_restaurant_cuisine')
        {
            if (
                (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                && (isset($_POST['cuisine_id']) && !empty($_POST['cuisine_id']))
            )
            {
                $restaurant_id = $_POST['restaurant_id'];
                $cuisine_id = $_POST['cuisine_id'];

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

                // check for restaurant is mapped with cuisine or not if yes then delete cuisine
                $check_restaurant_with_cuisine = "SELECT restaurant_cuisine_id FROM `restaurant_cuisines`
                                                  WHERE  restaurant_id = ? AND cuisine_id = ?";
                $check_restaurant_with_cuisine_stmt = mysqli_prepare($conn, $check_restaurant_with_cuisine);
                mysqli_stmt_bind_param($check_restaurant_with_cuisine_stmt, "ii", $restaurant_id, $cuisine_id);
                mysqli_stmt_execute($check_restaurant_with_cuisine_stmt);
                $check_restaurant_with_cuisine_result = mysqli_stmt_get_result($check_restaurant_with_cuisine_stmt);

                if (mysqli_num_rows($check_restaurant_with_cuisine_result) > 0)
                {
                    $restaurant_cuisine_id = mysqli_fetch_assoc($check_restaurant_with_cuisine_result)['restaurant_cuisine_id'];
                 
                    $delete_query = "UPDATE `restaurant_cuisines`
                                    SET is_hidden = 1
                                    WHERE restaurant_cuisine_id = ?";
                    $delete_query_stmt = mysqli_prepare($conn, $delete_query);
                    mysqli_stmt_bind_param($delete_query_stmt, "i", $restaurant_cuisine_id);
                    
                    if (mysqli_stmt_execute($delete_query_stmt))
                    {
                        $response['message'] = "Restaurant Cuisine is deleted successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "Error while executing delete restaurant cuisine query" . mysqli_error($conn);
                        $response['status'] = 201;   
                    }

                }
                else
                {
                    $response['message'] = "No cuisine found with restaurant";
                    $response['status'] = 201;   
                }

            }
            else
            {
                $response['message'] = "Restaurant id and cuisine id both are required";
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
