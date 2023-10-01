this.BX = this.BX || {};
this.BX.Location = this.BX.Location || {};
(function (exports,main_core,location_core) {
	'use strict';

	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }
	function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }
	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	/**
	 * Loads google source services
	 */
	var Loader = /*#__PURE__*/function () {
	  function Loader() {
	    babelHelpers.classCallCheck(this, Loader);
	  }
	  babelHelpers.createClass(Loader, null, [{
	    key: "load",
	    /**
	     * Loads google services
	     * @param {string} apiKey
	     * @param {string} languageId
	     * @returns {Promise}
	     */
	    value: function load(apiKey, languageId) {
	      if (_classStaticPrivateFieldSpecGet(Loader, Loader, _loadingPromise) === null) {
	        _classStaticPrivateFieldSpecSet(Loader, Loader, _loadingPromise, new Promise(function (resolve) {
	          BX.load([_classStaticPrivateMethodGet(Loader, Loader, _createSrc).call(Loader, apiKey, languageId)], function () {
	            resolve();
	          });
	        }));
	      }
	      return _classStaticPrivateFieldSpecGet(Loader, Loader, _loadingPromise);
	    }
	  }]);
	  return Loader;
	}();
	function _createSrc(apiKey, languageId) {
	  return 'https://maps.googleapis.com/maps/api/js' + "?key=".concat(apiKey) + '&libraries=places' + "&language=".concat(languageId) + "&region=".concat(_classStaticPrivateMethodGet(this, Loader, _getRegion).call(this, languageId));
	}
	function _getRegion(languageId) {
	  var map = {
	    'en': 'US',
	    'uk': 'UA',
	    'zh': 'CN',
	    'ja': 'JP',
	    'vi': 'VN',
	    'ms': 'MY',
	    'hi': 'IN'
	  };
	  return typeof map[languageId] !== 'undefined' ? map[languageId] : languageId.toUpperCase();
	}
	var _loadingPromise = {
	  writable: true,
	  value: null
	};

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var STATUS_OK = 'OK';
	var STATUS_ZERO_RESULTS = 'ZERO_RESULTS';
	var _languageId = /*#__PURE__*/new WeakMap();
	var _googleAutocompleteService = /*#__PURE__*/new WeakMap();
	var _loaderPromise = /*#__PURE__*/new WeakMap();
	var _googleSource = /*#__PURE__*/new WeakMap();
	var _biasBoundRadius = /*#__PURE__*/new WeakMap();
	var _getPredictionPromise = /*#__PURE__*/new WeakSet();
	var _initAutocompleteService = /*#__PURE__*/new WeakSet();
	var _convertToLocationsList = /*#__PURE__*/new WeakSet();
	var _getTypeHint = /*#__PURE__*/new WeakSet();
	var AutocompleteService = /*#__PURE__*/function (_AutocompleteServiceB) {
	  babelHelpers.inherits(AutocompleteService, _AutocompleteServiceB);
	  /** {string} */

	  /** {google.maps.places.AutocompleteService} */

	  /** {Promise} */

	  /** {GoogleSource} */

	  /** {number} */

	  function AutocompleteService(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, AutocompleteService);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AutocompleteService).call(this, props));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTypeHint);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _convertToLocationsList);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _initAutocompleteService);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getPredictionPromise);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _languageId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _googleAutocompleteService, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _loaderPromise, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _googleSource, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _biasBoundRadius, {
	      writable: true,
	      value: 50000
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId, props.languageId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource, props.googleSource);
	    // Because googleSource could still be in the process of loading
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _loaderPromise, props.googleSource.loaderPromise.then(function () {
	      _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _initAutocompleteService, _initAutocompleteService2).call(babelHelpers.assertThisInitialized(_this));
	    }));
	    return _this;
	  }
	  babelHelpers.createClass(AutocompleteService, [{
	    key: "autocomplete",
	    /**
	     * Returns Promise witch  will transfer locations list
	     * @param {string} query
	     * @param {AutocompleteServiceParams} params
	     * @returns {Promise}
	     */
	    value: function autocomplete(query, params) {
	      var _this2 = this;
	      if (query === '') {
	        return new Promise(function (resolve) {
	          resolve([]);
	        });
	      }

	      // Because google.maps.places.AutocompleteService could be still in the process of loading
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise).then(function () {
	        return _classPrivateMethodGet(_this2, _getPredictionPromise, _getPredictionPromise2).call(_this2, query, params);
	      }, function (error) {
	        return BX.debug(error);
	      });
	    }
	  }]);
	  return AutocompleteService;
	}(location_core.AutocompleteServiceBase);
	function _getPredictionPromise2(query, params) {
	  var _this3 = this;
	  var queryPredictionsParams = {
	    input: query
	  };
	  if (params.biasPoint) {
	    queryPredictionsParams.location = new google.maps.LatLng(params.biasPoint.latitude, params.biasPoint.longitude);
	    queryPredictionsParams.radius = babelHelpers.classPrivateFieldGet(this, _biasBoundRadius);
	  }
	  var cachedResult = location_core.AutocompleteCache.get(Google.code, queryPredictionsParams);
	  if (cachedResult !== null) {
	    return Promise.resolve(_classPrivateMethodGet(this, _convertToLocationsList, _convertToLocationsList2).call(this, cachedResult.data.result, cachedResult.data.status));
	  }
	  return new Promise(function (resolve) {
	    babelHelpers.classPrivateFieldGet(_this3, _googleAutocompleteService).getQueryPredictions(queryPredictionsParams, function (res, status) {
	      if (status === STATUS_OK || status === STATUS_ZERO_RESULTS) {
	        location_core.AutocompleteCache.set(Google.code, queryPredictionsParams, {
	          status: status,
	          result: res
	        });
	      }
	      resolve(_classPrivateMethodGet(_this3, _convertToLocationsList, _convertToLocationsList2).call(_this3, res, status));
	    });
	  });
	}
	function _initAutocompleteService2() {
	  if (typeof google === 'undefined' || typeof google.maps.places.AutocompleteService === 'undefined') {
	    throw new Error('google.maps.places.AutocompleteService must be defined');
	  }
	  babelHelpers.classPrivateFieldSet(this, _googleAutocompleteService, new google.maps.places.AutocompleteService());
	}
	function _convertToLocationsList2(data, status) {
	  if (status === STATUS_ZERO_RESULTS) {
	    return [];
	  }
	  if (!data || status !== STATUS_OK) {
	    return false;
	  }
	  var result = [];
	  var _iterator = _createForOfIteratorHelper(data),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var item = _step.value;
	      if (item.place_id) {
	        var name = void 0;
	        if (item.structured_formatting && item.structured_formatting.main_text) {
	          name = item.structured_formatting.main_text;
	        } else {
	          name = item.description;
	        }
	        var location = new location_core.Location({
	          sourceCode: babelHelpers.classPrivateFieldGet(this, _googleSource).sourceCode,
	          externalId: item.place_id,
	          name: name,
	          languageId: babelHelpers.classPrivateFieldGet(this, _languageId)
	        });
	        if (item.structured_formatting && item.structured_formatting.secondary_text) {
	          location.setFieldValue(location_core.LocationType.TMP_TYPE_CLARIFICATION, item.structured_formatting.secondary_text);
	        }
	        var typeHint = _classPrivateMethodGet(this, _getTypeHint, _getTypeHint2).call(this, item.types);
	        if (typeHint) {
	          location.setFieldValue(location_core.LocationType.TMP_TYPE_HINT, _classPrivateMethodGet(this, _getTypeHint, _getTypeHint2).call(this, item.types));
	        }
	        result.push(location);
	      }
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  return result;
	}
	function _getTypeHint2(types) {
	  var result = '';
	  if (types.indexOf('locality') >= 0) {
	    result = main_core.Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_LOCALITY');
	  } else if (types.indexOf('sublocality') >= 0) {
	    result = main_core.Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_SUBLOCAL');
	  } else if (types.indexOf('store') >= 0) {
	    result = main_core.Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_STORE');
	  } else if (types.indexOf('restaurant') >= 0) {
	    result = main_core.Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_RESTAURANT');
	  } else if (types.indexOf('cafe') >= 0) {
	    result = main_core.Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_CAFE');
	  }
	  return result;
	}

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet$1(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$1(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$1(descriptor, "get"); return _classApplyDescriptorGet$1(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$1(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$1(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$1(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Class for the autocomplete locations and addresses inputs
	 */
	var _languageId$1 = /*#__PURE__*/new WeakMap();
	var _googleMap = /*#__PURE__*/new WeakMap();
	var _googleSource$1 = /*#__PURE__*/new WeakMap();
	var _zoom = /*#__PURE__*/new WeakMap();
	var _locationMarker = /*#__PURE__*/new WeakMap();
	var _mode = /*#__PURE__*/new WeakMap();
	var _location = /*#__PURE__*/new WeakMap();
	var _geocoder = /*#__PURE__*/new WeakMap();
	var _locationRepository = /*#__PURE__*/new WeakMap();
	var _timerId = /*#__PURE__*/new WeakMap();
	var _isUpdating = /*#__PURE__*/new WeakMap();
	var _changeDelay = /*#__PURE__*/new WeakMap();
	var _loaderPromise$1 = /*#__PURE__*/new WeakMap();
	var _convertLocationToPosition = /*#__PURE__*/new WeakSet();
	var _adjustZoom = /*#__PURE__*/new WeakSet();
	var _getPositionToLocationPromise = /*#__PURE__*/new WeakSet();
	var _emitOnLocationChangedEvent = /*#__PURE__*/new WeakSet();
	var _onMarkerUpdatePosition = /*#__PURE__*/new WeakSet();
	var _createTimer = /*#__PURE__*/new WeakSet();
	var _fulfillOnChangedEvent = /*#__PURE__*/new WeakSet();
	var _onMapClick = /*#__PURE__*/new WeakSet();
	var _initGoogleMap = /*#__PURE__*/new WeakSet();
	var Map = /*#__PURE__*/function (_MapBase) {
	  babelHelpers.inherits(Map, _MapBase);
	  /** {string} */

	  /** {google.maps.Map} */

	  /** {GoogleSource} */

	  /** {number} */

	  /** {google.maps.Marker} */

	  /** {ControlMode} */

	  /** Location */

	  function Map(_props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Map);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Map).call(this, _props));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _initGoogleMap);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onMapClick);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _fulfillOnChangedEvent);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _createTimer);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onMarkerUpdatePosition);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _emitOnLocationChangedEvent);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getPositionToLocationPromise);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _adjustZoom);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _convertLocationToPosition);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _languageId$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _googleMap, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _googleSource$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _zoom, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _locationMarker, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _mode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _location, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _geocoder, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _locationRepository, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _timerId, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _isUpdating, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _changeDelay, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _loaderPromise$1, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId$1, _props.languageId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource$1, _props.googleSource);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _locationRepository, _props.locationRepository || new location_core.LocationRepository());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _changeDelay, _props.changeDelay || 700);
	    return _this;
	  }
	  babelHelpers.createClass(Map, [{
	    key: "render",
	    value: function render(props) {
	      var _this2 = this;
	      babelHelpers.classPrivateFieldSet(this, _loaderPromise$1, babelHelpers.classPrivateFieldGet(this, _googleSource$1).loaderPromise.then(function () {
	        _classPrivateMethodGet$1(_this2, _initGoogleMap, _initGoogleMap2).call(_this2, props);
	      }));
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise$1);
	    }
	  }, {
	    key: "onLocationChangedEventSubscribe",
	    value: function onLocationChangedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Map, Map, _onChangedEvent), listener);
	    }
	  }, {
	    key: "onStartChangingSubscribe",
	    value: function onStartChangingSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Map, Map, _onStartChanging), listener);
	    }
	  }, {
	    key: "onEndChangingSubscribe",
	    value: function onEndChangingSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Map, Map, _onEndChanging), listener);
	    }
	  }, {
	    key: "onMapViewChangedSubscribe",
	    value: function onMapViewChangedSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Map, Map, _onMapViewChanged), listener);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Event.unbindAll(this);
	      babelHelpers.classPrivateFieldSet(this, _googleMap, null);
	      babelHelpers.classPrivateFieldSet(this, _locationMarker, null);
	      babelHelpers.classPrivateFieldSet(this, _geocoder, null);
	      babelHelpers.classPrivateFieldSet(this, _timerId, null);
	      babelHelpers.classPrivateFieldSet(this, _loaderPromise$1, null);
	      babelHelpers.get(babelHelpers.getPrototypeOf(Map.prototype), "destroy", this).call(this);
	    }
	  }, {
	    key: "loaderPromise",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise$1);
	    }
	  }, {
	    key: "mode",
	    set: function set(mode) {
	      babelHelpers.classPrivateFieldSet(this, _mode, mode);
	      if (babelHelpers.classPrivateFieldGet(this, _locationMarker)) {
	        babelHelpers.classPrivateFieldGet(this, _locationMarker).setDraggable(mode === location_core.ControlMode.edit);
	      }
	    }
	  }, {
	    key: "zoom",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _zoom);
	    },
	    set: function set(zoom) {
	      babelHelpers.classPrivateFieldSet(this, _zoom, zoom);
	      if (babelHelpers.classPrivateFieldGet(this, _googleMap)) {
	        babelHelpers.classPrivateFieldGet(this, _googleMap).setZoom(zoom);
	      }
	    }
	  }, {
	    key: "location",
	    set: function set(location) {
	      babelHelpers.classPrivateFieldSet(this, _location, location);
	      var position = _classPrivateMethodGet$1(this, _convertLocationToPosition, _convertLocationToPosition2).call(this, location);
	      if (position) {
	        if (babelHelpers.classPrivateFieldGet(this, _locationMarker)) {
	          babelHelpers.classPrivateFieldSet(this, _isUpdating, true);
	          babelHelpers.classPrivateFieldGet(this, _locationMarker).setPosition(position);
	          babelHelpers.classPrivateFieldSet(this, _isUpdating, false);
	        }
	        if (babelHelpers.classPrivateFieldGet(this, _googleMap)) {
	          if (!babelHelpers.classPrivateFieldGet(this, _locationMarker).getMap()) {
	            babelHelpers.classPrivateFieldGet(this, _locationMarker).setMap(babelHelpers.classPrivateFieldGet(this, _googleMap));
	          }
	          babelHelpers.classPrivateFieldGet(this, _googleMap).panTo(position);
	        }
	      } else {
	        if (babelHelpers.classPrivateFieldGet(this, _locationMarker)) {
	          babelHelpers.classPrivateFieldGet(this, _locationMarker).setMap(null);
	        }
	      }
	      _classPrivateMethodGet$1(this, _adjustZoom, _adjustZoom2).call(this);
	    },
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _location);
	    }
	  }, {
	    key: "googleMap",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _googleMap);
	    }
	  }]);
	  return Map;
	}(location_core.MapBase);
	function _convertLocationToPosition2(location) {
	  if (!location) {
	    return null;
	  }
	  if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
	    return null;
	  }
	  return new google.maps.LatLng(location.latitude, location.longitude);
	}
	function _adjustZoom2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _location)) {
	    return;
	  }
	  var zoom = Map.getZoomByLocation(babelHelpers.classPrivateFieldGet(this, _location));
	  if (zoom !== null && zoom !== babelHelpers.classPrivateFieldGet(this, _zoom)) {
	    this.zoom = zoom;
	  }
	}
	function _getPositionToLocationPromise2(position) {
	  var _this3 = this;
	  return new Promise(function (resolve) {
	    babelHelpers.classPrivateFieldGet(_this3, _geocoder).geocode({
	      'location': position
	    }, function (results, status) {
	      if (status === 'OK' && results[0]) {
	        resolve(results[0].place_id);
	      } else if (status === 'ZERO_RESULTS') {
	        resolve('');
	      } else {
	        throw Error('Geocoder failed due to: ' + status);
	      }
	    });
	  }).then(function (placeId) {
	    var result;
	    if (placeId) {
	      result = babelHelpers.classPrivateFieldGet(_this3, _locationRepository).findByExternalId(placeId, babelHelpers.classPrivateFieldGet(_this3, _googleSource$1).sourceCode, babelHelpers.classPrivateFieldGet(_this3, _languageId$1));
	    } else {
	      result = new Promise(function (resolve) {
	        resolve(null);
	      });
	    }
	    return result;
	  });
	}
	function _emitOnLocationChangedEvent2(location) {
	  if (babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit) {
	    this.emit(_classStaticPrivateFieldSpecGet$1(Map, Map, _onChangedEvent), {
	      location: location
	    });
	  }
	}
	function _onMarkerUpdatePosition2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _isUpdating) && babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit) {
	    _classPrivateMethodGet$1(this, _createTimer, _createTimer2).call(this, babelHelpers.classPrivateFieldGet(this, _locationMarker).getPosition());
	  }
	}
	function _createTimer2(position) {
	  var _this4 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _timerId) !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldGet(this, _timerId));
	  }
	  babelHelpers.classPrivateFieldSet(this, _timerId, setTimeout(function () {
	    var requestId = main_core.Text.getRandom();
	    _this4.emit(_classStaticPrivateFieldSpecGet$1(Map, Map, _onStartChanging), {
	      requestId: requestId
	    });
	    babelHelpers.classPrivateFieldSet(_this4, _timerId, null);
	    babelHelpers.classPrivateFieldGet(_this4, _googleMap).panTo(position);
	    _classPrivateMethodGet$1(_this4, _fulfillOnChangedEvent, _fulfillOnChangedEvent2).call(_this4, position, requestId);
	  }, babelHelpers.classPrivateFieldGet(this, _changeDelay)));
	}
	function _fulfillOnChangedEvent2(position, requestId) {
	  var _this5 = this;
	  _classPrivateMethodGet$1(this, _getPositionToLocationPromise, _getPositionToLocationPromise2).call(this, position).then(function (location) {
	    _this5.emit(_classStaticPrivateFieldSpecGet$1(Map, Map, _onEndChanging), {
	      requestId: requestId
	    });
	    _classPrivateMethodGet$1(_this5, _emitOnLocationChangedEvent, _emitOnLocationChangedEvent2).call(_this5, location);
	  })["catch"](function (response) {
	    _this5.emit(_classStaticPrivateFieldSpecGet$1(Map, Map, _onEndChanging), {
	      requestId: requestId
	    });
	    location_core.ErrorPublisher.getInstance().notify(response.errors);
	  });
	}
	function _onMapClick2(position) {
	  if (babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit) {
	    if (!babelHelpers.classPrivateFieldGet(this, _locationMarker).getMap) {
	      babelHelpers.classPrivateFieldGet(this, _locationMarker).setMap(babelHelpers.classPrivateFieldGet(this, _googleMap));
	    }
	    babelHelpers.classPrivateFieldGet(this, _locationMarker).setPosition(position);
	    _classPrivateMethodGet$1(this, _createTimer, _createTimer2).call(this, position);
	  }
	}
	function _initGoogleMap2(props) {
	  var _this6 = this;
	  babelHelpers.classPrivateFieldSet(this, _mode, props.mode);
	  babelHelpers.classPrivateFieldSet(this, _location, props.location || null);
	  if (typeof google === 'undefined' || typeof google.maps.Map === 'undefined') {
	    throw new Error('google.maps.Map must be defined');
	  }
	  var position = _classPrivateMethodGet$1(this, _convertLocationToPosition, _convertLocationToPosition2).call(this, babelHelpers.classPrivateFieldGet(this, _location));
	  var mapProps = {
	    gestureHandling: 'greedy',
	    disableDefaultUI: true,
	    zoomControl: BX.prop.getBoolean(props, 'zoomControl', true),
	    zoomControlOptions: {
	      position: google.maps.ControlPosition.TOP_LEFT
	    }
	  };
	  var zoom = Map.getZoomByLocation(babelHelpers.classPrivateFieldGet(this, _location));
	  if (zoom) {
	    mapProps.zoom = zoom;
	  }
	  if (position) {
	    mapProps.center = position;
	  }
	  babelHelpers.classPrivateFieldSet(this, _googleMap, new google.maps.Map(props.mapContainer, mapProps));
	  babelHelpers.classPrivateFieldGet(this, _googleMap).addListener('click', function (e) {
	    _classPrivateMethodGet$1(_this6, _onMapClick, _onMapClick2).call(_this6, e.latLng);
	  });
	  if (typeof google.maps.Marker === 'undefined') {
	    throw new Error('google.maps.Marker must be defined');
	  }
	  babelHelpers.classPrivateFieldSet(this, _locationMarker, new google.maps.Marker({
	    position: position,
	    map: babelHelpers.classPrivateFieldGet(this, _googleMap),
	    draggable: babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit
	  }));
	  babelHelpers.classPrivateFieldGet(this, _locationMarker).addListener('position_changed', function () {
	    _classPrivateMethodGet$1(_this6, _onMarkerUpdatePosition, _onMarkerUpdatePosition2).call(_this6);
	  });
	  if (typeof google.maps.Geocoder === 'undefined') {
	    throw new Error('google.maps.Geocoder must be defined');
	  }
	  babelHelpers.classPrivateFieldSet(this, _geocoder, new google.maps.Geocoder());
	}
	var _onChangedEvent = {
	  writable: true,
	  value: 'onChanged'
	};
	var _onStartChanging = {
	  writable: true,
	  value: 'onStartChanging'
	};
	var _onEndChanging = {
	  writable: true,
	  value: 'onEndChanging'
	};
	var _onMapViewChanged = {
	  writable: true,
	  value: 'onMapViewChanged'
	};

	var _templateObject;
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateFieldSpecGet$2(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$2(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$2(descriptor, "get"); return _classApplyDescriptorGet$2(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor$2(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess$2(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet$2(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Class for the autocomplete locations and addresses inputs
	 */
	var _languageId$2 = /*#__PURE__*/new WeakMap();
	var _googleMap$1 = /*#__PURE__*/new WeakMap();
	var _googleSource$2 = /*#__PURE__*/new WeakMap();
	var _markerNode = /*#__PURE__*/new WeakMap();
	var _locationMarker$1 = /*#__PURE__*/new WeakMap();
	var _zoom$1 = /*#__PURE__*/new WeakMap();
	var _mode$1 = /*#__PURE__*/new WeakMap();
	var _location$1 = /*#__PURE__*/new WeakMap();
	var _geocoder$1 = /*#__PURE__*/new WeakMap();
	var _locationRepository$1 = /*#__PURE__*/new WeakMap();
	var _timerId$1 = /*#__PURE__*/new WeakMap();
	var _changeDelay$1 = /*#__PURE__*/new WeakMap();
	var _loaderPromise$2 = /*#__PURE__*/new WeakMap();
	var _isMapChanging = /*#__PURE__*/new WeakMap();
	var _convertLocationToPosition$1 = /*#__PURE__*/new WeakSet();
	var _adjustZoom$1 = /*#__PURE__*/new WeakSet();
	var _getPositionToLocationPromise$1 = /*#__PURE__*/new WeakSet();
	var _emitOnLocationChangedEvent$1 = /*#__PURE__*/new WeakSet();
	var _createTimer$1 = /*#__PURE__*/new WeakSet();
	var _fulfillOnChangedEvent$1 = /*#__PURE__*/new WeakSet();
	var _onDrag = /*#__PURE__*/new WeakSet();
	var _onDragStart = /*#__PURE__*/new WeakSet();
	var _onZoomChanged = /*#__PURE__*/new WeakSet();
	var _onMapChanging = /*#__PURE__*/new WeakSet();
	var _onIdle = /*#__PURE__*/new WeakSet();
	var _initGoogleMap$1 = /*#__PURE__*/new WeakSet();
	var Map$1 = /*#__PURE__*/function (_MapBase) {
	  babelHelpers.inherits(Map, _MapBase);
	  /** {string} */

	  /** {google.maps.Map} */

	  /** {GoogleSource} */

	  /** {google.maps.Marker} */

	  /** {number} */

	  /** {ControlMode} */

	  /** Location */

	  function Map(_props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Map);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Map).call(this, _props));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _initGoogleMap$1);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _onIdle);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _onMapChanging);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _onZoomChanged);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _onDragStart);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _onDrag);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _fulfillOnChangedEvent$1);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _createTimer$1);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _emitOnLocationChangedEvent$1);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getPositionToLocationPromise$1);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _adjustZoom$1);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _convertLocationToPosition$1);
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _languageId$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _googleMap$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _googleSource$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _markerNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _locationMarker$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _zoom$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _mode$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _location$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _geocoder$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _locationRepository$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _timerId$1, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _changeDelay$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _loaderPromise$2, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _isMapChanging, {
	      writable: true,
	      value: false
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId$2, _props.languageId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource$2, _props.googleSource);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _locationRepository$1, _props.locationRepository || new location_core.LocationRepository());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _changeDelay$1, _props.changeDelay || 700);
	    return _this;
	  }
	  babelHelpers.createClass(Map, [{
	    key: "render",
	    value: function render(props) {
	      var _this2 = this;
	      babelHelpers.classPrivateFieldSet(this, _loaderPromise$2, babelHelpers.classPrivateFieldGet(this, _googleSource$2).loaderPromise.then(function () {
	        _classPrivateMethodGet$2(_this2, _initGoogleMap$1, _initGoogleMap2$1).call(_this2, props);
	      }));
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise$2);
	    }
	  }, {
	    key: "panTo",
	    value: function panTo(latitude, longitude) {
	      if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && babelHelpers.classPrivateFieldGet(this, _googleMap$1)) {
	        babelHelpers.classPrivateFieldGet(this, _googleMap$1).panTo(new google.maps.LatLng(latitude, longitude));
	        _classPrivateMethodGet$2(this, _adjustZoom$1, _adjustZoom2$1).call(this);
	      }
	    }
	  }, {
	    key: "onLocationChangedEventSubscribe",
	    value: function onLocationChangedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$2(Map, Map, _onChangedEvent$1), listener);
	    }
	  }, {
	    key: "onStartChangingSubscribe",
	    value: function onStartChangingSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$2(Map, Map, _onStartChanging$1), listener);
	    }
	  }, {
	    key: "onEndChangingSubscribe",
	    value: function onEndChangingSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$2(Map, Map, _onEndChanging$1), listener);
	    }
	  }, {
	    key: "onMapViewChangedSubscribe",
	    value: function onMapViewChangedSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$2(Map, Map, _onMapViewChanged$1), listener);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Event.unbindAll(this);
	      babelHelpers.classPrivateFieldSet(this, _googleMap$1, null);
	      babelHelpers.classPrivateFieldSet(this, _geocoder$1, null);
	      babelHelpers.classPrivateFieldSet(this, _timerId$1, null);
	      babelHelpers.classPrivateFieldSet(this, _loaderPromise$2, null);
	      babelHelpers.get(babelHelpers.getPrototypeOf(Map.prototype), "destroy", this).call(this);
	    }
	  }, {
	    key: "loaderPromise",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise$2);
	    }
	  }, {
	    key: "mode",
	    set: function set(mode) {
	      babelHelpers.classPrivateFieldSet(this, _mode$1, mode);
	    }
	  }, {
	    key: "zoom",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _zoom$1);
	    },
	    set: function set(zoom) {
	      babelHelpers.classPrivateFieldSet(this, _zoom$1, zoom);
	      if (babelHelpers.classPrivateFieldGet(this, _googleMap$1)) {
	        babelHelpers.classPrivateFieldGet(this, _googleMap$1).setZoom(zoom);
	      }
	    }
	  }, {
	    key: "location",
	    set: function set(location) {
	      babelHelpers.classPrivateFieldSet(this, _location$1, location);
	      var position = _classPrivateMethodGet$2(this, _convertLocationToPosition$1, _convertLocationToPosition2$1).call(this, location);
	      if (position && babelHelpers.classPrivateFieldGet(this, _googleMap$1)) {
	        babelHelpers.classPrivateFieldGet(this, _googleMap$1).panTo(position);
	      }
	      _classPrivateMethodGet$2(this, _adjustZoom$1, _adjustZoom2$1).call(this);
	    },
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _location$1);
	    }
	  }, {
	    key: "googleMap",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _googleMap$1);
	    }
	  }]);
	  return Map;
	}(location_core.MapBase);
	function _convertLocationToPosition2$1(location) {
	  if (!location) {
	    return null;
	  }
	  if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
	    return null;
	  }
	  return new google.maps.LatLng(location.latitude, location.longitude);
	}
	function _adjustZoom2$1() {
	  if (!babelHelpers.classPrivateFieldGet(this, _location$1)) {
	    return;
	  }
	  var zoom = Map$1.getZoomByLocation(babelHelpers.classPrivateFieldGet(this, _location$1));
	  if (zoom !== null && zoom !== babelHelpers.classPrivateFieldGet(this, _zoom$1)) {
	    this.zoom = zoom;
	  }
	}
	function _getPositionToLocationPromise2$1(position) {
	  var _this3 = this;
	  return new Promise(function (resolve) {
	    babelHelpers.classPrivateFieldGet(_this3, _geocoder$1).geocode({
	      'location': position
	    }, function (results, status) {
	      if (status === 'OK' && results[0]) {
	        resolve(results[0].place_id);
	      } else if (status === 'ZERO_RESULTS') {
	        resolve('');
	      } else {
	        throw Error('Geocoder failed due to: ' + status);
	      }
	    });
	  }).then(function (placeId) {
	    var result;
	    if (placeId) {
	      result = babelHelpers.classPrivateFieldGet(_this3, _locationRepository$1).findByExternalId(placeId, babelHelpers.classPrivateFieldGet(_this3, _googleSource$2).sourceCode, babelHelpers.classPrivateFieldGet(_this3, _languageId$2));
	    } else {
	      result = new Promise(function (resolve) {
	        resolve(null);
	      });
	    }
	    return result;
	  });
	}
	function _emitOnLocationChangedEvent2$1(location) {
	  if (babelHelpers.classPrivateFieldGet(this, _mode$1) === location_core.ControlMode.edit) {
	    this.emit(_classStaticPrivateFieldSpecGet$2(Map$1, Map$1, _onChangedEvent$1), {
	      location: location
	    });
	  }
	}
	function _createTimer2$1() {
	  var _this4 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _timerId$1) !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldGet(this, _timerId$1));
	  }
	  babelHelpers.classPrivateFieldSet(this, _timerId$1, setTimeout(function () {
	    var requestId = main_core.Text.getRandom();
	    _this4.emit(_classStaticPrivateFieldSpecGet$2(Map$1, Map$1, _onStartChanging$1), {
	      requestId: requestId
	    });
	    babelHelpers.classPrivateFieldSet(_this4, _timerId$1, null);
	    var position = babelHelpers.classPrivateFieldGet(_this4, _googleMap$1).getCenter();
	    _classPrivateMethodGet$2(_this4, _fulfillOnChangedEvent$1, _fulfillOnChangedEvent2$1).call(_this4, position, requestId);
	  }, babelHelpers.classPrivateFieldGet(this, _changeDelay$1)));
	}
	function _fulfillOnChangedEvent2$1(position, requestId) {
	  var _this5 = this;
	  _classPrivateMethodGet$2(this, _getPositionToLocationPromise$1, _getPositionToLocationPromise2$1).call(this, position).then(function (location) {
	    _this5.emit(_classStaticPrivateFieldSpecGet$2(Map$1, Map$1, _onEndChanging$1), {
	      requestId: requestId
	    });
	    _classPrivateMethodGet$2(_this5, _emitOnLocationChangedEvent$1, _emitOnLocationChangedEvent2$1).call(_this5, location);
	  })["catch"](function (response) {
	    _this5.emit(_classStaticPrivateFieldSpecGet$2(Map$1, Map$1, _onEndChanging$1), {
	      requestId: requestId
	    });
	    location_core.ErrorPublisher.getInstance().notify(response.errors);
	  });
	}
	function _onDrag2() {
	  if (babelHelpers.classPrivateFieldGet(this, _timerId$1) !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldGet(this, _timerId$1));
	  }
	}
	function _onDragStart2() {
	  _classPrivateMethodGet$2(this, _onMapChanging, _onMapChanging2).call(this);
	  this.emit(_classStaticPrivateFieldSpecGet$2(Map$1, Map$1, _onMapViewChanged$1));
	}
	function _onZoomChanged2() {
	  _classPrivateMethodGet$2(this, _onMapChanging, _onMapChanging2).call(this);
	  this.emit(_classStaticPrivateFieldSpecGet$2(Map$1, Map$1, _onMapViewChanged$1));
	}
	function _onMapChanging2() {
	  if (babelHelpers.classPrivateFieldGet(this, _mode$1) === location_core.ControlMode.edit) {
	    babelHelpers.classPrivateFieldSet(this, _isMapChanging, true);
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _markerNode), 'location-map-mobile-center-marker-up');
	  }
	}
	function _onIdle2() {
	  if (babelHelpers.classPrivateFieldGet(this, _mode$1) === location_core.ControlMode.edit) {
	    if (babelHelpers.classPrivateFieldGet(this, _isMapChanging) === false) {
	      return;
	    }
	    var upClass = 'location-map-mobile-center-marker-up';
	    if (main_core.Dom.hasClass(babelHelpers.classPrivateFieldGet(this, _markerNode), upClass)) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _markerNode), upClass);
	    }
	    _classPrivateMethodGet$2(this, _createTimer$1, _createTimer2$1).call(this);
	    babelHelpers.classPrivateFieldSet(this, _isMapChanging, false);
	  }
	}
	function _initGoogleMap2$1(props) {
	  var _this6 = this;
	  babelHelpers.classPrivateFieldSet(this, _mode$1, props.mode);
	  babelHelpers.classPrivateFieldSet(this, _location$1, props.location || null);
	  if (typeof google === 'undefined' || typeof google.maps.Map === 'undefined') {
	    throw new Error('google.maps.Map must be defined');
	  }
	  var position = _classPrivateMethodGet$2(this, _convertLocationToPosition$1, _convertLocationToPosition2$1).call(this, babelHelpers.classPrivateFieldGet(this, _location$1));
	  var mapProps = {
	    gestureHandling: 'greedy',
	    disableDefaultUI: true,
	    zoomControl: BX.prop.getBoolean(props, 'zoomControl', true),
	    zoomControlOptions: {
	      position: google.maps.ControlPosition.TOP_LEFT
	    }
	  };
	  var zoom = Map$1.getZoomByLocation(babelHelpers.classPrivateFieldGet(this, _location$1));
	  if (zoom) {
	    mapProps.zoom = zoom;
	  }
	  if (position) {
	    mapProps.center = position;
	  }
	  babelHelpers.classPrivateFieldSet(this, _googleMap$1, new google.maps.Map(props.mapContainer, mapProps));
	  if (babelHelpers.classPrivateFieldGet(this, _mode$1) === location_core.ControlMode.edit) {
	    babelHelpers.classPrivateFieldSet(this, _markerNode, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"location-map-mobile-center-marker\"></div>"]))));
	    babelHelpers.classPrivateFieldGet(this, _googleMap$1).getDiv().appendChild(babelHelpers.classPrivateFieldGet(this, _markerNode));
	  } else {
	    babelHelpers.classPrivateFieldSet(this, _locationMarker$1, new google.maps.Marker({
	      position: position,
	      map: babelHelpers.classPrivateFieldGet(this, _googleMap$1),
	      draggable: false,
	      icon: '/bitrix/js/location/css/image/marker.png'
	    }));
	  }
	  babelHelpers.classPrivateFieldGet(this, _googleMap$1).addListener('dragstart', function () {
	    return _classPrivateMethodGet$2(_this6, _onDragStart, _onDragStart2).call(_this6);
	  });
	  babelHelpers.classPrivateFieldGet(this, _googleMap$1).addListener('idle', function () {
	    return _classPrivateMethodGet$2(_this6, _onIdle, _onIdle2).call(_this6);
	  });
	  babelHelpers.classPrivateFieldGet(this, _googleMap$1).addListener('drag', function () {
	    return _classPrivateMethodGet$2(_this6, _onDrag, _onDrag2).call(_this6);
	  });
	  babelHelpers.classPrivateFieldGet(this, _googleMap$1).addListener('zoom_changed', function () {
	    return _classPrivateMethodGet$2(_this6, _onZoomChanged, _onZoomChanged2).call(_this6);
	  });
	  if (typeof google.maps.Geocoder === 'undefined') {
	    throw new Error('google.maps.Geocoder must be defined');
	  }
	  babelHelpers.classPrivateFieldSet(this, _geocoder$1, new google.maps.Geocoder());
	  if (props.searchOnRender) {
	    _classPrivateMethodGet$2(this, _createTimer$1, _createTimer2$1).call(this);
	  }
	}
	var _onChangedEvent$1 = {
	  writable: true,
	  value: 'onChanged'
	};
	var _onStartChanging$1 = {
	  writable: true,
	  value: 'onStartChanging'
	};
	var _onEndChanging$1 = {
	  writable: true,
	  value: 'onEndChanging'
	};
	var _onMapViewChanged$1 = {
	  writable: true,
	  value: 'onMapViewChanged'
	};

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }
	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _map = /*#__PURE__*/new WeakMap();
	var _service = /*#__PURE__*/new WeakMap();
	var _googleSource$3 = /*#__PURE__*/new WeakMap();
	var _loadingPromise$1 = /*#__PURE__*/new WeakMap();
	var _getLoaderPromise = /*#__PURE__*/new WeakSet();
	var PhotoService = /*#__PURE__*/function (_PhotoServiceBase) {
	  babelHelpers.inherits(PhotoService, _PhotoServiceBase);
	  function PhotoService(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, PhotoService);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PhotoService).call(this, props));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _getLoaderPromise);
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _map, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _service, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _googleSource$3, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _loadingPromise$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource$3, props.googleSource);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map, props.map);
	    return _this;
	  }
	  babelHelpers.createClass(PhotoService, [{
	    key: "requestPhotos",
	    value: function requestPhotos(props) {
	      var _this2 = this;
	      return new Promise(function (resolve) {
	        var promise = _classPrivateMethodGet$3(_this2, _getLoaderPromise, _getLoaderPromise2).call(_this2);
	        if (!promise) {
	          resolve([]);
	        }
	        var loaderPromise = _classPrivateMethodGet$3(_this2, _getLoaderPromise, _getLoaderPromise2).call(_this2);
	        if (!loaderPromise) {
	          resolve([]);
	        }
	        loaderPromise.then(function () {
	          if (props.location.sourceCode !== babelHelpers.classPrivateFieldGet(_this2, _googleSource$3).sourceCode) {
	            resolve([]);
	            return;
	          }
	          if (props.location.externalId.length <= 0) {
	            resolve([]);
	            return;
	          }
	          babelHelpers.classPrivateFieldGet(_this2, _service).getDetails({
	            placeId: props.location.externalId,
	            fields: ['photos']
	          }, function (place, status) {
	            var resultPhotos = [];
	            if (status === google.maps.places.PlacesServiceStatus.OK) {
	              if (Array.isArray(place.photos)) {
	                var count = 0;
	                var _iterator = _createForOfIteratorHelper$1(place.photos),
	                  _step;
	                try {
	                  for (_iterator.s(); !(_step = _iterator.n()).done;) {
	                    var gPhoto = _step.value;
	                    resultPhotos.push({
	                      url: gPhoto.getUrl(),
	                      width: gPhoto.width,
	                      height: gPhoto.height,
	                      description: Array.isArray(gPhoto.html_attributions) ? gPhoto.html_attributions.join('<br>') : '',
	                      thumbnail: {
	                        url: gPhoto.getUrl({
	                          maxHeight: props.thumbnailHeight,
	                          maxWidth: props.thumbnailWidth
	                        }),
	                        width: props.thumbnailWidth,
	                        height: props.thumbnailHeight
	                      }
	                    });
	                    count++;
	                    if (props.maxPhotoCount && count >= props.maxPhotoCount) {
	                      break;
	                    }
	                  }
	                } catch (err) {
	                  _iterator.e(err);
	                } finally {
	                  _iterator.f();
	                }
	              }
	            }
	            resolve(resultPhotos);
	          });
	        });
	      });
	    }
	  }]);
	  return PhotoService;
	}(location_core.PhotoServiceBase);
	function _getLoaderPromise2() {
	  var _this3 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _loadingPromise$1)) {
	    //map haven't rendered yet	`
	    if (babelHelpers.classPrivateFieldGet(this, _map).loaderPromise === null) {
	      return;
	    }
	    babelHelpers.classPrivateFieldSet(this, _loadingPromise$1, babelHelpers.classPrivateFieldGet(this, _map).loaderPromise.then(function () {
	      babelHelpers.classPrivateFieldSet(_this3, _service, new google.maps.places.PlacesService(babelHelpers.classPrivateFieldGet(_this3, _map).googleMap));
	    }));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _loadingPromise$1);
	}

	function _createForOfIteratorHelper$2(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$2(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$2(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$2(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$2(o, minLen); }
	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _map$1 = /*#__PURE__*/new WeakMap();
	var _geocoder$2 = /*#__PURE__*/new WeakMap();
	var _loadingPromise$2 = /*#__PURE__*/new WeakMap();
	var _googleSource$4 = /*#__PURE__*/new WeakMap();
	var _getLoaderPromise$1 = /*#__PURE__*/new WeakSet();
	var _convertLocationType = /*#__PURE__*/new WeakSet();
	var _convertResultToLocations = /*#__PURE__*/new WeakSet();
	var GeocodingService = /*#__PURE__*/function (_GeocodingServiceBase) {
	  babelHelpers.inherits(GeocodingService, _GeocodingServiceBase);
	  function GeocodingService(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, GeocodingService);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GeocodingService).call(this, props));
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _convertResultToLocations);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _convertLocationType);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _getLoaderPromise$1);
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _map$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _geocoder$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _loadingPromise$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _googleSource$4, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map$1, props.map);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource$4, props.googleSource);
	    return _this;
	  }
	  babelHelpers.createClass(GeocodingService, [{
	    key: "geocodeConcrete",
	    value: function geocodeConcrete(addressString) {
	      var _this2 = this;
	      return new Promise(function (resolve) {
	        var loaderPromise = _classPrivateMethodGet$4(_this2, _getLoaderPromise$1, _getLoaderPromise2$1).call(_this2);
	        if (!loaderPromise) {
	          resolve([]);
	          return;
	        }
	        loaderPromise.then(function () {
	          babelHelpers.classPrivateFieldGet(_this2, _geocoder$2).geocode({
	            address: addressString
	          }, function (results, status) {
	            if (status === 'OK') {
	              resolve(_classPrivateMethodGet$4(_this2, _convertResultToLocations, _convertResultToLocations2).call(_this2, results));
	            } else if (status === 'ZERO_RESULTS') {
	              resolve([]);
	            } else {
	              BX.debug("Geocode was not successful for the following reason: ".concat(status));
	            }
	          });
	        });
	      });
	    }
	  }]);
	  return GeocodingService;
	}(location_core.GeocodingServiceBase);
	function _getLoaderPromise2$1() {
	  var _this3 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _loadingPromise$2)) {
	    //map haven't rendered yet	`
	    if (babelHelpers.classPrivateFieldGet(this, _googleSource$4).loaderPromise === null) {
	      return;
	    }
	    babelHelpers.classPrivateFieldSet(this, _loadingPromise$2, babelHelpers.classPrivateFieldGet(this, _googleSource$4).loaderPromise.then(function () {
	      babelHelpers.classPrivateFieldSet(_this3, _geocoder$2, new google.maps.Geocoder());
	    }));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _loadingPromise$2);
	}
	function _convertLocationType2(types) {
	  var typeMap = {
	    'country': location_core.LocationType.COUNTRY,
	    'locality': location_core.LocationType.LOCALITY,
	    'postal_town': location_core.LocationType.LOCALITY,
	    'route': location_core.LocationType.STREET,
	    'street_address': location_core.LocationType.ADDRESS_LINE_1,
	    'administrative_area_level_4': location_core.LocationType.ADM_LEVEL_4,
	    'administrative_area_level_3': location_core.LocationType.ADM_LEVEL_3,
	    'administrative_area_level_2': location_core.LocationType.ADM_LEVEL_2,
	    'administrative_area_level_1': location_core.LocationType.ADM_LEVEL_1,
	    'floor': location_core.LocationType.FLOOR,
	    'postal_code': location_core.AddressType.POSTAL_CODE,
	    'room': location_core.LocationType.ROOM,
	    'sublocality': location_core.LocationType.SUB_LOCALITY,
	    'sublocality_level_1': location_core.LocationType.SUB_LOCALITY_LEVEL_1,
	    'sublocality_level_2': location_core.LocationType.SUB_LOCALITY_LEVEL_2,
	    'street_number': location_core.LocationType.BUILDING
	  };
	  var result = location_core.LocationType.UNKNOWN;
	  var _iterator = _createForOfIteratorHelper$2(types),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var item = _step.value;
	      if (typeof typeMap[item] !== 'undefined') {
	        result = typeMap[item];
	        break;
	      }
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  return result;
	}
	function _convertResultToLocations2(data) {
	  var result = [];
	  var _iterator2 = _createForOfIteratorHelper$2(data),
	    _step2;
	  try {
	    for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	      var item = _step2.value;
	      var location = new location_core.Location();
	      location.sourceCode = babelHelpers.classPrivateFieldGet(this, _googleSource$4).sourceCode;
	      location.languageId = babelHelpers.classPrivateFieldGet(this, _googleSource$4).languageId;
	      location.externalId = item.place_id;
	      location.type = _classPrivateMethodGet$4(this, _convertLocationType, _convertLocationType2).call(this, item.types);
	      location.name = item.formatted_address;
	      location.latitude = item.geometry.location.lat();
	      location.longitude = item.geometry.location.lng();
	      result.push(location);
	    }
	  } catch (err) {
	    _iterator2.e(err);
	  } finally {
	    _iterator2.f();
	  }
	  return result;
	}

	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _languageId$3 = /*#__PURE__*/new WeakMap();
	var _sourceLanguageId = /*#__PURE__*/new WeakMap();
	var _loaderPromise$3 = /*#__PURE__*/new WeakMap();
	var _map$2 = /*#__PURE__*/new WeakMap();
	var _mapMobile = /*#__PURE__*/new WeakMap();
	var _photoService = /*#__PURE__*/new WeakMap();
	var _geocodingService = /*#__PURE__*/new WeakMap();
	var _autocompleteService = /*#__PURE__*/new WeakMap();
	var Google = /*#__PURE__*/function (_BaseSource) {
	  babelHelpers.inherits(Google, _BaseSource);
	  function Google(props) {
	    var _this;
	    babelHelpers.classCallCheck(this, Google);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Google).call(this, props));
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _languageId$3, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _sourceLanguageId, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _loaderPromise$3, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _map$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _mapMobile, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _photoService, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _geocodingService, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _autocompleteService, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isString(props.languageId) || props.languageId.trim() === '') {
	      throw new location_core.SourceCreationError('props.languageId must be a string');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId$3, props.languageId);
	    if (!main_core.Type.isString(props.sourceLanguageId) || props.sourceLanguageId.trim() === '') {
	      throw new location_core.SourceCreationError('props.sourceLanguageId must be a string');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sourceLanguageId, props.sourceLanguageId);
	    if (!main_core.Type.isString(props.apiKey) || props.apiKey.trim() === '') {
	      throw new location_core.SourceCreationError('props.apiKey must be a string');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _loaderPromise$3, Loader.load(props.apiKey, props.sourceLanguageId));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map$2, new Map({
	      googleSource: babelHelpers.assertThisInitialized(_this),
	      languageId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _languageId$3)
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _mapMobile, new Map$1({
	      googleSource: babelHelpers.assertThisInitialized(_this),
	      languageId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _languageId$3)
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _autocompleteService, new AutocompleteService({
	      googleSource: babelHelpers.assertThisInitialized(_this),
	      languageId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _languageId$3)
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _photoService, new PhotoService({
	      googleSource: babelHelpers.assertThisInitialized(_this),
	      map: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _map$2)
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _geocodingService, new GeocodingService({
	      googleSource: babelHelpers.assertThisInitialized(_this),
	      map: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _map$2)
	    }));
	    return _this;
	  }
	  babelHelpers.createClass(Google, [{
	    key: "sourceCode",
	    get: function get() {
	      return Google.code;
	    }
	  }, {
	    key: "loaderPromise",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise$3);
	    }
	  }, {
	    key: "map",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _map$2);
	    }
	  }, {
	    key: "mapMobile",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _mapMobile);
	    }
	  }, {
	    key: "autocompleteService",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _autocompleteService);
	    }
	  }, {
	    key: "photoService",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _photoService);
	    }
	  }, {
	    key: "geocodingService",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _geocodingService);
	    }
	  }, {
	    key: "languageId",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _languageId$3);
	    }
	  }]);
	  return Google;
	}(location_core.BaseSource);
	babelHelpers.defineProperty(Google, "code", 'GOOGLE');

	exports.Google = Google;

}((this.BX.Location.Google = this.BX.Location.Google || {}),BX,BX.Location.Core));
//# sourceMappingURL=google.bundle.js.map
