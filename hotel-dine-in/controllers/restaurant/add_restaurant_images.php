<?php
// sagar (restaurant image upload)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../lib/dnConnect.php';
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hotel_dine_in']) && $_POST['hotel_dine_in'] === 'upload_restaurant_images') {
        if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
            $restaurant_id = $_POST['restaurant_id'];

            // Validate restaurant ID if needed
            // e.g., Check if the restaurant exists in the database
            $check_restaurant_query = "SELECT restaurant_id FROM `restaurant_master` WHERE restaurant_id = ?";
            $check_restaurant_query_stmt = mysqli_prepare($conn, $check_restaurant_query);
            mysqli_stmt_bind_param($check_restaurant_query_stmt, "i", $restaurant_id);
            mysqli_stmt_execute($check_restaurant_query_stmt);
            $check_restaurant_query_result = mysqli_stmt_get_result($check_restaurant_query_stmt);

            if (mysqli_num_rows($check_restaurant_query_result) === 0) {
                $response['message'] = "Restaurant not found";
                $response['status'] = 201;
                echo json_encode($response);
                exit();
            }

            // Loop through the uploaded files
            $uploaded_images = [];
            $target_folder = __DIR__ . "/../../uploads/restaurant_images/";
            if (!file_exists($target_folder)) {
                mkdir($target_folder, 0777, true);
            }

            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                $image_name = "";
                $image_type = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                $image_tmp_name = $_FILES['images']['tmp_name'][$i];
                $image_size = $_FILES['images']['size'][$i];

                // Validate image format
                $allowed_extension = array("jpg", "jpeg", "png");
                if (!in_array($image_type, $allowed_extension)) {
                    $response['message'] = "Invalid file extension for image " . ($i + 1);
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // Validate image size (max 5MB)
                $maxFileSize = 5 * 1024 * 1024;
                if ($image_size > $maxFileSize) {
                    $response['message'] = "Image size for image " . ($i + 1) . " is large. Only 5MB allowed";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // Generate unique name for the image
                $random_number = rand(1000, 9999);
                $image_name = $random_number . '_' . basename($_FILES['images']['name'][$i]);
                $target_file = $target_folder . $image_name;

                // Move the file to the uploads directory
                if (!move_uploaded_file($image_tmp_name, $target_file)) {
                    $response['message'] = "Error while uploading image " . ($i + 1);
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }

                // Add image name to array
                $uploaded_images[] = $image_name;
            }

            // Insert image details into database (if needed)
            foreach ($uploaded_images as $image) {
                $insert_image_query = "INSERT INTO `restaurant_images` (restaurant_id, restaurant_image_url) VALUES (?, ?)";
                $insert_image_stmt = mysqli_prepare($conn, $insert_image_query);
                mysqli_stmt_bind_param($insert_image_stmt, "is", $restaurant_id, $image);

                if (!mysqli_stmt_execute($insert_image_stmt)) {
                    $response['message'] = "Error while inserting image into database";
                    $response['status'] = 201;
                    echo json_encode($response);
                    exit();
                }
            }

            $response['message'] = "Images uploaded successfully";
            $response['status'] = 200;
            echo json_encode($response);
        } else {
            $response['message'] = "No images selected";
            $response['status'] = 201;
            echo json_encode($response);
        }
    } else {
        $response['message'] = "Invalid tag";
        $response['status'] = 201;
        echo json_encode($response);
    }
} else {
    $response['message'] = "Only POST method allowed";
    $response['status'] = 201;
    echo json_encode($response);
}
?>
