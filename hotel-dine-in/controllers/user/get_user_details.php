<?php

// sagar (edit profile view)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'user_details')
        {
            if (isset($_POST['user_id']) && !empty($_POST['user_id']))
            {
                $user_id =$_POST['user_id'];
                
                $show_user_data = "SELECT user_full_name, user_phone_number, user_email, user_gender FROM `user_master` WHERE user_id = ?";
                
                $show_user_data_stmt = mysqli_prepare($conn, $show_user_data);
                mysqli_stmt_bind_param($show_user_data_stmt, "i", $user_id);
                mysqli_stmt_execute($show_user_data_stmt);
                $show_user_data_result = mysqli_stmt_get_result($show_user_data_stmt);
                
                if (mysqli_num_rows($show_user_data_result) > 0)
                {
                    $user_details = mysqli_fetch_assoc($show_user_data_result);
                    // echo "$user_details";

                    $response['user'] = $user_details;
                    $response['message'] = "User details fetch successfully";
                    $response['status'] = 200;
                    
                }
                else
                {
                    $response['message'] = "User not found";
                    $response['satus'] = 201;   
                }
            }
            else
            {
                $responsep['message'] = "User id required";
                $response['status'] = 201;
            }
        }
        else
        {
            $responsep['message'] = "Invalid Tag or tag missing";
            $response['status'] = 201;
        }
    }
    else
    {
        $responsep['message'] = "Only post method allow";
        $response['status'] = 201;
    }
    echo json_encode($response);
?>