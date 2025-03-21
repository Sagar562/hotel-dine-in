<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'search')
        {
            if (isset($_POST['search_term']) && !empty($_POST['search_term'])) {
                $search_term = trim($_POST['search_term']);
    
                
                // Prepare the SQL query to search by restaurant name or area
                $search_query = "SELECT DISTINCT rest.restaurant_id, rest.restaurant_name, rest_img.restaurant_image_url FROM `restaurant_master` AS rest
                                LEFT JOIN `restaurant_images` AS rest_img
                                ON rest_img.restaurant_id = rest.restaurant_id
                                INNER JOIN `restaurant_address` AS rest_address
                                ON rest_address.restaurant_id = rest.restaurant_id
                                WHERE rest.restaurant_name LIKE ? OR rest_address.restaurant_street like ?";
                
                // Prepare the SQL statement
                $search_stmt = mysqli_prepare($conn, $search_query);
                if (!$search_stmt) 
                {
                    $response['message'] = "Error while preparing the query";
                    $response['status'] = 201; 
                    echo json_encode($response);
                    exit();
                }
                $search_term_like = "%" . $search_term . "%"; // Add wildcard for partial matching
                mysqli_stmt_bind_param($search_stmt, "ss", $search_term_like, $search_term_like);
    
                // Execute the query
                $execution_status = mysqli_stmt_execute($search_stmt);
                if (!$execution_status) {
                    $response['message'] = "Error executing the query";
                    $response['status'] = 201; 
                    echo json_encode($response);
                    exit();
                }
    
                // Get the result
                $result = mysqli_stmt_get_result($search_stmt);
    
                // Check if any restaurants match the search criteria
                if (mysqli_num_rows($result) > 0) 
                {
                    $restaurants = [];
                    while ($row = mysqli_fetch_assoc($result)) 
                    {
                        $restaurants[] = $row;
                    }
    
                    $response['restaurants'] = $restaurants;
                    $response['message'] = "Search results found";
                    $response['status'] = 200; 
                }
                else
                {
                    $response['message'] = "No results found";
                    $response['status'] = 201;
                }
            }
            else 
            {
                $response['message'] = "Search term is required";
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
        $response['message'] = "Only POST method is allowed";
        $response['status'] = 201; 
    }

echo json_encode($response);
?>
