<?php

// sagar (add user address)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
include_once '../../lib/location.php';
$response = [];


    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'user_address')
        {
            if (
                (isset($_POST['house_number']) && !empty($_POST['house_number']))
                && (isset($_POST['society_name']) && !empty($_POST['society_name']))
                && (isset($_POST['area']) && !empty($_POST['area']))
                && (isset($_POST['latitude']) && !empty($_POST['latitude']))
                && (isset($_POST['longitude']) && !empty($_POST['longitude']))
                && (isset($_POST['user_id']) && !empty($_POST['user_id']))
            )
            {
                $house_number = trim($_POST['house_number']);
                $society_name = trim($_POST['society_name']);
                $area = trim($_POST['area']);
                $latitude = trim($_POST['latitude']);
                $longitude = trim($_POST['longitude']);
                $landmark = trim(isset($_POST['landmark']) ? $_POST['landmark'] : '');
                $user_id = $_POST['user_id'];


                // check user is present in database or not
                $check_user = "SELECT * FROM `user_master` WHERE user_id = ?";

                $check_user_stmt = mysqli_prepare($conn, $check_user);

                if (!$check_user_stmt)
                {
                    $response['message'] = "Error while preparing user prepare";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_user_stmt, "i", $user_id);

                if (!mysqli_stmt_execute($check_user_stmt))
                {
                    $response['message'] = "Error while executing user query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $check_user_result = mysqli_stmt_get_result($check_user_stmt);
                if (mysqli_num_rows($check_user_result) === 0)
                {
                    $response['message'] = "User not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // latitude and longitude function call
                $address = getDetailedAddressFromLatLon($latitude, $longitude);
                if ($address === 'Address not found')
                {
                    $response['message'] = "Error while fetching address details from latitude & longitude";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $street = isset($address['Street']) ? $address['Street'] : '';
                $city = isset($address['City']) ? $address['City'] : '';
                $state = isset($address['State']) ? $address['State'] : '';
                $country = isset($address['Country']) ? $address['Country'] : '';

                // validation
                if (strlen($house_number) > 50) {
                    $response['message'] = "House number too long";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                if (strlen($society_name) > 100) {
                    $response['message'] = "Society name too long";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                if (strlen($area) > 50) {
                    $response['message'] = "House number too long";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                if (strlen($street) > 100) {
                    $response['message'] = "Society name too long";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                if (strlen($landmark) > 100) {
                    $response['message'] = "Society name too long";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
          
                // only 5 address allow per user
                $check_address_limit = "SELECT COUNT(user_address_id) AS total_address FROM `user_address`
                                        WHERE user_id = ?";
                $check_address_limit_stmt = mysqli_prepare($conn, $check_address_limit);
                mysqli_stmt_bind_param($check_address_limit_stmt, "i", $user_id);
                mysqli_stmt_execute($check_address_limit_stmt);
                $check_address_limit_result = mysqli_stmt_get_result($check_address_limit_stmt);

                $user_address_count = mysqli_fetch_assoc($check_address_limit_result)['total_address'];

                $address_limit = 3;

                if ($address_limit > $user_address_count)
                {
                    // insert data into database
                    $insert_user_address_query = "INSERT INTO `user_address`(user_address_house_number, user_address_society, user_address_street, user_address_area, user_address_landmark, city_name, state_name, country_name, user_id, user_address_latitude, user_address_longitude)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $insert_user_address_query_stmt = mysqli_prepare($conn, $insert_user_address_query);

                    mysqli_stmt_bind_param($insert_user_address_query_stmt, 'ssssssssidd', $house_number, $society_name, $street, $area, $landmark, $city, $state, $country, $user_id, $latitude, $longitude);

                    $check_user_address_result = mysqli_stmt_execute($insert_user_address_query_stmt);

                    if ($check_user_address_result)
                    {
                        $user_address_id = mysqli_insert_id($conn);
                        $response['user_address_id'] = $user_address_id;
                        $response['message'] = "User Address inserted successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "Error while inserting user address";
                        $response['status'] = 201;
                    }
                }
                else
                {
                    $response['message'] = "New Address limit exides";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "house number, societ name are required";
                $response['status'] = 201;
            }

        }
        else
        {
            $response['message'] = "Invalid Tag";
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