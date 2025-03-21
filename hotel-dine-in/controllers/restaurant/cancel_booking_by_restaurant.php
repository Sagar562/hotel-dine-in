<?php

// sagar (cancel booking by restaurant)

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
                (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id']))
                && (isset($_POST['reservation_id']) && !empty($_POST['reservation_id']))
                && (isset($_POST['cancel_reason']) && !empty($_POST['cancel_reason']))
                )
            {
                $restaurant_id = trim($_POST['restaurant_id']);
                $reservation_id = trim($_POST['reservation_id']);
                $cancel_reason = trim($_POST['cancel_reason']);
                $cancel_by = 1;

                // check for restaurant is exist or not
                $check_restaurant = "SELECT restaurant_id FROM `restaurant_master`
                                     WHERE restaurant_id = ?";
                $check_restaurant_stmt = mysqli_prepare($conn, $check_restaurant);
                mysqli_stmt_bind_param($check_restaurant_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($check_restaurant_stmt);
                $check_restaurant_result = mysqli_stmt_get_result($check_restaurant_stmt);

                if (mysqli_num_rows($check_restaurant_result) === 0)
                {
                    $response['message'] = "Restaurant not found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                    // check reservation status
                    $check_reservation_status = "SELECT restaurant_id, reservation_status FROM `reservations`
                                                WHERE reservation_id = ? AND restaurant_id = ?";
                    $check_reservation_status_stmt = mysqli_prepare($conn, $check_reservation_status);
                    mysqli_stmt_bind_param($check_reservation_status_stmt, "ii", $reservation_id, $restaurant_id);
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

                        $cancel_reservation = "UPDATE `reservations`
                                                SET reservation_status = 0,
                                                    cancel_reason = ?,
                                                    cancel_by = ?
                                                WHERE reservation_id = ?
                                                AND reservation_status = 1
                                                AND reservation_date > CURDATE()";
                                            
                        $cancel_reservation_stmt = mysqli_prepare($conn, $cancel_reservation);
                        mysqli_stmt_bind_param($cancel_reservation_stmt, "sii", $cancel_reason, $cancel_by, $reservation_id);

                        if (mysqli_stmt_execute($cancel_reservation_stmt))
                        {
                            $response['message'] = "Reservation cancelled successfully";
                            $response['status'] = 200;
                        }
                        else
                        {
                            $response['message'] = "Failed to cancel reservation";
                            $response['status'] = 201;
                        }

                    }
                    else
                    {
                        $response['message'] = "No reservation found with given reservationId and restaurantId";
                        $response['status'] = 201;   
                    }
            }
            else
            {
                // cancel reason is comming from fix text on ui
                $response['message'] = "restaurant id, reservation id and cancel resaon required";
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