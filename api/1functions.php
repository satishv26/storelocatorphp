<?php 

    if(($_SERVER['REMOTE_ADDR'] == '127.0.0.1') || ($_SERVER['REMOTE_ADDR'] == 'localhost') ){
        $servername = "127.0.0.1";
        $username = "allure";
        $password = "Magento@1";
        $dbname = "Eclipse";
    }else{
        $servername = "127.0.0.1";
        $username = "root";
        $password = "Not2BuseD";
        $dbname = "eclipseapi";
    }
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


function getStoreDetail($storeId)
{
    global $conn;
    $storeDetail = [];
        $sql = "SELECT * FROM  stores WHERE store_id = ".$storeId;

        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if (count($row) == 0) {
            $status = 'false';
        }else{
            $status = "true";
        }
            $storeDetail = [
                "status" => $status,
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
                "store_hours" => getStoreTiming($row["store_id"]),
            ];
    return $storeDetail;
}

function getStoreTiming($store_id)
{
    $store_timing = [];
    global $conn;
    $sql = "SELECT * FROM store_hours WHERE store_id = ".$store_id."";
    $conn->set_charset("utf8");
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $store_timing[] = [
                "working_day" => $row["working_day"],
                "working_from" => $row["working_from"],
                "working_to" => $row["working_to"],
            ];
        }
    } else {
        return $store_timing;
    }
    return $store_timing;
}

function distance(
    $point1_lat,
    $point1_long,
    $point2_lat,
    $point2_long,
    $unit = "km",
    $decimals = 2
) {
    // Calculate the distance in degrees
    $degrees = rad2deg(
        acos(
            sin(deg2rad($point1_lat)) * sin(deg2rad($point2_lat)) +
                cos(deg2rad($point1_lat)) *
                    cos(deg2rad($point2_lat)) *
                    cos(deg2rad($point1_long - $point2_long))
        )
    );

    // Convert the distance in degrees to the chosen unit (kilometres, miles or nautical miles)
    switch ($unit) {
        case "km":
            $distance = $degrees * 111.13384; // 1 degree = 111.13384 km, based on the average diameter of the Earth (12,735 km)
            break;
        case "mi":
            $distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
            break;
        case "nmi":
            $distance = $degrees * 59.97662; // 1 degree = 59.97662 nautic miles, based on the average diameter of the Earth (6,876.3 nautical miles)
    }
    return round($distance, $decimals);
}

function getDefaultLatLong($ip){
    $ch = curl_init();
    // Calling API for customer side latitude longitude
    curl_setopt(
        $ch,
        CURLOPT_URL,
        "https://api.ip2location.io/?" .
            http_build_query([
                "ip" => $ip, // Add customer IP Address Here mumbai
                "key" => "BEC4081A2B6E2EAB9F73F35A505B280B", // Need to purchase after 3000 request
                "format" => "json",
            ])
    );

    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $ipData = json_decode($response);

    return $ipData;
}



?>