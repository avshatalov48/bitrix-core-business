/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_rest,im_v2_lib_logger,im_v2_lib_notifier,im_v2_provider_service_sending,main_core_events,im_v2_application_core,im_v2_const,im_v2_lib_desktopApi,ui_uploader_core,im_v2_lib_utils,main_core) {
	'use strict';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	class UploaderVideoCompressionFilter extends ui_uploader_core.Filter {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  async apply(file) {
	    if (file.isVideo() && !file.shouldTreatImageAsFile() && im_v2_lib_desktopApi.DesktopApi.isMediaCompressorAvailable()) {
	      await this.setModelFileStatus(file.getId(), im_v2_const.FileStatus.preparing);
	      const compressor = im_v2_lib_desktopApi.DesktopApi.createMediaCompressor();
	      const cancelPromise = new Promise(resolve => {
	        file.subscribeOnce(ui_uploader_core.FileEvent.REMOVE_COMPLETE, () => {
	          compressor.cancel();
	          compressor.removeCompressedFile();
	          file.remove();
	          resolve(null);
	        });
	      });
	      const compressedFile = await Promise.race([compressor.compress(file.getBinary()), cancelPromise]);
	      if (compressedFile) {
	        file.setFile(compressedFile);
	        file.setName(compressedFile.name);
	        file.subscribeOnce(ui_uploader_core.FileEvent.UPLOAD_COMPLETE, () => {
	          compressor.removeCompressedFile();
	        });
	      }
	    }
	  }
	  setModelFileStatus(id, status) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/update', {
	      id,
	      fields: {
	        status
	      }
	    });
	  }
	}

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _uploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploader");
	var _customData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("customData");
	class UploaderWrapper extends main_core_events.EventEmitter {
	  constructor({
	    uploaderId,
	    uploaderOptions,
	    events,
	    customData
	  }) {
	    super();
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _uploader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _customData, {
	      writable: true,
	      value: {}
	    });
	    this.setEventNamespace(UploaderWrapper.EVENT_NAMESPACE);
	    this.subscribeFromOptions(events);
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = uploaderId;
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = new ui_uploader_core.Uploader({
	      controller: UploaderWrapper.CONTROLLER,
	      multiple: true,
	      imageResizeWidth: 1280,
	      imageResizeHeight: 1280,
	      imageResizeMode: 'contain',
	      imageResizeMimeType: 'image/jpeg',
	      imageResizeMimeTypeMode: 'force',
	      imagePreviewHeight: 720,
	      imagePreviewWidth: 720,
	      treatOversizeImageAsFile: true,
	      ignoreUnknownImageTypes: true,
	      maxFileSize: null,
	      imageResizeFilter: file => {
	        return !file.getCustomData('sendAsFile') && !file.isAnimated();
	      },
	      ...uploaderOptions,
	      events: {
	        [ui_uploader_core.UploaderEvent.FILE_ADD_START]: event => {
	          this.emit(UploaderWrapper.Event.onFileAddStart, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_ADD]: event => {
	          this.emit(UploaderWrapper.Event.onFileAdd, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_LOAD_COMPLETE]: event => {
	          this.emit(UploaderWrapper.Event.onFileLoadComplete, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_STATE_CHANGE]: event => {
	          this.emit(UploaderWrapper.Event.onFileStateChange, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_STATUS_CHANGE]: event => {
	          this.emit(UploaderWrapper.Event.onFileStatusChange, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_UPLOAD_START]: event => {
	          this.emit(UploaderWrapper.Event.onFileUploadStart, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_UPLOAD_PROGRESS]: event => {
	          this.emit(UploaderWrapper.Event.onFileUploadProgress, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_UPLOAD_COMPLETE]: event => {
	          this.emit(UploaderWrapper.Event.onFileUploadComplete, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.ERROR]: event => {
	          this.emit(UploaderWrapper.Event.onFileUploadError, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_ERROR]: event => {
	          this.emit(UploaderWrapper.Event.onFileUploadError, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.MAX_FILE_COUNT_EXCEEDED]: event => {
	          this.emit(UploaderWrapper.Event.onMaxFileCountExceeded, {
	            ...event.getData(),
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.UPLOAD_COMPLETE]: event => {
	          this.emit(UploaderWrapper.Event.onUploadComplete, {
	            ...event.getData(),
	            uploaderId
	          });
	        }
	      },
	      filters: [{
	        type: ui_uploader_core.FilterType.PREPARATION,
	        filter: UploaderVideoCompressionFilter
	      }]
	    });
	    if (main_core.Type.isPlainObject(customData)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _customData)[_customData] = customData;
	    }
	  }
	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }
	  setCustomData(key, value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _customData)[_customData][key] = value;
	  }
	  getCustomData(key) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _customData)[_customData][key];
	  }
	  addFiles(filesOptions) {
	    const filesEntries = filesOptions.map(fileOption => {
	      return Object.values(fileOption);
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].addFiles(filesEntries);
	  }
	  removeFile(fileId) {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].getFile(fileId)) == null ? void 0 : _babelHelpers$classPr.remove == null ? void 0 : _babelHelpers$classPr.remove();
	  }
	  getFiles() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].getFiles();
	  }
	  getFilesIds() {
	    return this.getFiles().map(file => {
	      return file.getId();
	    });
	  }
	  isAllPending() {
	    return this.getFiles().every(currentFile => {
	      return currentFile.getStatus() === ui_uploader_core.FileStatus.PENDING;
	    });
	  }
	  isAllCompleted() {
	    return this.getFiles().every(currentFile => {
	      return currentFile.getStatus() === ui_uploader_core.FileStatus.COMPLETE;
	    });
	  }
	  getServerFilesIds() {
	    return this.getFiles().map(file => {
	      return file.getServerFileId().toString().slice(1);
	    });
	  }
	  getBinaryFiles() {
	    return this.getFiles().map(file => {
	      return file.getBinary();
	    });
	  }
	  start() {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].setAutoUpload(true);
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].start();
	  }
	  stop() {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].stop();
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].destroy({
	      removeFilesFromServer: false
	    });
	  }
	}
	UploaderWrapper.EVENT_NAMESPACE = 'BX.Messenger.v2.Service.Uploading.UploaderWrapper';
	UploaderWrapper.CONTROLLER = 'disk.uf.integration.diskUploaderController';
	UploaderWrapper.Event = {
	  onFileAddStart: 'onFileAddStart',
	  onFileAdd: 'onFileAdd',
	  onFileLoadComplete: 'onFileLoadComplete',
	  onFileStateChange: 'onFileStateChange',
	  onFileStatusChange: 'onFileStatusChange',
	  onFileUploadStart: 'onFileUploadStart',
	  onFileUploadProgress: 'onFileUploadProgress',
	  onFileUploadComplete: 'onFileUploadComplete',
	  onFileUploadError: 'onFileUploadError',
	  onFileUploadCancel: 'onFileUploadCancel',
	  onMaxFileCountExceeded: 'onMaxFileCountExceeded',
	  onUploadComplete: 'onUploadComplete'
	};

	function createDeferredPromise() {
	  let resolve;
	  let reject;
	  const promise = new Promise((resolveRef, rejectRef) => {
	    resolve = resolveRef;
	    reject = rejectRef;
	  });
	  return {
	    promise,
	    resolve,
	    reject
	  };
	}

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Service.UploadingService';
	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _isRequestingDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRequestingDiskFolderId");
	var _diskFolderIdRequestPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("diskFolderIdRequestPromise");
	var _uploaderWrappers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderWrappers");
	var _sendingService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendingService");
	var _queue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("queue");
	var _isUploading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUploading");
	var _createUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createUploader");
	var _addToQueue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addToQueue");
	var _processQueue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processQueue");
	var _addFileFromDiskToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileFromDiskToModel");
	var _isMediaFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMediaFile");
	var _setFileMapping = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setFileMapping");
	var _requestDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestDiskFolderId");
	var _uploadPreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadPreview");
	var _updateFileProgress = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFileProgress");
	var _cancelUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cancelUpload");
	var _getFileType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFileType");
	var _addFileToStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileToStore");
	var _updateFilePreviewInStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFilePreviewInStore");
	var _updateFileTypeInStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFileTypeInStore");
	var _updateFileSizeInStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFileSizeInStore");
	var _getDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDiskFolderId");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _getCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentUser");
	var _getChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatId");
	var _setMessagesText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessagesText");
	var _createMessageFromFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMessageFromFiles");
	var _tryToSendMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tryToSendMessage");
	var _prepareFileFromDisk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFileFromDisk");
	var _isMaxFileSizeExceeded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMaxFileSizeExceeded");
	var _setMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessageError");
	var _destroyUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("destroyUploader");
	class UploadingService extends main_core_events.EventEmitter {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    super();
	    Object.defineProperty(this, _destroyUploader, {
	      value: _destroyUploader2
	    });
	    Object.defineProperty(this, _setMessageError, {
	      value: _setMessageError2
	    });
	    Object.defineProperty(this, _isMaxFileSizeExceeded, {
	      value: _isMaxFileSizeExceeded2
	    });
	    Object.defineProperty(this, _prepareFileFromDisk, {
	      value: _prepareFileFromDisk2
	    });
	    Object.defineProperty(this, _tryToSendMessage, {
	      value: _tryToSendMessage2
	    });
	    Object.defineProperty(this, _createMessageFromFiles, {
	      value: _createMessageFromFiles2
	    });
	    Object.defineProperty(this, _setMessagesText, {
	      value: _setMessagesText2
	    });
	    Object.defineProperty(this, _getChatId, {
	      value: _getChatId2
	    });
	    Object.defineProperty(this, _getCurrentUser, {
	      value: _getCurrentUser2
	    });
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
	    });
	    Object.defineProperty(this, _getDiskFolderId, {
	      value: _getDiskFolderId2
	    });
	    Object.defineProperty(this, _updateFileSizeInStore, {
	      value: _updateFileSizeInStore2
	    });
	    Object.defineProperty(this, _updateFileTypeInStore, {
	      value: _updateFileTypeInStore2
	    });
	    Object.defineProperty(this, _updateFilePreviewInStore, {
	      value: _updateFilePreviewInStore2
	    });
	    Object.defineProperty(this, _addFileToStore, {
	      value: _addFileToStore2
	    });
	    Object.defineProperty(this, _getFileType, {
	      value: _getFileType2
	    });
	    Object.defineProperty(this, _cancelUpload, {
	      value: _cancelUpload2
	    });
	    Object.defineProperty(this, _updateFileProgress, {
	      value: _updateFileProgress2
	    });
	    Object.defineProperty(this, _uploadPreview, {
	      value: _uploadPreview2
	    });
	    Object.defineProperty(this, _requestDiskFolderId, {
	      value: _requestDiskFolderId2
	    });
	    Object.defineProperty(this, _setFileMapping, {
	      value: _setFileMapping2
	    });
	    Object.defineProperty(this, _isMediaFile, {
	      value: _isMediaFile2
	    });
	    Object.defineProperty(this, _addFileFromDiskToModel, {
	      value: _addFileFromDiskToModel2
	    });
	    Object.defineProperty(this, _processQueue, {
	      value: _processQueue2
	    });
	    Object.defineProperty(this, _addToQueue, {
	      value: _addToQueue2
	    });
	    Object.defineProperty(this, _createUploader, {
	      value: _createUploader2
	    });
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isRequestingDiskFolderId, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _diskFolderIdRequestPromise, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _uploaderWrappers, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _sendingService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _queue, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _isUploading, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace(EVENT_NAMESPACE);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService] = im_v2_provider_service_sending.SendingService.getInstance();
	  }

	  // eslint-disable-next-line max-lines-per-function

	  prepareFilesOptions(files, options, sendAsFile) {
	    return files.map(file => {
	      const id = im_v2_lib_utils.Utils.text.getUuidV4();
	      if (main_core.Type.isFunction(file.getBinary)) {
	        return {
	          file: file.getBinary(),
	          options: {
	            ...file.toJSON(),
	            clientPreview: file.getClientPreview(),
	            id,
	            customData: {
	              ...options
	            },
	            treatImageAsFile: sendAsFile
	          }
	        };
	      }
	      return {
	        file,
	        options: {
	          id,
	          customData: {
	            ...options
	          },
	          downloadUrl: URL.createObjectURL(file)
	        }
	      };
	    });
	  }
	  async addFiles(params) {
	    const {
	      files,
	      dialogId,
	      autoUpload,
	      sendAsFile = false,
	      maxParallelUploads,
	      maxParallelLoads
	    } = params;
	    const {
	      uploaderWrapper,
	      loadAllComplete,
	      uploadAllComplete
	    } = await babelHelpers.classPrivateFieldLooseBase(this, _createUploader)[_createUploader]({
	      dialogId,
	      autoUpload,
	      sendAsFile,
	      maxParallelUploads,
	      maxParallelLoads
	    });
	    const uploaderId = uploaderWrapper.getId();
	    const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].set(uploaderId, uploaderWrapper);
	    const filesOptions = this.prepareFilesOptions(files, {
	      chatId,
	      dialogId
	    }, sendAsFile);
	    const uploaderFiles = uploaderWrapper.addFiles(filesOptions);
	    return {
	      uploaderFiles,
	      uploaderId,
	      loadAllComplete,
	      uploadAllComplete
	    };
	  }
	  getFiles(uploaderId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).getFiles();
	  }
	  start(uploaderId) {
	    this.getFiles(uploaderId).forEach(file => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](file.getId(), 0, im_v2_const.FileStatus.progress);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addToQueue)[_addToQueue](uploaderId);
	  }
	  stop(uploaderId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).stop();
	  }
	  uploadFileFromDisk(files, dialogId) {
	    Object.values(files).forEach(file => {
	      const messageWithFile = babelHelpers.classPrivateFieldLooseBase(this, _prepareFileFromDisk)[_prepareFileFromDisk](file, dialogId);
	      babelHelpers.classPrivateFieldLooseBase(this, _addFileFromDiskToModel)[_addFileFromDiskToModel](messageWithFile).then(() => {
	        const message = {
	          tempMessageId: messageWithFile.tempMessageId,
	          fileIds: [messageWithFile.tempFileId],
	          dialogId: messageWithFile.dialogId
	        };
	        return babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessageWithFiles(message);
	      }).then(() => {
	        this.commitFile({
	          chatId: messageWithFile.chatId,
	          temporaryFileId: messageWithFile.tempFileId,
	          tempMessageId: messageWithFile.tempMessageId,
	          realFileId: messageWithFile.file.id.slice(1),
	          fromDisk: true
	        });
	      }).catch(error => {
	        console.error('SendingService: sendFilesFromDisk error:', error);
	      });
	    });
	  }
	  checkDiskFolderId(dialogId) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderId)[_getDiskFolderId](dialogId) > 0) {
	      return Promise.resolve(babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderId)[_getDiskFolderId](dialogId));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _diskFolderIdRequestPromise)[_diskFolderIdRequestPromise][dialogId];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _diskFolderIdRequestPromise)[_diskFolderIdRequestPromise][dialogId] = babelHelpers.classPrivateFieldLooseBase(this, _requestDiskFolderId)[_requestDiskFolderId](dialogId);
	    return babelHelpers.classPrivateFieldLooseBase(this, _diskFolderIdRequestPromise)[_diskFolderIdRequestPromise][dialogId];
	  }
	  commitFile(params) {
	    const {
	      temporaryFileId,
	      tempMessageId,
	      chatId,
	      realFileId,
	      fromDisk,
	      messageText = '',
	      sendAsFile = false
	    } = params;
	    const fileIdParams = {};
	    if (fromDisk) {
	      fileIdParams.disk_id = realFileId;
	    } else {
	      fileIdParams.upload_id = realFileId.toString().slice(1);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imDiskFileCommit, {
	      chat_id: chatId,
	      message: messageText,
	      template_id: tempMessageId,
	      file_template_id: temporaryFileId,
	      as_file: sendAsFile ? 'Y' : 'N',
	      ...fileIdParams
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _setMessageError)[_setMessageError](tempMessageId);
	      babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](temporaryFileId, 0, im_v2_const.FileStatus.error);
	      console.error('commitFile error', error);
	    });
	  }
	  commitMessage(uploaderId) {
	    const uploader = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId);
	    const chatId = uploader.getCustomData('chatId');
	    const sendAsFile = uploader.getCustomData('sendAsFile');
	    const text = uploader.getCustomData('text');
	    const tempMessageId = uploader.getCustomData('tempMessageId');
	    const fileIds = uploader.getServerFilesIds();
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imDiskFileCommit, {
	      chat_id: chatId,
	      message: text,
	      template_id: tempMessageId,
	      as_file: sendAsFile ? 'Y' : 'N',
	      upload_id: fileIds
	    });
	  }
	  sendMessageWithFiles(params) {
	    const {
	      uploaderId,
	      text
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _setMessagesText)[_setMessagesText](uploaderId, text);
	    babelHelpers.classPrivateFieldLooseBase(this, _tryToSendMessage)[_tryToSendMessage](uploaderId);
	  }
	  getUploaderIdByFileId(fileId) {
	    const uploaderIds = [...babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].keys()];
	    return uploaderIds.find(uploaderId => {
	      return this.getFiles(uploaderId).some(file => {
	        return file.getId() === fileId;
	      });
	    });
	  }
	  removeFileFromUploader(options) {
	    const {
	      uploaderId,
	      filesIds,
	      restartUploading = false
	    } = options;
	    const files = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).getFiles().filter(file => {
	      return filesIds.includes(file.getId());
	    });
	    files.forEach(file => {
	      file.remove();
	      file.abort();
	    });
	    if (restartUploading) {
	      const [firstFile] = this.getFiles(uploaderId);
	      if (firstFile) {
	        firstFile.upload();
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _isUploading)[_isUploading] = false;
	        babelHelpers.classPrivateFieldLooseBase(this, _processQueue)[_processQueue]();
	      }
	    }
	  }
	  async retry(uploaderId) {
	    const uploaderWrapper = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId);
	    const dialogId = uploaderWrapper.getCustomData('dialogId');
	    const text = uploaderWrapper.getCustomData('text');
	    const tempMessageId = uploaderWrapper.getCustomData('tempMessageId');
	    const binaryFiles = uploaderWrapper.getBinaryFiles();
	    const {
	      uploaderId: newUploaderId,
	      loadAllComplete
	    } = await this.addFiles({
	      dialogId,
	      files: binaryFiles
	    });
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/deleteLoadingMessageByMessageId', {
	      messageId: tempMessageId
	    });
	    await loadAllComplete;
	    this.sendMessageWithFiles({
	      uploaderId: newUploaderId,
	      text
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _destroyUploader)[_destroyUploader](uploaderId);
	  }
	}
	async function _createUploader2(params) {
	  const {
	    dialogId,
	    autoUpload = false,
	    sendAsFile = false,
	    maxParallelUploads,
	    maxParallelLoads
	  } = params;
	  const uploaderId = im_v2_lib_utils.Utils.text.getUuidV4();
	  const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	  const folderId = await this.checkDiskFolderId(dialogId);
	  const tempMessageId = im_v2_lib_utils.Utils.text.getUuidV4();
	  const loadAllComplete = createDeferredPromise();
	  const uploadAllComplete = createDeferredPromise();
	  const uploadPreviewPromises = [];
	  const uploaderWrapper = new UploaderWrapper({
	    uploaderId,
	    uploaderOptions: {
	      autoUpload,
	      maxParallelLoads,
	      maxParallelUploads,
	      controllerOptions: {
	        folderId,
	        chat: {
	          chatId,
	          dialogId
	        }
	      }
	    },
	    customData: {
	      chatId,
	      dialogId,
	      tempMessageId,
	      sendAsFile
	    },
	    events: {
	      [UploaderWrapper.Event.onFileAddStart]: event => {
	        const {
	          file
	        } = event.getData();
	        babelHelpers.classPrivateFieldLooseBase(this, _addFileToStore)[_addFileToStore](file, sendAsFile);
	      },
	      [UploaderWrapper.Event.onFileStatusChange]: event => {
	        const {
	          file
	        } = event.getData();
	        if (file.getStatus() === ui_uploader_core.FileStatus.PENDING) {
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isMediaFile)[_isMediaFile](file)) {
	            babelHelpers.classPrivateFieldLooseBase(this, _updateFilePreviewInStore)[_updateFilePreviewInStore](file, sendAsFile);
	          } else {
	            babelHelpers.classPrivateFieldLooseBase(this, _updateFilePreviewInStore)[_updateFilePreviewInStore](file, sendAsFile);
	            babelHelpers.classPrivateFieldLooseBase(this, _updateFileTypeInStore)[_updateFileTypeInStore](file, im_v2_const.FileType.file);
	            file.setTreatImageAsFile(true);
	            file.setCustomData('sendAsFile', true);
	          }
	          const target = event.getTarget();
	          if (target.isAllPending()) {
	            loadAllComplete.resolve();
	          }
	        }
	      },
	      [UploaderWrapper.Event.onFileUploadStart]: event => {
	        const {
	          file
	        } = event.getData();
	        babelHelpers.classPrivateFieldLooseBase(this, _updateFileSizeInStore)[_updateFileSizeInStore](file);
	        this.emit(UploadingService.event.uploadStart);
	      },
	      [UploaderWrapper.Event.onFileUploadProgress]: event => {
	        const {
	          file
	        } = event.getData();
	        babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](file.getId(), file.getProgress(), im_v2_const.FileStatus.upload);
	      },
	      [UploaderWrapper.Event.onFileUploadComplete]: async event => {
	        const {
	          file
	        } = event.getData();
	        const serverFileId = file.getServerFileId().toString().slice(1);
	        const temporaryFileId = file.getId();
	        if (babelHelpers.classPrivateFieldLooseBase(this, _isMediaFile)[_isMediaFile](file)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _setFileMapping)[_setFileMapping]({
	            serverFileId,
	            temporaryFileId
	          });
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](temporaryFileId, file.getProgress(), im_v2_const.FileStatus.wait);
	        uploadPreviewPromises.push(babelHelpers.classPrivateFieldLooseBase(this, _uploadPreview)[_uploadPreview](file));
	        this.emit(UploadingService.event.uploadComplete);
	      },
	      [UploaderWrapper.Event.onFileUploadError]: event => {
	        const {
	          error
	        } = event.getData();
	        const target = event.getTarget();
	        babelHelpers.classPrivateFieldLooseBase(this, _setMessageError)[_setMessageError](target.getCustomData('tempMessageId'));
	        target.getFiles().forEach(uploaderFile => {
	          babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](uploaderFile.getId(), 0, im_v2_const.FileStatus.error);
	        });
	        if (babelHelpers.classPrivateFieldLooseBase(this, _isMaxFileSizeExceeded)[_isMaxFileSizeExceeded](error)) {
	          im_v2_lib_notifier.Notifier.file.handleUploadError(error);
	        }
	        im_v2_lib_logger.Logger.error('UploadingService: upload error', error);
	        this.emit(UploadingService.event.uploadError);
	        babelHelpers.classPrivateFieldLooseBase(this, _isUploading)[_isUploading] = false;
	        babelHelpers.classPrivateFieldLooseBase(this, _processQueue)[_processQueue]();
	        this.stop(uploaderId);
	      },
	      [UploaderWrapper.Event.onFileUploadCancel]: event => {
	        const {
	          tempFileId
	        } = event.getData();
	        babelHelpers.classPrivateFieldLooseBase(this, _cancelUpload)[_cancelUpload](tempMessageId, tempFileId);
	        this.emit(UploadingService.event.uploadCancel);
	      },
	      [UploaderWrapper.Event.onUploadComplete]: async event => {
	        babelHelpers.classPrivateFieldLooseBase(this, _isUploading)[_isUploading] = false;
	        babelHelpers.classPrivateFieldLooseBase(this, _processQueue)[_processQueue]();
	        const target = event.getTarget();
	        if (target.isAllCompleted()) {
	          await Promise.all(uploadPreviewPromises);
	          uploadAllComplete.resolve();
	          await this.commitMessage(uploaderId);
	          babelHelpers.classPrivateFieldLooseBase(this, _destroyUploader)[_destroyUploader](uploaderId);
	        }
	      }
	    }
	  });
	  return {
	    uploaderWrapper,
	    loadAllComplete: loadAllComplete.promise,
	    uploadAllComplete: uploadAllComplete.promise
	  };
	}
	function _addToQueue2(uploaderId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].push(uploaderId);
	  babelHelpers.classPrivateFieldLooseBase(this, _processQueue)[_processQueue]();
	}
	function _processQueue2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isUploading)[_isUploading] || babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].length === 0) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _isUploading)[_isUploading] = true;
	  const uploaderId = babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].shift();
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).start();
	}
	function _addFileFromDiskToModel2(messageWithFile) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/add', {
	    id: messageWithFile.tempFileId,
	    chatId: messageWithFile.chatId,
	    authorId: im_v2_application_core.Core.getUserId(),
	    name: messageWithFile.file.name,
	    type: im_v2_lib_utils.Utils.file.getFileTypeByExtension(messageWithFile.file.ext),
	    extension: messageWithFile.file.ext,
	    size: messageWithFile.file.sizeInt,
	    status: im_v2_const.FileStatus.wait,
	    progress: 0,
	    authorName: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().name
	  });
	}
	function _isMediaFile2(file) {
	  return ui_uploader_core.isResizableImage(file.getBinary()) || file.isVideo() && main_core.Type.isStringFilled(file.getPreviewUrl());
	}
	function _setFileMapping2(options) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/setTemporaryFileMapping', options);
	}
	function _requestDiskFolderId2(dialogId) {
	  return new Promise((resolve, reject) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = true;
	    const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imDiskFolderGet, {
	      chat_id: chatId
	    }).then(response => {
	      const {
	        ID: diskFolderId
	      } = response.data();
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	        dialogId,
	        fields: {
	          diskFolderId
	        }
	      });
	      resolve(diskFolderId);
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      reject(error);
	    });
	  });
	}
	async function _uploadPreview2(file) {
	  const needPreview = babelHelpers.classPrivateFieldLooseBase(this, _getFileType)[_getFileType](file) === im_v2_const.FileType.video || file.isAnimated();
	  if (!needPreview) {
	    return Promise.resolve();
	  }
	  const id = file.getServerFileId().toString().slice(1);
	  const previewFile = file.getClientPreview();
	  if (!previewFile) {
	    file.setCustomData('sendAsFile', true);
	    return Promise.resolve();
	  }
	  const formData = new FormData();
	  formData.append('id', id);
	  formData.append('previewFile', previewFile, `preview_${file.getName()}.jpg`);
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imDiskFilePreviewUpload, {
	    data: formData
	  }).catch(([error]) => {
	    console.error('imDiskFilePreviewUpload request error', error);
	  });
	}
	function _updateFileProgress2(id, progress, status) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/update', {
	    id,
	    fields: {
	      progress: progress === 100 ? 99 : progress,
	      status
	    }
	  });
	}
	function _cancelUpload2(tempMessageId, tempFileId) {
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['messages/getById'](tempMessageId);
	  if (message) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/delete', {
	      id: tempMessageId
	    });
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/delete', {
	      id: tempFileId
	    });
	    const chat = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['chats/getByChatId'](message.chatId);
	    const lastMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['messages/findLastChatMessageId'](message.chatId);
	    if (main_core.Type.isString(lastMessageId) || main_core.Type.isNumber(lastMessageId)) {
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('recent/update', {
	        id: chat.dialogId,
	        fields: {
	          messageId: lastMessageId
	        }
	      });
	    } else {
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('recent/delete', {
	        id: chat.dialogId
	      });
	    }
	  }
	}
	function _getFileType2(file) {
	  if (ui_uploader_core.isResizableImage(file.getBinary())) {
	    return im_v2_const.FileType.image;
	  }
	  if (file.isVideo()) {
	    return im_v2_const.FileType.video;
	  }
	  if (file.getType().startsWith('audio')) {
	    return im_v2_const.FileType.audio;
	  }
	  return im_v2_const.FileType.file;
	}
	function _addFileToStore2(file, sendAsFile) {
	  const fileType = babelHelpers.classPrivateFieldLooseBase(this, _getFileType)[_getFileType](file);
	  const currentUser = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]();
	  const isFile = ![im_v2_const.FileType.image, im_v2_const.FileType.video].includes(fileType);
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/add', {
	    id: file.getId(),
	    name: file.getName(),
	    size: file.getSize(),
	    type: fileType,
	    extension: file.getExtension(),
	    chatId: file.getCustomData('chatId'),
	    authorId: currentUser.id,
	    authorName: currentUser.name,
	    status: file.isFailed() ? im_v2_const.FileStatus.error : im_v2_const.FileStatus.wait,
	    progress: 0,
	    urlDownload: file.getDownloadUrl(),
	    ...(() => {
	      if (isFile || sendAsFile) {
	        return {
	          image: false
	        };
	      }
	      return {};
	    })()
	  });
	}
	function _updateFilePreviewInStore2(file, sendAsFile) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/update', {
	    id: file.getId(),
	    fields: {
	      urlPreview: (() => {
	        if (file.isImage()) {
	          return file.getPreviewUrl() || file.getDownloadUrl();
	        }
	        return file.getPreviewUrl();
	      })(),
	      ...(() => {
	        if (sendAsFile) {
	          return {
	            image: false
	          };
	        }
	        return {
	          image: {
	            width: file.getPreviewWidth(),
	            height: file.getPreviewHeight()
	          }
	        };
	      })()
	    }
	  });
	}
	function _updateFileTypeInStore2(file, type) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/update', {
	    id: file.getId(),
	    fields: {
	      type
	    }
	  });
	}
	function _updateFileSizeInStore2(file) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/update', {
	    id: file.getId(),
	    fields: {
	      size: file.getSize()
	    }
	  });
	}
	function _getDiskFolderId2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId).diskFolderId;
	}
	function _getDialog2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['chats/get'](dialogId);
	}
	function _getCurrentUser2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['users/get'](userId);
	}
	function _getChatId2(dialogId) {
	  var _babelHelpers$classPr;
	  return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId)) == null ? void 0 : _babelHelpers$classPr.chatId;
	}
	function _setMessagesText2(uploaderId, text) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).setCustomData('text', text);
	}
	function _createMessageFromFiles2(uploaderId) {
	  const fileIds = [];
	  const files = this.getFiles(uploaderId);
	  files.forEach(file => {
	    if (!file.getError()) {
	      fileIds.push(file.getId());
	    }
	  });
	  const text = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).getCustomData('text');
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).getCustomData('dialogId');
	  const tempMessageId = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).getCustomData('tempMessageId');
	  return {
	    fileIds,
	    tempMessageId,
	    dialogId,
	    text
	  };
	}
	function _tryToSendMessage2(uploaderId) {
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _createMessageFromFiles)[_createMessageFromFiles](uploaderId);
	  void babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessageWithFiles(message);
	  this.start(uploaderId);
	}
	function _prepareFileFromDisk2(file, dialogId) {
	  const tempMessageId = im_v2_lib_utils.Utils.text.getUuidV4();
	  const realFileId = file.id.slice(1); // 'n123' => '123'
	  const tempFileId = `${tempMessageId}|${realFileId}`;
	  return {
	    tempMessageId,
	    tempFileId,
	    dialogId,
	    file,
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId).chatId
	  };
	}
	function _isMaxFileSizeExceeded2(error) {
	  return error.getCode() === 'MAX_FILE_SIZE_EXCEEDED';
	}
	function _setMessageError2(tempMessageId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/update', {
	    id: tempMessageId,
	    fields: {
	      error: true
	    }
	  });
	}
	function _destroyUploader2(uploaderId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].get(uploaderId).destroy();
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrappers)[_uploaderWrappers].delete(uploaderId);
	}
	UploadingService.event = {
	  uploadStart: 'uploadStart',
	  uploadComplete: 'uploadComplete',
	  uploadError: 'uploadError',
	  uploadCancel: 'uploadCancel'
	};
	UploadingService.instance = null;

	const MAX_FILES_COUNT_IN_ONE_MESSAGE = 10;
	const MAX_FILES_COUNT = 100;
	const MAX_PARALLEL_LOADS = 10;
	const MAX_PARALLEL_UPLOADS = 3;
	var _getUploadingService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUploadingService");
	var _createUploadingId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createUploadingId");
	var _addFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFiles");
	class MultiUploadingService {
	  constructor() {
	    Object.defineProperty(this, _addFiles, {
	      value: _addFiles2
	    });
	    Object.defineProperty(this, _createUploadingId, {
	      value: _createUploadingId2
	    });
	    Object.defineProperty(this, _getUploadingService, {
	      value: _getUploadingService2
	    });
	  }
	  static makeChunks(options) {
	    const {
	      files,
	      chunkSize = MAX_FILES_COUNT_IN_ONE_MESSAGE,
	      maxFilesCount = MAX_FILES_COUNT
	    } = options;
	    const chunks = [];
	    if (main_core.Type.isArray(files)) {
	      const preparedFiles = files.slice(0, maxFilesCount);
	      for (let i = 0; i < preparedFiles.length; i += chunkSize) {
	        const chunk = preparedFiles.slice(i, i + chunkSize);
	        chunks.push(chunk);
	      }
	    }
	    return chunks;
	  }
	  static getMaxParallelLoads(chunks) {
	    return Math.floor(MAX_PARALLEL_LOADS / chunks.length);
	  }
	  async upload({
	    files,
	    dialogId,
	    autoUpload,
	    sendAsFile
	  }) {
	    const chunks = MultiUploadingService.makeChunks({
	      files
	    });
	    const addFilesResults = await Promise.all(chunks.map(chunk => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _addFiles)[_addFiles]({
	        files: chunk,
	        maxParallelLoads: MultiUploadingService.getMaxParallelLoads(chunks),
	        maxParallelUploads: MAX_PARALLEL_UPLOADS,
	        dialogId,
	        autoUpload,
	        sendAsFile
	      });
	    }));
	    const uploadingId = babelHelpers.classPrivateFieldLooseBase(this, _createUploadingId)[_createUploadingId]();
	    const uploaderIds = [];
	    const loadCompletePromises = [];
	    const uploadCompletePromises = [];
	    addFilesResults.forEach(({
	      uploaderId,
	      uploaderFiles,
	      loadAllComplete,
	      uploadAllComplete
	    }) => {
	      if (main_core.Type.isArrayFilled(uploaderFiles)) {
	        uploaderIds.push(uploaderId);
	        loadCompletePromises.push(loadAllComplete);
	        uploadCompletePromises.push(uploadAllComplete);
	      }
	    });
	    const loadAllComplete = Promise.all(loadCompletePromises);
	    const uploadAllComplete = Promise.all(uploadCompletePromises);
	    const sourceFilesCount = files.length;
	    return {
	      uploadingId,
	      uploaderIds,
	      sourceFilesCount,
	      loadAllComplete,
	      uploadAllComplete
	    };
	  }
	}
	function _getUploadingService2() {
	  return UploadingService.getInstance();
	}
	function _createUploadingId2() {
	  return im_v2_lib_utils.Utils.text.getUuidV4();
	}
	async function _addFiles2(params) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getUploadingService)[_getUploadingService]().addFiles(params);
	}

	exports.UploadingService = UploadingService;
	exports.MultiUploadingService = MultiUploadingService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.UI.Uploader,BX.Messenger.v2.Lib,BX));
//# sourceMappingURL=uploading.bundle.js.map
