this.BX = this.BX || {};
this.BX.Location = this.BX.Location || {};
(function (exports,main_core,location_core) {
	'use strict';

	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } return value; }

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } return method; }

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

	var _getRegion = function _getRegion(languageId) {
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
	};

	var _createSrc = function _createSrc(apiKey, languageId) {
	  return 'https://maps.googleapis.com/maps/api/js' + "?key=".concat(apiKey) + '&libraries=places' + "&language=".concat(languageId) + "&region=".concat(_classStaticPrivateMethodGet(this, Loader, _getRegion).call(this, languageId));
	};

	var _loadingPromise = {
	  writable: true,
	  value: null
	};

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _languageId = new WeakMap();

	var _googleAutocompleteService = new WeakMap();

	var _loaderPromise = new WeakMap();

	var _googleSource = new WeakMap();

	var _localStorageKey = new WeakMap();

	var _localStorageResCount = new WeakMap();

	var _biasBoundRadius = new WeakMap();

	var _getLocalStoredResults = new WeakSet();

	var _getPredictionPromiseLocalStorage = new WeakSet();

	var _getStoredResults = new WeakSet();

	var _setPredictionResult = new WeakSet();

	var _getPredictionPromise = new WeakSet();

	var _initAutocompleteService = new WeakSet();

	var _convertToLocationsList = new WeakSet();

	var _getTypeHint = new WeakSet();

	var AutocompleteService = /*#__PURE__*/function (_AutocompleteServiceB) {
	  babelHelpers.inherits(AutocompleteService, _AutocompleteServiceB);

	  /** {string} */

	  /** {google.maps.places.AutocompleteService} */

	  /** {Promise} */

	  /** {GoogleSource} */

	  /** {string} */

	  /** {number} */

	  /** {number} */
	  function AutocompleteService(props) {
	    var _this;

	    babelHelpers.classCallCheck(this, AutocompleteService);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AutocompleteService).call(this, props));

	    _getTypeHint.add(babelHelpers.assertThisInitialized(_this));

	    _convertToLocationsList.add(babelHelpers.assertThisInitialized(_this));

	    _initAutocompleteService.add(babelHelpers.assertThisInitialized(_this));

	    _getPredictionPromise.add(babelHelpers.assertThisInitialized(_this));

	    _setPredictionResult.add(babelHelpers.assertThisInitialized(_this));

	    _getStoredResults.add(babelHelpers.assertThisInitialized(_this));

	    _getPredictionPromiseLocalStorage.add(babelHelpers.assertThisInitialized(_this));

	    _getLocalStoredResults.add(babelHelpers.assertThisInitialized(_this));

	    _languageId.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleAutocompleteService.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _loaderPromise.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleSource.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _localStorageKey.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: 'locationGoogleAutocomplete'
	    });

	    _localStorageResCount.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: 30
	    });

	    _biasBoundRadius.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: 50000
	    });

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId, props.languageId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource, props.googleSource); // Because googleSource could still be in the process of loading

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _loaderPromise, props.googleSource.loaderPromise.then(function () {
	      _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _initAutocompleteService, _initAutocompleteService2).call(babelHelpers.assertThisInitialized(_this));
	    }));
	    return _this;
	  } // eslint-disable-next-line no-unused-vars


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
	      } // Because google.maps.places.AutocompleteService could be still in the process of loading


	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise).then(function () {
	        return _classPrivateMethodGet(_this2, _getPredictionPromise, _getPredictionPromise2).call(_this2, query, params);
	      }, function (error) {
	        return BX.debug(error);
	      });
	    }
	  }]);
	  return AutocompleteService;
	}(location_core.AutocompleteServiceBase);

	var _getLocalStoredResults2 = function _getLocalStoredResults2(query, params) {
	  var result = null;

	  var storedResults = _classPrivateMethodGet(this, _getStoredResults, _getStoredResults2).call(this);

	  var _iterator = _createForOfIteratorHelper(storedResults.entries()),
	      _step;

	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	          index = _step$value[0],
	          item = _step$value[1];

	      if (item && typeof item.query !== 'undefined' && item.query === query) {
	        result = babelHelpers.objectSpread({}, item);
	        break;
	      }
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }

	  return result;
	};

	var _getPredictionPromiseLocalStorage2 = function _getPredictionPromiseLocalStorage2(query, params) {
	  var _this3 = this;

	  var result = null;

	  var answer = _classPrivateMethodGet(this, _getLocalStoredResults, _getLocalStoredResults2).call(this, query, params);

	  if (answer !== null) {
	    result = new Promise(function (resolve) {
	      resolve(_classPrivateMethodGet(_this3, _convertToLocationsList, _convertToLocationsList2).call(_this3, answer.answer, answer.status));
	    });
	  }

	  return result;
	};

	var _getStoredResults2 = function _getStoredResults2() {
	  var storedResults = BX.localStorage.get(babelHelpers.classPrivateFieldGet(this, _localStorageKey));

	  if (storedResults && storedResults.results && Array.isArray(storedResults.results)) {
	    return storedResults.results;
	  }

	  return [];
	};

	var _setPredictionResult2 = function _setPredictionResult2(query, params, answer, status) {
	  var storedResults = _classPrivateMethodGet(this, _getStoredResults, _getStoredResults2).call(this);

	  storedResults.push({
	    status: status,
	    query: query,
	    answer: answer
	  });

	  if (storedResults.length > babelHelpers.classPrivateFieldGet(this, _localStorageResCount)) {
	    storedResults.shift();
	  }

	  BX.localStorage.set(babelHelpers.classPrivateFieldGet(this, _localStorageKey), {
	    'results': storedResults
	  }, 86400);
	};

	var _getPredictionPromise2 = function _getPredictionPromise2(query, params) {
	  var _this4 = this;

	  var result = _classPrivateMethodGet(this, _getPredictionPromiseLocalStorage, _getPredictionPromiseLocalStorage2).call(this, query, params);

	  if (!result) {
	    var queryPredictionsParams = {
	      input: query
	    };

	    if (params.biasPoint) {
	      queryPredictionsParams.location = new google.maps.LatLng(params.biasPoint.latitude, params.biasPoint.longitude);
	      queryPredictionsParams.radius = babelHelpers.classPrivateFieldGet(this, _biasBoundRadius);
	    }

	    result = new Promise(function (resolve) {
	      babelHelpers.classPrivateFieldGet(_this4, _googleAutocompleteService).getQueryPredictions(queryPredictionsParams, function (res, status) {
	        var locationsList = _classPrivateMethodGet(_this4, _convertToLocationsList, _convertToLocationsList2).call(_this4, res, status);

	        _classPrivateMethodGet(_this4, _setPredictionResult, _setPredictionResult2).call(_this4, query, params, res, status);

	        resolve(locationsList);
	      });
	    });
	  }

	  return result;
	};

	var _initAutocompleteService2 = function _initAutocompleteService2() {
	  if (typeof google === 'undefined' || typeof google.maps.places.AutocompleteService === 'undefined') {
	    throw new Error('google.maps.places.AutocompleteService must be defined');
	  }

	  babelHelpers.classPrivateFieldSet(this, _googleAutocompleteService, new google.maps.places.AutocompleteService());
	};

	var _convertToLocationsList2 = function _convertToLocationsList2(data, status) {
	  if (status === 'ZERO_RESULTS') {
	    return [];
	  }

	  if (!data || status !== 'OK') {
	    return false;
	  }

	  var result = [];

	  var _iterator2 = _createForOfIteratorHelper(data),
	      _step2;

	  try {
	    for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	      var item = _step2.value;

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
	    _iterator2.e(err);
	  } finally {
	    _iterator2.f();
	  }

	  return result;
	};

	var _getTypeHint2 = function _getTypeHint2(types) {
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
	  /*
	  else
	  {
	  	result = types.join(', ');
	  }
	  */


	  return result;
	};

	function _classStaticPrivateFieldSpecGet$1(receiver, classConstructor, descriptor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	function _classStaticPrivateMethodGet$1(receiver, classConstructor, method) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } return method; }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	/**
	 * Class for the autocomplete locations and addresses inputs
	 */

	var _languageId$1 = new WeakMap();

	var _googleMap = new WeakMap();

	var _googleSource$1 = new WeakMap();

	var _zoom = new WeakMap();

	var _locationMarker = new WeakMap();

	var _mode = new WeakMap();

	var _location = new WeakMap();

	var _geocoder = new WeakMap();

	var _locationRepository = new WeakMap();

	var _timerId = new WeakMap();

	var _isUpdating = new WeakMap();

	var _changeDelay = new WeakMap();

	var _loaderPromise$1 = new WeakMap();

	var _convertLocationToPosition = new WeakSet();

	var _adjustZoom = new WeakSet();

	var _getPositionToLocationPromise = new WeakSet();

	var _emitOnLocationChangedEvent = new WeakSet();

	var _onMarkerUpdatePosition = new WeakSet();

	var _createTimer = new WeakSet();

	var _fulfillOnChangedEvent = new WeakSet();

	var _onMapClick = new WeakSet();

	var _initGoogleMap = new WeakSet();

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

	    _initGoogleMap.add(babelHelpers.assertThisInitialized(_this));

	    _onMapClick.add(babelHelpers.assertThisInitialized(_this));

	    _fulfillOnChangedEvent.add(babelHelpers.assertThisInitialized(_this));

	    _createTimer.add(babelHelpers.assertThisInitialized(_this));

	    _onMarkerUpdatePosition.add(babelHelpers.assertThisInitialized(_this));

	    _emitOnLocationChangedEvent.add(babelHelpers.assertThisInitialized(_this));

	    _getPositionToLocationPromise.add(babelHelpers.assertThisInitialized(_this));

	    _adjustZoom.add(babelHelpers.assertThisInitialized(_this));

	    _convertLocationToPosition.add(babelHelpers.assertThisInitialized(_this));

	    _languageId$1.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleMap.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleSource$1.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _zoom.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _locationMarker.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _mode.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _location.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _geocoder.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _locationRepository.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _timerId.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: null
	    });

	    _isUpdating.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: false
	    });

	    _changeDelay.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _loaderPromise$1.set(babelHelpers.assertThisInitialized(_this), {
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

	var _chooseZoomByLocation = function _chooseZoomByLocation(location) {
	  var result = 18;

	  if (location) {
	    var locationType = location.type;

	    if (locationType > 0) {
	      if (locationType < 100) result = 1;else if (locationType === 100) result = 4;else if (locationType <= 200) result = 6;else if (locationType <= 300) result = 11;else if (locationType <= 340) result = 16;else if (locationType > 340) result = 18;
	    }
	  }

	  return result;
	};

	var _onChangedEvent = {
	  writable: true,
	  value: 'onChanged'
	};

	var _convertLocationToPosition2 = function _convertLocationToPosition2(location) {
	  if (!location) {
	    return null;
	  }

	  if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
	    return null;
	  }

	  return new google.maps.LatLng(location.latitude, location.longitude);
	};

	var _adjustZoom2 = function _adjustZoom2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _location)) {
	    return;
	  }

	  var zoom = _classStaticPrivateMethodGet$1(Map, Map, _chooseZoomByLocation).call(Map, babelHelpers.classPrivateFieldGet(this, _location));

	  if (zoom !== null && zoom !== babelHelpers.classPrivateFieldGet(this, _zoom)) {
	    this.zoom = zoom;
	  }
	};

	var _getPositionToLocationPromise2 = function _getPositionToLocationPromise2(position) {
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
	};

	var _emitOnLocationChangedEvent2 = function _emitOnLocationChangedEvent2(location) {
	  if (babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit) {
	    this.emit(_classStaticPrivateFieldSpecGet$1(Map, Map, _onChangedEvent), {
	      location: location
	    });
	  }
	};

	var _onMarkerUpdatePosition2 = function _onMarkerUpdatePosition2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _isUpdating) && babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit) {
	    _classPrivateMethodGet$1(this, _createTimer, _createTimer2).call(this, babelHelpers.classPrivateFieldGet(this, _locationMarker).getPosition());
	  }
	};

	var _createTimer2 = function _createTimer2(position) {
	  var _this4 = this;

	  if (babelHelpers.classPrivateFieldGet(this, _timerId) !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldGet(this, _timerId));
	  }

	  babelHelpers.classPrivateFieldSet(this, _timerId, setTimeout(function () {
	    babelHelpers.classPrivateFieldSet(_this4, _timerId, null);
	    babelHelpers.classPrivateFieldGet(_this4, _googleMap).panTo(position);

	    _classPrivateMethodGet$1(_this4, _fulfillOnChangedEvent, _fulfillOnChangedEvent2).call(_this4, position);
	  }, babelHelpers.classPrivateFieldGet(this, _changeDelay)));
	};

	var _fulfillOnChangedEvent2 = function _fulfillOnChangedEvent2(position) {
	  _classPrivateMethodGet$1(this, _getPositionToLocationPromise, _getPositionToLocationPromise2).call(this, position).then(_classPrivateMethodGet$1(this, _emitOnLocationChangedEvent, _emitOnLocationChangedEvent2).bind(this)).catch(function (response) {
	    location_core.ErrorPublisher.getInstance().notify(response.errors);
	  });
	};

	var _onMapClick2 = function _onMapClick2(position) {
	  if (babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit) {
	    if (!babelHelpers.classPrivateFieldGet(this, _locationMarker).getMap) {
	      babelHelpers.classPrivateFieldGet(this, _locationMarker).setMap(babelHelpers.classPrivateFieldGet(this, _googleMap));
	    }

	    babelHelpers.classPrivateFieldGet(this, _locationMarker).setPosition(position);

	    _classPrivateMethodGet$1(this, _createTimer, _createTimer2).call(this, position);
	  }
	};

	var _initGoogleMap2 = function _initGoogleMap2(props) {
	  var _this5 = this;

	  babelHelpers.classPrivateFieldSet(this, _mode, props.mode);
	  babelHelpers.classPrivateFieldSet(this, _location, props.location || null);

	  if (typeof google === 'undefined' || typeof google.maps.Map === 'undefined') {
	    throw new Error('google.maps.Map must be defined');
	  }

	  var position = _classPrivateMethodGet$1(this, _convertLocationToPosition, _convertLocationToPosition2).call(this, babelHelpers.classPrivateFieldGet(this, _location));

	  var mapProps = {
	    gestureHandling: 'greedy',
	    disableDefaultUI: true,
	    zoomControl: true,
	    zoomControlOptions: {
	      position: google.maps.ControlPosition.TOP_LEFT
	    }
	  };

	  var zoom = _classStaticPrivateMethodGet$1(Map, Map, _chooseZoomByLocation).call(Map, babelHelpers.classPrivateFieldGet(this, _location));

	  if (zoom) {
	    mapProps.zoom = zoom;
	  }

	  if (position) {
	    mapProps.center = position;
	  }

	  babelHelpers.classPrivateFieldSet(this, _googleMap, new google.maps.Map(props.mapContainer, mapProps));
	  babelHelpers.classPrivateFieldGet(this, _googleMap).addListener('click', function (e) {
	    _classPrivateMethodGet$1(_this5, _onMapClick, _onMapClick2).call(_this5, e.latLng);
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
	    _classPrivateMethodGet$1(_this5, _onMarkerUpdatePosition, _onMarkerUpdatePosition2).call(_this5);
	  });

	  if (typeof google.maps.Geocoder === 'undefined') {
	    throw new Error('google.maps.Geocoder must be defined');
	  }

	  babelHelpers.classPrivateFieldSet(this, _geocoder, new google.maps.Geocoder());
	};

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }

	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _map = new WeakMap();

	var _service = new WeakMap();

	var _googleSource$2 = new WeakMap();

	var _loadingPromise$1 = new WeakMap();

	var _getLoaderPromise = new WeakSet();

	var PhotoService = /*#__PURE__*/function (_PhotoServiceBase) {
	  babelHelpers.inherits(PhotoService, _PhotoServiceBase);

	  function PhotoService(props) {
	    var _this;

	    babelHelpers.classCallCheck(this, PhotoService);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PhotoService).call(this, props));

	    _getLoaderPromise.add(babelHelpers.assertThisInitialized(_this));

	    _map.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _service.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleSource$2.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _loadingPromise$1.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource$2, props.googleSource);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map, props.map);
	    return _this;
	  }

	  babelHelpers.createClass(PhotoService, [{
	    key: "requestPhotos",
	    value: function requestPhotos(props) {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        var promise = _classPrivateMethodGet$2(_this2, _getLoaderPromise, _getLoaderPromise2).call(_this2);

	        if (!promise) {
	          resolve([]);
	        }

	        var loaderPromise = _classPrivateMethodGet$2(_this2, _getLoaderPromise, _getLoaderPromise2).call(_this2);

	        if (!loaderPromise) {
	          resolve([]);
	        }

	        loaderPromise.then(function () {
	          if (props.location.sourceCode !== babelHelpers.classPrivateFieldGet(_this2, _googleSource$2).sourceCode) {
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

	var _getLoaderPromise2 = function _getLoaderPromise2() {
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
	};

	function _createForOfIteratorHelper$2(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$2(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray$2(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$2(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$2(o, minLen); }

	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _map$1 = new WeakMap();

	var _geocoder$1 = new WeakMap();

	var _loadingPromise$2 = new WeakMap();

	var _googleSource$3 = new WeakMap();

	var _getLoaderPromise$1 = new WeakSet();

	var _convertLocationType = new WeakSet();

	var _convertResultToLocations = new WeakSet();

	var GeocodingService = /*#__PURE__*/function (_GeocodingServiceBase) {
	  babelHelpers.inherits(GeocodingService, _GeocodingServiceBase);

	  function GeocodingService(props) {
	    var _this;

	    babelHelpers.classCallCheck(this, GeocodingService);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GeocodingService).call(this, props));

	    _convertResultToLocations.add(babelHelpers.assertThisInitialized(_this));

	    _convertLocationType.add(babelHelpers.assertThisInitialized(_this));

	    _getLoaderPromise$1.add(babelHelpers.assertThisInitialized(_this));

	    _map$1.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _geocoder$1.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _loadingPromise$2.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleSource$3.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map$1, props.map);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource$3, props.googleSource);
	    return _this;
	  }

	  babelHelpers.createClass(GeocodingService, [{
	    key: "geocodeConcrete",
	    value: function geocodeConcrete(addressString) {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        var loaderPromise = _classPrivateMethodGet$3(_this2, _getLoaderPromise$1, _getLoaderPromise2$1).call(_this2);

	        if (!loaderPromise) {
	          resolve([]);
	          return;
	        }

	        loaderPromise.then(function () {
	          babelHelpers.classPrivateFieldGet(_this2, _geocoder$1).geocode({
	            address: addressString
	          }, function (results, status) {
	            if (status === 'OK') {
	              resolve(_classPrivateMethodGet$3(_this2, _convertResultToLocations, _convertResultToLocations2).call(_this2, results));
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

	var _getLoaderPromise2$1 = function _getLoaderPromise2() {
	  var _this3 = this;

	  if (!babelHelpers.classPrivateFieldGet(this, _loadingPromise$2)) {
	    //map haven't rendered yet	`
	    if (babelHelpers.classPrivateFieldGet(this, _googleSource$3).loaderPromise === null) {
	      return;
	    }

	    babelHelpers.classPrivateFieldSet(this, _loadingPromise$2, babelHelpers.classPrivateFieldGet(this, _googleSource$3).loaderPromise.then(function () {
	      babelHelpers.classPrivateFieldSet(_this3, _geocoder$1, new google.maps.Geocoder());
	    }));
	  }

	  return babelHelpers.classPrivateFieldGet(this, _loadingPromise$2);
	};

	var _convertLocationType2 = function _convertLocationType2(types) {
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
	};

	var _convertResultToLocations2 = function _convertResultToLocations2(data) {
	  var result = [];

	  var _iterator2 = _createForOfIteratorHelper$2(data),
	      _step2;

	  try {
	    for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	      var item = _step2.value;
	      var location = new location_core.Location();
	      location.sourceCode = babelHelpers.classPrivateFieldGet(this, _googleSource$3).sourceCode;
	      location.languageId = babelHelpers.classPrivateFieldGet(this, _googleSource$3).languageId;
	      location.externalId = item.place_id;
	      location.type = _classPrivateMethodGet$3(this, _convertLocationType, _convertLocationType2).call(this, item.types);
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
	};

	var _languageId$2 = new WeakMap();

	var _sourceLanguageId = new WeakMap();

	var _loaderPromise$2 = new WeakMap();

	var _map$2 = new WeakMap();

	var _photoService = new WeakMap();

	var _geocodingService = new WeakMap();

	var _autocompleteService = new WeakMap();

	var Google = /*#__PURE__*/function (_BaseSource) {
	  babelHelpers.inherits(Google, _BaseSource);

	  function Google(props) {
	    var _this;

	    babelHelpers.classCallCheck(this, Google);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Google).call(this, props));

	    _languageId$2.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: ''
	    });

	    _sourceLanguageId.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: ''
	    });

	    _loaderPromise$2.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: null
	    });

	    _map$2.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _photoService.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _geocodingService.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _autocompleteService.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    if (!main_core.Type.isString(props.languageId) || props.languageId.trim() === '') {
	      throw new location_core.SourceCreationError('props.languageId must be a string');
	    }

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId$2, props.languageId);

	    if (!main_core.Type.isString(props.sourceLanguageId) || props.sourceLanguageId.trim() === '') {
	      throw new location_core.SourceCreationError('props.sourceLanguageId must be a string');
	    }

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sourceLanguageId, props.sourceLanguageId);

	    if (!main_core.Type.isString(props.apiKey) || props.apiKey.trim() === '') {
	      throw new location_core.SourceCreationError('props.apiKey must be a string');
	    }

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _loaderPromise$2, Loader.load(props.apiKey, props.sourceLanguageId));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _map$2, new Map({
	      googleSource: babelHelpers.assertThisInitialized(_this),
	      languageId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _languageId$2)
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _autocompleteService, new AutocompleteService({
	      googleSource: babelHelpers.assertThisInitialized(_this),
	      languageId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _languageId$2)
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
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise$2);
	    }
	  }, {
	    key: "map",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _map$2);
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
	      return babelHelpers.classPrivateFieldGet(this, _languageId$2);
	    }
	  }]);
	  return Google;
	}(location_core.BaseSource);
	babelHelpers.defineProperty(Google, "code", 'GOOGLE');

	exports.Google = Google;

}((this.BX.Location.Google = this.BX.Location.Google || {}),BX,BX.Location.Core));
//# sourceMappingURL=google.bundle.js.map
