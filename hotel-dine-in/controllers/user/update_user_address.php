<?php

// sagar (update user address)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
include_once '../../lib/location.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'update_user_address')
        {
            if (
                (!empty($_POST['house_number']))
                && (!empty($_POST['society_name']))
                && (!empty($_POST['area']))
                && (!empty($_POST['user_id']))
            )
            {
                $house_number = trim($_POST['house_number']);
                $society_name = trim($_POST['society_name']);
                $area = trim($_POST['area']);
                $latitude = isset($_POST['latitude']) && !empty($_POST['latitude']) ? trim($_POST['latitude']) : '';
                $longitude = isset($_POST['longitude']) && !empty($_POST['longitude']) ? trim($_POST['longitude']) : '';
                $landmark = trim(isset($_POST['landmark']) ? $_POST['landmark'] : '');
                $user_id = $_POST['user_id'];

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
                $check_user_address =   "SELECT user.user_id FROM `user_master` AS user
                                        INNER JOIN `user_address` as user_address
                                        ON user.user_id = user_address.user_id
                                        WHERE user.user_id = ?";
                $check_user_address_stmt = mysqli_prepare($conn, $check_user_address);

                mysqli_stmt_bind_param($check_user_address_stmt, "i", $user_id);
  
                mysqli_stmt_execute($check_user_address_stmt);
  
                $check_user_address_result = mysqli_stmt_get_result($check_user_address_stmt);
  
                if (mysqli_num_rows($check_user_address_result) === 0)
                {
                    $response['message'] = "Please enter address";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();   
                }

                // check user is present in user table but not in address table
                $check_user_address =   "SELECT user.user_id FROM `user_master` AS user
                INNER JOIN `user_address` as user_address
                ON user.user_id = user_address.user_id
                WHERE user.user_id = ?";
                
                $check_user_address_stmt = mysqli_prepare($conn, $check_user_address);

                mysqli_stmt_bind_param($check_user_address_stmt, "i", $user_id);

                mysqli_stmt_execute($check_user_address_stmt);

                $check_user_address_result = mysqli_stmt_get_result($check_user_address_stmt);

                if (mysqli_num_rows($check_user_address_result) === 0)
                {
                $response['message'] = "Please enter address";
                $response['status'] = 201;
                echo json_encode($response);
                exit();   
                }



                // latitude and longitude function call
                if ($latitude && $longitude)
                {
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
                }
             

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
                if (strlen($landmark) > 100) {
                    $response['message'] = "Society name too long";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // update user address
                $update_user_address_query = "UPDATE `user_address`
                                            SET user_address_house_number = ?,
                                            user_address_society = ?,
                                            user_address_street = ?,
                                            user_address_area = ?,
                                            user_address_landmark = ?,
                                            city_name = ?,
                                            state_name = ?,
                                            country_name = ?,
                                            user_address_updatedAt = now()
                                            WHERE user_id = ?";

                $update_user_address_query_stmt = mysqli_prepare($conn, $update_user_address_query);

                mysqli_stmt_bind_param($update_user_address_query_stmt, 'ssssssssi', $house_number, $society_name, $street, $area, $landmark, $city, $state, $country, $user_id);
                
                // echo $update_user_address_query;
                $update_user_address_query_result = mysqli_stmt_execute($update_user_address_query_stmt);
                
                if ($update_user_address_query_result)
                {
                    $response['message'] = "User address updated successfully";
                    $response['status'] = 200;
                }
                else
                {
                    $response['message'] = "Error while updating user address";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "No Empty value allow";
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