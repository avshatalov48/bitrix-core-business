this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_node_base) {
	'use strict';

	const debounce = BX.Landing.Utils.debounce;
	const data = BX.Landing.Utils.data;
	const proxy = BX.Landing.Utils.proxy;
	const onCustomEvent = BX.Landing.Utils.onCustomEvent;
	const encodeDataValue = BX.Landing.Utils.encodeDataValue;
	class Map extends landing_node_base.Base {
	  constructor(options) {
	    super(options);
	    this.type = 'map';
	    this.attribute = 'data-map';
	    this.hidden = true;
	    this.createMap();
	    this.lastValue = this.getValue();
	    this.onBlockUpdateAttrs = this.onBlockUpdateAttrs.bind(this);
	    onCustomEvent('BX.Landing.Block:Node:updateAttr', this.onBlockUpdateAttrs);
	  }
	  createMap() {
	    this.mapOptions = {
	      mapContainer: this.node,
	      mapOptions: data(this.node, 'data-map'),
	      theme: data(this.node, 'data-map-theme'),
	      roads: data(this.node, 'data-map-roads') || [],
	      landmarks: data(this.node, 'data-map-landmarks') || [],
	      labels: data(this.node, 'data-map-labels') || [],
	      onMapClick: proxy(this.onMapClick, this),
	      onChange: debounce(this.onChange, 500, this),
	      fullscreenControl: false,
	      mapTypeControl: false,
	      zoomControl: false
	    };
	    this.map = BX.Landing.Provider.Map.create(this.node, this.mapOptions);
	  }
	  reInitMap() {
	    const prevOptions = BX.Runtime.clone(this.mapOptions);
	    this.mapOptions.mapOptions = data(this.node, 'data-map');
	    this.mapOptions.theme = data(this.node, 'data-map-theme');
	    this.mapOptions.roads = data(this.node, 'data-map-roads') || [];
	    this.mapOptions.landmarks = data(this.node, 'data-map-landmarks') || [];
	    this.mapOptions.labels = data(this.node, 'data-map-labels') || [];
	    if (prevOptions !== this.mapOptions) {
	      this.map.reinit(this.mapOptions);
	    }
	  }
	  onBlockUpdateAttrs(event) {
	    if (event.node === this.node) {
	      this.reInitMap();
	      this.lastValue = this.getValue();
	    }
	  }
	  onMapClick(event) {
	    if (BX.Landing.UI.Panel.StylePanel.getInstance().isShown()) {
	      return;
	    }
	    this.map.addMarker({
	      latLng: this.map.getPointByEvent(event),
	      title: '',
	      description: '',
	      showByDefault: false,
	      draggable: true,
	      editable: true
	    });
	    this.map.onMarkerClick(this.map.markers[this.map.markers.length - 1]);
	  }
	  onChange(preventHistory) {
	    if (this.isChanged()) {
	      if (!preventHistory) {
	        BX.Landing.History.getInstance().push();
	      }
	      this.lastValue = this.getValue();
	      this.onChangeHandler(this, preventHistory);
	    }
	  }
	  isChanged() {
	    return JSON.stringify(this.getValue()) !== JSON.stringify(this.lastValue);
	  }
	  getValue() {
	    return this.map && this.map.isApiLoaded() ? this.map.getValue() : null;
	  }
	  getAttrValue() {
	    return encodeDataValue(this.getValue());
	  }
	  setValue(value, preventSave, preventHistory) {
	    this.map.setValue(value, preventHistory);
	  }
	  getField() {
	    return new BX.Landing.UI.Field.BaseField({
	      selector: this.selector,
	      hidden: true
	    });
	  }
	}
	BX.Landing.Node.Map = Map;

	exports.Map = Map;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX.Landing.Node));
//# sourceMappingURL=map.bundle.js.map
