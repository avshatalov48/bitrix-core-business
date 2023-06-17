this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	const SENDER_STATUSES = {
	  PENDING: 0,
	  PROGRESS: 1,
	  DONE: 2,
	  CANCELLED: 3,
	  FAILED: 4
	};
	var _uploaderTask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderTask");
	var _status = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("status");
	var _listener = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("listener");
	var _requestToDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestToDelete");
	var _token = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("token");
	var _nextDataChunkToSend = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("nextDataChunkToSend");
	var _readOffset = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readOffset");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _createFileFromUploadedChunks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createFileFromUploadedChunks");
	var _updateProgress = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateProgress");
	var _readNext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readNext");
	var _isEndOfFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEndOfFile");
	var _getUploadContentEndpoint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUploadContentEndpoint");
	var _getCreateFileEndpoint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCreateFileEndpoint");
	var _getDeleteContentEndpoint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDeleteContentEndpoint");
	var _getBaseEndpoint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBaseEndpoint");
	var _getFileName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFileName");
	var _sendPostRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendPostRequest");
	var _getContentRangeHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContentRangeHeader");
	var _getBitrixSessid = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBitrixSessid");
	class FileSender {
	  constructor(task) {
	    Object.defineProperty(this, _getBitrixSessid, {
	      value: _getBitrixSessid2
	    });
	    Object.defineProperty(this, _getContentRangeHeader, {
	      value: _getContentRangeHeader2
	    });
	    Object.defineProperty(this, _sendPostRequest, {
	      value: _sendPostRequest2
	    });
	    Object.defineProperty(this, _getFileName, {
	      value: _getFileName2
	    });
	    Object.defineProperty(this, _getBaseEndpoint, {
	      value: _getBaseEndpoint2
	    });
	    Object.defineProperty(this, _getDeleteContentEndpoint, {
	      value: _getDeleteContentEndpoint2
	    });
	    Object.defineProperty(this, _getCreateFileEndpoint, {
	      value: _getCreateFileEndpoint2
	    });
	    Object.defineProperty(this, _getUploadContentEndpoint, {
	      value: _getUploadContentEndpoint2
	    });
	    Object.defineProperty(this, _isEndOfFile, {
	      value: _isEndOfFile2
	    });
	    Object.defineProperty(this, _readNext, {
	      value: _readNext2
	    });
	    Object.defineProperty(this, _updateProgress, {
	      value: _updateProgress2
	    });
	    Object.defineProperty(this, _createFileFromUploadedChunks, {
	      value: _createFileFromUploadedChunks2
	    });
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _uploaderTask, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _status, {
	      writable: true,
	      value: SENDER_STATUSES.PENDING
	    });
	    Object.defineProperty(this, _listener, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _requestToDelete, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _token, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _nextDataChunkToSend, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _readOffset, {
	      writable: true,
	      value: 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask] = task;
	    babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener] = task.listener;
	    this.abortController = new AbortController();
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	  }
	  uploadContent() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === SENDER_STATUSES.CANCELLED) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = SENDER_STATUSES.PROGRESS;
	    babelHelpers.classPrivateFieldLooseBase(this, _readNext)[_readNext]();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateProgress)[_updateProgress]();
	    const url = babelHelpers.classPrivateFieldLooseBase(this, _getUploadContentEndpoint)[_getUploadContentEndpoint]();
	    const headers = {
	      'Content-Type': babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.type,
	      'Content-Range': babelHelpers.classPrivateFieldLooseBase(this, _getContentRangeHeader)[_getContentRangeHeader](),
	      'X-Bitrix-Csrf-Token': babelHelpers.classPrivateFieldLooseBase(this, _getBitrixSessid)[_getBitrixSessid]()
	    };
	    const requestParams = {
	      url,
	      headers,
	      body: babelHelpers.classPrivateFieldLooseBase(this, _nextDataChunkToSend)[_nextDataChunkToSend],
	      signal: this.abortController.signal
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _sendPostRequest)[_sendPostRequest](requestParams).then(response => {
	      if (response.errors.length > 0) {
	        babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = SENDER_STATUSES.FAILED;
	        babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener](Uploader.EVENTS.uploadFileError, {
	          task: babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask],
	          result: response
	        });
	        console.error(response.errors[0].message);
	      } else if (response.data.token) {
	        babelHelpers.classPrivateFieldLooseBase(this, _token)[_token] = response.data.token;
	        babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset] += babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].chunkSize;
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _isEndOfFile)[_isEndOfFile]()) {
	          this.uploadContent();
	        } else {
	          babelHelpers.classPrivateFieldLooseBase(this, _createFileFromUploadedChunks)[_createFileFromUploadedChunks]();
	        }
	      }
	    }).catch(error => {
	      console.warn('error', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = SENDER_STATUSES.FAILED;
	      babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener](Uploader.EVENTS.uploadFileError, {
	        task: babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask],
	        result: error
	      });
	    });
	  }
	  deleteContent() {
	    this.abortController.abort();
	    babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = SENDER_STATUSES.CANCELLED;
	    babelHelpers.classPrivateFieldLooseBase(this, _requestToDelete)[_requestToDelete] = true;
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _token)[_token]) {
	      console.error('Empty token.');
	      return;
	    }
	    const url = babelHelpers.classPrivateFieldLooseBase(this, _getDeleteContentEndpoint)[_getDeleteContentEndpoint]();
	    const headers = {
	      'X-Bitrix-Csrf-Token': babelHelpers.classPrivateFieldLooseBase(this, _getBitrixSessid)[_getBitrixSessid]()
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _sendPostRequest)[_sendPostRequest]({
	      url,
	      headers
	    }).catch(error => console.error(error));
	  }
	  isPending() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === SENDER_STATUSES.PENDING;
	  }
	  getTaskId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].taskId;
	  }
	}
	function _init2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].progress = 0;
	  babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener](Uploader.EVENTS.startUpload, {
	    task: babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask]
	  });
	}
	function _createFileFromUploadedChunks2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _token)[_token]) {
	    console.error('Empty token.');
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _requestToDelete)[_requestToDelete]) {
	    return;
	  }
	  const url = babelHelpers.classPrivateFieldLooseBase(this, _getCreateFileEndpoint)[_getCreateFileEndpoint]();
	  const headers = {
	    'X-Upload-Content-Type': babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.type,
	    'X-Bitrix-Csrf-Token': babelHelpers.classPrivateFieldLooseBase(this, _getBitrixSessid)[_getBitrixSessid]()
	  };
	  const body = new FormData();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].previewBlob) {
	    body.append('previewFile', babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].previewBlob, `preview_${babelHelpers.classPrivateFieldLooseBase(this, _getFileName)[_getFileName]()}.jpg`);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _sendPostRequest)[_sendPostRequest]({
	    url,
	    headers,
	    body
	  }).then(response => {
	    if (response.errors.length > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = SENDER_STATUSES.FAILED;
	      babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener](Uploader.EVENTS.createFileError, {
	        task: babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask],
	        result: response
	      });
	      console.error(response.errors[0].message);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateProgress)[_updateProgress]();
	      babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = SENDER_STATUSES.DONE;
	      babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener](Uploader.EVENTS.complete, {
	        task: babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask],
	        result: response
	      });
	    }
	  }).catch(error => {
	    babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = SENDER_STATUSES.FAILED;
	    babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener](Uploader.EVENTS.createFileError, {
	      task: babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask],
	      result: error
	    });
	  });
	}
	function _updateProgress2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].progress = Math.round(babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset] * 100 / babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.size);
	  babelHelpers.classPrivateFieldLooseBase(this, _listener)[_listener](Uploader.EVENTS.progressUpdate, {
	    task: babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask]
	  });
	}
	function _readNext2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset] + babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].chunkSize > babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.size) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].chunkSize = babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.size - babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset];
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _nextDataChunkToSend)[_nextDataChunkToSend] = babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.slice(babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset], babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset] + babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].chunkSize);
	}
	function _isEndOfFile2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset] >= babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.size;
	}
	function _getUploadContentEndpoint2() {
	  const token = babelHelpers.classPrivateFieldLooseBase(this, _token)[_token] ? `&token=${babelHelpers.classPrivateFieldLooseBase(this, _token)[_token]}` : '';
	  return `
			${babelHelpers.classPrivateFieldLooseBase(this, _getBaseEndpoint)[_getBaseEndpoint](FileSender.UPLOAD_CHUNK_DEFAULT_ACTION)}
			&filename=${babelHelpers.classPrivateFieldLooseBase(this, _getFileName)[_getFileName]()}
			${token}
		`;
	}
	function _getCreateFileEndpoint2() {
	  const generateUniqueName = babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].generateUniqueName ? '&generateUniqueName=true' : '';
	  return `
			${babelHelpers.classPrivateFieldLooseBase(this, _getBaseEndpoint)[_getBaseEndpoint](FileSender.COMMIT_FILE_DEFAULT_ACTION)}
			&filename=${babelHelpers.classPrivateFieldLooseBase(this, _getFileName)[_getFileName]()}
			&folderId=${babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].diskFolderId}
			&contentId=${babelHelpers.classPrivateFieldLooseBase(this, _token)[_token]}
			${generateUniqueName}
		`;
	}
	function _getDeleteContentEndpoint2() {
	  return `${babelHelpers.classPrivateFieldLooseBase(this, _getBaseEndpoint)[_getBaseEndpoint](FileSender.ROLLBACK_UPLOAD_DEFAULT_ACTION)}&token=${babelHelpers.classPrivateFieldLooseBase(this, _token)[_token]}`;
	}
	function _getBaseEndpoint2(action) {
	  return `/bitrix/services/main/ajax.php?action=${action}`;
	}
	function _getFileName2() {
	  return encodeURIComponent(babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileName || babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.name);
	}
	function _sendPostRequest2(request) {
	  const {
	    url,
	    headers,
	    body,
	    signal
	  } = request;
	  const requestPrams = {
	    method: 'POST',
	    credentials: 'include',
	    headers: headers
	  };
	  if (signal) {
	    requestPrams.signal = this.abortController.signal;
	  }
	  if (body) {
	    requestPrams.body = body;
	  }
	  return new Promise((resolve, reject) => {
	    fetch(url, requestPrams).then(response => resolve(response.json())).catch(error => reject(error));
	  });
	}
	function _getContentRangeHeader2() {
	  const range = babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset] + babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].chunkSize - 1;
	  return `bytes ${babelHelpers.classPrivateFieldLooseBase(this, _readOffset)[_readOffset]}-${range}/${babelHelpers.classPrivateFieldLooseBase(this, _uploaderTask)[_uploaderTask].fileData.size}`;
	}
	function _getBitrixSessid2() {
	  // eslint-disable-next-line bitrix-rules/no-bx
	  return BX.bitrix_sessid();
	}
	FileSender.UPLOAD_CHUNK_DEFAULT_ACTION = 'disk.api.content.upload';
	FileSender.COMMIT_FILE_DEFAULT_ACTION = 'disk.api.file.createByContent';
	FileSender.ROLLBACK_UPLOAD_DEFAULT_ACTION = 'disk.api.content.rollbackUpload';

	const PreviewManager = {
	  get(file) {
	    return new Promise((resolve, reject) => {
	      if (file instanceof File) {
	        if (file.type.startsWith('video')) {
	          PreviewManager.getVideoPreviewBlob(file).then(blob => PreviewManager.getImageDimensions(blob)).then(result => resolve(result)).catch(error => reject(error));
	        } else if (file.type.startsWith('image')) {
	          const blob = new Blob([file], {
	            type: file.type
	          });
	          PreviewManager.getImageDimensions(blob).then(result => resolve(result)).catch(error => reject(error));
	        } else {
	          resolve({});
	        }
	      } else {
	        reject(new Error('Parameter "file" is not instance of "File"'));
	      }
	    });
	  },
	  getImageDimensions(fileBlob) {
	    return new Promise((resolve, reject) => {
	      if (!fileBlob) {
	        reject(new Error('getImageDimensions: fileBlob can\'t be empty'));
	      }
	      const image = new Image();
	      main_core.Event.bind(image, 'load', () => {
	        resolve({
	          blob: fileBlob,
	          width: image.width,
	          height: image.height
	        });
	      });
	      main_core.Event.bind(image, 'error', () => {
	        reject();
	      });
	      image.src = URL.createObjectURL(fileBlob);
	    });
	  },
	  getVideoPreviewBlob(blob, seekTime = 10) {
	    return new Promise((resolve, reject) => {
	      const video = document.createElement('video');
	      video.setAttribute('src', URL.createObjectURL(blob));
	      video.load();
	      main_core.Event.bind(video, 'error', error => {
	        reject(new Error(`Error while loading video file: ${error}`));
	      });
	      main_core.Event.bind(video, 'loadedmetadata', () => {
	        if (video.duration < seekTime) {
	          seekTime = 0;
	        }
	        video.currentTime = seekTime;
	        main_core.Event.bind(video, 'seeked', () => {
	          const canvas = document.createElement('canvas');
	          canvas.width = video.videoWidth;
	          canvas.height = video.videoHeight;
	          const context = canvas.getContext('2d');
	          context.drawImage(video, 0, 0, canvas.width, canvas.height);
	          context.canvas.toBlob(resultBlob => resolve(resultBlob), 'image/jpeg', 1);
	        });
	      });
	    });
	  }
	};

	var _queue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("queue");
	var _inputNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputNode");
	var _dropNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dropNode");
	var _fileMaxSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fileMaxSize");
	var _isCloud = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCloud");
	var _phpUploadMaxFilesize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("phpUploadMaxFilesize");
	var _phpPostMaxSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("phpPostMaxSize");
	var _calculateChunkSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calculateChunkSize");
	var _handleUploaderOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleUploaderOptions");
	var _initSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initSettings");
	class Uploader extends main_core_events.EventEmitter {
	  //1Mb
	  //5Mb
	  //100Mb

	  constructor(_options = {}) {
	    super();
	    Object.defineProperty(this, _initSettings, {
	      value: _initSettings2
	    });
	    Object.defineProperty(this, _handleUploaderOptions, {
	      value: _handleUploaderOptions2
	    });
	    Object.defineProperty(this, _calculateChunkSize, {
	      value: _calculateChunkSize2
	    });
	    Object.defineProperty(this, _queue, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _inputNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dropNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fileMaxSize, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _isCloud, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _phpUploadMaxFilesize, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _phpPostMaxSize, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Messenger.V2.Lib.Uploader');
	    babelHelpers.classPrivateFieldLooseBase(this, _handleUploaderOptions)[_handleUploaderOptions](_options);
	    babelHelpers.classPrivateFieldLooseBase(this, _initSettings)[_initSettings]();
	  }
	  addTask(task) {
	    if (!this.checkTaskParams(task)) {
	      return;
	    }
	    task.chunkSize = babelHelpers.classPrivateFieldLooseBase(this, _calculateChunkSize)[_calculateChunkSize](task.chunkSize);
	    task.listener = (eventName, data) => this.onUploadEvent(eventName, data);
	    babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].push(new FileSender(task));
	    this.checkUploadQueue();
	  }
	  deleteTask(taskId) {
	    if (!taskId) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue] = babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].filter(queueItem => {
	      if (queueItem.getTaskId() === taskId) {
	        queueItem.deleteContent();
	        return false;
	      }
	      return true;
	    });
	  }
	  getTask(taskId) {
	    const task = this.queue.find(queueItem => queueItem.taskId === taskId);
	    return task || null;
	  }
	  checkUploadQueue() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].length === 0) {
	      return;
	    }
	    const inProgressTasks = babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].filter(queueTask => queueTask.isPending());
	    if (inProgressTasks.length > 0) {
	      inProgressTasks[0].uploadContent();
	    }
	  }
	  onUploadEvent(eventName, event) {
	    this.emit(eventName, event);
	    this.checkUploadQueue();
	  }
	  checkTaskParams(task) {
	    if (!task.taskId) {
	      console.error('Empty TaskId.');
	      return false;
	    }
	    if (!task.fileData) {
	      console.error('Empty file data.');
	      return false;
	    }
	    if (!task.diskFolderId) {
	      console.error('Empty disk folder id.');
	      return false;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _fileMaxSize)[_fileMaxSize] && babelHelpers.classPrivateFieldLooseBase(this, _fileMaxSize)[_fileMaxSize] < task.fileData.size) {
	      this.emit(Uploader.EVENTS.fileMaxSizeExceeded, {
	        maxFileSizeLimit: babelHelpers.classPrivateFieldLooseBase(this, _fileMaxSize)[_fileMaxSize],
	        task: task
	      });
	      return false;
	    }
	    return true;
	  }
	}
	function _calculateChunkSize2(chunk = 0) {
	  const maxAvailableBoxChinkSize = Math.min(babelHelpers.classPrivateFieldLooseBase(this, _phpPostMaxSize)[_phpPostMaxSize], babelHelpers.classPrivateFieldLooseBase(this, _phpUploadMaxFilesize)[_phpUploadMaxFilesize]);
	  const minChunkSize = babelHelpers.classPrivateFieldLooseBase(this, _isCloud)[_isCloud] ? Uploader.CLOUD_MIN_CHUNK_SIZE : Uploader.BOX_MIN_CHUNK_SIZE;
	  const maxChunkSize = babelHelpers.classPrivateFieldLooseBase(this, _isCloud)[_isCloud] ? Uploader.CLOUD_MAX_CHUNK_SIZE : maxAvailableBoxChinkSize;
	  return Math.min(Math.max(chunk, minChunkSize), maxChunkSize);
	}
	function _handleUploaderOptions2(options) {
	  if (options.inputNode instanceof HTMLInputElement || main_core.Type.isArrayFilled(options.inputNode)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode] = options.inputNode;
	  }
	  if (options.dropNode instanceof HTMLElement || main_core.Type.isArrayFilled(options.dropNode)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _dropNode)[_dropNode] = options.dropNode;
	  }
	}
	function _initSettings2() {
	  const settings = main_core.Extension.getSettings('im.v2.lib.uploader');
	  babelHelpers.classPrivateFieldLooseBase(this, _isCloud)[_isCloud] = settings.get('isCloud');
	  babelHelpers.classPrivateFieldLooseBase(this, _phpUploadMaxFilesize)[_phpUploadMaxFilesize] = settings.get('phpUploadMaxFilesize');
	  babelHelpers.classPrivateFieldLooseBase(this, _phpPostMaxSize)[_phpPostMaxSize] = settings.get('phpPostMaxSize');
	}
	Uploader.EVENTS = {
	  startUpload: 'startUpload',
	  progressUpdate: 'progressUpdate',
	  complete: 'complete',
	  fileMaxSizeExceeded: 'fileMaxSizeExceeded',
	  createFileError: 'createFileError',
	  uploadFileError: 'uploadFileError'
	};
	Uploader.BOX_MIN_CHUNK_SIZE = 1024 * 1024;
	Uploader.CLOUD_MIN_CHUNK_SIZE = 1024 * 1024 * 5;
	Uploader.CLOUD_MAX_CHUNK_SIZE = 1024 * 1024 * 100;

	exports.Uploader = Uploader;
	exports.PreviewManager = PreviewManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX));
//# sourceMappingURL=uploader.bundle.js.map
