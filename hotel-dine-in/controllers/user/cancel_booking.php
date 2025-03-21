<?php
// sagar (cancel booking with blocking)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'cancel_reservation')
        {
            if (
                (isset($_POST['user_id']) && !empty($_POST['user_id']))
                && (isset($_POST['reservation_id']) && !empty($_POST['reservation_id']))
                && (isset($_POST['cancel_reason']) && !empty($_POST['cancel_reason']))
                )
            {
                $user_id = $_POST['user_id'];
                $reservation_id = $_POST['reservation_id'];
                $cancel_reason = trim($_POST['cancel_reason']);
                $cancel_by = 0;
    
                // check for userId
                $check_user = "SELECT user_id FROM `user_master`
                               WHERE user_id = ?";
                $check_user_stmt = mysqli_prepare($conn, $check_user);
                mysqli_stmt_bind_param($check_user_stmt, "i", $user_id);
                mysqli_stmt_execute($check_user_stmt);
                $check_user_result = mysqli_stmt_get_result($check_user_stmt);
                
                if (mysqli_num_rows($check_user_result) === 0)
                {
                    $response['message'] = "User not found with given userId";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check reservation status
                $check_reservation_status = "SELECT user_id, reservation_status FROM `reservations`
                                            WHERE reservation_id = ? AND user_id = ?";
                $check_reservation_status_stmt = mysqli_prepare($conn, $check_reservation_status);
                mysqli_stmt_bind_param($check_reservation_status_stmt, "ii", $reservation_id, $user_id);
                mysqli_stmt_execute($check_reservation_status_stmt);
                $check_reservation_status_result = mysqli_stmt_get_result($check_reservation_status_stmt);
                
                if (mysqli_num_rows($check_reservation_status_result) > 0)
                {

                    $reservation_status = mysqli_fetch_assoc($check_reservation_status_result)['reservation_status'];
                    // 0 means cancel and 1 for confirm
                    if ($reservation_status == 0) {
                        $response['message'] = "This reservation has already been cancelled";
                        $response['status'] = 201; 
                        echo json_encode($response);
                        exit();
                    }
          
                    // cancel reservation
                    $cancel_reservation = "UPDATE `reservations`
                                           SET 
                                           reservation_status = 0,
                                           cancel_reason = ?,
                                           cancel_by = ?
                                           WHERE reservation_id = ?
                                           AND reservation_status = 1
                                           AND reservation_date > CURDATE()";

                    $cancel_reservation_stmt = mysqli_prepare($conn, $cancel_reservation);
                    mysqli_stmt_bind_param($cancel_reservation_stmt, "sii", $cancel_reason, $cancel_by, $reservation_id);
                    
                    if (mysqli_stmt_execute($cancel_reservation_stmt))
                    {
                        // after cancel the reservation check for user cancel order for past 30 days
                        $check_cancellation = "SELECT COUNT(*) AS cancellation_count FROM `reservations`
                                               WHERE user_id = ? AND reservation_status = 0 AND reservation_date > NOW() - INTERVAL 30 DAY";
                        $check_cancellation_stmt = mysqli_prepare($conn, $check_cancellation);
                        mysqli_stmt_bind_param($check_cancellation_stmt, "i", $user_id);
                        mysqli_stmt_execute($check_cancellation_stmt);
                        $check_cancellation_result = mysqli_stmt_get_result($check_cancellation_stmt);
                       
                        $cancellation_count = mysqli_fetch_assoc($check_cancellation_result)['cancellation_count'];
                        
                        $max_cancellation = 10;

                        if ($cancellation_count > $max_cancellation)
                        {   
                            // block user if cancellation count in last 30 days exide 10 times
                            $block_user = "UPDATE `user_master`
                                           SET is_block = 1,
                                           is_blockedAt = NOW(),
                                           block_until = NOW() + INTERVAL 10 DAY
                                           WHERE user_id = ?";
                            $block_user_stmt = mysqli_prepare($conn, $block_user);
                            mysqli_stmt_bind_param($block_user_stmt, "i", $user_id);
                            
                            if (mysqli_stmt_execute($block_user_stmt))
                            {
                                $response['message'] = "You have been blocked due to excessive cancellations";
                                $response['status'] = 200;
                            }
                            else
                            {
                                $response['message'] = "Failed to execute block user query" . mysqli_error($conn);
                                $response['status'] = 201;
                            }
                        }
                        else
                        {
                            $response['message'] = "Reservation cancelled successfully";
                            $response['status'] = 200;
                        }
                    }
                    else
                    {
                        $response['message'] = "Failed to cancel reservation";
                        $response['status'] = 201;
                    }
                }
                else
                {
                    $response['message'] = "No reservation found with given reservationId and userId";
                    $response['status'] = 201;
                }
            }   
            else
            {
                $response['message'] = "userId, reservationId and cancellation reason are required";
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