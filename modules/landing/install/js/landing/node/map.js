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
	 * @constructo
	 */
	BX.Landing.Block.Node.Map = function(options)
	{
		BX.Landing.Block.Node.apply(this, arguments);
		this.type = "map";
		this.attribute = "data-map";
		this.hidden = true;
		this.createMap();
		this.lastValue = this.getValue();
		onCustomEvent("BX.Landing.Block:updateStyleWithoutDebounce", this.onBlockUpdateStyles.bind(this));
		onCustomEvent("BX.Landing.Block:Node:updateAttr", this.onBlockUpdateAttrs.bind(this));
	};


	BX.Landing.Block.Node.Map.prototype = {
		constructor: BX.Landing.Block.Node.Map,
		__proto__: BX.Landing.Block.Node.prototype,

		createMap: function()
		{
			var options = {
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
			this.map = BX.Landing.Provider.Map.create(this.node, options);
		},

		onBlockUpdateAttrs: function(event)
		{
			if (event.node === this.node)
			{
				this.createMap();
				this.lastValue = this.getValue();
			}
		},

		onBlockUpdateStyles: function(event)
		{
			if (event.block.contains(this.node))
			{
				this.createMap();
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
					BX.Landing.History.getInstance().push(
						new BX.Landing.History.Entry({
							block: this.getBlock().id,
							selector: this.selector,
							command: "editEmbed",
							undo: this.lastValue,
							redo: this.getValue()
						})
					);
				}

				this.lastValue = this.getValue();
				this.onChangeHandler(this);
			}
		},

		isChanged: function()
		{
			return JSON.stringify(this.getValue()) !== JSON.stringify(this.lastValue);
		},

		getValue: function()
		{
			return this.map.getValue();
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