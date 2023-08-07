/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,main_core_events,main_core_minimal) {
	'use strict';

	var FileSender = /*#__PURE__*/function () {
	  function FileSender(task) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, FileSender);
	    babelHelpers.defineProperty(this, "token", null);
	    babelHelpers.defineProperty(this, "nextDataChunkToSend", null);
	    babelHelpers.defineProperty(this, "readOffset", 0);
	    this.diskFolderId = task.diskFolderId;
	    this.listener = task.listener;
	    this.status = task.status;
	    this.taskId = task.taskId;
	    this.fileData = task.fileData;
	    this.fileName = task.fileName || this.fileData.name;
	    this.generateUniqueName = task.generateUniqueName;
	    this.chunkSizeInBytes = task.chunkSize;
	    this.previewBlob = task.previewBlob || null;
	    this.requestToDelete = false;
	    this.listener('onStartUpload', {
	      id: this.taskId,
	      file: this.fileData,
	      previewData: this.previewBlob
	    });
	    this.host = options.host || null;
	    this.actionUploadChunk = options.actionUploadChunk || 'disk.api.content.upload';
	    this.actionCommitFile = options.actionCommitFile || 'disk.api.file.createByContent';
	    this.actionRollbackUpload = options.actionRollbackUpload || 'disk.api.content.rollbackUpload';
	    this.customHeaders = options.customHeaders || null;
	  }
	  babelHelpers.createClass(FileSender, [{
	    key: "uploadContent",
	    value: function uploadContent() {
	      var _this = this;
	      if (this.status === Uploader.STATUSES.CANCELLED) {
	        return;
	      }
	      this.status = Uploader.STATUSES.PROGRESS;
	      this.readNext();
	      var url = "".concat(this.host ? this.host : "", "\n\t\t\t/bitrix/services/main/ajax.php?action=").concat(this.actionUploadChunk, "\n\t\t\t&filename=").concat(this.fileName, "\n\t\t\t").concat(this.token ? "&token=" + this.token : "");
	      var contentRangeHeader = "bytes " + this.readOffset + "-" + (this.readOffset + this.chunkSizeInBytes - 1) + "/" + this.fileData.size;
	      this.calculateProgress();
	      var headers = {
	        "Content-Type": this.fileData.type,
	        "Content-Range": contentRangeHeader
	      };
	      if (!this.customHeaders) {
	        headers['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();
	      } else
	        //if (this.customHeaders)
	        {
	          for (var customHeader in this.customHeaders) {
	            if (this.customHeaders.hasOwnProperty(customHeader)) {
	              headers[customHeader] = this.customHeaders[customHeader];
	            }
	          }
	        }
	      fetch(url, {
	        method: 'POST',
	        headers: headers,
	        credentials: "include",
	        body: this.nextDataChunkToSend
	      }).then(function (response) {
	        return response.json();
	      }).then(function (result) {
	        if (result.errors.length > 0) {
	          _this.status = Uploader.STATUSES.FAILED;
	          _this.listener('onUploadFileError', {
	            id: _this.taskId,
	            result: result
	          });
	          console.error(result.errors[0].message);
	        } else if (result.data.token) {
	          _this.token = result.data.token;
	          _this.readOffset = _this.readOffset + _this.chunkSizeInBytes;
	          if (!_this.isEndOfFile()) {
	            _this.uploadContent();
	          } else {
	            _this.createFileFromUploadedChunks();
	          }
	        }
	      })["catch"](function (err) {
	        _this.status = Uploader.STATUSES.FAILED;
	        _this.listener('onUploadFileError', {
	          id: _this.taskId,
	          result: err
	        });
	      });
	    }
	  }, {
	    key: "deleteContent",
	    value: function deleteContent() {
	      this.status = Uploader.STATUSES.CANCELLED;
	      this.requestToDelete = true;
	      if (!this.token) {
	        console.error('Empty token.');
	        return;
	      }
	      var url = "".concat(this.host ? this.host : "", "/bitrix/services/main/ajax.php?\n\t\taction=").concat(this.actionRollbackUpload, "&token=").concat(this.token);
	      var headers = {};
	      if (!this.customHeaders) {
	        headers['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();
	      } else
	        //if (this.customHeaders)
	        {
	          for (var customHeader in this.customHeaders) {
	            if (this.customHeaders.hasOwnProperty(customHeader)) {
	              headers[customHeader] = this.customHeaders[customHeader];
	            }
	          }
	        }
	      fetch(url, {
	        method: 'POST',
	        credentials: "include",
	        headers: headers
	      }).then(function (response) {
	        return response.json();
	      }).then(function (result) {
	        return console.log(result);
	      })["catch"](function (err) {
	        return console.error(err);
	      });
	    }
	  }, {
	    key: "createFileFromUploadedChunks",
	    value: function createFileFromUploadedChunks() {
	      var _this2 = this;
	      if (!this.token) {
	        console.error('Empty token.');
	        return;
	      }
	      if (this.requestToDelete) {
	        return;
	      }
	      var url = "".concat(this.host ? this.host : "", "/bitrix/services/main/ajax.php?action=").concat(this.actionCommitFile, "&filename=").concat(this.fileName) + "&folderId=" + this.diskFolderId + "&contentId=" + this.token + (this.generateUniqueName ? "&generateUniqueName=true" : "");
	      var headers = {
	        "X-Upload-Content-Type": this.fileData.type
	      };
	      if (!this.customHeaders) {
	        headers['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();
	      } else
	        //if (this.customHeaders)
	        {
	          for (var customHeader in this.customHeaders) {
	            if (this.customHeaders.hasOwnProperty(customHeader)) {
	              headers[customHeader] = this.customHeaders[customHeader];
	            }
	          }
	        }
	      var formData = new FormData();
	      if (this.previewBlob) {
	        formData.append("previewFile", this.previewBlob, "preview_" + this.fileName + ".jpg");
	      }
	      fetch(url, {
	        method: 'POST',
	        headers: headers,
	        credentials: "include",
	        body: formData
	      }).then(function (response) {
	        return response.json();
	      }).then(function (result) {
	        _this2.uploadResult = result;
	        if (result.errors.length > 0) {
	          _this2.status = Uploader.STATUSES.FAILED;
	          _this2.listener('onCreateFileError', {
	            id: _this2.taskId,
	            result: result
	          });
	          console.error(result.errors[0].message);
	        } else {
	          _this2.calculateProgress();
	          _this2.status = Uploader.STATUSES.DONE;
	          _this2.listener('onComplete', {
	            id: _this2.taskId,
	            result: result
	          });
	        }
	      })["catch"](function (err) {
	        _this2.status = Uploader.STATUSES.FAILED;
	        _this2.listener('onCreateFileError', {
	          id: _this2.taskId,
	          result: err
	        });
	      });
	    }
	  }, {
	    key: "calculateProgress",
	    value: function calculateProgress() {
	      this.progress = Math.round(this.readOffset * 100 / this.fileData.size);
	      this.listener('onProgress', {
	        id: this.taskId,
	        progress: this.progress,
	        readOffset: this.readOffset,
	        fileSize: this.fileData.size
	      });
	    }
	  }, {
	    key: "readNext",
	    value: function readNext() {
	      if (this.readOffset + this.chunkSizeInBytes > this.fileData.size) {
	        this.chunkSizeInBytes = this.fileData.size - this.readOffset;
	      }
	      this.nextDataChunkToSend = this.fileData.slice(this.readOffset, this.readOffset + this.chunkSizeInBytes);
	    }
	  }, {
	    key: "isEndOfFile",
	    value: function isEndOfFile() {
	      return this.readOffset >= this.fileData.size;
	    }
	  }]);
	  return FileSender;
	}();

	var Uploader = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Uploader, _EventEmitter);
	  //1Mb
	  //5Mb
	  //100Mb

	  function Uploader(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Uploader);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Uploader).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "queue", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "isCloud", BX.message.isCloud);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "phpUploadMaxFilesize", BX.message.phpUploadMaxFilesize);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "phpPostMaxSize", BX.message.phpPostMaxSize);
	    _this.setEventNamespace('BX.Messenger.Lib.Uploader');
	    _this.generatePreview = options.generatePreview || false;
	    if (options) {
	      _this.inputNode = options.inputNode || null;
	      _this.dropNode = options.dropNode || null;
	      _this.fileMaxSize = options.fileMaxSize || null;
	      _this.fileMaxWidth = options.fileMaxWidth || null;
	      _this.fileMaxHeight = options.fileMaxHeight || null;
	      if (options.sender) {
	        _this.senderOptions = {
	          host: options.sender.host,
	          actionUploadChunk: options.sender.actionUploadChunk,
	          actionCommitFile: options.sender.actionCommitFile,
	          actionRollbackUpload: options.sender.actionRollbackUpload,
	          customHeaders: options.sender.customHeaders || null
	        };
	      }
	      _this.assignInput();
	      _this.assignDrop();
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Uploader, [{
	    key: "setInputNode",
	    value: function setInputNode(node) {
	      if (node instanceof HTMLInputElement || Array.isArray(node)) {
	        this.inputNode = node;
	        this.assignInput();
	      }
	    }
	  }, {
	    key: "addFilesFromEvent",
	    value: function addFilesFromEvent(event) {
	      var _this2 = this;
	      Array.from(event.target.files).forEach(function (file) {
	        _this2.emitSelectedFile(file);
	      });
	    }
	  }, {
	    key: "getPreview",
	    value: function getPreview(file) {
	      var _this3 = this;
	      return new Promise(function (resolve, reject) {
	        if (!_this3.generatePreview) {
	          resolve();
	        }
	        if (file instanceof File) {
	          if (file.type.startsWith('video')) {
	            Uploader.getVideoPreviewBlob(file, 10).then(function (blob) {
	              return _this3.getImageDimensions(blob);
	            }).then(function (result) {
	              return resolve(result);
	            })["catch"](function (reason) {
	              return reject(reason);
	            });
	          } else if (file.type.startsWith('image')) {
	            var blob = new Blob([file], {
	              type: file.type
	            });
	            _this3.getImageDimensions(blob).then(function (result) {
	              return resolve(result);
	            });
	          } else {
	            resolve();
	          }
	        } else {
	          reject("Parameter 'file' is not instance of 'File'");
	        }
	      });
	    }
	  }, {
	    key: "addTask",
	    value: function addTask(task) {
	      var _this4 = this;
	      if (!this.isModernBrowser()) {
	        console.warn('Unsupported browser!');
	        return;
	      }
	      if (!this.checkTaskParams(task)) {
	        return;
	      }
	      task.chunkSize = this.calculateChunkSize(task.chunkSize);
	      task.listener = function (event, data) {
	        return _this4.onUploadEvent(event, data);
	      };
	      task.status = Uploader.STATUSES.PENDING;
	      var fileSender = new FileSender(task, this.senderOptions);
	      this.queue.push(fileSender);
	      this.checkUploadQueue();
	    }
	  }, {
	    key: "deleteTask",
	    value: function deleteTask(taskId) {
	      if (!taskId) {
	        return;
	      }
	      this.queue = this.queue.filter(function (queueItem) {
	        if (queueItem.taskId === taskId) {
	          queueItem.deleteContent();
	          return false;
	        }
	        return true;
	      });
	    }
	  }, {
	    key: "getTask",
	    value: function getTask(taskId) {
	      var task = this.queue.find(function (queueItem) {
	        return queueItem.taskId === taskId;
	      });
	      if (task) {
	        return {
	          id: task.id,
	          diskFolderId: task.diskFolderId,
	          fileData: task.fileData,
	          fileName: task.fileName,
	          progress: task.progress,
	          readOffset: task.readOffset,
	          status: task.status,
	          token: task.token,
	          uploadResult: task.uploadResult
	        };
	      }
	      return null;
	    }
	  }, {
	    key: "checkUploadQueue",
	    value: function checkUploadQueue() {
	      if (this.queue.length > 0) {
	        var inProgressTasks = this.queue.filter(function (queueTask) {
	          return queueTask.status === Uploader.STATUSES.PENDING;
	        });
	        if (inProgressTasks.length > 0) {
	          inProgressTasks[0].uploadContent();
	        }
	      }
	    }
	  }, {
	    key: "onUploadEvent",
	    value: function onUploadEvent(event, data) {
	      this.emit(event, data);
	      this.checkUploadQueue();
	    }
	  }, {
	    key: "checkTaskParams",
	    value: function checkTaskParams(task) {
	      if (!task.taskId) {
	        console.error('Empty Task ID.');
	        return false;
	      }
	      if (!task.fileData) {
	        console.error('Empty file data.');
	        return false;
	      }
	      if (!task.diskFolderId) {
	        console.error('Empty disk folder ID.');
	        return false;
	      }
	      if (this.fileMaxSize && this.fileMaxSize < task.fileData.size) {
	        var data = {
	          maxFileSizeLimit: this.fileMaxSize,
	          file: task.fileData
	        };
	        this.emit('onFileMaxSizeExceeded', data);
	        return false;
	      }
	      return true;
	    }
	  }, {
	    key: "calculateChunkSize",
	    value: function calculateChunkSize(taskChunkSize) {
	      if (main_core_minimal.Type.isUndefined(this.isCloud))
	        // widget case
	        {
	          return taskChunkSize;
	        }
	      var chunk = 0;
	      if (taskChunkSize) {
	        chunk = taskChunkSize;
	      }
	      if (this.isCloud === 'Y') {
	        chunk = chunk < Uploader.CLOUD_MIN_CHUNK_SIZE ? Uploader.CLOUD_MIN_CHUNK_SIZE : chunk;
	        chunk = chunk > Uploader.CLOUD_MAX_CHUNK_SIZE ? Uploader.CLOUD_MAX_CHUNK_SIZE : chunk;
	      } else
	        //if(this.isCloud === 'N')
	        {
	          var maxBoxChunkSize = Math.min(this.phpPostMaxSize, this.phpUploadMaxFilesize);
	          chunk = chunk < Uploader.BOX_MIN_CHUNK_SIZE ? Uploader.BOX_MIN_CHUNK_SIZE : chunk;
	          chunk = chunk > maxBoxChunkSize ? maxBoxChunkSize : chunk;
	        }
	      return chunk;
	    }
	  }, {
	    key: "isModernBrowser",
	    value: function isModernBrowser() {
	      return typeof fetch !== 'undefined';
	    }
	  }, {
	    key: "assignInput",
	    value: function assignInput() {
	      var _this5 = this;
	      if (this.inputNode instanceof HTMLInputElement) {
	        this.setOnChangeEventListener(this.inputNode);
	      } else if (Array.isArray(this.inputNode)) {
	        this.inputNode.forEach(function (node) {
	          if (node instanceof HTMLInputElement) {
	            _this5.setOnChangeEventListener(node);
	          }
	        });
	      }
	    }
	  }, {
	    key: "setOnChangeEventListener",
	    value: function setOnChangeEventListener(inputNode) {
	      var _this6 = this;
	      inputNode.addEventListener('change', function (event) {
	        _this6.addFilesFromEvent(event);
	      }, false);
	    }
	  }, {
	    key: "assignDrop",
	    value: function assignDrop() {
	      var _this7 = this;
	      if (this.dropNode instanceof HTMLElement) {
	        this.setDropEventListener(this.dropNode);
	      } else if (Array.isArray(this.dropNode)) {
	        this.dropNode.forEach(function (node) {
	          if (node instanceof HTMLElement) {
	            _this7.setDropEventListener(node);
	          }
	        });
	      }
	    }
	  }, {
	    key: "setDropEventListener",
	    value: function setDropEventListener(dropNode) {
	      var _this8 = this;
	      dropNode.addEventListener('drop', function (event) {
	        event.preventDefault();
	        event.stopPropagation();
	        Array.from(event.dataTransfer.files).forEach(function (file) {
	          _this8.emitSelectedFile(file);
	        });
	      }, false);
	    }
	  }, {
	    key: "emitSelectedFile",
	    value: function emitSelectedFile(file) {
	      var _this9 = this;
	      var data = {
	        file: file
	      };
	      this.getPreview(file).then(function (previewData) {
	        if (previewData) {
	          data['previewData'] = previewData.blob;
	          data['previewDataWidth'] = previewData.width;
	          data['previewDataHeight'] = previewData.height;
	          if (_this9.fileMaxWidth || _this9.fileMaxHeight) {
	            var isMaxWidthExceeded = _this9.fileMaxWidth === null ? false : _this9.fileMaxWidth < data['previewDataWidth'];
	            var isMaxHeightExceeded = _this9.fileMaxHeight === null ? false : _this9.fileMaxHeight < data['previewDataHeight'];
	            if (isMaxWidthExceeded || isMaxHeightExceeded) {
	              var eventData = {
	                maxWidth: _this9.fileMaxWidth,
	                maxHeight: _this9.fileMaxHeight,
	                fileWidth: data['previewDataWidth'],
	                fileHeight: data['previewDataHeight']
	              };
	              _this9.emit('onFileMaxResolutionExceeded', eventData);
	              return false;
	            }
	          }
	        }
	        _this9.emit('onSelectFile', data);
	      })["catch"](function (err) {
	        console.warn("Couldn't get preview for file ".concat(file.name, ". Error: ").concat(err));
	        _this9.emit('onSelectFile', data);
	      });
	    }
	  }, {
	    key: "getImageDimensions",
	    value: function getImageDimensions(fileBlob) {
	      return new Promise(function (resolved, rejected) {
	        if (!fileBlob) {
	          rejected('getImageDimensions: fileBlob can\'t be empty');
	        }
	        var img = new Image();
	        img.onload = function () {
	          resolved({
	            blob: fileBlob,
	            width: img.width,
	            height: img.height
	          });
	        };
	        img.onerror = function () {
	          rejected();
	        };
	        img.src = URL.createObjectURL(fileBlob);
	      });
	    }
	  }], [{
	    key: "getVideoPreviewBlob",
	    value: function getVideoPreviewBlob(file) {
	      var seekTime = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      return new Promise(function (resolve, reject) {
	        var videoPlayer = document.createElement('video');
	        videoPlayer.setAttribute('src', URL.createObjectURL(file));
	        videoPlayer.load();
	        videoPlayer.addEventListener('error', function (error) {
	          reject("Error while loading video file", error);
	        });
	        videoPlayer.addEventListener('loadedmetadata', function () {
	          if (videoPlayer.duration < seekTime) {
	            seekTime = 0;
	            // reject("Too big seekTime for the video.");
	            // return;
	          }

	          videoPlayer.currentTime = seekTime;
	          videoPlayer.addEventListener('seeked', function () {
	            var canvas = document.createElement("canvas");
	            canvas.width = videoPlayer.videoWidth;
	            canvas.height = videoPlayer.videoHeight;
	            var context = canvas.getContext("2d");
	            context.drawImage(videoPlayer, 0, 0, canvas.width, canvas.height);
	            context.canvas.toBlob(function (blob) {
	              return resolve(blob);
	            }, "image/jpeg", 1);
	          });
	        });
	      });
	    }
	  }]);
	  return Uploader;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(Uploader, "STATUSES", {
	  PENDING: 0,
	  PROGRESS: 1,
	  DONE: 2,
	  CANCELLED: 3,
	  FAILED: 4
	});
	babelHelpers.defineProperty(Uploader, "BOX_MIN_CHUNK_SIZE", 1024 * 1024);
	babelHelpers.defineProperty(Uploader, "CLOUD_MIN_CHUNK_SIZE", 1024 * 1024 * 5);
	babelHelpers.defineProperty(Uploader, "CLOUD_MAX_CHUNK_SIZE", 1024 * 1024 * 100);

	exports.Uploader = Uploader;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {}),BX.Event,BX));
//# sourceMappingURL=uploader.bundle.js.map
