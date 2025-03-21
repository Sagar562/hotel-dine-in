<?php

// cuisines (sagar)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'cuisines')
        {
            // get all cuisines
            $get_cuisines_query = "SELECT cuisine_id, cuisine_name, cuisine_image FROM `cuisines_master`
                                   WHERE cuisine_status = 1";
            $get_cuisines_query_result = mysqli_query($conn, $get_cuisines_query);

            if (!$get_cuisines_query_result)
            {
                $response['message'] = "error while executing query" . mysqli_error($conn);
                $response['status'] = 201;
                echo json_encode($response);
                exit();
            }

            if (mysqli_num_rows($get_cuisines_query_result) > 0)
            {
                $cuisines = [];
                while ($data = mysqli_fetch_assoc($get_cuisines_query_result))
                {
                    $cuisines[] = [
                        "cuisine_id" => $data['cuisine_id'],
                        "cuisine_name" => $data['cuisine_name'],
                        "cuisine_image" => $data['cuisine_image']
                    ];
                }
                $response['cuisines'] = $cuisines;
                $response['message'] = "All food types etched successfully";
                $response['status'] = 200;
            }
            else
            {
                $response['message'] = "No food type get";
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
        $response['message'] = "Only Post method allow";
        $response['status'] = 201;
    }
    
    echo json_encode($response);

?>