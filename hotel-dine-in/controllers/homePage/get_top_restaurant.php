<?php

// top restaurant (sagar)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'top_restaurant')
        {
            // get restaurant details
            $top_restaurant_query = "SELECT rest.restaurant_id, rest.restaurant_name, AVG(r.rating) AS average_rating, rest_img.restaurant_image_url
                                    FROM restaurant_master AS rest
                                    INNER JOIN `restaurant_images` AS rest_img
                                    ON rest_img.restaurant_id = rest.restaurant_id
                                    INNER JOIN ratings AS r
                                    ON rest.restaurant_id = r.restaurant_id
                                    GROUP BY rest.restaurant_id
                                    -- HAVING AVG(r.rating) > 3
                                    ORDER BY average_rating DESC
                                    LIMIT 5";

            $top_restaurant_query_result = mysqli_query($conn, $top_restaurant_query);
            
            if ($top_restaurant_query_result)
            {
                if (mysqli_num_rows($top_restaurant_query_result) > 0)
                {
                    $top_restaurant = [];
                    while ($data = mysqli_fetch_assoc($top_restaurant_query_result))
                    {
                        $average_rating = number_format((float)$data['average_rating'], 1, '.', '');

                            $top_restaurant[] = [
                                "restaurant_id" => $data['restaurant_id'],
                                "restaurant_name" => $data['restaurant_name'],
                                // "restaurant foode type" => "Veg",
                                "average_rating" => $average_rating,
                                "restaurant_image" => $data['restaurant_image_url']
                            ];
                    }              
                    $response['restaurant'] = $top_restaurant;
                    $response['message'] = "Top restaurant data fetched successfully";
                    $response['status'] = 200;        
                }
                else
                {
                    $response['message'] = "No data found";
                    $response['status'] = 201;
                }
            }
            else
            {
                $response['message'] = "Erro while executing query";
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