this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_lib_localstorage,im_call,im_debug,im_lib_clipboard,ui_notification,ui_buttons,im_provider_pull,main_core,promise,pull_client,rest_client,im_lib_utils,ui_vue,im_component_dialog,im_component_call,pull_component_status,im_const,im_lib_cookie,im_model,ui_vue_vuex,im_controller) {
	'use strict';

	var RestAuth = Object.freeze({
	  guest: 'guest'
	});
	var CallRestClient =
	/*#__PURE__*/
	function () {
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
	 * Application External Call
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	ui_vue.Vue.component('bx-im-application-call', {
	  data: function data() {
	    return {
	      downloadAppArticleCode: 11387752
	    };
	  },
	  props: {
	    chatId: {
	      default: 0
	    },
	    dialogId: {
	      default: '0'
	    },
	    startupErrorCode: {
	      default: ''
	    }
	  },
	  methods: {
	    redirectToAuthorize: function redirectToAuthorize() {
	      location.href = location.origin + '/auth/?backurl=' + location.pathname;
	    },
	    continueAsGuest: function continueAsGuest() {
	      im_lib_cookie.Cookie.set(null, 'VIDEOCONF_GUEST', '', {
	        path: '/'
	      });
	      location.reload(true);
	    },
	    getBxLink: function getBxLink() {
	      return "bx://videoconf/code/".concat(this.$root.$bitrixApplication.getAlias());
	    },
	    getErrorFromCode: function getErrorFromCode() {
	      if (this.startupErrorCode) {
	        if (this.startupErrorCode === im_const.CallApplicationErrorCode.bitrix24only) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_B24_ONLY'];
	        } else if (this.startupErrorCode === im_const.CallApplicationErrorCode.detectIntranetUser) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_PLEASE_LOG_IN'];
	        } else if (this.startupErrorCode === im_const.CallApplicationErrorCode.userLimitReached) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_USER_LIMIT'];
	        } else if (this.startupErrorCode === im_const.CallApplicationErrorCode.kickedFromCall) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_KICKED'];
	        }
	      } else if (this.callApplication.common.componentError) {
	        if (this.callApplication.common.componentError === im_const.CallApplicationErrorCode.kickedFromCall) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_KICKED'];
	        } else if (this.callApplication.common.componentError === im_const.CallApplicationErrorCode.unsupportedBrowser) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_UNSUPPORTED_BROWSER'];
	        } else if (this.callApplication.common.componentError === im_const.CallApplicationErrorCode.missingMicrophone) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_NO_MIC'];
	        } else if (this.callApplication.common.componentError === im_const.CallApplicationErrorCode.unsafeConnection) {
	          return this.localize['BX_IM_COMPONENT_CALL_ERROR_NO_HTTPS'];
	        }
	      }
	    },
	    openHelpArticle: function openHelpArticle() {
	      if (BX.Helper) {
	        BX.Helper.show("redirect=detail&code=" + this.downloadAppArticleCode);
	      }
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    authorizeButtonClasses: function authorizeButtonClasses() {
	      return ['ui-btn', 'ui-btn-sm', 'ui-btn-success-dark', 'ui-btn-no-caps', 'bx-im-application-call-button-authorize'];
	    },
	    continueAsGuestButtonClasses: function continueAsGuestButtonClasses() {
	      return ['ui-btn', 'ui-btn-sm', 'ui-btn-no-caps', 'bx-im-application-call-button-as-guest'];
	    },
	    isIntranetUserError: function isIntranetUserError() {
	      return this.startupErrorCode === im_const.CallApplicationErrorCode.detectIntranetUser;
	    },
	    isUnsupportedBrowserError: function isUnsupportedBrowserError() {
	      return this.callApplication.common.componentError === im_const.CallApplicationErrorCode.unsupportedBrowser;
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_', this.$root.$bitrixMessages);
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    callApplication: function callApplication(state) {
	      return state.callApplication;
	    },
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  template: "\n\t\t<div class=\"bx-im-application-call\">\n\t\t\t<template v-if=\"startupErrorCode || callApplication.common.componentError\">\n\t\t\t\t<template v-if=\"isIntranetUserError\">\n\t\t\t\t\t<div class=\"bx-im-application-call-error-message\">\n\t\t\t\t\t\t<div>{{ getErrorFromCode() }}</div>\n\t\t\t\t\t\t<div class=\"bx-im-application-call-error-message-buttons\">\n\t\t\t\t\t\t\t<button @click=\"redirectToAuthorize\" :class=\"authorizeButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AUTHORIZE'] }}</button>\n\t\t\t\t\t\t\t<button @click=\"continueAsGuest\" :class=\"continueAsGuestButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AS_GUEST'] }}</button>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<template v-else-if=\"isUnsupportedBrowserError\">\n\t\t\t\t\t<div class=\"bx-im-application-call-error-message\">\n\t\t\t\t\t\t<div>{{ getErrorFromCode() }}</div>\n\t\t\t\t\t\t<div class=\"bx-im-application-call-error-message-links\">\n\t\t\t\t\t\t\t<div class=\"bx-im-application-call-error-message-links-link\">\n\t\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_OPEN_APP'] }} \n\t\t\t\t\t\t\t\t<a :href=\"getBxLink()\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_OPEN_APP_LINK'] }}</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"bx-im-application-call-error-message-links-link\">\n\t\t\t\t\t\t\t\t{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_DOWNLOAD_APP'] }} -\n\t\t\t\t\t\t\t\t<a href=\"\" @click.prevent=\"openHelpArticle\" target=\"_blank\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_DOWNLOAD_APP_LINK'] }}</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<template v-else>\n\t\t\t\t\t<div class=\"bx-im-application-call-error-message\">{{ getErrorFromCode() }}</div>\n\t\t\t\t</template>\n\t\t\t</template>\n\t\t\t<template v-else>\n\t\t\t\t<bx-pull-component-status/>\n\t\t\t\t<bx-im-component-call :chatId=\"chatId\" dialogId=\"dialogId\" />\n\t\t\t</template>\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix Im mobile
	 * Dialog application
	 *
	 * @package bitrix
	 * @subpackage mobile
	 * @copyright 2001-2020 Bitrix
	 */
	var CallApplication =
	/*#__PURE__*/
	function () {
	  /* region 01. Initialize */
	  function CallApplication() {
	    var _this = this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, CallApplication);
	    this.inited = false;
	    this.dialogInited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    console.trace(params);
	    this.params.userId = this.params.userId ? parseInt(this.params.userId) : 0;
	    this.params.siteId = this.params.siteId || '';
	    this.params.chatId = this.params.chatId ? parseInt(this.params.chatId) : 0;
	    this.params.dialogId = this.params.chatId ? 'chat' + this.params.chatId.toString() : '0';
	    this.messagesQueue = [];
	    this.template = null;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.event = new ui_vue.VueVendorV2();
	    this.callContainer = null;
	    this.callView = null;
	    this.preCall = null;
	    this.currentCall = null;
	    this.useVideo = true;
	    this.localVideoStream = null;
	    this.selectedCameraId = "";
	    this.selectedMicrophoneId = "";
	    this.localVideoTimeout = null;
	    this.conferencePageTagInterval = null;
	    this.onCallUserInvitedHandler = this.onCallUserInvited.bind(this);
	    this.onCallUserStateChangedHandler = this.onCallUserStateChanged.bind(this);
	    this.onCallUserMicrophoneStateHandler = this.onCallUserMicrophoneState.bind(this);
	    this.onCallLocalMediaReceivedHandler = this.onCallLocalMediaReceived.bind(this);
	    this.onCallUserStreamReceivedHandler = this.onCallUserStreamReceived.bind(this);
	    this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
	    this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
	    this.onCallLeaveHandler = this.onCallLeave.bind(this);
	    this.onCallDestroyHandler = this.onCallDestroy.bind(this);
	    this.onPreCallDestroyHandler = this.onPreCallDestroy.bind(this);
	    this.onPreCallUserStateChangedHandler = this.onPreCallUserStateChanged.bind(this);
	    this.initRestClient().then(function () {
	      return _this.subscribePreCallChanges();
	    }).then(function () {
	      return _this.initPullClient();
	    }).then(function () {
	      return _this.initCore();
	    }).then(function () {
	      return _this.initComponent();
	    }).then(function () {
	      return _this.initUser();
	    }).then(function () {
	      return _this.startPageTagInterval();
	    }).then(function () {
	      return _this.tryJoinExistingCall();
	    }).then(function () {
	      return _this.initCall();
	    }).then(function () {
	      return _this.initPullHandlers();
	    }).then(function () {
	      return _this.subscribeToStoreChanges();
	    }).then(function () {
	      return _this.initComplete();
	    });
	  }

	  babelHelpers.createClass(CallApplication, [{
	    key: "initRestClient",
	    value: function initRestClient() {
	      console.log('1. initRestClient');
	      this.restClient = new CallRestClient({
	        endpoint: this.getHost() + '/rest'
	      });
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initPullClient",
	    value: function initPullClient() {
	      console.log('2. initPullClient');

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
	      var _this2 = this;

	      console.log('3. initCore');
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
	        _this2.controller.ready().then(function () {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "initComponent",
	    value: function initComponent() {
	      var _this3 = this;

	      console.log('4. initComponent');
	      this.controller.getStore().commit('application/set', {
	        dialog: {
	          chatId: this.getChatId(),
	          dialogId: this.getDialogId()
	        }
	      });
	      return this.controller.createVue(this, {
	        el: this.rootNode,
	        data: function data() {
	          return {
	            chatId: _this3.getChatId(),
	            dialogId: _this3.getDialogId(),
	            startupErrorCode: _this3.getStartupErrorCode()
	          };
	        },
	        template: "<bx-im-application-call :chatId=\"chatId\" :dialogId=\"dialogId\" :startupErrorCode=\"startupErrorCode\"/>"
	      }).then(function (vue) {
	        _this3.template = vue;
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "initUser",
	    value: function initUser() {
	      var _this4 = this;

	      var userWasKicked = im_lib_localstorage.LocalStorage.get(this.controller.getSiteId(), 0, "conf".concat(this.params.alias));

	      if (userWasKicked) {
	        this.params.startupErrorCode = im_const.CallApplicationErrorCode.kickedFromCall;
	      }

	      return new Promise(function (resolve, reject) {
	        if (_this4.getStartupErrorCode()) {
	          return resolve();
	        }

	        console.log('5. initUser');

	        if (_this4.params.userId > 0) {
	          _this4.controller.setUserId(_this4.params.userId);

	          if (_this4.params.isIntranetOrExtranet) {
	            _this4.switchToSessAuth();

	            _this4.controller.getStore().commit('callApplication/user', {
	              id: _this4.params.userId
	            });
	          } else {
	            var hashFromCookie = _this4.getUserHashCookie();

	            if (hashFromCookie) {
	              _this4.restClient.setAuthId(hashFromCookie);

	              _this4.restClient.setChatId(_this4.getChatId());

	              _this4.controller.getStore().commit('callApplication/user', {
	                id: _this4.params.userId,
	                hash: hashFromCookie
	              });

	              _this4.pullClient.start();
	            }
	          }

	          _this4.controller.getStore().commit('callApplication/common', {
	            inited: true
	          });

	          return resolve();
	        } else {
	          _this4.restClient.setAuthId('guest');

	          _this4.restClient.setChatId(_this4.getChatId());

	          if (typeof BX.SidePanel !== 'undefined') {
	            BX.SidePanel.Instance.disableAnchorBinding();
	          }

	          return _this4.restClient.callMethod('im.call.user.register', {
	            alias: _this4.params.alias,
	            user_hash: _this4.getUserHashCookie() || ''
	          }).then(function (result) {
	            _this4.controller.getStore().commit('callApplication/user', {
	              id: result.data().id,
	              hash: result.data().hash
	            });

	            _this4.controller.setUserId(result.data().id);

	            if (result.data().created) {
	              _this4.params.userCount++;
	            }

	            _this4.controller.getStore().commit('callApplication/common', {
	              inited: true
	            });

	            _this4.restClient.setAuthId(result.data().hash);

	            _this4.pullClient.start();

	            return resolve();
	          });
	        }
	      });
	    }
	  }, {
	    key: "startPageTagInterval",
	    value: function startPageTagInterval() {
	      var _this5 = this;

	      return new Promise(function (resolve) {
	        clearInterval(_this5.conferencePageTagInterval);
	        _this5.conferencePageTagInterval = setInterval(function () {
	          im_lib_localstorage.LocalStorage.set(_this5.params.siteId, _this5.params.userId, BX.CallEngine.getConferencePageTag(_this5.params.dialogId), "Y", 2);
	        }, 1000);
	        resolve();
	      });
	    }
	  }, {
	    key: "tryJoinExistingCall",
	    value: function tryJoinExistingCall() {
	      this.restClient.callMethod("im.call.tryJoinCall", {
	        entityType: 'chat',
	        entityId: this.params.dialogId,
	        provider: BX.Call.Provider.Voximplant,
	        type: BX.Call.Type.Permanent
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
	      if (this.preCall || this.currentCall) {
	        return;
	      }

	      var call = e.call;

	      if (call.associatedEntity.type === 'chat' && call.associatedEntity.id === this.params.dialogId) {
	        this.preCall = e.call;
	        this.updatePreCallCounter();
	        this.preCall.addEventListener(BX.Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
	        this.preCall.addEventListener(BX.Call.Event.onDestroy, this.onPreCallDestroyHandler);
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
	      var _this6 = this;

	      BX.CallEngine.setRestClient(this.restClient);
	      BX.CallEngine.setPullClient(this.pullClient);
	      BX.CallEngine.setCurrentUserId(this.controller.getUserId());
	      this.callContainer = document.getElementById('bx-im-component-call-container');
	      return new Promise(function (resolve, reject) {
	        BX.Call.Hardware.init().then(function () {
	          if (Object.values(BX.Call.Hardware.microphoneList).length === 0) {
	            _this6.setComponentError(im_const.CallApplicationErrorCode.missingMicrophone);
	          }

	          _this6.callView = new BX.Call.View({
	            container: _this6.callContainer,
	            showChatButtons: true,
	            showShareButton: true,
	            userLimit: BX.Call.Util.getUserLimit(),
	            language: _this6.params.language,
	            //layout: BX.Call.View.Layout.Grid,
	            uiState: BX.Call.View.UiState.Preparing
	          });

	          _this6.callView.setCallback('onButtonClick', _this6.onCallButtonClick.bind(_this6));

	          _this6.callView.disableAddUser();

	          _this6.callView.disableHistoryButton();

	          _this6.callView.show();

	          return _this6.getLocalVideo();
	        }).catch(function (error) {
	          if (error === 'NO_WEBRTC' && _this6.isHttps()) {
	            _this6.setComponentError(im_const.CallApplicationErrorCode.unsupportedBrowser);
	          } else if (error === 'NO_WEBRTC' && !_this6.isHttps()) {
	            _this6.setComponentError(im_const.CallApplicationErrorCode.unsafeConnection);
	          }
	        }).then(function (stream) {
	          if (stream) {
	            _this6.callView.setLocalStream(stream, true);
	          }

	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "subscribeToStoreChanges",
	    value: function subscribeToStoreChanges() {
	      var _this7 = this;

	      this.controller.getStore().subscribe(function (mutation, state) {
	        var payload = mutation.payload,
	            type = mutation.type;

	        if (type === 'users/update' && payload.fields.name) {
	          if (_this7.callView) {
	            _this7.callView.updateUserData(babelHelpers.defineProperty({}, payload.id, {
	              name: payload.fields.name
	            }));
	          }
	        } else if (type === 'dialogues/update' && typeof payload.fields.counter === 'number') {
	          if (_this7.callView) {
	            _this7.callView.setButtonCounter('chat', payload.fields.counter);
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
	      this.inited = true;
	      this.initPromise.resolve(this);
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
	    key: "getLocalVideo",
	    value: function getLocalVideo() {
	      var _this8 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this8.localVideoStream) {
	          return resolve(_this8.localVideoStream);
	        }

	        navigator.mediaDevices.getUserMedia({
	          video: {
	            width: {
	              ideal: BX.Call.Hardware.preferHdQuality ? 1280 : 640
	            },
	            height: {
	              ideal: BX.Call.Hardware.preferHdQuality ? 720 : 360
	            }
	          }
	        }).then(function (stream) {
	          _this8.localVideoStream = stream;
	          clearTimeout(_this8.localVideoTimeout);

	          _this8.controller.getStore().commit('callApplication/common', {
	            callError: ""
	          });

	          if (BX.Call.Util.hasHdVideo(_this8.localVideoStream)) {
	            // restore possibly cleared in localVideoTimeout flag
	            BX.Call.Hardware.preferHdQuality = true;
	          }

	          resolve(stream);
	        }).catch(function (error) {
	          clearTimeout(_this8.localVideoTimeout);

	          if (error.name === "OverconstrainedError") {
	            BX.Call.Hardware.preferHdQuality = false;
	          }

	          console.error(typeof error === "string" ? error : error.name);

	          _this8.controller.getStore().commit('callApplication/common', {
	            callError: typeof error === "string" ? error : error.name
	          });

	          reject(error);
	        });
	        _this8.localVideoTimeout = setTimeout(function () {
	          BX.Call.Hardware.preferHdQuality = false;

	          _this8.controller.getStore().commit('callApplication/common', {
	            callError: im_const.CallErrorCode.noSignalFromCamera
	          });
	        }, 5000);
	      });
	    }
	  }, {
	    key: "stopLocalVideo",
	    value: function stopLocalVideo() {
	      if (!this.localVideoStream) {
	        return;
	      }

	      this.localVideoStream.getTracks().forEach(function (tr) {
	        return tr.stop();
	      });
	      this.localVideoStream = null;
	    }
	  }, {
	    key: "restart",
	    value: function restart() {
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

	      this.initCall();
	      this.controller.getStore().commit('callApplication/returnToPreparation');
	    }
	    /* endregion 01. Initialize */

	    /* region 02. Methods */

	    /* region 01. Call methods */

	  }, {
	    key: "startCall",
	    value: function startCall() {
	      var _this9 = this;

	      var provider = BX.Call.Provider.Voximplant;
	      BX.Call.Engine.getInstance().createCall({
	        type: BX.Call.Type.Permanent,
	        entityType: 'chat',
	        entityId: this.getDialogId(),
	        provider: provider,
	        videoEnabled: true,
	        enableMicAutoParameters: BX.Call.Hardware.enableMicAutoParameters,
	        joinExisting: true
	      }).then(function (e) {
	        console.warn('call created', e);
	        _this9.currentCall = e.call;

	        _this9.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);

	        if (BX.Call.Hardware.defaultMicrophone) {
	          _this9.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
	        }

	        if (BX.Call.Hardware.defaultCamera) {
	          _this9.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
	        }

	        _this9.callView.setUiState(BX.Call.View.UiState.Calling);

	        _this9.callView.setLayout(BX.Call.View.Layout.Grid);

	        _this9.callView.appendUsers(_this9.currentCall.getUsers());

	        BX.Call.Util.getUsers(_this9.currentCall.id, _this9.getCallUsers(true)).then(function (userData) {
	          _this9.callView.updateUserData(userData);
	        });

	        _this9.bindCallEvents();

	        if (e.isNew) {
	          _this9.currentCall.setVideoEnabled(_this9.useVideo);

	          _this9.currentCall.inviteUsers();
	        } else {
	          _this9.currentCall.answer({
	            useVideo: _this9.useVideo
	          });
	        }
	      }).catch(function (e) {
	        console.warn('creating call error', e);
	      });
	      this.controller.getStore().commit('callApplication/startCall');
	    }
	  }, {
	    key: "endCall",
	    value: function endCall() {
	      if (this.currentCall) {
	        this.removeCallEvents();
	        this.currentCall.hangup();
	      }

	      this.controller.getStore().commit('callApplication/endCall');
	      this.restart();
	      window.close();
	    }
	  }, {
	    key: "kickFromCall",
	    value: function kickFromCall() {
	      this.setComponentError(im_const.CallApplicationErrorCode.kickedFromCall);
	      this.pullClient.disconnect();
	      this.endCall();
	      im_lib_localstorage.LocalStorage.set(this.controller.getSiteId(), 0, "conf".concat(this.params.alias), true);
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
	    key: "onCallButtonClick",
	    value: function onCallButtonClick(event) {
	      var buttonName = event.buttonName;
	      console.warn('Button clicked!', buttonName);
	      var handlers = {
	        hangup: this.onCallViewHangupButtonClick.bind(this),
	        close: this.onCallViewCloseButtonClick.bind(this),
	        //inviteUser: this.onCallViewInviteUserButtonClick.bind(this),
	        toggleMute: this.onCallViewToggleMuteButtonClick.bind(this),
	        toggleScreenSharing: this.onCallViewToggleScreenSharingButtonClick.bind(this),
	        toggleVideo: this.onCallViewToggleVideoButtonClick.bind(this),
	        showChat: this.onCallViewShowChatButtonClick.bind(this),
	        share: this.onCallViewShareButtonClick.bind(this),
	        fullscreen: this.onCallViewFullScreenButtonClick.bind(this)
	      };

	      if (handlers[buttonName]) {
	        handlers[buttonName](event);
	      } else {
	        console.error('Button handler not found!', buttonName);
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
	    value: function onCallViewToggleMuteButtonClick(e) {
	      if (this.currentCall) {
	        this.currentCall.setMuted(e.muted);
	      }

	      this.callView.setMuted(e.muted);
	    }
	  }, {
	    key: "onCallViewToggleScreenSharingButtonClick",
	    value: function onCallViewToggleScreenSharingButtonClick() {
	      if (this.currentCall.isScreenSharingStarted()) {
	        this.currentCall.stopScreenSharing();
	      } else {
	        this.callView.releaseLocalMedia();
	        this.currentCall.startScreenSharing();
	      }
	    }
	  }, {
	    key: "onCallViewToggleVideoButtonClick",
	    value: function onCallViewToggleVideoButtonClick(e) {
	      var _this10 = this;

	      this.useVideo = e.video;

	      if (!this.useVideo) {
	        this.callView.releaseLocalMedia();
	        this.stopLocalVideo();
	      }

	      if (this.currentCall) {
	        this.currentCall.setVideoEnabled(e.video);
	      } else {
	        if (this.useVideo) {
	          this.getLocalVideo().then(function (stream) {
	            return _this10.callView.setLocalStream(stream, true);
	          });
	        } else {
	          this.callView.setLocalStream(new MediaStream());
	        }
	      }
	    }
	  }, {
	    key: "onCallViewShareButtonClick",
	    value: function onCallViewShareButtonClick() {
	      BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('BX_IM_VIDEOCONF_LINK_COPY_DONE'),
	        autoHideDelay: 4000
	      });
	      im_lib_clipboard.Clipboard.copy(this.getDialogData().public.link);
	    }
	  }, {
	    key: "onCallViewFullScreenButtonClick",
	    value: function onCallViewFullScreenButtonClick() {
	      this.callView.toggleFullScreen();
	    }
	  }, {
	    key: "onCallViewShowChatButtonClick",
	    value: function onCallViewShowChatButtonClick() {
	      this.toggleChat();
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
	      this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler); //this.currentCall.addEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
	      //this.currentCall.addEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
	      //this.currentCall.addEventListener(BX.Call.Event.onJoin, this._onCallJoinHandler);

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
	      this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler); //this.currentCall.removeEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
	      //this.currentCall.removeEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);

	      this.currentCall.removeEventListener(BX.Call.Event.onLeave, this.onCallLeaveHandler);
	    }
	  }, {
	    key: "onCallUserInvited",
	    value: function onCallUserInvited(e) {
	      var _this11 = this;

	      this.callView.addUser(e.userId);
	      BX.Call.Util.getUsers(this.currentCall.id, [e.userId]).then(function (userData) {
	        _this11.callView.updateUserData(userData);
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
	      this.callView.setLocalStream(e.stream, e.tag == "main");
	      this.callView.setButtonActive("screen", e.tag == "screen");

	      if (e.tag == "screen") {
	        this.callView.disableSwitchCamera();
	        this.callView.updateButtons();
	      } else {
	        if (!this.currentCall.callFromMobile) {
	          this.callView.enableSwitchCamera();
	          this.callView.updateButtons();
	        }
	      }
	    }
	  }, {
	    key: "onCallUserStreamReceived",
	    value: function onCallUserStreamReceived(e) {
	      this.callView.setStream(e.userId, e.stream);
	    }
	  }, {
	    key: "onCallUserVoiceStarted",
	    value: function onCallUserVoiceStarted(e) {
	      this.callView.setUserTalking(e.userId, true);
	    }
	  }, {
	    key: "onCallUserVoiceStopped",
	    value: function onCallUserVoiceStopped(e) {
	      this.callView.setUserTalking(e.userId, false);
	    }
	  }, {
	    key: "onCallLeave",
	    value: function onCallLeave(e) {
	      this.restart();
	    }
	  }, {
	    key: "onCallDestroy",
	    value: function onCallDestroy(e) {
	      this.currentCall = null;
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
	    /* endregion 01. Call methods */

	    /* region 02. Component methods */

	  }, {
	    key: "setCallError",
	    value: function setCallError(errorCode) {
	      this.controller.getStore().commit('callApplication/setCallError', {
	        errorCode: errorCode
	      });
	    }
	  }, {
	    key: "setComponentError",
	    value: function setComponentError(errorCode) {
	      this.controller.getStore().commit('callApplication/setComponentError', {
	        errorCode: errorCode
	      });
	    }
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
	    key: "setUserName",
	    value: function setUserName(name) {
	      var _this12 = this;

	      this.restClient.callMethod('im.call.user.update', {
	        name: name,
	        chat_id: this.getChatId()
	      }).then(function () {
	        _this12.template.isSettingNewName = false;
	      });
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
	      var _this13 = this;

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

	        _this13.toggleChat();
	      });
	    }
	  }, {
	    key: "addMessage",
	    value: function addMessage() {
	      var _this14 = this;

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
	        _this14.messagesQueue.push({
	          id: messageId,
	          text: text,
	          file: file,
	          sending: false
	        });

	        _this14.processSendMessages();
	      });
	      return true;
	    }
	  }, {
	    key: "processSendMessages",
	    value: function processSendMessages() {
	      var _this15 = this;

	      this.messagesQueue.filter(function (element) {
	        return !element.sending;
	      }).forEach(function (element) {
	        element.sending = true;

	        if (element.file) {
	          _this15.sendMessageWithFile(element);
	        } else {
	          _this15.sendMessage(element);
	        }
	      });
	      return true;
	    }
	  }, {
	    key: "sendMessage",
	    value: function sendMessage(message) {
	      var _this16 = this;

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
	        _this16.controller.getStore().dispatch('messages/update', {
	          id: message.id,
	          chatId: message.chatId,
	          fields: {
	            id: response.data(),
	            sending: false,
	            error: false
	          }
	        }).then(function () {
	          _this16.controller.getStore().dispatch('messages/actionFinish', {
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
	      var _this17 = this;

	      this.controller.application.stopWriting();
	      var fileType = this.controller.getStore().getters['files/get'](this.getChatId(), message.file.id, true).type;
	      var diskFolderId = this.getDiskFolderId();
	      var query = {};

	      if (diskFolderId) {
	        query[im_const.RestMethod.imDiskFileUpload] = [im_const.RestMethod.imDiskFileUpload, {
	          id: diskFolderId,
	          data: {
	            NAME: message.file.source.files[0].name
	          },
	          fileContent: message.file.source,
	          generateUniqueName: true
	        }];
	      } else {
	        query[im_const.RestMethod.imDiskFolderGet] = [im_const.RestMethod.imDiskFolderGet, {
	          chat_id: this.getChatId()
	        }];
	        query[im_const.RestMethod.imDiskFileUpload] = [im_const.RestMethod.imDiskFileUpload, {
	          id: '$result[' + im_const.RestMethod.imDiskFolderGet + '][ID]',
	          data: {
	            NAME: message.file.source.files[0].name
	          },
	          fileContent: message.file.source,
	          generateUniqueName: true
	        }];
	      }

	      this.controller.restClient.callBatch(query, function (response) {
	        if (!response) {
	          _this17.requestDataSend = false;
	          console.warn('EMPTY_RESPONSE', 'Server returned an empty response. [1]');

	          _this17.fileError(_this17.getChatId, message.file.id, message.id);

	          return false;
	        }

	        if (!diskFolderId) {
	          var diskFolderGet = response[im_const.RestMethodHandler.imDiskFolderGet];

	          if (diskFolderGet && diskFolderGet.error()) {
	            console.warn(diskFolderGet.error().ex.error, diskFolderGet.error().ex.error_description);

	            _this17.fileError(_this17.getChatId(), message.file.id, message.id);

	            return false;
	          } //		this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, diskFolderGet);


	          _this17.controller.getStore().commit('application/set', {
	            dialog: {
	              diskFolderId: diskFolderGet.ID
	            }
	          });
	        }

	        var diskId = 0;
	        var diskFileUpload = response[im_const.RestMethod.imDiskFileUpload];

	        if (diskFileUpload) {
	          var result = diskFileUpload.data();

	          if (diskFileUpload.error()) {
	            console.warn(diskFileUpload.error().ex.error, diskFileUpload.error().ex.error_description);

	            _this17.fileError(_this17.getChatId(), message.file.id, message.id);

	            return false;
	          } else if (!result) {
	            console.warn('EMPTY_RESPONSE', 'Server returned an empty response. [2]');

	            _this17.fileError(_this17.getChatId(), message.file.id, message.id);

	            return false;
	          }

	          diskId = result.ID;
	        } else {
	          console.warn('EMPTY_RESPONSE', 'Server returned an empty response. [3]');

	          _this17.fileError(_this17.getChatId(), message.file.id, message.id);

	          return false;
	        }

	        message.chatId = _this17.getChatId();

	        _this17.controller.getStore().dispatch('files/update', {
	          chatId: message.chatId,
	          id: message.file.id,
	          fields: {
	            status: im_const.FileStatus.wait,
	            progress: 95
	          }
	        });

	        _this17.fileCommit({
	          chatId: message.chatId,
	          uploadId: diskId,
	          messageText: message.text,
	          messageId: message.id,
	          fileId: message.file.id,
	          fileType: fileType
	        }, message);
	      }, false, function (xhr) {
	        message.xhr = xhr;
	      });
	    }
	  }, {
	    key: "uploadFile",
	    value: function uploadFile(fileInput) {
	      var _this18 = this;

	      if (!fileInput) {
	        return false;
	      }

	      console.warn('addFile', fileInput.files[0].name, fileInput.files[0].size, fileInput.files[0]);
	      var file = fileInput.files[0];
	      var fileType = 'file';

	      if (file.type.toString().startsWith('image')) {
	        fileType = 'image';
	      } //if (!this.controller.application.isUnreadMessagesLoaded())
	      //{
	      //	this.addMessage('', { id: 0, source: fileInput });
	      //	return true;
	      //}


	      this.controller.getStore().dispatch('files/add', {
	        chatId: this.getChatId(),
	        authorId: this.controller.getUserId(),
	        name: file.name,
	        type: fileType,
	        extension: file.name.split('.').splice(-1)[0],
	        size: file.size,
	        image: false,
	        status: im_const.FileStatus.upload,
	        progress: 0,
	        authorName: this.controller.application.getCurrentUser().name,
	        urlPreview: ""
	      }).then(function (fileId) {
	        return _this18.addMessage('', {
	          id: fileId,
	          source: fileInput
	        });
	      });
	      return true;
	    }
	  }, {
	    key: "fileError",
	    value: function fileError(chatId, fileId) {
	      var messageId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	      this.controller.getStore().dispatch('files/update', {
	        chatId: chatId,
	        id: fileId,
	        fields: {
	          status: im_const.FileStatus.error,
	          progress: 0
	        }
	      });

	      if (messageId) {
	        this.controller.getStore().dispatch('messages/actionError', {
	          chatId: chatId,
	          id: messageId,
	          retry: false
	        });
	      }
	    }
	  }, {
	    key: "fileCommit",
	    value: function fileCommit(params, message) {
	      var _this19 = this;

	      this.controller.restClient.callMethod(im_const.RestMethod.imDiskFileCommit, {
	        chat_id: params.chatId,
	        upload_id: params.uploadId,
	        message: params.messageText,
	        template_id: params.messageId,
	        file_template_id: params.fileId
	      }, null, null).then(function (response) {
	        //this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, response, message);
	        _this19.controller.getStore().dispatch('messages/update', {
	          id: message.id,
	          chatId: message.chatId,
	          fields: {
	            id: response['MESSAGE_ID'],
	            sending: false,
	            error: false
	          }
	        }).then(function () {
	          _this19.controller.getStore().dispatch('messages/actionFinish', {
	            id: response['MESSAGE_ID'],
	            chatId: message.chatId
	          });
	        });
	      }).catch(function (error) {//this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, error, message);
	      });
	      return true;
	    }
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
	    key: "isUserRegistered",
	    value: function isUserRegistered() {
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

	exports.CallApplication = CallApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Lib,BX,BX,BX.Messenger.Lib,BX,BX.UI,BX.Messenger.Provider.Pull,BX,BX,BX,BX,BX.Messenger.Lib,BX,BX.Messenger,BX.Messenger,window,BX.Messenger.Const,BX.Messenger.Lib,BX.Messenger.Model,BX,BX.Messenger));
//# sourceMappingURL=call.bundle.js.map
