;(function() {
	"use strict";

	BX.namespace("BX.Landing.Provider.Map");

	var isFunction = BX.Landing.Utils.isFunction;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var create = BX.Landing.Utils.create;

	function prepareMapOptions(options)
	{
		if (!isPlainObject(options))
		{
			return {
				center: {lat: 54.71916849999999, lng: 20.48854240000003},
				zoom: 17,
				markers: [
					{
						latLng: {lat: 54.71916849999999, lng: 20.48854240000003},
						title: "Bitrix24",
						description: "Bitrix24 - Your company. United."
					}
				]
			};
		}

		return options;
	}

	/**
	 * Implements base interface for works with any map providers
	 * @param {object} options
	 * @param {function} [options.onChange]
	 * @param {function} [options.onMapClick]
	 * @param {function} [options.onAddMarker]
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
	BX.Landing.Provider.Map.BaseProvider = function(options)
	{
		this.onChangeHandler = isFunction(options.onChange) ? options.onChange : (function() {});
		this.onMapClickHandler = isFunction(options.onMapClick) ? options.onMapClick : (function() {});
		this.onAddMarkerHandler = isFunction(options.onAddMarker) ? options.onAddMarker : (function() {});
		this.options = options;
		this.mapContainer = options.mapContainer;
		this.mapOptions = prepareMapOptions(options.mapOptions);
		this.markers = new BX.Landing.Collection.BaseCollection();
		this.mapInstance = null;
		this.init();
	};

	BX.Landing.Provider.Map.BaseProvider.prototype = {
		/**
		 * Initializes map
		 * @abstract
		 */
		init: function()
		{
			throw new Error("Must be implemented by subclass");
		},

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
		 */
		addMarker: function(options)
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Removes marker from map
		 * @abstract
		 * @param options
		 */
		removeMarker: function(options)
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Gets map value
		 * @abstract
		 */
		getValue: function()
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * @abstract
		 */
		onEditFormApplyClick: function()
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * @abstract
		 * @param event
		 */
		onEditFormRemoveClick: function(event)
		{
			throw new Error("Must be implemented by subclass");
		},

		/**
		 * Creates balloon edit forms
		 * @param options
		 * @param [event]
		 * @return {BX.Landing.UI.Form.BalloonForm}
		 */
		createBalloonEditForm: function(options, event)
		{
			var form = new BX.Landing.UI.Form.BalloonForm({
				title: BX.message("LANDING_NODE_MAP_FORM_HEADER")
			});

			var applyButton = new BX.Landing.UI.Button.BaseButton({
				text: BX.message("LANDING_NODE_MAP_FORM_SHOW_BUTTON_APPLY"),
				className: ["ui-btn", "ui-btn-success", "ui-btn-sm"],
				onClick: this.onEditFormApplyClick.bind(this, event)
			});

			var removeButton = new BX.Landing.UI.Button.BaseButton({
				text: BX.message("LANDING_NODE_MAP_FORM_SHOW_BUTTON_REMOVE"),
				className: ["ui-btn", "ui-btn-danger", "ui-btn-sm"],
				onClick: this.onEditFormRemoveClick.bind(this, event)
			});

			applyButton.layout.classList.remove("landing-ui-button");
			removeButton.layout.classList.remove("landing-ui-button");

			var footer = create("div", {
				props: {className: "ui-btn-container ui-btn-container-center"},
				children: [
					applyButton.layout,
					removeButton.layout
				]
			});

			form.addField(
				new BX.Landing.UI.Field.Text({
					title: BX.message("LANDING_NODE_MAP_FORM_TITLE"),
					textOnly: true,
					content: options.title
				})
			);

			form.addField(
				new BX.Landing.UI.Field.Text({
					title: BX.message("LANDING_NODE_MAP_FORM_DESCRIPTION"),
					className: "landing-ui-field-map-description",
					content: options.description
				})
			);

			form.addField(
				new BX.Landing.UI.Field.Checkbox({
					className: "landing-ui-field-map-show-by-default",
					compact: true,
					items: [
						{name: BX.message("LANDING_NODE_MAP_FORM_SHOW_BY_DEFAULT"), "value": true}
					],
					value: [options.showByDefault]
				})
			);

			form.layout.appendChild(footer);

			return form;
		},

		/**
		 * Creates balloon content
		 * @param {{title: string, description: string}} options
		 * @return {HTMLElement}
		 */
		createBalloonContent: function(options)
		{
			return create("div", {
				props: {className: "landing-map-balloon-content"},
				children: [
					create("div", {
						props: {className: "landing-map-balloon-content-header"},
						html: options.title
					}),
					create("div", {
						props: {className: "landing-map-balloon-content-description"},
						html: options.description
					})
				]
			});
		}
	};
})();