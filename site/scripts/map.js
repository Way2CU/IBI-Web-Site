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
			        "featureType": "landscape.natural",
			        "elementType": "geometry.fill",
			        "stylers": [
			            {
			                "visibility": "on"
			            },
			            {
			                "color": "#e0efef"
			            }
			        ]
			    },
			    {
			        "featureType": "poi",
			        "elementType": "geometry.fill",
			        "stylers": [
			            {
			                "visibility": "on"
			            },
			            {
			                "hue": "#1900ff"
			            },
			            {
			                "color": "#c0e8e8"
			            }
			        ]
			    },
			    {
			        "featureType": "road",
			        "elementType": "geometry",
			        "stylers": [
			            {
			                "lightness": 100
			            },
			            {
			                "visibility": "simplified"
			            }
			        ]
			    },
			    {
			        "featureType": "road",
			        "elementType": "labels",
			        "stylers": [
			            {
			                "visibility": "off"
			            }
			        ]
			    },
			    {
			        "featureType": "transit.line",
			        "elementType": "geometry",
			        "stylers": [
			            {
			                "visibility": "on"
			            },
			            {
			                "lightness": 700
			            }
			        ]
			    },
			    {
			        "featureType": "water",
			        "elementType": "all",
			        "stylers": [
			            {
			                "color": "#7dcdcd"
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


