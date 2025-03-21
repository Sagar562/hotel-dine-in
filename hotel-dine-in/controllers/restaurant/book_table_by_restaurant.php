<?php

// sagar (restaurant can book table for walk-in guest and also in advance)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'table_booking_by_restaurant')
        {
            if (
                (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                && (isset($_POST['reservation_date']) && !empty($_POST['reservation_date']))
                && (isset($_POST['restaurant_slot_id']) && !empty($_POST['restaurant_slot_id']))
                && (isset($_POST['number_of_guests']) && !empty($_POST['number_of_guests']))
            )
            {
                $restaurant_id = trim($_POST['restaurant_id']);
                $reservation_date = trim($_POST['reservation_date']);
                $restaurant_slot_id = trim($_POST['restaurant_slot_id']);
                $number_of_guests = trim($_POST['number_of_guests']);
                $reservation_mode = 1;

                // validation
                $date = DateTime::createFromFormat('Y-m-d', $reservation_date);
                if (!$date || $date->format('Y-m-d') !== $reservation_date) {
                    $response['message'] = "Invalid date format. The correct format is YYYY-MM-DD";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check for restaurant exist or not
                $check_restaurant = "SELECT restaurant_id, restaurant_capacity FROM `restaurant_master`
                                     WHERE restaurant_id = ? AND restauarnt_approved_status = 1 AND restaurant_status = 1";
                $check_restaurant_stmt = mysqli_prepare($conn, $check_restaurant);
                mysqli_stmt_bind_param($check_restaurant_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($check_restaurant_stmt);
                $check_restaurant_result = mysqli_stmt_get_result($check_restaurant_stmt);

                if (mysqli_num_rows($check_restaurant_result) === 0)
                {
                    $response['message'] = "Restaurant not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                $restaurant_capacity = mysqli_fetch_assoc($check_restaurant_result)['restaurant_capacity'];


                // check slot id is coming from same restaurant id
                $check_restaurant_slot = "SELECT rest_slot.restaurant_slot_id FROM `restaurant_slots` AS rest_slot
                                        INNER JOIN `restaurant_time` AS rest_time
                                        ON rest_time.restaurant_time_id = rest_slot.restaurant_time_id
                                        INNER JOIN `restaurant_master` AS rest
                                        ON rest.restaurant_id = rest_time.restaurant_id
                                        WHERE rest.restaurant_id = ? AND rest_slot.restaurant_slot_id = ?
                                        AND rest.restaurant_status = 1 AND rest_time.is_delete = 0";
                $check_restaurant_slot_stmt = mysqli_prepare($conn, $check_restaurant_slot);
                mysqli_stmt_bind_param($check_restaurant_slot_stmt, "ii", $restaurant_id, $restaurant_slot_id);
                mysqli_stmt_execute($check_restaurant_slot_stmt);
                $check_restaurant_slot_result = mysqli_stmt_get_result($check_restaurant_slot_stmt);

                if (mysqli_num_rows($check_restaurant_slot_result) === 0)
                {
                    $response['message'] = "slotId not found with given restaurantId";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check for available slot
                $check_resetvation_query = "SELECT SUM(number_of_guests) AS reserved_count FROM reservations WHERE restaurant_id = ? AND reservation_date = ? AND restaurant_slot_id = ? AND reservation_status = 1";
                $check_resetvation_query_stmt = mysqli_prepare($conn, $check_resetvation_query);
                
                mysqli_stmt_bind_param($check_resetvation_query_stmt, "isi", $restaurant_id,$reservation_date, $restaurant_slot_id);
                mysqli_stmt_execute($check_resetvation_query_stmt);
                $check_resetvation_query_result = mysqli_stmt_get_result($check_resetvation_query_stmt);

                $reserved_count = mysqli_fetch_assoc($check_resetvation_query_result)['reserved_count'];

                // check current guest capacity is greater then total capacity
                if ($reserved_count + $number_of_guests > $restaurant_capacity)
                {
                    $response['message'] = "not enough slots available for the selected reservation time";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // insert reservation in database
                $insert_reservation_query = "INSERT INTO `reservations`(restaurant_id, restaurant_slot_id, reservation_date, number_of_guests, reservation_mode)
                VALUES(?, ?, ?, ?, ?)";

                $insert_reservation_query_stmt = mysqli_prepare($conn, $insert_reservation_query);
                mysqli_stmt_bind_param($insert_reservation_query_stmt, "iisii", $restaurant_id, $restaurant_slot_id, $reservation_date, $number_of_guests, $reservation_mode);
                
                if (mysqli_stmt_execute($insert_reservation_query_stmt))
                {
                    $response['message'] = "Reservation successfully done by restaurant";
                    $response['status'] = 200;
                }
                else
                {
                    $response['message'] = "Failed to make reservation";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "restaurant id, reservation date, slot id and number of guest required";
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