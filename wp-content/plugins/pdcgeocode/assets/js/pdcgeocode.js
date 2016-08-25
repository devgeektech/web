function initMap() {
  var myLatlng = {lat: 51.5072, lng: 0.1275};

  var map = new google.maps.Map(document.getElementById('pdcgeocode_map'), {
      zoom: 14,                        // set the zoom level manually
	  zoomControl: true,
	  scaleControl: true,
	  scrollwheel: false,
	  disableDoubleClickZoom: true,
      center: myLatlng
  });

    var locations = new Array();
	
	var data = {
		'action': 'get_users_for_map',
		'zoom_level': null      // We pass php values differently!
	};
	
	jQuery.getJSON( pdcgeocode.ajaxurl, data, function(response) {
		//console.log( 'response=' + response );
		locations = response;            
                var marker, i;
		var markers = new Array();
	
                console.log( 'locations.length=' + locations.length );
                
		for (i = 0; i < locations.length; i++) {  
		console.log( locations[i][1] + locations[i][2] );
		  marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations[i][1], locations[i][2]),
			map: map
		  });
	
		  markers.push(marker);
                  
		  
		  };
        function AutoCenter() {
		  //  Create a new viewpoint bound
		  var bounds = new google.maps.LatLngBounds();
		  //  Go through each...
		  jQuery.each(markers, function (index, marker) {
		  bounds.extend(marker.position);
		  });
		  //  Fit these bounds to the map
		  map.fitBounds(bounds);
		}
		AutoCenter();
        });

  map.addListener('center_changed', function() {
    // 3 seconds after the center of the map has changed, pan back to the
    // marker.
    window.setTimeout(function() {
      map.panTo(marker.getPosition());
    }, 3000);
  });

  marker.addListener('click', function() {
    map.setZoom(8);
    map.setCenter(marker.getPosition());
  });
  
  
   
}

initMap();