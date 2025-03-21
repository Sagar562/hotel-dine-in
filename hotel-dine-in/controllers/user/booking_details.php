<?php

// sagar (user booking details)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {   
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'user_booking_details')
        {
            if (isset($_POST['user_id']) && !empty($_POST['user_id']))
            {
                $user_id = $_POST['user_id'];
                
                // check for user is present or not
                $check_user_query = "SELECT user_id FROM `user_master` 
                                    WHERE user_id = ?";
                $check_user_query_stmt = mysqli_prepare($conn, $check_user_query);
                if ($check_user_query_stmt)
                {
                    mysqli_stmt_bind_param($check_user_query_stmt, "i", $user_id);
                    
                    if (mysqli_stmt_execute($check_user_query_stmt))
                    {
                        $check_user_query_result = mysqli_stmt_get_result($check_user_query_stmt);

                        if (mysqli_num_rows($check_user_query_result) === 0)
                        {
                            $response['message'] = "User not found";
                            $response['status'] = 201;
                            echo json_encode($response);
                            exit();
                        }
                    }
                    else
                    {
                        $response['message'] = "Error while executing check user query";
                        $response['status'] = 201;
                    }
                }
                else
                {
                    $response['message'] = "Error while preparing check user query";
                    $response['status'] = 201;
                }


                // fetch user data
                $booking_details_query = "SELECT u.user_full_name, r.reservation_id, rest.restaurant_name , r.reservation_date, r.number_of_guests, rest_slot.start_time ,rest_time.meal_type, r.reservation_status FROM `reservations` as r
                            INNER JOIN `user_master` as u
                            ON u.user_id = r.user_id
                            INNER JOIN `restaurant_slots` AS rest_slot
                            ON rest_slot.restaurant_slot_id = r.restaurant_slot_id
                            INNER JOIN `restaurant_time` AS rest_time
                            ON rest_time.restaurant_time_id = rest_slot.restaurant_time_id
                            INNER JOIN `restaurant_master` AS rest
                            ON rest.restaurant_id = r.restaurant_id
                            WHERE u.user_id = ?";
                

                $booking_details_query_stmt = mysqli_prepare($conn, $booking_details_query);
                if ($booking_details_query_stmt)
                {
                    mysqli_stmt_bind_param($booking_details_query_stmt, "i", $user_id);

                    if (mysqli_stmt_execute($booking_details_query_stmt))
                    {
                        $booking_details_query_result = mysqli_stmt_get_result($booking_details_query_stmt);

                        if (mysqli_num_rows($booking_details_query_result) > 0)
                        {
                            $user_booking_details = [];
                            $meal_labels = [0 => 'breakfast', 1 => 'lunch', 2 => 'dinner'];
                            $booking_status_labels = [1 => 'confirm', 0 => 'cancelled'];

                            while ($data = mysqli_fetch_assoc($booking_details_query_result))
                            {
                                $meal_type = $data['meal_type'];
                                $booking_status = $data['reservation_status'];
                                // $special_request = $data['special_request'];
                                // for meal
                                $meal_label = $meal_labels[$meal_type] ?? 'Not found meal type'; 
                                // for booking status
                                $booking_status_label = $booking_status_labels[$booking_status] ?? 'not found booking status';

                                    $user_booking_details[] = [
                                        "reservation_id" => $data['reservation_id'],
                                        "restaurant_name" => $data['restaurant_name'],
                                        "booking_date" => $data['reservation_date'],
                                        "meal_type" => $meal_label,
                                        "slot_start_time" => $data['start_time'],
                                        "number_of_guest" => $data['number_of_guests'],
                                        "booking_status" => $booking_status_label,
                                        // "special_request" => $data['special_request'] 
                                    ];
                                    // if ($special_request !== NULL) 
                                    // {
                                    //     $user_booking_details[count($user_booking_details) - 1]['special_request'] = $special_request;
                                    // }

                            }
                            $response['user_booking_details'] = $user_booking_details;
                            $response['message'] = "User booking details fetched successfully";
                            $response['status'] = 200;
                        }
                        else
                        {
                            $response['message'] = "No booking yet";
                            $response['status'] = 201;
                        }
                    }
                    else
                    {
                        $response['message'] = "Error while executing booking details query";
                        $response['status'] = 201;
                    }
                }
                else
                {
                    $response['message'] = "Error while preparing booking details query";
                    $response['status'] = 201;
                }
                
            }
            else
            {
                $response['message'] = "User id required";
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