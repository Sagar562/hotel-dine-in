<?php

// sagar (fetch booking details)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];


    // function for reduce duplicate code
    function fetch_booking_details($result)
    {
        $bookings = [];
        $meal_types = [0 => "Breafast", 1 => "Lunch", 2 => "Dinner"];
        $reservation_status = [0 => "cancelled", 1 => "Confirmed"];

        while ($data = mysqli_fetch_assoc($result)) 
        {
            $status = $reservation_status[$data['reservation_status']];
            $meal_type = $meal_types[$data['meal_type']];
            $cancel_reason = $data['cancel_reason'] ? $data['cancel_reason'] : '';

            
            $bookings[] = [
                "user_name" => $data['user_full_name'],
                "user_phone_number" => $data['user_phone_number'],
                "reservation_id" => $data['reservation_id'],
                "reservation_status" => $status,
                "reservation_date" => $data['reservation_date'],
                "reservation_time" => $data['start_time'],
                "meal_type" => $meal_type,
                "cancel_reason" => $cancel_reason
            ];
        }
        $response['reservation_details'] = $bookings; 

        return $response;
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'fetch_booking')
        {
            if (isset($_POST['restaurant_id']) && $_POST['restaurant_id'])
            {
                $restaurant_id = $_POST['restaurant_id'];

                if (isset($_POST['confirm_upcoming_reservation']) && !empty($_POST['confirm_upcoming_reservation']))
                {
                    $fetch_bookings_query = "SELECT r.reservation_id, u.user_full_name, u.user_phone_number, rest_slot.start_time , r.reservation_status, r.reservation_date, r.cancel_reason, rest_time.meal_type
                    FROM reservations AS r
                    INNER JOIN `user_master` AS u
                    ON u.user_id = r.user_id
                    INNER JOIN `restaurant_slots` AS rest_slot
                    ON rest_slot.restaurant_slot_id = r.restaurant_slot_id
                    INNER JOIN `restaurant_time` AS rest_time
                    ON rest_time.restaurant_time_id = rest_slot.restaurant_time_id
                    WHERE r.restaurant_id = ?  AND (r.reservation_date > curdate() OR (r.reservation_date = curdate() AND rest_slot.start_time >= CURTIME())) AND r.reservation_status = 1
                    ORDER BY r.reservation_date DESC";

                    $fetch_bookings_stmt = mysqli_prepare($conn, $fetch_bookings_query);
                    mysqli_stmt_bind_param($fetch_bookings_stmt, "i", $restaurant_id);
                    mysqli_stmt_execute($fetch_bookings_stmt);
                    $fetch_bookings_result = mysqli_stmt_get_result($fetch_bookings_stmt);

                    if (mysqli_num_rows($fetch_bookings_result) > 0) 
                    {
                        $response = fetch_booking_details($fetch_bookings_result);
                       
                        $response['message'] = "Upcoming reservation fetched successfully";
                        $response['status'] = 200;  
                    }
                    else
                    {
                        $response['message'] = "No bookings found for the given restaurant and date.";
                        $response['status'] = 201;
                    }
                }
                else if (isset($_POST['confirm_reservation_history']) && !empty($_POST['confirm_reservation_history']))
                {
                    // for all history details
                    $fetch_booking_history_query = "SELECT r.reservation_id, u.user_full_name, u.user_phone_number, rest_slot.start_time , r.reservation_status, r.reservation_date, r.cancel_reason, rest_time.meal_type
                    FROM reservations AS r
                    INNER JOIN `user_master` AS u
                    ON u.user_id = r.user_id
                    INNER JOIN `restaurant_slots` AS rest_slot
                    ON rest_slot.restaurant_slot_id = r.restaurant_slot_id
                    INNER JOIN `restaurant_time` AS rest_time
                    ON rest_time.restaurant_time_id = rest_slot.restaurant_time_id
                    WHERE r.restaurant_id = ? AND (r.reservation_date < curdate() OR (r.reservation_date = curdate() AND rest_slot.start_time <= CURTIME())) AND r.reservation_status = 1
                    ORDER BY r.reservation_date DESC";

                    $fetch_booking_history_stmt = mysqli_prepare($conn, $fetch_booking_history_query);
                    mysqli_stmt_bind_param($fetch_booking_history_stmt, "i", $restaurant_id);
                    mysqli_stmt_execute($fetch_booking_history_stmt);
                    $fetch_booking_history_result = mysqli_stmt_get_result($fetch_booking_history_stmt);

                    if (mysqli_num_rows($fetch_booking_history_result) > 0)
                    {
                        $response = fetch_booking_details($fetch_booking_history_result);                    

                        $response['message'] = "Booking history fetched successfully";
                        $response['status'] = 200;
                    } 
                    else
                    {
                        $response['message'] = "No reservation history found for the given restaurant.";
                        $response['status'] = 201;
                    }
                }
                else if (isset($_POST['cancel_resevation']) && !empty($_POST['cancel_resevation']))
                {
                    $fetch_cancellation_reservation_query = "SELECT r.reservation_id, u.user_full_name, u.user_phone_number, rest_slot.start_time , r.reservation_status, r.reservation_date, r.cancel_reason, rest_time.meal_type
                    FROM reservations AS r
                    INNER JOIN `user_master` AS u
                    ON u.user_id = r.user_id
                    INNER JOIN `restaurant_slots` AS rest_slot
                    ON rest_slot.restaurant_slot_id = r.restaurant_slot_id
                    INNER JOIN `restaurant_time` AS rest_time
                    ON rest_time.restaurant_time_id = rest_slot.restaurant_time_id
                    WHERE r.restaurant_id = ? AND r.reservation_status = 0
                    ORDER BY r.reservation_date DESC";

                    $fetch_cancellation_reservation_query_stmt = mysqli_prepare($conn, $fetch_cancellation_reservation_query);
                    mysqli_stmt_bind_param($fetch_cancellation_reservation_query_stmt, "i", $restaurant_id);
                    mysqli_stmt_execute($fetch_cancellation_reservation_query_stmt);
                    $fetch_cancellation_reservation_query_result = mysqli_stmt_get_result($fetch_cancellation_reservation_query_stmt);

                    if (mysqli_num_rows($fetch_cancellation_reservation_query_result) > 0)
                    {
                        $response = fetch_booking_details($fetch_cancellation_reservation_query_result);

                        $response['message'] = "Cancellation booking fetched successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "No cancellation found for the given restaurant.";
                        $response['status'] = 201;
                    }
                }
                else 
                {
                    // Query to fetch the latest booking
                    $fetch_latest_booking_query = "SELECT r.reservation_id, u.user_full_name, u.user_phone_number, rest_slot.start_time, 
                               r.reservation_status, r.reservation_date, r.cancel_reason, rest_time.meal_type
                        FROM reservations AS r
                        INNER JOIN `user_master` AS u 
                        ON u.user_id = r.user_id
                        INNER JOIN `restaurant_slots` AS rest_slot 
                        ON rest_slot.restaurant_slot_id = r.restaurant_slot_id
                        INNER JOIN `restaurant_time` AS rest_time
                        ON rest_time.restaurant_time_id = rest_slot.restaurant_time_id
                        WHERE r.restaurant_id = ?
                        ORDER BY r.reservation_date DESC, rest_slot.start_time DESC";

                    $fetch_latest_booking_stmt = mysqli_prepare($conn, $fetch_latest_booking_query);
                    mysqli_stmt_bind_param($fetch_latest_booking_stmt, "i", $restaurant_id);
                    mysqli_stmt_execute($fetch_latest_booking_stmt);
                    $fetch_latest_booking_result = mysqli_stmt_get_result($fetch_latest_booking_stmt);
                
                    if (mysqli_num_rows($fetch_latest_booking_result) > 0)
                    {
                        $response = fetch_booking_details($fetch_latest_booking_result);
                       
                        $response['message'] = "All booking deatails fetched successfully";
                        $response['status'] = 200;
                    }
                    else 
                    {
                        $response['message'] = "No bookings found for the given restaurant.";
                        $response['status'] = 201;
                    }
                }              
            }
            else
            {
                $response['message'] = "restaurant id is required";
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