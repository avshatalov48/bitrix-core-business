this.BX = this.BX || {};
this.BX.Location = this.BX.Location || {};
(function (exports,main_core_events,location_core) {
	'use strict';

	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } return value; }

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } return method; }

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	/**
	 * Loads google source services
	 */
	var Loader =
	/*#__PURE__*/
	function () {
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
	      return new Promise(function (resolve) {
	        if (_classStaticPrivateFieldSpecGet(Loader, Loader, _isGoogleApiLoaded)) {
	          resolve();
	        }

	        BX.load([_classStaticPrivateMethodGet(Loader, Loader, _createSrc).call(Loader, apiKey, languageId)], function () {
	          _classStaticPrivateFieldSpecSet(Loader, Loader, _isGoogleApiLoaded, true);

	          resolve();
	        });
	      });
	    }
	  }]);
	  return Loader;
	}();

	var _createSrc = function _createSrc(apiKey, languageId) {
	  return 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places&language=' + languageId;
	};

	var _isGoogleApiLoaded = {
	  writable: true,
	  value: false
	};

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var Autocompleter =
	/*#__PURE__*/
	function () {
	  /** {string} */

	  /** {google.maps.places.AutocompleteService} */

	  /** {Promise}*/

	  /** {GoogleSource} */

	  /** {string} */

	  /** {number} */
	  function Autocompleter(props) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Autocompleter);

	    _convertToLocationsList.add(this);

	    _initAutocompleteService.add(this);

	    _getPredictionPromise.add(this);

	    _setPredictionResult.add(this);

	    _getPredictionPromiseLocalStorage.add(this);

	    _getLocalStoredResults.add(this);

	    _languageId.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _googleAutocompleteService.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _loaderPromise.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _source.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _localStorageKey.set(this, {
	      writable: true,
	      value: 'locationGoogleAutocomplete'
	    });

	    _localStorageResCount.set(this, {
	      writable: true,
	      value: 30
	    });

	    babelHelpers.classPrivateFieldSet(this, _languageId, props.languageId);
	    babelHelpers.classPrivateFieldSet(this, _source, props.source); //Because source could still be in the process of loading

	    babelHelpers.classPrivateFieldSet(this, _loaderPromise, props.loaderPromise.then(function () {
	      _classPrivateMethodGet(_this, _initAutocompleteService, _initAutocompleteService2).call(_this);
	    }));
	  }

	  babelHelpers.createClass(Autocompleter, [{
	    key: "autocomplete",

	    /**
	     * Returns Promise witch  will transfer locations list
	     * @param {string} query
	     * @param {object} params
	     * @returns {Promise}
	     */
	    value: function autocomplete(query, params) {
	      var _this2 = this;

	      //Because google.maps.places.AutocompleteService could be still in the process of loading
	      return babelHelpers.classPrivateFieldGet(this, _loaderPromise).then(function () {
	        return _classPrivateMethodGet(_this2, _getPredictionPromise, _getPredictionPromise2).call(_this2, query, params);
	      }, function (error) {
	        return BX.debug(error);
	      });
	    }
	  }]);
	  return Autocompleter;
	}();

	var _languageId = new WeakMap();

	var _googleAutocompleteService = new WeakMap();

	var _loaderPromise = new WeakMap();

	var _source = new WeakMap();

	var _localStorageKey = new WeakMap();

	var _localStorageResCount = new WeakMap();

	var _getLocalStoredResults = new WeakSet();

	var _getPredictionPromiseLocalStorage = new WeakSet();

	var _setPredictionResult = new WeakSet();

	var _getPredictionPromise = new WeakSet();

	var _initAutocompleteService = new WeakSet();

	var _convertToLocationsList = new WeakSet();

	var _getLocalStoredResults2 = function _getLocalStoredResults2(query, params) {
	  var result = null,
	      storedResults = localStorage.getItem(babelHelpers.classPrivateFieldGet(this, _localStorageKey));

	  if (storedResults) {
	    try {
	      storedResults = JSON.parse(storedResults);
	    } catch (e) {
	      return null;
	    }

	    if (Array.isArray(storedResults)) {
	      var _iteratorNormalCompletion = true;
	      var _didIteratorError = false;
	      var _iteratorError = undefined;

	      try {
	        for (var _iterator = storedResults.entries()[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	          var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	              index = _step$value[0],
	              item = _step$value[1];

	          if (item && typeof item.query !== 'undefined' && item.query === query) {
	            result = Object.assign({}, item);
	            storedResults.splice(index, 1);
	            storedResults.push(result);
	            localStorage.setItem(babelHelpers.classPrivateFieldGet(this, _localStorageKey), JSON.stringify(storedResults));
	            break;
	          }
	        }
	      } catch (err) {
	        _didIteratorError = true;
	        _iteratorError = err;
	      } finally {
	        try {
	          if (!_iteratorNormalCompletion && _iterator.return != null) {
	            _iterator.return();
	          }
	        } finally {
	          if (_didIteratorError) {
	            throw _iteratorError;
	          }
	        }
	      }
	    }
	  }

	  return result;
	};

	var _getPredictionPromiseLocalStorage2 = function _getPredictionPromiseLocalStorage2(query, params) {
	  var _this3 = this;

	  var result = null,
	      answer = _classPrivateMethodGet(this, _getLocalStoredResults, _getLocalStoredResults2).call(this, query, params);

	  if (answer !== null) {
	    result = new Promise(function (resolve) {
	      resolve(_classPrivateMethodGet(_this3, _convertToLocationsList, _convertToLocationsList2).call(_this3, answer.answer, answer.status));
	    });
	  }

	  return result;
	};

	var _setPredictionResult2 = function _setPredictionResult2(query, params, answer, status) {
	  var storedResults = localStorage.getItem(babelHelpers.classPrivateFieldGet(this, _localStorageKey));

	  if (storedResults) {
	    try {
	      storedResults = JSON.parse(storedResults);
	    } catch (e) {
	      return;
	    }
	  }

	  if (!Array.isArray(storedResults)) {
	    storedResults = [];
	  }

	  storedResults.push({
	    status: status,
	    query: query,
	    answer: answer
	  });

	  if (storedResults.length > babelHelpers.classPrivateFieldGet(this, _localStorageResCount)) {
	    storedResults.shift();
	  }

	  localStorage.setItem(babelHelpers.classPrivateFieldGet(this, _localStorageKey), JSON.stringify(storedResults));
	};

	var _getPredictionPromise2 = function _getPredictionPromise2(query, params) {
	  var _this4 = this;

	  var result = _classPrivateMethodGet(this, _getPredictionPromiseLocalStorage, _getPredictionPromiseLocalStorage2).call(this, query, params);

	  if (!result) {
	    result = new Promise(function (resolve) {
	      babelHelpers.classPrivateFieldGet(_this4, _googleAutocompleteService).getQueryPredictions({
	        input: query
	      }, function (result, status) {
	        var locationsList = _classPrivateMethodGet(_this4, _convertToLocationsList, _convertToLocationsList2).call(_this4, result, status);

	        _classPrivateMethodGet(_this4, _setPredictionResult, _setPredictionResult2).call(_this4, query, params, result, status);

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
	  var _iteratorNormalCompletion2 = true;
	  var _didIteratorError2 = false;
	  var _iteratorError2 = undefined;

	  try {
	    for (var _iterator2 = data[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
	      var item = _step2.value;
	      var location = new location_core.Location({
	        sourceCode: babelHelpers.classPrivateFieldGet(this, _source).sourceCode,
	        externalId: item.place_id,
	        name: item.description,
	        languageId: babelHelpers.classPrivateFieldGet(this, _languageId)
	      });
	      result.push(location);
	    }
	  } catch (err) {
	    _didIteratorError2 = true;
	    _iteratorError2 = err;
	  } finally {
	    try {
	      if (!_iteratorNormalCompletion2 && _iterator2.return != null) {
	        _iterator2.return();
	      }
	    } finally {
	      if (_didIteratorError2) {
	        throw _iteratorError2;
	      }
	    }
	  }

	  return result;
	};

	function _classStaticPrivateFieldSpecGet$1(receiver, classConstructor, descriptor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	/**
	 * Class for the autocomplete locations and addresses inputs
	 */

	var Map =
	/*#__PURE__*/
	function (_EventEmitter) {
	  babelHelpers.inherits(Map, _EventEmitter);

	  /** {string} */

	  /** {google.maps.Map} */

	  /** {Promise}*/

	  /** {GoogleSource} */

	  /** {number} */

	  /** {google.maps.Marker} */

	  /** {ControlMode}*/
	  function Map(props) {
	    var _this;

	    babelHelpers.classCallCheck(this, Map);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Map).call(this, props));

	    _convertGoogleZoomToInner.add(babelHelpers.assertThisInitialized(_this));

	    _convertInnerZoomToGoogle.add(babelHelpers.assertThisInitialized(_this));

	    _initGoogleMap.add(babelHelpers.assertThisInitialized(_this));

	    _onMapClick.add(babelHelpers.assertThisInitialized(_this));

	    _fulfillOnChangedEvent.add(babelHelpers.assertThisInitialized(_this));

	    _createTimer.add(babelHelpers.assertThisInitialized(_this));

	    _onMarkerUpdatePosition.add(babelHelpers.assertThisInitialized(_this));

	    _emitOnChangedEvent.add(babelHelpers.assertThisInitialized(_this));

	    _getPositionToLocationPromise.add(babelHelpers.assertThisInitialized(_this));

	    _convertLocationToPosition.add(babelHelpers.assertThisInitialized(_this));

	    _languageId$1.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleMap.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _loaderPromise$1.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _googleSource.set(babelHelpers.assertThisInitialized(_this), {
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

	    _geocoder.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _locationRepository.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _defaultPosition.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _changeDelay.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: void 0
	    });

	    _timerId.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: ''
	    });

	    _isUpdating.set(babelHelpers.assertThisInitialized(_this), {
	      writable: true,
	      value: false
	    });

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _languageId$1, props.languageId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _googleSource, props.googleSource);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _zoom, props.zoom || 10);

	    var _location = props.location || null;

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _locationRepository, props.locationRepository || new location_core.LocationRepository());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _defaultPosition, props.defaultPosition || {
	      latitude: 54.719208,
	      longitude: 20.488515
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _changeDelay, props.changeDelay || 500);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _mode, props.mode); //Because googleSource could still be in the process of loading

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _loaderPromise$1, props.loaderPromise.then(function () {
	      _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _initGoogleMap, _initGoogleMap2).call(babelHelpers.assertThisInitialized(_this), props.mapContainer, _location, babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _zoom));
	    }));
	    return _this;
	  }

	  babelHelpers.createClass(Map, [{
	    key: "update",
	    value: function update(location) {
	      var position = _classPrivateMethodGet$1(this, _convertLocationToPosition, _convertLocationToPosition2).call(this, location);

	      babelHelpers.classPrivateFieldSet(this, _isUpdating, true);
	      babelHelpers.classPrivateFieldGet(this, _locationMarker).setPosition(position);
	      babelHelpers.classPrivateFieldSet(this, _isUpdating, false);
	      babelHelpers.classPrivateFieldGet(this, _googleMap).panTo(position);
	    }
	  }, {
	    key: "onChangedEventSubscribe",
	    value: function onChangedEventSubscribe(listener) {
	      this.subscribe(_classStaticPrivateFieldSpecGet$1(Map, Map, _onChangedEvent), listener);
	    }
	  }, {
	    key: "mode",
	    set: function set(mode) {
	      babelHelpers.classPrivateFieldSet(this, _mode, mode);
	      babelHelpers.classPrivateFieldGet(this, _locationMarker).setDraggable(mode === location_core.ControlMode.edit);
	    }
	  }, {
	    key: "zoom",
	    set: function set(innerZoom) {
	      if (babelHelpers.classPrivateFieldGet(this, _googleMap)) {
	        babelHelpers.classPrivateFieldGet(this, _googleMap).setZoom(_classPrivateMethodGet$1(this, _convertInnerZoomToGoogle, _convertInnerZoomToGoogle2).call(this, innerZoom));
	      }
	    },
	    get: function get() {
	      if (babelHelpers.classPrivateFieldGet(this, _googleMap)) {
	        return _classPrivateMethodGet$1(this, _convertGoogleZoomToInner, _convertGoogleZoomToInner2).call(this, babelHelpers.classPrivateFieldGet(this, _googleMap).getZoom());
	      }
	    }
	  }]);
	  return Map;
	}(main_core_events.EventEmitter);

	var _languageId$1 = new WeakMap();

	var _googleMap = new WeakMap();

	var _loaderPromise$1 = new WeakMap();

	var _googleSource = new WeakMap();

	var _zoom = new WeakMap();

	var _locationMarker = new WeakMap();

	var _mode = new WeakMap();

	var _geocoder = new WeakMap();

	var _locationRepository = new WeakMap();

	var _defaultPosition = new WeakMap();

	var _changeDelay = new WeakMap();

	var _timerId = new WeakMap();

	var _isUpdating = new WeakMap();

	var _convertLocationToPosition = new WeakSet();

	var _getPositionToLocationPromise = new WeakSet();

	var _emitOnChangedEvent = new WeakSet();

	var _onMarkerUpdatePosition = new WeakSet();

	var _createTimer = new WeakSet();

	var _fulfillOnChangedEvent = new WeakSet();

	var _onMapClick = new WeakSet();

	var _initGoogleMap = new WeakSet();

	var _convertInnerZoomToGoogle = new WeakSet();

	var _convertGoogleZoomToInner = new WeakSet();

	var _onChangedEvent = {
	  writable: true,
	  value: 'onChanged'
	};

	var _convertLocationToPosition2 = function _convertLocationToPosition2(location) {
	  var lat, lon;

	  if (location) {
	    lat = location.latitude;
	    lon = location.longitude;
	  } else {
	    lat = babelHelpers.classPrivateFieldGet(this, _defaultPosition).latitude;
	    lon = babelHelpers.classPrivateFieldGet(this, _defaultPosition).longitude;
	  }

	  return new google.maps.LatLng(lat, lon);
	};

	var _getPositionToLocationPromise2 = function _getPositionToLocationPromise2(position) {
	  var _this2 = this;

	  return new Promise(function (resolve) {
	    babelHelpers.classPrivateFieldGet(_this2, _geocoder).geocode({
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
	      result = babelHelpers.classPrivateFieldGet(_this2, _locationRepository).findByExternalId(placeId, babelHelpers.classPrivateFieldGet(_this2, _googleSource).sourceCode, babelHelpers.classPrivateFieldGet(_this2, _languageId$1));
	    } else {
	      result = new Promise(function (resolve) {
	        resolve(null);
	      });
	    }

	    return result;
	  });
	};

	var _emitOnChangedEvent2 = function _emitOnChangedEvent2(location) {
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
	  var _this3 = this;

	  if (babelHelpers.classPrivateFieldGet(this, _timerId) !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldGet(this, _timerId));
	  }

	  babelHelpers.classPrivateFieldSet(this, _timerId, setTimeout(function () {
	    babelHelpers.classPrivateFieldSet(_this3, _timerId, null);
	    babelHelpers.classPrivateFieldGet(_this3, _googleMap).panTo(position);

	    _classPrivateMethodGet$1(_this3, _fulfillOnChangedEvent, _fulfillOnChangedEvent2).call(_this3, position);
	  }, babelHelpers.classPrivateFieldGet(this, _changeDelay)));
	};

	var _fulfillOnChangedEvent2 = function _fulfillOnChangedEvent2(position) {
	  _classPrivateMethodGet$1(this, _getPositionToLocationPromise, _getPositionToLocationPromise2).call(this, position).then(_classPrivateMethodGet$1(this, _emitOnChangedEvent, _emitOnChangedEvent2).bind(this));
	};

	var _onMapClick2 = function _onMapClick2(position) {
	  if (babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit) {
	    babelHelpers.classPrivateFieldGet(this, _locationMarker).setPosition(position);

	    _classPrivateMethodGet$1(this, _createTimer, _createTimer2).call(this, position);
	  }
	};

	var _initGoogleMap2 = function _initGoogleMap2(mapNode, location, zoom) {
	  var _this4 = this;

	  if (typeof google === 'undefined' || typeof google.maps.Map === 'undefined') {
	    throw new Error('google.maps.Map must be defined');
	  }

	  var position = _classPrivateMethodGet$1(this, _convertLocationToPosition, _convertLocationToPosition2).call(this, location);

	  babelHelpers.classPrivateFieldSet(this, _googleMap, new google.maps.Map(mapNode, {
	    center: position,
	    zoom: zoom
	  }));
	  babelHelpers.classPrivateFieldGet(this, _googleMap).addListener('click', function (e) {
	    _classPrivateMethodGet$1(_this4, _onMapClick, _onMapClick2).call(_this4, e.latLng);
	  });
	  babelHelpers.classPrivateFieldGet(this, _googleMap).addListener('zoom_changed', function (e) {
	    console.log('zoom_changed');
	    console.log(babelHelpers.classPrivateFieldGet(_this4, _googleMap).getZoom());
	  });

	  if (typeof google.maps.Marker === 'undefined') {
	    throw new Error('google.maps.Marker must be defined');
	  }

	  babelHelpers.classPrivateFieldSet(this, _locationMarker, new google.maps.Marker({
	    position: position,
	    map: babelHelpers.classPrivateFieldGet(this, _googleMap),
	    animation: google.maps.Animation.DROP,
	    draggable: babelHelpers.classPrivateFieldGet(this, _mode) === location_core.ControlMode.edit
	  }));
	  babelHelpers.classPrivateFieldGet(this, _locationMarker).addListener('position_changed', function () {
	    _classPrivateMethodGet$1(_this4, _onMarkerUpdatePosition, _onMarkerUpdatePosition2).call(_this4);
	  });

	  if (typeof google.maps.Geocoder === 'undefined') {
	    throw new Error('google.maps.Geocoder must be defined');
	  }

	  babelHelpers.classPrivateFieldSet(this, _geocoder, new google.maps.Geocoder());
	};

	var _convertInnerZoomToGoogle2 = function _convertInnerZoomToGoogle2(innerZoom) {
	  var result;
	  if (innerZoom <= location_core.ZoomType.World) result = 1;else if (innerZoom <= location_core.ZoomType.Country) result = 4;else if (innerZoom <= location_core.ZoomType.Region) result = 6;else if (innerZoom <= location_core.ZoomType.City) result = 11;else if (innerZoom <= location_core.ZoomType.Street) result = 16;else result = 18;
	  return result;
	};

	var _convertGoogleZoomToInner2 = function _convertGoogleZoomToInner2(gZoom) {
	  var result;
	  if (gZoom <= 5) result = location_core.ZoomType.World;else if (gZoom <= 7) result = location_core.ZoomType.Country;else if (gZoom <= 9) result = location_core.ZoomType.Region;else if (gZoom <= 10) result = location_core.ZoomType.City;else if (gZoom <= 15) result = location_core.ZoomType.Street;else if (gZoom > 15) result = location_core.ZoomType.Building;
	  return result;
	};

	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var GoogleSource =
	/*#__PURE__*/
	function () {
	  function GoogleSource(props) {
	    babelHelpers.classCallCheck(this, GoogleSource);

	    _createAutocompleter.add(this);

	    _code.set(this, {
	      writable: true,
	      value: 'GOOGLE'
	    });

	    _languageId$2.set(this, {
	      writable: true,
	      value: ''
	    });

	    _autocompleter.set(this, {
	      writable: true,
	      value: null
	    });

	    _loaderPromise$2.set(this, {
	      writable: true,
	      value: null
	    });

	    babelHelpers.classPrivateFieldSet(this, _languageId$2, props.languageId);
	    babelHelpers.classPrivateFieldSet(this, _loaderPromise$2, Loader.load(props.apiKey, props.languageId));
	  }

	  babelHelpers.createClass(GoogleSource, [{
	    key: "createMap",

	    /**
	     *
	     * @param {Element} mapContainer
	     * @param {Location} location
	     * @param mode
	     * @param zoom
	     * @return {MapBase}
	     * todo: initial zoom
	     */
	    value: function createMap(mapContainer, location, mode, zoom) {
	      return new Map({
	        googleSource: this,
	        languageId: babelHelpers.classPrivateFieldGet(this, _languageId$2),
	        loaderPromise: babelHelpers.classPrivateFieldGet(this, _loaderPromise$2),
	        mapContainer: mapContainer,
	        location: location,
	        mode: mode,
	        zoom: zoom
	      });
	    }
	  }, {
	    key: "autocompleter",
	    get: function get() {
	      if (babelHelpers.classPrivateFieldGet(this, _autocompleter) === null) {
	        babelHelpers.classPrivateFieldSet(this, _autocompleter, _classPrivateMethodGet$2(this, _createAutocompleter, _createAutocompleter2).call(this, babelHelpers.classPrivateFieldGet(this, _languageId$2)));
	      }

	      return babelHelpers.classPrivateFieldGet(this, _autocompleter);
	    }
	  }, {
	    key: "sourceCode",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _code);
	    }
	  }]);
	  return GoogleSource;
	}();

	var _code = new WeakMap();

	var _languageId$2 = new WeakMap();

	var _autocompleter = new WeakMap();

	var _loaderPromise$2 = new WeakMap();

	var _createAutocompleter = new WeakSet();

	var _createAutocompleter2 = function _createAutocompleter2(languageId) {
	  return new Autocompleter({
	    source: this,
	    languageId: languageId,
	    loaderPromise: babelHelpers.classPrivateFieldGet(this, _loaderPromise$2)
	  });
	};

	/**
	 * Creates Source using code and source init params
	 */

	var SourceFactory =
	/*#__PURE__*/
	function () {
	  function SourceFactory() {
	    babelHelpers.classCallCheck(this, SourceFactory);
	  }

	  babelHelpers.createClass(SourceFactory, null, [{
	    key: "createSource",
	    value: function createSource(sourceCode, sourceParams) {
	      //todo: make the event for custom and tests sources.
	      if (sourceCode === 'GOOGLE') {
	        var params = Object.assign({}, sourceParams);
	        params.languageId = BX.message('LANGUAGE_ID');
	        return new GoogleSource(params);
	      } else {
	        throw new Error('WrongSourceType', 'Source "' + sourceCode + '" does not exist');
	      }
	    }
	  }]);
	  return SourceFactory;
	}();

	exports.SourceFactory = SourceFactory;

}((this.BX.Location.Source = this.BX.Location.Source || {}),BX.Event,BX.Location.Core));
//# sourceMappingURL=source.bundle.js.map
