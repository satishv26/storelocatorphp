<?php
include 'functions.php';
header('Access-Control-Allow-Origin: *'); 

try {
    $storeList = $city = $state = array();
    $latLongData = getDefaultLatLong($ip);
    $latitude1 = $latLongData->latitude;
    $longitude1 = $latLongData->longitude;
    $unit = isset($_GET["unit"]) ? mysqli_real_escape_string($conn, $_GET["unit"]) : "km";
    $range = isset($_GET["range"]) ? mysqli_real_escape_string($conn, $_GET["range"]) : "20000";
    $isActive = isset($_GET["is_active"]) ? mysqli_real_escape_string($conn, $_GET["is_active"]) : 1;
    $isVisible = isset($_GET["is_visible"]) ? mysqli_real_escape_string($conn, $_GET["is_visible"]) : 1;

    if ($unit == 'km') {
        $unitMultiplier = 6371;
    }else{
        $unitMultiplier = 3956; //For miles - mi
    }

    if (isset($_GET['province']) && !empty($_GET['province'])) {
        if ($_GET['province'] == 'all') {
            $sql = "SELECT DISTINCT state FROM stores";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc() ) {
                $state[] =  $row["state"];
            }
            $storeList = [
                "status" => "true",
                "province" => $state
            ];
            echo json_encode($storeList, JSON_PARTIAL_OUTPUT_ON_ERROR);
        }else{
        $sql1 = "SELECT * , ($unitMultiplier * 2 * ASIN(SQRT( POWER(SIN(( $latitude1 - latitude) *  pi()/180 / 2), 2) +COS( $latitude1 * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( $longitude1 - longitude) * pi()/180 / 2), 2) ))) as distance from stores having  distance <= ".$range." AND is_active = ".$isActive." AND is_visible = ".$isVisible." AND state = ".$_GET['province']." order by distance";
        $conn->set_charset("utf8");
        $result1 = $conn->query($sql1);
        if ($result1->num_rows > 0) {
            while ($row = $result1->fetch_assoc() ) {
                $latitude2 = $row["latitude"];
                $longitude2 = $row["longitude"];
                $city[] = [
                    "location_id" => $row["store_id"],
                    "store_name" => htmlspecialchars($row["store_name"]), // error field
                    "quantity_status" => "In Stock",
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
                "count" => count($city),
                "stores" => $city
            ];
            echo json_encode($storeList, JSON_PARTIAL_OUTPUT_ON_ERROR);
        } else {
            $storeList = [
                "status" => "false",
                "message" => empty($message) ? "try correct page no. OR there is no stores" : $message
            ];
            echo json_encode($storeList);
        }
        }


    } else {
        $sql1 = "SELECT * , ($unitMultiplier * 2 * ASIN(SQRT( POWER(SIN(( $latitude1 - latitude) *  pi()/180 / 2), 2) +COS( $latitude1 * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( $longitude1 - longitude) * pi()/180 / 2), 2) ))) as distance from stores having  distance <= ".$range." AND is_active = ".$isActive." AND is_visible = ".$isVisible." order by distance";
        $conn->set_charset("utf8");
        $result1 = $conn->query($sql1);
        if ($result1->num_rows > 0) {

            while ($row = $result1->fetch_assoc() ) {
                $latitude2 = $row["latitude"];
                $longitude2 = $row["longitude"];
                $city[] = [
                    "location_id" => $row["store_id"],
                    "store_name" => htmlspecialchars($row["store_name"]), // error field
                    "quantity_status" => "In Stock",
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
                "count" => count($city),
                "stores" => $city
            ];
            echo json_encode($storeList, JSON_PARTIAL_OUTPUT_ON_ERROR);
        } else {
            $storeList = [
                "status" => "false",
                "message" => empty($message) ? "try correct page no. OR there is no stores" : $message
            ];
            echo json_encode($storeList);
        }
    }




} catch (Exception $e) {
    $storeList = [
        "status" => "false",
        "message" => $e->getMessage()
    ];
    echo json_encode($storeList);
}
