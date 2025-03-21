<?php

// sagar (restaurant available slots)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'available_slots')
        {
            if(
                // (isset($_POST['user_id']) && !empty($_POST['user_id']))
                (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                && (isset($_POST['date']) && !empty($_POST['date']))
            )
            {
                $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? (int)$_POST['user_id'] : NULL;
                $restaurant_id = $_POST['restaurant_id'];
                $date = $_POST['date'];
                $restaurant_capacity = 40;

                // check for restaurant id is exists in database or not
                $check_restaurant = "SELECT restaurant_id, restaurant_capacity FROM `restaurant_master`
                                     WHERE restaurant_id = ?";
                $check_restaurant_stmt = mysqli_prepare($conn, $check_restaurant);
                mysqli_stmt_bind_param($check_restaurant_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($check_restaurant_stmt);
                $check_restaurant_result = mysqli_stmt_get_result($check_restaurant_stmt);

                if (mysqli_num_rows($check_restaurant_result) === 0)
                {
                    $response['message'] = "Restaurant not found on given restaurant id";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $restaurant_capacity = mysqli_fetch_assoc($check_restaurant_result)['restaurant_capacity'];
                // $selected_date = DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');

                 // check for selected date is above then privious data and rang to next 5 days
                $current_date = date('Y-m-d');
                $max_date = date('Y-m-d', strtotime("+5 days"));

                if ($date < $current_date)
                {
                    $response['message'] = "Selected date cannot be in the past";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                if ($date > $max_date)
                {
                    $response['message'] = "Selected date cannot be more than 5 days in the future";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // query for get slots of restaurant
                $get_slots = "SELECT rest_time.meal_type, rest_slot.restaurant_slot_id, rest_slot.start_time FROM `restaurant_time` AS rest_time
                            INNER JOIN `restaurant_slots` AS rest_slot
                            ON rest_slot.restaurant_time_id = rest_time.restaurant_time_id
                            WHERE rest_time.restaurant_id = ? AND rest_time.is_delete = 0";
                $get_slots_stmt = mysqli_prepare($conn, $get_slots);
                mysqli_stmt_bind_param($get_slots_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($get_slots_stmt);
                $get_slots_result = mysqli_stmt_get_result($get_slots_stmt);

                $available_slots = [
                    'Breakfast' => [],
                    'Lunch' => [],
                    'Dinner' => []
                ];

                $meal_types = [0 => "Breakfast", 1 => "Lunch", 2 => "Dinner"];

                if (mysqli_num_rows($get_slots_result) > 0)
                {
                    // get each slot
                    while ($data = mysqli_fetch_assoc($get_slots_result))
                    {
                        $slot_id = $data['restaurant_slot_id'];
                        $start_time = $data['start_time'];

                        $meal_type = $meal_types[$data['meal_type']];

                        // get slots on given date and check
                        $reserved_slot = "SELECT SUM(number_of_guests) AS reserved_count 
                                         FROM `reservations` 
                                         WHERE restaurant_id = ? AND reservation_date = ? 
                                         AND restaurant_slot_id = ? AND reservation_status = 1";
                        $reserved_slot_stmt = mysqli_prepare($conn, $reserved_slot);
                        mysqli_stmt_bind_param($reserved_slot_stmt, "isi", $restaurant_id, $date, $slot_id);
                        mysqli_stmt_execute($reserved_slot_stmt);
                        $reserved_slot_result = mysqli_stmt_get_result($reserved_slot_stmt);
                        
                        // check for available guest in slot
                        $reserved_guest = mysqli_fetch_assoc($reserved_slot_result)['reserved_count'];
                        $remaining_slots = $restaurant_capacity - $reserved_guest;


                        $current_time = date("H:i:s");
                        echo $start_time;
                        // add slots those reserved guest is > 0
                        if ($remaining_slots > 0)
                        {                            
                            $available_slots[$meal_type][] = [
                                "slot_id" => $slot_id,
                                // "meal_type" => $meal_type,
                                "start_time" => $start_time,
                                "remaining_slots" => $remaining_slots
                            ];
                        }
                        else if ($remaining_slots == 0)
                        {
                            $available_slots[$meal_type][] = [
                                "slot_id" => $slot_id,
                                // "meal_type" => $meal_type,
                                "start_time" => $start_time,
                                "remaining_slots" => "All seats Booked"
                            ];
                        }
                    }
                    // $response['user_id'] = $user_id;
                    $response['available_slots'] = $available_slots;
                    $response['message'] = "Available slots fetched successfully";
                    $response['status'] = 200;
                }
                else
                {
                    $response['message'] = "No slots available for given date";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "Date is required";
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