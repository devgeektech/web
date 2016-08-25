var infowindow = new google.maps.InfoWindow();
                
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
                  
		  google.maps.event.addListener(marker, 'click', (function(marker, i) {
			return function() {
			  infowindow.setContent('<h1 class="infowindow">' + locations[i][0] + '</h1>');
			  infowindow.open(map, marker);
			}
		  })(marker, i));
                  
                  
                  
                  
                  
                  
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
		//AutoCenter();
                
                });	
                
                
                
function initialize() {
	var infowindow = null;
	var locations = new Array();
	
	var data = {
		'action': 'get_users_for_map',
		'zoom_level': null      // We pass php values differently!
	};
	
	jQuery.getJSON( pdcgeocode.ajaxurl, data, function(response) {
		//console.log( 'response=' + response );
		locations = response;
		
		var map = new google.maps.Map(document.getElementById('pdcgeocode_map'), {
		  zoom: 4,
		  center: new google.maps.LatLng(32, 95),
		  mapTypeId: google.maps.MapTypeId.HYBRID
		});
                
		
		});
	
		
	

}

//google.maps.event.addDomListener(window, 'load', initialize);                