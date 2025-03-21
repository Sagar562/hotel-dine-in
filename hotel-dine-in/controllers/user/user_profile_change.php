<?php

// sagar (edit profile)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'update_user_profile')
        {
            if 
            (
                (!empty($_POST['full_name']))
                && (!empty($_POST['email']))
                && (!empty($_POST['phone_number']))
                && (!empty($_POST['gender']))
                &&(isset($_POST['user_id']) && (!empty($_POST['user_id'])))
            )
            {
                $user_id = $_POST['user_id'];
                $full_name = trim($_POST['full_name']);
                $email = trim($_POST['email']);
                $phone_number = trim($_POST['phone_number']);
                $gender = $_POST['gender'];
                $user_image = isset($_FILES['image']) ? $_FILES['image'] : '';


                // check user is already their or not
                $find_user = "SELECT * from `user_master` WHERE user_id = ?";
                $find_user_stmt = mysqli_prepare($conn, $find_user);
                if (!$find_user)
                {
                    $response['message'] = "Error while preparing find user query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                mysqli_stmt_bind_param($find_user_stmt, "i", $user_id);

                if (!mysqli_stmt_execute($find_user_stmt))
                {
                    $response['message'] = "Error while executing find user query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                $find_user_result = mysqli_stmt_get_result($find_user_stmt);
                if (mysqli_num_rows($find_user_result) === 0)
                {
                    $response['message'] = "User Not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // validation

                // full name
                if (!preg_match("/^[a-zA-Z\s]+$/", $full_name) || strlen($full_name) < 3 || strlen($full_name) > 30) {
                    $response['message'] = "Full name must contain only letters and spaces, and must be between 3 and 30 characters.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
    
                // email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $response['message'] = "Invalid email formate";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // phone number
                if (!preg_match("/^[0-9]{10}$/", $phone_number)) {
                    $response['message'] = "Phone number must contain only 10 numbers";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // gender
                $valid_genders = ["male", "female", "other"];
                $gender = strtolower($gender); 
                if (!in_array($gender, $valid_genders)) {
                    $response['message'] = "Gender must be 'male', 'female', or 'other'.";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                
                // check for email is exist in other data or not
                $check_duplicate_email_query = "SELECT user_id FROM `user_master` 
                                      WHERE user_email = ? AND user_id != ?";
                
                $check_duplicate_email_query_stmt = mysqli_prepare($conn, $check_duplicate_email_query);
                mysqli_stmt_bind_param($check_duplicate_email_query_stmt, "si", $email, $user_id);
                if (!mysqli_stmt_execute($check_duplicate_email_query_stmt))
                {
                    $response['message'] = "error while executing duplicate email query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $check_duplicate_email_query_result = mysqli_stmt_get_result($check_duplicate_email_query_stmt);

                if (mysqli_num_rows($check_duplicate_email_query_result) > 0)
                {
                    $response['message'] = "This email already exists for another user";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check for phone number is exist in other data or not
                $check_duplicate_phone_number_query = "SELECT user_id FROM `user_master` 
                                                      WHERE user_phone_number = ? AND user_id != ?";

                $check_duplicate_phone_number_query_stmt = mysqli_prepare($conn, $check_duplicate_phone_number_query);

                mysqli_stmt_bind_param($check_duplicate_phone_number_query_stmt, "ii", $phone_number, $user_id);

                if (!mysqli_stmt_execute($check_duplicate_phone_number_query_stmt))
                {
                    $response['message'] = "error while executing duplicate phone number query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();   
                }

                $check_duplicate_phone_number_query_result = mysqli_stmt_get_result($check_duplicate_phone_number_query_stmt);

                if (mysqli_num_rows($check_duplicate_phone_number_query_result) > 0)
                {
                    $response['message'] = "This phone number already exists for another user";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();   
                }

                // add profile image
                // file upload
                $image_name = "";
                if (!empty($_FILES['image']))
                {
                    $target_folder = __DIR__ . "/../../uploads/profile/";

                    if (!file_exists($target_folder)) {
                        mkdir($target_folder, 0777, true);  
                    }

                    $random_number = rand(1000, 9999);
                    $image_name = $random_number.'_'.basename($user_image['name']);
                    $target_file = $target_folder . $image_name;
                    $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // validate image formate
                    $allowed_extension = array("jpg", "jpeg", "png");

                    if (!in_array($image_type, $allowed_extension))
                    {
                        $response['message'] = "Invalid file extention";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }

                    // validate image size
                    $maxFileSize = 5 * 1024 * 1024;
                    if ($user_image['size'] > $maxFileSize)
                    {
                        $response['message'] = "Image size is large only 5mb allow";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    // move file to uploads/rating_images folder
                    // echo "Target file: " . $target_file;  

                    if (!move_uploaded_file($user_image['tmp_name'], $target_file))
                    {
                        $response['message'] = "Error while uploading image";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                }

                    $update_profile_query = "UPDATE `user_master` 
                                            SET user_full_name = ?,
                                            user_phone_number = ?,
                                            user_email = ?,
                                            user_gender = ?,
                                            user_image = ?,
                                            user_updatedAt = NOW() 
                                            WHERE user_id = ?";
                    
                    $update_profile_query_stmt = mysqli_prepare($conn, $update_profile_query);

                    mysqli_stmt_bind_param($update_profile_query_stmt, "sisssi", $full_name, $phone_number, $email, $gender, $image_name , $user_id);
                    

                    $update_profile_query_execute = mysqli_stmt_execute($update_profile_query_stmt);

                    if ($update_profile_query_execute)
                    {
                        $response['message'] = "User profile updated successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "Error while Updating user data";
                        $response['status'] = 201;
                    }
            }
            else
            {
                $response['message'] = "full name, email, phone number, gender and userId are required.";
                $response['status'] = 201;
            }
        }
        else
        {
            $response['message'] = "Invalid tag";
            $response['status'] = 201;
        }
    }
    else
    {
        $response['message'] = 'Only post method allow';
        $response['status'] = 201;
    }

    echo json_encode($response);
?>