export const roads = {
	'': [],
	'off': [
		{
			"featureType": "road",
			"stylers": [
				{"visibility": "off" }
			]
		}
	]
};

export const landmarks = {
	'': [],
	'off': [
		{
			"featureType": "administrative",
			"elementType": "geometry",
			"stylers": [{"visibility": "off"}]
		},
		{
			"featureType": "poi",
			"stylers": [{"visibility": "off"}]},
		{
			"featureType": "road",
			"elementType": "labels.icon",
			"stylers": [{"visibility": "off"}]
		},
		{
			"featureType": "transit",
			"stylers": [{"visibility": "off"}]
		}
	]
};

export const labels = {
	'': [],
	'off': [
		{
			"elementType": "labels",
			"stylers": [{"visibility": "off"}]
		},
		{
			"featureType": "administrative.land_parcel",
			"stylers": [{"visibility": "off"}]
		},
		{
			"featureType": "administrative.neighborhood",
			"stylers": [{"visibility": "off"}]
		}
	]
};