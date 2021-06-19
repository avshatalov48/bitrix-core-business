this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_call,im_debug,im_application_launch,im_component_call,im_model,im_controller,im_lib_cookie,im_lib_localstorage,im_lib_logger,im_lib_clipboard,im_lib_uploader,im_lib_desktop,im_const,ui_notification,ui_buttons,ui_progressround,ui_viewer,ui_vue,ui_vue_vuex,main_core,main_core_events,promise,main_date,pull_client,im_provider_pull,rest_client,im_lib_utils) {
	'use strict';

	var RestAuth = Object.freeze({
	  guest: 'guest'
	});
	var CallRestClient = /*#__PURE__*/function () {
	  function CallRestClient(params) {
	    babelHelpers.classCallCheck(this, CallRestClient);
	    this.queryAuthRestore = false;
	    this.setAuthId(RestAuth.guest);
	    this.restClient = new rest_client.RestClient({
	      endpoint: params.endpoint,
	      queryParams: this.queryParams,
	      cors: true
	    });
	  }

	  babelHelpers.createClass(CallRestClient, [{
	    key: "setAuthId",
	    value: function setAuthId(authId) {
	      var customAuthId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

	      if (babelHelpers.typeof(this.queryParams) !== 'object') {
	        this.queryParams = {};
	      }

	      if (authId == RestAuth.guest || typeof authId === 'string' && authId.match(/^[a-f0-9]{32}$/)) {
	        this.queryParams.call_auth_id = authId;
	      } else {
	        console.error("%CallRestClient.setAuthId: auth is not correct (%c".concat(authId, "%c)"), "color: black;", "font-weight: bold; color: red", "color: black");
	        return false;
	      }

	      if (authId == RestAuth.guest && typeof customAuthId === 'string' && customAuthId.match(/^[a-f0-9]{32}$/)) {
	        this.queryParams.call_custom_auth_id = customAuthId;
	      }

	      return true;
	    }
	  }, {
	    key: "setChatId",
	    value: function setChatId(chatId) {
	      if (babelHelpers.typeof(this.queryParams) !== 'object') {
	        this.queryParams = {};
	      }

	      this.queryParams.call_chat_id = chatId;
	    }
	  }, {
	    key: "setConfId",
	    value: function setConfId(alias) {
	      if (babelHelpers.typeof(this.queryParams) !== 'object') {
	        this.queryParams = {};
	      }

	      this.queryParams.videoconf_id = alias;
	    }
	  }, {
	    key: "setPassword",
	    value: function setPassword(password) {
	      if (babelHelpers.typeof(this.queryParams) !== 'object') {
	        this.queryParams = {};
	      }

	      this.queryParams.videoconf_password = password;
	    }
	  }, {
	    key: "callMethod",
	    value: function callMethod(method, params, callback, sendCallback) {
	      var _this = this;

	      var logTag = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;

	      if (!logTag) {
	        logTag = im_lib_utils.Utils.getLogTrackingParams({
	          name: method
	        });
	      }

	      var promise$$1 = new BX.Promise(); // TODO: Callbacks methods will not work!

	      this.restClient.callMethod(method, params, null, sendCallback, logTag).then(function (result) {
	        _this.queryAuthRestore = false;
	        promise$$1.fulfill(result);
	      }).catch(function (result) {
	        var error = result.error();

	        if (error.ex.error == 'LIVECHAT_AUTH_WIDGET_USER') {
	          _this.setAuthId(error.ex.hash);

	          if (method === RestMethod.widgetUserRegister) {
	            console.warn("BX.LiveChatRestClient: ".concat(error.ex.error_description, " (").concat(error.ex.error, ")"));
	            _this.queryAuthRestore = false;
	            promise$$1.reject(result);
	            return false;
	          }

	          if (!_this.queryAuthRestore) {
	            console.warn('BX.LiveChatRestClient: your auth-token has expired, send query with a new token');
	            _this.queryAuthRestore = true;

	            _this.restClient.callMethod(method, params, null, sendCallback, logTag).then(function (result) {
	              _this.queryAuthRestore = false;
	              promise$$1.fulfill(result);
	            }).catch(function (result) {
	              _this.queryAuthRestore = false;
	              promise$$1.reject(result);
	            });

	            return false;
	          }
	        }

	        _this.queryAuthRestore = false;
	        promise$$1.reject(result);
	      });
	      return promise$$1;
	    }
	  }, {
	    key: "callBatch",
	    value: function callBatch(calls, callback, bHaltOnError, sendCallback, logTag) {
	      var _this2 = this;

	      var resultCallback = function resultCallback(result) {

	        for (var method in calls) {
	          if (!calls.hasOwnProperty(method)) {
	            continue;
	          }

	          var _error = result[method].error();

	          if (_error && _error.ex.error == 'LIVECHAT_AUTH_WIDGET_USER') {
	            _this2.setAuthId(_error.ex.hash);

	            if (method === RestMethod.widgetUserRegister) {
	              console.warn("BX.LiveChatRestClient: ".concat(_error.ex.error_description, " (").concat(_error.ex.error, ")"));
	              _this2.queryAuthRestore = false;
	              callback(result);
	              return false;
	            }

	            if (!_this2.queryAuthRestore) {
	              console.warn('BX.LiveChatRestClient: your auth-token has expired, send query with a new token');
	              _this2.queryAuthRestore = true;

	              _this2.restClient.callBatch(calls, callback, bHaltOnError, sendCallback, logTag);

	              return false;
	            }
	          }
	        }

	        _this2.queryAuthRestore = false;
	        callback(result);
	        return true;
	      };

	      return this.restClient.callBatch(calls, resultCallback, bHaltOnError, sendCallback, logTag);
	    }
	  }]);
	  return CallRestClient;
	}();

	/**
	 * Bitrix Im mobile
	 * Dialog application
	 *
	 * @package bitrix
	 * @subpackage mobile
	 * @copyright 2001-2020 Bitrix
	 */

	var CallApplication = /*#__PURE__*/function () {
	  /* region 01. Initialize */
	  function CallApplication() {
	    var _this = this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, CallApplication);
	    this.inited = false;
	    this.dialogInited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    this.params.userId = this.params.userId ? parseInt(this.params.userId) : 0;
	    this.params.siteId = this.params.siteId || '';
	    this.params.chatId = this.params.chatId ? parseInt(this.params.chatId) : 0;
	    this.params.dialogId = this.params.chatId ? 'chat' + this.params.chatId.toString() : '0';
	    this.params.passwordRequired = !!this.params.passwordRequired;
	    this.messagesQueue = [];
	    this.template = null;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.event = new ui_vue.VueVendorV2();
	    this.callContainer = null;
	    this.callView = null;
	    this.preCall = null;
	    this.currentCall = null;
	    this.videoStrategy = null;
	    this.featureConfig = {};
	    (params.featureConfig || []).forEach(function (limit) {
	      _this.featureConfig[limit.id] = limit;
	    });
	    this.localVideoStream = null;
	    this.conferencePageTagInterval = null;
	    this.onCallUserInvitedHandler = this.onCallUserInvited.bind(this);
	    this.onCallUserStateChangedHandler = this.onCallUserStateChanged.bind(this);
	    this.onCallUserMicrophoneStateHandler = this.onCallUserMicrophoneState.bind(this);
	    this.onCallLocalMediaReceivedHandler = BX.debounce(this.onCallLocalMediaReceived.bind(this), 1000);
	    this.onCallUserStreamReceivedHandler = this.onCallUserStreamReceived.bind(this);
	    this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
	    this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
	    this.onCallUserScreenStateHandler = this.onCallUserScreenState.bind(this);
	    this.onCallUserRecordStateHandler = this.onCallUserRecordState.bind(this);
	    this.onCallUserFloorRequestHandler = this.onCallUserFloorRequest.bind(this);
	    this._onCallJoinHandler = this.onCallJoin.bind(this);
	    this.onCallLeaveHandler = this.onCallLeave.bind(this);
	    this.onCallDestroyHandler = this.onCallDestroy.bind(this);
	    this.onPreCallDestroyHandler = this.onPreCallDestroy.bind(this);
	    this.onPreCallUserStateChangedHandler = this.onPreCallUserStateChanged.bind(this);
	    this.waitingForCallStatus = false;
	    this.waitingForCallStatusTimeout = null;
	    this.callEventReceived = false;
	    this.callRecordState = BX.Call.View.RecordState.Stopped;
	    this.desktop = null;
	    this.floatingScreenShareWindow = null;
	    this.initDesktopEvents().then(function () {
	      return _this.initRestClient();
	    }).then(function () {
	      return _this.subscribePreCallChanges();
	    }).then(function () {
	      return _this.initPullClient();
	    }).then(function () {
	      return _this.initCore();
	    }).then(function () {
	      return _this.setModelData();
	    }).then(function () {
	      return _this.initComponent();
	    }).then(function () {
	      return _this.initCallInterface();
	    }) // .then(() => this.initUploader())
	    .then(function () {
	      return _this.initUserComplete();
	    }).catch(function () {});
	  }

	  babelHelpers.createClass(CallApplication, [{
	    key: "initDesktopEvents",
	    value: function initDesktopEvents() {
	      var _this2 = this;

	      if (!im_lib_utils.Utils.platform.isBitrixDesktop()) {
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      }

	      this.desktop = new im_lib_desktop.Desktop();
	      this.floatingScreenShareWindow = new BX.Call.FloatingScreenShare({
	        desktop: this.desktop,
	        onBackToCallClick: this.onFloatingScreenShareBackToCallClick.bind(this),
	        onStopSharingClick: this.onFloatingScreenShareStopClick.bind(this)
	      });

	      if (this.floatingScreenShareWindow) {
	        this.desktop.addCustomEvent("BXScreenMediaSharing", function (id, title, x, y, width, height, app) {
	          _this2.floatingScreenShareWindow.setSharingData({
	            title: title,
	            x: x,
	            y: y,
	            width: width,
	            height: height,
	            app: app
	          }).then(function () {
	            _this2.floatingScreenShareWindow.show();
	          }).catch(function (error) {
	            im_lib_logger.Logger.log('setSharingData error', error);
	          });
	        });
	        window.addEventListener('focus', function () {
	          _this2.onWindowFocus();
	        });
	        window.addEventListener('blur', function () {
	          _this2.onWindowBlur();
	        });
	      }

	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initRestClient",
	    value: function initRestClient() {
	      this.restClient = new CallRestClient({
	        endpoint: this.getHost() + '/rest'
	      });
	      this.restClient.setConfId(this.params.conferenceId);
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initPullClient",
	    value: function initPullClient() {
	      if (!this.params.isIntranetOrExtranet) {
	        this.pullClient = new pull_client.PullClient({
	          serverEnabled: true,
	          userId: this.params.userId,
	          siteId: this.params.siteId,
	          restClient: this.restClient,
	          skipStorageInit: true,
	          configTimestamp: 0,
	          skipCheckRevision: true,
	          getPublicListMethod: 'im.call.channel.public.list'
	        });
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      } else {
	        this.pullClient = BX.PULL;
	        return this.pullClient.start().then(function () {
	          return new Promise(function (resolve, reject) {
	            return resolve();
	          });
	        });
	      }
	    }
	  }, {
	    key: "initPullHandlers",
	    value: function initPullHandlers() {
	      this.pullClient.subscribe(new im_provider_pull.ImCallPullHandler({
	        store: this.controller.getStore(),
	        application: this,
	        controller: this.controller
	      }));
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initCore",
	    value: function initCore() {
	      var _this3 = this;

	      this.controller = new im_controller.Controller({
	        host: this.getHost(),
	        siteId: this.params.siteId,
	        userId: this.params.userId,
	        languageId: this.params.language,
	        pull: {
	          client: this.pullClient
	        },
	        rest: {
	          client: this.restClient
	        },
	        //localize: this.localize,
	        vuexBuilder: {
	          database: !im_lib_utils.Utils.browser.isIe(),
	          databaseName: 'imol/call',
	          databaseType: ui_vue_vuex.VuexBuilder.DatabaseType.localStorage,
	          models: [im_model.CallApplicationModel.create()]
	        }
	      });
	      return new Promise(function (resolve, reject) {
	        _this3.controller.ready().then(function () {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "initComponent",
	    value: function initComponent() {
	      var _this4 = this;

	      if (this.getStartupErrorCode()) {
	        this.setError(this.getStartupErrorCode());
	      }

	      return this.controller.createVue(this, {
	        el: this.rootNode,
	        data: function data() {
	          return {
	            chatId: _this4.getChatId(),
	            dialogId: _this4.getDialogId()
	          };
	        },
	        template: "<bx-im-component-call :dialogId=\"dialogId\"/>"
	      }).then(function (vue) {
	        _this4.template = vue;
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "setModelData",
	    value: function setModelData() {
	      this.controller.getStore().commit('application/set', {
	        dialog: {
	          chatId: this.getChatId(),
	          dialogId: this.getDialogId()
	        }
	      });

	      if (this.params.passwordRequired) {
	        this.controller.getStore().commit('callApplication/common', {
	          passChecked: false
	        });
	      }

	      if (this.params.conferenceTitle) {
	        this.controller.getStore().commit('callApplication/setConferenceTitle', {
	          conferenceTitle: this.params.conferenceTitle
	        });
	      }

	      if (this.params.alias) {
	        this.controller.getStore().commit('callApplication/setAlias', {
	          alias: this.params.alias
	        });
	      }

	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initCallInterface",
	    value: function initCallInterface() {
	      this.callContainer = document.getElementById('bx-im-component-call-container');
	      this.callView = new BX.Call.View({
	        container: this.callContainer,
	        showChatButtons: true,
	        showShareButton: this.getFeatureState('screenSharing') !== CallApplication.FeatureState.Disabled,
	        showRecordButton: this.getFeatureState('record') !== CallApplication.FeatureState.Disabled,
	        userLimit: BX.Call.Util.getUserLimit(),
	        isIntranetOrExtranet: !!this.params.isIntranetOrExtranet,
	        language: this.params.language,
	        layout: im_lib_utils.Utils.device.isMobile() ? BX.Call.View.Layout.Mobile : BX.Call.View.Layout.Centered,
	        uiState: BX.Call.View.UiState.Preparing,
	        blockedButtons: ['camera', 'microphone', 'chat', 'floorRequest', 'screen', 'record'],
	        localUserState: BX.Call.UserState.Idle,
	        hiddenButtons: this.params.isIntranetOrExtranet ? [] : ['record']
	      });
	      this.callView.subscribe(BX.Call.View.Event.onButtonClick, this.onCallButtonClick.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onReplaceCamera, this.onCallReplaceCamera.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onReplaceMicrophone, this.onCallReplaceMicrophone.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onReplaceSpeaker, this.onCallReplaceSpeaker.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onChangeHdVideo, this.onCallViewChangeHdVideo.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onChangeMicAutoParams, this.onCallViewChangeMicAutoParams.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onUserNameMouseOver, this.onCallViewUserNameMouseOver.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onUserNameMouseOut, this.onCallViewUserNameMouseOut.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onUserNameClick, this.onCallViewUserNameClick.bind(this));
	      this.callView.subscribe(BX.Call.View.Event.onUserChangeNameClick, this.onCallViewUserChangeNameClick.bind(this));
	      this.callView.blockAddUser();
	      this.callView.blockHistoryButton();

	      if (!im_lib_utils.Utils.device.isMobile()) {
	        this.callView.show();
	      }

	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initUser",
	    value: function initUser() {
	      var _this5 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this5.getStartupErrorCode() || !_this5.controller.getStore().state.callApplication.common.passChecked) {
	          return reject();
	        }

	        if (_this5.params.userId > 0) {
	          _this5.controller.setUserId(_this5.params.userId);

	          if (_this5.params.isIntranetOrExtranet) {
	            _this5.switchToSessAuth();

	            _this5.controller.getStore().commit('callApplication/user', {
	              id: _this5.params.userId
	            });
	          } else {
	            var hashFromCookie = _this5.getUserHashCookie();

	            if (hashFromCookie) {
	              _this5.restClient.setAuthId(hashFromCookie);

	              _this5.restClient.setChatId(_this5.getChatId());

	              _this5.controller.getStore().commit('callApplication/user', {
	                id: _this5.params.userId,
	                hash: hashFromCookie
	              });

	              _this5.pullClient.start();
	            }
	          }

	          _this5.controller.getStore().commit('callApplication/common', {
	            inited: true
	          });

	          return resolve();
	        } else {
	          _this5.restClient.setAuthId('guest');

	          _this5.restClient.setChatId(_this5.getChatId());

	          if (typeof BX.SidePanel !== 'undefined') {
	            BX.SidePanel.Instance.disableAnchorBinding();
	          }

	          return _this5.restClient.callMethod('im.call.user.register', {
	            alias: _this5.params.alias,
	            user_hash: _this5.getUserHashCookie() || ''
	          }).then(function (result) {
	            BX.message['USER_ID'] = result.data().id;

	            _this5.controller.getStore().commit('callApplication/user', {
	              id: result.data().id,
	              hash: result.data().hash
	            });

	            _this5.controller.setUserId(result.data().id);

	            _this5.callView.setLocalUserId(result.data().id);

	            if (result.data().created) {
	              _this5.params.userCount++;
	            }

	            _this5.controller.getStore().commit('callApplication/common', {
	              inited: true
	            });

	            _this5.restClient.setAuthId(result.data().hash);

	            _this5.pullClient.start();

	            return resolve();
	          });
	        }
	      });
	    } // initUploader()
	    // {
	    // 	this.uploader = new Uploader({
	    // 		generatePreview: true,
	    // 		sender: {
	    // 			actionUploadChunk: 'im.call.disk.upload',
	    // 			actionCommitFile: 'im.call.disk.commit',
	    // 		}
	    // 	});
	    //
	    // 	this.uploader.subscribe('onStartUpload', event => {
	    // 		const eventData = event.getData();
	    // 		Logger.log('Uploader: onStartUpload', eventData);
	    //
	    // 		this.controller.getStore().dispatch('files/update', {
	    // 			chatId: this.getChatId(),
	    // 			id: eventData.id,
	    // 			fields: {
	    // 				status: FileStatus.upload,
	    // 				progress: 0
	    // 			}
	    // 		});
	    // 	});
	    //
	    // 	this.uploader.subscribe('onProgress', (event) => {
	    // 		const eventData = event.getData();
	    // 		Logger.log('Uploader: onProgress', eventData);
	    //
	    // 		this.controller.getStore().dispatch('files/update', {
	    // 			chatId: this.getChatId(),
	    // 			id: eventData.id,
	    // 			fields: {
	    // 				status: FileStatus.upload,
	    // 				progress: (eventData.progress === 100 ? 99 : eventData.progress),
	    // 			}
	    // 		});
	    // 	});
	    //
	    // 	this.uploader.subscribe('onSelectFile', (event) => {
	    // 		const eventData = event.getData();
	    // 		const file = eventData.file;
	    // 		Logger.log('Uploader: onSelectFile', eventData);
	    //
	    // 		let fileType = 'file';
	    // 		if (file.type.toString().startsWith('image'))
	    // 		{
	    // 			fileType = 'image';
	    // 		}
	    // 		else if (file.type.toString().startsWith('video'))
	    // 		{
	    // 			fileType = 'video';
	    // 		}
	    //
	    // 		this.controller.getStore().dispatch('files/add', {
	    // 			chatId: this.getChatId(),
	    // 			authorId: this.controller.getUserId(),
	    // 			name: file.name,
	    // 			type: fileType,
	    // 			extension: file.name.split('.').splice(-1)[0],
	    // 			size: file.size,
	    // 			image: !eventData.previewData? false: {
	    // 				width: eventData.previewDataWidth,
	    // 				height: eventData.previewDataHeight,
	    // 			},
	    // 			status: FileStatus.wait,
	    // 			progress: 0,
	    // 			authorName: this.controller.application.getCurrentUser().name,
	    // 			urlPreview: eventData.previewData? URL.createObjectURL(eventData.previewData) : "",
	    // 		}).then(fileId => {
	    // 			this.addMessage('', {id: fileId, source: eventData, previewBlob: eventData.previewData})
	    // 		});
	    // 	});
	    //
	    // 	this.uploader.subscribe('onComplete', (event) => {
	    // 		const eventData = event.getData();
	    // 		Logger.log('Uploader: onComplete', eventData);
	    //
	    // 		this.controller.getStore().dispatch('files/update', {
	    // 			chatId: this.getChatId(),
	    // 			id: eventData.id,
	    // 			fields: {
	    // 				status: FileStatus.wait,
	    // 				progress: 100
	    // 			}
	    // 		});
	    //
	    // 		const message = this.messagesQueue.find(message => {
	    // 			if (message.file)
	    // 			{
	    // 				return message.file.id === eventData.id;
	    // 			}
	    //
	    // 			return false;
	    // 		});
	    // 		const fileType = this.controller.getStore().getters['files/get'](this.getChatId(), message.file.id, true).type;
	    //
	    // 		this.fileCommit({
	    // 			chatId: this.getChatId(),
	    // 			uploadId: eventData.result.data.file.id,
	    // 			messageText: message.text,
	    // 			messageId: message.id,
	    // 			fileId: message.file.id,
	    // 			fileType
	    // 		}, message);
	    // 	});
	    //
	    // 	this.uploader.subscribe('onUploadFileError', (event) => {
	    // 		const eventData = event.getData();
	    // 		Logger.log('Uploader: onUploadFileError', eventData);
	    //
	    // 		const message = this.messagesQueue.find(message => {
	    // 			if (message.file)
	    // 			{
	    // 				return message.file.id === eventData.id;
	    // 			}
	    //
	    // 			return false;
	    // 		});
	    //
	    // 		this.fileError(this.getChatId(), message.file.id, message.id);
	    // 	});
	    //
	    // 	this.uploader.subscribe('onCreateFileError', (event) => {
	    // 		const eventData = event.getData();
	    // 		Logger.log('Uploader: onCreateFileError', eventData);
	    //
	    // 		const message = this.messagesQueue.find(message => {
	    // 			if (message.file)
	    // 			{
	    // 				return message.file.id === eventData.id;
	    // 			}
	    //
	    // 			return false;
	    // 		});
	    //
	    // 		this.fileError(this.getChatId(), message.file.id, message.id);
	    // 	});
	    //
	    // 	return new Promise((resolve, reject) => resolve());
	    // }

	  }, {
	    key: "initUserComplete",
	    value: function initUserComplete() {
	      var _this6 = this;

	      return this.initUser().then(function () {
	        return _this6.startPageTagInterval();
	      }).then(function () {
	        return _this6.tryJoinExistingCall();
	      }).then(function () {
	        return _this6.initCall();
	      }).then(function () {
	        return _this6.initPullHandlers();
	      }).then(function () {
	        return _this6.subscribeToStoreChanges();
	      }).then(function () {
	        return _this6.initComplete();
	      }).catch(function () {});
	    }
	  }, {
	    key: "startPageTagInterval",
	    value: function startPageTagInterval() {
	      var _this7 = this;

	      return new Promise(function (resolve) {
	        clearInterval(_this7.conferencePageTagInterval);
	        _this7.conferencePageTagInterval = setInterval(function () {
	          im_lib_localstorage.LocalStorage.set(_this7.params.siteId, _this7.params.userId, BX.CallEngine.getConferencePageTag(_this7.params.dialogId), "Y", 2);
	        }, 1000);
	        resolve();
	      });
	    }
	  }, {
	    key: "tryJoinExistingCall",
	    value: function tryJoinExistingCall() {
	      var _this8 = this;

	      this.restClient.callMethod("im.call.tryJoinCall", {
	        entityType: 'chat',
	        entityId: this.params.dialogId,
	        provider: BX.Call.Provider.Voximplant,
	        type: BX.Call.Type.Permanent
	      }).then(function (result) {
	        im_lib_logger.Logger.warn('tryJoinCall', result.data());

	        if (result.data().success) {
	          _this8.waitingForCallStatus = true;
	          _this8.waitingForCallStatusTimeout = setTimeout(function () {
	            _this8.waitingForCallStatus = false;

	            if (!_this8.callEventReceived) {
	              _this8.setConferenceStatus(false);
	            }

	            _this8.callEventReceived = false;
	          }, 5000);
	        } else {
	          _this8.setConferenceStatus(false);
	        }
	      });
	    }
	  }, {
	    key: "subscribePreCallChanges",
	    value: function subscribePreCallChanges() {
	      BX.addCustomEvent(window, 'CallEvents::callCreated', this.onCallCreated.bind(this));
	    }
	  }, {
	    key: "onCallCreated",
	    value: function onCallCreated(e) {
	      var _this9 = this;

	      im_lib_logger.Logger.warn('we got event onCallCreated', e);

	      if (this.preCall || this.currentCall) {
	        return;
	      }

	      var call = e.call;

	      if (call.associatedEntity.type === 'chat' && call.associatedEntity.id === this.params.dialogId) {
	        this.preCall = e.call;
	        this.updatePreCallCounter();
	        this.preCall.addEventListener(BX.Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
	        this.preCall.addEventListener(BX.Call.Event.onDestroy, this.onPreCallDestroyHandler);

	        if (this.waitingForCallStatus) {
	          this.callEventReceived = true;
	        }

	        this.setConferenceStatus(true);
	        this.setConferenceStartDate(e.call.startDate);
	      }

	      var userReadyToJoin = this.controller.getStore().state.callApplication.common.userReadyToJoin;

	      if (userReadyToJoin) {
	        var videoEnabled = this.controller.getStore().state.callApplication.common.joinWithVideo;
	        setTimeout(function () {
	          BX.Call.Hardware.init().then(function () {
	            _this9.startCall(videoEnabled);
	          });
	        }, 1000);
	      }
	    }
	  }, {
	    key: "releasePreCall",
	    value: function releasePreCall() {
	      if (this.preCall) {
	        this.preCall.removeEventListener(BX.Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
	        this.preCall.removeEventListener(BX.Call.Event.onDestroy, this.onPreCallDestroyHandler);
	        this.preCall = null;
	      }
	    }
	  }, {
	    key: "onPreCallDestroy",
	    value: function onPreCallDestroy(e) {
	      if (this.waitingForCallStatusTimeout) {
	        clearTimeout(this.waitingForCallStatusTimeout);
	      }

	      this.setConferenceStatus(false);
	      this.releasePreCall();
	    }
	  }, {
	    key: "onPreCallUserStateChanged",
	    value: function onPreCallUserStateChanged(e) {
	      this.updatePreCallCounter();
	    }
	  }, {
	    key: "updatePreCallCounter",
	    value: function updatePreCallCounter() {
	      if (this.preCall) {
	        this.controller.getStore().commit('callApplication/common', {
	          userInCallCount: this.preCall.getParticipatingUsers().length
	        });
	      } else {
	        this.controller.getStore().commit('callApplication/common', {
	          userInCallCount: 0
	        });
	      }
	    }
	  }, {
	    key: "initCall",
	    value: function initCall() {
	      BX.CallEngine.setRestClient(this.restClient);
	      BX.CallEngine.setPullClient(this.pullClient);
	      BX.CallEngine.setCurrentUserId(this.controller.getUserId());
	      this.callView.unblockButtons(['chat']);
	    }
	  }, {
	    key: "createVideoStrategy",
	    value: function createVideoStrategy() {
	      if (this.videoStrategy) {
	        this.videoStrategy.destroy();
	      }

	      var strategyType = im_lib_utils.Utils.device.isMobile() ? BX.Call.VideoStrategy.Type.OnlySpeaker : BX.Call.VideoStrategy.Type.AllowAll;
	      this.videoStrategy = new BX.Call.VideoStrategy({
	        call: this.currentCall,
	        callView: this.callView,
	        strategyType: strategyType
	      });
	    }
	  }, {
	    key: "removeVideoStrategy",
	    value: function removeVideoStrategy() {
	      if (this.videoStrategy) {
	        this.videoStrategy.destroy();
	      }

	      this.videoStrategy = null;
	    }
	  }, {
	    key: "subscribeToStoreChanges",
	    value: function subscribeToStoreChanges() {
	      var _this10 = this;

	      this.controller.getStore().subscribe(function (mutation, state) {
	        var payload = mutation.payload,
	            type = mutation.type;

	        if (type === 'users/update' && payload.fields.name) {
	          if (_this10.callView) {
	            _this10.callView.updateUserData(babelHelpers.defineProperty({}, payload.id, {
	              name: payload.fields.name
	            }));
	          }
	        } else if (type === 'dialogues/update' && typeof payload.fields.counter === 'number') {
	          if (_this10.callView) {
	            _this10.callView.setButtonCounter('chat', payload.fields.counter);
	          }
	        } else if (type === 'dialogues/update' && payload.fields.name) {
	          document.title = payload.fields.name;
	        }
	      });
	    }
	  }, {
	    key: "initComplete",
	    value: function initComplete() {
	      this.controller.getStore().commit('callApplication/common', {
	        userCount: this.params.userCount
	      });

	      if (this.controller.getStore().state.callApplication.common.inited) {
	        main_core_events.EventEmitter.emit(im_const.EventType.conference.initCompleted);
	        this.inited = true;
	        this.initPromise.resolve(this);
	      }
	    }
	  }, {
	    key: "ready",
	    value: function ready() {
	      if (this.inited) {
	        var promise$$1 = new BX.Promise();
	        promise$$1.resolve(this);
	        return promise$$1;
	      }

	      return this.initPromise;
	    }
	  }, {
	    key: "restart",
	    value: function restart() {
	      console.trace("restart");
	      return;

	      if (this.currentCall) {
	        this.removeCallEvents();
	        this.currentCall = null;
	      }

	      if (this.callView) {
	        this.callView.releaseLocalMedia();
	        this.callView.close();
	        this.callView.destroy();
	        this.callView = null;
	      }

	      this.initCallInterface();
	      this.initCall();
	      this.controller.getStore().commit('callApplication/returnToPreparation');
	    }
	    /* endregion 01. Initialize */

	    /* region 02. Methods */

	    /* region 01. Call methods */

	  }, {
	    key: "initHardware",
	    value: function initHardware() {
	      var _this11 = this;

	      return new Promise(function (resolve, reject) {
	        BX.Call.Hardware.init().then(function () {
	          if (Object.values(BX.Call.Hardware.microphoneList).length === 0) {
	            _this11.setError(im_const.CallApplicationErrorCode.missingMicrophone);
	          }

	          _this11.callView.unblockButtons(["camera", "microphone"]);

	          _this11.callView.enableMediaSelection();

	          resolve();
	        }).catch(function (error) {
	          if (error === 'NO_WEBRTC' && _this11.isHttps()) {
	            _this11.setError(im_const.CallApplicationErrorCode.unsupportedBrowser);
	          } else if (error === 'NO_WEBRTC' && !_this11.isHttps()) {
	            _this11.setError(im_const.CallApplicationErrorCode.unsafeConnection);
	          }

	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "startCall",
	    value: function startCall(videoEnabled) {
	      var _this12 = this;

	      var provider = BX.Call.Provider.Voximplant;

	      if (im_lib_utils.Utils.device.isMobile()) {
	        this.callView.show();
	      } else {
	        this.callView.setLayout(BX.Call.View.Layout.Grid);
	      }

	      this.callView.setUiState(BX.Call.View.UiState.Calling);
	      this.callView.setLocalUserState(BX.Call.UserState.Connected);

	      if (this.localVideoStream) {
	        if (videoEnabled) {
	          this.callView.setLocalStream(this.localVideoStream, true);
	        } else {
	          this.stopLocalVideoStream();
	        }
	      }

	      if (!videoEnabled) {
	        this.callView.setCameraState(false);
	      }

	      this.controller.getStore().commit('callApplication/startCall');
	      BX.Call.Engine.getInstance().createCall({
	        type: BX.Call.Type.Permanent,
	        entityType: 'chat',
	        entityId: this.getDialogId(),
	        provider: provider,
	        videoEnabled: videoEnabled,
	        enableMicAutoParameters: BX.Call.Hardware.enableMicAutoParameters,
	        joinExisting: true
	      }).then(function (e) {
	        im_lib_logger.Logger.warn('call created', e);
	        _this12.currentCall = e.call; //this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);

	        _this12.currentCall.useHdVideo(true);

	        if (BX.Call.Hardware.defaultMicrophone) {
	          _this12.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
	        }

	        if (BX.Call.Hardware.defaultCamera) {
	          _this12.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
	        }

	        if (!im_lib_utils.Utils.device.isMobile()) {
	          _this12.callView.setLayout(BX.Call.View.Layout.Grid);
	        }

	        _this12.callView.appendUsers(_this12.currentCall.getUsers());

	        BX.Call.Util.getUsers(_this12.currentCall.id, _this12.getCallUsers(true)).then(function (userData) {
	          _this12.callView.updateUserData(userData);
	        });

	        _this12.releasePreCall();

	        _this12.bindCallEvents();

	        if (_this12.callView.isMuted) {
	          _this12.currentCall.setMuted(true);
	        }

	        if (e.isNew) {
	          _this12.currentCall.setVideoEnabled(videoEnabled);

	          _this12.currentCall.inviteUsers();
	        } else {
	          _this12.currentCall.answer({
	            useVideo: videoEnabled
	          });
	        }
	      }).catch(function (e) {
	        im_lib_logger.Logger.warn('creating call error', e);
	      });
	    }
	  }, {
	    key: "endCall",
	    value: function endCall() {
	      if (this.currentCall) {
	        this.removeCallEvents();
	        this.currentCall.hangup();
	      }

	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordStop();
	      }

	      this.callRecordState = BX.Call.View.RecordState.Stopped;

	      if (im_lib_utils.Utils.platform.isBitrixDesktop()) {
	        this.floatingScreenShareWindow.destroy();
	        this.floatingScreenShareWindow = null;
	        window.close();
	      } else {
	        this.callView.releaseLocalMedia();
	        this.callView.close();
	        this.setError(im_const.CallApplicationErrorCode.userLeftCall);
	        this.controller.getStore().commit('callApplication/endCall');
	      }
	    }
	  }, {
	    key: "kickFromCall",
	    value: function kickFromCall() {
	      this.setError(im_const.CallApplicationErrorCode.kickedFromCall);
	      this.pullClient.disconnect();
	      this.endCall();
	    }
	  }, {
	    key: "getCallUsers",
	    value: function getCallUsers(includeSelf) {
	      var result = Object.keys(this.currentCall.getUsers());

	      if (includeSelf) {
	        result.push(this.currentCall.userId);
	      }

	      return result;
	    }
	  }, {
	    key: "setLocalVideoStream",
	    value: function setLocalVideoStream(stream) {
	      this.localVideoStream = stream;
	    }
	  }, {
	    key: "stopLocalVideoStream",
	    value: function stopLocalVideoStream() {
	      if (this.localVideoStream) {
	        this.localVideoStream.getTracks().forEach(function (tr) {
	          return tr.stop();
	        });
	      }

	      this.localVideoStream = null;
	    }
	  }, {
	    key: "setSelectedCamera",
	    value: function setSelectedCamera(cameraId) {
	      if (this.callView) {
	        this.callView.setCameraId(cameraId);
	      }
	    }
	  }, {
	    key: "setSelectedMic",
	    value: function setSelectedMic(micId) {
	      if (this.callView) {
	        this.callView.setMicrophoneId(micId);
	      }
	    }
	  }, {
	    key: "getFeature",
	    value: function getFeature(id) {
	      if (typeof this.featureConfig[id] === 'undefined') {
	        return {
	          id: id,
	          state: CallApplication.FeatureState.Enabled,
	          articleCode: ''
	        };
	      }

	      return this.featureConfig[id];
	    }
	  }, {
	    key: "getFeatureState",
	    value: function getFeatureState(id) {
	      return this.getFeature(id).state;
	    }
	  }, {
	    key: "canRecord",
	    value: function canRecord() {
	      return im_lib_utils.Utils.platform.isBitrixDesktop() && im_lib_utils.Utils.platform.getDesktopVersion() >= 54;
	    }
	  }, {
	    key: "isRecording",
	    value: function isRecording() {
	      return this.canRecord() && this.callRecordState != BX.Call.View.RecordState.Stopped;
	    }
	  }, {
	    key: "showFeatureLimitSlider",
	    value: function showFeatureLimitSlider(id) {
	      var articleCode = this.getFeature(id).articleCode;

	      if (!articleCode || !window.BX.UI.InfoHelper) {
	        console.warn('Limit article not found', id);
	        return false;
	      }

	      window.BX.UI.InfoHelper.show(articleCode);
	      return true;
	    }
	  }, {
	    key: "onCallReplaceCamera",
	    value: function onCallReplaceCamera(event) {
	      var cameraId = event.data.deviceId;
	      BX.Call.Hardware.defaultCamera = cameraId;

	      if (this.currentCall) {
	        this.currentCall.setCameraId(cameraId);
	      } else {
	        this.template.$emit('cameraSelected', cameraId);
	      }
	    }
	  }, {
	    key: "onCallReplaceMicrophone",
	    value: function onCallReplaceMicrophone(event) {
	      var microphoneId = event.data.deviceId;
	      BX.Call.Hardware.defaultMicrophone = microphoneId.deviceId;

	      if (this.callView) {
	        this.callView.setMicrophoneId(microphoneId);
	      }

	      if (this.currentCall) {
	        this.currentCall.setMicrophoneId(microphoneId);
	      } else {
	        this.template.$emit('micSelected', event.data.deviceId);
	      }
	    }
	  }, {
	    key: "onCallReplaceSpeaker",
	    value: function onCallReplaceSpeaker(event) {
	      BX.Call.Hardware.defaultSpeaker = event.data.deviceId;
	    }
	  }, {
	    key: "onCallViewChangeHdVideo",
	    value: function onCallViewChangeHdVideo(event) {
	      BX.Call.Hardware.preferHdQuality = event.data.allowHdVideo;
	    }
	  }, {
	    key: "onCallViewChangeMicAutoParams",
	    value: function onCallViewChangeMicAutoParams(event) {
	      BX.Call.Hardware.enableMicAutoParameters = event.data.allowMicAutoParams;
	    }
	  }, {
	    key: "onCallViewUserNameMouseOver",
	    value: function onCallViewUserNameMouseOver() {
	      if (!this.isExternalUser()) {
	        return false;
	      }

	      this.callView.toggleLocalUserNameEditIcon();
	    }
	  }, {
	    key: "onCallViewUserNameMouseOut",
	    value: function onCallViewUserNameMouseOut() {
	      if (!this.isExternalUser()) {
	        return false;
	      }

	      this.callView.toggleLocalUserNameEditIcon();
	    }
	  }, {
	    key: "onCallViewUserNameClick",
	    value: function onCallViewUserNameClick() {
	      if (!this.isExternalUser()) {
	        return false;
	      }

	      this.callView.toggleLocalUserNameInput();
	    }
	  }, {
	    key: "onCallViewUserChangeNameClick",
	    value: function onCallViewUserChangeNameClick(event) {
	      if (!this.isExternalUser()) {
	        return false;
	      }

	      if (im_lib_utils.Utils.device.isMobile()) {
	        this.renameGuestMobile(event);
	      } else {
	        this.renameGuest(event);
	      }
	    }
	  }, {
	    key: "renameGuest",
	    value: function renameGuest(event) {
	      if (event.data.needToUpdate) {
	        this.callView.toggleLocalUserNameLoader();
	        this.setUserName(event.data.newName).then(function () {
	          im_lib_logger.Logger.log('setting name to', event.data.newName);
	        }).catch(function (error) {
	          im_lib_logger.Logger.log('error setting name', error);
	        });
	      } else {
	        this.callView.toggleLocalUserNameInput();
	      }
	    }
	  }, {
	    key: "renameGuestMobile",
	    value: function renameGuestMobile(event) {
	      var _this13 = this;

	      if (event.data.needToUpdate) {
	        this.callView.toggleRenameSliderInputLoader();
	        this.setUserName(event.data.newName).then(function () {
	          im_lib_logger.Logger.log('setting name to', event.data.newName);

	          if (_this13.callView.renameSlider) {
	            _this13.callView.renameSlider.close();
	          }
	        }).catch(function (error) {
	          im_lib_logger.Logger.log('error setting name', error);
	        });
	      } else if (!event.data.needToUpdate && this.callView.renameSlider) {
	        this.callView.renameSlider.close();
	      }
	    }
	  }, {
	    key: "onCallButtonClick",
	    value: function onCallButtonClick(event) {
	      var buttonName = event.data.buttonName;
	      im_lib_logger.Logger.warn('Button clicked!', buttonName);
	      var handlers = {
	        hangup: this.onCallViewHangupButtonClick.bind(this),
	        close: this.onCallViewCloseButtonClick.bind(this),
	        //inviteUser: this.onCallViewInviteUserButtonClick.bind(this),
	        toggleMute: this.onCallViewToggleMuteButtonClick.bind(this),
	        toggleScreenSharing: this.onCallViewToggleScreenSharingButtonClick.bind(this),
	        record: this.onCallViewRecordButtonClick.bind(this),
	        toggleVideo: this.onCallViewToggleVideoButtonClick.bind(this),
	        showChat: this.onCallViewShowChatButtonClick.bind(this),
	        share: this.onCallViewShareButtonClick.bind(this),
	        fullscreen: this.onCallViewFullScreenButtonClick.bind(this),
	        floorRequest: this.onCallViewFloorRequestButtonClick.bind(this)
	      };

	      if (handlers[buttonName]) {
	        handlers[buttonName](event);
	      } else {
	        im_lib_logger.Logger.error('Button handler not found!', buttonName);
	      }
	    }
	  }, {
	    key: "onCallViewHangupButtonClick",
	    value: function onCallViewHangupButtonClick(e) {
	      this.endCall();
	    }
	  }, {
	    key: "onCallViewCloseButtonClick",
	    value: function onCallViewCloseButtonClick(e) {
	      this.endCall();
	    }
	  }, {
	    key: "onCallViewToggleMuteButtonClick",
	    value: function onCallViewToggleMuteButtonClick(event) {
	      if (this.currentCall) {
	        this.currentCall.setMuted(event.data.muted);
	      } else {
	        this.template.$emit('setMicState', !event.data.muted);
	      }

	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordMute(event.data.muted);
	      }

	      this.callView.setMuted(event.data.muted);
	    }
	  }, {
	    key: "onCallViewToggleScreenSharingButtonClick",
	    value: function onCallViewToggleScreenSharingButtonClick() {
	      if (this.getFeatureState('screenSharing') === CallApplication.FeatureState.Limited) {
	        this.showFeatureLimitSlider('screenSharing');
	        return;
	      }

	      if (this.getFeatureState('screenSharing') === CallApplication.FeatureState.Disabled) {
	        return;
	      }

	      if (this.currentCall.isScreenSharingStarted()) {
	        this.currentCall.stopScreenSharing();

	        if (this.isRecording()) {
	          BXDesktopSystem.CallRecordStopSharing();
	        }

	        if (this.floatingScreenShareWindow) {
	          this.floatingScreenShareWindow.close();
	        }
	      } else {
	        this.restClient.callMethod("im.call.onShareScreen", {
	          callId: this.currentCall.id
	        });
	        this.currentCall.startScreenSharing();
	      }
	    }
	  }, {
	    key: "onCallViewRecordButtonClick",
	    value: function onCallViewRecordButtonClick(event) {
	      if (event.data.recordState === BX.Call.View.RecordState.Started) {
	        if (this.getFeatureState('record') === CallApplication.FeatureState.Limited) {
	          this.showFeatureLimitSlider('record');
	          return;
	        }

	        if (this.getFeatureState('record') === CallApplication.FeatureState.Disabled) {
	          return;
	        }

	        if (this.canRecord()) {
	          this.callView.setButtonActive('record', true);
	        } else {
	          if (window.BX.Helper) {
	            window.BX.Helper.show("redirect=detail&code=12398134");
	          }

	          return;
	        }
	      } else if (event.data.recordState === BX.Call.View.RecordState.Paused) {
	        if (this.canRecord()) {
	          BXDesktopSystem.CallRecordPause(true);
	        }
	      } else if (event.data.recordState === BX.Call.View.RecordState.Resumed) {
	        if (this.canRecord()) {
	          BXDesktopSystem.CallRecordPause(false);
	        }
	      } else if (event.data.recordState === BX.Call.View.RecordState.Stopped) {
	        this.callView.setButtonActive('record', false);
	      }

	      this.currentCall.sendRecordState({
	        action: event.data.recordState,
	        date: new Date()
	      });
	      this.callRecordState = event.data.recordState;
	    }
	  }, {
	    key: "onCallViewToggleVideoButtonClick",
	    value: function onCallViewToggleVideoButtonClick(event) {
	      if (this.currentCall) {
	        if (!BX.Call.Hardware.initialized) {
	          return;
	        }

	        if (event.data.video && Object.values(BX.Call.Hardware.cameraList).length === 0) {
	          return;
	        }

	        if (!event.data.video) {
	          this.callView.releaseLocalMedia();
	        }

	        this.currentCall.setVideoEnabled(event.data.video);
	      } else {
	        this.template.$emit('setCameraState', event.data.video);
	      }
	    }
	  }, {
	    key: "onCallViewShareButtonClick",
	    value: function onCallViewShareButtonClick() {
	      var notifyWidth = 400;

	      if (im_lib_utils.Utils.device.isMobile() && document.body.clientWidth < 400) {
	        notifyWidth = document.body.clientWidth - 40;
	      }

	      BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('BX_IM_VIDEOCONF_LINK_COPY_DONE'),
	        autoHideDelay: 4000,
	        width: notifyWidth
	      });
	      im_lib_clipboard.Clipboard.copy(this.getDialogData().public.link);
	    }
	  }, {
	    key: "onCallViewFullScreenButtonClick",
	    value: function onCallViewFullScreenButtonClick() {
	      this.toggleFullScreen();
	    }
	  }, {
	    key: "onFloatingScreenShareBackToCallClick",
	    value: function onFloatingScreenShareBackToCallClick() {
	      BXDesktopWindow.ExecuteCommand('show.active');

	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.hide();
	      }
	    }
	  }, {
	    key: "onFloatingScreenShareStopClick",
	    value: function onFloatingScreenShareStopClick() {
	      BXDesktopWindow.ExecuteCommand('show.active');
	      this.onCallViewToggleScreenSharingButtonClick();
	    }
	  }, {
	    key: "onWindowFocus",
	    value: function onWindowFocus() {
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.hide();
	      }
	    }
	  }, {
	    key: "onWindowBlur",
	    value: function onWindowBlur() {
	      if (this.floatingScreenShareWindow && this.currentCall && this.currentCall.isScreenSharingStarted()) {
	        this.floatingScreenShareWindow.show();
	      }
	    }
	  }, {
	    key: "isFullScreen",
	    value: function isFullScreen() {
	      if ("webkitFullscreenElement" in document) {
	        return !!document.webkitFullscreenElement;
	      } else if ("fullscreenElement" in document) {
	        return !!document.fullscreenElement;
	      }

	      return false;
	    }
	  }, {
	    key: "toggleFullScreen",
	    value: function toggleFullScreen() {
	      if (this.isFullScreen()) {
	        this.exitFullScreen();
	      } else {
	        this.enterFullScreen();
	      }
	    }
	  }, {
	    key: "enterFullScreen",
	    value: function enterFullScreen() {
	      if (BX.browser.IsChrome() || BX.browser.IsSafari()) {
	        document.body.webkitRequestFullScreen();
	      } else if (BX.browser.IsFirefox()) {
	        document.body.requestFullscreen();
	      }
	    }
	  }, {
	    key: "exitFullScreen",
	    value: function exitFullScreen() {
	      if (document.cancelFullScreen) {
	        document.cancelFullScreen();
	      } else if (document.mozCancelFullScreen) {
	        document.mozCancelFullScreen();
	      } else if (document.webkitCancelFullScreen) {
	        document.webkitCancelFullScreen();
	      } else if (document.document.exitFullscreen()) {
	        document.exitFullscreen();
	      }
	    }
	  }, {
	    key: "onCallViewShowChatButtonClick",
	    value: function onCallViewShowChatButtonClick() {
	      this.toggleChat();
	    }
	  }, {
	    key: "onCallViewFloorRequestButtonClick",
	    value: function onCallViewFloorRequestButtonClick() {
	      var _this14 = this;

	      var floorState = this.callView.getUserFloorRequestState(BX.CallEngine.getCurrentUserId());
	      var talkingState = this.callView.getUserTalking(BX.CallEngine.getCurrentUserId());
	      this.callView.setUserFloorRequestState(BX.CallEngine.getCurrentUserId(), !floorState);

	      if (this.currentCall) {
	        this.currentCall.requestFloor(!floorState);
	      }

	      clearTimeout(this.callViewFloorRequestTimeout);

	      if (talkingState && !floorState) {
	        this.callViewFloorRequestTimeout = setTimeout(function () {
	          if (_this14.currentCall) {
	            _this14.currentCall.requestFloor(false);
	          }
	        }, 1500);
	      }
	    }
	  }, {
	    key: "bindCallEvents",
	    value: function bindCallEvents() {
	      this.currentCall.addEventListener(BX.Call.Event.onUserInvited, this.onCallUserInvitedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onDestroy, this.onCallDestroyHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onStreamReceived, this.onCallUserStreamReceivedHandler); //this.currentCall.addEventListener(BX.Call.Event.onStreamRemoved, this.onCallUserStreamRemoved.bind(this));

	      this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler); //this.currentCall.addEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
	      //this.currentCall.addEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);

	      this.currentCall.addEventListener(BX.Call.Event.onJoin, this._onCallJoinHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onLeave, this.onCallLeaveHandler);
	    }
	  }, {
	    key: "removeCallEvents",
	    value: function removeCallEvents() {
	      this.currentCall.removeEventListener(BX.Call.Event.onUserInvited, this.onCallUserInvitedHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onDestroy, this.onCallDestroyHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onStreamReceived, this.onCallUserStreamReceivedHandler); //this.currentCall.removeEventListener(BX.Call.Event.onStreamRemoved, this.onCallUserStreamRemoved.bind(this));

	      this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler); //this.currentCall.removeEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
	      //this.currentCall.removeEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);

	      this.currentCall.removeEventListener(BX.Call.Event.onLeave, this.onCallLeaveHandler);
	    }
	  }, {
	    key: "onCallUserInvited",
	    value: function onCallUserInvited(e) {
	      var _this15 = this;

	      this.callView.addUser(e.userId);
	      BX.Call.Util.getUsers(this.currentCall.id, [e.userId]).then(function (userData) {
	        _this15.callView.updateUserData(userData);
	      });
	    }
	  }, {
	    key: "onCallUserStateChanged",
	    value: function onCallUserStateChanged(e) {
	      this.callView.setUserState(e.userId, e.state);
	    }
	  }, {
	    key: "onCallUserMicrophoneState",
	    value: function onCallUserMicrophoneState(e) {
	      this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
	    }
	  }, {
	    key: "onCallLocalMediaReceived",
	    value: function onCallLocalMediaReceived(e) {
	      //this.template.$emit('callLocalMediaReceived');
	      this.stopLocalVideoStream();
	      this.callView.setLocalStream(e.stream, e.tag == "main");
	      this.callView.setButtonActive("screen", e.tag == "screen");

	      if (e.tag == "screen") {
	        this.callView.blockSwitchCamera();
	        this.callView.updateButtons();
	      } else {
	        if (!this.currentCall.callFromMobile) {
	          this.callView.unblockSwitchCamera();
	          this.callView.updateButtons();
	        }
	      }
	    }
	  }, {
	    key: "onCallUserStreamReceived",
	    value: function onCallUserStreamReceived(e) {
	      if (this.callView) {
	        if ("stream" in e) {
	          this.callView.setStream(e.userId, e.stream);
	        }

	        if ("mediaRenderer" in e && e.mediaRenderer.kind === "audio") {
	          this.callView.setStream(e.userId, e.mediaRenderer.stream);
	        }

	        if ("mediaRenderer" in e && e.mediaRenderer.kind === "video") {
	          this.callView.setVideoRenderer(e.userId, e.mediaRenderer);
	        }
	      }
	    }
	  }, {
	    key: "onCallUserVoiceStarted",
	    value: function onCallUserVoiceStarted(e) {
	      this.callView.setUserTalking(e.userId, true);
	      this.callView.setUserFloorRequestState(e.userId, false);
	    }
	  }, {
	    key: "onCallUserVoiceStopped",
	    value: function onCallUserVoiceStopped(e) {
	      this.callView.setUserTalking(e.userId, false);
	    }
	  }, {
	    key: "onCallUserScreenState",
	    value: function onCallUserScreenState(e) {
	      if (this.callView) {
	        this.callView.setUserScreenState(e.userId, e.screenState);
	      }
	    }
	  }, {
	    key: "onCallUserRecordState",
	    value: function onCallUserRecordState(event) {
	      this.callRecordState = event.recordState.state;
	      this.callView.setRecordState(event.recordState);

	      if (!this.canRecord() || event.userId != this.controller.getUserId()) {
	        return true;
	      }

	      if (event.recordState.state === BX.Call.View.RecordState.Started && event.recordState.userId == this.controller.getUserId()) {
	        var windowId = window.bxdWindowId || window.document.title;
	        var fileName = BX.message('IM_CALL_RECORD_NAME');
	        var dialogId = this.currentCall.associatedEntity.id;
	        var dialogName = this.currentCall.associatedEntity.name;
	        var callId = this.currentCall.id;
	        var callDate = BX.Main.Date.format(this.params.formatRecordDate || 'd.m.Y');

	        if (fileName) {
	          fileName = fileName.replace('#CHAT_TITLE#', dialogName).replace('#CALL_ID#', callId).replace('#DATE#', callDate);
	        } else {
	          fileName = "call_record_" + this.currentCall.id;
	        }

	        BX.CallEngine.getRestClient().callMethod("im.call.onStartRecord", {
	          callId: this.currentCall.id
	        });
	        BXDesktopSystem.CallRecordStart({
	          windowId: windowId,
	          fileName: fileName,
	          callId: callId,
	          callDate: callDate,
	          dialogId: dialogId,
	          dialogName: dialogName,
	          muted: this.currentCall.isMuted(),
	          cropTop: 72,
	          cropBottom: 73
	        });
	      } else if (event.recordState.state === BX.Call.View.RecordState.Stopped) {
	        BXDesktopSystem.CallRecordStop();
	      }

	      return true;
	    }
	  }, {
	    key: "onCallUserFloorRequest",
	    value: function onCallUserFloorRequest(e) {
	      this.callView.setUserFloorRequestState(e.userId, e.requestActive);
	    }
	  }, {
	    key: "onCallJoin",
	    value: function onCallJoin(e) {
	      if (!e.local) {
	        return;
	      }

	      this.callView.unblockButtons(['camera', 'floorRequest', 'screen', 'record']);
	      this.callView.setUiState(BX.Call.View.UiState.Connected);
	    }
	  }, {
	    key: "onCallLeave",
	    value: function onCallLeave(e) {
	      if (!e.local) {
	        return;
	      }

	      this.endCall();
	    }
	  }, {
	    key: "onCallDestroy",
	    value: function onCallDestroy(e) {
	      this.currentCall = null;

	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.close;
	      }

	      this.restart();
	    }
	  }, {
	    key: "onCheckDevicesSave",
	    value: function onCheckDevicesSave(changedValues) {
	      if (changedValues['camera']) {
	        BX.Call.Hardware.defaultCamera = changedValues['camera'];
	      }

	      if (changedValues['microphone']) {
	        BX.Call.Hardware.defaultMicrophone = changedValues['microphone'];
	      }

	      if (changedValues['audioOutput']) {
	        BX.Call.Hardware.defaultSpeaker = changedValues['audioOutput'];
	      }

	      if (changedValues['preferHDQuality']) {
	        BX.Call.Hardware.preferHdQuality = changedValues['preferHDQuality'];
	      }

	      if (changedValues['enableMicAutoParameters']) {
	        BX.Call.Hardware.enableMicAutoParameters = changedValues['enableMicAutoParameters'];
	      }
	    }
	  }, {
	    key: "setCameraState",
	    value: function setCameraState(state) {
	      this.callView.setCameraState(state);
	    }
	    /* endregion 01. Call methods */

	    /* region 02. Component methods */

	    /* region 01. General actions */

	  }, {
	    key: "isChatShow",
	    value: function isChatShow() {
	      return this.controller.getStore().state.callApplication.common.showChat;
	    }
	  }, {
	    key: "toggleChat",
	    value: function toggleChat() {
	      var newState = !this.isChatShow();
	      this.controller.getStore().state.callApplication.common.showChat = newState;
	      this.callView.setButtonActive('chat', newState);
	    }
	  }, {
	    key: "setDialogInited",
	    value: function setDialogInited() {
	      this.dialogInited = true;
	      var dialogData = this.getDialogData();
	      document.title = dialogData.name;
	    }
	  }, {
	    key: "changeVideoconfUrl",
	    value: function changeVideoconfUrl(newUrl) {
	      window.history.pushState("", "", newUrl);
	    }
	  }, {
	    key: "sendNewMessageNotify",
	    value: function sendNewMessageNotify(text) {
	      var _this16 = this;

	      if (im_lib_utils.Utils.device.isMobile()) {
	        return true;
	      }

	      var MAX_LENGTH = 40;
	      var AUTO_HIDE_TIME = 4000;
	      text = text.replace(/<br \/>/gi, ' ');
	      text = text.replace(/\[USER=([0-9]+)](.*?)\[\/USER]/ig, function (whole, userId, text) {
	        return text;
	      });
	      text = text.replace(/\[CHAT=(imol\|)?([0-9]+)](.*?)\[\/CHAT]/ig, function (whole, imol, chatId, text) {
	        return text;
	      });
	      text = text.replace(/\[PCH=([0-9]+)](.*?)\[\/PCH]/ig, function (whole, historyId, text) {
	        return text;
	      });
	      text = text.replace(/\[SEND(?:=(.+?))?](.+?)?\[\/SEND]/ig, function (whole, command, text) {
	        return text ? text : command;
	      });
	      text = text.replace(/\[PUT(?:=(.+?))?](.+?)?\[\/PUT]/ig, function (whole, command, text) {
	        return text ? text : command;
	      });
	      text = text.replace(/\[CALL(?:=(.+?))?](.+?)?\[\/CALL]/ig, function (whole, command, text) {
	        return text ? text : command;
	      });
	      text = text.replace(/\[ATTACH=([0-9]+)]/ig, function (whole, historyId, text) {
	        return '';
	      });

	      if (text.length > MAX_LENGTH) {
	        text = text.substring(0, MAX_LENGTH - 1) + '...';
	      }

	      var notifyNode = BX.create("div", {
	        props: {
	          className: 'bx-im-application-call-notify-new-message'
	        },
	        html: text
	      });
	      var notify = BX.UI.Notification.Center.notify({
	        content: notifyNode,
	        autoHideDelay: AUTO_HIDE_TIME
	      });
	      notifyNode.addEventListener('click', function (event) {
	        notify.close();

	        _this16.toggleChat();
	      });
	      return true;
	    }
	  }, {
	    key: "insertText",
	    value: function insertText() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.template.$emit(im_const.EventType.textarea.insertText, params);
	    }
	    /* endregion 01. General actions */

	    /* region 02. Store actions */

	  }, {
	    key: "setError",
	    value: function setError(errorCode) {
	      this.controller.getStore().commit('callApplication/setError', {
	        errorCode: errorCode
	      });
	    }
	  }, {
	    key: "toggleSmiles",
	    value: function toggleSmiles() {
	      this.controller.getStore().commit('callApplication/toggleSmiles');
	    }
	  }, {
	    key: "setJoinType",
	    value: function setJoinType(joinWithVideo) {
	      this.controller.getStore().commit('callApplication/setJoinType', {
	        joinWithVideo: joinWithVideo
	      });
	    }
	  }, {
	    key: "setConferenceStatus",
	    value: function setConferenceStatus(conferenceStarted) {
	      this.controller.getStore().commit('callApplication/setConferenceStatus', {
	        conferenceStarted: conferenceStarted
	      });
	    }
	  }, {
	    key: "setConferenceStartDate",
	    value: function setConferenceStartDate(conferenceStartDate) {
	      this.controller.getStore().commit('callApplication/setConferenceStartDate', {
	        conferenceStartDate: conferenceStartDate
	      });
	    }
	  }, {
	    key: "setUserReadyToJoin",
	    value: function setUserReadyToJoin() {
	      this.controller.getStore().commit('callApplication/setUserReadyToJoin');
	    }
	    /* endregion 02. Store actions */

	    /* region 03. Rest actions */

	  }, {
	    key: "setUserName",
	    value: function setUserName(name) {
	      var _this17 = this;

	      return new Promise(function (resolve, reject) {
	        _this17.restClient.callMethod('im.call.user.update', {
	          name: name,
	          chat_id: _this17.getChatId()
	        }).then(function () {
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "checkPassword",
	    value: function checkPassword(password) {
	      var _this18 = this;

	      return new Promise(function (resolve, reject) {
	        _this18.restClient.callMethod('im.videoconf.password.check', {
	          password: password,
	          alias: _this18.params.alias
	        }).then(function (result) {
	          if (result.data() === true) {
	            _this18.restClient.setPassword(password);

	            _this18.controller.getStore().commit('callApplication/common', {
	              passChecked: true
	            });

	            _this18.initUserComplete();

	            resolve();
	          } else {
	            reject();
	          }
	        });
	      });
	    }
	    /* endregion 03. Rest actions */

	    /* region 04. Messages and files */

	  }, {
	    key: "addMessage",
	    value: function addMessage() {
	      var _this19 = this;

	      var text = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var file = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!text && !file) {
	        return false;
	      }

	      if (!this.controller.application.isUnreadMessagesLoaded()) {
	        this.sendMessage({
	          id: 0,
	          text: text,
	          file: file
	        });
	        this.processSendMessages();
	        return true;
	      }

	      var params = {};

	      if (file) {
	        params.FILE_ID = [file.id];
	      }

	      this.controller.getStore().commit('application/increaseDialogExtraCount');
	      this.controller.getStore().dispatch('messages/add', {
	        chatId: this.getChatId(),
	        authorId: this.controller.getUserId(),
	        text: text,
	        params: params,
	        sending: !file
	      }).then(function (messageId) {
	        _this19.messagesQueue.push({
	          id: messageId,
	          text: text,
	          file: file,
	          sending: false
	        });

	        _this19.processSendMessages();
	      });
	      return true;
	    }
	  }, {
	    key: "processSendMessages",
	    value: function processSendMessages() {
	      var _this20 = this;

	      if (!this.getDiskFolderId()) {
	        this.requestDiskFolderId().then(function () {
	          _this20.processSendMessages();
	        }).catch(function () {
	          im_lib_logger.Logger.warn('uploadFile', 'Error get disk folder id');
	          return false;
	        });
	        return false;
	      }

	      this.messagesQueue.filter(function (element) {
	        return !element.sending;
	      }).forEach(function (element) {
	        element.sending = true;

	        if (element.file) {
	          _this20.sendMessageWithFile(element);
	        } else {
	          _this20.sendMessage(element);
	        }
	      });
	      return true;
	    }
	  }, {
	    key: "sendMessage",
	    value: function sendMessage(message) {
	      var _this21 = this;

	      this.controller.application.stopWriting(); //let quiteId = this.controller.getStore().getters['dialogues/getQuoteId'](this.getDialogId());
	      //if (quiteId)
	      //{
	      //	let quoteMessage = this.controller.getStore().getters['messages/getMessage'](this.getChatId(), quiteId);
	      //	if (quoteMessage)
	      //	{
	      //		let user = this.controller.getStore().getters['users/get'](quoteMessage.authorId);
	      //
	      //		let newMessage = [];
	      //		newMessage.push("------------------------------------------------------");
	      //		newMessage.push((user.name ? user.name : this.getLocalize('BX_LIVECHAT_SYSTEM_MESSAGE')));
	      //		newMessage.push(quoteMessage.text);
	      //		newMessage.push('------------------------------------------------------');
	      //		newMessage.push(message.text);
	      //		message.text = newMessage.join("\n");
	      //
	      //		this.quoteMessageClear();
	      //	}
	      //}

	      message.chatId = this.getChatId();
	      this.controller.restClient.callMethod(im_const.RestMethod.imMessageAdd, {
	        'TEMPLATE_ID': message.id,
	        'CHAT_ID': message.chatId,
	        'MESSAGE': message.text
	      }, null, null).then(function (response) {
	        _this21.controller.getStore().dispatch('messages/update', {
	          id: message.id,
	          chatId: message.chatId,
	          fields: {
	            id: response.data(),
	            sending: false,
	            error: false
	          }
	        }).then(function () {
	          _this21.controller.getStore().dispatch('messages/actionFinish', {
	            id: response.data(),
	            chatId: message.chatId
	          });
	        }); //this.controller.executeRestAnswer(ImRestMethodHandler.imMessageAdd, response, message);

	      }).catch(function (error) {//this.controller.executeRestAnswer(ImRestMethodHandler.imMessageAdd, error, message);
	      });
	      return true;
	    }
	  }, {
	    key: "sendMessageWithFile",
	    value: function sendMessageWithFile(message) {
	      this.controller.application.stopWriting();
	      var diskFolderId = this.getDiskFolderId();
	      message.chatId = this.getChatId();
	      this.uploader.senderOptions.customHeaders['Call-Auth-Id'] = this.getUserHash();
	      this.uploader.senderOptions.customHeaders['Call-Chat-Id'] = message.chatId;
	      this.uploader.addTask({
	        taskId: message.file.id,
	        fileData: message.file.source.file,
	        fileName: message.file.source.file.name,
	        generateUniqueName: true,
	        diskFolderId: diskFolderId,
	        previewBlob: message.file.previewBlob
	      });
	    } // uploadFile(event)
	    // {
	    // 	if (!event)
	    // 	{
	    // 		return false;
	    // 	}
	    //
	    // 	this.uploader.addFilesFromEvent(event);
	    // }
	    // fileError(chatId, fileId, messageId = 0)
	    // {
	    // 	this.controller.getStore().dispatch('files/update', {
	    // 		chatId: chatId,
	    // 		id: fileId,
	    // 		fields: {
	    // 			status: FileStatus.error,
	    // 			progress: 0
	    // 		}
	    // 	});
	    // 	if (messageId)
	    // 	{
	    // 		this.controller.getStore().dispatch('messages/actionError', {
	    // 			chatId: chatId,
	    // 			id: messageId,
	    // 			retry: false,
	    // 		});
	    // 	}
	    // }

	  }, {
	    key: "requestDiskFolderId",
	    value: function requestDiskFolderId() {
	      var _this22 = this;

	      if (this.requestDiskFolderPromise) {
	        return this.requestDiskFolderPromise;
	      }

	      this.requestDiskFolderPromise = new Promise(function (resolve, reject) {
	        if (_this22.flagRequestDiskFolderIdSended || _this22.getDiskFolderId()) {
	          _this22.flagRequestDiskFolderIdSended = false;
	          resolve();
	          return true;
	        }

	        _this22.flagRequestDiskFolderIdSended = true;

	        _this22.controller.restClient.callMethod(im_const.RestMethod.imDiskFolderGet, {
	          chat_id: _this22.controller.application.getChatId()
	        }).then(function (response) {
	          _this22.controller.executeRestAnswer(im_const.RestMethodHandler.imDiskFolderGet, response);

	          _this22.flagRequestDiskFolderIdSended = false;
	          resolve();
	        }).catch(function (error) {
	          _this22.flagRequestDiskFolderIdSended = false;

	          _this22.controller.executeRestAnswer(im_const.RestMethodHandler.imDiskFolderGet, error);

	          reject();
	        });
	      });
	      return this.requestDiskFolderPromise;
	    } // fileCommit(params, message)
	    // {
	    // 	this.controller.restClient.callMethod(ImRestMethod.imDiskFileCommit, {
	    // 		chat_id: params.chatId,
	    // 		upload_id: params.uploadId,
	    // 		message: params.messageText,
	    // 		template_id: params.messageId,
	    // 		file_template_id: params.fileId,
	    // 	}, null, null, ).then(response => {
	    // 		this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, response, message);
	    // 	}).catch(error => {
	    // 		this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, error, message);
	    // 	});
	    //
	    // 	return true;
	    // }

	    /* endregion 04. Messages and files */

	    /* endregion 02. Component methods */

	    /* endregion 02. Methods */

	    /* region 03. Utils */

	  }, {
	    key: "addLocalize",
	    value: function addLocalize(phrases) {
	      return this.controller.addLocalize(phrases);
	    }
	  }, {
	    key: "getLocalize",
	    value: function getLocalize(name) {
	      return this.controller.getLocalize(name);
	    }
	  }, {
	    key: "isExternalUser",
	    value: function isExternalUser() {
	      return !!this.getUserHash();
	    }
	  }, {
	    key: "getChatId",
	    value: function getChatId() {
	      return parseInt(this.params.chatId);
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.params.dialogId;
	    }
	  }, {
	    key: "getDialogData",
	    value: function getDialogData() {
	      if (!this.dialogInited) {
	        return false;
	      }

	      return this.controller.getStore().getters['dialogues/get'](this.getDialogId());
	    }
	  }, {
	    key: "getHost",
	    value: function getHost() {
	      return location.origin || '';
	    }
	  }, {
	    key: "getStartupErrorCode",
	    value: function getStartupErrorCode() {
	      return this.params.startupErrorCode ? this.params.startupErrorCode : '';
	    }
	  }, {
	    key: "getDiskFolderId",
	    value: function getDiskFolderId() {
	      return this.controller.getStore().state.application.dialog.diskFolderId;
	    }
	  }, {
	    key: "isHttps",
	    value: function isHttps() {
	      return location.protocol === 'https:';
	    }
	  }, {
	    key: "getUserHash",
	    value: function getUserHash() {
	      return this.controller.getStore().state.callApplication.user.hash;
	    }
	  }, {
	    key: "getUserHashCookie",
	    value: function getUserHashCookie() {
	      var userHash = '';
	      var cookie = im_lib_cookie.Cookie.get(null, 'BITRIX_CALL_HASH');

	      if (typeof cookie === 'string' && cookie.match(/^[a-f0-9]{32}$/)) {
	        userHash = cookie;
	      }

	      return userHash;
	    }
	  }, {
	    key: "getAlias",
	    value: function getAlias() {
	      return this.params.alias ? this.params.alias : '';
	    }
	  }, {
	    key: "switchToSessAuth",
	    value: function switchToSessAuth() {
	      this.restClient.restClient.queryParams = undefined;
	      return true;
	    }
	    /* endregion 03. Utils */

	  }]);
	  return CallApplication;
	}();

	CallApplication.FeatureState = {
	  Enabled: 'enabled',
	  Disabled: 'disabled',
	  Limited: 'limited'
	};

	exports.CallApplication = CallApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX,BX,BX.Messenger.Application,BX.Messenger,BX.Messenger.Model,BX.Messenger,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Const,BX,BX.UI,BX,BX,BX,BX,BX,BX.Event,BX,BX,BX,BX.Messenger.Provider.Pull,BX,BX.Messenger.Lib));
//# sourceMappingURL=call.bundle.js.map
