<?php
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
    $latitude1 = $ipData->latitude;
    $longitude1 = $ipData->longitude;
?>
<html>
  <head>
    <title>Store Locator</title>
    <meta name="viewport" content="initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css">
    <script type="text/javascript" charset="utf8" src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-2.0.3.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyCxDMdbvN72TgWens7PG9Q76qhjyRg_vcQ"></script>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/styles.css">
    <script type="text/javascript" src="js/script.js"></script>
  </head>
  <body>
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
    $mysqli = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


      $sql = "SELECT * FROM  stores WHERE is_active = 1 AND is_visible = 1";
      $result = $mysqli->query($sql);
      $fields= $storeIds = array();
      $addfield=array();
      if (!empty($result))
        {
        while($row = mysqli_fetch_assoc($result))
        {
          $address = ($row['store_name'].', '.$row['address1'].', '. $row['city'] .', '. $row['state'].', '.$row['countrycode'].'- '.$row['zipcode']).", Contact No.-".$row['phone_number']." email id- ".$row['email'];
          $coordinates[] = array("store_id"=> $row['store_id'],"latitude" => $row['latitude'], "longitude" => $row["longitude"],"city"=>$row['city'], "address"=> $address);
        }
      }
    ?>
<div class="row">
  <div class="column">
      <div class="store_select">
              <h3>Select City:</h3>
              <select id="store_city">
                <option value="0" id="select_city">Select City</option>
                <?php foreach ($coordinates as $key => $value): ?>
                  <option id="<?= $value['store_id'] ?>" data-address="<?= $value['address'] ?>" data-latitude="<?= $value['latitude'] ?>" data-longitude="<?= $value['longitude'] ?>" value='<?php echo isset($value['store_id'])?$value['store_id']:''; ?>'>
                    <?php echo isset($value['city'])?$value['city']:''; ?>
                  </option>
                <?php endforeach ?>
              </select>
          <p>Click the button to get your coordinates.</p>
          <input
              type="text"
              placeholder="Enter your address"
              id="autocomplete"
          />
          <button onclick="getData()">use location</button>
          <div class="addresslist"></div>
      </div>
  </div>
  <div class="column">
    <div id="map"></div>
  </div>
</div>
    
  </body>
</html>

<script type="text/javascript">
  var licon = map = "";
  var range = 50; // range in km
  jQuery("#store_city").change(function (event) {
      var storeID =  $(this).val();
      var latitude = jQuery('#'+$(this).val()).attr('data-latitude');
      var longitude = jQuery('#'+$(this).val()).attr('data-longitude');
      var address = jQuery('#'+$(this).val()).attr('data-address');
      if(storeID == 0){
        getAllData();
      }else{
       // init_map(latitude, longitude, address);
        getAllData(latitude,longitude, range);
      }
    });
  function getData() {
    if($('#autocomplete').val() == ''){
      //init_map(<?php echo $latitude1 ?>,<?php echo $latitude1 ?>,'kandivali west mumbai 400067',"logo.png");
      getAllData(<?php echo $latitude1 ?>,<?php echo $longitude1 ?>,range);
    }else{
        $.ajax({
        url: "map_api_wrapper.php",
        data: {address:$('#autocomplete').val(), range:range},
        async: true,
        dataType: 'json',
        success: function (data) {
          console.log(data);
          //load map
          var lat = data['latitude'];
          var long = data['longitude'];
          var fadd = data['formatted_address'];
          licon = "logo.png";
         // init_map(lat,long,fadd,licon);
          getAllData(data['latitude'],data['longitude'], range);
        }
      });
    }
  }
  new google.maps.places.Autocomplete(
        document.getElementById("autocomplete"))

  new google.maps.places.Autocomplete(
     document.getElementById("autocomplete"), {
        bounds: new google.maps.LatLngBounds('48.403547', '-89.243494')
     }
  );

</script>