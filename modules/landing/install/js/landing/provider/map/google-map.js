;(function() {
	"use strict";

	BX.namespace("BX.Landing.Provider.Map");

	var proxy = BX.Landing.Utils.proxy;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isArray = BX.Landing.Utils.isArray;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var isBoolean = BX.Landing.Utils.isBoolean;
	var roads = {
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
	var landmarks = {
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

	var labels = {
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

	/**
	 * Implements interface for works with Google Maps
	 * @extends BX.Landing.Provider.Map.BaseProvider
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.Provider.Map.GoogleMap = function(options)
	{
		this.themes = BX.Landing.Provider.Map.GoogleMap.Theme;
		BX.Landing.Provider.Map.BaseProvider.apply(this, arguments);
	};

	BX.Landing.Provider.Map.GoogleMap.prototype = {
		constructor: BX.Landing.Provider.Map.GoogleMap,
		__proto__: BX.Landing.Provider.Map.BaseProvider.prototype,

		init: function()
		{
			var opts = this.options;

			this.mapInstance = new google.maps.Map(this.mapContainer, {
				zoom: this.mapOptions.zoom,
				center: this.mapOptions.center,
				zoomControl: isBoolean(opts.zoomControl) ? opts.zoomControl : true,
				mapTypeControl: isBoolean(opts.mapTypeControl) ? opts.mapTypeControl : true,
				mapTypeControlOptions: isPlainObject(opts.mapTypeControlOptions) ? opts.mapTypeControlOptions : null,
				scaleControl: isBoolean(opts.scaleControl) ? opts.scaleControl : true,
				streetViewControl: isBoolean(opts.streetViewControl) ? opts.streetViewControl : true,
				rotateControl: isBoolean(opts.rotateControl) ? opts.rotateControl : true,
				fullscreenControl: isBoolean(opts.fullscreenControl) ? opts.fullscreenControl : true,
				styles: (opts.theme && opts.theme in this.themes ? this.themes[opts.theme] : [])
					.concat(roads[opts.roads] || [], landmarks[opts.landmarks] || [], labels[opts.labels] || [])
			});

			if (this.mapOptions.markers)
			{
				this.mapOptions.markers.forEach(function(markerItem) {
					markerItem.editable = BX.Landing.getMode() === "edit";
					markerItem.draggable = BX.Landing.getMode() === "edit";
					this.addMarker(markerItem);
				}, this);
			}

			this.mapInstance.addListener("click", this.onMapClickHandler);
			this.mapInstance.addListener("bounds_changed", proxy(this.onChange, this));
			this.mapInstance.addListener("center_changed", proxy(this.onChange, this));
			this.mapInstance.addListener("zoom_changed", proxy(this.onChange, this));
		},

		onChange: function()
		{
			this.onChangeHandler(this.preventChangeEvent);
		},

		/**
		 * @inheritDoc
		 * @param options
		 */
		addMarker: function(options)
		{
			var item = {};
			item.marker = new google.maps.Marker({
				position: options.latLng,
				map: this.mapInstance,
				draggable: options.draggable
			});

			item.form = BX.Landing.getMode() === "edit" ? this.createBalloonEditForm(options, item) : null;
			item.content = this.createBalloonContent(options);

			item.infoWindow = new google.maps.InfoWindow({
				content: options.editable && BX.Landing.getMode() === "edit" ? item.form.layout : item.content
			});

			if (options.showByDefault && BX.Landing.getMode() !== "edit")
			{
				item.infoWindow.open(this.mapInstance, item.marker);
			}

			this.markers.add(item);

			item.marker.addListener("click", this.onMarkerClick.bind(this, item));
			this.onChange();
		},

		onMarkerClick: function(item)
		{
			void (this.activeMarker && this.activeMarker.infoWindow.close());
			item.infoWindow.open(this.mapInstance, item.marker);
			this.activeMarker = item;
		},

		onEditFormRemoveClick: function(event)
		{
			if (event)
			{
				event.infoWindow.close();
				this.removeMarker(event);
			}

			this.markers.remove(event);
			this.onChange();
		},

		onEditFormApplyClick: function(event)
		{
			event.infoWindow.close();
			this.onChange();
		},

		removeMarker: function(event)
		{
			event.marker.setMap(null);
			this.markers.remove(event);
		},

		setZoom: function(zoom)
		{
			this.mapInstance.setZoom(zoom);
		},

		setCenter: function(center)
		{
			this.mapInstance.setCenter(center);
		},

		getMarkersValue: function()
		{
			return this.markers.map(function(item) {
				return {
					title: item.form ? item.form.fields[0].getValue() : "",
					description: item.form ? item.form.fields[1].getValue() : "",
					showByDefault: item.form ? !!item.form.fields[2].getValue()[0] : "",
					latLng: item.marker.position.toJSON()
				};
			});
		},

		getValue: function()
		{
			return {
				center: this.mapInstance.getCenter() ? this.mapInstance.getCenter().toJSON() : {},
				zoom: this.mapInstance.getZoom(),
				markers: this.getMarkersValue()
			}
		},

		setValue: function(value, preventChangeEvent)
		{
			this.preventChangeEvent = preventChangeEvent;

			this.markers.forEach(this.removeMarker, this);

			if (isPlainObject(value))
			{
				if (isArray(value.markers))
				{
					value.markers.forEach(this.addMarker, this);
				}

				if (!isEmpty(value.center))
				{
					this.setCenter(value.center);
				}

				if (!isEmpty(value.zoom))
				{
					this.setZoom(value.zoom);
				}
			}

			this.preventChangeEvent = false;
		}
	};
})();