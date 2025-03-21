<?php

// sagar (restaurant rating)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'rating')
        {
            if (
                (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                && (isset($_POST['user_id']) && !empty($_POST['user_id']))
                && (isset($_POST['rating']) && !empty($_POST['rating']))
            )
            {
                $restaurant_id = $_POST['restaurant_id'];
                $user_id = $_POST['user_id'];
                $rating = $_POST['rating'];
                $rating_feedback = isset($_POST['rating_feedback']) ? trim($_POST['rating_feedback']) : '';
                $rating_image = isset($_FILES['image']) ? $_FILES['image'] : '';

                // validation
                // find user
                $check_user_query = "SELECT user_id FROM `user_master`
                                     WHERE user_id = ?";
                $check_user_query_stmt = mysqli_prepare($conn, $check_user_query);
                mysqli_stmt_bind_param($check_user_query_stmt, "i", $user_id);
                mysqli_stmt_execute($check_user_query_stmt);
                $check_user_query_result = mysqli_stmt_get_result($check_user_query_stmt);
                if (mysqli_num_rows($check_user_query_result) === 0)
                {
                    $response['message'] = "User not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();   
                }
                // find restaurant
                $check_restaurant_query = "SELECT restaurant_id FROM `restaurant_master`
                                           WHERE restaurant_id = ? AND restauarnt_approved_status = 1 AND restaurant_status = 1";
                $check_restaurant_query_stmt = mysqli_prepare($conn, $check_restaurant_query);
                mysqli_stmt_bind_param($check_restaurant_query_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($check_restaurant_query_stmt);
                $check_restaurant_query_result = mysqli_stmt_get_result($check_restaurant_query_stmt);
                if (mysqli_num_rows($check_restaurant_query_result) === 0)
                {
                    $response['message'] = "Restaurant not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();   
                }

                // rating validation
                if (!preg_match("/^[1-5]{1}$/", $rating)) {
                    $response['message'] = "Rating must contain only 1 number (between 1 to 5)";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // rating feedback validation



                // file upload
                $image_name = "";
                if (!empty($_FILES['image']))
                {
                    $target_folder = __DIR__ . "/../../uploads/";

                    // if (!file_exists($target_folder)) {
                    //     mkdir($target_folder, 0777, true);  
                    // }

                    $random_number = rand(1000, 9999);
                    $image_name = $random_number.'_'.basename($rating_image['name']);
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
                    if ($rating_image['size'] > $maxFileSize)
                    {
                        $response['message'] = "Image size is large only 5mb allow";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    // move file to uploads/rating_images folder
                    // echo "Target file: " . $target_file;  

                    if (!move_uploaded_file($rating_image['tmp_name'], $target_file))
                    {
                        $response['message'] = "Error while uploading image";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                }
                // check for user alrady given rating for that restaurant
                $check_ratig = "SELECT rating_id FROM `ratings`
                WHERE restaurant_id = ? AND user_id = ?";
                $check_ratig_stmt = mysqli_prepare($conn, $check_ratig);
                if (!$check_ratig_stmt)
                {
                    $response['message'] = "Error while preparing check rating query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                mysqli_stmt_bind_param($check_ratig_stmt, "ii", $restaurant_id, $user_id);
                if (!mysqli_stmt_execute($check_ratig_stmt))
                {
                    $response['message'] = "Error while executing check rating query";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
                $check_ratig_result = mysqli_stmt_get_result($check_ratig_stmt);
                if (mysqli_num_rows($check_ratig_result) > 0)
                {
                    $update_rating_query = "UPDATE `ratings`
                                            SET rating = ?,
                                            review = ?,
                                            image = ?,
                                            createdAt = NOW()
                                            WHERE restaurant_id = ? AND user_id = ?";
                    $update_rating_query_stmt = mysqli_prepare($conn, $update_rating_query);
                    if (!$update_rating_query_stmt)
                    {
                        $response['message'] = "Error while preparing update rating query";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();
                    }
                    mysqli_stmt_bind_param($update_rating_query_stmt, "issii", $rating, $rating_feedback, $image_name, $restaurant_id, $user_id);
                    if (mysqli_stmt_execute($update_rating_query_stmt))
                    {
                        $response['message'] = "Rating updated successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "Error while updating rating query";
                        $response['status'] = 201;
                    }
                }
                else
                {
                    // insert rating if user enter rating first time
                    $insert_rating_query = "INSERT INTO `ratings`(restaurant_id, user_id, rating, review, image)
                    VALUES(?, ?, ?, ?, ?)";

                    $insert_rating_query_stmt = mysqli_prepare($conn, $insert_rating_query);
                    if (!$insert_rating_query_stmt)
                    {
                        $response['message'] = "Error while preparing insert rating query";
                        $response['status'] = 201;
                        echo json_encode($response);
                        exit();   
                    }
                    mysqli_stmt_bind_param($insert_rating_query_stmt, "iiiss", $restaurant_id, $user_id, $rating, $rating_feedback, $image_name);

                    if (mysqli_stmt_execute($insert_rating_query_stmt))
                    {
                        $response['message'] = "Rating inserted successfully";
                        $response['status'] = 200;
                    }
                    else
                    {
                        $response['message'] = "Error while inserting raing";
                        $response['status'] = 201;
                    }
                }
            }
            else
            {
                $response['message'] = "Restaurant id, user id, rating fields are required";
                $response['status'] = 201;
            }
        }
        else
        {
            $response['message'] = "Invalid tag or tag missing";
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