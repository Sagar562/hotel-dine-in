<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

// Function to add minutes to a time
function addMinutes($time, $minutes) {
    $dateTime = DateTime::createFromFormat('H:i', $time);
    $dateTime->modify("+{$minutes} minutes");
    return $dateTime->format('H:i');
}

// Function to convert 24-hour time to 12-hour time with AM/PM
function convertTo12HourFormat($time) {
    $dateTime = DateTime::createFromFormat('H:i', $time); // Create a DateTime object from the 24-hour format
    return $dateTime->format('g:i A'); // Return the 12-hour format (g = 12-hour format without leading zero, A = AM/PM)
}

// Function to add 24 hours to a time (keeps it in the correct format)
function add24Hours($time) {
    // Create a DateTime object from the provided time (in H:i format)
    $dateTime = DateTime::createFromFormat('H:i', $time);
    
    // Modify the DateTime object by adding 24 hours
    $dateTime->modify("+24 hours");
    
    // Return the modified time in 'H:i' format (24-hour format)
    return $dateTime->format('H:i');
}

// Function to create subslots based on interval time
function createSubSlots($start_time, $end_time, $interval_time, $restaurant_time_id) {
    $subslots = [];
    $current_start_time = $start_time;
    
    // Check if the end time is earlier than the start time, indicating time crosses midnight
    $start_time_dt = DateTime::createFromFormat('H:i', $start_time);
    $end_time_dt = DateTime::createFromFormat('H:i', $end_time);

    if ($end_time_dt < $start_time_dt) {
        // Adjust the end time by adding 24 hours
        $end_time = add24Hours($end_time); // Adding 24 hours to the end time (now 26:00 if required)

    }


    // Generate subslots as long as the end time is not exceeded
    while (strtotime($current_start_time) < strtotime($end_time)) {
        $current_end_time = addMinutes($current_start_time, $interval_time);

        // Convert both start and end times to 12-hour format
        $start_time_12hr = convertTo12HourFormat($current_start_time);
        $end_time_12hr = convertTo12HourFormat($current_end_time);

        // Store subslot in 12-hour format
        $subslots[] = [
            'restaurant_time_id' => $restaurant_time_id,
            'start_time' => $start_time_12hr,
            'end_time' => $end_time_12hr
        ];

        // Move to the next subslot's start time
        $current_start_time = $current_end_time;
    }
    return $subslots;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'restaurant_slots')
    {
        if (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id'])) 
        {
            $restaurant_id = $_POST['restaurant_id'];
            
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

            // Check the meal time data for breakfast, lunch, and dinner
            $meal_data = [
                0 => ['meal_start_time' => NULL, 'meal_end_time' => NULL],  // Breakfast
                1 => ['meal_start_time' => NULL, 'meal_end_time' => NULL],  // Lunch
                2 => ['meal_start_time' => NULL, 'meal_end_time' => NULL],  // Dinner
            ];

            for ($meal_type = 0; $meal_type <= 2; $meal_type++) {
                if (
                    isset($_POST["meal_start_time_{$meal_type}"]) && !empty($_POST["meal_start_time_{$meal_type}"]) 
                    && isset($_POST["meal_end_time_{$meal_type}"]) && !empty($_POST["meal_end_time_{$meal_type}"])
                    && isset($_POST["interval_time_{$meal_type}"]) && !empty($_POST["interval_time_{$meal_type}"]) 
                ) {

                    $meal_data[$meal_type]['meal_start_time'] = $_POST["meal_start_time_{$meal_type}"];
                    $meal_data[$meal_type]['meal_end_time'] = $_POST["meal_end_time_{$meal_type}"];
                    $meal_data[$meal_type]['interval_time'] = $_POST["interval_time_{$meal_type}"];                    
                
                    $interval_time = $meal_data[$meal_type]['interval_time'];

                    if ($interval_time % 15 !== 0 || $interval_time <= 0 || $interval_time > 150) {
                        $response['message'] = "Interval time must be a multiple of 15 minutes and less than or equal to 150 minutes.";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                }
            }

           
            // Insert the meal times and time slots into the database
            foreach ($meal_data as $meal_type => $value) {
                if ($value['meal_start_time'] !== NULL && $value['meal_end_time'] !== NULL && $value['interval_time'] !== NULL)
                {
                    $meal_start_time = $value['meal_start_time'];
                    $meal_end_time = $value['meal_end_time'];
                    $interval_time = $value['interval_time'];

                    // check for that meal type time slot already exiest for that restaurant if yes then update it
                    $check_meal_type = "SELECT restaurant_time_id FROM `restaurant_time`
                                        WHERE meal_type = ? AND restaurant_id = ?";
                    $check_meal_type_stmt = mysqli_prepare($conn, $check_meal_type);
                    mysqli_stmt_bind_param($check_meal_type_stmt, "ii", $meal_type, $restaurant_id);
                    mysqli_stmt_execute($check_meal_type_stmt);
                    $check_meal_type_result = mysqli_stmt_get_result($check_meal_type_stmt);

                    if (mysqli_num_rows($check_meal_type_result) > 0) {
                        // Fetch the restaurant_time_id from the result
                        $data = mysqli_fetch_assoc($check_meal_type_result);
                        $restaurant_time_id = $data['restaurant_time_id'];  
                    
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
                    }
                    
                    // Insert the created slots into the database
                    $insert_time = "INSERT INTO restaurant_time (restaurant_id, meal_type, meal_start_time, meal_end_time, interval_time) VALUES (?, ?, ?, ?, ?)";

                    $insert_time_stmt = mysqli_prepare($conn, $insert_time); 
                    if (!$insert_time_stmt)
                    {
                        $response['message'] = "Error while preparing insert restaurant time";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    mysqli_stmt_bind_param($insert_time_stmt, "iissi", $restaurant_id, $meal_type, $meal_start_time, $meal_end_time, $interval_time);
                    if (mysqli_stmt_execute($insert_time_stmt))
                    {
                        $restaurant_time_id = mysqli_insert_id($conn);

                        $subslots = createSubSlots($meal_start_time, $meal_end_time, $interval_time, $restaurant_time_id);

                        foreach ($subslots as $subslot)
                        {

                            $insert_slot = "INSERT INTO `restaurant_slots` (restaurant_time_id, start_time, end_time)
                            VALUES(?, ?, ?)";
                            
                            $insert_slot_stmt = mysqli_prepare($conn, $insert_slot);
                            if (!$insert_slot_stmt)
                            {
                                $response['message'] = "Error while preparing insert restaurant slot" . mysqli_error($conn);
                                $response['status'] = 201;
                                echo json_encode($response);
                                exit();
                            }
                            mysqli_stmt_bind_param($insert_slot_stmt, "iss", $subslot['restaurant_time_id'], $subslot['start_time'], $subslot['end_time']);
                            if (!mysqli_stmt_execute($insert_slot_stmt))
                            {  
                                $response['message'] = "Error while inserting restaurant slots" . mysqli_error($conn);
                                $response['status'] = 201;
                                echo json_encode($response);
                                exit();
                            }
                        }
                        $response['message'] = "Restaurant time and slots both are inserted successfully for all time slot";
                        $response['status'] = 200;
                    }    
                    else
                    {
                        $response['message'] = "Error while inserting restaurant time";
                        $response['status'] = 201;
                    }
                }
                // else
                // {
                //     $response['message'] = "Not all time slots booked";
                //     $response['status'] = 201;
                // }
            }

        } else {
            $response['message'] = "Restaurant ID is required";
            $response['status'] = 201;
        }
    } else {
        $response['message'] = "Invalid tag or tag not found";
        $response['status'] = 201;
    }

} else {
    $response['message'] = "Only POST method allowed";
    $response['status'] = 201;
}

echo json_encode($response);
?>
