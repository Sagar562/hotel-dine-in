<?php
// sagar (book a table)


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'book_table')
        {
            if (
                (isset($_POST['user_id']) && !empty($_POST['user_id']))
                && (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                && (isset($_POST['reservation_date']) && !empty($_POST['reservation_date']))
                && (isset($_POST['restaurant_slot_id']) && !empty($_POST['restaurant_slot_id']))
                && (isset($_POST['number_of_guests']) && !empty($_POST['number_of_guests']))
            )
            {
                $user_id = $_POST['user_id'];
                $restaurant_id = $_POST['restaurant_id'];
                $reservation_date = trim($_POST['reservation_date']);
                $restaurant_slot_id = $_POST['restaurant_slot_id'];
                $number_of_guests = trim($_POST['number_of_guests']);
                $reservation_mode = 0; // for online

                // check for selected date is above then privious data and rang to next 5 days
                $current_date = date('Y-m-d');
                $max_date = date('Y-m-d', strtotime("+5 days"));

                // validation
                $date = DateTime::createFromFormat('Y-m-d', $reservation_date);
                if (!$date || $date->format('Y-m-d') !== $reservation_date) {
                    $response['message'] = "Invalid date format. The correct format is YYYY-MM-DD";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                if ($reservation_date < $current_date)
                {
                    $response['message'] = "Selected date cannot be in the past";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                if ($reservation_date > $max_date)
                {
                    $response['message'] = "Selected date cannot be more than 5 days in the future";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check for user who wants to book table is exist in database or not
                $check_user_query = "SELECT user_id FROM `user_master`
                                     WHERE user_id = ?";
                $check_user_query_stmt = mysqli_prepare($conn, $check_user_query);
                mysqli_stmt_bind_param($check_user_query_stmt, "i", $user_id);
                mysqli_stmt_execute($check_user_query_stmt);
                $check_user_query_result = mysqli_stmt_get_result($check_user_query_stmt);
                
                if (mysqli_num_rows($check_user_query_result) === 0)
                {
                    $response['message'] = "User not found with given user id";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                
                // check for user is blocked or not and also there is user in database or not
                $check_user_isBlocked = "SELECT user_id FROM `user_master`
                                         WHERE is_block = 1 AND user_id = ?";
                $check_user_isBlocked_stmt = mysqli_prepare($conn, $check_user_isBlocked);
                mysqli_stmt_bind_param($check_user_isBlocked_stmt, "i", $user_id);
                mysqli_stmt_execute($check_user_isBlocked_stmt);
                
                $check_user_isBlocked_result = mysqli_stmt_get_result($check_user_isBlocked_stmt);

                if (mysqli_num_rows($check_user_isBlocked_result) > 0)
                {
                    $response['message'] = "User is temporarily blocked or not userId is not exists";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }


                // check restaurant in database or not
                $check_restaurant_query = "SELECT restaurant_id, restaurant_capacity FROM `restaurant_master`
                                           WHERE restaurant_id = ?";
                $check_restaurant_query_stmt = mysqli_prepare($conn, $check_restaurant_query);
                mysqli_stmt_bind_param($check_restaurant_query_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($check_restaurant_query_stmt);
                $check_restaurant_query_result = mysqli_stmt_get_result($check_restaurant_query_stmt);

                if (mysqli_num_rows($check_restaurant_query_result) === 0)
                {
                    $response['message'] = "Restaurant not found on given restaurant id";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();   
                }
                $restaurant_capacity = mysqli_fetch_assoc($check_restaurant_query_result)['restaurant_capacity'];

                // check for slotid is present in db or not
                $check_slot_query = "SELECT rest_slot.restaurant_slot_id FROM `restaurant_slots` AS rest_slot
                                    INNER JOIN `restaurant_time` AS rest_time
                                    ON rest_time.restaurant_time_id = rest_slot.restaurant_time_id
                                    INNER JOIN `restaurant_master` AS rest
                                    ON rest.restaurant_id = rest_time.restaurant_id
                                    WHERE rest.restaurant_id = ? AND rest_slot.restaurant_slot_id = ?
                                    AND rest.restaurant_status = 1 AND rest_time.is_delete = 0";

                $check_slot_query_stmt = mysqli_prepare($conn, $check_slot_query);
                mysqli_stmt_bind_param($check_slot_query_stmt, "ii", $restaurant_id, $restaurant_slot_id);
                mysqli_stmt_execute($check_slot_query_stmt);
                $check_slot_query_result = mysqli_stmt_get_result($check_slot_query_stmt);

                if (mysqli_num_rows($check_slot_query_result) === 0)
                {
                    $response['message'] = "slotId not found with given restaurantId";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check for slot available for user guest
                $check_resetvation_query = "SELECT SUM(number_of_guests) AS reserved_count FROM reservations WHERE restaurant_id = ? AND reservation_date = ? AND restaurant_slot_id = ? AND reservation_status = 1";
                $check_resetvation_query_stmt = mysqli_prepare($conn, $check_resetvation_query);
                
                mysqli_stmt_bind_param($check_resetvation_query_stmt, "isi", $restaurant_id,$reservation_date, $restaurant_slot_id);
                mysqli_stmt_execute($check_resetvation_query_stmt);
                $check_resetvation_query_result = mysqli_stmt_get_result($check_resetvation_query_stmt);

                $reserved_count = mysqli_fetch_assoc($check_resetvation_query_result)['reserved_count'];

                if ($reserved_count + $number_of_guests > $restaurant_capacity)
                {
                    $response['message'] = "not enough slots available for the selected reservation time";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }


                // insert reservation into database
                $insert_reservation_query = "INSERT INTO `reservations`(user_id, restaurant_id, restaurant_slot_id, reservation_date, number_of_guests, reservation_mode)
                VALUES(?, ?, ?, ?, ?, ?)";
                $insert_reservation_query_stmt = mysqli_prepare($conn, $insert_reservation_query);
                mysqli_stmt_bind_param($insert_reservation_query_stmt, "iiisii", $user_id, $restaurant_id, $restaurant_slot_id, $reservation_date, $number_of_guests, $reservation_mode);
                
                if (mysqli_stmt_execute($insert_reservation_query_stmt))
                {
                    $response['message'] = "Reservation successfully done";
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
                $response['message'] = "userId, restaurantId, date, slotId fields are required";
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
