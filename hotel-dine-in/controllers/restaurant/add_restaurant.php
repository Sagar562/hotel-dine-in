<?php

// sagar (restaurant registration)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'add_restaurant')
        {
            if (
                (isset($_POST['owner_id']) && !empty($_POST['owner_id']))
                && (isset($_POST['restaurant_name']) && !empty($_POST['restaurant_name']))
                && (isset($_POST['restaurant_licence_no']) && !empty($_POST['restaurant_licence_no']))
                && (isset($_POST['restaurant_avg_price']) && !empty($_POST['restaurant_avg_price']))
                && (isset($_POST['restaurant_description']) && !empty($_POST['restaurant_description']))
                && (isset($_POST['restaurant_food_type']))
                && (isset($_POST['restaurant_capacity']) && !empty($_POST['restaurant_capacity']))
            )
            {
                // find owner
                $owner_id = $_POST['owner_id'];

                $check_owner = "SELECT owner_email, owner_phone_number FROM `owner_master`
                                WHERE owner_id = ?";
                $check_owner_stmt = mysqli_prepare($conn, $check_owner);
                if (!$check_owner_stmt)
                {
                    $response['message'] = "Error while preparing check owner query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_owner_stmt, "i", $owner_id);
                if (!mysqli_stmt_execute($check_owner_stmt))
                {
                    $response['message'] = "Error while executing check owner query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $check_owner_result = mysqli_stmt_get_result($check_owner_stmt);
                if (mysqli_num_rows($check_owner_result) > 0)
                {
                    $owner_data = mysqli_fetch_assoc($check_owner_result);
                    $owner_email = $owner_data['owner_email'];
                    $owner_phone_number = $owner_data['owner_phone_number'];
                }
                else
                {
                    $response['message'] = "owner not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // get restaurant data
                $restaurant_name = $_POST['restaurant_name'];
                $restaurant_email = (isset($_POST['restaurant_email']) && !empty($_POST['restaurant_email'])) ? $_POST['restaurant_email'] : $owner_email;
                $restaurant_phone_number = (isset($_POST['restaurant_phone_number']) && !empty($_POST['restaurant_phone_number'])) ? $_POST['restaurant_phone_number'] :$owner_phone_number;
                $restaurant_licence_no = $_POST['restaurant_licence_no'];
                $restaurant_website_link = (isset($_POST['restaurant_website_link']) && !empty($_POST['restaurant_website_link'])) ? $_POST['restaurant_website_link'] : '';
                $restaurant_avg_price = $_POST['restaurant_avg_price'];
                $restaurant_description = $_POST['restaurant_description'];
                $restaurant_food_type = $_POST['restaurant_food_type'];
                $restaurant_capacity = $_POST['restaurant_capacity'];

                // validation
                if (!preg_match("/^[a-zA-Z0-9\s]+$/", $restaurant_name) || strlen($restaurant_name) < 2 || strlen($restaurant_name) > 50) {
                    $response['message'] = "Restaurant name must contain letters, numbers and spaces, and be between 2 and 50 characters.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // restaurant email
                if (!filter_var($restaurant_email, FILTER_VALIDATE_EMAIL)) {
                    $response['message'] = "Invalid restaurant email address.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // restaurant phone number
                if (!preg_match("/^[0-9]{10}$/", $restaurant_phone_number)) {
                    $response['message'] = "Restaurant phone number must be 10 digits.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // licence number
                if (!preg_match("/^[a-zA-Z0-9]{5,20}$/", $restaurant_licence_no)) {
                    $response['message'] = "Restaurant licence number must be alphanumeric and between 5 to 20 characters.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // average price
                if (!is_numeric($restaurant_avg_price) || $restaurant_avg_price <= 0) {
                    $response['message'] = "Average price must be a positive number.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // restaurant description
                if (strlen($restaurant_description) < 10 || strlen($restaurant_description) > 500) {
                    $response['message'] = "Restaurant description must be between 10 and 500 characters.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // food type
                if (!preg_match("/^[0-2]{1}$/", $restaurant_food_type)) {
                    $response['message'] = "Restaurant food type must be 0, 1 or 2 digits.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // food capacity
                if (!preg_match("/^[0-9]{1,3}$/", $restaurant_capacity)) 
                {
                    $response['message'] = "Restaurant capactity must be digits only and not more then 999";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // check licence number in database for duplicate
                $check_licence_no = "SELECT restaurant_id FROM `restaurant_master`
                                    WHERE restaurant_licence_no = ?";
                $check_licence_no_stmt = mysqli_prepare($conn, $check_licence_no);
                if (!$check_licence_no_stmt)
                {
                    $response['message'] = "Error while preparing to check licence number query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_licence_no_stmt, "s", $restaurant_licence_no);
                if (!mysqli_stmt_execute($check_licence_no_stmt))
                {
                    $response['message'] = "Error while executing check licence number query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $check_licence_no_result = mysqli_stmt_get_result($check_licence_no_stmt);
                if (mysqli_num_rows($check_licence_no_result) > 0)
                {
                    $response['message'] = "Restaurant licence number already exist";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }


                // insert into restaurant master
                $insert_restaurant = "INSERT INTO `restaurant_master`(restaurant_name, restaurant_email, restaurant_phone_number, restaurant_licence_no, restaurant_website_link, restaurant_avg_price, restaurant_description, restaurant_food_type,restaurant_addedBy, restaurant_capacity)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $insert_restaurant_stmt = mysqli_prepare($conn, $insert_restaurant);
                if (!$insert_restaurant_stmt)
                {
                    $response['message'] = "Error while preparing to insert restaurant query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($insert_restaurant_stmt, "ssissisiii", $restaurant_name, $restaurant_email, $restaurant_phone_number, $restaurant_licence_no, $restaurant_website_link, $restaurant_avg_price, $restaurant_description, $restaurant_food_type, $owner_id, $restaurant_capacity);

                if (mysqli_stmt_execute($insert_restaurant_stmt))
                {
                    // insert owner id and restaurant id into restaurant_owners table
                    $restaurant_id = mysqli_insert_id($conn);
                    $insert_owner = "INSERT INTO `restaurant_owners`(restaurant_id, owner_id)
                                    VALUES(?, ?)";
                    $insert_owner_stmt = mysqli_prepare($conn, $insert_owner);
                    if (!$insert_owner_stmt)
                    {
                        $response['message'] = "Error while preparing to insert owner query";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    mysqli_stmt_bind_param($insert_owner_stmt, "ii", $restaurant_id, $owner_id);
                    if (mysqli_stmt_execute($insert_owner_stmt))
                    {
                        $response['message'] = "Restaurant registration form submited successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "Error while executing insert owner query";
                        $response['status'] = 201;
                    }
                }
                else
                {
                    $response['message'] = "Error while executing insert restaurant query";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "OwnerId, restaurant name, licence number, average price, description and food type are required";
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