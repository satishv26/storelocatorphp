<?php
 header('Access-Control-Allow-Origin: *'); 
function geocode($address){
 
    // url encode the address
    $address = urlencode($address);
    
    // google map geocode api url
     $url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key=AIzaSyDG3bVbiBF5hjbbATeDWCuHgQBcqIZM6lk";

    // get the json response from url
    $resp_json = file_get_contents($url);
    
    // decode the json response
    $resp = json_decode($resp_json, true);
    // response status will be 'OK', if able to geocode given address
    if($resp['status']=='OK'){
        //define empty array
        $data_arr = array(); 
        // get the important data
        $data_arr['latitude'] = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : '';
        $data_arr['longitude'] = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : '';
        $data_arr['formatted_address'] = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : '';
        
        // verify if data is exist
        if(!empty($data_arr) && !empty($data_arr['latitude']) && !empty($data_arr['longitude'])){

            return $data_arr;
            
        }else{
            return false;
        }
        
    }else{
        return false;
    }
}
if (isset($_GET['address']) && !empty($_GET['address'])) {
    echo json_encode(geocode($_GET['address']));
}else{

     if(($_SERVER['REMOTE_ADDR'] == '127.0.0.1') || ($_SERVER['REMOTE_ADDR'] == 'localhost') ){
        $servername = "127.0.0.1";
        $username = "allure";
        $password = "Magento@1";
        $dbname = "Eclipse";
    }else{
        $servername = "127.0.0.1";
        $username = "eclipse";
        $password = "wp@allure_#";
        $dbname = "eclipse_prod";
    }
    // Create connection
    $mysqli = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $isActive = isset($_GET["is_active"]) ? mysqli_real_escape_string($conn, $_GET["is_active"]) : 1;
    $isVisible = isset($_GET["is_visible"]) ? mysqli_real_escape_string($conn, $_GET["is_visible"]) : 1;
    $no_of_records_per_page = isset($_GET["page_size"]) ? mysqli_real_escape_string($conn, $_GET["page_size"]) : 5;
    $pageno = isset($_GET["page_number"]) ? mysqli_real_escape_string($conn, $_GET["page_number"]) : 1;
    $unit = isset($_GET["unit"]) ? mysqli_real_escape_string($conn, $_GET["unit"]) : "km";
    $range = isset($_GET["range"]) ? $_GET["range"] : "80000";

    if ($unit == 'km') {
        $unitMultiplier = 6371;
    }else{
        $unitMultiplier = 3956; //For miles - mi
    }

    // To get current lat long default user address start
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

    $ch = curl_init();
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
    $latitude1 = isset($_GET["latitude"]) ? $_GET["latitude"] : $ipData->latitude;
    $longitude1 = isset($_GET["longitude"]) ? $_GET["longitude"] : $ipData->longitude;
    // To get current lat long default user address end

    //$sql = "select * from stores";
    $sql = "SELECT * , ($unitMultiplier * 2 * ASIN(SQRT( POWER(SIN(( $latitude1 - latitude) *  pi()/180 / 2), 2) +COS( $latitude1 * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( $longitude1 - longitude) * pi()/180 / 2), 2) ))) as distance from stores having  distance <= ".$range." AND is_active = ".$isActive." AND is_visible = ".$isVisible." order by distance";
    $mysqli->set_charset("utf8");
    $result = $mysqli->query($sql);
    $fields=array();
    $addfield=array();

    if (!empty($result))
    {   $total_rows = $result->num_rows;
        while($row = mysqli_fetch_assoc($result))
        {   
            $latitude2 = $row["latitude"];
            $longitude2 = $row["longitude"];
            $address = ($row['store_name'].', '.$row['address1'].', '. $row['city'] .', '. $row['countrycode'].', '.$row['state'].'- '.$row['zipcode'].",Contact No.-".$row['phone_number'].", email id- ".$row['email']);
            $coordinates[] = array("latitude" => $row['latitude'], "longitude" => $row["longitude"], "count"=> $total_rows,
                "location_id" => $row["store_id"],
                "store_name" => htmlspecialchars($row["store_name"]), // error field
                "quantity_status" => "In Stock",
                "street1" => htmlspecialchars($row["address1"]), // error field
                "street2" => htmlspecialchars($row["address2"]),
                "street3" => htmlspecialchars($row["address3"]),
                "city" => htmlspecialchars($row["city"]), // error field
                "state" => htmlspecialchars($row["state"]),
                "zip_code" => htmlspecialchars($row["zipcode"]),
                "phone_number" => htmlspecialchars($row["phone_number"]),
                "email" => htmlspecialchars($row["email"]),
                "country_code" => htmlspecialchars($row["countrycode"]),
                "latitude" => $row["latitude"],
                "longitude" => $row["longitude"],
                "distance_diff" =>
                        distance(
                            $latitude1, 
                            $longitude1, 
                            $latitude2, 
                            $longitude2, 
                            $unit, 
                            2
                        ).$unit, // you change unit here
                "formatted_address"=> htmlspecialchars($address));
        }
    }
    $coordinates = json_encode($coordinates);
    
    echo $coordinates;

    return false;
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
?>
