<?php
include 'functions.php';
header('Access-Control-Allow-Origin: *'); 
$headerKey = "Ocp-Apim-Subscription-Key";
$headerValue = "68e9f227efcc4ead96154d49a154fbea";

    if (isset($_GET["ip"]) && !empty($_GET['ip'])) {
        $ip = $_GET["ip"];
    }else{
        $ip =
            getenv("HTTP_CLIENT_IP") ?:
            getenv("HTTP_X_FORWARDED_FOR") ?:
            getenv("HTTP_X_FORWARDED") ?:
            getenv("HTTP_FORWARDED_FOR") ?:
            getenv("HTTP_FORWARDED") ?:
            getenv("REMOTE_ADDR"); // Add customer IP Address Here
        $ip = $ip == "127.0.0.1" ? "206.84.236.171" : $ip;
    }

    if (isset($_GET["pincode"]) && !empty($_GET['pincode'])) {
        // google map geocode api url
        $pincode = urlencode($_GET['pincode']);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyDG3bVbiBF5hjbbATeDWCuHgQBcqIZM6lk&components=postal_code:{$pincode}";
        // get the json response from url
        $resp_json = file_get_contents($url);
        
        // decode the json response
        $resp = json_decode($resp_json, true);

        // response status will be 'OK', if able to geocode given address
        if($resp['status']=='OK'){
                $latitude1 = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : '';
                $longitude1 = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : '';   
        }
    }
    if (isset($_GET['latitude']) && isset($_GET['longitude'])) {
        $latitude1 = $_GET["latitude"];
        $longitude1 = $_GET["longitude"];
    }else{
        $latLongData = getDefaultLatLong($ip);
        $latitude1 = $latLongData->latitude;
        $longitude1 = $latLongData->longitude;
    }
    if (isset($_GET['location_id']) && !empty($_GET['location_id']) && isset($_GET['sku']) && !empty($_GET['sku'])) {
        $storeDetail =  getStoreDetail($_GET['location_id'], $_GET['sku']);
        echo json_encode($storeDetail, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }else if (isset($_GET['location_id']) && !empty($_GET['location_id'])) {
        $storeDetail =  getStoreDetail($_GET['location_id']);
        echo json_encode($storeDetail, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }else{
    $inValidStores = $stores = []; // empty for all

    $isActive = isset($_GET["is_active"]) ? mysqli_real_escape_string($conn, $_GET["is_active"]) : 1;
    $isVisible = isset($_GET["is_visible"]) ? mysqli_real_escape_string($conn, $_GET["is_visible"]) : 1;
    $no_of_records_per_page = isset($_GET["page_size"]) ? mysqli_real_escape_string($conn, $_GET["page_size"]) : 5;
    $pageno = isset($_GET["page_number"]) ? mysqli_real_escape_string($conn, $_GET["page_number"]) : 1;
    $unit = isset($_GET["unit"]) ? mysqli_real_escape_string($conn, $_GET["unit"]) : "km";
    $range = isset($_GET["range"]) ? mysqli_real_escape_string($conn, $_GET["range"]) : "15000";

    if ($unit == 'km') {
        $unitMultiplier = 6371;
    }else{
        $unitMultiplier = 3956; //For miles - mi
    }
    $offset = ($pageno - 1) * $no_of_records_per_page;
    $storeDetail = $storeDetails = [];

try {

    // $sql = "SELECT * FROM  stores WHERE is_visible = ".$isVisible."";
    // $conn->set_charset("utf8");
    // $result = $conn->query($sql);
    // $total_rows = $result->num_rows;
    // $total_pages = ceil($total_rows / $no_of_records_per_page);
    // foreach (mysqli_fetch_all($result) as $id => $result1) {
    //     $storeIds[] = $result1[1];
    // }
    // $ids = implode(",", $storeIds);

    $sku = isset($_GET["sku"]) ? mysqli_real_escape_string($conn, $_GET["sku"]) : "all";
    // using joing query
    $sql1 = "SELECT DISTINCT stores.store_name, stores.store_id, stores.address1, stores.address2, stores.address3, stores.city, stores.state, stores.zipcode, stores.countrycode, stores.latitude, stores.longitude, stores.phone_number, ($unitMultiplier * 2 * ASIN(SQRT( POWER(SIN(( $latitude1 - latitude) *  pi()/180 / 2), 2) +COS( $latitude1 * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( $longitude1 - longitude) * pi()/180 / 2), 2) ))) as distance FROM stores JOIN items ON stores.store_id = items.location_id having distance <= ".$range." LIMIT $offset, $no_of_records_per_page";
    //AND stores.store_id IN (".$ids.") //remove add near having based on requiremennt

    $conn->set_charset("utf8");
    $result1 = $conn->query($sql1);
    if (isset($_GET['range'])) {
        $total_rows = $result1->num_rows; //total store count
    }else{
        $total_rows = 80; //$result1->num_rows; //total store count
    }
    $total_pages = ceil($total_rows / $no_of_records_per_page);
    if ($result1->num_rows > 0) {
        while ($row = $result1->fetch_assoc()) {
            // Store's Latitude & Longitude
            $latitude2 = $row["latitude"];
            $longitude2 = $row["longitude"];
            $qty = 0;
            if (isset($_GET['sku'])) {
                $qty = getQtyStatusByLocationId($_GET['sku'],$row["store_id"]);
            }
              if ($qty > 0) {
                $quantity_status = "In Stock";
              }else{
                $quantity_status = "Out of Stock";
              }
            $stores[] = [
                "location_id" => $row["store_id"],
                "store_name" => htmlspecialchars($row["store_name"]), // error field
                "quantity" => $qty,
                "quantity_status" => $quantity_status,
                "street1" => htmlspecialchars($row["address1"]), // error field
                "street2" => htmlspecialchars($row["address2"]),
                "street3" => htmlspecialchars($row["address3"]),
                "city" => htmlspecialchars($row["city"]), // error field
                "state" => htmlspecialchars($row["state"]),
                "zip_code" => htmlspecialchars($row["zipcode"]),
                "country_code" => htmlspecialchars($row["countrycode"]),
                "latitude" => $row["latitude"],
                "longitude" => $row["longitude"],
                "phone_number" => $row["phone_number"],
                "distance_diff" =>
                        distance(
                            $latitude1, 
                            $longitude1, 
                            $latitude2, 
                            $longitude2, 
                            $unit, 
                            2
                        ).$unit, // you change unit here
                "store_hours" => getStoreTiming($row["store_id"]),
            ];
        }

        $storeList = [
            "status" => "true",
            "user_ip" => $ip,
            "store_count" => (string) $total_rows,
            "total_pages" => (string) $total_pages,
            "current_page" => $pageno,
            "page_size" => (string) $no_of_records_per_page,
            "stores" => $stores,
            "current_storecount" => count($stores),
        ];
        echo json_encode($storeList);
    } else {
        $storeList = [
            "status" => "false",
            "message" => empty($message) ? "try correct page no. OR there is no stores" : $message,
            "user_ip" => $ip,
            "stores" => $stores,
        ];
        echo json_encode($storeList);
    }



} catch (Exception $e) {
    $storeList = [
        "status" => "false",
        "message" => $e->getMessage(),
        "user_ip" => $ip,
        "stores" => $stores,
    ];
    echo json_encode($storeList);
}
    }
