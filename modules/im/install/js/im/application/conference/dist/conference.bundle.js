this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_debug,im_application_launch,im_component_conference_conferencePublic,Call,im_model,im_controller,im_lib_cookie,im_lib_localstorage,im_lib_logger,im_lib_clipboard,im_lib_desktop,im_const,ui_notificationManager,ui_notification,ui_buttons,ui_progressround,ui_viewer,ui_vue,ui_vue_vuex,main_core,promise,main_date,main_core_events,pull_client,im_provider_pull,rest_client,im_lib_utils) {
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
	      if (babelHelpers["typeof"](this.queryParams) !== 'object') {
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
	      if (babelHelpers["typeof"](this.queryParams) !== 'object') {
	        this.queryParams = {};
	      }
	      this.queryParams.call_chat_id = chatId;
	    }
	  }, {
	    key: "setConfId",
	    value: function setConfId(alias) {
	      if (babelHelpers["typeof"](this.queryParams) !== 'object') {
	        this.queryParams = {};
	      }
	      this.queryParams.videoconf_id = alias;
	    }
	  }, {
	    key: "setPassword",
	    value: function setPassword(password) {
	      if (babelHelpers["typeof"](this.queryParams) !== 'object') {
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
	      var promise$$1 = new BX.Promise();

	      // TODO: Callbacks methods will not work!
	      this.restClient.callMethod(method, params, null, sendCallback, logTag).then(function (result) {
	        _this.queryAuthRestore = false;
	        promise$$1.fulfill(result);
	      })["catch"](function (result) {
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
	            })["catch"](function (result) {
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
	 * Bitrix Im
	 * Conference application
	 *
	 * @package bitrix
	 * @subpackage mobile
	 * @copyright 2001-2021 Bitrix
	 */
	var ConferenceApplication = /*#__PURE__*/function () {
	  /* region 01. Initialize */
	  function ConferenceApplication() {
	    var _this = this;
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ConferenceApplication);
	    this.inited = false;
	    this.hardwareInited = false;
	    this.dialogInited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    this.params.userId = this.params.userId ? parseInt(this.params.userId) : 0;
	    this.params.siteId = this.params.siteId || '';
	    this.params.chatId = this.params.chatId ? parseInt(this.params.chatId) : 0;
	    this.params.dialogId = this.params.chatId ? 'chat' + this.params.chatId.toString() : '0';
	    this.params.passwordRequired = !!this.params.passwordRequired;
	    this.params.isBroadcast = !!this.params.isBroadcast;
	    BX.Messenger.Lib.Logger.setConfig(params.loggerConfig);
	    this.messagesQueue = [];
	    this.template = null;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.event = new ui_vue.VueVendorV2();
	    this.callContainer = null;
	    // this.callView = null;
	    this.preCall = null;
	    this.currentCall = null;
	    this.videoStrategy = null;
	    this.callDetails = {};
	    this.showFeedback = true;
	    this.featureConfig = {};
	    (params.featureConfig || []).forEach(function (limit) {
	      _this.featureConfig[limit.id] = limit;
	    });
	    this.localVideoStream = null;
	    this.conferencePageTagInterval = null;
	    this.onCallUserInvitedHandler = this.onCallUserInvited.bind(this);
	    this.onCallUserStateChangedHandler = this.onCallUserStateChanged.bind(this);
	    this.onCallUserMicrophoneStateHandler = this.onCallUserMicrophoneState.bind(this);
	    this.onCallUserCameraStateHandler = this.onCallUserCameraState.bind(this);
	    this.onCallUserVideoPausedHandler = this.onCallUserVideoPaused.bind(this);
	    this.onCallLocalMediaReceivedHandler = BX.debounce(this.onCallLocalMediaReceived.bind(this), 1000);
	    this.onCallRemoteMediaReceivedHandler = this.onCallRemoteMediaReceived.bind(this);
	    this.onCallRemoteMediaStoppedHandler = this.onCallRemoteMediaStopped.bind(this);
	    this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
	    this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
	    this.onCallUserScreenStateHandler = this.onCallUserScreenState.bind(this);
	    this.onCallUserRecordStateHandler = this.onCallUserRecordState.bind(this);
	    this.onCallUserFloorRequestHandler = this.onCallUserFloorRequest.bind(this);
	    this.onMicrophoneLevelHandler = this.onMicrophoneLevel.bind(this);
	    this._onCallJoinHandler = this.onCallJoin.bind(this);
	    this.onCallLeaveHandler = this.onCallLeave.bind(this);
	    this.onCallDestroyHandler = this.onCallDestroy.bind(this);
	    this.onInputFocusHandler = this.onInputFocus.bind(this);
	    this.onInputBlurHandler = this.onInputBlur.bind(this);
	    this.onPreCallDestroyHandler = this.onPreCallDestroy.bind(this);
	    this.onPreCallUserStateChangedHandler = this.onPreCallUserStateChanged.bind(this);
	    this.waitingForCallStatus = false;
	    this.waitingForCallStatusTimeout = null;
	    this.callEventReceived = false;
	    this.callRecordState = Call.View.RecordState.Stopped;
	    this.desktop = null;
	    this.floatingScreenShareWindow = null;
	    this.webScreenSharePopup = null;
	    this.mutePopup = null;
	    this.allowMutePopup = true;
	    this.initDesktopEvents().then(function () {
	      return _this.initRestClient();
	    }).then(function () {
	      return _this.subscribePreCallChanges();
	    }).then(function () {
	      return _this.subscribeNotifierEvents();
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
	    }).then(function () {
	      return _this.initHardware();
	    }).then(function () {
	      return _this.initUserComplete();
	    })["catch"](function (error) {
	      console.error('Init error', error);
	    });
	  }
	  /* region 01. Initialize methods */
	  babelHelpers.createClass(ConferenceApplication, [{
	    key: "initDesktopEvents",
	    value: function initDesktopEvents() {
	      var _this2 = this;
	      if (!im_lib_utils.Utils.platform.isBitrixDesktop()) {
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      }
	      this.desktop = new im_lib_desktop.Desktop();
	      this.floatingScreenShareWindow = new Call.FloatingScreenShare({
	        desktop: this.desktop,
	        onBackToCallClick: this.onFloatingScreenShareBackToCallClick.bind(this),
	        onStopSharingClick: this.onFloatingScreenShareStopClick.bind(this),
	        onChangeScreenClick: this.onFloatingScreenShareChangeScreenClick.bind(this)
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
	          })["catch"](function (error) {
	            im_lib_logger.Logger.error('setSharingData error', error);
	          });
	        });
	        window.addEventListener('focus', function () {
	          _this2.onWindowFocus();
	        });
	        window.addEventListener('blur', function () {
	          _this2.onWindowBlur();
	        });
	      }
	      this.desktop.addCustomEvent('bxImUpdateCounterMessage', function (counter) {
	        if (!_this2.controller) {
	          return false;
	        }
	        _this2.controller.getStore().commit('conference/common', {
	          messageCount: counter
	        });
	      });
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.focus, this.onInputFocusHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.blur, this.onInputBlurHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.conference.userRenameFocus, this.onInputFocusHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.conference.userRenameBlur, this.onInputBlurHandler);
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
	    key: "subscribePreCallChanges",
	    value: function subscribePreCallChanges() {
	      BX.addCustomEvent(window, 'CallEvents::callCreated', this.onCallCreated.bind(this));
	    }
	  }, {
	    key: "subscribeNotifierEvents",
	    value: function subscribeNotifierEvents() {
	      var _this3 = this;
	      ui_notificationManager.Notifier.subscribe('click', function (event) {
	        var _event$getData = event.getData(),
	          id = _event$getData.id;
	        if (id.startsWith('im-videconf')) {
	          _this3.toggleChat();
	        }
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
	    key: "initCore",
	    value: function initCore() {
	      var _this4 = this;
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
	        vuexBuilder: {
	          database: !im_lib_utils.Utils.browser.isIe(),
	          databaseName: 'imol/call',
	          databaseType: ui_vue_vuex.VuexBuilder.DatabaseType.localStorage,
	          models: [im_model.ConferenceModel.create(), im_model.CallModel.create()]
	        }
	      });
	      window.BX.Messenger.Application.Core = {
	        controller: this.controller
	      };
	      return new Promise(function (resolve, reject) {
	        _this4.controller.ready().then(function () {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "setModelData",
	    value: function setModelData() {
	      var _this5 = this;
	      this.controller.getStore().commit('application/set', {
	        dialog: {
	          chatId: this.getChatId(),
	          dialogId: this.getDialogId()
	        },
	        options: {
	          darkBackground: true
	        }
	      });

	      //set presenters ID list
	      var presentersIds = this.params.presenters.map(function (presenter) {
	        return presenter['id'];
	      });
	      this.controller.getStore().dispatch('conference/setBroadcastMode', {
	        broadcastMode: this.params.isBroadcast
	      });
	      this.controller.getStore().dispatch('conference/setPresenters', {
	        presenters: presentersIds
	      });

	      //set presenters info in users model
	      this.params.presenters.forEach(function (presenter) {
	        _this5.controller.getStore().dispatch('users/set', presenter);
	      });
	      if (this.params.passwordRequired) {
	        this.controller.getStore().commit('conference/common', {
	          passChecked: false
	        });
	      }
	      if (this.params.conferenceTitle) {
	        this.controller.getStore().dispatch('conference/setConferenceTitle', {
	          conferenceTitle: this.params.conferenceTitle
	        });
	      }
	      if (this.params.alias) {
	        this.controller.getStore().commit('conference/setAlias', {
	          alias: this.params.alias
	        });
	      }
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initComponent",
	    value: function initComponent() {
	      var _this6 = this;
	      if (this.getStartupErrorCode()) {
	        this.setError(this.getStartupErrorCode());
	      }
	      return new Promise(function (resolve, reject) {
	        _this6.controller.createVue(_this6, {
	          el: _this6.rootNode,
	          data: function data() {
	            return {
	              dialogId: _this6.getDialogId()
	            };
	          },
	          template: "<bx-im-component-conference-public :dialogId=\"dialogId\"/>"
	        }).then(function (vue) {
	          _this6.template = vue;
	          resolve();
	        })["catch"](function (error) {
	          return reject(error);
	        });
	      });
	    }
	  }, {
	    key: "initCallInterface",
	    value: function initCallInterface() {
	      var _this7 = this;
	      return new Promise(function (resolve, reject) {
	        _this7.callContainer = document.getElementById('bx-im-component-call-container');
	        var hiddenButtons = ['document'];
	        if (_this7.isViewerMode()) {
	          hiddenButtons = ['camera', 'microphone', 'screen', 'record', 'floorRequest', 'document'];
	        }
	        if (!_this7.params.isIntranetOrExtranet) {
	          hiddenButtons.push('record');
	        }
	        _this7.callView = new Call.View({
	          container: _this7.callContainer,
	          showChatButtons: true,
	          showUsersButton: true,
	          showShareButton: _this7.getFeatureState('screenSharing') !== ConferenceApplication.FeatureState.Disabled,
	          showRecordButton: _this7.getFeatureState('record') !== ConferenceApplication.FeatureState.Disabled,
	          userLimit: Call.Util.getUserLimit(),
	          isIntranetOrExtranet: !!_this7.params.isIntranetOrExtranet,
	          language: _this7.params.language,
	          layout: im_lib_utils.Utils.device.isMobile() ? Call.View.Layout.Mobile : Call.View.Layout.Centered,
	          uiState: Call.View.UiState.Preparing,
	          blockedButtons: ['camera', 'microphone', 'floorRequest', 'screen', 'record'],
	          localUserState: Call.UserState.Idle,
	          hiddenTopButtons: !_this7.isBroadcast() || _this7.getBroadcastPresenters().length > 1 ? [] : ['grid'],
	          hiddenButtons: hiddenButtons,
	          broadcastingMode: _this7.isBroadcast(),
	          broadcastingPresenters: _this7.getBroadcastPresenters()
	        });
	        _this7.callView.subscribe(Call.View.Event.onButtonClick, _this7.onCallButtonClick.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onReplaceCamera, _this7.onCallReplaceCamera.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onReplaceMicrophone, _this7.onCallReplaceMicrophone.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onReplaceSpeaker, _this7.onCallReplaceSpeaker.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onChangeHdVideo, _this7.onCallViewChangeHdVideo.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onChangeMicAutoParams, _this7.onCallViewChangeMicAutoParams.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onChangeFaceImprove, _this7.onCallViewChangeFaceImprove.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onUserRename, _this7.onCallViewUserRename.bind(_this7));
	        _this7.callView.subscribe(Call.View.Event.onUserPinned, _this7.onCallViewUserPinned.bind(_this7));
	        _this7.callView.blockAddUser();
	        _this7.callView.blockHistoryButton();
	        if (!im_lib_utils.Utils.device.isMobile()) {
	          _this7.callView.show();
	        }
	        resolve();
	      });
	    }
	  }, {
	    key: "initUserComplete",
	    value: function initUserComplete() {
	      var _this8 = this;
	      return new Promise(function (resolve, reject) {
	        _this8.initUser().then(function () {
	          return _this8.startPageTagInterval();
	        }).then(function () {
	          return _this8.tryJoinExistingCall();
	        }).then(function () {
	          return _this8.initCall();
	        }).then(function () {
	          return _this8.initPullHandlers();
	        }).then(function () {
	          return _this8.subscribeToStoreChanges();
	        }).then(function () {
	          return _this8.initComplete();
	        }).then(function () {
	          return resolve;
	        })["catch"](function (error) {
	          return reject(error);
	        });
	      });
	    }
	    /* endregion 01. Initialize methods */
	    /* region 02. initUserComplete methods */
	  }, {
	    key: "initUser",
	    value: function initUser() {
	      var _this9 = this;
	      return new Promise(function (resolve, reject) {
	        if (_this9.getStartupErrorCode() || !_this9.getConference().common.passChecked) {
	          return reject();
	        }
	        if (_this9.params.userId > 0) {
	          _this9.controller.setUserId(_this9.params.userId);
	          if (_this9.params.isIntranetOrExtranet) {
	            _this9.switchToSessAuth();
	            _this9.controller.getStore().commit('conference/user', {
	              id: _this9.params.userId
	            });
	          } else {
	            var hashFromCookie = _this9.getUserHashCookie();
	            if (hashFromCookie) {
	              _this9.restClient.setAuthId(hashFromCookie);
	              _this9.restClient.setChatId(_this9.getChatId());
	              _this9.controller.getStore().commit('conference/user', {
	                id: _this9.params.userId,
	                hash: hashFromCookie
	              });
	              _this9.pullClient.start();
	            }
	          }
	          _this9.controller.getStore().commit('conference/common', {
	            inited: true
	          });
	          return resolve();
	        } else {
	          _this9.restClient.setAuthId('guest');
	          _this9.restClient.setChatId(_this9.getChatId());
	          if (typeof BX.SidePanel !== 'undefined') {
	            BX.SidePanel.Instance.disableAnchorBinding();
	          }
	          return _this9.restClient.callMethod('im.call.user.register', {
	            alias: _this9.params.alias,
	            user_hash: _this9.getUserHashCookie() || ''
	          }).then(function (result) {
	            BX.message['USER_ID'] = result.data().id;
	            _this9.controller.getStore().commit('conference/user', {
	              id: result.data().id,
	              hash: result.data().hash
	            });
	            _this9.controller.setUserId(result.data().id);
	            _this9.callView.setLocalUserId(result.data().id);
	            if (result.data().created) {
	              _this9.params.userCount++;
	            }
	            _this9.controller.getStore().commit('conference/common', {
	              inited: true
	            });
	            _this9.restClient.setAuthId(result.data().hash);
	            _this9.pullClient.start();
	            return resolve();
	          });
	        }
	      });
	    }
	  }, {
	    key: "startPageTagInterval",
	    value: function startPageTagInterval() {
	      var _this10 = this;
	      return new Promise(function (resolve) {
	        clearInterval(_this10.conferencePageTagInterval);
	        _this10.conferencePageTagInterval = setInterval(function () {
	          im_lib_localstorage.LocalStorage.set(_this10.params.siteId, _this10.params.userId, BX.CallEngine.getConferencePageTag(_this10.params.dialogId), "Y", 2);
	        }, 1000);
	        resolve();
	      });
	    }
	  }, {
	    key: "tryJoinExistingCall",
	    value: function tryJoinExistingCall() {
	      var _this11 = this;
	      this.restClient.callMethod("im.call.tryJoinCall", {
	        entityType: 'chat',
	        entityId: this.params.dialogId,
	        provider: Call.Provider.Voximplant,
	        type: Call.Type.Permanent
	      }).then(function (result) {
	        im_lib_logger.Logger.warn('tryJoinCall', result.data());
	        if (result.data().success) {
	          _this11.waitingForCallStatus = true;
	          _this11.waitingForCallStatusTimeout = setTimeout(function () {
	            _this11.waitingForCallStatus = false;
	            if (!_this11.callEventReceived) {
	              _this11.setConferenceStatus(false);
	            }
	            _this11.callEventReceived = false;
	          }, 5000);
	        } else {
	          _this11.setConferenceStatus(false);
	        }
	      });
	    }
	  }, {
	    key: "initCall",
	    value: function initCall() {
	      Call.Engine.setRestClient(this.restClient);
	      Call.Engine.setPullClient(this.pullClient);
	      Call.Engine.setCurrentUserId(this.controller.getUserId());
	      this.callView.unblockButtons(['chat']);
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
	    key: "subscribeToStoreChanges",
	    value: function subscribeToStoreChanges() {
	      var _this12 = this;
	      this.controller.getStore().subscribe(function (mutation, state) {
	        var payload = mutation.payload,
	          type = mutation.type;
	        if (type === 'users/update' && payload.fields.name) {
	          if (!_this12.callView) {
	            return false;
	          }
	          _this12.callView.updateUserData(babelHelpers.defineProperty({}, payload.id, {
	            name: payload.fields.name
	          }));
	        } else if (type === 'dialogues/set') {
	          if (payload[0].dialogId !== _this12.getDialogId()) {
	            return false;
	          }
	          if (!im_lib_utils.Utils.platform.isBitrixDesktop()) {
	            _this12.callView.setButtonCounter('chat', payload[0].counter);
	          }
	        } else if (type === 'dialogues/update') {
	          if (payload.dialogId !== _this12.getDialogId()) {
	            return false;
	          }
	          if (typeof payload.fields.counter === 'number' && _this12.callView) {
	            if (im_lib_utils.Utils.platform.isBitrixDesktop()) {
	              if (payload.actionName === "decreaseCounter" && !payload.dialogMuted && typeof payload.fields.previousCounter === 'number') {
	                var counter = payload.fields.counter;
	                if (_this12.getConference().common.messageCount) {
	                  counter = _this12.getConference().common.messageCount - (payload.fields.previousCounter - counter);
	                  if (counter < 0) {
	                    counter = 0;
	                  }
	                }
	                _this12.callView.setButtonCounter('chat', counter);
	              }
	            } else {
	              _this12.callView.setButtonCounter('chat', payload.fields.counter);
	            }
	          }
	          if (typeof payload.fields.name !== 'undefined') {
	            document.title = payload.fields.name.toString();
	          }
	        } else if (type === 'conference/common' && typeof payload.messageCount === 'number') {
	          if (_this12.callView) {
	            _this12.callView.setButtonCounter('chat', payload.messageCount);
	          }
	        } else if (type === 'conference/common' && typeof payload.userCount === 'number') {
	          if (_this12.callView) {
	            _this12.callView.setButtonCounter('users', payload.userCount);
	          }
	        }
	      });
	    }
	  }, {
	    key: "initComplete",
	    value: function initComplete() {
	      this.controller.getStore().commit('conference/common', {
	        userCount: this.params.userCount
	      });
	      this.callView.setButtonCounter('users', this.params.userCount);
	      if (this.isExternalUser()) {
	        this.callView.localUser.userModel.allowRename = true;
	      }
	      if (this.getConference().common.inited) {
	        this.inited = true;
	        this.initPromise.resolve(this);
	      }
	      if (im_lib_utils.Utils.platform.isBitrixDesktop()) {
	        this.desktop.onCustomEvent('bxConferenceLoadComplete', []);
	      }
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	    /* endregion 02. initUserComplete methods */
	    /* endregion 01. Initialize */
	    /* region 02. Methods */
	    /* region 01. Call methods */
	  }, {
	    key: "initHardware",
	    value: function initHardware() {
	      var _this13 = this;
	      return new Promise(function (resolve, reject) {
	        Call.Hardware.init().then(function () {
	          if (_this13.hardwareInited) {
	            resolve();
	            return true;
	          }
	          if (Object.values(Call.Hardware.microphoneList).length === 0) {
	            _this13.setError(im_const.ConferenceErrorCode.missingMicrophone);
	          }
	          if (!_this13.isViewerMode()) {
	            _this13.callView.unblockButtons(["camera", "microphone"]);
	            _this13.callView.enableMediaSelection();
	          }
	          _this13.hardwareInited = true;
	          resolve();
	        })["catch"](function (error) {
	          if (error === 'NO_WEBRTC' && _this13.isHttps()) {
	            _this13.setError(im_const.ConferenceErrorCode.unsupportedBrowser);
	          } else if (error === 'NO_WEBRTC' && !_this13.isHttps()) {
	            _this13.setError(im_const.ConferenceErrorCode.unsafeConnection);
	          }
	          im_lib_logger.Logger.error('Init hardware error', error);
	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "startCall",
	    value: function startCall(videoEnabled) {
	      var _this14 = this;
	      var viewerMode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var provider = Call.Provider.Voximplant;
	      if (im_lib_utils.Utils.device.isMobile()) {
	        this.callView.show();
	        this.callView.setButtonCounter('chat', this.getDialogData().counter);
	        this.callView.setButtonCounter('users', this.getConference().common.userCount);
	      } else {
	        this.callView.setLayout(Call.View.Layout.Grid);
	      }
	      this.callView.setUiState(Call.View.UiState.Calling);
	      if (this.localVideoStream) {
	        if (videoEnabled) {
	          this.callView.setLocalStream(this.localVideoStream, Call.Hardware.enableMirroring);
	        } else {
	          this.stopLocalVideoStream();
	        }
	      }
	      if (!videoEnabled) {
	        this.callView.setCameraState(false);
	      }
	      this.controller.getStore().commit('conference/startCall');
	      Call.Engine.createCall({
	        type: Call.Type.Permanent,
	        entityType: 'chat',
	        entityId: this.getDialogId(),
	        provider: provider,
	        videoEnabled: videoEnabled,
	        enableMicAutoParameters: Call.Hardware.enableMicAutoParameters,
	        joinExisting: true
	      }).then(function (e) {
	        im_lib_logger.Logger.warn('call created', e);
	        _this14.currentCall = e.call;
	        //this.currentCall.useHdVideo(Call.Hardware.preferHdQuality);
	        _this14.currentCall.useHdVideo(true);
	        if (Call.Hardware.defaultMicrophone) {
	          _this14.currentCall.setMicrophoneId(Call.Hardware.defaultMicrophone);
	        }
	        if (Call.Hardware.defaultCamera) {
	          _this14.currentCall.setCameraId(Call.Hardware.defaultCamera);
	        }
	        if (!im_lib_utils.Utils.device.isMobile()) {
	          _this14.callView.setLayout(Call.View.Layout.Grid);
	        }
	        _this14.callView.appendUsers(_this14.currentCall.getUsers());
	        Call.Util.getUsers(_this14.currentCall.id, _this14.getCallUsers(true)).then(function (userData) {
	          _this14.controller.getStore().dispatch('users/set', Object.values(userData));
	          _this14.controller.getStore().dispatch('conference/setUsers', {
	            users: Object.keys(userData)
	          });
	          _this14.callView.updateUserData(userData);
	        });
	        _this14.releasePreCall();
	        _this14.bindCallEvents();
	        if (_this14.callView.isMuted) {
	          _this14.currentCall.setMuted(true);
	        }
	        if (e.isNew) {
	          _this14.currentCall.setVideoEnabled(videoEnabled);
	          _this14.currentCall.inviteUsers();
	        } else {
	          _this14.currentCall.answer({
	            useVideo: videoEnabled,
	            joinAsViewer: viewerMode
	          });
	        }
	      })["catch"](function (e) {
	        im_lib_logger.Logger.error('creating call error', e);
	      });
	    }
	    /**
	     * @param {int} callId
	     * @param {object} options
	     */
	  }, {
	    key: "joinCall",
	    value: function joinCall(callId, options) {
	      var _this15 = this;
	      var video = BX.prop.getBoolean(options, "video", false);
	      var joinAsViewer = BX.prop.getBoolean(options, "joinAsViewer", false);
	      if (im_lib_utils.Utils.device.isMobile()) {
	        this.callView.show();
	      } else {
	        this.callView.setLayout(Call.View.Layout.Grid);
	      }
	      if (joinAsViewer) {
	        this.callView.setLocalUserDirection(Call.EndpointDirection.RecvOnly);
	      } else {
	        this.callView.setLocalUserDirection(Call.EndpointDirection.SendRecv);
	      }
	      this.callView.setUiState(Call.View.UiState.Calling);
	      Call.Engine.getCallWithId(callId).then(function (result) {
	        _this15.currentCall = result.call;
	        _this15.releasePreCall();
	        _this15.bindCallEvents();
	        _this15.controller.getStore().commit('conference/startCall');
	        _this15.callView.appendUsers(_this15.currentCall.getUsers());
	        Call.Util.getUsers(_this15.currentCall.id, _this15.getCallUsers(true)).then(function (userData) {
	          _this15.controller.getStore().dispatch('users/set', Object.values(userData));
	          _this15.controller.getStore().dispatch('conference/setUsers', {
	            users: Object.keys(userData)
	          });
	          _this15.callView.updateUserData(userData);
	        });
	        if (!joinAsViewer) {
	          //this.currentCall.useHdVideo(Call.Hardware.preferHdQuality);
	          _this15.currentCall.useHdVideo(true);
	          if (Call.Hardware.defaultMicrophone) {
	            _this15.currentCall.setMicrophoneId(Call.Hardware.defaultMicrophone);
	          }
	          if (Call.Hardware.defaultCamera) {
	            _this15.currentCall.setCameraId(Call.Hardware.defaultCamera);
	          }
	          if (_this15.callView.isMuted) {
	            _this15.currentCall.setMuted(true);
	          }
	        }
	        _this15.currentCall.answer({
	          useVideo: !!video,
	          joinAsViewer: joinAsViewer
	        });
	      })["catch"](function (error) {
	        return console.error(error);
	      });
	    }
	  }, {
	    key: "endCall",
	    value: function endCall() {
	      if (this.currentCall) {
	        this.showFeedback = this.currentCall.wasConnected;
	        this.callDetails = {
	          id: this.currentCall.id,
	          provider: this.currentCall.provider,
	          userCount: this.currentCall.users.length,
	          browser: Call.Util.getBrowserForStatistics(),
	          isMobile: BX.browser.IsMobile(),
	          isConference: true
	        };
	        this.removeCallEvents();
	        this.currentCall.hangup();
	      }
	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordStop();
	      }
	      this.callRecordState = Call.View.RecordState.Stopped;
	      if (im_lib_utils.Utils.platform.isBitrixDesktop()) {
	        if (this.floatingScreenShareWindow) {
	          this.floatingScreenShareWindow.destroy();
	          this.floatingScreenShareWindow = null;
	        }
	        window.close();
	      } else {
	        this.callView.releaseLocalMedia();
	        this.callView.close();
	        this.setError(im_const.ConferenceErrorCode.userLeftCall);
	        this.controller.getStore().commit('conference/endCall');
	      }
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.focus, this.onInputFocusHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.blur, this.onInputBlurHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.conference.userRenameFocus, this.onInputFocusHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.conference.userRenameBlur, this.onInputBlurHandler);
	    }
	  }, {
	    key: "restart",
	    value: function restart() {
	      console.trace("restart");
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
	      this.controller.getStore().commit('conference/endCall');
	    }
	  }, {
	    key: "kickFromCall",
	    value: function kickFromCall() {
	      this.setError(im_const.ConferenceErrorCode.kickedFromCall);
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
	          state: ConferenceApplication.FeatureState.Enabled,
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
	      return this.canRecord() && this.callRecordState != Call.View.RecordState.Stopped;
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
	    key: "showMicMutedNotification",
	    value: function showMicMutedNotification() {
	      var _this16 = this;
	      if (this.mutePopup || !this.callView) {
	        return;
	      }
	      this.mutePopup = new Call.Hint({
	        bindElement: this.callView.buttons.microphone.elements.icon,
	        targetContainer: this.callView.container,
	        buttons: [this.createUnmuteButton()],
	        onClose: function onClose() {
	          _this16.allowMutePopup = false;
	          _this16.mutePopup.destroy();
	          _this16.mutePopup = null;
	        }
	      });
	      this.mutePopup.show();
	    }
	  }, {
	    key: "createUnmuteButton",
	    value: function createUnmuteButton() {
	      var _this17 = this;
	      return new BX.UI.Button({
	        baseClass: "ui-btn ui-btn-icon-mic",
	        text: BX.message("IM_CALL_UNMUTE_MIC"),
	        size: BX.UI.Button.Size.EXTRA_SMALL,
	        color: BX.UI.Button.Color.LIGHT_BORDER,
	        noCaps: true,
	        round: true,
	        events: {
	          click: function click() {
	            _this17.onCallViewToggleMuteButtonClick({
	              muted: false
	            });
	            _this17.mutePopup.destroy();
	            _this17.mutePopup = null;
	          }
	        }
	      });
	    }
	  }, {
	    key: "showWebScreenSharePopup",
	    value: function showWebScreenSharePopup() {
	      if (this.webScreenSharePopup) {
	        this.webScreenSharePopup.show();
	        return;
	      }
	      this.webScreenSharePopup = new Call.WebScreenSharePopup({
	        bindElement: this.callView.buttons.screen.elements.root,
	        targetContainer: this.callView.container,
	        onClose: function () {
	          this.webScreenSharePopup.destroy();
	          this.webScreenSharePopup = null;
	        }.bind(this),
	        onStopSharingClick: function () {
	          this.onCallViewToggleScreenSharingButtonClick();
	          this.webScreenSharePopup.destroy();
	          this.webScreenSharePopup = null;
	        }.bind(this)
	      });
	      this.webScreenSharePopup.show();
	    }
	  }, {
	    key: "isViewerMode",
	    value: function isViewerMode() {
	      var viewerMode = false;
	      var isBroadcast = this.isBroadcast();
	      if (isBroadcast) {
	        var presenters = this.getBroadcastPresenters();
	        var currentUserId = this.controller.getStore().state.application.common.userId;
	        var isCurrentUserPresenter = presenters.includes(currentUserId);
	        viewerMode = isBroadcast && !isCurrentUserPresenter;
	      }
	      return viewerMode;
	    }
	  }, {
	    key: "onCallCreated",
	    value: function onCallCreated(e) {
	      var _this18 = this;
	      im_lib_logger.Logger.warn('we got event onCallCreated', e);
	      if (this.preCall || this.currentCall) {
	        return;
	      }
	      var call = e.call;
	      if (call.associatedEntity.type === 'chat' && call.associatedEntity.id === this.params.dialogId) {
	        this.preCall = e.call;
	        this.updatePreCallCounter();
	        this.preCall.addEventListener(Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
	        this.preCall.addEventListener(Call.Event.onDestroy, this.onPreCallDestroyHandler);
	        if (this.waitingForCallStatus) {
	          this.callEventReceived = true;
	        }
	        this.setConferenceStatus(true);
	        this.setConferenceStartDate(e.call.startDate);
	      }
	      var userReadyToJoin = this.getConference().common.userReadyToJoin;
	      if (userReadyToJoin) {
	        var viewerMode = this.isViewerMode();
	        var videoEnabled = this.getConference().common.joinWithVideo;
	        im_lib_logger.Logger.warn('ready to join call after waiting', videoEnabled, viewerMode);
	        setTimeout(function () {
	          Call.Hardware.init().then(function () {
	            if (viewerMode && _this18.preCall) {
	              _this18.joinCall(_this18.preCall.id, {
	                joinAsViewer: true
	              });
	            } else {
	              _this18.startCall(videoEnabled);
	            }
	          });
	        }, 1000);
	      }
	    }
	  }, {
	    key: "releasePreCall",
	    value: function releasePreCall() {
	      if (this.preCall) {
	        this.preCall.removeEventListener(Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
	        this.preCall.removeEventListener(Call.Event.onDestroy, this.onPreCallDestroyHandler);
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
	        this.controller.getStore().commit('conference/common', {
	          userInCallCount: this.preCall.getParticipatingUsers().length
	        });
	      } else {
	        this.controller.getStore().commit('conference/common', {
	          userInCallCount: 0
	        });
	      }
	    }
	  }, {
	    key: "createVideoStrategy",
	    value: function createVideoStrategy() {
	      if (this.videoStrategy) {
	        this.videoStrategy.destroy();
	      }
	      var strategyType = im_lib_utils.Utils.device.isMobile() ? VideoStrategy.Type.OnlySpeaker : VideoStrategy.Type.AllowAll;
	      this.videoStrategy = new VideoStrategy({
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
	    key: "onCallReplaceCamera",
	    value: function onCallReplaceCamera(event) {
	      var cameraId = event.data.deviceId;
	      Call.Hardware.defaultCamera = cameraId;
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
	      Call.Hardware.defaultMicrophone = microphoneId.deviceId;
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
	      Call.Hardware.defaultSpeaker = event.data.deviceId;
	    }
	  }, {
	    key: "onCallViewChangeHdVideo",
	    value: function onCallViewChangeHdVideo(event) {
	      Call.Hardware.preferHdQuality = event.data.allowHdVideo;
	    }
	  }, {
	    key: "onCallViewChangeMicAutoParams",
	    value: function onCallViewChangeMicAutoParams(event) {
	      Call.Hardware.enableMicAutoParameters = event.data.allowMicAutoParams;
	    }
	  }, {
	    key: "onCallViewChangeFaceImprove",
	    value: function onCallViewChangeFaceImprove(event) {
	      if (typeof BX.desktop === 'undefined') {
	        return;
	      }
	      BX.desktop.cameraSmoothingStatus(event.data.faceImproveEnabled);
	    }
	  }, {
	    key: "onCallViewUserRename",
	    value: function onCallViewUserRename(event) {
	      var newName = event.data.newName;
	      if (!this.isExternalUser()) {
	        return false;
	      }
	      if (im_lib_utils.Utils.device.isMobile()) {
	        this.renameGuestMobile(newName);
	      } else {
	        this.renameGuest(newName);
	      }
	    }
	  }, {
	    key: "onCallViewUserPinned",
	    value: function onCallViewUserPinned(event) {
	      if (event.data.userId) {
	        this.updateCallUser(event.data.userId, {
	          pinned: true
	        });
	        return true;
	      }
	      this.controller.getStore().dispatch('call/unpinUser');
	      return true;
	    }
	  }, {
	    key: "renameGuest",
	    value: function renameGuest(newName) {
	      var _this19 = this;
	      this.callView.localUser.userModel.renameRequested = true;
	      this.setUserName(newName).then(function () {
	        _this19.callView.localUser.userModel.wasRenamed = true;
	        im_lib_logger.Logger.log('setting name to', newName);
	      })["catch"](function (error) {
	        im_lib_logger.Logger.error('error setting name', error);
	      });
	    }
	  }, {
	    key: "renameGuestMobile",
	    value: function renameGuestMobile(newName) {
	      var _this20 = this;
	      this.setUserName(newName).then(function () {
	        im_lib_logger.Logger.log('setting mobile name to', newName);
	        if (_this20.callView.renameSlider) {
	          _this20.callView.renameSlider.close();
	        }
	      })["catch"](function (error) {
	        im_lib_logger.Logger.error('error setting name', error);
	      });
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
	        toggleSpeaker: this.onCallViewToggleSpeakerButtonClick.bind(this),
	        showChat: this.onCallViewShowChatButtonClick.bind(this),
	        toggleUsers: this.onCallViewToggleUsersButtonClick.bind(this),
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
	      if (this.getFeatureState('screenSharing') === ConferenceApplication.FeatureState.Limited) {
	        this.showFeatureLimitSlider('screenSharing');
	        return;
	      }
	      if (this.getFeatureState('screenSharing') === ConferenceApplication.FeatureState.Disabled) {
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
	        if (this.webScreenSharePopup) {
	          this.webScreenSharePopup.close();
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
	      if (event.data.recordState === Call.View.RecordState.Started) {
	        if (this.getFeatureState('record') === ConferenceApplication.FeatureState.Limited) {
	          this.showFeatureLimitSlider('record');
	          return;
	        }
	        if (this.getFeatureState('record') === ConferenceApplication.FeatureState.Disabled) {
	          return;
	        }
	        if (this.canRecord()) {
	          // TODO: create popup menu with choice type of record - im/install/js/im/call/controller.js:1635
	          // Call.View.RecordType.Video / Call.View.RecordType.Audio

	          this.callView.setButtonActive('record', true);
	        } else {
	          if (window.BX.Helper) {
	            window.BX.Helper.show("redirect=detail&code=12398134");
	          }
	          return;
	        }
	      } else if (event.data.recordState === Call.View.RecordState.Paused) {
	        if (this.canRecord()) {
	          BXDesktopSystem.CallRecordPause(true);
	        }
	      } else if (event.data.recordState === Call.View.RecordState.Resumed) {
	        if (this.canRecord()) {
	          BXDesktopSystem.CallRecordPause(false);
	        }
	      } else if (event.data.recordState === Call.View.RecordState.Stopped) {
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
	        if (!Call.Hardware.initialized) {
	          return;
	        }
	        if (event.data.video && Object.values(Call.Hardware.cameraList).length === 0) {
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
	    key: "onCallViewToggleSpeakerButtonClick",
	    value: function onCallViewToggleSpeakerButtonClick(event) {
	      this.callView.muteSpeaker(!event.speakerMuted);
	      if (event.fromHotKey) {
	        BX.UI.Notification.Center.notify({
	          content: BX.message(this.callView.speakerMuted ? 'IM_M_CALL_MUTE_SPEAKERS_OFF' : 'IM_M_CALL_MUTE_SPEAKERS_ON'),
	          position: "top-right",
	          autoHideDelay: 3000,
	          closeButton: true
	        });
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
	      im_lib_clipboard.Clipboard.copy(this.getDialogData()["public"].link);
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
	    key: "onFloatingScreenShareChangeScreenClick",
	    value: function onFloatingScreenShareChangeScreenClick() {
	      if (this.currentCall) {
	        this.currentCall.startScreenSharing(true);
	      }
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
	    key: "onCallViewToggleUsersButtonClick",
	    value: function onCallViewToggleUsersButtonClick() {
	      this.toggleUserList();
	    }
	  }, {
	    key: "onCallViewFloorRequestButtonClick",
	    value: function onCallViewFloorRequestButtonClick() {
	      var _this21 = this;
	      var floorState = this.callView.getUserFloorRequestState(Call.Engine.getCurrentUserId());
	      var talkingState = this.callView.getUserTalking(Call.Engine.getCurrentUserId());
	      this.callView.setUserFloorRequestState(Call.Engine.getCurrentUserId(), !floorState);
	      if (this.currentCall) {
	        this.currentCall.requestFloor(!floorState);
	      }
	      clearTimeout(this.callViewFloorRequestTimeout);
	      if (talkingState && !floorState) {
	        this.callViewFloorRequestTimeout = setTimeout(function () {
	          if (_this21.currentCall) {
	            _this21.currentCall.requestFloor(false);
	          }
	        }, 1500);
	      }
	    }
	  }, {
	    key: "bindCallEvents",
	    value: function bindCallEvents() {
	      this.currentCall.addEventListener(Call.Event.onUserInvited, this.onCallUserInvitedHandler);
	      this.currentCall.addEventListener(Call.Event.onDestroy, this.onCallDestroyHandler);
	      this.currentCall.addEventListener(Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
	      this.currentCall.addEventListener(Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
	      this.currentCall.addEventListener(Call.Event.onUserCameraState, this.onCallUserCameraStateHandler);
	      this.currentCall.addEventListener(Call.Event.onUserVideoPaused, this.onCallUserVideoPausedHandler);
	      this.currentCall.addEventListener(Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
	      this.currentCall.addEventListener(Call.Event.onRemoteMediaReceived, this.onCallRemoteMediaReceivedHandler);
	      this.currentCall.addEventListener(Call.Event.onRemoteMediaStopped, this.onCallRemoteMediaStoppedHandler);
	      this.currentCall.addEventListener(Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
	      this.currentCall.addEventListener(Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
	      this.currentCall.addEventListener(Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
	      this.currentCall.addEventListener(Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
	      this.currentCall.addEventListener(Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
	      this.currentCall.addEventListener(Call.Event.onMicrophoneLevel, this.onMicrophoneLevelHandler);
	      //this.currentCall.addEventListener(Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
	      //this.currentCall.addEventListener(Call.Event.onCallFailure, this._onCallFailureHandler);
	      this.currentCall.addEventListener(Call.Event.onJoin, this._onCallJoinHandler);
	      this.currentCall.addEventListener(Call.Event.onLeave, this.onCallLeaveHandler);
	    }
	  }, {
	    key: "removeCallEvents",
	    value: function removeCallEvents() {
	      this.currentCall.removeEventListener(Call.Event.onUserInvited, this.onCallUserInvitedHandler);
	      this.currentCall.removeEventListener(Call.Event.onDestroy, this.onCallDestroyHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserCameraState, this.onCallUserCameraStateHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserVideoPaused, this.onCallUserVideoPausedHandler);
	      this.currentCall.removeEventListener(Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
	      this.currentCall.removeEventListener(Call.Event.onRemoteMediaReceived, this.onCallRemoteMediaReceivedHandler);
	      this.currentCall.removeEventListener(Call.Event.onRemoteMediaStopped, this.onCallRemoteMediaStoppedHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
	      this.currentCall.removeEventListener(Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
	      this.currentCall.removeEventListener(Call.Event.onMicrophoneLevel, this.onMicrophoneLevelHandler);
	      //this.currentCall.removeEventListener(Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
	      //this.currentCall.removeEventListener(Call.Event.onCallFailure, this._onCallFailureHandler);
	      this.currentCall.removeEventListener(Call.Event.onLeave, this.onCallLeaveHandler);
	    }
	  }, {
	    key: "onCallUserInvited",
	    value: function onCallUserInvited(e) {
	      var _this22 = this;
	      this.callView.addUser(e.userId);
	      Call.Util.getUsers(this.currentCall.id, [e.userId]).then(function (userData) {
	        _this22.controller.getStore().dispatch('users/set', Object.values(userData));
	        _this22.controller.getStore().dispatch('conference/setUsers', {
	          users: Object.keys(userData)
	        });
	        _this22.callView.updateUserData(userData);
	      });
	    }
	  }, {
	    key: "onCallUserStateChanged",
	    value: function onCallUserStateChanged(e) {
	      this.callView.setUserState(e.userId, e.state);
	      this.updateCallUser(e.userId, {
	        state: e.state
	      });
	      /*if (e.direction)
	      {
	      	this.callView.setUserDirection(e.userId, e.direction);
	      }*/
	    }
	  }, {
	    key: "onCallUserMicrophoneState",
	    value: function onCallUserMicrophoneState(e) {
	      this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
	      this.updateCallUser(e.userId, {
	        microphoneState: e.microphoneState
	      });
	    }
	  }, {
	    key: "onCallUserCameraState",
	    value: function onCallUserCameraState(e) {
	      this.callView.setUserCameraState(e.userId, e.cameraState);
	      this.updateCallUser(e.userId, {
	        cameraState: e.cameraState
	      });
	    }
	  }, {
	    key: "onCallUserVideoPaused",
	    value: function onCallUserVideoPaused(e) {
	      this.callView.setUserVideoPaused(e.userId, e.videoPaused);
	    }
	  }, {
	    key: "onCallLocalMediaReceived",
	    value: function onCallLocalMediaReceived(e) {
	      //this.template.$emit('callLocalMediaReceived');

	      this.stopLocalVideoStream();
	      var enableVideoMirroring = e.tag == "main" ? Call.Hardware.enableMirroring : false;
	      this.callView.setLocalStream(e.stream, enableVideoMirroring);
	      this.callView.setButtonActive("screen", e.tag == "screen");
	      if (e.tag == "screen") {
	        if (!im_lib_utils.Utils.platform.isBitrixDesktop()) {
	          this.showWebScreenSharePopup();
	        }
	        this.callView.blockSwitchCamera();
	        this.callView.updateButtons();
	      } else {
	        if (this.webScreenSharePopup) {
	          this.webScreenSharePopup.close();
	        }
	        if (!this.currentCall.callFromMobile && !this.isViewerMode()) {
	          this.callView.unblockSwitchCamera();
	          this.callView.updateButtons();
	        }
	      }
	    }
	  }, {
	    key: "onCallRemoteMediaReceived",
	    value: function onCallRemoteMediaReceived(e) {
	      if (this.callView) {
	        if ('track' in e) {
	          this.callView.setUserMedia(e.userId, e.kind, e.track);
	        }
	        if ('mediaRenderer' in e && e.mediaRenderer.kind === 'audio') {
	          this.callView.setUserMedia(e.userId, 'audio', e.mediaRenderer.stream.getAudioTracks()[0]);
	        }
	        if ('mediaRenderer' in e && (e.mediaRenderer.kind === 'video' || e.mediaRenderer.kind === 'sharing')) {
	          this.callView.setVideoRenderer(e.userId, e.mediaRenderer);
	        }
	      }
	    }
	  }, {
	    key: "onCallRemoteMediaStopped",
	    value: function onCallRemoteMediaStopped(e) {
	      if (this.callView) {
	        if ('mediaRenderer' in e) {
	          if (e.kind === 'video' || e.kind === 'sharing') {
	            this.callView.setVideoRenderer(e.userId, null);
	          }
	        } else {
	          this.callView.setUserMedia(e.userId, e.kind, null);
	        }
	      }
	    }
	  }, {
	    key: "onCallUserVoiceStarted",
	    value: function onCallUserVoiceStarted(e) {
	      if (e.local) {
	        if (this.currentCall.muted && this.allowMutePopup) {
	          this.showMicMutedNotification();
	        }
	        return;
	      }
	      this.callView.setUserTalking(e.userId, true);
	      this.callView.setUserFloorRequestState(e.userId, false);
	      this.updateCallUser(e.userId, {
	        talking: true,
	        floorRequestState: false
	      });
	    }
	  }, {
	    key: "onCallUserVoiceStopped",
	    value: function onCallUserVoiceStopped(e) {
	      this.callView.setUserTalking(e.userId, false);
	      this.updateCallUser(e.userId, {
	        talking: false
	      });
	    }
	  }, {
	    key: "onCallUserScreenState",
	    value: function onCallUserScreenState(e) {
	      if (this.callView) {
	        this.callView.setUserScreenState(e.userId, e.screenState);
	      }
	      this.updateCallUser(e.userId, {
	        screenState: e.screenState
	      });
	    }
	  }, {
	    key: "onCallUserRecordState",
	    value: function onCallUserRecordState(event) {
	      this.callRecordState = event.recordState.state;
	      this.callView.setRecordState(event.recordState);
	      if (!this.canRecord() || event.userId != this.controller.getUserId()) {
	        return true;
	      }
	      if (event.recordState.state === Call.View.RecordState.Started && event.recordState.userId == this.controller.getUserId()) {
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
	        Call.Engine.getRestClient().callMethod("im.call.onStartRecord", {
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
	          cropBottom: 73,
	          shareMethod: 'im.disk.record.share'
	        });
	      } else if (event.recordState.state === Call.View.RecordState.Stopped) {
	        BXDesktopSystem.CallRecordStop();
	      }
	      return true;
	    }
	  }, {
	    key: "onCallUserFloorRequest",
	    value: function onCallUserFloorRequest(e) {
	      this.callView.setUserFloorRequestState(e.userId, e.requestActive);
	      this.updateCallUser(e.userId, {
	        floorRequestState: e.requestActive
	      });
	    }
	  }, {
	    key: "onMicrophoneLevel",
	    value: function onMicrophoneLevel(e) {
	      this.callView.setMicrophoneLevel(e.level);
	    }
	  }, {
	    key: "onCallJoin",
	    value: function onCallJoin(e) {
	      if (!e.local) {
	        return;
	      }
	      if (!this.isViewerMode()) {
	        this.callView.unblockButtons(['camera', 'floorRequest', 'screen', 'record']);
	      }
	      this.callView.setUiState(Call.View.UiState.Connected);
	    }
	  }, {
	    key: "onCallLeave",
	    value: function onCallLeave(e) {
	      if (!e.local) {
	        return;
	      }
	      if (this.webScreenSharePopup) {
	        this.webScreenSharePopup.close();
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
	      if (this.webScreenSharePopup) {
	        this.webScreenSharePopup.close();
	      }
	      this.restart();
	    }
	  }, {
	    key: "onCheckDevicesSave",
	    value: function onCheckDevicesSave(changedValues) {
	      if (changedValues['camera']) {
	        Call.Hardware.defaultCamera = changedValues['camera'];
	      }
	      if (changedValues['microphone']) {
	        Call.Hardware.defaultMicrophone = changedValues['microphone'];
	      }
	      if (changedValues['audioOutput']) {
	        Call.Hardware.defaultSpeaker = changedValues['audioOutput'];
	      }
	      if (changedValues['preferHDQuality']) {
	        Call.Hardware.preferHdQuality = changedValues['preferHDQuality'];
	      }
	      if (changedValues['enableMicAutoParameters']) {
	        Call.Hardware.enableMicAutoParameters = changedValues['enableMicAutoParameters'];
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
	    key: "isChatShowed",
	    value: function isChatShowed() {
	      return this.getConference().common.showChat;
	    }
	  }, {
	    key: "toggleChat",
	    value: function toggleChat() {
	      var rightPanelMode = this.getConference().common.rightPanelMode;
	      if (rightPanelMode === im_const.ConferenceRightPanelMode.hidden) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.chat
	        });
	        this.callView.setButtonActive('chat', true);
	      } else if (rightPanelMode === im_const.ConferenceRightPanelMode.chat) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.hidden
	        });
	        this.callView.setButtonActive('chat', false);
	      } else if (rightPanelMode === im_const.ConferenceRightPanelMode.users) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.split
	        });
	        this.callView.setButtonActive('chat', true);
	      } else if (rightPanelMode === im_const.ConferenceRightPanelMode.split) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.users
	        });
	        this.callView.setButtonActive('chat', false);
	      }
	    }
	  }, {
	    key: "toggleUserList",
	    value: function toggleUserList() {
	      var rightPanelMode = this.getConference().common.rightPanelMode;
	      if (rightPanelMode === im_const.ConferenceRightPanelMode.hidden) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.users
	        });
	        this.callView.setButtonActive('users', true);
	      } else if (rightPanelMode === im_const.ConferenceRightPanelMode.users) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.hidden
	        });
	        this.callView.setButtonActive('users', false);
	      } else if (rightPanelMode === im_const.ConferenceRightPanelMode.chat) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.split
	        });
	        this.callView.setButtonActive('users', true);
	      } else if (rightPanelMode === im_const.ConferenceRightPanelMode.split) {
	        this.controller.getStore().dispatch('conference/changeRightPanelMode', {
	          mode: im_const.ConferenceRightPanelMode.chat
	        });
	        this.callView.setButtonActive('users', false);
	      }
	    }
	  }, {
	    key: "pinUser",
	    value: function pinUser(user) {
	      if (!this.callView) {
	        return false;
	      }
	      this.callView.pinUser(user.id);
	      this.callView.setLayout(Call.View.Layout.Centered);
	    }
	  }, {
	    key: "unpinUser",
	    value: function unpinUser() {
	      if (!this.callView) {
	        return false;
	      }
	      this.callView.unpinUser();
	    }
	  }, {
	    key: "changeBackground",
	    value: function changeBackground() {
	      if (!Call.Hardware) {
	        return false;
	      }
	      Call.BackgroundDialog.open();
	    }
	  }, {
	    key: "openChat",
	    value: function openChat(user) {
	      this.desktop.onCustomEvent('bxConferenceOpenChat', [user.id]);
	    }
	  }, {
	    key: "openProfile",
	    value: function openProfile(user) {
	      this.desktop.onCustomEvent('bxConferenceOpenProfile', [user.id]);
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
	    value: function sendNewMessageNotify(params) {
	      if (!this.checkIfMessageNotifyIsNeeded(params)) {
	        return false;
	      }
	      var text = im_lib_utils.Utils.text.purify(params.message.text, params.message.params, params.files);
	      var avatar = '';
	      var userName = '';

	      // avatar and username only for non-system messages
	      if (params.message.senderId > 0 && params.message.system !== 'Y') {
	        var messageAuthor = this.controller.getStore().getters['users/get'](params.message.senderId, true);
	        userName = messageAuthor.name;
	        avatar = messageAuthor.avatar;
	      }
	      ui_notificationManager.Notifier.notify({
	        id: "im-videconf-".concat(params.message.id),
	        title: userName,
	        icon: avatar,
	        text: text
	      });
	      return true;
	    }
	  }, {
	    key: "checkIfMessageNotifyIsNeeded",
	    value: function checkIfMessageNotifyIsNeeded(params) {
	      var rightPanelMode = this.getConference().common.rightPanelMode;
	      return !im_lib_utils.Utils.device.isMobile() && params.chatId === this.getChatId() && (rightPanelMode !== im_const.ConferenceRightPanelMode.chat || rightPanelMode !== im_const.ConferenceRightPanelMode.split) && params.message.senderId !== this.controller.getUserId() && !this.getConference().common.error;
	    }
	  }, {
	    key: "onInputFocus",
	    value: function onInputFocus(e) {
	      this.callView.setHotKeyTemporaryBlock(true);
	    }
	  }, {
	    key: "onInputBlur",
	    value: function onInputBlur(e) {
	      this.callView.setHotKeyTemporaryBlock(false);
	    }
	  }, {
	    key: "setUserWasRenamed",
	    value: function setUserWasRenamed() {
	      if (this.callView) {
	        this.callView.localUser.userModel.wasRenamed = true;
	      }
	    }
	    /* endregion 01. General actions */
	    /* region 02. Store actions */
	  }, {
	    key: "setError",
	    value: function setError(errorCode) {
	      var currentError = this.getConference().common.error;
	      // if user kicked from call - dont show him end of call form
	      if (currentError && currentError === im_const.ConferenceErrorCode.kickedFromCall) {
	        return;
	      }
	      this.controller.getStore().commit('conference/setError', {
	        errorCode: errorCode
	      });
	    }
	  }, {
	    key: "toggleSmiles",
	    value: function toggleSmiles() {
	      this.controller.getStore().commit('conference/toggleSmiles');
	    }
	  }, {
	    key: "setJoinType",
	    value: function setJoinType(joinWithVideo) {
	      this.controller.getStore().commit('conference/setJoinType', {
	        joinWithVideo: joinWithVideo
	      });
	    }
	  }, {
	    key: "setConferenceStatus",
	    value: function setConferenceStatus(conferenceStarted) {
	      this.controller.getStore().commit('conference/setConferenceStatus', {
	        conferenceStarted: conferenceStarted
	      });
	    }
	  }, {
	    key: "setConferenceStartDate",
	    value: function setConferenceStartDate(conferenceStartDate) {
	      this.controller.getStore().commit('conference/setConferenceStartDate', {
	        conferenceStartDate: conferenceStartDate
	      });
	    }
	  }, {
	    key: "setUserReadyToJoin",
	    value: function setUserReadyToJoin() {
	      this.controller.getStore().commit('conference/setUserReadyToJoin');
	    }
	  }, {
	    key: "updateCallUser",
	    value: function updateCallUser(userId, fields) {
	      this.controller.getStore().dispatch('call/updateUser', {
	        id: userId,
	        fields: fields
	      });
	    }
	    /* endregion 02. Store actions */
	    /* region 03. Rest actions */
	  }, {
	    key: "setUserName",
	    value: function setUserName(name) {
	      var _this23 = this;
	      return new Promise(function (resolve, reject) {
	        _this23.restClient.callMethod('im.call.user.update', {
	          name: name,
	          chat_id: _this23.getChatId()
	        }).then(function () {
	          resolve();
	        })["catch"](function (error) {
	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "checkPassword",
	    value: function checkPassword(password) {
	      var _this24 = this;
	      return new Promise(function (resolve, reject) {
	        _this24.restClient.callMethod('im.videoconf.password.check', {
	          password: password,
	          alias: _this24.params.alias
	        }).then(function (result) {
	          if (result.data() === true) {
	            _this24.restClient.setPassword(password);
	            _this24.controller.getStore().commit('conference/common', {
	              passChecked: true
	            });
	            _this24.initUserComplete();
	            resolve();
	          } else {
	            reject();
	          }
	        })["catch"](function (result) {
	          console.error('Password check error', result);
	        });
	      });
	    }
	  }, {
	    key: "changeLink",
	    value: function changeLink() {
	      var _this25 = this;
	      return new Promise(function (resolve, reject) {
	        _this25.restClient.callMethod('im.videoconf.share.change', {
	          dialog_id: _this25.getDialogId()
	        }).then(function () {
	          resolve();
	        })["catch"](function (error) {
	          reject(error);
	        });
	      });
	    }
	    /* endregion 03. Rest actions */
	    /* endregion 02. Component methods */
	    /* endregion 02. Methods */
	    /* region 03. Utils */
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
	    key: "getConference",
	    value: function getConference() {
	      return this.controller.getStore().state.conference;
	    }
	  }, {
	    key: "isBroadcast",
	    value: function isBroadcast() {
	      return this.getConference().common.isBroadcast;
	    }
	  }, {
	    key: "getBroadcastPresenters",
	    value: function getBroadcastPresenters() {
	      return this.getConference().common.presenters;
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
	    key: "isHttps",
	    value: function isHttps() {
	      return location.protocol === 'https:';
	    }
	  }, {
	    key: "getUserHash",
	    value: function getUserHash() {
	      return this.getConference().user.hash;
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
	    key: "switchToSessAuth",
	    value: function switchToSessAuth() {
	      this.restClient.restClient.queryParams = undefined;
	      return true;
	    } /* endregion 03. Utils */
	  }]);
	  return ConferenceApplication;
	}();
	ConferenceApplication.FeatureState = {
	  Enabled: 'enabled',
	  Disabled: 'disabled',
	  Limited: 'limited'
	};

	exports.ConferenceApplication = ConferenceApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX,BX.Messenger.Application,BX.Messenger,BX.Call,BX.Messenger.Model,BX.Messenger,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Const,BX.UI.NotificationManager,BX,BX.UI,BX.UI,BX.UI.Viewer,BX,BX,BX,BX,BX.Main,BX.Event,BX,BX.Messenger.Provider.Pull,BX,BX.Messenger.Lib));
//# sourceMappingURL=conference.bundle.js.map
