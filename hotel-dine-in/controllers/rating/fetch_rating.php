
<?php

// sagar (fetch restaurant rating)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'fetch_rating')
        {           
            // for rating filter
            if
                (
                isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id'])
                && isset($_POST['rating']) && !empty($_POST['rating'])                
                )
            {
                $restaurant_id = $_POST['restaurant_id'];
                $rating = $_POST['rating'];

                // rating validation
                if (!preg_match("/^[1-5]{1}$/", $rating)) {
                    $response['message'] = "Rating must contain only 1 number (between 1 to 5)";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // check restaurant id is present in database or not
                $check_restaurant_query = "SELECT * FROM `restaurant_master` WHERE restaurant_id = ?";

                $check_restaurant_query_stmt = mysqli_prepare($conn, $check_restaurant_query);
                mysqli_stmt_bind_param($check_restaurant_query_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($check_restaurant_query_stmt);
                $check_restaurant_query_result = mysqli_stmt_get_result($check_restaurant_query_stmt);
                
                if (mysqli_num_rows($check_restaurant_query_result) == 0)
                {
                    $response['message'] = "No restaurant found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                $rating_filter = "SELECT u.user_full_name, r.rating, r.review, r.image FROM `ratings` AS r
                INNER JOIN `user_master` AS u
                ON r.user_id = u.user_id
                WHERE r.restaurant_id = ? AND r.is_hidden = ? AND r.rating = ?
                ORDER BY r.rating DESC";

                $rating_filter_stmt = mysqli_prepare($conn, $rating_filter);

                mysqli_stmt_bind_param($rating_filter_stmt, "iii", $restaurant_id, $rating_status, $rating);

                mysqli_stmt_execute($rating_filter_stmt);

                $rating_filter_result = mysqli_stmt_get_result($rating_filter_stmt);

                if (mysqli_num_rows($rating_filter_result) > 0)
                {
                    while ($rating_data = mysqli_fetch_assoc($rating_filter_result))
                    {
                        $users[] = [
                            "full_name" => $rating_data['user_full_name'],
                            "rating" => $rating_data['rating'],
                            "review" => $rating_data['review'],
                            "rating_image" => $rating_data['image']
                        ];   
                    }              
                    // $response['Rating for'] = $rating;
                    $response['users'] = $users;
                    $response['message'] = "Rating fetched successfull with filter";
                    $response['status'] = 200;   
                }
                else
                {
                    $response['message'] = "No rating found";
                    $response['status'] = 201;
                }
    
            }
           
            else if (
                isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id'])
                )
            {
                $restaurant_id = $_POST['restaurant_id'];

                // check restaurant id is present in database or not
                $check_restaurant_query = "SELECT * FROM `restaurant_master` WHERE restaurant_id = ?";

                $check_restaurant_query_stmt = mysqli_prepare($conn, $check_restaurant_query);
                mysqli_stmt_bind_param($check_restaurant_query_stmt, "i", $restaurant_id);
                mysqli_stmt_execute($check_restaurant_query_stmt);
                $check_restaurant_query_result = mysqli_stmt_get_result($check_restaurant_query_stmt);
                
                if (mysqli_num_rows($check_restaurant_query_result) == 0)
                {
                    $response['message'] = "No restaurant found";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // get all rating related to restaurant
                $all_rating_query = "SELECT u.user_full_name, r.rating, r.review, r.image FROM `ratings` AS r
                INNER JOIN `user_master` AS u
                ON r.user_id = u.user_id
                WHERE r.restaurant_id = ? AND r.is_hidden = 0 AND r.is_delete = 0
                ORDER BY r.rating DESC";
                        
                $all_rating_query_stmt = mysqli_prepare($conn, $all_rating_query);

                mysqli_stmt_bind_param($all_rating_query_stmt, "i", $restaurant_id);

                mysqli_stmt_execute($all_rating_query_stmt);

                $all_rating_query_result = mysqli_stmt_get_result($all_rating_query_stmt);

                if (mysqli_num_rows($all_rating_query_result) > 0)
                {
                    $users = [];
                    while ($rating_data = mysqli_fetch_assoc($all_rating_query_result))
                    {
                        $users[] = [
                            "full_name" => $rating_data['user_full_name'],
                            "rating" => $rating_data['rating'],
                            "review" => $rating_data['review'],
                            "rating_image" => $rating_data['image']
                        ];                    
                    }
                    $response['users'] = $users;
                    $response['message'] = "Rating fetched successfully";
                    $response['status'] = 200;
                }
                else
                {
                    $response['message'] = "No data found for given restaurant id";
                    $response['status'] = 201;
                }
            }
            
            else
            {
                $response['message'] = "Invalid restaurant";
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