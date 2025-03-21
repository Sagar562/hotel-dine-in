<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
include_once '../../lib/location.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'add_restaurant_address')
        {
            if (
                    (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                    && (isset($_POST['owner_id']) && !empty($_POST['owner_id']))
                    && (isset($_POST['restaurant_number']) && !empty($_POST['restaurant_number']))
                    && (isset($_POST['restaurant_complex']) && !empty($_POST['restaurant_complex']))
                    && (isset($_POST['restaurant_area']) && !empty($_POST['restaurant_area']))
                    && (isset($_POST['restaurant_latitude']) && !empty($_POST['restaurant_latitude']))
                    && (isset($_POST['restaurant_longitude']) && !empty($_POST['restaurant_longitude']))
                )
                {
                    $restaurant_id = $_POST['restaurant_id'];
                    $owner_id = $_POST['owner_id'];
                    $restaurant_number = $_POST['restaurant_number'];
                    $restaurant_complex = $_POST['restaurant_complex'];
                    $restaurant_area = $_POST['restaurant_area'];
                    $restaurant_latitude = $_POST['restaurant_latitude'];
                    $restaurant_longitude = $_POST['restaurant_longitude'];
                    $restaurant_landmark = (isset($_POST['restaurant_landmark']) && !empty($_POST['restaurant_landmark'])) ? $_POST['restaurant_landmark'] : '';

                    // check for restaurant exist in db or not
                    $check_restaurant = "SELECT restaurant_id FROM `restaurant_master`
                                        WHERE restaurant_id = ?";
                    $check_restaurant_stmt = mysqli_prepare($conn, $check_restaurant);
                    if (!$check_restaurant_stmt)
                    {
                        $response['message'] = "Error while preparing check restaurant query" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    mysqli_stmt_bind_param($check_restaurant_stmt, "i", $restaurant_id);
                    if (!mysqli_stmt_execute($check_restaurant_stmt))
                    {
                        $response['message'] = "Error while executing check restaurant query" . mysqli_error($conn);
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

                    // check for owner present in database or not
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
                    if (mysqli_num_rows($check_owner_result) === 0)
                    {
                        $response['message'] = "owner not found";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }

                    // check for owenr and restaurant are same at restaurant registration
                    $check_owner_restaurant = "SELECT restaurant_id, restaurant_addedBy FROM `restaurant_master`
                                                WHERE restaurant_id = ? AND restaurant_addedBy = ?";
                    $check_owner_restaurant_stmt = mysqli_prepare($conn, $check_owner_restaurant);
                    if (!$check_owner_restaurant_stmt)
                    {
                        $response['message'] = "Error while preparing check owner restaurant query" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    mysqli_stmt_bind_param($check_owner_restaurant_stmt, "ii", $restaurant_id, $owner_id);
                    if (!mysqli_stmt_execute($check_owner_restaurant_stmt))
                    {
                        $response['message'] = "Error while executing check owner restaurant query" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    $check_owner_restaurant_result = mysqli_stmt_get_result($check_owner_restaurant_stmt);
                    if (mysqli_num_rows($check_owner_restaurant_result) === 0)
                    {
                        $response['message'] = "Owner not found with registered restaurant";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }

                    // latitude and longitude function call
                    $address = getDetailedAddressFromLatLon($restaurant_latitude, $restaurant_longitude);
                    if ($address === 'Address not found')
                    {
                        $response['message'] = "Error while fetching address details from latitude & longitude";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    $restaurant_street = isset($address['Street']) ? $address['Street'] : '';
                    $restaurant_city = isset($address['City']) ? $address['City'] : '';
                    $restaurant_state = isset($address['State']) ? $address['State'] : '';
                    $restaurant_country = isset($address['Country']) ? $address['Country'] : '';

                    // insert restaurant address
                    $insert_restaurant_address = "INSERT INTO `restaurant_address`(restaurant_id, restaurant_number, restaurant_complex, restaurant_street, restaurant_area, restaurant_landmark, restaurant_city, restaurant_state, restaurant_country, restaurant_latitude, restaurant_longitude, restaurant_address_addedBy)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $insert_restaurant_address_stmt = mysqli_prepare($conn, $insert_restaurant_address);
                    if (!$insert_restaurant_address_stmt)
                    {
                        $response['message'] = "Error while preparing insert restaurant address query" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    mysqli_stmt_bind_param($insert_restaurant_address_stmt, "issssssssddi", $restaurant_id, $restaurant_number, $restaurant_complex, $restaurant_street, $restaurant_area, $restaurant_landmark, $restaurant_city, $restaurant_state, $restaurant_country, $restaurant_latitude, $restaurant_longitude, $owner_id);
                    if (mysqli_stmt_execute($insert_restaurant_address_stmt))
                    {
                        $response['message'] = "Restaurant address added successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "Error while executing insert restaurant address query" . mysqli_error($conn);
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                }
                else
                {
                    $response['message'] = "restaurant id, owner id, restaurant number, restaurant complex, restaurant area, restaurant latitude, restaurant longitude";
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