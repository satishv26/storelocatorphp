<?php
include 'functions.php';
header('Access-Control-Allow-Origin: *'); 

try {

    if (isset($_GET['sku']) && !empty($_GET['sku'])) {
        $sku = "where sku = ".$_GET['sku']."";
    }else{
        $sku = '';
    }
    $sql = "SELECT * FROM swatch $sku";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc() ) {
        $rowdata[] =  [
                        // "id" => $row['id'],
                        "sku" => $row['sku'],
                        // "color_name" => $row['color_name'],
                        // "color_value" => $row['color_value'],
                        $row['color_name'] => "https://" . $_SERVER['SERVER_NAME'].'/images/swatches/'.$row['color_value']
                    ];
    }
    $storeList = [
        "status" => "true",
        "colors" => isset($rowdata) ? $rowdata : 'Data not exists.'
    ];
    echo json_encode($storeList, JSON_PARTIAL_OUTPUT_ON_ERROR);

} catch (Exception $e) {
    $storeList = [
        "status" => "false",
        "message" => $e->getMessage()
    ];
    // echo json_encode($storeList, JSON_PARTIAL_OUTPUT_ON_ERROR);

    echo json_encode($storeList);
}
