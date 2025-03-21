<?php

// sagar (login)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'login')
        {
            
            if (
                (isset($_POST['email']) && !empty($_POST['email']))
                && (isset($_POST['password']) && !empty($_POST['password']))
               )
               {
                    $email = trim($_POST['email']);
                    $password = trim($_POST['password']);

                    
                    // email validation
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $response['message'] = "Invalid email formate";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }

                    // password validation
                    if (!preg_match("/^[a-zA-Z0-9#@!$%^&*]{8,20}$/", $password)) {
                        $response['message'] = "Password must be between 8 and 20 characters long, contain only alphanumeric characters or allowed symbols (#, @, !, $, %, ^, &, *, etc.), and no spaces.";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    
                    $get_user_data =   "SELECT user_id, user_password FROM `user_master`
                                        WHERE user_email = ?";
                    $get_user_data_stmt = mysqli_prepare($conn, $get_user_data);
                    mysqli_stmt_bind_param($get_user_data_stmt, "s", $email);
                    
                    mysqli_stmt_execute($get_user_data_stmt);
                    
                    $get_user_result = mysqli_stmt_get_result($get_user_data_stmt);
                    
                    if (mysqli_num_rows($get_user_result) > 0)
                    {
                        $userData =  mysqli_fetch_assoc($get_user_result);

                        $stored_password = $userData['user_password'];
                        $user_id = $userData['user_id'];


                        $password = trim($password);
                        $stored_password = trim($stored_password);

                        $check = password_verify($password, $stored_password);
                        

                        if ($check)
                        {   
                            $response['message'] = "Login successfully";
                            $response['user_id'] = $user_id;
                            $response['status'] = 200;
                            
                        }
                        else
                        {
                            // echo "Store " . $stored_password;
                            // echo "pass" . $check;
                            $response['message'] = "Invalid Password";
                            $response['status'] = 201;
                        }
                    }
                    else
                    {
                        $response['message'] = "User not found";
                        $response['status'] = 201;
                    }
                    mysqli_stmt_close($get_user_data_stmt);
               }
               else
               {
                $response['message'] = 'Email and password required';
                $response['status'] = 201;
               }
        }
        else
        {
            $response['message'] = 'Invalid tag or tag not found';
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