;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var debounce = BX.Landing.Utils.debounce;
	var data = BX.Landing.Utils.data;
	var proxy = BX.Landing.Utils.proxy;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;


	/**
	 * @extends {BX.Landing.Block.Node}
	 * @param options
	 * @constructor
	 */
	BX.Landing.Block.Node.Map = function(options)
	{
		BX.Landing.Block.Node.apply(this, arguments);
		this.type = "map";
		this.attribute = "data-map";
		this.hidden = true;
		this.createMap();
		this.lastValue = this.getValue();
		// todo: on api loaded - getvalue
		// this.onBlockUpdateStyles = this.onBlockUpdateStyles.bind(this);
		this.onBlockUpdateAttrs = this.onBlockUpdateAttrs.bind(this);
		onCustomEvent("BX.Landing.Block:Node:updateAttr", this.onBlockUpdateAttrs);
	};


	BX.Landing.Block.Node.Map.prototype = {
		constructor: BX.Landing.Block.Node.Map,
		__proto__: BX.Landing.Block.Node.prototype,

		createMap: function()
		{
			this.mapOptions = {
				mapContainer: this.node,
				mapOptions: data(this.node, "data-map"),
				theme: data(this.node, "data-map-theme"),
				roads: data(this.node, "data-map-roads") || [],
				landmarks: data(this.node, "data-map-landmarks") || [],
				labels: data(this.node, "data-map-labels") || [],
				onMapClick: proxy(this.onMapClick, this),
				onChange: debounce(this.onChange, 500, this),
				fullscreenControl: false,
				mapTypeControl: false,
				zoomControl: false,
			};
			this.map = BX.Landing.Provider.Map.create(this.node, this.mapOptions);
		},

		reinitMap: function()
		{
			const prevOptions = BX.Runtime.clone(this.mapOptions);
			this.mapOptions.mapOptions = data(this.node, "data-map");
			this.mapOptions.theme = data(this.node, "data-map-theme");
			this.mapOptions.roads = data(this.node, "data-map-roads") || [];
			this.mapOptions.landmarks = data(this.node, "data-map-landmarks") || [];
			this.mapOptions.labels = data(this.node, "data-map-labels") || [];

			if (prevOptions !== this.mapOptions)
			{
				this.map.reinit(this.mapOptions);
			}
		},

		onBlockUpdateAttrs: function(event)
		{
			if (event.node === this.node)
			{
				this.reinitMap();
				this.lastValue = this.getValue();
			}
		},

		onMapClick: function(event)
		{
			if (BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				return;
			}

			this.map.addMarker({
				latLng: this.map.getPointByEvent(event),
				title: "",
				description: "",
				showByDefault: false,
				draggable: true,
				editable: true
			});

			this.map.onMarkerClick(this.map.markers[this.map.markers.length-1]);
		},

		onChange: function(preventHistory)
		{
			if (this.isChanged())
			{
				if (!preventHistory)
				{
					BX.Landing.History.getInstance().push();
				}

				this.lastValue = this.getValue();
				this.onChangeHandler(this, preventHistory);
			}
		},

		isChanged: function()
		{
			return JSON.stringify(this.getValue()) !== JSON.stringify(this.lastValue);
		},

		getValue: function()
		{
			return this.map && this.map.isApiLoaded()
				? this.map.getValue()
				: null;
		},

		getAttrValue: function()
		{
			return encodeDataValue(this.getValue());
		},

		setValue: function(value, preventSave, preventHistory)
		{
			this.map.setValue(value, preventHistory);
		},

		getField: function()
		{
			return new BX.Landing.UI.Field.BaseField({
				selector: this.selector,
				hidden: true
			});
		}
	};

})();