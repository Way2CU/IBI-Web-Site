// create or use existing site scope
var Site = Site || {};

Site.map = function (langitude, latitude) {
  var mapCanvas = document.getElementById('map');
  var mapOptions = {
    center: {lat: langitude, lng: latitude},
    zoom: 17,
    scrollwheel: false,
    styles: [
			    {
			        "featureType": "administrative",
			        "elementType": "labels.text.fill",
			        "stylers": [
			            {
			                "color": "#444444"
			            }
			        ]
			    },
			    {
			        "featureType": "landscape",
			        "elementType": "all",
			        "stylers": [
			            {
			                "color": "#f2f2f2"
			            }
			        ]
			    },
			    {
			        "featureType": "poi",
			        "elementType": "all",
			        "stylers": [
			            {
			                "visibility": "off"
			            }
			        ]
			    },
			    {
			        "featureType": "poi.park",
			        "elementType": "geometry",
			        "stylers": [
			            {
			                "visibility": "on"
			            }
			        ]
			    },
			    {
			        "featureType": "poi.school",
			        "elementType": "geometry",
			        "stylers": [
			            {
			                "visibility": "on"
			            }
			        ]
			    },
			    {
			        "featureType": "road",
			        "elementType": "all",
			        "stylers": [
			            {
			                "saturation": -100
			            },
			            {
			                "lightness": 45
			            }
			        ]
			    },
			    {
			        "featureType": "road.highway",
			        "elementType": "all",
			        "stylers": [
			            {
			                "visibility": "simplified"
			            }
			        ]
			    },
			    {
			        "featureType": "road.arterial",
			        "elementType": "labels.icon",
			        "stylers": [
			            {
			                "visibility": "off"
			            }
			        ]
			    },
			    {
			        "featureType": "transit",
			        "elementType": "all",
			        "stylers": [
			            {
			                "visibility": "off"
			            }
			        ]
			    },
			    {
			        "featureType": "water",
			        "elementType": "all",
			        "stylers": [
			            {
			                "color": "#b9d7e3"
			            },
			            {
			                "visibility": "on"
			            }
			        ]
			    }
			]
  }

  var map = new google.maps.Map(mapCanvas, mapOptions);

  var marker = new google.maps.Marker({
    position:{lat: langitude, lng: latitude},
    map: map,
    title: "IBI"
  });
}

$(function(){
  Site.location = new Site.map(32.063931, 34.769609);
});


