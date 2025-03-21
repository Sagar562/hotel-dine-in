<?php

// sagar (owner registration)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if ((isset($_POST['hotel_dine_in'])) && ($_POST['hotel_dine_in'] === 'owner_registration'))
        {
            if 
            (
                (isset($_POST['owner_full_name']) && !empty($_POST['owner_full_name']))
                && (isset($_POST['owner_email']) && !empty($_POST['owner_email']))
                && (isset($_POST['owner_phone_number']) && !empty($_POST['owner_phone_number']))
                && (isset($_POST['owner_gender']) && !empty($_POST['owner_gender']))
                && (isset($_POST['owner_aadharcard']) && !empty($_POST['owner_aadharcard']))
                && (isset($_POST['owner_pancard']) && !empty($_POST['owner_pancard']))
                // && (isset($_POST['owner_image']) && !empty($_POST['owner_image']))
            )
            {
                $owner_full_name = trim($_POST['owner_full_name']);
                $owner_email = trim($_POST['owner_email']);
                $owner_phone_number = trim($_POST['owner_phone_number']);
                $owner_gender = $_POST['owner_gender'];
                $owner_aadharcard = trim($_POST['owner_aadharcard']);
                $owner_pancard = trim($_POST['owner_pancard']);


                // validation

                // full name
                if ((!preg_match("/^[a-zA-Z]+(\s[a-zA-Z]+)*$/", $owner_full_name)) || ((strlen($owner_full_name) < 3) || (strlen($owner_full_name) > 30))) {
                    $response['message'] = "Full name must contain only letters and at-least 3 character and atmost 30 characters.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                // phone number
                if (!preg_match("/^[0-9]{10}$/", $owner_phone_number)) {
                    $response['message'] = "Phone number must contain only 10 numbers";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // gender
                $valid_genders = ["male", "female", "other"];
                $owner_gender = strtolower($owner_gender); 
                if (!in_array($owner_gender, $valid_genders)) 
                {
                    $response['message'] = "Gender must be 'male', 'female', or 'other'.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check for email
                $check_owner_email =  "SELECT * FROM `owner_master` 
                                WHERE owner_email = ?";

                $check_owner_email_stmt = mysqli_prepare($conn, $check_owner_email);
                if (!$check_owner_email_stmt)
                {
                    $response['message'] = "Error while preparing owner email query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_owner_email_stmt, "s", $owner_email);
                if (!mysqli_execute($check_owner_email_stmt))
                {
                    $response['message'] = "Error while executing owner email query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                $check_owner_email_result = mysqli_stmt_get_result($check_owner_email_stmt);                

                if (mysqli_num_rows($check_owner_email_result) > 0)
                {
                    $response['message'] = "Email alread present";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // for phone number
                $check_owner_phone_number =  "SELECT * FROM `owner_master` 
                                WHERE owner_phone_number = ?";

                $check_owner_phone_number_stmt = mysqli_prepare($conn, $check_owner_phone_number);
                if (!$check_owner_phone_number_stmt)
                {
                    $response['message'] = "Error while preparing owner phone number query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_owner_phone_number_stmt, "i", $owner_phone_number);
                if (!mysqli_execute($check_owner_phone_number_stmt))
                {
                    $response['message'] = "Error while executing owner phone number query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                $check_owner_phone_number_result = mysqli_stmt_get_result($check_owner_phone_number_stmt);                

                if (mysqli_num_rows($check_owner_phone_number_result) > 0)
                {
                    $response['message'] = "phone number alread present";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // insert user data
                $owner_registration = "INSERT INTO `owner_master`(owner_full_name, owner_email, owner_phone_number, owner_gender, owner_aadharcard, owner_pancard)
                VALUES(?, ?, ?, ?, ?, ?)";

                $owner_registration_stmt = mysqli_prepare($conn, $owner_registration);

                mysqli_stmt_bind_param($owner_registration_stmt, "ssisss", $owner_full_name, $owner_email, $owner_phone_number, $owner_gender, $owner_aadharcard, $owner_pancard);

                $owner_registration_result = mysqli_stmt_execute($owner_registration_stmt);

                if ($owner_registration_result)
                {
                    $response['message'] = "Owner data inserted successfull";
                    $response['status'] = 200;
                }
                else
                {
                    $response['message'] = "Error while inserting owner details";
                    $response['status'] = 201;
                }
                
            }
            else
            {
                $response['message'] = "Full name, email, phone number, gender, adharcard, pancard fields are required";
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