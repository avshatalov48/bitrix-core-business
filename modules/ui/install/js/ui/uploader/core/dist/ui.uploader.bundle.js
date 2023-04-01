this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,ui_uploader_core,ui_vue3,main_core) {
	'use strict';

	const FileStatus = {
	  INIT: 'init',
	  ADDED: 'added',
	  LOADING: 'loading',
	  PENDING: 'pending',
	  UPLOADING: 'uploading',
	  COMPLETE: 'complete',
	  //REMOVING: 'removing',
	  //REMOVE_FAILED: 'remove-failed',
	  LOAD_FAILED: 'load-failed',
	  UPLOAD_FAILED: 'upload-failed'
	};

	const FileOrigin = {
	  CLIENT: 'client',
	  SERVER: 'server'
	};

	const FileEvent = {
	  ADD: 'onAdd',
	  BEFORE_UPLOAD: 'onBeforeUpload',
	  UPLOAD_START: 'onUploadStart',
	  UPLOAD_ERROR: 'onUploadError',
	  UPLOAD_PROGRESS: 'onUploadProgress',
	  UPLOAD_COMPLETE: 'onUploadComplete',
	  UPLOAD_CONTROLLER_INIT: 'onUploadControllerInit',
	  LOAD_START: 'onLoadStart',
	  LOAD_PROGRESS: 'onLoadProgress',
	  LOAD_COMPLETE: 'onLoadComplete',
	  LOAD_ERROR: 'onLoadError',
	  LOAD_CONTROLLER_INIT: 'onLoadControllerInit',
	  REMOVE_ERROR: 'onRemoveError',
	  REMOVE_COMPLETE: 'onRemoveComplete',
	  REMOVE_CONTROLLER_INIT: 'onRemoveControllerInit',
	  STATE_CHANGE: 'onStateChange',
	  STATUS_CHANGE: 'onStatusChange',
	  PREPARE_FILE_ASYNC: 'onPrepareFileAsync'
	};

	class UploaderError extends main_core.BaseError {
	  constructor(code, ...args) {
	    let message = main_core.Type.isString(args[0]) ? args[0] : null;
	    let description = main_core.Type.isString(args[1]) ? args[1] : null;
	    const customData = main_core.Type.isPlainObject(args[args.length - 1]) ? args[args.length - 1] : {};
	    const replacements = {};
	    Object.keys(customData).forEach(key => {
	      replacements[`#${key}#`] = customData[key];
	    });

	    if (!main_core.Type.isString(message) && main_core.Loc.hasMessage(`UPLOADER_${code}`)) {
	      message = main_core.Loc.getMessage(`UPLOADER_${code}`, replacements);
	    }

	    if (main_core.Type.isStringFilled(message) && !main_core.Type.isString(description) && main_core.Loc.hasMessage(`UPLOADER_${code}_DESC`)) {
	      description = main_core.Loc.getMessage(`UPLOADER_${code}_DESC`, replacements);
	    }

	    super(message, code, customData);
	    this.description = '';
	    this.origin = UploaderError.Origin.CLIENT;
	    this.type = UploaderError.Type.USER;
	    this.setDescription(description);
	  }

	  static createFromAjaxErrors(errors) {
	    if (!main_core.Type.isArrayFilled(errors) || !main_core.Type.isPlainObject(errors[0])) {
	      return new this('SERVER_ERROR');
	    }

	    const uploaderError = errors.find(error => {
	      return error.type === 'file-uploader';
	    });

	    if (uploaderError && !uploaderError.system) {
	      // Take the First Uploader User Error
	      const {
	        code,
	        message,
	        description,
	        customData
	      } = uploaderError;
	      const error = new this(code, message, description, customData);
	      error.setOrigin(UploaderError.Origin.SERVER);
	      error.setType(UploaderError.Type.USER);
	      return error;
	    } else {
	      let {
	        code,
	        message,
	        description
	      } = errors[0];
	      const {
	        customData,
	        system,
	        type
	      } = errors[0];

	      if (code === 'NETWORK_ERROR') {
	        message = main_core.Loc.getMessage('UPLOADER_NETWORK_ERROR');
	      } else {
	        code = main_core.Type.isStringFilled(code) ? code : 'SERVER_ERROR';

	        if (!main_core.Type.isStringFilled(description)) {
	          description = message;
	          message = main_core.Loc.getMessage('UPLOADER_SERVER_ERROR');
	        }
	      }

	      console.error('Uploader', errors);
	      const error = new this(code, message, description, customData);
	      error.setOrigin(UploaderError.Origin.SERVER);

	      if (type === 'file-uploader') {
	        error.setType(system ? UploaderError.Type.SYSTEM : UploaderError.Type.USER);
	      } else {
	        error.setType(UploaderError.Type.UNKNOWN);
	      }

	      return error;
	    }
	  }

	  static createFromError(error) {
	    return new this(error.name, error.message);
	  }

	  getDescription() {
	    return this.description;
	  }

	  setDescription(text) {
	    if (main_core.Type.isString(text)) {
	      this.description = text;
	    }

	    return this;
	  }

	  getOrigin() {
	    return this.origin;
	  }

	  setOrigin(origin) {
	    if (Object.values(UploaderError.Origin).includes(origin)) {
	      this.origin = origin;
	    }

	    return this;
	  }

	  getType() {
	    return this.type;
	  }

	  setType(type) {
	    if (main_core.Type.isStringFilled(type)) {
	      this.type = type;
	    }

	    return this;
	  }

	  clone() {
	    const options = JSON.parse(JSON.stringify(this));
	    const error = new UploaderError(options.code, options.message, options.description, options.customData);
	    error.setOrigin(options.origin);
	    error.setType(options.type);
	    return error;
	  }

	  toString() {
	    return `Uploader Error (${this.getCode()}): ${this.getMessage()} (${this.getOrigin()})`;
	  }

	  toJSON() {
	    return {
	      code: this.getCode(),
	      message: this.getMessage(),
	      description: this.getDescription(),
	      origin: this.getOrigin(),
	      type: this.getType(),
	      customData: this.getCustomData()
	    };
	  }

	}
	UploaderError.Origin = {
	  SERVER: 'server',
	  CLIENT: 'client'
	};
	UploaderError.Type = {
	  USER: 'user',
	  SYSTEM: 'system',
	  UNKNOWN: 'unknown'
	};

	var _server = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("server");

	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");

	class AbstractUploadController extends main_core_events.EventEmitter {
	  constructor(server, options = {}) {
	    super();
	    Object.defineProperty(this, _server, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.UI.Uploader.UploadController');
	    babelHelpers.classPrivateFieldLooseBase(this, _server)[_server] = server;
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	  }

	  getServer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _server)[_server];
	  }

	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _options)[_options];
	  }

	  getOption(option, defaultValue) {
	    if (!main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options][option])) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _options)[_options][option];
	    } else if (!main_core.Type.isUndefined(defaultValue)) {
	      return defaultValue;
	    }

	    return null;
	  }

	  upload(file) {
	    throw new Error('You must implement upload() method.');
	  }

	  abort() {
	    throw new Error('You must implement abort() method.');
	  }

	}

	var _server$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("server");

	var _options$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");

	class AbstractLoadController extends main_core_events.EventEmitter {
	  constructor(server, options = {}) {
	    super();
	    Object.defineProperty(this, _server$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options$1, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.UI.Uploader.LoadController');
	    babelHelpers.classPrivateFieldLooseBase(this, _server$1)[_server$1] = server;
	    babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1] = options;
	  }

	  getServer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _server$1)[_server$1];
	  }

	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1];
	  }

	  getOption(option, defaultValue) {
	    if (!main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1][option])) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1][option];
	    } else if (!main_core.Type.isUndefined(defaultValue)) {
	      return defaultValue;
	    }

	    return null;
	  }

	  load(file) {
	    throw new Error('You must implement load() method.');
	  }

	  abort() {
	    throw new Error('You must implement abort() method.');
	  }

	}

	var _server$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("server");

	var _options$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");

	class AbstractRemoveController extends main_core_events.EventEmitter {
	  constructor(server, options = {}) {
	    super();
	    Object.defineProperty(this, _server$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options$2, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.UI.Uploader.RemoveController');
	    babelHelpers.classPrivateFieldLooseBase(this, _server$2)[_server$2] = server;
	    babelHelpers.classPrivateFieldLooseBase(this, _options$2)[_options$2] = options;
	  }

	  getServer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _server$2)[_server$2];
	  }

	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _options$2)[_options$2];
	  }

	  getOption(option, defaultValue) {
	    if (!Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _options$2)[_options$2][option])) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _options$2)[_options$2][option];
	    } else if (!Type.isUndefined(defaultValue)) {
	      return defaultValue;
	    }

	    return null;
	  }

	  remove(file) {
	    throw new Error('You must implement remove() method.');
	  }

	}

	let crypto = window.crypto || window.msCrypto;

	if (!crypto && typeof process === 'object') {
	  crypto = require('crypto').webcrypto;
	}

	const createUniqueId = () => {
	  return `${1e7}-${1e3}-${4e3}-${8e3}-${1e11}`.replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
	};

	const getExtensionFromType = type => {
	  if (!main_core.Type.isStringFilled(type)) {
	    return '';
	  }

	  const subtype = type.split('/').pop();

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

	let counter = 0;

	const createFileFromBlob = (blob, fileName) => {
	  if (!main_core.Type.isStringFilled(fileName)) {
	    const date = new Date();
	    fileName = `File ${date.getFullYear()}-${date.getMonth()}-${date.getDate()}-${++counter}`;
	    const extension = getExtensionFromType(blob.type);

	    if (extension) {
	      fileName += `.${extension}`;
	    }
	  }

	  try {
	    return new File([blob], fileName, {
	      lastModified: Date.now(),
	      lastModifiedDate: new Date(),
	      type: blob.type
	    });
	  } catch (exception) {
	    const file = blob.slice(0, blob.size, blob.type);
	    file.name = fileName;
	    file.lastModified = Date.now();
	    file.lastModifiedDate = new Date();
	    return file;
	  }
	};

	const regexp = /^data:((?:\w+\/(?:(?!;).)+)?)((?:;[\w\W]*?[^;])*),(.+)$/;

	const isDataUri = str => {
	  return typeof str === 'string' ? str.match(regexp) : false;
	};

	const createBlobFromDataUri = dataURI => {
	  const byteString = atob(dataURI.split(',')[1]);
	  const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
	  const buffer = new ArrayBuffer(byteString.length);
	  const view = new Uint8Array(buffer);

	  for (let i = 0; i < byteString.length; i++) {
	    view[i] = byteString.charCodeAt(i);
	  }

	  return new Blob([buffer], {
	    type: mimeString
	  });
	};

	const getFileExtension = filename => {
	  const position = main_core.Type.isStringFilled(filename) ? filename.lastIndexOf('.') : -1;
	  return position > 0 ? filename.substring(position + 1) : '';
	};

	const imageExtensions = ['jpg', 'bmp', 'jpeg', 'jpe', 'gif', 'png', 'webp'];

	const isResizableImage = (file, mimeType = null) => {
	  const fileName = main_core.Type.isFile(file) ? file.name : file;
	  const type = main_core.Type.isFile(file) ? file.type : mimeType;
	  const extension = getFileExtension(fileName).toLowerCase();

	  if (imageExtensions.includes(extension)) {
	    if (type === null || /^image\/[a-z0-9.-]+$/i.test(type)) {
	      return true;
	    }
	  }

	  return false;
	};

	const formatFileSize = (size, base = 1024) => {
	  let i = 0;
	  const units = getUnits();

	  while (size >= base && units[i + 1]) {
	    size /= base;
	    i++;
	  }

	  return (main_core.Type.isInteger(size) ? size : size.toFixed(1)) + units[i];
	};

	let fileSizeUnits = null;

	const getUnits = () => {
	  if (fileSizeUnits !== null) {
	    return fileSizeUnits;
	  }

	  const units = main_core.Loc.getMessage('UPLOADER_FILE_SIZE_POSTFIXES').split(/[|]/);
	  fileSizeUnits = main_core.Type.isArrayFilled(units) ? units : ['B', 'kB', 'MB', 'GB', 'TB'];
	  return fileSizeUnits;
	};

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");

	var _file = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("file");

	var _serverId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("serverId");

	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");

	var _originalName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("originalName");

	var _size = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("size");

	var _type = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("type");

	var _width = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("width");

	var _height = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("height");

	var _clientPreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientPreview");

	var _clientPreviewUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientPreviewUrl");

	var _clientPreviewWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientPreviewWidth");

	var _clientPreviewHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientPreviewHeight");

	var _serverPreviewUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("serverPreviewUrl");

	var _serverPreviewWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("serverPreviewWidth");

	var _serverPreviewHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("serverPreviewHeight");

	var _downloadUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("downloadUrl");

	var _removeUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeUrl");

	var _status = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("status");

	var _origin = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("origin");

	var _errors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errors");

	var _progress = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("progress");

	var _uploadController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadController");

	var _loadController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadController");

	var _removeController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeController");

	var _uploadCallbacks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadCallbacks");

	var _setStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setStatus");

	class UploaderFile extends main_core_events.EventEmitter {
	  constructor(source, fileOptions = {}) {
	    super();
	    Object.defineProperty(this, _setStatus, {
	      value: _setStatus2
	    });
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _file, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _serverId, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _originalName, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _size, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _type, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _width, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _height, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _clientPreview, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _clientPreviewUrl, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _clientPreviewWidth, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _clientPreviewHeight, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _serverPreviewUrl, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _serverPreviewWidth, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _serverPreviewHeight, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _downloadUrl, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _removeUrl, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _status, {
	      writable: true,
	      value: FileStatus.INIT
	    });
	    Object.defineProperty(this, _origin, {
	      writable: true,
	      value: FileOrigin.CLIENT
	    });
	    Object.defineProperty(this, _errors, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _progress, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _uploadController, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _loadController, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _removeController, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploadCallbacks, {
	      writable: true,
	      value: new CallbackCollection(this)
	    });
	    this.setEventNamespace('BX.UI.Uploader.File');
	    const options = main_core.Type.isPlainObject(fileOptions) ? fileOptions : {};

	    if (main_core.Type.isFile(source)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _file)[_file] = source;
	    } else if (main_core.Type.isBlob(source)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _file)[_file] = createFileFromBlob(source, options.name || source.name);
	    } else if (isDataUri(source)) {
	      const blob = createBlobFromDataUri(source);
	      babelHelpers.classPrivateFieldLooseBase(this, _file)[_file] = createFileFromBlob(blob, options.name);
	    } else if (main_core.Type.isNumber(source) || main_core.Type.isStringFilled(source)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _origin)[_origin] = FileOrigin.SERVER;
	      babelHelpers.classPrivateFieldLooseBase(this, _serverId)[_serverId] = source;

	      if (main_core.Type.isPlainObject(options)) {
	        this.setFile(options);
	      }
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = main_core.Type.isStringFilled(options.id) ? options.id : createUniqueId();
	    this.subscribeFromOptions({
	      [FileEvent.ADD]: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.ADDED);
	      }
	    });
	    this.subscribeFromOptions(options.events);
	  }

	  load() {
	    if (!this.canLoad()) {
	      return;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.LOADING);

	    this.emit(FileEvent.LOAD_START);

	    babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController].load(this);
	  }

	  upload(callbacks = {}) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadCallbacks)[_uploadCallbacks].subscribe(callbacks);

	    if (this.isComplete() && this.isUploadable()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _uploadCallbacks)[_uploadCallbacks].emit('onComplete');
	    } else if (this.isUploadFailed()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _uploadCallbacks)[_uploadCallbacks].emit('onError', {
	        error: this.getError()
	      });
	    } else if (!this.canUpload()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _uploadCallbacks)[_uploadCallbacks].emit('onError', {
	        error: new UploaderError('FILE_UPLOAD_NOT_ALLOWED')
	      });
	    }

	    const event = new main_core_events.BaseEvent({
	      data: {
	        file: this
	      }
	    });
	    this.emit(FileEvent.BEFORE_UPLOAD, event);

	    if (event.isDefaultPrevented()) {
	      return;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.UPLOADING);

	    this.emit(FileEvent.UPLOAD_START);

	    babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController].upload(this);
	  }

	  remove() {
	    if (this.getStatus() === FileStatus.INIT) {
	      return;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.INIT);

	    this.emit(FileEvent.REMOVE_COMPLETE);
	    this.abort(); //this.#setStatus(FileStatus.REMOVING);
	    //this.#removeController.remove(this);

	    if (babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController] !== null && this.getOrigin() === FileOrigin.CLIENT) {
	      babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController].remove(this);
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController] = null;
	  } // stop(): void
	  // {
	  // 	if (this.isUploading())
	  // 	{
	  // 		this.abort();
	  // 		this.setStatus(FileStatus.PENDING);
	  // 	}
	  // }
	  //
	  // resume(): void
	  // {
	  //
	  // }
	  // retry(): void
	  // {
	  // 	// TODO
	  // }


	  abort() {
	    if (this.isLoading()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.LOAD_FAILED);

	      const error = new UploaderError('FILE_LOAD_ABORTED');
	      this.emit(FileEvent.LOAD_ERROR, {
	        error
	      });
	    } else if (this.isUploading()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.UPLOAD_FAILED);

	      const error = new UploaderError('FILE_UPLOAD_ABORTED');
	      this.emit('onUploadError', {
	        error
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadCallbacks)[_uploadCallbacks].emit('onError', {
	        error
	      });
	    }

	    if (babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController].abort();
	    }

	    if (babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController].abort();
	    }
	  }

	  getUploadController() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController];
	  }

	  setUploadController(controller) {
	    if (this.getOrigin() === FileOrigin.SERVER) {
	      return;
	    }

	    if (!(controller instanceof AbstractUploadController) && !main_core.Type.isNull(controller)) {
	      return;
	    }

	    const changed = babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController] !== controller;
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController] = controller;

	    if (babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController] && changed) {
	      babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController].subscribeOnce('onError', event => {
	        const error = this.addError(event.getData().error);

	        babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.UPLOAD_FAILED);

	        this.emit(FileEvent.UPLOAD_ERROR, {
	          error
	        });

	        babelHelpers.classPrivateFieldLooseBase(this, _uploadCallbacks)[_uploadCallbacks].emit('onError', {
	          error
	        });
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController].subscribe('onProgress', event => {
	        const {
	          progress
	        } = event.getData();
	        this.setProgress(progress);
	        this.emit(FileEvent.UPLOAD_PROGRESS, {
	          progress
	        });
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController].subscribeOnce('onUpload', event => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.COMPLETE);

	        this.setFile(event.getData().fileInfo);
	        this.emit(FileEvent.UPLOAD_COMPLETE);

	        babelHelpers.classPrivateFieldLooseBase(this, _uploadCallbacks)[_uploadCallbacks].emit('onComplete');
	      });
	    }

	    if (changed) {
	      this.emit(FileEvent.UPLOAD_CONTROLLER_INIT, {
	        controller
	      });
	    }
	  }

	  setLoadController(controller) {
	    if (!(controller instanceof AbstractLoadController)) {
	      return;
	    }

	    const changed = babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController] !== controller;
	    babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController] = controller;

	    if (babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController] && changed) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController].subscribeOnce('onError', event => {
	        const error = this.addError(event.getData().error);

	        babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.LOAD_FAILED);

	        this.emit(FileEvent.LOAD_ERROR, {
	          error
	        });
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController].subscribe('onProgress', event => {
	        const {
	          progress
	        } = event.getData();
	        this.emit(FileEvent.LOAD_PROGRESS, {
	          progress
	        });
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController].subscribeOnce('onLoad', event => {
	        if (this.getOrigin() === FileOrigin.SERVER) {
	          this.setFile(event.getData().fileInfo);

	          babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.COMPLETE);

	          this.emit(FileEvent.LOAD_COMPLETE);
	        } else {
	          const event = new main_core_events.BaseEvent({
	            data: {
	              file: this
	            }
	          });
	          this.emitAsync(FileEvent.PREPARE_FILE_ASYNC, event).then(() => {
	            if (this.isUploadable()) {
	              babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.PENDING);
	            } else {
	              babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.COMPLETE);
	            }

	            this.emit(FileEvent.LOAD_COMPLETE);
	          }).catch(error => {
	            error = this.addError(error);

	            babelHelpers.classPrivateFieldLooseBase(this, _setStatus)[_setStatus](FileStatus.LOAD_FAILED);

	            this.emit(FileEvent.LOAD_ERROR, {
	              error
	            });
	          });
	        }
	      });
	    }

	    if (changed) {
	      this.emit(FileEvent.LOAD_CONTROLLER_INIT, {
	        controller
	      });
	    }
	  }

	  setRemoveController(controller) {
	    if (!(controller instanceof AbstractRemoveController) && !main_core.Type.isNull(controller)) {
	      return;
	    }

	    const changed = babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController] !== controller;
	    babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController] = controller;

	    if (babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController] && changed) {
	      babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController].subscribeOnce('onError', event => {//const error = this.addError(event.getData().error);
	        //this.emit(FileEvent.REMOVE_ERROR, { error });
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _removeController)[_removeController].subscribeOnce('onRemove', event => {//this.#setStatus(FileStatus.INIT);
	        //this.emit(FileEvent.REMOVE_COMPLETE);
	      });
	    }

	    if (changed) {
	      this.emit(FileEvent.REMOVE_CONTROLLER_INIT, {
	        controller
	      });
	    }
	  }

	  isReadyToUpload() {
	    return this.getStatus() === FileStatus.PENDING;
	  }

	  isUploadable() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploadController)[_uploadController] !== null;
	  }

	  isLoadable() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadController)[_loadController] !== null;
	  }

	  canUpload() {
	    return this.isReadyToUpload() && this.isUploadable();
	  }

	  canLoad() {
	    return this.getStatus() === FileStatus.ADDED && this.isLoadable();
	  }

	  isUploading() {
	    return this.getStatus() === FileStatus.UPLOADING;
	  }

	  isLoading() {
	    return this.getStatus() === FileStatus.LOADING;
	  }

	  isComplete() {
	    return this.getStatus() === FileStatus.COMPLETE;
	  }

	  isFailed() {
	    return this.getStatus() === FileStatus.LOAD_FAILED || this.getStatus() === FileStatus.UPLOAD_FAILED;
	  }

	  isLoadFailed() {
	    return this.getStatus() === FileStatus.LOAD_FAILED;
	  }

	  isUploadFailed() {
	    return this.getStatus() === FileStatus.UPLOAD_FAILED;
	  }

	  getBinary() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _file)[_file];
	  }
	  /**
	   * @internal
	   */


	  setFile(file) {
	    if (main_core.Type.isFile(file)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _file)[_file] = file;
	    } else if (main_core.Type.isBlob(file)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _file)[_file] = createFileFromBlob(file, this.getName());
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

	  getName() {
	    return this.getBinary() ? this.getBinary().name : babelHelpers.classPrivateFieldLooseBase(this, _name)[_name];
	  }
	  /**
	   * @internal
	   */


	  setName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = name;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'name',
	        value: name
	      });
	    }
	  }

	  getOriginalName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _originalName)[_originalName] ? babelHelpers.classPrivateFieldLooseBase(this, _originalName)[_originalName] : this.getName();
	  }
	  /**
	   * @internal
	   */


	  setOriginalName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _originalName)[_originalName] = name;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'originalName',
	        value: name
	      });
	    }
	  }

	  getExtension() {
	    const name = this.getOriginalName();
	    const position = name.lastIndexOf('.');
	    return position >= 0 ? name.substring(position + 1).toLowerCase() : '';
	  }

	  getType() {
	    return this.getBinary() ? this.getBinary().type : babelHelpers.classPrivateFieldLooseBase(this, _type)[_type];
	  }
	  /**
	   * internal
	   */


	  setType(type) {
	    if (main_core.Type.isStringFilled(type)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _type)[_type] = type;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'type',
	        value: type
	      });
	    }
	  }

	  getSize() {
	    return this.getBinary() ? this.getBinary().size : babelHelpers.classPrivateFieldLooseBase(this, _size)[_size];
	  }

	  getSizeFormatted() {
	    return formatFileSize(this.getSize());
	  }
	  /**
	   * @internal
	   */


	  setSize(size) {
	    if (main_core.Type.isNumber(size) && size >= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _size)[_size] = size;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'size',
	        value: size
	      });
	    }
	  }

	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }

	  getServerId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _serverId)[_serverId];
	  }

	  setServerId(id) {
	    if (main_core.Type.isNumber(id) || main_core.Type.isStringFilled(id)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _serverId)[_serverId] = id;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'serverId',
	        value: id
	      });
	    }
	  }

	  getStatus() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status];
	  }

	  getOrigin() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _origin)[_origin];
	  }

	  getDownloadUrl() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _downloadUrl)[_downloadUrl];
	  }

	  setDownloadUrl(url) {
	    if (main_core.Type.isStringFilled(url)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _downloadUrl)[_downloadUrl] = url;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'downloadUrl',
	        value: url
	      });
	    }
	  }

	  getRemoveUrl() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _removeUrl)[_removeUrl];
	  }

	  setRemoveUrl(url) {
	    if (main_core.Type.isStringFilled(url)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _removeUrl)[_removeUrl] = url;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'removeUrl',
	        value: url
	      });
	    }
	  }

	  getWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _width)[_width];
	  }

	  setWidth(width) {
	    if (main_core.Type.isNumber(width)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _width)[_width] = width;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'width',
	        value: width
	      });
	    }
	  }

	  getHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _height)[_height];
	  }

	  setHeight(height) {
	    if (main_core.Type.isNumber(height)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _height)[_height] = height;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'height',
	        value: height
	      });
	    }
	  }

	  getPreviewUrl() {
	    return this.getClientPreview() ? this.getClientPreviewUrl() : this.getServerPreviewUrl();
	  }

	  getPreviewWidth() {
	    return this.getClientPreview() ? this.getClientPreviewWidth() : this.getServerPreviewWidth();
	  }

	  getPreviewHeight() {
	    return this.getClientPreview() ? this.getClientPreviewHeight() : this.getServerPreviewHeight();
	  }

	  getClientPreview() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _clientPreview)[_clientPreview];
	  }

	  setClientPreview(file, width = null, height = null) {
	    if (main_core.Type.isBlob(file) || main_core.Type.isNull(file)) {
	      this.revokeClientPreviewUrl();
	      const url = main_core.Type.isNull(file) ? null : URL.createObjectURL(file);
	      babelHelpers.classPrivateFieldLooseBase(this, _clientPreview)[_clientPreview] = file;
	      babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewUrl)[_clientPreviewUrl] = url;
	      babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewWidth)[_clientPreviewWidth] = width;
	      babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewHeight)[_clientPreviewHeight] = height;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'clientPreviewUrl',
	        value: url
	      });
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'clientPreviewWidth',
	        value: width
	      });
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'clientPreviewHeight',
	        value: height
	      });
	    }
	  }

	  getClientPreviewUrl() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewUrl)[_clientPreviewUrl];
	  }

	  revokeClientPreviewUrl() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewUrl)[_clientPreviewUrl] !== null) {
	      URL.revokeObjectURL(babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewUrl)[_clientPreviewUrl]);
	      babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewUrl)[_clientPreviewUrl] = null;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'clientPreviewUrl',
	        value: null
	      });
	    }
	  }

	  getClientPreviewWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewWidth)[_clientPreviewWidth];
	  }

	  getClientPreviewHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _clientPreviewHeight)[_clientPreviewHeight];
	  }

	  getServerPreviewUrl() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _serverPreviewUrl)[_serverPreviewUrl];
	  }

	  setServerPreview(url, width = null, height = null) {
	    if (main_core.Type.isStringFilled(url) || main_core.Type.isNull(url)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _serverPreviewUrl)[_serverPreviewUrl] = url;
	      babelHelpers.classPrivateFieldLooseBase(this, _serverPreviewWidth)[_serverPreviewWidth] = width;
	      babelHelpers.classPrivateFieldLooseBase(this, _serverPreviewHeight)[_serverPreviewHeight] = height;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'serverPreviewUrl',
	        value: url
	      });
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'serverPreviewWidth',
	        value: width
	      });
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'serverPreviewHeight',
	        value: height
	      });
	    }
	  }

	  getServerPreviewWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _serverPreviewWidth)[_serverPreviewWidth];
	  }

	  getServerPreviewHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _serverPreviewHeight)[_serverPreviewHeight];
	  }

	  isImage() {
	    return isResizableImage(this.getOriginalName(), this.getType());
	  }

	  getProgress() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _progress)[_progress];
	  }

	  setProgress(progress) {
	    if (main_core.Type.isNumber(progress)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _progress)[_progress] = progress;
	      this.emit(FileEvent.STATE_CHANGE, {
	        property: 'progress',
	        value: progress
	      });
	    }
	  }

	  addError(error) {
	    if (error instanceof Error) {
	      error = UploaderError.createFromError(error);
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors].push(error);

	    this.emit(FileEvent.STATE_CHANGE);
	    return error;
	  }

	  getError() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors][0] || null;
	  }

	  getErrors() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors];
	  }

	  getState() {
	    return JSON.parse(JSON.stringify(this));
	  }

	  toJSON() {
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
	      failed: this.isFailed(),
	      width: this.getWidth(),
	      height: this.getHeight(),
	      progress: this.getProgress(),
	      error: this.getError(),
	      errors: this.getErrors(),
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

	}

	function _setStatus2(status) {
	  babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = status;
	  this.emit(FileEvent.STATE_CHANGE, {
	    property: 'status',
	    value: status
	  });
	  this.emit(FileEvent.STATUS_CHANGE);
	}

	var _emitter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitter");

	class CallbackCollection {
	  constructor(file) {
	    Object.defineProperty(this, _emitter, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter] = new main_core_events.EventEmitter(file, 'BX.UI.Uploader.File.UploadCallbacks');
	  }

	  subscribe(callbacks = {}) {
	    callbacks = main_core.Type.isPlainObject(callbacks) ? callbacks : {};

	    if (main_core.Type.isFunction(callbacks.onComplete)) {
	      this.getEmitter().subscribeOnce('onComplete', callbacks.onComplete);
	    }

	    if (main_core.Type.isFunction(callbacks.onError)) {
	      this.getEmitter().subscribeOnce('onError', callbacks.onError);
	    }
	  }

	  emit(eventName, event) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].emit(eventName, event);

	      babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter].unsubscribeAll();
	    }
	  }

	  getEmitter() {
	    if (main_core.Type.isNull(babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter] = new main_core_events.EventEmitter(this, 'BX.UI.Uploader.File.UploadCallbacks');
	    }

	    return babelHelpers.classPrivateFieldLooseBase(this, _emitter)[_emitter];
	  }

	}

	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");

	var _offset = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("offset");

	var _retries = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("retries");

	class Chunk {
	  constructor(data, offset) {
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _offset, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _retries, {
	      writable: true,
	      value: []
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = data;
	    babelHelpers.classPrivateFieldLooseBase(this, _offset)[_offset] = offset;
	  }

	  getNextRetryDelay() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _retries)[_retries].length === 0) {
	      return null;
	    }

	    return babelHelpers.classPrivateFieldLooseBase(this, _retries)[_retries].shift();
	  }

	  setRetries(retries) {
	    if (main_core.Type.isArray(retries)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _retries)[_retries] = retries;
	    }
	  }

	  getData() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _data)[_data];
	  }

	  getOffset() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _offset)[_offset];
	  }

	  getSize() {
	    return this.getData().size;
	  }

	}

	var _file$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("file");

	var _chunkOffset = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chunkOffset");

	var _chunkTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chunkTimeout");

	var _token = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("token");

	var _xhr = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("xhr");

	var _aborted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("aborted");

	var _uploadChunk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadChunk");

	var _retryUploadChunk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("retryUploadChunk");

	var _getNextChunk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNextChunk");

	class UploadController extends AbstractUploadController {
	  constructor(server, options = {}) {
	    super(server, options);
	    Object.defineProperty(this, _getNextChunk, {
	      value: _getNextChunk2
	    });
	    Object.defineProperty(this, _retryUploadChunk, {
	      value: _retryUploadChunk2
	    });
	    Object.defineProperty(this, _uploadChunk, {
	      value: _uploadChunk2
	    });
	    Object.defineProperty(this, _file$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _chunkOffset, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _chunkTimeout, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _token, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _xhr, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _aborted, {
	      writable: true,
	      value: false
	    });
	  }

	  upload(file) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _chunkOffset)[_chunkOffset] !== null) {
	      return;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _file$1)[_file$1] = file;

	    const nextChunk = babelHelpers.classPrivateFieldLooseBase(this, _getNextChunk)[_getNextChunk]();

	    if (nextChunk) {
	      babelHelpers.classPrivateFieldLooseBase(this, _uploadChunk)[_uploadChunk](nextChunk);
	    }
	  }

	  abort() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _xhr)[_xhr]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _aborted)[_aborted] = true;

	      babelHelpers.classPrivateFieldLooseBase(this, _xhr)[_xhr].abort();

	      babelHelpers.classPrivateFieldLooseBase(this, _xhr)[_xhr] = null;
	    }

	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _chunkTimeout)[_chunkTimeout]);
	  }

	  getFile() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _file$1)[_file$1];
	  }

	  getChunkSize() {
	    return this.getServer().getChunkSize();
	  }

	  getChunkOffset() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chunkOffset)[_chunkOffset];
	  }

	  getToken() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _token)[_token];
	  }

	  setToken(token) {
	    if (main_core.Type.isStringFilled(token)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _token)[_token] = token;
	    }
	  }

	}

	function _uploadChunk2(chunk) {
	  const totalSize = this.getFile().getSize();
	  const isOnlyOneChunk = chunk.getOffset() === 0 && totalSize === chunk.getSize();
	  let fileName = this.getFile().getName();

	  if (fileName.normalize) {
	    fileName = fileName.normalize();
	  }

	  const type = main_core.Type.isStringFilled(this.getFile().getType()) ? this.getFile().getType() : 'application/octet-stream';
	  const headers = [{
	    name: 'Content-Type',
	    value: type
	  }, {
	    name: 'X-Upload-Content-Name',
	    value: encodeURIComponent(fileName)
	  }];

	  if (!isOnlyOneChunk) {
	    const rangeStart = chunk.getOffset();
	    const rangeEnd = chunk.getOffset() + chunk.getSize() - 1;
	    const rangeHeader = `bytes ${rangeStart}-${rangeEnd}/${totalSize}`;
	    headers.push({
	      name: 'Content-Range',
	      value: rangeHeader
	    });
	  }

	  const controllerOptions = this.getServer().getControllerOptions();
	  main_core.ajax.runAction('ui.fileuploader.upload', {
	    headers,
	    data: chunk.getData(),
	    preparePost: false,
	    getParameters: {
	      controller: this.getServer().getController(),
	      controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null,
	      token: this.getToken() || ''
	    },
	    onrequeststart: xhr => {
	      babelHelpers.classPrivateFieldLooseBase(this, _xhr)[_xhr] = xhr;
	      babelHelpers.classPrivateFieldLooseBase(this, _aborted)[_aborted] = false;
	    },
	    onprogressupload: event => {
	      if (event.lengthComputable) {
	        const size = this.getFile().getSize();
	        const uploadedBytes = Math.min(size, chunk.getOffset() + event.loaded);
	        const progress = size > 0 ? Math.floor(uploadedBytes / size * 100) : 100;
	        this.emit('onProgress', {
	          progress
	        });
	      }
	    }
	  }).then(response => {
	    if (response.data.token) {
	      this.setToken(response.data.token);

	      if (this.getFile().getServerId() === null) {
	        // Now we can remove a temp file on the backend
	        this.getFile().setServerId(response.data.token);
	      }

	      const size = this.getFile().getSize();
	      const progress = size > 0 ? Math.floor((chunk.getOffset() + chunk.getSize()) / size * 100) : 100;
	      this.emit('onProgress', {
	        progress
	      });

	      const nextChunk = babelHelpers.classPrivateFieldLooseBase(this, _getNextChunk)[_getNextChunk]();

	      if (nextChunk) {
	        babelHelpers.classPrivateFieldLooseBase(this, _uploadChunk)[_uploadChunk](nextChunk);
	      } else {
	        this.emit('onProgress', {
	          progress: 100
	        });
	        this.emit('onUpload', {
	          fileInfo: response.data.file
	        });
	      }
	    } else {
	      this.emit('onError', {
	        error: new UploaderError('SERVER_ERROR')
	      });
	    }
	  }).catch(response => {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _aborted)[_aborted]) {
	      return;
	    }

	    const error = UploaderError.createFromAjaxErrors(response.errors);
	    const shouldRetry = error.getCode() === 'NETWORK_ERROR' || error.getType() === UploaderError.Type.UNKNOWN;

	    if (!shouldRetry || !babelHelpers.classPrivateFieldLooseBase(this, _retryUploadChunk)[_retryUploadChunk](chunk)) {
	      this.emit('onError', {
	        error
	      });
	    }
	  });
	}

	function _retryUploadChunk2(chunk) {
	  const nextDelay = chunk.getNextRetryDelay();

	  if (nextDelay === null) {
	    return false;
	  }

	  clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _chunkTimeout)[_chunkTimeout]);
	  babelHelpers.classPrivateFieldLooseBase(this, _chunkTimeout)[_chunkTimeout] = setTimeout(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadChunk)[_uploadChunk](chunk);
	  }, nextDelay);
	  return true;
	}

	function _getNextChunk2() {
	  if (this.getChunkOffset() !== null && this.getChunkOffset() >= this.getFile().getSize()) {
	    // End of File
	    return null;
	  }

	  if (this.getChunkOffset() === null) {
	    // First call
	    babelHelpers.classPrivateFieldLooseBase(this, _chunkOffset)[_chunkOffset] = 0;
	  }

	  let chunk;

	  if (this.getChunkOffset() === 0 && this.getFile().getSize() <= this.getChunkSize()) {
	    chunk = new Chunk(this.getFile().getBinary(), this.getChunkOffset());
	    babelHelpers.classPrivateFieldLooseBase(this, _chunkOffset)[_chunkOffset] = this.getFile().getSize();
	  } else {
	    const currentChunkSize = Math.min(this.getChunkSize(), this.getFile().getSize() - this.getChunkOffset());
	    const nextOffset = this.getChunkOffset() + currentChunkSize;
	    const fileRange = this.getFile().getBinary().slice(this.getChunkOffset(), nextOffset);
	    chunk = new Chunk(fileRange, this.getChunkOffset());
	    babelHelpers.classPrivateFieldLooseBase(this, _chunkOffset)[_chunkOffset] = nextOffset;
	  }

	  chunk.setRetries([...this.getServer().getChunkRetryDelays()]);
	  return chunk;
	}

	const pendingQueues = new WeakMap();
	const loadingFiles = new WeakMap();
	function loadMultiple(controller, file) {
	  const server = controller.getServer();
	  const timeout = controller.getOption('timeout', 100);
	  let queue = pendingQueues.get(server);

	  if (!queue) {
	    queue = {
	      tasks: [],
	      load: main_core.Runtime.debounce(loadInternal, timeout, server),
	      xhr: null,
	      aborted: false
	    };
	    pendingQueues.set(server, queue);
	  }

	  queue.tasks.push({
	    controller,
	    file
	  });
	  queue.load();
	}
	function abort(controller, file) {
	  const server = controller.getServer();
	  const queue = pendingQueues.get(server);

	  if (queue) {
	    queue.tasks = queue.tasks.filter(task => {
	      return task.file !== file;
	    });

	    if (queue.tasks.length === 0) {
	      pendingQueues.delete(server);
	    }
	  } else {
	    const queue = loadingFiles.get(file);

	    if (queue) {
	      queue.tasks = queue.tasks.filter(task => {
	        return task.file !== file;
	      });
	      loadingFiles.delete(file);

	      if (queue.tasks.length === 0) {
	        queue.aborted = true;
	        queue.xhr.abort();
	      }
	    }
	  }
	}

	function loadInternal() {
	  const server = this;
	  const queue = pendingQueues.get(server);

	  if (!queue) {
	    return;
	  }

	  pendingQueues.delete(server);

	  if (queue.tasks.length === 0) {
	    return;
	  }

	  const fileIds = [];
	  queue.tasks.forEach(task => {
	    const file = task.file;
	    fileIds.push(file.getServerId());
	    loadingFiles.set(file, queue);
	  });
	  const controllerOptions = server.getControllerOptions();
	  main_core.ajax.runAction('ui.fileuploader.load', {
	    data: {
	      fileIds: fileIds
	    },
	    getParameters: {
	      controller: server.getController(),
	      controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null
	    },
	    onrequeststart: xhr => {
	      queue.xhr = xhr;
	    },
	    onprogress: event => {
	      if (event.lengthComputable) {
	        const progress = event.total > 0 ? Math.floor(event.loaded / event.total * 100) : 100;
	        queue.tasks.forEach(task => {
	          const {
	            controller,
	            file
	          } = task;
	          controller.emit('onProgress', {
	            file,
	            progress
	          });
	        });
	      }
	    }
	  }).then(response => {
	    var _response$data;

	    if ((_response$data = response.data) != null && _response$data.files) {
	      const fileResults = {};
	      response.data.files.forEach(fileResult => {
	        fileResults[fileResult.id] = fileResult;
	      });
	      queue.tasks.forEach(task => {
	        const {
	          controller,
	          file
	        } = task;
	        const fileResult = fileResults[file.getServerId()] || null;
	        loadingFiles.delete(file);

	        if (fileResult && fileResult.success) {
	          controller.emit('onProgress', {
	            file,
	            progress: 100
	          });
	          controller.emit('onLoad', {
	            fileInfo: fileResult.data.file
	          });
	        } else {
	          const error = UploaderError.createFromAjaxErrors(fileResult == null ? void 0 : fileResult.errors);
	          controller.emit('onError', {
	            error
	          });
	        }
	      });
	    } else {
	      const error = new UploaderError('SERVER_ERROR');
	      queue.tasks.forEach(task => {
	        const {
	          controller,
	          file
	        } = task;
	        loadingFiles.delete(file);
	        controller.emit('onError', {
	          error: error.clone()
	        });
	      });
	    }
	  }).catch(response => {
	    const error = queue.aborted ? null : UploaderError.createFromAjaxErrors(response.errors);
	    queue.tasks.forEach(task => {
	      const {
	        controller,
	        file
	      } = task;
	      loadingFiles.delete(file);

	      if (!queue.aborted) {
	        controller.emit('onError', {
	          error: error.clone()
	        });
	      }
	    });
	  });
	}

	var _file$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("file");

	class ServerLoadController extends AbstractLoadController {
	  constructor(server, options = {}) {
	    super(server, options);
	    Object.defineProperty(this, _file$2, {
	      writable: true,
	      value: null
	    });
	  }

	  load(file) {
	    if (this.getServer().getController()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _file$2)[_file$2] = file;
	      loadMultiple(this, file);
	    } else {
	      this.emit('onProgress', {
	        file,
	        progress: 100
	      });
	      this.emit('onLoad', {
	        fileInfo: null
	      });
	    }
	  }

	  abort() {
	    if (this.getServer().getController() && babelHelpers.classPrivateFieldLooseBase(this, _file$2)[_file$2]) {
	      abort(this, babelHelpers.classPrivateFieldLooseBase(this, _file$2)[_file$2]);
	    }
	  }

	}

	class ClientLoadController extends AbstractLoadController {
	  constructor(server, options = {}) {
	    super(server, options);
	  }

	  load(file) {
	    if (main_core.Type.isFile(file.getBinary())) {
	      this.emit('onProgress', {
	        file,
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

	  abort() {}

	}

	const queues = new WeakMap();
	function removeMultiple(controller, file) {
	  const server = controller.getServer();
	  let queue = queues.get(server);

	  if (!queue) {
	    queue = {
	      tasks: [],
	      remove: main_core.Runtime.debounce(removeInternal, 1000, server),
	      xhr: null
	    };
	    queues.set(server, queue);
	  }

	  queue.tasks.push({
	    controller,
	    file
	  });
	  queue.remove();
	}

	function removeInternal() {
	  const server = this;
	  const queue = queues.get(server);

	  if (!queue) {
	    return;
	  }

	  const {
	    tasks
	  } = queue;
	  queues.delete(server);
	  const fileIds = [];
	  tasks.forEach(task => {
	    const file = task.file;

	    if (file.getServerId() !== null) {
	      fileIds.push(file.getServerId());
	    }
	  });

	  if (fileIds.length === 0) {
	    return;
	  }

	  const controllerOptions = server.getControllerOptions();
	  main_core.ajax.runAction('ui.fileuploader.remove', {
	    data: {
	      fileIds: fileIds
	    },
	    getParameters: {
	      controller: server.getController(),
	      controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null
	    },
	    onrequeststart: xhr => {
	      queue.xhr = xhr;
	    }
	  }).then(response => {
	    var _response$data;

	    if ((_response$data = response.data) != null && _response$data.files) {
	      const fileResults = {};
	      response.data.files.forEach(fileResult => {
	        fileResults[fileResult.id] = fileResult;
	      });
	      tasks.forEach(task => {
	        const {
	          controller,
	          file
	        } = task;
	        const fileResult = fileResults[file.getServerId()] || null;

	        if (fileResult && fileResult.success) {
	          controller.emit('onRemove', {
	            fileId: fileResult.id
	          });
	        } else {
	          const error = UploaderError.createFromAjaxErrors(fileResult == null ? void 0 : fileResult.errors);
	          controller.emit('onError', {
	            error
	          });
	        }
	      });
	    } else {
	      const error = new UploaderError('SERVER_ERROR');
	      tasks.forEach(task => {
	        const {
	          controller
	        } = task;
	        controller.emit('onError', {
	          error: error.clone()
	        });
	      });
	    }
	  }).catch(response => {
	    const error = UploaderError.createFromAjaxErrors(response.errors);
	    tasks.forEach(task => {
	      const {
	        controller
	      } = task;
	      controller.emit('onError', {
	        error: error.clone()
	      });
	    });
	  });
	}

	class RemoveController extends AbstractRemoveController {
	  constructor(server) {
	    super(server);
	  }

	  remove(file) {
	    removeMultiple(this, file);
	  }

	}

	var _controller = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("controller");

	var _controllerOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("controllerOptions");

	var _uploadControllerClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadControllerClass");

	var _uploadControllerOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadControllerOptions");

	var _loadControllerClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadControllerClass");

	var _loadControllerOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadControllerOptions");

	var _removeControllerClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeControllerClass");

	var _removeControllerOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeControllerOptions");

	var _chunkSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chunkSize");

	var _defaultChunkSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultChunkSize");

	var _chunkMinSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chunkMinSize");

	var _chunkMaxSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chunkMaxSize");

	var _chunkRetryDelays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chunkRetryDelays");

	var _calcChunkSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calcChunkSize");

	class Server {
	  constructor(serverOptions) {
	    Object.defineProperty(this, _calcChunkSize, {
	      value: _calcChunkSize2
	    });
	    Object.defineProperty(this, _controller, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _controllerOptions, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploadControllerClass, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploadControllerOptions, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _loadControllerClass, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _loadControllerOptions, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _removeControllerClass, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _removeControllerOptions, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _chunkSize, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _defaultChunkSize, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _chunkMinSize, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _chunkMaxSize, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _chunkRetryDelays, {
	      writable: true,
	      value: [1000, 3000, 6000]
	    });
	    const options = main_core.Type.isPlainObject(serverOptions) ? serverOptions : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller] = main_core.Type.isStringFilled(options.controller) ? options.controller : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _controllerOptions)[_controllerOptions] = main_core.Type.isPlainObject(options.controllerOptions) ? options.controllerOptions : null;

	    const _chunkSize2 = main_core.Type.isNumber(options.chunkSize) && options.chunkSize > 0 ? options.chunkSize : this.getDefaultChunkSize();

	    babelHelpers.classPrivateFieldLooseBase(this, _chunkSize)[_chunkSize] = options.forceChunkSize === true ? _chunkSize2 : babelHelpers.classPrivateFieldLooseBase(this, _calcChunkSize)[_calcChunkSize](_chunkSize2);

	    if (options.chunkRetryDelays === false || options.chunkRetryDelays === null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _chunkRetryDelays)[_chunkRetryDelays] = [];
	    } else if (main_core.Type.isArray(options.chunkRetryDelays)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _chunkRetryDelays)[_chunkRetryDelays] = options.chunkRetryDelays;
	    }

	    ['uploadControllerClass', 'loadControllerClass', 'removeControllerClass'].forEach(controllerClass => {
	      let fn = null;

	      if (main_core.Type.isStringFilled(options[controllerClass])) {
	        fn = main_core.Runtime.getClass(options[controllerClass]);

	        if (!main_core.Type.isFunction(fn)) {
	          throw new Error(`Uploader.Server: "${controllerClass}" must be a function.`);
	        }
	      } else if (main_core.Type.isFunction(options[controllerClass])) {
	        fn = options[controllerClass];
	      }

	      if (controllerClass === 'uploadControllerClass') {
	        babelHelpers.classPrivateFieldLooseBase(this, _uploadControllerClass)[_uploadControllerClass] = fn;
	      } else if (controllerClass === 'loadControllerClass') {
	        babelHelpers.classPrivateFieldLooseBase(this, _loadControllerClass)[_loadControllerClass] = fn;
	      } else if (controllerClass === 'removeControllerClass') {
	        babelHelpers.classPrivateFieldLooseBase(this, _removeControllerClass)[_removeControllerClass] = fn;
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _loadControllerOptions)[_loadControllerOptions] = main_core.Type.isPlainObject(options.loadControllerOptions) ? options.loadControllerOptions : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadControllerOptions)[_uploadControllerOptions] = main_core.Type.isPlainObject(options.uploadControllerOptions) ? options.uploadControllerOptions : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _removeControllerOptions)[_removeControllerOptions] = main_core.Type.isPlainObject(options.removeControllerOptions) ? options.removeControllerOptions : {};
	  }

	  createUploadController() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _uploadControllerClass)[_uploadControllerClass]) {
	      const controller = new (babelHelpers.classPrivateFieldLooseBase(this, _uploadControllerClass)[_uploadControllerClass])(this, babelHelpers.classPrivateFieldLooseBase(this, _uploadControllerOptions)[_uploadControllerOptions]);

	      if (!(controller instanceof AbstractUploadController)) {
	        throw new Error('Uploader.Server: "uploadControllerClass" must be an instance of AbstractUploadController.');
	      }

	      return controller;
	    } else if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller])) {
	      return new UploadController(this, babelHelpers.classPrivateFieldLooseBase(this, _uploadControllerOptions)[_uploadControllerOptions]);
	    }

	    return null;
	  }

	  createLoadController() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _loadControllerClass)[_loadControllerClass]) {
	      const controller = new (babelHelpers.classPrivateFieldLooseBase(this, _loadControllerClass)[_loadControllerClass])(this, babelHelpers.classPrivateFieldLooseBase(this, _loadControllerOptions)[_loadControllerOptions]);

	      if (!(controller instanceof AbstractLoadController)) {
	        throw new Error('Uploader.Server: "loadControllerClass" must be an instance of AbstractLoadController.');
	      }

	      return controller;
	    }

	    return new ServerLoadController(this, babelHelpers.classPrivateFieldLooseBase(this, _loadControllerOptions)[_loadControllerOptions]);
	  }

	  createClientLoadController() {
	    return new ClientLoadController(this, babelHelpers.classPrivateFieldLooseBase(this, _loadControllerOptions)[_loadControllerOptions]);
	  }

	  createRemoveController() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _removeControllerClass)[_removeControllerClass]) {
	      const controller = new (babelHelpers.classPrivateFieldLooseBase(this, _removeControllerClass)[_removeControllerClass])(this, babelHelpers.classPrivateFieldLooseBase(this, _removeControllerOptions)[_removeControllerOptions]);

	      if (!(controller instanceof AbstractRemoveController)) {
	        throw new Error('Uploader.Server: "removeControllerClass" must be an instance of AbstractRemoveController.');
	      }

	      return controller;
	    } else if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller])) {
	      return new RemoveController(this, babelHelpers.classPrivateFieldLooseBase(this, _removeControllerOptions)[_removeControllerOptions]);
	    }

	    return null;
	  }

	  getController() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller];
	  }

	  getControllerOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _controllerOptions)[_controllerOptions];
	  }

	  getChunkSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chunkSize)[_chunkSize];
	  }

	  getDefaultChunkSize() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _defaultChunkSize)[_defaultChunkSize] === null) {
	      const settings = main_core.Extension.getSettings('ui.uploader.core');
	      babelHelpers.classPrivateFieldLooseBase(this, _defaultChunkSize)[_defaultChunkSize] = settings.get('defaultChunkSize', 5 * 1024 * 1024);
	    }

	    return babelHelpers.classPrivateFieldLooseBase(this, _defaultChunkSize)[_defaultChunkSize];
	  }

	  getChunkMinSize() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _chunkMinSize)[_chunkMinSize] === null) {
	      const settings = main_core.Extension.getSettings('ui.uploader.core');
	      babelHelpers.classPrivateFieldLooseBase(this, _chunkMinSize)[_chunkMinSize] = settings.get('chunkMinSize', 1024 * 1024);
	    }

	    return babelHelpers.classPrivateFieldLooseBase(this, _chunkMinSize)[_chunkMinSize];
	  }

	  getChunkMaxSize() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _chunkMaxSize)[_chunkMaxSize] === null) {
	      const settings = main_core.Extension.getSettings('ui.uploader.core');
	      babelHelpers.classPrivateFieldLooseBase(this, _chunkMaxSize)[_chunkMaxSize] = settings.get('chunkMaxSize', 5 * 1024 * 1024);
	    }

	    return babelHelpers.classPrivateFieldLooseBase(this, _chunkMaxSize)[_chunkMaxSize];
	  }

	  getChunkRetryDelays() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chunkRetryDelays)[_chunkRetryDelays];
	  }

	}

	function _calcChunkSize2(chunkSize) {
	  return Math.min(Math.max(this.getChunkMinSize(), chunkSize), this.getChunkMaxSize());
	}

	var _uploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploader");

	class Filter {
	  constructor(uploader, filterOptions = {}) {
	    Object.defineProperty(this, _uploader, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = uploader;
	  }

	  getUploader() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader];
	  }
	  /**
	   * @abstract
	   */


	  apply(...args) {
	    throw new Error('You must implement apply() method.');
	  }

	}

	var _maxFileSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxFileSize");

	var _minFileSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("minFileSize");

	var _maxTotalFileSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxTotalFileSize");

	var _imageMaxFileSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imageMaxFileSize");

	var _imageMinFileSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imageMinFileSize");

	class FileSizeFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    Object.defineProperty(this, _maxFileSize, {
	      writable: true,
	      value: 256 * 1024 * 1024
	    });
	    Object.defineProperty(this, _minFileSize, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _maxTotalFileSize, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _imageMaxFileSize, {
	      writable: true,
	      value: 48 * 1024 * 1024
	    });
	    Object.defineProperty(this, _imageMinFileSize, {
	      writable: true,
	      value: 0
	    });
	    const settings = main_core.Extension.getSettings('ui.uploader.core');
	    babelHelpers.classPrivateFieldLooseBase(this, _maxFileSize)[_maxFileSize] = settings.get('maxFileSize', babelHelpers.classPrivateFieldLooseBase(this, _maxFileSize)[_maxFileSize]);
	    babelHelpers.classPrivateFieldLooseBase(this, _minFileSize)[_minFileSize] = settings.get('minFileSize', babelHelpers.classPrivateFieldLooseBase(this, _minFileSize)[_minFileSize]);
	    babelHelpers.classPrivateFieldLooseBase(this, _maxTotalFileSize)[_maxTotalFileSize] = settings.get('maxTotalFileSize', babelHelpers.classPrivateFieldLooseBase(this, _maxTotalFileSize)[_maxTotalFileSize]);
	    babelHelpers.classPrivateFieldLooseBase(this, _imageMaxFileSize)[_imageMaxFileSize] = settings.get('imageMaxFileSize', babelHelpers.classPrivateFieldLooseBase(this, _imageMaxFileSize)[_imageMaxFileSize]);
	    babelHelpers.classPrivateFieldLooseBase(this, _imageMinFileSize)[_imageMinFileSize] = settings.get('imageMinFileSize', babelHelpers.classPrivateFieldLooseBase(this, _imageMinFileSize)[_imageMinFileSize]);
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    this.setMaxFileSize(options['maxFileSize']);
	    this.setMinFileSize(options['minFileSize']);
	    this.setMaxTotalFileSize(options['maxTotalFileSize']);
	    this.setImageMaxFileSize(options['imageMaxFileSize']);
	    this.setImageMinFileSize(options['imageMinFileSize']);
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (this.getMaxFileSize() !== null && file.getSize() > this.getMaxFileSize()) {
	        reject(new UploaderError('MAX_FILE_SIZE_EXCEEDED', {
	          maxFileSize: formatFileSize(this.getMaxFileSize()),
	          maxFileSizeInBytes: this.getMaxFileSize()
	        }));
	        return;
	      }

	      if (file.getSize() < this.getMinFileSize()) {
	        reject(new UploaderError('MIN_FILE_SIZE_EXCEEDED', {
	          minFileSize: formatFileSize(this.getMinFileSize()),
	          minFileSizeInBytes: this.getMinFileSize()
	        }));
	        return;
	      }

	      if (file.isImage()) {
	        if (this.getImageMaxFileSize() !== null && file.getSize() > this.getImageMaxFileSize()) {
	          reject(new UploaderError('IMAGE_MAX_FILE_SIZE_EXCEEDED', {
	            imageMaxFileSize: formatFileSize(this.getImageMaxFileSize()),
	            imageMaxFileSizeInBytes: this.getImageMaxFileSize()
	          }));
	          return;
	        }

	        if (file.getSize() < this.getImageMinFileSize()) {
	          reject(new UploaderError('IMAGE_MIN_FILE_SIZE_EXCEEDED', {
	            imageMinFileSize: formatFileSize(this.getImageMinFileSize()),
	            imageMinFileSizeInBytes: this.getImageMinFileSize()
	          }));
	          return;
	        }
	      }

	      if (this.getMaxTotalFileSize() !== null) {
	        if (this.getUploader().getTotalSize() > this.getMaxTotalFileSize()) {
	          reject(new UploaderError('MAX_TOTAL_FILE_SIZE_EXCEEDED', {
	            maxTotalFileSize: formatFileSize(this.getMaxTotalFileSize()),
	            maxTotalFileSizeInBytes: this.getMaxTotalFileSize()
	          }));
	          return;
	        }
	      }

	      resolve();
	    });
	  }

	  getMaxFileSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _maxFileSize)[_maxFileSize];
	  }

	  setMaxFileSize(value) {
	    if (main_core.Type.isNumber(value) && value >= 0 || main_core.Type.isNull(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxFileSize)[_maxFileSize] = value;
	    }
	  }

	  getMinFileSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _minFileSize)[_minFileSize];
	  }

	  setMinFileSize(value) {
	    if (main_core.Type.isNumber(value) && value >= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _minFileSize)[_minFileSize] = value;
	    }
	  }

	  getMaxTotalFileSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _maxTotalFileSize)[_maxTotalFileSize];
	  }

	  setMaxTotalFileSize(value) {
	    if (main_core.Type.isNumber(value) && value >= 0 || main_core.Type.isNull(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxTotalFileSize)[_maxTotalFileSize] = value;
	    }
	  }

	  getImageMaxFileSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imageMaxFileSize)[_imageMaxFileSize];
	  }

	  setImageMaxFileSize(value) {
	    if (main_core.Type.isNumber(value) && value >= 0 || main_core.Type.isNull(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imageMaxFileSize)[_imageMaxFileSize] = value;
	    }
	  }

	  getImageMinFileSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imageMinFileSize)[_imageMinFileSize];
	  }

	  setImageMinFileSize(value) {
	    if (main_core.Type.isNumber(value) && value >= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imageMinFileSize)[_imageMinFileSize] = value;
	    }
	  }

	}

	const isValidFileType = (file, fileTypes) => {
	  if (!main_core.Type.isArrayFilled(fileTypes)) {
	    return true;
	  }

	  const mimeType = file.type;
	  const baseMimeType = mimeType.replace(/\/.*$/, '');

	  for (let i = 0; i < fileTypes.length; i++) {
	    if (!main_core.Type.isStringFilled(fileTypes[i])) {
	      continue;
	    }

	    const type = fileTypes[i].trim().toLowerCase();

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

	class FileTypeFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (isValidFileType(file.getBinary(), this.getUploader().getAcceptedFileTypes())) {
	        resolve();
	      } else {
	        reject(new UploaderError('FILE_TYPE_NOT_ALLOWED'));
	      }
	    });
	  }

	}

	const getArrayBuffer = file => {
	  return new Promise((resolve, reject) => {
	    const fileReader = new FileReader();
	    fileReader.readAsArrayBuffer(file);

	    fileReader.onload = () => {
	      const buffer = fileReader.result;
	      resolve(buffer);
	    };

	    fileReader.onerror = () => {
	      reject(fileReader.error);
	    };
	  });
	};

	const convertStringToBuffer = str => {
	  const result = [];

	  for (let i = 0; i < str.length; i++) {
	    result.push(str.charCodeAt(i) & 0xFF);
	  }

	  return result;
	};

	const compareBuffers = (dataView, dest, start) => {
	  for (let i = start, j = 0; j < dest.length;) {
	    if (dataView.getUint8(i++) !== dest[j++]) {
	      return false;
	    }
	  }

	  return true;
	};

	const GIF87a = convertStringToBuffer('GIF87a');
	const GIF89a = convertStringToBuffer('GIF89a');
	class Gif {
	  getSize(file) {
	    return new Promise((resolve, reject) => {
	      if (file.size < 10) {
	        return reject(new Error('GIF signature not found.'));
	      }

	      const blob = file.slice(0, 10);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

	        if (!compareBuffers(view, GIF87a, 0) && !compareBuffers(view, GIF89a, 0)) {
	          return reject(new Error('GIF signature not found.'));
	        }

	        resolve({
	          width: view.getUint16(6, true),
	          height: view.getUint16(8, true)
	        });
	      }).catch(error => {
	        reject(error);
	      });
	    });
	  }

	}

	const PNG_SIGNATURE = convertStringToBuffer('\x89PNG\r\n\x1a\n');
	const IHDR_SIGNATURE = convertStringToBuffer('IHDR');
	const FRIED_CHUNK_NAME = convertStringToBuffer('CgBI');
	class Png {
	  getSize(file) {
	    return new Promise((resolve, reject) => {
	      if (file.size < 40) {
	        return reject(new Error('PNG signature not found.'));
	      }

	      const blob = file.slice(0, 40);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

	        if (!compareBuffers(view, PNG_SIGNATURE, 0)) {
	          return reject(new Error('PNG signature not found.'));
	        }

	        if (compareBuffers(view, FRIED_CHUNK_NAME, 12)) {
	          if (compareBuffers(view, IHDR_SIGNATURE, 28)) {
	            resolve({
	              width: view.getUint32(32),
	              height: view.getUint32(36)
	            });
	          } else {
	            return reject(new Error('PNG IHDR not found.'));
	          }
	        } else if (compareBuffers(view, IHDR_SIGNATURE, 12)) {
	          resolve({
	            width: view.getUint32(16),
	            height: view.getUint32(20)
	          });
	        } else {
	          return reject(new Error('PNG IHDR not found.'));
	        }
	      }).catch(error => {
	        return reject(error);
	      });
	    });
	  }

	}

	const BMP_SIGNATURE = 0x424d; // BM

	class Bmp {
	  getSize(file) {
	    return new Promise((resolve, reject) => {
	      if (file.size < 26) {
	        return reject(new Error('BMP signature not found.'));
	      }

	      const blob = file.slice(0, 26);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

	        if (!view.getUint16(0) === BMP_SIGNATURE) {
	          return reject(new Error('BMP signature not found.'));
	        }

	        resolve({
	          width: view.getUint32(18, true),
	          height: Math.abs(view.getInt32(22, true))
	        });
	      }).catch(error => {
	        reject(error);
	      });
	    });
	  }

	}

	const EXIF_SIGNATURE = convertStringToBuffer('Exif\0\0');
	class Jpeg {
	  getSize(file) {
	    return new Promise((resolve, reject) => {
	      if (file.size < 2) {
	        return reject(new Error('JPEG signature not found.'));
	      }

	      getArrayBuffer(file).then(buffer => {
	        const view = new DataView(buffer);

	        if (view.getUint8(0) !== 0xFF || view.getUint8(1) !== 0xD8) {
	          return reject(new Error('JPEG signature not found.'));
	        }

	        let offset = 2;
	        let orientation = -1;

	        for (;;) {
	          if (view.byteLength - offset < 2) {
	            return reject(new Error('JPEG signature not found.'));
	          }

	          if (view.getUint8(offset++) !== 0xFF) {
	            return reject(new Error('JPEG signature not found.'));
	          }

	          let code = view.getUint8(offset++);
	          let length; // skip padding bytes

	          while (code === 0xFF) {
	            code = view.getUint8(offset++);
	          }

	          if (0xD0 <= code && code <= 0xD9 || code === 0x01) {
	            length = 0;
	          } else if (0xC0 <= code && code <= 0xFE) {
	            // the rest of the unreserved markers
	            if (view.byteLength - offset < 2) {
	              return reject(new Error('JPEG signature not found.'));
	            }

	            length = view.getUint16(offset) - 2;
	            offset += 2;
	          } else {
	            return reject(new Error('JPEG unknown markers.'));
	          }

	          if (code === 0xD9
	          /* EOI */
	          || code === 0xDA
	          /* SOS */
	          ) {
	            return reject(new Error('JPEG end of the data stream.'));
	          } // try to get orientation from Exif segment


	          if (code === 0xE1 && length >= 10 && compareBuffers(view, EXIF_SIGNATURE, offset)) {
	            const exifBlock = new DataView(view.buffer, offset + 6, offset + length);
	            orientation = getOrientation(exifBlock);
	          }

	          if (length >= 5 && 0xC0 <= code && code <= 0xCF && code !== 0xC4 && code !== 0xC8 && code !== 0xCC) {
	            if (view.byteLength - offset < length) {
	              return reject(new Error('JPEG size not found.'));
	            }

	            let width = view.getUint16(offset + 3);
	            let height = view.getUint16(offset + 1);

	            if (orientation >= 5 && orientation <= 8) {
	              [width, height] = [height, width];
	            }

	            return resolve({
	              width,
	              height,
	              orientation
	            });
	          }

	          offset += length;
	        }
	      }).catch(error => {
	        reject(error);
	      });
	    });
	  }

	}
	const Marker = {
	  BIG_ENDIAN: 0x4d4d,
	  LITTLE_ENDIAN: 0x4949
	};

	const getOrientation = exifBlock => {
	  const byteAlign = exifBlock.getUint16(0);
	  const isBigEndian = byteAlign === Marker.BIG_ENDIAN;
	  const isLittleEndian = byteAlign === Marker.LITTLE_ENDIAN;

	  if (isBigEndian || isLittleEndian) {
	    return extractOrientation(exifBlock, isLittleEndian);
	  }

	  return -1;
	};

	const extractOrientation = (exifBlock, littleEndian = false) => {
	  const offset = 8; // idf offset

	  const idfDirectoryEntries = exifBlock.getUint16(offset, littleEndian);
	  const IDF_ENTRY_BYTES = 12;
	  const NUM_DIRECTORY_ENTRIES_BYTES = 2;

	  for (let directoryEntryNumber = 0; directoryEntryNumber < idfDirectoryEntries; directoryEntryNumber++) {
	    const start = offset + NUM_DIRECTORY_ENTRIES_BYTES + directoryEntryNumber * IDF_ENTRY_BYTES;
	    const end = start + IDF_ENTRY_BYTES; // Skip on corrupt EXIF blocks

	    if (start > exifBlock.byteLength) {
	      return -1;
	    }

	    const block = new DataView(exifBlock.buffer, exifBlock.byteOffset + start, end - start);
	    const tagNumber = block.getUint16(0, littleEndian); // 274 is the `orientation` tag ID

	    if (tagNumber === 274) {
	      const dataFormat = block.getUint16(2, littleEndian);

	      if (dataFormat !== 3) {
	        return -1;
	      }

	      const numberOfComponents = block.getUint32(4, littleEndian);

	      if (numberOfComponents !== 1) {
	        return -1;
	      }

	      return block.getUint16(8, littleEndian);
	    }
	  }
	};

	const RIFF_HEADER = 0x52494646; // RIFF

	const WEBP_SIGNATURE = 0x57454250; // WEBP

	const VP8_SIGNATURE = 0x56503820; // VP8

	const VP8L_SIGNATURE = 0x5650384c; // VP8L

	const VP8X_SIGNATURE = 0x56503858; // VP8X

	class Webp {
	  getSize(file) {
	    return new Promise((resolve, reject) => {
	      if (file.size < 16) {
	        return reject(new Error('WEBP signature not found.'));
	      }

	      const blob = file.slice(0, 30);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

	        if (view.getUint32(0) !== RIFF_HEADER && view.getUint32(8) !== WEBP_SIGNATURE) {
	          return reject(new Error('WEBP signature not found.'));
	        }

	        const headerType = view.getUint32(12);
	        const headerView = new DataView(buffer, 20, 10);

	        if (headerType === VP8_SIGNATURE && headerView.getUint8(0) !== 0x2f) {
	          resolve({
	            width: headerView.getUint16(6, true) & 0x3fff,
	            height: headerView.getUint16(8, true) & 0x3fff
	          });
	          return;
	        } else if (headerType === VP8L_SIGNATURE && headerView.getUint8(0) === 0x2f) {
	          const bits = headerView.getUint32(1, true);
	          resolve({
	            width: (bits & 0x3FFF) + 1,
	            height: (bits >> 14 & 0x3FFF) + 1
	          });
	          return;
	        } else if (headerType === VP8X_SIGNATURE) {
	          const extendedHeader = headerView.getUint8(0);
	          const validStart = (extendedHeader & 0xc0) === 0;
	          const validEnd = (extendedHeader & 0x01) === 0;

	          if (validStart && validEnd) {
	            const width = 1 + (headerView.getUint8(6) << 16 | headerView.getUint8(5) << 8 | headerView.getUint8(4));
	            const height = 1 + (headerView.getUint8(9) << 0 | headerView.getUint8(8) << 8 | headerView.getUint8(7));
	            resolve({
	              width,
	              height
	            });
	            return;
	          }
	        }

	        reject(new Error('WEBP signature not found.'));
	      }).catch(error => {
	        reject(error);
	      });
	    });
	  }

	}

	const jpg = new Jpeg();
	const typeHandlers = {
	  gif: new Gif(),
	  png: new Png(),
	  bmp: new Bmp(),
	  jpg: jpg,
	  jpeg: jpg,
	  jpe: jpg,
	  webp: new Webp()
	};

	const getImageSize = file => {
	  if (file.size === 0) {
	    return Promise.reject(new Error('Unknown image type.'));
	  }

	  const extension = getFileExtension(file.name).toLowerCase();
	  const type = file.type.replace(/^image\//, '');
	  const typeHandler = typeHandlers[extension] || typeHandlers[type];

	  if (!typeHandler) {
	    return Promise.reject(new Error('Unknown image type.'));
	  }

	  return typeHandler.getSize(file);
	};

	var _imageMinWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imageMinWidth");

	var _imageMinHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imageMinHeight");

	var _imageMaxWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imageMaxWidth");

	var _imageMaxHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imageMaxHeight");

	var _ignoreUnknownImageTypes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ignoreUnknownImageTypes");

	class ImageSizeFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    Object.defineProperty(this, _imageMinWidth, {
	      writable: true,
	      value: 1
	    });
	    Object.defineProperty(this, _imageMinHeight, {
	      writable: true,
	      value: 1
	    });
	    Object.defineProperty(this, _imageMaxWidth, {
	      writable: true,
	      value: 7000
	    });
	    Object.defineProperty(this, _imageMaxHeight, {
	      writable: true,
	      value: 7000
	    });
	    Object.defineProperty(this, _ignoreUnknownImageTypes, {
	      writable: true,
	      value: false
	    });
	    const settings = main_core.Extension.getSettings('ui.uploader.core');
	    babelHelpers.classPrivateFieldLooseBase(this, _imageMinWidth)[_imageMinWidth] = settings.get('imageMinWidth', babelHelpers.classPrivateFieldLooseBase(this, _imageMinWidth)[_imageMinWidth]);
	    babelHelpers.classPrivateFieldLooseBase(this, _imageMinHeight)[_imageMinHeight] = settings.get('imageMinHeight', babelHelpers.classPrivateFieldLooseBase(this, _imageMinHeight)[_imageMinHeight]);
	    babelHelpers.classPrivateFieldLooseBase(this, _imageMaxWidth)[_imageMaxWidth] = settings.get('imageMaxWidth', babelHelpers.classPrivateFieldLooseBase(this, _imageMaxWidth)[_imageMaxWidth]);
	    babelHelpers.classPrivateFieldLooseBase(this, _imageMaxHeight)[_imageMaxHeight] = settings.get('imageMaxHeight', babelHelpers.classPrivateFieldLooseBase(this, _imageMaxHeight)[_imageMaxHeight]);
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    this.setImageMinWidth(options['imageMinWidth']);
	    this.setImageMinHeight(options['imageMinHeight']);
	    this.setImageMaxWidth(options['imageMaxWidth']);
	    this.setImageMaxHeight(options['imageMaxHeight']);
	    this.setIgnoreUnknownImageTypes(options['ignoreUnknownImageTypes']);
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (!file.isImage()) {
	        resolve();
	        return;
	      }

	      getImageSize(file.getBinary()).then(({
	        width,
	        height
	      }) => {
	        file.setWidth(width);
	        file.setHeight(height);

	        if (width < this.getImageMinWidth() || height < this.getImageMinHeight()) {
	          reject(new UploaderError('IMAGE_IS_TOO_SMALL', {
	            minWidth: this.getImageMinWidth(),
	            minHeight: this.getImageMinHeight()
	          }));
	        } else if (width > this.getImageMaxWidth() || height > this.getImageMaxHeight()) {
	          reject(new UploaderError('IMAGE_IS_TOO_BIG', {
	            maxWidth: this.getImageMaxWidth(),
	            maxHeight: this.getImageMaxHeight()
	          }));
	        } else {
	          resolve();
	        }
	      }).catch(error => {
	        if (this.getIgnoreUnknownImageTypes()) {
	          resolve();
	        } else {
	          if (error) {
	            console.log('Uploader ImageSizeFilter:', error);
	          }

	          reject(new UploaderError('IMAGE_TYPE_NOT_SUPPORTED'));
	        }
	      });
	    });
	  }

	  getImageMinWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imageMinWidth)[_imageMinWidth];
	  }

	  setImageMinWidth(value) {
	    if (main_core.Type.isNumber(value) && value > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imageMinWidth)[_imageMinWidth] = value;
	    }
	  }

	  getImageMinHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imageMinHeight)[_imageMinHeight];
	  }

	  setImageMinHeight(value) {
	    if (main_core.Type.isNumber(value) && value > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imageMinHeight)[_imageMinHeight] = value;
	    }
	  }

	  getImageMaxWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imageMaxWidth)[_imageMaxWidth];
	  }

	  setImageMaxWidth(value) {
	    if (main_core.Type.isNumber(value) && value > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imageMaxWidth)[_imageMaxWidth] = value;
	    }
	  }

	  getImageMaxHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imageMaxHeight)[_imageMaxHeight];
	  }

	  setImageMaxHeight(value) {
	    if (main_core.Type.isNumber(value) && value > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imageMaxHeight)[_imageMaxHeight] = value;
	    }
	  }

	  getIgnoreUnknownImageTypes() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _ignoreUnknownImageTypes)[_ignoreUnknownImageTypes];
	  }

	  setIgnoreUnknownImageTypes(value) {
	    if (main_core.Type.isBoolean(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _ignoreUnknownImageTypes)[_ignoreUnknownImageTypes] = value;
	    }
	  }

	}

	const createWorker = fn => {
	  const workerBlob = new Blob(['(', fn.toString(), ')()'], {
	    type: 'application/javascript'
	  });
	  const workerURL = URL.createObjectURL(workerBlob);
	  const worker = new Worker(workerURL);
	  return {
	    post: (message, callback, transfer) => {
	      const id = createUniqueId();

	      worker.onmessage = event => {
	        if (event.data.id === id) {
	          callback(event.data.message);
	        }
	      };

	      worker.postMessage({
	        id,
	        message
	      }, transfer);
	    },
	    terminate: () => {
	      worker.terminate();
	      URL.revokeObjectURL(workerURL);
	    }
	  };
	};

	const BitmapWorker = function () {
	  self.onmessage = event => {
	    // Hack for Safari. Workers can become unpredictable.
	    // Sometimes 'self.postMessage' doesn't emit 'onmessage' event.
	    setTimeout(() => {
	      createImageBitmap(event.data.message.file).then(bitmap => {
	        var _event$data;

	        self.postMessage({
	          id: event == null ? void 0 : (_event$data = event.data) == null ? void 0 : _event$data.id,
	          message: bitmap
	        }, [bitmap]);
	      }).catch(() => {
	        self.postMessage({
	          id: event.data.id,
	          message: null
	        }, []);
	      });
	    }, 0);
	  };
	};

	const loadImage = file => new Promise((resolve, reject) => {
	  const image = document.createElement('img');
	  const url = URL.createObjectURL(file);
	  image.src = url;

	  image.onerror = error => {
	    URL.revokeObjectURL(image.src);
	    reject(error);
	  };

	  image.onload = () => {
	    URL.revokeObjectURL(url);
	    resolve({
	      width: image.naturalWidth,
	      height: image.naturalHeight,
	      image
	    });
	  };
	});

	const createImagePreview = (data, width, height) => {
	  width = Math.round(width);
	  height = Math.round(height);
	  const canvas = document.createElement('canvas');
	  canvas.width = width;
	  canvas.height = height;
	  const context = canvas.getContext('2d'); // context.imageSmoothingQuality = 'high';

	  context.drawImage(data, 0, 0, width, height);
	  return canvas;
	};

	const supportedMimeTypes = main_core.Browser.isSafari() ? ['image/jpeg', 'image/png'] : ['image/jpeg', 'image/png', 'image/webp'];

	const getCanvasToBlobType = (blob, mimeType = 'image/jpeg', mimeTypeMode = 'auto') => {
	  mimeType = supportedMimeTypes.includes(mimeType) ? mimeType : 'image/jpeg';

	  if (mimeTypeMode === 'force') {
	    return mimeType;
	  } else {
	    return supportedMimeTypes.includes(blob.type) ? blob.type : mimeType;
	  }
	};

	const getFilenameWithoutExtension = name => {
	  return name.substr(0, name.lastIndexOf('.')) || name;
	};

	const extensionMap = {
	  'jpeg': 'jpg'
	};

	const renameFileToMatchMimeType = (filename, mimeType) => {
	  const name = getFilenameWithoutExtension(filename);
	  const type = mimeType.split('/')[1];
	  const extension = extensionMap[type] || type;
	  return `${name}.${extension}`;
	};

	const canvasPrototype = window.HTMLCanvasElement && window.HTMLCanvasElement.prototype;
	const hasToBlobSupport = window.HTMLCanvasElement && canvasPrototype.toBlob;

	const convertCanvasToBlob = (canvas, type, quality) => {
	  return new Promise((resolve, reject) => {
	    if (hasToBlobSupport) {
	      canvas.toBlob(blob => {
	        resolve(blob);
	      }, type, quality);
	    } else {
	      const blob = createBlobFromDataUri(canvas.toDataURL(type, quality));
	      resolve(blob);
	    }
	  });
	};

	const getResizedImageSize = (imageData, options) => {
	  const {
	    mode = 'contain',
	    upscale = false
	  } = options;
	  let {
	    width,
	    height
	  } = options;

	  if (!width && !height) {
	    return {
	      targetWidth: 0,
	      targetHeight: 0,
	      useOriginalSize: true
	    };
	  }

	  if (width === null) {
	    width = height;
	  } else if (height === null) {
	    height = width;
	  }

	  if (mode !== 'force') {
	    const ratioWidth = width / imageData.width;
	    const ratioHeight = height / imageData.height;
	    let ratio = 1;

	    if (mode === 'cover') {
	      ratio = Math.max(ratioWidth, ratioHeight);
	    } else if (mode === 'contain') {
	      ratio = Math.min(ratioWidth, ratioHeight);
	    } // if image is too small, exit here with original image


	    if (ratio > 1 && upscale === false) {
	      return {
	        targetWidth: imageData.width,
	        targetHeight: imageData.height,
	        useOriginalSize: true
	      };
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


	  return {
	    targetWidth: Math.round(width),
	    targetHeight: Math.round(height),
	    useOriginalSize: false
	  };
	};

	let canCreateImageBitmap = 'createImageBitmap' in window && typeof ImageBitmap !== 'undefined' && ImageBitmap.prototype && ImageBitmap.prototype.close;

	if (canCreateImageBitmap && main_core.Browser.isSafari()) {
	  const ua = navigator.userAgent.toLowerCase();
	  const regex = new RegExp('version\\/([0-9.]+)', 'i');
	  const result = regex.exec(ua);

	  if (result && result[1] && result[1] < '16.4') {
	    // Webkit bug https://bugs.webkit.org/show_bug.cgi?id=223326
	    canCreateImageBitmap = false;
	  }
	}

	const resizeImage = (source, options) => {
	  return new Promise((resolve, reject) => {
	    const loadImageDataFallback = () => {
	      loadImage(source).then(({
	        image
	      }) => {
	        handleImageLoad(image);
	      }).catch(error => {
	        reject(error);
	      });
	    };

	    const handleImageLoad = imageData => {
	      const {
	        targetWidth,
	        targetHeight,
	        useOriginalSize
	      } = getResizedImageSize(imageData, options);

	      if (useOriginalSize) {
	        if ('close' in imageData) {
	          imageData.close();
	        }

	        resolve({
	          preview: source,
	          width: imageData.width,
	          height: imageData.height
	        });
	        return;
	      }

	      const canvas = createImagePreview(imageData, targetWidth, targetHeight); // if it was ImageBitmap

	      if ('close' in imageData) {
	        imageData.close();
	      }

	      const {
	        quality = 0.92,
	        mimeType,
	        mimeTypeMode
	      } = options;
	      const type = getCanvasToBlobType(source, mimeType, mimeTypeMode);
	      convertCanvasToBlob(canvas, type, quality).then(blob => {
	        let preview = blob;

	        if (main_core.Type.isFile(source)) {
	          // File type could be changed pic.gif -> pic.jpg
	          const newFileName = renameFileToMatchMimeType(source.name, type);
	          preview = createFileFromBlob(blob, newFileName);
	        }

	        resolve({
	          preview,
	          width: targetWidth,
	          height: targetHeight
	        });
	      }).catch(error => {
	        reject(error);
	      });
	    };

	    if (canCreateImageBitmap) {
	      const bitmapWorker = createWorker(BitmapWorker);
	      bitmapWorker.post({
	        file: source
	      }, imageBitmap => {
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

	const isVideo = blob => {
	  return /^video\/[a-z0-9.-]+$/i.test(blob.type);
	};

	const createVideoPreview = (blob, options = {
	  width: 300,
	  height: 3000
	}, seekTime = 10) => {
	  return new Promise((resolve, reject) => {
	    const video = document.createElement('video');
	    video.setAttribute('src', URL.createObjectURL(blob));
	    video.load();
	    main_core.Event.bind(video, 'error', error => {
	      reject('Error while loading video file', error);
	    });
	    main_core.Event.bind(video, 'loadedmetadata', () => {
	      if (video.duration < seekTime) {
	        seekTime = 0;
	      }

	      video.currentTime = seekTime;
	      main_core.Event.bind(video, 'seeked', () => {
	        const imageData = {
	          width: video.videoWidth,
	          height: video.videoHeight
	        };
	        const {
	          targetWidth,
	          targetHeight
	        } = getResizedImageSize(imageData, options);

	        if (!targetWidth || !targetHeight) {
	          reject();
	          return;
	        }

	        const canvas = createImagePreview(video, targetWidth, targetHeight);
	        const {
	          quality = 0.92,
	          mimeType = 'image/jpeg'
	        } = options;
	        convertCanvasToBlob(canvas, mimeType, quality).then(blob => {
	          resolve({
	            preview: blob,
	            width: targetWidth,
	            height: targetHeight
	          });
	        }).catch(() => {
	          reject();
	        });
	      });
	    });
	  });
	};

	var _imagePreviewWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imagePreviewWidth");

	var _imagePreviewHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imagePreviewHeight");

	var _imagePreviewQuality = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imagePreviewQuality");

	var _imagePreviewMimeType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imagePreviewMimeType");

	var _imagePreviewMimeTypeMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imagePreviewMimeTypeMode");

	var _imagePreviewUpscale = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imagePreviewUpscale");

	var _imagePreviewResizeMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("imagePreviewResizeMode");

	var _getResizeImageOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getResizeImageOptions");

	class ImagePreviewFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    Object.defineProperty(this, _getResizeImageOptions, {
	      value: _getResizeImageOptions2
	    });
	    Object.defineProperty(this, _imagePreviewWidth, {
	      writable: true,
	      value: 300
	    });
	    Object.defineProperty(this, _imagePreviewHeight, {
	      writable: true,
	      value: 300
	    });
	    Object.defineProperty(this, _imagePreviewQuality, {
	      writable: true,
	      value: 0.92
	    });
	    Object.defineProperty(this, _imagePreviewMimeType, {
	      writable: true,
	      value: 'image/jpeg'
	    });
	    Object.defineProperty(this, _imagePreviewMimeTypeMode, {
	      writable: true,
	      value: 'auto'
	    });
	    Object.defineProperty(this, _imagePreviewUpscale, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _imagePreviewResizeMode, {
	      writable: true,
	      value: 'contain'
	    });
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    this.setImagePreviewWidth(options['imagePreviewWidth']);
	    this.setImagePreviewHeight(options['imagePreviewHeight']);
	    this.setImagePreviewQuality(options['imagePreviewQuality']);
	    this.setImagePreviewUpscale(options['imagePreviewUpscale']);
	    this.setImagePreviewResizeMode(options['imagePreviewResizeMode']);
	    this.setImagePreviewMimeType(options['imagePreviewMimeType']);
	    this.setImagePreviewMimeTypeMode(options['imagePreviewMimeTypeMode']);
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (isResizableImage(file.getBinary())) {
	        resizeImage(file.getBinary(), babelHelpers.classPrivateFieldLooseBase(this, _getResizeImageOptions)[_getResizeImageOptions]()).then(({
	          preview,
	          width,
	          height
	        }) => {
	          file.setClientPreview(preview, width, height);
	          resolve();
	        }).catch(error => {
	          if (error) {
	            console.log('Uploader: image resize error', error);
	          }

	          resolve();
	        });
	      } else if (isVideo(file.getBinary()) && !main_core.Browser.isSafari()) {
	        createVideoPreview(file.getBinary(), babelHelpers.classPrivateFieldLooseBase(this, _getResizeImageOptions)[_getResizeImageOptions]()).then(({
	          preview,
	          width,
	          height
	        }) => {
	          file.setClientPreview(preview, width, height);
	          resolve();
	        }).catch(error => {
	          if (error) {
	            console.log('Uploader: video preview error', error);
	          }

	          resolve();
	        });
	      } else {
	        resolve();
	      }
	    });
	  }

	  getImagePreviewWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewWidth)[_imagePreviewWidth];
	  }

	  setImagePreviewWidth(value) {
	    if (main_core.Type.isNumber(value) && value > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewWidth)[_imagePreviewWidth] = value;
	    }
	  }

	  getImagePreviewHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewHeight)[_imagePreviewHeight];
	  }

	  setImagePreviewHeight(value) {
	    if (main_core.Type.isNumber(value) && value > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewHeight)[_imagePreviewHeight] = value;
	    }
	  }

	  getImagePreviewQuality() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewQuality)[_imagePreviewQuality];
	  }

	  setImagePreviewQuality(value) {
	    if (main_core.Type.isNumber(value) && value > 0.1 && value <= 1) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewQuality)[_imagePreviewQuality] = value;
	    }
	  }

	  getImagePreviewUpscale() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewUpscale)[_imagePreviewUpscale];
	  }

	  setImagePreviewUpscale(value) {
	    if (main_core.Type.isBoolean(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewUpscale)[_imagePreviewUpscale] = value;
	    }
	  }

	  getImagePreviewResizeMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewResizeMode)[_imagePreviewResizeMode];
	  }

	  setImagePreviewResizeMode(value) {
	    if (['contain', 'force', 'cover'].includes(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewResizeMode)[_imagePreviewResizeMode] = value;
	    }
	  }

	  getImagePreviewMimeType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewMimeType)[_imagePreviewMimeType];
	  }

	  setImagePreviewMimeType(value) {
	    if (['image/jpeg', 'image/png', 'image/webp'].includes(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewMimeType)[_imagePreviewMimeType] = value;
	    }
	  }

	  getImagePreviewMimeTypeMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewMimeTypeMode)[_imagePreviewMimeTypeMode];
	  }

	  setImagePreviewMimeTypeMode(value) {
	    if (['auto', 'force'].includes(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _imagePreviewMimeTypeMode)[_imagePreviewMimeTypeMode] = value;
	    }
	  }

	}

	function _getResizeImageOptions2() {
	  return {
	    width: this.getImagePreviewWidth(),
	    height: this.getImagePreviewHeight(),
	    mode: this.getImagePreviewResizeMode(),
	    upscale: this.getImagePreviewUpscale(),
	    quality: this.getImagePreviewQuality(),
	    mimeType: this.getImagePreviewMimeType(),
	    mimeTypeMode: this.getImagePreviewMimeTypeMode()
	  };
	}

	var _resizeWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeWidth");

	var _resizeHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeHeight");

	var _resizeMethod = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeMethod");

	var _resizeMimeType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeMimeType");

	var _resizeMimeTypeMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeMimeTypeMode");

	var _resizeQuality = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeQuality");

	class ImageResizeFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    Object.defineProperty(this, _resizeWidth, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _resizeHeight, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _resizeMethod, {
	      writable: true,
	      value: 'contain'
	    });
	    Object.defineProperty(this, _resizeMimeType, {
	      writable: true,
	      value: 'image/jpeg'
	    });
	    Object.defineProperty(this, _resizeMimeTypeMode, {
	      writable: true,
	      value: 'auto'
	    });
	    Object.defineProperty(this, _resizeQuality, {
	      writable: true,
	      value: 0.92
	    });
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    this.setResizeWidth(options['imageResizeWidth']);
	    this.setResizeHeight(options['imageResizeHeight']);
	    this.setResizeMode(options['imageResizeMode']);
	    this.setResizeMimeType(options['imageResizeMimeType']);
	    this.setResizeMimeTypeMode(options['imageResizeMimeTypeMode']);
	    this.setResizeQuality(options['imageResizeQuality']);
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (this.getResizeWidth() === null && this.getResizeHeight() === null) {
	        return resolve();
	      }

	      if (!isResizableImage(file.getBinary())) {
	        return resolve();
	      }

	      const options = {
	        width: this.getResizeWidth(),
	        height: this.getResizeHeight(),
	        mode: this.getResizeMode(),
	        quality: this.getResizeQuality(),
	        mimeType: this.getResizeMimeType(),
	        mimeTypeMode: this.getResizeMimeTypeMode()
	      };
	      resizeImage(file.getBinary(), options).then(({
	        preview,
	        width,
	        height
	      }) => {
	        file.setWidth(width);
	        file.setHeight(height);
	        file.setFile(preview);
	        resolve();
	      }).catch(error => {
	        if (error) {
	          console.log('image resize error', error);
	        }

	        resolve();
	      });
	    });
	  }

	  getResizeWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _resizeWidth)[_resizeWidth];
	  }

	  setResizeWidth(value) {
	    if (main_core.Type.isNumber(value) && value > 0 || main_core.Type.isNull(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _resizeWidth)[_resizeWidth] = value;
	    }
	  }

	  getResizeHeight() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _resizeHeight)[_resizeHeight];
	  }

	  setResizeHeight(value) {
	    if (main_core.Type.isNumber(value) && value > 0 || main_core.Type.isNull(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _resizeHeight)[_resizeHeight] = value;
	    }
	  }

	  getResizeMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _resizeMethod)[_resizeMethod];
	  }

	  setResizeMode(value) {
	    if (['contain', 'force', 'cover'].includes(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _resizeMethod)[_resizeMethod] = value;
	    }
	  }

	  getResizeMimeType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _resizeMimeType)[_resizeMimeType];
	  }

	  setResizeMimeType(value) {
	    if (['image/jpeg', 'image/png', 'image/webp'].includes(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _resizeMimeType)[_resizeMimeType] = value;
	    }
	  }

	  getResizeMimeTypeMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _resizeMimeTypeMode)[_resizeMimeTypeMode];
	  }

	  setResizeMimeTypeMode(value) {
	    if (['auto', 'force'].includes(value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _resizeMimeTypeMode)[_resizeMimeTypeMode] = value;
	    }
	  }

	  getResizeQuality() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _resizeQuality)[_resizeQuality];
	  }

	  setResizeQuality(value) {
	    if (main_core.Type.isNumber(value) && value > 0.1 && value <= 1) {
	      babelHelpers.classPrivateFieldLooseBase(this, _resizeQuality)[_resizeQuality] = value;
	    }
	  }

	}

	const UploaderStatus = {
	  STARTED: 0,
	  STOPPED: 1
	};

	const UploaderEvent = {
	  UPLOAD_START: 'onUploadStart',
	  UPLOAD_COMPLETE: 'onUploadComplete',
	  ERROR: 'onError',
	  MAX_FILE_COUNT_EXCEEDED: 'onMaxFileCountExceeded',
	  DESTROY: 'onDestroy',
	  BEFORE_BROWSE: 'onBeforeBrowse',
	  BEFORE_DROP: 'onBeforeDrop',
	  BEFORE_PASTE: 'onBeforePaste',
	  FILE_BEFORE_ADD: 'File:onBeforeAdd',
	  FILE_ADD_START: 'File:onAddStart',
	  FILE_LOAD_START: 'File:onLoadStart',
	  FILE_LOAD_PROGRESS: 'File:onLoadProgress',
	  FILE_LOAD_COMPLETE: 'File:onLoadComplete',
	  FILE_ERROR: 'File:onError',
	  FILE_ADD: 'File:onAdd',
	  FILE_REMOVE: 'File:onRemove',
	  FILE_UPLOAD_START: 'File:onUploadStart',
	  FILE_UPLOAD_PROGRESS: 'File:onUploadProgress',
	  FILE_UPLOAD_COMPLETE: 'File:onUploadComplete',
	  FILE_COMPLETE: 'File:onComplete',
	  FILE_STATUS_CHANGE: 'File:onStatusChange',
	  FILE_STATE_CHANGE: 'File:onStateChange'
	};

	const FilterType = {
	  VALIDATION: 'validation',
	  PREPARATION: 'preparation'
	};

	const getFilesFromDataTransfer = dataTransfer => {
	  return new Promise((resolve, reject) => {
	    if (!dataTransfer.items) {
	      resolve(dataTransfer.files ? Array.from(dataTransfer.files) : []);
	      return;
	    }

	    const items = Array.from(dataTransfer.items).filter(item => isFileSystemItem(item)).map(item => getFilesFromItem(item));
	    Promise.all(items).then(fileGroups => {
	      const files = [];
	      fileGroups.forEach(group => {
	        files.push.apply(files, group);
	      });
	      resolve(files);
	    }).catch(reject);
	  });
	};

	const isFileSystemItem = item => {
	  if ('webkitGetAsEntry' in item) {
	    const entry = item.webkitGetAsEntry();

	    if (entry) {
	      return entry.isFile || entry.isDirectory;
	    }
	  }

	  return item.kind === 'file';
	};

	const getFilesFromItem = item => {
	  return new Promise((resolve, reject) => {
	    if (isDirectoryEntry(item)) {
	      getFilesInDirectory(getAsEntry(item)).then(resolve).catch(reject);
	      return;
	    }

	    resolve([item.getAsFile()]);
	  });
	};

	const getFilesInDirectory = entry => {
	  return new Promise((resolve, reject) => {
	    const files = [];
	    let dirCounter = 0;
	    let fileCounter = 0;

	    const resolveIfDone = () => {
	      if (fileCounter === 0 && dirCounter === 0) {
	        resolve(files);
	      }
	    };

	    const readEntries = dirEntry => {
	      dirCounter++;
	      const directoryReader = dirEntry.createReader();

	      const readBatch = () => {
	        directoryReader.readEntries(entries => {
	          if (entries.length === 0) {
	            dirCounter--;
	            resolveIfDone();
	            return;
	          }

	          entries.forEach(entry => {
	            if (entry.isDirectory) {
	              readEntries(entry);
	            } else {
	              fileCounter++;
	              entry.file(file => {
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

	const isDirectoryEntry = item => isEntry(item) && (getAsEntry(item) || {}).isDirectory;

	const isEntry = item => 'webkitGetAsEntry' in item;

	const getAsEntry = item => item.webkitGetAsEntry();

	let result = null;

	const canAppendFileToForm = () => {
	  if (result === null) {
	    try {
	      const dataTransfer = new DataTransfer();
	      const file = new File(['hello'], 'my.txt');
	      dataTransfer.items.add(file);
	      const input = document.createElement('input');
	      input.setAttribute('type', 'file');
	      input.files = dataTransfer.files;
	      result = input.files.length === 1;
	    } catch (err) {
	      result = false;
	    }
	  }

	  return result;
	};

	const assignFileToInput = (input, file) => {
	  try {
	    const dataTransfer = new DataTransfer();
	    const files = main_core.Type.isArray(file) ? file : [file];
	    files.forEach(file => {
	      dataTransfer.items.add(file);
	    });
	    input.files = dataTransfer.files;
	  } catch (error) {
	    return false;
	  }

	  return true;
	};

	var _files = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("files");

	var _multiple = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("multiple");

	var _autoUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("autoUpload");

	var _allowReplaceSingle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("allowReplaceSingle");

	var _maxParallelUploads = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxParallelUploads");

	var _maxParallelLoads = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxParallelLoads");

	var _acceptOnlyImages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("acceptOnlyImages");

	var _acceptedFileTypes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("acceptedFileTypes");

	var _ignoredFileNames = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ignoredFileNames");

	var _maxFileCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxFileCount");

	var _server$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("server");

	var _hiddenFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hiddenFields");

	var _hiddenFieldsContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hiddenFieldsContainer");

	var _hiddenFieldName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hiddenFieldName");

	var _assignAsFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("assignAsFile");

	var _filters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filters");

	var _status$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("status");

	var _onBeforeUploadHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onBeforeUploadHandler");

	var _onFileStatusChangeHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onFileStatusChangeHandler");

	var _onFileStateChangeHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onFileStateChangeHandler");

	var _onInputFileChangeHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onInputFileChangeHandler");

	var _onPasteHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPasteHandler");

	var _onDropHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDropHandler");

	var _browsingNodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("browsingNodes");

	var _dropNodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dropNodes");

	var _pastingNodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pastingNodes");

	var _setLoadEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setLoadEvents");

	var _setUploadEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setUploadEvents");

	var _setRemoveEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setRemoveEvents");

	var _handleBeforeUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleBeforeUpload");

	var _handleFileStatusChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileStatusChange");

	var _handleFileStateChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileStateChange");

	var _exceedsMaxFileCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("exceedsMaxFileCount");

	var _applyFilters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyFilters");

	var _removeFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeFile");

	var _handleBrowseClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleBrowseClick");

	var _handleInputFileChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleInputFileChange");

	var _handleDrop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDrop");

	var _preventDefault = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preventDefault");

	var _handlePaste = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePaste");

	var _uploadNext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadNext");

	var _loadNext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadNext");

	var _setHiddenField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setHiddenField");

	var _resetHiddenField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resetHiddenField");

	var _resetHiddenFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resetHiddenFields");

	var _syncInputPositions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("syncInputPositions");

	class Uploader extends main_core_events.EventEmitter {
	  constructor(uploaderOptions) {
	    super();
	    Object.defineProperty(this, _syncInputPositions, {
	      value: _syncInputPositions2
	    });
	    Object.defineProperty(this, _resetHiddenFields, {
	      value: _resetHiddenFields2
	    });
	    Object.defineProperty(this, _resetHiddenField, {
	      value: _resetHiddenField2
	    });
	    Object.defineProperty(this, _setHiddenField, {
	      value: _setHiddenField2
	    });
	    Object.defineProperty(this, _loadNext, {
	      value: _loadNext2
	    });
	    Object.defineProperty(this, _uploadNext, {
	      value: _uploadNext2
	    });
	    Object.defineProperty(this, _handlePaste, {
	      value: _handlePaste2
	    });
	    Object.defineProperty(this, _preventDefault, {
	      value: _preventDefault2
	    });
	    Object.defineProperty(this, _handleDrop, {
	      value: _handleDrop2
	    });
	    Object.defineProperty(this, _handleInputFileChange, {
	      value: _handleInputFileChange2
	    });
	    Object.defineProperty(this, _handleBrowseClick, {
	      value: _handleBrowseClick2
	    });
	    Object.defineProperty(this, _removeFile, {
	      value: _removeFile2
	    });
	    Object.defineProperty(this, _applyFilters, {
	      value: _applyFilters2
	    });
	    Object.defineProperty(this, _exceedsMaxFileCount, {
	      value: _exceedsMaxFileCount2
	    });
	    Object.defineProperty(this, _handleFileStateChange, {
	      value: _handleFileStateChange2
	    });
	    Object.defineProperty(this, _handleFileStatusChange, {
	      value: _handleFileStatusChange2
	    });
	    Object.defineProperty(this, _handleBeforeUpload, {
	      value: _handleBeforeUpload2
	    });
	    Object.defineProperty(this, _setRemoveEvents, {
	      value: _setRemoveEvents2
	    });
	    Object.defineProperty(this, _setUploadEvents, {
	      value: _setUploadEvents2
	    });
	    Object.defineProperty(this, _setLoadEvents, {
	      value: _setLoadEvents2
	    });
	    Object.defineProperty(this, _files, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _multiple, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _autoUpload, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _allowReplaceSingle, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _maxParallelUploads, {
	      writable: true,
	      value: 2
	    });
	    Object.defineProperty(this, _maxParallelLoads, {
	      writable: true,
	      value: 10
	    });
	    Object.defineProperty(this, _acceptOnlyImages, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _acceptedFileTypes, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _ignoredFileNames, {
	      writable: true,
	      value: ['.ds_store', 'thumbs.db', 'desktop.ini']
	    });
	    Object.defineProperty(this, _maxFileCount, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _server$3, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _hiddenFields, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _hiddenFieldsContainer, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _hiddenFieldName, {
	      writable: true,
	      value: 'file'
	    });
	    Object.defineProperty(this, _assignAsFile, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _filters, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _status$1, {
	      writable: true,
	      value: UploaderStatus.STOPPED
	    });
	    Object.defineProperty(this, _onBeforeUploadHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _onFileStatusChangeHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _onFileStateChangeHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _onInputFileChangeHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _onPasteHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _onDropHandler, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _browsingNodes, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _dropNodes, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _pastingNodes, {
	      writable: true,
	      value: new Set()
	    });
	    this.setEventNamespace('BX.UI.Uploader');
	    babelHelpers.classPrivateFieldLooseBase(this, _onBeforeUploadHandler)[_onBeforeUploadHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleBeforeUpload)[_handleBeforeUpload].bind(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _onFileStatusChangeHandler)[_onFileStatusChangeHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleFileStatusChange)[_handleFileStatusChange].bind(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _onFileStateChangeHandler)[_onFileStateChangeHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleFileStateChange)[_handleFileStateChange].bind(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _onInputFileChangeHandler)[_onInputFileChangeHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleInputFileChange)[_handleInputFileChange].bind(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _onPasteHandler)[_onPasteHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handlePaste)[_handlePaste].bind(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _onDropHandler)[_onDropHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleDrop)[_handleDrop].bind(this);
	    const options = main_core.Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _multiple)[_multiple] = main_core.Type.isBoolean(options.multiple) ? options.multiple : false;
	    const acceptedFileTypes = main_core.Type.isString(options.acceptedFileTypes) || main_core.Type.isArray(options.acceptedFileTypes) ? options.acceptedFileTypes : Uploader.getGlobalOption('acceptedFileTypes', null);
	    this.setAcceptedFileTypes(acceptedFileTypes);
	    const acceptOnlyImages = main_core.Type.isBoolean(options.acceptOnlyImages) ? options.acceptOnlyImages : Uploader.getGlobalOption('acceptOnlyImages', null);
	    this.setAcceptOnlyImages(acceptOnlyImages);
	    const ignoredFileNames = main_core.Type.isArray(options.ignoredFileNames) ? options.ignoredFileNames : Uploader.getGlobalOption('ignoredFileNames', null);
	    this.setIgnoredFileNames(ignoredFileNames);
	    this.setMaxFileCount(options.maxFileCount);
	    this.setAllowReplaceSingle(options.allowReplaceSingle);
	    this.assignBrowse(options.browseElement);
	    this.assignDropzone(options.dropElement);
	    this.assignPaste(options.pasteElement);
	    this.setHiddenFieldsContainer(options.hiddenFieldsContainer);
	    this.setHiddenFieldName(options.hiddenFieldName);
	    this.setAssignAsFile(options.assignAsFile);
	    this.setAutoUpload(options.autoUpload);
	    this.setMaxParallelUploads(options.maxParallelUploads);
	    this.setMaxParallelLoads(options.maxParallelLoads);
	    let serverOptions = main_core.Type.isPlainObject(options.serverOptions) ? options.serverOptions : {};
	    serverOptions = Object.assign({}, {
	      controller: options.controller,
	      controllerOptions: options.controllerOptions
	    }, serverOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _server$3)[_server$3] = new Server(serverOptions);
	    this.subscribeFromOptions(options.events);
	    this.addFilter(FilterType.VALIDATION, new FileSizeFilter(this, options));
	    this.addFilter(FilterType.VALIDATION, new FileTypeFilter(this, options));
	    this.addFilter(FilterType.VALIDATION, new ImageSizeFilter(this, options));
	    this.addFilter(FilterType.VALIDATION, new ImagePreviewFilter(this, options));
	    this.addFilter(FilterType.PREPARATION, new ImageResizeFilter(this, options));
	    this.addFilters(options.filters);
	    this.addFiles(options.files);
	  }

	  static getGlobalOption(path, defaultValue = null) {
	    const globalOptions = main_core.Extension.getSettings('ui.uploader.core');
	    return globalOptions.get(path, defaultValue);
	  }

	  addFiles(fileList) {
	    if (!main_core.Type.isArrayLike(fileList)) {
	      return;
	    }

	    const files = Array.from(fileList);

	    if (babelHelpers.classPrivateFieldLooseBase(this, _exceedsMaxFileCount)[_exceedsMaxFileCount](files)) {
	      return;
	    }

	    files.forEach(file => {
	      if (main_core.Type.isArrayFilled(file)) {
	        this.addFile(file[0], file[1]);
	      } else {
	        this.addFile(file);
	      }
	    });
	  }

	  addFile(source, options) {
	    const file = new UploaderFile(source, options);

	    if (this.getIgnoredFileNames().includes(file.getName().toLowerCase())) {
	      return;
	    }

	    if (babelHelpers.classPrivateFieldLooseBase(this, _exceedsMaxFileCount)[_exceedsMaxFileCount]([file])) {
	      return;
	    }

	    if (!this.isMultiple() && this.shouldReplaceSingle() && babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].length > 0) {
	      const fileToReplace = babelHelpers.classPrivateFieldLooseBase(this, _files)[_files][0];

	      this.removeFile(fileToReplace);
	    }

	    const event = new main_core_events.BaseEvent({
	      data: {
	        file: file
	      }
	    });
	    this.emit(UploaderEvent.FILE_BEFORE_ADD, event);

	    if (event.isDefaultPrevented()) {
	      return;
	    }

	    file.subscribe(FileEvent.STATUS_CHANGE, babelHelpers.classPrivateFieldLooseBase(this, _onFileStatusChangeHandler)[_onFileStatusChangeHandler]);
	    file.subscribe(FileEvent.STATE_CHANGE, babelHelpers.classPrivateFieldLooseBase(this, _onFileStateChangeHandler)[_onFileStateChangeHandler]);

	    babelHelpers.classPrivateFieldLooseBase(this, _setUploadEvents)[_setUploadEvents](file);

	    babelHelpers.classPrivateFieldLooseBase(this, _setLoadEvents)[_setLoadEvents](file);

	    babelHelpers.classPrivateFieldLooseBase(this, _setRemoveEvents)[_setRemoveEvents](file);

	    if (file.getOrigin() === FileOrigin.SERVER) {
	      file.setLoadController(this.getServer().createLoadController());
	    } else {
	      file.setLoadController(this.getServer().createClientLoadController());
	    }

	    if (file.getOrigin() === FileOrigin.CLIENT) {
	      const uploadController = this.getServer().createUploadController();
	      file.setUploadController(uploadController);
	    }

	    file.setRemoveController(this.getServer().createRemoveController());

	    babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].push(file);

	    file.emit(FileEvent.ADD);
	    this.emit(UploaderEvent.FILE_ADD_START, {
	      file
	    });

	    if (file.getOrigin() === FileOrigin.SERVER) {
	      file.load();
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	    }
	  }

	  start() {
	    if (this.getStatus() !== UploaderStatus.STARTED && this.getPendingFileCount() > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _status$1)[_status$1] = UploaderStatus.STARTED;
	      this.emit(UploaderEvent.UPLOAD_START);

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadNext)[_uploadNext]();
	    }
	  } // stop(): void
	  // {
	  // 	this.#status = UploaderStatus.STOPPED;
	  //
	  // 	this.getFiles().forEach((file: UploaderFile) => {
	  // 		if (file.isUploading())
	  // 		{
	  // 			file.abort();
	  // 			file.setStatus(FileStatus.PENDING);
	  // 		}
	  // 	});
	  //
	  // 	this.emit('onStop');
	  // }


	  cancel() {
	    this.getFiles().forEach(file => {
	      file.remove();
	    });
	  }

	  destroy() {
	    this.emit(UploaderEvent.DESTROY);
	    this.unassignBrowseAll();
	    this.unassignDropzoneAll();
	    this.unassignPasteAll();
	    this.getFiles().forEach(file => {
	      file.remove();
	    });

	    babelHelpers.classPrivateFieldLooseBase(this, _resetHiddenFields)[_resetHiddenFields]();

	    babelHelpers.classPrivateFieldLooseBase(this, _files)[_files] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _server$3)[_server$3] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _acceptedFileTypes)[_acceptedFileTypes] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _ignoredFileNames)[_ignoredFileNames] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _filters)[_filters] = null;
	    Object.setPrototypeOf(this, null);
	  }

	  removeFile(file) {
	    if (main_core.Type.isString(file)) {
	      file = this.getFile(file);
	    }

	    const index = babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].findIndex(element => element === file);

	    if (index === -1) {
	      return;
	    }

	    file.remove();
	  }

	  getFile(id) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].find(file => file.getId() === id) || null;
	  }

	  getFiles() {
	    return Array.from(babelHelpers.classPrivateFieldLooseBase(this, _files)[_files]);
	  }

	  isMultiple() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _multiple)[_multiple];
	  }

	  getStatus() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status$1)[_status$1];
	  }

	  addFilter(type, filter, filterOptions = {}) {
	    if (main_core.Type.isFunction(filter) || main_core.Type.isString(filter)) {
	      const className = main_core.Type.isString(filter) ? main_core.Reflection.getClass(filter) : filter;

	      if (main_core.Type.isFunction(className)) {
	        filter = new className(this, filterOptions);
	      }
	    }

	    if (filter instanceof Filter) {
	      let filters = babelHelpers.classPrivateFieldLooseBase(this, _filters)[_filters].get(type);

	      if (!main_core.Type.isArray(filters)) {
	        filters = [];

	        babelHelpers.classPrivateFieldLooseBase(this, _filters)[_filters].set(type, filters);
	      }

	      filters.push(filter);
	    } else {
	      throw new Error('Uploader: a filter must be an instance of FileUploader.Filter.');
	    }
	  }

	  addFilters(filters) {
	    if (main_core.Type.isArray(filters)) {
	      filters.forEach(filter => {
	        if (main_core.Type.isPlainObject(filter)) {
	          this.addFilter(filter.type, filter.filter, filter.options);
	        }
	      });
	    }
	  }

	  getServer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _server$3)[_server$3];
	  }

	  assignBrowse(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (!main_core.Type.isElementNode(node) || babelHelpers.classPrivateFieldLooseBase(this, _browsingNodes)[_browsingNodes].has(node)) {
	        return;
	      }

	      let input;

	      if (node.tagName === 'INPUT' && node.type === 'file') {
	        input = node; // Add already selected files

	        if (input.files && input.files.length) {
	          this.addFiles(input.files);
	        }

	        const acceptAttr = input.getAttribute('accept');

	        if (main_core.Type.isStringFilled(acceptAttr)) {
	          this.setAcceptedFileTypes(acceptAttr);
	        }

	        babelHelpers.classPrivateFieldLooseBase(this, _browsingNodes)[_browsingNodes].set(node, null);
	      } else {
	        input = document.createElement('input');
	        input.setAttribute('type', 'file');

	        const onBrowseClickHandler = babelHelpers.classPrivateFieldLooseBase(this, _handleBrowseClick)[_handleBrowseClick].bind(this, input, node);

	        babelHelpers.classPrivateFieldLooseBase(this, _browsingNodes)[_browsingNodes].set(node, onBrowseClickHandler);

	        main_core.Event.bind(node, 'click', onBrowseClickHandler);
	      }

	      if (this.isMultiple()) {
	        input.setAttribute('multiple', 'multiple');
	      }

	      if (main_core.Type.isArrayFilled(this.getAcceptedFileTypes())) {
	        input.setAttribute('accept', this.getAcceptedFileTypes().join(','));
	      }

	      main_core.Event.bind(input, 'change', babelHelpers.classPrivateFieldLooseBase(this, _onInputFileChangeHandler)[_onInputFileChangeHandler]);
	    });
	  }

	  unassignBrowse(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _browsingNodes)[_browsingNodes].has(node)) {
	        main_core.Event.unbind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _browsingNodes)[_browsingNodes].get(node));
	        main_core.Event.unbind(node, 'change', babelHelpers.classPrivateFieldLooseBase(this, _onInputFileChangeHandler)[_onInputFileChangeHandler]);

	        babelHelpers.classPrivateFieldLooseBase(this, _browsingNodes)[_browsingNodes].delete(node);
	      }
	    });
	  }

	  unassignBrowseAll() {
	    Array.from(babelHelpers.classPrivateFieldLooseBase(this, _browsingNodes)[_browsingNodes].keys()).forEach(node => {
	      this.unassignBrowse(node);
	    });
	  }

	  assignDropzone(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (!main_core.Type.isElementNode(node) || babelHelpers.classPrivateFieldLooseBase(this, _dropNodes)[_dropNodes].has(node)) {
	        return;
	      }

	      main_core.Event.bind(node, 'dragover', babelHelpers.classPrivateFieldLooseBase(this, _preventDefault)[_preventDefault]);
	      main_core.Event.bind(node, 'dragenter', babelHelpers.classPrivateFieldLooseBase(this, _preventDefault)[_preventDefault]);
	      main_core.Event.bind(node, 'drop', babelHelpers.classPrivateFieldLooseBase(this, _onDropHandler)[_onDropHandler]);

	      babelHelpers.classPrivateFieldLooseBase(this, _dropNodes)[_dropNodes].add(node);
	    });
	  }

	  unassignDropzone(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _dropNodes)[_dropNodes].has(node)) {
	        main_core.Event.unbind(node, 'dragover', babelHelpers.classPrivateFieldLooseBase(this, _preventDefault)[_preventDefault]);
	        main_core.Event.unbind(node, 'dragenter', babelHelpers.classPrivateFieldLooseBase(this, _preventDefault)[_preventDefault]);
	        main_core.Event.unbind(node, 'drop', babelHelpers.classPrivateFieldLooseBase(this, _onDropHandler)[_onDropHandler]);

	        babelHelpers.classPrivateFieldLooseBase(this, _dropNodes)[_dropNodes].delete(node);
	      }
	    });
	  }

	  unassignDropzoneAll() {
	    Array.from(babelHelpers.classPrivateFieldLooseBase(this, _dropNodes)[_dropNodes]).forEach(node => {
	      this.unassignDropzone(node);
	    });
	  }

	  assignPaste(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (!main_core.Type.isElementNode(node) || babelHelpers.classPrivateFieldLooseBase(this, _pastingNodes)[_pastingNodes].has(node)) {
	        return;
	      }

	      main_core.Event.bind(node, 'paste', babelHelpers.classPrivateFieldLooseBase(this, _onPasteHandler)[_onPasteHandler]);

	      babelHelpers.classPrivateFieldLooseBase(this, _pastingNodes)[_pastingNodes].add(node);
	    });
	  }

	  unassignPaste(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _pastingNodes)[_pastingNodes].has(node)) {
	        main_core.Event.unbind(node, 'paste', babelHelpers.classPrivateFieldLooseBase(this, _onPasteHandler)[_onPasteHandler]);

	        babelHelpers.classPrivateFieldLooseBase(this, _pastingNodes)[_pastingNodes].delete(node);
	      }
	    });
	  }

	  unassignPasteAll() {
	    Array.from(babelHelpers.classPrivateFieldLooseBase(this, _pastingNodes)[_pastingNodes]).forEach(node => {
	      this.unassignPaste(node);
	    });
	  }

	  getHiddenFieldsContainer() {
	    let element = null;

	    if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldsContainer)[_hiddenFieldsContainer])) {
	      element = document.querySelector(babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldsContainer)[_hiddenFieldsContainer]);

	      if (!main_core.Type.isElementNode(element)) {
	        console.error(`Uploader: a hidden field container was not found (${babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldsContainer)[_hiddenFieldsContainer]}).`);
	      }
	    } else if (main_core.Type.isElementNode(babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldsContainer)[_hiddenFieldsContainer])) {
	      element = babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldsContainer)[_hiddenFieldsContainer];
	    }

	    return element;
	  }

	  setHiddenFieldsContainer(container) {
	    if (main_core.Type.isStringFilled(container) || main_core.Type.isElementNode(container) || main_core.Type.isNull(container)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldsContainer)[_hiddenFieldsContainer] = container;
	    }
	  }

	  getHiddenFieldName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldName)[_hiddenFieldName];
	  }

	  setHiddenFieldName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hiddenFieldName)[_hiddenFieldName] = name;
	    }
	  }

	  shouldAssignAsFile() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _assignAsFile)[_assignAsFile];
	  }

	  setAssignAsFile(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _assignAsFile)[_assignAsFile] = flag;
	    }
	  }

	  getTotalSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].reduce((totalSize, file) => {
	      return totalSize + file.getSize();
	    }, 0);
	  }

	  shouldAutoUpload() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _autoUpload)[_autoUpload];
	  }

	  setAutoUpload(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _autoUpload)[_autoUpload] = flag;
	    }
	  }

	  getMaxParallelUploads() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _maxParallelUploads)[_maxParallelUploads];
	  }

	  setMaxParallelUploads(number) {
	    if (main_core.Type.isNumber(number) && number > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxParallelUploads)[_maxParallelUploads] = number;
	    }
	  }

	  getMaxParallelLoads() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _maxParallelLoads)[_maxParallelLoads];
	  }

	  setMaxParallelLoads(number) {
	    if (main_core.Type.isNumber(number) && number > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxParallelLoads)[_maxParallelLoads] = number;
	    }
	  }

	  getUploadingFileCount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].filter(file => file.isUploading()).length;
	  }

	  getPendingFileCount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].filter(file => file.isReadyToUpload()).length;
	  }

	  static getImageExtensions() {
	    return this.getGlobalOption('imageExtensions', ['.jpg', '.bmp', '.jpeg', '.jpe', '.gif', '.png', '.webp']);
	  }

	  setAcceptOnlyImages(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      this.acceptOnlyImages(flag);
	    }
	  }

	  acceptOnlyImages(flag = true) {
	    const imageExtensions = flag ? Uploader.getImageExtensions() : [];
	    this.setAcceptedFileTypes(imageExtensions);
	    babelHelpers.classPrivateFieldLooseBase(this, _acceptOnlyImages)[_acceptOnlyImages] = flag;
	  }

	  shouldAcceptOnlyImages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _acceptOnlyImages)[_acceptOnlyImages];
	  }

	  getAcceptedFileTypes() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _acceptedFileTypes)[_acceptedFileTypes];
	  }

	  setAcceptedFileTypes(fileTypes) {
	    if (main_core.Type.isString(fileTypes)) {
	      fileTypes = fileTypes.split(',');
	    }

	    if (main_core.Type.isArray(fileTypes)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _acceptedFileTypes)[_acceptedFileTypes] = [];
	      babelHelpers.classPrivateFieldLooseBase(this, _acceptOnlyImages)[_acceptOnlyImages] = false;
	      fileTypes.forEach(type => {
	        if (main_core.Type.isStringFilled(type)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _acceptedFileTypes)[_acceptedFileTypes].push(type);
	        }
	      });
	    }
	  }

	  getIgnoredFileNames() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _ignoredFileNames)[_ignoredFileNames];
	  }

	  setIgnoredFileNames(fileNames) {
	    if (main_core.Type.isArray(fileNames)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _ignoredFileNames)[_ignoredFileNames] = [];
	      fileNames.forEach(fileName => {
	        if (main_core.Type.isStringFilled(fileName)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _ignoredFileNames)[_ignoredFileNames].push(fileName.toLowerCase());
	        }
	      });
	    }
	  }

	  setMaxFileCount(maxFileCount) {
	    if (main_core.Type.isNumber(maxFileCount) && maxFileCount > 0 || maxFileCount === null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxFileCount)[_maxFileCount] = maxFileCount;
	    }
	  }

	  getMaxFileCount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _maxFileCount)[_maxFileCount];
	  }

	  setAllowReplaceSingle(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _allowReplaceSingle)[_allowReplaceSingle] = flag;
	    }
	  }

	  shouldReplaceSingle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _allowReplaceSingle)[_allowReplaceSingle];
	  }

	}

	function _setLoadEvents2(file) {
	  file.subscribeFromOptions({
	    [FileEvent.LOAD_START]: () => {
	      this.emit(UploaderEvent.FILE_LOAD_START, {
	        file
	      });
	    },
	    [FileEvent.LOAD_PROGRESS]: event => {
	      const {
	        progress
	      } = event.getData();
	      this.emit(UploaderEvent.FILE_LOAD_PROGRESS, {
	        file,
	        progress
	      });
	    },
	    [FileEvent.LOAD_ERROR]: event => {
	      const {
	        error
	      } = event.getData();
	      this.emit(UploaderEvent.FILE_ERROR, {
	        file,
	        error
	      });
	      this.emit(UploaderEvent.FILE_ADD, {
	        file,
	        error
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	    },
	    [FileEvent.LOAD_COMPLETE]: () => {
	      this.emit(UploaderEvent.FILE_ADD, {
	        file
	      });
	      this.emit(UploaderEvent.FILE_LOAD_COMPLETE, {
	        file
	      });

	      if (file.getOrigin() === FileOrigin.SERVER || !file.isUploadable()) {
	        this.emit(UploaderEvent.FILE_COMPLETE, {
	          file
	        });

	        babelHelpers.classPrivateFieldLooseBase(this, _setHiddenField)[_setHiddenField](file);
	      } else if (file.isUploadable() && this.shouldAutoUpload()) {
	        file.upload();
	      }

	      babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	    },
	    [FileEvent.PREPARE_FILE_ASYNC]: event => {
	      const file = event.getData().file;
	      return babelHelpers.classPrivateFieldLooseBase(this, _applyFilters)[_applyFilters](FilterType.VALIDATION, file).then(() => babelHelpers.classPrivateFieldLooseBase(this, _applyFilters)[_applyFilters](FilterType.PREPARATION, file));
	    }
	  });
	}

	function _setUploadEvents2(file) {
	  file.subscribeFromOptions({
	    [FileEvent.BEFORE_UPLOAD]: babelHelpers.classPrivateFieldLooseBase(this, _onBeforeUploadHandler)[_onBeforeUploadHandler],
	    [FileEvent.UPLOAD_START]: () => {
	      this.emit(UploaderEvent.FILE_UPLOAD_START, {
	        file
	      });
	    },
	    [FileEvent.UPLOAD_PROGRESS]: event => {
	      const {
	        progress
	      } = event.getData();
	      this.emit(UploaderEvent.FILE_UPLOAD_PROGRESS, {
	        file,
	        progress
	      });
	    },
	    [FileEvent.UPLOAD_ERROR]: event => {
	      const {
	        error
	      } = event.getData();
	      this.emit(UploaderEvent.FILE_ERROR, {
	        file,
	        error
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadNext)[_uploadNext]();
	    },
	    [FileEvent.UPLOAD_COMPLETE]: () => {
	      this.emit(UploaderEvent.FILE_UPLOAD_COMPLETE, {
	        file
	      });
	      this.emit(UploaderEvent.FILE_COMPLETE, {
	        file
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _setHiddenField)[_setHiddenField](file);

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadNext)[_uploadNext]();
	    }
	  });
	}

	function _setRemoveEvents2(file) {
	  file.subscribeOnce(FileEvent.REMOVE_ERROR, event => {
	    const {
	      error
	    } = event.getData();
	    this.emit(UploaderEvent.FILE_ERROR, {
	      file,
	      error
	    });
	  });
	  file.subscribeOnce(FileEvent.REMOVE_COMPLETE, () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _removeFile)[_removeFile](file);
	  });
	}

	function _handleBeforeUpload2(event) {
	  if (this.getStatus() === UploaderStatus.STOPPED) {
	    event.preventDefault();
	    this.start();
	  } else {
	    if (this.getUploadingFileCount() >= this.getMaxParallelUploads()) {
	      event.preventDefault();
	    }
	  }
	}

	function _handleFileStatusChange2(event) {
	  const file = event.getTarget();
	  this.emit(UploaderEvent.FILE_STATUS_CHANGE, {
	    file
	  });
	}

	function _handleFileStateChange2(event) {
	  const file = event.getTarget();
	  const property = event.getData().property;
	  const value = event.getData().value;
	  this.emit(UploaderEvent.FILE_STATE_CHANGE, {
	    file,
	    property,
	    value
	  });
	}

	function _exceedsMaxFileCount2(fileList) {
	  const totalNewFiles = fileList.length;

	  const totalFiles = babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].length;

	  if (!this.isMultiple() && totalNewFiles > 1) {
	    return true;
	  }

	  let maxFileCount;

	  if (this.isMultiple()) {
	    maxFileCount = this.getMaxFileCount();
	  } else {
	    maxFileCount = this.shouldReplaceSingle() ? null : 1;
	  }

	  if (maxFileCount !== null && totalFiles + totalNewFiles > maxFileCount) {
	    const error = new UploaderError('MAX_FILE_COUNT_EXCEEDED', {
	      maxFileCount
	    });
	    this.emit(UploaderEvent.MAX_FILE_COUNT_EXCEEDED, {
	      error
	    });
	    this.emit(UploaderEvent.ERROR, {
	      error
	    });
	    return true;
	  }

	  return false;
	}

	function _applyFilters2(type, ...args) {
	  return new Promise((resolve, reject) => {
	    const filters = [...(babelHelpers.classPrivateFieldLooseBase(this, _filters)[_filters].get(type) || [])];

	    if (filters.length === 0) {
	      resolve();
	      return;
	    }

	    const firstFilter = filters.shift(); // chain filters

	    filters.reduce((current, next) => {
	      return current.then(() => next.apply(...args));
	    }, firstFilter.apply(...args)).then(result => resolve(result)).catch(error => reject(error));
	  });
	}

	function _removeFile2(file) {
	  const index = babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].findIndex(element => element === file);

	  if (index !== -1) {
	    babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].splice(index, 1);
	  }

	  file.unsubscribeAll();
	  this.emit(UploaderEvent.FILE_REMOVE, {
	    file
	  });

	  babelHelpers.classPrivateFieldLooseBase(this, _resetHiddenField)[_resetHiddenField](file);
	}

	function _handleBrowseClick2(input, node) {
	  const event = new main_core_events.BaseEvent({
	    data: {
	      input,
	      node
	    }
	  });
	  this.emit(UploaderEvent.BEFORE_BROWSE, event);

	  if (event.isDefaultPrevented()) {
	    return;
	  }

	  input.click();
	}

	function _handleInputFileChange2(event) {
	  const input = event.currentTarget;
	  this.addFiles(Array.from(input.files)); // reset file input

	  input.value = '';
	}

	function _handleDrop2(dragEvent) {
	  dragEvent.preventDefault();
	  const event = new main_core_events.BaseEvent({
	    data: {
	      dragEvent
	    }
	  });
	  this.emit(UploaderEvent.BEFORE_DROP, event);

	  if (event.isDefaultPrevented()) {
	    return;
	  }

	  getFilesFromDataTransfer(dragEvent.dataTransfer).then(files => {
	    this.addFiles(files);
	  });
	}

	function _preventDefault2(event) {
	  event.preventDefault();
	}

	function _handlePaste2(clipboardEvent) {
	  clipboardEvent.preventDefault();
	  const clipboardData = clipboardEvent.clipboardData;

	  if (!clipboardData) {
	    return;
	  }

	  const event = new main_core_events.BaseEvent({
	    data: {
	      clipboardEvent
	    }
	  });
	  this.emit(UploaderEvent.BEFORE_PASTE, event);

	  if (event.isDefaultPrevented()) {
	    return;
	  }

	  getFilesFromDataTransfer(clipboardData).then(files => {
	    this.addFiles(files);
	  });
	}

	function _uploadNext2() {
	  if (this.getStatus() !== UploaderStatus.STARTED) {
	    return;
	  }

	  const maxParallelUploads = this.getMaxParallelUploads();
	  const currentUploads = this.getUploadingFileCount();

	  const pendingFiles = babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].filter(file => file.isReadyToUpload());

	  const pendingUploads = pendingFiles.length;

	  if (currentUploads < maxParallelUploads) {
	    const limit = Math.min(maxParallelUploads - currentUploads, pendingFiles.length);

	    for (let i = 0; i < limit; i++) {
	      const pendingFile = pendingFiles[i];
	      pendingFile.upload();
	    }
	  } // All files are COMPLETE or FAILED


	  if (currentUploads === 0 && pendingUploads === 0) {
	    babelHelpers.classPrivateFieldLooseBase(this, _status$1)[_status$1] = UploaderStatus.STOPPED;
	    this.emit(UploaderEvent.UPLOAD_COMPLETE);
	  }
	}

	function _loadNext2() {
	  const maxParallelLoads = this.getMaxParallelLoads();

	  const currentLoads = babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].filter(file => file.isLoading()).length;

	  const pendingFiles = babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].filter(file => {
	    return file.getStatus() === FileStatus.ADDED && file.getOrigin() === FileOrigin.CLIENT;
	  });

	  if (currentLoads < maxParallelLoads) {
	    const limit = Math.min(maxParallelLoads - currentLoads, pendingFiles.length);

	    for (let i = 0; i < limit; i++) {
	      const pendingFile = pendingFiles[i];
	      pendingFile.load();
	    }
	  }
	}

	function _setHiddenField2(file) {
	  const container = this.getHiddenFieldsContainer();

	  if (!container || babelHelpers.classPrivateFieldLooseBase(this, _hiddenFields)[_hiddenFields].has(file.getId())) {
	    return;
	  } // TODO: is it needed?


	  const isExistingServerFile = main_core.Type.isNumber(file.getServerId());

	  if (isExistingServerFile) {
	    return;
	  }

	  const assignAsFile = file.getOrigin() === FileOrigin.CLIENT && !file.isUploadable() && this.shouldAssignAsFile() && canAppendFileToForm();
	  const input = document.createElement('input');
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
	    assignFileToInput(input, file.getBinary());
	  } else if (file.getServerId() !== null) {
	    input.value = file.getServerId();
	  }

	  main_core.Dom.append(input, container);

	  babelHelpers.classPrivateFieldLooseBase(this, _hiddenFields)[_hiddenFields].set(file.getId(), input);

	  babelHelpers.classPrivateFieldLooseBase(this, _syncInputPositions)[_syncInputPositions]();
	}

	function _resetHiddenField2(file) {
	  const input = babelHelpers.classPrivateFieldLooseBase(this, _hiddenFields)[_hiddenFields].get(file.getId());

	  if (input) {
	    main_core.Dom.remove(input);

	    babelHelpers.classPrivateFieldLooseBase(this, _hiddenFields)[_hiddenFields].delete(file.getId());
	  }
	}

	function _resetHiddenFields2() {
	  Array.from(babelHelpers.classPrivateFieldLooseBase(this, _hiddenFields)[_hiddenFields].values()).forEach(input => {
	    main_core.Dom.remove(input);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _hiddenFields)[_hiddenFields] = [];
	}

	function _syncInputPositions2() {
	  const container = this.getHiddenFieldsContainer();

	  if (!container) {
	    return;
	  }

	  this.getFiles().forEach(file => {
	    const input = babelHelpers.classPrivateFieldLooseBase(this, _hiddenFields)[_hiddenFields].get(file.getId());

	    if (input) {
	      main_core.Dom.append(input, container);
	    }
	  });
	}

	/**
	 * @memberof BX.UI.Uploader
	 */

	var _uploader$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploader");

	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");

	var _uploaderError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderError");

	var _getItemsArray = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsArray");

	var _getItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItem");

	var _handleFileAdd = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileAdd");

	var _handleFileRemove = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileRemove");

	var _handleFileStateChange$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileStateChange");

	var _handleError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleError");

	var _handleUploadStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleUploadStart");

	var _handleUploadComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleUploadComplete");

	class VueUploaderAdapter extends main_core_events.EventEmitter {
	  constructor(uploaderOptions) {
	    super();
	    Object.defineProperty(this, _handleUploadComplete, {
	      value: _handleUploadComplete2
	    });
	    Object.defineProperty(this, _handleUploadStart, {
	      value: _handleUploadStart2
	    });
	    Object.defineProperty(this, _handleError, {
	      value: _handleError2
	    });
	    Object.defineProperty(this, _handleFileStateChange$1, {
	      value: _handleFileStateChange2$1
	    });
	    Object.defineProperty(this, _handleFileRemove, {
	      value: _handleFileRemove2
	    });
	    Object.defineProperty(this, _handleFileAdd, {
	      value: _handleFileAdd2
	    });
	    Object.defineProperty(this, _getItem, {
	      value: _getItem2
	    });
	    Object.defineProperty(this, _getItemsArray, {
	      value: _getItemsArray2
	    });
	    Object.defineProperty(this, _uploader$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploaderError, {
	      writable: true,
	      value: null
	    });
	    this.setEventNamespace('BX.UI.Uploader.Vue.Adapter');
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items] = ui_vue3.ref([]);
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderError)[_uploaderError] = ui_vue3.shallowRef(null);
	    const options = main_core.Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
	    const userEvents = options.events;
	    options.events = {
	      'File:onAddStart': babelHelpers.classPrivateFieldLooseBase(this, _handleFileAdd)[_handleFileAdd].bind(this),
	      'File:onRemove': babelHelpers.classPrivateFieldLooseBase(this, _handleFileRemove)[_handleFileRemove].bind(this),
	      'File:onStateChange': babelHelpers.classPrivateFieldLooseBase(this, _handleFileStateChange$1)[_handleFileStateChange$1].bind(this),
	      'onError': babelHelpers.classPrivateFieldLooseBase(this, _handleError)[_handleError].bind(this),
	      'onUploadStart': babelHelpers.classPrivateFieldLooseBase(this, _handleUploadStart)[_handleUploadStart].bind(this),
	      'onUploadComplete': babelHelpers.classPrivateFieldLooseBase(this, _handleUploadComplete)[_handleUploadComplete].bind(this)
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader$1)[_uploader$1] = new ui_uploader_core.Uploader(options);

	    babelHelpers.classPrivateFieldLooseBase(this, _uploader$1)[_uploader$1].subscribeFromOptions(userEvents);
	  }

	  getUploader() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploader$1)[_uploader$1];
	  }

	  getItems() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _items)[_items];
	  }

	  getUploaderError() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderError)[_uploaderError];
	  }

	}

	function _getItemsArray2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].value;
	}

	function _getItem2(id) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getItemsArray)[_getItemsArray]().find(item => item.id === id);
	}

	function _handleFileAdd2(event) {
	  const {
	    file
	  } = event.getData();
	  const item = file.getState();

	  babelHelpers.classPrivateFieldLooseBase(this, _getItemsArray)[_getItemsArray]().push(item);

	  this.emit('Item:onAdd', {
	    item
	  });
	}

	function _handleFileRemove2(event) {
	  const {
	    file
	  } = event.getData();

	  const position = babelHelpers.classPrivateFieldLooseBase(this, _getItemsArray)[_getItemsArray]().findIndex(fileInfo => fileInfo.id === file.getId());

	  if (position >= 0) {
	    const result = babelHelpers.classPrivateFieldLooseBase(this, _getItemsArray)[_getItemsArray]().splice(position, 1);

	    this.emit('Item:onRemove', {
	      item: result[0]
	    });
	  }
	}

	function _handleFileStateChange2$1(event) {
	  const {
	    file
	  } = event.getData();

	  const item = babelHelpers.classPrivateFieldLooseBase(this, _getItem)[_getItem](file.getId());

	  if (item) {
	    Object.assign(item, file.getState());
	  }
	}

	function _handleError2(event) {
	  const {
	    error
	  } = event.getData();
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderError)[_uploaderError].value = error.toJSON();
	  this.emit('Uploader:onError', event);
	}

	function _handleUploadStart2(event) {
	  this.emit('Uploader:onUploadStart', event);
	}

	function _handleUploadComplete2(event) {
	  this.emit('Uploader:onUploadComplete', event);
	}

	/**
	 * @memberof BX.UI.Uploader
	 */

	var _vueAdapter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("vueAdapter");

	var _uploaderOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderOptions");

	var _widgetOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("widgetOptions");

	var _vueApp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("vueApp");

	class VueUploaderWidget extends main_core_events.EventEmitter {
	  constructor(uploaderOptions, widgetOptions = {}) {
	    super();
	    Object.defineProperty(this, _vueAdapter, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploaderOptions, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _widgetOptions, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _vueApp, {
	      writable: true,
	      value: null
	    });
	    this.setEventNamespace('BX.UI.Uploader.Vue.Widget');
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderOptions)[_uploaderOptions] = uploaderOptions;
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetOptions)[_widgetOptions] = widgetOptions;
	  }

	  getRootComponent() {
	    return null;
	  }

	  getAdapter() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _vueAdapter)[_vueAdapter] === null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _vueAdapter)[_vueAdapter] = new VueUploaderAdapter(babelHelpers.classPrivateFieldLooseBase(this, _uploaderOptions)[_uploaderOptions]);
	    }

	    return babelHelpers.classPrivateFieldLooseBase(this, _vueAdapter)[_vueAdapter];
	  }

	  getUploader() {
	    return this.getAdapter().getUploader();
	  }

	  getVueApp() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp] !== null) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp];
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp] = ui_vue3.BitrixVue.createApp(this.getRootComponent(), {
	      uploaderOptions: babelHelpers.classPrivateFieldLooseBase(this, _uploaderOptions)[_uploaderOptions],
	      widgetOptions: babelHelpers.classPrivateFieldLooseBase(this, _widgetOptions)[_widgetOptions],
	      uploaderAdapter: this.getAdapter()
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp];
	  }

	  renderTo(node) {
	    if (main_core.Type.isDomNode(node)) {
	      this.getVueApp().mount(node);
	    }
	  }

	}

	/**
	 * @memberof BX.UI.Uploader
	 */

	const VueUploaderComponent = {
	  name: 'VueUploaderComponent',
	  props: {
	    uploaderOptions: {
	      type: Object
	    },
	    widgetOptions: {
	      type: Object,
	      default: {}
	    },
	    uploaderAdapter: {
	      type: Object,
	      default: null
	    }
	  },
	  data: () => ({
	    items: [],
	    uploaderError: null
	  }),

	  provide() {
	    return {
	      uploader: this.uploader,
	      adapter: this.adapter,
	      widgetOptions: this.widgetOptions
	    };
	  },

	  beforeCreate() {
	    this.adapter = this.uploaderAdapter === null ? new VueUploaderAdapter(this.uploaderOptions) : this.uploaderAdapter;
	    this.uploader = this.adapter.getUploader();
	  },

	  created() {
	    this.items = this.adapter.getItems();
	    this.uploaderError = this.adapter.getUploaderError();
	  },

	  unmounted() {
	    this.uploader.destroy();
	  }

	};

	const isImage = blob => {
	  return /^image\/[a-z0-9.-]+$/i.test(blob.type);
	};

	const Marker$1 = {
	  JPEG: 0xffd8,
	  APP1: 0xffe1,
	  EXIF: 0x45786966,
	  TIFF: 0x4949,
	  Orientation: 0x0112,
	  Unknown: 0xff00
	};

	const getUint16 = (view, offset, little = false) => view.getUint16(offset, little);

	const getUint32 = (view, offset, little = false) => view.getUint32(offset, little);

	const getJpegOrientation = file => {
	  return new Promise((resolve, reject) => {
	    const reader = new FileReader();

	    reader.onload = function (e) {
	      const view = new DataView(e.target.result);

	      if (getUint16(view, 0) !== Marker$1.JPEG) {
	        resolve(-1);
	        return;
	      }

	      const length = view.byteLength;
	      let offset = 2;

	      while (offset < length) {
	        const marker = getUint16(view, offset);
	        offset += 2; // APP1 Marker

	        if (marker === Marker$1.APP1) {
	          if (getUint32(view, offset += 2) !== Marker$1.EXIF) {
	            // no EXIF
	            break;
	          }

	          const little = getUint16(view, offset += 6) === Marker$1.TIFF;
	          offset += getUint32(view, offset + 4, little);
	          const tags = getUint16(view, offset, little);
	          offset += 2;

	          for (let i = 0; i < tags; i++) {
	            // Found the orientation tag
	            if (getUint16(view, offset + i * 12, little) === Marker$1.Orientation) {
	              resolve(getUint16(view, offset + i * 12 + 8, little));
	              return;
	            }
	          }
	        } else if ((marker & Marker$1.Unknown) !== Marker$1.Unknown) {
	          break; // Invalid
	        } else {
	          offset += getUint16(view, offset);
	        }
	      } // Nothing found


	      resolve(-1);
	    };

	    reader.readAsArrayBuffer(file.slice(0, 64 * 1024));
	  });
	};

	const isJpeg = blob => {
	  return /^image\/jpeg$/i.test(blob.type);
	};



	var index = /*#__PURE__*/Object.freeze({
		formatFileSize: formatFileSize,
		getFileExtension: getFileExtension,
		getFilenameWithoutExtension: getFilenameWithoutExtension,
		getExtensionFromType: getExtensionFromType,
		getJpegOrientation: getJpegOrientation,
		getArrayBuffer: getArrayBuffer,
		isDataUri: isDataUri,
		isImage: isImage,
		isResizableImage: isResizableImage,
		isJpeg: isJpeg,
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
	exports.UploaderEvent = UploaderEvent;
	exports.FileStatus = FileStatus;
	exports.FileOrigin = FileOrigin;
	exports.FileEvent = FileEvent;
	exports.FilterType = FilterType;
	exports.Helpers = index;
	exports.UploaderError = UploaderError;
	exports.VueUploaderAdapter = VueUploaderAdapter;
	exports.VueUploaderWidget = VueUploaderWidget;
	exports.VueUploaderComponent = VueUploaderComponent;
	exports.Server = Server;

}((this.BX.UI.Uploader = this.BX.UI.Uploader || {}),BX.Event,BX.UI.Uploader,BX.Vue3,BX));
//# sourceMappingURL=ui.uploader.bundle.js.map
