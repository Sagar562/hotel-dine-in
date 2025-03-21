<?php

// sagar (get user address)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'user_details')
        {
            if (
                (isset($_POST['user_id']) && !empty($_POST['user_id']))
                && (isset($_POST['user_address_id']) && !empty($_POST['user_address_id']))
                )
            {
                $user_id = $_POST['user_id'];
                $user_address_id = $_POST['user_address_id'];
                // check user is present in user table
                $check_user =  "SELECT user_id FROM `user_master` 
                WHERE user_id = ?";

                $check_user_stmt = mysqli_prepare($conn, $check_user);
                mysqli_stmt_bind_param($check_user_stmt, "i", $user_id);
                mysqli_stmt_execute($check_user_stmt);
                $check_user_result = mysqli_stmt_get_result($check_user_stmt);

                if (mysqli_num_rows($check_user_result) === 0)
                {
                    $response['message'] = "User not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check user is present in user table but not in address table
                $check_user_address =   "SELECT address.user_address_house_number, address.user_address_society, address.user_address_area, address.user_address_street, address.user_address_landmark, address.city_name, address.state_name, address.country_name FROM `user_master` AS user
                                        INNER JOIN `user_address` as address
                                        ON user.user_id = address.user_id
                                        WHERE user.user_id = ? AND address.user_address_id = ?";

                $check_user_address_stmt = mysqli_prepare($conn, $check_user_address);

                mysqli_stmt_bind_param($check_user_address_stmt, "ii", $user_id, $user_address_id);

                mysqli_stmt_execute($check_user_address_stmt);

                $check_user_address_result = mysqli_stmt_get_result($check_user_address_stmt);

                if (mysqli_num_rows($check_user_address_result) > 0)
                {
                    $user_address = mysqli_fetch_assoc($check_user_address_result); 
                    $response['address_id'] = $user_address_id;
                    $response['user address'] = $user_address;
                    $response['message'] = "User address fetched successfully";
                    $response['status'] = 200;
                }
                else
                {
                    $response['message'] = "Address id not matched with user id";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "User id and address id  both are required";
                $response['status'] = 201;
            }
        }
        else
        {
            $response['message'] = "Invalid tag or tag missing";
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