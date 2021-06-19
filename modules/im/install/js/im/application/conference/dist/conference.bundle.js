this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_call,im_debug,im_application_launch,im_component_conference_conferencePublic,im_model,im_controller,im_lib_cookie,im_lib_localstorage,im_lib_logger,im_lib_clipboard,im_lib_desktop,im_const,ui_notification,ui_buttons,ui_progressround,ui_viewer,ui_vue,ui_vue_vuex,main_core,promise,main_date,main_core_events,pull_client,im_provider_pull,rest_client,im_lib_utils) {
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
	    this.callView = null;
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
	    this.onCallUserStreamReceivedHandler = this.onCallUserStreamReceived.bind(this);
	    this.onCallUserStreamRemovedHandler = this.onCallUserStreamRemoved.bind(this);
	    this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
	    this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
	    this.onCallUserScreenStateHandler = this.onCallUserScreenState.bind(this);
	    this.onCallUserRecordStateHandler = this.onCallUserRecordState.bind(this);
	    this.onCallUserFloorRequestHandler = this.onCallUserFloorRequest.bind(this);
	    this._onCallJoinHandler = this.onCallJoin.bind(this);
	    this.onCallLeaveHandler = this.onCallLeave.bind(this);
	    this.onCallDestroyHandler = this.onCallDestroy.bind(this);
	    this.onChatTextareaFocusHandler = this.onChatTextareaFocus.bind(this);
	    this.onChatTextareaBlurHandler = this.onChatTextareaBlur.bind(this);
	    this.onPreCallDestroyHandler = this.onPreCallDestroy.bind(this);
	    this.onPreCallUserStateChangedHandler = this.onPreCallUserStateChanged.bind(this);
	    this.waitingForCallStatus = false;
	    this.waitingForCallStatusTimeout = null;
	    this.callEventReceived = false;
	    this.callRecordState = BX.Call.View.RecordState.Stopped;
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
	    }).catch(function (error) {
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
	      this.floatingScreenShareWindow = new BX.Call.FloatingScreenShare({
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
	          }).catch(function (error) {
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
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.focus, this.onChatTextareaFocusHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.blur, this.onChatTextareaBlurHandler);
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
	        vuexBuilder: {
	          database: !im_lib_utils.Utils.browser.isIe(),
	          databaseName: 'imol/call',
	          databaseType: ui_vue_vuex.VuexBuilder.DatabaseType.localStorage,
	          models: [im_model.ConferenceModel.create()]
	        }
	      });
	      return new Promise(function (resolve, reject) {
	        _this3.controller.ready().then(function () {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "setModelData",
	    value: function setModelData() {
	      var _this4 = this;

	      this.controller.getStore().commit('application/set', {
	        dialog: {
	          chatId: this.getChatId(),
	          dialogId: this.getDialogId()
	        },
	        options: {
	          darkBackground: true
	        }
	      }); //set presenters ID list

	      var presentersIds = this.params.presenters.map(function (presenter) {
	        return presenter['id'];
	      });
	      this.controller.getStore().dispatch('conference/setBroadcastMode', {
	        broadcastMode: this.params.isBroadcast
	      });
	      this.controller.getStore().dispatch('conference/setPresenters', {
	        presenters: presentersIds
	      }); //set presenters info in users model

	      this.params.presenters.forEach(function (presenter) {
	        _this4.controller.getStore().dispatch('users/set', presenter);
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
	      var _this5 = this;

	      if (this.getStartupErrorCode()) {
	        this.setError(this.getStartupErrorCode());
	      }

	      return new Promise(function (resolve, reject) {
	        _this5.controller.createVue(_this5, {
	          el: _this5.rootNode,
	          data: function data() {
	            return {
	              dialogId: _this5.getDialogId()
	            };
	          },
	          template: "<bx-im-component-conference-public :dialogId=\"dialogId\"/>"
	        }).then(function (vue) {
	          _this5.template = vue;
	          resolve();
	        }).catch(function (error) {
	          return reject(error);
	        });
	      });
	    }
	  }, {
	    key: "initCallInterface",
	    value: function initCallInterface() {
	      var _this6 = this;

	      return new Promise(function (resolve, reject) {
	        _this6.callContainer = document.getElementById('bx-im-component-call-container');
	        var hiddenButtons = [];

	        if (_this6.isViewerMode()) {
	          hiddenButtons = ['camera', 'microphone', 'screen', 'record', 'floorRequest'];
	        }

	        if (!_this6.params.isIntranetOrExtranet) {
	          hiddenButtons.push('record');
	        }

	        _this6.callView = new BX.Call.View({
	          container: _this6.callContainer,
	          showChatButtons: true,
	          showUsersButton: true,
	          showShareButton: _this6.getFeatureState('screenSharing') !== ConferenceApplication.FeatureState.Disabled,
	          showRecordButton: _this6.getFeatureState('record') !== ConferenceApplication.FeatureState.Disabled,
	          userLimit: BX.Call.Util.getUserLimit(),
	          isIntranetOrExtranet: !!_this6.params.isIntranetOrExtranet,
	          language: _this6.params.language,
	          layout: im_lib_utils.Utils.device.isMobile() ? BX.Call.View.Layout.Mobile : BX.Call.View.Layout.Centered,
	          uiState: BX.Call.View.UiState.Preparing,
	          blockedButtons: ['camera', 'microphone', 'floorRequest', 'screen', 'record'],
	          localUserState: BX.Call.UserState.Idle,
	          hiddenTopButtons: !_this6.isBroadcast() || _this6.getBroadcastPresenters().length > 1 ? [] : ['grid'],
	          hiddenButtons: hiddenButtons,
	          broadcastingMode: _this6.isBroadcast(),
	          broadcastingPresenters: _this6.getBroadcastPresenters()
	        });

	        _this6.callView.subscribe(BX.Call.View.Event.onButtonClick, _this6.onCallButtonClick.bind(_this6));

	        _this6.callView.subscribe(BX.Call.View.Event.onReplaceCamera, _this6.onCallReplaceCamera.bind(_this6));

	        _this6.callView.subscribe(BX.Call.View.Event.onReplaceMicrophone, _this6.onCallReplaceMicrophone.bind(_this6));

	        _this6.callView.subscribe(BX.Call.View.Event.onReplaceSpeaker, _this6.onCallReplaceSpeaker.bind(_this6));

	        _this6.callView.subscribe(BX.Call.View.Event.onChangeHdVideo, _this6.onCallViewChangeHdVideo.bind(_this6));

	        _this6.callView.subscribe(BX.Call.View.Event.onChangeMicAutoParams, _this6.onCallViewChangeMicAutoParams.bind(_this6));

	        _this6.callView.subscribe(BX.Call.View.Event.onUserRename, _this6.onCallViewUserRename.bind(_this6));

	        _this6.callView.blockAddUser();

	        _this6.callView.blockHistoryButton();

	        if (!im_lib_utils.Utils.device.isMobile()) {
	          _this6.callView.show();
	        }

	        resolve();
	      }).catch(function (error) {
	        return reject(error);
	      });
	    }
	  }, {
	    key: "initUserComplete",
	    value: function initUserComplete() {
	      var _this7 = this;

	      return new Promise(function (resolve, reject) {
	        _this7.initUser().then(function () {
	          return _this7.startPageTagInterval();
	        }).then(function () {
	          return _this7.tryJoinExistingCall();
	        }).then(function () {
	          return _this7.initCall();
	        }).then(function () {
	          return _this7.initPullHandlers();
	        }).then(function () {
	          return _this7.subscribeToStoreChanges();
	        }).then(function () {
	          return _this7.initComplete();
	        }).then(function () {
	          return resolve;
	        }).catch(function (error) {
	          return reject(error);
	        });
	      });
	    }
	    /* endregion 01. Initialize methods */

	    /* region 02. initUserComplete methods */

	  }, {
	    key: "initUser",
	    value: function initUser() {
	      var _this8 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this8.getStartupErrorCode() || !_this8.getConference().common.passChecked) {
	          return reject();
	        }

	        if (_this8.params.userId > 0) {
	          _this8.controller.setUserId(_this8.params.userId);

	          if (_this8.params.isIntranetOrExtranet) {
	            _this8.switchToSessAuth();

	            _this8.controller.getStore().commit('conference/user', {
	              id: _this8.params.userId
	            });
	          } else {
	            var hashFromCookie = _this8.getUserHashCookie();

	            if (hashFromCookie) {
	              _this8.restClient.setAuthId(hashFromCookie);

	              _this8.restClient.setChatId(_this8.getChatId());

	              _this8.controller.getStore().commit('conference/user', {
	                id: _this8.params.userId,
	                hash: hashFromCookie
	              });

	              _this8.pullClient.start();
	            }
	          }

	          _this8.controller.getStore().commit('conference/common', {
	            inited: true
	          });

	          return resolve();
	        } else {
	          _this8.restClient.setAuthId('guest');

	          _this8.restClient.setChatId(_this8.getChatId());

	          if (typeof BX.SidePanel !== 'undefined') {
	            BX.SidePanel.Instance.disableAnchorBinding();
	          }

	          return _this8.restClient.callMethod('im.call.user.register', {
	            alias: _this8.params.alias,
	            user_hash: _this8.getUserHashCookie() || ''
	          }).then(function (result) {
	            BX.message['USER_ID'] = result.data().id;

	            _this8.controller.getStore().commit('conference/user', {
	              id: result.data().id,
	              hash: result.data().hash
	            });

	            _this8.controller.setUserId(result.data().id);

	            _this8.callView.setLocalUserId(result.data().id);

	            if (result.data().created) {
	              _this8.params.userCount++;
	            }

	            _this8.controller.getStore().commit('conference/common', {
	              inited: true
	            });

	            _this8.restClient.setAuthId(result.data().hash);

	            _this8.pullClient.start();

	            return resolve();
	          });
	        }
	      });
	    }
	  }, {
	    key: "startPageTagInterval",
	    value: function startPageTagInterval() {
	      var _this9 = this;

	      return new Promise(function (resolve) {
	        clearInterval(_this9.conferencePageTagInterval);
	        _this9.conferencePageTagInterval = setInterval(function () {
	          im_lib_localstorage.LocalStorage.set(_this9.params.siteId, _this9.params.userId, BX.CallEngine.getConferencePageTag(_this9.params.dialogId), "Y", 2);
	        }, 1000);
	        resolve();
	      });
	    }
	  }, {
	    key: "tryJoinExistingCall",
	    value: function tryJoinExistingCall() {
	      var _this10 = this;

	      this.restClient.callMethod("im.call.tryJoinCall", {
	        entityType: 'chat',
	        entityId: this.params.dialogId,
	        provider: BX.Call.Provider.Voximplant,
	        type: BX.Call.Type.Permanent
	      }).then(function (result) {
	        im_lib_logger.Logger.warn('tryJoinCall', result.data());

	        if (result.data().success) {
	          _this10.waitingForCallStatus = true;
	          _this10.waitingForCallStatusTimeout = setTimeout(function () {
	            _this10.waitingForCallStatus = false;

	            if (!_this10.callEventReceived) {
	              _this10.setConferenceStatus(false);
	            }

	            _this10.callEventReceived = false;
	          }, 5000);
	        } else {
	          _this10.setConferenceStatus(false);
	        }
	      });
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
	      var _this11 = this;

	      this.controller.getStore().subscribe(function (mutation, state) {
	        var payload = mutation.payload,
	            type = mutation.type;

	        if (type === 'users/update' && payload.fields.name) {
	          if (!_this11.callView) {
	            return false;
	          }

	          _this11.callView.updateUserData(babelHelpers.defineProperty({}, payload.id, {
	            name: payload.fields.name
	          }));
	        } else if (type === 'dialogues/set') {
	          if (payload[0].dialogId !== _this11.getDialogId()) {
	            return false;
	          }

	          if (!im_lib_utils.Utils.platform.isBitrixDesktop()) {
	            _this11.callView.setButtonCounter('chat', payload[0].counter);
	          }
	        } else if (type === 'dialogues/update') {
	          if (payload.dialogId !== _this11.getDialogId()) {
	            return false;
	          }

	          if (typeof payload.fields.counter === 'number' && _this11.callView) {
	            if (im_lib_utils.Utils.platform.isBitrixDesktop()) {
	              if (payload.actionName === "decreaseCounter" && !payload.dialogMuted && typeof payload.fields.previousCounter === 'number') {
	                var counter = payload.fields.counter;

	                if (_this11.getConference().common.messageCount) {
	                  counter = _this11.getConference().common.messageCount - (payload.fields.previousCounter - counter);

	                  if (counter < 0) {
	                    counter = 0;
	                  }
	                }

	                _this11.callView.setButtonCounter('chat', counter);
	              }
	            } else {
	              _this11.callView.setButtonCounter('chat', payload.fields.counter);
	            }
	          }

	          if (typeof payload.fields.name !== 'undefined') {
	            document.title = payload.fields.name.toString();
	          }
	        } else if (type === 'conference/common' && typeof payload.messageCount === 'number') {
	          if (_this11.callView) {
	            _this11.callView.setButtonCounter('chat', payload.messageCount);
	          }
	        } else if (type === 'conference/common' && typeof payload.userCount === 'number') {
	          if (_this11.callView) {
	            _this11.callView.setButtonCounter('users', payload.userCount);
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
	      var _this12 = this;

	      return new Promise(function (resolve, reject) {
	        BX.Call.Hardware.init().then(function () {
	          if (_this12.hardwareInited) {
	            resolve();
	            return true;
	          }

	          if (Object.values(BX.Call.Hardware.microphoneList).length === 0) {
	            _this12.setError(im_const.ConferenceErrorCode.missingMicrophone);
	          }

	          if (!_this12.isViewerMode()) {
	            _this12.callView.unblockButtons(["camera", "microphone"]);

	            _this12.callView.enableMediaSelection();
	          }

	          _this12.hardwareInited = true;
	          resolve();
	        }).catch(function (error) {
	          if (error === 'NO_WEBRTC' && _this12.isHttps()) {
	            _this12.setError(im_const.ConferenceErrorCode.unsupportedBrowser);
	          } else if (error === 'NO_WEBRTC' && !_this12.isHttps()) {
	            _this12.setError(im_const.ConferenceErrorCode.unsafeConnection);
	          }

	          im_lib_logger.Logger.error('Init hardware error', error);
	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "startCall",
	    value: function startCall(videoEnabled) {
	      var _this13 = this;

	      var viewerMode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var provider = BX.Call.Provider.Voximplant;

	      if (im_lib_utils.Utils.device.isMobile()) {
	        this.callView.show();
	        this.callView.setButtonCounter('chat', this.getDialogData().counter);
	        this.callView.setButtonCounter('users', this.getConference().common.userCount);
	      } else {
	        this.callView.setLayout(BX.Call.View.Layout.Grid);
	      }

	      this.callView.setUiState(BX.Call.View.UiState.Calling);

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

	      this.controller.getStore().commit('conference/startCall');
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
	        _this13.currentCall = e.call; //this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);

	        _this13.currentCall.useHdVideo(true);

	        if (BX.Call.Hardware.defaultMicrophone) {
	          _this13.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
	        }

	        if (BX.Call.Hardware.defaultCamera) {
	          _this13.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
	        }

	        if (!im_lib_utils.Utils.device.isMobile()) {
	          _this13.callView.setLayout(BX.Call.View.Layout.Grid);
	        }

	        _this13.callView.appendUsers(_this13.currentCall.getUsers());

	        BX.Call.Util.getUsers(_this13.currentCall.id, _this13.getCallUsers(true)).then(function (userData) {
	          _this13.callView.updateUserData(userData);
	        });

	        _this13.releasePreCall();

	        _this13.bindCallEvents();

	        if (_this13.callView.isMuted) {
	          _this13.currentCall.setMuted(true);
	        }

	        if (e.isNew) {
	          _this13.currentCall.setVideoEnabled(videoEnabled);

	          _this13.currentCall.inviteUsers();
	        } else {
	          _this13.currentCall.answer({
	            useVideo: videoEnabled,
	            joinAsViewer: viewerMode
	          });
	        }
	      }).catch(function (e) {
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
	      var _this14 = this;

	      var video = BX.prop.getBoolean(options, "video", false);
	      var joinAsViewer = BX.prop.getBoolean(options, "joinAsViewer", false);

	      if (im_lib_utils.Utils.device.isMobile()) {
	        this.callView.show();
	      } else {
	        this.callView.setLayout(BX.Call.View.Layout.Grid);
	      }

	      if (joinAsViewer) {
	        this.callView.setLocalUserDirection(BX.Call.EndpointDirection.RecvOnly);
	      } else {
	        this.callView.setLocalUserDirection(BX.Call.EndpointDirection.SendRecv);
	      }

	      this.callView.setUiState(BX.Call.View.UiState.Calling);
	      BX.CallEngine.getCallWithId(callId).then(function (result) {
	        _this14.currentCall = result.call;

	        _this14.releasePreCall();

	        _this14.bindCallEvents();

	        _this14.controller.getStore().commit('conference/startCall');

	        _this14.callView.appendUsers(_this14.currentCall.getUsers());

	        BX.Call.Util.getUsers(_this14.currentCall.id, _this14.getCallUsers(true)).then(function (userData) {
	          _this14.callView.updateUserData(userData);
	        });

	        if (!joinAsViewer) {
	          //this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);
	          _this14.currentCall.useHdVideo(true);

	          if (BX.Call.Hardware.defaultMicrophone) {
	            _this14.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
	          }

	          if (BX.Call.Hardware.defaultCamera) {
	            _this14.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
	          }

	          if (_this14.callView.isMuted) {
	            _this14.currentCall.setMuted(true);
	          }
	        }

	        _this14.currentCall.answer({
	          useVideo: !!video,
	          joinAsViewer: joinAsViewer
	        });
	      }).catch(function (error) {
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
	          browser: BX.Call.Util.getBrowserForStatistics(),
	          isMobile: BX.browser.IsMobile(),
	          isConference: true
	        };
	        this.removeCallEvents();
	        this.currentCall.hangup();
	      }

	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordStop();
	      }

	      this.callRecordState = BX.Call.View.RecordState.Stopped;

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

	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.focus, this.onChatTextareaFocusHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.blur, this.onChatTextareaBlurHandler);
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
	    key: "showMicMutedNotification",
	    value: function showMicMutedNotification() {
	      var _this15 = this;

	      if (this.mutePopup || !this.callView) {
	        return;
	      }

	      this.mutePopup = new BX.Call.MicMutedPopup({
	        bindElement: this.callView.buttons.microphone.elements.icon,
	        targetContainer: this.callView.container,
	        onClose: function onClose() {
	          _this15.allowMutePopup = false;

	          _this15.mutePopup.destroy();

	          _this15.mutePopup = null;
	        },
	        onUnmuteClick: function onUnmuteClick() {
	          _this15.onCallViewToggleMuteButtonClick({
	            muted: false
	          });

	          _this15.mutePopup.destroy();

	          _this15.mutePopup = null;
	        }
	      });
	      this.mutePopup.show();
	    }
	  }, {
	    key: "showWebScreenSharePopup",
	    value: function showWebScreenSharePopup() {
	      if (this.webScreenSharePopup) {
	        this.webScreenSharePopup.show();
	        return;
	      }

	      this.webScreenSharePopup = new BX.Call.WebScreenSharePopup({
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
	      var _this16 = this;

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

	      var userReadyToJoin = this.getConference().common.userReadyToJoin;

	      if (userReadyToJoin) {
	        var viewerMode = this.isViewerMode();
	        var videoEnabled = this.getConference().common.joinWithVideo;
	        im_lib_logger.Logger.warn('ready to join call after waiting', videoEnabled, viewerMode);
	        setTimeout(function () {
	          BX.Call.Hardware.init().then(function () {
	            if (viewerMode && _this16.preCall) {
	              _this16.joinCall(_this16.preCall.id, {
	                joinAsViewer: true
	              });
	            } else {
	              _this16.startCall(videoEnabled);
	            }
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
	    key: "renameGuest",
	    value: function renameGuest(newName) {
	      var _this17 = this;

	      this.callView.localUser.userModel.renameRequested = true;
	      this.setUserName(newName).then(function () {
	        _this17.callView.localUser.userModel.wasRenamed = true;
	        im_lib_logger.Logger.log('setting name to', newName);
	      }).catch(function (error) {
	        im_lib_logger.Logger.error('error setting name', error);
	      });
	    }
	  }, {
	    key: "renameGuestMobile",
	    value: function renameGuestMobile(newName) {
	      var _this18 = this;

	      this.setUserName(newName).then(function () {
	        im_lib_logger.Logger.log('setting mobile name to', newName);

	        if (_this18.callView.renameSlider) {
	          _this18.callView.renameSlider.close();
	        }
	      }).catch(function (error) {
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
	      if (event.data.recordState === BX.Call.View.RecordState.Started) {
	        if (this.getFeatureState('record') === ConferenceApplication.FeatureState.Limited) {
	          this.showFeatureLimitSlider('record');
	          return;
	        }

	        if (this.getFeatureState('record') === ConferenceApplication.FeatureState.Disabled) {
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
	      var _this19 = this;

	      var floorState = this.callView.getUserFloorRequestState(BX.CallEngine.getCurrentUserId());
	      var talkingState = this.callView.getUserTalking(BX.CallEngine.getCurrentUserId());
	      this.callView.setUserFloorRequestState(BX.CallEngine.getCurrentUserId(), !floorState);

	      if (this.currentCall) {
	        this.currentCall.requestFloor(!floorState);
	      }

	      clearTimeout(this.callViewFloorRequestTimeout);

	      if (talkingState && !floorState) {
	        this.callViewFloorRequestTimeout = setTimeout(function () {
	          if (_this19.currentCall) {
	            _this19.currentCall.requestFloor(false);
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
	      this.currentCall.addEventListener(BX.Call.Event.onUserCameraState, this.onCallUserCameraStateHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onUserVideoPaused, this.onCallUserVideoPausedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onStreamReceived, this.onCallUserStreamReceivedHandler);
	      this.currentCall.addEventListener(BX.Call.Event.onStreamRemoved, this.onCallUserStreamRemovedHandler);
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
	      this.currentCall.removeEventListener(BX.Call.Event.onUserCameraState, this.onCallUserCameraStateHandler);
	      this.currentCall.removeEventListener(BX.Call.Event.onUserVideoPaused, this.onCallUserVideoPausedHandler);
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
	      var _this20 = this;

	      this.callView.addUser(e.userId);
	      BX.Call.Util.getUsers(this.currentCall.id, [e.userId]).then(function (userData) {
	        _this20.callView.updateUserData(userData);
	      });
	    }
	  }, {
	    key: "onCallUserStateChanged",
	    value: function onCallUserStateChanged(e) {
	      this.callView.setUserState(e.userId, e.state);
	      /*if (e.direction)
	      {
	      	this.callView.setUserDirection(e.userId, e.direction);
	      }*/
	    }
	  }, {
	    key: "onCallUserMicrophoneState",
	    value: function onCallUserMicrophoneState(e) {
	      this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
	    }
	  }, {
	    key: "onCallUserCameraState",
	    value: function onCallUserCameraState(e) {
	      this.callView.setUserCameraState(e.userId, e.cameraState);
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
	      this.callView.setLocalStream(e.stream, e.tag == "main");
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
	    key: "onCallUserStreamReceived",
	    value: function onCallUserStreamReceived(e) {
	      if (this.callView) {
	        if ("stream" in e) {
	          this.callView.setStream(e.userId, e.stream);
	        }

	        if ("mediaRenderer" in e && e.mediaRenderer.kind === "audio") {
	          this.callView.setStream(e.userId, e.mediaRenderer.stream);
	        }

	        if ("mediaRenderer" in e && (e.mediaRenderer.kind === "video" || e.mediaRenderer.kind === "sharing")) {
	          this.callView.setVideoRenderer(e.userId, e.mediaRenderer);
	        }
	      }
	    }
	  }, {
	    key: "onCallUserStreamRemoved",
	    value: function onCallUserStreamRemoved(e) {
	      if ("mediaRenderer" in e && (e.mediaRenderer.kind === "video" || e.mediaRenderer.kind === "sharing")) {
	        this.callView.setVideoRenderer(e.userId, null);
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

	      if (!this.isViewerMode()) {
	        this.callView.unblockButtons(['camera', 'floorRequest', 'screen', 'record']);
	      }

	      this.callView.setUiState(BX.Call.View.UiState.Connected);
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
	      var _this21 = this;

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

	        _this21.toggleChat();
	      });
	      return true;
	    }
	  }, {
	    key: "onChatTextareaFocus",
	    value: function onChatTextareaFocus(e) {
	      this.callView.setHotKeyActive('microphoneSpace', false);
	    }
	  }, {
	    key: "onChatTextareaBlur",
	    value: function onChatTextareaBlur(e) {
	      this.callView.setHotKeyActive('microphoneSpace', true);
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
	      var currentError = this.getConference().common.error; // if user kicked from call - dont show him end of call form

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
	    /* endregion 02. Store actions */

	    /* region 03. Rest actions */

	  }, {
	    key: "setUserName",
	    value: function setUserName(name) {
	      var _this22 = this;

	      return new Promise(function (resolve, reject) {
	        _this22.restClient.callMethod('im.call.user.update', {
	          name: name,
	          chat_id: _this22.getChatId()
	        }).then(function () {
	          resolve();
	        }).catch(function (error) {
	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "checkPassword",
	    value: function checkPassword(password) {
	      var _this23 = this;

	      return new Promise(function (resolve, reject) {
	        _this23.restClient.callMethod('im.videoconf.password.check', {
	          password: password,
	          alias: _this23.params.alias
	        }).then(function (result) {
	          if (result.data() === true) {
	            _this23.restClient.setPassword(password);

	            _this23.controller.getStore().commit('conference/common', {
	              passChecked: true
	            });

	            _this23.initUserComplete();

	            resolve();
	          } else {
	            reject();
	          }
	        });
	      });
	    }
	  }, {
	    key: "changeLink",
	    value: function changeLink() {
	      var _this24 = this;

	      return new Promise(function (resolve, reject) {
	        _this24.restClient.callMethod('im.videoconf.share.change', {
	          dialog_id: _this24.getDialogId()
	        }).then(function () {
	          resolve();
	        }).catch(function (error) {
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
	    }
	    /* endregion 03. Utils */

	  }]);
	  return ConferenceApplication;
	}();

	ConferenceApplication.FeatureState = {
	  Enabled: 'enabled',
	  Disabled: 'disabled',
	  Limited: 'limited'
	};

	exports.ConferenceApplication = ConferenceApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX,BX,BX.Messenger.Application,BX.Messenger,BX.Messenger.Model,BX.Messenger,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Const,BX,BX.UI,BX,BX,BX,BX,BX,BX,BX,BX.Event,BX,BX.Messenger.Provider.Pull,BX,BX.Messenger.Lib));
//# sourceMappingURL=conference.bundle.js.map
