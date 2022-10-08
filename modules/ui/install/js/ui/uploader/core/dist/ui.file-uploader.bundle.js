this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	var FileStatus = {
	  INIT: 'init',
	  ADDED: 'added',
	  LOADING: 'loading',
	  PENDING: 'pending',
	  UPLOADING: 'uploading',
	  ABORTED: 'aborted',
	  COMPLETE: 'complete',
	  LOAD_FAILED: 'load-failed',
	  UPLOAD_FAILED: 'upload-failed'
	};

	var FileOrigin = {
	  CLIENT: 'client',
	  SERVER: 'server'
	};

	var AbstractUploadController = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(AbstractUploadController, _EventEmitter);

	  function AbstractUploadController(server) {
	    var _this;

	    babelHelpers.classCallCheck(this, AbstractUploadController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AbstractUploadController).call(this));

	    _this.setEventNamespace('BX.UI.FileUploader.UploadController');

	    _this.server = server;
	    return _this;
	  }

	  babelHelpers.createClass(AbstractUploadController, [{
	    key: "getServer",
	    value: function getServer() {
	      return this.server;
	    }
	  }, {
	    key: "upload",
	    value: function upload(file) {
	      throw new Error('You must implement upload() method.');
	    }
	  }, {
	    key: "abort",
	    value: function abort() {
	      throw new Error('You must implement abort() method.');
	    }
	  }]);
	  return AbstractUploadController;
	}(main_core_events.EventEmitter);

	var AbstractLoadController = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(AbstractLoadController, _EventEmitter);

	  function AbstractLoadController(server) {
	    var _this;

	    babelHelpers.classCallCheck(this, AbstractLoadController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AbstractLoadController).call(this));

	    _this.setEventNamespace('BX.UI.FileUploader.LoadController');

	    _this.server = server;
	    return _this;
	  }

	  babelHelpers.createClass(AbstractLoadController, [{
	    key: "getServer",
	    value: function getServer() {
	      return this.server;
	    }
	  }, {
	    key: "load",
	    value: function load(file) {
	      throw new Error('You must implement load() method.');
	    }
	  }, {
	    key: "abort",
	    value: function abort() {
	      throw new Error('You must implement abort() method.');
	    }
	  }]);
	  return AbstractLoadController;
	}(main_core_events.EventEmitter);

	var crypto = window.crypto || window.msCrypto;

	var createUniqueId = function createUniqueId() {
	  return "".concat(1e7, "-", 1e3, "-", 4e3, "-", 8e3, "-", 1e11).replace(/[018]/g, function (c) {
	    return (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16);
	  });
	};

	var getExtensionFromType = function getExtensionFromType(type) {
	  if (!main_core.Type.isStringFilled(type)) {
	    return '';
	  }

	  var subtype = type.split('/').pop();

	  if (/javascript/.test(subtype)) {
	    return 'js';
	  }

	  if (/plain/.test(subtype)) {
	    return 'txt';
	  }

	  if (/svg/.test(subtype)) {
	    return 'svg';
	  }

	  if (/[a-z]+/.test(subtype)) {
	    return subtype;
	  }

	  return '';
	};

	var counter = 0;

	var createFileFromBlob = function createFileFromBlob(blob, fileName) {
	  if (!main_core.Type.isStringFilled(fileName)) {
	    var date = new Date();
	    fileName = "File ".concat(date.getFullYear(), "-").concat(date.getMonth(), "-").concat(date.getDate(), "-").concat(++counter);
	    var extension = getExtensionFromType(blob.type);

	    if (extension) {
	      fileName += ".".concat(extension);
	    }
	  }

	  try {
	    return new File([blob], fileName, {
	      lastModified: Date.now(),
	      lastModifiedDate: new Date(),
	      type: blob.type
	    });
	  } catch (exception) {
	    var file = blob.slice(0, blob.size, blob.type);
	    file.name = fileName;
	    file.lastModified = Date.now();
	    file.lastModifiedDate = new Date();
	    return file;
	  }
	};

	var regexp = /^data:((?:\w+\/(?:(?!;).)+)?)((?:;[\w\W]*?[^;])*),(.+)$/;

	var isDataUri = function isDataUri(str) {
	  return typeof str === 'string' ? str.match(regexp) : false;
	};

	var createBlobFromDataUri = function createBlobFromDataUri(dataURI) {
	  var byteString = atob(dataURI.split(',')[1]);
	  var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
	  var buffer = new ArrayBuffer(byteString.length);
	  var view = new Uint8Array(buffer);

	  for (var i = 0; i < byteString.length; i++) {
	    view[i] = byteString.charCodeAt(i);
	  }

	  return new Blob([buffer], {
	    type: mimeString
	  });
	};

	var getFileExtension = function getFileExtension(filename) {
	  var position = main_core.Type.isStringFilled(filename) ? filename.lastIndexOf('.') : -1;
	  return position > 0 ? filename.substring(position + 1) : '';
	};

	var imageExtensions = ['jpg', 'bmp', 'jpeg', 'jpe', 'gif', 'png', 'webp'];

	var isResizableImage = function isResizableImage(file) {
	  var mimeType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	  var filename = main_core.Type.isFile(file) ? file.name : file;
	  var type = main_core.Type.isFile(file) ? file.type : mimeType;
	  var extension = getFileExtension(filename).toLowerCase();

	  if (imageExtensions.includes(extension)) {
	    if (type === null || /^image/.test(type)) {
	      return true;
	    }
	  }

	  return false;
	};

	var formatFileSize = function formatFileSize(size) {
	  var base = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1024;
	  var i = 0;
	  var units = getUnits();

	  while (size >= base && units[i + 1]) {
	    size /= base;
	    i++;
	  }

	  return (main_core.Type.isInteger(size) ? size : size.toFixed(1)) + units[i];
	};

	var fileSizeUnits = null;

	var getUnits = function getUnits() {
	  if (fileSizeUnits !== null) {
	    return fileSizeUnits;
	  }

	  var units = main_core.Loc.getMessage('UPLOADER_FILE_SIZE_POSTFIXES').split(/[|]/);
	  fileSizeUnits = main_core.Type.isArrayFilled(units) ? units : ['B', 'kB', 'MB', 'GB', 'TB'];
	  return fileSizeUnits;
	};

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _setProperty = new WeakSet();

	var UploaderFile = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(UploaderFile, _EventEmitter);

	  function UploaderFile(source) {
	    var _this;

	    var fileOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, UploaderFile);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UploaderFile).call(this));

	    _setProperty.add(babelHelpers.assertThisInitialized(_this));

	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "file", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "serverId", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "name", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "originalName", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "size", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "type", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "width", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "height", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clientPreview", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clientPreviewUrl", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clientPreviewWidth", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clientPreviewHeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "serverPreviewUrl", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "serverPreviewWidth", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "serverPreviewHeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "downloadUrl", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "removeUrl", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "status", FileStatus.INIT);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "origin", FileOrigin.CLIENT);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "uploadController", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loadController", null);

	    _this.setEventNamespace('BX.UI.FileUploader.File');

	    var options = main_core.Type.isPlainObject(fileOptions) ? fileOptions : {};

	    if (main_core.Type.isFile(source)) {
	      _this.file = source;
	    } else if (main_core.Type.isBlob(source)) {
	      _this.file = createFileFromBlob(source, options.name || source.name);
	    } else if (isDataUri(source)) {
	      var blob = createBlobFromDataUri(source);
	      _this.file = createFileFromBlob(blob, options.name);
	    } else if (main_core.Type.isNumber(source) || main_core.Type.isStringFilled(source)) {
	      _this.origin = FileOrigin.SERVER;
	      _this.serverId = source;

	      if (main_core.Type.isPlainObject(options)) {
	        _this.setFile(options);
	      }
	    }

	    _this.id = main_core.Type.isStringFilled(options.id) ? options.id : createUniqueId();

	    _this.subscribeFromOptions(options.events);

	    _this.fireStateChangeEvent = main_core.Runtime.debounce(_this.fireStateChangeEvent, 0, babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(UploaderFile, [{
	    key: "load",
	    value: function load() {
	      if (!this.canLoad()) {
	        return;
	      }

	      this.setStatus(FileStatus.LOADING);
	      this.emit('onLoadStart');
	      this.loadController.load(this);
	    }
	  }, {
	    key: "upload",
	    value: function upload() {
	      var _this2 = this;

	      if (!this.canUpload()) {
	        return;
	      }

	      var event = new main_core_events.BaseEvent({
	        data: {
	          file: this
	        }
	      });
	      this.emit('onBeforeUpload', event);

	      if (event.isDefaultPrevented()) {
	        return;
	      }

	      this.setStatus(FileStatus.UPLOADING);
	      event = new main_core_events.BaseEvent({
	        data: {
	          file: this.getFile()
	        }
	      });
	      this.emitAsync('onPrepareFileAsync', event).then(function (result) {
	        var file = main_core.Type.isArrayFilled(result) && main_core.Type.isFile(result[0]) ? result[0] : _this2.getFile();

	        _this2.emit('onUploadStart');

	        if (_this2.uploadController) {
	          _this2.uploadController.upload(file);
	        }
	      }).catch(function (error) {
	        console.error(error);
	      });
	    }
	  }, {
	    key: "abort",
	    value: function abort() {
	      if (this.uploadController) {
	        this.uploadController.abort();
	      }

	      this.setStatus(FileStatus.ABORTED);
	      this.emit('onAbort');
	    }
	  }, {
	    key: "abortLoad",
	    value: function abortLoad() {
	      if (this.loadController) {
	        this.loadController.abort();
	      }

	      this.setStatus(FileStatus.ABORTED);
	      this.emit('onAbort');
	    }
	  }, {
	    key: "retry",
	    value: function retry() {// TODO
	    }
	  }, {
	    key: "cancel",
	    value: function cancel() {
	      this.abort();
	      this.emit('onCancel');
	    }
	  }, {
	    key: "setUploadController",
	    value: function setUploadController(controller) {
	      this.uploadController = controller;
	    }
	  }, {
	    key: "setLoadController",
	    value: function setLoadController(controller) {
	      this.loadController = controller;
	    }
	  }, {
	    key: "isReadyToUpload",
	    value: function isReadyToUpload() {
	      return this.getStatus() === FileStatus.PENDING;
	    }
	  }, {
	    key: "isUploadable",
	    value: function isUploadable() {
	      return this.uploadController !== null;
	    }
	  }, {
	    key: "isLoadable",
	    value: function isLoadable() {
	      return this.loadController !== null;
	    }
	  }, {
	    key: "canUpload",
	    value: function canUpload() {
	      return this.isReadyToUpload() && this.isUploadable();
	    }
	  }, {
	    key: "canLoad",
	    value: function canLoad() {
	      return this.getStatus() === FileStatus.ADDED && this.isLoadable();
	    }
	  }, {
	    key: "isUploading",
	    value: function isUploading() {
	      return this.getStatus() === FileStatus.UPLOADING;
	    }
	  }, {
	    key: "isLoading",
	    value: function isLoading() {
	      return this.getStatus() === FileStatus.LOADING;
	    }
	  }, {
	    key: "isComplete",
	    value: function isComplete() {
	      return this.getStatus() === FileStatus.COMPLETE;
	    }
	  }, {
	    key: "isFailed",
	    value: function isFailed() {
	      return this.getStatus() === FileStatus.LOAD_FAILED || this.getStatus() === FileStatus.UPLOAD_FAILED;
	    }
	  }, {
	    key: "getFile",
	    value: function getFile() {
	      return this.file;
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "setFile",
	    value: function setFile(file) {
	      if (main_core.Type.isFile(file)) {
	        this.file = file;
	      } else if (main_core.Type.isPlainObject(file)) {
	        this.setName(file.name);
	        this.setOriginalName(file.originalName);
	        this.setType(file.type);
	        this.setSize(file.size);
	        this.setServerId(file.serverId);
	        this.setWidth(file.width);
	        this.setHeight(file.height);
	        this.setClientPreview(file.clientPreview, file.clientPreviewWidth, file.clientPreviewHeight);
	        this.setServerPreview(file.serverPreviewUrl, file.serverPreviewWidth, file.serverPreviewHeight);
	        this.setDownloadUrl(file.downloadUrl);
	        this.setRemoveUrl(file.removeUrl);
	      }
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.getFile() ? this.getFile().name : this.name;
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "setName",
	    value: function setName(name) {
	      if (main_core.Type.isStringFilled(name)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'name', name);
	      }
	    }
	  }, {
	    key: "getOriginalName",
	    value: function getOriginalName() {
	      return this.originalName ? this.originalName : this.getName();
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "setOriginalName",
	    value: function setOriginalName(name) {
	      if (main_core.Type.isStringFilled(name)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'originalName', name);
	      }
	    }
	  }, {
	    key: "getExtension",
	    value: function getExtension() {
	      var position = this.getName().lastIndexOf('.');
	      return position > 0 ? this.getName().substring(position + 1).toLowerCase() : '';
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.getFile() ? this.getFile().type : this.type;
	    }
	    /**
	     * internal
	     */

	  }, {
	    key: "setType",
	    value: function setType(type) {
	      if (main_core.Type.isStringFilled(type)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'type', type);
	      }
	    }
	  }, {
	    key: "getSize",
	    value: function getSize() {
	      return this.getFile() ? this.getFile().size : this.size;
	    }
	  }, {
	    key: "getSizeFormatted",
	    value: function getSizeFormatted() {
	      return formatFileSize(this.getSize());
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "setSize",
	    value: function setSize(size) {
	      if (main_core.Type.isNumber(size) && size >= 0) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'size', size);
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getServerId",
	    value: function getServerId() {
	      return this.serverId;
	    }
	  }, {
	    key: "setServerId",
	    value: function setServerId(id) {
	      if (main_core.Type.isNumber(id) || main_core.Type.isStringFilled(id)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'serverId', id);
	      }
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      return this.status;
	    }
	  }, {
	    key: "setStatus",
	    value: function setStatus(status) {
	      _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'status', status);

	      this.emit('onStatusChange');
	    }
	  }, {
	    key: "getOrigin",
	    value: function getOrigin() {
	      return this.origin;
	    }
	  }, {
	    key: "getDownloadUrl",
	    value: function getDownloadUrl() {
	      return this.downloadUrl;
	    }
	  }, {
	    key: "setDownloadUrl",
	    value: function setDownloadUrl(url) {
	      if (main_core.Type.isStringFilled(url)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'downloadUrl', url);
	      }
	    }
	  }, {
	    key: "getRemoveUrl",
	    value: function getRemoveUrl() {
	      return this.removeUrl;
	    }
	  }, {
	    key: "setRemoveUrl",
	    value: function setRemoveUrl(url) {
	      if (main_core.Type.isStringFilled(url)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'removeUrl', url);
	      }
	    }
	  }, {
	    key: "getWidth",
	    value: function getWidth() {
	      return this.width;
	    }
	  }, {
	    key: "setWidth",
	    value: function setWidth(width) {
	      if (main_core.Type.isNumber(width)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'width', width);
	      }
	    }
	  }, {
	    key: "getHeight",
	    value: function getHeight() {
	      return this.height;
	    }
	  }, {
	    key: "setHeight",
	    value: function setHeight(height) {
	      if (main_core.Type.isNumber(height)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'height', height);
	      }
	    }
	  }, {
	    key: "getPreviewUrl",
	    value: function getPreviewUrl() {
	      return this.getClientPreview() ? this.getClientPreviewUrl() : this.getServerPreviewUrl();
	    }
	  }, {
	    key: "getPreviewWidth",
	    value: function getPreviewWidth() {
	      return this.getClientPreview() ? this.getClientPreviewWidth() : this.getServerPreviewWidth();
	    }
	  }, {
	    key: "getPreviewHeight",
	    value: function getPreviewHeight() {
	      return this.getClientPreview() ? this.getClientPreviewHeight() : this.getServerPreviewHeight();
	    }
	  }, {
	    key: "getClientPreview",
	    value: function getClientPreview() {
	      return this.clientPreview;
	    }
	  }, {
	    key: "setClientPreview",
	    value: function setClientPreview(file) {
	      var width = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var height = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

	      if (main_core.Type.isFile(file) || main_core.Type.isNull(file)) {
	        this.revokeClientPreviewUrl();

	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'clientPreview', file);

	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'clientPreviewWidth', width);

	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'clientPreviewHeight', height);
	      }
	    }
	  }, {
	    key: "getClientPreviewUrl",
	    value: function getClientPreviewUrl() {
	      if (this.clientPreviewUrl === null && this.getClientPreview() !== null) {
	        this.clientPreviewUrl = URL.createObjectURL(this.getClientPreview());
	      }

	      return this.clientPreviewUrl;
	    }
	  }, {
	    key: "revokeClientPreviewUrl",
	    value: function revokeClientPreviewUrl() {
	      if (this.clientPreviewUrl !== null) {
	        URL.revokeObjectURL(this.clientPreviewUrl);
	      }

	      this.clientPreviewUrl = null;
	    }
	  }, {
	    key: "getClientPreviewWidth",
	    value: function getClientPreviewWidth() {
	      return this.clientPreviewWidth;
	    }
	  }, {
	    key: "getClientPreviewHeight",
	    value: function getClientPreviewHeight() {
	      return this.clientPreviewHeight;
	    }
	  }, {
	    key: "getServerPreviewUrl",
	    value: function getServerPreviewUrl() {
	      return this.serverPreviewUrl;
	    }
	  }, {
	    key: "setServerPreview",
	    value: function setServerPreview(url) {
	      var width = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var height = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

	      if (main_core.Type.isStringFilled(url) || main_core.Type.isNull(url)) {
	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'serverPreviewUrl', url);

	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'serverPreviewWidth', width);

	        _classPrivateMethodGet(this, _setProperty, _setProperty2).call(this, 'serverPreviewHeight', height);
	      }
	    }
	  }, {
	    key: "getServerPreviewWidth",
	    value: function getServerPreviewWidth() {
	      return this.serverPreviewWidth;
	    }
	  }, {
	    key: "getServerPreviewHeight",
	    value: function getServerPreviewHeight() {
	      return this.serverPreviewHeight;
	    }
	  }, {
	    key: "isImage",
	    value: function isImage() {
	      return isResizableImage(this.getOriginalName(), this.getType());
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return JSON.parse(JSON.stringify(this));
	    }
	  }, {
	    key: "fireStateChangeEvent",
	    value: function fireStateChangeEvent() {
	      this.emit('onStateChange');
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        id: this.getId(),
	        serverId: this.getServerId(),
	        status: this.getStatus(),
	        name: this.getName(),
	        originalName: this.getOriginalName(),
	        size: this.getSize(),
	        sizeFormatted: this.getSizeFormatted(),
	        type: this.getType(),
	        extension: this.getExtension(),
	        origin: this.getOrigin(),
	        isImage: this.isImage(),
	        width: this.getWidth(),
	        height: this.getHeight(),
	        previewUrl: this.getPreviewUrl(),
	        previewWidth: this.getPreviewWidth(),
	        previewHeight: this.getPreviewHeight(),
	        clientPreviewUrl: this.getClientPreviewUrl(),
	        clientPreviewWidth: this.getClientPreviewWidth(),
	        clientPreviewHeight: this.getClientPreviewHeight(),
	        serverPreviewUrl: this.getServerPreviewUrl(),
	        serverPreviewWidth: this.getServerPreviewWidth(),
	        serverPreviewHeight: this.getServerPreviewHeight(),
	        downloadUrl: this.getDownloadUrl(),
	        removeUrl: this.getRemoveUrl()
	      };
	    }
	  }]);
	  return UploaderFile;
	}(main_core_events.EventEmitter);

	var _setProperty2 = function _setProperty2(name, value) {
	  this[name] = value;
	  this.fireStateChangeEvent();
	};

	var UploaderError = /*#__PURE__*/function (_BaseError) {
	  babelHelpers.inherits(UploaderError, _BaseError);

	  function UploaderError(code) {
	    var _ref, _ref2;

	    var _this;

	    babelHelpers.classCallCheck(this, UploaderError);
	    var message = main_core.Type.isString(arguments.length <= 1 ? undefined : arguments[1]) ? arguments.length <= 1 ? undefined : arguments[1] : null;
	    var description = main_core.Type.isString(arguments.length <= 2 ? undefined : arguments[2]) ? arguments.length <= 2 ? undefined : arguments[2] : null;
	    var customData = main_core.Type.isPlainObject((_ref = (arguments.length <= 1 ? 0 : arguments.length - 1) - 1 + 1, _ref < 1 || arguments.length <= _ref ? undefined : arguments[_ref])) ? (_ref2 = (arguments.length <= 1 ? 0 : arguments.length - 1) - 1 + 1, _ref2 < 1 || arguments.length <= _ref2 ? undefined : arguments[_ref2]) : {};
	    var replacements = {};
	    Object.keys(customData).forEach(function (key) {
	      replacements["#".concat(key, "#")] = customData[key];
	    });

	    if (!main_core.Type.isString(message) && main_core.Loc.hasMessage("UPLOADER_".concat(code))) {
	      message = main_core.Loc.getMessage("UPLOADER_".concat(code), replacements);
	    }

	    if (main_core.Type.isStringFilled(message) && !main_core.Type.isString(description) && main_core.Loc.hasMessage("UPLOADER_".concat(code, "_DESC"))) {
	      description = main_core.Loc.getMessage("UPLOADER_".concat(code, "_DESC"), replacements);
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UploaderError).call(this, message, code, customData));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "description", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "origin", 'client');

	    _this.setDescription(description);

	    return _this;
	  }

	  babelHelpers.createClass(UploaderError, [{
	    key: "getDescription",
	    value: function getDescription() {
	      return this.description;
	    }
	  }, {
	    key: "setDescription",
	    value: function setDescription(text) {
	      if (main_core.Type.isString(text)) {
	        this.description = text;
	      }

	      return this;
	    }
	  }, {
	    key: "getOrigin",
	    value: function getOrigin() {
	      return this.origin;
	    }
	  }, {
	    key: "setOrigin",
	    value: function setOrigin(origin) {
	      if (main_core.Type.isStringFilled(origin)) {
	        this.origin = origin;
	      }

	      return this;
	    }
	  }, {
	    key: "clone",
	    value: function clone() {
	      var options = JSON.parse(JSON.stringify(this));
	      var error = new UploaderError(options.code, options.message, options.description, options.customData);
	      error.setOrigin(options.origin);
	      return error;
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        code: this.getCode(),
	        message: this.getMessage(),
	        description: this.getDescription(),
	        origin: this.getOrigin(),
	        customData: this.getCustomData()
	      };
	    }
	  }], [{
	    key: "createFromAjaxErrors",
	    value: function createFromAjaxErrors(errors) {
	      if (!main_core.Type.isArrayFilled(errors) || !main_core.Type.isPlainObject(errors[0])) {
	        return new this('SERVER_ERROR');
	      }

	      var uploaderError = errors.find(function (error) {
	        return error.type === 'file-uploader';
	      });

	      if (uploaderError && !uploaderError.system) {
	        var code = uploaderError.code,
	            message = uploaderError.message,
	            description = uploaderError.description,
	            customData = uploaderError.customData;
	        var error = new this(code, message, description, customData);
	        error.setOrigin('server');
	        return error;
	      } else {
	        var _errors$ = errors[0],
	            _code = _errors$.code,
	            _message = _errors$.message;

	        if (_code === 'NETWORK_ERROR') {
	          _message = main_core.Loc.getMessage('UPLOADER_SERVER_ERROR');
	        } else {
	          _code = 'SERVER_ERROR';
	          _message = null;
	        }

	        console.error('FileUploader', errors);

	        var _error = new this(_code, _message);

	        _error.setOrigin('server');

	        return _error;
	      }
	    }
	  }]);
	  return UploaderError;
	}(main_core.BaseError);

	var Chunk = /*#__PURE__*/function () {
	  function Chunk(data, offset) {
	    babelHelpers.classCallCheck(this, Chunk);
	    babelHelpers.defineProperty(this, "data", null);
	    babelHelpers.defineProperty(this, "offset", 0);
	    babelHelpers.defineProperty(this, "retries", []);
	    this.data = data;
	    this.offset = offset;
	  }

	  babelHelpers.createClass(Chunk, [{
	    key: "getNextRetryDelay",
	    value: function getNextRetryDelay() {
	      if (this.retries.length === 0) {
	        return null;
	      }

	      return this.retries.shift();
	    }
	  }, {
	    key: "setRetries",
	    value: function setRetries(retries) {
	      if (main_core.Type.isArray(retries)) {
	        this.retries = retries;
	      }
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	  }, {
	    key: "getOffset",
	    value: function getOffset() {
	      return this.offset;
	    }
	  }, {
	    key: "getSize",
	    value: function getSize() {
	      return this.getData().size;
	    }
	  }]);
	  return Chunk;
	}();

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _uploadChunk = new WeakSet();

	var _retryUploadChunk = new WeakSet();

	var _getNextChunk = new WeakSet();

	var UploadController = /*#__PURE__*/function (_AbstractUploadContro) {
	  babelHelpers.inherits(UploadController, _AbstractUploadContro);

	  function UploadController(server) {
	    var _this;

	    babelHelpers.classCallCheck(this, UploadController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UploadController).call(this, server));

	    _getNextChunk.add(babelHelpers.assertThisInitialized(_this));

	    _retryUploadChunk.add(babelHelpers.assertThisInitialized(_this));

	    _uploadChunk.add(babelHelpers.assertThisInitialized(_this));

	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "file", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "chunkOffset", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "chunkTimeout", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "token", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "xhr", null);
	    return _this;
	  }

	  babelHelpers.createClass(UploadController, [{
	    key: "upload",
	    value: function upload(file) {
	      if (this.chunkOffset !== null) {
	        return;
	      }

	      this.file = file;

	      var nextChunk = _classPrivateMethodGet$1(this, _getNextChunk, _getNextChunk2).call(this);

	      if (nextChunk) {
	        _classPrivateMethodGet$1(this, _uploadChunk, _uploadChunk2).call(this, nextChunk);
	      }
	    }
	  }, {
	    key: "abort",
	    value: function abort() {
	      if (this.xhr) {
	        this.xhr.abort();
	        this.xhr = null;
	      }

	      clearTimeout(this.chunkTimeout);
	    }
	  }, {
	    key: "getFile",
	    value: function getFile() {
	      return this.file;
	    }
	  }, {
	    key: "getChunkSize",
	    value: function getChunkSize() {
	      return this.getServer().getChunkSize();
	    }
	  }, {
	    key: "getChunkOffset",
	    value: function getChunkOffset() {
	      return this.chunkOffset;
	    }
	  }, {
	    key: "getToken",
	    value: function getToken() {
	      return this.token;
	    }
	  }, {
	    key: "setToken",
	    value: function setToken(token) {
	      if (main_core.Type.isStringFilled(token)) {
	        this.token = token;
	      }
	    }
	  }]);
	  return UploadController;
	}(AbstractUploadController);

	var _uploadChunk2 = function _uploadChunk2(chunk) {
	  var _this2 = this;

	  var totalSize = this.getFile().size;
	  var isOnlyOneChunk = chunk.getOffset() === 0 && totalSize === chunk.getSize();
	  var fileName = this.getFile().name;

	  if (fileName.normalize) {
	    fileName = fileName.normalize();
	  }

	  var headers = [{
	    name: 'Content-Type',
	    value: this.getFile().type
	  }, {
	    name: 'X-Upload-Content-Name',
	    value: encodeURIComponent(fileName)
	  }];

	  if (!isOnlyOneChunk) {
	    var rangeStart = chunk.getOffset();
	    var rangeEnd = chunk.getOffset() + chunk.getSize() - 1;
	    var rangeHeader = "bytes ".concat(rangeStart, "-").concat(rangeEnd, "/").concat(totalSize);
	    headers.push({
	      name: 'Content-Range',
	      value: rangeHeader
	    });
	  }

	  var controllerOptions = this.getServer().getControllerOptions();
	  main_core.ajax.runAction('ui.fileuploader.upload', {
	    headers: headers,
	    data: chunk.getData(),
	    preparePost: false,
	    getParameters: {
	      controller: this.getServer().getController(),
	      controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null,
	      token: this.getToken() || ''
	    },
	    onrequeststart: function onrequeststart(xhr) {
	      _this2.xhr = xhr;
	    },
	    onprogressupload: function onprogressupload(event) {
	      if (event.lengthComputable) {
	        var size = _this2.getFile().size;

	        var uploadedBytes = Math.min(size, chunk.getOffset() + event.loaded);
	        var progress = size > 0 ? Math.floor(uploadedBytes / size * 100) : 100;

	        _this2.emit('onProgress', {
	          progress: progress
	        });
	      }
	    }
	  }).then(function (response) {
	    console.log('response', response);

	    if (response.data.token) {
	      _this2.setToken(response.data.token);

	      var size = _this2.getFile().size;

	      var progress = size > 0 ? Math.floor((chunk.getOffset() + chunk.getSize()) / size * 100) : 100;

	      _this2.emit('onProgress', {
	        progress: progress
	      });

	      var nextChunk = _classPrivateMethodGet$1(_this2, _getNextChunk, _getNextChunk2).call(_this2);

	      if (nextChunk) {
	        _classPrivateMethodGet$1(_this2, _uploadChunk, _uploadChunk2).call(_this2, nextChunk);
	      } else {
	        _this2.emit('onProgress', {
	          progress: 100
	        });

	        _this2.emit('onUpload', {
	          fileInfo: response.data.file
	        });
	      }
	    } else {
	      _this2.emit('onError', {
	        error: new UploaderError('SERVER_ERROR')
	      });
	    }
	  }).catch(function (response) {
	    console.log('error', response);
	    var error = UploaderError.createFromAjaxErrors(response.errors);
	    var shouldRetry = error.getCode() === 'NETWORK_ERROR';

	    if (!shouldRetry || !_classPrivateMethodGet$1(_this2, _retryUploadChunk, _retryUploadChunk2).call(_this2, chunk)) {
	      _this2.emit('onError', {
	        error: error
	      });
	    }
	  });
	};

	var _retryUploadChunk2 = function _retryUploadChunk2(chunk) {
	  var _this3 = this;

	  var nextDelay = chunk.getNextRetryDelay();

	  if (nextDelay === null) {
	    return false;
	  }

	  clearTimeout(this.chunkTimeout);
	  this.chunkTimeout = setTimeout(function () {
	    _classPrivateMethodGet$1(_this3, _uploadChunk, _uploadChunk2).call(_this3, chunk);
	  }, nextDelay);
	  return true;
	};

	var _getNextChunk2 = function _getNextChunk2() {
	  if (this.getChunkOffset() !== null && this.getChunkOffset() >= this.getFile().size) {
	    // End of File
	    return null;
	  }

	  if (this.getChunkOffset() === null) {
	    // First call
	    this.chunkOffset = 0;
	  }

	  var chunk;

	  if (this.getChunkOffset() === 0 && this.getFile().size <= this.getChunkSize()) {
	    chunk = new Chunk(this.getFile(), this.getChunkOffset());
	    this.chunkOffset = this.getFile().size;
	  } else {
	    var currentChunkSize = Math.min(this.getChunkSize(), this.getFile().size - this.getChunkOffset());
	    var nextOffset = this.getChunkOffset() + currentChunkSize;
	    var fileRange = this.getFile().slice(this.getChunkOffset(), nextOffset);
	    chunk = new Chunk(fileRange, this.getChunkOffset());
	    this.chunkOffset = nextOffset;
	  }

	  chunk.setRetries(babelHelpers.toConsumableArray(this.getServer().getChunkRetryDelays()));
	  return chunk;
	};

	var queues = new WeakMap();
	function loadMultiple(controller, file) {
	  var server = controller.getServer();
	  var queue = queues.get(server);

	  if (!queue) {
	    queue = {
	      tasks: [],
	      load: main_core.Runtime.debounce(loadInternal, 100, server),
	      xhr: null
	    };
	    queues.set(server, queue);
	  }

	  queue.tasks.push({
	    controller: controller,
	    file: file
	  });
	  queue.load();
	}
	function abort(controller) {
	  var server = controller.getServer();
	  var queue = queues.get(server);

	  if (queue) {
	    queue.xhr.abort();
	    queue.xhr = null;
	    queues.delete(server);
	  }
	}

	function loadInternal() {
	  var server = this;
	  var queue = queues.get(server);

	  if (!queue) {
	    return;
	  }

	  var tasks = queue.tasks;
	  queues.delete(server);
	  var fileIds = [];
	  tasks.forEach(function (task) {
	    var controller = task.controller,
	        file = task.file;
	    fileIds.push(file.getServerId());
	  });
	  var controllerOptions = server.getControllerOptions();
	  main_core.ajax.runAction('ui.fileuploader.load', {
	    data: {
	      fileIds: fileIds
	    },
	    getParameters: {
	      controller: server.getController(),
	      controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null
	    },
	    onrequeststart: function onrequeststart(xhr) {
	      queue.xhr = xhr;
	    },
	    onprogress: function onprogress(event) {
	      if (event.lengthComputable) {
	        var progress = event.total > 0 ? Math.floor(event.loaded / event.total * 100) : 100;
	        tasks.forEach(function (task) {
	          var controller = task.controller,
	              file = task.file;
	          controller.emit('onProgress', {
	            file: file,
	            progress: progress
	          });
	        });
	      }
	    }
	  }).then(function (response) {
	    var _response$data;

	    if ((_response$data = response.data) !== null && _response$data !== void 0 && _response$data.files) {
	      var fileResults = {};
	      response.data.files.forEach(function (fileResult) {
	        fileResults[fileResult.id] = fileResult;
	      });
	      tasks.forEach(function (task) {
	        var controller = task.controller,
	            file = task.file;
	        var fileResult = fileResults[file.getServerId()] || null;

	        if (fileResult && fileResult.success) {
	          controller.emit('onProgress', {
	            file: file,
	            progress: 100
	          });
	          controller.emit('onLoad', {
	            fileInfo: fileResult.data.file
	          });
	        } else {
	          var error = UploaderError.createFromAjaxErrors(fileResult === null || fileResult === void 0 ? void 0 : fileResult.errors);
	          controller.emit('onError', {
	            error: error
	          });
	        }
	      });
	    } else {
	      var error = new UploaderError('SERVER_ERROR');
	      tasks.forEach(function (task) {
	        var controller = task.controller;
	        controller.emit('onError', {
	          error: error.clone()
	        });
	      });
	    }
	  }).catch(function (response) {
	    var error = UploaderError.createFromAjaxErrors(response.errors);
	    tasks.forEach(function (task) {
	      var controller = task.controller;
	      controller.emit('onError', {
	        error: error.clone()
	      });
	    });
	  });
	}

	var ServerLoadController = /*#__PURE__*/function (_AbstractLoadControll) {
	  babelHelpers.inherits(ServerLoadController, _AbstractLoadControll);

	  function ServerLoadController(server) {
	    babelHelpers.classCallCheck(this, ServerLoadController);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ServerLoadController).call(this, server));
	  }

	  babelHelpers.createClass(ServerLoadController, [{
	    key: "load",
	    value: function load(file) {
	      if (this.getServer().getController()) {
	        loadMultiple(this, file);
	      } else {
	        this.emit('onProgress', {
	          file: file,
	          progress: 100
	        });
	        this.emit('onLoad', {
	          fileInfo: file
	        });
	      } // const controllerOptions = this.getServer().getControllerOptions();
	      // Ajax.runAction('ui.fileuploader.load', {
	      // 		data: {
	      // 			fileIds: [file.getServerId()],
	      // 		},
	      // 		getParameters: {
	      // 			controller: this.getServer().getController(),
	      // 			controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null,
	      // 		},
	      // 		onrequeststart: (xhr) => {
	      // 			this.xhr = xhr;
	      // 		},
	      // 		onprogress: (event: ProgressEvent) => {
	      // 			if (event.lengthComputable)
	      // 			{
	      // 				const progress = event.total > 0 ? Math.floor(event.loaded / event.total * 100): 100;
	      // 				this.emit('onProgress', { progress });
	      // 			}
	      // 		}
	      // 	})
	      // 	.then(response => {
	      // 		if (response.data?.files)
	      // 		{
	      // 			this.emit('onProgress', { file, progress: 100 });
	      // 			this.emit('onLoad', { file: response.data.file })
	      // 		}
	      // 		else
	      // 		{
	      // 			this.emit('onError', { error: new UploaderError('SERVER_ERROR') });
	      // 		}
	      // 	})
	      // 	.catch(response => {
	      // 		this.emit('onError', { error: UploaderError.createFromAjaxErrors(response.errors) });
	      // 	})
	      // ;

	    }
	  }, {
	    key: "abort",
	    value: function abort$$1() {
	      if (this.getServer().getController()) {
	        abort(this);
	      }
	    }
	  }]);
	  return ServerLoadController;
	}(AbstractLoadController);

	var ClientLoadController = /*#__PURE__*/function (_AbstractLoadControll) {
	  babelHelpers.inherits(ClientLoadController, _AbstractLoadControll);

	  function ClientLoadController(server) {
	    babelHelpers.classCallCheck(this, ClientLoadController);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ClientLoadController).call(this, server));
	  }

	  babelHelpers.createClass(ClientLoadController, [{
	    key: "load",
	    value: function load(file) {
	      if (main_core.Type.isFile(file.getFile())) {
	        this.emit('onProgress', {
	          file: file,
	          progress: 100
	        });
	        this.emit('onLoad', {
	          fileInfo: file
	        });
	      } else {
	        this.emit('onError', {
	          error: new UploaderError('WRONG_FILE_SOURCE')
	        });
	      }
	    }
	  }, {
	    key: "abort",
	    value: function abort() {}
	  }]);
	  return ClientLoadController;
	}(AbstractLoadController);

	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _calcChunkSize = new WeakSet();

	var Server = /*#__PURE__*/function () {
	  function Server(serverOptions) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Server);

	    _calcChunkSize.add(this);

	    babelHelpers.defineProperty(this, "controller", null);
	    babelHelpers.defineProperty(this, "controllerOptions", null);
	    babelHelpers.defineProperty(this, "uploadControllerClass", null);
	    babelHelpers.defineProperty(this, "loadControllerClass", null);
	    babelHelpers.defineProperty(this, "chunkSize", null);
	    babelHelpers.defineProperty(this, "defaultChunkSize", null);
	    babelHelpers.defineProperty(this, "chunkMinSize", null);
	    babelHelpers.defineProperty(this, "chunkMaxSize", null);
	    babelHelpers.defineProperty(this, "chunkRetryDelays", [500, 1000, 3000]);
	    var options = main_core.Type.isPlainObject(serverOptions) ? serverOptions : {};
	    this.controller = main_core.Type.isStringFilled(options.controller) ? options.controller : null;
	    this.controllerOptions = main_core.Type.isPlainObject(options.controllerOptions) ? options.controllerOptions : null;

	    var _chunkSize = main_core.Type.isNumber(options.chunkSize) && options.chunkSize > 0 ? options.chunkSize : this.getDefaultChunkSize();

	    this.chunkSize = options.forceChunkSize === true ? _chunkSize : _classPrivateMethodGet$2(this, _calcChunkSize, _calcChunkSize2).call(this, _chunkSize);

	    if (options.chunkRetryDelays === false || options.chunkRetryDelays === null) {
	      this.chunkRetryDelays = [];
	    } else if (main_core.Type.isArray(options.chunkRetryDelays)) {
	      this.chunkRetryDelays = options.chunkRetryDelays;
	    }

	    ['uploadControllerClass', 'loadControllerClass'].forEach(function (controllerClass) {
	      if (main_core.Type.isStringFilled(options[controllerClass])) {
	        _this[controllerClass] = main_core.Runtime.getClass(options[controllerClass]);

	        if (!main_core.Type.isFunction(options[controllerClass])) {
	          throw new Error("FileUploader.Server: \"".concat(controllerClass, "\" must be a function."));
	        }
	      } else if (main_core.Type.isFunction(options[controllerClass])) {
	        _this[controllerClass] = options[controllerClass];
	      }
	    });
	  }

	  babelHelpers.createClass(Server, [{
	    key: "createUploadController",
	    value: function createUploadController() {
	      if (this.uploadControllerClass) {
	        var controller = new this.uploadControllerClass(this);

	        if (!(controller instanceof AbstractUploadController)) {
	          throw new Error('FileUploader.Server: "uploadControllerClass" must be an instance of AbstractUploadController.');
	        }

	        return controller;
	      } else if (main_core.Type.isStringFilled(this.controller)) {
	        return new UploadController(this);
	      }

	      return null;
	    }
	  }, {
	    key: "createLoadController",
	    value: function createLoadController() {
	      if (this.loadControllerClass) {
	        var controller = new this.loadControllerClass(this);

	        if (!(controller instanceof AbstractLoadController)) {
	          throw new Error('FileUploader.Server: "loadControllerClass" must be an instance of AbstractLoadController.');
	        }

	        return controller;
	      }

	      return new ServerLoadController(this);
	    }
	  }, {
	    key: "createClientLoadController",
	    value: function createClientLoadController() {
	      return new ClientLoadController(this);
	    }
	  }, {
	    key: "getController",
	    value: function getController() {
	      return this.controller;
	    }
	  }, {
	    key: "getControllerOptions",
	    value: function getControllerOptions() {
	      return this.controllerOptions;
	    }
	  }, {
	    key: "getChunkSize",
	    value: function getChunkSize() {
	      return this.chunkSize;
	    }
	  }, {
	    key: "getDefaultChunkSize",
	    value: function getDefaultChunkSize() {
	      if (this.defaultChunkSize === null) {
	        var settings = main_core.Extension.getSettings('ui.file-uploader');
	        this.defaultChunkSize = settings.get('defaultChunkSize', 5 * 1024 * 1024);
	      }

	      return this.defaultChunkSize;
	    }
	  }, {
	    key: "getChunkMinSize",
	    value: function getChunkMinSize() {
	      if (this.chunkMinSize === null) {
	        var settings = main_core.Extension.getSettings('ui.file-uploader');
	        this.chunkMinSize = settings.get('chunkMinSize', 1024 * 1024);
	      }

	      return this.chunkMinSize;
	    }
	  }, {
	    key: "getChunkMaxSize",
	    value: function getChunkMaxSize() {
	      if (this.chunkMaxSize === null) {
	        var settings = main_core.Extension.getSettings('ui.file-uploader');
	        this.chunkMaxSize = settings.get('chunkMaxSize', 5 * 1024 * 1024);
	      }

	      return this.chunkMaxSize;
	    }
	  }, {
	    key: "getChunkRetryDelays",
	    value: function getChunkRetryDelays() {
	      return this.chunkRetryDelays;
	    }
	  }]);
	  return Server;
	}();

	var _calcChunkSize2 = function _calcChunkSize2(chunkSize) {
	  return Math.min(Math.max(this.getChunkMinSize(), chunkSize), this.getChunkMaxSize());
	};

	var Filter = /*#__PURE__*/function () {
	  function Filter(uploader) {
	    babelHelpers.classCallCheck(this, Filter);
	    babelHelpers.defineProperty(this, "uploader", null);
	    this.uploader = uploader;
	  }

	  babelHelpers.createClass(Filter, [{
	    key: "getUploader",
	    value: function getUploader() {
	      return this.uploader;
	    }
	    /**
	     * @abstract
	     */

	  }, {
	    key: "apply",
	    value: function apply() {
	      throw new Error('You must implement apply() method.');
	    }
	  }]);
	  return Filter;
	}();

	var FileSizeFilter = /*#__PURE__*/function (_Filter) {
	  babelHelpers.inherits(FileSizeFilter, _Filter);

	  function FileSizeFilter(uploader) {
	    var _this;

	    var filterOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, FileSizeFilter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FileSizeFilter).call(this, uploader));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxFileSize", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "minFileSize", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxTotalFileSize", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imageMaxFileSize", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imageMinFileSize", null);
	    var options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    var integerOptions = ['maxFileSize', 'minFileSize', 'maxTotalFileSize', 'imageMaxFileSize', 'imageMinFileSize'];
	    integerOptions.forEach(function (option) {
	      _this[option] = main_core.Type.isNumber(options[option]) && options[option] >= 0 ? options[option] : _this[option];
	    });
	    return _this;
	  }

	  babelHelpers.createClass(FileSizeFilter, [{
	    key: "apply",
	    value: function apply(file) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this2.maxFileSize !== null && file.getSize() > _this2.maxFileSize) {
	          reject(new UploaderError('MAX_FILE_SIZE_EXCEEDED', {
	            maxFileSize: formatFileSize(_this2.maxFileSize),
	            maxFileSizeInBytes: _this2.maxFileSize
	          }));
	          return;
	        }

	        if (_this2.minFileSize !== null && file.getSize() < _this2.minFileSize) {
	          reject(new UploaderError('MIN_FILE_SIZE_EXCEEDED', {
	            minFileSize: formatFileSize(_this2.minFileSize),
	            minFileSizeInBytes: _this2.minFileSize
	          }));
	          return;
	        }

	        if (file.isImage()) {
	          if (_this2.imageMaxFileSize !== null && file.getSize() > _this2.imageMaxFileSize) {
	            reject(new UploaderError('IMAGE_MAX_FILE_SIZE_EXCEEDED', {
	              imageMaxFileSize: formatFileSize(_this2.imageMaxFileSize),
	              imageMaxFileSizeInBytes: _this2.imageMaxFileSize
	            }));
	            return;
	          }

	          if (_this2.imageMinFileSize !== null && file.getSize() < _this2.imageMinFileSize) {
	            reject(new UploaderError('IMAGE_MIN_FILE_SIZE_EXCEEDED', {
	              imageMinFileSize: formatFileSize(_this2.imageMinFileSize),
	              imageMinFileSizeInBytes: _this2.imageMinFileSize
	            }));
	            return;
	          }
	        }

	        if (_this2.maxTotalFileSize !== null) {
	          if (_this2.getUploader().getTotalSize() > _this2.maxTotalFileSize) {
	            reject(new UploaderError('MAX_TOTAL_FILE_SIZE_EXCEEDED', {
	              maxTotalFileSize: formatFileSize(_this2.maxTotalFileSize),
	              maxTotalFileSizeInBytes: _this2.maxTotalFileSize
	            }));
	            return;
	          }
	        }

	        resolve();
	      });
	    }
	  }]);
	  return FileSizeFilter;
	}(Filter);

	var isValidFileType = function isValidFileType(file, fileTypes) {
	  if (!main_core.Type.isArrayFilled(fileTypes)) {
	    return true;
	  }

	  var mimeType = file.type;
	  var baseMimeType = mimeType.replace(/\/.*$/, '');

	  for (var i = 0; i < fileTypes.length; i++) {
	    if (!main_core.Type.isStringFilled(fileTypes[i])) {
	      continue;
	    }

	    var type = fileTypes[i].trim().toLowerCase();

	    if (type.charAt(0) === '.') // extension case
	      {
	        if (file.name.toLowerCase().indexOf(type, file.name.length - type.length) !== -1) {
	          return true;
	        }
	      } else if (/\/\*$/.test(type)) // image/* mime type case
	      {
	        if (baseMimeType === type.replace(/\/.*$/, '')) {
	          return true;
	        }
	      } else if (mimeType === type) {
	      return true;
	    }
	  }

	  return false;
	};

	var FileTypeFilter = /*#__PURE__*/function (_Filter) {
	  babelHelpers.inherits(FileTypeFilter, _Filter);

	  function FileTypeFilter(uploader) {
	    babelHelpers.classCallCheck(this, FileTypeFilter);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FileTypeFilter).call(this, uploader));
	  }

	  babelHelpers.createClass(FileTypeFilter, [{
	    key: "apply",
	    value: function apply(file) {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        if (isValidFileType(file.getFile(), _this.getUploader().getAcceptedFileTypes())) {
	          resolve();
	        } else {
	          reject(new UploaderError('FILE_TYPE_NOT_ALLOWED'));
	        }
	      });
	    }
	  }]);
	  return FileTypeFilter;
	}(Filter);

	var getArrayBuffer = function getArrayBuffer(file) {
	  return new Promise(function (resolve, reject) {
	    var fileReader = new FileReader();
	    fileReader.readAsArrayBuffer(file);

	    fileReader.onload = function () {
	      var buffer = fileReader.result;
	      resolve(buffer);
	    };

	    fileReader.onerror = function () {
	      reject(fileReader.error);
	    };
	  });
	};

	var convertStringToBuffer = function convertStringToBuffer(str) {
	  var result = [];

	  for (var i = 0; i < str.length; i++) {
	    result.push(str.charCodeAt(i) & 0xFF);
	  }

	  return result;
	};

	var compareBuffers = function compareBuffers(dataView, dest, start) {
	  for (var i = start, j = 0; j < dest.length;) {
	    if (dataView.getUint8(i++) !== dest[j++]) {
	      return false;
	    }
	  }

	  return true;
	};

	var GIF87a = convertStringToBuffer('GIF87a');
	var GIF89a = convertStringToBuffer('GIF89a');

	var Gif = /*#__PURE__*/function () {
	  function Gif() {
	    babelHelpers.classCallCheck(this, Gif);
	  }

	  babelHelpers.createClass(Gif, [{
	    key: "getSize",
	    value: function getSize(file) {
	      return new Promise(function (resolve, reject) {
	        if (file.size < 10) {
	          return resolve(null);
	        }

	        var blob = file.slice(0, 10);
	        getArrayBuffer(blob).then(function (buffer) {
	          var view = new DataView(buffer);

	          if (!compareBuffers(view, GIF87a, 0) && !compareBuffers(view, GIF89a, 0)) {
	            return resolve(null);
	          }

	          resolve({
	            width: view.getUint16(6, true),
	            height: view.getUint16(8, true)
	          });
	        }).catch(function () {
	          resolve(null);
	        });
	      });
	    }
	  }]);
	  return Gif;
	}();

	var PNG_SIGNATURE = convertStringToBuffer('\x89PNG\r\n\x1a\n');
	var IHDR_SIGNATURE = convertStringToBuffer('IHDR');
	var FRIED_CHUNK_NAME = convertStringToBuffer('CgBI');

	var Png = /*#__PURE__*/function () {
	  function Png() {
	    babelHelpers.classCallCheck(this, Png);
	  }

	  babelHelpers.createClass(Png, [{
	    key: "getSize",
	    value: function getSize(file) {
	      return new Promise(function (resolve, reject) {
	        if (file.size < 40) {
	          return resolve(null);
	        }

	        var blob = file.slice(0, 40);
	        getArrayBuffer(blob).then(function (buffer) {
	          var view = new DataView(buffer);

	          if (!compareBuffers(view, PNG_SIGNATURE, 0)) {
	            return resolve(null);
	          }

	          if (compareBuffers(view, FRIED_CHUNK_NAME, 12)) {
	            if (compareBuffers(view, IHDR_SIGNATURE, 28)) {
	              resolve({
	                width: view.getUint32(32),
	                height: view.getUint32(36)
	              });
	            } else {
	              resolve(null);
	            }
	          } else if (compareBuffers(view, IHDR_SIGNATURE, 12)) {
	            resolve({
	              width: view.getUint32(16),
	              height: view.getUint32(20)
	            });
	          } else {
	            resolve(null);
	          }
	        }).catch(function () {
	          resolve(null);
	        });
	      });
	    }
	  }]);
	  return Png;
	}();

	var BMP_SIGNATURE = 0x424d; // BM

	var Bmp = /*#__PURE__*/function () {
	  function Bmp() {
	    babelHelpers.classCallCheck(this, Bmp);
	  }

	  babelHelpers.createClass(Bmp, [{
	    key: "getSize",
	    value: function getSize(file) {
	      return new Promise(function (resolve, reject) {
	        if (file.size < 26) {
	          return resolve(null);
	        }

	        var blob = file.slice(0, 26);
	        getArrayBuffer(blob).then(function (buffer) {
	          var view = new DataView(buffer);

	          if (!view.getUint16(0) === BMP_SIGNATURE) {
	            return resolve(null);
	          }

	          resolve({
	            width: view.getUint32(18, true),
	            height: Math.abs(view.getInt32(22, true))
	          });
	        }).catch(function () {
	          resolve(null);
	        });
	      });
	    }
	  }]);
	  return Bmp;
	}();

	var EXIF_SIGNATURE = convertStringToBuffer('Exif\0\0');

	var Jpeg = /*#__PURE__*/function () {
	  function Jpeg() {
	    babelHelpers.classCallCheck(this, Jpeg);
	  }

	  babelHelpers.createClass(Jpeg, [{
	    key: "getSize",
	    value: function getSize(file) {
	      return new Promise(function (resolve, reject) {
	        if (file.size < 2) {
	          return resolve(null);
	        }

	        getArrayBuffer(file).then(function (buffer) {
	          var view = new DataView(buffer);

	          if (view.getUint8(0) !== 0xFF || view.getUint8(1) !== 0xD8) {
	            resolve(null);
	          }

	          var offset = 2;
	          var orientation = -1;

	          for (;;) {
	            if (view.byteLength - offset < 2) {
	              return resolve(null);
	            }

	            if (view.getUint8(offset++) !== 0xFF) {
	              return resolve(null);
	            }

	            var code = view.getUint8(offset++);
	            var length = void 0; // skip padding bytes

	            while (code === 0xFF) {
	              code = view.getUint8(offset++);
	            }

	            if (0xD0 <= code && code <= 0xD9 || code === 0x01) {
	              length = 0;
	            } else if (0xC0 <= code && code <= 0xFE) {
	              // the rest of the unreserved markers
	              if (view.byteLength - offset < 2) {
	                return resolve(null);
	              }

	              length = view.getUint16(offset) - 2;
	              offset += 2;
	            } else {
	              // unknown markers
	              return resolve(null);
	            }

	            if (code === 0xD9
	            /* EOI */
	            || code === 0xDA
	            /* SOS */
	            ) {
	                // end of the datastream
	                return resolve(null);
	              } // try to get orientation from Exif segment


	            if (code === 0xE1 && length >= 10 && compareBuffers(view, EXIF_SIGNATURE, offset)) {
	              var exifBlock = new DataView(view.buffer, offset + 6, offset + length);
	              orientation = getOrientation(exifBlock);
	            }

	            if (length >= 5 && 0xC0 <= code && code <= 0xCF && code !== 0xC4 && code !== 0xC8 && code !== 0xCC) {
	              if (view.byteLength - offset < length) {
	                return resolve(null);
	              }

	              var width = view.getUint16(offset + 3);
	              var height = view.getUint16(offset + 1);

	              if (orientation >= 5 && orientation <= 8) {
	                var _ref = [height, width];
	                width = _ref[0];
	                height = _ref[1];
	              }

	              return resolve({
	                width: width,
	                height: height,
	                orientation: orientation
	              });
	            }

	            offset += length;
	          }
	        }).catch(function () {
	          resolve(null);
	        });
	      });
	    }
	  }]);
	  return Jpeg;
	}();
	var Marker = {
	  BIG_ENDIAN: 0x4d4d,
	  LITTLE_ENDIAN: 0x4949
	};

	var getOrientation = function getOrientation(exifBlock) {
	  var byteAlign = exifBlock.getUint16(0);
	  var isBigEndian = byteAlign === Marker.BIG_ENDIAN;
	  var isLittleEndian = byteAlign === Marker.LITTLE_ENDIAN;

	  if (isBigEndian || isLittleEndian) {
	    return extractOrientation(exifBlock, isLittleEndian);
	  }

	  return -1;
	};

	var extractOrientation = function extractOrientation(exifBlock) {
	  var littleEndian = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	  var offset = 8; // idf offset

	  var idfDirectoryEntries = exifBlock.getUint16(offset, littleEndian);
	  var IDF_ENTRY_BYTES = 12;
	  var NUM_DIRECTORY_ENTRIES_BYTES = 2;

	  for (var directoryEntryNumber = 0; directoryEntryNumber < idfDirectoryEntries; directoryEntryNumber++) {
	    var start = offset + NUM_DIRECTORY_ENTRIES_BYTES + directoryEntryNumber * IDF_ENTRY_BYTES;
	    var end = start + IDF_ENTRY_BYTES; // Skip on corrupt EXIF blocks

	    if (start > exifBlock.byteLength) {
	      return -1;
	    }

	    var block = new DataView(exifBlock.buffer, exifBlock.byteOffset + start, end - start);
	    var tagNumber = block.getUint16(0, littleEndian); // 274 is the `orientation` tag ID

	    if (tagNumber === 274) {
	      var dataFormat = block.getUint16(2, littleEndian);

	      if (dataFormat !== 3) {
	        return -1;
	      }

	      var numberOfComponents = block.getUint32(4, littleEndian);

	      if (numberOfComponents !== 1) {
	        return -1;
	      }

	      return block.getUint16(8, littleEndian);
	    }
	  }
	};

	var RIFF_HEADER = 0x52494646; // RIFF

	var WEBP_SIGNATURE = 0x57454250; // WEBP

	var VP8_SIGNATURE = 0x56503820; // VP8

	var VP8L_SIGNATURE = 0x5650384c; // VP8L

	var VP8X_SIGNATURE = 0x56503858; // VP8X

	var Webp = /*#__PURE__*/function () {
	  function Webp() {
	    babelHelpers.classCallCheck(this, Webp);
	  }

	  babelHelpers.createClass(Webp, [{
	    key: "getSize",
	    value: function getSize(file) {
	      return new Promise(function (resolve, reject) {
	        if (file.size < 16) {
	          return resolve(null);
	        }

	        var blob = file.slice(0, 30);
	        getArrayBuffer(blob).then(function (buffer) {
	          var view = new DataView(buffer);

	          if (view.getUint32(0) !== RIFF_HEADER && view.getUint32(8) !== WEBP_SIGNATURE) {
	            return resolve(null);
	          }

	          var headerType = view.getUint32(12);
	          var headerView = new DataView(buffer, 20, 10);

	          if (headerType === VP8_SIGNATURE && headerView.getUint8(0) !== 0x2f) {
	            resolve({
	              width: headerView.getUint16(6, true) & 0x3fff,
	              height: headerView.getUint16(8, true) & 0x3fff
	            });
	            return;
	          } else if (headerType === VP8L_SIGNATURE && headerView.getUint8(0) === 0x2f) {
	            var bits = headerView.getUint32(1, true);
	            resolve({
	              width: (bits & 0x3FFF) + 1,
	              height: (bits >> 14 & 0x3FFF) + 1
	            });
	            return;
	          } else if (headerType === VP8X_SIGNATURE) {
	            var extendedHeader = headerView.getUint8(0);
	            var validStart = (extendedHeader & 0xc0) === 0;
	            var validEnd = (extendedHeader & 0x01) === 0;

	            if (validStart && validEnd) {
	              var width = 1 + (headerView.getUint8(6) << 16 | headerView.getUint8(5) << 8 | headerView.getUint8(4));
	              var height = 1 + (headerView.getUint8(9) << 0 | headerView.getUint8(8) << 8 | headerView.getUint8(7));
	              resolve({
	                width: width,
	                height: height
	              });
	              return;
	            }
	          }

	          resolve(null);
	        }).catch(function () {
	          resolve(null);
	        });
	      });
	    }
	  }]);
	  return Webp;
	}();

	var jpg = new Jpeg();
	var typeHandlers = {
	  gif: new Gif(),
	  png: new Png(),
	  bmp: new Bmp(),
	  jpg: jpg,
	  jpeg: jpg,
	  jpe: jpg,
	  webp: new Webp()
	};

	var getImageSize = function getImageSize(file) {
	  if (file.size === 0) {
	    return Promise.resolve(null);
	  }

	  var extension = getFileExtension(file.name).toLowerCase();
	  var type = file.type.replace(/^image\//, '');
	  var typeHandler = typeHandlers[extension] || typeHandlers[type];

	  if (!typeHandler) {
	    return Promise.resolve(null);
	  }

	  return typeHandler.getSize(file);
	};

	var ImageSizeFilter = /*#__PURE__*/function (_Filter) {
	  babelHelpers.inherits(ImageSizeFilter, _Filter);

	  function ImageSizeFilter(uploader) {
	    var _this;

	    var filterOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ImageSizeFilter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ImageSizeFilter).call(this, uploader));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imageMinWidth", 1);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imageMinHeight", 1);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imageMaxWidth", 10000);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imageMaxHeight", 10000);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "ignoreUnknownImageTypes", false);
	    var options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    ['imageMinWidth', 'imageMinHeight', 'imageMaxWidth', 'imageMaxHeight'].forEach(function (option) {
	      _this[option] = main_core.Type.isNumber(options[option]) && options[option] > 0 ? options[option] : _this[option];
	    });

	    if (main_core.Type.isBoolean(options['ignoreUnknownImageTypes'])) {
	      _this.ignoreUnknownImageTypes = options['ignoreUnknownImageTypes'];
	    }

	    return _this;
	  }

	  babelHelpers.createClass(ImageSizeFilter, [{
	    key: "apply",
	    value: function apply(file) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (!file.isImage()) {
	          resolve();
	          return;
	        }

	        getImageSize(file.getFile()).then(function (_ref) {
	          var width = _ref.width,
	              height = _ref.height;
	          file.setWidth(width);
	          file.setHeight(height);

	          if (width < _this2.imageMinWidth || height < _this2.imageMinHeight) {
	            reject(new UploaderError('IMAGE_IS_TOO_SMALL', {
	              minWidth: _this2.imageMinWidth,
	              minHeight: _this2.imageMinHeight
	            }));
	          } else if (width > _this2.imageMaxWidth || height > _this2.imageMaxHeight) {
	            reject(new UploaderError('IMAGE_IS_TOO_BIG', {
	              maxWidth: _this2.imageMaxWidth,
	              maxHeight: _this2.imageMaxHeight
	            }));
	          } else {
	            resolve();
	          }
	        }).catch(function () {
	          if (_this2.ignoreUnknownImageTypes) {
	            resolve();
	          } else {
	            reject(new UploaderError('IMAGE_TYPE_NOT_SUPPORTED'));
	          }
	        });
	      });
	    }
	  }]);
	  return ImageSizeFilter;
	}(Filter);

	var createWorker = function createWorker(fn) {
	  var workerBlob = new Blob(['(', fn.toString(), ')()'], {
	    type: 'application/javascript'
	  });
	  var workerURL = URL.createObjectURL(workerBlob);
	  var worker = new Worker(workerURL);
	  return {
	    post: function post(message, callback, transfer) {
	      var id = createUniqueId();

	      worker.onmessage = function (event) {
	        if (event.data.id === id) {
	          callback(event.data.message);
	        }
	      };

	      worker.postMessage({
	        id: id,
	        message: message
	      }, transfer);
	    },
	    terminate: function terminate() {
	      worker.terminate();
	      URL.revokeObjectURL(workerURL);
	    }
	  };
	};

	var BitmapWorker = function BitmapWorker() {
	  self.onmessage = function (event) {
	    createImageBitmap(event.data.message.file).then(function (bitmap) {
	      self.postMessage({
	        id: event.data.id,
	        message: bitmap
	      }, [bitmap]);
	    }).catch(function () {
	      self.postMessage({
	        id: event.data.id,
	        message: null
	      }, []);
	    });
	  };
	};

	var loadImage = function loadImage(file) {
	  return new Promise(function (resolve, reject) {
	    var image = document.createElement('img');
	    var url = URL.createObjectURL(file);
	    image.src = url;

	    image.onerror = function (error) {
	      URL.revokeObjectURL(image.src);
	      reject(error);
	    };

	    image.onload = function () {
	      URL.revokeObjectURL(url);
	      resolve({
	        width: image.naturalWidth,
	        height: image.naturalHeight,
	        image: image
	      });
	    };
	  });
	};

	var createImagePreview = function createImagePreview(data, width, height) {
	  width = Math.round(width);
	  height = Math.round(height);
	  var canvas = document.createElement('canvas');
	  canvas.width = width;
	  canvas.height = height;
	  var context = canvas.getContext('2d'); // context.imageSmoothingQuality = 'high';

	  context.drawImage(data, 0, 0, width, height);
	  return canvas;
	};

	var getFilenameWithoutExtension = function getFilenameWithoutExtension(name) {
	  return name.substr(0, name.lastIndexOf('.')) || name;
	};

	var extensionMap = {
	  'jpeg': 'jpg'
	};

	var renameFileToMatchMimeType = function renameFileToMatchMimeType(filename, mimeType) {
	  var name = getFilenameWithoutExtension(filename);
	  var type = mimeType.split('/')[1];
	  var extension = extensionMap[type] || type;
	  return "".concat(name, ".").concat(extension);
	};

	var canvasPrototype = window.HTMLCanvasElement && window.HTMLCanvasElement.prototype;
	var hasToBlobSupport = window.HTMLCanvasElement && canvasPrototype.toBlob;

	var convertCanvasToBlob = function convertCanvasToBlob(canvas, type, quality) {
	  return new Promise(function (resolve, reject) {
	    if (hasToBlobSupport) {
	      canvas.toBlob(function (blob) {
	        resolve(blob);
	      }, type, quality);
	    } else {
	      var blob = createBlobFromDataUri(canvas.toDataURL(type, quality));
	      resolve(blob);
	    }
	  });
	};

	var canCreateImageBitmap = 'createImageBitmap' in window && typeof ImageBitmap !== 'undefined' && ImageBitmap.prototype && ImageBitmap.prototype.close;

	var resizeImage = function resizeImage(file, options) {
	  return new Promise(function (resolve, reject) {
	    var loadImageDataFallback = function loadImageDataFallback() {
	      loadImage(file).then(function (_ref) {
	        var image = _ref.image;
	        handleImageLoad(image);
	      }).catch(function (error) {
	        reject(error);
	      });
	    };

	    var handleImageLoad = function handleImageLoad(imageData) {
	      var _calcTargetSize = calcTargetSize(imageData, options),
	          targetWidth = _calcTargetSize.targetWidth,
	          targetHeight = _calcTargetSize.targetHeight;

	      if (!targetWidth || !targetHeight) {
	        if ('close' in imageData) {
	          imageData.close();
	        }

	        resolve({
	          preview: file,
	          width: imageData.width,
	          height: imageData.height
	        });
	        return;
	      }

	      var canvas = createImagePreview(imageData, targetWidth, targetHeight); // if it was ImageBitmap

	      if ('close' in imageData) {
	        imageData.close();
	      }

	      var _options$quality = options.quality,
	          quality = _options$quality === void 0 ? 0.92 : _options$quality,
	          _options$mimeType = options.mimeType,
	          mimeType = _options$mimeType === void 0 ? 'image/jpeg' : _options$mimeType;
	      var type = /jpeg|png|webp/.test(file.type) ? file.type : mimeType;
	      convertCanvasToBlob(canvas, type, quality).then(function (blob) {
	        var newFileName = renameFileToMatchMimeType(file.name, type);
	        var preview = createFileFromBlob(blob, newFileName);
	        resolve({
	          preview: preview,
	          width: targetWidth,
	          height: targetHeight
	        });
	      }).catch(function () {
	        reject();
	      });
	    };

	    if (canCreateImageBitmap) {
	      var bitmapWorker = createWorker(BitmapWorker);
	      bitmapWorker.post({
	        file: file
	      }, function (imageBitmap) {
	        bitmapWorker.terminate();

	        if (imageBitmap) {
	          handleImageLoad(imageBitmap);
	        } else {
	          loadImageDataFallback();
	        }
	      });
	    } else {
	      loadImageDataFallback();
	    }
	  });
	};

	var calcTargetSize = function calcTargetSize(imageData) {
	  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	  var _options$mode = options.mode,
	      mode = _options$mode === void 0 ? 'contain' : _options$mode,
	      _options$upscale = options.upscale,
	      upscale = _options$upscale === void 0 ? false : _options$upscale,
	      width = options.width,
	      height = options.height;
	  var result = {
	    targetWidth: 0,
	    targetHeight: 0
	  };

	  if (!width && !height) {
	    return result;
	  }

	  if (width === null) {
	    width = height;
	  } else if (height === null) {
	    height = width;
	  }

	  if (mode !== 'force') {
	    var ratioWidth = width / imageData.width;
	    var ratioHeight = height / imageData.height;
	    var ratio = 1;

	    if (mode === 'cover') {
	      ratio = Math.max(ratioWidth, ratioHeight);
	    } else if (mode === 'contain') {
	      ratio = Math.min(ratioWidth, ratioHeight);
	    } // if image is too small, exit here with original image


	    if (ratio > 1 && upscale === false) {
	      return result;
	    }

	    width = imageData.width * ratio;
	    height = imageData.height * ratio;
	  }
	  /*if (mode === 'crop')
	  {
	  	const sourceImageRatio = sourceImageWidth / sourceImageHeight;
	  	const targetRatio = targetWidth / targetHeight;
	  		if (sourceImageRatio > targetRatio)
	  	{
	  		const newWidth = sourceImageHeight * targetRatio;
	  		srcX = (sourceImageWidth - newWidth) / 2;
	  		sourceImageWidth = newWidth;
	  	}
	  	else
	  	{
	  		const newHeight = sourceImageWidth / targetRatio;
	  		srcY = (sourceImageHeight - newHeight) / 2;
	  		sourceImageHeight = newHeight;
	  	}
	  		context.drawImage(image, srcX, srcY, sourceImageWidth, sourceImageHeight, 0, 0, targetWidth, targetHeight);
	  }*/


	  result.targetWidth = Math.round(width);
	  result.targetHeight = Math.round(height);
	  return result;
	};

	var ImagePreviewFilter = /*#__PURE__*/function (_Filter) {
	  babelHelpers.inherits(ImagePreviewFilter, _Filter);

	  function ImagePreviewFilter(uploader) {
	    var _this;

	    var filterOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ImagePreviewFilter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ImagePreviewFilter).call(this, uploader));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imagePreviewWidth", 300);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imagePreviewHeight", 300);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imagePreviewQuality", 0.92);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imagePreviewMimeType", 'image/jpeg');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imagePreviewUpscale", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "imagePreviewResizeMethod", 'contain');
	    var options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    var integerOptions = ['imagePreviewWidth', 'imagePreviewHeight', 'imagePreviewQuality'];
	    integerOptions.forEach(function (option) {
	      _this[option] = main_core.Type.isNumber(options[option]) && options[option] > 0 ? options[option] : _this[option];
	    });

	    if (main_core.Type.isBoolean(options['imagePreviewUpscale'])) {
	      _this.imagePreviewUpscale = options['imagePreviewUpscale'];
	    }

	    if (['contain', 'force', 'cover'].includes(options['imagePreviewResizeMethod'])) {
	      _this.imagePreviewResizeMethod = options['imagePreviewResizeMethod'];
	    }

	    if (['image/jpeg', 'image/png'].includes(options['imagePreviewMimeType'])) {
	      _this.imagePreviewMimeType = options['imagePreviewMimeType'];
	    }

	    return _this;
	  }

	  babelHelpers.createClass(ImagePreviewFilter, [{
	    key: "apply",
	    value: function apply(file) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (!isResizableImage(file.getFile())) {
	          resolve();
	          return;
	        }

	        var options = {
	          width: _this2.imagePreviewWidth,
	          height: _this2.imagePreviewHeight,
	          mode: _this2.imagePreviewResizeMethod,
	          upscale: _this2.imagePreviewUpscale,
	          quality: _this2.imagePreviewQuality,
	          mimeType: _this2.imagePreviewMimeType
	        };
	        resizeImage(file.getFile(), options).then(function (_ref) {
	          var preview = _ref.preview,
	              width = _ref.width,
	              height = _ref.height;
	          //setTimeout(() => {
	          file.setClientPreview(preview, width, height);
	          resolve(); //}, 60000);
	        }).catch(function () {
	          resolve();
	        });
	      });
	    }
	  }]);
	  return ImagePreviewFilter;
	}(Filter);

	var TransformImageFilter = /*#__PURE__*/function (_Filter) {
	  babelHelpers.inherits(TransformImageFilter, _Filter);

	  function TransformImageFilter(uploader) {
	    var _this;

	    var filterOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, TransformImageFilter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TransformImageFilter).call(this, uploader));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "resizeWidth", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "resizeHeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "resizeMethod", 'contain');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "resizeMimeType", 'image/jpeg');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "resizeQuality", 0.92);
	    var options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};

	    if (main_core.Type.isNumber(options['imageResizeWidth']) && options['imageResizeWidth'] > 0) {
	      _this.resizeWidth = options['imageResizeWidth'];
	    }

	    if (main_core.Type.isNumber(options['imageResizeHeight']) && options['imageResizeHeight'] > 0) {
	      _this.resizeHeight = options['imageResizeHeight'];
	    }

	    if (['contain', 'force', 'cover'].includes(options['imageResizeMethod'])) {
	      _this.resizeMethod = options['imageResizeMethod'];
	    }

	    if (main_core.Type.isNumber(options['imageResizeQuality'])) {
	      _this.resizeQuality = Math.min(Math.max(0.1, options['imageResizeQuality']), 1);
	    }

	    if (['image/jpeg', 'image/png'].includes(options['imageResizeMimeType'])) {
	      _this.resizeMimeType = options['imageResizeMimeType'];
	    }

	    return _this;
	  }

	  babelHelpers.createClass(TransformImageFilter, [{
	    key: "apply",
	    value: function apply(file) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (!isResizableImage(file)) {
	          return resolve(file);
	        }

	        if (_this2.resizeWidth === null && _this2.resizeHeight === null) {
	          return resolve(file);
	        }

	        var options = {
	          width: _this2.resizeWidth,
	          height: _this2.resizeHeight,
	          mode: _this2.resizeMethod,
	          quality: _this2.resizeQuality,
	          mimeType: _this2.resizeMimeType
	        };
	        resizeImage(file, options).then(function (_ref) {
	          var preview = _ref.preview;
	          resolve(preview);
	        }).catch(function () {
	          resolve(file);
	        });
	      });
	    }
	  }]);
	  return TransformImageFilter;
	}(Filter);

	var UploaderStatus = {
	  STARTED: 0,
	  STOPPED: 1
	};

	var FilterType = {
	  VALIDATION: 'validation',
	  PREPARATION: 'preparation'
	};

	var getFilesFromDataTransfer = function getFilesFromDataTransfer(dataTransfer) {
	  return new Promise(function (resolve, reject) {
	    if (!dataTransfer.items) {
	      resolve(dataTransfer.files ? Array.from(dataTransfer.files) : []);
	      return;
	    }

	    var items = Array.from(dataTransfer.items).filter(function (item) {
	      return isFileSystemItem(item);
	    }).map(function (item) {
	      return getFilesFromItem(item);
	    });
	    Promise.all(items).then(function (fileGroups) {
	      var files = [];
	      fileGroups.forEach(function (group) {
	        files.push.apply(files, group);
	      });
	      resolve(files);
	    }).catch(reject);
	  });
	};

	var isFileSystemItem = function isFileSystemItem(item) {
	  if ('webkitGetAsEntry' in item) {
	    var entry = item.webkitGetAsEntry();

	    if (entry) {
	      return entry.isFile || entry.isDirectory;
	    }
	  }

	  return item.kind === 'file';
	};

	var getFilesFromItem = function getFilesFromItem(item) {
	  return new Promise(function (resolve, reject) {
	    if (isDirectoryEntry(item)) {
	      getFilesInDirectory(getAsEntry(item)).then(resolve).catch(reject);
	      return;
	    }

	    resolve([item.getAsFile()]);
	  });
	};

	var getFilesInDirectory = function getFilesInDirectory(entry) {
	  return new Promise(function (resolve, reject) {
	    var files = [];
	    var dirCounter = 0;
	    var fileCounter = 0;

	    var resolveIfDone = function resolveIfDone() {
	      if (fileCounter === 0 && dirCounter === 0) {
	        resolve(files);
	      }
	    };

	    var readEntries = function readEntries(dirEntry) {
	      dirCounter++;
	      var directoryReader = dirEntry.createReader();

	      var readBatch = function readBatch() {
	        directoryReader.readEntries(function (entries) {
	          if (entries.length === 0) {
	            dirCounter--;
	            resolveIfDone();
	            return;
	          }

	          entries.forEach(function (entry) {
	            if (entry.isDirectory) {
	              readEntries(entry);
	            } else {
	              fileCounter++;
	              entry.file(function (file) {
	                files.push(file);
	                fileCounter--;
	                resolveIfDone();
	              });
	            }
	          });
	          readBatch();
	        }, reject);
	      };

	      readBatch();
	    };

	    readEntries(entry);
	  });
	};

	var isDirectoryEntry = function isDirectoryEntry(item) {
	  return isEntry(item) && (getAsEntry(item) || {}).isDirectory;
	};

	var isEntry = function isEntry(item) {
	  return 'webkitGetAsEntry' in item;
	};

	var getAsEntry = function getAsEntry(item) {
	  return item.webkitGetAsEntry();
	};

	var result = null;

	var canAppendFileToForm = function canAppendFileToForm() {
	  if (result === null) {
	    try {
	      var dataTransfer = new DataTransfer();
	      var file = new File(['hello'], 'my.txt');
	      dataTransfer.items.add(file);
	      var input = document.createElement('input');
	      input.setAttribute('type', 'file');
	      input.files = dataTransfer.files;
	      result = input.files.length === 1;
	    } catch (err) {
	      result = false;
	    }
	  }

	  return result;
	};

	var assignFileToInput = function assignFileToInput(input, file) {
	  try {
	    var dataTransfer = new DataTransfer();
	    var files = main_core.Type.isArray(file) ? file : [file];
	    files.forEach(function (file) {
	      dataTransfer.items.add(file);
	    });
	    input.files = dataTransfer.files;
	  } catch (error) {
	    return false;
	  }

	  return true;
	};

	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _setLoadController = new WeakSet();

	var _setUploadController = new WeakSet();

	var _exceedsMaxFileCount = new WeakSet();

	var _applyFilters = new WeakSet();

	var _uploadNext = new WeakSet();

	var _loadNext = new WeakSet();

	var _setHiddenField = new WeakSet();

	var _resetHiddenField = new WeakSet();

	var _syncInputPositions = new WeakSet();

	var Uploader = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Uploader, _EventEmitter);

	  function Uploader(uploaderOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, Uploader);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Uploader).call(this));

	    _syncInputPositions.add(babelHelpers.assertThisInitialized(_this));

	    _resetHiddenField.add(babelHelpers.assertThisInitialized(_this));

	    _setHiddenField.add(babelHelpers.assertThisInitialized(_this));

	    _loadNext.add(babelHelpers.assertThisInitialized(_this));

	    _uploadNext.add(babelHelpers.assertThisInitialized(_this));

	    _applyFilters.add(babelHelpers.assertThisInitialized(_this));

	    _exceedsMaxFileCount.add(babelHelpers.assertThisInitialized(_this));

	    _setUploadController.add(babelHelpers.assertThisInitialized(_this));

	    _setLoadController.add(babelHelpers.assertThisInitialized(_this));

	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "files", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "multiple", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "autoUpload", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "allowReplaceSingle", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxParallelUploads", 2);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxParallelLoads", 10);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "acceptedFileTypes", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "ignoredFileNames", ['.ds_store', 'thumbs.db', 'desktop.ini']);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxFileCount", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "server", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hiddenFields", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hiddenFieldsContainer", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hiddenFieldName", 'file');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "assignAsFile", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "filters", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "status", UploaderStatus.STOPPED);

	    _this.setEventNamespace('BX.UI.FileUploader');

	    var options = main_core.Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
	    _this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple : false;

	    _this.setAutoUpload(options.autoUpload);

	    _this.setMaxParallelUploads(options.maxParallelUploads);

	    _this.setMaxParallelLoads(options.maxParallelLoads);

	    _this.setAcceptedFileTypes(options.acceptedFileTypes);

	    _this.setIgnoredFileNames(options.ignoredFileNames);

	    _this.setMaxFileCount(options.maxFileCount);

	    _this.setAllowReplaceSingle(options.allowReplaceSingle);

	    _this.assignBrowse(options.browseElement);

	    _this.assignDropzone(options.dropElement);

	    _this.assignPaste(options.pasteElement);

	    _this.setHiddenFieldsContainer(options.hiddenFieldsContainer);

	    _this.setHiddenFieldName(options.hiddenFieldName);

	    _this.setAssignAsFile(options.assignAsFile);

	    var serverOptions = main_core.Type.isPlainObject(options.serverOptions) ? options.serverOptions : {};
	    serverOptions = Object.assign({}, {
	      controller: options.controller,
	      controllerOptions: options.controllerOptions
	    }, serverOptions);
	    _this.server = new Server(serverOptions);

	    _this.subscribeFromOptions(options.events);

	    _this.addFilter(FilterType.VALIDATION, new FileSizeFilter(babelHelpers.assertThisInitialized(_this), options));

	    _this.addFilter(FilterType.VALIDATION, new FileTypeFilter(babelHelpers.assertThisInitialized(_this), options));

	    _this.addFilter(FilterType.VALIDATION, new ImageSizeFilter(babelHelpers.assertThisInitialized(_this), options));

	    _this.addFilter(FilterType.VALIDATION, new ImagePreviewFilter(babelHelpers.assertThisInitialized(_this), options));

	    _this.addFilter(FilterType.PREPARATION, new TransformImageFilter(babelHelpers.assertThisInitialized(_this), options));

	    _this.addFilters(options.filters);

	    _this.handleBeforeUpload = _this.handleBeforeUpload.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handlePrepareFileAsync = _this.handlePrepareFileAsync.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleUploadStart = _this.handleBeforeUpload.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleFileCancel = _this.handleFileCancel.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleFileStatusChange = _this.handleFileStatusChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleFileStateChange = _this.handleFileStateChange.bind(babelHelpers.assertThisInitialized(_this));

	    _this.addFiles(options.files);

	    return _this;
	  }

	  babelHelpers.createClass(Uploader, [{
	    key: "addFiles",
	    value: function addFiles(fileList) {
	      var _this2 = this;

	      if (!main_core.Type.isArrayLike(fileList)) {
	        return;
	      }

	      var files = Array.from(fileList);

	      if (_classPrivateMethodGet$3(this, _exceedsMaxFileCount, _exceedsMaxFileCount2).call(this, files)) {
	        return;
	      }

	      files.forEach(function (file) {
	        if (main_core.Type.isArrayFilled(file)) {
	          _this2.addFile(file[0], file[1]);
	        } else {
	          _this2.addFile(file);
	        }
	      });
	    }
	  }, {
	    key: "addFile",
	    value: function addFile(source, options) {
	      var file = new UploaderFile(source, options);

	      if (this.getIgnoredFileNames().includes(file.getName().toLowerCase())) {
	        return;
	      }

	      if (_classPrivateMethodGet$3(this, _exceedsMaxFileCount, _exceedsMaxFileCount2).call(this, [file])) {
	        return;
	      }

	      if (!this.isMultiple() && this.shouldReplaceSingle() && this.getFiles().length > 0) {
	        var fileToReplace = this.getFiles()[0];
	        this.removeFile(fileToReplace);
	      }

	      var event = new main_core_events.BaseEvent({
	        data: {
	          file: file
	        }
	      });
	      this.emit('File:onBeforeAdd', event);

	      if (event.isDefaultPrevented()) {
	        return;
	      }

	      _classPrivateMethodGet$3(this, _setLoadController, _setLoadController2).call(this, file);

	      _classPrivateMethodGet$3(this, _setUploadController, _setUploadController2).call(this, file);

	      this.files.push(file);
	      file.setStatus(FileStatus.ADDED);
	      this.emit('File:onAddStart', {
	        file: file
	      });
	      file.subscribe('onBeforeUpload', this.handleBeforeUpload);
	      file.subscribe('onPrepareFileAsync', this.handlePrepareFileAsync);
	      file.subscribe('onUploadStart', this.handleUploadStart);
	      file.subscribe('onCancel', this.handleFileCancel);
	      file.subscribe('onStatusChange', this.handleFileStatusChange);
	      file.subscribe('onStateChange', this.handleFileStateChange);

	      if (file.getOrigin() === FileOrigin.SERVER) {
	        file.load();
	      } else {
	        _classPrivateMethodGet$3(this, _loadNext, _loadNext2).call(this);
	      }
	    }
	  }, {
	    key: "start",
	    value: function start() {
	      if (this.getStatus() !== UploaderStatus.STARTED) {
	        this.status = UploaderStatus.STARTED;
	        this.emit('onStart');

	        _classPrivateMethodGet$3(this, _uploadNext, _uploadNext2).call(this);
	      }
	    }
	  }, {
	    key: "stop",
	    value: function stop() {
	      this.status = UploaderStatus.STOPPED;
	      this.getFiles().forEach(function (file) {
	        if (file.isUploading()) {
	          file.abort();
	          file.setStatus(FileStatus.PENDING);
	        }
	      });
	      this.emit('onStop');
	    }
	  }, {
	    key: "cancel",
	    value: function cancel() {
	      this.getFiles().forEach(function (file) {
	        file.cancel();
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.emit('onDestroy'); // TODO
	      // unassignBrowse
	      // unassignDrop

	      this.getFiles().forEach(function (file) {
	        file.cancel();
	      });

	      for (var property in this) {
	        if (this.hasOwnProperty(property)) {
	          delete this[property];
	        }
	      }

	      Object.setPrototypeOf(this, null);
	    }
	  }, {
	    key: "removeFile",
	    value: function removeFile(file) {
	      if (main_core.Type.isString(file)) {
	        file = this.getFile(file);
	      }

	      var index = this.files.findIndex(function (element) {
	        return element === file;
	      });

	      if (index >= 0) {
	        this.files.splice(index, 1);
	        file.abort();
	        file.setStatus(FileStatus.INIT);
	        this.emit('File:onRemove', {
	          file: file
	        });

	        _classPrivateMethodGet$3(this, _resetHiddenField, _resetHiddenField2).call(this, file);
	      }
	    }
	  }, {
	    key: "getFile",
	    value: function getFile(id) {
	      return this.getFiles().find(function (file) {
	        return file.getId() === id;
	      }) || null;
	    }
	  }, {
	    key: "getFiles",
	    value: function getFiles() {
	      return this.files;
	    }
	  }, {
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.multiple;
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      return this.status;
	    }
	  }, {
	    key: "addFilter",
	    value: function addFilter(type, filter) {
	      var filterOptions = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	      if (main_core.Type.isFunction(filter) || main_core.Type.isString(filter)) {
	        var className = main_core.Type.isString(filter) ? main_core.Reflection.getClass(filter) : filter;

	        if (main_core.Type.isFunction(className)) {
	          filter = new className(this, filterOptions);
	        }
	      }

	      if (filter instanceof Filter) {
	        var filters = this.filters.get(type);

	        if (!main_core.Type.isArray(filters)) {
	          filters = [];
	          this.filters.set(type, filters);
	        }

	        filters.push(filter);
	      } else {
	        throw new Error('FileUploader: a filter must be an instance of FileUploader.Filter.');
	      }
	    }
	  }, {
	    key: "addFilters",
	    value: function addFilters(filters) {
	      var _this3 = this;

	      if (main_core.Type.isArray(filters)) {
	        filters.forEach(function (filter) {
	          if (main_core.Type.isPlainObject(filter)) {
	            _this3.addFilter(filter.type, filter.filter, filter.options);
	          }
	        });
	      }
	    }
	  }, {
	    key: "getServer",
	    value: function getServer() {
	      return this.server;
	    }
	  }, {
	    key: "assignBrowse",
	    value: function assignBrowse(nodes) {
	      var _this4 = this;

	      nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	      if (!main_core.Type.isArray(nodes)) {
	        return;
	      }

	      nodes.forEach(function (node) {
	        if (!main_core.Type.isElementNode(node)) {
	          return;
	        }

	        var input = null;

	        if (node.tagName === 'INPUT' && node.type === 'file') {
	          input = node; // Add already selected files

	          if (input.files) {
	            _this4.addFiles(input.files);
	          }

	          var acceptAttr = input.getAttribute('accept');

	          if (main_core.Type.isStringFilled(acceptAttr)) {
	            _this4.setAcceptedFileTypes(acceptAttr);
	          }
	        } else {
	          input = document.createElement('input');
	          input.setAttribute('type', 'file');
	          main_core.Event.bind(node, 'click', function () {
	            input.click();
	          });
	        }

	        if (_this4.isMultiple()) {
	          input.setAttribute('multiple', 'multiple');
	        }

	        if (main_core.Type.isArrayFilled(_this4.getAcceptedFileTypes())) {
	          input.setAttribute('accept', _this4.getAcceptedFileTypes().join(','));
	        }

	        main_core.Event.bind(input, 'change', function () {
	          _this4.addFiles(Array.from(input.files)); // reset file input


	          input.value = '';
	        });
	      });
	    }
	  }, {
	    key: "assignDropzone",
	    value: function assignDropzone(nodes) {
	      var _this5 = this;

	      nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	      if (!main_core.Type.isArray(nodes)) {
	        return;
	      }

	      nodes.forEach(function (node) {
	        if (!main_core.Type.isElementNode(node)) {
	          return;
	        }

	        main_core.Event.bind(node, 'dragover', function (event) {
	          event.preventDefault();
	        });
	        main_core.Event.bind(node, 'dragenter', function (event) {
	          event.preventDefault();
	        });
	        main_core.Event.bind(node, 'drop', function (event) {
	          event.preventDefault();
	          event.stopPropagation();
	          getFilesFromDataTransfer(event.dataTransfer).then(function (files) {
	            _this5.addFiles(files);
	          });
	        });
	      });
	    }
	  }, {
	    key: "assignPaste",
	    value: function assignPaste(nodes) {
	      var _this6 = this;

	      nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	      if (!main_core.Type.isArray(nodes)) {
	        return;
	      }

	      nodes.forEach(function (node) {
	        if (!main_core.Type.isElementNode(node)) {
	          return;
	        }

	        main_core.Event.bind(node, 'paste', function (event) {
	          event.preventDefault();
	          var clipboardData = event.clipboardData;

	          if (!clipboardData) {
	            return;
	          }

	          getFilesFromDataTransfer(clipboardData).then(function (files) {
	            _this6.addFiles(files);
	          });
	        });
	      });
	    }
	  }, {
	    key: "getHiddenFieldsContainer",
	    value: function getHiddenFieldsContainer() {
	      var element = null;

	      if (main_core.Type.isStringFilled(this.hiddenFieldsContainer)) {
	        element = document.querySelector(this.hiddenFieldsContainer);
	      } else if (main_core.Type.isElementNode(this.hiddenFieldsContainer)) {
	        element = this.hiddenFieldsContainer;
	      }

	      return element;
	    }
	  }, {
	    key: "setHiddenFieldsContainer",
	    value: function setHiddenFieldsContainer(container) {
	      if (main_core.Type.isStringFilled(container) || main_core.Type.isElementNode(container) || main_core.Type.isNull(container)) {
	        this.hiddenFieldsContainer = container;
	      }
	    }
	  }, {
	    key: "getHiddenFieldName",
	    value: function getHiddenFieldName() {
	      return this.hiddenFieldName;
	    }
	  }, {
	    key: "setHiddenFieldName",
	    value: function setHiddenFieldName(name) {
	      if (main_core.Type.isStringFilled(name)) {
	        this.hiddenFieldName = name;
	      }
	    }
	  }, {
	    key: "shouldAssignAsFile",
	    value: function shouldAssignAsFile() {
	      return this.assignAsFile;
	    }
	  }, {
	    key: "setAssignAsFile",
	    value: function setAssignAsFile(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.assignAsFile = flag;
	      }
	    }
	  }, {
	    key: "getTotalSize",
	    value: function getTotalSize() {
	      return this.getFiles().reduce(function (totalSize, file) {
	        return totalSize + file.getSize();
	      }, 0);
	    }
	  }, {
	    key: "shouldAutoUpload",
	    value: function shouldAutoUpload() {
	      return this.autoUpload;
	    }
	  }, {
	    key: "setAutoUpload",
	    value: function setAutoUpload(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.autoUpload = flag;
	      }
	    }
	  }, {
	    key: "getMaxParallelUploads",
	    value: function getMaxParallelUploads() {
	      return this.maxParallelUploads;
	    }
	  }, {
	    key: "setMaxParallelUploads",
	    value: function setMaxParallelUploads(number) {
	      if (main_core.Type.isNumber(number) && number > 0) {
	        this.maxParallelUploads = number;
	      }
	    }
	  }, {
	    key: "getMaxParallelLoads",
	    value: function getMaxParallelLoads() {
	      return this.maxParallelLoads;
	    }
	  }, {
	    key: "setMaxParallelLoads",
	    value: function setMaxParallelLoads(number) {
	      if (main_core.Type.isNumber(number) && number > 0) {
	        this.maxParallelLoads = number;
	      }
	    }
	  }, {
	    key: "getUploadingFileCount",
	    value: function getUploadingFileCount() {
	      return this.getFiles().filter(function (file) {
	        return file.isUploading();
	      }).length;
	    }
	  }, {
	    key: "getAcceptedFileTypes",
	    value: function getAcceptedFileTypes() {
	      return this.acceptedFileTypes;
	    }
	  }, {
	    key: "setAcceptedFileTypes",
	    value: function setAcceptedFileTypes(fileTypes) {
	      var _this7 = this;

	      if (main_core.Type.isString(fileTypes)) {
	        fileTypes = fileTypes.split(',');
	      }

	      if (main_core.Type.isArray(fileTypes)) {
	        this.acceptedFileTypes = [];
	        fileTypes.forEach(function (type) {
	          if (main_core.Type.isStringFilled(type)) {
	            _this7.acceptedFileTypes.push(type);
	          }
	        });
	      }
	    }
	  }, {
	    key: "getIgnoredFileNames",
	    value: function getIgnoredFileNames() {
	      return this.ignoredFileNames;
	    }
	  }, {
	    key: "setIgnoredFileNames",
	    value: function setIgnoredFileNames(fileNames) {
	      var _this8 = this;

	      if (main_core.Type.isArray(fileNames)) {
	        this.ignoredFileNames = [];
	        fileNames.forEach(function (fileName) {
	          if (main_core.Type.isStringFilled(fileName)) {
	            _this8.ignoredFileNames.push(fileName.toLowerCase());
	          }
	        });
	      }
	    }
	  }, {
	    key: "setMaxFileCount",
	    value: function setMaxFileCount(maxFileCount) {
	      if (main_core.Type.isNumber(maxFileCount) && maxFileCount > 0 || maxFileCount === null) {
	        this.maxFileCount = maxFileCount;
	      }
	    }
	  }, {
	    key: "getMaxFileCount",
	    value: function getMaxFileCount() {
	      return this.maxFileCount;
	    }
	  }, {
	    key: "setAllowReplaceSingle",
	    value: function setAllowReplaceSingle(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.allowReplaceSingle = flag;
	      }
	    }
	  }, {
	    key: "shouldReplaceSingle",
	    value: function shouldReplaceSingle() {
	      return this.allowReplaceSingle;
	    }
	  }, {
	    key: "handleBeforeUpload",
	    value: function handleBeforeUpload(event) {
	      if (this.getStatus() === UploaderStatus.STOPPED) {
	        event.preventDefault();
	        this.start();
	      } else {
	        if (this.getUploadingFileCount() >= this.getMaxParallelUploads()) {
	          event.preventDefault();
	        }
	      }
	    }
	  }, {
	    key: "handlePrepareFileAsync",
	    value: function handlePrepareFileAsync(event) {
	      var _this9 = this;

	      return new Promise(function (resolve, reject) {
	        var _event$getData = event.getData(),
	            file = _event$getData.file;

	        _classPrivateMethodGet$3(_this9, _applyFilters, _applyFilters2).call(_this9, FilterType.PREPARATION, file).then(function (transformedFile) {
	          if (main_core.Type.isFile(transformedFile)) {
	            resolve(transformedFile);
	          } else {
	            resolve(file);
	          }
	        }).catch(function (error) {
	          return reject(error);
	        });
	      });
	    }
	  }, {
	    key: "handleUploadStart",
	    value: function handleUploadStart(event) {
	      var file = event.getTarget();
	      this.emit('File:onUploadStart', {
	        file: file
	      });
	    }
	  }, {
	    key: "handleFileCancel",
	    value: function handleFileCancel(event) {
	      var file = event.getTarget();
	      this.emit('File:onCancel', {
	        file: file
	      });
	      this.removeFile(file);
	    }
	  }, {
	    key: "handleFileStatusChange",
	    value: function handleFileStatusChange(event) {
	      var file = event.getTarget();
	      this.emit('File:onStatusChange', {
	        file: file
	      });
	    }
	  }, {
	    key: "handleFileStateChange",
	    value: function handleFileStateChange(event) {
	      var file = event.getTarget();
	      this.emit('File:onStateChange', {
	        file: file
	      });

	      if (file.isComplete()) {
	        _classPrivateMethodGet$3(this, _setHiddenField, _setHiddenField2).call(this, file);
	      }
	    }
	  }]);
	  return Uploader;
	}(main_core_events.EventEmitter);

	var _setLoadController2 = function _setLoadController2(file) {
	  var _this10 = this;

	  var loadController = file.getOrigin() === FileOrigin.SERVER ? this.getServer().createLoadController() : this.getServer().createClientLoadController();
	  loadController.subscribeFromOptions({
	    'onError': function onError(event) {
	      file.setStatus(FileStatus.LOAD_FAILED);

	      _this10.emit('File:onError', {
	        file: file,
	        error: event.getData().error
	      });

	      _classPrivateMethodGet$3(_this10, _loadNext, _loadNext2).call(_this10);
	    },
	    'onProgress': function onProgress(event) {
	      _this10.emit('File:onLoadProgress', {
	        file: file,
	        progress: event.getData().progress
	      });
	    },
	    'onLoad': function onLoad(event) {
	      if (file.getOrigin() === FileOrigin.SERVER) {
	        file.setFile(event.getData().fileInfo);
	        file.setStatus(FileStatus.COMPLETE);

	        _this10.emit('File:onAdd', {
	          file: file
	        });

	        _this10.emit('File:onLoadComplete', {
	          file: file
	        });

	        _this10.emit('File:onComplete', {
	          file: file
	        });

	        return;
	      } // Validation


	      _classPrivateMethodGet$3(_this10, _applyFilters, _applyFilters2).call(_this10, FilterType.VALIDATION, file).then(function () {
	        if (file.isUploadable()) {
	          file.setStatus(FileStatus.PENDING);

	          _this10.emit('File:onAdd', {
	            file: file
	          });

	          _this10.emit('File:onLoadComplete', {
	            file: file
	          });

	          if (_this10.shouldAutoUpload()) {
	            file.upload();
	          }
	        } else {
	          file.setStatus(FileStatus.COMPLETE);

	          _this10.emit('File:onAdd', {
	            file: file
	          });

	          _this10.emit('File:onLoadComplete', {
	            file: file
	          });

	          _this10.emit('File:onComplete', {
	            file: file
	          });
	        }

	        _classPrivateMethodGet$3(_this10, _loadNext, _loadNext2).call(_this10);
	      }).catch(function (error) {
	        file.setStatus(FileStatus.LOAD_FAILED);

	        _this10.emit('File:onError', {
	          file: file,
	          error: error
	        });

	        _this10.emit('File:onAdd', {
	          file: file,
	          error: error
	        });

	        _classPrivateMethodGet$3(_this10, _loadNext, _loadNext2).call(_this10);
	      });
	    }
	  });
	  file.setLoadController(loadController);
	};

	var _setUploadController2 = function _setUploadController2(file) {
	  var _this11 = this;

	  var uploadController = this.getServer().createUploadController();

	  if (!uploadController) {
	    return;
	  }

	  uploadController.subscribeFromOptions({
	    'onError': function onError(event) {
	      file.setStatus(FileStatus.UPLOAD_FAILED);

	      _this11.emit('File:onError', {
	        file: file,
	        error: event.getData().error
	      });

	      _classPrivateMethodGet$3(_this11, _uploadNext, _uploadNext2).call(_this11);
	    },
	    'onProgress': function onProgress(event) {
	      _this11.emit('File:onUploadProgress', {
	        file: file,
	        progress: event.getData().progress
	      });
	    },
	    'onUpload': function onUpload(event) {
	      file.setStatus(FileStatus.COMPLETE);
	      file.setFile(event.getData().fileInfo);

	      _this11.emit('File:onUploadComplete', {
	        file: file
	      });

	      _this11.emit('File:onComplete', {
	        file: file
	      });

	      _classPrivateMethodGet$3(_this11, _uploadNext, _uploadNext2).call(_this11);
	    }
	  });
	  file.setUploadController(uploadController);
	};

	var _exceedsMaxFileCount2 = function _exceedsMaxFileCount2(fileList) {
	  var totalNewFiles = fileList.length;
	  var totalFiles = this.getFiles().length;

	  if (!this.isMultiple() && totalNewFiles > 1) {
	    return true;
	  }

	  var maxFileCount;

	  if (this.isMultiple()) {
	    maxFileCount = this.getMaxFileCount();
	  } else {
	    maxFileCount = this.shouldReplaceSingle() ? null : 1;
	  }

	  if (maxFileCount !== null && totalFiles + totalNewFiles > maxFileCount) {
	    var error = new UploaderError('MAX_FILE_COUNT_EXCEEDED', {
	      maxFileCount: maxFileCount
	    });
	    this.emit('onMaxFileCountExceeded', {
	      error: error
	    });
	    this.emit('onError', {
	      error: error
	    });
	    return true;
	  }

	  return false;
	};

	var _applyFilters2 = function _applyFilters2(type) {
	  var _this12 = this;

	  for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	    args[_key - 1] = arguments[_key];
	  }

	  return new Promise(function (resolve, reject) {
	    var filters = babelHelpers.toConsumableArray(_this12.filters.get(type) || []);

	    if (filters.length === 0) {
	      resolve();
	      return;
	    }

	    var firstFilter = filters.shift(); // chain filters

	    filters.reduce(function (current, next) {
	      return current.then(function () {
	        return next.apply.apply(next, args);
	      });
	    }, firstFilter.apply.apply(firstFilter, args)).then(function (result) {
	      return resolve(result);
	    }).catch(function (error) {
	      return reject(error);
	    });
	  });
	};

	var _uploadNext2 = function _uploadNext2() {
	  if (this.getStatus() !== UploaderStatus.STARTED) {
	    return;
	  }

	  var maxParallelUploads = this.getMaxParallelUploads();
	  var currentUploads = this.getUploadingFileCount();
	  var pendingFiles = this.getFiles().filter(function (file) {
	    return file.isReadyToUpload();
	  });
	  var pendingUploads = pendingFiles.length;

	  if (currentUploads < maxParallelUploads) {
	    var limit = Math.min(maxParallelUploads - currentUploads, pendingFiles.length);

	    for (var i = 0; i < limit; i++) {
	      var pendingFile = pendingFiles[i];
	      pendingFile.upload();
	    }
	  } // All files are COMPLETE or FAILED


	  if (currentUploads === 0 && pendingUploads === 0) {
	    this.status = UploaderStatus.STOPPED;
	    this.emit('onUploadComplete');
	  }
	};

	var _loadNext2 = function _loadNext2() {
	  var maxParallelLoads = this.getMaxParallelLoads();
	  var currentLoads = this.getFiles().filter(function (file) {
	    return file.isLoading();
	  }).length;
	  var pendingFiles = this.getFiles().filter(function (file) {
	    return file.getStatus() === FileStatus.ADDED && file.getOrigin() === FileOrigin.CLIENT;
	  });

	  if (currentLoads < maxParallelLoads) {
	    var limit = Math.min(maxParallelLoads - currentLoads, pendingFiles.length);

	    for (var i = 0; i < limit; i++) {
	      var pendingFile = pendingFiles[i];
	      pendingFile.load();
	    }
	  }
	};

	var _setHiddenField2 = function _setHiddenField2(file) {
	  var container = this.getHiddenFieldsContainer();

	  if (!container || this.hiddenFields.has(file.getId())) {
	    return;
	  }

	  var isExistingServerFile = main_core.Type.isNumber(file.getServerId());

	  if (isExistingServerFile) {
	    return;
	  }

	  var assignAsFile = file.getOrigin() === FileOrigin.CLIENT && !file.isUploadable() && this.shouldAssignAsFile() && canAppendFileToForm();
	  var input = document.createElement('input');
	  input.type = assignAsFile ? 'file' : 'hidden';
	  input.name = this.getHiddenFieldName() + (this.isMultiple() ? '[]' : '');

	  if (assignAsFile) {
	    main_core.Dom.style(input, {
	      visibility: 'hidden',
	      left: 0,
	      top: 0,
	      width: 0,
	      height: 0,
	      position: 'absolute',
	      'pointer-events': 'none'
	    });
	    assignFileToInput(input, file.getFile());
	  } else if (file.getServerId() !== null) {
	    input.value = file.getServerId();
	  }

	  container.appendChild(input);
	  this.hiddenFields.set(file.getId(), input);

	  _classPrivateMethodGet$3(this, _syncInputPositions, _syncInputPositions2).call(this);
	};

	var _resetHiddenField2 = function _resetHiddenField2(file) {
	  var input = this.hiddenFields.get(file.getId());

	  if (input) {
	    main_core.Dom.remove(input);
	    this.hiddenFields.delete(file.getId());
	  }
	};

	var _syncInputPositions2 = function _syncInputPositions2() {
	  var _this13 = this;

	  var container = this.getHiddenFieldsContainer();

	  if (!container) {
	    return;
	  }

	  this.getFiles().forEach(function (file) {
	    var input = _this13.hiddenFields.get(file.getId());

	    if (input) {
	      container.appendChild(input);
	    }
	  });
	};

	var isImage = function isImage(file) {
	  return /^image/.test(file.type);
	};



	var index = /*#__PURE__*/Object.freeze({
		formatFileSize: formatFileSize,
		getFileExtension: getFileExtension,
		getFilenameWithoutExtension: getFilenameWithoutExtension,
		getExtensionFromType: getExtensionFromType,
		getArrayBuffer: getArrayBuffer,
		isDataUri: isDataUri,
		isImage: isImage,
		isResizableImage: isResizableImage,
		getImageSize: getImageSize,
		resizeImage: resizeImage,
		loadImage: loadImage,
		isValidFileType: isValidFileType,
		canAppendFileToForm: canAppendFileToForm,
		assignFileToInput: assignFileToInput,
		createFileFromBlob: createFileFromBlob,
		createBlobFromDataUri: createBlobFromDataUri,
		createUniqueId: createUniqueId,
		createWorker: createWorker
	});

	exports.Uploader = Uploader;
	exports.UploaderStatus = UploaderStatus;
	exports.FileStatus = FileStatus;
	exports.FileOrigin = FileOrigin;
	exports.FilterType = FilterType;
	exports.Helpers = index;

}((this.BX.UI.FileUploader = this.BX.UI.FileUploader || {}),BX.Event,BX));
//# sourceMappingURL=ui.file-uploader.bundle.js.map
