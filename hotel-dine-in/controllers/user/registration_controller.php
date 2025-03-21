<?php

// sagar (User registration)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'registration') {
        if (
            (isset($_POST['full_name']) && !empty($_POST['full_name']))
            && (isset($_POST['gender']) && !empty($_POST['gender']))
            && (isset($_POST['password']) && !empty($_POST['password']))
        ) {
            
            if (!isset($_POST['email']) || empty($_POST['email']))
            {
                $response['message'] = "Email field required";
                $response['status'] = 201;
                echo json_encode($response);
                exit();
            }
            if (!isset($_POST['phone_number']) || empty($_POST['phone_number']))    
            {
                $response['message'] = "Phone number field required";
                $response['status'] = 201;
                echo json_encode($response);
                exit();
            }


            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $phone_number = trim($_POST['phone_number']);
            $password = trim($_POST['password']);
            $gender = trim($_POST['gender']);
            $user_image = (isset($_POST['user_image']) && !empty($_POST['user_image'])) ? $_POST['user_image'] : NULL;

            // full name
            if ((!preg_match("/^[a-zA-Z]+(\s[a-zA-Z]+)*$/", $full_name)) || ((strlen($full_name) < 3) || (strlen($full_name) > 30))) {
                $response['message'] = "Full name must contain only letters and at-least 3 character and atmost 30 characters.";
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

            // password
            if (!preg_match("/^[a-zA-Z0-9#@!$%^&*]{8,30}$/", $password)) {
                $response['message'] = "Password must be between 8 and 30 characters long, contain only alphanumeric characters or allowed symbols (#, @, !, $, %, ^, &, *, etc.), and no spaces.";
                $response['status'] = 201;
                echo json_encode($response);
                exit();
            }

            // hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // check for email is exist in other data or not
            $check_duplicate_email_query = "SELECT user_id FROM `user_master` 
                                            WHERE user_email = ?";

            $check_duplicate_email_query_stmt = mysqli_prepare($conn, $check_duplicate_email_query);
            mysqli_stmt_bind_param($check_duplicate_email_query_stmt, "s", $email);
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
                                                   WHERE user_phone_number = ?";

            $check_duplicate_phone_number_query_stmt = mysqli_prepare($conn, $check_duplicate_phone_number_query);

            mysqli_stmt_bind_param($check_duplicate_phone_number_query_stmt, "i", $phone_number);

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

            // if user not in database then enter data into database
            $user_registration = "INSERT INTO `user_master`(user_full_name, user_email, user_phone_number, user_password, user_gender, user_image)
            VALUES (?, ?, ?, ?, ?, ?)";

            $user_registration_stmt = mysqli_prepare($conn, $user_registration);

            mysqli_stmt_bind_param($user_registration_stmt, "ssisss", $full_name, $email, $phone_number, $hashed_password, $gender, $user_image);

            $user_registration_result = mysqli_stmt_execute($user_registration_stmt);

            if ($user_registration_result) {
                $response['message'] = "User registered successfully";
                $response['status'] = 200;
            } else {
                $response['message'] = "Error while register user" . mysqli_error($conn);
                $response['status'] = 201;
            }
        } else {
            $response['message'] = "Full name, password, gender required";
            $response['status'] = 201;
        }
    } else {
        $response['mesaage'] = 'Invalid Tag';
        $response['status'] = 201;
    }
} else {
    $response['message'] = 'Only Post method allow';
    $response['status'] = 201;
}

echo json_encode($response);
// mysqli_stmt_close($stmt);

?>