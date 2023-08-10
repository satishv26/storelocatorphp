<?php
include 'functions.php';
header('Access-Control-Allow-Origin: *'); 

try {
    if (isset($_GET['sku']) && !empty($_GET['sku'])) {
    $sku = isset($_GET["sku"]) ? mysqli_real_escape_string($conn, $_GET["sku"]) : 1;
            $sql = "SELECT * FROM swatch WHERE sku = ".$sku."";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc() ) {
                $rowdata[] =  [
                                "id" => $row['id'],
                                "sku" => $row['sku'],
                                "color_name" => $row['color_name'],
                                "color_value" => $row['color_value'],
                            ];
            }
            $storeList = [
                "status" => "true",
                "colors" => isset($rowdata) ? $rowdata : 'Data not exists.'
            ];
            echo json_encode($storeList, JSON_PARTIAL_OUTPUT_ON_ERROR);
    } else {
            $storeList = [
                "status" => "false",
                "message" => empty($message) ? "try correct page no. OR there is no stores" : $message
            ];
            echo json_encode($storeList);
    }

} catch (Exception $e) {
    $storeList = [
        "status" => "false",
        "message" => $e->getMessage()
    ];
    // echo json_encode($storeList, JSON_PARTIAL_OUTPUT_ON_ERROR);

    echo json_encode($storeList);
}
