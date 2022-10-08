this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events,ui_uploader_core,ui_vue) {
	'use strict';

	const FileStatus = {
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

	const FileOrigin = {
	  CLIENT: 'client',
	  SERVER: 'server'
	};

	class AbstractUploadController extends main_core_events.EventEmitter {
	  constructor(server) {
	    super();
	    this.setEventNamespace('BX.UI.Uploader.UploadController');
	    this.server = server;
	  }

	  getServer() {
	    return this.server;
	  }

	  upload(file) {
	    throw new Error('You must implement upload() method.');
	  }

	  abort() {
	    throw new Error('You must implement abort() method.');
	  }

	}

	class AbstractLoadController extends main_core_events.EventEmitter {
	  constructor(server) {
	    super();
	    this.setEventNamespace('BX.UI.Uploader.LoadController');
	    this.server = server;
	  }

	  getServer() {
	    return this.server;
	  }

	  load(file) {
	    throw new Error('You must implement load() method.');
	  }

	  abort() {
	    throw new Error('You must implement abort() method.');
	  }

	}

	const crypto = window.crypto || window.msCrypto;

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
	  const filename = main_core.Type.isFile(file) ? file.name : file;
	  const type = main_core.Type.isFile(file) ? file.type : mimeType;
	  const extension = getFileExtension(filename).toLowerCase();

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

	var _setProperty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setProperty");

	class UploaderFile extends main_core_events.EventEmitter {
	  constructor(source, fileOptions = {}) {
	    super();
	    Object.defineProperty(this, _setProperty, {
	      value: _setProperty2
	    });
	    this.id = null;
	    this.file = null;
	    this.serverId = null;
	    this.name = '';
	    this.originalName = null;
	    this.size = 0;
	    this.type = '';
	    this.width = null;
	    this.height = null;
	    this.clientPreview = null;
	    this.clientPreviewUrl = null;
	    this.clientPreviewWidth = null;
	    this.clientPreviewHeight = null;
	    this.serverPreviewUrl = null;
	    this.serverPreviewWidth = null;
	    this.serverPreviewHeight = null;
	    this.downloadUrl = null;
	    this.removeUrl = null;
	    this.status = FileStatus.INIT;
	    this.origin = FileOrigin.CLIENT;
	    this.uploadController = null;
	    this.loadController = null;
	    this.setEventNamespace('BX.UI.Uploader.File');
	    const options = main_core.Type.isPlainObject(fileOptions) ? fileOptions : {};

	    if (main_core.Type.isFile(source)) {
	      this.file = source;
	    } else if (main_core.Type.isBlob(source)) {
	      this.file = createFileFromBlob(source, options.name || source.name);
	    } else if (isDataUri(source)) {
	      const blob = createBlobFromDataUri(source);
	      this.file = createFileFromBlob(blob, options.name);
	    } else if (main_core.Type.isNumber(source) || main_core.Type.isStringFilled(source)) {
	      this.origin = FileOrigin.SERVER;
	      this.serverId = source;

	      if (main_core.Type.isPlainObject(options)) {
	        this.setFile(options);
	      }
	    }

	    this.id = main_core.Type.isStringFilled(options.id) ? options.id : createUniqueId();
	    this.subscribeFromOptions(options.events); //this.fireStateChangeEvent = Runtime.debounce(this.fireStateChangeEvent, 0, this);
	  }

	  load() {
	    if (!this.canLoad()) {
	      return;
	    }

	    this.setStatus(FileStatus.LOADING);
	    this.emit('onLoadStart');
	    this.loadController.load(this);
	  }

	  upload() {
	    if (!this.canUpload()) {
	      return;
	    }

	    let event = new main_core_events.BaseEvent({
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
	    this.emitAsync('onPrepareFileAsync', event).then(result => {
	      const file = main_core.Type.isArrayFilled(result) && main_core.Type.isFile(result[0]) ? result[0] : this.getFile();
	      this.emit('onUploadStart');

	      if (this.uploadController) {
	        this.uploadController.upload(file);
	      }
	    }).catch(error => {
	      console.error(error);
	    });
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
	    if (this.uploadController) {
	      this.uploadController.abort();
	    }

	    this.setStatus(FileStatus.ABORTED);
	    this.emit('onAbort');
	  }

	  abortLoad() {
	    if (this.loadController) {
	      this.loadController.abort();
	    }

	    this.setStatus(FileStatus.ABORTED);
	    this.emit('onAbort');
	  }

	  cancel() {
	    this.abort();
	    this.emit('onCancel');
	  }

	  setUploadController(controller) {
	    this.uploadController = controller;
	  }

	  setLoadController(controller) {
	    this.loadController = controller;
	  }

	  isReadyToUpload() {
	    return this.getStatus() === FileStatus.PENDING;
	  }

	  isUploadable() {
	    return this.uploadController !== null;
	  }

	  isLoadable() {
	    return this.loadController !== null;
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

	  getFile() {
	    return this.file;
	  }
	  /**
	   * @internal
	   */


	  setFile(file) {
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

	  getName() {
	    return this.getFile() ? this.getFile().name : this.name;
	  }
	  /**
	   * @internal
	   */


	  setName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('name', name);
	    }
	  }

	  getOriginalName() {
	    return this.originalName ? this.originalName : this.getName();
	  }
	  /**
	   * @internal
	   */


	  setOriginalName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('originalName', name);
	    }
	  }

	  getExtension() {
	    const name = this.getOriginalName();
	    const position = name.lastIndexOf('.');
	    return position > 0 ? name.substring(position + 1).toLowerCase() : '';
	  }

	  getType() {
	    return this.getFile() ? this.getFile().type : this.type;
	  }
	  /**
	   * internal
	   */


	  setType(type) {
	    if (main_core.Type.isStringFilled(type)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('type', type);
	    }
	  }

	  getSize() {
	    return this.getFile() ? this.getFile().size : this.size;
	  }

	  getSizeFormatted() {
	    return formatFileSize(this.getSize());
	  }
	  /**
	   * @internal
	   */


	  setSize(size) {
	    if (main_core.Type.isNumber(size) && size >= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('size', size);
	    }
	  }

	  getId() {
	    return this.id;
	  }

	  getServerId() {
	    return this.serverId;
	  }

	  setServerId(id) {
	    if (main_core.Type.isNumber(id) || main_core.Type.isStringFilled(id)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('serverId', id);
	    }
	  }

	  getStatus() {
	    return this.status;
	  }

	  setStatus(status) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('status', status);

	    this.emit('onStatusChange');
	  }

	  getOrigin() {
	    return this.origin;
	  }

	  getDownloadUrl() {
	    return this.downloadUrl;
	  }

	  setDownloadUrl(url) {
	    if (main_core.Type.isStringFilled(url)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('downloadUrl', url);
	    }
	  }

	  getRemoveUrl() {
	    return this.removeUrl;
	  }

	  setRemoveUrl(url) {
	    if (main_core.Type.isStringFilled(url)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('removeUrl', url);
	    }
	  }

	  getWidth() {
	    return this.width;
	  }

	  setWidth(width) {
	    if (main_core.Type.isNumber(width)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('width', width);
	    }
	  }

	  getHeight() {
	    return this.height;
	  }

	  setHeight(height) {
	    if (main_core.Type.isNumber(height)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('height', height);
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
	    return this.clientPreview;
	  }

	  setClientPreview(file, width = null, height = null) {
	    if (main_core.Type.isFile(file) || main_core.Type.isNull(file)) {
	      this.revokeClientPreviewUrl();

	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('clientPreview', file);

	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('clientPreviewWidth', width);

	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('clientPreviewHeight', height);
	    }
	  }

	  getClientPreviewUrl() {
	    if (this.clientPreviewUrl === null && this.getClientPreview() !== null) {
	      this.clientPreviewUrl = URL.createObjectURL(this.getClientPreview());
	    }

	    return this.clientPreviewUrl;
	  }

	  revokeClientPreviewUrl() {
	    if (this.clientPreviewUrl !== null) {
	      URL.revokeObjectURL(this.clientPreviewUrl);
	    }

	    this.clientPreviewUrl = null;
	  }

	  getClientPreviewWidth() {
	    return this.clientPreviewWidth;
	  }

	  getClientPreviewHeight() {
	    return this.clientPreviewHeight;
	  }

	  getServerPreviewUrl() {
	    return this.serverPreviewUrl;
	  }

	  setServerPreview(url, width = null, height = null) {
	    if (main_core.Type.isStringFilled(url) || main_core.Type.isNull(url)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('serverPreviewUrl', url);

	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('serverPreviewWidth', width);

	      babelHelpers.classPrivateFieldLooseBase(this, _setProperty)[_setProperty]('serverPreviewHeight', height);
	    }
	  }

	  getServerPreviewWidth() {
	    return this.serverPreviewWidth;
	  }

	  getServerPreviewHeight() {
	    return this.serverPreviewHeight;
	  }

	  isImage() {
	    return isResizableImage(this.getOriginalName(), this.getType());
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

	function _setProperty2(name, value) {
	  this[name] = value;
	  this.emit('onStateChange');
	}

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
	    this.origin = 'client';
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
	      error.setOrigin('server');
	      return error;
	    } else {
	      let {
	        code,
	        message,
	        description,
	        customData
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
	      error.setOrigin('server');
	      return error;
	    }
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
	    if (main_core.Type.isStringFilled(origin)) {
	      this.origin = origin;
	    }

	    return this;
	  }

	  clone() {
	    const options = JSON.parse(JSON.stringify(this));
	    const error = new UploaderError(options.code, options.message, options.description, options.customData);
	    error.setOrigin(options.origin);
	    return error;
	  }

	  toJSON() {
	    return {
	      code: this.getCode(),
	      message: this.getMessage(),
	      description: this.getDescription(),
	      origin: this.getOrigin(),
	      customData: this.getCustomData()
	    };
	  }

	}

	class Chunk {
	  constructor(data, offset) {
	    this.data = null;
	    this.offset = 0;
	    this.retries = [];
	    this.data = data;
	    this.offset = offset;
	  }

	  getNextRetryDelay() {
	    if (this.retries.length === 0) {
	      return null;
	    }

	    return this.retries.shift();
	  }

	  setRetries(retries) {
	    if (main_core.Type.isArray(retries)) {
	      this.retries = retries;
	    }
	  }

	  getData() {
	    return this.data;
	  }

	  getOffset() {
	    return this.offset;
	  }

	  getSize() {
	    return this.getData().size;
	  }

	}

	var _uploadChunk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadChunk");

	var _retryUploadChunk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("retryUploadChunk");

	var _getNextChunk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNextChunk");

	class UploadController extends AbstractUploadController {
	  constructor(server) {
	    super(server);
	    Object.defineProperty(this, _getNextChunk, {
	      value: _getNextChunk2
	    });
	    Object.defineProperty(this, _retryUploadChunk, {
	      value: _retryUploadChunk2
	    });
	    Object.defineProperty(this, _uploadChunk, {
	      value: _uploadChunk2
	    });
	    this.file = null;
	    this.chunkOffset = null;
	    this.chunkTimeout = null;
	    this.token = null;
	    this.xhr = null;
	    this.aborted = false;
	  }

	  upload(file) {
	    if (this.chunkOffset !== null) {
	      return;
	    }

	    this.file = file;

	    const nextChunk = babelHelpers.classPrivateFieldLooseBase(this, _getNextChunk)[_getNextChunk]();

	    if (nextChunk) {
	      babelHelpers.classPrivateFieldLooseBase(this, _uploadChunk)[_uploadChunk](nextChunk);
	    }
	  }

	  abort() {
	    if (this.xhr) {
	      this.aborted = true;
	      this.xhr.abort();
	      this.xhr = null;
	    }

	    this.emit('onAbort');
	    clearTimeout(this.chunkTimeout);
	  }

	  getFile() {
	    return this.file;
	  }

	  getChunkSize() {
	    return this.getServer().getChunkSize();
	  }

	  getChunkOffset() {
	    return this.chunkOffset;
	  }

	  getToken() {
	    return this.token;
	  }

	  setToken(token) {
	    if (main_core.Type.isStringFilled(token)) {
	      this.token = token;
	    }
	  }

	}

	function _uploadChunk2(chunk) {
	  const totalSize = this.getFile().size;
	  const isOnlyOneChunk = chunk.getOffset() === 0 && totalSize === chunk.getSize();
	  let fileName = this.getFile().name;

	  if (fileName.normalize) {
	    fileName = fileName.normalize();
	  }

	  const headers = [{
	    name: 'Content-Type',
	    value: this.getFile().type
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
	      this.xhr = xhr;
	      this.aborted = false;
	    },
	    onprogressupload: event => {
	      if (event.lengthComputable) {
	        const size = this.getFile().size;
	        const uploadedBytes = Math.min(size, chunk.getOffset() + event.loaded);
	        const progress = size > 0 ? Math.floor(uploadedBytes / size * 100) : 100;
	        this.emit('onProgress', {
	          progress
	        });
	      }
	    }
	  }).then(response => {
	    console.log('response', response);

	    if (response.data.token) {
	      this.setToken(response.data.token);
	      const size = this.getFile().size;
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
	    if (this.aborted) {
	      return;
	    }

	    const error = UploaderError.createFromAjaxErrors(response.errors);
	    const shouldRetry = error.getCode() === 'NETWORK_ERROR';

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

	  clearTimeout(this.chunkTimeout);
	  this.chunkTimeout = setTimeout(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadChunk)[_uploadChunk](chunk);
	  }, nextDelay);
	  return true;
	}

	function _getNextChunk2() {
	  if (this.getChunkOffset() !== null && this.getChunkOffset() >= this.getFile().size) {
	    // End of File
	    return null;
	  }

	  if (this.getChunkOffset() === null) {
	    // First call
	    this.chunkOffset = 0;
	  }

	  let chunk;

	  if (this.getChunkOffset() === 0 && this.getFile().size <= this.getChunkSize()) {
	    chunk = new Chunk(this.getFile(), this.getChunkOffset());
	    this.chunkOffset = this.getFile().size;
	  } else {
	    const currentChunkSize = Math.min(this.getChunkSize(), this.getFile().size - this.getChunkOffset());
	    const nextOffset = this.getChunkOffset() + currentChunkSize;
	    const fileRange = this.getFile().slice(this.getChunkOffset(), nextOffset);
	    chunk = new Chunk(fileRange, this.getChunkOffset());
	    this.chunkOffset = nextOffset;
	  }

	  chunk.setRetries([...this.getServer().getChunkRetryDelays()]);
	  return chunk;
	}

	const queues = new WeakMap();
	function loadMultiple(controller, file) {
	  const server = controller.getServer();
	  let queue = queues.get(server);

	  if (!queue) {
	    queue = {
	      tasks: [],
	      load: main_core.Runtime.debounce(loadInternal, 100, server),
	      xhr: null
	    };
	    queues.set(server, queue);
	  }

	  queue.tasks.push({
	    controller,
	    file
	  });
	  queue.load();
	}
	function abort(controller) {
	  const server = controller.getServer();
	  const queue = queues.get(server);

	  if (queue) {
	    queue.xhr.abort();
	    queue.xhr = null;
	    queues.delete(server);
	    tasks.forEach(task => {
	      const {
	        controller,
	        file
	      } = task;
	      controller.emit('onAbort');
	    });
	  }
	}

	function loadInternal() {
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
	    const {
	      controller,
	      file
	    } = task;
	    fileIds.push(file.getServerId());
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
	        tasks.forEach(task => {
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
	      tasks.forEach(task => {
	        const {
	          controller,
	          file
	        } = task;
	        const fileResult = fileResults[file.getServerId()] || null;

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

	class ServerLoadController extends AbstractLoadController {
	  constructor(server) {
	    super(server);
	  }

	  load(file) {
	    if (this.getServer().getController()) {
	      loadMultiple(this, file);
	    } else {
	      this.emit('onProgress', {
	        file,
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

	  abort() {
	    if (this.getServer().getController()) {
	      abort(this);
	    }
	  }

	}

	class ClientLoadController extends AbstractLoadController {
	  constructor(server) {
	    super(server);
	  }

	  load(file) {
	    if (main_core.Type.isFile(file.getFile())) {
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

	var _calcChunkSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calcChunkSize");

	class Server {
	  constructor(serverOptions) {
	    Object.defineProperty(this, _calcChunkSize, {
	      value: _calcChunkSize2
	    });
	    this.controller = null;
	    this.controllerOptions = null;
	    this.uploadControllerClass = null;
	    this.loadControllerClass = null;
	    this.chunkSize = null;
	    this.defaultChunkSize = null;
	    this.chunkMinSize = null;
	    this.chunkMaxSize = null;
	    this.chunkRetryDelays = [1000, 3000, 6000];
	    const options = main_core.Type.isPlainObject(serverOptions) ? serverOptions : {};
	    this.controller = main_core.Type.isStringFilled(options.controller) ? options.controller : null;
	    this.controllerOptions = main_core.Type.isPlainObject(options.controllerOptions) ? options.controllerOptions : null;

	    const _chunkSize = main_core.Type.isNumber(options.chunkSize) && options.chunkSize > 0 ? options.chunkSize : this.getDefaultChunkSize();

	    this.chunkSize = options.forceChunkSize === true ? _chunkSize : babelHelpers.classPrivateFieldLooseBase(this, _calcChunkSize)[_calcChunkSize](_chunkSize);

	    if (options.chunkRetryDelays === false || options.chunkRetryDelays === null) {
	      this.chunkRetryDelays = [];
	    } else if (main_core.Type.isArray(options.chunkRetryDelays)) {
	      this.chunkRetryDelays = options.chunkRetryDelays;
	    }

	    ['uploadControllerClass', 'loadControllerClass'].forEach(controllerClass => {
	      if (main_core.Type.isStringFilled(options[controllerClass])) {
	        this[controllerClass] = main_core.Runtime.getClass(options[controllerClass]);

	        if (!main_core.Type.isFunction(options[controllerClass])) {
	          throw new Error(`Uploader.Server: "${controllerClass}" must be a function.`);
	        }
	      } else if (main_core.Type.isFunction(options[controllerClass])) {
	        this[controllerClass] = options[controllerClass];
	      }
	    });
	  }

	  createUploadController() {
	    if (this.uploadControllerClass) {
	      const controller = new this.uploadControllerClass(this);

	      if (!(controller instanceof AbstractUploadController)) {
	        throw new Error('Uploader.Server: "uploadControllerClass" must be an instance of AbstractUploadController.');
	      }

	      return controller;
	    } else if (main_core.Type.isStringFilled(this.controller)) {
	      return new UploadController(this);
	    }

	    return null;
	  }

	  createLoadController() {
	    if (this.loadControllerClass) {
	      const controller = new this.loadControllerClass(this);

	      if (!(controller instanceof AbstractLoadController)) {
	        throw new Error('Uploader.Server: "loadControllerClass" must be an instance of AbstractLoadController.');
	      }

	      return controller;
	    }

	    return new ServerLoadController(this);
	  }

	  createClientLoadController() {
	    return new ClientLoadController(this);
	  }

	  getController() {
	    return this.controller;
	  }

	  getControllerOptions() {
	    return this.controllerOptions;
	  }

	  getChunkSize() {
	    return this.chunkSize;
	  }

	  getDefaultChunkSize() {
	    if (this.defaultChunkSize === null) {
	      const settings = main_core.Extension.getSettings('ui.uploader.core');
	      this.defaultChunkSize = settings.get('defaultChunkSize', 5 * 1024 * 1024);
	    }

	    return this.defaultChunkSize;
	  }

	  getChunkMinSize() {
	    if (this.chunkMinSize === null) {
	      const settings = main_core.Extension.getSettings('ui.uploader.core');
	      this.chunkMinSize = settings.get('chunkMinSize', 1024 * 1024);
	    }

	    return this.chunkMinSize;
	  }

	  getChunkMaxSize() {
	    if (this.chunkMaxSize === null) {
	      const settings = main_core.Extension.getSettings('ui.uploader.core');
	      this.chunkMaxSize = settings.get('chunkMaxSize', 5 * 1024 * 1024);
	    }

	    return this.chunkMaxSize;
	  }

	  getChunkRetryDelays() {
	    return this.chunkRetryDelays;
	  }

	}

	function _calcChunkSize2(chunkSize) {
	  return Math.min(Math.max(this.getChunkMinSize(), chunkSize), this.getChunkMaxSize());
	}

	class Filter {
	  constructor(uploader, filterOptions = {}) {
	    this.uploader = null;
	    this.uploader = uploader;
	  }

	  getUploader() {
	    return this.uploader;
	  }
	  /**
	   * @abstract
	   */


	  apply(...args) {
	    throw new Error('You must implement apply() method.');
	  }

	}

	class FileSizeFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    this.maxFileSize = null;
	    this.minFileSize = null;
	    this.maxTotalFileSize = null;
	    this.imageMaxFileSize = null;
	    this.imageMinFileSize = null;
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    const integerOptions = ['maxFileSize', 'minFileSize', 'maxTotalFileSize', 'imageMaxFileSize', 'imageMinFileSize'];
	    integerOptions.forEach(option => {
	      this[option] = main_core.Type.isNumber(options[option]) && options[option] >= 0 ? options[option] : this[option];
	    });
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (this.maxFileSize !== null && file.getSize() > this.maxFileSize) {
	        reject(new UploaderError('MAX_FILE_SIZE_EXCEEDED', {
	          maxFileSize: formatFileSize(this.maxFileSize),
	          maxFileSizeInBytes: this.maxFileSize
	        }));
	        return;
	      }

	      if (this.minFileSize !== null && file.getSize() < this.minFileSize) {
	        reject(new UploaderError('MIN_FILE_SIZE_EXCEEDED', {
	          minFileSize: formatFileSize(this.minFileSize),
	          minFileSizeInBytes: this.minFileSize
	        }));
	        return;
	      }

	      if (file.isImage()) {
	        if (this.imageMaxFileSize !== null && file.getSize() > this.imageMaxFileSize) {
	          reject(new UploaderError('IMAGE_MAX_FILE_SIZE_EXCEEDED', {
	            imageMaxFileSize: formatFileSize(this.imageMaxFileSize),
	            imageMaxFileSizeInBytes: this.imageMaxFileSize
	          }));
	          return;
	        }

	        if (this.imageMinFileSize !== null && file.getSize() < this.imageMinFileSize) {
	          reject(new UploaderError('IMAGE_MIN_FILE_SIZE_EXCEEDED', {
	            imageMinFileSize: formatFileSize(this.imageMinFileSize),
	            imageMinFileSizeInBytes: this.imageMinFileSize
	          }));
	          return;
	        }
	      }

	      if (this.maxTotalFileSize !== null) {
	        if (this.getUploader().getTotalSize() > this.maxTotalFileSize) {
	          reject(new UploaderError('MAX_TOTAL_FILE_SIZE_EXCEEDED', {
	            maxTotalFileSize: formatFileSize(this.maxTotalFileSize),
	            maxTotalFileSizeInBytes: this.maxTotalFileSize
	          }));
	          return;
	        }
	      }

	      resolve();
	    });
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
	      if (isValidFileType(file.getFile(), this.getUploader().getAcceptedFileTypes())) {
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
	        return resolve(null);
	      }

	      const blob = file.slice(0, 10);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

	        if (!compareBuffers(view, GIF87a, 0) && !compareBuffers(view, GIF89a, 0)) {
	          return resolve(null);
	        }

	        resolve({
	          width: view.getUint16(6, true),
	          height: view.getUint16(8, true)
	        });
	      }).catch(() => {
	        resolve(null);
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
	        return resolve(null);
	      }

	      const blob = file.slice(0, 40);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

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
	      }).catch(() => {
	        resolve(null);
	      });
	    });
	  }

	}

	const BMP_SIGNATURE = 0x424d; // BM

	class Bmp {
	  getSize(file) {
	    return new Promise((resolve, reject) => {
	      if (file.size < 26) {
	        return resolve(null);
	      }

	      const blob = file.slice(0, 26);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

	        if (!view.getUint16(0) === BMP_SIGNATURE) {
	          return resolve(null);
	        }

	        resolve({
	          width: view.getUint32(18, true),
	          height: Math.abs(view.getInt32(22, true))
	        });
	      }).catch(() => {
	        resolve(null);
	      });
	    });
	  }

	}

	const EXIF_SIGNATURE = convertStringToBuffer('Exif\0\0');
	class Jpeg {
	  getSize(file) {
	    return new Promise((resolve, reject) => {
	      if (file.size < 2) {
	        return resolve(null);
	      }

	      getArrayBuffer(file).then(buffer => {
	        const view = new DataView(buffer);

	        if (view.getUint8(0) !== 0xFF || view.getUint8(1) !== 0xD8) {
	          resolve(null);
	        }

	        let offset = 2;
	        let orientation = -1;

	        for (;;) {
	          if (view.byteLength - offset < 2) {
	            return resolve(null);
	          }

	          if (view.getUint8(offset++) !== 0xFF) {
	            return resolve(null);
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
	            const exifBlock = new DataView(view.buffer, offset + 6, offset + length);
	            orientation = getOrientation(exifBlock);
	          }

	          if (length >= 5 && 0xC0 <= code && code <= 0xCF && code !== 0xC4 && code !== 0xC8 && code !== 0xCC) {
	            if (view.byteLength - offset < length) {
	              return resolve(null);
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
	      }).catch(() => {
	        resolve(null);
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
	        return resolve(null);
	      }

	      const blob = file.slice(0, 30);
	      getArrayBuffer(blob).then(buffer => {
	        const view = new DataView(buffer);

	        if (view.getUint32(0) !== RIFF_HEADER && view.getUint32(8) !== WEBP_SIGNATURE) {
	          return resolve(null);
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

	        resolve(null);
	      }).catch(() => {
	        resolve(null);
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
	    return Promise.resolve(null);
	  }

	  const extension = getFileExtension(file.name).toLowerCase();
	  const type = file.type.replace(/^image\//, '');
	  const typeHandler = typeHandlers[extension] || typeHandlers[type];

	  if (!typeHandler) {
	    return Promise.resolve(null);
	  }

	  return typeHandler.getSize(file);
	};

	class ImageSizeFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    this.imageMinWidth = 1;
	    this.imageMinHeight = 1;
	    this.imageMaxWidth = 10000;
	    this.imageMaxHeight = 10000;
	    this.ignoreUnknownImageTypes = false;
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    ['imageMinWidth', 'imageMinHeight', 'imageMaxWidth', 'imageMaxHeight'].forEach(option => {
	      this[option] = main_core.Type.isNumber(options[option]) && options[option] > 0 ? options[option] : this[option];
	    });

	    if (main_core.Type.isBoolean(options['ignoreUnknownImageTypes'])) {
	      this.ignoreUnknownImageTypes = options['ignoreUnknownImageTypes'];
	    }
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (!file.isImage()) {
	        resolve();
	        return;
	      }

	      getImageSize(file.getFile()).then(({
	        width,
	        height
	      }) => {
	        file.setWidth(width);
	        file.setHeight(height);

	        if (width < this.imageMinWidth || height < this.imageMinHeight) {
	          reject(new UploaderError('IMAGE_IS_TOO_SMALL', {
	            minWidth: this.imageMinWidth,
	            minHeight: this.imageMinHeight
	          }));
	        } else if (width > this.imageMaxWidth || height > this.imageMaxHeight) {
	          reject(new UploaderError('IMAGE_IS_TOO_BIG', {
	            maxWidth: this.imageMaxWidth,
	            maxHeight: this.imageMaxHeight
	          }));
	        } else {
	          resolve();
	        }
	      }).catch(() => {
	        if (this.ignoreUnknownImageTypes) {
	          resolve();
	        } else {
	          reject(new UploaderError('IMAGE_TYPE_NOT_SUPPORTED'));
	        }
	      });
	    });
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
	    createImageBitmap(event.data.message.file).then(bitmap => {
	      self.postMessage({
	        id: event.data.id,
	        message: bitmap
	      }, [bitmap]);
	    }).catch(() => {
	      self.postMessage({
	        id: event.data.id,
	        message: null
	      }, []);
	    });
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

	const canCreateImageBitmap = 'createImageBitmap' in window && typeof ImageBitmap !== 'undefined' && ImageBitmap.prototype && ImageBitmap.prototype.close;

	const resizeImage = (file, options) => {
	  return new Promise((resolve, reject) => {
	    const loadImageDataFallback = () => {
	      loadImage(file).then(({
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
	        targetHeight
	      } = calcTargetSize(imageData, options);

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

	      const canvas = createImagePreview(imageData, targetWidth, targetHeight); // if it was ImageBitmap

	      if ('close' in imageData) {
	        imageData.close();
	      }

	      const {
	        quality = 0.92,
	        mimeType = 'image/jpeg'
	      } = options;
	      const type = /jpeg|png|webp/.test(file.type) ? file.type : mimeType;
	      convertCanvasToBlob(canvas, type, quality).then(blob => {
	        const newFileName = renameFileToMatchMimeType(file.name, type);
	        const preview = createFileFromBlob(blob, newFileName);
	        resolve({
	          preview,
	          width: targetWidth,
	          height: targetHeight
	        });
	      }).catch(() => {
	        reject();
	      });
	    };

	    if (canCreateImageBitmap) {
	      const bitmapWorker = createWorker(BitmapWorker);
	      bitmapWorker.post({
	        file
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

	const calcTargetSize = (imageData, options = {}) => {
	  let {
	    mode = 'contain',
	    upscale = false,
	    width,
	    height
	  } = options;
	  const result = {
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
	    const ratioWidth = width / imageData.width;
	    const ratioHeight = height / imageData.height;
	    let ratio = 1;

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

	class ImagePreviewFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    this.imagePreviewWidth = 300;
	    this.imagePreviewHeight = 300;
	    this.imagePreviewQuality = 0.92;
	    this.imagePreviewMimeType = 'image/jpeg';
	    this.imagePreviewUpscale = false;
	    this.imagePreviewResizeMethod = 'contain';
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    const integerOptions = ['imagePreviewWidth', 'imagePreviewHeight', 'imagePreviewQuality'];
	    integerOptions.forEach(option => {
	      this[option] = main_core.Type.isNumber(options[option]) && options[option] > 0 ? options[option] : this[option];
	    });

	    if (main_core.Type.isBoolean(options['imagePreviewUpscale'])) {
	      this.imagePreviewUpscale = options['imagePreviewUpscale'];
	    }

	    if (['contain', 'force', 'cover'].includes(options['imagePreviewResizeMethod'])) {
	      this.imagePreviewResizeMethod = options['imagePreviewResizeMethod'];
	    }

	    if (['image/jpeg', 'image/png'].includes(options['imagePreviewMimeType'])) {
	      this.imagePreviewMimeType = options['imagePreviewMimeType'];
	    }
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (!isResizableImage(file.getFile())) {
	        resolve();
	        return;
	      }

	      const options = {
	        width: this.imagePreviewWidth,
	        height: this.imagePreviewHeight,
	        mode: this.imagePreviewResizeMethod,
	        upscale: this.imagePreviewUpscale,
	        quality: this.imagePreviewQuality,
	        mimeType: this.imagePreviewMimeType
	      };
	      resizeImage(file.getFile(), options).then(({
	        preview,
	        width,
	        height
	      }) => {
	        //setTimeout(() => {
	        file.setClientPreview(preview, width, height);
	        resolve(); //}, 60000);
	      }).catch(() => {
	        resolve();
	      });
	    });
	  }

	}

	class TransformImageFilter extends Filter {
	  constructor(uploader, filterOptions = {}) {
	    super(uploader);
	    this.resizeWidth = null;
	    this.resizeHeight = null;
	    this.resizeMethod = 'contain';
	    this.resizeMimeType = 'image/jpeg';
	    this.resizeQuality = 0.92;
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};

	    if (main_core.Type.isNumber(options['imageResizeWidth']) && options['imageResizeWidth'] > 0) {
	      this.resizeWidth = options['imageResizeWidth'];
	    }

	    if (main_core.Type.isNumber(options['imageResizeHeight']) && options['imageResizeHeight'] > 0) {
	      this.resizeHeight = options['imageResizeHeight'];
	    }

	    if (['contain', 'force', 'cover'].includes(options['imageResizeMethod'])) {
	      this.resizeMethod = options['imageResizeMethod'];
	    }

	    if (main_core.Type.isNumber(options['imageResizeQuality'])) {
	      this.resizeQuality = Math.min(Math.max(0.1, options['imageResizeQuality']), 1);
	    }

	    if (['image/jpeg', 'image/png'].includes(options['imageResizeMimeType'])) {
	      this.resizeMimeType = options['imageResizeMimeType'];
	    }
	  }

	  apply(file) {
	    return new Promise((resolve, reject) => {
	      if (!isResizableImage(file)) {
	        return resolve(file);
	      }

	      if (this.resizeWidth === null && this.resizeHeight === null) {
	        return resolve(file);
	      }

	      const options = {
	        width: this.resizeWidth,
	        height: this.resizeHeight,
	        mode: this.resizeMethod,
	        quality: this.resizeQuality,
	        mimeType: this.resizeMimeType
	      };
	      resizeImage(file, options).then(({
	        preview
	      }) => {
	        resolve(preview);
	      }).catch(() => {
	        resolve(file);
	      });
	    });
	  }

	}

	const UploaderStatus = {
	  STARTED: 0,
	  STOPPED: 1
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

	var _setLoadController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setLoadController");

	var _setUploadController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setUploadController");

	var _exceedsMaxFileCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("exceedsMaxFileCount");

	var _applyFilters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyFilters");

	var _uploadNext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadNext");

	var _loadNext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadNext");

	var _setHiddenField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setHiddenField");

	var _resetHiddenField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resetHiddenField");

	var _syncInputPositions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("syncInputPositions");

	class Uploader extends main_core_events.EventEmitter {
	  constructor(uploaderOptions) {
	    super();
	    Object.defineProperty(this, _syncInputPositions, {
	      value: _syncInputPositions2
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
	    Object.defineProperty(this, _applyFilters, {
	      value: _applyFilters2
	    });
	    Object.defineProperty(this, _exceedsMaxFileCount, {
	      value: _exceedsMaxFileCount2
	    });
	    Object.defineProperty(this, _setUploadController, {
	      value: _setUploadController2
	    });
	    Object.defineProperty(this, _setLoadController, {
	      value: _setLoadController2
	    });
	    this.files = [];
	    this.multiple = false;
	    this.autoUpload = true;
	    this.allowReplaceSingle = true;
	    this.maxParallelUploads = 2;
	    this.maxParallelLoads = 10;
	    this.acceptOnlyImages = false;
	    this.acceptedFileTypes = [];
	    this.ignoredFileNames = ['.ds_store', 'thumbs.db', 'desktop.ini'];
	    this.maxFileCount = null;
	    this.server = null;
	    this.hiddenFields = new Map();
	    this.hiddenFieldsContainer = null;
	    this.hiddenFieldName = 'file';
	    this.assignAsFile = false;
	    this.filters = new Map();
	    this.status = UploaderStatus.STOPPED;
	    this.setEventNamespace('BX.UI.Uploader');
	    const options = main_core.Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
	    this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple : false;
	    this.acceptOnlyImages = main_core.Type.isBoolean(options.acceptOnlyImages) ? options.acceptOnlyImages : false;
	    this.setAutoUpload(options.autoUpload);
	    this.setMaxParallelUploads(options.maxParallelUploads);
	    this.setMaxParallelLoads(options.maxParallelLoads);

	    if (this.acceptOnlyImages) {
	      const settings = main_core.Extension.getSettings('ui.uploader.core');
	      const imageExtensions = settings.get('imageExtensions', 'jpg,bmp,jpeg,jpe,gif,png,webp');
	      this.setAcceptedFileTypes(imageExtensions);
	    }

	    this.setAcceptedFileTypes(options.acceptedFileTypes);
	    this.setIgnoredFileNames(options.ignoredFileNames);
	    this.setMaxFileCount(options.maxFileCount);
	    this.setAllowReplaceSingle(options.allowReplaceSingle);
	    this.assignBrowse(options.browseElement);
	    this.assignDropzone(options.dropElement);
	    this.assignPaste(options.pasteElement);
	    this.setHiddenFieldsContainer(options.hiddenFieldsContainer);
	    this.setHiddenFieldName(options.hiddenFieldName);
	    this.setAssignAsFile(options.assignAsFile);
	    let serverOptions = main_core.Type.isPlainObject(options.serverOptions) ? options.serverOptions : {};
	    serverOptions = Object.assign({}, {
	      controller: options.controller,
	      controllerOptions: options.controllerOptions
	    }, serverOptions);
	    this.server = new Server(serverOptions);
	    this.subscribeFromOptions(options.events);
	    this.addFilter(FilterType.VALIDATION, new FileSizeFilter(this, options));
	    this.addFilter(FilterType.VALIDATION, new FileTypeFilter(this, options));
	    this.addFilter(FilterType.VALIDATION, new ImageSizeFilter(this, options));
	    this.addFilter(FilterType.VALIDATION, new ImagePreviewFilter(this, options));
	    this.addFilter(FilterType.PREPARATION, new TransformImageFilter(this, options));
	    this.addFilters(options.filters);
	    this.handleBeforeUpload = this.handleBeforeUpload.bind(this);
	    this.handlePrepareFileAsync = this.handlePrepareFileAsync.bind(this);
	    this.handleUploadStart = this.handleBeforeUpload.bind(this);
	    this.handleFileCancel = this.handleFileCancel.bind(this);
	    this.handleFileStatusChange = this.handleFileStatusChange.bind(this);
	    this.handleFileStateChange = this.handleFileStateChange.bind(this);
	    this.addFiles(options.files);
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

	    if (!this.isMultiple() && this.shouldReplaceSingle() && this.getFiles().length > 0) {
	      const fileToReplace = this.getFiles()[0];
	      this.removeFile(fileToReplace);
	    }

	    const event = new main_core_events.BaseEvent({
	      data: {
	        file: file
	      }
	    });
	    this.emit('File:onBeforeAdd', event);

	    if (event.isDefaultPrevented()) {
	      return;
	    }

	    babelHelpers.classPrivateFieldLooseBase(this, _setLoadController)[_setLoadController](file);

	    babelHelpers.classPrivateFieldLooseBase(this, _setUploadController)[_setUploadController](file);

	    this.files.push(file);
	    file.setStatus(FileStatus.ADDED);
	    this.emit('File:onAddStart', {
	      file
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
	      babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	    }
	  }

	  start() {
	    if (this.getStatus() !== UploaderStatus.STARTED && this.getPendingFileCount() > 0) {
	      this.status = UploaderStatus.STARTED;
	      this.emit('onUploadStart');

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadNext)[_uploadNext]();
	    }
	  }

	  stop() {
	    this.status = UploaderStatus.STOPPED;
	    this.getFiles().forEach(file => {
	      if (file.isUploading()) {
	        file.abort();
	        file.setStatus(FileStatus.PENDING);
	      }
	    });
	    this.emit('onStop');
	  }

	  cancel() {
	    this.getFiles().forEach(file => {
	      file.cancel();
	    });
	  }

	  destroy() {
	    this.emit('onDestroy'); // TODO
	    // unassignBrowse
	    // unassignDrop

	    this.getFiles().forEach(file => {
	      file.cancel();
	    });

	    for (const property in this) {
	      if (this.hasOwnProperty(property)) {
	        delete this[property];
	      }
	    }

	    Object.setPrototypeOf(this, null);
	  }

	  removeFile(file) {
	    if (main_core.Type.isString(file)) {
	      file = this.getFile(file);
	    }

	    const index = this.files.findIndex(element => element === file);

	    if (index >= 0) {
	      this.files.splice(index, 1);
	      file.abort();
	      file.setStatus(FileStatus.INIT);
	      this.emit('File:onRemove', {
	        file
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _resetHiddenField)[_resetHiddenField](file);
	    }
	  }

	  getFile(id) {
	    return this.getFiles().find(file => file.getId() === id) || null;
	  }

	  getFiles() {
	    return this.files;
	  }

	  isMultiple() {
	    return this.multiple;
	  }

	  getStatus() {
	    return this.status;
	  }

	  addFilter(type, filter, filterOptions = {}) {
	    if (main_core.Type.isFunction(filter) || main_core.Type.isString(filter)) {
	      const className = main_core.Type.isString(filter) ? main_core.Reflection.getClass(filter) : filter;

	      if (main_core.Type.isFunction(className)) {
	        filter = new className(this, filterOptions);
	      }
	    }

	    if (filter instanceof Filter) {
	      let filters = this.filters.get(type);

	      if (!main_core.Type.isArray(filters)) {
	        filters = [];
	        this.filters.set(type, filters);
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
	    return this.server;
	  }

	  assignBrowse(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (!main_core.Type.isElementNode(node)) {
	        return;
	      }

	      let input = null;

	      if (node.tagName === 'INPUT' && node.type === 'file') {
	        input = node; // Add already selected files

	        if (input.files) {
	          this.addFiles(input.files);
	        }

	        const acceptAttr = input.getAttribute('accept');

	        if (main_core.Type.isStringFilled(acceptAttr)) {
	          this.setAcceptedFileTypes(acceptAttr);
	        }
	      } else {
	        input = document.createElement('input');
	        input.setAttribute('type', 'file');
	        main_core.Event.bind(node, 'click', () => {
	          input.click();
	        });
	      }

	      if (this.isMultiple()) {
	        input.setAttribute('multiple', 'multiple');
	      }

	      if (main_core.Type.isArrayFilled(this.getAcceptedFileTypes())) {
	        input.setAttribute('accept', this.getAcceptedFileTypes().join(','));
	      }

	      main_core.Event.bind(input, 'change', () => {
	        this.addFiles(Array.from(input.files)); // reset file input

	        input.value = '';
	      });
	    });
	  }

	  assignDropzone(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (!main_core.Type.isElementNode(node)) {
	        return;
	      }

	      main_core.Event.bind(node, 'dragover', event => {
	        event.preventDefault();
	      });
	      main_core.Event.bind(node, 'dragenter', event => {
	        event.preventDefault();
	      });
	      main_core.Event.bind(node, 'drop', event => {
	        event.preventDefault();
	        getFilesFromDataTransfer(event.dataTransfer).then(files => {
	          this.addFiles(files);
	        });
	      });
	    });
	  }

	  assignPaste(nodes) {
	    nodes = main_core.Type.isElementNode(nodes) ? [nodes] : nodes;

	    if (!main_core.Type.isArray(nodes)) {
	      return;
	    }

	    nodes.forEach(node => {
	      if (!main_core.Type.isElementNode(node)) {
	        return;
	      }

	      main_core.Event.bind(node, 'paste', event => {
	        event.preventDefault();
	        const clipboardData = event.clipboardData;

	        if (!clipboardData) {
	          return;
	        }

	        getFilesFromDataTransfer(clipboardData).then(files => {
	          this.addFiles(files);
	        });
	      });
	    });
	  }

	  getHiddenFieldsContainer() {
	    let element = null;

	    if (main_core.Type.isStringFilled(this.hiddenFieldsContainer)) {
	      element = document.querySelector(this.hiddenFieldsContainer);
	    } else if (main_core.Type.isElementNode(this.hiddenFieldsContainer)) {
	      element = this.hiddenFieldsContainer;
	    }

	    return element;
	  }

	  setHiddenFieldsContainer(container) {
	    if (main_core.Type.isStringFilled(container) || main_core.Type.isElementNode(container) || main_core.Type.isNull(container)) {
	      this.hiddenFieldsContainer = container;
	    }
	  }

	  getHiddenFieldName() {
	    return this.hiddenFieldName;
	  }

	  setHiddenFieldName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      this.hiddenFieldName = name;
	    }
	  }

	  shouldAssignAsFile() {
	    return this.assignAsFile;
	  }

	  setAssignAsFile(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      this.assignAsFile = flag;
	    }
	  }

	  getTotalSize() {
	    return this.getFiles().reduce((totalSize, file) => {
	      return totalSize + file.getSize();
	    }, 0);
	  }

	  shouldAutoUpload() {
	    return this.autoUpload;
	  }

	  setAutoUpload(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      this.autoUpload = flag;
	    }
	  }

	  getMaxParallelUploads() {
	    return this.maxParallelUploads;
	  }

	  setMaxParallelUploads(number) {
	    if (main_core.Type.isNumber(number) && number > 0) {
	      this.maxParallelUploads = number;
	    }
	  }

	  getMaxParallelLoads() {
	    return this.maxParallelLoads;
	  }

	  setMaxParallelLoads(number) {
	    if (main_core.Type.isNumber(number) && number > 0) {
	      this.maxParallelLoads = number;
	    }
	  }

	  getUploadingFileCount() {
	    return this.getFiles().filter(file => file.isUploading()).length;
	  }

	  getPendingFileCount() {
	    return this.getFiles().filter(file => file.isReadyToUpload()).length;
	  }

	  shouldAcceptOnlyImages() {
	    return this.acceptOnlyImages;
	  }

	  getAcceptedFileTypes() {
	    return this.acceptedFileTypes;
	  }

	  setAcceptedFileTypes(fileTypes) {
	    if (main_core.Type.isString(fileTypes)) {
	      fileTypes = fileTypes.split(',');
	    }

	    if (main_core.Type.isArray(fileTypes)) {
	      this.acceptedFileTypes = [];
	      fileTypes.forEach(type => {
	        if (main_core.Type.isStringFilled(type)) {
	          this.acceptedFileTypes.push(type);
	        }
	      });
	    }
	  }

	  getIgnoredFileNames() {
	    return this.ignoredFileNames;
	  }

	  setIgnoredFileNames(fileNames) {
	    if (main_core.Type.isArray(fileNames)) {
	      this.ignoredFileNames = [];
	      fileNames.forEach(fileName => {
	        if (main_core.Type.isStringFilled(fileName)) {
	          this.ignoredFileNames.push(fileName.toLowerCase());
	        }
	      });
	    }
	  }

	  setMaxFileCount(maxFileCount) {
	    if (main_core.Type.isNumber(maxFileCount) && maxFileCount > 0 || maxFileCount === null) {
	      this.maxFileCount = maxFileCount;
	    }
	  }

	  getMaxFileCount() {
	    return this.maxFileCount;
	  }

	  setAllowReplaceSingle(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      this.allowReplaceSingle = flag;
	    }
	  }

	  shouldReplaceSingle() {
	    return this.allowReplaceSingle;
	  }

	  handleBeforeUpload(event) {
	    if (this.getStatus() === UploaderStatus.STOPPED) {
	      event.preventDefault();
	      this.start();
	    } else {
	      if (this.getUploadingFileCount() >= this.getMaxParallelUploads()) {
	        event.preventDefault();
	      }
	    }
	  }

	  handlePrepareFileAsync(event) {
	    return new Promise((resolve, reject) => {
	      const {
	        file
	      } = event.getData();

	      babelHelpers.classPrivateFieldLooseBase(this, _applyFilters)[_applyFilters](FilterType.PREPARATION, file).then(transformedFile => {
	        if (main_core.Type.isFile(transformedFile)) {
	          resolve(transformedFile);
	        } else {
	          resolve(file);
	        }
	      }).catch(error => reject(error));
	    });
	  }

	  handleUploadStart(event) {
	    const file = event.getTarget();
	    this.emit('File:onUploadStart', {
	      file
	    });
	  }

	  handleFileCancel(event) {
	    const file = event.getTarget();
	    this.emit('File:onCancel', {
	      file
	    });
	    this.removeFile(file);
	  }

	  handleFileStatusChange(event) {
	    const file = event.getTarget();
	    this.emit('File:onStatusChange', {
	      file
	    });
	  }

	  handleFileStateChange(event) {
	    const file = event.getTarget();
	    this.emit('File:onStateChange', {
	      file
	    });
	  }

	}

	function _setLoadController2(file) {
	  const loadController = file.getOrigin() === FileOrigin.SERVER ? this.getServer().createLoadController() : this.getServer().createClientLoadController();
	  loadController.subscribeFromOptions({
	    'onError': event => {
	      file.setStatus(FileStatus.LOAD_FAILED);
	      this.emit('File:onError', {
	        file,
	        error: event.getData().error
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	    },
	    'onAbort': event => {
	      if (file.getOrigin() === FileOrigin.SERVER) {
	        file.setStatus(FileStatus.ABORTED);
	      } else {
	        file.setStatus(FileStatus.LOAD_FAILED);
	      }

	      this.emit('File:onAbort', {
	        file
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	    },
	    'onProgress': event => {
	      this.emit('File:onLoadProgress', {
	        file,
	        progress: event.getData().progress
	      });
	    },
	    'onLoad': event => {
	      if (file.getOrigin() === FileOrigin.SERVER) {
	        file.setFile(event.getData().fileInfo);
	        file.setStatus(FileStatus.COMPLETE);
	        this.emit('File:onAdd', {
	          file
	        });
	        this.emit('File:onLoadComplete', {
	          file
	        });
	        this.emit('File:onComplete', {
	          file
	        });

	        babelHelpers.classPrivateFieldLooseBase(this, _setHiddenField)[_setHiddenField](file);

	        return;
	      } // Validation


	      babelHelpers.classPrivateFieldLooseBase(this, _applyFilters)[_applyFilters](FilterType.VALIDATION, file).then(() => {
	        if (file.isUploadable()) {
	          file.setStatus(FileStatus.PENDING);
	          this.emit('File:onAdd', {
	            file
	          });
	          this.emit('File:onLoadComplete', {
	            file
	          });

	          if (this.shouldAutoUpload()) {
	            file.upload();
	          }
	        } else {
	          file.setStatus(FileStatus.COMPLETE);
	          this.emit('File:onAdd', {
	            file
	          });
	          this.emit('File:onLoadComplete', {
	            file
	          });
	          this.emit('File:onComplete', {
	            file
	          });
	        }

	        babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	      }).catch(error => {
	        file.setStatus(FileStatus.LOAD_FAILED);
	        this.emit('File:onError', {
	          file,
	          error
	        });
	        this.emit('File:onAdd', {
	          file,
	          error
	        });

	        babelHelpers.classPrivateFieldLooseBase(this, _loadNext)[_loadNext]();
	      });
	    }
	  });
	  file.setLoadController(loadController);
	}

	function _setUploadController2(file) {
	  const uploadController = this.getServer().createUploadController();

	  if (!uploadController) {
	    return;
	  }

	  uploadController.subscribeFromOptions({
	    'onError': event => {
	      file.setStatus(FileStatus.UPLOAD_FAILED);
	      this.emit('File:onError', {
	        file,
	        error: event.getData().error
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadNext)[_uploadNext]();
	    },
	    'onAbort': event => {
	      file.setStatus(FileStatus.ABORTED);
	      this.emit('File:onAbort', {
	        file
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadNext)[_uploadNext]();
	    },
	    'onProgress': event => {
	      this.emit('File:onUploadProgress', {
	        file,
	        progress: event.getData().progress
	      });
	    },
	    'onUpload': event => {
	      file.setStatus(FileStatus.COMPLETE);
	      file.setFile(event.getData().fileInfo);
	      this.emit('File:onUploadComplete', {
	        file
	      });
	      this.emit('File:onComplete', {
	        file
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _setHiddenField)[_setHiddenField](file);

	      babelHelpers.classPrivateFieldLooseBase(this, _uploadNext)[_uploadNext]();
	    }
	  });
	  file.setUploadController(uploadController);
	}

	function _exceedsMaxFileCount2(fileList) {
	  const totalNewFiles = fileList.length;
	  const totalFiles = this.getFiles().length;

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
	    this.emit('onMaxFileCountExceeded', {
	      error
	    });
	    this.emit('onError', {
	      error
	    });
	    return true;
	  }

	  return false;
	}

	function _applyFilters2(type, ...args) {
	  return new Promise((resolve, reject) => {
	    const filters = [...(this.filters.get(type) || [])];

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

	function _uploadNext2() {
	  if (this.getStatus() !== UploaderStatus.STARTED) {
	    return;
	  }

	  const maxParallelUploads = this.getMaxParallelUploads();
	  const currentUploads = this.getUploadingFileCount();
	  const pendingFiles = this.getFiles().filter(file => file.isReadyToUpload());
	  const pendingUploads = pendingFiles.length;

	  if (currentUploads < maxParallelUploads) {
	    const limit = Math.min(maxParallelUploads - currentUploads, pendingFiles.length);

	    for (let i = 0; i < limit; i++) {
	      const pendingFile = pendingFiles[i];
	      pendingFile.upload();
	    }
	  } // All files are COMPLETE or FAILED


	  if (currentUploads === 0 && pendingUploads === 0) {
	    this.status = UploaderStatus.STOPPED;
	    this.emit('onUploadComplete');
	  }
	}

	function _loadNext2() {
	  const maxParallelLoads = this.getMaxParallelLoads();
	  const currentLoads = this.getFiles().filter(file => file.isLoading()).length;
	  const pendingFiles = this.getFiles().filter(file => {
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

	  if (!container || this.hiddenFields.has(file.getId())) {
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
	    assignFileToInput(input, file.getFile());
	  } else if (file.getServerId() !== null) {
	    input.value = file.getServerId();
	  }

	  container.appendChild(input);
	  this.hiddenFields.set(file.getId(), input);

	  babelHelpers.classPrivateFieldLooseBase(this, _syncInputPositions)[_syncInputPositions]();
	}

	function _resetHiddenField2(file) {
	  const input = this.hiddenFields.get(file.getId());

	  if (input) {
	    main_core.Dom.remove(input);
	    this.hiddenFields.delete(file.getId());
	  }
	}

	function _syncInputPositions2() {
	  const container = this.getHiddenFieldsContainer();

	  if (!container) {
	    return;
	  }

	  this.getFiles().forEach(file => {
	    const input = this.hiddenFields.get(file.getId());

	    if (input) {
	      container.appendChild(input);
	    }
	  });
	}

	/**
	 * @memberof BX.UI.Uploader
	 */

	var _uploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploader");

	var _vueApp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("vueApp");

	class VueUploader {
	  constructor(uploaderOptions, vueOptions = {}) {
	    Object.defineProperty(this, _uploader, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _vueApp, {
	      writable: true,
	      value: null
	    });
	    const context = this;
	    babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp] = ui_vue.BitrixVue.createApp({
	      data() {
	        return {
	          items: [],
	          rootComponentId: null,
	          multiple: true,
	          acceptOnlyImages: false
	        };
	      },

	      mixins: [vueOptions],
	      provide: {
	        getUploader() {
	          return babelHelpers.classPrivateFieldLooseBase(context, _uploader)[_uploader];
	        },

	        getWidget() {
	          return context;
	        }

	      },
	      methods: {
	        getUploader() {
	          return babelHelpers.classPrivateFieldLooseBase(context, _uploader)[_uploader];
	        },

	        getWidget() {
	          return context;
	        }

	      },
	      // language=Vue
	      template: `
				<component
					:is="rootComponentId"
					:items="items"
				/>
			`
	    });
	    const options = main_core.Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
	    const userEvents = options.events;
	    options.events = {
	      'File:onAddStart': this.handleFileAdd.bind(this),
	      'File:onRemove': this.handleFileRemove.bind(this),
	      'File:onUploadProgress': this.handleFileUploadProgress.bind(this),
	      'File:onStateChange': this.handleFileStateChange.bind(this),
	      'File:onError': this.handleFileError.bind(this),
	      'onError': this.handleError.bind(this),
	      'onUploadStart': this.handleUploadStart.bind(this),
	      'onUploadComplete': this.handleUploadComplete.bind(this)
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = new ui_uploader_core.Uploader(options);

	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribeFromOptions(userEvents);

	    babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp].multiple = babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].isMultiple();
	    babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp].acceptOnlyImages = babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].shouldAcceptOnlyImages();
	    babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp].rootComponentId = this.getRootComponentId();
	  }

	  getVueOptions() {
	    return {};
	  }

	  getRootComponentId() {
	    return null;
	  }

	  getUploader() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader];
	  }

	  getVueApp() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp];
	  }

	  renderTo(node) {
	    if (main_core.Type.isDomNode(node)) {
	      const container = main_core.Dom.create('div');
	      node.appendChild(container);

	      if (!this.getUploader().getHiddenFieldsContainer()) {
	        this.getUploader().setHiddenFieldsContainer(node);
	      }

	      this.getVueApp().mount(container);
	    }
	  }

	  remove(id) {
	    this.getUploader().removeFile(id);
	  }

	  getItems() {
	    return this.getVueApp().items;
	  }

	  getItem(id) {
	    return this.getItems().find(item => item.id === id);
	  }

	  createItemFromFile(file) {
	    const item = file.getState();
	    item.progress = 0;
	    return item;
	  }

	  handleFileAdd(event) {
	    const {
	      file,
	      error
	    } = event.getData();
	    const item = this.createItemFromFile(file);
	    this.getItems().push(item);
	    this.getVueApp().$Bitrix.eventEmitter.emit('Item:onAdd', {
	      item
	    });
	  }

	  handleFileRemove(event) {
	    const {
	      file
	    } = event.getData();
	    const position = this.getItems().findIndex(fileInfo => fileInfo.id === file.getId());

	    if (position >= 0) {
	      const result = this.getItems().splice(position, 1);
	      this.getVueApp().$Bitrix.eventEmitter.emit('Item:onRemove', {
	        item: result[0]
	      });
	    }
	  }

	  handleFileError(event) {
	    const {
	      file,
	      error
	    } = event.getData();
	    const item = this.getItem(file.getId());
	    item.error = error;
	  }

	  handleFileUploadProgress(event) {
	    const {
	      file,
	      progress
	    } = event.getData();
	    const item = this.getItem(file.getId());

	    if (item) {
	      item.progress = progress;
	    }
	  }

	  handleFileStateChange(event) {
	    const {
	      file
	    } = event.getData();
	    const item = this.getItem(file.getId());

	    if (item) {
	      Object.assign(item, file.getState());
	    }
	  }

	  handleError(event) {
	    this.getVueApp().$Bitrix.eventEmitter.emit('Uploader:onError', event);
	  }

	  handleUploadStart(event) {
	    this.getVueApp().$Bitrix.eventEmitter.emit('Uploader:onUploadStart', event);
	  }

	  handleUploadComplete(event) {
	    this.getVueApp().$Bitrix.eventEmitter.emit('Uploader:onUploadComplete', event);
	  }

	}

	const isImage = file => {
	  return /^image\/[a-z0-9.-]+$/i.test(file.type);
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
	exports.VueUploader = VueUploader;

}((this.BX.UI.Uploader = this.BX.UI.Uploader || {}),BX,BX.Event,BX.UI.Uploader,BX));
//# sourceMappingURL=ui.uploader.bundle.js.map
