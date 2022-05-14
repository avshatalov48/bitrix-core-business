(function (exports,main_core,main_core_events) {
	'use strict';

	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }

	function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }

	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	var Options = /*#__PURE__*/function () {
	  function Options() {
	    babelHelpers.classCallCheck(this, Options);
	  }

	  babelHelpers.createClass(Options, null, [{
	    key: "getEventName",
	    value: function getEventName(eventName) {
	      return [this.getEventNamespace()].concat(babelHelpers.toConsumableArray(eventName)).join(':');
	    }
	  }, {
	    key: "getEventNamespace",
	    value: function getEventNamespace() {
	      return 'BX:Main:Uploader:';
	    }
	  }, {
	    key: "calibratePostSize",
	    value: function calibratePostSize(deltaTime, size) {
	      if (deltaTime <= 0) {
	        return;
	      }

	      if (deltaTime < this.defaultSettings['estimatedTimeForUploadFile']) {
	        var sizes = [this.defaultSettings['currentPostSize'] * 2, this.defaultSettings["phpPostMaxSize"]];

	        if (size > 0) {
	          sizes.push(Math.ceil(size * this.defaultSettings['estimatedTimeForUploadFile'] * 1000 / deltaTime));
	        }

	        this.defaultSettings['currentPostSize'] = Math.min.apply(Math, sizes);
	      } else {
	        this.defaultSettings['currentPostSize'] = Math.max(Math.ceil(this.defaultSettings['currentPostSize'] / 2), this.defaultSettings['phpPostMinSize']);
	      }

	      this.defaultSettings['currentPostSize'] = Math.max(this.defaultSettings['currentPostSize'], this.defaultSettings['phpPostMinSize']);
	    }
	  }, {
	    key: "getUploadLimits",
	    value: function getUploadLimits(key) {
	      if (!this.defaultSettings) {
	        this.defaultSettings = {
	          currentPostSize: 5.5 * 1024 * 1024,
	          phpPostMinSize: 5.5 * 1024 * 1024,
	          // Bytes
	          phpUploadMaxFilesize: Math.min(/^d+$/.test(main_core.Loc.getMessage('phpUploadMaxFilesize')) ? main_core.Loc.getMessage('phpUploadMaxFilesize') : 5 * 1024 * 1024, 5 * 1024 * 1024),
	          // Bytes 5MB because of Cloud
	          phpMaxFileUploads: Math.max(/^d+$/.test(main_core.Loc.getMessage('phpMaxFileUploads')) ? main_core.Loc.getMessage('phpMaxFileUploads') : 20, 20),
	          phpPostMaxSize: /^d+$/.test(main_core.Loc.getMessage('phpPostMaxSize')) ? main_core.Loc.getMessage('phpPostMaxSize') : 11 * 1024 * 1024,
	          // Bytes
	          estimatedTimeForUploadFile: 10 * 60,
	          // in sec
	          maxSize: this.getMaxSize()
	        };
	      }

	      if (key) {
	        return this.defaultSettings[key];
	      }

	      return this.defaultSettings;
	    }
	  }, {
	    key: "getFileTypes",
	    value: function getFileTypes() {
	      return ['A', //'A'll files
	      'I', //'I'mages
	      'F' //'F'iles with selected extensions
	      ];
	    }
	  }, {
	    key: "getImageExtensions",
	    value: function getImageExtensions() {
	      return ["jpg", "bmp", "jpeg", "jpe", "gif", "png", "webp"];
	    }
	  }, {
	    key: "getMaxSize",
	    value: function getMaxSize() {
	      if (_classStaticPrivateFieldSpecGet(this, Options, _quota) !== null && !_classStaticPrivateFieldSpecGet(this, Options, _quota)) {
	        if (/^\d+$/.test(main_core.Loc.getMessage("bxQuota"))) {
	          _classStaticPrivateFieldSpecSet(this, Options, _quota, parseInt(main_core.Loc.getMessage("bxQuota")));
	        } else {
	          _classStaticPrivateFieldSpecSet(this, Options, _quota, null);
	        }
	      }

	      return _classStaticPrivateFieldSpecGet(this, Options, _quota);
	    }
	  }, {
	    key: "decrementMaxSize",
	    value: function decrementMaxSize(size) {
	      if (this.getMaxSize() !== null) {
	        _classStaticPrivateFieldSpecSet(this, Options, _quota, _classStaticPrivateFieldSpecGet(this, Options, _quota) - size);
	      }

	      return _classStaticPrivateFieldSpecGet(this, Options, _quota);
	    }
	  }, {
	    key: "getMaxTimeToUploading",
	    value: function getMaxTimeToUploading() {
	      return 900;
	    }
	  }, {
	    key: "getVersion",
	    value: function getVersion() {
	      return '1';
	    }
	  }]);
	  return Options;
	}();

	babelHelpers.defineProperty(Options, "defaultSettings", null);
	var _quota = {
	  writable: true,
	  value: void 0
	};
	babelHelpers.defineProperty(Options, "uploadStatus", {
	  ready: 'upload is ready',
	  preparing: 'upload is not started, but preparing',
	  inProgress: 'upload is in active streaming',
	  done: 'upload is in successfully done',
	  error: 'upload is in finished with errors',
	  stopped: 'PAUSE'
	});
	babelHelpers.defineProperty(Options, "fileStatus", {
	  ready: 'fileIsReady',
	  removed: 'fileIsRemoved',
	  restored: 'fileIsRestored',
	  errored: 'fileIsBad'
	});

	var DropZone = /*#__PURE__*/function () {
	  function DropZone(dropZoneNode) {
	    babelHelpers.classCallCheck(this, DropZone);

	    if (main_core.Type.isStringFilled(dropZoneNode)) {
	      dropZoneNode = document.getElementById(dropZoneNode);
	    }

	    if (main_core.Type.isDomNode(dropZoneNode) && BX.DD && BX.ajax.FormData.isSupported()) {
	      this.initialize(dropZoneNode);
	    }
	  }

	  babelHelpers.createClass(DropZone, [{
	    key: "initialize",
	    value: function initialize(dropZoneNode) {
	      var _this = this;

	      this.dndObject = new BX.DD.dropFiles(dropZoneNode);

	      if (!this.dndObject || !this.dndObject.supported()) {
	        return;
	      }

	      var handlers = {
	        dropFiles: function dropFiles(_ref) {
	          var _ref$compatData = babelHelpers.slicedToArray(_ref.compatData, 2),
	              files = _ref$compatData[0],
	              e = _ref$compatData[1];

	          if (e && e["dataTransfer"] && e["dataTransfer"]["items"] && e["dataTransfer"]["items"].length > 0) {
	            var replaceFileArray = false;
	            var fileCopies = [];
	            var item;

	            for (var i = 0; i < e["dataTransfer"]["items"].length; i++) {
	              item = e["dataTransfer"]["items"][i];

	              if (item["webkitGetAsEntry"] && item["getAsFile"]) {
	                replaceFileArray = true;
	                var entry = item["webkitGetAsEntry"]();

	                if (entry && entry.isFile) {
	                  fileCopies.push(item["getAsFile"]());
	                }
	              }
	            }

	            if (replaceFileArray) files = fileCopies;
	          }

	          main_core_events.EventEmitter.emit(_this, Options.getEventName('caught'), {
	            files: files
	          });
	        },
	        dragEnter: function dragEnter(_ref2) {
	          var _ref2$compatData = babelHelpers.slicedToArray(_ref2.compatData, 1),
	              e = _ref2$compatData[0];

	          var isFileTransfer = false;

	          if (e && e["dataTransfer"] && e["dataTransfer"]["types"]) {
	            for (var i = 0; i < e["dataTransfer"]["types"].length; i++) {
	              if (e["dataTransfer"]["types"][i] === "Files") {
	                isFileTransfer = true;
	                break;
	              }
	            }
	          }

	          if (isFileTransfer) {
	            _this.dndObject.DIV.classList.add('bxu-file-input-over');

	            BX.onCustomEvent(_this, 'dragEnter', [e]); // compatibility event
	          }
	        },
	        dragLeave: function dragLeave(_ref3) {
	          var _ref3$compatData = babelHelpers.slicedToArray(_ref3.compatData, 1),
	              e = _ref3$compatData[0];

	          _this.dndObject.DIV.classList.remove('bxu-file-input-over');

	          BX.onCustomEvent(_this, 'dragLeave', [e]); // compatibility event
	        }
	      };
	      main_core_events.EventEmitter.subscribe(this.dndObject, 'dropFiles', handlers.dropFiles);
	      main_core_events.EventEmitter.subscribe(this.dndObject, 'dragEnter', handlers.dragEnter);
	      main_core_events.EventEmitter.subscribe(this.dndObject, 'dragLeave', handlers.dragLeave);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core_events.EventEmitter.unsubscribeAll(this.dndObject);
	      delete this.dndObject.DIV;
	      delete this.dndObject;
	    }
	  }]);
	  return DropZone;
	}();

	var buildAjaxPromiseToRestoreCsrf = function buildAjaxPromiseToRestoreCsrf(config, withoutRestoringCsrf) {
	  withoutRestoringCsrf = withoutRestoringCsrf || false;
	  var originalConfig = Object.assign({}, config);
	  var request = null;

	  config.onrequeststart = function (xhr) {
	    request = xhr;
	  };

	  var promise = BX.ajax.promise(config);
	  return promise.then(function (response) {
	    if (!withoutRestoringCsrf && main_core.Type.isPlainObject(response) && response['errors']) {
	      var csrfProblem = false;
	      response.errors.forEach(function (error) {
	        if (error.code === 'invalid_csrf' && error.customData.csrf) {
	          BX.message({
	            'bitrix_sessid': error.customData.csrf
	          });
	          originalConfig.headers = originalConfig.headers || [];
	          originalConfig.headers = originalConfig.headers.filter(function (header) {
	            return header && header.name !== 'X-Bitrix-Csrf-Token';
	          });
	          originalConfig.headers.push({
	            name: 'X-Bitrix-Csrf-Token',
	            value: BX.bitrix_sessid()
	          });
	          csrfProblem = true;
	        }
	      });

	      if (csrfProblem) {
	        return buildAjaxPromiseToRestoreCsrf(originalConfig, true);
	      }
	    }

	    return response;
	  }).then(function (response) {
	    var assetsLoaded = new BX.Promise();
	    assetsLoaded.fulfill(response);
	    return assetsLoaded;
	  }).catch(function (_ref) {
	    var reason = _ref.reason,
	        data = _ref.data;

	    if (reason === 'status' && data && (String(data).indexOf('503') >= 0 || String(data).indexOf('504') >= 0)) {
	      originalConfig['50xCounter'] = (originalConfig['50xCounter'] || 0) + 1;

	      if (originalConfig['50xCounter'] <= 2) {
	        var headers = request.getAllResponseHeaders().trim().split(/[\r\n]+/);
	        var headerMap = {};
	        headers.forEach(function (line) {
	          var parts = line.split(': ');
	          var header = parts.shift().toLowerCase();
	          headerMap[header] = parts.join(': ');
	        });
	        var timeoutSec = null;

	        if (headerMap['retry-after'] && /\d+/.test(headerMap['retry-after'])) {
	          timeoutSec = parseInt(headerMap['retry-after']);
	        }

	        var p = new BX.Promise();
	        setTimeout(function () {
	          p.fulfill();
	        }, (timeoutSec || 20) * 1000);
	        return p.then(function () {
	          return buildAjaxPromiseToRestoreCsrf(originalConfig);
	        });
	      }
	    }

	    var ajaxReject = new BX.Promise();

	    if (main_core.Type.isPlainObject(data) && data.status && data.hasOwnProperty('data')) {
	      ajaxReject.reject(data);
	    } else {
	      ajaxReject.reject({
	        status: 'error',
	        data: {
	          ajaxRejectData: data
	        },
	        errors: [{
	          code: 'NETWORK_ERROR',
	          message: 'Network error'
	        }]
	      });
	    }

	    return ajaxReject;
	  });
	};

	var Stream = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Stream, _EventEmitter);

	  function Stream() {
	    var _this;

	    babelHelpers.classCallCheck(this, Stream);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Stream).call(this));

	    _this.setEventNamespace(Options.getEventNamespace());

	    _this.onprogress = _this.onprogress.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onprogressupload = _this.onprogressupload.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Stream, [{
	    key: "send",
	    value: function send(url, formData) {
	      var _this2 = this;

	      this.deltaTime = -1 * new Date().getTime();
	      this.totalSize = null;
	      buildAjaxPromiseToRestoreCsrf({
	        method: 'POST',
	        dataType: 'json',
	        url: url,
	        data: formData,
	        timeout: Options.getMaxTimeToUploading(),
	        preparePost: false,
	        headers: [{
	          name: 'X-Bitrix-Csrf-Token',
	          value: BX.bitrix_sessid()
	        }, {
	          name: 'X-Bitrix-Site-Id',
	          value: BX.message.SITE_ID || ''
	        }],
	        onprogress: this.onprogress,
	        onprogressupload: this.onprogressupload
	      }).then(function (response) {
	        _this2.done({
	          status: 'success',
	          data: response
	        });
	      }).catch(function (_ref2) {
	        var errors = _ref2.errors,
	            data = _ref2.data;

	        _this2.done({
	          status: 'failed',
	          errors: errors.map(function (_ref3) {
	            var code = _ref3.code,
	                message = _ref3.message;
	            return message;
	          }),
	          data: data
	        });
	      }).catch(function (response) {
	        _this2.done({
	          status: 'failed',
	          errors: ['Unexpected server response.'],
	          data: response
	        });
	      });
	    }
	  }, {
	    key: "onprogress",
	    value: function onprogress(e) {}
	  }, {
	    key: "onprogressupload",
	    value: function onprogressupload(e) {
	      var procent = 5;

	      if (babelHelpers.typeof(e) == "object" && e.lengthComputable) {
	        procent = e.loaded * 100 / (e["total"] || e["totalSize"]);
	        this.totalSize = e["total"] || e["totalSize"];
	      } else if (e > procent) procent = e;

	      procent = procent > 5 ? procent : 5;
	      this.emit('progress', procent);
	    }
	  }, {
	    key: "done",
	    value: function done(response) {
	      this.deltaTime += new Date().getTime();
	      Options.calibratePostSize(this.deltaTime, this.totalSize);
	      this.emit('done', response);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      console.log('Clear all from stream');
	    }
	  }]);
	  return Stream;
	}(main_core_events.EventEmitter);

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _currentFileToUpload = /*#__PURE__*/new WeakMap();

	var PackageFile = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PackageFile, _EventEmitter);

	  //null|ready
	  //null|inprogress|done|errored
	  function PackageFile(item, pack) {
	    var _this;

	    babelHelpers.classCallCheck(this, PackageFile);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PackageFile).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "isReadyToPack", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "packStatus", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "packPercent", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "uploadStatus", null);

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _currentFileToUpload, {
	      writable: true,
	      value: null
	    });

	    _this.setEventNamespace(Options.getEventNamespace());

	    _this.item = item; // item with a node

	    _this.id = item.getId();
	    _this.name = item.name;
	    _this.fileStatus = Options.fileStatus.ready; // ready|remove|restore

	    _this.isReadyToPack = item.preparationStatus === _this.constructor.preparationStatusIsDone;
	    _this.copiesCount = item.getThumbs("getCount") + 1;
	    main_core_events.EventEmitter.subscribeOnce(item, 'onFileIsDeleted', function () {
	      _this.fileStatus = Options.fileStatus.removed;
	    });

	    if (!_this.isReadyToPack) {
	      main_core_events.EventEmitter.subscribeOnce(item, 'onFileIsPrepared', function () {
	        _this.isReadyToPack = true;

	        _this.emit('onReady');
	      });
	      main_core_events.EventEmitter.emit(item, 'onFileHasToBePrepared', new main_core_events.BaseEvent({
	        compatData: [item.getId(), item]
	      }));
	    }

	    return _this;
	  }

	  babelHelpers.createClass(PackageFile, [{
	    key: "isReady",
	    value: function isReady() {
	      return this.isReadyToPack;
	    }
	  }, {
	    key: "isRemoved",
	    value: function isRemoved() {
	      return this.fileStatus === Options.fileStatus.removed;
	    }
	  }, {
	    key: "isPacked",
	    value: function isPacked() {
	      return this.packStatus === Options.uploadStatus.done;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "markAsPacked",
	    value: function markAsPacked(percentToIncrement) {
	      if (percentToIncrement === true) {
	        this.packStatus = Options.uploadStatus.done;
	        this.packPercent = 100;
	      } else {
	        this.packPercent += percentToIncrement / this.copiesCount;
	        this.packPercent = this.packPercent > 100 ? 100 : this.packPercent;
	      }
	    }
	  }, {
	    key: "packFile",
	    value: function packFile() {
	      var result = {
	        error: false,
	        done: true,
	        data: null
	      };

	      if (this.isRemoved()) {
	        result.data = {
	          removed: 'Y',
	          name: this.name
	        };
	        this.markAsPacked(true);
	      }

	      if (this.isPacked()) {
	        return result;
	      }

	      var currentBlob;
	      var copyName = 'default';

	      if (this.packStatus === null) {
	        result.data = this.item.getProps() || {
	          name: this.name
	        };

	        if (this.item['restored']) {
	          result.data['restored'] = this.item['restored'];
	          delete this.item['restored'];
	        }

	        this.packStatus = Options.uploadStatus.inProgress;
	        currentBlob = this.item["file"];
	      } else if (babelHelpers.classPrivateFieldGet(this, _currentFileToUpload) instanceof Blob) {
	        currentBlob = babelHelpers.classPrivateFieldGet(this, _currentFileToUpload);
	        babelHelpers.classPrivateFieldSet(this, _currentFileToUpload, null);
	      } else {
	        currentBlob = this.item.getThumbs(null);

	        if (currentBlob === null) {
	          this.markAsPacked(true);
	          return result;
	        }

	        copyName = currentBlob['thumb'];
	      }

	      var packingPercent = 100;

	      if (currentBlob instanceof Blob) // Regular behaviour
	        {
	          var blob = BX.UploaderUtils.getFilePart(currentBlob, Options.getUploadLimits('phpUploadMaxFilesize'));

	          if (blob && blob !== currentBlob) {
	            if (blob.packages - blob.package > 1) {
	              babelHelpers.classPrivateFieldSet(this, _currentFileToUpload, currentBlob);
	            }

	            packingPercent = blob.size / currentBlob.size * 100;
	            copyName = [copyName, '.ch', blob.package, '.', (blob.start > 0 ? blob.start : "0") + '.chs' + blob.packages].join('');
	            blob.name = copyName;
	          }

	          currentBlob = blob;
	        }

	      if (currentBlob) {
	        result.data = result.data || {
	          name: this.name
	        };

	        if (currentBlob instanceof Blob) {
	          result.data[copyName] = currentBlob;
	        } else {
	          result.data['files'] = result.data['files'] || {};
	          result.data['files'][copyName] = currentBlob;
	        }
	      }

	      if (result.data) {
	        result.done = false;
	        this.markAsPacked(packingPercent);
	      } else {
	        this.markAsPacked(true);
	      }

	      return result;
	    }
	  }, {
	    key: "parseResponse",
	    value: function parseResponse(_ref) {// console.log('parseResponse: ', this.getId(), file);

	      var file = _ref.file,
	          hash = _ref.hash,
	          status = _ref.status;
	    }
	  }, {
	    key: "size",
	    get: function get() {
	      return this.item ? this.item.size || 0 : 0;
	    }
	  }]);
	  return PackageFile;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(PackageFile, "preparationStatusIsDone", 4);

	var getFormDataSize = function getFormDataSize(formData) {
	  var entries = formData.entries();
	  var entry,
	      filesCount = 0,
	      formSize = 0;

	  while ((entry = entries.next()) && entry.done === false) {
	    var _entry$value = babelHelpers.slicedToArray(entry.value, 2),
	        name = _entry$value[0],
	        value = _entry$value[1];

	    if (value instanceof Blob) {
	      filesCount++;
	      formSize += value.size;
	    } else {
	      formSize += value.toString().length;
	    }

	    formSize += name.toString().length;
	  }

	  return [formSize, filesCount];
	};

	var convertFormDataToObject = function convertFormDataToObject(formData) {
	  var entries = formData.entries();
	  var entry;
	  var data = {};

	  while ((entry = entries.next()) && entry.done === false) {
	    var _entry$value2 = babelHelpers.slicedToArray(entry.value, 2),
	        name = _entry$value2[0],
	        value = _entry$value2[1];

	    if (name.indexOf('[') <= 0) {
	      data[name] = value;
	    } else {
	      (function () {
	        var names = [name.substring(0, name.indexOf('['))];
	        name.replace(/\[(.*?)\]/gi, function (n, nn) {
	          names.push(nn.length > 0 ? nn : '');
	        });
	        var n = void 0;
	        var pointer = data;

	        while (n = names.shift()) {
	          if (n === '') {
	            pointer.push(value);
	            break;
	          } else if (names.length <= 0) {
	            pointer[n] = value;
	            break;
	          } else if (names[0] === '') {
	            pointer[n] = pointer[n] || [];
	            pointer = pointer[n];
	          } else {
	            pointer[n] = pointer[n] || {};
	            pointer = pointer[n];
	          }
	        }
	      })();
	    }
	  }

	  return data;
	};

	var copyFormToForm = function copyFormToForm(fromData1, formData2) {
	  var entries = fromData1.entries();
	  var entry;

	  while ((entry = entries.next()) && entry.done === false) {
	    var _entry$value3 = babelHelpers.slicedToArray(entry.value, 2),
	        name = _entry$value3[0],
	        value = _entry$value3[1];

	    if (value instanceof Blob) {
	      formData2.append(name, value, value.name);
	    } else {
	      formData2.append(name, value);
	    }
	  }
	};

	var appendToForm = function appendToForm(formData, ob, prefix) {
	  for (var ii in ob) {
	    if (ob.hasOwnProperty(ii)) {
	      var name = (prefix ? prefix + '[#name#]' : '#name#').replace('#name#', ii);

	      if (main_core.Type.isPlainObject(ob[ii])) {
	        appendToForm(formData, ob[ii], name);
	      } else {
	        if (ob[ii] instanceof Blob) {
	          formData.append(name, ob[ii], ob[ii]['name'] || ii);
	        } else {
	          formData.append(name, ob[ii]);
	        }
	      }
	    }
	  }
	};

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _formDataFilesCount = /*#__PURE__*/new WeakMap();

	var _formDataSize = /*#__PURE__*/new WeakMap();

	var Package = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Package, _EventEmitter);

	  function Package(_ref) {
	    var _this;

	    var id = _ref.id,
	        formData = _ref.formData,
	        files = _ref.files,
	        uploadFileUrl = _ref.uploadFileUrl,
	        uploadInputName = _ref.uploadInputName;
	    babelHelpers.classCallCheck(this, Package);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Package).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "length", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "filesVirgin", new Set());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "filesInprogress", new Set());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "files", new Map());

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _formDataFilesCount, {
	      writable: true,
	      value: 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _formDataSize, {
	      writable: true,
	      value: 0
	    });

	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "makeAPackTimeout", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "uploadStatus", Options.uploadStatus.ready);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "errors", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "response", {
	      status: 'start'
	    });

	    _this.setEventNamespace(Options.getEventNamespace());

	    _this.id = id;
	    _this.formData = formData;
	    _this.uploadFileUrl = uploadFileUrl;
	    _this.uploadInputName = uploadInputName;

	    _this.initFiles(files);

	    console.log('2. Package is created with ', _this.filesVirgin.size, ' files.');
	    _this.doneStreaming = _this.doneStreaming.bind(babelHelpers.assertThisInitialized(_this));
	    _this.progressStreaming = _this.progressStreaming.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Package, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "initFiles",
	    value: function initFiles(files) {
	      var _this2 = this;

	      files.forEach(function (fileItem) {
	        var uploadFile = new PackageFile(fileItem);

	        _this2.filesVirgin.add(uploadFile.getId());

	        _this2.files.set(uploadFile.getId(), uploadFile);
	      });
	    }
	  }, {
	    key: "prepare",
	    value: function prepare() {
	      var _getFormDataSize = getFormDataSize(this.formData),
	          _getFormDataSize2 = babelHelpers.slicedToArray(_getFormDataSize, 2),
	          formSize = _getFormDataSize2[0],
	          filesCount = _getFormDataSize2[1];

	      console.log('2.1 Prepare form with files: ', filesCount, ' and formSize: ', parseInt(formSize), 'B');

	      if (Options.getUploadLimits('phpMaxFileUploads') <= filesCount) {
	        this.error('Too many files in your form. ');
	        return false;
	      }

	      if (Options.getUploadLimits('phpPostMaxSize') - formSize < Options.getUploadLimits('phpPostMinSize')) {
	        this.error('Too much data in your form. ');
	        return false;
	      }

	      var packSize = 0;
	      this.files.forEach(function (file) {
	        packSize += file.size;
	      });

	      if (Options.getMaxSize() !== null && Options.getMaxSize() < packSize) {
	        this.error('There is not enough space on your server.');
	        return false;
	      }

	      Options.decrementMaxSize(packSize);
	      babelHelpers.classPrivateFieldSet(this, _formDataSize, formSize);
	      babelHelpers.classPrivateFieldSet(this, _formDataFilesCount, filesCount);
	      return true;
	    }
	  }, {
	    key: "run",
	    value: function run(stream) {
	      if (this.uploadStatus !== Options.uploadStatus.ready) {
	        return;
	      }

	      console.log('4. Package is running with a stream: ', stream);
	      this.uploadStatus = Options.uploadStatus.preparing;
	      return this.startStreaming(stream);
	    }
	  }, {
	    key: "bindStream",
	    value: function bindStream(stream) {
	      if (stream === this.stream) {
	        return;
	      }

	      this.stream = stream;
	      stream.subscribe('done', this.doneStreaming);
	      stream.subscribe('progress', this.progressStreaming);
	    }
	  }, {
	    key: "unbindStream",
	    value: function unbindStream(stream) {
	      if (stream || this.stream) {
	        (stream || this.stream).unsubscribe('done', this.doneStreaming);
	        (stream || this.stream).unsubscribe('progress', this.progressStreaming);

	        if (stream === this.stream) {
	          delete this.stream;
	        }
	      }
	    }
	  }, {
	    key: "makeAPack",
	    value: function makeAPack(formSize, filesCount, formData) {
	      var _this3 = this;

	      while (formSize - Options.getUploadLimits('phpUploadMaxFilesize') > 0 && filesCount > 0) {
	        if (this.filesVirgin.size <= 0) {
	          break;
	        }

	        var entry = this.filesVirgin.entries().next();

	        if (entry.done === true) {
	          break;
	        }
	        /*@var uploadItem: PackageFile */


	        var _entry$value = babelHelpers.slicedToArray(entry.value, 1),
	            uploadItemId = _entry$value[0];

	        var uploadItem = this.files.get(uploadItemId);

	        if (!uploadItem.isReady()) {
	          return uploadItem.subscribeOnce('onReady', function () {
	            _this3.makeAPack(formSize, filesCount, formData);
	          });
	        }

	        var result = uploadItem.packFile();

	        if (result.data) {
	          var name = "".concat(this.uploadInputName, "[").concat(uploadItem.getId(), "]");
	          var tmpFormData = new FormData();
	          appendToForm(tmpFormData, result.data, name);

	          var _getFormDataSize3 = getFormDataSize(tmpFormData),
	              _getFormDataSize4 = babelHelpers.slicedToArray(_getFormDataSize3, 2),
	              tmpFormSize = _getFormDataSize4[0],
	              tmpFilesCount = _getFormDataSize4[1];

	          copyFormToForm(tmpFormData, formData);
	          formSize -= tmpFormSize;
	          filesCount -= tmpFilesCount;
	          this.filesInprogress.add(uploadItemId);
	        }

	        if (result.done === true) {
	          this.filesVirgin.delete(uploadItemId);
	        }
	      }

	      return this.emit('onPackIsReady', formData);
	    }
	  }, {
	    key: "startStreaming",
	    value: function startStreaming(stream) {
	      this.bindStream(stream);
	      this.doStreaming(stream);
	    }
	  }, {
	    key: "doStreaming",
	    value: function doStreaming(stream) {
	      var _this4 = this;

	      this.subscribeOnce('onPackIsReady', function (_ref2) {
	        var data = _ref2.data;
	        console.log('onPackIsReady: ', data);
	        console.groupEnd('Make a pack.');
	        clearTimeout(_this4.makeAPackTimeout);
	        _this4.makeAPackTimeout = 0;

	        if (data instanceof FormData) {
	          var firstValue = data.entries().next();

	          if (firstValue.done === true && !firstValue.value) {
	            return _this4.checkAndDone(stream);
	          }

	          copyFormToForm(_this4.formData, data);
	          console.log('4.1. Start streaming');
	          return stream.send(_this4.uploadFileUrl, data);
	        }

	        _this4.error('Package: error in packing');
	      });
	      var formSize = Math.min(Options.getUploadLimits('currentPostSize'), Options.getUploadLimits('phpPostMaxSize') - babelHelpers.classPrivateFieldGet(this, _formDataSize));
	      var filesCount = Options.getUploadLimits('phpMaxFileUploads') - babelHelpers.classPrivateFieldGet(this, _formDataFilesCount);
	      var fromData = new FormData();
	      console.group('Make a pack.');
	      this.makeAPack(formSize, filesCount, fromData);
	      this.makeAPackTimeout = setTimeout(function () {
	        _this4.emit('onPackIsReady', null);
	      }, Options.getUploadLimits('estimatedTimeForUploadFile') * 1000);
	    }
	  }, {
	    key: "doneStreaming",
	    value: function doneStreaming(_ref3) {
	      var stream = _ref3.target,
	          _ref3$data = _ref3.data,
	          status = _ref3$data.status,
	          data = _ref3$data.data,
	          errors = _ref3$data.errors;
	      console.log('4.2. Done streaming');

	      if (status === 'success') {
	        this.parseResponse(data);

	        if (this.errors.length <= 0) {
	          this.doStreaming(stream);
	        }
	      } else {
	        this.error(errors.join('. '));
	      }
	    }
	  }, {
	    key: "progressStreaming",
	    value: function progressStreaming(_ref4) {
	      var _this5 = this;

	      var percent = _ref4.data;
	      this.filesInprogress.forEach(function (itemId) {
	        var item = _this5.files.get(itemId);

	        var currentPercent = percent * (item.packPercent || 0);

	        if (!item['previousPackPercent']) {
	          item['previousPackPercent'] = currentPercent;
	        }

	        _this5.emit('fileIsInProgress', {
	          itemId: itemId,
	          item: item.item,
	          percent: Math.ceil(Math.max(item['previousPackPercent'], currentPercent) / 100)
	        });

	        item['previousPackPercent'] = currentPercent;
	      });
	    }
	  }, {
	    key: "parseResponse",
	    value: function parseResponse(data) {
	      var _this6 = this;

	      var merge = function merge(ar1, ar2) {
	        for (var jj in ar2) {
	          if (ar2.hasOwnProperty(jj)) {
	            ar1[jj] = main_core.Type.isPlainObject(ar2[jj]) && main_core.Type.isPlainObject(ar1[jj]) ? merge(ar1[jj], ar2[jj]) : ar2[jj];
	          }
	        }

	        return ar1;
	      };

	      this.response = merge(this.response, data);

	      if (data.status === 'error') {
	        this.error('Error in a uploading');
	      } else if (!data['files']) {
	        this.error('Unexpected server response.');
	      } else {
	        this.filesInprogress.forEach(function (itemId) {
	          var fileResponse = data['files'][itemId] || {
	            status: 'error',
	            errors: ['File data is not found']
	          };

	          if (fileResponse.status === 'error' || fileResponse.status === 'uploaded') {
	            _this6.filesVirgin.delete(itemId);

	            _this6.emit(fileResponse.status === 'error' ? 'fileIsErrored' : 'fileIsUploaded', {
	              itemId: itemId,
	              item: _this6.files.get(itemId).item,
	              response: fileResponse
	            });
	          }

	          _this6.files.get(itemId).parseResponse(fileResponse);
	        });
	        this.filesInprogress.clear();
	      }
	    }
	  }, {
	    key: "checkAndDone",
	    value: function checkAndDone(stream) {
	      console.log('5. Form has been sent.');

	      if (this.response['status'] === 'done') {
	        this.done(stream);
	      } else if (this.response['status'] === 'start') {
	        this.error('Error with starting package.');
	      } else if (this.response['status'] !== 'continue') {
	        this.error('Unknown response');
	      }
	    }
	  }, {
	    key: "done",
	    value: function done(stream) {
	      console.log('5.1 Release the stream');
	      this.unbindStream(stream);
	      this.emit('done', {
	        status: this.errors.length <= 0 ? 'success' : 'failed'
	      });
	    }
	  }, {
	    key: "error",
	    value: function error(errorText) {
	      var _this7 = this;

	      var handler = function handler(itemId) {
	        _this7.emit('fileIsErrored', {
	          itemId: itemId,
	          item: _this7.files.get(itemId).item,
	          response: {
	            error: errorText,
	            status: 'failed'
	          },
	          serverResponse: Object.assign({}, _this7.response)
	        });
	      };

	      this.filesVirgin.forEach(handler);
	      this.filesVirgin.clear();
	      this.filesInprogress.forEach(handler);
	      this.filesInprogress.clear();
	      this.errors.push(errorText);
	      console.log('5. Form has been sent with errors: ', this.errors);
	      this.done(this.stream);
	    }
	  }, {
	    key: "getServerResponse",
	    value: function getServerResponse() {
	      return this.response;
	    }
	  }, {
	    key: "filesCount",
	    get: function get() {
	      return this.filesVirgin.size + this.filesInprogress.size;
	    }
	  }, {
	    key: "data",
	    get: function get() {
	      return convertFormDataToObject(this.formData);
	    }
	  }]);
	  return Package;
	}(main_core_events.EventEmitter);

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess$1(receiver, classConstructor); return method; }

	function _classStaticPrivateFieldSpecGet$1(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess$1(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor$1(descriptor, "get"); return _classApplyDescriptorGet$1(receiver, descriptor); }

	function _classCheckPrivateStaticFieldDescriptor$1(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }

	function _classCheckPrivateStaticAccess$1(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classApplyDescriptorGet$1(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	var Streams = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Streams, _EventEmitter);

	  function Streams() {
	    babelHelpers.classCallCheck(this, Streams);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Streams).apply(this, arguments));
	  }

	  babelHelpers.createClass(Streams, null, [{
	    key: "addPackage",
	    value: function addPackage(pack) {
	      console.log('3. Add to a stream queue.');

	      if (this.maxInstances > 0 && _classStaticPrivateFieldSpecGet$1(this, Streams, _instance).size > this.maxInstances) {
	        _classStaticPrivateFieldSpecGet$1(this, Streams, _packages).set(pack);
	      } else {
	        _classStaticPrivateFieldSpecGet$1(this, Streams, _packages).delete(pack);

	        _classStaticPrivateMethodGet(this, Streams, _runPackage).call(this, pack);
	      }

	      if (!window[_classStaticPrivateFieldSpecGet$1(this, Streams, _hiddenTag)]) {
	        window[_classStaticPrivateFieldSpecGet$1(this, Streams, _hiddenTag)] = _classStaticPrivateMethodGet(this, Streams, _catchWindow).bind(this);
	        main_core.Event.bind(window, 'beforeunload', window[_classStaticPrivateFieldSpecGet$1(this, Streams, _hiddenTag)]);
	      }
	    }
	  }]);
	  return Streams;
	}(main_core_events.EventEmitter);

	function _catchWindow(event) {
	  if (_classStaticPrivateFieldSpecGet$1(this, Streams, _packages).size > 0 || _classStaticPrivateFieldSpecGet$1(this, Streams, _instance).size > 0) {
	    var confirmationMessage = main_core.Loc.getMessage('UPLOADER_UPLOADING_ONBEFOREUNLOAD');
	    (event || window.event).returnValue = confirmationMessage;
	    return confirmationMessage;
	  }
	}

	function _runPackage(pack) {
	  var _this = this;

	  var stream = new Stream();

	  _classStaticPrivateFieldSpecGet$1(this, Streams, _instance).set(stream);

	  console.log('3.1. Run package in a stream.');
	  pack.subscribeOnce('done', function () {
	    console.log('6. Package is done so release the stream.');

	    _classStaticPrivateFieldSpecGet$1(_this, Streams, _instance).delete(stream);

	    stream.destroy();

	    if (_classStaticPrivateFieldSpecGet$1(_this, Streams, _packages).size > 0) {
	      var _classStaticPrivateFi = babelHelpers.slicedToArray(_classStaticPrivateFieldSpecGet$1(_this, Streams, _packages).entries().next().value, 1),
	          newPack = _classStaticPrivateFi[0];

	      _this.addPackage(newPack);
	    } else if (_classStaticPrivateFieldSpecGet$1(_this, Streams, _instance).size <= 0) {
	      main_core.Event.unbind(window, 'beforeunload', window[_classStaticPrivateFieldSpecGet$1(_this, Streams, _hiddenTag)]);
	      delete window[_classStaticPrivateFieldSpecGet$1(_this, Streams, _hiddenTag)];
	    }
	  });
	  pack.run(stream);
	}

	babelHelpers.defineProperty(Streams, "maxInstances", 3);
	var _instance = {
	  writable: true,
	  value: new Map()
	};
	var _packages = {
	  writable: true,
	  value: new Map()
	};
	var _hiddenTag = {
	  writable: true,
	  value: Symbol('streams descriptor')
	};

	(function (window) {
	  window.BX = window['BX'] || {};
	  if (window.BX["UploaderQueue"]) return false;
	  var BX = window.BX,
	      statuses = {
	    "new": 0,
	    ready: 1,
	    preparing: 2,
	    inprogress: 3,
	    done: 4,
	    failed: 5,
	    stopped: 6,
	    changed: 7,
	    uploaded: 8
	  };
	  /**
	   * @return {BX.UploaderQueue}
	   * @params array
	   * @params[placeHolder] - DOM node to append files /OL or UL/
	   */

	  BX.UploaderQueue = function (params, limits, caller) {
	    this.dialogName = "BX.UploaderQueue";
	    limits = !!limits ? limits : {};
	    this.limits = {
	      phpPostMaxSize: limits["phpPostMaxSize"],
	      phpUploadMaxFilesize: limits["phpUploadMaxFilesize"],
	      uploadMaxFilesize: limits["uploadMaxFilesize"] > 0 ? limits["uploadMaxFilesize"] : 0,
	      uploadFileWidth: limits["uploadFileWidth"] > 0 ? limits["uploadFileWidth"] : 0,
	      uploadFileHeight: limits["uploadFileHeight"] > 0 ? limits["uploadFileHeight"] : 0
	    };
	    this.placeHolder = BX(params["placeHolder"]);
	    this.showImage = params["showImage"] !== false && params["showImage"] !== 'N';
	    this.sortItems = params["sortItems"] !== false && params["sortItems"] !== 'N';
	    this.fileCopies = params["copies"];
	    this.fileFields = params["fields"];
	    this.uploader = caller;
	    this.itForUpload = new BX.UploaderUtils.Hash();
	    this.items = new BX.UploaderUtils.Hash();
	    this.itUploaded = new BX.UploaderUtils.Hash();
	    this.itFailed = new BX.UploaderUtils.Hash();
	    this.thumb = {
	      tagName: "LI",
	      className: "bx-bxu-thumb-thumb"
	    };

	    if (!!params["thumb"]) {
	      for (var ii in params["thumb"]) {
	        if (params["thumb"].hasOwnProperty(ii) && this.thumb.hasOwnProperty(ii)) {
	          this.thumb[ii] = params["thumb"][ii];
	        }
	      }
	    }

	    BX.addCustomEvent(caller, "onItemIsAdded", BX.delegate(this.addItem, this));
	    BX.addCustomEvent(caller, "onFileIsDeleted", BX.delegate(this.deleteItem, this));
	    BX.addCustomEvent(caller, "onFileIsReinited", BX.delegate(this.reinitItem, this));
	    this.log('Initialized');
	    return this;
	  };

	  BX.UploaderQueue.prototype = {
	    showError: function showError(text) {
	      this.log('Error! ' + text);
	    },
	    log: function log(text) {
	      BX.UploaderUtils.log('queue', text);
	    },
	    addItem: function addItem(file, being) {
	      var isImage;
	      if (!this.showImage) isImage = false;else if (BX.type.isDomNode(file)) isImage = BX.UploaderUtils.isImage(file.value, null, null);else isImage = BX.UploaderUtils.isImage(file["name"], file["type"], file["size"]);
	      BX.onCustomEvent(this.uploader, "onFileIsBeforeCreated", [file, being, isImage, this.uploader]);
	      var params = {
	        copies: this.fileCopies,
	        fields: this.fileFields
	      },
	          res = isImage ? new BX.UploaderImage(file, params, this.limits, this.uploader) : new BX.UploaderFile(file, params, this.limits, this.uploader),
	          children,
	          node,
	          itemStatus = {
	        status: statuses.ready
	      };
	      BX.onCustomEvent(res, "onFileIsAfterCreated", [res, being, itemStatus, this.uploader]);
	      BX.onCustomEvent(this.uploader, "onFileIsAfterCreated", [res, being, itemStatus, this.uploader]);
	      this.items.setItem(res.id, res);

	      if (being || itemStatus["status"] !== statuses.ready) {
	        this.itUploaded.setItem(res.id, res);
	      } else {
	        this.itForUpload.setItem(res.id, res);
	      }

	      if (!!this.placeHolder) {
	        if (BX(being)) {
	          res.thumbNode = node = BX(being);
	          node.setAttribute("bx-bxu-item-id", res.id);
	        } else {
	          children = res.makeThumb();
	          node = BX.create(this.thumb.tagName, {
	            attrs: {
	              id: res.id + 'Item',
	              'bx-bxu-item-id': res.id,
	              className: this.thumb.className
	            }
	          });

	          if (BX.type.isNotEmptyString(children)) {
	            if (this.thumb.tagName == 'TR') {
	              children = children.replace(/[\n\t]/gi, "").replace(/^(\s+)(.*?)/gi, "$2").replace(/(.*?)(\s+)$/gi, "$1");
	              if (!!children["trim"]) children = children.trim();

	              var replaceFunction = function replaceFunction(str, tdParams, tdInnerHTML) {
	                var td = node.insertCell(-1),
	                    attrs = {
	                  colspan: true,
	                  headers: true,
	                  accesskey: true,
	                  "class": true,
	                  contenteditable: true,
	                  contextmenu: true,
	                  dir: true,
	                  hidden: true,
	                  id: true,
	                  lang: true,
	                  spellcheck: true,
	                  style: true,
	                  tabindex: true,
	                  title: true,
	                  translate: true
	                },
	                    param;
	                td.innerHTML = tdInnerHTML;
	                tdParams = tdParams.split(" ");

	                while ((param = tdParams.pop()) && param) {
	                  param = param.split("=");

	                  if (param.length == 2) {
	                    param[0] = param[0].replace(/^(\s+)(.*?)/gi, "$2").replace(/(.*?)(\s+)$/gi, "$1").replace(/^["'](.*?)["']$/gi, "$1");
	                    param[1] = param[1].replace(/^(\s+)(.*?)/gi, "$2").replace(/(.*?)(\s+)$/gi, "$1").replace(/^["'](.*?)["']$/gi, "$1");
	                    if (attrs[param[0]] === true) td.setAttribute(param[0], param[1]);else td[param[0]] = param[1];
	                  }
	                }

	                return "";
	              },
	                  regex = /^<td(.*?)>(.*?)<\/td>/i;

	              window.data1 = children;

	              while (regex.test(children)) {
	                children = children.replace(regex, replaceFunction);
	              }
	            } else {
	              node.innerHTML = children;
	            }
	          } else if (BX.type.isDomNode(children)) {
	            BX.adjust(node, {
	              children: [children]
	            });
	          }
	        }

	        if (!!window["jsDD"] && this.sortItems) {
	          if (!this._onbxdragstart) {
	            this._onbxdragstart = BX.delegate(this.onbxdragstart, this);
	            this._onbxdragstop = BX.delegate(this.onbxdragstop, this);
	            this._onbxdrag = BX.delegate(this.onbxdrag, this);
	            this._onbxdraghout = BX.delegate(this.onbxdraghout, this);
	            this._onbxdestdraghover = BX.delegate(this.onbxdestdraghover, this);
	            this._onbxdestdraghout = BX.delegate(this.onbxdestdraghout, this);
	            this._onbxdestdragfinish = BX.delegate(this.onbxdestdragfinish, this);
	          }

	          BX.addClass(node, "bx-drag-draggable");
	          node.onbxdragstart = this._onbxdragstart;
	          node.onbxdragstop = this._onbxdragstop;
	          node.onbxdrag = this._onbxdrag;
	          node.onbxdraghout = this._onbxdraghout;
	          window.jsDD.registerObject(node);
	          node.onbxdestdraghover = this._onbxdestdraghover;
	          node.onbxdestdraghout = this._onbxdestdraghout;
	          node.onbxdestdragfinish = this._onbxdestdragfinish;
	          window.jsDD.registerDest(node);
	          var inputs = BX.findChild(node, {
	            tagName: "INPUT",
	            props: {
	              "type": "text"
	            }
	          }, true, true);

	          for (var ii = 0; ii <= inputs.length; ii++) {
	            BX.bind(inputs[ii], "mousedown", BX.eventCancelBubble);
	          }
	        }

	        node.setAttribute("bx-item-id", res.id);

	        if (BX(being)) {
	          BX.onCustomEvent(this.uploader, "onFileIsBound", [res.id, res, this.caller, being]);
	          BX.onCustomEvent(res, "onFileIsBound", [res.id, res, this.caller, being]);
	        } else if (!!being) {
	          this.placeHolder.appendChild(node);
	          BX.onCustomEvent(this.uploader, "onFileIsAttached", [res.id, res, this.caller, being]);
	          BX.onCustomEvent(res, "onFileIsAttached", [res.id, res, this.caller, being]);
	        } else {
	          this.placeHolder.appendChild(node);
	          BX.onCustomEvent(this.uploader, "onFileIsAppended", [res.id, res, this.caller]);
	          BX.onCustomEvent(res, "onFileIsAppended", [res.id, res, this.caller]);
	        }
	      }

	      BX.onCustomEvent(this.uploader, "onQueueIsChanged", [this, "add", res.id, res]);
	    },
	    getItem: function getItem(id) {
	      var item = this.items.getItem(id);
	      if (item) return {
	        item: item,
	        node: item.thumbNode || BX(id + 'Item')
	      };
	      return null;
	    },
	    onbxdragstart: function onbxdragstart() {
	      var item = BX.proxy_context,
	          id = item && item.getAttribute("bx-item-id");

	      if (id) {
	        var template = item.innerHTML.replace(new RegExp(id, "gi"), "DragCopy");
	        item.__dragCopyDiv = BX.create('DIV', {
	          attrs: {
	            className: "bx-drag-object " + item.className
	          },
	          style: {
	            position: "absolute",
	            zIndex: 10,
	            width: item.clientWidth + 'px'
	          },
	          html: template
	        });
	        item.__dragCopyPos = BX.pos(item);
	        BX.onCustomEvent(this.uploader, "onBxDragStart", [item, item.__dragCopyDiv]);
	        document.body.appendChild(item.__dragCopyDiv);
	        BX.addClass(item, "bx-drag-source");
	        var c = BX('DragCopyProperCanvas'),
	            c1,
	            it = this.items.getItem(id);

	        if (c && it && BX(it.canvas)) {
	          c1 = it.canvas.cloneNode(true);
	          c.parentNode.replaceChild(c1, c);
	          c1.getContext("2d").drawImage(it.canvas, 0, 0);
	        }
	      }

	      return true;
	    },
	    onbxdragstop: function onbxdragstop() {
	      var item = BX.proxy_context;

	      if (item.__dragCopyDiv) {
	        BX.removeClass(item, "bx-drag-source");

	        item.__dragCopyDiv.parentNode.removeChild(item.__dragCopyDiv);

	        item.__dragCopyDiv = null;
	        delete item['__dragCopyDiv'];
	        delete item['__dragCopyPos'];
	      }

	      return true;
	    },
	    onbxdrag: function onbxdrag(x, y) {
	      var item = BX.proxy_context,
	          div = item.__dragCopyDiv;

	      if (div) {
	        if (item.__dragCopyPos) {
	          if (!item.__dragCopyPos.deltaX) item.__dragCopyPos.deltaX = item.__dragCopyPos.left - x;
	          if (!item.__dragCopyPos.deltaY) item.__dragCopyPos.deltaY = item.__dragCopyPos.top - y;
	          x += item.__dragCopyPos.deltaX;
	          y += item.__dragCopyPos.deltaY;
	        }

	        div.style.left = x + 'px';
	        div.style.top = y + 'px';
	      }
	    },
	    onbxdraghout: function onbxdraghout(currentNode, x, y) {},
	    onbxdestdraghover: function onbxdestdraghover(currentNode) {
	      if (!currentNode || !currentNode.hasAttribute("bx-bxu-item-id") || !this.items.hasItem(currentNode.getAttribute("bx-bxu-item-id"))) return;
	      var item = BX.proxy_context;
	      BX.addClass(item, "bx-drag-over");
	      return true;
	    },
	    onbxdestdraghout: function onbxdestdraghout() {
	      var item = BX.proxy_context;
	      BX.removeClass(item, "bx-drag-over");
	      return true;
	    },
	    onbxdestdragfinish: function onbxdestdragfinish(currentNode) {
	      var item = BX.proxy_context;
	      BX.removeClass(item, "bx-drag-over");
	      if (item == currentNode || !BX.hasClass(currentNode, "bx-drag-draggable")) return true;
	      var id = currentNode.getAttribute("bx-bxu-item-id");
	      if (!this.items.hasItem(id)) return;
	      var obj = item.parentNode,
	          n = obj.childNodes.length,
	          act,
	          it,
	          buff,
	          j;

	      for (j = 0; j < n; j++) {
	        if (obj.childNodes[j] == item) item.number = j;else if (obj.childNodes[j] == currentNode) currentNode.number = j;
	        if (currentNode.number > 0 && item.number > 0) break;
	      }

	      if (this.itForUpload.hasItem(id)) {
	        act = item.number <= currentNode.number ? "beforeItem" : item.nextSibling ? "afterItem" : "inTheEnd";
	        it = null;

	        if (act != "inTheEnd") {
	          for (j = item.number + (act == "beforeItem" ? 0 : 1); j < n; j++) {
	            if (this.itForUpload.hasItem(obj.childNodes[j].getAttribute("bx-bxu-item-id"))) {
	              it = obj.childNodes[j].getAttribute("bx-bxu-item-id");
	              break;
	            }
	          }

	          if (it === null) act = "inTheEnd";
	        }

	        buff = this.itForUpload.removeItem(currentNode.getAttribute("bx-bxu-item-id"));
	        if (act != "inTheEnd") this.itForUpload.insertBeforeItem(buff.id, buff, it);else this.itForUpload.setItem(buff.id, buff);
	      }

	      act = item.number <= currentNode.number ? "beforeItem" : item.nextSibling ? "afterItem" : "inTheEnd";
	      it = null;

	      if (act != "inTheEnd") {
	        for (j = item.number + (act == "beforeItem" ? 0 : 1); j < n; j++) {
	          if (this.items.hasItem(obj.childNodes[j].getAttribute("bx-bxu-item-id"))) {
	            it = obj.childNodes[j].getAttribute("bx-bxu-item-id");
	            break;
	          }
	        }

	        if (it === null) act = "inTheEnd";
	      }

	      buff = this.items.removeItem(currentNode.getAttribute("bx-bxu-item-id"));
	      if (act != "inTheEnd") this.items.insertBeforeItem(buff.id, buff, it);else this.items.setItem(buff.id, buff);
	      currentNode.parentNode.removeChild(currentNode);

	      if (item.number <= currentNode.number) {
	        item.parentNode.insertBefore(currentNode, item);
	      } else if (item.nextSibling) {
	        item.parentNode.insertBefore(currentNode, item.nextSibling);
	      } else {
	        for (j = 0; j < n; j++) {
	          if (obj.childNodes[j] == item) item.number = j;else if (obj.childNodes[j] == currentNode) currentNode.number = j;
	        }

	        if (item.number <= currentNode.number) {
	          item.parentNode.insertBefore(currentNode, item);
	        } else {
	          item.parentNode.appendChild(currentNode);
	        }
	      }

	      BX.onCustomEvent(item, "onFileOrderIsChanged", [item.id, item, this.caller]);
	      BX.onCustomEvent(this.uploader, "onQueueIsChanged", [this, "sort", item.id, item]);
	      return true;
	    },
	    deleteItem: function deleteItem(id, item) {
	      var pointer = this.getItem(id),
	          node;

	      if (pointer && (!this.placeHolder || (node = pointer.node) && node)) {
	        if (!!node) {
	          if (!!window["jsDD"]) {
	            node.onmousedown = null;
	            node.onbxdragstart = null;
	            node.onbxdragstop = null;
	            node.onbxdrag = null;
	            node.onbxdraghout = null;
	            node.onbxdestdraghover = null;
	            node.onbxdestdraghout = null;
	            node.onbxdestdragfinish = null;
	            node.__bxpos = null;
	            window.jsDD.arObjects[node.__bxddid] = null;
	            delete window.jsDD.arObjects[node.__bxddid];
	            window.jsDD.arDestinations[node.__bxddeid] = null;
	            delete window.jsDD.arDestinations[node.__bxddeid];
	          }

	          BX.unbindAll(node);
	          if (item["replaced"] !== true) node.parentNode.removeChild(node);
	        }

	        this.items.removeItem(id);
	        this.itUploaded.removeItem(id);
	        this.itFailed.removeItem(id);
	        this.itForUpload.removeItem(id);
	        BX.onCustomEvent(this.uploader, "onQueueIsChanged", [this, "delete", id, item]);
	        return true;
	      }

	      return false;
	    },
	    reinitItem: function reinitItem(id, item) {
	      var node, children;

	      if (!!this.placeHolder && this.items.hasItem(id) && (node = BX(id + 'Item')) && node) {
	        children = item.makeThumb();

	        if (BX.type.isNotEmptyString(children)) {
	          if (this.thumb.tagName == 'TR') {
	            children = children.replace(/[\n\t]/gi, "").replace(/^(\s+)(.*?)/gi, "$2").replace(/(.*?)(\s+)$/gi, "$1");
	            if (!!children["trim"]) children = children.trim();

	            var replaceFunction = function replaceFunction(str, tdParams, tdInnerHTML) {
	              var td = node.insertCell(-1),
	                  attrs = {
	                colspan: true,
	                headers: true,
	                accesskey: true,
	                "class": true,
	                contenteditable: true,
	                contextmenu: true,
	                dir: true,
	                hidden: true,
	                id: true,
	                lang: true,
	                spellcheck: true,
	                style: true,
	                tabindex: true,
	                title: true,
	                translate: true
	              },
	                  param;
	              td.innerHTML = tdInnerHTML;
	              tdParams = tdParams.split(" ");

	              while ((param = tdParams.pop()) && param) {
	                param = param.split("=");

	                if (param.length == 2) {
	                  param[0] = param[0].replace(/^(\s+)(.*?)/gi, "$2").replace(/(.*?)(\s+)$/gi, "$1").replace(/^["'](.*?)["']$/gi, "$1");
	                  param[1] = param[1].replace(/^(\s+)(.*?)/gi, "$2").replace(/(.*?)(\s+)$/gi, "$1").replace(/^["'](.*?)["']$/gi, "$1");
	                  if (attrs[param[0]] === true) td.setAttribute(param[0], param[1]);else td[param[0]] = param[1];
	                }
	              }

	              return "";
	            },
	                regex = /^<td(.*?)>(.*?)<\/td>/i;

	            window.data1 = children;

	            while (regex.test(children)) {
	              children = children.replace(regex, replaceFunction);
	            }
	          } else {
	            node.innerHTML = children;
	          }
	        } else if (BX.type.isDomNode(children)) {
	          while (BX(node.firstChild)) {
	            BX.remove(node.firstChild);
	          }

	          BX.adjust(node, {
	            children: [children]
	          });
	        }

	        BX.onCustomEvent(this.uploader, "onFileIsAppended", [item.id, item, this.caller]);
	        BX.onCustomEvent(item, "onFileIsAppended", [item.id, item, this.caller]);
	      }
	    },
	    clear: function clear() {
	      var item;

	      while ((item = this.items.getFirst()) && !!item) {
	        this.deleteItem(item.id, item);
	      }
	    },
	    restoreFiles: function restoreFiles(data, restoreErrored, startAgain) {
	      data.reset();
	      var item, copy, erroredFile;

	      while ((item = data.getNext()) && item) {
	        erroredFile = this.itFailed.hasItem(item.id);

	        if (restoreErrored === true) {
	          this.itFailed.removeItem(item.id);
	        }

	        if (!this.items.hasItem(item.id) || this.itFailed.hasItem(item.id)) {
	          continue;
	        }

	        if (startAgain === true || startAgain !== false && erroredFile) // for compatibility
	          {
	            delete item["uploadStatus"];
	            delete item.file["uploadStatus"];
	            delete item.file["firstChunk"];
	            delete item.file["package"];
	            delete item.file["packages"];

	            if (item.file["copies"]) {
	              item.file["copies"].reset();

	              while ((copy = item.file["copies"].getNext()) && copy) {
	                delete copy["uploadStatus"];
	                delete copy["firstChunk"];
	                delete copy["package"];
	                delete copy["packages"];
	              }

	              item.file["copies"].reset();
	            }

	            item["restored"] = startAgain === true ? "Y" : "C"; // Start again or continue
	          } else {
	          if (erroredFile) // If a error was occurred on the last step we should send this piece again
	            {
	              if (item.file["package"]) {
	                item.file["package"]--;
	              }

	              if (item.file["copies"]) {
	                item.file["copies"].reset();

	                while ((copy = item.file["copies"].getNext()) && copy) {
	                  delete copy["uploadStatus"];
	                  delete copy["firstChunk"];
	                  delete copy["package"];
	                  delete copy["packages"];
	                }

	                item.file["copies"].reset();
	              }
	            }

	          item["restored"] = "C"; // Continue
	        }

	        this.itUploaded.removeItem(item.id);
	        this.itForUpload.setItem(item.id, item);
	        BX.onCustomEvent(item, "onUploadRestore", [item]);
	      }
	    }
	  };
	  return statuses;
	})(window);

	(function (window) {
	  window.BX = window['BX'] || {};
	  if (window.BX["UploaderUtils"]) return false;
	  var BX = window.BX;
	  BX.UploaderLog = [];
	  BX.UploaderDebug = false;
	  var statuses = {
	    "new": 0,
	    ready: 1,
	    preparing: 2,
	    inprogress: 3,
	    done: 4,
	    failed: 5,
	    stopped: 6,
	    changed: 7,
	    uploaded: 8
	  };
	  BX.UploaderUtils = {
	    statuses: statuses,
	    getId: function getId() {
	      return new Date().valueOf() + Math.round(Math.random() * 1000000);
	    },
	    log: function log() {
	      if (BX.UploaderDebug === true) {
	        console.log(arguments);
	      } else {
	        BX.UploaderLog.push(arguments);
	      }
	    },
	    Hash: function () {
	      var d = function d() {
	        this.length = 0;
	        this.items = {};
	        this.order = [];
	        var i;

	        if (arguments.length == 1 && BX.type.isArray(arguments[0]) && arguments[0].length > 0) {
	          var data = arguments[0];

	          for (i = 0; i < data.length; i++) {
	            if (data[i] && babelHelpers.typeof(data[i]) == "object" && data[i]["id"]) {
	              this.setItem(data[i]["id"], data[i]);
	            }
	          }
	        } else {
	          for (i = 0; i < arguments.length; i += 2) {
	            this.setItem(arguments[i], arguments[i + 1]);
	          }
	        }
	      };

	      d.prototype = {
	        getIds: function getIds() {
	          return this.order;
	        },
	        getQueue: function getQueue(id) {
	          id += '';
	          return BX.util.array_search(id, this.order);
	        },
	        getByOrder: function getByOrder(order) {
	          return this.getItem(this.order[order]);
	        },
	        removeItem: function removeItem(in_key) {
	          in_key += '';
	          var tmp_value, number;

	          if (typeof this.items[in_key] != 'undefined') {
	            tmp_value = this.items[in_key];
	            number = this.getQueue(in_key);
	            this.pointer -= this.pointer >= number ? 1 : 0;
	            delete this.items[in_key];
	            this.order = BX.util.deleteFromArray(this.order, number);
	            this.length = this.order.length;
	          }

	          return tmp_value;
	        },
	        getItem: function getItem(in_key) {
	          in_key += '';
	          return this.items[in_key];
	        },
	        unshiftItem: function unshiftItem(in_key, in_value) {
	          in_key += '';

	          if (typeof in_value != 'undefined') {
	            if (typeof this.items[in_key] == 'undefined') {
	              this.order.unshift(in_key);
	              this.length = this.order.length;
	            }

	            this.items[in_key] = in_value;
	          }

	          return in_value;
	        },
	        setItem: function setItem(in_key, in_value) {
	          in_key += '';

	          if (typeof in_value != 'undefined') {
	            if (typeof this.items[in_key] == 'undefined') {
	              this.order.push(in_key);
	              this.length = this.order.length;
	            }

	            this.items[in_key] = in_value;
	          }

	          return in_value;
	        },
	        hasItem: function hasItem(in_key) {
	          in_key += '';
	          return typeof this.items[in_key] != 'undefined';
	        },
	        insertBeforeItem: function insertBeforeItem(in_key, in_value, after_key) {
	          in_key += '';

	          if (typeof in_value != 'undefined') {
	            if (typeof this.items[in_key] == 'undefined') {
	              this.order.splice(this.getQueue(after_key), 0, in_key);
	              this.length = this.order.length;
	            }

	            this.items[in_key] = in_value;
	          }

	          return in_value;
	        },
	        getFirst: function getFirst() {
	          var in_key,
	              item = null;

	          for (var ii = 0; ii < this.order.length; ii++) {
	            in_key = this.order[ii];

	            if (!!in_key && this.hasItem(in_key)) {
	              item = this.getItem(in_key);
	              break;
	            }
	          }

	          return item;
	        },
	        getNext: function getNext() {
	          this.pointer = 0 <= this.pointer && this.pointer < this.order.length ? this.pointer : -1;
	          var res = this.getItem(this.order[this.pointer + 1]);
	          if (!!res) this.pointer++;else this.pointer = -1;
	          return res;
	        },
	        getPrev: function getPrev() {
	          this.pointer = 0 <= this.pointer && this.pointer < this.order.length ? this.pointer : 0;
	          var res = this.getItem(this.order[this.pointer - 1]);
	          if (!!res) this.pointer--;
	          return res;
	        },
	        reset: function reset() {
	          this.pointer = -1;
	        },
	        setPointer: function setPointer(in_key) {
	          this.pointer = this.getQueue(in_key);
	          return this.pointer;
	        },
	        getLast: function getLast() {
	          var in_key,
	              item = null;

	          for (var ii = this.order.length; ii >= 0; ii--) {
	            in_key = this.order[ii];

	            if (!!in_key && this.hasItem(in_key)) {
	              item = this.getItem(in_key);
	              break;
	            }
	          }

	          return item;
	        }
	      };
	      return d;
	    }(),
	    getFileNameOnly: function getFileNameOnly(name) {
	      var delimiter = "\\",
	          start = name.lastIndexOf(delimiter),
	          finish = name.length;

	      if (start == -1) {
	        delimiter = "/";
	        start = name.lastIndexOf(delimiter);
	      }

	      if (start + 1 == name.length) {
	        finish = start;
	        start = name.substring(0, finish).lastIndexOf(delimiter);
	      }

	      name = name.substring(start + 1, finish);

	      if (delimiter == "/" && name.indexOf("?") > 0) {
	        name = name.substring(0, name.indexOf("?"));
	      }

	      if (name == '') name = 'noname';
	      return name;
	    },
	    isImageExt: function isImageExt(ext) {
	      return BX.message('bxImageExtensions') && BX.type.isNotEmptyString(ext) ? new RegExp('(?:^|\\W)(' + ext + ')(?:\\W|$)', 'gi').test(BX.message('bxImageExtensions')) : false;
	    },
	    isImage: function isImage(name, type, size) {
	      size = BX.type.isNumber(size) ? size : BX.type.isNotEmptyString(size) && !/[\D]+/gi.test(size) ? parseInt(size) : null;
	      return (type === null || (type || '').indexOf("image/") === 0) && (size === null || size < 20 * 1024 * 1024) && BX.UploaderUtils.isImageExt((name || '').lastIndexOf('.') > 0 ? name.substr(name.lastIndexOf('.') + 1).toLowerCase() : '');
	    },
	    scaleImage: function scaleImage(arSourceSize, arSize, resizeType) {
	      var sourceImageWidth = parseInt(arSourceSize["width"]),
	          sourceImageHeight = parseInt(arSourceSize["height"]);
	      resizeType = !resizeType && !!arSize["type"] ? arSize["type"] : resizeType;
	      arSize = !!arSize ? arSize : {};
	      arSize.width = parseInt(!!arSize.width ? arSize.width : 0);
	      arSize.height = parseInt(!!arSize.height ? arSize.height : 0);
	      var res = {
	        bNeedCreatePicture: false,
	        source: {
	          x: 0,
	          y: 0,
	          width: 0,
	          height: 0
	        },
	        destin: {
	          x: 0,
	          y: 0,
	          width: 0,
	          height: 0
	        }
	      },
	          width,
	          height;

	      if (!(sourceImageWidth > 0 || sourceImageHeight > 0)) {
	        BX.DoNothing();
	      } else {
	        if (!BX.type.isNotEmptyString(resizeType)) {
	          resizeType = "inscribed";
	        }

	        var ResizeCoeff, iResizeCoeff;

	        if (resizeType.indexOf("proportional") >= 0) {
	          width = Math.max(sourceImageWidth, sourceImageHeight);
	          height = Math.min(sourceImageWidth, sourceImageHeight);
	        } else {
	          width = sourceImageWidth;
	          height = sourceImageHeight;
	        }

	        if (resizeType == "exact") {
	          var ratio = sourceImageWidth / sourceImageHeight < arSize["width"] / arSize["height"] ? arSize["width"] / sourceImageWidth : arSize["height"] / sourceImageHeight,
	              x = Math.max(0, Math.round(sourceImageWidth / 2 - arSize["width"] / 2 / ratio)),
	              y = Math.max(0, Math.round(sourceImageHeight / 2 - arSize["height"] / 2 / ratio));
	          res.bNeedCreatePicture = true;
	          res.coeff = ratio;
	          res.destin["width"] = arSize["width"];
	          res.destin["height"] = arSize["height"];
	          res.source["x"] = x;
	          res.source["y"] = y;
	          res.source["width"] = Math.round(arSize["width"] / ratio, 0);
	          res.source["height"] = Math.round(arSize["height"] / ratio, 0);
	        } else {
	          if (resizeType == "circumscribed") {
	            ResizeCoeff = {
	              width: width > 0 ? arSize["width"] / width : 1,
	              height: height > 0 ? arSize["height"] / height : 1
	            };
	            iResizeCoeff = Math.max(ResizeCoeff["width"], ResizeCoeff["height"], 1);
	          } else {
	            ResizeCoeff = {
	              width: width > 0 ? arSize["width"] / width : 1,
	              height: height > 0 ? arSize["height"] / height : 1
	            };
	            iResizeCoeff = Math.min(ResizeCoeff["width"], ResizeCoeff["height"], 1);
	            iResizeCoeff = 0 < iResizeCoeff ? iResizeCoeff : 1;
	          }

	          res.bNeedCreatePicture = iResizeCoeff != 1;
	          res.coeff = iResizeCoeff;
	          res.destin["width"] = Math.max(1, parseInt(iResizeCoeff * sourceImageWidth));
	          res.destin["height"] = Math.max(1, parseInt(iResizeCoeff * sourceImageHeight));
	          res.source["x"] = 0;
	          res.source["y"] = 0;
	          res.source["width"] = sourceImageWidth;
	          res.source["height"] = sourceImageHeight;
	        }
	      }

	      return res;
	    },
	    dataURLToBlob: function dataURLToBlob(dataURL) {
	      var marker = ';base64,',
	          parts,
	          contentType,
	          raw,
	          rawLength;

	      if (dataURL.indexOf(marker) == -1) {
	        parts = dataURL.split(',');
	        contentType = parts[0].split(':')[1];
	        raw = parts[1];
	        return new Blob([raw], {
	          type: contentType
	        });
	      }

	      parts = dataURL.split(marker);
	      contentType = parts[0].split(':')[1];
	      raw = window.atob(parts[1]);
	      rawLength = raw.length;
	      var uInt8Array = new Uint8Array(rawLength);

	      for (var i = 0; i < rawLength; ++i) {
	        uInt8Array[i] = raw.charCodeAt(i);
	      }

	      return new Blob([uInt8Array], {
	        type: contentType
	      });
	    },
	    sizeof: function sizeof(obj) {
	      var size = 0,
	          key;

	      for (key in obj) {
	        if (obj.hasOwnProperty(key)) {
	          size += key.length;

	          if (babelHelpers.typeof(obj[key]) == "object") {
	            if (obj[key] === null) BX.DoNothing();else if (obj[key]["size"] > 0) size += obj[key].size;else size += BX.UploaderUtils.sizeof(obj[key]);
	          } else if (typeof obj[key] == "number") {
	            size += obj[key].toString().length;
	          } else if (!!obj[key] && obj[key].length > 0) {
	            size += obj[key].length;
	          }
	        }
	      }

	      return size;
	    },
	    FormToArray: function FormToArray(form, data) {
	      return BX.ajax.prepareForm(form, data);
	    },
	    getFormattedSize: function getFormattedSize(size, precision) {
	      var a = ["b", "Kb", "Mb", "Gb", "Tb"],
	          pos = 0;

	      while (size >= 1024 && pos < 4) {
	        size /= 1024;
	        pos++;
	      }

	      return Math.round(size * (precision > 0 ? precision * 10 : 1)) / (precision > 0 ? precision * 10 : 1) + " " + BX.message("FILE_SIZE_" + a[pos]);
	    },
	    bindEvents: function bindEvents(obj, event, func) {
	      var funcs = [],
	          ii;

	      if (typeof func == "string") {
	        eval('funcs.push(' + func + ');');
	      } else if (!!func["length"] && func["length"] > 0) {
	        for (ii = 0; ii < func.length; ii++) {
	          if (typeof func[ii] == "string") eval('funcs.push(' + func[ii] + ');');else funcs.push(func[ii]);
	        }
	      } else funcs.push(func);

	      if (funcs.length > 0) {
	        for (ii = 0; ii < funcs.length; ii++) {
	          BX.addCustomEvent(obj, event, funcs[ii]);
	        }
	      }
	    },
	    applyFilePart: function applyFilePart(file, blob) {
	      if (BX.type.isDomNode(file)) {
	        file.uploadStatus = statuses.done;
	      } else if (file == blob) {
	        file.uploadStatus = statuses.done;
	      } else if (file.blobed === true) {
	        file.uploadStatus = file.package + 1 >= file.packages ? statuses.done : statuses.inprogress;
	        if (file.uploadStatus == statuses.inprogress) file.package++;
	      }

	      return true;
	    },
	    getFilePart: function getFilePart(file, MaxFilesize) {
	      var blob,
	          chunkSize = MaxFilesize,
	          start,
	          end;

	      if (BX.type.isDomNode(file)) {
	        blob = file;
	      } else if (MaxFilesize <= 0 || file.size <= MaxFilesize) {
	        blob = file;
	      } else if (file['packages'] && file['packages'] <= file['package']) {
	        blob = null;
	      } else if (window.Blob || window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder) {
	        if (file['packages']) {
	          file.package++;
	          start = file.package * chunkSize;
	          end = start + chunkSize;
	        } else {
	          file.packages = Math.ceil(file.size / chunkSize);
	          file.package = 0;
	          start = 0;
	          end = chunkSize;
	        }

	        if ('mozSlice' in file) blob = file.mozSlice(start, end, file.type);else if ('webkitSlice' in file) blob = file.webkitSlice(start, end, file.type);else if ('slice' in file) blob = file.slice(start, end, file.type);else blob = file.Slice(start, end, file.type);

	        for (var ii in file) {
	          if (file.hasOwnProperty(ii)) {
	            blob[ii] = file[ii];
	          }
	        }

	        blob["name"] = file["name"];
	        blob["start"] = start;
	        blob["package"] = file.package;
	        blob["packages"] = file.packages;
	      }

	      return blob;
	    },
	    makeAnArray: function makeAnArray(file, data) {
	      file = !!file ? file : {
	        files: [],
	        props: {}
	      };
	      var ii;

	      for (var jj in data) {
	        if (data.hasOwnProperty(jj)) {
	          if (babelHelpers.typeof(data[jj]) == "object" && data[jj].length > 0) {
	            file[jj] = !!file[jj] ? file[jj] : [];

	            for (ii = 0; ii < data[jj].length; ii++) {
	              file[jj].push(data[jj][ii]);
	            }
	          } else {
	            for (ii in data[jj]) {
	              if (data[jj].hasOwnProperty(ii)) {
	                file[jj] = !!file[jj] ? file[jj] : {};
	                file[jj][ii] = data[jj][ii];
	              }
	            }
	          }
	        }
	      }

	      return file;
	    },
	    appendToForm: function appendToForm(fd, key, val) {
	      if (!!val && babelHelpers.typeof(val) == "object") {
	        for (var ii in val) {
	          if (val.hasOwnProperty(ii)) {
	            BX.UploaderUtils.appendToForm(fd, key + '[' + ii + ']', val[ii]);
	          }
	        }
	      } else {
	        fd.append(key, !!val ? val : '');
	      }
	    },
	    FormData: function FormData() {
	      return new (BX.Uploader.getInstanceName() == "BX.UploaderSimple" ? FormDataLocal : window.FormData)();
	    },
	    prepareData: function prepareData(arData) {
	      var data = {};

	      if (null != arData) {
	        if (babelHelpers.typeof(arData) == 'object') {
	          for (var i in arData) {
	            if (arData.hasOwnProperty(i)) {
	              var name = BX.util.urlencode(i);
	              if (babelHelpers.typeof(arData[i]) == 'object') data[name] = BX.UploaderUtils.prepareData(arData[i]);else data[name] = BX.util.urlencode(arData[i]);
	            }
	          }
	        } else data = BX.util.urlencode(arData);
	      }

	      return data;
	    }
	  };

	  var FormDataLocal = function FormDataLocal() {
	    var uniqueID;

	    do {
	      uniqueID = Math.floor(Math.random() * 99999);
	    } while (BX("form-" + uniqueID));

	    this.local = true;
	    this.form = BX.create("FORM", {
	      props: {
	        id: "form-" + uniqueID,
	        method: "POST",
	        enctype: "multipart/form-data",
	        encoding: "multipart/form-data"
	      },
	      style: {
	        display: "none"
	      }
	    });
	    document.body.appendChild(this.form);
	  };

	  FormDataLocal.prototype = {
	    append: function append(name, val) {
	      if (BX.type.isDomNode(val)) {
	        this.form.appendChild(val);
	      } else {
	        this.form.appendChild(BX.create("INPUT", {
	          props: {
	            type: "hidden",
	            name: name,
	            value: val
	          }
	        }));
	      }
	    }
	  };

	  BX.UploaderUtils.slice = function (file, start, end) {
	    var blob = null;
	    if ('mozSlice' in file) blob = file.mozSlice(start, end);else if ('webkitSlice' in file) blob = file.webkitSlice(start, end);else if ('slice' in file) blob = file.slice(start, end);else blob = file.Slice(start, end, file.type);
	    return blob;
	  };

	  BX.UploaderUtils.readFile = function (file, callback, method) {
	    if (window["FileReader"]) {
	      var fileReader = new FileReader();
	      fileReader.onload = fileReader.onerror = callback;
	      method = method || 'readAsDataURL';

	      if (fileReader[method]) {
	        fileReader[method](file);
	        return fileReader;
	      }
	    }

	    return false;
	  };
	})(window);

	var UploaderQueue = window.BX["UploaderQueue"];
	var UploaderUtils = window.BX["UploaderUtils"];

	var Uploader = /*#__PURE__*/function () {
	  function Uploader(params) {
	    babelHelpers.classCallCheck(this, Uploader);
	    babelHelpers.defineProperty(this, "fileInput", null);
	    babelHelpers.defineProperty(this, "form", null);
	    babelHelpers.defineProperty(this, "limits", {});
	    babelHelpers.defineProperty(this, "packages", new Map());
	    var input = params.input,
	        uploadFileUrl = params.uploadFileUrl,
	        id = params.id,
	        CID = params.CID,
	        controlId = params.controlId,
	        dropZone = params.dropZone,
	        placeHolder = params.placeHolder,
	        events = params.events;

	    if (main_core.Type.isStringFilled(uploadFileUrl)) {
	      this.uploadFileUrl = uploadFileUrl;
	    }

	    input = main_core.Type.isStringFilled(input) ? document.getElementById(input) : input;

	    if (main_core.Type.isDomNode(input)) {
	      this.fileInput = input;
	      this.form = input.form;
	      this.uploadFileUrl = this.uploadFileUrl || this.form.getAttribute('action');
	    } else if (input !== null) {
	      main_core.Runtime.debug(main_core.Loc.getMessage('UPLOADER_INPUT_IS_NOT_DEFINED'));
	      return;
	    }

	    if (!this.uploadFileUrl) {
	      main_core.Runtime.debug(main_core.Loc.getMessage('UPLOADER_ACTION_URL_NOT_DEFINED'));
	      return;
	    }

	    this.constructor.justCounter++;
	    var uniqueId = UploaderUtils.getId();
	    this.id = main_core.Type.isStringFilled(id) ? id : ['bitrixUploaderID', uniqueId].join('');
	    this.CID = main_core.Type.isStringFilled(CID) ? CID : 'CID' + uniqueId; // this is a security id

	    this.controlId = controlId || 'bitrixUploader'; // this is a control id can be lice control name

	    this.onChange = this.onChange.bind(this);
	    this.setLimits(params);
	    this.initParams(params);
	    this.init(this.fileInput);
	    this.dropZone = this.initDropZone(dropZone);
	    this.bindUserEvents(events);
	    this.initFilesQueue(params);
	    BX.onCustomEvent(window, 'onUploaderIsInited', [this.id, this]);
	    Uploader.repo.set(this.id, this);
	  }

	  babelHelpers.createClass(Uploader, [{
	    key: "setLimits",
	    value: function setLimits(_ref) {
	      var uploadMaxFilesize = _ref.uploadMaxFilesize,
	          uploadFileWidth = _ref.uploadFileWidth,
	          uploadFileHeight = _ref.uploadFileHeight,
	          allowUpload = _ref.allowUpload,
	          allowUploadExt = _ref.allowUploadExt;
	      this.limits = {
	        uploadMaxFilesize: uploadMaxFilesize || 0,
	        uploadFileWidth: uploadFileWidth || 0,
	        uploadFileHeight: uploadFileHeight || 0,
	        uploadFileExt: '',
	        uploadFile: this.fileInput ? this.fileInput.getAttribute('accept') : '',
	        allowUpload: allowUpload,
	        //compatibility
	        allowUploadExt: allowUploadExt //compatibility

	      };
	      var acceptAttribute = [];

	      if (main_core.Type.isStringFilled(this.limits['uploadFile'])) {
	        acceptAttribute.push(this.limits['uploadFile']);
	      }

	      if (allowUpload === 'I') {
	        acceptAttribute.push('image/*');
	      }

	      if (main_core.Type.isStringFilled(allowUploadExt)) {
	        var separator = allowUploadExt.indexOf(',') >= 0 ? ',' : ' ';
	        var extensions = [];
	        allowUploadExt.split(separator).forEach(function (extension) {
	          extensions.push(extension.trim().replace('.', ''));
	          acceptAttribute.push('.' + extension.trim().replace('.', ''));
	        });

	        if (extensions) {
	          this.limits["uploadFileExt"] = extensions;
	        }
	      }

	      this.limits['uploadFile'] = acceptAttribute.join(', ');
	    }
	  }, {
	    key: "initParams",
	    value: function initParams(_ref2) {
	      var uploadMethod = _ref2.uploadMethod,
	          uploadFormData = _ref2.uploadFormData,
	          filesInputMultiple = _ref2.filesInputMultiple,
	          uploadInputName = _ref2.uploadInputName,
	          uploadInputInfoName = _ref2.uploadInputInfoName,
	          deleteFileOnServer = _ref2.deleteFileOnServer,
	          pasteFileHashInForm = _ref2.pasteFileHashInForm;
	      // Limits
	      this.params = {
	        filesInputMultiple: this.fileInput && this.fileInput["multiple"] || filesInputMultiple ? "multiple" : false,
	        uploadFormData: uploadFormData === "N" ? "N" : "Y",
	        uploadMethod: uploadMethod === "immediate" ? "immediate" : "deferred",
	        uploadInputName: main_core.Type.isStringFilled(uploadInputName) ? uploadInputName : 'bxu_files',
	        uploadInputInfoName: main_core.Type.isStringFilled(uploadInputInfoName) ? uploadInputInfoName : 'bxu_info',
	        deleteFileOnServer: !(deleteFileOnServer === false || deleteFileOnServer === "N"),
	        //to insert hash into the form
	        filesInputName: this.fileInput && this.fileInput["name"] ? this.fileInput["name"] : "FILES",
	        pasteFileHashInForm: !(pasteFileHashInForm === false || pasteFileHashInForm === "N")
	      };
	    }
	  }, {
	    key: "init",
	    value: function init(fileInput) {
	      if (fileInput === null) {
	        return true;
	      }

	      if (main_core.Type.isDomNode(fileInput)) {
	        var newFileInput = this.makeFileInput(fileInput);

	        if (fileInput === this.fileInput) {
	          this.fileInput = newFileInput;
	        }

	        if (newFileInput) {
	          return true;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "initDropZone",
	    value: function initDropZone(dropZoneNode) {
	      var _this = this;

	      var dropZone = new DropZone(dropZoneNode);
	      main_core_events.EventEmitter.subscribe(dropZone, Options.getEventName('caught'), function (_ref3) {
	        var data = _ref3.data;

	        _this.onChange(data);
	      });
	      main_core_events.EventEmitter.subscribe(this, Options.getEventName('destroy'), function () {
	        main_core_events.EventEmitter.unsubscribeAll(dropZone, Options.getEventName('caught'));
	        dropZone.destroy();
	      });
	      return dropZone;
	    }
	  }, {
	    key: "initFilesQueue",
	    value: function initFilesQueue(_ref4) {
	      var fields = _ref4.fields,
	          copies = _ref4.copies,
	          placeHolder = _ref4.placeHolder,
	          showImage = _ref4.showImage,
	          sortItems = _ref4.sortItems,
	          thumb = _ref4.thumb,
	          queueFields = _ref4.queueFields;
	      var params = {
	        fields: queueFields && queueFields['fields'] ? queueFields['fields'] : fields,
	        copies: queueFields && queueFields['copies'] ? queueFields['copies'] : copies,
	        placeHolder: queueFields && queueFields['placeHolder'] ? queueFields['placeHolder'] : placeHolder,
	        showImage: queueFields && queueFields['showImage'] ? queueFields['showImage'] : showImage,
	        sortItems: queueFields && queueFields['sortItems'] ? queueFields['sortItems'] : sortItems,
	        thumb: queueFields && queueFields['thumb'] ? queueFields['thumb'] : thumb
	      };
	      this.queue = new UploaderQueue(params, this.limits, this);
	    }
	  }, {
	    key: "bindUserEvents",
	    value: function bindUserEvents(events) {
	      if (!main_core.Type.isPlainObject(events)) {
	        return;
	      }

	      for (var eventName in events) {
	        if (events.hasOwnProperty(eventName)) {
	          main_core_events.EventEmitter.subscribe(this, eventName, events[eventName]);
	        }
	      }
	    }
	  }, {
	    key: "makeFileInput",
	    value: function makeFileInput(oldFileInput) {
	      if (!main_core.Type.isDomNode(oldFileInput)) {
	        return false;
	      }

	      main_core.Event.unbindAll(oldFileInput, 'change');
	      var newFileInput = oldFileInput.cloneNode(true);
	      newFileInput.value = '';
	      newFileInput.setAttribute('name', this.params["uploadInputName"] + '[]');
	      newFileInput.setAttribute('multiple', this.params["filesInputMultiple"]);
	      newFileInput.setAttribute('accept', this.limits["uploadFile"]);
	      oldFileInput.parentNode.replaceChild(newFileInput, oldFileInput);
	      BX.onCustomEvent(this, "onFileinputIsReinited", [newFileInput, this]);
	      main_core.Event.bind(newFileInput, "change", this.onChange);
	      return newFileInput;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      if (!event) {
	        return;
	      }

	      if (event['preventDefault']) {
	        event.preventDefault();
	      }

	      if (event['stopPropagation']) {
	        event.stopPropagation();
	      }

	      var files = [];

	      if (main_core.Type.isArray(event)) {
	        files = event;
	      } else if (main_core.Type.isObject(event)) {
	        if (event['target']) {
	          var fileInput = event['target'];
	          files = fileInput.files;

	          if (!fileInput || fileInput.disabled) {
	            return false;
	          }

	          BX.onCustomEvent(this, "onFileinputIsChanged", [fileInput, this]);
	          this.init(fileInput);
	        } else if (event['files']) {
	          files = event['files'];
	        }
	      }

	      this.onAttach(files);
	      return false;
	    }
	  }, {
	    key: "onAttach",
	    value: function onAttach(files, nodes, check) {
	      var _this2 = this;

	      if (!files || !files['length']) {
	        return false;
	      }

	      check = check !== false;
	      files = babelHelpers.toConsumableArray(files);
	      nodes = nodes && main_core.Type.isArray(nodes) ? babelHelpers.toConsumableArray(nodes) : [];
	      BX.onCustomEvent(this, "onAttachFiles", [files, nodes, this]);
	      var added = false;
	      babelHelpers.toConsumableArray(files).forEach(function (file, index) {
	        var ext = '';
	        var type = (file['type'] || '').toLowerCase();

	        if (main_core.Type.isDomNode(file) && file.value) {
	          ext = (file.value.name || '').split('.').pop();
	        } else {
	          ext = (file['name'] || file['tmp_url'] || '').split('.').pop();

	          if (ext.indexOf('?') > 0) {
	            ext = ext.substr(0, ext.indexOf('?'));
	          }
	        }

	        ext = ext.toLowerCase();

	        if (check) {
	          var errors = [];

	          if (_this2.limits['uploadFile'].indexOf('image/') >= 0 && type.indexOf('image/') < 0 && Options.getImageExtensions().indexOf(ext) < 0) {
	            errors.push('File type is not an image like.');
	          }

	          if (_this2.limits['uploadFileExt'].length > 0) {
	            if (_this2.limits['uploadFileExt'].indexOf(ext) < 0) {
	              errors.push("File extension ".concat(ext, " is in ").concat(_this2.limits['uploadFileExt']));
	            } else {
	              errors.pop();
	            }
	          }

	          if (_this2.limits['uploadMaxFilesize'] > 0 && file.size > _this2.limits['uploadMaxFilesize']) {
	            errors.push("File size ".concat(file.size, " is bigger than ").concat(_this2.limits['uploadMaxFilesize']));
	          }

	          if (errors.length > 0) {
	            return;
	          }
	        }

	        if (String['normalize']) {
	          file.name = String(file.name).normalize();
	        }

	        BX.onCustomEvent(_this2, "onItemIsAdded", [file, nodes[index] || null, _this2]);
	        added = true;
	      });

	      if (added && this.params["uploadMethod"] === "immediate") {
	        this.submit();
	      }

	      return false;
	    }
	  }, {
	    key: "getFormData",
	    value: function getFormData() {
	      var formData = new FormData(this.params["uploadFormData"] === "Y" && this.form ? this.form : undefined);
	      var entries = formData.entries();
	      var entry;

	      while ((entry = entries.next()) && entry.done === false) {
	        var _entry$value = babelHelpers.slicedToArray(entry.value, 1),
	            name = _entry$value[0];

	        if (name.indexOf(this.params["filesInputName"]) === 0 || name.indexOf(this.params["uploadInputInfoName"]) === 0 || name.indexOf(this.params["uploadInputName"]) === 0) {
	          formData.delete(name);
	        }
	      }

	      formData.append('AJAX_POST', 'Y');
	      formData.append('USER_ID', main_core.Loc.getMessage('USER_ID'));
	      formData.append('sessid', BX.bitrix_sessid());

	      if (BX.message.SITE_ID) {
	        formData.append('SITE_ID', BX.message.SITE_ID);
	      }

	      formData.append(this.params["uploadInputInfoName"] + '[controlId]', this.controlId);
	      formData.append(this.params["uploadInputInfoName"] + '[CID]', this.CID);
	      formData.append(this.params["uploadInputInfoName"] + '[uploadInputName]', this.params["uploadInputName"]);
	      formData.append(this.params["uploadInputInfoName"] + '[version]', Options.getVersion());
	      return formData;
	    }
	  }, {
	    key: "submit",
	    value: function submit() {
	      var _this3 = this;

	      //region Compatibility
	      if (this.queue.itForUpload.length <= 0) {
	        BX.onCustomEvent(this, 'onStart', [null, {
	          filesCount: 0
	        }, this]);
	        BX.onCustomEvent(this, 'onDone', [null, null, {
	          filesCount: 0
	        }]);
	        BX.onCustomEvent(this, 'onFinish', [null, null, {
	          filesCount: 0
	        }]);
	        return;
	      } //endregion


	      var files = Object.values(this.queue.itForUpload.items);
	      var formData = this.getFormData(); //region Here we can change formData

	      var changedData = {};
	      var buffer1 = {
	        post: {
	          data: changedData,
	          size: 0,
	          filesCount: files.length
	        },
	        //compatibility field
	        filesCount: files.length
	      };
	      var eventOnPackageIsInitialized = new main_core_events.BaseEvent();
	      eventOnPackageIsInitialized.setCompatData([buffer1, this.queue.itForUpload]);
	      eventOnPackageIsInitialized.setData({
	        formData: formData,
	        data: changedData,
	        files: files
	      });
	      main_core_events.EventEmitter.emit(this, 'onPackageIsInitialized', eventOnPackageIsInitialized);
	      appendToForm(formData, buffer1.post.data);

	      if (buffer1.post.data !== changedData) {
	        appendToForm(formData, changedData);
	      } //endregion


	      var packageId = 'pIndex' + (new Date().valueOf() + Math.round(Math.random() * 1000000));
	      formData.append(this.params["uploadInputInfoName"] + '[packageIndex]', packageId);
	      formData.append(this.params["uploadInputInfoName"] + '[mode]', 'upload');
	      formData.append(this.params["uploadInputInfoName"] + '[filesCount]', files.length);

	      if (this.packages.size <= 0) {
	        console.group('Upload');
	      }

	      console.log('1. Create a new Package');
	      var packItem = new Package({
	        id: packageId,
	        formData: formData,
	        files: files,
	        uploadFileUrl: this.uploadFileUrl,
	        uploadInputName: this.params["uploadInputName"]
	      });
	      this.queue.itForUpload = new UploaderUtils.Hash();
	      var eventOnStart = new main_core_events.BaseEvent();
	      eventOnStart.setCompatData([packageId, Object.assign({
	        post: {
	          data: packItem.data,
	          filesCount: files.length
	        }
	      }, packItem), this]);
	      eventOnStart.setData({
	        package: packItem
	      });
	      main_core_events.EventEmitter.emit(this, 'onStart', eventOnStart);
	      this.packages.set(packItem.getId(), packItem);
	      main_core_events.EventEmitter.emit(this, 'onBusy');
	      packItem.subscribeOnce('done', function (_ref5) {
	        var p = _ref5.target,
	            status = _ref5.data.status;
	        var evDone = new main_core_events.BaseEvent();
	        evDone.setCompatData([{}, packageId, packItem, packItem.getServerResponse()]);
	        evDone.setData({
	          package: packItem,
	          response: packItem.getServerResponse()
	        });
	        main_core_events.EventEmitter.emit(_this3, 'onDone', evDone); // region Compatibility

	        if (status === 'failed') {
	          main_core_events.EventEmitter.emit(_this3, 'onError', new main_core_events.BaseEvent({
	            compatData: [{}, packageId, packItem.getServerResponse()]
	          }));
	        } // endregion Compatibility


	        _this3.packages.delete(p.getId());

	        if (_this3.packages.size <= 0) {
	          setTimeout(function () {
	            var ev = new main_core_events.BaseEvent();
	            ev.setCompatData([{}, packageId, packItem, packItem.getServerResponse()]);
	            ev.setData({
	              package: packItem,
	              response: packItem.getServerResponse()
	            });
	            main_core_events.EventEmitter.emit(_this3, 'onFinish', ev);
	            console.groupEnd('Upload');
	          });
	        }
	      });
	      packItem.subscribe('fileIsUploaded', function (_ref6) {
	        var _ref6$data = _ref6.data,
	            itemId = _ref6$data.itemId,
	            item = _ref6$data.item,
	            response = _ref6$data.response;

	        _this3.queue.itUploaded.setItem(itemId, item);

	        BX.onCustomEvent(_this3, 'onFileIsUploaded', [itemId, item, response]);
	        BX.onCustomEvent(item, 'onUploadDone', [item, response, _this3, packItem.getId()]);
	      });
	      packItem.subscribe('fileIsErrored', function (_ref7) {
	        var _ref7$data = _ref7.data,
	            itemId = _ref7$data.itemId,
	            item = _ref7$data.item,
	            response = _ref7$data.response;

	        _this3.queue.itFailed.setItem(itemId, item);

	        BX.onCustomEvent(_this3, 'onFileIsUploadedWithError', [itemId, item, response, _this3, packItem.getId()]);
	        BX.onCustomEvent(item, 'onUploadError', [item, response, _this3, packItem.getId()]);
	      });
	      packItem.subscribe('fileIsInProgress', function (_ref8) {
	        var _ref8$data = _ref8.data,
	            item = _ref8$data.item,
	            percent = _ref8$data.percent;
	        BX.onCustomEvent(item, 'onUploadProgress', [item, percent, _this3, packItem.getId()]);
	      });

	      if (packItem.prepare()) {
	        files.forEach(function (item) {
	          BX.onCustomEvent(item, 'onUploadStart', [item, 0, _this3, packItem.getId()]);
	        });
	        Streams.addPackage(packItem);
	      }
	    }
	  }, {
	    key: "log",
	    value: function log(text) {}
	  }, {
	    key: "destruct",
	    value: function destruct() {
	      main_core_events.EventEmitter.emit(this, Options.getEventName('destroy'));
	      delete this.dropZone;
	    }
	    /*region Compatbility */

	  }, {
	    key: "getItem",

	    /*endregion*/
	    value: function getItem(id) {
	      return this.queue.getItem(id);
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return this.queue.items;
	    }
	  }, {
	    key: "restoreItems",
	    value: function restoreItems() {
	      //Todo check it
	      this.queue.restoreFiles.apply(this.queue, arguments);
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      var item;

	      while ((item = this.queue.items.getFirst()) && item) {
	        item.deleteFile();
	      }
	    }
	  }, {
	    key: "controlID",
	    get: function get() {
	      return this.controlId;
	    }
	  }, {
	    key: "dialogName",
	    get: function get() {
	      return "BX.Uploader";
	    }
	  }, {
	    key: "length",
	    get: function get() {
	      return this.queue.itForUpload.length;
	    }
	  }, {
	    key: "streams",
	    get: function get() {
	      var _this4 = this;

	      if (!this['#_streams']) {
	        this['#_streams'] = {
	          packages: {
	            getItem: function getItem(id) {
	              return _this4.packages.get(id);
	            }
	          }
	        };
	      }

	      return this['#_streams'];
	    }
	  }], [{
	    key: "getById",
	    value: function getById(id) {
	      return this.repo.get(id);
	    }
	  }, {
	    key: "getInstanceById",
	    value: function getInstanceById(id) {
	      return this.repo.get(id);
	    }
	  }, {
	    key: "getInstanceName",
	    value: function getInstanceName() {
	      return 'BX.Uploader';
	    }
	  }]);
	  return Uploader;
	}();

	babelHelpers.defineProperty(Uploader, "repo", new Map());
	babelHelpers.defineProperty(Uploader, "justCounter", 0);
	babelHelpers.defineProperty(Uploader, "getInstance", function (params) {
	  BX.onCustomEvent(window, "onUploaderIsAlmostInited", ['BX.Uploader', params]);
	  return new this(params);
	});

	var Manager = /*#__PURE__*/function () {
	  function Manager() {
	    babelHelpers.classCallCheck(this, Manager);
	  }

	  babelHelpers.createClass(Manager, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return Uploader.getById(id);
	    }
	  }]);
	  return Manager;
	}();

	exports.UploaderManager = Manager;
	exports.Uploader = Uploader;

}((this.BX = this.BX || {}),BX,BX.Event));
//# sourceMappingURL=uploader.js.map
