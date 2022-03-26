this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core_events,landing_collection_basecollection,main_core) {
	'use strict';

	let _ = t => t,
	    _t,
	    _t2;
	class BaseProvider extends main_core_events.EventEmitter {
	  /**
	   * Implements base interface for works with any map providers
	   * @param {object} options
	   * @param {function} [options.onChange]
	   * @param {function} [options.onMapClick]
	   * @param {function} [options.onAddMarker]
	   * @param {function} [options.onApiLoaded]
	   * @param {HTMLElement|Element} [options.mapContainer]
	   * @param {Object} [options.mapOptions]
	   * @param {Number|String} [options.mapOptions.zoom]
	   * @param {{lat: String|Number, lng: String|Number}} [options.mapOptions.center]
	   * @param {{
	   * 		latLng: {lat: String|Number, lng: String|Number},
	   * 		title: String,
	   * 		description: String,
	   * 		showByDefault: String,
	   * 	}[]} [options.mapOptions.markers]
	   * @constructor
	   */
	  constructor(options) {
	    super();
	    /**
	     * Must be implemented by subclass
	     * @type {string}
	     */

	    this.code = '';
	    this.onChangeHandler = main_core.Type.isFunction(options.onChange) ? options.onChange : () => {};
	    this.onMapClickHandler = main_core.Type.isFunction(options.onMapClick) ? options.onMapClick : () => {};
	    this.onAddMarkerHandler = main_core.Type.isFunction(options.onAddMarker) ? options.onAddMarker : () => {};
	    this.onApiLoadedHandler = main_core.Type.isFunction(options.onApiLoaded) ? options.onApiLoaded : () => {};
	    this.onInitHandler = main_core.Type.isFunction(options.onProviderInit) ? options.onProviderInit : this.init;
	    this.options = options;
	    this.mapOptions = this.prepareMapOptions(options.mapOptions);
	    this.mapContainer = options.mapContainer;
	    this.markers = new landing_collection_basecollection.BaseCollection();
	    this.mapInstance = null;
	    this.cache = new main_core.Cache.MemoryCache();
	    this.handleApiLoad();
	  }
	  /**
	   * Default options for map
	   * @type {{}}
	   */


	  getDefaultMapOptions() {
	    return {
	      center: this.getDefaultCenter(),
	      zoom: 17,
	      markers: [{
	        latLng: this.getDefaultCenter(),
	        // todo: desc to lang message
	        title: "Bitrix24",
	        description: "Bitrix24 - Your company. United."
	      }]
	    };
	  }
	  /**
	   * Check if map options have required fields
	   * @param mapOptions
	   * @return {{center: ([]|{lng: number, lat: number}), zoom: number, markers: [{description: string, title: string, latLng: (*|{lng: number, lat: number})}]}|*}
	   */


	  prepareMapOptions(mapOptions) {
	    if (!main_core.Type.isPlainObject(mapOptions)) {
	      return this.getDefaultMapOptions();
	    }

	    let preparedOptions = mapOptions;

	    if (!this.isPoint(preparedOptions.center)) {
	      preparedOptions.center = this.getDefaultCenter();

	      if (main_core.Type.isArray(mapOptions.markers) && mapOptions.markers.length > 0) {
	        const firstMarker = mapOptions.markers[0];

	        if (main_core.Type.isPlainObject(firstMarker) && this.isPoint(firstMarker.latLng)) {
	          preparedOptions.center = firstMarker.latLng;
	        }
	      }
	    }

	    return preparedOptions;
	  }
	  /**
	   * Return a default center point by language
	   * @return {{lng: number, lat: number}}
	   */


	  getDefaultCenter() {
	    let point;

	    switch (main_core.Loc.getMessage('LANGUAGE_ID')) {
	      case 'ru':
	        point = {
	          lat: 54.71916849999999,
	          lng: 20.48854240000003
	        };
	        break;

	      case 'ua':
	        point = {
	          lat: 50.440333,
	          lng: 30.526835
	        };
	        break;

	      default:
	        //default - en
	        point = {
	          lat: 38.814089,
	          lng: -77.042356
	        };
	        break;
	    }

	    return point;
	  }
	  /**
	   * Check is current variable is a geo point
	   * @param point
	   * @return {boolean}
	   */


	  isPoint(point) {
	    return main_core.Type.isObjectLike(point) && Object.keys(point).length === 2;
	  }
	  /**
	   * Extract coords from map events (f.e. click)
	   * @param event
	   */


	  getPointByEvent(event) {
	    throw new Error("Must be implemented by subclass");
	  }

	  getCode() {
	    return this.code;
	  }
	  /**
	   * Check is provider API was loaded
	   * @return {boolean}
	   */


	  isApiLoaded() {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Initializes map
	   * Must be implemented by subclass
	   * @abstract
	   */


	  init() {
	    this.emit('onInit');
	  }
	  /**
	   * Set api load handle function
	   * @abstract
	   */


	  handleApiLoad() {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   *
	   */


	  onChange() {
	    this.onChangeHandler(this.preventChangeEvent);
	  }
	  /**
	   * Adds marker on map
	   * @abstract
	   * @param {Object} options
	   * @param {Object} options.latLng
	   * @param {Object} options.latLng
	   * @param {String|Number} options.latLng.lat
	   * @param {String|Number} options.latLng.lng
	   * @param {String} [options.title]
	   * @param {String} [options.description]
	   * @param {boolean} [options.showByDefault = false]
	   * @param {boolean} [options.editable = false]
	   * @param {boolean} [options.draggable = false]
	   * @return {void}
	   */


	  addMarker(options) {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * When marker clicked
	   * @param item
	   */


	  onMarkerClick(item) {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Removes marker from map
	   * @abstract
	   * @param options
	   */


	  removeMarker(options) {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Gets map value
	   * @abstract
	   */


	  getValue() {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Set values
	   * @param value
	   * @param preventChangeEvent
	   */


	  setValue(value, preventChangeEvent) {
	    this.preventChangeEvent = preventChangeEvent;
	    this.markers.forEach(this.removeMarker, this);

	    if (main_core.Type.isPlainObject(value)) {
	      if (main_core.Type.isArray(value.markers)) {
	        value.markers.forEach(this.addMarker, this);
	      }

	      if (!BX.Landing.Utils.isEmpty(value.center)) {
	        this.setCenter(value.center);
	      }

	      if (!BX.Landing.Utils.isEmpty(value.zoom)) {
	        this.setZoom(value.zoom);
	      }
	    }

	    this.preventChangeEvent = false;
	  }
	  /**
	   * @abstract
	   */


	  onEditFormApplyClick(event) {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * @abstract
	   * @param event
	   */


	  onEditFormRemoveClick(event) {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Creates balloon edit forms
	   * @param options
	   * @param [event]
	   * @return {BX.Landing.UI.Form.BalloonForm}
	   */


	  createBalloonEditForm(options, event) {
	    const form = new BX.Landing.UI.Form.BalloonForm({
	      title: main_core.Loc.getMessage("LANDING_NODE_MAP_FORM_HEADER")
	    });
	    const applyButton = new BX.Landing.UI.Button.BaseButton({
	      text: main_core.Loc.getMessage("LANDING_NODE_MAP_FORM_SHOW_BUTTON_APPLY"),
	      className: ["ui-btn", "ui-btn-success", "ui-btn-sm"],
	      onClick: this.onEditFormApplyClick.bind(this, event)
	    });
	    const removeButton = new BX.Landing.UI.Button.BaseButton({
	      text: main_core.Loc.getMessage("LANDING_NODE_MAP_FORM_SHOW_BUTTON_REMOVE"),
	      className: ["ui-btn", "ui-btn-danger", "ui-btn-sm"],
	      onClick: this.onEditFormRemoveClick.bind(this, event)
	    });
	    applyButton.layout.classList.remove("landing-ui-button");
	    removeButton.layout.classList.remove("landing-ui-button");
	    const footer = main_core.Tag.render(_t || (_t = _`
			<div class="ui-btn-container ui-btn-container-center">
				${0}
				${0}
			</div>
		`), applyButton.layout, removeButton.layout);
	    form.addField(new BX.Landing.UI.Field.Text({
	      title: main_core.Loc.getMessage("LANDING_NODE_MAP_FORM_TITLE"),
	      textOnly: true,
	      content: options.title
	    }));
	    form.addField(new BX.Landing.UI.Field.Text({
	      title: main_core.Loc.getMessage("LANDING_NODE_MAP_FORM_DESCRIPTION"),
	      className: "landing-ui-field-map-description",
	      content: options.description
	    }));
	    form.addField(new BX.Landing.UI.Field.Checkbox({
	      className: "landing-ui-field-map-show-by-default",
	      compact: true,
	      items: [{
	        name: main_core.Loc.getMessage("LANDING_NODE_MAP_FORM_SHOW_BY_DEFAULT"),
	        "value": true
	      }],
	      value: [options.showByDefault]
	    }));
	    form.layout.appendChild(footer);
	    return form;
	  }
	  /**
	   * Creates balloon content
	   * @param {{title: string, description: string}} options
	   * @return {HTMLElement}
	   */


	  createBalloonContent(options) {
	    return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="landing-map-balloon-content">
				<div class="landing-map-balloon-content-header">${0}</div>	
				<div class="landing-map-balloon-content-description">${0}</div>	
			</div>
		`), options.title, options.description);
	  }

	}

	const AUBERGINE = [{
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#1d2c4d"
	  }]
	}, {
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#8ec3b9"
	  }]
	}, {
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#1a3646"
	  }]
	}, {
	  "featureType": "administrative.country",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#4b6878"
	  }]
	}, {
	  "featureType": "administrative.land_parcel",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#64779e"
	  }]
	}, {
	  "featureType": "administrative.province",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#4b6878"
	  }]
	}, {
	  "featureType": "landscape.man_made",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#334e87"
	  }]
	}, {
	  "featureType": "landscape.natural",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#023e58"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#283d6a"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#6f9ba5"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#1d2c4d"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "geometry.fill",
	  "stylers": [{
	    "color": "#023e58"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#3c7680"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#304a7d"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#98a5be"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#1d2c4d"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#2c6675"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#255763"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#b0d5ce"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#023e58"
	  }]
	}, {
	  "featureType": "transit",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#98a5be"
	  }]
	}, {
	  "featureType": "transit",
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#1d2c4d"
	  }]
	}, {
	  "featureType": "transit.line",
	  "elementType": "geometry.fill",
	  "stylers": [{
	    "color": "#283d6a"
	  }]
	}, {
	  "featureType": "transit.station",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#3a4762"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#0e1626"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#4e6d70"
	  }]
	}];

	const DARK = [{
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#212121"
	  }]
	}, {
	  "elementType": "labels.icon",
	  "stylers": [{
	    "visibility": "off"
	  }]
	}, {
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#757575"
	  }]
	}, {
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#212121"
	  }]
	}, {
	  "featureType": "administrative",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#757575"
	  }]
	}, {
	  "featureType": "administrative.country",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#9e9e9e"
	  }]
	}, {
	  "featureType": "administrative.land_parcel",
	  "stylers": [{
	    "visibility": "off"
	  }]
	}, {
	  "featureType": "administrative.locality",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#bdbdbd"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#757575"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#181818"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#616161"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#1b1b1b"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "geometry.fill",
	  "stylers": [{
	    "color": "#2c2c2c"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#8a8a8a"
	  }]
	}, {
	  "featureType": "road.arterial",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#373737"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#3c3c3c"
	  }]
	}, {
	  "featureType": "road.highway.controlled_access",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#4e4e4e"
	  }]
	}, {
	  "featureType": "road.local",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#616161"
	  }]
	}, {
	  "featureType": "transit",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#757575"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#000000"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#3d3d3d"
	  }]
	}];

	const NIGHT = [{
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#242f3e"
	  }]
	}, {
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#746855"
	  }]
	}, {
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#242f3e"
	  }]
	}, {
	  "featureType": "administrative.locality",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#d59563"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#d59563"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#263c3f"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#6b9a76"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#38414e"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#212a37"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#9ca5b3"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#746855"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#1f2835"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#f3d19c"
	  }]
	}, {
	  "featureType": "transit",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#2f3948"
	  }]
	}, {
	  "featureType": "transit.station",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#d59563"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#17263c"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#515c6d"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#17263c"
	  }]
	}];

	const RETRO = [{
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#ebe3cd"
	  }]
	}, {
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#523735"
	  }]
	}, {
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#f5f1e6"
	  }]
	}, {
	  "featureType": "administrative",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#c9b2a6"
	  }]
	}, {
	  "featureType": "administrative.land_parcel",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#dcd2be"
	  }]
	}, {
	  "featureType": "administrative.land_parcel",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#ae9e90"
	  }]
	}, {
	  "featureType": "landscape.natural",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#dfd2ae"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#dfd2ae"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#93817c"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "geometry.fill",
	  "stylers": [{
	    "color": "#a5b076"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#447530"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#f5f1e6"
	  }]
	}, {
	  "featureType": "road.arterial",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#fdfcf8"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#f8c967"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#e9bc62"
	  }]
	}, {
	  "featureType": "road.highway.controlled_access",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#e98d58"
	  }]
	}, {
	  "featureType": "road.highway.controlled_access",
	  "elementType": "geometry.stroke",
	  "stylers": [{
	    "color": "#db8555"
	  }]
	}, {
	  "featureType": "road.local",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#806b63"
	  }]
	}, {
	  "featureType": "transit.line",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#dfd2ae"
	  }]
	}, {
	  "featureType": "transit.line",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#8f7d77"
	  }]
	}, {
	  "featureType": "transit.line",
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#ebe3cd"
	  }]
	}, {
	  "featureType": "transit.station",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#dfd2ae"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "geometry.fill",
	  "stylers": [{
	    "color": "#b9d3c2"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#92998d"
	  }]
	}];

	const SILVER = [{
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#f5f5f5"
	  }]
	}, {
	  "elementType": "labels.icon",
	  "stylers": [{
	    "visibility": "off"
	  }]
	}, {
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#616161"
	  }]
	}, {
	  "elementType": "labels.text.stroke",
	  "stylers": [{
	    "color": "#f5f5f5"
	  }]
	}, {
	  "featureType": "administrative.land_parcel",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#bdbdbd"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#eeeeee"
	  }]
	}, {
	  "featureType": "poi",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#757575"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#e5e5e5"
	  }]
	}, {
	  "featureType": "poi.park",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#9e9e9e"
	  }]
	}, {
	  "featureType": "road",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#ffffff"
	  }]
	}, {
	  "featureType": "road.arterial",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#757575"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#dadada"
	  }]
	}, {
	  "featureType": "road.highway",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#616161"
	  }]
	}, {
	  "featureType": "road.local",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#9e9e9e"
	  }]
	}, {
	  "featureType": "transit.line",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#e5e5e5"
	  }]
	}, {
	  "featureType": "transit.station",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#eeeeee"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "geometry",
	  "stylers": [{
	    "color": "#c9c9c9"
	  }]
	}, {
	  "featureType": "water",
	  "elementType": "labels.text.fill",
	  "stylers": [{
	    "color": "#9e9e9e"
	  }]
	}];

	const themes = {
	  AUBERGINE: AUBERGINE,
	  DARK: DARK,
	  NIGHT: NIGHT,
	  RETRO: RETRO,
	  SILVER: SILVER
	};

	const roads = {
	  '': [],
	  'off': [{
	    "featureType": "road",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }]
	};
	const landmarks = {
	  '': [],
	  'off': [{
	    "featureType": "administrative",
	    "elementType": "geometry",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }, {
	    "featureType": "poi",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }, {
	    "featureType": "road",
	    "elementType": "labels.icon",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }, {
	    "featureType": "transit",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }]
	};
	const labels = {
	  '': [],
	  'off': [{
	    "elementType": "labels",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }, {
	    "featureType": "administrative.land_parcel",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }, {
	    "featureType": "administrative.neighborhood",
	    "stylers": [{
	      "visibility": "off"
	    }]
	  }]
	};

	class GoogleMap extends BaseProvider {
	  constructor(options) {
	    super(options);
	    this.code = 'google';
	    this.themes = themes;
	  }
	  /**
	   * Extract coords from map events (f.e. click)
	   * @param event
	   */


	  getPointByEvent(event) {
	    const point = event.latLng;
	    return this.isPoint(point) ? point : {};
	  }
	  /**
	   * @inheritDoc
	   */


	  init() {
	    let opts = this.options;
	    this.mapInstance = new google.maps.Map(this.mapContainer, {
	      zoom: this.mapOptions.zoom,
	      center: this.mapOptions.center,
	      zoomControl: main_core.Type.isBoolean(opts.zoomControl) ? opts.zoomControl : true,
	      mapTypeControl: main_core.Type.isBoolean(opts.mapTypeControl) ? opts.mapTypeControl : true,
	      mapTypeControlOptions: main_core.Type.isPlainObject(opts.mapTypeControlOptions) ? opts.mapTypeControlOptions : null,
	      scaleControl: main_core.Type.isBoolean(opts.scaleControl) ? opts.scaleControl : true,
	      streetViewControl: main_core.Type.isBoolean(opts.streetViewControl) ? opts.streetViewControl : true,
	      rotateControl: main_core.Type.isBoolean(opts.rotateControl) ? opts.rotateControl : true,
	      fullscreenControl: main_core.Type.isBoolean(opts.fullscreenControl) ? opts.fullscreenControl : true,
	      styles: (opts.theme && opts.theme in this.themes ? this.themes[opts.theme] : []).concat(roads[opts.roads] || [], landmarks[opts.landmarks] || [], labels[opts.labels] || [])
	    });

	    if (this.mapOptions.markers) {
	      this.mapOptions.markers.forEach(function (markerItem) {
	        markerItem.editable = BX.Landing.getMode() === "edit";
	        markerItem.draggable = BX.Landing.getMode() === "edit";
	        this.addMarker(markerItem);
	      }, this);
	    }

	    this.onChange = this.onChange.bind(this);
	    this.mapInstance.addListener("bounds_changed", this.onChange);
	    this.mapInstance.addListener("center_changed", this.onChange);
	    this.mapInstance.addListener("zoom_changed", this.onChange);
	    this.mapInstance.addListener("click", this.onMapClickHandler);
	    super.init();
	  }
	  /**
	   * Check is provider API was loaded
	   * @return {boolean}
	   */


	  isApiLoaded() {
	    return typeof google !== "undefined";
	  }
	  /**
	   * Set api load handle function
	   * @abstract
	   */


	  handleApiLoad() {
	    window.onGoogleMapApiLoaded = () => {
	      this.onApiLoadedHandler(this.getCode());
	    };
	  }
	  /**
	   * @inheritDoc
	   * @param options
	   */


	  addMarker(options) {
	    let item = {};
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

	    if (options.showByDefault && BX.Landing.getMode() !== "edit") {
	      item.infoWindow.open(this.mapInstance, item.marker);
	      this.activeMarker = item;
	    }

	    this.markers.add(item); // in editor - always, in public - only if not empty

	    if (BX.Landing.getMode() === "edit" || options.title || options.description) {
	      item.marker.addListener("click", this.onMarkerClick.bind(this, item));
	    }

	    this.onChange();
	  }

	  onMarkerClick(item) {
	    void (this.activeMarker && this.activeMarker.infoWindow.close());
	    item.infoWindow.open(this.mapInstance, item.marker);
	    this.activeMarker = item;
	  }

	  onEditFormRemoveClick(event) {
	    if (event) {
	      event.infoWindow.close();
	      this.removeMarker(event);
	    }

	    this.markers.remove(event);
	    this.onChange();
	  }

	  onEditFormApplyClick(event) {
	    event.infoWindow.close();
	    this.onChange();
	  }

	  removeMarker(event) {
	    event.marker.setMap(null);
	    this.markers.remove(event);
	  }

	  setZoom(zoom) {
	    this.mapInstance.setZoom(zoom);
	  }

	  setCenter(center) {
	    this.mapInstance.setCenter(center);
	  }

	  getMarkersValue() {
	    return this.markers.map(function (item) {
	      return {
	        title: item.form ? item.form.fields[0].getValue() : "",
	        description: item.form ? item.form.fields[1].getValue() : "",
	        showByDefault: item.form ? !!item.form.fields[2].getValue()[0] : "",
	        latLng: item.marker.position.toJSON()
	      };
	    });
	  }

	  getValue() {
	    return {
	      center: this.mapInstance.getCenter() ? this.mapInstance.getCenter().toJSON() : {},
	      zoom: this.mapInstance.getZoom(),
	      markers: this.getMarkersValue()
	    };
	  }

	}

	class YandexMap extends BaseProvider {
	  constructor(options) {
	    super(options);
	    this.code = 'yandex';
	  }
	  /**
	   * @inheritDoc
	   */


	  init() {
	    const opts = this.options;
	    const controls = ['zoomControl', 'fullscreenControl', 'typeSelector', 'routeButtonControl'];

	    if (opts.fullscreenControl === false) {
	      controls.splice(controls.indexOf('fullscreenControl'), 1);
	    }

	    if (opts.mapTypeControl === false) {
	      controls.splice(controls.indexOf('typeSelector'), 1);
	      controls.splice(controls.indexOf('routeButtonControl'), 1);
	    }

	    this.mapInstance = new ymaps.Map(this.mapContainer, {
	      center: this.convertPointIn(this.mapOptions.center),
	      zoom: this.mapOptions.zoom,
	      behaviors: opts.zoomControl === false ? ['drag'] : ['default'],
	      controls: controls
	    });
	    this.mapInstance.events.add('click', event => {
	      this.cache.delete('value');
	      this.onMapClickHandler(event);

	      if (BX.Landing.getMode() === "edit") {
	        this.markers[this.markers.length - 1].marker.balloon.open();
	      }
	    });
	    this.mapInstance.events.add('actionend', this.onChange.bind(this));

	    if (this.mapOptions.markers) {
	      this.mapOptions.markers.forEach(function (markerItem) {
	        markerItem.editable = BX.Landing.getMode() === "edit";
	        markerItem.draggable = BX.Landing.getMode() === "edit";
	        this.addMarker(markerItem);
	      }, this);
	    }

	    super.init();
	  }
	  /**
	   * Check is provider API was loaded
	   * @return {boolean}
	   */


	  isApiLoaded() {
	    return typeof ymaps !== "undefined" && typeof ymaps.Map !== "undefined";
	  }
	  /**
	   * Convert point from Google format to Yandex
	   * @param point
	   * @return {[number,number]}
	   */


	  convertPointIn(point) {
	    return [point.lat, point.lng];
	  }
	  /**
	   * Convert point from Yandex for export
	   * @param point
	   * @return {{lng: number, lat: number}}
	   */


	  convertPointOut(point) {
	    return {
	      lat: point[0],
	      lng: point[1]
	    };
	  }
	  /**
	   * Extract coords from map events (f.e. click)
	   * @param event
	   */


	  getPointByEvent(event) {
	    return this.convertPointOut(event.get('coords'));
	  }
	  /**
	   * Set api load handle function
	   * @abstract
	   */


	  handleApiLoad() {
	    window.onYandexMapApiLoaded = () => {
	      this.onApiLoadedHandler(this.getCode());
	    };
	  }
	  /**
	   * @inheritDoc
	   * @param options
	   */


	  addMarker(options) {
	    const item = {};
	    item.form = BX.Landing.getMode() === "edit" ? this.createBalloonEditForm(options, item) : null;
	    item.content = this.createBalloonContent(options);
	    item.ballonId = 'ballonContent_' + main_core.Text.getRandom('6');
	    const isNoNeedBalloon = BX.Landing.getMode() !== "edit" && !options.title && !options.description;
	    const balloonTemplate = ymaps.templateLayoutFactory.createClass('<div id="' + item.ballonId + '"></div>', {
	      build: function () {
	        balloonTemplate.superclass.build.call(this);
	        const container = document.querySelector('#' + item.ballonId);
	        const content = options.editable && BX.Landing.getMode() === "edit" ? item.form.layout : item.content;
	        main_core.Dom.append(content, container);
	      }
	    });
	    item.marker = new ymaps.Placemark(this.convertPointIn(options.latLng), {}, {
	      balloonContentLayout: isNoNeedBalloon ? null : balloonTemplate,
	      balloonPanelMaxMapArea: 0,
	      draggable: options.draggable
	    });
	    this.mapInstance.geoObjects.add(item.marker);

	    if (options.showByDefault) {
	      item.marker.balloon.open();
	    }

	    this.markers.add(item);
	  }

	  onMarkerClick(item) {// Yandex will do everything himself
	  }

	  onEditFormRemoveClick(event) {
	    if (event) {
	      event.marker.balloon.close();
	      this.removeMarker(event);
	    }

	    this.markers.remove(event);
	    this.onChange();
	  }

	  onEditFormApplyClick(event) {
	    event.marker.balloon.close();
	    this.onChange();
	  }

	  removeMarker(event) {
	    this.mapInstance.geoObjects.remove(event.marker);
	    this.markers.remove(event);
	  }

	  setZoom(zoom) {
	    this.mapInstance.setZoom(zoom);
	  }

	  setCenter(center) {
	    this.mapInstance.setCenter(this.convertPointIn(center));
	  }

	  getMarkersValue() {
	    return this.markers.map(item => {
	      return {
	        title: item.form ? item.form.fields[0].getValue() : "",
	        description: item.form ? item.form.fields[1].getValue() : "",
	        showByDefault: item.form ? !!item.form.fields[2].getValue()[0] : "",
	        latLng: this.convertPointOut(item.marker.geometry.getCoordinates())
	      };
	    });
	  }

	  getValue() {
	    return this.cache.remember('value', () => {
	      return {
	        center: this.mapInstance.getCenter() ? this.convertPointOut(this.mapInstance.getCenter()) : {},
	        zoom: this.mapInstance.getZoom(),
	        markers: this.getMarkersValue()
	      };
	    });
	  }

	  onChange() {
	    this.cache.delete('value');
	    super.onChange();
	  }

	}

	class Map {
	  /**
	   * If API not loaded already - create schedule
	   * @type {{}}
	   */
	  constructor() {}
	  /**
	   * Create map provider for current node
	   * @param node
	   * @param options
	   * @return {*}
	   */


	  static create(node, options) {
	    // handler for load api
	    options.onApiLoaded = Map.onApiLoaded; // get provider code

	    let providerCode = node.dataset[Map.DATA_ATTRIBUTE];

	    if (!providerCode || Object.keys(Map.PROVIDERS).indexOf(providerCode) === -1) {
	      providerCode = Map.DEFAULT_PROVIDER;
	    } // init or set to schedule


	    const provider = new Map.PROVIDERS[providerCode](options);

	    if (provider.isApiLoaded()) {
	      provider.onInitHandler();
	    } else {
	      if (!main_core.Type.isArray(Map.scheduled[provider.getCode()])) {
	        Map.scheduled[provider.getCode()] = [];
	      }

	      Map.scheduled[provider.getCode()].push(provider);
	    }

	    return provider;
	  }

	  static onApiLoaded(providerCode) {
	    if (main_core.Type.isArray(Map.scheduled[providerCode])) {
	      Map.scheduled[providerCode].forEach(provider => {
	        provider.onInitHandler();
	      });
	    }
	  }

	}
	Map.PROVIDERS = {
	  google: GoogleMap,
	  yandex: YandexMap
	};
	Map.DEFAULT_PROVIDER = 'google';
	Map.DATA_ATTRIBUTE = 'mapProvider';
	Map.scheduled = {};

	exports.Map = Map;

}((this.BX.Landing.Provider = this.BX.Landing.Provider || {}),BX.Event,BX.Landing.Collection,BX));
//# sourceMappingURL=map.bundle.js.map
