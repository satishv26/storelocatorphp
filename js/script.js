  var licon = map = lat = long = "";
  $(window).on("load", function() {
      getAllData();
  });

  function getAllData(lat,long, range) {
    $('.addresslist').html('');
    $.ajax({
      url: "map_api_wrapper.php",
      async: true,
      data:{latitude:lat, longitude:long, range:range},
      dataType: 'json',
      success: function (data) {
        if(lat){
           var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 9,
            center: new google.maps.LatLng(lat,long),
            mapTypeId: google.maps.MapTypeId.ROADMAP
          });
        }else{
          var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 4,
            center: new google.maps.LatLng('48.403547','-89.243494'),
            mapTypeId: google.maps.MapTypeId.ROADMAP
          });
        }
        
        var infowindow = new google.maps.InfoWindow();

        var marker, i;
        if (data == null) {
          $('.addresslist').html('Store not found nearby 50km. you are redirected to all store view.');
          $('#store_city').val(0).attr("selected", "selected");
          setTimeout(function() {
            getAllData();
          }, 3000);
        }else{        
          for (i = 0; i < data.length; i++) {

            $('.addresslist').append(`<div class="val_container_text address__content">
                            <p>`+data[i].store_name+`</p>                         
                            `+data[i].formatted_address+`<div>Phone : `+data[i].phone_number+`</div><div>`+data[i].latitude+`</div>
                              <a href="https://www.google.com/maps/search/`+data[i].formatted_address+`" target="_blank" class="link-btn"> 
                              Get Directions </a>
                              <span>distance away: `+data[i].distance_diff+` </span>
                          </div><br>`);
            marker = new google.maps.Marker({
              position: new google.maps.LatLng(data[i].latitude, data[i].longitude),
              map: map
            });
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
              return function() {
                infowindow.setContent('<address class="addressss">'+data[i].formatted_address+'</address>');
                infowindow.open(map, marker);
              }
            })(marker, i));
          }
        }
        // to show the current location with all store locatino start - you can remove
         marker = new google.maps.Marker({
            position: new google.maps.LatLng(lat,long), //make dynamic current lat long
            map: map,
            icon: "logo.png"
          });
          google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
              infowindow.setContent('Your current location');
              infowindow.open(map, marker);
            }
          })(marker, i));
          // to show the current location with all store locatino end - you can remove

      }
    });  
  }

  jQuery('#autocomplete').keypress(function(event) {
    geoFindMe();
  });

  function init_map(lat,long,fadd,licon) {
        var map_options = {
            zoom: 14,
            center: new google.maps.LatLng(lat, long)
          }
        map = new google.maps.Map(document.getElementById("map"), map_options);
       marker = new google.maps.Marker({
            map: map,
            icon: licon,
            position: new google.maps.LatLng(lat, long)
        });
        infowindow = new google.maps.InfoWindow({
            content: fadd
        });
        google.maps.event.addListener(marker, "click", function () {
            infowindow.open(map, marker);
        });
        infowindow.open(map, marker);
    }


  function geoFindMe() {

    const status = document.querySelector('#status');
    const mapLink = document.querySelector('#map-link');

    mapLink.href = '';
    mapLink.textContent = '';

    function success(position) {
      const latitude  = position.coords.latitude;
      const longitude = position.coords.longitude;

      status.textContent = '';
      mapLink.href = `https://www.openstreetmap.org/#map=18/${latitude}/${longitude}`;
      mapLink.textContent = `Latitude: ${latitude} °, Longitude: ${longitude} °`;
    }

    function error() {
      status.textContent = 'Unable to retrieve your location';
    }

    if(!navigator.geolocation) {
      status.textContent = 'Geolocation is not supported by your browser';
    } else {
      status.textContent = 'Locating…';
      navigator.geolocation.getCurrentPosition(success, error);
    }

  }