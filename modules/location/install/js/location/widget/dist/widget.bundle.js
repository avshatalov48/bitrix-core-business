this.BX = this.BX || {};
this.BX.Location = this.BX.Location || {};
(function (exports,ui_vue,ui_designTokens,location_google,main_popup,location_source,ui_forms,location_core,location_widget,main_core_events,main_core) {
	'use strict';

	/**
	 * Contains
	 * */
	var State = function State() {
	  babelHelpers.classCallCheck(this, State);
	};
	babelHelpers.defineProperty(State, "INITIAL", 'INITIAL');
	babelHelpers.defineProperty(State, "DATA_INPUTTING", 'DATA_INPUTTING');
	babelHelpers.defineProperty(State, "DATA_SELECTED", 'DATA_SELECTED');
	babelHelpers.defineProperty(State, "DATA_SUPPOSED", 'DATA_SUPPOSED');
	babelHelpers.defineProperty(State, "DATA_LOADING", 'DATA_LOADING');
	babelHelpers.defineProperty(State, "DATA_LOADED", 'DATA_LOADED');

	var _templateObject, _templateObject2;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _address = /*#__PURE__*/new WeakMap();
	var _element = /*#__PURE__*/new WeakMap();
	var _stringElement = /*#__PURE__*/new WeakMap();
	var _addressFormat = /*#__PURE__*/new WeakMap();
	var _convertAddressToString = /*#__PURE__*/new WeakSet();
	var AddressString = /*#__PURE__*/function () {
	  function AddressString(props) {
	    babelHelpers.classCallCheck(this, AddressString);
	    _classPrivateMethodInitSpec(this, _convertAddressToString);
	    _classPrivateFieldInitSpec(this, _address, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _element, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _stringElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _addressFormat, {
	      writable: true,
	      value: void 0
	    });
	    if (!(props.addressFormat instanceof location_core.Format)) {
	      throw new Error('addressFormat must be instance of Format');
	    }
	    babelHelpers.classPrivateFieldSet(this, _addressFormat, props.addressFormat);
	  }
	  babelHelpers.createClass(AddressString, [{
	    key: "render",
	    value: function render(props) {
	      babelHelpers.classPrivateFieldSet(this, _address, props.address);
	      var addresStr = _classPrivateMethodGet(this, _convertAddressToString, _convertAddressToString2).call(this, babelHelpers.classPrivateFieldGet(this, _address));
	      babelHelpers.classPrivateFieldSet(this, _stringElement, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"location-map-address-text\">", "</div>"])), addresStr));
	      babelHelpers.classPrivateFieldSet(this, _element, main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"location-map-address-container\">\n\t\t\t\t<div class=\"location-map-address-icon\"></div>\n\t\t\t\t", "\n\t\t\t</div>"])), babelHelpers.classPrivateFieldGet(this, _stringElement)));
	      if (addresStr === '') {
	        this.hide();
	      }
	      return babelHelpers.classPrivateFieldGet(this, _element);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (babelHelpers.classPrivateFieldGet(this, _element)) {
	        babelHelpers.classPrivateFieldGet(this, _element).style.display = 'block';
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (babelHelpers.classPrivateFieldGet(this, _element)) {
	        babelHelpers.classPrivateFieldGet(this, _element).style.display = 'none';
	      }
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return !babelHelpers.classPrivateFieldGet(this, _element) || babelHelpers.classPrivateFieldGet(this, _element).style.display === 'none';
	    }
	  }, {
	    key: "address",
	    set: function set(address) {
	      babelHelpers.classPrivateFieldSet(this, _address, address);
	      if (!babelHelpers.classPrivateFieldGet(this, _stringElement)) {
	        return;
	      }
	      babelHelpers.classPrivateFieldGet(this, _stringElement).innerHTML = _classPrivateMethodGet(this, _convertAddressToString, _convertAddressToString2).call(this, address);
	      if (!address && !this.isHidden()) {
	        this.hide();
	      } else if (address && this.isHidden()) {
	        this.show();
	      }
	    }
	  }]);
	  return AddressString;
	}();
	function _convertAddressToString2(address) {
	  var result = '';
	  if (address) {
	    result = location_core.AddressStringConverter.convertAddressToStringTemplate(address, babelHelpers.classPrivateFieldGet(this, _addressFormat).getTemplate(location_core.FormatTemplateType.DEFAULT), location_core.AddressStringConverter.CONTENT_TYPE_HTML, ', ', babelHelpers.classPrivateFieldGet(this, _addressFormat));
	  }
	  return result;
	}

	var AddressApplier = ui_vue.Vue.extend({
	  props: {
	    address: {
	      required: true
	    },
	    addressFormat: {
	      required: true
	    },
	    isHidden: {
	      required: true
	    }
	  },
	  methods: {
	    handleApplyClick: function handleApplyClick() {
	      this.$emit('apply', {
	        address: this.address
	      });
	    },
	    convertAddressToString: function convertAddressToString(address) {
	      if (!address) {
	        return '';
	      }
	      return address.toString(this.addressFormat, location_core.AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA);
	    }
	  },
	  computed: {
	    addressString: function addressString() {
	      if (!this.address) {
	        return '';
	      }
	      return this.address.toString(this.addressFormat, location_core.AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA, location_core.AddressStringConverter.CONTENT_TYPE_TEXT);
	    },
	    containerStyles: function containerStyles() {
	      return {
	        display: this.isHidden ? 'none' : 'flex'
	      };
	    },
	    containerClasses: function containerClasses() {
	      return this.isHidden ? {
	        hidden: true
	      } : {};
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('LOCATION_WIDGET_');
	    }
	  },
	  template: "\n\t\t<div\n\t\t\t:class=\"containerClasses\"\n\t\t\t:style=\"containerStyles\"\n\t\t\tclass=\"location-map-address-changed\"\n\t\t>\n\t\t\t<div class=\"location-map-address-changed-inner\">\n\t\t\t<div class=\"location-map-address-changed-title\">\n\t\t\t\t{{localize.LOCATION_WIDGET_AUI_ADDRESS_CHANGED_NEW_ADDRESS}}\n\t\t\t</div>\n\t\t\t<div class=\"location-map-address-changed-text\">{{addressString}}</div>\n\t\t\t</div>\n\t\t\t<button @click=\"handleApplyClick\" type=\"button\" class=\"location-map-address-apply-btn\">\n\t\t\t\t{{localize.LOCATION_WIDGET_AUI_ADDRESS_APPLY}}\n\t\t\t</button>\n\t\t</div>\t\n\t"
	});

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Popup window, which contains map
	 */
	var _adjustRightPosition = /*#__PURE__*/new WeakSet();
	var Popup = /*#__PURE__*/function (_MainPopup) {
	  babelHelpers.inherits(Popup, _MainPopup);
	  function Popup() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, Popup);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(Popup)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _adjustRightPosition);
	    return _this;
	  }
	  babelHelpers.createClass(Popup, [{
	    key: "getBindElement",
	    value: function getBindElement() {
	      return this.bindElement;
	    }
	  }, {
	    key: "adjustPosition",
	    value: function adjustPosition(bindOptions) {
	      var isCustomPosition, isCustomPositionSuccess;
	      if (this.bindOptions.position && this.bindOptions.position === 'right') {
	        isCustomPosition = true;
	        isCustomPositionSuccess = _classPrivateMethodGet$1(this, _adjustRightPosition, _adjustRightPosition2).call(this);
	      }
	      if (!(isCustomPosition && isCustomPositionSuccess)) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(Popup.prototype), "adjustPosition", this).call(this, bindOptions);
	      }
	    }
	    /**
	     * Adjust the popup in right position
	     * @returns {boolean} an indicator whether or not we have managed to adjust the popup successfully
	     */
	  }]);
	  return Popup;
	}(main_popup.Popup);
	function _adjustRightPosition2() {
	  var bindElRect = this.bindElement.getBoundingClientRect();
	  var popupHeight = this.getPopupContainer().offsetHeight;
	  var popupWidth = this.getPopupContainer().offsetWidth;

	  /**
	   * Check if the popup fits in the viewport
	   */
	  if (bindElRect.left + bindElRect.width + popupWidth > document.documentElement.clientWidth) {
	    return false;
	  }
	  var angleOffsetY = popupHeight / 2;
	  var left = bindElRect.left + bindElRect.width + 10;
	  var top = window.pageYOffset + bindElRect.top + bindElRect.height / 2 - popupHeight / 2;
	  if (top < window.pageYOffset) {
	    angleOffsetY -= window.pageYOffset - top;
	    top = window.pageYOffset;
	  } else if (top > window.pageYOffset + document.body.clientHeight - popupHeight) {
	    angleOffsetY += top - (window.pageYOffset + document.body.clientHeight - popupHeight);
	    top = window.pageYOffset + document.body.clientHeight - popupHeight;
	  }
	  this.setAngle({
	    position: 'left',
	    offset: angleOffsetY
	  });
	  main_core.Dom.adjust(this.popupContainer, {
	    style: {
	      top: "".concat(top, "px"),
	      left: "".concat(left, "px"),
	      zIndex: this.getZindex()
	    }
	  });
	  return true;
	}

	var _templateObject$1, _templateObject2$1, _templateObject3;
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _map = /*#__PURE__*/new WeakMap();
	var _mode = /*#__PURE__*/new WeakMap();
	var _address$1 = /*#__PURE__*/new WeakMap();
	var _popup = /*#__PURE__*/new WeakMap();
	var _addressString = /*#__PURE__*/new WeakMap();
	var _addressApplier = /*#__PURE__*/new WeakMap();
	var _addressFormat$1 = /*#__PURE__*/new WeakMap();
	var _gallery = /*#__PURE__*/new WeakMap();
	var _locationRepository = /*#__PURE__*/new WeakMap();
	var _isMapRendered = /*#__PURE__*/new WeakMap();
	var _mapInnerContainer = /*#__PURE__*/new WeakMap();
	var _geocodingService = /*#__PURE__*/new WeakMap();
	var _contentWrapper = /*#__PURE__*/new WeakMap();
	var _userLocationPoint = /*#__PURE__*/new WeakMap();
	var _createAddressApplier = /*#__PURE__*/new WeakSet();
	var _onLocationChanged = /*#__PURE__*/new WeakSet();
	var _renderPopup = /*#__PURE__*/new WeakSet();
	var _extractLatLon = /*#__PURE__*/new WeakSet();
	var _convertAddressToLocation = /*#__PURE__*/new WeakSet();
	var _setLocationInternal = /*#__PURE__*/new WeakSet();
	var _renderMap = /*#__PURE__*/new WeakSet();
	var MapPopup = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(MapPopup, _EventEmitter);
	  function MapPopup(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, MapPopup);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MapPopup).call(this, props));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _renderMap);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _setLocationInternal);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _convertAddressToLocation);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _extractLatLon);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _renderPopup);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _onLocationChanged);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _createAddressApplier);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _map, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _mode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _address$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _addressString, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _addressApplier, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _addressFormat$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _gallery, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _locationRepository, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _isMapRendered, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _mapInnerContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _geocodingService, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _contentWrapper, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _userLocationPoint, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Location.Widget.MapPopup');
	    if (!(props.map instanceof location_core.MapBase)) {
	      BX.debug('map must be instance of Map');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map, props.map);
	    if (props.geocodingService instanceof location_core.GeocodingServiceBase) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _geocodingService, props.geocodingService);
	    }
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _map).onLocationChangedEventSubscribe(_classPrivateMethodGet$2(babelHelpers.assertThisInitialized(_this), _onLocationChanged, _onLocationChanged2).bind(babelHelpers.assertThisInitialized(_this)));
	    if (!(props.popup instanceof Popup)) {
	      BX.debug('popup must be instance of Popup');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _popup, props.popup);
	    if (!(props.addressFormat instanceof location_core.Format)) {
	      BX.debug('addressFormat must be instance of Format');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _addressFormat$1, props.addressFormat);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _addressString, new AddressString({
	      addressFormat: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressFormat$1)
	    }));
	    _classPrivateMethodGet$2(babelHelpers.assertThisInitialized(_this), _createAddressApplier, _createAddressApplier2).call(babelHelpers.assertThisInitialized(_this));
	    if (props.gallery) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _gallery, props.gallery);
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _locationRepository, props.locationRepository);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _userLocationPoint, props.userLocationPoint);
	    return _this;
	  }
	  babelHelpers.createClass(MapPopup, [{
	    key: "render",
	    value: function render(props) {
	      babelHelpers.classPrivateFieldSet(this, _address$1, props.address);
	      babelHelpers.classPrivateFieldSet(this, _mode, props.mode);
	      babelHelpers.classPrivateFieldSet(this, _isMapRendered, false);
	      babelHelpers.classPrivateFieldSet(this, _mapInnerContainer, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"location-map-inner\"></div>"]))));
	      _classPrivateMethodGet$2(this, _renderPopup, _renderPopup2).call(this, props.bindElement, babelHelpers.classPrivateFieldGet(this, _mapInnerContainer));
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this2 = this;
	      var useUserLocation = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      _classPrivateMethodGet$2(this, _convertAddressToLocation, _convertAddressToLocation2).call(this, babelHelpers.classPrivateFieldGet(this, _address$1), useUserLocation).then(function (location) {
	        if (!location) {
	          return;
	        }
	        babelHelpers.classPrivateFieldGet(_this2, _popup).show();
	        if (!babelHelpers.classPrivateFieldGet(_this2, _isMapRendered)) {
	          _classPrivateMethodGet$2(_this2, _renderMap, _renderMap2).call(_this2, {
	            location: location
	          }).then(function () {
	            if (babelHelpers.classPrivateFieldGet(_this2, _gallery)) {
	              babelHelpers.classPrivateFieldGet(_this2, _gallery).location = location;
	            }
	            _this2.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onShowedEvent));
	            babelHelpers.classPrivateFieldGet(_this2, _map).onMapShow();
	          });
	          babelHelpers.classPrivateFieldSet(_this2, _isMapRendered, true);
	        } else {
	          babelHelpers.classPrivateFieldGet(_this2, _map).location = location;
	          if (babelHelpers.classPrivateFieldGet(_this2, _gallery)) {
	            babelHelpers.classPrivateFieldGet(_this2, _gallery).location = location;
	          }
	          _this2.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onShowedEvent));
	          babelHelpers.classPrivateFieldGet(_this2, _map).onMapShow();
	        }
	      });
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return babelHelpers.classPrivateFieldGet(this, _popup).isShown();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      babelHelpers.classPrivateFieldGet(this, _popup).close();
	      babelHelpers.classPrivateFieldGet(this, _addressApplier).$props.isHidden = true;
	      this.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onClosedEvent));
	    }
	  }, {
	    key: "onChangedEventSubscribe",
	    value: function onChangedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onChangedEvent), listener);
	    }
	  }, {
	    key: "onMouseOverSubscribe",
	    value: function onMouseOverSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onMouseOverEvent), listener);
	    }
	  }, {
	    key: "onMouseOutSubscribe",
	    value: function onMouseOutSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onMouseOutEvent), listener);
	    }
	  }, {
	    key: "subscribeOnShowedEvent",
	    value: function subscribeOnShowedEvent(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onShowedEvent), listener);
	    }
	  }, {
	    key: "subscribeOnClosedEvent",
	    value: function subscribeOnClosedEvent(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onClosedEvent), listener);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.classPrivateFieldSet(this, _map, null);
	      babelHelpers.classPrivateFieldSet(this, _gallery, null);
	      babelHelpers.classPrivateFieldSet(this, _addressString, null);
	      babelHelpers.classPrivateFieldSet(this, _addressApplier, null);
	      babelHelpers.classPrivateFieldGet(this, _popup).destroy();
	      babelHelpers.classPrivateFieldSet(this, _popup, null);
	      main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _contentWrapper));
	      babelHelpers.classPrivateFieldSet(this, _contentWrapper, null);
	      main_core.Event.unbindAll(this);
	    }
	  }, {
	    key: "bindElement",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _popup).getBindElement();
	    },
	    set: function set(bindElement) {
	      if (main_core.Type.isDomNode(bindElement)) {
	        babelHelpers.classPrivateFieldGet(this, _popup).setBindElement(bindElement);
	      } else {
	        BX.debug('bindElement must be type of dom node');
	      }
	    }
	  }, {
	    key: "address",
	    set: function set(address) {
	      var _this3 = this;
	      babelHelpers.classPrivateFieldSet(this, _address$1, address);
	      babelHelpers.classPrivateFieldGet(this, _addressString).address = address;
	      _classPrivateMethodGet$2(this, _convertAddressToLocation, _convertAddressToLocation2).call(this, address).then(function (location) {
	        _classPrivateMethodGet$2(_this3, _setLocationInternal, _setLocationInternal2).call(_this3, location);
	      });
	    }
	  }, {
	    key: "mode",
	    set: function set(mode) {
	      babelHelpers.classPrivateFieldSet(this, _mode, mode);
	      babelHelpers.classPrivateFieldGet(this, _map).mode = mode;
	    }
	  }]);
	  return MapPopup;
	}(main_core_events.EventEmitter);
	function _createAddressApplier2() {
	  var _this4 = this;
	  babelHelpers.classPrivateFieldSet(this, _addressApplier, new AddressApplier({
	    propsData: {
	      address: babelHelpers.classPrivateFieldGet(this, _address$1),
	      addressFormat: babelHelpers.classPrivateFieldGet(this, _addressFormat$1),
	      isHidden: true
	    }
	  }));
	  babelHelpers.classPrivateFieldGet(this, _addressApplier).$mount();
	  babelHelpers.classPrivateFieldGet(this, _addressApplier).$on('apply', function (event) {
	    var prevAddress = event.address;
	    babelHelpers.classPrivateFieldSet(_this4, _address$1, prevAddress);
	    babelHelpers.classPrivateFieldGet(_this4, _addressString).address = prevAddress;
	    babelHelpers.classPrivateFieldGet(_this4, _addressApplier).$props.isHidden = true;
	    _this4.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onChangedEvent), {
	      address: prevAddress
	    });
	  });
	}
	function _onLocationChanged2(event) {
	  var data = event.getData();
	  var location = data.location;
	  var address = location.toAddress();
	  if (!babelHelpers.classPrivateFieldGet(this, _address$1)) {
	    babelHelpers.classPrivateFieldSet(this, _address$1, address);
	    babelHelpers.classPrivateFieldGet(this, _addressString).address = address;
	    this.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onChangedEvent), {
	      address: address
	    });
	  } else if (address.fieldCollection.isEqual(babelHelpers.classPrivateFieldGet(this, _address$1).fieldCollection, location_core.LocationType.ADDRESS_LINE_1)) {
	    babelHelpers.classPrivateFieldGet(this, _address$1).latitude = address.latitude;
	    babelHelpers.classPrivateFieldGet(this, _address$1).longitude = address.longitude;
	    if (babelHelpers.classPrivateFieldGet(this, _address$1).location) {
	      babelHelpers.classPrivateFieldGet(this, _address$1).location.latitude = address.latitude;
	      babelHelpers.classPrivateFieldGet(this, _address$1).location.longitude = address.longitude;
	    }
	    this.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onChangedEvent), {
	      address: babelHelpers.classPrivateFieldGet(this, _address$1)
	    });
	    babelHelpers.classPrivateFieldGet(this, _addressApplier).$props.isHidden = true;
	  } else {
	    babelHelpers.classPrivateFieldGet(this, _addressString).address = address;
	    babelHelpers.classPrivateFieldGet(this, _addressApplier).$props.address = address;
	    babelHelpers.classPrivateFieldGet(this, _addressApplier).$props.isHidden = false;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _gallery)) {
	    babelHelpers.classPrivateFieldGet(this, _gallery).location = location;
	  }
	}
	function _renderPopup2(bindElement, mapInnerContainer) {
	  var _this5 = this;
	  var gallery = '';
	  if (babelHelpers.classPrivateFieldGet(this, _gallery)) {
	    gallery = babelHelpers.classPrivateFieldGet(this, _gallery).render();
	  }
	  var thirdPartyWarningNode = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"location-map-address-third-party-warning\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('LOCATION_WIDGET_THIRD_PARTY_WARNING'));
	  babelHelpers.classPrivateFieldSet(this, _contentWrapper, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"location-map-wrapper\">\n\t\t\t\t<div class=\"location-map-container\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>"])), mapInnerContainer, gallery, babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit ? babelHelpers.classPrivateFieldGet(this, _addressString).render({
	    address: babelHelpers.classPrivateFieldGet(this, _address$1)
	  }) : '', thirdPartyWarningNode, babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit ? babelHelpers.classPrivateFieldGet(this, _addressApplier).$el : ''));
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _contentWrapper), 'click', function (e) {
	    return e.stopPropagation();
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _contentWrapper), 'mouseover', function (e) {
	    return _this5.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onMouseOverEvent), e);
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _contentWrapper), 'mouseout', function (e) {
	    return _this5.emit(_classStaticPrivateFieldSpecGet(MapPopup, MapPopup, _onMouseOutEvent), e);
	  });
	  this.bindElement = bindElement;
	  babelHelpers.classPrivateFieldGet(this, _popup).setContent(babelHelpers.classPrivateFieldGet(this, _contentWrapper));
	}
	function _extractLatLon2(address) {
	  var result = null;
	  var lat;
	  var lon;
	  if (address.latitude && address.longitude) {
	    lat = address.latitude;
	    lon = address.longitude;
	  } else if (address.location && address.location.latitude && address.location.longitude) {
	    lat = address.location.latitude;
	    lon = address.location.longitude;
	  }
	  if (lat && lat !== '0' && lon && lon !== '0') {
	    result = [lat, lon];
	  }
	  return result;
	}
	function _convertAddressToLocation2(address) {
	  var _this6 = this;
	  var useUserLocation = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	  return new Promise(function (resolve) {
	    if (useUserLocation) {
	      resolve(babelHelpers.classPrivateFieldGet(_this6, _userLocationPoint) && babelHelpers.classPrivateFieldGet(_this6, _mode) !== location_core.ControlMode.view ? new location_core.Location({
	        latitude: babelHelpers.classPrivateFieldGet(_this6, _userLocationPoint).latitude,
	        longitude: babelHelpers.classPrivateFieldGet(_this6, _userLocationPoint).longitude
	      }) : null);
	      return;
	    }
	    if (address) {
	      var latLon = _classPrivateMethodGet$2(_this6, _extractLatLon, _extractLatLon2).call(_this6, address);
	      if (latLon) {
	        resolve(new location_core.Location({
	          latitude: latLon[0],
	          longitude: latLon[1],
	          type: address.getType()
	        }));
	        return;
	      }
	    }
	    resolve(null);
	  });
	}
	function _setLocationInternal2(location) {
	  if (babelHelpers.classPrivateFieldGet(this, _map)) {
	    babelHelpers.classPrivateFieldGet(this, _map).location = location;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _gallery)) {
	    babelHelpers.classPrivateFieldGet(this, _gallery).location = location;
	  }
	}
	function _renderMap2(_ref) {
	  var location = _ref.location;
	  return babelHelpers.classPrivateFieldGet(this, _map).render({
	    mapContainer: babelHelpers.classPrivateFieldGet(this, _mapInnerContainer),
	    location: location,
	    mode: babelHelpers.classPrivateFieldGet(this, _mode)
	  });
	}
	var _onChangedEvent = {
	  writable: true,
	  value: 'onChanged'
	};
	var _onMouseOverEvent = {
	  writable: true,
	  value: 'onMouseOver'
	};
	var _onMouseOutEvent = {
	  writable: true,
	  value: 'onMouseOut'
	};
	var _onShowedEvent = {
	  writable: true,
	  value: 'onShow'
	};
	var _onClosedEvent = {
	  writable: true,
	  value: 'onClose'
	};

	/**
	 * Base class for the address widget feature
	 */
	var BaseFeature = /*#__PURE__*/function () {
	  function BaseFeature(props) {
	    babelHelpers.classCallCheck(this, BaseFeature);
	    babelHelpers.defineProperty(this, "_saveResourceStrategy", false);
	    this._saveResourceStrategy = props.saveResourceStrategy;
	  }
	  babelHelpers.createClass(BaseFeature, [{
	    key: "render",
	    value: function render(props) {
	      throw new location_core.MethodNotImplemented('Method render must be implemented');
	    }
	  }, {
	    key: "setAddressWidget",
	    value: function setAddressWidget(addressWidget) {
	      throw new location_core.MethodNotImplemented('Method render must be implemented');
	    }
	  }, {
	    key: "setAddress",
	    value: function setAddress(address) {
	      throw new location_core.MethodNotImplemented('Method set address must be implemented');
	    }
	  }, {
	    key: "setMode",
	    value: function setMode(mode) {}
	  }, {
	    key: "destroy",
	    value: function destroy() {}
	  }, {
	    key: "resetView",
	    value: function resetView() {}
	  }]);
	  return BaseFeature;
	}();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Props for the address widget constructor
	 */
	var _mode$1 = /*#__PURE__*/new WeakMap();
	var _state = /*#__PURE__*/new WeakMap();
	var _address$2 = /*#__PURE__*/new WeakMap();
	var _addressFormat$2 = /*#__PURE__*/new WeakMap();
	var _languageId = /*#__PURE__*/new WeakMap();
	var _features = /*#__PURE__*/new WeakMap();
	var _inputNode = /*#__PURE__*/new WeakMap();
	var _controlWrapper = /*#__PURE__*/new WeakMap();
	var _destroyed = /*#__PURE__*/new WeakMap();
	var _isAddressChangedByFeature = /*#__PURE__*/new WeakMap();
	var _isInputNodeValueUpdated = /*#__PURE__*/new WeakMap();
	var _needWarmBackendAfterAddressChanged = /*#__PURE__*/new WeakMap();
	var _locationRepository$1 = /*#__PURE__*/new WeakMap();
	var _addFeature = /*#__PURE__*/new WeakSet();
	var _executeFeatureMethod = /*#__PURE__*/new WeakSet();
	var _emitOnAddressChanged = /*#__PURE__*/new WeakSet();
	var _warmBackendAfterAddressChanged = /*#__PURE__*/new WeakSet();
	var _onInputFocus = /*#__PURE__*/new WeakSet();
	var _convertAddressToString$1 = /*#__PURE__*/new WeakSet();
	var _setInputValue = /*#__PURE__*/new WeakSet();
	var _onInputFocusOut = /*#__PURE__*/new WeakSet();
	var _storeAsLastAddress = /*#__PURE__*/new WeakSet();
	var _destroyFeatures = /*#__PURE__*/new WeakSet();
	/**
	 * Address widget
	 */
	var Address = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Address, _EventEmitter);
	  /* If address was changed by user */

	  /* If state of the widget was changed */

	  /* Any feature-related events */

	  /**
	   * Constructor
	   * @param {AddressConstructorProps} props
	   */
	  function Address(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Address);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Address).call(this));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _destroyFeatures);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _storeAsLastAddress);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _onInputFocusOut);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _setInputValue);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _convertAddressToString$1);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _onInputFocus);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _warmBackendAfterAddressChanged);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _emitOnAddressChanged);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _executeFeatureMethod);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _addFeature);
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _mode$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _state, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _address$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _addressFormat$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _languageId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _features, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _inputNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _controlWrapper, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _destroyed, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _isAddressChangedByFeature, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _isInputNodeValueUpdated, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _needWarmBackendAfterAddressChanged, {
	      writable: true,
	      value: true
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _locationRepository$1, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Location.Widget.Address');
	    if (!(props.addressFormat instanceof location_core.Format)) {
	      BX.debug('addressFormat must be instance of Format');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _addressFormat$2, props.addressFormat);
	    if (props.address && !(props.address instanceof location_core.Address)) {
	      BX.debug('address must be instance of Address');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _address$2, props.address || null);
	    if (!location_core.ControlMode.isValid(props.mode)) {
	      BX.debug('mode must be valid ControlMode');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _mode$1, props.mode);
	    if (!main_core.Type.isString(props.languageId)) {
	      throw new TypeError('props.languageId must be type of string');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId, props.languageId);
	    if (props.features) {
	      if (!main_core.Type.isArray(props.features)) {
	        throw new TypeError('features must be an array');
	      }
	      props.features.forEach(function (feature) {
	        _classPrivateMethodGet$3(babelHelpers.assertThisInitialized(_this), _addFeature, _addFeature2).call(babelHelpers.assertThisInitialized(_this), feature);
	      });
	    }
	    if (main_core.Type.isBoolean(props.needWarmBackendAfterAddressChanged)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _needWarmBackendAfterAddressChanged, props.needWarmBackendAfterAddressChanged);
	    }
	    if (props.locationRepository instanceof location_core.LocationRepository) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _locationRepository$1, props.locationRepository);
	    } else if (babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _needWarmBackendAfterAddressChanged)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _locationRepository$1, new location_core.LocationRepository());
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _state, State.INITIAL);
	    return _this;
	  }

	  /**
	   * @param {AddressEntity} address
	   * @param {BaseFeature} sourceFeature
	   * @param {Array} excludeFeatures
	   * @param {Object} options
	   * @internal
	   */
	  babelHelpers.createClass(Address, [{
	    key: "setAddressByFeature",
	    value: function setAddressByFeature(address, sourceFeature) {
	      var excludeFeatures = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
	      var options = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
	      var addressId = babelHelpers.classPrivateFieldGet(this, _address$2) ? babelHelpers.classPrivateFieldGet(this, _address$2).id : 0;
	      if (address && !address.getFieldValue(location_core.AddressType.ADDRESS_LINE_1) && babelHelpers.classPrivateFieldGet(this, _addressFormat$2).isTemplateExists(location_core.FormatTemplateType.ADDRESS_LINE_1)) {
	        address.setFieldValue(location_core.AddressType.ADDRESS_LINE_1, location_core.AddressStringConverter.convertAddressToStringTemplate(address, babelHelpers.classPrivateFieldGet(this, _addressFormat$2).getTemplate(location_core.FormatTemplateType.ADDRESS_LINE_1), location_core.AddressStringConverter.CONTENT_TYPE_TEXT, null, babelHelpers.classPrivateFieldGet(this, _addressFormat$2)));
	      }
	      babelHelpers.classPrivateFieldSet(this, _address$2, address);
	      var storeAsLastAddress = options.hasOwnProperty('storeAsLastAddress') ? options.storeAsLastAddress : true;
	      if (storeAsLastAddress) {
	        _classPrivateMethodGet$3(this, _storeAsLastAddress, _storeAsLastAddress2).call(this);
	      }
	      if (addressId > 0) {
	        babelHelpers.classPrivateFieldGet(this, _address$2).id = addressId;
	      }
	      babelHelpers.classPrivateFieldSet(this, _isAddressChangedByFeature, true);
	      _classPrivateMethodGet$3(this, _setInputValue, _setInputValue2).call(this, address);
	      _classPrivateMethodGet$3(this, _executeFeatureMethod, _executeFeatureMethod2).call(this, 'setAddress', [address], sourceFeature, excludeFeatures);
	      if (babelHelpers.classPrivateFieldGet(this, _state) !== State.DATA_INPUTTING) {
	        _classPrivateMethodGet$3(this, _emitOnAddressChanged, _emitOnAddressChanged2).call(this);
	      }
	    }
	  }, {
	    key: "emitFeatureEvent",
	    value: function emitFeatureEvent(featureEvent) {
	      this.emit(Address.onFeatureEvent, featureEvent);
	    }
	    /**
	     * Add feature to the widget
	     * @param {BaseFeature} feature
	     */
	  }, {
	    key: "onInputKeyup",
	    value: function onInputKeyup(e) {
	      switch (e.code) {
	        case 'Tab':
	        case 'Esc':
	        case 'Enter':
	        case 'NumpadEnter':
	          this.resetView();
	          break;
	      }
	    }
	  }, {
	    key: "onInputInput",
	    value: function onInputInput(e) {
	      babelHelpers.classPrivateFieldSet(this, _isInputNodeValueUpdated, true);
	    }
	  }, {
	    key: "resetView",
	    value: function resetView() {
	      _classPrivateMethodGet$3(this, _executeFeatureMethod, _executeFeatureMethod2).call(this, 'resetView');
	    }
	    /**
	     * Render Widget
	     * @param {AddressRenderProps} props
	     */
	  }, {
	    key: "render",
	    value: function render(props) {
	      if (!main_core.Type.isDomNode(props.controlWrapper)) {
	        BX.debug('props.controlWrapper  must be instance of Element');
	      }
	      babelHelpers.classPrivateFieldSet(this, _controlWrapper, props.controlWrapper);
	      if (babelHelpers.classPrivateFieldGet(this, _mode$1) === location_core.ControlMode.edit) {
	        if (!main_core.Type.isDomNode(props.inputNode)) {
	          BX.debug('props.inputNode  must be instance of Element');
	        }
	        babelHelpers.classPrivateFieldSet(this, _inputNode, props.inputNode);
	        _classPrivateMethodGet$3(this, _setInputValue, _setInputValue2).call(this, babelHelpers.classPrivateFieldGet(this, _address$2));
	      }
	      _classPrivateMethodGet$3(this, _executeFeatureMethod, _executeFeatureMethod2).call(this, 'render', [props]);

	      // We can prevent these events in features if need
	      if (babelHelpers.classPrivateFieldGet(this, _mode$1) === location_core.ControlMode.edit) {
	        main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'focus', _classPrivateMethodGet$3(this, _onInputFocus, _onInputFocus2).bind(this));
	        main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'focusout', _classPrivateMethodGet$3(this, _onInputFocusOut, _onInputFocusOut2).bind(this));
	        main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'keyup', this.onInputKeyup.bind(this));
	        main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'input', this.onInputInput.bind(this));
	      }
	    }
	  }, {
	    key: "setStateByFeature",
	    value: function setStateByFeature(state) {
	      babelHelpers.classPrivateFieldSet(this, _state, state);
	      this.emit(Address.onStateChangedEvent, {
	        state: state
	      });
	    }
	  }, {
	    key: "subscribeOnStateChangedEvent",
	    value: function subscribeOnStateChangedEvent(listener) {
	      this.subscribe(Address.onStateChangedEvent, listener);
	    }
	  }, {
	    key: "subscribeOnAddressChangedEvent",
	    value: function subscribeOnAddressChangedEvent(listener) {
	      this.subscribe(Address.onAddressChangedEvent, listener);
	    }
	  }, {
	    key: "subscribeOnFeatureEvent",
	    value: function subscribeOnFeatureEvent(listener) {
	      this.subscribe(Address.onFeatureEvent, listener);
	    }
	  }, {
	    key: "subscribeOnErrorEvent",
	    value: function subscribeOnErrorEvent(listener) {
	      location_core.ErrorPublisher.getInstance().subscribe(listener);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _destroyed)) {
	        return;
	      }
	      main_core.Event.unbindAll(this);
	      main_core.Event.unbind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'focus', _classPrivateMethodGet$3(this, _onInputFocus, _onInputFocus2));
	      main_core.Event.unbind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'focusout', _classPrivateMethodGet$3(this, _onInputFocusOut, _onInputFocusOut2));
	      main_core.Event.unbind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'keyup', this.onInputKeyup);
	      main_core.Event.unbind(babelHelpers.classPrivateFieldGet(this, _inputNode), 'input', this.onInputInput);
	      _classPrivateMethodGet$3(this, _executeFeatureMethod, _executeFeatureMethod2).call(this, 'destroy');
	      _classPrivateMethodGet$3(this, _destroyFeatures, _destroyFeatures2).call(this);
	      babelHelpers.classPrivateFieldSet(this, _destroyed, true);
	    }
	  }, {
	    key: "isDestroyed",
	    value: function isDestroyed() {
	      return babelHelpers.classPrivateFieldGet(this, _destroyed);
	    }
	  }, {
	    key: "features",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _features);
	    }
	  }, {
	    key: "controlWrapper",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _controlWrapper);
	    }
	  }, {
	    key: "inputNode",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _inputNode);
	    }
	  }, {
	    key: "address",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _address$2);
	    },
	    set: function set(address) {
	      if (address && !(address instanceof location_core.Address)) {
	        BX.debug('address must be instance of Address');
	      }
	      babelHelpers.classPrivateFieldSet(this, _address$2, address);
	      _classPrivateMethodGet$3(this, _storeAsLastAddress, _storeAsLastAddress2).call(this);
	      _classPrivateMethodGet$3(this, _executeFeatureMethod, _executeFeatureMethod2).call(this, 'setAddress', [address]);
	      babelHelpers.classPrivateFieldSet(this, _isInputNodeValueUpdated, false);
	      babelHelpers.classPrivateFieldSet(this, _isAddressChangedByFeature, false);
	      _classPrivateMethodGet$3(this, _setInputValue, _setInputValue2).call(this, address);
	    }
	  }, {
	    key: "mode",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _mode$1);
	    },
	    set: function set(mode) {
	      if (!location_core.ControlMode.isValid(mode)) {
	        BX.debug('mode must be valid ControlMode');
	      }
	      babelHelpers.classPrivateFieldSet(this, _mode$1, mode);
	      _classPrivateMethodGet$3(this, _executeFeatureMethod, _executeFeatureMethod2).call(this, 'setMode', [mode]);
	    }
	  }, {
	    key: "state",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _state);
	    }
	  }, {
	    key: "addressFormat",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _addressFormat$2);
	    }
	  }]);
	  return Address;
	}(main_core_events.EventEmitter);
	function _addFeature2(feature) {
	  if (!(feature instanceof BaseFeature)) {
	    BX.debug('feature must be instance of BaseFeature');
	  }
	  feature.setAddressWidget(this);
	  babelHelpers.classPrivateFieldGet(this, _features).push(feature);
	}
	function _executeFeatureMethod2(method) {
	  var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	  var sourceFeature = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	  var excludeFeatures = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : [];
	  var result;
	  var _iterator = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _features)),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var feature = _step.value;
	      var isExcluded = false;
	      var _iterator2 = _createForOfIteratorHelper(excludeFeatures),
	        _step2;
	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var excludeFeature = _step2.value;
	          if (feature instanceof excludeFeature) {
	            isExcluded = true;
	            break;
	          }
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	      if (!isExcluded && feature !== sourceFeature) {
	        result = feature[method].apply(feature, params);
	      }
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  return result;
	}
	function _emitOnAddressChanged2() {
	  this.emit(Address.onAddressChangedEvent, {
	    address: babelHelpers.classPrivateFieldGet(this, _address$2)
	  });
	  if (babelHelpers.classPrivateFieldGet(this, _address$2) && babelHelpers.classPrivateFieldGet(this, _needWarmBackendAfterAddressChanged)) {
	    _classPrivateMethodGet$3(this, _warmBackendAfterAddressChanged, _warmBackendAfterAddressChanged2).call(this, babelHelpers.classPrivateFieldGet(this, _address$2));
	  }
	}
	function _warmBackendAfterAddressChanged2(address) {
	  if (address.location !== null && address.location.id <= 0) {
	    babelHelpers.classPrivateFieldGet(this, _locationRepository$1).findParents(address.location);
	  }
	}
	function _onInputFocus2(e) {
	  var value = babelHelpers.classPrivateFieldGet(this, _inputNode).value;
	  if (value.length > 0) {
	    BX.setCaretPosition(babelHelpers.classPrivateFieldGet(this, _inputNode), value.length);
	  }
	}
	function _convertAddressToString2$1(address, templateType) {
	  var result = '';
	  if (address) {
	    if (!babelHelpers.classPrivateFieldGet(this, _addressFormat$2).isTemplateExists(templateType)) {
	      console.error("Address format \"".concat(babelHelpers.classPrivateFieldGet(this, _addressFormat$2).code, "\" does not have a template \"").concat(templateType, "\""));
	      return '';
	    }
	    result = location_core.AddressStringConverter.convertAddressToStringTemplate(address, babelHelpers.classPrivateFieldGet(this, _addressFormat$2).getTemplate(templateType), location_core.AddressStringConverter.CONTENT_TYPE_TEXT, ', ', babelHelpers.classPrivateFieldGet(this, _addressFormat$2));
	  }
	  return result;
	}
	function _setInputValue2(address) {
	  if (babelHelpers.classPrivateFieldGet(this, _inputNode)) {
	    var shortAddressString = _classPrivateMethodGet$3(this, _convertAddressToString$1, _convertAddressToString2$1).call(this, address, location_core.FormatTemplateType.AUTOCOMPLETE);
	    var fullAddressString = _classPrivateMethodGet$3(this, _convertAddressToString$1, _convertAddressToString2$1).call(this, address, location_core.FormatTemplateType.DEFAULT);
	    babelHelpers.classPrivateFieldGet(this, _inputNode).value = shortAddressString.trim() !== '' ? shortAddressString : fullAddressString;
	    babelHelpers.classPrivateFieldGet(this, _inputNode).title = fullAddressString;
	    var selectionStart = babelHelpers.classPrivateFieldGet(this, _inputNode).selectionStart;
	    var selectionEnd = shortAddressString.length;
	    babelHelpers.classPrivateFieldGet(this, _inputNode).setSelectionRange(selectionStart, selectionEnd);
	  }
	}
	function _onInputFocusOut2(e) {
	  // Seems that we don't have any autocompleter feature
	  if (babelHelpers.classPrivateFieldGet(this, _isInputNodeValueUpdated) && !babelHelpers.classPrivateFieldGet(this, _isAddressChangedByFeature)) {
	    var value = babelHelpers.classPrivateFieldGet(this, _inputNode).value.trim();
	    var address = new location_core.Address({
	      languageId: babelHelpers.classPrivateFieldGet(this, _languageId)
	    });
	    address.setFieldValue(babelHelpers.classPrivateFieldGet(this, _addressFormat$2).fieldForUnRecognized, value);
	    this.address = address;
	    _classPrivateMethodGet$3(this, _emitOnAddressChanged, _emitOnAddressChanged2).call(this);
	  }
	  babelHelpers.classPrivateFieldSet(this, _isInputNodeValueUpdated, false);
	  babelHelpers.classPrivateFieldSet(this, _isAddressChangedByFeature, false);
	}
	function _storeAsLastAddress2() {
	  if (babelHelpers.classPrivateFieldGet(this, _address$2) && babelHelpers.classPrivateFieldGet(this, _address$2).fieldCollection && babelHelpers.classPrivateFieldGet(this, _address$2).fieldCollection.isFieldExists(location_core.AddressType.LOCALITY)) {
	    location_core.Storage.getInstance().lastAddress = babelHelpers.classPrivateFieldGet(this, _address$2);
	  }
	}
	function _destroyFeatures2() {
	  babelHelpers.classPrivateFieldGet(this, _features).splice(0, babelHelpers.classPrivateFieldGet(this, _features).length);
	}
	babelHelpers.defineProperty(Address, "onAddressChangedEvent", 'onAddressChanged');
	babelHelpers.defineProperty(Address, "onStateChangedEvent", 'onStateChanged');
	babelHelpers.defineProperty(Address, "onFeatureEvent", 'onFeatureEvent');

	var _templateObject$2, _templateObject2$2, _templateObject3$1;
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _node = /*#__PURE__*/new WeakMap();
	var _leftItemNodeContainer = /*#__PURE__*/new WeakMap();
	var _rightItemNodeContainer = /*#__PURE__*/new WeakMap();
	var MenuBottom = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(MenuBottom, _EventEmitter);
	  function MenuBottom() {
	    var _this;
	    babelHelpers.classCallCheck(this, MenuBottom);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MenuBottom).call(this));
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _leftItemNodeContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _rightItemNodeContainer, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Location.Widget.Autocomplete.MenuBottom');
	    return _this;
	  }
	  babelHelpers.createClass(MenuBottom, [{
	    key: "render",
	    value: function render() {
	      babelHelpers.classPrivateFieldSet(this, _leftItemNodeContainer, main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"location-map-popup-item--info-left\"></div>"]))));
	      babelHelpers.classPrivateFieldSet(this, _rightItemNodeContainer, main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<div></div>"]))));
	      babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t<span class=\"location-map-popup-item--info\"> \t\t\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</span>\t\t\t\n\t\t\t</div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _leftItemNodeContainer), babelHelpers.classPrivateFieldGet(this, _rightItemNodeContainer)));
	      return babelHelpers.classPrivateFieldGet(this, _node);
	    }
	  }, {
	    key: "setRightItemNode",
	    value: function setRightItemNode(node) {
	      while (babelHelpers.classPrivateFieldGet(this, _rightItemNodeContainer).firstChild) {
	        babelHelpers.classPrivateFieldGet(this, _rightItemNodeContainer).removeChild(babelHelpers.classPrivateFieldGet(this, _rightItemNodeContainer).firstChild);
	      }
	      babelHelpers.classPrivateFieldGet(this, _rightItemNodeContainer).appendChild(node);
	    }
	  }, {
	    key: "setLeftItemNode",
	    value: function setLeftItemNode(node) {
	      while (babelHelpers.classPrivateFieldGet(this, _leftItemNodeContainer).firstChild) {
	        babelHelpers.classPrivateFieldGet(this, _leftItemNodeContainer).removeChild(babelHelpers.classPrivateFieldGet(this, _leftItemNodeContainer).firstChild);
	      }
	      babelHelpers.classPrivateFieldGet(this, _leftItemNodeContainer).appendChild(node);
	    }
	  }]);
	  return MenuBottom;
	}(main_core_events.EventEmitter);

	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _bottom = /*#__PURE__*/new WeakMap();
	var Menu = /*#__PURE__*/function (_MainMenu) {
	  babelHelpers.inherits(Menu, _MainMenu);
	  function Menu(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Menu);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Menu).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "choseItemIdx", -1);
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _bottom, {
	      writable: true,
	      value: void 0
	    });
	    var elRect = options.bindElement.getBoundingClientRect();
	    _this.popupWindow.setMaxWidth(elRect.width);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _bottom, new MenuBottom());
	    _this.layout.menuContainer.appendChild(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _bottom).render());
	    return _this;
	  }
	  babelHelpers.createClass(Menu, [{
	    key: "isMenuEmpty",
	    value: function isMenuEmpty() {
	      return this.menuItems.length <= 0;
	    }
	  }, {
	    key: "isChoseLastItem",
	    value: function isChoseLastItem() {
	      return this.choseItemIdx >= this.menuItems.length - 1;
	    }
	  }, {
	    key: "isChoseFirstItem",
	    value: function isChoseFirstItem() {
	      return this.choseItemIdx === 0;
	    }
	  }, {
	    key: "isItemChosen",
	    value: function isItemChosen() {
	      return this.choseItemIdx >= 0;
	    }
	  }, {
	    key: "isDestroyed",
	    value: function isDestroyed() {
	      return this.getPopupWindow().isDestroyed();
	    }
	  }, {
	    key: "isItemExist",
	    value: function isItemExist(index) {
	      return typeof this.menuItems[index] !== 'undefined';
	    }
	  }, {
	    key: "getChosenItem",
	    value: function getChosenItem() {
	      var result = null;
	      if (this.isItemChosen() && this.isItemExist(this.choseItemIdx)) {
	        result = this.menuItems[this.choseItemIdx];
	      }
	      return result;
	    }
	  }, {
	    key: "chooseNextItem",
	    value: function chooseNextItem() {
	      if (!this.isMenuEmpty() && !this.isChoseLastItem()) {
	        this.chooseItem(this.choseItemIdx + 1);
	      }
	      return this.getChosenItem();
	    }
	  }, {
	    key: "choosePrevItem",
	    value: function choosePrevItem() {
	      if (!this.isMenuEmpty() && !this.isChoseFirstItem()) {
	        this.chooseItem(this.choseItemIdx - 1);
	      }
	      return this.getChosenItem();
	    }
	  }, {
	    key: "highlightItem",
	    value: function highlightItem(index) {
	      if (this.isItemExist(index)) {
	        var item = this.getChosenItem();
	        if (item && item.layout.item) {
	          item.layout.item.classList.add('highlighted');
	        }
	      }
	    }
	  }, {
	    key: "unHighlightItem",
	    value: function unHighlightItem(index) {
	      if (this.isItemExist(index)) {
	        var item = this.getChosenItem();
	        if (item && item.layout.item) {
	          item.layout.item.classList.remove('highlighted');
	        }
	      }
	    }
	  }, {
	    key: "chooseItem",
	    value: function chooseItem(index) {
	      var idx = index;
	      if (idx < 0) {
	        idx = this.menuItems.length - 1;
	      } else if (idx > this.menuItems.length - 1) {
	        idx = 0;
	      }
	      this.unHighlightItem(this.choseItemIdx);
	      this.choseItemIdx = idx;
	      this.highlightItem(this.choseItemIdx);
	    }
	  }, {
	    key: "clearItems",
	    value: function clearItems() {
	      while (this.menuItems.length > 0) {
	        this.removeMenuItem(this.menuItems[0].id);
	      }
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.getPopupWindow().isShown();
	    }
	  }, {
	    key: "setBottomRightItemNode",
	    value: function setBottomRightItemNode(node) {
	      babelHelpers.classPrivateFieldGet(this, _bottom).setRightItemNode(node);
	    }
	  }, {
	    key: "setBottomLeftItemNode",
	    value: function setBottomLeftItemNode(node) {
	      babelHelpers.classPrivateFieldGet(this, _bottom).setLeftItemNode(node);
	    }
	  }]);
	  return Menu;
	}(main_popup.Menu);

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }
	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$6(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$6(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$6(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess$1(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess$1(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _inputNode$1 = /*#__PURE__*/new WeakMap();
	var _menuNode = /*#__PURE__*/new WeakMap();
	var _menu = /*#__PURE__*/new WeakMap();
	var _locationList = /*#__PURE__*/new WeakMap();
	var _createMenu = /*#__PURE__*/new WeakSet();
	var _isAddressOfSameLocation = /*#__PURE__*/new WeakSet();
	var _createMenuItem = /*#__PURE__*/new WeakSet();
	var _onItemSelect = /*#__PURE__*/new WeakSet();
	var _getLocationFromList = /*#__PURE__*/new WeakSet();
	var Prompt = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Prompt, _EventEmitter);
	  /** Element */

	  /** Element */

	  /** {Menu} */

	  /** {Array<Location>} */

	  function Prompt(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Prompt);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Prompt).call(this, props));
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _getLocationFromList);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _onItemSelect);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _createMenuItem);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _isAddressOfSameLocation);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _createMenu);
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _inputNode$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _menuNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _menu, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _locationList, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Location.Widget.Prompt');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _inputNode$1, props.inputNode);
	    if (props.menuNode) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _menuNode, props.menuNode);
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Prompt, [{
	    key: "getMenu",
	    value: function getMenu() {
	      if (!babelHelpers.classPrivateFieldGet(this, _menu) || babelHelpers.classPrivateFieldGet(this, _menu).isDestroyed()) {
	        babelHelpers.classPrivateFieldSet(this, _menu, _classPrivateMethodGet$4(this, _createMenu, _createMenu2).call(this));
	      }
	      return babelHelpers.classPrivateFieldGet(this, _menu);
	    }
	    /**
	     * Show menu with list of locations
	     * @param {array} locationsList
	     * @param {string} searchPhrase
	     * @returns void
	     */
	  }, {
	    key: "show",
	    value: function show(locationsList, searchPhrase) {
	      if (locationsList.length > 0) {
	        this.setMenuItems(locationsList, searchPhrase);
	        this.getMenu().show();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.getMenu().close();
	    }
	    /**
	     * @param {array<Location>} locationsList
	     * @param {string} searchPhrase
	     * @param {Address} address
	     * @returns {*}
	     */
	  }, {
	    key: "setMenuItems",
	    value: function setMenuItems(locationsList, searchPhrase, address) {
	      var _this2 = this;
	      this.getMenu().clearItems();
	      if (Array.isArray(locationsList)) {
	        babelHelpers.classPrivateFieldSet(this, _locationList, locationsList.slice());
	        var showFlatList = !address || !address.getFieldValue(location_core.AddressType.LOCALITY) || !_classStaticPrivateMethodGet(Prompt, Prompt, _hasLocationWithLocality).call(Prompt, babelHelpers.classPrivateFieldGet(this, _locationList));
	        if (showFlatList) {
	          locationsList.forEach(function (location, index) {
	            _this2.getMenu().addMenuItem(_classPrivateMethodGet$4(_this2, _createMenuItem, _createMenuItem2).call(_this2, index, location, searchPhrase));
	          });
	        } else {
	          locationsList.forEach(function (location, index) {
	            if (_classPrivateMethodGet$4(_this2, _isAddressOfSameLocation, _isAddressOfSameLocation2).call(_this2, address, location)) {
	              _this2.getMenu().addMenuItem(_classPrivateMethodGet$4(_this2, _createMenuItem, _createMenuItem2).call(_this2, index, location, searchPhrase));
	            }
	          });
	          var isSeparatorSet = false;
	          locationsList.forEach(function (location, index) {
	            if (!_classPrivateMethodGet$4(_this2, _isAddressOfSameLocation, _isAddressOfSameLocation2).call(_this2, address, location)) {
	              if (!isSeparatorSet) {
	                _this2.getMenu().addMenuItem({
	                  html: main_core.Loc.getMessage('LOCATION_WIDGET_PROMPT_IN_OTHER_CITY'),
	                  delimiter: true
	                });
	              }
	              _this2.getMenu().addMenuItem(_classPrivateMethodGet$4(_this2, _createMenuItem, _createMenuItem2).call(_this2, index, location, searchPhrase));
	              isSeparatorSet = true;
	            }
	          });
	        }
	      }
	    }
	  }, {
	    key: "choosePrevItem",
	    value: function choosePrevItem() {
	      var isRecursive = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var result = null;
	      var item = this.getMenu().choosePrevItem();
	      if (item) {
	        if (item.delimiter && item.delimiter === true) {
	          result = isRecursive ? this.getMenu().chooseNextItem() : this.choosePrevItem(true);
	        } else {
	          result = _classPrivateMethodGet$4(this, _getLocationFromList, _getLocationFromList2).call(this, this.getMenu().choseItemIdx);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "chooseNextItem",
	    value: function chooseNextItem() {
	      var result = null;
	      var item = this.getMenu().chooseNextItem();
	      if (item) {
	        if (item.delimiter && item.delimiter === true) {
	          result = this.chooseNextItem();
	        } else {
	          result = _classPrivateMethodGet$4(this, _getLocationFromList, _getLocationFromList2).call(this, this.getMenu().choseItemIdx);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "isItemChosen",
	    value: function isItemChosen() {
	      return this.getMenu().isItemChosen();
	    }
	  }, {
	    key: "getChosenItem",
	    value: function getChosenItem() {
	      var result = null;
	      var menuItem = this.getMenu().getChosenItem();
	      if (menuItem && menuItem.id) {
	        result = _classPrivateMethodGet$4(this, _getLocationFromList, _getLocationFromList2).call(this, this.getMenu().choseItemIdx);
	      }
	      return result;
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.getMenu().isShown();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _menu)) {
	        babelHelpers.classPrivateFieldGet(this, _menu).destroy();
	        babelHelpers.classPrivateFieldSet(this, _menu, null);
	      }
	      babelHelpers.classPrivateFieldSet(this, _locationList, null);
	    }
	  }], [{
	    key: "createMenuItemText",
	    value: function createMenuItemText(locationName, searchPhrase, location) {
	      var result = "\n\t\t<div>\n\t\t\t<strong>".concat(locationName, "</strong>\n\t\t</div>");
	      var clarification;
	      if (location.getFieldValue(location_core.LocationType.TMP_TYPE_CLARIFICATION)) {
	        clarification = location.getFieldValue(location_core.LocationType.TMP_TYPE_CLARIFICATION);
	        if (clarification) {
	          if (location.getFieldValue(location_core.LocationType.TMP_TYPE_HINT)) {
	            clarification += " <i>(".concat(location.getFieldValue(location_core.LocationType.TMP_TYPE_HINT), ")</i>");
	          }
	          result += "<div>".concat(clarification, "</div>");
	        }
	      }
	      return '<div data-role="location-widget-menu-item" tabindex="-1">' + result + '</div>';
	    }
	  }]);
	  return Prompt;
	}(main_core_events.EventEmitter);
	function _createMenu2() {
	  return new Menu({
	    bindElement: babelHelpers.classPrivateFieldGet(this, _menuNode) ? babelHelpers.classPrivateFieldGet(this, _menuNode) : babelHelpers.classPrivateFieldGet(this, _inputNode$1),
	    autoHide: false,
	    closeByEsc: true,
	    className: 'location-widget-prompt-menu'
	  });
	}
	function _isAddressOfSameLocation2(address, location) {
	  return address && address.getFieldValue(location_core.AddressType.LOCALITY) && location && location.address && location.address.getFieldValue(location_core.AddressType.LOCALITY) && _classStaticPrivateMethodGet(Prompt, Prompt, _getAddressPossibleLocalities).call(Prompt, location.address).includes(address.getFieldValue(location_core.AddressType.LOCALITY));
	}
	function _getAddressPossibleLocalities(address) {
	  var result = [];
	  if (address.getFieldValue(location_core.AddressType.LOCALITY)) {
	    result.push(address.getFieldValue(location_core.AddressType.LOCALITY));
	  }

	  /**
	   * Address break-down formed on frontend is very inaccurate so we can't rely only on the locality type field
	   * @see #142094
	   */
	  if (address.getFieldValue(location_core.AddressType.ADM_LEVEL_1)) {
	    result.push(address.getFieldValue(location_core.AddressType.ADM_LEVEL_1));
	  }
	  return result;
	}
	function _hasLocationWithLocality(locationsList) {
	  var _iterator = _createForOfIteratorHelper$1(locationsList),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var location = _step.value;
	      if (location.address && location.address.getFieldValue(location_core.AddressType.LOCALITY)) {
	        return true;
	      }
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  return false;
	}
	function _createMenuItem2(index, location, searchPhrase) {
	  var _this3 = this;
	  return {
	    id: index,
	    title: location.name,
	    html: Prompt.createMenuItemText(location.name, searchPhrase, location),
	    onclick: function onclick(event, item) {
	      _classPrivateMethodGet$4(_this3, _onItemSelect, _onItemSelect2).call(_this3, index);
	      _this3.close();
	    }
	  };
	}
	function _onItemSelect2(index) {
	  var location = _classPrivateMethodGet$4(this, _getLocationFromList, _getLocationFromList2).call(this, index);
	  if (location) {
	    this.emit(Prompt.onItemSelectedEvent, {
	      location: location
	    });
	  }
	}
	function _getLocationFromList2(index) {
	  var result = null;
	  if (babelHelpers.classPrivateFieldGet(this, _locationList)[index] !== undefined) {
	    result = babelHelpers.classPrivateFieldGet(this, _locationList)[index];
	  }
	  if (!result) {
	    BX.debug("Location with index ".concat(index, " was not found"));
	  }
	  return result;
	}
	babelHelpers.defineProperty(Prompt, "onItemSelectedEvent", 'onItemSelected');

	function _classPrivateMethodInitSpec$5(obj, privateSet) { _checkPrivateRedeclaration$7(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$6(obj, privateMap, value) { _checkPrivateRedeclaration$7(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$7(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$5(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _input = /*#__PURE__*/new WeakMap();
	var _value = /*#__PURE__*/new WeakMap();
	var _pureAddressString = /*#__PURE__*/new WeakMap();
	var _addressFormat$3 = /*#__PURE__*/new WeakMap();
	var _actualizePureString = /*#__PURE__*/new WeakSet();
	var _isPureAddressStringModified = /*#__PURE__*/new WeakSet();
	var _convertAddressToString$2 = /*#__PURE__*/new WeakSet();
	var AddressString$1 = /*#__PURE__*/function () {
	  // Input node element

	  // Address string value

	  // Address string as it was without custom inputs

	  function AddressString(input, addressFormat, _address) {
	    babelHelpers.classCallCheck(this, AddressString);
	    _classPrivateMethodInitSpec$5(this, _convertAddressToString$2);
	    _classPrivateMethodInitSpec$5(this, _isPureAddressStringModified);
	    _classPrivateMethodInitSpec$5(this, _actualizePureString);
	    _classPrivateFieldInitSpec$6(this, _input, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$6(this, _value, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$6(this, _pureAddressString, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$6(this, _addressFormat$3, {
	      writable: true,
	      value: null
	    });
	    if (!(input instanceof HTMLInputElement)) {
	      throw new TypeError('Wrong input type');
	    }
	    babelHelpers.classPrivateFieldSet(this, _input, input);
	    if (!(addressFormat instanceof location_core.Format)) {
	      throw new TypeError('Wrong addressFormat type');
	    }
	    babelHelpers.classPrivateFieldSet(this, _addressFormat$3, addressFormat);
	    if (_address && !(_address instanceof location_core.Address)) {
	      throw new TypeError('Wrong address type');
	    }
	    if (_address) {
	      this.setValueFromAddress(_address);
	    }
	  }

	  /**
	   *
	   * @param {string} value Address string value
	   * @param {boolean} isPureAddress Does it contain user input or not
	   */
	  babelHelpers.createClass(AddressString, [{
	    key: "setValue",
	    value: function setValue(value) {
	      var isPureAddress = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      babelHelpers.classPrivateFieldSet(this, _value, value);
	      babelHelpers.classPrivateFieldGet(this, _input).value = value;
	      if (isPureAddress) {
	        babelHelpers.classPrivateFieldSet(this, _pureAddressString, value);
	      }
	      _classPrivateMethodGet$5(this, _actualizePureString, _actualizePureString2).call(this);
	    }
	  }, {
	    key: "actualize",
	    value: function actualize() {
	      babelHelpers.classPrivateFieldSet(this, _value, babelHelpers.classPrivateFieldGet(this, _input).value);
	      _classPrivateMethodGet$5(this, _actualizePureString, _actualizePureString2).call(this);
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      return babelHelpers.classPrivateFieldGet(this, _value).trim() !== babelHelpers.classPrivateFieldGet(this, _input).value.trim();
	    }
	  }, {
	    key: "hasPureAddressString",
	    value: function hasPureAddressString() {
	      return babelHelpers.classPrivateFieldGet(this, _pureAddressString) !== '';
	    } // We suggest that user will input data after the address data
	  }, {
	    key: "setValueFromAddress",
	    value: function setValueFromAddress(address) {
	      var value = '';
	      if (address) {
	        value = _classPrivateMethodGet$5(this, _convertAddressToString$2, _convertAddressToString2$2).call(this, address, location_core.FormatTemplateType.AUTOCOMPLETE);
	        if (value.trim() === '') {
	          value = _classPrivateMethodGet$5(this, _convertAddressToString$2, _convertAddressToString2$2).call(this, address, location_core.FormatTemplateType.DEFAULT);
	        }
	      }
	      this.setValue(value, true);
	    }
	  }, {
	    key: "value",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _value);
	    }
	  }, {
	    key: "customTail",
	    get: function get() {
	      if (babelHelpers.classPrivateFieldGet(this, _pureAddressString) === '') {
	        return babelHelpers.classPrivateFieldGet(this, _value);
	      }
	      var result;
	      if (!_classPrivateMethodGet$5(this, _isPureAddressStringModified, _isPureAddressStringModified2).call(this)) {
	        result = babelHelpers.classPrivateFieldGet(this, _value).slice(babelHelpers.classPrivateFieldGet(this, _pureAddressString).length);
	      } else {
	        result = babelHelpers.classPrivateFieldGet(this, _value);
	      }
	      return result;
	    }
	  }]);
	  return AddressString;
	}();
	function _actualizePureString2() {
	  if (_classPrivateMethodGet$5(this, _isPureAddressStringModified, _isPureAddressStringModified2).call(this)) {
	    babelHelpers.classPrivateFieldSet(this, _pureAddressString, '');
	  }
	}
	function _isPureAddressStringModified2() {
	  return babelHelpers.classPrivateFieldGet(this, _value) === '' || babelHelpers.classPrivateFieldGet(this, _pureAddressString) === '' || babelHelpers.classPrivateFieldGet(this, _value).indexOf(babelHelpers.classPrivateFieldGet(this, _pureAddressString)) !== 0;
	}
	function _convertAddressToString2$2(address, templateType) {
	  if (!babelHelpers.classPrivateFieldGet(this, _addressFormat$3).isTemplateExists(templateType)) {
	    console.error("Address format \"".concat(babelHelpers.classPrivateFieldGet(this, _addressFormat$3).code, "\" does not have a template \"").concat(templateType, "\""));
	    return '';
	  }
	  return location_core.AddressStringConverter.convertAddressToStringTemplate(address, babelHelpers.classPrivateFieldGet(this, _addressFormat$3).getTemplate(templateType), location_core.AddressStringConverter.CONTENT_TYPE_TEXT, ', ', babelHelpers.classPrivateFieldGet(this, _addressFormat$3));
	}

	var _templateObject$3, _templateObject2$3;
	function _classPrivateMethodInitSpec$6(obj, privateSet) { _checkPrivateRedeclaration$8(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$7(obj, privateMap, value) { _checkPrivateRedeclaration$8(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$8(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet$1(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$2(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$1(descriptor, "get"); return _classApplyDescriptorGet$1(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$1(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$2(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$1(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodGet$6(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * @mixes EventEmitter
	 */
	var _address$3 = /*#__PURE__*/new WeakMap();
	var _addressString$1 = /*#__PURE__*/new WeakMap();
	var _languageId$1 = /*#__PURE__*/new WeakMap();
	var _addressFormat$4 = /*#__PURE__*/new WeakMap();
	var _sourceCode = /*#__PURE__*/new WeakMap();
	var _locationRepository$2 = /*#__PURE__*/new WeakMap();
	var _userLocationPoint$1 = /*#__PURE__*/new WeakMap();
	var _presetLocationsProvider = /*#__PURE__*/new WeakMap();
	var _prompt = /*#__PURE__*/new WeakMap();
	var _autocompleteService = /*#__PURE__*/new WeakMap();
	var _timerId = /*#__PURE__*/new WeakMap();
	var _inputNode$2 = /*#__PURE__*/new WeakMap();
	var _searchPhrase = /*#__PURE__*/new WeakMap();
	var _state$1 = /*#__PURE__*/new WeakMap();
	var _wasCleared = /*#__PURE__*/new WeakMap();
	var _isDestroyed = /*#__PURE__*/new WeakMap();
	var _isAutocompleteRequestStarted = /*#__PURE__*/new WeakMap();
	var _isNextAutocompleteRequestWaiting = /*#__PURE__*/new WeakMap();
	var _onLocationSelectTimerId = /*#__PURE__*/new WeakMap();
	var _onInputClick = /*#__PURE__*/new WeakSet();
	var _showPresetLocations = /*#__PURE__*/new WeakSet();
	var _createRightBottomMenuNode = /*#__PURE__*/new WeakSet();
	var _createLeftBottomMenuNode = /*#__PURE__*/new WeakSet();
	var _showMenu = /*#__PURE__*/new WeakSet();
	var _onInputFocusOut$1 = /*#__PURE__*/new WeakSet();
	var _onInputFocus$1 = /*#__PURE__*/new WeakSet();
	var _makeAutocompleteServiceParams = /*#__PURE__*/new WeakSet();
	var _onDocumentClick = /*#__PURE__*/new WeakSet();
	var _onPromptsReceived = /*#__PURE__*/new WeakSet();
	var _getShowOnMapHandler = /*#__PURE__*/new WeakSet();
	var _onPromptItemSelected = /*#__PURE__*/new WeakSet();
	var _setState = /*#__PURE__*/new WeakSet();
	var _fulfillSelection = /*#__PURE__*/new WeakSet();
	var _onAddressChangedEventEmit = /*#__PURE__*/new WeakSet();
	var _getLocationDetails = /*#__PURE__*/new WeakSet();
	var _convertStringToAddress = /*#__PURE__*/new WeakSet();
	var _onLocationSelect = /*#__PURE__*/new WeakSet();
	var _onInputKeyDown = /*#__PURE__*/new WeakSet();
	var _onInputKeyUp = /*#__PURE__*/new WeakSet();
	var _onInputPaste = /*#__PURE__*/new WeakSet();
	var _createOnLocationSelectTimer = /*#__PURE__*/new WeakSet();
	var _showPromptInner = /*#__PURE__*/new WeakSet();
	var _createTimer = /*#__PURE__*/new WeakSet();
	var Autocomplete = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Autocomplete, _EventEmitter);
	  /** {Address} */

	  /** {AddressString|null} */

	  /** {String} */

	  /** {Format} */

	  /** {String} */

	  /** {LocationRepository} */

	  /** {Point} */

	  /** {Function} */

	  /** {Prompt} */

	  /** {AutocompleteServiceBase} */

	  /** {number} */

	  /** {Element} */

	  function Autocomplete(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Autocomplete);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Autocomplete).call(this, props));
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _createTimer);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _showPromptInner);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _createOnLocationSelectTimer);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onInputPaste);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onInputKeyUp);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onInputKeyDown);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onLocationSelect);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _convertStringToAddress);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _getLocationDetails);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onAddressChangedEventEmit);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _fulfillSelection);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _setState);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onPromptItemSelected);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _getShowOnMapHandler);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onPromptsReceived);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onDocumentClick);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _makeAutocompleteServiceParams);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onInputFocus$1);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onInputFocusOut$1);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _showMenu);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _createLeftBottomMenuNode);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _createRightBottomMenuNode);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _showPresetLocations);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _onInputClick);
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _address$3, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _addressString$1, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _languageId$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _addressFormat$4, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _sourceCode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _locationRepository$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _userLocationPoint$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _presetLocationsProvider, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _prompt, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _autocompleteService, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _timerId, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _inputNode$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _searchPhrase, {
	      writable: true,
	      value: {
	        requested: '',
	        current: '',
	        dropped: ''
	      }
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _state$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _wasCleared, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _isDestroyed, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _isAutocompleteRequestStarted, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _isNextAutocompleteRequestWaiting, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _onLocationSelectTimerId, {
	      writable: true,
	      value: null
	    });
	    _this.setEventNamespace('BX.Location.Widget.Autocomplete');
	    if (!(props.addressFormat instanceof location_core.Format)) {
	      throw new Error('props.addressFormat must be type of Format');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _addressFormat$4, props.addressFormat);
	    if (!(props.autocompleteService instanceof location_core.AutocompleteServiceBase)) {
	      throw new Error('props.autocompleteService must be type of AutocompleteServiceBase');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _autocompleteService, props.autocompleteService);
	    if (!props.languageId) {
	      throw new Error('props.languageId must be defined');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId$1, props.languageId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sourceCode, props.sourceCode);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _address$3, props.address);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _presetLocationsProvider, props.presetLocationsProvider);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _locationRepository$2, props.locationRepository || new location_core.LocationRepository());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _userLocationPoint$1, props.userLocationPoint);
	    _classPrivateMethodGet$6(babelHelpers.assertThisInitialized(_this), _setState, _setState2).call(babelHelpers.assertThisInitialized(_this), State.INITIAL);
	    return _this;
	  }
	  babelHelpers.createClass(Autocomplete, [{
	    key: "render",
	    value: function render(props) {
	      babelHelpers.classPrivateFieldSet(this, _inputNode$2, props.inputNode);
	      babelHelpers.classPrivateFieldSet(this, _address$3, props.address);
	      babelHelpers.classPrivateFieldSet(this, _addressString$1, new AddressString$1(babelHelpers.classPrivateFieldGet(this, _inputNode$2), babelHelpers.classPrivateFieldGet(this, _addressFormat$4), babelHelpers.classPrivateFieldGet(this, _address$3)));
	      babelHelpers.classPrivateFieldGet(this, _inputNode$2).addEventListener('keydown', _classPrivateMethodGet$6(this, _onInputKeyDown, _onInputKeyDown2).bind(this));
	      babelHelpers.classPrivateFieldGet(this, _inputNode$2).addEventListener('keyup', _classPrivateMethodGet$6(this, _onInputKeyUp, _onInputKeyUp2).bind(this));
	      babelHelpers.classPrivateFieldGet(this, _inputNode$2).addEventListener('focus', _classPrivateMethodGet$6(this, _onInputFocus$1, _onInputFocus2$1).bind(this));
	      babelHelpers.classPrivateFieldGet(this, _inputNode$2).addEventListener('focusout', _classPrivateMethodGet$6(this, _onInputFocusOut$1, _onInputFocusOut2$1).bind(this));
	      babelHelpers.classPrivateFieldGet(this, _inputNode$2).addEventListener('click', _classPrivateMethodGet$6(this, _onInputClick, _onInputClick2).bind(this));
	      babelHelpers.classPrivateFieldGet(this, _inputNode$2).addEventListener('paste', _classPrivateMethodGet$6(this, _onInputPaste, _onInputPaste2).bind(this));
	      babelHelpers.classPrivateFieldSet(this, _prompt, new Prompt({
	        inputNode: props.inputNode,
	        menuNode: props.menuNode
	      }));
	      babelHelpers.classPrivateFieldGet(this, _prompt).subscribe(Prompt.onItemSelectedEvent, _classPrivateMethodGet$6(this, _onPromptItemSelected, _onPromptItemSelected2).bind(this));
	      document.addEventListener('click', _classPrivateMethodGet$6(this, _onDocumentClick, _onDocumentClick2).bind(this));
	    } // eslint-disable-next-line no-unused-vars
	  }, {
	    key: "onAddressChangedEventSubscribe",
	    /**
	     * Subscribe on changed event
	     * @param {Function} listener
	     */
	    value: function onAddressChangedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onAddressChangedEvent), listener);
	    }
	    /**
	     * Subscribe on loading event
	     * @param {Function} listener
	     */
	  }, {
	    key: "onStateChangedEventSubscribe",
	    value: function onStateChangedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onStateChangedEvent), listener);
	    }
	    /**
	     * @param {Function} listener
	     */
	  }, {
	    key: "onSearchStartedEventSubscribe",
	    value: function onSearchStartedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onSearchStartedEvent), listener);
	    }
	    /**
	     * @param {Function} listener
	     */
	  }, {
	    key: "onSearchCompletedEventSubscribe",
	    value: function onSearchCompletedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onSearchCompletedEvent), listener);
	    }
	    /**
	     * @param {Function} listener
	     */
	  }, {
	    key: "onShowOnMapClickedEventSubscribe",
	    value: function onShowOnMapClickedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onShowOnMapClickedEvent), listener);
	    }
	    /**
	     * Is called when autocompleteService returned location list
	     * @param {array} locationsList
	     * @param {object} params
	     */
	  }, {
	    key: "showPrompt",
	    /**
	     * @param {string} searchPhrase
	     */
	    value: function showPrompt(searchPhrase) {
	      babelHelpers.classPrivateFieldGet(this, _searchPhrase).requested = searchPhrase;
	      babelHelpers.classPrivateFieldGet(this, _searchPhrase).current = searchPhrase;
	      babelHelpers.classPrivateFieldGet(this, _searchPhrase).dropped = '';
	      _classPrivateMethodGet$6(this, _showPromptInner, _showPromptInner2).call(this, searchPhrase);
	    }
	  }, {
	    key: "closePrompt",
	    value: function closePrompt() {
	      if (babelHelpers.classPrivateFieldGet(this, _prompt)) {
	        babelHelpers.classPrivateFieldGet(this, _prompt).close();
	      }
	    }
	  }, {
	    key: "isPromptShown",
	    value: function isPromptShown() {
	      if (babelHelpers.classPrivateFieldGet(this, _prompt)) {
	        babelHelpers.classPrivateFieldGet(this, _prompt).isShown();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _isDestroyed)) {
	        return;
	      }
	      main_core.Event.unbindAll(this);
	      if (babelHelpers.classPrivateFieldGet(this, _prompt)) {
	        babelHelpers.classPrivateFieldGet(this, _prompt).destroy();
	        babelHelpers.classPrivateFieldSet(this, _prompt, null);
	      }
	      babelHelpers.classPrivateFieldSet(this, _timerId, null);
	      if (babelHelpers.classPrivateFieldGet(this, _inputNode$2)) {
	        babelHelpers.classPrivateFieldGet(this, _inputNode$2).removeEventListener('keydown', _classPrivateMethodGet$6(this, _onInputKeyDown, _onInputKeyDown2));
	        babelHelpers.classPrivateFieldGet(this, _inputNode$2).removeEventListener('keyup', _classPrivateMethodGet$6(this, _onInputKeyUp, _onInputKeyUp2));
	        babelHelpers.classPrivateFieldGet(this, _inputNode$2).removeEventListener('focus', _classPrivateMethodGet$6(this, _onInputFocus$1, _onInputFocus2$1));
	        babelHelpers.classPrivateFieldGet(this, _inputNode$2).removeEventListener('focusout', _classPrivateMethodGet$6(this, _onInputFocusOut$1, _onInputFocusOut2$1));
	        babelHelpers.classPrivateFieldGet(this, _inputNode$2).removeEventListener('click', _classPrivateMethodGet$6(this, _onInputClick, _onInputClick2));
	        babelHelpers.classPrivateFieldGet(this, _inputNode$2).removeEventListener('paste', _classPrivateMethodGet$6(this, _onInputPaste, _onInputPaste2));
	      }
	      document.removeEventListener('click', _classPrivateMethodGet$6(this, _onDocumentClick, _onDocumentClick2));
	      babelHelpers.classPrivateFieldSet(this, _isDestroyed, true);
	    }
	  }, {
	    key: "address",
	    /**
	     * @param address
	     */
	    set: function set(address) {
	      babelHelpers.classPrivateFieldSet(this, _address$3, address);
	      if (babelHelpers.classPrivateFieldGet(this, _addressString$1))
	        // already rendered
	        {
	          babelHelpers.classPrivateFieldGet(this, _addressString$1).setValueFromAddress(babelHelpers.classPrivateFieldGet(this, _address$3));
	        }
	      if (!address) {
	        babelHelpers.classPrivateFieldSet(this, _wasCleared, true);
	      }
	    }
	    /**
	     * @returns {Address}
	     */
	    ,
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _address$3);
	    }
	    /**
	     * Close menu on mouse click outside
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "state",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _state$1);
	    }
	  }]);
	  return Autocomplete;
	}(main_core_events.EventEmitter);
	function _onInputClick2(e) {
	  var value = babelHelpers.classPrivateFieldGet(this, _addressString$1).value;
	  if (value.length === 0) {
	    _classPrivateMethodGet$6(this, _showPresetLocations, _showPresetLocations2).call(this);
	  }
	}
	function _showPresetLocations2() {
	  var presetLocationList = babelHelpers.classPrivateFieldGet(this, _presetLocationsProvider).call(this);
	  babelHelpers.classPrivateFieldGet(this, _prompt).setMenuItems(presetLocationList, '');
	  var leftBottomMenuMessage;
	  if (presetLocationList.length > 0) {
	    leftBottomMenuMessage = main_core.Loc.getMessage('LOCATION_WIDGET_PICK_ADDRESS_OR_SHOW_ON_MAP');
	  } else {
	    leftBottomMenuMessage = main_core.Loc.getMessage('LOCATION_WIDGET_START_PRINTING_OR_SHOW_ON_MAP');
	  }
	  _classPrivateMethodGet$6(this, _showMenu, _showMenu2).call(this, leftBottomMenuMessage, null);
	}
	function _createRightBottomMenuNode2(location) {
	  var element = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"location-map-popup-item--show-on-map\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t"])), main_core.Loc.getMessage('LOCATION_WIDGET_SHOW_ON_MAP'));
	  element.addEventListener('click', _classPrivateMethodGet$6(this, _getShowOnMapHandler, _getShowOnMapHandler2).call(this, location));
	  return element;
	}
	function _createLeftBottomMenuNode2(text) {
	  return main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span>\n\t\t\t\t\t<span class=\"menu-popup-item-icon\"></span>\n\t\t\t\t\t<span class=\"menu-popup-item-text\">", "</span>\n\t\t\t\t</span>\n\t\t"])), text);
	}
	function _showMenu2(leftBottomText, location) {
	  /* Menu destroys popup after the closing, so we need to refresh it every time, we show it */
	  babelHelpers.classPrivateFieldGet(this, _prompt).getMenu().setBottomRightItemNode(_classPrivateMethodGet$6(this, _createRightBottomMenuNode, _createRightBottomMenuNode2).call(this, location));
	  babelHelpers.classPrivateFieldGet(this, _prompt).getMenu().setBottomLeftItemNode(_classPrivateMethodGet$6(this, _createLeftBottomMenuNode, _createLeftBottomMenuNode2).call(this, leftBottomText));
	  babelHelpers.classPrivateFieldGet(this, _prompt).getMenu().show();
	}
	function _onInputFocusOut2$1(e) {
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed)) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _state$1) === State.DATA_INPUTTING && !(e.relatedTarget && e.relatedTarget.getAttribute('data-role') === 'location-widget-menu-item')) {
	    _classPrivateMethodGet$6(this, _setState, _setState2).call(this, State.DATA_SUPPOSED);
	    var isChanged = false;
	    if (babelHelpers.classPrivateFieldGet(this, _addressString$1)) {
	      if (!babelHelpers.classPrivateFieldGet(this, _address$3) || !babelHelpers.classPrivateFieldGet(this, _addressString$1).hasPureAddressString()) {
	        babelHelpers.classPrivateFieldSet(this, _address$3, _classPrivateMethodGet$6(this, _convertStringToAddress, _convertStringToAddress2).call(this, babelHelpers.classPrivateFieldGet(this, _addressString$1).value));
	        isChanged = true;
	      }
	      // this.#addressString === null until autocompete'll be rendered
	      else if (babelHelpers.classPrivateFieldGet(this, _addressString$1).customTail !== '') {
	        var currentValue = babelHelpers.classPrivateFieldGet(this, _address$3).getFieldValue(babelHelpers.classPrivateFieldGet(this, _addressFormat$4).fieldForUnRecognized);
	        var newValue = currentValue ? currentValue + babelHelpers.classPrivateFieldGet(this, _addressString$1).customTail : babelHelpers.classPrivateFieldGet(this, _addressString$1).customTail;
	        babelHelpers.classPrivateFieldGet(this, _address$3).setFieldValue(babelHelpers.classPrivateFieldGet(this, _addressFormat$4).fieldForUnRecognized, newValue);
	        isChanged = true;
	      }
	    }
	    if (isChanged) {
	      babelHelpers.classPrivateFieldGet(this, _addressString$1).setValueFromAddress(babelHelpers.classPrivateFieldGet(this, _address$3));
	      _classPrivateMethodGet$6(this, _onAddressChangedEventEmit, _onAddressChangedEventEmit2).call(this, [], {
	        storeAsLastAddress: false
	      });
	    }
	  }

	  // Let's prevent other onInputFocusOut handlers.
	  e.stopImmediatePropagation();
	}
	function _onInputFocus2$1() {
	  var _this2 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed)) {
	    return;
	  }
	  if (!babelHelpers.classPrivateFieldGet(this, _address$3)) {
	    var lastAddress = location_core.Storage.getInstance().lastAddress;
	    if (lastAddress && lastAddress.fieldCollection.isFieldExists(location_core.AddressType.LOCALITY) && !babelHelpers.classPrivateFieldGet(this, _wasCleared)) {
	      var fieldCollection = {};
	      fieldCollection[location_core.AddressType.LOCALITY] = lastAddress.fieldCollection.getFieldValue(location_core.AddressType.LOCALITY);
	      if (lastAddress.fieldCollection.isFieldExists(location_core.AddressType.COUNTRY)) {
	        fieldCollection[location_core.AddressType.COUNTRY] = lastAddress.fieldCollection.getFieldValue(location_core.AddressType.COUNTRY);
	      }
	      if (lastAddress.fieldCollection.isFieldExists(location_core.AddressType.ADM_LEVEL_1)) {
	        fieldCollection[location_core.AddressType.ADM_LEVEL_1] = lastAddress.fieldCollection.getFieldValue(location_core.AddressType.ADM_LEVEL_1);
	      }
	      if (['RU', 'RU_2'].includes(babelHelpers.classPrivateFieldGet(this, _addressFormat$4).code)) {
	        fieldCollection[location_core.AddressType.ADDRESS_LINE_2] = ', ';
	      }
	      babelHelpers.classPrivateFieldSet(this, _address$3, new location_core.Address({
	        languageId: lastAddress.languageId,
	        fieldCollection: fieldCollection
	      }));
	      babelHelpers.classPrivateFieldGet(this, _addressString$1).setValueFromAddress(babelHelpers.classPrivateFieldGet(this, _address$3));
	      _classPrivateMethodGet$6(this, _setState, _setState2).call(this, State.DATA_SUPPOSED);
	      _classPrivateMethodGet$6(this, _onAddressChangedEventEmit, _onAddressChangedEventEmit2).call(this, [], {
	        storeAsLastAddress: false
	      });
	      setTimeout(function () {
	        BX.setCaretPosition(babelHelpers.classPrivateFieldGet(_this2, _inputNode$2), babelHelpers.classPrivateFieldGet(_this2, _inputNode$2).value.length);
	      }, 0);
	    }
	  } else {
	    if (babelHelpers.classPrivateFieldGet(this, _address$3) && (!babelHelpers.classPrivateFieldGet(this, _address$3).location || !babelHelpers.classPrivateFieldGet(this, _address$3).location.hasExternalRelation()) && babelHelpers.classPrivateFieldGet(this, _addressString$1).value.length > 0) {
	      this.showPrompt(babelHelpers.classPrivateFieldGet(this, _addressString$1).value);
	    }
	  }
	}
	function _makeAutocompleteServiceParams2() {
	  var result = {};

	  //result.biasPoint = this.#userLocationPoint;
	  if (babelHelpers.classPrivateFieldGet(this, _address$3) && babelHelpers.classPrivateFieldGet(this, _address$3).latitude && babelHelpers.classPrivateFieldGet(this, _address$3).longitude) {
	    result.biasPoint = new location_core.Point(babelHelpers.classPrivateFieldGet(this, _address$3).latitude, babelHelpers.classPrivateFieldGet(this, _address$3).longitude);
	  }
	  return result;
	}
	function _onDocumentClick2(event) {
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed)) {
	    return;
	  }
	  if (event.target === babelHelpers.classPrivateFieldGet(this, _inputNode$2)) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _prompt).isShown()) {
	    babelHelpers.classPrivateFieldGet(this, _prompt).close();
	  }
	}
	function _onPromptsReceived2(locationsList, params) {
	  var _this3 = this;
	  if (Array.isArray(locationsList) && locationsList.length > 0) {
	    if (locationsList.length === 1 && babelHelpers.classPrivateFieldGet(this, _address$3) && babelHelpers.classPrivateFieldGet(this, _address$3).location && babelHelpers.classPrivateFieldGet(this, _address$3).location.externalId && babelHelpers.classPrivateFieldGet(this, _address$3).location.externalId === locationsList[0].externalId) {
	      this.closePrompt();
	      return;
	    }
	    babelHelpers.classPrivateFieldGet(this, _prompt).setMenuItems(locationsList, babelHelpers.classPrivateFieldGet(this, _searchPhrase).requested, this.address);
	    _classPrivateMethodGet$6(this, _showMenu, _showMenu2).call(this, main_core.Loc.getMessage('LOCATION_WIDGET_PICK_ADDRESS_OR_SHOW_ON_MAP'), locationsList[0]);
	  } else {
	    babelHelpers.classPrivateFieldGet(this, _prompt).getMenu().clearItems();
	    babelHelpers.classPrivateFieldGet(this, _prompt).getMenu().addMenuItem({
	      id: 'notFound',
	      html: "<span>".concat(main_core.Loc.getMessage('LOCATION_WIDGET_PROMPT_ADDRESS_NOT_FOUND'), "</span>"),
	      // eslint-disable-next-line no-unused-vars
	      onclick: function onclick(event, item) {
	        babelHelpers.classPrivateFieldGet(_this3, _prompt).close();
	      }
	    });
	    _classPrivateMethodGet$6(this, _showMenu, _showMenu2).call(this, main_core.Loc.getMessage('LOCATION_WIDGET_CHECK_ADDRESS_OR_SHOW_ON_MAP'), null);
	  }
	}
	function _getShowOnMapHandler2(location) {
	  var _this4 = this;
	  return function () {
	    if (location) {
	      _classPrivateMethodGet$6(_this4, _fulfillSelection, _fulfillSelection2).call(_this4, location);
	      return;
	    }

	    // Otherwise this click will close just opened map popup.
	    setTimeout(function () {
	      _this4.emit(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onShowOnMapClickedEvent));
	    }, 1);
	  };
	}
	function _onPromptItemSelected2(event) {
	  if (event.data.location) {
	    _classPrivateMethodGet$6(this, _fulfillSelection, _fulfillSelection2).call(this, event.data.location);
	  }
	}
	function _setState2(state) {
	  babelHelpers.classPrivateFieldSet(this, _state$1, state);
	  this.emit(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onStateChangedEvent), {
	    state: babelHelpers.classPrivateFieldGet(this, _state$1)
	  });
	}
	function _fulfillSelection2(location) {
	  var _this5 = this;
	  var result;
	  _classPrivateMethodGet$6(this, _setState, _setState2).call(this, State.DATA_SELECTED);
	  if (location) {
	    if (location.hasExternalRelation() && babelHelpers.classPrivateFieldGet(this, _sourceCode) === location.sourceCode) {
	      result = _classPrivateMethodGet$6(this, _getLocationDetails, _getLocationDetails2).call(this, location).then(function (detailedLocation) {
	        if (location.address && location.address.getFieldValue(location_core.AddressType.ADDRESS_LINE_2)) {
	          var addressLine2 = '';
	          if (detailedLocation.address.getFieldValue(location_core.AddressType.ADDRESS_LINE_2)) {
	            addressLine2 = detailedLocation.address.getFieldValue(location_core.AddressType.ADDRESS_LINE_2);
	            addressLine2 += ', ';
	          }
	          addressLine2 += location.address.getFieldValue(location_core.AddressType.ADDRESS_LINE_2);
	          detailedLocation.address.setFieldValue(location_core.AddressType.ADDRESS_LINE_2, addressLine2);
	        }
	        _classPrivateMethodGet$6(_this5, _createOnLocationSelectTimer, _createOnLocationSelectTimer2).call(_this5, detailedLocation, 0);
	        return true;
	      }, function (response) {
	        return location_core.ErrorPublisher.getInstance().notify(response.errors);
	      });
	    } else {
	      result = new Promise(function (resolve) {
	        setTimeout(function () {
	          _classPrivateMethodGet$6(_this5, _createOnLocationSelectTimer, _createOnLocationSelectTimer2).call(_this5, location, 0);
	          resolve();
	        }, 0);
	      });
	    }
	  } else {
	    result = new Promise(function (resolve) {
	      setTimeout(function () {
	        _classPrivateMethodGet$6(_this5, _createOnLocationSelectTimer, _createOnLocationSelectTimer2).call(_this5, null, 0);
	        resolve();
	      }, 0);
	    });
	  }
	  return result;
	}
	function _onAddressChangedEventEmit2() {
	  var excludeSetAddressFeatures = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	  this.emit(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onAddressChangedEvent), {
	    address: babelHelpers.classPrivateFieldGet(this, _address$3),
	    excludeSetAddressFeatures: excludeSetAddressFeatures,
	    options: options
	  });
	}
	function _getLocationDetails2(location) {
	  var _this6 = this;
	  _classPrivateMethodGet$6(this, _setState, _setState2).call(this, State.DATA_LOADING);
	  return babelHelpers.classPrivateFieldGet(this, _locationRepository$2).findByExternalId(location.externalId, location.sourceCode, location.languageId).then(function (detailedLocation) {
	    _classPrivateMethodGet$6(_this6, _setState, _setState2).call(_this6, State.DATA_LOADED);
	    var result;
	    /*
	     * Nominatim could return a bit different location without the coordinates.
	     * For example N752206814
	     */
	    if (detailedLocation.latitude !== '0' && detailedLocation.longitude !== '0' && detailedLocation !== '') {
	      result = detailedLocation;
	      result.name = location.name;
	    } else {
	      result = location;
	    }
	    return result;
	  }, function (response) {
	    location_core.ErrorPublisher.getInstance().notify(response.errors);
	  });
	}
	function _convertStringToAddress2(addressString) {
	  var result = new location_core.Address({
	    languageId: babelHelpers.classPrivateFieldGet(this, _languageId$1)
	  });
	  result.setFieldValue(babelHelpers.classPrivateFieldGet(this, _addressFormat$4).fieldForUnRecognized, addressString);
	  return result;
	}
	function _onLocationSelect2(location) {
	  babelHelpers.classPrivateFieldSet(this, _address$3, location ? location.toAddress() : null);
	  babelHelpers.classPrivateFieldGet(this, _addressString$1).setValueFromAddress(babelHelpers.classPrivateFieldGet(this, _address$3));
	  _classPrivateMethodGet$6(this, _onAddressChangedEventEmit, _onAddressChangedEventEmit2).call(this);
	}
	function _onInputKeyDown2(e) {
	  if (!(babelHelpers.classPrivateFieldGet(this, _inputNode$2) && babelHelpers.classPrivateFieldGet(this, _inputNode$2).selectionStart === 0 && babelHelpers.classPrivateFieldGet(this, _inputNode$2).selectionEnd === babelHelpers.classPrivateFieldGet(this, _inputNode$2).value.length)) {
	    return;
	  }
	  if (e.code === 'Backspace' || e.code === 'Delete' || e.code === 'KeyV' && (e.ctrlKey || e.metaKey) || e.code === 'KeyX' && (e.ctrlKey || e.metaKey) || e.code === 'Insert' && e.shiftKey || !(e.ctrlKey || e.metaKey) && babelHelpers.toConsumableArray(e.key).length === 1) {
	    this.address = null;
	    _classPrivateMethodGet$6(this, _onAddressChangedEventEmit, _onAddressChangedEventEmit2).call(this);
	  }
	}
	function _onInputKeyUp2(e) {
	  var _this7 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed)) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _state$1) !== State.DATA_INPUTTING && babelHelpers.classPrivateFieldGet(this, _addressString$1).isChanged()) {
	    _classPrivateMethodGet$6(this, _setState, _setState2).call(this, State.DATA_INPUTTING);
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _prompt).isShown()) {
	    var location;
	    var onLocationSelectTimeout = 700;
	    switch (e.code) {
	      case 'NumpadEnter':
	      case 'Enter':
	        if (babelHelpers.classPrivateFieldGet(this, _prompt).isItemChosen()) {
	          _classPrivateMethodGet$6(this, _fulfillSelection, _fulfillSelection2).call(this, babelHelpers.classPrivateFieldGet(this, _prompt).getChosenItem()).then(function () {
	            babelHelpers.classPrivateFieldGet(_this7, _prompt).close();
	          }, function (error) {
	            return BX.debug(error);
	          });
	        }
	        return;
	      case 'Tab':
	      case 'Escape':
	        _classPrivateMethodGet$6(this, _setState, _setState2).call(this, State.DATA_SUPPOSED);
	        _classPrivateMethodGet$6(this, _onAddressChangedEventEmit, _onAddressChangedEventEmit2).call(this);
	        babelHelpers.classPrivateFieldGet(this, _prompt).close();
	        return;
	      case 'ArrowUp':
	        location = babelHelpers.classPrivateFieldGet(this, _prompt).choosePrevItem();
	        if (location && location.address) {
	          _classPrivateMethodGet$6(this, _createOnLocationSelectTimer, _createOnLocationSelectTimer2).call(this, location, onLocationSelectTimeout);
	        }
	        return;
	      case 'ArrowDown':
	        location = babelHelpers.classPrivateFieldGet(this, _prompt).chooseNextItem();
	        if (location && location.address) {
	          _classPrivateMethodGet$6(this, _createOnLocationSelectTimer, _createOnLocationSelectTimer2).call(this, location, onLocationSelectTimeout);
	        }
	        return;
	    }
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _addressString$1).isChanged()) {
	    babelHelpers.classPrivateFieldGet(this, _addressString$1).actualize();
	    this.showPrompt(babelHelpers.classPrivateFieldGet(this, _addressString$1).value);
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _addressString$1).value.length === 0) {
	    _classPrivateMethodGet$6(this, _showPresetLocations, _showPresetLocations2).call(this);
	  }
	}
	function _onInputPaste2() {
	  var _this8 = this;
	  setTimeout(function () {
	    if (babelHelpers.classPrivateFieldGet(_this8, _state$1) !== State.DATA_INPUTTING && babelHelpers.classPrivateFieldGet(_this8, _addressString$1).isChanged()) {
	      _classPrivateMethodGet$6(_this8, _setState, _setState2).call(_this8, State.DATA_INPUTTING);
	    }
	    if (babelHelpers.classPrivateFieldGet(_this8, _addressString$1).isChanged()) {
	      babelHelpers.classPrivateFieldGet(_this8, _addressString$1).actualize();
	      _this8.showPrompt(babelHelpers.classPrivateFieldGet(_this8, _addressString$1).value);
	    }
	  }, 0);
	}
	function _createOnLocationSelectTimer2(location, timeout) {
	  var _this9 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _onLocationSelectTimerId) !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldGet(this, _onLocationSelectTimerId));
	  }
	  babelHelpers.classPrivateFieldSet(this, _onLocationSelectTimerId, setTimeout(function () {
	    _classPrivateMethodGet$6(_this9, _onLocationSelect, _onLocationSelect2).call(_this9, location);
	  }, timeout));
	}
	function _showPromptInner2(searchPhrase) {
	  if (searchPhrase.length <= 3) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _timerId) !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldGet(this, _timerId));
	  }
	  babelHelpers.classPrivateFieldSet(this, _timerId, _classPrivateMethodGet$6(this, _createTimer, _createTimer2).call(this, searchPhrase));
	}
	function _createTimer2(searchPhrase) {
	  var _this10 = this;
	  return setTimeout(function () {
	    // to avoid multiple parallel requests, server responses are too slow.
	    if (babelHelpers.classPrivateFieldGet(_this10, _isAutocompleteRequestStarted)) {
	      clearTimeout(babelHelpers.classPrivateFieldGet(_this10, _timerId));
	      babelHelpers.classPrivateFieldSet(_this10, _timerId, _classPrivateMethodGet$6(_this10, _createTimer, _createTimer2).call(_this10, searchPhrase));
	      babelHelpers.classPrivateFieldSet(_this10, _isNextAutocompleteRequestWaiting, true);
	      return;
	    }
	    babelHelpers.classPrivateFieldSet(_this10, _isNextAutocompleteRequestWaiting, false);
	    _this10.emit(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onSearchStartedEvent));
	    babelHelpers.classPrivateFieldSet(_this10, _isAutocompleteRequestStarted, true);
	    var params = _classPrivateMethodGet$6(_this10, _makeAutocompleteServiceParams, _makeAutocompleteServiceParams2).call(_this10);
	    babelHelpers.classPrivateFieldGet(_this10, _autocompleteService).autocomplete(searchPhrase, params).then(function (locationsList) {
	      babelHelpers.classPrivateFieldSet(_this10, _timerId, null);
	      if (!babelHelpers.classPrivateFieldGet(_this10, _isNextAutocompleteRequestWaiting)) {
	        _classPrivateMethodGet$6(_this10, _onPromptsReceived, _onPromptsReceived2).call(_this10, locationsList, params);
	        _this10.emit(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onSearchCompletedEvent));
	      }
	      babelHelpers.classPrivateFieldSet(_this10, _isAutocompleteRequestStarted, false);
	    }, function (error) {
	      if (!babelHelpers.classPrivateFieldGet(_this10, _isNextAutocompleteRequestWaiting)) {
	        _this10.emit(_classStaticPrivateFieldSpecGet$1(Autocomplete, Autocomplete, _onSearchCompletedEvent));
	      }
	      babelHelpers.classPrivateFieldSet(_this10, _isAutocompleteRequestStarted, false);
	      BX.debug(error);
	    });
	  }, 300);
	}
	var _onAddressChangedEvent = {
	  writable: true,
	  value: 'onAddressChanged'
	};
	var _onStateChangedEvent = {
	  writable: true,
	  value: 'onStateChanged'
	};
	var _onSearchStartedEvent = {
	  writable: true,
	  value: 'onSearchStarted'
	};
	var _onSearchCompletedEvent = {
	  writable: true,
	  value: 'onSearchCompleted'
	};
	var _onShowOnMapClickedEvent = {
	  writable: true,
	  value: 'onShowOnMapClicked'
	};

	var _templateObject$4, _templateObject2$4;
	function _classPrivateFieldInitSpec$8(obj, privateMap, value) { _checkPrivateRedeclaration$9(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$9(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _description = /*#__PURE__*/new WeakMap();
	var _url = /*#__PURE__*/new WeakMap();
	var _link = /*#__PURE__*/new WeakMap();
	var _location = /*#__PURE__*/new WeakMap();
	var _title = /*#__PURE__*/new WeakMap();
	var Photo = /*#__PURE__*/function () {
	  function Photo(props) {
	    babelHelpers.classCallCheck(this, Photo);
	    _classPrivateFieldInitSpec$8(this, _description, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(this, _url, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(this, _link, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(this, _location, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _url, props.url);
	    babelHelpers.classPrivateFieldSet(this, _link, props.link || '');
	    babelHelpers.classPrivateFieldSet(this, _description, props.description || '');
	    babelHelpers.classPrivateFieldSet(this, _location, props.location);
	    babelHelpers.classPrivateFieldSet(this, _title, props.title || '');
	  }
	  babelHelpers.createClass(Photo, [{
	    key: "render",
	    value: function render() {
	      var description = '';
	      if (babelHelpers.classPrivateFieldGet(this, _description)) {
	        //todo: sanitize
	        description = main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<span class=\"location-map-item-description\">", "</span>"])), babelHelpers.classPrivateFieldGet(this, _description));
	      }
	      return main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"location-map-photo-item-block\">\n\t\t\t\t<span class=\"location-map-photo-item-block-image-block-inner\">\n\t\t\t\t\t", "\n\t\t\t\t\t<span \n\t\t\t\t\t\tdata-viewer data-viewer-type=\"image\" \n\t\t\t\t\t\tdata-src=\"", "\" \n\t\t\t\t\t\tdata-title=\"", "\"\n\t\t\t\t\t\tclass=\"location-map-item-photo-image\" \n\t\t\t\t\t\tdata-viewer-group-by=\"", "\"\n\t\t\t\t\t\tstyle=\"background-image: url(", ");\">\t\t\t\t\t\t\t\n\t\t\t\t\t</span>\n\t\t\t\t</span>\n\t\t\t</div>"])), description, babelHelpers.classPrivateFieldGet(this, _link), babelHelpers.classPrivateFieldGet(this, _title), babelHelpers.classPrivateFieldGet(this, _location).externalId, babelHelpers.classPrivateFieldGet(this, _url));
	    }
	  }]);
	  return Photo;
	}();

	var _templateObject$5, _templateObject2$5;
	function _createForOfIteratorHelper$2(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$2(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$2(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$2(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$2(o, minLen); }
	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$7(obj, privateSet) { _checkPrivateRedeclaration$a(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$9(obj, privateMap, value) { _checkPrivateRedeclaration$a(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$a(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$7(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _photos = /*#__PURE__*/new WeakMap();
	var _container = /*#__PURE__*/new WeakMap();
	var _photosContainer = /*#__PURE__*/new WeakMap();
	var _thumbnailHeight = /*#__PURE__*/new WeakMap();
	var _thumbnailWidth = /*#__PURE__*/new WeakMap();
	var _photoService = /*#__PURE__*/new WeakMap();
	var _maxPhotoCount = /*#__PURE__*/new WeakMap();
	var _location$1 = /*#__PURE__*/new WeakMap();
	var _setPhotos = /*#__PURE__*/new WeakSet();
	var _renderPhotos = /*#__PURE__*/new WeakSet();
	var Gallery = /*#__PURE__*/function () {
	  function Gallery(props) {
	    babelHelpers.classCallCheck(this, Gallery);
	    _classPrivateMethodInitSpec$7(this, _renderPhotos);
	    _classPrivateMethodInitSpec$7(this, _setPhotos);
	    _classPrivateFieldInitSpec$9(this, _photos, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$9(this, _container, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$9(this, _photosContainer, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$9(this, _thumbnailHeight, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(this, _thumbnailWidth, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(this, _photoService, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(this, _maxPhotoCount, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(this, _location$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _thumbnailHeight, props.thumbnailHeight);
	    babelHelpers.classPrivateFieldSet(this, _thumbnailWidth, props.thumbnailWidth);
	    babelHelpers.classPrivateFieldSet(this, _maxPhotoCount, props.maxPhotoCount);
	    babelHelpers.classPrivateFieldSet(this, _photoService, props.photoService);
	  }
	  babelHelpers.createClass(Gallery, [{
	    key: "refresh",
	    value: function refresh() {
	      var _this = this;
	      if (babelHelpers.classPrivateFieldGet(this, _location$1)) {
	        babelHelpers.classPrivateFieldGet(this, _photoService).requestPhotos({
	          location: babelHelpers.classPrivateFieldGet(this, _location$1),
	          thumbnailHeight: babelHelpers.classPrivateFieldGet(this, _thumbnailHeight),
	          thumbnailWidth: babelHelpers.classPrivateFieldGet(this, _thumbnailWidth),
	          maxPhotoCount: babelHelpers.classPrivateFieldGet(this, _maxPhotoCount)
	        }).then(function (photosData) {
	          if (Array.isArray(photosData) && photosData.length > 0) {
	            _classPrivateMethodGet$7(_this, _setPhotos, _setPhotos2).call(_this, photosData);
	            _this.show();
	          } else {
	            _this.hide();
	          }
	        });
	      } else {
	        this.hide();
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (babelHelpers.classPrivateFieldGet(this, _container)) {
	        babelHelpers.classPrivateFieldGet(this, _container).style.display = 'none';
	      }
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return !babelHelpers.classPrivateFieldGet(this, _container) || babelHelpers.classPrivateFieldGet(this, _container).clientWidth <= 0;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (babelHelpers.classPrivateFieldGet(this, _container)) {
	        babelHelpers.classPrivateFieldGet(this, _container).style.display = 'block';
	      }
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      babelHelpers.classPrivateFieldSet(this, _photosContainer, main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\t\t\t\t\t\n\t\t\t\t<div class=\"location-map-photo-inner\">\t\t\t\t\t\n\t\t\t\t</div>"]))));
	      babelHelpers.classPrivateFieldSet(this, _container, main_core.Tag.render(_templateObject2$5 || (_templateObject2$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"location-map-photo-container\">\n\t\t\t\t", "\n\t\t\t</div>"])), babelHelpers.classPrivateFieldGet(this, _photosContainer)));
	      return babelHelpers.classPrivateFieldGet(this, _container);
	    }
	  }, {
	    key: "location",
	    set: function set(location) {
	      babelHelpers.classPrivateFieldSet(this, _location$1, location);
	      this.refresh();
	    }
	  }]);
	  return Gallery;
	}();
	function _setPhotos2(photosData) {
	  if (!babelHelpers.classPrivateFieldGet(this, _location$1)) {
	    return;
	  }
	  var photos = [];
	  var _iterator = _createForOfIteratorHelper$2(photosData),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var _photo2 = _step.value;
	      photos.push(new Photo({
	        url: _photo2.thumbnail.url,
	        link: _photo2.url,
	        location: babelHelpers.classPrivateFieldGet(this, _location$1),
	        title: babelHelpers.classPrivateFieldGet(this, _location$1).name + " ( " + BX.util.strip_tags(_photo2.description) + ' )'
	      }));
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  if (!Array.isArray(photos)) {
	    BX.debug('Wrong type of photos. Must be array');
	    return;
	  }
	  babelHelpers.classPrivateFieldSet(this, _photos, []);
	  for (var _i = 0, _photos2 = photos; _i < _photos2.length; _i++) {
	    var photo = _photos2[_i];
	    babelHelpers.classPrivateFieldGet(this, _photos).push(photo);
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _photos).length > 0 && babelHelpers.classPrivateFieldGet(this, _photosContainer)) {
	    var renderedPhotos = babelHelpers.classPrivateFieldGet(this, _photos) ? _classPrivateMethodGet$7(this, _renderPhotos, _renderPhotos2).call(this, babelHelpers.classPrivateFieldGet(this, _photos)) : '';
	    babelHelpers.classPrivateFieldGet(this, _photosContainer).innerHTML = '';
	    if (renderedPhotos.length > 0) {
	      var _iterator2 = _createForOfIteratorHelper$2(renderedPhotos),
	        _step2;
	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var _photo = _step2.value;
	          babelHelpers.classPrivateFieldGet(this, _photosContainer).appendChild(_photo);
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	    }
	  }
	}
	function _renderPhotos2(photos) {
	  var result = [];
	  var _iterator3 = _createForOfIteratorHelper$2(photos),
	    _step3;
	  try {
	    for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	      var photo = _step3.value;
	      result.push(photo.render());
	    }
	  } catch (err) {
	    _iterator3.e(err);
	  } finally {
	    _iterator3.f();
	  }
	  return result;
	}

	var _templateObject$6, _templateObject2$6, _templateObject3$2, _templateObject4;
	function _classPrivateMethodInitSpec$8(obj, privateSet) { _checkPrivateRedeclaration$b(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$a(obj, privateMap, value) { _checkPrivateRedeclaration$b(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$b(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$8(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	function _classStaticPrivateFieldSpecGet$2(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$3(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$2(descriptor, "get"); return _classApplyDescriptorGet$2(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$2(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$3(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$2(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var _title$1 = /*#__PURE__*/new WeakMap();
	var _value$1 = /*#__PURE__*/new WeakMap();
	var _type = /*#__PURE__*/new WeakMap();
	var _sort = /*#__PURE__*/new WeakMap();
	var _mode$2 = /*#__PURE__*/new WeakMap();
	var _input$1 = /*#__PURE__*/new WeakMap();
	var _viewContainer = /*#__PURE__*/new WeakMap();
	var _container$1 = /*#__PURE__*/new WeakMap();
	var _state$2 = /*#__PURE__*/new WeakMap();
	var _setState$1 = /*#__PURE__*/new WeakSet();
	var _renderEditMode = /*#__PURE__*/new WeakSet();
	var _renderViewMode = /*#__PURE__*/new WeakSet();
	var _refreshLayout = /*#__PURE__*/new WeakSet();
	var Field = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Field, _EventEmitter);
	  function Field(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Field);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Field).call(this, props));
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _refreshLayout);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderViewMode);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _renderEditMode);
	    _classPrivateMethodInitSpec$8(babelHelpers.assertThisInitialized(_this), _setState$1);
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _title$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _value$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _type, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _sort, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _mode$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _input$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _viewContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _container$1, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$a(babelHelpers.assertThisInitialized(_this), _state$2, {
	      writable: true,
	      value: State.INITIAL
	    });
	    _this.setEventNamespace('BX.Location.Widget.Field');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _title$1, props.title);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _type, props.type);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sort, props.sort);
	    return _this;
	  }
	  babelHelpers.createClass(Field, [{
	    key: "render",
	    value: function render(props) {
	      babelHelpers.classPrivateFieldSet(this, _value$1, typeof props.value === 'string' ? props.value : '');
	      if (!location_core.ControlMode.isValid(props.mode)) {
	        BX.debug('props.mode must be valid ControlMode');
	      }
	      babelHelpers.classPrivateFieldSet(this, _mode$2, props.mode);
	      babelHelpers.classPrivateFieldSet(this, _container$1, main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-content-block ui-entity-editor-field-text\">\n\t\t\t\t<div class=\"ui-entity-editor-block-title\">\n\t\t\t\t\t<label class=\"ui-entity-editor-block-title-text\">", ":</label>\t\t\t\t\n\t\t\t\t</div>\n\t\t\t</div>"])), babelHelpers.classPrivateFieldGet(this, _title$1)));
	      if (babelHelpers.classPrivateFieldGet(this, _mode$2) === location_core.ControlMode.edit) {
	        _classPrivateMethodGet$8(this, _renderEditMode, _renderEditMode2).call(this, babelHelpers.classPrivateFieldGet(this, _container$1));
	      } else {
	        _classPrivateMethodGet$8(this, _renderViewMode, _renderViewMode2).call(this, babelHelpers.classPrivateFieldGet(this, _container$1));
	      }
	      return babelHelpers.classPrivateFieldGet(this, _container$1);
	    }
	  }, {
	    key: "subscribeOnValueChangedEvent",
	    value: function subscribeOnValueChangedEvent(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$2(Field, Field, _onValueChangedEvent), listener);
	    }
	  }, {
	    key: "subscribeOnStateChangedEvent",
	    value: function subscribeOnStateChangedEvent(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$2(Field, Field, _onStateChangedEvent$1), listener);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _container$1));
	      main_core.Event.unbindAll(this);
	      babelHelpers.classPrivateFieldSet(this, _container$1, null);
	    }
	  }, {
	    key: "container",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _container$1);
	    }
	  }, {
	    key: "state",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _state$2);
	    }
	  }, {
	    key: "type",
	    set: function set(type) {
	      babelHelpers.classPrivateFieldSet(this, _type, type);
	    },
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _type);
	    }
	  }, {
	    key: "sort",
	    set: function set(sort) {
	      babelHelpers.classPrivateFieldSet(this, _sort, sort);
	    },
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _sort);
	    }
	  }, {
	    key: "value",
	    set: function set(value) {
	      babelHelpers.classPrivateFieldSet(this, _value$1, typeof value === 'string' ? value : '');
	      _classPrivateMethodGet$8(this, _refreshLayout, _refreshLayout2).call(this);
	    },
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _value$1);
	    }
	  }]);
	  return Field;
	}(main_core_events.EventEmitter);
	function _setState2$1(state) {
	  babelHelpers.classPrivateFieldSet(this, _state$2, state);
	  this.emit(_classStaticPrivateFieldSpecGet$2(Field, Field, _onStateChangedEvent$1), {
	    state: babelHelpers.classPrivateFieldGet(this, _state$2)
	  });
	}
	function _renderEditMode2(container) {
	  var _this2 = this;
	  babelHelpers.classPrivateFieldSet(this, _input$1, main_core.Tag.render(_templateObject2$6 || (_templateObject2$6 = babelHelpers.taggedTemplateLiteral(["<input type=\"text\" class=\"ui-ctl-element\" value=\"", "\">"])), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _value$1))));
	  babelHelpers.classPrivateFieldSet(this, _viewContainer, null);
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _input$1), 'focus', function (e) {
	    _classPrivateMethodGet$8(_this2, _setState$1, _setState2$1).call(_this2, State.DATA_INPUTTING);
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _input$1), 'focusout', function (e) {
	    _classPrivateMethodGet$8(_this2, _setState$1, _setState2$1).call(_this2, State.DATA_SELECTED);
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _input$1), 'change', function (e) {
	    _classPrivateMethodGet$8(_this2, _setState$1, _setState2$1).call(_this2, State.DATA_SELECTED);
	    babelHelpers.classPrivateFieldSet(_this2, _value$1, babelHelpers.classPrivateFieldGet(_this2, _input$1).value);
	    _this2.emit(_classStaticPrivateFieldSpecGet$2(Field, Field, _onValueChangedEvent), {
	      value: _this2
	    });
	  });
	  container.appendChild(main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-entity-editor-content-block\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>"])), babelHelpers.classPrivateFieldGet(this, _input$1)));
	}
	function _renderViewMode2(container) {
	  babelHelpers.classPrivateFieldSet(this, _input$1, null);
	  babelHelpers.classPrivateFieldSet(this, _viewContainer, main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-title-6\">\n\t\t\t\t", "\n\t\t\t</div>"])), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _value$1))));
	  container.appendChild(babelHelpers.classPrivateFieldGet(this, _viewContainer));
	}
	function _refreshLayout2() {
	  if (babelHelpers.classPrivateFieldGet(this, _mode$2) === location_core.ControlMode.edit) {
	    babelHelpers.classPrivateFieldGet(this, _input$1).value = babelHelpers.classPrivateFieldGet(this, _value$1);
	  } else {
	    babelHelpers.classPrivateFieldGet(this, _viewContainer).innerHTML = main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _value$1));
	  }
	}
	var _onValueChangedEvent = {
	  writable: true,
	  value: 'onValueChanged'
	};
	var _onStateChangedEvent$1 = {
	  writable: true,
	  value: 'onStateChanged'
	};

	function _createForOfIteratorHelper$3(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$3(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$3(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$3(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$3(o, minLen); }
	function _arrayLikeToArray$3(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$9(obj, privateSet) { _checkPrivateRedeclaration$c(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$b(obj, privateMap, value) { _checkPrivateRedeclaration$c(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$c(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet$3(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$4(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$3(descriptor, "get"); return _classApplyDescriptorGet$3(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$3(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$4(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$3(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodGet$9(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _address$4 = /*#__PURE__*/new WeakMap();
	var _addressFormat$5 = /*#__PURE__*/new WeakMap();
	var _mode$3 = /*#__PURE__*/new WeakMap();
	var _fields = /*#__PURE__*/new WeakMap();
	var _languageId$2 = /*#__PURE__*/new WeakMap();
	var _container$2 = /*#__PURE__*/new WeakMap();
	var _state$3 = /*#__PURE__*/new WeakMap();
	var _initFields = /*#__PURE__*/new WeakSet();
	var _onFieldChanged = /*#__PURE__*/new WeakSet();
	var _setState$2 = /*#__PURE__*/new WeakSet();
	var Fields = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Fields, _EventEmitter);
	  function Fields(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Fields);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Fields).call(this, props));
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _setState$2);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _onFieldChanged);
	    _classPrivateMethodInitSpec$9(babelHelpers.assertThisInitialized(_this), _initFields);
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _address$4, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _addressFormat$5, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _mode$3, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _fields, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _languageId$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _container$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$b(babelHelpers.assertThisInitialized(_this), _state$3, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Location.Widget.Fields');
	    if (!(props.addressFormat instanceof location_core.Format)) {
	      BX.debug('addressFormat must be instance of Format');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _addressFormat$5, props.addressFormat);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId$2, props.languageId);
	    _classPrivateMethodGet$9(babelHelpers.assertThisInitialized(_this), _initFields, _initFields2).call(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }
	  babelHelpers.createClass(Fields, [{
	    key: "render",
	    value: function render(props) {
	      if (props.address && !(props.address instanceof location_core.Address)) {
	        BX.debug('props.address must be instance of Address');
	      }
	      babelHelpers.classPrivateFieldSet(this, _address$4, props.address);
	      if (!location_core.ControlMode.isValid(props.mode)) {
	        BX.debug('props.mode must be valid ControlMode');
	      }
	      babelHelpers.classPrivateFieldSet(this, _mode$3, props.mode);
	      if (!main_core.Type.isDomNode(props.container)) {
	        BX.debug('props.container must be dom node');
	      }
	      babelHelpers.classPrivateFieldSet(this, _container$2, props.container);
	      var _iterator = _createForOfIteratorHelper$3(babelHelpers.classPrivateFieldGet(this, _fields)),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var field = _step.value;
	          var value = babelHelpers.classPrivateFieldGet(this, _address$4) ? babelHelpers.classPrivateFieldGet(this, _address$4).getFieldValue(field.type) : '';
	          if (babelHelpers.classPrivateFieldGet(this, _mode$3) === location_core.ControlMode.view && !value) {
	            continue;
	          }
	          var item = field.render({
	            value: value,
	            mode: babelHelpers.classPrivateFieldGet(this, _mode$3)
	          });
	          babelHelpers.classPrivateFieldGet(this, _container$2).appendChild(item);
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }, {
	    key: "subscribeOnAddressChangedEvent",
	    value: function subscribeOnAddressChangedEvent(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$3(Fields, Fields, _onAddressChangedEvent$1), listener);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Event.unbindAll(this);
	      var _iterator2 = _createForOfIteratorHelper$3(babelHelpers.classPrivateFieldGet(this, _fields)),
	        _step2;
	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var field = _step2.value;
	          field.destroy();
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _container$2));
	    }
	  }, {
	    key: "subscribeOnStateChangedEvent",
	    value: function subscribeOnStateChangedEvent(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$3(Fields, Fields, _onStateChangedEvent$2), listener);
	    }
	  }, {
	    key: "address",
	    set: function set(address) {
	      if (address && !(address instanceof location_core.Address)) {
	        BX.debug('address must be instance of Address');
	      }
	      babelHelpers.classPrivateFieldSet(this, _address$4, address);
	      var _iterator3 = _createForOfIteratorHelper$3(babelHelpers.classPrivateFieldGet(this, _fields)),
	        _step3;
	      try {
	        for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	          var field = _step3.value;
	          field.value = babelHelpers.classPrivateFieldGet(this, _address$4) ? babelHelpers.classPrivateFieldGet(this, _address$4).getFieldValue(field.type) : '';
	        }
	      } catch (err) {
	        _iterator3.e(err);
	      } finally {
	        _iterator3.f();
	      }
	    }
	  }, {
	    key: "state",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _state$3);
	    }
	  }]);
	  return Fields;
	}(main_core_events.EventEmitter);
	function _initFields2() {
	  var _this2 = this;
	  var _loop = function _loop() {
	    if (!babelHelpers.classPrivateFieldGet(_this2, _addressFormat$5).fieldCollection.fields.hasOwnProperty(type)) {
	      return "continue";
	    }
	    var formatField = babelHelpers.classPrivateFieldGet(_this2, _addressFormat$5).fieldCollection.fields[type];
	    var field = new Field({
	      title: formatField.name,
	      type: formatField.type,
	      sort: formatField.sort
	    });
	    field.subscribeOnValueChangedEvent(function (event) {
	      _classPrivateMethodGet$9(_this2, _onFieldChanged, _onFieldChanged2).call(_this2, field);
	    });
	    field.subscribeOnStateChangedEvent(function (event) {
	      var data = event.getData();
	      _classPrivateMethodGet$9(_this2, _setState$2, _setState2$2).call(_this2, data.state);
	    });
	    babelHelpers.classPrivateFieldGet(_this2, _fields).push(field);
	  };
	  for (var type in babelHelpers.classPrivateFieldGet(this, _addressFormat$5).fieldCollection.fields) {
	    var _ret = _loop();
	    if (_ret === "continue") continue;
	  }
	  babelHelpers.classPrivateFieldGet(this, _fields).sort(function (a, b) {
	    return a.sort - b.sort;
	  });
	}
	function _onFieldChanged2(field) {
	  if (!babelHelpers.classPrivateFieldGet(this, _address$4)) {
	    babelHelpers.classPrivateFieldSet(this, _address$4, new location_core.Address({
	      languageId: babelHelpers.classPrivateFieldGet(this, _languageId$2)
	    }));
	  }
	  babelHelpers.classPrivateFieldGet(this, _address$4).setFieldValue(field.type, field.value);
	  this.emit(_classStaticPrivateFieldSpecGet$3(Fields, Fields, _onAddressChangedEvent$1), {
	    address: babelHelpers.classPrivateFieldGet(this, _address$4),
	    changedField: field
	  });
	}
	function _setState2$2(state) {
	  babelHelpers.classPrivateFieldSet(this, _state$3, state);
	  this.emit(_classStaticPrivateFieldSpecGet$3(Fields, Fields, _onStateChangedEvent$2), {
	    state: babelHelpers.classPrivateFieldGet(this, _state$3)
	  });
	}
	var _onAddressChangedEvent$1 = {
	  writable: true,
	  value: 'onAddressChanged'
	};
	var _onStateChangedEvent$2 = {
	  writable: true,
	  value: 'onStateChanged'
	};

	function _classPrivateFieldInitSpec$c(obj, privateMap, value) { _checkPrivateRedeclaration$d(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$d(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	/**
	 * Complex address widget
	 */
	var _map$1 = /*#__PURE__*/new WeakMap();
	var _mapBindElement = /*#__PURE__*/new WeakMap();
	var _addressWidget = /*#__PURE__*/new WeakMap();
	var MapFeature = /*#__PURE__*/function (_BaseFeature) {
	  babelHelpers.inherits(MapFeature, _BaseFeature);
	  function MapFeature(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, MapFeature);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MapFeature).call(this, props));
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _map$1, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _mapBindElement, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$c(babelHelpers.assertThisInitialized(_this), _addressWidget, {
	      writable: true,
	      value: null
	    });
	    if (!(props.map instanceof MapPopup)) {
	      BX.debug('props.map must be instance of MapPopup');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map$1, props.map);
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _map$1).onChangedEventSubscribe(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget).setAddressByFeature(data.address, babelHelpers.assertThisInitialized(_this));
	    });
	    return _this;
	  }
	  babelHelpers.createClass(MapFeature, [{
	    key: "showMap",
	    value: function showMap() {
	      var useUserLocation = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      if (!babelHelpers.classPrivateFieldGet(this, _map$1).isShown()) {
	        babelHelpers.classPrivateFieldGet(this, _map$1).show(useUserLocation);
	      }
	    }
	  }, {
	    key: "closeMap",
	    value: function closeMap() {
	      if (babelHelpers.classPrivateFieldGet(this, _map$1).isShown()) {
	        babelHelpers.classPrivateFieldGet(this, _map$1).close();
	      }
	      babelHelpers.classPrivateFieldGet(this, _map$1).bindelement = babelHelpers.classPrivateFieldGet(this, _mapBindElement);
	    }
	  }, {
	    key: "resetView",
	    value: function resetView() {
	      this.closeMap();
	    }
	    /**
	     * Render Widget
	     * @param {Object} props
	     */
	  }, {
	    key: "render",
	    value: function render(props) {
	      if (!main_core.Type.isDomNode(props.mapBindElement)) {
	        BX.debug('props.mapBindElement  must be instance of Element');
	      }
	      babelHelpers.classPrivateFieldSet(this, _mapBindElement, props.mapBindElement);
	      babelHelpers.classPrivateFieldGet(this, _map$1).render({
	        bindElement: props.mapBindElement,
	        address: babelHelpers.classPrivateFieldGet(this, _addressWidget).address,
	        mode: babelHelpers.classPrivateFieldGet(this, _addressWidget).mode
	      });
	    }
	  }, {
	    key: "setAddress",
	    value: function setAddress(address) {
	      if (this.addressWidget.state === State.DATA_INPUTTING) {
	        return;
	      }
	      babelHelpers.classPrivateFieldGet(this, _map$1).address = address;
	    }
	  }, {
	    key: "setAddressWidget",
	    value: function setAddressWidget(addressWidget) {
	      babelHelpers.classPrivateFieldSet(this, _addressWidget, addressWidget);
	    }
	  }, {
	    key: "setMode",
	    value: function setMode(mode) {
	      babelHelpers.classPrivateFieldGet(this, _map$1).mode = mode;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.classPrivateFieldGet(this, _map$1).destroy();
	      babelHelpers.classPrivateFieldSet(this, _map$1, null);
	    }
	  }, {
	    key: "map",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _map$1);
	    }
	  }, {
	    key: "addressWidget",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _addressWidget);
	    }
	  }, {
	    key: "mapBindElement",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _mapBindElement);
	    }
	  }]);
	  return MapFeature;
	}(BaseFeature);

	function _classPrivateFieldInitSpec$d(obj, privateMap, value) { _checkPrivateRedeclaration$e(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$e(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	/**
	 * Complex address widget
	 */
	var _autocomplete = /*#__PURE__*/new WeakMap();
	var _addressWidget$1 = /*#__PURE__*/new WeakMap();
	var AutocompleteFeature = /*#__PURE__*/function (_BaseFeature) {
	  babelHelpers.inherits(AutocompleteFeature, _BaseFeature);
	  function AutocompleteFeature(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, AutocompleteFeature);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AutocompleteFeature).call(this, props));
	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _autocomplete, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$d(babelHelpers.assertThisInitialized(_this), _addressWidget$1, {
	      writable: true,
	      value: null
	    });
	    if (!(props.autocomplete instanceof Autocomplete)) {
	      BX.debug('props.autocomplete  must be instance of Autocomplete');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _autocomplete, props.autocomplete);
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _autocomplete).onAddressChangedEventSubscribe(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget$1).setAddressByFeature(data.address, babelHelpers.assertThisInitialized(_this), data.excludeSetAddressFeatures, data.options);
	    });
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _autocomplete).onStateChangedEventSubscribe(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget$1).setStateByFeature(data.state);
	    });
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _autocomplete).onSearchStartedEventSubscribe(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget$1).emitFeatureEvent({
	        feature: babelHelpers.assertThisInitialized(_this),
	        eventCode: AutocompleteFeature.searchStartedEvent,
	        payload: data
	      });
	    });
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _autocomplete).onSearchCompletedEventSubscribe(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget$1).emitFeatureEvent({
	        feature: babelHelpers.assertThisInitialized(_this),
	        eventCode: AutocompleteFeature.searchCompletedEvent,
	        payload: data
	      });
	    });
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _autocomplete).onShowOnMapClickedEventSubscribe(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget$1).emitFeatureEvent({
	        feature: babelHelpers.assertThisInitialized(_this),
	        eventCode: AutocompleteFeature.showOnMapClickedEvent,
	        payload: data
	      });
	    });
	    return _this;
	  }
	  babelHelpers.createClass(AutocompleteFeature, [{
	    key: "resetView",
	    value: function resetView() {
	      babelHelpers.classPrivateFieldGet(this, _autocomplete).closePrompt();
	    }
	  }, {
	    key: "render",
	    value: function render(props) {
	      if (babelHelpers.classPrivateFieldGet(this, _addressWidget$1).mode === location_core.ControlMode.edit) {
	        babelHelpers.classPrivateFieldGet(this, _autocomplete).render({
	          inputNode: babelHelpers.classPrivateFieldGet(this, _addressWidget$1).inputNode,
	          menuNode: props.autocompleteMenuElement,
	          address: babelHelpers.classPrivateFieldGet(this, _addressWidget$1).address,
	          mode: babelHelpers.classPrivateFieldGet(this, _addressWidget$1).mode
	        });
	      }
	    }
	  }, {
	    key: "setAddress",
	    value: function setAddress(address) {
	      babelHelpers.classPrivateFieldGet(this, _autocomplete).address = address;
	    }
	  }, {
	    key: "setAddressWidget",
	    value: function setAddressWidget(addressWidget) {
	      babelHelpers.classPrivateFieldSet(this, _addressWidget$1, addressWidget);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.classPrivateFieldGet(this, _autocomplete).destroy();
	      babelHelpers.classPrivateFieldSet(this, _autocomplete, null);
	    }
	  }]);
	  return AutocompleteFeature;
	}(BaseFeature);
	babelHelpers.defineProperty(AutocompleteFeature, "searchStartedEvent", 'searchStarted');
	babelHelpers.defineProperty(AutocompleteFeature, "searchCompletedEvent", 'searchCompleted');
	babelHelpers.defineProperty(AutocompleteFeature, "showOnMapClickedEvent", 'showOnMapClicked');

	function _classPrivateFieldInitSpec$e(obj, privateMap, value) { _checkPrivateRedeclaration$f(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$f(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	/**
	 * Fields widget feature
	 */
	var _fields$1 = /*#__PURE__*/new WeakMap();
	var _addressWidget$2 = /*#__PURE__*/new WeakMap();
	var FieldsFeature = /*#__PURE__*/function (_BaseFeature) {
	  babelHelpers.inherits(FieldsFeature, _BaseFeature);
	  function FieldsFeature(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, FieldsFeature);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldsFeature).call(this, props));
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _fields$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$e(babelHelpers.assertThisInitialized(_this), _addressWidget$2, {
	      writable: true,
	      value: null
	    });
	    if (!(props.fields instanceof Fields)) {
	      BX.debug('props.Fields must be instance of Fields');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _fields$1, props.fields);
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _fields$1).subscribeOnAddressChangedEvent(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget$2).setAddressByFeature(data.address, babelHelpers.assertThisInitialized(_this));
	    });
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _fields$1).subscribeOnStateChangedEvent(function (event) {
	      var data = event.getData();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _addressWidget$2).setStateByFeature(data.state);
	    });
	    return _this;
	  }
	  babelHelpers.createClass(FieldsFeature, [{
	    key: "render",
	    value: function render(props) {
	      if (babelHelpers.classPrivateFieldGet(this, _addressWidget$2).mode === location_core.ControlMode.edit) {
	        if (!main_core.Type.isDomNode(props.fieldsContainer)) {
	          BX.debug('props.fieldsContainer  must be instance of Element');
	        }
	        babelHelpers.classPrivateFieldGet(this, _fields$1).render({
	          address: babelHelpers.classPrivateFieldGet(this, _addressWidget$2).address,
	          mode: babelHelpers.classPrivateFieldGet(this, _addressWidget$2).mode,
	          container: props.fieldsContainer
	        });
	      }
	    }
	  }, {
	    key: "setAddressWidget",
	    value: function setAddressWidget(addressWidget) {
	      babelHelpers.classPrivateFieldSet(this, _addressWidget$2, addressWidget);
	    }
	  }, {
	    key: "setAddress",
	    value: function setAddress(address) {
	      babelHelpers.classPrivateFieldGet(this, _fields$1).address = address;
	    }
	  }, {
	    key: "setMode",
	    value: function setMode(mode) {
	      babelHelpers.classPrivateFieldGet(this, _fields$1).mode = mode;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.classPrivateFieldGet(this, _fields$1).destroy();
	      babelHelpers.classPrivateFieldSet(this, _fields$1, null);
	    }
	  }]);
	  return FieldsFeature;
	}(BaseFeature);

	function _classPrivateMethodInitSpec$a(obj, privateSet) { _checkPrivateRedeclaration$g(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$f(obj, privateMap, value) { _checkPrivateRedeclaration$g(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$g(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$a(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Map feature for the address widget with auto map opening / closing behavior
	 */
	var _showMapTimerId = /*#__PURE__*/new WeakMap();
	var _showMapDelay = /*#__PURE__*/new WeakMap();
	var _closeMapTimerId = /*#__PURE__*/new WeakMap();
	var _closeMapDelay = /*#__PURE__*/new WeakMap();
	var _isDestroyed$1 = /*#__PURE__*/new WeakMap();
	var _onControlWrapperClick = /*#__PURE__*/new WeakSet();
	var _onDocumentClick$1 = /*#__PURE__*/new WeakSet();
	var _processOnMouseOver = /*#__PURE__*/new WeakSet();
	var _processOnMouseOut = /*#__PURE__*/new WeakSet();
	var MapFeatureAuto = /*#__PURE__*/function (_MapFeature) {
	  babelHelpers.inherits(MapFeatureAuto, _MapFeature);
	  function MapFeatureAuto() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, MapFeatureAuto);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(MapFeatureAuto)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    _classPrivateMethodInitSpec$a(babelHelpers.assertThisInitialized(_this), _processOnMouseOut);
	    _classPrivateMethodInitSpec$a(babelHelpers.assertThisInitialized(_this), _processOnMouseOver);
	    _classPrivateMethodInitSpec$a(babelHelpers.assertThisInitialized(_this), _onDocumentClick$1);
	    _classPrivateMethodInitSpec$a(babelHelpers.assertThisInitialized(_this), _onControlWrapperClick);
	    _classPrivateFieldInitSpec$f(babelHelpers.assertThisInitialized(_this), _showMapTimerId, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$f(babelHelpers.assertThisInitialized(_this), _showMapDelay, {
	      writable: true,
	      value: 700
	    });
	    _classPrivateFieldInitSpec$f(babelHelpers.assertThisInitialized(_this), _closeMapTimerId, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$f(babelHelpers.assertThisInitialized(_this), _closeMapDelay, {
	      writable: true,
	      value: 800
	    });
	    _classPrivateFieldInitSpec$f(babelHelpers.assertThisInitialized(_this), _isDestroyed$1, {
	      writable: true,
	      value: false
	    });
	    return _this;
	  }
	  babelHelpers.createClass(MapFeatureAuto, [{
	    key: "render",
	    /**
	     * Render Widget
	     * @param {AddressRenderProps} props
	     */
	    value: function render(props) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(MapFeatureAuto.prototype), "render", this).call(this, props);
	      this.addressWidget.controlWrapper.addEventListener('click', _classPrivateMethodGet$a(this, _onControlWrapperClick, _onControlWrapperClick2).bind(this));
	      this.addressWidget.controlWrapper.addEventListener('mouseover', _classPrivateMethodGet$a(this, _processOnMouseOver, _processOnMouseOver2).bind(this));
	      this.addressWidget.controlWrapper.addEventListener('mouseout', _classPrivateMethodGet$a(this, _processOnMouseOut, _processOnMouseOut2).bind(this));
	      document.addEventListener('click', _classPrivateMethodGet$a(this, _onDocumentClick$1, _onDocumentClick2$1).bind(this));
	      this.map.onMouseOverSubscribe(_classPrivateMethodGet$a(this, _processOnMouseOver, _processOnMouseOver2).bind(this));
	      this.map.onMouseOutSubscribe(_classPrivateMethodGet$a(this, _processOnMouseOut, _processOnMouseOut2).bind(this));
	    } // eslint-disable-next-line no-unused-vars
	  }, {
	    key: "setAddress",
	    value: function setAddress(address) {
	      if (!address) {
	        this.closeMap();
	      }
	      this.map.address = address;
	      if (address && this.addressWidget.state !== State.DATA_SUPPOSED) {
	        this.showMap();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _isDestroyed$1)) {
	        return;
	      }
	      document.removeEventListener('click', _classPrivateMethodGet$a(this, _onDocumentClick$1, _onDocumentClick2$1));
	      if (this.addressWidget.controlWrapper) {
	        this.addressWidget.controlWrapper.removeEventListener('click', _classPrivateMethodGet$a(this, _onControlWrapperClick, _onControlWrapperClick2));
	        this.addressWidget.controlWrapper.removeEventListener('mouseover', _classPrivateMethodGet$a(this, _processOnMouseOver, _processOnMouseOver2));
	        this.addressWidget.controlWrapper.removeEventListener('mouseout', _classPrivateMethodGet$a(this, _processOnMouseOut, _processOnMouseOut2));
	      }
	      babelHelpers.classPrivateFieldSet(this, _showMapTimerId, null);
	      babelHelpers.classPrivateFieldSet(this, _closeMapTimerId, null);
	      babelHelpers.get(babelHelpers.getPrototypeOf(MapFeatureAuto.prototype), "destroy", this).call(this);
	      babelHelpers.classPrivateFieldSet(this, _isDestroyed$1, true);
	    }
	  }]);
	  return MapFeatureAuto;
	}(MapFeature);
	function _onControlWrapperClick2(event) {
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed$1)) {
	    return;
	  }
	  if (this.addressWidget.mode === location_core.ControlMode.view) {
	    if (this.map.isShown()) {
	      this.closeMap();
	    } else {
	      clearTimeout(babelHelpers.classPrivateFieldGet(this, _showMapTimerId));
	    }
	  } else if (this.addressWidget.mode === location_core.ControlMode.edit && this._saveResourceStrategy === false) {
	    if (this.addressWidget.address && !this.map.isShown() && event.target === this.addressWidget.inputNode) {
	      this.showMap();
	    }
	  }
	}
	function _onDocumentClick2$1(event) {
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed$1)) {
	    return;
	  }
	  if (this.addressWidget.inputNode !== event.target) {
	    this.closeMap();
	  }
	}
	function _processOnMouseOver2() {
	  var _this2 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed$1)) {
	    return;
	  }
	  clearTimeout(babelHelpers.classPrivateFieldGet(this, _showMapTimerId));
	  clearTimeout(babelHelpers.classPrivateFieldGet(this, _closeMapTimerId));
	  if (this.addressWidget.mode !== location_core.ControlMode.view) {
	    return;
	  }
	  if (this.addressWidget.address && !this.map.isShown()) {
	    babelHelpers.classPrivateFieldSet(this, _showMapTimerId, setTimeout(function () {
	      _this2.showMap();
	    }, babelHelpers.classPrivateFieldGet(this, _showMapDelay)));
	  }
	}
	function _processOnMouseOut2() {
	  var _this3 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _isDestroyed$1)) {
	    return;
	  }
	  clearTimeout(babelHelpers.classPrivateFieldGet(this, _showMapTimerId));
	  clearTimeout(babelHelpers.classPrivateFieldGet(this, _closeMapTimerId));
	  if (this.addressWidget.mode !== location_core.ControlMode.view) {
	    return;
	  }
	  if (this.addressWidget.mode === location_core.ControlMode.view && this.map.isShown()) {
	    babelHelpers.classPrivateFieldSet(this, _closeMapTimerId, setTimeout(function () {
	      _this3.closeMap();
	    }, babelHelpers.classPrivateFieldGet(this, _closeMapDelay)));
	  }
	}

	/**
	 * Props type for the main fabric method
	 */
	/**
	 * Factory class with a set of tools for the address widget creation
	 */
	var Factory = /*#__PURE__*/function () {
	  function Factory() {
	    babelHelpers.classCallCheck(this, Factory);
	  }
	  babelHelpers.createClass(Factory, [{
	    key: "createAddressWidget",
	    /**
	     * Main factory method
	     * @param {FactoryCreateAddressWidgetProps} props
	     * @returns {Address}
	     */
	    value: function createAddressWidget(props) {
	      var sourceCode = props.sourceCode || BX.message('LOCATION_WIDGET_SOURCE_CODE');
	      var sourceParams = props.sourceParams || BX.message('LOCATION_WIDGET_SOURCE_PARAMS');
	      var languageId = props.languageId || BX.message('LOCATION_WIDGET_LANGUAGE_ID');
	      var sourceLanguageId = props.sourceLanguageId || BX.message('LOCATION_WIDGET_SOURCE_LANGUAGE_ID');
	      var userLocationPoint = new location_core.Location(JSON.parse(BX.message('LOCATION_WIDGET_USER_LOCATION_POINT')));
	      var addressFormat = props.addressFormat || new location_core.Format(JSON.parse(BX.message('LOCATION_WIDGET_DEFAULT_FORMAT')));
	      var presetLocationsProvider = props.presetLocationsProvider ? props.presetLocationsProvider : function () {
	        return props.presetLocationList ? props.presetLocationList : [];
	      };
	      var features = [];
	      if (!props.useFeatures || props.useFeatures.fields !== false) {
	        features.push(this.createFieldsFeature({
	          addressFormat: addressFormat,
	          languageId: languageId
	        }));
	      }
	      var source = null;
	      if (sourceCode && sourceParams) {
	        try {
	          source = location_source.Factory.create(sourceCode, languageId, sourceLanguageId, sourceParams);
	        } catch (e) {
	          if (e instanceof location_core.SourceCreationError) {
	            source = null;
	          } else {
	            throw e;
	          }
	        }
	      }
	      var mapFeature = null;
	      if (source) {
	        if (!props.useFeatures || props.useFeatures.autocomplete !== false) {
	          features.push(this.createAutocompleteFeature({
	            languageId: languageId,
	            addressFormat: addressFormat,
	            source: source,
	            userLocationPoint: userLocationPoint,
	            presetLocationsProvider: presetLocationsProvider
	          }));
	        }
	        if (!props.useFeatures || props.useFeatures.map !== false) {
	          var showPhotos = !!sourceParams.showPhotos;
	          var useGeocodingService = !!sourceParams.useGeocodingService;
	          var DEFAULT_THUMBNAIL_HEIGHT = 80;
	          var DEFAULT_THUMBNAIL_WIDTH = 150;
	          var DEFAULT_MAX_PHOTO_COUNT = showPhotos ? 5 : 0;
	          var DEFAULT_MAP_BEHAVIOR = 'auto';
	          mapFeature = this.createMapFeature({
	            addressFormat: addressFormat,
	            source: source,
	            useGeocodingService: useGeocodingService,
	            popupOptions: props.popupOptions,
	            popupBindOptions: props.popupBindOptions,
	            thumbnailHeight: props.thumbnailHeight || DEFAULT_THUMBNAIL_HEIGHT,
	            thumbnailWidth: props.thumbnailWidth || DEFAULT_THUMBNAIL_WIDTH,
	            maxPhotoCount: props.maxPhotoCount || DEFAULT_MAX_PHOTO_COUNT,
	            mapBehavior: props.mapBehavior || DEFAULT_MAP_BEHAVIOR,
	            userLocationPoint: userLocationPoint
	          });
	          features.push(mapFeature);
	        }
	      }
	      var widget = new Address({
	        features: features,
	        address: props.address,
	        mode: props.mode,
	        addressFormat: addressFormat,
	        languageId: languageId
	      });
	      if (mapFeature) {
	        widget.subscribeOnFeatureEvent(function (event) {
	          var data = event.getData();
	          if (data.feature instanceof AutocompleteFeature && data.eventCode === AutocompleteFeature.showOnMapClickedEvent) {
	            mapFeature.showMap(true);
	          }
	        });
	      }
	      return widget;
	    }
	  }, {
	    key: "createFieldsFeature",
	    value: function createFieldsFeature(props) {
	      var fields = new Fields({
	        addressFormat: props.addressFormat,
	        languageId: props.languageId
	      });
	      return new FieldsFeature({
	        fields: fields
	      });
	    }
	  }, {
	    key: "createAutocompleteFeature",
	    value: function createAutocompleteFeature(props) {
	      var autocomplete = new Autocomplete({
	        sourceCode: props.source.sourceCode,
	        languageId: props.languageId,
	        addressFormat: props.addressFormat,
	        autocompleteService: props.source.autocompleteService,
	        userLocationPoint: props.userLocationPoint,
	        presetLocationsProvider: props.presetLocationsProvider
	      });
	      return new AutocompleteFeature({
	        autocomplete: autocomplete
	      });
	    }
	  }, {
	    key: "createMapFeature",
	    value: function createMapFeature(props) {
	      var popupOptions = {
	        cacheable: true,
	        closeByEsc: true,
	        className: "location-popup-window location-source-".concat(props.source.sourceCode),
	        animation: 'fading',
	        angle: true,
	        bindOptions: props.popupBindOptions
	      };
	      if (props.popupOptions) {
	        popupOptions = Object.assign(popupOptions, props.popupOptions);
	      }
	      var popup = new Popup(popupOptions);
	      var gallery = null;
	      if (props.maxPhotoCount > 0) {
	        gallery = new Gallery({
	          photoService: props.source.photoService,
	          thumbnailHeight: props.thumbnailHeight,
	          thumbnailWidth: props.thumbnailWidth,
	          maxPhotoCount: props.maxPhotoCount
	        });
	      }
	      var mapFeatureProps = {
	        saveResourceStrategy: props.source.sourceCode === location_google.Google.code,
	        map: new MapPopup({
	          addressFormat: props.addressFormat,
	          map: props.source.map,
	          popup: popup,
	          gallery: gallery,
	          locationRepository: new location_core.LocationRepository(),
	          geocodingService: props.useGeocodingService ? props.source.geocodingService : null,
	          userLocationPoint: props.userLocationPoint
	        })
	      };
	      var result;
	      if (props.mapBehavior === 'manual') {
	        result = new MapFeature(mapFeatureProps);
	      } else {
	        result = new MapFeatureAuto(mapFeatureProps);
	      }
	      return result;
	    }
	  }]);
	  return Factory;
	}();

	var _templateObject$7;
	function _classPrivateMethodInitSpec$b(obj, privateSet) { _checkPrivateRedeclaration$h(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$g(obj, privateMap, value) { _checkPrivateRedeclaration$h(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$h(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet$4(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$5(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$4(descriptor, "get"); return _classApplyDescriptorGet$4(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$4(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$5(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$4(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodGet$b(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _state$4 = /*#__PURE__*/new WeakMap();
	var _titleContainer = /*#__PURE__*/new WeakMap();
	var _titles = /*#__PURE__*/new WeakMap();
	var _getTitle = /*#__PURE__*/new WeakSet();
	var Switch = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Switch, _EventEmitter);
	  function Switch() {
	    var _this;
	    var props = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Switch);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Switch).call(this));
	    _classPrivateMethodInitSpec$b(babelHelpers.assertThisInitialized(_this), _getTitle);
	    _classPrivateFieldInitSpec$g(babelHelpers.assertThisInitialized(_this), _state$4, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(babelHelpers.assertThisInitialized(_this), _titleContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$g(babelHelpers.assertThisInitialized(_this), _titles, {
	      writable: true,
	      value: ['on', 'off']
	    });
	    _this.setEventNamespace('BX.Location.Widget.Switch');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _state$4, props.state);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _titles, props.titles);
	    return _this;
	  }
	  babelHelpers.createClass(Switch, [{
	    key: "render",
	    value: function render(mode) {
	      var _this2 = this;
	      babelHelpers.classPrivateFieldSet(this, _titleContainer, main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["\t\t\t\n\t\t\t<span class=\"ui-link ui-link-secondary ui-entity-editor-block-title-link\">\n\t\t\t\t", "\n\t\t\t</span>"])), _classPrivateMethodGet$b(this, _getTitle, _getTitle2).call(this)));
	      babelHelpers.classPrivateFieldGet(this, _titleContainer).addEventListener('click', function (event) {
	        _this2.state = babelHelpers.classPrivateFieldGet(_this2, _state$4) === Switch.STATE_OFF ? Switch.STATE_ON : Switch.STATE_OFF;
	        _this2.emit(_classStaticPrivateFieldSpecGet$4(Switch, Switch, _onToggleEvent), {
	          state: babelHelpers.classPrivateFieldGet(_this2, _state$4)
	        });
	        event.stopPropagation();
	        return false;
	      });
	      babelHelpers.classPrivateFieldGet(this, _titleContainer).addEventListener('mouseover', function (event) {
	        event.stopPropagation();
	      });
	      return babelHelpers.classPrivateFieldGet(this, _titleContainer);
	    }
	  }, {
	    key: "subscribeOnToggleEventSubscribe",
	    value: function subscribeOnToggleEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$4(Switch, Switch, _onToggleEvent), listener);
	    }
	  }, {
	    key: "state",
	    set: function set(state) {
	      babelHelpers.classPrivateFieldSet(this, _state$4, state);
	      if (babelHelpers.classPrivateFieldGet(this, _titleContainer)) {
	        babelHelpers.classPrivateFieldGet(this, _titleContainer).innerHTML = _classPrivateMethodGet$b(this, _getTitle, _getTitle2).call(this);
	      }
	    },
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _state$4);
	    }
	  }]);
	  return Switch;
	}(main_core_events.EventEmitter);
	function _getTitle2() {
	  return babelHelpers.classPrivateFieldGet(this, _titles)[babelHelpers.classPrivateFieldGet(this, _state$4)];
	}
	babelHelpers.defineProperty(Switch, "STATE_OFF", 0);
	babelHelpers.defineProperty(Switch, "STATE_ON", 1);
	var _onToggleEvent = {
	  writable: true,
	  value: "onToggleEvent"
	};

	var _templateObject$8;
	function _classPrivateMethodInitSpec$c(obj, privateSet) { _checkPrivateRedeclaration$i(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$h(obj, privateMap, value) { _checkPrivateRedeclaration$i(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$i(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet$5(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$6(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$5(descriptor, "get"); return _classApplyDescriptorGet$5(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$5(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$6(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$5(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodGet$c(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _type$1 = /*#__PURE__*/new WeakMap();
	var _domNode = /*#__PURE__*/new WeakMap();
	var _getClassByType = /*#__PURE__*/new WeakSet();
	var Icon = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Icon, _EventEmitter);
	  function Icon() {
	    var _this;
	    babelHelpers.classCallCheck(this, Icon);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Icon).call(this));
	    _classPrivateMethodInitSpec$c(babelHelpers.assertThisInitialized(_this), _getClassByType);
	    _classPrivateFieldInitSpec$h(babelHelpers.assertThisInitialized(_this), _type$1, {
	      writable: true,
	      value: Icon.TYPE_SEARCH
	    });
	    _classPrivateFieldInitSpec$h(babelHelpers.assertThisInitialized(_this), _domNode, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Location.Widget.Icon');
	    return _this;
	  }
	  babelHelpers.createClass(Icon, [{
	    key: "render",
	    value: function render(props) {
	      var _this2 = this;
	      babelHelpers.classPrivateFieldSet(this, _type$1, props.type);
	      babelHelpers.classPrivateFieldSet(this, _domNode, main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\"></div>"])), _classPrivateMethodGet$c(this, _getClassByType, _getClassByType2).call(this, babelHelpers.classPrivateFieldGet(this, _type$1))));
	      babelHelpers.classPrivateFieldGet(this, _domNode).addEventListener('click', function (e) {
	        _this2.emit(_classStaticPrivateFieldSpecGet$5(Icon, Icon, _onClickEvent));
	      });
	      return babelHelpers.classPrivateFieldGet(this, _domNode);
	    }
	  }, {
	    key: "subscribeOnClickEvent",
	    value: function subscribeOnClickEvent(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$5(Icon, Icon, _onClickEvent), listener);
	    }
	  }, {
	    key: "type",
	    set: function set(type) {
	      babelHelpers.classPrivateFieldSet(this, _type$1, type);
	      if (babelHelpers.classPrivateFieldGet(this, _domNode)) {
	        babelHelpers.classPrivateFieldGet(this, _domNode).className = _classPrivateMethodGet$c(this, _getClassByType, _getClassByType2).call(this, babelHelpers.classPrivateFieldGet(this, _type$1));
	      }
	    }
	  }]);
	  return Icon;
	}(main_core_events.EventEmitter);
	function _getClassByType2(iconType) {
	  var iconClass = '';
	  if (iconType === Icon.TYPE_CLEAR) {
	    iconClass = "ui-ctl-after ui-ctl-icon-btn ui-ctl-icon-clear";
	  } else if (iconType === Icon.TYPE_SEARCH) {
	    iconClass = "ui-ctl-after ui-ctl-icon-search";
	  } else if (iconType === Icon.TYPE_LOADER) {
	    iconClass = "ui-ctl-after ui-ctl-icon-loader";
	  } else {
	    BX.debug('Wrong icon type');
	  }
	  return iconClass;
	}
	var _onClickEvent = {
	  writable: true,
	  value: 'onClick'
	};
	babelHelpers.defineProperty(Icon, "TYPE_CLEAR", 'clear');
	babelHelpers.defineProperty(Icon, "TYPE_SEARCH", 'search');
	babelHelpers.defineProperty(Icon, "TYPE_LOADER", 'loader');

	var _templateObject$9, _templateObject2$7, _templateObject3$3, _templateObject4$1, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	function _classPrivateMethodInitSpec$d(obj, privateSet) { _checkPrivateRedeclaration$j(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$j(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateMethodGet$1(receiver, classConstructor, method) { _classCheckPrivateStaticAccess$7(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess$7(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classPrivateMethodGet$d(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Address field widget for the ui.entity-editor
	 */
	var _onIconClick = /*#__PURE__*/new WeakSet();
	var _onFieldsSwitchToggle = /*#__PURE__*/new WeakSet();
	var _hideFields = /*#__PURE__*/new WeakSet();
	var _showFields = /*#__PURE__*/new WeakSet();
	var _onAddressWidgetChangedState = /*#__PURE__*/new WeakSet();
	var _onAddressChanged = /*#__PURE__*/new WeakSet();
	var _convertAddressToString$3 = /*#__PURE__*/new WeakSet();
	var _getAddress = /*#__PURE__*/new WeakSet();
	var UIAddress = /*#__PURE__*/function (_BX$UI$EntityEditorFi) {
	  babelHelpers.inherits(UIAddress, _BX$UI$EntityEditorFi);
	  function UIAddress(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, UIAddress);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UIAddress).call(this, props));
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _getAddress);
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _convertAddressToString$3);
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _onAddressChanged);
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _onAddressWidgetChangedState);
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _showFields);
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _hideFields);
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _onFieldsSwitchToggle);
	    _classPrivateMethodInitSpec$d(babelHelpers.assertThisInitialized(_this), _onIconClick);
	    _this._input = null;
	    _this._inputIcon = null;
	    _this._hiddenInput = null;
	    _this._innerWrapper = null;
	    _this._addressWidget = null;
	    _this._addressFieldsContainer = null;
	    return _this;
	  }
	  babelHelpers.createClass(UIAddress, [{
	    key: "initialize",
	    value: function initialize(id, settings) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(UIAddress.prototype), "initialize", this).call(this, id, settings);
	      var value = this.getValue();
	      var address = null;
	      if (main_core.Type.isStringFilled(value)) {
	        try {
	          address = new location_core.Address(JSON.parse(value));
	        } catch (e) {
	          BX.debug('Cant parse address value');
	          return;
	        }
	      }
	      var widgetFactory = new Factory();
	      this._addressWidget = widgetFactory.createAddressWidget({
	        address: address,
	        mode: this._mode === BX.UI.EntityEditorMode.edit ? location_core.ControlMode.edit : location_core.ControlMode.view,
	        popupBindOptions: {
	          position: 'right'
	        }
	      });
	      this._addressWidget.subscribeOnStateChangedEvent(_classPrivateMethodGet$d(this, _onAddressWidgetChangedState, _onAddressWidgetChangedState2).bind(this));
	      this._addressWidget.subscribeOnAddressChangedEvent(_classPrivateMethodGet$d(this, _onAddressChanged, _onAddressChanged2).bind(this));
	      this._fieldsSwitch = new Switch({
	        state: Switch.STATE_OFF,
	        titles: [BX.message('LOCATION_WIDGET_AUI_MORE'), BX.message('LOCATION_WIDGET_AUI_BRIEFLY')]
	      });
	      this._fieldsSwitch.subscribeOnToggleEventSubscribe(_classPrivateMethodGet$d(this, _onFieldsSwitchToggle, _onFieldsSwitchToggle2).bind(this));
	    }
	  }, {
	    key: "focus",
	    value: function focus() {
	      if (!this._input) {
	        return;
	      }
	      BX.focus(this._input);
	      BX.UI.EditorTextHelper.getCurrent().setPositionAtEnd(this._input);
	    }
	  }, {
	    key: "getModeSwitchType",
	    value: function getModeSwitchType(mode) {
	      var result = BX.UI.EntityEditorModeSwitchType.common;
	      if (mode === BX.UI.EntityEditorMode.edit) {
	        // eslint-disable-next-line no-bitwise
	        result |= BX.UI.EntityEditorModeSwitchType.button | BX.UI.EntityEditorModeSwitchType.content;
	      }
	      return result;
	    }
	  }, {
	    key: "doSetMode",
	    value: function doSetMode(mode) {
	      this._addressWidget.mode = mode === BX.UI.EntityEditorMode.edit ? location_core.ControlMode.edit : location_core.ControlMode.view;
	      this._fieldsSwitch.state = Switch.STATE_OFF;
	    }
	  }, {
	    key: "getContentWrapper",
	    value: function getContentWrapper() {
	      return this._innerWrapper;
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      if (!this.isEditable()) {
	        return;
	      }
	      var address = _classPrivateMethodGet$d(this, _getAddress, _getAddress2).call(this);
	      this._model.setField(this.getName(), address ? address.toJson() : '');
	      this._addressWidget.resetView();
	    }
	  }, {
	    key: "showError",
	    value: function showError(error, anchor) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(UIAddress.prototype), "showError", this).apply(this, [error, anchor]);
	      if (this._input) {
	        BX.addClass(this._inputContainer, 'ui-ctl-danger');
	      }
	    }
	  }, {
	    key: "clearError",
	    value: function clearError() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(UIAddress.prototype), "clearError", this).apply(this);
	      if (this._input) {
	        BX.removeClass(this._inputContainer, 'ui-ctl-danger');
	      }
	    }
	  }, {
	    key: "doClearLayout",
	    value: function doClearLayout(options) {
	      this._input = null;
	      this._innerWrapper = null;
	      this._inputContainer = null;
	      this._addressFieldsContainer = null;
	      this._inputIcon = null;
	      this._hiddenInput = null;
	      main_core.Dom.clean(this._innerWrapper);
	    }
	  }, {
	    key: "validate",
	    value: function validate(result) {
	      if (!(this._mode === BX.UI.EntityEditorMode.edit && this._input)) {
	        throw Error('BX.Location.UIAddress. Invalid validation context');
	      }
	      this.clearError();
	      if (this.hasValidators()) {
	        return this.executeValidators(result);
	      }
	      var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== '';
	      if (!isValid) {
	        result.addError(BX.UI.EntityValidationError.create({
	          field: this
	        }));
	        this.showRequiredFieldError(this._input);
	      }
	      return isValid;
	    }
	  }, {
	    key: "getRuntimeValue",
	    value: function getRuntimeValue() {
	      return this._mode === BX.UI.EntityEditorMode.edit ? _classPrivateMethodGet$d(this, _getAddress, _getAddress2).call(this) : null;
	    }
	  }, {
	    key: "layout",
	    value: function layout(options) {
	      if (this._hasLayout) {
	        return;
	      }
	      this.ensureWrapperCreated({
	        classNames: ['ui-entity-card-content-block-field-phone']
	      });
	      this.adjustWrapper();
	      var title = this.getTitle();
	      if (this.isDragEnabled()) {
	        this._wrapper.appendChild(this.createDragButton());
	      }
	      var addressWidgetParams = {};
	      if (this._mode === BX.UI.EntityEditorMode.edit) {
	        this._wrapper.appendChild(this.createTitleNode(title));
	        this._input = main_core.Tag.render(_templateObject$9 || (_templateObject$9 = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element ui-ctl-textbox\" value=\"\" type=\"text\" autocomplete=\"off\" name=\"", "\">"])), "".concat(this.getName(), "_STRING"));
	        this._hiddenInput = main_core.Tag.render(_templateObject2$7 || (_templateObject2$7 = babelHelpers.taggedTemplateLiteral(["<input value='", "' type=\"hidden\" name=\"", "\">"])), this.getValue(), this.getName());
	        this._inputIcon = new Icon();
	        this._inputIcon.subscribeOnClickEvent(_classPrivateMethodGet$d(this, _onIconClick, _onIconClick2).bind(this));
	        var inputIconNode = this._inputIcon.render({
	          type: _classStaticPrivateMethodGet$1(UIAddress, UIAddress, _chooseInputIconTypeByAddress).call(UIAddress, _classPrivateMethodGet$d(this, _getAddress, _getAddress2).call(this))
	        });
	        this._inputContainer = main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-w100 ui-ctl-after-icon\">", "", "", "</div>"])), inputIconNode, this._input, this._hiddenInput);
	        this._titleWrapper.appendChild(main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["", ""])), this._fieldsSwitch.render(this._mode)));
	        this._innerWrapper = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\t\t\t\t\t\t    \n\t\t\t\t<div class=\"location-search-control-block\">\t\t\t\t\t\n\t\t\t\t\t", "\n\t\t\t\t</div>"])), this._inputContainer);
	        addressWidgetParams.inputNode = this._input;
	        addressWidgetParams.mapBindElement = inputIconNode;
	        this._addressFieldsContainer = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"location-fields-control-block\"></div>"])));
	        if (this._fieldsSwitch.state === Switch.STATE_ON) {
	          this._addressFieldsContainer.classList.add('visible');
	        }
	        addressWidgetParams.fieldsContainer = this._addressFieldsContainer;
	        this._innerWrapper.appendChild(this._addressFieldsContainer);
	      } else
	        // if(this._mode === BX.UI.EntityEditorMode.view)
	        {
	          this._wrapper.appendChild(this.createTitleNode(title));
	          var addressStringNode;
	          if (this.hasContentToDisplay()) {
	            var addressString = _classPrivateMethodGet$d(this, _convertAddressToString$3, _convertAddressToString2$3).call(this, _classPrivateMethodGet$d(this, _getAddress, _getAddress2).call(this));
	            addressStringNode = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-link ui-link-dark ui-link-dotted\">", "</span>"])), addressString);
	            this._innerWrapper = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"location-search-control-block\">\n\t\t\t\t\t\t<div class=\"ui-entity-editor-content-block-text\">\n\t\t\t\t\t\t\t", "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>"])), addressStringNode);
	            addressWidgetParams.mapBindElement = addressStringNode;
	          } else {
	            this._innerWrapper = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div class=\"location-search-control-block\">\n\t\t\t\t\t", "\n\t\t\t\t</div>"])), BX.message('UI_ENTITY_EDITOR_FIELD_EMPTY'));
	            addressWidgetParams.mapBindElement = this._innerWrapper;
	          }
	        }
	      addressWidgetParams.controlWrapper = this._innerWrapper;
	      this._addressWidget.render(addressWidgetParams);
	      this._wrapper.appendChild(this._innerWrapper);
	      this._addressWidget.subscribeOnErrorEvent(this.errorListener.bind(this));
	      if (this.isContextMenuEnabled()) {
	        this._wrapper.appendChild(this.createContextMenuButton());
	      }
	      if (this.isDragEnabled()) {
	        this.initializeDragDropAbilities();
	      }
	      this.registerLayout(options);
	      this._hasLayout = true;
	    }
	  }, {
	    key: "errorListener",
	    value: function errorListener(event) {
	      var _this2 = this;
	      var data = event.getData();
	      var errors = data.errors;
	      if (this._inputIcon) {
	        this._inputIcon.type = Icon.TYPE_CLEAR;
	      }
	      if (!main_core.Type.isArray(errors)) {
	        return;
	      }

	      // todo: this.showError supports only one error
	      errors.forEach(function (error) {
	        var message;
	        if (error.message) {
	          message = error.message;
	        } else {
	          message = BX.message('LOCATION_WIDGET_AUI_UNKNOWN_ERROR');
	        }
	        if (error.code) {
	          message += " [".concat(error.code, "]");
	        }
	        _this2.showError(message);
	      });
	    }
	  }, {
	    key: "processModelChange",
	    value: function processModelChange(params) {
	      if (BX.prop.get(params, 'originator', null) === this) {
	        return;
	      }
	      if (!BX.prop.getBoolean(params, 'forAll', false) && BX.prop.getString(params, 'name', '') !== this.getName()) {
	        return;
	      }
	      this.refreshLayout();
	    }
	  }], [{
	    key: "create",
	    value: function create(id, settings) {
	      var self = new UIAddress();
	      self.initialize(id, settings);
	      return self;
	    }
	  }, {
	    key: "registerField",
	    value: function registerField() {
	      if (typeof BX.UI.EntityEditorControlFactory !== 'undefined') {
	        BX.UI.EntityEditorControlFactory.registerFactoryMethod('address', UIAddress.registerFieldMethod);
	      } else {
	        BX.addCustomEvent('BX.UI.EntityEditorControlFactory:onInitialize', function (params, eventArgs) {
	          eventArgs.methods.address = UIAddress.registerFieldMethod;
	        });
	      }
	    }
	  }, {
	    key: "registerFieldMethod",
	    value: function registerFieldMethod(type, controlId, settings) {
	      var result = null;
	      if (type === 'address') {
	        result = UIAddress.create(controlId, settings);
	      }
	      return result;
	    }
	  }]);
	  return UIAddress;
	}(BX.UI.EntityEditorField);
	function _onIconClick2() {
	  if (this._input.value !== '') {
	    this._input.value = '';
	    this._addressWidget.address = null;
	    this._inputIcon.type = Icon.TYPE_SEARCH;
	  }
	  if (this.hasError()) {
	    this.clearError();
	  }
	}
	function _onFieldsSwitchToggle2(event) {
	  var data = event.getData();
	  var state = data.state;
	  if (state === Switch.STATE_OFF) {
	    _classPrivateMethodGet$d(this, _hideFields, _hideFields2).call(this);
	  } else {
	    _classPrivateMethodGet$d(this, _showFields, _showFields2).call(this);
	  }
	  this._addressWidget.resetView();
	}
	function _hideFields2() {
	  if (this._addressFieldsContainer) {
	    this._addressFieldsContainer.classList.remove('visible');
	  }
	}
	function _showFields2() {
	  if (this._addressFieldsContainer) {
	    this._addressFieldsContainer.classList.add('visible');
	  }
	}
	function _onAddressWidgetChangedState2(event) {
	  var data = event.getData();
	  var state = data.state;
	  var iconType;
	  if (data.state === location_widget.State.DATA_LOADING) {
	    iconType = Icon.TYPE_LOADER;
	  } else {
	    if (data.state === location_widget.State.DATA_INPUTTING) {
	      this.markAsChanged();
	    }
	    iconType = _classStaticPrivateMethodGet$1(UIAddress, UIAddress, _chooseInputIconTypeByAddress).call(UIAddress, _classPrivateMethodGet$d(this, _getAddress, _getAddress2).call(this));
	  }
	  this._inputIcon.type = iconType;
	}
	function _onAddressChanged2(event) {
	  var data = event.getData();
	  var address = data.address;
	  if (this._hiddenInput) {
	    this._hiddenInput.value = address ? address.toJson() : '';
	    this.markAsChanged();
	  }
	  if (this._inputIcon) {
	    this._inputIcon.type = _classStaticPrivateMethodGet$1(UIAddress, UIAddress, _chooseInputIconTypeByAddress).call(UIAddress, address);
	  }
	}
	function _chooseInputIconTypeByAddress(address) {
	  return address ? Icon.TYPE_CLEAR : Icon.TYPE_SEARCH;
	}
	function _convertAddressToString2$3(address) {
	  if (!address) {
	    return '';
	  }
	  return address.toString(this._addressWidget.addressFormat);
	}
	function _getAddress2() {
	  return this._addressWidget.address;
	}

	// Register fields for ui.entity-editor
	UIAddress.registerField();

	exports.Address = Address;
	exports.BaseFeature = BaseFeature;
	exports.MapFeature = MapFeature;
	exports.AutocompleteFeature = AutocompleteFeature;
	exports.FieldsFeature = FieldsFeature;
	exports.Factory = Factory;
	exports.State = State;
	exports.UIAddress = UIAddress;

}((this.BX.Location.Widget = this.BX.Location.Widget || {}),BX,BX,BX.Location.Google,BX.Main,BX.Location.Source,BX,BX.Location.Core,BX.Location.Widget,BX.Event,BX));
//# sourceMappingURL=widget.bundle.js.map
