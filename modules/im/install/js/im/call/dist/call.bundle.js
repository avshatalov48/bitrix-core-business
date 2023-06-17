this.BX = this.BX || {};
(function (exports,im_lib_utils,ui_switcher,ui_dialogs_messagebox,ui_buttons,main_core_events,main_popup,main_core,loader,resize_observer,webrtc_adapter,im_lib_localstorage) {
	'use strict';

	// screensharing workaround
	function applyHacks() {
	  if (window["BXDesktopSystem"]) {
	    navigator['getDisplayMedia'] = function () {
	      var mediaParams = {
	        audio: false,
	        video: {
	          mandatory: {
	            chromeMediaSource: 'desktop',
	            maxWidth: screen.width > 1920 ? screen.width : 1920,
	            maxHeight: screen.height > 1080 ? screen.height : 1080
	          },
	          optional: [{
	            googTemporalLayeredScreencast: true
	          }]
	        }
	      };
	      return navigator.mediaDevices.getUserMedia(mediaParams);
	    };
	  }
	}

	var BackgroundDialog = /*#__PURE__*/function () {
	  function BackgroundDialog() {
	    babelHelpers.classCallCheck(this, BackgroundDialog);
	  }
	  babelHelpers.createClass(BackgroundDialog, null, [{
	    key: "isAvailable",
	    value: function isAvailable() {
	      return im_lib_utils.Utils.platform.getDesktopVersion() >= 52;
	    }
	  }, {
	    key: "isMaskAvailable",
	    value: function isMaskAvailable() {
	      return im_lib_utils.Utils.platform.isDesktopFeatureEnabled('mask');
	    }
	  }, {
	    key: "open",
	    value: function open(options) {
	      var _this = this;
	      options = main_core.Type.isPlainObject(options) ? options : {};
	      var tab = main_core.Type.isStringFilled(options.tab) ? options.tab : 'background'; // mask, background

	      if (!this.isAvailable()) {
	        if (window.BX.Helper) {
	          window.BX.Helper.show("redirect=detail&code=12398124");
	        }
	        return false;
	      }
	      var html = "<div id=\"bx-desktop-loader\" class=\"bx-desktop-loader-wrap\">\n\t\t\t\t\t\t<div class=\"bx-desktop-loader\">\n\t\t\t\t\t\t\t<svg class=\"bx-desktop-loader-circular\" viewBox=\"25 25 50 50\">\n\t\t\t\t\t\t\t\t<circle class=\"bx-desktop-loader-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div id=\"placeholder\"></div>";
	      var js = "BX.Runtime.loadExtension(\"im.v2.component.call-background\").then(function(exports) {\n\t\t\t\tBX.Vue3.BitrixVue.createApp({\n\t\t\t\t\tcomponents: {CallBackground: exports.CallBackground},\n\t\t\t\t\ttemplate: '<CallBackground tab=\"".concat(tab, "\"/>',\n\t\t\t\t}).mount(\"#placeholder\");\n\t\t\t});");
	      (opener || top).BX.desktop.createWindow("callBackground", function (controller) {
	        var title = _this.isMaskAvailable() ? BX.message('BXD_CALL_BG_MASK_TITLE') : BX.message('BXD_CALL_BG_TITLE');
	        controller.SetProperty("title", title);
	        controller.SetProperty("clientSize", {
	          Width: 943,
	          Height: 670
	        });
	        controller.SetProperty("minClientSize", {
	          Width: 943,
	          Height: 670
	        });
	        controller.SetProperty("backgroundColor", "#2B3038");
	        controller.ExecuteCommand("center");
	        controller.ExecuteCommand("html.load", (opener || top).BXIM.desktop.getHtmlPage(html, js, false));
	      });
	      return true;
	    }
	  }]);
	  return BackgroundDialog;
	}();

	var Logger = /*#__PURE__*/function () {
	  function Logger(serviceUrl, token) {
	    babelHelpers.classCallCheck(this, Logger);
	    this.serviceUrl = serviceUrl;
	    this.token = token;
	    this.socket = null;
	    this.attempt = 0;
	    this.reconnectTimeout = null;
	    this.unsentMessages = [];
	    this.onSocketOpenHandler = this.onSocketOpen.bind(this);
	    this.onSocketCloseHandler = this.onSocketClose.bind(this);
	    this.onSocketErrorHandler = this.onSocketError.bind(this);
	    this.connect();
	  }
	  babelHelpers.createClass(Logger, [{
	    key: "log",
	    value: function log(message) {
	      if (typeof message != 'string') {
	        console.error("Message should be string");
	        return;
	      }
	      if (this.isConnected) {
	        this.socket.send(JSON.stringify({
	          action: 'log',
	          message: message
	        }));
	      } else {
	        this.unsentMessages.push(message);
	      }
	    }
	  }, {
	    key: "sendStat",
	    value: function sendStat(statRecord) {
	      if (babelHelpers["typeof"](statRecord) == 'object') {
	        statRecord = JSON.stringify(statRecord);
	      }
	      if (this.isConnected) {
	        this.socket.send(JSON.stringify({
	          action: 'stat',
	          message: statRecord
	        }));
	      }
	    }
	  }, {
	    key: "connect",
	    value: function connect() {
	      if (this.socket) {
	        return;
	      }
	      if (!this.serviceUrl) {
	        console.error('Logging service url is empty');
	        return;
	      }
	      if (!this.serviceUrl.startsWith('ws://') && !this.serviceUrl.startsWith('wss://')) {
	        console.error('Logging service url should start with ws:// or wss://');
	        return;
	      }
	      if (!this.token) {
	        console.error('Logging token is empty');
	        return;
	      }
	      this.attempt++;
	      this.socket = new WebSocket(this.serviceUrl + '?token=' + this.token);
	      this.bindSocketEvents();
	    }
	  }, {
	    key: "scheduleReconnect",
	    value: function scheduleReconnect() {
	      clearTimeout(this.reconnectTimeout);
	      if (this.attempt > 3) {
	        console.error("Could not connect to the logging service, giving up");
	        return;
	      }
	      this.reconnectTimeout = setTimeout(this.connect.bind(this), this.getConnectionDelay(this.attempt) * 1000);
	    }
	  }, {
	    key: "getConnectionDelay",
	    value: function getConnectionDelay(attempt) {
	      switch (attempt) {
	        case 0:
	        case 1:
	          return 15;
	        case 2:
	          return 30;
	        default:
	          return 60;
	      }
	    }
	  }, {
	    key: "disconnect",
	    value: function disconnect() {
	      clearTimeout(this.reconnectTimeout);
	      if (this.socket) {
	        this.removeSocketEvents();
	        this.socket.close(1000);
	        this.socket = null;
	      }
	    }
	  }, {
	    key: "bindSocketEvents",
	    value: function bindSocketEvents() {
	      this.socket.addEventListener('open', this.onSocketOpenHandler);
	      this.socket.addEventListener('close', this.onSocketCloseHandler);
	      this.socket.addEventListener('error', this.onSocketErrorHandler);
	    }
	  }, {
	    key: "removeSocketEvents",
	    value: function removeSocketEvents() {
	      this.socket.removeEventListener('open', this.onSocketOpenHandler);
	      this.socket.removeEventListener('close', this.onSocketCloseHandler);
	      this.socket.removeEventListener('error', this.onSocketErrorHandler);
	    }
	  }, {
	    key: "onSocketOpen",
	    value: function onSocketOpen() {
	      this.attempt = 0;
	      for (var i = 0; i < this.unsentMessages.length; i++) {
	        this.socket.send(JSON.stringify({
	          action: 'log',
	          message: this.unsentMessages[i]
	        }));
	      }
	      this.unsentMessages = [];
	    }
	  }, {
	    key: "onSocketClose",
	    value: function onSocketClose() {
	      this.socket = null;
	      this.scheduleReconnect();
	    }
	  }, {
	    key: "onSocketError",
	    value: function onSocketError() {
	      this.socket = null;
	      this.scheduleReconnect();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.disconnect();
	      this.unsentMessages = null;
	    }
	  }, {
	    key: "isConnected",
	    get: function get() {
	      return this.socket && this.socket.readyState === 1;
	    }
	  }]);
	  return Logger;
	}();

	/**
	 * Abstract call class
	 * Public methods:
	 * - inviteUsers
	 * - cancel
	 * - answer
	 * - decline
	 * - hangup
	 *
	 * Events:
	 * - onJoin
	 * - onLeave
	 * - onUserStateChanged
	 * - onStreamReceived
	 * - onStreamRemoved
	 * - onCallFailure
	 * - onDestroy
	 */
	var AbstractCall = /*#__PURE__*/function () {
	  function AbstractCall(params) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, AbstractCall);
	    this.id = params.id;
	    this.instanceId = params.instanceId;
	    this.parentId = params.parentId || null;
	    this.direction = params.direction;
	    this.type = BX.prop.getInteger(params, "type", CallType.Instant); // @see {BX.Call.Type}
	    this.state = BX.prop.getString(params, "state", CallState.Idle);
	    this.ready = false;
	    this.userId = CallEngine.getCurrentUserId();
	    this.initiatorId = params.initiatorId || '';
	    this.users = main_core.Type.isArray(params.users) ? params.users.filter(function (userId) {
	      return userId != _this.userId;
	    }) : [];
	    this.associatedEntity = main_core.Type.isPlainObject(params.associatedEntity) ? params.associatedEntity : {};
	    this.startDate = new Date(BX.prop.getString(params, "startDate", ""));

	    // media constraints
	    this.videoEnabled = params.videoEnabled === true;
	    this.videoHd = params.videoHd === true;
	    this.cameraId = params.cameraId || '';
	    this.microphoneId = params.microphoneId || '';
	    this.muted = params.muted === true;
	    this.wasConnected = false;
	    this.logToken = params.logToken || '';
	    if (CallEngine.getLogService() && this.logToken) {
	      this.logger = new Logger(CallEngine.getLogService(), this.logToken);
	    }
	    this.localStreams = {
	      main: null,
	      screen: null
	    };
	    this.eventListeners = {};
	    if (main_core.Type.isPlainObject(params.events)) {
	      this.initEventListeners(params.events);
	    }
	    this._microphoneLevel = 0;
	  }
	  babelHelpers.createClass(AbstractCall, [{
	    key: "initEventListeners",
	    value: function initEventListeners(eventListeners) {
	      for (var eventName in eventListeners) {
	        this.addEventListener(eventName, eventListeners[eventName]);
	      }
	    }
	  }, {
	    key: "addEventListener",
	    value: function addEventListener(eventName, listener) {
	      if (!main_core.Type.isArray(this.eventListeners[eventName])) {
	        this.eventListeners[eventName] = [];
	      }
	      if (main_core.Type.isFunction(listener)) {
	        this.eventListeners[eventName].push(listener);
	      }
	    }
	  }, {
	    key: "removeEventListener",
	    value: function removeEventListener(eventName, listener) {
	      if (main_core.Type.isArray(this.eventListeners[eventName]) && this.eventListeners[eventName].indexOf(listener) >= 0) {
	        var listenerIndex = this.eventListeners[eventName].indexOf(listener);
	        if (listenerIndex >= 0) {
	          this.eventListeners[eventName].splice(listenerIndex, 1);
	        }
	      }
	    }
	  }, {
	    key: "runCallback",
	    value: function runCallback(eventName, eventFields) {
	      //console.log(eventName, eventFields);
	      if (main_core.Type.isArray(this.eventListeners[eventName]) && this.eventListeners[eventName].length > 0) {
	        if (eventName === null || babelHelpers["typeof"](eventFields) !== "object") {
	          eventFields = {};
	        }
	        eventFields.call = this;
	        for (var i = 0; i < this.eventListeners[eventName].length; i++) {
	          try {
	            this.eventListeners[eventName][i].call(this, eventFields);
	          } catch (err) {
	            console.error(eventName + " callback error: ", err);
	            this.log(eventName + " callback error: ", err);
	          }
	        }
	      }
	    }
	  }, {
	    key: "getLocalStream",
	    value: function getLocalStream(tag) {
	      return this.localStreams[tag];
	    }
	  }, {
	    key: "setLocalStream",
	    value: function setLocalStream(mediaStream, tag) {
	      tag = tag || "main";
	      this.localStreams[tag] = mediaStream;
	    }
	  }, {
	    key: "isVideoEnabled",
	    value: function isVideoEnabled() {
	      return this.videoEnabled;
	    }
	  }, {
	    key: "isAnyoneParticipating",
	    value: function isAnyoneParticipating() {
	      throw new Error("isAnyoneParticipating should be implemented");
	    }
	  }, {
	    key: "__onPullEvent",
	    value: function __onPullEvent(command, params) {
	      throw new Error("__onPullEvent should be implemented");
	    }
	  }, {
	    key: "inviteUsers",
	    value: function inviteUsers() {
	      throw new Error("inviteUsers is not implemented");
	    }
	  }, {
	    key: "cancel",
	    value: function cancel() {
	      throw new Error("cancel is not implemented");
	    }
	  }, {
	    key: "answer",
	    value: function answer() {
	      throw new Error("answer is not implemented");
	    }
	  }, {
	    key: "decline",
	    value: function decline(code, reason) {
	      throw new Error("decline is not implemented");
	    }
	  }, {
	    key: "hangup",
	    value: function hangup() {
	      throw new Error("hangup is not implemented");
	    }
	  }, {
	    key: "log",
	    value: function log() {
	      var text = Util$1.getLogMessage.apply(null, arguments);
	      if (BX.desktop && BX.desktop.ready()) {
	        BX.desktop.log(BX.message('USER_ID') + '.video.log', text.substr(3));
	      }
	      if (CallEngine.debugFlag && console) {
	        var a = ['Call log [' + Util$1.getTimeForLog() + ']: '];
	        console.log.apply(this, a.concat(Array.prototype.slice.call(arguments)));
	      }
	      if (this.logger) {
	        this.logger.log(text);
	      }
	      if (BX.MessengerDebug) {
	        BX.MessengerDebug.addLog(this.id, text);
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.logger) {
	        this.logger.destroy();
	        this.logger = null;
	      }
	      this.state = CallState.Finished;
	      this.runCallback(CallEvent.onDestroy);
	    }
	  }, {
	    key: "provider",
	    get: function get() {
	      throw new Error("must be overwritten");
	    }
	  }, {
	    key: "microphoneLevel",
	    get: function get() {
	      return this._microphoneLevel;
	    },
	    set: function set(level) {
	      if (level != this._microphoneLevel) {
	        this._microphoneLevel = level;
	        this.runCallback(CallEvent.onMicrophoneLevel, {
	          level: level
	        });
	      }
	    }
	  }]);
	  return AbstractCall;
	}();

	var UserModel = /*#__PURE__*/function () {
	  function UserModel(config) {
	    babelHelpers.classCallCheck(this, UserModel);
	    this.data = {
	      id: BX.prop.getInteger(config, "id", 0),
	      name: BX.prop.getString(config, "name", ""),
	      avatar: BX.prop.getString(config, "avatar", ""),
	      gender: BX.prop.getString(config, "gender", ""),
	      state: BX.prop.getString(config, "state", UserState.Idle),
	      talking: BX.prop.getBoolean(config, "talking", false),
	      cameraState: BX.prop.getBoolean(config, "cameraState", true),
	      microphoneState: BX.prop.getBoolean(config, "microphoneState", true),
	      screenState: BX.prop.getBoolean(config, "screenState", false),
	      videoPaused: BX.prop.getBoolean(config, "videoPaused", false),
	      floorRequestState: BX.prop.getBoolean(config, "floorRequestState", false),
	      localUser: BX.prop.getBoolean(config, "localUser", false),
	      centralUser: BX.prop.getBoolean(config, "centralUser", false),
	      pinned: BX.prop.getBoolean(config, "pinned", false),
	      presenter: BX.prop.getBoolean(config, "presenter", false),
	      order: BX.prop.getInteger(config, "order", false),
	      allowRename: BX.prop.getBoolean(config, "allowRename", false),
	      wasRenamed: BX.prop.getBoolean(config, "wasRenamed", false),
	      renameRequested: BX.prop.getBoolean(config, "renameRequested", false),
	      direction: BX.prop.getString(config, "direction", EndpointDirection.SendRecv)
	    };
	    for (var fieldName in this.data) {
	      if (this.data.hasOwnProperty(fieldName)) {
	        Object.defineProperty(this, fieldName, {
	          get: this._getField(fieldName).bind(this),
	          set: this._setField(fieldName).bind(this)
	        });
	      }
	    }
	    this.onUpdate = {
	      talking: this._onUpdateTalking.bind(this),
	      state: this._onUpdateState.bind(this)
	    };
	    this.talkingStop = null;
	    this.eventEmitter = new main_core_events.EventEmitter(this, 'UserModel');
	  }
	  babelHelpers.createClass(UserModel, [{
	    key: "_getField",
	    value: function _getField(fieldName) {
	      return function () {
	        return this.data[fieldName];
	      };
	    }
	  }, {
	    key: "_setField",
	    value: function _setField(fieldName) {
	      return function (newValue) {
	        var oldValue = this.data[fieldName];
	        if (oldValue == newValue) {
	          return;
	        }
	        this.data[fieldName] = newValue;
	        if (this.onUpdate.hasOwnProperty(fieldName)) {
	          this.onUpdate[fieldName](newValue, oldValue);
	        }
	        this.eventEmitter.emit("changed", {
	          user: this,
	          fieldName: fieldName,
	          oldValue: oldValue,
	          newValue: newValue
	        });
	      };
	    }
	  }, {
	    key: "_onUpdateTalking",
	    value: function _onUpdateTalking(talking) {
	      if (talking) {
	        this.floorRequestState = false;
	      } else {
	        this.talkingStop = new Date().getTime();
	      }
	    }
	  }, {
	    key: "_onUpdateState",
	    value: function _onUpdateState(newValue) {
	      if (newValue != UserState.Connected) {
	        this.talking = false;
	        this.screenState = false;
	      }
	    }
	  }, {
	    key: "wasTalkingAgo",
	    value: function wasTalkingAgo() {
	      if (this.state != UserState.Connected) {
	        return +Infinity;
	      }
	      if (this.talking) {
	        return 0;
	      }
	      if (!this.talkingStop) {
	        return +Infinity;
	      }
	      return new Date().getTime() - this.talkingStop;
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe(event, handler) {
	      this.eventEmitter.subscribe(event, handler);
	    }
	  }, {
	    key: "unsubscribe",
	    value: function unsubscribe(event, handler) {
	      this.eventEmitter.unsubscribe(event, handler);
	    }
	  }]);
	  return UserModel;
	}();
	var UserRegistry = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(UserRegistry, _EventEmitter);
	  function UserRegistry() {
	    var _this;
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, UserRegistry);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserRegistry).call(this));
	    _this.setEventNamespace('BX.Call.UserRegistry');
	    _this.users = main_core.Type.isArray(config.users) ? config.users : [];
	    _this._sort();
	    return _this;
	  }
	  babelHelpers.createClass(UserRegistry, [{
	    key: "get",
	    /**
	     *
	     * @param {int} userId
	     * @returns {UserModel|null}
	     */
	    value: function get(userId) {
	      for (var i = 0; i < this.users.length; i++) {
	        if (this.users[i].id == userId) {
	          return this.users[i];
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "push",
	    value: function push(user) {
	      if (!(user instanceof UserModel)) {
	        throw Error("user should be instance of UserModel");
	      }
	      this.users.push(user);
	      this._sort();
	      user.subscribe("changed", this._onUserChanged.bind(this));
	      this.emit("userAdded", {
	        user: user
	      });
	    }
	  }, {
	    key: "_onUserChanged",
	    value: function _onUserChanged(event) {
	      if (event.data.fieldName === 'order') {
	        this._sort();
	      }
	      this.emit("userChanged", event.data);
	    }
	  }, {
	    key: "_sort",
	    value: function _sort() {
	      this.users = this.users.sort(function (a, b) {
	        return a.order - b.order;
	      });
	    }
	  }]);
	  return UserRegistry;
	}(main_core_events.EventEmitter);

	function createSVG(elementName, config) {
	  var element = document.createElementNS('http://www.w3.org/2000/svg', elementName);
	  if ("attrNS" in config && main_core.Type.isObject(config.attrNS)) {
	    for (var key in config.attrNS) {
	      if (config.attrNS.hasOwnProperty(key)) {
	        element.setAttributeNS(null, key, config.attrNS[key]);
	      }
	    }
	  }
	  main_core.Dom.adjust(element, config);
	  return element;
	}

	var TitleButton = /*#__PURE__*/function () {
	  function TitleButton(config) {
	    babelHelpers.classCallCheck(this, TitleButton);
	    this.elements = {
	      root: null
	    };
	    this.text = main_core.Type.isStringFilled(config.text) ? config.text : '';
	    this.isGroupCall = config.isGroupCall;
	  }
	  babelHelpers.createClass(TitleButton, [{
	    key: "render",
	    value: function render() {
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-panel-title"
	        },
	        html: this.getTitle()
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      var prettyName = '<span class="bx-messenger-videocall-panel-title-name">' + main_core.Text.encode(this.text) + '</span>';
	      if (this.isGroupCall) {
	        return BX.message("IM_M_GROUP_CALL_WITH").replace("#CHAT_NAME#", prettyName);
	      } else {
	        return BX.message("IM_M_CALL_WITH").replace("#USER_NAME#", prettyName);
	      }
	    }
	  }]);
	  return TitleButton;
	}();
	var SimpleButton = /*#__PURE__*/function () {
	  function SimpleButton(config) {
	    babelHelpers.classCallCheck(this, SimpleButton);
	    this["class"] = config["class"];
	    this.backgroundClass = BX.prop.getString(config, "backgroundClass", "");
	    this.backgroundClass = "bx-messenger-videocall-panel-icon-background" + (this.backgroundClass ? " " : "") + this.backgroundClass;
	    this.blocked = config.blocked === true;
	    this.text = BX.prop.getString(config, "text", "");
	    this.isActive = false;
	    this.counter = BX.prop.getInteger(config, "counter", 0);
	    this.elements = {
	      root: null,
	      counter: null
	    };
	    this.callbacks = {
	      onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
	      onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
	      onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(SimpleButton, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      var textNode;
	      if (this.text !== '') {
	        textNode = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel-text"
	          },
	          text: this.text
	        });
	      } else {
	        textNode = null;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-panel-item" + (this.blocked ? " blocked" : "")
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: this.backgroundClass
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-panel-icon bx-messenger-videocall-panel-icon-" + this["class"]
	            },
	            children: [this.elements.counter = main_core.Dom.create("span", {
	              props: {
	                className: "bx-messenger-videocall-panel-item-counter"
	              },
	              text: 0,
	              dataset: {
	                counter: 0,
	                counterType: 'digits'
	              }
	            })]
	          })]
	        }), textNode, main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel-item-bottom-spacer"
	          }
	        })],
	        events: {
	          click: this.callbacks.onClick,
	          mouseover: this.callbacks.onMouseOver,
	          mouseout: this.callbacks.onMouseOut
	        }
	      });
	      if (this.isActive) {
	        this.elements.root.classList.add("active");
	      }
	      if (this.counter) {
	        this.setCounter(this.counter);
	      }
	      return this.elements.root;
	    }
	  }, {
	    key: "setActive",
	    value: function setActive(isActive) {
	      if (this.isActive == isActive) {
	        return;
	      }
	      this.isActive = isActive;
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.isActive) {
	        this.elements.root.classList.add("active");
	      } else {
	        this.elements.root.classList.remove("active");
	      }
	    }
	  }, {
	    key: "setBlocked",
	    value: function setBlocked(isBlocked) {
	      if (this.blocked == isBlocked) {
	        return;
	      }
	      this.blocked = isBlocked;
	      if (this.blocked) {
	        this.elements.root.classList.add("blocked");
	      } else {
	        this.elements.root.classList.remove("blocked");
	      }
	    }
	  }, {
	    key: "setCounter",
	    value: function setCounter(counter) {
	      this.counter = parseInt(counter, 10);
	      var counterLabel = this.counter;
	      if (counterLabel > 999) {
	        counterLabel = 999;
	      }
	      var counterType = 'digits';
	      if (counterLabel.toString().length === 2) {
	        counterType = 'dozens';
	      } else if (counterLabel.toString().length > 2) {
	        counterType = 'hundreds';
	      }
	      this.elements.counter.dataset.counter = counterLabel;
	      this.elements.counter.dataset.counterType = counterType;
	      this.elements.counter.innerText = counterLabel;
	    }
	  }]);
	  return SimpleButton;
	}();
	var DeviceButton = /*#__PURE__*/function () {
	  function DeviceButton(config) {
	    babelHelpers.classCallCheck(this, DeviceButton);
	    this["class"] = config["class"];
	    this.text = config.text;
	    this.enabled = config.enabled === true;
	    this.arrowEnabled = config.arrowEnabled === true;
	    this.arrowHidden = config.arrowHidden === true;
	    this.blocked = config.blocked === true;
	    this.showLevel = config.showLevel === true;
	    this.level = config.level || 0;
	    this.sideIcon = BX.prop.getString(config, "sideIcon", "");
	    this.elements = {
	      root: null,
	      iconContainer: null,
	      icon: null,
	      arrow: null,
	      levelMeter: null,
	      pointer: null,
	      ellipsis: null
	    };
	    this.callbacks = {
	      onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
	      onArrowClick: BX.prop.getFunction(config, "onArrowClick", BX.DoNothing),
	      onSideIconClick: BX.prop.getFunction(config, "onSideIconClick", BX.DoNothing),
	      onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
	      onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(DeviceButton, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          id: "bx-messenger-videocall-panel-item-with-arrow-" + this["class"],
	          className: "bx-messenger-videocall-panel-item-with-arrow" + (this.blocked ? " blocked" : "")
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel-item-with-arrow-left"
	          },
	          children: [this.elements.iconContainer = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-panel-item-with-arrow-icon-container"
	            },
	            children: [this.elements.icon = main_core.Dom.create("div", {
	              props: {
	                className: this.getIconClass()
	              }
	            })]
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-panel-text"
	            },
	            text: this.text
	          })]
	        })],
	        events: {
	          click: this.callbacks.onClick,
	          mouseover: this.callbacks.onMouseOver,
	          mouseout: this.callbacks.onMouseOut
	        }
	      });
	      this.elements.arrow = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-panel-item-with-arrow-right"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel-item-with-arrow-right-icon"
	          }
	        })],
	        events: {
	          click: function (e) {
	            this.callbacks.onArrowClick.apply(this, arguments);
	            e.stopPropagation();
	          }.bind(this)
	        }
	      });
	      if (!this.arrowHidden) {
	        this.elements.root.appendChild(this.elements.arrow);
	      }
	      if (this.showLevel) {
	        this.elements.icon.appendChild(createSVG("svg", {
	          attrNS: {
	            "class": "bx-messenger-videocall-panel-item-level-meter-container",
	            width: 3,
	            height: 20
	          },
	          children: [createSVG("g", {
	            attrNS: {
	              fill: "#30B1DC"
	            },
	            children: [createSVG("rect", {
	              attrNS: {
	                x: 0,
	                y: 0,
	                width: 3,
	                height: 20,
	                rx: 1.5,
	                opacity: .1
	              }
	            }), this.elements.levelMeter = createSVG("rect", {
	              attrNS: {
	                x: 0,
	                y: 20,
	                width: 3,
	                height: 20,
	                rx: 1.5
	              }
	            })]
	          })]
	        }));
	      }
	      this.elements.ellipsis = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-panel-icon-ellipsis"
	        },
	        events: {
	          click: this.callbacks.onSideIconClick
	        }
	      });
	      this.elements.pointer = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-panel-icon-pointer"
	        },
	        events: {
	          click: this.callbacks.onSideIconClick
	        }
	      });
	      if (this.sideIcon == "pointer") {
	        BX.Dom.insertAfter(this.elements.pointer, this.elements.icon);
	      } else if (this.sideIcon == "ellipsis") {
	        BX.Dom.insertAfter(this.elements.ellipsis, this.elements.icon);
	      }
	      return this.elements.root;
	    }
	  }, {
	    key: "getIconClass",
	    value: function getIconClass() {
	      return "bx-messenger-videocall-panel-item-with-arrow-icon bx-messenger-videocall-panel-item-with-arrow-icon-" + this["class"] + (this.enabled ? "" : "-off");
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      if (this.enabled) {
	        return;
	      }
	      this.enabled = true;
	      this.elements.icon.className = this.getIconClass();
	      if (this.elements.levelMeter) {
	        this.elements.levelMeter.setAttribute('y', Math.round((1 - this.level) * 20));
	      }
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      if (!this.enabled) {
	        return;
	      }
	      this.enabled = false;
	      this.elements.icon.className = this.getIconClass();
	      if (this.elements.levelMeter) {
	        this.elements.levelMeter.setAttribute('y', 20);
	      }
	    }
	  }, {
	    key: "setBlocked",
	    value: function setBlocked(blocked) {
	      if (this.blocked == blocked) {
	        return;
	      }
	      this.blocked = blocked;
	      this.elements.icon.className = this.getIconClass();
	      if (this.blocked) {
	        this.elements.root.classList.add("blocked");
	      } else {
	        this.elements.root.classList.remove("blocked");
	      }
	    }
	  }, {
	    key: "setSideIcon",
	    value: function setSideIcon(sideIcon) {
	      if (this.sideIcon == sideIcon) {
	        return;
	      }
	      this.sideIcon = sideIcon;
	      BX.Dom.remove(this.elements.pointer);
	      BX.Dom.remove(this.elements.ellipsis);
	      if (this.sideIcon == "pointer") {
	        BX.Dom.insertAfter(this.elements.pointer, this.elements.icon);
	      } else if (this.sideIcon == "ellipsis") {
	        BX.Dom.insertAfter(this.elements.ellipsis, this.elements.icon);
	      }
	    }
	  }, {
	    key: "showArrow",
	    value: function showArrow() {
	      if (!this.arrowHidden) {
	        return;
	      }
	      this.arrowHidden = false;
	      this.elements.root.appendChild(this.elements.arrow);
	    }
	  }, {
	    key: "hideArrow",
	    value: function hideArrow() {
	      if (this.arrowHidden) {
	        return;
	      }
	      this.arrowHidden = false;
	      this.elements.root.removeChild(this.elements.arrow);
	    }
	  }, {
	    key: "setLevel",
	    value: function setLevel(level) {
	      this.level = Math.log(level * 100) / 4.6;
	      if (this.showLevel && this.enabled) {
	        this.elements.levelMeter.setAttribute('y', Math.round((1 - this.level) * 20));
	      }
	    }
	  }]);
	  return DeviceButton;
	}();
	var WaterMarkButton = /*#__PURE__*/function () {
	  function WaterMarkButton(config) {
	    babelHelpers.classCallCheck(this, WaterMarkButton);
	    this.language = config.language;
	  }
	  babelHelpers.createClass(WaterMarkButton, [{
	    key: "render",
	    value: function render() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-watermark"
	        },
	        children: [main_core.Dom.create("img", {
	          props: {
	            className: "bx-messenger-videocall-watermark-img",
	            src: this.getWatermarkUrl(this.language)
	          }
	        })]
	      });
	    }
	  }, {
	    key: "getWatermarkUrl",
	    value: function getWatermarkUrl(language) {
	      switch (language) {
	        case 'ua':
	          return '/bitrix/js/im/images/watermark-white-ua.svg';
	        case 'ru':
	        case 'kz':
	        case 'by':
	          return '/bitrix/js/im/images/watermark-white-ru.svg';
	        default:
	          return '/bitrix/js/im/images/watermark-white-en.svg';
	      }
	    }
	  }]);
	  return WaterMarkButton;
	}();
	var TopButton = /*#__PURE__*/function () {
	  function TopButton(config) {
	    babelHelpers.classCallCheck(this, TopButton);
	    this.iconClass = BX.prop.getString(config, "iconClass", "");
	    this.text = BX.prop.getString(config, "text", "");
	    this.callbacks = {
	      onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
	      onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
	      onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(TopButton, [{
	    key: "render",
	    value: function render() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-top-button"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-button-icon " + this.iconClass
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-button-text "
	          },
	          text: this.text
	        })],
	        events: {
	          click: this.callbacks.onClick,
	          mouseover: this.callbacks.onMouseOver,
	          mouseout: this.callbacks.onMouseOut
	        }
	      });
	    }
	  }]);
	  return TopButton;
	}();
	var TopFramelessButton = /*#__PURE__*/function () {
	  function TopFramelessButton(config) {
	    babelHelpers.classCallCheck(this, TopFramelessButton);
	    this.iconClass = BX.prop.getString(config, "iconClass", "");
	    this.textClass = BX.prop.getString(config, "textClass", "");
	    this.text = BX.prop.getString(config, "text", "");
	    this.callbacks = {
	      onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
	      onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
	      onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(TopFramelessButton, [{
	    key: "render",
	    value: function render() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-top-button-frameless"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-button-icon " + this.iconClass
	          }
	        }), this.text != "" ? main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-button-text " + this.textClass
	          },
	          text: this.text
	        }) : null],
	        events: {
	          click: this.callbacks.onClick,
	          mouseover: this.callbacks.onMouseOver,
	          mouseout: this.callbacks.onMouseOut
	        }
	      });
	    }
	  }]);
	  return TopFramelessButton;
	}();
	var ParticipantsButton = /*#__PURE__*/function () {
	  function ParticipantsButton(config) {
	    babelHelpers.classCallCheck(this, ParticipantsButton);
	    this.count = BX.prop.getInteger(config, "count", 0);
	    this.foldButtonState = BX.prop.getString(config, "foldButtonState", ParticipantsButton.FoldButtonState.Hidden);
	    this.allowAdding = BX.prop.getBoolean(config, "allowAdding", false);
	    this.elements = {
	      root: null,
	      leftContainer: null,
	      rightContainer: null,
	      foldIcon: null,
	      count: null,
	      separator: null
	    };
	    this.callbacks = {
	      onListClick: BX.prop.getFunction(config, "onListClick", BX.DoNothing),
	      onAddClick: BX.prop.getFunction(config, "onAddClick", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(ParticipantsButton, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-top-participants"
	        },
	        children: [this.elements.leftContainer = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-participants-inner left" + (this.foldButtonState != ParticipantsButton.FoldButtonState.Hidden ? " active" : "")
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-button-icon participants"
	            }
	          }), this.elements.count = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-participants-text-count"
	            },
	            text: this.count
	          }), this.elements.foldIcon = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-participants-fold-icon " + this.foldButtonState
	            }
	          })],
	          events: {
	            click: this.callbacks.onListClick
	          }
	        })]
	      });
	      this.elements.separator = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-top-participants-separator"
	        }
	      });
	      this.elements.rightContainer = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-top-participants-inner active"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-button-icon add"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-participants-text"
	          },
	          text: BX.message("IM_M_CALL_BTN_ADD")
	        })],
	        events: {
	          click: this.callbacks.onAddClick
	        }
	      });
	      if (this.allowAdding) {
	        this.elements.root.appendChild(this.elements.separator);
	        this.elements.root.appendChild(this.elements.rightContainer);
	      }
	      return this.elements.root;
	    }
	  }, {
	    key: "update",
	    value: function update(config) {
	      this.count = BX.prop.getInteger(config, "count", this.count);
	      this.foldButtonState = BX.prop.getString(config, "foldButtonState", this.foldButtonState);
	      this.allowAdding = BX.prop.getBoolean(config, "allowAdding", this.allowAdding);
	      this.elements.count.innerText = this.count;
	      this.elements.foldIcon.className = "bx-messenger-videocall-top-participants-fold-icon " + this.foldButtonState;
	      if (this.foldButtonState == ParticipantsButton.FoldButtonState.Hidden) {
	        this.elements.leftContainer.classList.remove("active");
	      } else {
	        this.elements.leftContainer.classList.add("active");
	      }
	      if (this.allowAdding && !this.elements.separator.parentElement) {
	        this.elements.root.appendChild(this.elements.separator);
	        this.elements.root.appendChild(this.elements.rightContainer);
	      }
	      if (!this.allowAdding && this.elements.separator.parentElement) {
	        BX.remove(this.elements.separator);
	        BX.remove(this.elements.rightContainer);
	      }
	    }
	  }]);
	  return ParticipantsButton;
	}();
	babelHelpers.defineProperty(ParticipantsButton, "FoldButtonState", {
	  Active: "active",
	  Fold: "fold",
	  Unfold: "unfold",
	  Hidden: "hidden"
	});
	var ParticipantsButtonMobile = /*#__PURE__*/function () {
	  function ParticipantsButtonMobile(config) {
	    babelHelpers.classCallCheck(this, ParticipantsButtonMobile);
	    this.count = BX.prop.getInteger(config, "count", 0);
	    this.elements = {
	      root: null,
	      icon: null,
	      text: null,
	      arrow: null
	    };
	    this.callbacks = {
	      onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(ParticipantsButtonMobile, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-top-participants-mobile"
	        },
	        children: [this.elements.icon = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-participants-mobile-icon"
	          }
	        }), this.elements.text = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-participants-mobile-text"
	          },
	          text: BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.count)
	        }), this.elements.arrow = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-participants-mobile-arrow"
	          }
	        })],
	        events: {
	          click: this.callbacks.onClick
	        }
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "setCount",
	    value: function setCount(count) {
	      if (this.count == count) {
	        return;
	      }
	      this.count = count;
	      this.elements.text.innerText = BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.count);
	    }
	  }]);
	  return ParticipantsButtonMobile;
	}();
	var RecordStatusButton = /*#__PURE__*/function () {
	  function RecordStatusButton(config) {
	    babelHelpers.classCallCheck(this, RecordStatusButton);
	    this.userId = config.userId;
	    this.recordState = config.recordState;
	    this.updateViewInterval = null;
	    this.elements = {
	      root: null,
	      timeText: null,
	      stateText: null
	    };
	    this.callbacks = {
	      onPauseClick: BX.prop.getFunction(config, "onPauseClick", BX.DoNothing),
	      onStopClick: BX.prop.getFunction(config, "onStopClick", BX.DoNothing),
	      onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
	      onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(RecordStatusButton, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-top-recordstatus record-status-" + this.recordState.state + " " + (this.recordState.userId == this.userId ? '' : 'record-user-viewer')
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-recordstatus-status"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-button-icon record-status"
	            }
	          })]
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-recordstatus-time"
	          },
	          children: [this.elements.timeText = main_core.Dom.create("span", {
	            props: {
	              className: "bx-messenger-videocall-top-recordstatus-time-text"
	            },
	            text: this.getTimeText()
	          }), main_core.Dom.create("span", {
	            props: {
	              className: "bx-messenger-videocall-top-recordstatus-time-separator"
	            },
	            html: ' &ndash; '
	          }), this.elements.stateText = main_core.Dom.create("span", {
	            props: {
	              className: "bx-messenger-videocall-top-recordstatus-time-state"
	            },
	            text: BX.message('IM_M_CALL_RECORD_TITLE')
	          })]
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-top-recordstatus-buttons"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-recordstatus-separator"
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-recordstatus-button"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-top-button-icon record-pause"
	              }
	            })],
	            events: {
	              click: this.callbacks.onPauseClick
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-recordstatus-separator"
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-recordstatus-button"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-top-button-icon record-stop"
	              }
	            })],
	            events: {
	              click: this.callbacks.onStopClick
	            }
	          })]
	        })],
	        events: {
	          mouseover: this.callbacks.onMouseOver,
	          mouseout: this.callbacks.onMouseOut
	        }
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "getTimeText",
	    value: function getTimeText() {
	      if (this.recordState.state === View.RecordState.Stopped) {
	        return '';
	      }
	      var nowDate = new Date();
	      var startDate = new Date(this.recordState.date.start);
	      if (startDate.getTime() < nowDate.getDate()) {
	        startDate = nowDate;
	      }
	      var pauseTime = this.recordState.date.pause.map(function (element) {
	        var finish = element.finish ? new Date(element.finish) : nowDate;
	        return finish - new Date(element.start);
	      }).reduce(function (sum, element) {
	        return sum + element;
	      }, 0);
	      var totalTime = nowDate - startDate - pauseTime;
	      if (totalTime <= 0) {
	        totalTime = 0;
	      }
	      var second = Math.floor(totalTime / 1000);
	      var hour = Math.floor(second / 60 / 60);
	      if (hour > 0) {
	        second -= hour * 60 * 60;
	      }
	      var minute = Math.floor(second / 60);
	      if (minute > 0) {
	        second -= minute * 60;
	      }
	      return (hour > 0 ? hour + ':' : '') + (hour > 0 ? minute.toString().padStart(2, "0") + ':' : minute + ':') + second.toString().padStart(2, "0");
	    }
	  }, {
	    key: "update",
	    value: function update(recordState) {
	      if (this.recordState.state !== recordState.state) {
	        clearInterval(this.updateViewInterval);
	        if (recordState.state === View.RecordState.Started) {
	          this.updateViewInterval = setInterval(this.updateView.bind(this), 1000);
	        }
	      }
	      this.recordState = recordState;
	      this.updateView();
	    }
	  }, {
	    key: "updateView",
	    value: function updateView() {
	      var timeText = this.getTimeText();
	      if (this.elements.timeText.innerText !== timeText) {
	        this.elements.timeText.innerText = this.getTimeText();
	      }
	      if (!this.elements.root.classList.contains("record-status-" + this.recordState.state)) {
	        this.elements.root.className = "bx-messenger-videocall-top-recordstatus record-status-" + this.recordState.state + ' ' + (this.recordState.userId == this.userId ? '' : 'record-user-viewer');
	      }
	    }
	  }, {
	    key: "stopViewUpdate",
	    value: function stopViewUpdate() {
	      if (this.updateViewInterval) {
	        clearInterval(this.updateViewInterval);
	        this.updateViewInterval = null;
	      }
	    }
	  }]);
	  return RecordStatusButton;
	}();

	var CallUserMobile = /*#__PURE__*/function () {
	  function CallUserMobile(config) {
	    babelHelpers.classCallCheck(this, CallUserMobile);
	    this.userModel = config.userModel;
	    this.elements = {
	      root: null,
	      avatar: null,
	      avatarOutline: null,
	      userName: null,
	      userStatus: null,
	      menuArrow: null,
	      floorRequest: null,
	      mic: null,
	      cam: null
	    };
	    this._onUserFieldChangeHandler = this._onUserFieldChange.bind(this);
	    this.userModel.subscribe("changed", this._onUserFieldChangeHandler);
	    this.callbacks = {
	      onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(CallUserMobile, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-mobile"
	        },
	        children: [this.elements.avatar = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-mobile-avatar" + (this.userModel.talking ? " talking" : "")
	          },
	          children: [this.elements.floorRequest = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-mobile-floor-request bx-messenger-videocall-floor-request-icon"
	            }
	          })]
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-mobile-body"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-mobile-text"
	            },
	            children: [this.elements.mic = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-mobile-icon" + (this.userModel.microphoneState ? "" : " bx-call-view-icon-red-microphone-off")
	              }
	            }), this.elements.cam = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-mobile-icon" + (this.userModel.cameraState ? "" : " bx-call-view-icon-red-camera-off")
	              }
	            }), this.elements.userName = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-mobile-username"
	              },
	              text: this.userModel.name
	            }), main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-mobile-menu-arrow"
	              }
	            })]
	          }), this.elements.userStatus = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-mobile-user-status"
	            },
	            text: this.userModel.pinned ? BX.message("IM_M_CALL_PINNED_USER") : BX.message("IM_M_CALL_CURRENT_PRESENTER")
	          })]
	        })],
	        events: {
	          click: this.callbacks.onClick
	        }
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "update",
	    value: function update() {
	      if (!this.elements.root) {
	        return;
	      }
	      this.elements.userName.innerText = this.userModel.name;
	      if (this.userModel.avatar !== '') {
	        this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
	      } else {
	        this.elements.root.style.removeProperty("--avatar");
	      }
	      this.elements.avatar.classList.toggle("talking", this.userModel.talking);
	      this.elements.floorRequest.classList.toggle("active", this.userModel.floorRequestState);
	      this.elements.mic.classList.toggle("bx-call-view-icon-red-microphone-off", !this.userModel.microphoneState);
	      this.elements.cam.classList.toggle("bx-call-view-icon-red-camera-off", !this.userModel.cameraState);
	      this.elements.userStatus.innerText = this.userModel.pinned ? BX.message("IM_M_CALL_PINNED_USER") : BX.message("IM_M_CALL_CURRENT_PRESENTER");
	    }
	  }, {
	    key: "mount",
	    value: function mount(parentElement) {
	      parentElement.appendChild(this.render());
	    }
	  }, {
	    key: "dismount",
	    value: function dismount() {
	      if (!this.elements.root) {
	        return;
	      }
	      main_core.Dom.remove(this.elements.root);
	    }
	  }, {
	    key: "setUserModel",
	    value: function setUserModel(userModel) {
	      this.userModel.unsubscribe("changed", this._onUserFieldChangeHandler);
	      this.userModel = userModel;
	      this.userModel.subscribe("changed", this._onUserFieldChangeHandler);
	      this.update();
	    }
	  }, {
	    key: "_onUserFieldChange",
	    value: function _onUserFieldChange(event) {
	      this.update();
	    }
	  }]);
	  return CallUserMobile;
	}();
	var UserSelectorMobile = /*#__PURE__*/function () {
	  function UserSelectorMobile(config) {
	    babelHelpers.classCallCheck(this, UserSelectorMobile);
	    this.userRegistry = config.userRegistry;
	    this.userRegistry.subscribe("userAdded", this._onUserAdded.bind(this));
	    this.userRegistry.subscribe("userChanged", this._onUserChanged.bind(this));
	    this.elements = {
	      root: null,
	      users: {}
	    };
	  }
	  babelHelpers.createClass(UserSelectorMobile, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-selector-mobile"
	        }
	      });
	      this.updateUsers();
	      return this.elements.root;
	    }
	  }, {
	    key: "renderUser",
	    value: function renderUser(userFields) {
	      return createSVG("svg", {
	        attrNS: {
	          width: 14.5,
	          height: 11.6
	        },
	        style: {
	          order: userFields.order
	        },
	        children: [createSVG("circle", {
	          attrNS: {
	            "class": "bx-messenger-videocall-user-selector-mobile-border" + (userFields.talking ? " talking" : ""),
	            cx: 7.25,
	            cy: 5.8,
	            r: 4.6
	          }
	        }), createSVG("circle", {
	          attrNS: {
	            "class": "bx-messenger-videocall-user-selector-mobile-dot" + (userFields.centralUser ? " pinned" : ""),
	            cx: 7.25,
	            cy: 5.8,
	            r: 3.3
	          }
	        })]
	      });
	    }
	  }, {
	    key: "updateUsers",
	    value: function updateUsers() {
	      this.userRegistry.users.forEach(function (userFields) {
	        if (userFields.localUser || userFields.state != UserState.Connected) {
	          if (this.elements.users[userFields.id]) {
	            BX.remove(this.elements.users[userFields.id]);
	            this.elements.users[userFields.id] = null;
	          }
	        } else {
	          var newNode = this.renderUser(userFields);
	          if (this.elements.users[userFields.id]) {
	            BX.replace(this.elements.users[userFields.id], newNode);
	          } else {
	            this.elements.root.appendChild(newNode);
	          }
	          this.elements.users[userFields.id] = newNode;
	        }
	      }, this);
	    }
	  }, {
	    key: "_onUserAdded",
	    value: function _onUserAdded(event) {
	      this.updateUsers();
	    }
	  }, {
	    key: "_onUserChanged",
	    value: function _onUserChanged(event) {
	      this.updateUsers();
	    }
	  }, {
	    key: "mount",
	    value: function mount(parentElement) {
	      parentElement.appendChild(this.render());
	    }
	  }, {
	    key: "dismount",
	    value: function dismount() {
	      if (!this.elements.root) {
	        return;
	      }
	      BX.remove(this.elements.root);
	    }
	  }]);
	  return UserSelectorMobile;
	}();
	var MobileSlider = /*#__PURE__*/function () {
	  function MobileSlider(config) {
	    babelHelpers.classCallCheck(this, MobileSlider);
	    this.parent = config.parent || null;
	    this.content = config.content || null;
	    this.elements = {
	      background: null,
	      root: null,
	      handle: null,
	      body: null
	    };
	    this.callbacks = {
	      onClose: BX.prop.getFunction(config, "onClose", BX.DoNothing),
	      onDestroy: BX.prop.getFunction(config, "onDestroy", BX.DoNothing)
	    };
	    this.touchStartY = 0;
	    this.processedTouchId = 0;
	  }
	  babelHelpers.createClass(MobileSlider, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.background = main_core.Dom.create("div", {
	        props: {
	          className: "bx-videocall-mobile-menu-background"
	        },
	        events: {
	          click: this._onBackgroundClick.bind(this)
	        }
	      });
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-videocall-mobile-menu-container"
	        },
	        children: [this.elements.handle = main_core.Dom.create("div", {
	          props: {
	            className: "bx-videocall-mobile-menu-handle"
	          }
	        }), this.elements.body = main_core.Dom.create("div", {
	          props: {
	            className: "bx-videocall-mobile-menu"
	          },
	          children: [this.content]
	        })],
	        events: {
	          touchstart: this._onTouchStart.bind(this),
	          touchmove: this._onTouchMove.bind(this),
	          touchend: this._onTouchEnd.bind(this)
	        }
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.parent) {
	        this.render();
	        this.parent.appendChild(this.elements.root);
	        this.parent.appendChild(this.elements.background);
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      BX.remove(this.elements.root);
	      BX.remove(this.elements.background);
	      this.callbacks.onClose();
	    }
	  }, {
	    key: "closeWithAnimation",
	    value: function closeWithAnimation() {
	      if (!this.elements.root) {
	        return;
	      }
	      this.elements.root.classList.add("closing");
	      this.elements.background.classList.add("closing");
	      this.elements.root.addEventListener("animationend", function () {
	        this.close();
	      }.bind(this));
	    }
	  }, {
	    key: "_onTouchStart",
	    value: function _onTouchStart(e) {
	      this.touchStartY = e.pageY;
	      if (this.processedTouchId || e.touches.length > 1) {
	        return;
	      }
	      if (e.target == this.elements.header || e.target == this.elements.root || this.elements.body.scrollTop === 0) {
	        this.processedTouchId = e.touches[0].identifier;
	      }
	    }
	  }, {
	    key: "_onTouchMove",
	    value: function _onTouchMove(e) {
	      if (e.touches.length > 1) {
	        return;
	      }
	      if (e.touches[0].identifier != this.processedTouchId) {
	        return;
	      }
	      var delta = this.touchStartY - e.pageY;
	      if (delta > 0) {
	        delta = 0;
	      }
	      this.elements.root.style.bottom = delta + "px";
	      if (delta) {
	        e.preventDefault();
	      }
	    }
	  }, {
	    key: "_onTouchEnd",
	    value: function _onTouchEnd(e) {
	      var allowProcessing = false;
	      for (var i = 0; i < e.changedTouches.length; i++) {
	        if (e.changedTouches[i].identifier == this.processedTouchId) {
	          allowProcessing = true;
	          break;
	        }
	      }
	      if (!allowProcessing) {
	        return;
	      }
	      var delta = e.pageY - this.touchStartY;
	      if (delta > 100) {
	        this.closeWithAnimation();
	        e.preventDefault();
	      } else {
	        this.elements.root.style.removeProperty("bottom");
	      }
	      this.processedTouchId = 0;
	      this.touchStartY = 0;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.callbacks.onDestroy();
	      this.elements = {};
	      this.callbacks = {};
	      this.parent = null;
	    }
	  }, {
	    key: "_onBackgroundClick",
	    value: function _onBackgroundClick() {
	      this.closeWithAnimation();
	    }
	  }]);
	  return MobileSlider;
	}();
	var MobileMenu = /*#__PURE__*/function () {
	  function MobileMenu(config) {
	    babelHelpers.classCallCheck(this, MobileMenu);
	    this.parent = config.parent || null;
	    this.header = BX.prop.getString(config, "header", "");
	    this.largeIcons = BX.prop.getBoolean(config, "largeIcons", false);
	    this.slider = null;
	    var items = BX.prop.getArray(config, "items", []);
	    if (items.length === 0) {
	      throw Error("Items array should not be empty");
	    }
	    this.items = items.filter(function (item) {
	      return babelHelpers["typeof"](item) === "object" && !!item;
	    }).map(function (item) {
	      return new MobileMenuItem(item);
	    });
	    this.elements = {
	      root: null,
	      header: null,
	      body: null
	    };
	    this.callbacks = {
	      onClose: BX.prop.getFunction(config, "onClose", BX.DoNothing),
	      onDestroy: BX.prop.getFunction(config, "onDestroy", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(MobileMenu, [{
	    key: "render",
	    value: function render() {
	      var _this = this;
	      this.elements.header = main_core.Dom.create("div", {
	        props: {
	          className: "bx-videocall-mobile-menu-header"
	        },
	        text: this.header
	      });
	      this.elements.body = main_core.Dom.create("div", {
	        props: {
	          className: "bx-videocall-mobile-menu-body" + (this.largeIcons ? " bx-videocall-mobile-menu-large" : "")
	        }
	      });
	      this.items.forEach(function (item) {
	        if (item) {
	          _this.elements.body.appendChild(item.render());
	        }
	      });
	      return BX.createFragment([this.elements.header, this.elements.body]);
	    }
	  }, {
	    key: "setHeader",
	    value: function setHeader(header) {
	      this.header = header;
	      if (this.elements.header) {
	        this.elements.header.innerText = header;
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.slider) {
	        this.slider = new MobileSlider({
	          parent: this.parent,
	          content: this.render(),
	          onClose: this.onSliderClose.bind(this),
	          onDestroy: this.onSliderDestroy.bind(this)
	        });
	      }
	      this.slider.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.slider) {
	        this.slider.close();
	      }
	    }
	  }, {
	    key: "onSliderClose",
	    value: function onSliderClose() {
	      this.slider.destroy();
	    }
	  }, {
	    key: "onSliderDestroy",
	    value: function onSliderDestroy() {
	      this.slider = null;
	      this.destroy();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.slider) {
	        this.slider.destroy();
	      }
	      this.slider = null;
	      this.items.forEach(function (item) {
	        item.destroy();
	      });
	      this.items = [];
	      this.callbacks.onDestroy();
	      this.elements = {};
	      this.callbacks = {};
	      this.parent = null;
	    }
	  }]);
	  return MobileMenu;
	}();
	var MobileMenuItem = /*#__PURE__*/function () {
	  function MobileMenuItem(config) {
	    babelHelpers.classCallCheck(this, MobileMenuItem);
	    this.id = BX.prop.getString(config, "id", Util.getUuidv4());
	    this.icon = BX.prop.getString(config, "icon", "");
	    this.iconClass = BX.prop.getString(config, "iconClass", "");
	    this.text = BX.prop.getString(config, "text", "");
	    this.showSubMenu = BX.prop.getBoolean(config, "showSubMenu", false);
	    this.separator = BX.prop.getBoolean(config, "separator", false);
	    this.enabled = BX.prop.getBoolean(config, "enabled", true);
	    this.userModel = BX.prop.get(config, "userModel", null);
	    if (this.userModel) {
	      this._userChangeHandler = this._onUserChange.bind(this);
	      this.subscribeUserEvents();
	      this.text = this.userModel.name;
	      this.icon = this.userModel.avatar;
	      this.iconClass = "user-avatar";
	    }
	    this.elements = {
	      root: null,
	      icon: null,
	      content: null,
	      submenu: null,
	      separator: null,
	      mic: null,
	      cam: null
	    };
	    this.callbacks = {
	      click: BX.prop.getFunction(config, "onClick", BX.DoNothing),
	      clickSubMenu: BX.prop.getFunction(config, "onClickSubMenu", BX.DoNothing)
	    };
	  }
	  babelHelpers.createClass(MobileMenuItem, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      if (this.separator) {
	        this.elements.root = main_core.Dom.create("hr", {
	          props: {
	            className: "bx-videocall-mobile-menu-item-separator"
	          }
	        });
	      } else {
	        this.elements.root = main_core.Dom.create("div", {
	          props: {
	            className: "bx-videocall-mobile-menu-item" + (this.enabled ? "" : " disabled")
	          },
	          children: [this.elements.icon = main_core.Dom.create("div", {
	            props: {
	              className: "bx-videocall-mobile-menu-item-icon " + this.iconClass
	            }
	          }), this.elements.content = main_core.Dom.create("div", {
	            props: {
	              className: "bx-videocall-mobile-menu-item-content"
	            },
	            children: [main_core.Dom.create("span", {
	              text: this.text
	            })]
	          })],
	          events: {
	            click: this.callbacks.click
	          }
	        });
	        if (this.icon != "") {
	          this.elements.icon.style.backgroundImage = "url(\"" + this.icon + "\")";
	        }
	        if (this.showSubMenu) {
	          this.elements.submenu = main_core.Dom.create("div", {
	            props: {
	              className: "bx-videocall-mobile-menu-item-submenu-icon"
	            }
	          });
	          this.elements.root.appendChild(this.elements.submenu);
	        }
	        if (this.userModel) {
	          this.elements.mic = main_core.Dom.create("div", {
	            props: {
	              className: "bx-videocall-mobile-menu-icon-user bx-call-view-icon-red-microphone-off"
	            }
	          });
	          this.elements.cam = main_core.Dom.create("div", {
	            props: {
	              className: "bx-videocall-mobile-menu-icon-user bx-call-view-icon-red-camera-off"
	            }
	          });
	          if (!this.userModel.cameraState) {
	            this.elements.content.prepend(this.elements.cam);
	          }
	          if (!this.userModel.microphoneState) {
	            this.elements.content.prepend(this.elements.mic);
	          }
	        }
	      }
	      return this.elements.root;
	    }
	  }, {
	    key: "updateUserIcons",
	    value: function updateUserIcons() {
	      if (!this.userModel) {
	        return;
	      }
	      if (this.userModel.microphoneState) {
	        BX.remove(this.elements.mic);
	      } else {
	        this.elements.content.prepend(this.elements.mic);
	      }
	      if (this.userModel.cameraState) {
	        BX.remove(this.elements.cam);
	      } else {
	        this.elements.content.prepend(this.elements.cam);
	      }
	    }
	  }, {
	    key: "subscribeUserEvents",
	    value: function subscribeUserEvents() {
	      this.userModel.subscribe("changed", this._userChangeHandler);
	    }
	  }, {
	    key: "_onUserChange",
	    value: function _onUserChange(event) {
	      this.updateUserIcons();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.userModel) {
	        this.userModel.unsubscribe("changed", this._userChangeHandler);
	        this.userModel = null;
	      }
	      this.callbacks = null;
	      this.elements = null;
	    }
	  }]);
	  return MobileMenuItem;
	}();

	function logPlaybackError(error) {
	  console.error("Playback start error: ", error);
	}

	var CallUser = /*#__PURE__*/function () {
	  function CallUser() {
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, CallUser);
	    babelHelpers.defineProperty(this, "elements", {});
	    this.userModel = config.userModel;
	    this.userModel.subscribe("changed", this._onUserFieldChanged.bind(this));
	    this.parentContainer = config.parentContainer;
	    this.screenSharingUser = main_core.Type.isBoolean(config.screenSharingUser) ? config.screenSharingUser : false;
	    this.allowBackgroundItem = main_core.Type.isBoolean(config.allowBackgroundItem) ? config.allowBackgroundItem : true;
	    this.allowMaskItem = main_core.Type.isBoolean(config.allowMaskItem) ? config.allowMaskItem : true;
	    this._allowPinButton = main_core.Type.isBoolean(config.allowPinButton) ? config.allowPinButton : true;
	    this._visible = true;
	    this._audioTrack = config.audioTrack;
	    this._audioStream = this._audioTrack ? new MediaStream([this._audioTrack]) : null;
	    this._videoTrack = config.videoTrack;
	    this._stream = this._videoTrack ? new MediaStream([this._videoTrack]) : null;
	    this._videoRenderer = null;
	    this._flipVideo = false;
	    this.hidden = false;
	    this.videoBlurState = false;
	    this.isChangingName = false;
	    this.incomingVideoConstraints = {
	      width: 0,
	      height: 0
	    };
	    if (config.audioElement) {
	      this.elements.audio = config.audioElement;
	    }
	    this.callBacks = {
	      onClick: main_core.Type.isFunction(config.onClick) ? config.onClick : BX.DoNothing,
	      onUserRename: main_core.Type.isFunction(config.onUserRename) ? config.onUserRename : BX.DoNothing,
	      onUserRenameInputFocus: main_core.Type.isFunction(config.onUserRenameInputFocus) ? config.onUserRenameInputFocus : BX.DoNothing,
	      onUserRenameInputBlur: main_core.Type.isFunction(config.onUserRenameInputBlur) ? config.onUserRenameInputBlur : BX.DoNothing,
	      onPin: main_core.Type.isFunction(config.onPin) ? config.onPin : BX.DoNothing,
	      onUnPin: main_core.Type.isFunction(config.onUnPin) ? config.onUnPin : BX.DoNothing
	    };
	    this.checkAspectInterval = setInterval(this.checkVideoAspect.bind(this), 500);
	  }
	  babelHelpers.createClass(CallUser, [{
	    key: "render",
	    value: function render() {
	      var _this = this;
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user"
	        },
	        dataset: {
	          userId: this.userModel.id,
	          order: this.userModel.order
	        },
	        children: [this.elements.videoBorder = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-border"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-talking-icon"
	            }
	          })]
	        }), this.elements.container = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-inner"
	          },
	          children: [this.elements.avatarBackground = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-avatar-background"
	            }
	          }), this.elements.avatarContainer = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-avatar-border"
	            },
	            children: [this.elements.avatar = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-avatar"
	              }
	            }), main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-avatar-overlay-border"
	              }
	            })]
	          }), this.elements.panel = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-panel"
	            }
	          }), this.elements.state = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-status-text"
	            },
	            text: this.getStateMessage(this.userModel.state)
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-bottom"
	            },
	            children: [this.elements.nameContainer = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-name-container" + (this.userModel.allowRename && !this.userModel.wasRenamed ? " hidden" : "")
	              },
	              children: [this.elements.micState = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-user-device-state mic" + (this.userModel.microphoneState ? " hidden" : "")
	                }
	              }), this.elements.cameraState = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-user-device-state camera" + (this.userModel.cameraState ? " hidden" : "")
	                }
	              }), this.elements.name = main_core.Dom.create("span", {
	                props: {
	                  className: "bx-messenger-videocall-user-name"
	                },
	                text: this.screenSharingUser ? BX.message('IM_CALL_USERS_SCREEN').replace("#NAME#", this.userModel.name) : this.userModel.name
	              }), this.elements.changeNameIcon = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-user-change-name-icon hidden"
	                }
	              })],
	              events: {
	                click: this.toggleNameInput.bind(this)
	              }
	            }), this.elements.changeNameContainer = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-change-name-container hidden"
	              },
	              children: [this.elements.changeNameCancel = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-user-change-name-cancel"
	                },
	                events: {
	                  click: this.toggleNameInput.bind(this)
	                }
	              }), this.elements.changeNameInput = main_core.Dom.create("input", {
	                props: {
	                  className: "bx-messenger-videocall-user-change-name-input"
	                },
	                attrs: {
	                  type: 'text',
	                  value: this.userModel.name
	                },
	                events: {
	                  keydown: this.onNameInputKeyDown.bind(this),
	                  focus: this.callBacks.onUserRenameInputFocus,
	                  blur: this.callBacks.onUserRenameInputBlur
	                }
	              }), this.elements.changeNameConfirm = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-user-change-name-confirm"
	                },
	                events: {
	                  click: this.changeName.bind(this)
	                }
	              }), this.elements.changeNameLoader = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-user-change-name-loader hidden"
	                },
	                children: [main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-videocall-user-change-name-loader-icon"
	                  }
	                })]
	              })]
	            }), this.elements.introduceYourselfContainer = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-user-introduce-yourself-container" + (!this.userModel.allowRename || this.userModel.wasRenamed ? " hidden" : "")
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-user-introduce-yourself-text"
	                },
	                text: BX.message('IM_CALL_GUEST_INTRODUCE_YOURSELF')
	              })],
	              events: {
	                click: this.toggleNameInput.bind(this)
	              }
	            })]
	          }), this.elements.floorRequest = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-user-floor-request bx-messenger-videocall-floor-request-icon"
	            }
	          })]
	        })],
	        style: {
	          order: this.userModel.order
	        },
	        events: {
	          click: function (e) {
	            e.stopPropagation();
	            this.callBacks.onClick({
	              userId: this.id
	            });
	          }.bind(this)
	        }
	      });
	      if (this.userModel.talking) {
	        this.elements.root.classList.add("bx-messenger-videocall-user-talking");
	      }
	      if (this.userModel.localUser) {
	        this.elements.root.classList.add("bx-messenger-videocall-user-self");
	      }
	      if (this.userModel.avatar !== '') {
	        this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
	      } else {
	        this.elements.root.style.removeProperty("--avatar");
	      }
	      this.elements.videoContainer = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-video-container"
	        },
	        children: [this.elements.video = main_core.Dom.create("video", {
	          props: {
	            className: "bx-messenger-videocall-video",
	            volume: 0,
	            autoplay: true
	          },
	          attrs: {
	            playsinline: true,
	            muted: true
	          }
	        })]
	      });
	      this.elements.container.appendChild(this.elements.videoContainer);
	      if (this.stream && this.stream.active) {
	        this.elements.video.srcObject = this.stream;
	      }
	      if (this.flipVideo) {
	        this.elements.video.classList.add("bx-messenger-videocall-video-flipped");
	      }
	      if (this.userModel.screenState) {
	        this.elements.video.classList.add("bx-messenger-videocall-video-contain");
	      }
	      if (this.userModel.cameraState && this.userModel.microphoneState) {
	        this.elements.nameContainer.classList.add("extra-padding");
	      }

	      //this.elements.nameContainer.appendChild(this.elements.micState);

	      // todo: show button only if user have the permission to remove user
	      /*this.elements.removeButton = Dom.create("div", {
	      	props: {className: "bx-messenger-videocall-user-close"}
	      });
	      	this.elements.container.appendChild(this.elements.removeButton);*/

	      this.elements.buttonMask = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-panel-button mask"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-icon mask"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-text"
	          },
	          text: BX.message("IM_CALL_CHANGE_MASK")
	        })],
	        events: {
	          click: function click() {
	            return BackgroundDialog.open({
	              'tab': 'mask'
	            });
	          }
	        }
	      });
	      this.elements.buttonBackground = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-panel-button"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-icon background"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-text"
	          },
	          text: BX.message("IM_CALL_CHANGE_BACKGROUND")
	        })],
	        events: {
	          click: function click() {
	            return BackgroundDialog.open();
	          }
	        }
	      });
	      this.elements.buttonMenu = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-panel-button"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-icon menu"
	          }
	        })],
	        events: {
	          click: function click() {
	            return _this.showMenu();
	          }
	        }
	      });
	      this.elements.buttonPin = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-panel-button"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-icon pin"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-text"
	          },
	          text: BX.message("IM_CALL_PIN")
	        })],
	        events: {
	          click: function click(e) {
	            e.stopPropagation();
	            _this.callBacks.onPin({
	              userId: _this.userModel.id
	            });
	          }
	        }
	      });
	      this.elements.buttonUnPin = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-panel-button"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-icon unpin"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-panel-button-text"
	          },
	          text: BX.message("IM_CALL_UNPIN")
	        })],
	        events: {
	          click: function click(e) {
	            e.stopPropagation();
	            _this.callBacks.onUnPin();
	          }
	        }
	      });
	      this.updatePanelDeferred();
	      return this.elements.root;
	    }
	  }, {
	    key: "setIncomingVideoConstraints",
	    value: function setIncomingVideoConstraints(width, height) {
	      this.incomingVideoConstraints.width = typeof width === "undefined" ? this.incomingVideoConstraints.width : width;
	      this.incomingVideoConstraints.height = typeof height === "undefined" ? this.incomingVideoConstraints.height : height;
	      if (!this.videoRenderer) {
	        return;
	      }

	      // vox low quality temporary workaround
	      // (disabled to test quality)
	      // if (this.incomingVideoConstraints.width >= 320 && this.incomingVideoConstraints.width <= 640)
	      // {
	      // 	this.incomingVideoConstraints.width = 640;
	      // }
	      // if (this.incomingVideoConstraints.height >= 180 && this.incomingVideoConstraints.height <= 360)
	      // {
	      // 	this.incomingVideoConstraints.height = 360;
	      // }

	      this.videoRenderer.requestVideoSize(this.incomingVideoConstraints.width, this.incomingVideoConstraints.height);
	    }
	  }, {
	    key: "updateRendererState",
	    value: function updateRendererState() {
	      /*if (this.videoRenderer)
	      {
	      	if (this.visible)
	      	{
	      		this.videoRenderer.enable();
	      	}
	      	else
	      	{
	      		this.videoRenderer.disable();
	      	}
	      }*/

	      /*if (this.elements.video && this.elements.video.srcObject)
	      {
	      	if (this.visible)
	      	{
	      		this.elements.video.play();
	      	}
	      	else
	      	{
	      		this.elements.video.pause();
	      	}
	      }*/
	    }
	  }, {
	    key: "_onUserFieldChanged",
	    value: function _onUserFieldChanged(event) {
	      var eventData = event.data;
	      switch (eventData.fieldName) {
	        case "id":
	          return this.updateId();
	        case "name":
	          return this.updateName();
	        case "avatar":
	          return this.updateAvatar();
	        case "state":
	          return this.updateState();
	        case "talking":
	          return this.updateTalking();
	        case "microphoneState":
	          return this.updateMicrophoneState();
	        case "cameraState":
	          return this.updateCameraState();
	        case "videoPaused":
	          return this.updateVideoPaused();
	        case "floorRequestState":
	          return this.updateFloorRequestState();
	        case "screenState":
	          return this.updateScreenState();
	        case "pinned":
	          return this.updatePanel();
	        case "allowRename":
	          return this.updateRenameAllowed();
	        case "wasRenamed":
	          return this.updateWasRenamed();
	        case "renameRequested":
	          return this.updateRenameRequested();
	        case "order":
	          return this.updateOrder();
	      }
	    }
	  }, {
	    key: "toggleRenameIcon",
	    value: function toggleRenameIcon() {
	      if (!this.userModel.allowRename) {
	        return;
	      }
	      this.elements.changeNameIcon.classList.toggle('hidden');
	    }
	  }, {
	    key: "toggleNameInput",
	    value: function toggleNameInput(event) {
	      if (!this.userModel.allowRename || !this.elements.root) {
	        return;
	      }
	      event.stopPropagation();
	      if (this.isChangingName) {
	        this.isChangingName = false;
	        if (!this.userModel.wasRenamed) {
	          this.elements.introduceYourselfContainer.classList.remove('hidden');
	          this.elements.changeNameContainer.classList.add('hidden');
	        } else {
	          this.elements.changeNameContainer.classList.add('hidden');
	          this.elements.nameContainer.classList.remove('hidden');
	        }
	      } else {
	        if (!this.userModel.wasRenamed) {
	          this.elements.introduceYourselfContainer.classList.add('hidden');
	        }
	        this.isChangingName = true;
	        this.elements.nameContainer.classList.add('hidden');
	        this.elements.changeNameContainer.classList.remove('hidden');
	        this.elements.changeNameInput.value = this.userModel.name;
	        this.elements.changeNameInput.focus();
	        this.elements.changeNameInput.select();
	      }
	    }
	  }, {
	    key: "onNameInputKeyDown",
	    value: function onNameInputKeyDown(event) {
	      if (!this.userModel.allowRename) {
	        return;
	      }

	      //enter
	      if (event.keyCode === 13) {
	        this.changeName(event);
	      }
	      //escape
	      else if (event.keyCode === 27) {
	        this.toggleNameInput(event);
	      }
	    }
	  }, {
	    key: "onNameInputFocus",
	    value: function onNameInputFocus(event) {}
	  }, {
	    key: "onNameInputBlur",
	    value: function onNameInputBlur(event) {}
	  }, {
	    key: "changeName",
	    value: function changeName(event) {
	      event.stopPropagation();
	      var inputValue = this.elements.changeNameInput.value;
	      var newName = inputValue.trim();
	      var needToUpdate = true;
	      if (newName === this.userModel.name || newName === '') {
	        needToUpdate = false;
	      }
	      if (needToUpdate) {
	        this.elements.changeNameConfirm.classList.toggle('hidden');
	        this.elements.changeNameLoader.classList.toggle('hidden');
	        this.callBacks.onUserRename(newName);
	      } else {
	        this.toggleNameInput(event);
	      }
	    }
	  }, {
	    key: "showMenu",
	    value: function showMenu() {
	      var _this2 = this;
	      var menuItems = [];
	      if (this.userModel.localUser && this.allowBackgroundItem) {
	        menuItems.push({
	          text: this.allowMaskItem ? BX.message("IM_CALL_CHANGE_BG_MASK") : BX.message("IM_CALL_CHANGE_BACKGROUND"),
	          onclick: function onclick() {
	            _this2.menu.close();
	            BackgroundDialog.open();
	          }
	        });
	      }
	      if (menuItems.length === 0) {
	        return;
	      }
	      var rect = main_core.Dom.getRelativePosition(this.elements.buttonMenu, this.parentContainer);
	      this.menu = new main_popup.Menu({
	        id: 'call-view-user-menu-' + this.userModel.id,
	        bindElement: {
	          left: rect.left,
	          top: rect.top,
	          bottom: rect.bottom
	        },
	        items: menuItems,
	        targetContainer: this.parentContainer,
	        autoHide: true,
	        closeByEsc: true,
	        offsetTop: 0,
	        offsetLeft: 0,
	        bindOptions: {
	          position: 'bottom'
	        },
	        angle: true,
	        overlay: {
	          backgroundColor: 'white',
	          opacity: 0
	        },
	        cacheable: false,
	        events: {
	          onPopupDestroy: function onPopupDestroy() {
	            return _this2.menu = null;
	          }
	        }
	      });
	      this.menu.show();
	    }
	  }, {
	    key: "updateAvatar",
	    value: function updateAvatar() {
	      if (this.elements.root) {
	        if (this.userModel.avatar !== '') {
	          this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
	        } else {
	          this.elements.root.style.removeProperty("--avatar");
	        }
	      }
	    }
	  }, {
	    key: "updateId",
	    value: function updateId() {
	      if (this.elements.root) {
	        this.elements.root.dataset.userId = this.userModel.id;
	      }
	    }
	  }, {
	    key: "updateName",
	    value: function updateName() {
	      if (this.isChangingName) {
	        this.isChangingName = false;
	        this.elements.changeNameConfirm.classList.toggle('hidden');
	        this.elements.changeNameLoader.classList.toggle('hidden');
	        this.elements.changeNameContainer.classList.add('hidden');
	        this.elements.nameContainer.classList.remove('hidden');
	      }
	      if (this.elements.name) {
	        this.elements.name.innerText = this.screenSharingUser ? BX.message('IM_CALL_USERS_SCREEN').replace("#NAME#", this.userModel.name) : this.userModel.name;
	      }
	    }
	  }, {
	    key: "updateRenameAllowed",
	    value: function updateRenameAllowed() {
	      if (this.userModel.allowRename && this.elements.nameContainer && this.elements.introduceYourselfContainer) {
	        this.elements.nameContainer.classList.add('hidden');
	        this.elements.introduceYourselfContainer.classList.remove('hidden');
	      }
	    }
	  }, {
	    key: "updateWasRenamed",
	    value: function updateWasRenamed() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.userModel.allowRename) {
	        this.elements.introduceYourselfContainer.classList.add('hidden');
	        this.elements.changeNameIcon.classList.remove('hidden');
	        if (this.elements.changeNameContainer.classList.contains('hidden')) {
	          this.elements.nameContainer.classList.remove('hidden');
	        }
	      }
	    }
	  }, {
	    key: "updateRenameRequested",
	    value: function updateRenameRequested() {
	      if (this.userModel.allowRename) {
	        this.elements.introduceYourselfContainer.classList.add('hidden');
	      }
	    }
	  }, {
	    key: "updateOrder",
	    value: function updateOrder() {
	      if (this.elements.root) {
	        this.elements.root.dataset.order = this.userModel.order;
	        this.elements.root.style.order = this.userModel.order;
	      }
	    }
	  }, {
	    key: "updatePanelDeferred",
	    value: function updatePanelDeferred() {
	      setTimeout(this.updatePanel.bind(this), 0);
	    }
	  }, {
	    key: "updatePanel",
	    value: function updatePanel() {
	      if (!this.isMounted()) {
	        return;
	      }
	      var width = this.elements.root.offsetWidth;
	      main_core.Dom.clean(this.elements.panel);
	      if (this.userModel.localUser && this.allowBackgroundItem) {
	        if (width > 300) {
	          if (this.allowMaskItem) {
	            this.elements.panel.appendChild(this.elements.buttonMask);
	          }
	          this.elements.panel.appendChild(this.elements.buttonBackground);
	        } else {
	          this.elements.panel.appendChild(this.elements.buttonMenu);
	        }
	      }
	      if (!this.userModel.localUser && this.allowPinButton) {
	        if (this.userModel.pinned) {
	          this.elements.panel.appendChild(this.elements.buttonUnPin);
	        } else {
	          this.elements.panel.appendChild(this.elements.buttonPin);
	        }
	        if (width > 250) {
	          this.elements.buttonPin.classList.remove("no-text");
	          this.elements.buttonUnPin.classList.remove("no-text");
	        } else {
	          this.elements.buttonPin.classList.add("no-text");
	          this.elements.buttonUnPin.classList.add("no-text");
	        }
	      }
	    }
	  }, {
	    key: "update",
	    value: function update() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.hasVideo() /* && this.visible*/) {
	        if (this.visible) {
	          if (this.videoRenderer) {
	            this.videoRenderer.render(this.elements.video);
	          } else if (this.elements.video.srcObject != this.stream) {
	            this.elements.video.srcObject = this.stream;
	          }
	        }
	        main_core.Dom.remove(this.elements.avatarContainer);
	        this.elements.video.classList.toggle("bx-messenger-videocall-video-flipped", this.flipVideo);
	        this.elements.video.classList.toggle("bx-messenger-videocall-video-contain", this.userModel.screenState);
	      } else {
	        this.elements.video.srcObject = null;
	        this.elements.container.insertBefore(this.elements.avatarContainer, this.elements.panel);
	      }
	      this.updatePanelDeferred();
	    }
	  }, {
	    key: "playAudio",
	    value: function playAudio() {
	      if (!this.audioStream) {
	        this.elements.audio.srcObject = null;
	        return;
	      }
	      if (this.speakerId && main_core.Type.isFunction(this.elements.audio.setSinkId)) {
	        this.elements.audio.setSinkId(this.speakerId).then(function () {
	          this.elements.audio.srcObject = this.audioStream;
	          this.elements.audio.play()["catch"](logPlaybackError);
	        }.bind(this))["catch"](console.error);
	      } else {
	        this.elements.audio.srcObject = this.audioStream;
	        this.elements.audio.play()["catch"](logPlaybackError);
	      }
	    }
	  }, {
	    key: "playVideo",
	    value: function playVideo() {
	      if (this.elements.video) {
	        this.elements.video.play()["catch"](logPlaybackError);
	      }
	    }
	  }, {
	    key: "blurVideo",
	    value: function blurVideo(blurState) {
	      blurState = !!blurState;
	      if (this.videoBlurState == blurState) {
	        return;
	      }
	      this.videoBlurState = blurState;
	      if (this.elements.video) {
	        this.elements.video.classList.toggle('bx-messenger-videocall-video-blurred');
	      }
	    }
	  }, {
	    key: "getStateMessage",
	    value: function getStateMessage(userState, videoPaused) {
	      switch (userState) {
	        case UserState.Idle:
	          return "";
	        case UserState.Calling:
	          return BX.message("IM_M_CALL_STATUS_WAIT_ANSWER");
	        case UserState.Declined:
	          return BX.message("IM_M_CALL_STATUS_DECLINED");
	        case UserState.Ready:
	        case UserState.Connecting:
	          return BX.message("IM_M_CALL_STATUS_WAIT_CONNECT");
	        case UserState.Connected:
	          return videoPaused ? BX.message("IM_M_CALL_STATUS_VIDEO_PAUSED") : "";
	        case UserState.Failed:
	          return BX.message("IM_M_CALL_STATUS_CONNECTION_ERROR");
	        case UserState.Unavailable:
	          return BX.message("IM_M_CALL_STATUS_UNAVAILABLE");
	        default:
	          return "";
	      }
	    }
	  }, {
	    key: "mount",
	    value: function mount(parent, force) {
	      force = force === true;
	      if (!this.elements.root) {
	        this.render();
	      }
	      if (this.isMounted() && this.elements.root.parentElement == parent && !force) {
	        this.updatePanelDeferred();
	        return false;
	      }
	      parent.appendChild(this.elements.root);
	      this.update();
	    }
	  }, {
	    key: "dismount",
	    value: function dismount() {
	      // this.visible = false;
	      if (!this.isMounted()) {
	        return false;
	      }
	      this.elements.video.srcObject = null;
	      main_core.Dom.remove(this.elements.root);
	    }
	  }, {
	    key: "isMounted",
	    value: function isMounted() {
	      return !!(this.elements.root && this.elements.root.parentElement);
	    }
	  }, {
	    key: "updateState",
	    value: function updateState() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.userModel.state == UserState.Calling || this.userModel.state == UserState.Connecting) {
	        this.elements.avatar.classList.add("bx-messenger-videocall-user-avatar-pulse");
	      } else {
	        this.elements.avatar.classList.remove("bx-messenger-videocall-user-avatar-pulse");
	      }
	      this.elements.state.innerText = this.getStateMessage(this.userModel.state, this.userModel.videoPaused);
	      this.update();
	    }
	  }, {
	    key: "updateTalking",
	    value: function updateTalking() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.userModel.talking) {
	        this.elements.root.classList.add("bx-messenger-videocall-user-talking");
	      } else {
	        this.elements.root.classList.remove("bx-messenger-videocall-user-talking");
	      }
	    }
	  }, {
	    key: "updateMicrophoneState",
	    value: function updateMicrophoneState() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.userModel.microphoneState) {
	        this.elements.micState.classList.add("hidden");
	      } else {
	        this.elements.micState.classList.remove("hidden");
	      }
	      if (this.userModel.cameraState && this.userModel.microphoneState) {
	        this.elements.nameContainer.classList.add("extra-padding");
	      } else {
	        this.elements.nameContainer.classList.remove("extra-padding");
	      }
	    }
	  }, {
	    key: "updateCameraState",
	    value: function updateCameraState() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.userModel.cameraState) {
	        this.elements.cameraState.classList.add("hidden");
	      } else {
	        this.elements.cameraState.classList.remove("hidden");
	      }
	      if (this.userModel.cameraState && this.userModel.microphoneState) {
	        this.elements.nameContainer.classList.add("extra-padding");
	      } else {
	        this.elements.nameContainer.classList.remove("extra-padding");
	      }
	    }
	  }, {
	    key: "updateVideoPaused",
	    value: function updateVideoPaused() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (this.stream && this.hasVideo()) {
	        this.blurVideo(this.userModel.videoPaused);
	      }
	      this.updateState();
	    }
	  }, {
	    key: "updateFloorRequestState",
	    value: function updateFloorRequestState() {
	      if (!this.elements.floorRequest) {
	        return;
	      }
	      if (this.userModel.floorRequestState) {
	        this.elements.floorRequest.classList.add("active");
	      } else {
	        this.elements.floorRequest.classList.remove("active");
	      }
	    }
	  }, {
	    key: "updateScreenState",
	    value: function updateScreenState() {
	      if (!this.elements.video) {
	        return;
	      }
	      if (this.userModel.screenState) {
	        this.elements.video.classList.add("bx-messenger-videocall-video-contain");
	      } else {
	        this.elements.video.classList.remove("bx-messenger-videocall-video-contain");
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (!this.elements.root) {
	        return;
	      }
	      this.elements.root.dataset.hidden = 1;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.elements.root) {
	        return;
	      }
	      delete this.elements.root.dataset.hidden;
	    }
	  }, {
	    key: "hasVideo",
	    value: function hasVideo() {
	      return this.userModel.state == UserState.Connected && (!!this._videoTrack || !!this._videoRenderer);
	    }
	  }, {
	    key: "checkVideoAspect",
	    value: function checkVideoAspect() {
	      if (!this.elements.video) {
	        return;
	      }
	      if (this.elements.video.videoHeight > this.elements.video.videoWidth) {
	        this.elements.video.classList.add("bx-messenger-videocall-video-vertical");
	      } else {
	        this.elements.video.classList.remove("bx-messenger-videocall-video-vertical");
	      }
	    }
	  }, {
	    key: "releaseStream",
	    value: function releaseStream() {
	      if (this.elements.video) {
	        this.elements.video.srcObject = null;
	      }
	      this.videoTrack = null;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.releaseStream();
	      clearInterval(this.checkAspectInterval);
	    }
	  }, {
	    key: "id",
	    get: function get() {
	      return this.userModel.id;
	    }
	  }, {
	    key: "allowPinButton",
	    get: function get() {
	      return this._allowPinButton;
	    },
	    set: function set(allowPinButton) {
	      if (this._allowPinButton == allowPinButton) {
	        return;
	      }
	      this._allowPinButton = allowPinButton;
	      this.update();
	    }
	  }, {
	    key: "audioTrack",
	    get: function get() {
	      return this._audioTrack;
	    },
	    set: function set(audioTrack) {
	      if (this._audioTrack === audioTrack) {
	        return;
	      }
	      this._audioTrack = audioTrack;
	      this._audioStream = this._audioTrack ? new MediaStream([this._audioTrack]) : null;
	      this.playAudio();
	    }
	  }, {
	    key: "audioStream",
	    get: function get() {
	      return this._audioStream;
	    }
	  }, {
	    key: "flipVideo",
	    get: function get() {
	      return this._flipVideo;
	    },
	    set: function set(flipVideo) {
	      this._flipVideo = flipVideo;
	      this.update();
	    }
	  }, {
	    key: "stream",
	    get: function get() {
	      return this._stream;
	    }
	  }, {
	    key: "visible",
	    get: function get() {
	      return this._visible;
	    },
	    set: function set(visible) {
	      if (this._visible !== visible) {
	        this._visible = visible;
	        this.update();
	        this.updateRendererState();
	      }
	    }
	  }, {
	    key: "videoRenderer",
	    get: function get() {
	      return this._videoRenderer;
	    },
	    set: function set(videoRenderer) {
	      this._videoRenderer = videoRenderer;
	      this.update();
	      this.updateRendererState();
	    }
	  }, {
	    key: "videoTrack",
	    get: function get() {
	      return this._videoTrack;
	    },
	    set: function set(videoTrack) {
	      if (this._videoTrack === videoTrack) {
	        return;
	      }
	      this._videoTrack = videoTrack;
	      this._stream = this._videoTrack ? new MediaStream([this._videoTrack]) : null;
	      this.update();
	    }
	  }]);
	  return CallUser;
	}();

	var lsKey = {
	  defaultMicrophone: 'bx-im-settings-default-microphone',
	  defaultCamera: 'bx-im-settings-default-camera',
	  defaultSpeaker: 'bx-im-settings-default-speaker',
	  enableMicAutoParameters: 'bx-im-settings-enable-mic-auto-parameters',
	  preferHd: 'bx-im-settings-camera-prefer-hd',
	  enableMirroring: 'bx-im-settings-camera-enable-mirroring'
	};
	var Events = {
	  initialized: "initialized",
	  deviceChanged: "deviceChange",
	  onChangeMirroringVideo: "onChangeMirroringVideo"
	};
	var HardwareManager = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(HardwareManager, _EventEmitter);
	  function HardwareManager() {
	    var _this;
	    babelHelpers.classCallCheck(this, HardwareManager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(HardwareManager).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "Events", Events);
	    _this.setEventNamespace('BX.Call.HardwareManager');
	    _this.initialized = false;
	    _this._currentDeviceList = [];
	    _this.updating = false;
	    return _this;
	  }
	  babelHelpers.createClass(HardwareManager, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      if (this.initialized) {
	        return Promise.resolve();
	      }
	      if (this.initPromise) {
	        return this.initPromise;
	      }
	      this.initPromise = new Promise(function (resolve, reject) {
	        _this2.enumerateDevices().then(function (deviceList) {
	          _this2._currentDeviceList = _this2.filterDeviceList(deviceList);
	          navigator.mediaDevices.addEventListener('devicechange', BX.debounce(_this2.onNavigatorDeviceChanged.bind(_this2), 500));
	          _this2.initialized = true;
	          _this2.initPromise = null;
	          _this2.emit(Events.initialized, {});
	          resolve();
	        })["catch"](function (e) {
	          _this2.initPromise = null;
	          reject(e);
	        });
	      });
	      return this.initPromise;
	    }
	  }, {
	    key: "enumerateDevices",
	    value: function enumerateDevices() {
	      return new Promise(function (resolve, reject) {
	        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
	          return reject("NO_WEBRTC");
	        }
	        navigator.mediaDevices.enumerateDevices().then(function (devices) {
	          return resolve(devices);
	        });
	      });
	    }
	  }, {
	    key: "hasCamera",
	    value: function hasCamera() {
	      if (!this.initialized) {
	        throw new Error("HardwareManager is not initialized yet");
	      }
	      return Object.keys(this.cameraList).length > 0;
	    }
	  }, {
	    key: "getMicrophoneList",
	    value: function getMicrophoneList() {
	      if (!this.initialized) {
	        throw new Error("HardwareManager is not initialized yet");
	      }
	      return Object.values(this._currentDeviceList).filter(function (deviceInfo) {
	        return deviceInfo.kind == "audioinput";
	      });
	    }
	  }, {
	    key: "getCameraList",
	    value: function getCameraList() {
	      if (!this.initialized) {
	        throw new Error("HardwareManager is not initialized yet");
	      }
	      return Object.values(this._currentDeviceList).filter(function (deviceInfo) {
	        return deviceInfo.kind == "videoinput";
	      });
	    }
	  }, {
	    key: "getSpeakerList",
	    value: function getSpeakerList() {
	      if (!this.initialized) {
	        throw new Error("HardwareManager is not initialized yet");
	      }
	      return Object.values(this._currentDeviceList).filter(function (deviceInfo) {
	        return deviceInfo.kind == "audiooutput";
	      });
	    }
	  }, {
	    key: "canSelectSpeaker",
	    value: function canSelectSpeaker() {
	      return 'setSinkId' in HTMLMediaElement.prototype;
	    }
	  }, {
	    key: "updateDeviceList",
	    value: function updateDeviceList(e) {
	      var _this3 = this;
	      if (this.updating) {
	        return;
	      }
	      this.updating = true;
	      var removedDevices = this._currentDeviceList;
	      var addedDevices = [];
	      var shouldSkipDeviceChangedEvent = this._currentDeviceList.every(function (deviceInfo) {
	        return deviceInfo.deviceId == "" && deviceInfo.label == "";
	      });
	      navigator.mediaDevices.enumerateDevices().then(function (devices) {
	        devices = _this3.filterDeviceList(devices);
	        devices.forEach(function (deviceInfo) {
	          var index = removedDevices.findIndex(function (dev) {
	            return dev.kind === deviceInfo.kind && dev.deviceId === deviceInfo.deviceId;
	          });
	          if (index != -1) {
	            // device found in previous version
	            removedDevices.splice(index, 1);
	          } else {
	            addedDevices.push(deviceInfo);
	          }
	        });
	        if (!shouldSkipDeviceChangedEvent) {
	          _this3.emit(Events.deviceChanged, {
	            added: addedDevices,
	            removed: removedDevices
	          });
	        }
	        _this3._currentDeviceList = devices;
	        _this3.updating = false;
	      });
	    }
	  }, {
	    key: "filterDeviceList",
	    value: function filterDeviceList(browserDeviceList) {
	      return browserDeviceList.filter(function (device) {
	        switch (device.kind) {
	          case "audioinput":
	            return device.deviceId !== "default" && device.deviceId !== "communications";
	          case "audiooutput":
	            return device.deviceId !== "default";
	          default:
	            return true;
	        }
	      });
	    }
	  }, {
	    key: "onNavigatorDeviceChanged",
	    value: function onNavigatorDeviceChanged(e) {
	      if (!this.initialized) {
	        return;
	      }
	      this.updateDeviceList();
	    }
	  }, {
	    key: "_getDeviceMap",
	    value: function _getDeviceMap(deviceKind) {
	      var result = {};
	      if (!this.initialized) {
	        throw new Error("HardwareManager is not initialized yet");
	      }
	      for (var i = 0; i < this._currentDeviceList.length; i++) {
	        if (this._currentDeviceList[i].kind == deviceKind) {
	          result[this._currentDeviceList[i].deviceId] = this._currentDeviceList[i].label;
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "cameraList",
	    get: function get() {
	      return this._getDeviceMap("videoinput");
	    }
	  }, {
	    key: "microphoneList",
	    get: function get() {
	      return this._getDeviceMap("audioinput");
	    }
	  }, {
	    key: "audioOutputList",
	    get: function get() {
	      return this._getDeviceMap("audiooutput");
	    }
	  }, {
	    key: "defaultMicrophone",
	    get: function get() {
	      var microphoneId = localStorage ? localStorage.getItem(lsKey.defaultMicrophone) : '';
	      return this.microphoneList[microphoneId] ? microphoneId : '';
	    },
	    set: function set(microphoneId) {
	      if (localStorage) {
	        localStorage.setItem(lsKey.defaultMicrophone, microphoneId);
	      }
	    }
	  }, {
	    key: "defaultCamera",
	    get: function get() {
	      var cameraId = localStorage ? localStorage.getItem(lsKey.defaultCamera) : '';
	      return this.cameraList[cameraId] ? cameraId : '';
	    },
	    set: function set(cameraId) {
	      if (localStorage) {
	        localStorage.setItem(lsKey.defaultCamera, cameraId);
	      }
	    }
	  }, {
	    key: "defaultSpeaker",
	    get: function get() {
	      var speakerId = localStorage ? localStorage.getItem(lsKey.defaultSpeaker) : '';
	      return this.audioOutputList[speakerId] ? speakerId : '';
	    },
	    set: function set(speakerId) {
	      if (localStorage) {
	        localStorage.setItem(lsKey.defaultSpeaker, speakerId);
	      }
	    }
	  }, {
	    key: "enableMicAutoParameters",
	    get: function get() {
	      return localStorage ? localStorage.getItem(lsKey.enableMicAutoParameters) !== 'N' : true;
	    },
	    set: function set(enableMicAutoParameters) {
	      if (localStorage) {
	        localStorage.setItem(lsKey.enableMicAutoParameters, enableMicAutoParameters ? 'Y' : 'N');
	      }
	    }
	  }, {
	    key: "preferHdQuality",
	    get: function get() {
	      return localStorage ? localStorage.getItem(lsKey.preferHd) !== 'N' : true;
	    },
	    set: function set(preferHdQuality) {
	      if (localStorage) {
	        localStorage.setItem(lsKey.preferHd, preferHdQuality ? 'Y' : 'N');
	      }
	    }
	  }, {
	    key: "enableMirroring",
	    get: function get() {
	      return localStorage ? localStorage.getItem(lsKey.enableMirroring) !== 'N' : true;
	    },
	    set: function set(enableMirroring) {
	      if (this.enableMirroring !== enableMirroring) {
	        this.emit(Events.onChangeMirroringVideo, {
	          enableMirroring: enableMirroring
	        });
	        if (BX.desktop) {
	          BX.desktop.onCustomEvent(Events.onChangeMirroringVideo, [enableMirroring]);
	        }
	        if (localStorage) {
	          localStorage.setItem(lsKey.enableMirroring, enableMirroring ? 'Y' : 'N');
	        }
	      }
	    }
	  }]);
	  return HardwareManager;
	}(main_core_events.EventEmitter);
	var Hardware = new HardwareManager();

	var FloorRequest = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(FloorRequest, _EventEmitter);
	  function FloorRequest(config) {
	    var _this;
	    babelHelpers.classCallCheck(this, FloorRequest);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FloorRequest).call(this));
	    _this.setEventNamespace("BX.Call.FloorRequest");
	    _this.hideTime = BX.prop.getInteger(config, "hideTime", 10);
	    _this.userModel = config.userModel;
	    _this.elements = {
	      root: null,
	      avatar: null
	    };
	    _this._hideTimeout = null;
	    _this._onUserModelChangedHandler = _this._onUserModelChanged.bind(babelHelpers.assertThisInitialized(_this));
	    _this.userModel.subscribe("changed", _this._onUserModelChangedHandler);
	    return _this;
	  }
	  babelHelpers.createClass(FloorRequest, [{
	    key: "mount",
	    value: function mount(container) {
	      container.appendChild(this.render());
	      this.scheduleDismount();
	    }
	  }, {
	    key: "dismount",
	    value: function dismount() {
	      BX.remove(this.elements.root);
	      this.destroy();
	    }
	  }, {
	    key: "dismountWithAnimation",
	    value: function dismountWithAnimation() {
	      var _this2 = this;
	      if (!this.elements.root) {
	        return;
	      }
	      this.elements.root.classList.add("closing");
	      this.elements.root.addEventListener("animationend", function () {
	        return _this2.dismount();
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-view-floor-request-notification"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-floor-request-notification-icon-container"
	          },
	          children: [this.elements.avatar = main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-floor-request-notification-avatar"
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-floor-request-notification-icon bx-messenger-videocall-floor-request-icon"
	            }
	          })]
	        }), main_core.Dom.create("span", {
	          props: {
	            className: "bx-call-view-floor-request-notification-text-container"
	          },
	          html: BX.message("IM_CALL_WANTS_TO_SAY_" + (this.userModel.gender == "F" ? "F" : "M")).replace("#NAME#", '<span class ="bx-call-view-floor-request-notification-text-name">' + BX.util.htmlspecialchars(this.userModel.name) + '</span>')
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-floor-request-notification-close"
	          },
	          events: {
	            click: this.dismount.bind(this)
	          }
	        })]
	      });
	      if (this.userModel.avatar) {
	        this.elements.avatar.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
	      }
	      return this.elements.root;
	    }
	  }, {
	    key: "scheduleDismount",
	    value: function scheduleDismount() {
	      return;
	      this._hideTimeout = setTimeout(this.dismountWithAnimation.bind(this), this.hideTime * 1000);
	    }
	  }, {
	    key: "_onUserModelChanged",
	    value: function _onUserModelChanged(event) {
	      var eventData = event.data;
	      if (eventData.fieldName == "floorRequestState" && !this.userModel.floorRequestState) {
	        this.dismountWithAnimation();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      clearTimeout(this._hideTimeout);
	      this._hideTimeout = null;
	      this.elements = null;
	      if (this.userModel) {
	        this.userModel.unsubscribe("changed", this._onUserModelChangedHandler);
	        this.userModel = null;
	      }
	      this.emit("onDestroy", {});
	    }
	  }], [{
	    key: "create",
	    value: function create(config) {
	      return new FloorRequest(config);
	    }
	  }]);
	  return FloorRequest;
	}(main_core_events.EventEmitter);

	var maximumNotifications = 5;
	var instance;
	var NotificationManager = /*#__PURE__*/function () {
	  function NotificationManager() {
	    babelHelpers.classCallCheck(this, NotificationManager);
	    this.maxNotification = maximumNotifications;
	    this.notifications = [];
	  }
	  babelHelpers.createClass(NotificationManager, [{
	    key: "addNotification",
	    value: function addNotification(notification) {
	      var _this = this;
	      notification.subscribe("onDestroy", function () {
	        return _this.onNotificationDestroy(notification);
	      });
	      this.notifications.push(notification);
	      if (this.notifications.length > this.maxNotification) {
	        var firstNotification = this.notifications.shift();
	        firstNotification.dismount();
	      }
	    }
	  }, {
	    key: "onNotificationDestroy",
	    value: function onNotificationDestroy(notification) {
	      var index = this.notifications.indexOf(notification);
	      if (index != -1) {
	        this.notifications.splice(index, 1);
	      }
	    }
	  }], [{
	    key: "Instance",
	    get: function get() {
	      if (!instance) {
	        instance = new NotificationManager();
	      }
	      return instance;
	    }
	  }]);
	  return NotificationManager;
	}();

	var DeviceSelectorEvents = {
	  onMicrophoneSelect: "onMicrophoneSelect",
	  onMicrophoneSwitch: "onMicrophoneSwitch",
	  onCameraSelect: "onCameraSelect",
	  onCameraSwitch: "onCameraSwitch",
	  onSpeakerSelect: "onSpeakerSelect",
	  onSpeakerSwitch: "onSpeakerSwitch",
	  onChangeHdVideo: "onChangeHdVideo",
	  onChangeMicAutoParams: "onChangeMicAutoParams",
	  onChangeFaceImprove: "onChangeFaceImprove",
	  onAdvancedSettingsClick: "onOpenAdvancedSettingsClick",
	  onShow: "onShow",
	  onDestroy: "onDestroy"
	};

	/**
	 * @param config
	 * @param {Node} config.parentElement
	 * @param {boolean} config.cameraEnabled
	 * @param {boolean} config.microphoneEnabled
	 * @param {boolean} config.speakerEnabled
	 * @param {boolean} config.allowHdVideo
	 * @param {boolean} config.faceImproveEnabled
	 * @param {object} config.events

	 * @returns {DeviceSelector}
	 */

	/**
	 * @param config
	 * @param {Node} config.parentElement
	 * @param {number} config.zIndex
	 * @param {boolean} config.cameraEnabled
	 * @param {boolean} config.microphoneEnabled
	 * @param {boolean} config.speakerEnabled
	 * @param {boolean} config.allowHdVideo
	 * @param {boolean} config.faceImproveEnabled
	 * @constructor
	 */
	var DeviceSelector = /*#__PURE__*/function () {
	  function DeviceSelector(config) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, DeviceSelector);
	    this.viewElement = config.viewElement || null;
	    this.parentElement = config.parentElement;
	    this.zIndex = config.zIndex;
	    this.cameraEnabled = BX.prop.getBoolean(config, "cameraEnabled", false);
	    this.cameraId = BX.prop.getString(config, "cameraId", false);
	    this.microphoneEnabled = BX.prop.getBoolean(config, "microphoneEnabled", false);
	    this.microphoneId = BX.prop.getString(config, "microphoneId", false);
	    this.speakerEnabled = BX.prop.getBoolean(config, "speakerEnabled", false);
	    this.speakerId = BX.prop.getString(config, "speakerId", false);
	    this.allowHdVideo = BX.prop.getBoolean(config, "allowHdVideo", false);
	    this.faceImproveEnabled = BX.prop.getBoolean(config, "faceImproveEnabled", false);
	    this.allowFaceImprove = BX.prop.getBoolean(config, "allowFaceImprove", false);
	    this.allowBackground = BX.prop.getBoolean(config, "allowBackground", true);
	    this.allowMask = BX.prop.getBoolean(config, "allowMask", true);
	    this.allowAdvancedSettings = BX.prop.getBoolean(config, "allowAdvancedSettings", false);
	    this.showCameraBlock = BX.prop.getBoolean(config, "showCameraBlock", true);
	    this.popup = null;
	    this.eventEmitter = new BX.Event.EventEmitter(this, "DeviceSelector");
	    this.elements = {
	      root: null,
	      micContainer: null,
	      cameraContainer: null,
	      speakerContainer: null
	    };
	    var eventListeners = BX.prop.getObject(config, "events", {});
	    Object.values(DeviceSelectorEvents).forEach(function (eventName) {
	      if (eventListeners[eventName]) {
	        _this.eventEmitter.subscribe(eventName, eventListeners[eventName]);
	      }
	    });
	  }
	  babelHelpers.createClass(DeviceSelector, [{
	    key: "show",
	    // static create(config)
	    // {
	    // 	return new DeviceSelector(config);
	    // };
	    value: function show() {
	      var _this2 = this;
	      if (this.popup) {
	        this.popup.show();
	        return;
	      }
	      this.popup = new main_popup.Popup({
	        id: 'call-view-device-selector',
	        bindElement: this.parentElement,
	        targetContainer: this.viewElement,
	        autoHide: true,
	        zIndex: this.zIndex,
	        closeByEsc: true,
	        offsetTop: 0,
	        offsetLeft: 0,
	        bindOptions: {
	          position: 'top'
	        },
	        angle: {
	          position: "bottom"
	        },
	        overlay: {
	          backgroundColor: 'white',
	          opacity: 0
	        },
	        content: this.render(),
	        events: {
	          onPopupClose: function onPopupClose() {
	            return _this2.popup.destroy();
	          },
	          onPopupDestroy: function onPopupDestroy() {
	            return _this2.destroy();
	          }
	        }
	      });
	      this.popup.show();
	      this.eventEmitter.emit(DeviceSelectorEvents.onShow, {});
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this3 = this;
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-view-device-selector"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-device-selector-top"
	          },
	          children: [DeviceMenu.create({
	            deviceLabel: BX.message("IM_M_CALL_BTN_MIC"),
	            deviceList: Hardware.getMicrophoneList(),
	            selectedDevice: this.microphoneId,
	            deviceEnabled: this.microphoneEnabled,
	            icons: ["microphone", "microphone-off"],
	            events: {
	              onSwitch: this.onMicrophoneSwitch.bind(this),
	              onSelect: this.onMicrophoneSelect.bind(this)
	            }
	          }).render(), this.showCameraBlock ? DeviceMenu.create({
	            deviceLabel: BX.message("IM_M_CALL_BTN_CAMERA"),
	            deviceList: Hardware.getCameraList(),
	            selectedDevice: this.cameraId,
	            deviceEnabled: this.cameraEnabled,
	            icons: ["camera", "camera-off"],
	            events: {
	              onSwitch: this.onCameraSwitch.bind(this),
	              onSelect: this.onCameraSelect.bind(this)
	            }
	          }).render() : null, Hardware.canSelectSpeaker() ? DeviceMenu.create({
	            deviceLabel: BX.message("IM_M_CALL_BTN_SPEAKER"),
	            deviceList: Hardware.getSpeakerList(),
	            selectedDevice: this.speakerId,
	            deviceEnabled: this.speakerEnabled,
	            icons: ["speaker", "speaker-off"],
	            events: {
	              onSwitch: this.onSpeakerSwitch.bind(this),
	              onSelect: this.onSpeakerSelect.bind(this)
	            }
	          }).render() : null]
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-device-selector-bottom"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-device-selector-bottom-item"
	            },
	            children: [main_core.Dom.create("input", {
	              props: {
	                id: "device-selector-hd-video",
	                className: "bx-call-view-device-selector-bottom-item-checkbox"
	              },
	              attrs: {
	                type: "checkbox",
	                checked: this.allowHdVideo
	              },
	              events: {
	                change: this.onAllowHdVideoChange.bind(this)
	              }
	            }), main_core.Dom.create("label", {
	              props: {
	                className: "bx-call-view-device-selector-bottom-item-label"
	              },
	              attrs: {
	                "for": "device-selector-hd-video"
	              },
	              text: BX.message("IM_M_CALL_HD_VIDEO")
	            })]
	          }), this.allowFaceImprove ? main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-device-selector-bottom-item"
	            },
	            children: [main_core.Dom.create("input", {
	              props: {
	                id: "device-selector-mic-auto-params",
	                className: "bx-call-view-device-selector-bottom-item-checkbox"
	              },
	              attrs: {
	                type: "checkbox",
	                checked: this.faceImproveEnabled
	              },
	              events: {
	                change: this.onFaceImproveChange.bind(this)
	              }
	            }), main_core.Dom.create("label", {
	              props: {
	                className: "bx-call-view-device-selector-bottom-item-label"
	              },
	              attrs: {
	                "for": "device-selector-mic-auto-params"
	              },
	              text: BX.message("IM_SETTINGS_HARDWARE_CAMERA_FACE_IMPROVE")
	            })]
	          }) : null, this.allowBackground ? main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-device-selector-bottom-item"
	            },
	            children: [main_core.Dom.create("span", {
	              props: {
	                className: "bx-call-view-device-selector-bottom-item-action"
	              },
	              text: this.allowMask ? BX.message("IM_M_CALL_BG_MASK_CHANGE") : BX.message("IM_M_CALL_BACKGROUND_CHANGE"),
	              events: {
	                click: function click() {
	                  BackgroundDialog.open();
	                  _this3.popup.close();
	                }
	              }
	            })]
	          }) : null, this.allowAdvancedSettings ? main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-device-selector-bottom-item"
	            },
	            children: [main_core.Dom.create("span", {
	              props: {
	                className: "bx-call-view-device-selector-bottom-item-action"
	              },
	              text: BX.message("IM_M_CALL_ADVANCED_SETTINGS"),
	              events: {
	                click: function click(e) {
	                  // to prevent BX.IM.autoHide
	                  e.stopPropagation();
	                  _this3.eventEmitter.emit(DeviceSelectorEvents.onAdvancedSettingsClick);
	                  _this3.popup.close();
	                }
	              }
	            })]
	          }) : null]
	        })]
	      });
	    }
	  }, {
	    key: "onMicrophoneSwitch",
	    value: function onMicrophoneSwitch() {
	      this.microphoneEnabled = !this.microphoneEnabled;
	      this.eventEmitter.emit(DeviceSelectorEvents.onMicrophoneSwitch, {
	        microphoneEnabled: this.microphoneEnabled
	      });
	    }
	  }, {
	    key: "onMicrophoneSelect",
	    value: function onMicrophoneSelect(e) {
	      this.eventEmitter.emit(DeviceSelectorEvents.onMicrophoneSelect, {
	        deviceId: e.data.deviceId
	      });
	    }
	  }, {
	    key: "onCameraSwitch",
	    value: function onCameraSwitch() {
	      this.cameraEnabled = !this.cameraEnabled;
	      this.eventEmitter.emit(DeviceSelectorEvents.onCameraSwitch, {
	        cameraEnabled: this.cameraEnabled
	      });
	    }
	  }, {
	    key: "onCameraSelect",
	    value: function onCameraSelect(e) {
	      this.eventEmitter.emit(DeviceSelectorEvents.onCameraSelect, {
	        deviceId: e.data.deviceId
	      });
	    }
	  }, {
	    key: "onSpeakerSwitch",
	    value: function onSpeakerSwitch() {
	      this.speakerEnabled = !this.speakerEnabled;
	      this.eventEmitter.emit(DeviceSelectorEvents.onSpeakerSwitch, {
	        speakerEnabled: this.speakerEnabled
	      });
	    }
	  }, {
	    key: "onSpeakerSelect",
	    value: function onSpeakerSelect(e) {
	      this.eventEmitter.emit(DeviceSelectorEvents.onSpeakerSelect, {
	        deviceId: e.data.deviceId
	      });
	    }
	  }, {
	    key: "onAllowHdVideoChange",
	    value: function onAllowHdVideoChange(e) {
	      this.allowHdVideo = e.currentTarget.checked;
	      this.eventEmitter.emit(DeviceSelectorEvents.onChangeHdVideo, {
	        allowHdVideo: this.allowHdVideo
	      });
	    }
	  }, {
	    key: "onAllowMirroringVideoChange",
	    value: function onAllowMirroringVideoChange(e) {
	      Hardware.enableMirroring = e.target.checked;
	    }
	  }, {
	    key: "onFaceImproveChange",
	    value: function onFaceImproveChange(e) {
	      this.faceImproveEnabled = e.currentTarget.checked;
	      this.eventEmitter.emit(DeviceSelectorEvents.onChangeFaceImprove, {
	        faceImproveEnabled: this.faceImproveEnabled
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.popup = null;
	      this.eventEmitter.emit(DeviceSelectorEvents.onDestroy, {});
	    }
	  }]);
	  return DeviceSelector;
	}();
	babelHelpers.defineProperty(DeviceSelector, "Events", DeviceSelectorEvents);
	var DeviceMenuEvents = {
	  onSelect: "onSelect",
	  onSwitch: "onSwitch"
	};
	var DeviceMenu = /*#__PURE__*/function () {
	  function DeviceMenu(config) {
	    babelHelpers.classCallCheck(this, DeviceMenu);
	    config = BX.type.isObject(config) ? config : {};
	    this.deviceList = BX.prop.getArray(config, "deviceList", []);
	    this.selectedDevice = BX.prop.getString(config, "selectedDevice", "");
	    this.deviceEnabled = BX.prop.getBoolean(config, "deviceEnabled", false);
	    this.deviceLabel = BX.prop.getString(config, "deviceLabel", "");
	    this.icons = BX.prop.getArray(config, "icons", []);
	    this.eventEmitter = new main_core_events.EventEmitter(this, 'DeviceMenu');
	    this.elements = {
	      root: null,
	      switchIcon: null,
	      menuInner: null,
	      menuItems: {} // deviceId => {root: element, icon: element}
	    };

	    var events = BX.prop.getObject(config, "events", {});
	    for (var eventName in events) {
	      if (!events.hasOwnProperty(eventName)) {
	        continue;
	      }
	      this.eventEmitter.subscribe(eventName, events[eventName]);
	    }
	  }
	  babelHelpers.createClass(DeviceMenu, [{
	    key: "render",
	    value: function render() {
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-view-device-selector-menu-container"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-device-selector-switch-wrapper"
	          },
	          children: [this.elements.switchIcon = main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-device-selector-device-icon " + this.getDeviceIconClass()
	            }
	          }), main_core.Dom.create("span", {
	            props: {
	              className: "bx-call-view-device-selector-device-text"
	            },
	            text: this.deviceLabel
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-device-selector-device-switch"
	            },
	            children: [new BX.UI.Switcher({
	              size: 'small',
	              checked: this.deviceEnabled,
	              handlers: {
	                toggled: this.onSwitchToggled.bind(this)
	              }
	            }).getNode()]
	          })]
	        }), this.elements.menuInner = main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-device-selector-menu-inner" + (this.deviceEnabled ? "" : " inactive")
	          },
	          children: this.deviceList.map(this.renderDevice.bind(this))
	        })]
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "renderDevice",
	    value: function renderDevice(deviceInfo) {
	      var iconClass = this.selectedDevice === deviceInfo.deviceId ? "selected" : "";
	      var deviceElements = {};
	      deviceElements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-view-device-selector-menu-item"
	        },
	        dataset: {
	          deviceId: deviceInfo.deviceId
	        },
	        children: [deviceElements.icon = main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-device-selector-menu-item-icon " + iconClass
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-device-selector-menu-item-text"
	          },
	          text: deviceInfo.label || "(" + BX.message("IM_M_CALL_DEVICE_NO_NAME") + ")"
	        })],
	        events: {
	          click: this.onMenuItemClick.bind(this)
	        }
	      });
	      this.elements.menuItems[deviceInfo.deviceId] = deviceElements;
	      return deviceElements.root;
	    }
	  }, {
	    key: "getDeviceIconClass",
	    value: function getDeviceIconClass() {
	      var result = "";
	      if (this.deviceEnabled && this.icons.length > 0) {
	        result = this.icons[0];
	      } else if (!this.deviceEnabled && this.icons.length > 1) {
	        result = this.icons[1];
	      }
	      return result;
	    }
	  }, {
	    key: "onSwitchToggled",
	    value: function onSwitchToggled() {
	      this.deviceEnabled = !this.deviceEnabled;
	      this.elements.switchIcon.className = "bx-call-view-device-selector-device-icon " + this.getDeviceIconClass();
	      if (this.deviceEnabled) {
	        this.elements.menuInner.classList.remove("inactive");
	      } else {
	        this.elements.menuInner.classList.add("inactive");
	      }
	      this.eventEmitter.emit(DeviceMenuEvents.onSwitch, {
	        deviceEnabled: this.deviceEnabled
	      });
	    }
	  }, {
	    key: "onMenuItemClick",
	    value: function onMenuItemClick(e) {
	      var currentDevice = this.selectedDevice;
	      var selectedDevice = e.currentTarget.dataset.deviceId;
	      if (currentDevice == selectedDevice) {
	        return;
	      }
	      this.selectedDevice = selectedDevice;
	      if (this.elements.menuItems[currentDevice]) {
	        this.elements.menuItems[currentDevice]['icon'].classList.remove('selected');
	      }
	      if (this.elements.menuItems[this.selectedDevice]) {
	        this.elements.menuItems[this.selectedDevice]['icon'].classList.add('selected');
	      }
	      this.eventEmitter.emit(DeviceMenuEvents.onSelect, {
	        deviceId: this.selectedDevice
	      });
	    }
	  }], [{
	    key: "create",
	    value: function create(config) {
	      return new DeviceMenu(config);
	    }
	  }]);
	  return DeviceMenu;
	}();

	var UserSelector = /*#__PURE__*/function () {
	  function UserSelector(config) {
	    babelHelpers.classCallCheck(this, UserSelector);
	    this.userList = config.userList;
	    this.current = config.current;
	    this.parentElement = config.parentElement;
	    this.zIndex = config.zIndex;
	    this.menu = null;
	    this.callbacks = {
	      onSelect: main_core.Type.isFunction(config.onSelect) ? config.onSelect : BX.DoNothing
	    };
	  }
	  babelHelpers.createClass(UserSelector, [{
	    key: "show",
	    value: function show() {
	      var _this = this;
	      var menuItems = [];
	      this.userList.forEach(function (user) {
	        menuItems.push({
	          id: user.id,
	          text: user.name || "unknown (" + user.id + ")",
	          className: _this.current == user.id ? "menu-popup-item-accept" : "device-selector-empty",
	          onclick: function onclick() {
	            _this.menu.close();
	            _this.callbacks.onSelect(user.id);
	          }
	        });
	      });
	      this.menu = new main_popup.Menu({
	        id: 'call-view-select-user',
	        bindElement: this.parentElement,
	        items: menuItems,
	        autoHide: true,
	        zIndex: this.zIndex,
	        closeByEsc: true,
	        offsetTop: 0,
	        offsetLeft: 0,
	        bindOptions: {
	          position: 'bottom'
	        },
	        angle: false,
	        overlay: {
	          backgroundColor: 'white',
	          opacity: 0
	        },
	        events: {
	          onPopupClose: function onPopupClose() {
	            _this.menu.popupWindow.destroy();
	            main_popup.MenuManager.destroy('call-view-select-device');
	          },
	          onPopupDestroy: function onPopupDestroy() {
	            _this.menu = null;
	          }
	        }
	      });
	      this.menu.popupWindow.show();
	    }
	  }], [{
	    key: "create",
	    value: function create(config) {
	      return new UserSelector(config);
	    }
	  }]);
	  return UserSelector;
	}();

	var Layouts = {
	  Grid: 1,
	  Centered: 2,
	  Mobile: 3
	};
	var UiState = {
	  Preparing: 1,
	  Initializing: 2,
	  Calling: 3,
	  Connected: 4,
	  Error: 5
	};
	var Size = {
	  Folded: 'folded',
	  Full: 'full'
	};
	var RecordState = {
	  Started: 'started',
	  Resumed: 'resumed',
	  Paused: 'paused',
	  Stopped: 'stopped'
	};
	var RecordType = {
	  None: 'none',
	  Video: 'video',
	  Audio: 'audio'
	};
	var RoomState = {
	  None: 1,
	  Speaker: 2,
	  NonSpeaker: 3
	};
	var EventName = {
	  onShow: 'onShow',
	  onClose: 'onClose',
	  onDestroy: 'onDestroy',
	  onButtonClick: 'onButtonClick',
	  onBodyClick: 'onBodyClick',
	  onReplaceCamera: 'onReplaceCamera',
	  onReplaceMicrophone: 'onReplaceMicrophone',
	  onReplaceSpeaker: 'onReplaceSpeaker',
	  onSetCentralUser: 'onSetCentralUser',
	  onLayoutChange: 'onLayoutChange',
	  onChangeHdVideo: 'onChangeHdVideo',
	  onChangeMicAutoParams: 'onChangeMicAutoParams',
	  onChangeFaceImprove: 'onChangeFaceImprove',
	  onUserClick: 'onUserClick',
	  onUserRename: 'onUserRename',
	  onUserPinned: 'onUserPinned',
	  onDeviceSelectorShow: 'onDeviceSelectorShow',
	  onOpenAdvancedSettings: 'onOpenAdvancedSettings'
	};
	var newUserPosition = 999;
	var localUserPosition = 1000;
	var addButtonPosition = 1001;
	var MIN_WIDTH = 250;
	var SIDE_USER_WIDTH = 160; // keep in sync with .bx-messenger-videocall-user-block .bx-messenger-videocall-user width
	var SIDE_USER_HEIGHT = 90; // keep in sync with .bx-messenger-videocall-user height

	var MAX_USERS_PER_PAGE = 15;
	var MIN_GRID_USER_WIDTH = 249;
	var MIN_GRID_USER_HEIGHT = 140;
	var View = /*#__PURE__*/function () {
	  function View(config) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, View);
	    this.title = config.title;
	    this.container = config.container;
	    this.baseZIndex = config.baseZIndex;
	    this.cameraId = config.cameraId;
	    this.microphoneId = config.microphoneId;
	    this.speakerId = '';
	    this.speakerMuted = false;
	    this.showChatButtons = config.showChatButtons === true;
	    this.showUsersButton = config.showUsersButton === true;
	    this.showShareButton = config.showShareButton !== false;
	    this.showRecordButton = config.showRecordButton !== false;
	    this.showDocumentButton = config.showDocumentButton !== false;
	    this.showButtonPanel = config.showButtonPanel !== false;
	    this.broadcastingMode = BX.prop.getBoolean(config, "broadcastingMode", false);
	    this.broadcastingPresenters = BX.prop.getArray(config, "broadcastingPresenters", []);
	    this.currentPage = 1;
	    this.pagesCount = 1;
	    this.usersPerPage = 0; // initializes after rendering and on resize

	    this.language = config.language || '';
	    this.lastPosition = 1;
	    this.userData = {};
	    if (config.userData) {
	      this.updateUserData(config.userData);
	    }
	    this.userLimit = config.userLimit || 1;
	    this.userId = BX.message('USER_ID');
	    this.isIntranetOrExtranet = BX.prop.getBoolean(config, "isIntranetOrExtranet", true);
	    this.users = {}; // Call participants. The key is the user id.
	    this.screenUsers = {}; // Screen sharing participants. The key is the user id.
	    this.userRegistry = new UserRegistry();
	    var localUserModel = new UserModel({
	      id: this.userId,
	      state: BX.prop.getString(config, "localUserState", UserState.Connected),
	      localUser: true,
	      order: localUserPosition,
	      name: this.userData[this.userId] ? this.userData[this.userId].name : '',
	      avatar: this.userData[this.userId] ? this.userData[this.userId].avatar_hr : ''
	    });
	    this.userRegistry.push(localUserModel);
	    this.localUser = new CallUser({
	      parentContainer: this.container,
	      userModel: localUserModel,
	      allowBackgroundItem: BackgroundDialog.isAvailable() && this.isIntranetOrExtranet,
	      allowMaskItem: BackgroundDialog.isMaskAvailable() && this.isIntranetOrExtranet,
	      onUserRename: this._onUserRename.bind(this),
	      onUserRenameInputFocus: this._onUserRenameInputFocus.bind(this),
	      onUserRenameInputBlur: this._onUserRenameInputBlur.bind(this)
	    });
	    this.centralUser = this.localUser; //show local user until someone is connected
	    this.centralUserMobile = null;
	    this.pinnedUser = null;
	    this.presenterId = null;
	    this.returnToGridAfterScreenStopped = false;
	    this.mediaSelectionBlocked = config.mediaSelectionBlocked === true;
	    this.visible = false;
	    this.elements = {
	      root: null,
	      wrap: null,
	      watermark: null,
	      container: null,
	      overlay: null,
	      topPanel: null,
	      bottom: null,
	      notificationPanel: null,
	      panel: null,
	      audioContainer: null,
	      audio: {
	        // userId: <audio> for this user's stream
	      },
	      center: null,
	      localUserMobile: null,
	      userBlock: null,
	      ear: {
	        left: null,
	        right: null
	      },
	      userList: {
	        container: null,
	        addButton: null
	      },
	      userSelectorContainer: null,
	      pinnedUserContainer: null,
	      renameSlider: {
	        input: null,
	        button: null
	      },
	      pageNavigatorLeft: null,
	      pageNavigatorLeftCounter: null,
	      pageNavigatorRight: null,
	      pageNavigatorRightCounter: null
	    };
	    this.buttons = {
	      title: null,
	      grid: null,
	      add: null,
	      share: null,
	      record: null,
	      document: null,
	      microphone: null,
	      camera: null,
	      speaker: null,
	      screen: null,
	      mobileMenu: null,
	      chat: null,
	      users: null,
	      history: null,
	      hangup: null,
	      fullscreen: null,
	      overlay: null,
	      status: null,
	      returnToCall: null,
	      recordStatus: null,
	      participants: null,
	      participantsMobile: null,
	      watermark: null,
	      hd: null,
	      "protected": null,
	      more: null
	    };
	    this.size = Size.Full;
	    this.maxWidth = null;
	    this.isMuted = false;
	    this.isCameraOn = false;
	    this.isFullScreen = false;
	    this.isUserBlockFolded = false;
	    this.recordState = this.getDefaultRecordState();
	    this.blockedButtons = {};
	    var configBlockedButtons = BX.prop.getArray(config, "blockedButtons", []);
	    configBlockedButtons.forEach(function (buttonCode) {
	      return _this.blockedButtons[buttonCode] = true;
	    });
	    this.hiddenButtons = {};
	    this.overflownButtons = {};
	    if (!this.showUsersButton) {
	      this.hiddenButtons['users'] = true;
	    }
	    var configHiddenButtons = BX.prop.getArray(config, "hiddenButtons", []);
	    configHiddenButtons.forEach(function (buttonCode) {
	      return _this.hiddenButtons[buttonCode] = true;
	    });
	    this.hiddenTopButtons = {};
	    var configHiddenTopButtons = BX.prop.getArray(config, "hiddenTopButtons", []);
	    configHiddenTopButtons.forEach(function (buttonCode) {
	      return _this.hiddenTopButtons[buttonCode] = true;
	    });
	    this.uiState = config.uiState || UiState.Calling;
	    this.layout = config.layout || Layouts.Centered;
	    this.roomState = RoomState.None;
	    this.eventEmitter = new main_core_events.EventEmitter(this, 'BX.Call.View');
	    this.scrollInterval = 0;

	    // Event handlers
	    this._onFullScreenChangeHandler = this._onFullScreenChange.bind(this);
	    //this._onResizeHandler = BX.throttle(this._onResize.bind(this), 500);
	    this._onResizeHandler = this._onResize.bind(this);
	    this._onOrientationChangeHandler = BX.debounce(this._onOrientationChange.bind(this), 500);
	    this._onKeyDownHandler = this._onKeyDown.bind(this);
	    this._onKeyUpHandler = this._onKeyUp.bind(this);
	    this.resizeObserver = new BX.ResizeObserver(this._onResizeHandler);
	    this.intersectionObserver = null;

	    // timers
	    this.switchPresenterTimeout = 0;
	    this.deviceSelector = null;
	    this.userSelector = null;
	    this.pinnedUserContainer = null;
	    this.renameSlider = null;
	    this.userSize = {
	      width: 0,
	      height: 0
	    };
	    this.hintManager = BX.UI.Hint.createInstance({
	      popupParameters: {
	        targetContainer: document.body,
	        className: 'bx-messenger-videocall-panel-item-hotkey-hint',
	        bindOptions: {
	          forceBindPosition: true
	        }
	      }
	    });
	    this.hotKey = {
	      all: Util$1.isDesktop(),
	      microphone: true,
	      microphoneSpace: true,
	      camera: true,
	      screen: true,
	      record: true,
	      speaker: true,
	      chat: true,
	      users: true,
	      floorRequest: true,
	      muteSpeaker: true,
	      grid: true
	    };
	    this.hotKeyTemporaryBlock = 0;
	    this.init();
	    this.subscribeEvents(config);
	    if (main_core.Type.isPlainObject(config.userStates)) {
	      this.appendUsers(config.userStates);
	    }

	    /*this.resizeCalled = 0;
	    this.reportResizeCalled = BX.debounce(function()
	    {
	    	console.log('resizeCalled ' + this.resizeCalled + ' times');
	    	this.resizeCalled = 0;
	    }.bind(this), 100)*/
	  }
	  babelHelpers.createClass(View, [{
	    key: "init",
	    value: function init() {
	      if (this.isFullScreenSupported()) {
	        if (main_core.Browser.isChrome() || main_core.Browser.isSafari()) {
	          window.addEventListener("fullscreenchange", this._onFullScreenChangeHandler);
	          window.addEventListener("webkitfullscreenchange", this._onFullScreenChangeHandler);
	        } else if (main_core.Browser.isFirefox()) {
	          window.addEventListener("mozfullscreenchange", this._onFullScreenChangeHandler);
	        }
	      }
	      if (main_core.Browser.isMobile()) {
	        document.documentElement.style.setProperty('--view-height', window.innerHeight + 'px');
	        window.addEventListener("orientationchange", this._onOrientationChangeHandler);
	      }
	      this.elements.audioContainer = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-audio-container"
	        }
	      });
	      if (Hardware.initialized) {
	        this.setSpeakerId(Hardware.defaultSpeaker);
	      } else {
	        Hardware.subscribe(Hardware.Events.initialized, function () {
	          this.setSpeakerId(Hardware.defaultSpeaker);
	        }.bind(this));
	      }
	      window.addEventListener("keydown", this._onKeyDownHandler);
	      window.addEventListener("keyup", this._onKeyUpHandler);
	      if (main_core.Browser.isMac()) {
	        this.keyModifier = '&#8984; + Shift';
	      } else {
	        this.keyModifier = 'Ctrl + Shift';
	      }
	      this.container.appendChild(this.elements.audioContainer);
	    }
	  }, {
	    key: "subscribeEvents",
	    value: function subscribeEvents(config) {
	      for (var _event in EventName) {
	        if (EventName.hasOwnProperty(_event) && main_core.Type.isFunction(config[_event])) {
	          this.setCallback(_event, config[_event]);
	        }
	      }
	    }
	  }, {
	    key: "setCallback",
	    value: function setCallback(name, cb) {
	      if (main_core.Type.isFunction(cb) && EventName.hasOwnProperty(name)) {
	        this.eventEmitter.subscribe(name, function (event) {
	          cb(event.data);
	        });
	      }
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe(eventName, listener) {
	      return this.eventEmitter.subscribe(eventName, listener);
	    }
	  }, {
	    key: "unsubscribe",
	    value: function unsubscribe(eventName, listener) {
	      return this.eventEmitter.unsubscribe(eventName, listener);
	    }
	  }, {
	    key: "getNextPosition",
	    value: function getNextPosition() {
	      return this.lastPosition++;
	    }
	  }, {
	    key: "appendUsers",
	    /**
	     * @param {object} userStates {userId -> state}
	     */
	    value: function appendUsers(userStates) {
	      if (!main_core.Type.isPlainObject(userStates)) {
	        return;
	      }
	      var userIds = Object.keys(userStates);
	      for (var i = 0; i < userIds.length; i++) {
	        var userId = userIds[i];
	        this.addUser(userId, userStates[userId] ? userStates[userId] : UserState.Idle);
	      }
	    }
	  }, {
	    key: "setCentralUser",
	    value: function setCentralUser(userId) {
	      var _this2 = this;
	      if (this.centralUser.id == userId) {
	        return;
	      }
	      if (userId == this.userId && this.getUsersWithVideo().length > 0) {
	        return;
	      }
	      if (!this.users[userId] && userId != this.userId) {
	        return;
	      }
	      var previousCentralUser = this.centralUser;
	      this.centralUser = userId == this.userId ? this.localUser : this.users[userId];
	      if (this.layout == Layouts.Centered || this.layout == Layouts.Mobile) {
	        previousCentralUser.dismount();
	        this.updateUserList();
	      }
	      if (this.layout == Layouts.Mobile) {
	        if (this.centralUserMobile) {
	          this.centralUserMobile.setUserModel(this.userRegistry.get(userId));
	        } else {
	          this.centralUserMobile = new CallUserMobile({
	            userModel: this.userRegistry.get(userId),
	            onClick: function onClick() {
	              return _this2.showUserMenu(_this2.centralUser.id);
	            }
	          });
	          this.centralUserMobile.mount(this.elements.pinnedUserContainer);
	        }
	      }
	      this.userRegistry.users.forEach(function (userModel) {
	        return userModel.centralUser = userModel.id == userId;
	      });
	      this.eventEmitter.emit(EventName.onSetCentralUser, {
	        userId: userId,
	        stream: userId == this.userId ? this.localUser.stream : this.users[userId].stream
	      });
	    }
	  }, {
	    key: "getLeftUser",
	    value: function getLeftUser(userId) {
	      var candidateUserId = null;
	      for (var i = 0; i < this.userRegistry.users.length; i++) {
	        var userModel = this.userRegistry.users[i];
	        if (userModel.id == userId && candidateUserId) {
	          return candidateUserId;
	        }
	        if (!userModel.localUser && userModel.state == UserState.Connected) {
	          candidateUserId = userModel.id;
	        }
	      }
	      return candidateUserId;
	    }
	  }, {
	    key: "getRightUser",
	    value: function getRightUser(userId) {
	      var candidateUserId = null;
	      for (var i = this.userRegistry.users.length - 1; i >= 0; i--) {
	        var userModel = this.userRegistry.users[i];
	        if (userModel.id == userId && candidateUserId) {
	          return candidateUserId;
	        }
	        if (!userModel.localUser && userModel.state == UserState.Connected) {
	          candidateUserId = userModel.id;
	        }
	      }
	      return candidateUserId;
	    }
	  }, {
	    key: "getUserCount",
	    value: function getUserCount() {
	      return Object.keys(this.users).length;
	    }
	  }, {
	    key: "getConnectedUserCount",
	    value: function getConnectedUserCount(withYou) {
	      var count = this.getConnectedUsers().length;
	      if (withYou) {
	        var userId = parseInt(this.userId);
	        if (!this.broadcastingMode || this.broadcastingPresenters.includes(userId)) {
	          count += 1;
	        }
	      }
	      return count;
	    }
	  }, {
	    key: "getUsersWithVideo",
	    value: function getUsersWithVideo() {
	      var result = [];
	      for (var userId in this.users) {
	        if (this.users[userId].hasVideo()) {
	          result.push(userId);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "getConnectedUsers",
	    value: function getConnectedUsers() {
	      var result = [];
	      for (var i = 0; i < this.userRegistry.users.length; i++) {
	        var userModel = this.userRegistry.users[i];
	        if (userModel.id != this.userId && userModel.state == UserState.Connected) {
	          result.push(userModel.id);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "getDisplayedUsers",
	    value: function getDisplayedUsers() {
	      var result = [];
	      for (var i = 0; i < this.userRegistry.users.length; i++) {
	        var userModel = this.userRegistry.users[i];
	        if (userModel.id != this.userId && (userModel.state == UserState.Connected || userModel.state == UserState.Connecting)) {
	          result.push(userModel.id);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "hasUserWithScreenSharing",
	    value: function hasUserWithScreenSharing() {
	      return this.userRegistry.users.some(function (userModel) {
	        return userModel.screenState;
	      });
	    }
	  }, {
	    key: "getPresenterUserId",
	    value: function getPresenterUserId() {
	      var currentPresenterId = this.presenterId || 0;
	      if (currentPresenterId == this.localUser.id) {
	        currentPresenterId = 0;
	      }
	      var userId; // for usage in iterators

	      var currentPresenterModel = this.userRegistry.get(currentPresenterId);

	      // 1. Current user, who is sharing screen has top priority
	      if (currentPresenterModel && currentPresenterModel.screenState === true) {
	        return currentPresenterId;
	      }

	      // 2. If current user is not sharing screen, but someone is sharing - he should become presenter
	      for (userId in this.users) {
	        if (this.users.hasOwnProperty(userId) && this.userRegistry.get(userId).screenState === true) {
	          return parseInt(userId);
	        }
	      }

	      // 3. If current user is talking, or stopped talking less then one second ago - he should stay presenter
	      if (currentPresenterModel && currentPresenterModel.wasTalkingAgo() < 1000) {
	        return currentPresenterId;
	      }

	      // 4. Return currently talking user
	      var minTalkingAgo = 0;
	      var minTalkingAgoUserId = 0;
	      for (userId in this.users) {
	        if (!this.users.hasOwnProperty(userId)) {
	          continue;
	        }
	        var userWasTalkingAgo = this.userRegistry.get(userId).wasTalkingAgo();
	        if (userWasTalkingAgo < 1000) {
	          return parseInt(userId);
	        }
	        if (userWasTalkingAgo < minTalkingAgo) {
	          minTalkingAgoUserId = parseInt(userId);
	        }
	      }

	      // 5. Return last talking user
	      if (minTalkingAgoUserId) {
	        return minTalkingAgoUserId;
	      }

	      // return current user in center
	      return this.centralUser.id;
	    }
	  }, {
	    key: "switchPresenter",
	    value: function switchPresenter() {
	      var _this3 = this;
	      var newPresenterId = this.getPresenterUserId();
	      if (!newPresenterId) {
	        return;
	      }
	      this.presenterId = newPresenterId;
	      this.userRegistry.users.forEach(function (userModel) {
	        return userModel.presenter = userModel.id == _this3.presenterId;
	      });
	      if (this.pinnedUser === null) {
	        this.setCentralUser(newPresenterId);
	      }
	      if (this.layout == Layouts.Grid) {
	        var presentersPage = this.findUsersPage(this.presenterId);
	        if (presentersPage) {
	          this.setCurrentPage(presentersPage);
	        }
	      }
	    }
	  }, {
	    key: "switchPresenterDeferred",
	    value: function switchPresenterDeferred() {
	      clearTimeout(this.switchPresenterTimeout);
	      this.switchPresenterTimeout = setTimeout(this.switchPresenter.bind(this), 1000);
	    }
	  }, {
	    key: "cancelSwitchPresenter",
	    value: function cancelSwitchPresenter() {
	      clearTimeout(this.switchPresenterTimeout);
	    }
	  }, {
	    key: "setUiState",
	    value: function setUiState(uiState) {
	      if (this.uiState == uiState) {
	        return;
	      }
	      this.uiState = uiState;
	      if (this.uiState == UiState.Error && this.elements.container) {
	        main_core.Dom.clean(this.elements.container);
	        this.elements.container.appendChild(this.elements.overlay);
	      }
	      if (!this.elements.root) {
	        return;
	      }
	      this.updateButtons();
	      this.elements.wrap.classList.toggle("with-clouds", this.uiState == UiState.Preparing);
	    }
	  }, {
	    key: "setLayout",
	    value: function setLayout(newLayout) {
	      if (newLayout == this.layout) {
	        return;
	      }
	      this.layout = newLayout;
	      if (this.layout == Layouts.Centered || this.layout == Layouts.Mobile) {
	        this.elements.root.classList.remove("bx-messenger-videocall-grid");
	        this.elements.root.classList.add("bx-messenger-videocall-centered");
	        this.centralUser.mount(this.elements.center);
	        this.elements.container.appendChild(this.elements.userBlock);
	        if (this.layout != Layouts.Mobile) {
	          this.elements.userBlock.appendChild(this.elements.userList.container);
	        }
	        this.centralUser.playVideo();
	        //this.centralUser.updateAvatarWidth();
	      }

	      if (this.layout == Layouts.Grid) {
	        this.elements.root.classList.remove("bx-messenger-videocall-centered");
	        this.elements.root.classList.add("bx-messenger-videocall-grid");
	        this.elements.container.appendChild(this.elements.userList.container);
	        this.elements.container.removeChild(this.elements.userBlock);
	        if (this.isFullScreen && this.buttons.participants) {
	          this.buttons.participants.update({
	            foldButtonState: ParticipantsButton.FoldButtonState.Hidden
	          });
	        }
	        this.unpinUser();
	      }
	      if (this.layout == Layouts.Centered && this.isFullScreen) {
	        this.setUserBlockFolded(true);
	      }
	      this.elements.root.classList.toggle("bx-messenger-videocall-fullscreen-mobile", this.layout == Layouts.Mobile);
	      this.renderUserList();
	      this.toggleEars();
	      this.updateButtons();
	      this.eventEmitter.emit(EventName.onLayoutChange, {
	        layout: this.layout
	      });
	    }
	  }, {
	    key: "setRoomState",
	    value: function setRoomState(roomState) {
	      if (this.roomState === roomState) {
	        return;
	      }
	      this.roomState = roomState;
	      if (this.buttons.microphone) {
	        this.buttons.microphone.setSideIcon(this.getMicrophoneSideIcon(this.roomState));
	      }
	    }
	  }, {
	    key: "getMicrophoneSideIcon",
	    value: function getMicrophoneSideIcon(roomState) {
	      switch (roomState) {
	        case RoomState.Speaker:
	          return 'ellipsis';
	        case RoomState.NonSpeaker:
	          return 'pointer';
	        case RoomState.None:
	        default:
	          return null;
	      }
	    }
	  }, {
	    key: "setCurrentPage",
	    value: function setCurrentPage(pageNumber) {
	      if (pageNumber < 1 || pageNumber > this.pagesCount || pageNumber == this.currentPage) {
	        return;
	      }
	      this.currentPage = pageNumber;
	      if (this.elements.root) {
	        this.elements.pageNavigatorLeftCounter.innerHTML = this.currentPage - 1 + '&nbsp;/&nbsp;' + this.pagesCount;
	        this.elements.pageNavigatorRightCounter.innerHTML = this.currentPage + 1 + '&nbsp;/&nbsp;' + this.pagesCount;
	      }
	      if (this.layout !== Layouts.Grid) {
	        return;
	      }
	      this.renderUserList();
	      this.toggleEars();
	    }
	  }, {
	    key: "calculateUsersPerPage",
	    value: function calculateUsersPerPage() {
	      if (!this.elements.userList) {
	        return 1000;
	      }
	      var containerSize = this.elements.userList.container.getBoundingClientRect();
	      var columns = Math.floor(containerSize.width / MIN_GRID_USER_WIDTH) || 1;
	      var rows = Math.floor(containerSize.height / MIN_GRID_USER_HEIGHT) || 1;
	      var usersPerPage = columns * rows - 1;
	      if (!usersPerPage) {
	        return 1000;
	      }
	      if (usersPerPage <= MAX_USERS_PER_PAGE) {
	        return usersPerPage;
	      } else {
	        // check if the last row should be filled up
	        var elementSize = Util$1.findBestElementSize(containerSize.width, containerSize.height, MAX_USERS_PER_PAGE + 1, MIN_GRID_USER_WIDTH, MIN_GRID_USER_HEIGHT);
	        // console.log('Optimal element size: width '+elementSize.width+' height '+elementSize.height);
	        columns = Math.floor(containerSize.width / elementSize.width);
	        rows = Math.floor(containerSize.height / elementSize.height);
	        return columns * rows - 1;
	      }
	    }
	  }, {
	    key: "calculatePagesCount",
	    value: function calculatePagesCount(usersPerPage) {
	      var pages = Math.ceil(this.getDisplayedUsers().length / usersPerPage);
	      return pages > 0 ? pages : 1;
	    }
	  }, {
	    key: "recalculatePages",
	    value: function recalculatePages() {
	      this.usersPerPage = this.calculateUsersPerPage();
	      this.pagesCount = this.calculatePagesCount(this.usersPerPage);
	      if (this.elements.root) {
	        this.elements.pageNavigatorLeftCounter.innerHTML = this.currentPage - 1 + '&nbsp;/&nbsp;' + this.pagesCount;
	        this.elements.pageNavigatorRightCounter.innerHTML = this.currentPage + 1 + '&nbsp;/&nbsp;' + this.pagesCount;
	      }
	    }
	  }, {
	    key: "findUsersPage",
	    /**
	     * Returns page number, where the user is displayed, or 0 if user is not found
	     * @param {int} userId Id of the user
	     * @return {int}
	     */
	    value: function findUsersPage(userId) {
	      if (userId == this.userId || this.usersPerPage === 0) {
	        return 0;
	      }
	      var displayedUsers = this.getDisplayedUsers();
	      var userPosition = 0;
	      for (var i = 0; i < displayedUsers.length; i++) {
	        if (displayedUsers[i] == userId) {
	          userPosition = i + 1;
	          break;
	        }
	      }
	      return userPosition ? Math.ceil(userPosition / this.usersPerPage) : 0;
	    }
	  }, {
	    key: "setCameraId",
	    value: function setCameraId(cameraId) {
	      if (this.cameraId == cameraId) {
	        return;
	      }
	      if (this.localUser.stream && this.localUser.stream.getVideoTracks().length > 0) {
	        throw new Error("Can not set camera id while having active stream");
	      }
	      this.cameraId = cameraId;
	    }
	  }, {
	    key: "setMicrophoneId",
	    value: function setMicrophoneId(microphoneId) {
	      if (this.microphoneId == microphoneId) {
	        return;
	      }
	      if (this.localUser.stream && this.localUser.stream.getAudioTracks().length > 0) {
	        throw new Error("Can not set microphone id while having active stream");
	      }
	      this.microphoneId = microphoneId;
	    }
	  }, {
	    key: "setMicrophoneLevel",
	    value: function setMicrophoneLevel(level) {
	      var _this$buttons$microph;
	      this.microphoneLevel = level;
	      (_this$buttons$microph = this.buttons.microphone) === null || _this$buttons$microph === void 0 ? void 0 : _this$buttons$microph.setLevel(level);
	    }
	  }, {
	    key: "setCameraState",
	    value: function setCameraState(newCameraState) {
	      newCameraState = !!newCameraState;
	      if (this.isCameraOn == newCameraState) {
	        return;
	      }
	      this.isCameraOn = newCameraState;
	      if (this.buttons.camera) {
	        if (this.isCameraOn) {
	          this.buttons.camera.enable();
	        } else {
	          this.buttons.camera.disable();
	        }
	      }
	    }
	  }, {
	    key: "setMuted",
	    value: function setMuted(isMuted) {
	      isMuted = !!isMuted;
	      if (this.isMuted == isMuted) {
	        return;
	      }
	      this.isMuted = isMuted;
	      if (this.buttons.microphone) {
	        if (this.isMuted) {
	          this.buttons.microphone.disable();
	        } else {
	          this.buttons.microphone.enable();
	        }
	      }
	      this.userRegistry.get(this.userId).microphoneState = !isMuted;
	    }
	  }, {
	    key: "setLocalUserId",
	    value: function setLocalUserId(userId) {
	      if (userId == this.userId) {
	        return;
	      }
	      this.userId = parseInt(userId);
	      this.localUser.userModel.id = this.userId;
	      this.localUser.userModel.name = this.userData[this.userId] ? this.userData[this.userId].name : '';
	      this.localUser.userModel.avatar = this.userData[this.userId] ? this.userData[this.userId].avatar_hr : '';
	    }
	  }, {
	    key: "setUserBlockFolded",
	    value: function setUserBlockFolded(isUserBlockFolded) {
	      var _this$elements$userBl, _this$elements$root;
	      this.isUserBlockFolded = isUserBlockFolded;
	      (_this$elements$userBl = this.elements.userBlock) === null || _this$elements$userBl === void 0 ? void 0 : _this$elements$userBl.classList.toggle("folded", this.isUserBlockFolded);
	      (_this$elements$root = this.elements.root) === null || _this$elements$root === void 0 ? void 0 : _this$elements$root.classList.toggle("bx-messenger-videocall-userblock-folded", this.isUserBlockFolded);
	      if (this.isUserBlockFolded) {
	        if (this.buttons.participants && this.layout == Layouts.Centered) {
	          this.buttons.participants.update({
	            foldButtonState: ParticipantsButton.FoldButtonState.Unfold
	          });
	        }
	      } else {
	        if (this.buttons.participants) {
	          this.buttons.participants.update({
	            foldButtonState: this.isFullScreen && this.layout == Layouts.Centered ? ParticipantsButton.FoldButtonState.Fold : ParticipantsButton.FoldButtonState.Hidden
	          });
	        }
	      }
	    }
	  }, {
	    key: "addUser",
	    value: function addUser(userId, state, direction) {
	      userId = Number(userId);
	      if (this.users[userId]) {
	        return;
	      }
	      state = state || UserState.Idle;
	      if (!direction) {
	        if (this.broadcastingPresenters.length > 0 && !this.broadcastingPresenters.includes(userId)) {
	          direction = EndpointDirection.RecvOnly;
	        } else {
	          direction = EndpointDirection.SendRecv;
	        }
	      }
	      var userModel = new UserModel({
	        id: userId,
	        name: this.userData[userId] ? this.userData[userId].name : '',
	        avatar: this.userData[userId] ? this.userData[userId].avatar_hr : '',
	        state: state,
	        order: state == UserState.Connected ? this.getNextPosition() : newUserPosition,
	        direction: direction
	      });
	      this.userRegistry.push(userModel);
	      if (!this.elements.audio[userId]) {
	        this.elements.audio[userId] = main_core.Dom.create("audio");
	        this.elements.audioContainer.appendChild(this.elements.audio[userId]);
	      }
	      this.users[userId] = new CallUser({
	        parentContainer: this.container,
	        userModel: userModel,
	        audioElement: this.elements.audio[userId],
	        allowPinButton: this.getConnectedUserCount() > 1,
	        onClick: this._onUserClick.bind(this),
	        onPin: this._onUserPin.bind(this),
	        onUnPin: this._onUserUnPin.bind(this)
	      });
	      this.screenUsers[userId] = new CallUser({
	        parentContainer: this.container,
	        userModel: userModel,
	        allowPinButton: false,
	        screenSharingUser: true
	      });
	      if (this.elements.root) {
	        this.updateUserList();
	        this.updateButtons();
	        this.updateUserButtons();
	      }
	    }
	  }, {
	    key: "setUserDirection",
	    value: function setUserDirection(userId, direction) {
	      var user = this.userRegistry.get(userId);
	      if (!user || user.direction == direction) {
	        return;
	      }
	      user.direction = direction;
	      this.updateUserList();
	    }
	  }, {
	    key: "setLocalUserDirection",
	    value: function setLocalUserDirection(direction) {
	      if (this.localUser.userModel.direction != direction) {
	        this.localUser.userModel.direction = direction;
	        this.updateUserList();
	      }
	    }
	  }, {
	    key: "setUserState",
	    value: function setUserState(userId, newState) {
	      var user = this.userRegistry.get(userId);
	      if (!user) {
	        return;
	      }
	      if (newState === UserState.Connected && this.uiState === UiState.Calling) {
	        this.setUiState(UiState.Connected);
	      }
	      user.state = newState;

	      // maybe switch central user
	      if (this.centralUser.id == this.userId && newState == UserState.Connected) {
	        this.setCentralUser(userId);
	      } else if (userId == this.centralUser.id) {
	        if (newState == UserState.Connecting || newState == UserState.Failed) {
	          this.centralUser.blurVideo();
	        } else if (newState == UserState.Connected) {
	          this.centralUser.blurVideo(false);
	        } else if (newState == UserState.Idle) {
	          var usersWithVideo = this.getUsersWithVideo();
	          var connectedUsers = this.getConnectedUsers();
	          if (connectedUsers.length === 0) {
	            this.setCentralUser(this.userId);
	          } else if (usersWithVideo.length > 0) {
	            this.setCentralUser(usersWithVideo[0]);
	          } else
	            //if (connectedUsers.length > 0)
	            {
	              this.setCentralUser(connectedUsers[0]);
	            }
	        }
	      }
	      if (newState == UserState.Connected && user.order == newUserPosition) {
	        user.order = this.getNextPosition();
	      }
	      if (userId == this.localUser.id) {
	        this.setCameraState(this.localUser.hasVideo());
	        this.localUser.userModel.cameraState = this.localUser.hasVideo();
	      }
	      this.updateUserList();
	      this.updateButtons();
	      this.updateUserButtons();
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      this.title = title;
	    }
	  }, {
	    key: "getUserTalking",
	    value: function getUserTalking(userId) {
	      var user = this.userRegistry.get(userId);
	      if (!user) {
	        return false;
	      }
	      return !!user.talking;
	    }
	  }, {
	    key: "setUserTalking",
	    value: function setUserTalking(userId, talking) {
	      var user = this.userRegistry.get(userId);
	      if (user) {
	        user.talking = talking;
	      }
	      if (userId == this.userId) {
	        return;
	      }
	      if (userId == this.presenterId && !talking) {
	        this.switchPresenterDeferred();
	      } else {
	        this.switchPresenter();
	      }
	    }
	  }, {
	    key: "setUserMicrophoneState",
	    value: function setUserMicrophoneState(userId, isMicrophoneOn) {
	      var user = this.userRegistry.get(userId);
	      if (user) {
	        user.microphoneState = isMicrophoneOn;
	      }
	    }
	  }, {
	    key: "setUserCameraState",
	    value: function setUserCameraState(userId, cameraState) {
	      var user = this.userRegistry.get(userId);
	      if (user) {
	        user.cameraState = cameraState;
	      }
	    }
	  }, {
	    key: "setUserVideoPaused",
	    value: function setUserVideoPaused(userId, videoPaused) {
	      var user = this.userRegistry.get(userId);
	      if (user) {
	        user.videoPaused = videoPaused;
	      }
	    }
	  }, {
	    key: "getUserFloorRequestState",
	    value: function getUserFloorRequestState(userId) {
	      var user = this.userRegistry.get(userId);
	      return user && user.floorRequestState;
	    }
	  }, {
	    key: "setUserFloorRequestState",
	    value: function setUserFloorRequestState(userId, userFloorRequestState) {
	      var user = this.userRegistry.get(userId);
	      if (!user) {
	        return;
	      }
	      if (user.floorRequestState != userFloorRequestState) {
	        user.floorRequestState = userFloorRequestState;
	        if (userId != this.localUser.id && userFloorRequestState) {
	          this.showFloorRequestNotification(userId);
	        }
	      }
	      if (userId == this.userId) {
	        this.setButtonActive('floorRequest', userFloorRequestState);
	      }
	    }
	  }, {
	    key: "pinUser",
	    value: function pinUser(userId) {
	      if (!(userId in this.users)) {
	        console.error("User " + userId + " is not known");
	        return;
	      }
	      this.pinnedUser = this.users[userId];
	      this.userRegistry.users.forEach(function (userModel) {
	        return userModel.pinned = userModel.id == userId;
	      });
	      this.setCentralUser(userId);
	      this.eventEmitter.emit(EventName.onUserPinned, {
	        userId: userId
	      });
	    }
	  }, {
	    key: "unpinUser",
	    value: function unpinUser() {
	      this.pinnedUser = null;
	      this.userRegistry.users.forEach(function (userModel) {
	        return userModel.pinned = false;
	      });
	      this.eventEmitter.emit(EventName.onUserPinned, {
	        userId: null
	      });
	      this.switchPresenterDeferred();
	    }
	  }, {
	    key: "showFloorRequestNotification",
	    value: function showFloorRequestNotification(userId) {
	      var userModel = this.userRegistry.get(userId);
	      if (!userModel) {
	        return;
	      }
	      var notification = FloorRequest.create({
	        userModel: userModel
	      });
	      notification.mount(this.elements.notificationPanel);
	      NotificationManager.Instance.addNotification(notification);
	    }
	  }, {
	    key: "setUserScreenState",
	    value: function setUserScreenState(userId, screenState) {
	      var user = this.userRegistry.get(userId);
	      if (!user) {
	        return;
	      }
	      user.screenState = screenState;
	      if (userId != this.userId) {
	        if (screenState === true && this.layout === View.Layout.Grid) {
	          this.setLayout(Layouts.Centered);
	          this.returnToGridAfterScreenStopped = true;
	        }
	        if (screenState === false && this.layout === Layouts.Centered && !this.hasUserWithScreenSharing() && !this.pinnedUser && this.returnToGridAfterScreenStopped) {
	          this.returnToGridAfterScreenStopped = false;
	          this.setLayout(Layouts.Grid);
	        }
	        this.switchPresenter();
	      }
	    }
	  }, {
	    key: "flipLocalVideo",
	    value: function flipLocalVideo(flipVideo) {
	      this.localUser.flipVideo = !!flipVideo;
	    }
	  }, {
	    key: "setLocalStream",
	    value: function setLocalStream(mediaStream, flipVideo) {
	      this.localUser.videoTrack = mediaStream.getVideoTracks().length > 0 ? mediaStream.getVideoTracks()[0] : null;
	      if (!main_core.Type.isUndefined(flipVideo)) {
	        this.flipLocalVideo(flipVideo);
	      }
	      this.setCameraState(this.localUser.hasVideo());
	      this.localUser.userModel.cameraState = this.localUser.hasVideo();
	      var videoTracks = mediaStream.getVideoTracks();
	      if (videoTracks.length > 0) {
	        var videoTrackSettings = videoTracks[0].getSettings();
	        this.cameraId = videoTrackSettings.deviceId || '';
	      } else {
	        this.cameraId = '';
	      }
	      var audioTracks = mediaStream.getAudioTracks();
	      if (audioTracks.length > 0) {
	        var audioTrackSettings = audioTracks[0].getSettings();
	        this.microphoneId = audioTrackSettings.deviceId || '';
	      }

	      /*if(!this.localUser.hasVideo())
	      {
	      	return false;
	      }*/

	      if (this.layout !== Layouts.Grid && this.centralUser.id == this.userId) {
	        if (videoTracks.length > 0 || Object.keys(this.users).length === 0) {
	          this.centralUser.videoTrack = videoTracks[0];
	        } else {
	          this.setCentralUser(Object.keys(this.users)[0]);
	        }
	      } else {
	        this.updateUserList();
	      }
	    }
	  }, {
	    key: "setSpeakerId",
	    value: function setSpeakerId(speakerId) {
	      if (this.speakerId == speakerId) {
	        return;
	      }
	      if (!('setSinkId' in HTMLMediaElement.prototype)) {
	        console.error("Speaker selection is not supported");
	      }
	      this.speakerId = speakerId;
	      for (var userId in this.elements.audio) {
	        this.elements.audio[userId].setSinkId(this.speakerId);
	      }
	    }
	  }, {
	    key: "muteSpeaker",
	    value: function muteSpeaker(mute) {
	      this.speakerMuted = !!mute;
	      for (var userId in this.elements.audio) {
	        this.elements.audio[userId].volume = this.speakerMuted ? 0 : 1;
	      }
	      if (!this.buttons.speaker) {
	        return;
	      }
	      if (this.speakerMuted) {
	        this.buttons.speaker.disable();
	        this.buttons.speaker.hideArrow();
	      } else {
	        this.buttons.speaker.enable();
	        if (Hardware.canSelectSpeaker()) {
	          this.buttons.speaker.showArrow();
	        }
	      }
	    }
	  }, {
	    key: "setVideoRenderer",
	    value: function setVideoRenderer(userId, mediaRenderer) {
	      if (!this.users[userId]) {
	        throw Error("User " + userId + " is not a part of this call");
	      }
	      if (mediaRenderer === null) {
	        this.users[userId].videoRenderer = null;
	        return;
	      }
	      if (!("render" in mediaRenderer) || !main_core.Type.isFunction(mediaRenderer.render)) {
	        throw Error("mediaRenderer should have method render");
	      }
	      if (!("kind" in mediaRenderer) || mediaRenderer.kind !== "video" && mediaRenderer.kind !== "sharing") {
	        throw Error("mediaRenderer should be of video kind");
	      }
	      this.users[userId].videoRenderer = mediaRenderer;
	    }
	  }, {
	    key: "setUserMedia",
	    value: function setUserMedia(userId, kind, track) {
	      if (kind === 'audio') {
	        this.users[userId].audioTrack = track;
	      }
	      if (kind === 'video') {
	        this.users[userId].videoTrack = track;
	      }
	      if (kind === 'screen') {
	        this.screenUsers[userId].videoTrack = track;
	        this.updateUserList();
	        this.setUserScreenState(userId, track !== null);
	      }
	    }
	  }, {
	    key: "applyIncomingVideoConstraints",
	    value: function applyIncomingVideoConstraints() {
	      var userId;
	      var user;
	      if (this.layout === Layouts.Grid) {
	        for (userId in this.users) {
	          user = this.users[userId];
	          user.setIncomingVideoConstraints(this.userSize.width, this.userSize.height);
	        }
	      } else if (this.layout === Layouts.Centered) {
	        for (userId in this.users) {
	          user = this.users[userId];
	          if (userId == this.centralUser.id) {
	            var containerSize = this.elements.center.getBoundingClientRect();
	            user.setIncomingVideoConstraints(Math.floor(containerSize.width), Math.floor(containerSize.height));
	          } else {
	            user.setIncomingVideoConstraints(SIDE_USER_WIDTH, SIDE_USER_HEIGHT);
	          }
	        }
	      }
	    }
	  }, {
	    key: "getDefaultRecordState",
	    value: function getDefaultRecordState() {
	      return {
	        state: RecordState.Stopped,
	        userId: 0,
	        date: {
	          start: null,
	          pause: []
	        }
	      };
	    }
	  }, {
	    key: "setRecordState",
	    value: function setRecordState(recordState) {
	      this.recordState = recordState;
	      if (this.buttons.recordStatus) {
	        this.buttons.recordStatus.update(this.recordState);
	      }
	      if (this.recordState.userId != this.userId) {
	        if (this.recordState.state === RecordState.Stopped) {
	          this.unblockButtons(['record']);
	        } else {
	          this.blockButtons(['record']);
	        }
	      }
	      if (this.elements.topPanel) {
	        if (this.recordState.state === RecordState.Stopped) {
	          delete this.elements.topPanel.dataset.recordState;
	        } else {
	          this.elements.topPanel.dataset.recordState = recordState.state;
	        }
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.elements.root) {
	        this.render();
	      }
	      this.container.appendChild(this.elements.root);
	      if (this.layout !== Layouts.Mobile) {
	        this.startIntersectionObserver();
	      }
	      this.updateButtons();
	      this.updateUserList();
	      this.resumeVideo();
	      this.toggleEars();
	      this.visible = true;
	      this.eventEmitter.emit(EventName.onShow);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.overflownButtonsPopup) {
	        this.overflownButtonsPopup.close();
	      }
	      main_core.Dom.remove(this.elements.root);
	      this.visible = false;
	    }
	  }, {
	    key: "startIntersectionObserver",
	    value: function startIntersectionObserver() {
	      if (!('IntersectionObserver' in window)) {
	        return;
	      }
	      this.intersectionObserver = new IntersectionObserver(this._onIntersectionChange.bind(this), {
	        root: this.elements.userList.container,
	        threshold: 0.5
	      });
	    }
	  }, {
	    key: "observeIntersections",
	    /**
	     * @param {CallUser} callUser
	     */
	    value: function observeIntersections(callUser) {
	      if (this.intersectionObserver && callUser.elements.root) {
	        this.intersectionObserver.observe(callUser.elements.root);
	      }
	    }
	  }, {
	    key: "unobserveIntersections",
	    /**
	     * @param {CallUser} callUser
	     */
	    value: function unobserveIntersections(callUser) {
	      if (this.intersectionObserver && callUser.elements.root) {
	        this.intersectionObserver.unobserve(callUser.elements.root);
	      }
	    }
	  }, {
	    key: "showDeviceSelector",
	    value: function showDeviceSelector(bindElement) {
	      var _this4 = this,
	        _events;
	      if (this.deviceSelector) {
	        return;
	      }
	      this.deviceSelector = new DeviceSelector({
	        viewElement: this.container,
	        parentElement: bindElement,
	        zIndex: this.baseZIndex + 500,
	        microphoneEnabled: !this.isMuted,
	        microphoneId: this.microphoneId || Hardware.defaultMicrophone,
	        cameraEnabled: this.isCameraOn,
	        cameraId: this.cameraId,
	        speakerEnabled: !this.speakerMuted,
	        speakerId: this.speakerId,
	        allowHdVideo: Hardware.preferHdQuality,
	        faceImproveEnabled: Util$1.isDesktop() && typeof BX.desktop !== 'undefined' && BX.desktop.cameraSmoothingStatus(),
	        allowFaceImprove: Util$1.isDesktop() && typeof BX.desktop !== 'undefined' && BX.desktop.enableInVersion(64),
	        allowBackground: BackgroundDialog.isAvailable() && this.isIntranetOrExtranet,
	        allowMask: BackgroundDialog.isMaskAvailable() && this.isIntranetOrExtranet,
	        allowAdvancedSettings: typeof BXIM !== 'undefined' && this.isIntranetOrExtranet,
	        showCameraBlock: !this.isButtonBlocked('camera'),
	        events: (_events = {}, babelHelpers.defineProperty(_events, DeviceSelector.Events.onMicrophoneSelect, this._onMicrophoneSelected.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onMicrophoneSwitch, this._onMicrophoneButtonClick.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onCameraSelect, this._onCameraSelected.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onCameraSwitch, this._onCameraButtonClick.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onSpeakerSelect, this._onSpeakerSelected.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onSpeakerSwitch, this._onSpeakerButtonClick.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onChangeHdVideo, this._onChangeHdVideo.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onChangeMicAutoParams, this._onChangeMicAutoParams.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onChangeFaceImprove, this._onChangeFaceImprove.bind(this)), babelHelpers.defineProperty(_events, DeviceSelector.Events.onAdvancedSettingsClick, function () {
	          return _this4.eventEmitter.emit(EventName.onOpenAdvancedSettings);
	        }), babelHelpers.defineProperty(_events, DeviceSelector.Events.onDestroy, function () {
	          return _this4.deviceSelector = null;
	        }), babelHelpers.defineProperty(_events, DeviceSelector.Events.onShow, function () {
	          return _this4.eventEmitter.emit(EventName.onDeviceSelectorShow, {});
	        }), _events)
	      });
	      this.deviceSelector.show();
	    }
	  }, {
	    key: "showCallMenu",
	    value: function showCallMenu() {
	      var _this5 = this;
	      var menuItems = [{
	        text: BX.message("IM_M_CALL_BTN_WANT_TO_SAY"),
	        iconClass: "hand",
	        onClick: this._onMobileCallMenuFloorRequestClick.bind(this)
	      }, {
	        text: BX.message("IM_M_CALL_MOBILE_MENU_PARTICIPANTS_LIST"),
	        iconClass: "participants",
	        onClick: this._onMobileCallMenShowParticipantsClick.bind(this)
	      },
	      // TODO:
	      /*{
	      	text: "Add participant",
	      	iconClass: "add-participant",
	      	onClick: function() {}
	      },*/

	      /*{ //DEBUG: mobile audio
	      	text: "Enable audio",
	      	iconClass: "",
	      	onClick: function() {
	      		for (var userId in this.elements.audio)
	      		{
	      			if (this.users[userId].stream)
	      			{
	      				console.log('user ' + userId + ' stream found, trying to play');
	      				this.elements.audio[userId].srcObject = this.users[userId].stream;
	      				this.elements.audio[userId].play();
	      			}
	      		}
	      		this.callMenu.close();
	      	}.bind(this)
	      },*/
	      {
	        text: BX.message("IM_M_CALL_MOBILE_MENU_COPY_INVITE"),
	        iconClass: "add-participant",
	        onClick: this._onMobileCallMenuCopyInviteClick.bind(this)
	      }, !this.isIntranetOrExtranet ? {
	        text: BX.message("IM_M_CALL_MOBILE_MENU_CHANGE_MY_NAME"),
	        iconClass: "change-name",
	        onClick: function onClick() {
	          _this5.callMenu.close();
	          setTimeout(_this5.showRenameSlider.bind(_this5), 100);
	        }
	      } : null, {
	        separator: true
	      }, {
	        text: BX.message("IM_M_CALL_MOBILE_MENU_CANCEL"),
	        enabled: false,
	        onClick: this._onMobileCallMenuCancelClick.bind(this)
	      }];
	      this.callMenu = new MobileMenu({
	        parent: this.elements.root,
	        items: menuItems,
	        onClose: function onClose() {
	          return _this5.callMenu.destroy();
	        },
	        onDestroy: function onDestroy() {
	          return _this5.callMenu = null;
	        }
	      });
	      this.callMenu.show();
	    }
	  }, {
	    key: "showUserMenu",
	    value: function showUserMenu(userId) {
	      var _this6 = this;
	      var userModel = this.userRegistry.get(userId);
	      if (!userModel) {
	        return false;
	      }
	      var pinItem = null;
	      if (this.pinnedUser && this.pinnedUser.id == userId) {
	        pinItem = {
	          text: BX.message("IM_M_CALL_MOBILE_MENU_UNPIN"),
	          iconClass: "unpin",
	          onClick: function onClick() {
	            _this6.userMenu.close();
	            _this6.unpinUser();
	          }
	        };
	      } else if (this.userId != userId) {
	        pinItem = {
	          text: BX.message("IM_M_CALL_MOBILE_MENU_PIN"),
	          iconClass: "pin",
	          onClick: function onClick() {
	            _this6.userMenu.close();
	            _this6.pinUser(userId);
	          }
	        };
	      }
	      var menuItems = [{
	        userModel: userModel,
	        enabled: false
	      }, {
	        separator: true
	      }, pinItem, this.userId == userId && !this.isIntranetOrExtranet ? {
	        text: BX.message("IM_M_CALL_MOBILE_MENU_CHANGE_MY_NAME"),
	        iconClass: "change-name",
	        onClick: function onClick() {
	          _this6.userMenu.close();
	          setTimeout(_this6.showRenameSlider.bind(_this6), 100);
	        }
	      } : null,
	      /*{
	      	text: BX.message("IM_M_CALL_MOBILE_MENU_WRITE_TO_PRIVATE_CHAT"),
	      	iconClass: "private-chat",
	      	onClick: function()
	      	{
	      		this.userMenu.close();
	      		this.eventEmitter.emit(EventName.onButtonClick, {
	      			})
	      	}.bind(this)
	      },*/
	      /*{
	      	// TODO:
	      	text: "Remove user",
	      	iconClass: "remove-user"
	      },*/
	      {
	        separator: true
	      }, {
	        text: BX.message("IM_M_CALL_MOBILE_MENU_CANCEL"),
	        enabled: false,
	        onClick: function onClick() {
	          return _this6.userMenu.close();
	        }
	      }];
	      this.userMenu = new MobileMenu({
	        parent: this.elements.root,
	        items: menuItems,
	        onClose: function onClose() {
	          return _this6.userMenu.destroy();
	        },
	        onDestroy: function onDestroy() {
	          return _this6.userMenu = null;
	        }
	      });
	      this.userMenu.show();
	    }
	  }, {
	    key: "showParticipantsMenu",
	    value: function showParticipantsMenu() {
	      var _this7 = this;
	      if (this.participantsMenu) {
	        return;
	      }
	      var menuItems = [];
	      menuItems.push({
	        userModel: this.localUser.userModel,
	        showSubMenu: true,
	        onClick: function () {
	          this.participantsMenu.close();
	          this.showUserMenu(this.localUser.userModel.id);
	        }.bind(this)
	      });
	      this.userRegistry.users.forEach(function (userModel) {
	        if (userModel.localUser || userModel.state != UserState.Connected) {
	          return;
	        }
	        if (menuItems.length > 0) {
	          menuItems.push({
	            separator: true
	          });
	        }
	        menuItems.push({
	          userModel: userModel,
	          showSubMenu: true,
	          onClick: function onClick() {
	            _this7.participantsMenu.close();
	            _this7.showUserMenu(userModel.id);
	          }
	        });
	      });
	      if (menuItems.length === 0) {
	        return false;
	      }
	      this.participantsMenu = new MobileMenu({
	        parent: this.elements.root,
	        items: menuItems,
	        header: BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.getConnectedUserCount(true)),
	        largeIcons: true,
	        onClose: function () {
	          this.participantsMenu.destroy();
	        }.bind(this),
	        onDestroy: function () {
	          this.participantsMenu = null;
	        }.bind(this)
	      });
	      this.participantsMenu.show();
	      return true;
	    }
	  }, {
	    key: "showMessage",
	    /**
	     * @param {Object} params
	     * @param {string} params.text
	     * @param {string} [params.subText]
	     */
	    value: function showMessage(params) {
	      if (!this.elements.root) {
	        this.render();
	        this.container.appendChild(this.elements.root);
	      }
	      var statusNode = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-status bx-messenger-videocall-user-status-wide"
	        }
	      });
	      if (main_core.Type.isStringFilled(params.text)) {
	        var textNode = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-status-text"
	          },
	          text: params.text
	        });
	        statusNode.appendChild(textNode);
	      }
	      if (this.elements.overlay.childElementCount) {
	        main_core.Dom.clean(this.elements.overlay);
	      }
	      this.elements.overlay.appendChild(statusNode);
	    }
	  }, {
	    key: "hideMessage",
	    value: function hideMessage() {
	      this.elements.overlay.textContent = '';
	    }
	  }, {
	    key: "showFatalError",
	    /**
	     * @param {Object} params
	     * @param {string} params.text
	     * @param {string} [params.subText]
	     */
	    value: function showFatalError(params) {
	      this.showMessage(params);
	      this.setUiState(UiState.Error);
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.buttons.recordStatus) {
	        this.buttons.recordStatus.stopViewUpdate();
	      }
	      this.recordState = this.getDefaultRecordState();
	      if (this.elements.root) {
	        BX.remove(this.elements.root);
	      }
	      this.visible = false;
	      this.eventEmitter.emit(EventName.onClose);
	    }
	  }, {
	    key: "setSize",
	    value: function setSize(size) {
	      if (this.size == size) {
	        return;
	      }
	      this.size = size;
	      if (this.size == Size.Folded) {
	        if (this.overflownButtonsPopup) {
	          this.overflownButtonsPopup.close();
	        }
	        if (this.elements.panel) {
	          this.elements.panel.classList.add('bx-messenger-videocall-panel-folded');
	        }
	        main_core.Dom.remove(this.elements.container);
	        main_core.Dom.remove(this.elements.topPanel);
	        this.elements.root.style.removeProperty('max-width');
	        this.updateButtons();
	      } else {
	        if (this.elements.panel) {
	          this.elements.panel.classList.remove('bx-messenger-videocall-panel-folded');
	        }
	        this.elements.wrap.appendChild(this.elements.topPanel);
	        this.elements.wrap.appendChild(this.elements.container);
	        if (this.maxWidth > 0) {
	          this.elements.root.style.maxWidth = Math.max(this.maxWidth, MIN_WIDTH) + 'px';
	        }
	        this.updateButtons();
	        this.updateUserList();
	        this.resumeVideo();
	      }
	    }
	  }, {
	    key: "isButtonBlocked",
	    value: function isButtonBlocked(buttonName) {
	      switch (buttonName) {
	        case 'camera':
	          return this.uiState !== UiState.Preparing && this.uiState !== UiState.Connected || this.blockedButtons[buttonName] === true;
	        case 'chat':
	          return !this.showChatButtons || this.blockedButtons[buttonName] === true;
	        case 'floorRequest':
	          return this.uiState !== UiState.Connected || this.blockedButtons[buttonName] === true;
	        case 'screen':
	          return !this.showShareButton || !this.isScreenSharingSupported() || this.isFullScreen || this.blockedButtons[buttonName] === true;
	        case 'users':
	          return !this.showUsersButton || this.blockedButtons[buttonName] === true;
	        case 'record':
	          return !this.showRecordButton || this.blockedButtons[buttonName] === true;
	        case 'document':
	          return !this.showDocumentButton || this.blockedButtons[buttonName] === true;
	        default:
	          return this.blockedButtons[buttonName] === true;
	      }
	    }
	  }, {
	    key: "isButtonHidden",
	    value: function isButtonHidden(buttonName) {
	      return this.hiddenButtons[buttonName] === true;
	    }
	  }, {
	    key: "showButton",
	    value: function showButton(buttonCode) {
	      this.showButtons([buttonCode]);
	    }
	  }, {
	    key: "hideButton",
	    value: function hideButton(buttonCode) {
	      this.hideButtons([buttonCode]);
	    }
	  }, {
	    key: "checkPanelOverflow",
	    /**
	     * @return {bool} Returns true if buttons update is required
	     */
	    value: function checkPanelOverflow() {
	      var delta = this.elements.panel.scrollWidth - this.elements.panel.offsetWidth;
	      var mediumButtonMinWidth = 55; // todo: move to constants maybe? or maybe even calculate dynamically somehow?
	      if (delta > 0) {
	        var countOfButtonsToHide = Math.ceil(delta / mediumButtonMinWidth);
	        if (Object.keys(this.overflownButtons).length === 0) {
	          countOfButtonsToHide += 1;
	        }
	        var buttons = this.getButtonList();
	        for (var i = buttons.length - 1; i > 0; i--) {
	          if (buttons[i] === 'hangup' || buttons[i] === 'close' || buttons[i] === 'more') {
	            continue;
	          }
	          this.overflownButtons[buttons[i]] = true;
	          countOfButtonsToHide -= 1;
	          if (!countOfButtonsToHide) {
	            break;
	          }
	        }
	        return true;
	      } else {
	        var hiddenButtonsCount = Object.keys(this.overflownButtons).length;
	        if (hiddenButtonsCount > 0) {
	          var unusedPanelSpace = this.calculateUnusedPanelSpace();
	          if (unusedPanelSpace > mediumButtonMinWidth) {
	            var countOfButtonsToShow = Math.min(Math.floor(unusedPanelSpace / mediumButtonMinWidth), hiddenButtonsCount);
	            var buttonsLeftHidden = hiddenButtonsCount - countOfButtonsToShow;
	            if (buttonsLeftHidden === 1) {
	              countOfButtonsToShow += 1;
	            }
	            if (countOfButtonsToShow == hiddenButtonsCount) {
	              // show all buttons;
	              this.overflownButtons = {};
	            } else {
	              for (var _i = 0; _i < countOfButtonsToShow; _i++) {
	                delete this.overflownButtons[Object.keys(this.overflownButtons)[0]];
	              }
	            }
	            return true;
	          }
	        }
	      }
	      return false;
	    }
	  }, {
	    key: "showButtons",
	    /**
	     * @param {string[]} buttons Array of buttons names to show
	     */
	    value: function showButtons(buttons) {
	      var _this8 = this;
	      if (!main_core.Type.isArray(buttons)) {
	        console.error("buttons should be array");
	      }
	      buttons.forEach(function (buttonName) {
	        if (_this8.hiddenButtons.hasOwnProperty(buttonName)) {
	          delete _this8.hiddenButtons[buttonName];
	        }
	      });
	      this.updateButtons();
	    }
	  }, {
	    key: "hideButtons",
	    /**
	     * @param {string[]} buttons Array of buttons names to hide
	     */
	    value: function hideButtons(buttons) {
	      var _this9 = this;
	      if (!main_core.Type.isArray(buttons)) {
	        console.error("buttons should be array");
	      }
	      buttons.forEach(function (buttonName) {
	        return _this9.hiddenButtons[buttonName] = true;
	      });
	      this.updateButtons();
	    }
	  }, {
	    key: "blockAddUser",
	    value: function blockAddUser() {
	      this.blockedButtons['add'] = true;
	      if (this.elements.userList.addButton) {
	        main_core.Dom.remove(this.elements.userList.addButton);
	        this.elements.userList.addButton = null;
	      }
	    }
	  }, {
	    key: "blockSwitchCamera",
	    value: function blockSwitchCamera() {
	      this.blockedButtons['camera'] = true;
	    }
	  }, {
	    key: "unblockSwitchCamera",
	    value: function unblockSwitchCamera() {
	      delete this.blockedButtons['camera'];
	    }
	  }, {
	    key: "blockScreenSharing",
	    value: function blockScreenSharing() {
	      this.blockedButtons['screen'] = true;
	    }
	  }, {
	    key: "blockHistoryButton",
	    value: function blockHistoryButton() {
	      this.blockedButtons['history'] = true;
	    }
	  }, {
	    key: "blockButtons",
	    /**
	     * @param {string[]} buttons Array of buttons names to block
	     */
	    value: function blockButtons(buttons) {
	      var _this10 = this;
	      if (!main_core.Type.isArray(buttons)) {
	        console.error("buttons should be array");
	      }
	      buttons.forEach(function (buttonName) {
	        _this10.blockedButtons[buttonName] = true;
	        if (_this10.buttons[buttonName]) {
	          _this10.buttons[buttonName].setBlocked(true);
	        }
	      });
	    }
	  }, {
	    key: "unblockButtons",
	    /**
	     * @param {string[]} buttons Array of buttons names to unblock
	     */
	    value: function unblockButtons(buttons) {
	      var _this11 = this;
	      if (!main_core.Type.isArray(buttons)) {
	        console.error("buttons should be array");
	      }
	      buttons.forEach(function (buttonName) {
	        delete _this11.blockedButtons[buttonName];
	        if (_this11.buttons[buttonName]) {
	          _this11.buttons[buttonName].setBlocked(_this11.isButtonBlocked(buttonName));
	        }
	      });
	    }
	  }, {
	    key: "disableMediaSelection",
	    value: function disableMediaSelection() {
	      this.mediaSelectionBlocked = true;
	    }
	  }, {
	    key: "enableMediaSelection",
	    value: function enableMediaSelection() {
	      this.mediaSelectionBlocked = false;
	      if (this.buttons.microphone && this.isMediaSelectionAllowed()) {
	        this.buttons.microphone.showArrow();
	      }
	      if (this.buttons.camera && this.isMediaSelectionAllowed()) {
	        this.buttons.camera.showArrow();
	      }
	    }
	  }, {
	    key: "isMediaSelectionAllowed",
	    value: function isMediaSelectionAllowed() {
	      return this.layout != Layouts.Mobile && (this.uiState == UiState.Preparing || this.uiState == UiState.Connected) && !this.mediaSelectionBlocked && !this.isFullScreen;
	    }
	  }, {
	    key: "getButtonList",
	    value: function getButtonList() {
	      var _this12 = this;
	      if (this.uiState == UiState.Error) {
	        return ['close'];
	      }
	      if (this.uiState == UiState.Initializing) {
	        return ['hangup'];
	      }
	      if (this.size == Size.Folded) {
	        return ['title', 'spacer', 'returnToCall', 'hangup'];
	      }
	      var result = [];
	      result.push('microphone');
	      result.push('camera');
	      if (this.layout != Layouts.Mobile) {
	        result.push('speaker');
	      } else {
	        result.push('mobileMenu');
	      }
	      result.push('chat');
	      result.push('users');
	      if (this.layout != Layouts.Mobile) {
	        result.push('floorRequest');
	        result.push('screen');
	        result.push('record');
	        result.push('document');
	      }
	      result = result.filter(function (buttonCode) {
	        return !_this12.hiddenButtons.hasOwnProperty(buttonCode) && !_this12.overflownButtons.hasOwnProperty(buttonCode);
	      });
	      if (Object.keys(this.overflownButtons).length > 0) {
	        result.push('more');
	      }
	      if (this.uiState == UiState.Preparing) {
	        result.push('close');
	      } else {
	        result.push('hangup');
	      }
	      return result;
	    }
	  }, {
	    key: "getTopButtonList",
	    value: function getTopButtonList() {
	      var _this13 = this;
	      var result = [];
	      if (this.layout == Layouts.Mobile) {
	        return ['participantsMobile'];
	      }
	      result.push('watermark');
	      result.push('hd');
	      result.push('separator');
	      result.push('protected');
	      result.push('recordStatus');
	      result.push('spacer');
	      var separatorNeeded = false;
	      if (this.uiState === UiState.Connected && this.layout != Layouts.Mobile) {
	        result.push('grid');
	        separatorNeeded = true;
	      }
	      if (this.uiState != UiState.Preparing && this.isFullScreenSupported() && this.layout != Layouts.Mobile) {
	        result.push('fullscreen');
	        separatorNeeded = true;
	      }
	      if (this.uiState != UiState.Preparing) {
	        if (separatorNeeded) {
	          result.push('separator');
	        }
	        result.push('participants');
	      }
	      var previousButtonCode = '';
	      result = result.filter(function (buttonCode) {
	        if (previousButtonCode === 'spacer' && buttonCode === 'separator') {
	          return true;
	        }
	        previousButtonCode = buttonCode;
	        return !_this13.hiddenTopButtons.hasOwnProperty(buttonCode);
	      });
	      return result;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this14 = this;
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall"
	        },
	        children: [this.elements.wrap = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-wrap"
	          },
	          children: [this.elements.container = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-inner"
	            },
	            children: [this.elements.center = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-central-user"
	              },
	              events: {
	                touchstart: this._onCenterTouchStart.bind(this),
	                touchend: this._onCenterTouchEnd.bind(this)
	              }
	            }), this.elements.pageNavigatorLeft = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-page-navigator left"
	              },
	              children: [this.elements.pageNavigatorLeftCounter = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-page-navigator-counter left"
	                },
	                html: this.currentPage - 1 + '&nbsp;/&nbsp;' + this.pagesCount
	              }), main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-page-navigator-icon left"
	                }
	              })],
	              events: {
	                click: this._onLeftPageNavigatorClick.bind(this)
	              }
	            }), this.elements.pageNavigatorRight = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-page-navigator right"
	              },
	              children: [this.elements.pageNavigatorRightCounter = main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-page-navigator-counter right"
	                },
	                html: this.currentPage + 1 + '&nbsp;/&nbsp;' + this.pagesCount
	              }), main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-videocall-page-navigator-icon right"
	                }
	              })],
	              events: {
	                click: this._onRightPageNavigatorClick.bind(this)
	              }
	            })]
	          }), this.elements.topPanel = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-top-panel"
	            }
	          }), this.elements.notificationPanel = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-notification-panel"
	            }
	          }), this.elements.bottom = main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-bottom"
	            },
	            children: [this.elements.userSelectorContainer = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-bottom-user-selector-container"
	              }
	            }), this.elements.pinnedUserContainer = main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-bottom-pinned-user-container"
	              }
	            })]
	          })]
	        })],
	        events: {
	          click: this._onBodyClick.bind(this)
	        }
	      });
	      if (this.uiState == UiState.Preparing) {
	        this.elements.wrap.classList.add("with-clouds");
	      }
	      if (this.showButtonPanel) {
	        this.elements.panel = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel"
	          }
	        });
	        this.elements.bottom.appendChild(this.elements.panel);
	      } else {
	        this.elements.root.classList.add("bx-messenger-videocall-no-button-panel");
	      }
	      if (this.layout == Layouts.Mobile) {
	        this.userSelector = new UserSelectorMobile({
	          userRegistry: this.userRegistry
	        });
	        this.userSelector.mount(this.elements.userSelectorContainer);
	        this.elements.ear.left = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-mobile-ear left"
	          },
	          events: {
	            click: this._onLeftEarClick.bind(this)
	          }
	        });
	        this.elements.ear.right = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-mobile-ear right"
	          },
	          events: {
	            click: this._onRightEarClick.bind(this)
	          }
	        });
	        this.elements.localUserMobile = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-local-user-mobile"
	          }
	        });
	        if (window.innerHeight < window.innerWidth) {
	          this.elements.root.classList.add("orientation-landscape");
	        }
	        this.elements.wrap.appendChild(this.elements.ear.left);
	        this.elements.wrap.appendChild(this.elements.ear.right);
	        this.elements.wrap.appendChild(this.elements.localUserMobile);
	      }
	      this.centralUser.mount(this.elements.center);
	      this.elements.overlay = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-overlay"
	        }
	      });
	      this.elements.userBlock = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-block"
	        },
	        children: [this.elements.ear.top = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-top"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-ear-icon"
	            }
	          })],
	          events: {
	            mouseenter: this.scrollUserListUp.bind(this),
	            mouseleave: this.stopScroll.bind(this)
	          }
	        }), this.elements.ear.bottom = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-bottom"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-videocall-ear-icon"
	            }
	          })],
	          events: {
	            mouseenter: this.scrollUserListDown.bind(this),
	            mouseleave: this.stopScroll.bind(this)
	          }
	        })]
	      });
	      this.elements.userList.container = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-list"
	        },
	        events: {
	          scroll: main_core.Runtime.debounce(this.toggleEars.bind(this), 300),
	          wheel: function wheel(e) {
	            return _this14.elements.userList.container.scrollTop += e.deltaY;
	          }
	        }
	      });
	      this.elements.userList.addButton = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-user-add"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-user-add-inner"
	          }
	        })],
	        style: {
	          order: addButtonPosition
	        },
	        events: {
	          click: this._onAddButtonClick.bind(this)
	        }
	      });
	      if (this.layout == Layouts.Centered || this.layout == Layouts.Mobile) {
	        this.centralUser.mount(this.elements.center);
	        this.elements.root.classList.add("bx-messenger-videocall-centered");
	        if (this.layout != Layouts.Mobile) {
	          this.elements.container.appendChild(this.elements.userBlock);
	        }
	      }
	      if (this.layout == Layouts.Grid) {
	        this.elements.root.classList.add("bx-messenger-videocall-grid");
	      }
	      if (this.layout == Layouts.Mobile) {
	        this.elements.root.classList.add("bx-messenger-videocall-fullscreen-mobile");
	      }
	      this.resizeObserver.observe(this.elements.root);
	      this.resizeObserver.observe(this.container);
	      return this.elements.root;
	    }
	  }, {
	    key: "renderUserList",
	    value: function renderUserList() {
	      var showLocalUser = this.shouldShowLocalUser();
	      var userCount = 0;
	      var skipUsers = 0;
	      var skippedUsers = 0;
	      var renderedUsers = 0;
	      if (this.layout == Layouts.Grid && this.pagesCount > 1) {
	        skipUsers = (this.currentPage - 1) * this.usersPerPage;
	      }
	      for (var i = 0; i < this.userRegistry.users.length; i++) {
	        var userModel = this.userRegistry.users[i];
	        var userId = userModel.id;
	        if (!this.users.hasOwnProperty(userId)) {
	          continue;
	        }
	        var user = this.users[userId];
	        var screenUser = this.screenUsers[userId];
	        if (userId == this.centralUser.id && (this.layout == Layouts.Centered || this.layout == Layouts.Mobile)) {
	          this.unobserveIntersections(user);
	          if (screenUser.hasVideo()) {
	            screenUser.mount(this.elements.center);
	            screenUser.visible = true;
	            user.mount(this.elements.userList.container);
	          } else {
	            user.visible = true;
	            user.mount(this.elements.center);
	            screenUser.dismount();
	          }
	          continue;
	        }
	        var userState = userModel.state;
	        var userActive = userState != UserState.Idle && userState != UserState.Declined && userState != UserState.Unavailable && userState != UserState.Busy && userModel.direction != EndpointDirection.RecvOnly;
	        if (userActive && skipUsers > 0 && skippedUsers < skipUsers) {
	          // skip users on previous pages
	          skippedUsers++;
	          userActive = false;
	        }
	        if (userActive && this.layout == Layouts.Grid && this.usersPerPage > 0 && renderedUsers >= this.usersPerPage) {
	          // skip users on following pages
	          userActive = false;
	        }
	        if (!userActive) {
	          user.dismount();
	          this.unobserveIntersections(user);
	          screenUser.dismount();
	          continue;
	        }
	        if (screenUser.hasVideo()) {
	          screenUser.mount(this.elements.userList.container);
	          userCount++;
	        } else {
	          screenUser.dismount();
	        }
	        user.mount(this.elements.userList.container);
	        this.observeIntersections(user);
	        renderedUsers++;
	        userCount++;
	      }
	      if (showLocalUser) {
	        if (this.layout == Layouts.Centered && this.userId == this.centralUser.id || this.layout == Layouts.Mobile) {
	          // this.unobserveIntersections(this.localUser);
	          this.localUser.mount(this.elements.center, true);
	          this.localUser.visible = true;
	        } else {
	          // using force true to always move self to the end of the list
	          this.localUser.mount(this.elements.userList.container);
	          if (this.layout == Layouts.Centered && this.intersectionObserver) ; else {
	            this.localUser.visible = true;
	          }
	        }
	        userCount++;
	      } else {
	        this.localUser.dismount();
	        // this.unobserveIntersections(this.localUser);
	      }

	      if (this.layout == Layouts.Grid) {
	        this.updateGridUserSize(userCount);
	      } else {
	        this.elements.userList.container.classList.add("bx-messenger-videocall-user-list-small");
	        this.elements.userList.container.style.removeProperty('--avatar-size');
	        this.updateCentralUserAvatarSize();
	      }
	      this.applyIncomingVideoConstraints();
	      var showAdd = this.layout == Layouts.Centered && userCount > 0 /*&& !this.isFullScreen*/ && this.uiState === UiState.Connected && !this.isButtonBlocked("add") && this.getConnectedUserCount() < this.userLimit - 1;
	      if (showAdd && !this.isFullScreen) {
	        this.elements.userList.container.appendChild(this.elements.userList.addButton);
	      } else {
	        main_core.Dom.remove(this.elements.userList.addButton);
	      }
	      this.elements.root.classList.toggle("bx-messenger-videocall-user-list-empty", this.elements.userList.container.childElementCount === 0);
	      this.localUser.updatePanelDeferred();
	    }
	  }, {
	    key: "shouldShowLocalUser",
	    value: function shouldShowLocalUser() {
	      return this.localUser.userModel.state != UserState.Idle && this.localUser.userModel.direction != EndpointDirection.RecvOnly;
	    }
	  }, {
	    key: "updateGridUserSize",
	    value: function updateGridUserSize(userCount) {
	      var containerSize = this.elements.userList.container.getBoundingClientRect();
	      this.userSize = Util$1.findBestElementSize(containerSize.width, containerSize.height, userCount, MIN_GRID_USER_WIDTH, MIN_GRID_USER_HEIGHT);
	      var avatarSize = Math.round(this.userSize.height * 0.45);
	      this.elements.userList.container.style.setProperty('--grid-user-width', this.userSize.width + 'px');
	      this.elements.userList.container.style.setProperty('--grid-user-height', this.userSize.height + 'px');
	      this.elements.userList.container.style.setProperty('--avatar-size', avatarSize + 'px');
	      if (this.userSize.width < 220) {
	        this.elements.userList.container.classList.add("bx-messenger-videocall-user-list-small");
	      } else {
	        this.elements.userList.container.classList.remove("bx-messenger-videocall-user-list-small");
	      }
	    }
	  }, {
	    key: "updateCentralUserAvatarSize",
	    value: function updateCentralUserAvatarSize() {
	      var containerSize;
	      var avatarSize;
	      if (this.layout == Layouts.Mobile) {
	        containerSize = this.elements.root.getBoundingClientRect();
	        avatarSize = Math.round(containerSize.width * 0.55);
	      } else if (this.layout == Layouts.Centered) {
	        containerSize = this.elements.center.getBoundingClientRect();
	        avatarSize = Math.round(containerSize.height * 0.45);
	        avatarSize = Math.min(avatarSize, 142);
	        this.centralUser.setIncomingVideoConstraints(Math.floor(containerSize.width), Math.floor(containerSize.height));
	      }
	      this.elements.center.style.setProperty('--avatar-size', avatarSize + 'px');
	    }
	  }, {
	    key: "renderButtons",
	    value: function renderButtons(buttons) {
	      var _this15 = this;
	      var panelInner, left, center, right;
	      panelInner = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-videocall-panel-inner"
	        }
	      });
	      if (this.layout === Layouts.Mobile || this.size === Size.Folded) {
	        left = panelInner;
	        center = panelInner;
	        right = panelInner;
	      } else {
	        left = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel-inner-left"
	          }
	        });
	        center = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel-inner-center"
	          }
	        });
	        right = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-videocall-panel-inner-right"
	          }
	        });
	        panelInner.appendChild(left);
	        panelInner.appendChild(center);
	        panelInner.appendChild(right);
	      }
	      for (var i = 0; i < buttons.length; i++) {
	        switch (buttons[i]) {
	          case "title":
	            this.buttons.title = new TitleButton({
	              text: this.title,
	              isGroupCall: Object.keys(this.users).length > 1
	            });
	            left.appendChild(this.buttons.title.render());
	            break;
	          /*case "grid":
	          	this.buttons.grid = new SimpleButton({
	          		class: "grid",
	          		text: BX.message("IM_M_CALL_BTN_GRID"),
	          		onClick: this._onGridButtonClick.bind(this)
	          	});
	          	panelInner.appendChild(this.buttons.grid.render());
	          	break;*/
	          /*case "add":
	          	this.buttons.add = new SimpleButton({
	          		class: "add",
	          		text: BX.message("IM_M_CALL_BTN_ADD"),
	          		onClick: this._onAddButtonClick.bind(this)
	          	});
	          	leftSubPanel.appendChild(this.buttons.add.render());
	          	break;*/
	          case "share":
	            this.buttons.share = new SimpleButton({
	              "class": "share",
	              text: BX.message("IM_M_CALL_BTN_LINK"),
	              onClick: this._onShareButtonClick.bind(this)
	            });
	            center.appendChild(this.buttons.share.render());
	            break;
	          case "microphone":
	            this.buttons.microphone = new DeviceButton({
	              "class": "microphone",
	              text: BX.message("IM_M_CALL_BTN_MIC"),
	              enabled: !this.isMuted,
	              arrowHidden: this.layout == Layouts.Mobile,
	              arrowEnabled: this.isMediaSelectionAllowed(),
	              showPointer: true,
	              //todo
	              blocked: this.isButtonBlocked("microphone"),
	              showLevel: true,
	              sideIcon: this.getMicrophoneSideIcon(this.roomState),
	              onClick: function onClick(e) {
	                _this15._onMicrophoneButtonClick(e);
	                _this15._showMicrophoneHint(e);
	              },
	              onArrowClick: this._onMicrophoneArrowClick.bind(this),
	              onMouseOver: this._showMicrophoneHint.bind(this),
	              onMouseOut: function onMouseOut() {
	                return _this15._destroyHotKeyHint();
	              },
	              onSideIconClick: this._onMicrophoneSideIconClick.bind(this)
	            });
	            left.appendChild(this.buttons.microphone.render());
	            break;
	          case "camera":
	            this.buttons.camera = new DeviceButton({
	              "class": "camera",
	              text: BX.message("IM_M_CALL_BTN_CAMERA"),
	              enabled: this.isCameraOn,
	              arrowHidden: this.layout == Layouts.Mobile,
	              arrowEnabled: this.isMediaSelectionAllowed(),
	              blocked: this.isButtonBlocked("camera"),
	              onClick: this._onCameraButtonClick.bind(this),
	              onArrowClick: this._onCameraArrowClick.bind(this),
	              onMouseOver: function onMouseOver(e) {
	                _this15._showHotKeyHint(e.currentTarget.firstChild, "camera", _this15.keyModifier + " + V");
	              },
	              onMouseOut: function onMouseOut() {
	                _this15._destroyHotKeyHint();
	              }
	            });
	            left.appendChild(this.buttons.camera.render());
	            break;
	          case "screen":
	            if (!this.buttons.screen) {
	              this.buttons.screen = new SimpleButton({
	                "class": "screen",
	                text: BX.message("IM_M_CALL_BTN_SCREEN"),
	                blocked: this.isButtonBlocked("screen"),
	                onClick: this._onScreenButtonClick.bind(this),
	                onMouseOver: function onMouseOver(e) {
	                  _this15._showHotKeyHint(e.currentTarget, "screen", _this15.keyModifier + " + S");
	                },
	                onMouseOut: function onMouseOut() {
	                  _this15._destroyHotKeyHint();
	                }
	              });
	            } else {
	              this.buttons.screen.setBlocked(this.isButtonBlocked("screen"));
	            }
	            center.appendChild(this.buttons.screen.render());
	            break;
	          case "users":
	            if (!this.buttons.users) {
	              this.buttons.users = new SimpleButton({
	                "class": "users",
	                backgroundClass: "calm-counter",
	                text: BX.message("IM_M_CALL_BTN_USERS"),
	                blocked: this.isButtonBlocked("users"),
	                onClick: this._onUsersButtonClick.bind(this),
	                onMouseOver: function (e) {
	                  this._showHotKeyHint(e.currentTarget, "users", this.keyModifier + ' + U');
	                }.bind(this),
	                onMouseOut: function () {
	                  this._destroyHotKeyHint();
	                }.bind(this)
	              });
	            } else {
	              this.buttons.users.setBlocked(this.isButtonBlocked("users"));
	            }
	            center.appendChild(this.buttons.users.render());
	            break;
	          case "record":
	            if (!this.buttons.record) {
	              this.buttons.record = new SimpleButton({
	                "class": "record",
	                backgroundClass: "bx-messenger-videocall-panel-background-record",
	                text: BX.message("IM_M_CALL_BTN_RECORD"),
	                blocked: this.isButtonBlocked("record"),
	                onClick: this._onRecordToggleClick.bind(this),
	                onMouseOver: function onMouseOver(e) {
	                  if (_this15.isRecordingHotKeySupported()) {
	                    _this15._showHotKeyHint(e.currentTarget, "record", _this15.keyModifier + " + R");
	                  }
	                },
	                onMouseOut: function onMouseOut() {
	                  if (_this15.isRecordingHotKeySupported()) {
	                    _this15._destroyHotKeyHint();
	                  }
	                }
	              });
	            } else {
	              this.buttons.record.setBlocked(this.isButtonBlocked('record'));
	            }
	            center.appendChild(this.buttons.record.render());
	            break;
	          case "document":
	            if (!this.buttons.document) {
	              this.buttons.document = new SimpleButton({
	                "class": "document",
	                text: BX.message("IM_M_CALL_BTN_DOCUMENT"),
	                blocked: this.isButtonBlocked("document"),
	                onClick: this._onDocumentButtonClick.bind(this)
	              });
	            } else {
	              this.buttons.document.setBlocked(this.isButtonBlocked('document'));
	            }
	            center.appendChild(this.buttons.document.render());
	            break;
	          case "returnToCall":
	            this.buttons.returnToCall = new SimpleButton({
	              "class": "returnToCall",
	              text: BX.message("IM_M_CALL_BTN_RETURN_TO_CALL"),
	              onClick: this._onBodyClick.bind(this)
	            });
	            right.appendChild(this.buttons.returnToCall.render());
	            break;
	          case "hangup":
	            this.buttons.hangup = new SimpleButton({
	              "class": "hangup",
	              backgroundClass: "bx-messenger-videocall-panel-icon-background-hangup",
	              text: Object.keys(this.users).length > 1 ? BX.message("IM_M_CALL_BTN_DISCONNECT") : BX.message("IM_M_CALL_BTN_HANGUP"),
	              onClick: this._onHangupButtonClick.bind(this)
	            });
	            right.appendChild(this.buttons.hangup.render());
	            break;
	          case "close":
	            this.buttons.close = new SimpleButton({
	              "class": "close",
	              backgroundClass: "bx-messenger-videocall-panel-icon-background-hangup",
	              text: BX.message("IM_M_CALL_BTN_CLOSE"),
	              onClick: this._onCloseButtonClick.bind(this)
	            });
	            right.appendChild(this.buttons.close.render());
	            break;
	          case "speaker":
	            /*this.buttons.speaker = new Buttons.DeviceButton({
	            	class: "speaker",
	            	text: BX.message("IM_M_CALL_BTN_SPEAKER"),
	            	enabled: !this.speakerMuted,
	            	arrowEnabled: Hardware.canSelectSpeaker() && this.isMediaSelectionAllowed(),
	            	onClick: this._onSpeakerButtonClick.bind(this),
	            	onArrowClick: this._onSpeakerArrowClick.bind(this)
	            });
	            rightSubPanel.appendChild(this.buttons.speaker.render());*/
	            break;
	          case "mobileMenu":
	            if (!this.buttons.mobileMenu) {
	              this.buttons.mobileMenu = new SimpleButton({
	                "class": "sandwich",
	                text: BX.message("IM_M_CALL_BTN_MENU"),
	                onClick: this._onMobileMenuButtonClick.bind(this)
	              });
	            }
	            center.appendChild(this.buttons.mobileMenu.render());
	            break;
	          case "chat":
	            if (!this.buttons.chat) {
	              this.buttons.chat = new SimpleButton({
	                "class": "chat",
	                text: BX.message("IM_M_CALL_BTN_CHAT"),
	                blocked: this.isButtonBlocked("chat"),
	                onClick: this._onChatButtonClick.bind(this),
	                onMouseOver: function onMouseOver(e) {
	                  _this15._showHotKeyHint(e.currentTarget, "chat", _this15.keyModifier + " + C");
	                },
	                onMouseOut: function onMouseOut() {
	                  _this15._destroyHotKeyHint();
	                }
	              });
	            } else {
	              this.buttons.chat.setBlocked(this.isButtonBlocked('chat'));
	            }
	            center.appendChild(this.buttons.chat.render());
	            break;
	          case "floorRequest":
	            if (!this.buttons.floorRequest) {
	              this.buttons.floorRequest = new SimpleButton({
	                "class": "floor-request",
	                backgroundClass: "bx-messenger-videocall-panel-background-floor-request",
	                text: BX.message("IM_M_CALL_BTN_WANT_TO_SAY"),
	                blocked: this.isButtonBlocked("floorRequest"),
	                onClick: this._onFloorRequestButtonClick.bind(this),
	                onMouseOver: function onMouseOver(e) {
	                  _this15._showHotKeyHint(e.currentTarget, "floorRequest", _this15.keyModifier + " + H");
	                },
	                onMouseOut: function onMouseOut() {
	                  return _this15._destroyHotKeyHint();
	                }
	              });
	            } else {
	              this.buttons.floorRequest.setBlocked(this.isButtonBlocked('floorRequest'));
	            }
	            center.appendChild(this.buttons.floorRequest.render());
	            break;
	          case "more":
	            if (!this.buttons.more) {
	              this.buttons.more = new SimpleButton({
	                "class": "more",
	                onClick: this._onMoreButtonClick.bind(this)
	              });
	            }
	            center.appendChild(this.buttons.more.render());
	            break;
	          case "spacer":
	            panelInner.appendChild(main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-panel-spacer"
	              }
	            }));
	            break;
	          /*case "history":
	          	this.buttons.history = new Buttons.SimpleButton({
	          		class: "history",
	          		text: BX.message("IM_M_CALL_BTN_HISTORY"),
	          		onClick: this._onHistoryButtonClick.bind(this)
	          	});
	          	rightSubPanel.appendChild(this.buttons.history.render());
	          	break;*/
	        }
	      }

	      return panelInner;
	    }
	  }, {
	    key: "renderTopButtons",
	    value: function renderTopButtons(buttons) {
	      var _this16 = this;
	      var result = BX.createFragment();
	      for (var i = 0; i < buttons.length; i++) {
	        switch (buttons[i]) {
	          case "watermark":
	            this.buttons.waterMark = new WaterMarkButton({
	              language: this.language
	            });
	            result.appendChild(this.buttons.waterMark.render());
	            break;
	          case "hd":
	            this.buttons.hd = new TopFramelessButton({
	              iconClass: "hd"
	            });
	            result.appendChild(this.buttons.hd.render());
	            break;
	          case "protected":
	            this.buttons["protected"] = new TopFramelessButton({
	              iconClass: "protected",
	              textClass: "protected",
	              text: BX.message("IM_M_CALL_PROTECTED"),
	              onMouseOver: function onMouseOver(e) {
	                _this16.hintManager.show(e.currentTarget, BX.message("IM_M_CALL_PROTECTED_HINT"));
	              },
	              onMouseOut: function onMouseOut() {
	                _this16.hintManager.hide();
	              }
	            });
	            result.appendChild(this.buttons["protected"].render());
	            break;
	          case "recordStatus":
	            if (this.buttons.recordStatus) {
	              this.buttons.recordStatus.updateView();
	            } else {
	              this.buttons.recordStatus = new RecordStatusButton({
	                userId: this.userId,
	                recordState: this.recordState,
	                onPauseClick: this._onRecordPauseClick.bind(this),
	                onStopClick: this._onRecordStopClick.bind(this),
	                onMouseOver: this._onRecordMouseOver.bind(this),
	                onMouseOut: this._onRecordMouseOut.bind(this)
	              });
	            }
	            result.appendChild(this.buttons.recordStatus.render());
	            break;
	          case "grid":
	            this.buttons.grid = new TopButton({
	              iconClass: this.layout == Layouts.Grid ? "speaker" : "grid",
	              text: this.layout == Layouts.Grid ? BX.message("IM_M_CALL_SPEAKER_MODE") : BX.message("IM_M_CALL_GRID_MODE"),
	              onClick: this._onGridButtonClick.bind(this),
	              onMouseOver: function onMouseOver(e) {
	                _this16._showHotKeyHint(e.currentTarget, "grid", _this16.keyModifier + " + W", {
	                  position: "bottom"
	                });
	              },
	              onMouseOut: function onMouseOut() {
	                _this16._destroyHotKeyHint();
	              }
	            });
	            result.appendChild(this.buttons.grid.render());
	            break;
	          case "fullscreen":
	            this.buttons.fullscreen = new TopButton({
	              iconClass: this.isFullScreen ? "fullscreen-leave" : "fullscreen-enter",
	              text: this.isFullScreen ? BX.message("IM_M_CALL_WINDOW_MODE") : BX.message("IM_M_CALL_FULLSCREEN_MODE"),
	              onClick: this._onFullScreenButtonClick.bind(this)
	            });
	            result.appendChild(this.buttons.fullscreen.render());
	            break;
	          case "participants":
	            var foldButtonState = void 0;
	            if (this.isFullScreen && this.layout == Layouts.Centered) {
	              foldButtonState = this.isUserBlockFolded ? ParticipantsButton.FoldButtonState.Unfold : ParticipantsButton.FoldButtonState.Fold;
	            } else if (this.showUsersButton) {
	              foldButtonState = ParticipantsButton.FoldButtonState.Active;
	            } else {
	              foldButtonState = ParticipantsButton.FoldButtonState.Hidden;
	            }
	            if (this.buttons.participants) {
	              this.buttons.participants.update({
	                foldButtonState: foldButtonState,
	                allowAdding: !this.isButtonBlocked("add"),
	                count: this.getConnectedUserCount(true)
	              });
	            } else {
	              this.buttons.participants = new ParticipantsButton({
	                foldButtonState: foldButtonState,
	                allowAdding: !this.isButtonBlocked("add"),
	                count: this.getConnectedUserCount(true),
	                onListClick: this._onParticipantsButtonListClick.bind(this),
	                onAddClick: this._onAddButtonClick.bind(this)
	              });
	            }
	            result.appendChild(this.buttons.participants.render());
	            break;
	          case "participantsMobile":
	            this.buttons.participantsMobile = new ParticipantsButtonMobile({
	              count: this.getConnectedUserCount(true),
	              onClick: this._onParticipantsButtonMobileListClick.bind(this)
	            });
	            result.appendChild(this.buttons.participantsMobile.render());
	            break;
	          case "separator":
	            result.appendChild(main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-top-separator"
	              }
	            }));
	            break;
	          case "spacer":
	            result.appendChild(main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-videocall-top-panel-spacer"
	              }
	            }));
	            break;
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "calculateUnusedPanelSpace",
	    value: function calculateUnusedPanelSpace(buttonList) {
	      if (!buttonList) {
	        buttonList = this.getButtonList();
	      }
	      var totalButtonWidth = 0;
	      for (var i = 0; i < buttonList.length; i++) {
	        var button = this.buttons[buttonList[i]];
	        if (!button) {
	          continue;
	        }
	        var buttonWidth = button.elements.root ? button.elements.root.getBoundingClientRect().width : 0;
	        totalButtonWidth += buttonWidth;
	      }
	      return this.elements.panel.scrollWidth - totalButtonWidth - 32;
	    }
	  }, {
	    key: "setButtonActive",
	    value: function setButtonActive(buttonName, isActive) {
	      if (!this.buttons[buttonName]) {
	        return;
	      }
	      this.buttons[buttonName].setActive(isActive);
	    }
	  }, {
	    key: "getButtonActive",
	    value: function getButtonActive(buttonName) {
	      if (!this.buttons[buttonName]) {
	        return false;
	      }
	      return this.buttons[buttonName].isActive;
	    }
	  }, {
	    key: "setButtonCounter",
	    value: function setButtonCounter(buttonName, counter) {
	      if (!this.buttons[buttonName]) {
	        return;
	      }
	      this.buttons[buttonName].setCounter(counter);
	    }
	  }, {
	    key: "updateUserList",
	    value: function updateUserList() {
	      if (this.layout == Layouts.Mobile) {
	        if (this.localUser != this.centralUser) {
	          if (this.localUser.hasVideo()) {
	            this.localUser.mount(this.elements.localUserMobile);
	            this.localUser.visible = true;
	          } else {
	            this.localUser.dismount();
	          }
	          this.centralUser.mount(this.elements.center);
	          this.centralUser.visible = true;
	        }
	        return;
	      }
	      if (this.layout == Layouts.Grid && this.size == Size.Full) {
	        this.recalculatePages();
	      }
	      this.renderUserList();
	      if (this.layout == Layouts.Centered) {
	        if (!this.elements.userList.container.parentElement) {
	          this.elements.userBlock.appendChild(this.elements.userList.container);
	        }
	        //this.centralUser.setFullSize(this.elements.userList.container.childElementCount === 0);
	      } else if (this.layout == Layouts.Grid) {
	        if (!this.elements.userList.container.parentElement) {
	          this.elements.container.appendChild(this.elements.userList.container);
	        }
	      }
	      this.toggleEars();
	    }
	  }, {
	    key: "showOverflownButtonsPopup",
	    value: function showOverflownButtonsPopup() {
	      var _this17 = this;
	      if (this.overflownButtonsPopup) {
	        this.overflownButtonsPopup.show();
	        return;
	      }
	      var bindElement = this.buttons.more && this.buttons.more.elements.root ? this.buttons.more.elements.root : this.elements.panel;
	      this.overflownButtonsPopup = new main_popup.Popup({
	        id: 'bx-call-buttons-popup',
	        bindElement: bindElement,
	        targetContainer: this.container,
	        content: this.renderButtons(Object.keys(this.overflownButtons)),
	        cacheable: false,
	        closeIcon: false,
	        autoHide: true,
	        overlay: {
	          backgroundColor: 'white',
	          opacity: 0
	        },
	        bindOptions: {
	          position: 'top'
	        },
	        angle: {
	          position: 'bottom',
	          offset: 49
	        },
	        className: 'bx-call-buttons-popup',
	        contentBackground: 'unset',
	        events: {
	          onPopupDestroy: function onPopupDestroy() {
	            _this17.overflownButtonsPopup = null;
	            _this17.buttons.more.setActive(false);
	          }
	        }
	      });
	      this.overflownButtonsPopup.show();
	    }
	  }, {
	    key: "resumeVideo",
	    value: function resumeVideo() {
	      for (var userId in this.users) {
	        var user = this.users[userId];
	        user.playVideo();
	        var screenUser = this.screenUsers[userId];
	        screenUser.playVideo();
	      }
	      this.localUser.playVideo();
	    }
	  }, {
	    key: "updateUserButtons",
	    value: function updateUserButtons() {
	      for (var userId in this.users) {
	        if (this.users.hasOwnProperty(userId)) {
	          this.users[userId].allowPinButton = this.getConnectedUserCount() > 1;
	        }
	      }
	    }
	  }, {
	    key: "updateButtons",
	    value: function updateButtons() {
	      if (!this.elements.panel) {
	        return;
	      }
	      main_core.Dom.clean(this.elements.panel);
	      main_core.Dom.clean(this.elements.topPanel);
	      this.elements.panel.appendChild(this.renderButtons(this.getButtonList()));
	      if (this.elements.topPanel) {
	        this.elements.topPanel.appendChild(this.renderTopButtons(this.getTopButtonList()));
	      }
	      if (this.buttons.participantsMobile) {
	        this.buttons.participantsMobile.setCount(this.getConnectedUserCount(true));
	      }
	    }
	  }, {
	    key: "updateUserData",
	    value: function updateUserData(userData) {
	      for (var userId in userData) {
	        if (!this.userData[userId]) {
	          this.userData[userId] = {
	            name: '',
	            avatar_hr: '',
	            gender: 'M'
	          };
	        }
	        if (userData[userId].name) {
	          this.userData[userId].name = userData[userId].name;
	        }
	        if (userData[userId].avatar_hr) {
	          this.userData[userId].avatar_hr = Util$1.isAvatarBlank(userData[userId].avatar_hr) ? '' : userData[userId].avatar_hr;
	        } else if (userData[userId].avatar) {
	          this.userData[userId].avatar_hr = Util$1.isAvatarBlank(userData[userId].avatar) ? '' : userData[userId].avatar;
	        }
	        if (userData[userId].gender) {
	          this.userData[userId].gender = userData[userId].gender === 'F' ? 'F' : 'M';
	        }
	        var userModel = this.userRegistry.get(userId);
	        if (userModel) {
	          userModel.name = this.userData[userId].name;
	          userModel.avatar = this.userData[userId].avatar_hr;
	        }
	      }
	    }
	  }, {
	    key: "isScreenSharingSupported",
	    value: function isScreenSharingSupported() {
	      return navigator.mediaDevices && typeof navigator.mediaDevices.getDisplayMedia === "function" || typeof BXDesktopSystem !== "undefined";
	    }
	  }, {
	    key: "isRecordingHotKeySupported",
	    value: function isRecordingHotKeySupported() {
	      return typeof BXDesktopSystem !== "undefined" && BXDesktopSystem.ApiVersion() >= 60;
	    }
	  }, {
	    key: "isFullScreenSupported",
	    value: function isFullScreenSupported() {
	      if (BX.browser.IsChrome() || BX.browser.IsSafari()) {
	        return document.webkitFullscreenEnabled === true;
	      } else if (BX.browser.IsFirefox()) {
	        return document.fullscreenEnabled === true;
	      } else {
	        return false;
	      }
	    }
	  }, {
	    key: "toggleEars",
	    value: function toggleEars() {
	      this.toggleTopEar();
	      this.toggleBottomEar();
	      if (this.layout == Layouts.Grid && this.pagesCount > 1 && this.currentPage > 1) {
	        this.elements.pageNavigatorLeft.classList.add("active");
	      } else {
	        this.elements.pageNavigatorLeft.classList.remove("active");
	      }
	      if (this.layout == Layouts.Grid && this.pagesCount > 1 && this.currentPage < this.pagesCount) {
	        this.elements.pageNavigatorRight.classList.add("active");
	      } else {
	        this.elements.pageNavigatorRight.classList.remove("active");
	      }
	    }
	  }, {
	    key: "toggleTopEar",
	    value: function toggleTopEar() {
	      if (this.layout !== Layouts.Grid && this.elements.userList.container.scrollHeight > this.elements.userList.container.offsetHeight && this.elements.userList.container.scrollTop > 0) {
	        this.elements.ear.top.classList.add("active");
	      } else {
	        this.elements.ear.top.classList.remove("active");
	      }
	    }
	  }, {
	    key: "toggleBottomEar",
	    value: function toggleBottomEar() {
	      if (this.layout !== Layouts.Grid && this.elements.userList.container.offsetHeight + this.elements.userList.container.scrollTop < this.elements.userList.container.scrollHeight) {
	        this.elements.ear.bottom.classList.add("active");
	      } else {
	        this.elements.ear.bottom.classList.remove("active");
	      }
	    }
	  }, {
	    key: "scrollUserListUp",
	    value: function scrollUserListUp() {
	      var _this18 = this;
	      this.stopScroll();
	      this.scrollInterval = setInterval(function () {
	        return _this18.elements.userList.container.scrollTop -= 10;
	      }, 20);
	    }
	  }, {
	    key: "scrollUserListDown",
	    value: function scrollUserListDown() {
	      var _this19 = this;
	      this.stopScroll();
	      this.scrollInterval = setInterval(function () {
	        return _this19.elements.userList.container.scrollTop += 10;
	      }, 20);
	    }
	  }, {
	    key: "stopScroll",
	    value: function stopScroll() {
	      if (this.scrollInterval) {
	        clearInterval(this.scrollInterval);
	        this.scrollInterval = 0;
	      }
	    }
	  }, {
	    key: "toggleRenameSliderInputLoader",
	    value: function toggleRenameSliderInputLoader() {
	      this.elements.renameSlider.button.classList.add('ui-btn-wait');
	    }
	  }, {
	    key: "setHotKeyTemporaryBlock",
	    value: function setHotKeyTemporaryBlock(isActive, force) {
	      if (!!isActive) {
	        this.hotKeyTemporaryBlock++;
	      } else {
	        this.hotKeyTemporaryBlock--;
	        if (this.hotKeyTemporaryBlock < 0 || force) {
	          this.hotKeyTemporaryBlock = 0;
	        }
	      }
	    }
	  }, {
	    key: "setHotKeyActive",
	    value: function setHotKeyActive(name, isActive) {
	      if (typeof this.hotKey[name] === 'undefined') {
	        return;
	      }
	      this.hotKey[name] = !!isActive;
	    }
	  }, {
	    key: "isHotKeyActive",
	    value: function isHotKeyActive(name) {
	      if (!this.hotKey['all']) {
	        return false;
	      }
	      if (this.hotKeyTemporaryBlock > 0) {
	        return false;
	      }
	      if (this.isButtonHidden(name)) {
	        return false;
	      }
	      if (this.isButtonBlocked(name)) {
	        return false;
	      }
	      return !!this.hotKey[name];
	    } // event handlers
	  }, {
	    key: "_onBodyClick",
	    value: function _onBodyClick() {
	      this.eventEmitter.emit(EventName.onBodyClick);
	    }
	  }, {
	    key: "_onCenterTouchStart",
	    value: function _onCenterTouchStart(e) {
	      this.centerTouchX = e.pageX;
	    }
	  }, {
	    key: "_onCenterTouchEnd",
	    value: function _onCenterTouchEnd(e) {
	      var delta = e.pageX - this.centerTouchX;
	      if (delta > 100) {
	        this.pinUser(this.getRightUser(this.centralUser.id));
	        e.preventDefault();
	      }
	      if (delta < -100) {
	        this.pinUser(this.getLeftUser(this.centralUser.id));
	        e.preventDefault();
	      }
	    }
	  }, {
	    key: "_onFullScreenChange",
	    value: function _onFullScreenChange() {
	      if ("webkitFullscreenElement" in document) {
	        this.isFullScreen = !!document.webkitFullscreenElement;
	      } else if ("fullscreenElement" in document) {
	        this.isFullScreen = !!document.fullscreenElement;
	      } else {
	        return;
	      }

	      // safari workaround
	      setTimeout(function () {
	        if (!this.elements.root) {
	          return;
	        }
	        if (this.isFullScreen) {
	          this.elements.root.classList.add("bx-messenger-videocall-fullscreen");
	        } else {
	          this.elements.root.classList.remove("bx-messenger-videocall-fullscreen");
	        }
	        this.updateUserList();
	        this.updateButtons();
	        this.setUserBlockFolded(this.isFullScreen);
	      }.bind(this), 0);
	    }
	  }, {
	    key: "_onIntersectionChange",
	    value: function _onIntersectionChange(entries) {
	      var t = {};
	      entries.forEach(function (intersectionEntry) {
	        t[intersectionEntry.target.dataset.userId] = intersectionEntry.isIntersecting;
	      });
	      for (var userId in t) {
	        if (this.users[userId]) {
	          this.users[userId].visible = t[userId];
	        }
	        if (userId == this.localUser.id) {
	          this.localUser.visible = t[userId];
	        }
	      }
	    }
	  }, {
	    key: "_onResize",
	    value: function _onResize() {
	      // this.resizeCalled++;
	      // this.reportResizeCalled();

	      if (!this.elements.root) {
	        return;
	      }
	      if (this.centralUser) ;
	      if (BX.browser.IsMobile()) {
	        document.documentElement.style.setProperty('--view-height', window.innerHeight + 'px');
	      }
	      if (this.layout == Layouts.Grid) {
	        this.updateUserList();
	      } else {
	        this.updateCentralUserAvatarSize();
	        this.toggleEars();
	      }
	      var rootDimensions = this.elements.root.getBoundingClientRect();
	      this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-450", rootDimensions.width < 450);
	      this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-550", rootDimensions.width < 550);
	      this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-650", rootDimensions.width < 650);
	      this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-700", rootDimensions.width < 700);
	      this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-850", rootDimensions.width < 850);
	      this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-900", rootDimensions.width < 900);

	      /*if (this.maxWidth === 0)
	      {
	      	this.elements.root.style.maxWidth = this.container.clientWidth + 'px';
	      }*/

	      if (this.checkPanelOverflow()) {
	        this.updateButtons();
	        if (this.overflownButtonsPopup && !Object.keys(this.overflownButtons).length) {
	          this.overflownButtonsPopup.close();
	        }
	      }
	    }
	  }, {
	    key: "_onOrientationChange",
	    value: function _onOrientationChange() {
	      if (!this.elements.root) {
	        return;
	      }
	      if (window.innerHeight > window.innerWidth) {
	        this.elements.root.classList.remove("orientation-landscape");
	      } else {
	        this.elements.root.classList.add("orientation-landscape");
	      }
	    }
	  }, {
	    key: "_showHotKeyHint",
	    value: function _showHotKeyHint(targetNode, name, text, options) {
	      var existingHint = BX.PopupWindowManager.getPopupById('ui-hint-popup');
	      if (existingHint) {
	        existingHint.destroy();
	      }
	      if (!this.isHotKeyActive(name)) {
	        return;
	      }
	      options = options || {};
	      this.hintManager.popupParameters.events = {
	        onShow: function onShow(event) {
	          var popup = event.getTarget();
	          // hack to get hint sizes
	          popup.getPopupContainer().style.display = 'block';
	          if (options.position === 'bottom') {
	            popup.setOffset({
	              offsetTop: 10,
	              offsetLeft: targetNode.offsetWidth / 2 - popup.getPopupContainer().offsetWidth / 2
	            });
	          } else {
	            popup.setOffset({
	              offsetLeft: targetNode.offsetWidth / 2 - popup.getPopupContainer().offsetWidth / 2
	            });
	          }
	        }
	      };
	      this.hintManager.show(targetNode, text);
	    }
	  }, {
	    key: "_destroyHotKeyHint",
	    value: function _destroyHotKeyHint() {
	      if (!Util$1.isDesktop()) {
	        return;
	      }
	      if (!this.hintManager.popup) {
	        return;
	      }

	      // we need to destroy, not .hide for onShow event handler (see method _showHotKeyHint).
	      this.hintManager.popup.destroy();
	      this.hintManager.popup = null;
	    }
	  }, {
	    key: "_showMicrophoneHint",
	    value: function _showMicrophoneHint(e) {
	      this.hintManager.hide();
	      if (!this.isHotKeyActive("microphone")) {
	        return;
	      }
	      var micHotkeys = '';
	      if (this.isMuted && this.isHotKeyActive("microphoneSpace")) {
	        micHotkeys = BX.message("IM_SPACE_HOTKEY") + '<br>';
	      }
	      micHotkeys += this.keyModifier + ' + A';
	      this._showHotKeyHint(e.currentTarget.firstChild, "microphone", micHotkeys);
	    }
	  }, {
	    key: "_onKeyDown",
	    value: function _onKeyDown(e) {
	      if (!Util$1.isDesktop()) {
	        return;
	      }
	      if (!(e.shiftKey && (e.ctrlKey || e.metaKey)) && !(e.code === 'Space')) {
	        return;
	      }
	      if (event.repeat) {
	        return;
	      }
	      var callMinimized = this.size === View.Size.Folded;
	      if (e.code === 'KeyA' && this.isHotKeyActive('microphone')) {
	        e.preventDefault();
	        this._onMicrophoneButtonClick(e);
	      } else if (e.code === 'Space' && this.isMuted && this.isHotKeyActive('microphoneSpace')) {
	        if (!callMinimized) {
	          e.preventDefault();
	          this.pushToTalk = true;
	          this.microphoneHotkeyTimerId = setTimeout(function () {
	            this._onMicrophoneButtonClick(e);
	          }.bind(this), 100);
	        }
	      } else if (e.code === 'KeyS' && this.isHotKeyActive('screen')) {
	        e.preventDefault();
	        this._onScreenButtonClick(e);
	      } else if (e.code === 'KeyV' && this.isHotKeyActive('camera')) {
	        e.preventDefault();
	        this._onCameraButtonClick(e);
	      } else if (e.code === 'KeyU' && this.isHotKeyActive('users')) {
	        e.preventDefault();
	        this._onUsersButtonClick(e);
	      } else if (e.code === 'KeyR' && this.isRecordingHotKeySupported() && this.isHotKeyActive('record')) {
	        e.preventDefault();
	        this._onForceRecordToggleClick(e);
	      } else if (e.code === 'KeyH' && this.isHotKeyActive('floorRequest')) {
	        e.preventDefault();
	        this._onFloorRequestButtonClick(e);
	      } else if (e.code === 'KeyC' && this.isHotKeyActive('chat')) {
	        e.preventDefault();
	        if (callMinimized) {
	          this._onBodyClick(e);
	        } else {
	          this._onChatButtonClick(e);
	          this._destroyHotKeyHint();
	        }
	      } else if (e.code === 'KeyM' && this.isHotKeyActive('muteSpeaker')) {
	        e.preventDefault();
	        this.eventEmitter.emit(EventName.onButtonClick, {
	          buttonName: "toggleSpeaker",
	          speakerMuted: this.speakerMuted,
	          fromHotKey: true
	        });
	      } else if (e.code === 'KeyW' && this.isHotKeyActive('grid')) {
	        e.preventDefault();
	        this.setLayout(this.layout == Layouts.Centered ? Layouts.Grid : Layouts.Centered);
	      }
	    }
	  }, {
	    key: "_onKeyUp",
	    value: function _onKeyUp(e) {
	      if (!Util$1.isDesktop()) {
	        return;
	      }
	      clearTimeout(this.microphoneHotkeyTimerId);
	      if (this.pushToTalk && !this.isMuted && e.code === 'Space') {
	        e.preventDefault();
	        this.pushToTalk = false;
	        this._onMicrophoneButtonClick(e);
	      }
	    }
	  }, {
	    key: "_onUserClick",
	    value: function _onUserClick(e) {
	      var userId = e.userId;
	      if (userId == this.userId) {
	        return;
	      }

	      /*if(this.layout == Layouts.Grid)
	      {
	      	this.setLayout(Layouts.Centered);
	      }*/
	      if (userId == this.centralUser.id && this.layout != Layouts.Grid) {
	        this.elements.root.classList.toggle("bx-messenger-videocall-hidden-panels");
	      }
	      if (this.layout == Layouts.Centered && userId != this.centralUser.id) {
	        this.pinUser(userId);
	      }
	      this.eventEmitter.emit(EventName.onUserClick, {
	        userId: userId,
	        stream: userId == this.userId ? this.localUser.stream : this.users[userId].stream
	      });
	    }
	  }, {
	    key: "_onUserRename",
	    value: function _onUserRename(newName) {
	      this.eventEmitter.emit(EventName.onUserRename, {
	        newName: newName
	      });
	    }
	  }, {
	    key: "_onUserRenameInputFocus",
	    value: function _onUserRenameInputFocus() {
	      this.setHotKeyTemporaryBlock(true);
	    }
	  }, {
	    key: "_onUserRenameInputBlur",
	    value: function _onUserRenameInputBlur() {
	      this.setHotKeyTemporaryBlock(false);
	    }
	  }, {
	    key: "_onUserPin",
	    value: function _onUserPin(e) {
	      if (this.layout == Layouts.Grid) {
	        this.setLayout(Layouts.Centered);
	      }
	      this.pinUser(e.userId);
	    }
	  }, {
	    key: "_onUserUnPin",
	    value: function _onUserUnPin() {
	      this.unpinUser();
	    }
	  }, {
	    key: "_onRecordToggleClick",
	    value: function _onRecordToggleClick(e) {
	      if (this.recordState.state === View.RecordState.Stopped) {
	        this._onRecordStartClick(e);
	      } else {
	        this._onRecordStopClick(e);
	      }
	    }
	  }, {
	    key: "_onForceRecordToggleClick",
	    value: function _onForceRecordToggleClick(e) {
	      if (this.recordState.state === View.RecordState.Stopped) {
	        this._onForceRecordStartClick(View.RecordType.Video);
	      } else {
	        this._onRecordStopClick(e);
	      }
	    }
	  }, {
	    key: "_onForceRecordStartClick",
	    value: function _onForceRecordStartClick(recordType) {
	      if (typeof recordType === 'undefined') {
	        recordType = View.RecordType.None;
	      }
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "record",
	        recordState: View.RecordState.Started,
	        forceRecord: recordType,
	        // none, video, audio
	        node: null
	      });
	    }
	  }, {
	    key: "_onRecordStartClick",
	    value: function _onRecordStartClick(e) {
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "record",
	        recordState: View.RecordState.Started,
	        node: e.currentTarget
	      });
	    }
	  }, {
	    key: "_onRecordPauseClick",
	    value: function _onRecordPauseClick(e) {
	      var recordState;
	      if (this.recordState.state === View.RecordState.Paused) {
	        this.recordState.state = View.RecordState.Started;
	        recordState = View.RecordState.Resumed;
	      } else {
	        this.recordState.state = View.RecordState.Paused;
	        recordState = this.recordState.state;
	      }
	      this.buttons.recordStatus.update(this.recordState);
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "record",
	        recordState: recordState,
	        node: e.currentTarget
	      });
	    }
	  }, {
	    key: "_onRecordStopClick",
	    value: function _onRecordStopClick(e) {
	      this.recordState.state = View.RecordState.Stopped;
	      this.buttons.recordStatus.update(this.recordState);
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "record",
	        recordState: this.recordState.state,
	        node: e.currentTarget
	      });
	    }
	  }, {
	    key: "_onRecordMouseOver",
	    value: function _onRecordMouseOver(e) {
	      if (this.recordState.userId == this.userId || !this.userData[this.recordState.userId]) {
	        return;
	      }
	      var recordingUserName = main_core.Text.encode(this.userData[this.recordState.userId].name);
	      this.hintManager.show(e.currentTarget, BX.message("IM_M_CALL_RECORD_HINT").replace("#USER_NAME#", recordingUserName));
	    }
	  }, {
	    key: "_onRecordMouseOut",
	    value: function _onRecordMouseOut() {
	      this.hintManager.hide();
	    }
	  }, {
	    key: "_onDocumentButtonClick",
	    value: function _onDocumentButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'document',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onGridButtonClick",
	    value: function _onGridButtonClick() {
	      this.setLayout(this.layout == Layouts.Centered ? Layouts.Grid : Layouts.Centered);
	    }
	  }, {
	    key: "_onAddButtonClick",
	    value: function _onAddButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "inviteUser",
	        node: e.currentTarget
	      });
	    }
	  }, {
	    key: "_onShareButtonClick",
	    value: function _onShareButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "share",
	        node: e.currentTarget
	      });
	    }
	  }, {
	    key: "_onMicrophoneButtonClick",
	    value: function _onMicrophoneButtonClick(e) {
	      if ("stopPropagation" in e) {
	        e.stopPropagation();
	      }
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "toggleMute",
	        muted: !this.isMuted
	      });
	    }
	  }, {
	    key: "_onMicrophoneArrowClick",
	    value: function _onMicrophoneArrowClick(e) {
	      e.stopPropagation();
	      this.showDeviceSelector(e.currentTarget);
	    }
	  }, {
	    key: "_onMicrophoneSideIconClick",
	    value: function _onMicrophoneSideIconClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "microphoneSideIcon"
	      });
	    }
	  }, {
	    key: "_onMicrophoneSelected",
	    value: function _onMicrophoneSelected(e) {
	      if (e.data.deviceId === this.microphoneId) {
	        return;
	      }
	      this.eventEmitter.emit(EventName.onReplaceMicrophone, {
	        deviceId: e.data.deviceId
	      });
	    }
	  }, {
	    key: "_onCameraButtonClick",
	    value: function _onCameraButtonClick(e) {
	      if ("stopPropagation" in e) {
	        e.stopPropagation();
	      }
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "toggleVideo",
	        video: !this.isCameraOn
	      });
	    }
	  }, {
	    key: "_onCameraArrowClick",
	    value: function _onCameraArrowClick(e) {
	      e.stopPropagation();
	      this.showDeviceSelector(e.currentTarget);
	    }
	  }, {
	    key: "_onCameraSelected",
	    value: function _onCameraSelected(e) {
	      if (e.data.deviceId === this.cameraId) {
	        return;
	      }
	      this.eventEmitter.emit(EventName.onReplaceCamera, {
	        deviceId: e.data.deviceId
	      });
	    }
	  }, {
	    key: "_onSpeakerButtonClick",
	    value: function _onSpeakerButtonClick() {
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "toggleSpeaker",
	        speakerMuted: this.speakerMuted
	      });
	    }
	  }, {
	    key: "_onChangeHdVideo",
	    value: function _onChangeHdVideo(e) {
	      this.eventEmitter.emit(EventName.onChangeHdVideo, e.data);
	    }
	  }, {
	    key: "_onChangeMicAutoParams",
	    value: function _onChangeMicAutoParams(e) {
	      this.eventEmitter.emit(EventName.onChangeMicAutoParams, e.data);
	    }
	  }, {
	    key: "_onChangeFaceImprove",
	    value: function _onChangeFaceImprove(e) {
	      this.eventEmitter.emit(EventName.onChangeFaceImprove, e.data);
	    }
	  }, {
	    key: "_onSpeakerSelected",
	    value: function _onSpeakerSelected(e) {
	      this.setSpeakerId(e.data.deviceId);
	      this.eventEmitter.emit(EventName.onReplaceSpeaker, {
	        deviceId: e.data.deviceId
	      });
	    }
	  }, {
	    key: "_onScreenButtonClick",
	    value: function _onScreenButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'toggleScreenSharing',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onChatButtonClick",
	    value: function _onChatButtonClick(e) {
	      this.hintManager.hide();
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'showChat',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onUsersButtonClick",
	    value: function _onUsersButtonClick(e) {
	      this.hintManager.hide();
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'toggleUsers',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onMobileMenuButtonClick",
	    value: function _onMobileMenuButtonClick(e) {
	      e.stopPropagation();
	      this.showCallMenu();
	    }
	  }, {
	    key: "_onFloorRequestButtonClick",
	    value: function _onFloorRequestButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'floorRequest',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onMoreButtonClick",
	    value: function _onMoreButtonClick(e) {
	      e.stopPropagation();
	      if (this.overflownButtonsPopup) {
	        this.overflownButtonsPopup.close();
	        this.buttons.more.setActive(false);
	      } else {
	        this.showOverflownButtonsPopup();
	        this.buttons.more.setActive(true);
	      }
	    }
	  }, {
	    key: "_onHistoryButtonClick",
	    value: function _onHistoryButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'showHistory',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onHangupButtonClick",
	    value: function _onHangupButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'hangup',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onCloseButtonClick",
	    value: function _onCloseButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'close',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onFullScreenButtonClick",
	    value: function _onFullScreenButtonClick(e) {
	      e.stopPropagation();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'fullscreen',
	        node: e.target
	      });
	    }
	  }, {
	    key: "_onParticipantsButtonListClick",
	    value: function _onParticipantsButtonListClick(event) {
	      if (!this.isButtonBlocked('users')) {
	        this._onUsersButtonClick(event);
	        return;
	      }
	      if (!this.isFullScreen) {
	        return;
	      }
	      this.setUserBlockFolded(!this.isUserBlockFolded);
	    }
	  }, {
	    key: "_onParticipantsListButtonClick",
	    value: function _onParticipantsListButtonClick(e) {
	      var _this20 = this;
	      e.stopPropagation();
	      var viewEvent = new main_core_events.BaseEvent({
	        data: {
	          buttonName: 'participantsList',
	          node: e.target
	        },
	        compatData: ['participantsList', e.target]
	      });
	      this.eventEmitter.emit(EventName.onButtonClick, viewEvent);
	      if (viewEvent.isDefaultPrevented()) {
	        return;
	      }
	      UserSelector.create({
	        parentElement: e.currentTarget,
	        zIndex: this.baseZIndex + 500,
	        userList: Object.values(this.users),
	        current: this.centralUser.id,
	        onSelect: function onSelect(userId) {
	          return _this20.setCentralUser(userId);
	        }
	      }).show();
	    }
	  }, {
	    key: "_onParticipantsButtonMobileListClick",
	    value: function _onParticipantsButtonMobileListClick() {
	      this.showParticipantsMenu();
	    }
	  }, {
	    key: "_onMobileCallMenuFloorRequestClick",
	    value: function _onMobileCallMenuFloorRequestClick() {
	      this.callMenu.close();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: 'floorRequest'
	      });
	    }
	  }, {
	    key: "_onMobileCallMenShowParticipantsClick",
	    value: function _onMobileCallMenShowParticipantsClick() {
	      this.callMenu.close();
	      this.showParticipantsMenu();
	    }
	  }, {
	    key: "_onMobileCallMenuCopyInviteClick",
	    value: function _onMobileCallMenuCopyInviteClick() {
	      this.callMenu.close();
	      this.eventEmitter.emit(EventName.onButtonClick, {
	        buttonName: "share",
	        node: null
	      });
	    }
	  }, {
	    key: "showRenameSlider",
	    value: function showRenameSlider() {
	      var _this21 = this;
	      if (!this.renameSlider) {
	        this.renameSlider = new MobileSlider({
	          parent: this.elements.root,
	          content: this.renderRenameSlider(),
	          onClose: function onClose() {
	            return _this21.renameSlider.destroy();
	          },
	          onDestroy: function onDestroy() {
	            return _this21.renameSlider = null;
	          }
	        });
	      }
	      this.renameSlider.show();
	      setTimeout(function () {
	        _this21.elements.renameSlider.input.focus();
	        _this21.elements.renameSlider.input.select();
	      }, 400);
	    }
	  }, {
	    key: "renderRenameSlider",
	    value: function renderRenameSlider() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-videocall-mobile-rename-slider-wrap"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-videocall-mobile-rename-slider-title"
	          },
	          text: BX.message("IM_M_CALL_MOBILE_MENU_CHANGE_MY_NAME")
	        }), this.elements.renameSlider.input = main_core.Dom.create("input", {
	          props: {
	            className: "bx-videocall-mobile-rename-slider-input"
	          },
	          attrs: {
	            type: "text",
	            value: this.localUser.userModel.name
	          }
	        }), this.elements.renameSlider.button = main_core.Dom.create("button", {
	          props: {
	            className: "bx-videocall-mobile-rename-slider-button ui-btn ui-btn-md ui-btn-primary"
	          },
	          text: BX.message("IM_M_CALL_MOBILE_RENAME_CONFIRM"),
	          events: {
	            click: this._onMobileUserRename.bind(this)
	          }
	        })]
	      });
	    }
	  }, {
	    key: "_onMobileUserRename",
	    value: function _onMobileUserRename(event) {
	      event.stopPropagation();
	      var inputValue = this.elements.renameSlider.input.value;
	      var newName = inputValue.trim();
	      var needToUpdate = true;
	      if (newName === this.localUser.userModel.name || newName === '') {
	        needToUpdate = false;
	      }
	      if (needToUpdate) {
	        this.toggleRenameSliderInputLoader();
	        this._onUserRename(newName);
	      } else {
	        this.renameSlider.close();
	      }
	    }
	  }, {
	    key: "_onMobileCallMenuCancelClick",
	    value: function _onMobileCallMenuCancelClick() {
	      this.callMenu.close();
	    }
	  }, {
	    key: "_onLeftEarClick",
	    value: function _onLeftEarClick() {
	      this.pinUser(this.getLeftUser(this.centralUser.id));
	    }
	  }, {
	    key: "_onRightEarClick",
	    value: function _onRightEarClick() {
	      this.pinUser(this.getRightUser(this.centralUser.id));
	    }
	  }, {
	    key: "_onLeftPageNavigatorClick",
	    value: function _onLeftPageNavigatorClick(e) {
	      e.stopPropagation();
	      this.setCurrentPage(this.currentPage - 1);
	    }
	  }, {
	    key: "_onRightPageNavigatorClick",
	    value: function _onRightPageNavigatorClick(e) {
	      e.stopPropagation();
	      this.setCurrentPage(this.currentPage + 1);
	    }
	  }, {
	    key: "setMaxWidth",
	    value: function setMaxWidth(maxWidth) {
	      if (this.maxWidth !== maxWidth) {
	        var MAX_WIDTH_SPEAKER_MODE = 650;
	        if (maxWidth < MAX_WIDTH_SPEAKER_MODE && (!this.maxWidth || this.maxWidth > MAX_WIDTH_SPEAKER_MODE) && this.layout === Layouts.Centered) {
	          this.setLayout(Layouts.Grid);
	        }
	        var animateUnsetProperty = this.maxWidth === null;
	        this.maxWidth = maxWidth;
	        if (this.size !== View.Size.Folded) {
	          this._applyMaxWidth(animateUnsetProperty);
	        }
	      }
	    }
	  }, {
	    key: "removeMaxWidth",
	    value: function removeMaxWidth() {
	      this.setMaxWidth(null);
	    }
	  }, {
	    key: "_applyMaxWidth",
	    value: function _applyMaxWidth(animateUnsetProperty) {
	      var _this22 = this;
	      var containerDimensions = this.container.getBoundingClientRect();
	      if (this.maxWidth !== null) {
	        if (!this.elements.root.style.maxWidth && animateUnsetProperty) {
	          this.elements.root.style.maxWidth = containerDimensions.width + 'px';
	        }
	        setTimeout(function () {
	          return _this22.elements.root.style.maxWidth = Math.max(_this22.maxWidth, MIN_WIDTH) + 'px';
	        }, 0);
	      } else {
	        this.elements.root.style.maxWidth = containerDimensions.width + 'px';
	        this.elements.root.addEventListener('transitionend', function () {
	          return _this22.elements.root.style.removeProperty('max-width');
	        }, {
	          once: true
	        });
	      }
	    }
	  }, {
	    key: "releaseLocalMedia",
	    value: function releaseLocalMedia() {
	      this.localUser.releaseStream();
	      if (this.centralUser.id == this.userId) {
	        this.centralUser.releaseStream();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.overflownButtonsPopup) {
	        this.overflownButtonsPopup.close();
	      }
	      if (this.elements.root) {
	        main_core.Dom.remove(this.elements.root);
	        this.elements.root = null;
	      }
	      this.visible = false;
	      window.removeEventListener("webkitfullscreenchange", this._onFullScreenChangeHandler);
	      window.removeEventListener("mozfullscreenchange", this._onFullScreenChangeHandler);
	      window.removeEventListener("orientationchange", this._onOrientationChangeHandler);
	      window.removeEventListener("keydown", this._onKeyDownHandler);
	      window.removeEventListener("keyup", this._onKeyUpHandler);
	      this.resizeObserver.disconnect();
	      this.resizeObserver = null;
	      if (this.intersectionObserver) {
	        this.intersectionObserver.disconnect();
	        this.intersectionObserver = null;
	      }
	      for (var userId in this.users) {
	        if (this.users.hasOwnProperty(userId)) {
	          this.users[userId].destroy();
	        }
	      }
	      this.userData = null;
	      this.centralUser.destroy();
	      this.hintManager.hide();
	      this.hintManager = null;
	      clearTimeout(this.switchPresenterTimeout);
	      if (this.buttons.recordStatus) {
	        this.buttons.recordStatus.stopViewUpdate();
	      }
	      this.recordState = this.getDefaultRecordState();
	      this.buttons = null;
	      this.eventEmitter.emit(EventName.onDestroy);
	      this.eventEmitter.unsubscribeAll();
	    }
	  }]);
	  return View;
	}();
	babelHelpers.defineProperty(View, "Layout", Layouts);
	babelHelpers.defineProperty(View, "Size", Size);
	babelHelpers.defineProperty(View, "RecordState", RecordState);
	babelHelpers.defineProperty(View, "RecordType", RecordType);
	babelHelpers.defineProperty(View, "RecordSource", {
	  Chat: 'BXCLIENT_CHAT'
	});
	babelHelpers.defineProperty(View, "UiState", UiState);
	babelHelpers.defineProperty(View, "Event", EventName);
	babelHelpers.defineProperty(View, "RoomState", RoomState);
	babelHelpers.defineProperty(View, "DeviceSelector", DeviceSelector);
	babelHelpers.defineProperty(View, "NotificationManager", NotificationManager);
	babelHelpers.defineProperty(View, "MIN_WIDTH", MIN_WIDTH);

	var VOLUME_THRESHOLD = 0.1;
	var INACTIVITY_TIME = 2000;
	var AVERAGING_COEFFICIENT = 0.5; // from 0 to 1;

	/**
	 * Naive voice activity detection
	 * @param {object} config
	 * @param {MediaStream} config.mediaStream
	 * @param {function} config.onVoiceStarted
	 * @param {function} config.onVoiceStopped
	 * @constructor
	 */
	var SimpleVAD = /*#__PURE__*/function () {
	  function SimpleVAD(config) {
	    babelHelpers.classCallCheck(this, SimpleVAD);
	    if (!(config.mediaStream instanceof MediaStream)) {
	      throw new Error("config.mediaStream should be instance of MediaStream");
	    }
	    if (config.mediaStream.getAudioTracks().length === 0) {
	      throw new Error("config.mediaStream should contain audio track");
	    }
	    this.mediaStream = new MediaStream();
	    this.mediaStream.addTrack(config.mediaStream.getAudioTracks()[0].clone());
	    this.audioContext = null;
	    this.mediaStreamNode = null;
	    this.analyserNode = null;
	    this.audioTimeDomainData = null;
	    this.voiceState = false;
	    this.measureInterval = 0;
	    this.inactivityTimeout = 0;
	    this.currentVolume = 0;
	    this.callbacks = {
	      voiceStarted: main_core.Type.isFunction(config.onVoiceStarted) ? config.onVoiceStarted : BX.DoNothing,
	      voiceStopped: main_core.Type.isFunction(config.onVoiceStopped) ? config.onVoiceStopped : BX.DoNothing
	    };
	    if (SimpleVAD.isSupported()) {
	      this.init();
	    }
	  }
	  babelHelpers.createClass(SimpleVAD, [{
	    key: "init",
	    value: function init() {
	      this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
	      this.analyserNode = this.audioContext.createAnalyser();
	      this.analyserNode.fftSize = 128;
	      this.mediaStreamNode = this.audioContext.createMediaStreamSource(this.mediaStream);
	      this.mediaStreamNode.connect(this.analyserNode);
	      this.audioTimeDomainData = new Float32Array(this.analyserNode.fftSize);
	      this.measureInterval = setInterval(this.analyzeAudioStream.bind(this), 100);
	    }
	  }, {
	    key: "analyzeAudioStream",
	    value: function analyzeAudioStream() {
	      this.analyserNode.getFloatTimeDomainData(this.audioTimeDomainData);
	      this.updateCurrentVolume(this.audioTimeDomainData);
	      this.setVoiceState(this.currentVolume >= VOLUME_THRESHOLD);
	    }
	  }, {
	    key: "setVoiceState",
	    value: function setVoiceState(voiceState) {
	      if (this.voiceState == voiceState) {
	        return;
	      }
	      if (voiceState) {
	        this.callbacks.voiceStarted();
	        clearTimeout(this.inactivityTimeout);
	        this.inactivityTimeout = 0;
	        this.voiceState = true;
	      } else {
	        if (!this.inactivityTimeout) {
	          this.inactivityTimeout = setTimeout(this.onInactivityTimeout.bind(this), INACTIVITY_TIME);
	        }
	      }
	    }
	  }, {
	    key: "onInactivityTimeout",
	    value: function onInactivityTimeout() {
	      this.inactivityTimeout = 0;
	      this.voiceState = false;
	      this.callbacks.voiceStopped();
	    }
	  }, {
	    key: "updateCurrentVolume",
	    value: function updateCurrentVolume(audioTimeDomainData) {
	      var sum = 0;
	      for (var i = 0; i < audioTimeDomainData.length; i++) {
	        sum += audioTimeDomainData[i] * audioTimeDomainData[i];
	      }
	      var rms = Math.sqrt(sum / audioTimeDomainData.length);
	      this.currentVolume = Math.max(rms, this.currentVolume * AVERAGING_COEFFICIENT);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.analyserNode) {
	        this.analyserNode.disconnect();
	      }
	      if (this.mediaStreamNode) {
	        this.mediaStreamNode.disconnect();
	      }
	      if (this.audioContext) {
	        this.audioContext.close();
	      }
	      if (this.mediaStream) {
	        this.mediaStream.getTracks().forEach(function (track) {
	          return track.stop();
	        });
	        this.mediaStream = null;
	      }
	      clearInterval(this.measureInterval);
	      this.analyserNode = null;
	      this.mediaStreamNode = null;
	      this.audioContext = null;
	      this.callbacks = {
	        voiceStarted: BX.DoNothing,
	        voiceStopped: BX.DoNothing
	      };
	    }
	  }], [{
	    key: "isSupported",
	    value: function isSupported() {
	      return (window.AudioContext || window.webkitAudioContext) && window.AnalyserNode && typeof window.AnalyserNode.prototype['getFloatTimeDomainData'] === "function";
	    }
	  }]);
	  return SimpleVAD;
	}();

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var ajaxActions = {
	  invite: 'im.call.invite',
	  cancel: 'im.call.cancel',
	  answer: 'im.call.answer',
	  decline: 'im.call.decline',
	  hangup: 'im.call.hangup',
	  ping: 'im.call.ping',
	  negotiationNeeded: 'im.call.negotiationNeeded',
	  connectionOffer: 'im.call.connectionOffer',
	  connectionAnswer: 'im.call.connectionAnswer',
	  iceCandidate: 'im.call.iceCandidate'
	};
	var pullEvents = {
	  ping: 'Call::ping',
	  answer: 'Call::answer',
	  negotiationNeeded: 'Call::negotiationNeeded',
	  connectionOffer: 'Call::connectionOffer',
	  connectionAnswer: 'Call::connectionAnswer',
	  iceCandidate: 'Call::iceCandidate',
	  voiceStarted: 'Call::voiceStarted',
	  voiceStopped: 'Call::voiceStopped',
	  recordState: 'Call::recordState',
	  microphoneState: 'Call::microphoneState',
	  cameraState: 'Call::cameraState',
	  videoPaused: 'Call::videoPaused',
	  customMessage: 'Call::customMessage',
	  hangup: 'Call::hangup',
	  userInviteTimeout: 'Call::userInviteTimeout'
	};
	var defaultConnectionOptions = {
	  offerToReceiveVideo: true,
	  offerToReceiveAudio: true
	};
	var signalingConnectionRefreshPeriod = 30000;
	var signalingWaitReplyPeriod = 10000;
	//var signalingWaitReplyPeriod = 5000;
	var pingPeriod = 5000;
	var backendPingPeriod = 25000;
	var reinvitePeriod = 5500;

	/**
	 * Implements Call interface
	 * Public methods:
	 * - inviteUsers
	 * - cancel
	 * - answer
	 * - decline
	 * - hangup
	 * - setMuted
	 * - setVideoEnabled
	 * - setCameraId
	 * - setMicrophoneId
	 *
	 * Events:
	 * - onCallStateChanged //not sure about this.
	 * - onUserStateChanged
	 * - onUserVoiceStarted
	 * - onUserVoiceStopped
	 * - onLocalMediaReceived
	 * - onLocalMediaStopped
	 * - onLocalMediaError
	 * - onDeviceListUpdated
	 * - onDestroy
	 */
	var _changeRecordState = /*#__PURE__*/new WeakSet();
	var _onPullEventUsersJoined = /*#__PURE__*/new WeakSet();
	var _onPullEventUsersInvited = /*#__PURE__*/new WeakSet();
	var _onPullEventUserInviteTimeout = /*#__PURE__*/new WeakSet();
	var _onPullEventAnswer = /*#__PURE__*/new WeakSet();
	var _onPullEventAnswerSelf = /*#__PURE__*/new WeakSet();
	var _onPullEventHangup = /*#__PURE__*/new WeakSet();
	var _onPullEventPing = /*#__PURE__*/new WeakSet();
	var _onPullEventNegotiationNeeded = /*#__PURE__*/new WeakSet();
	var _onPullEventConnectionOffer = /*#__PURE__*/new WeakSet();
	var _onPullEventConnectionAnswer = /*#__PURE__*/new WeakSet();
	var _onPullEventIceCandidate = /*#__PURE__*/new WeakSet();
	var _onPullEventVoiceStarted = /*#__PURE__*/new WeakSet();
	var _onPullEventVoiceStopped = /*#__PURE__*/new WeakSet();
	var _onPullEventMicrophoneState = /*#__PURE__*/new WeakSet();
	var _onPullEventCameraState = /*#__PURE__*/new WeakSet();
	var _onPullEventVideoPaused = /*#__PURE__*/new WeakSet();
	var _onPullEventRecordState = /*#__PURE__*/new WeakSet();
	var _onPullEventAssociatedEntityReplaced = /*#__PURE__*/new WeakSet();
	var _onPullEventFinish = /*#__PURE__*/new WeakSet();
	var _onPullEventRepeatAnswer = /*#__PURE__*/new WeakSet();
	var _onPullEventCallCustomMessage = /*#__PURE__*/new WeakSet();
	var _onPeerStateChanged = /*#__PURE__*/new WeakSet();
	var _onPeerInviteTimeout = /*#__PURE__*/new WeakSet();
	var _onPeerRTCStatsReceived = /*#__PURE__*/new WeakSet();
	var _onUnload = /*#__PURE__*/new WeakSet();
	var PlainCall = /*#__PURE__*/function (_AbstractCall) {
	  babelHelpers.inherits(PlainCall, _AbstractCall);
	  function PlainCall(_params) {
	    var _this;
	    babelHelpers.classCallCheck(this, PlainCall);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PlainCall).call(this, _params));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onUnload);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPeerRTCStatsReceived);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPeerInviteTimeout);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPeerStateChanged);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventCallCustomMessage);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventRepeatAnswer);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventFinish);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventAssociatedEntityReplaced);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventRecordState);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventVideoPaused);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventCameraState);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventMicrophoneState);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventVoiceStopped);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventVoiceStarted);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventIceCandidate);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventConnectionAnswer);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventConnectionOffer);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventNegotiationNeeded);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventPing);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventHangup);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventAnswerSelf);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventAnswer);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventUserInviteTimeout);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventUsersInvited);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventUsersJoined);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _changeRecordState);
	    _this.callFromMobile = _params.callFromMobile;
	    _this.state = _params.state || '';
	    _this.peers = _this.initPeers(_this.users);
	    _this.signaling = new Signaling({
	      call: babelHelpers.assertThisInitialized(_this)
	    });
	    _this.recordState = {
	      state: 'stopped',
	      userId: 0,
	      date: {
	        start: null,
	        pause: []
	      }
	    };
	    _this.deviceList = [];
	    _this.turnServer = (main_core.Browser.isFirefox() ? BX.message('turn_server_firefox') : BX.message('turn_server')) || 'turn.calls.bitrix24.com';
	    _this.turnServerLogin = BX.message('turn_server_login') || 'bitrix';
	    _this.turnServerPassword = BX.message('turn_server_password') || 'bitrix';
	    _this.pingUsersInterval = setInterval(_this.pingUsers.bind(babelHelpers.assertThisInitialized(_this)), pingPeriod);
	    _this.pingBackendInterval = setInterval(_this.pingBackend.bind(babelHelpers.assertThisInitialized(_this)), backendPingPeriod);
	    _this.reinviteTimeout = null;
	    _this._onUnloadHandler = _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _onUnload, _onUnload2).bind(babelHelpers.assertThisInitialized(_this));
	    _this.enableMicAutoParameters = _params.enableMicAutoParameters !== false;
	    _this.microphoneLevelInterval = null;
	    window.addEventListener("unload", _this._onUnloadHandler);
	    return _this;
	  }
	  babelHelpers.createClass(PlainCall, [{
	    key: "initPeers",
	    value: function initPeers(userIds) {
	      var peers = {};
	      for (var i = 0; i < userIds.length; i++) {
	        var userId = Number(userIds[i]);
	        if (userId == this.userId) {
	          continue;
	        }
	        peers[userId] = this.createPeer(userId);
	      }
	      return peers;
	    }
	  }, {
	    key: "createPeer",
	    value: function createPeer(userId) {
	      var _this2 = this;
	      return new Peer({
	        call: this,
	        userId: userId,
	        ready: userId == this.initiatorId,
	        signalingConnected: userId == this.initiatorId,
	        isLegacyMobile: userId == this.initiatorId && this.callFromMobile,
	        onMediaReceived: function onMediaReceived(e) {
	          console.log("onMediaReceived: ", e);
	          _this2.runCallback(CallEvent.onRemoteMediaReceived, e);
	        },
	        onMediaStopped: function onMediaStopped(e) {
	          _this2.runCallback(CallEvent.onRemoteMediaStopped, e);
	        },
	        onStateChanged: _classPrivateMethodGet(this, _onPeerStateChanged, _onPeerStateChanged2).bind(this),
	        onInviteTimeout: _classPrivateMethodGet(this, _onPeerInviteTimeout, _onPeerInviteTimeout2).bind(this),
	        onRTCStatsReceived: _classPrivateMethodGet(this, _onPeerRTCStatsReceived, _onPeerRTCStatsReceived2).bind(this),
	        onNetworkProblem: function onNetworkProblem(e) {
	          _this2.runCallback(CallEvent.onNetworkProblem, e);
	        }
	      });
	    }
	  }, {
	    key: "getUsers",
	    /**
	     * Returns call participants and their states
	     * @return {object} userId => user state
	     */
	    value: function getUsers() {
	      var result = {};
	      for (var userId in this.peers) {
	        result[userId] = this.peers[userId].calculatedState;
	      }
	      return result;
	    }
	  }, {
	    key: "isReady",
	    value: function isReady() {
	      return this.ready;
	    }
	  }, {
	    key: "setVideoEnabled",
	    value: function setVideoEnabled(videoEnabled) {
	      var _this3 = this;
	      videoEnabled = videoEnabled === true;
	      if (this.videoEnabled == videoEnabled) {
	        return;
	      }
	      this.videoEnabled = videoEnabled;
	      var hasVideoTracks = this.localStreams['main'] && this.localStreams['main'].getVideoTracks().length > 0;
	      if (this.ready && hasVideoTracks !== this.videoEnabled) {
	        this.replaceLocalMediaStream().then(function () {
	          var hasVideoTracks = _this3.localStreams['main'] && _this3.localStreams['main'].getVideoTracks().length > 0;
	          if (_this3.videoEnabled && !hasVideoTracks) {
	            _this3.videoEnabled = false;
	          }
	          _this3.signaling.sendCameraState(_this3.users, _this3.videoEnabled);
	        })["catch"](function () {
	          // TODO!!
	        });
	      }
	    }
	  }, {
	    key: "setMuted",
	    value: function setMuted(muted) {
	      muted = !!muted;
	      if (this.muted == muted) {
	        return;
	      }
	      this.muted = muted;
	      if (this.localStreams["main"]) {
	        var audioTracks = this.localStreams["main"].getAudioTracks();
	        if (audioTracks[0]) {
	          audioTracks[0].enabled = !this.muted;
	        }
	      }
	      this.signaling.sendMicrophoneState(this.users, !this.muted);
	      this.sendTalkingState();
	    }
	  }, {
	    key: "isMuted",
	    value: function isMuted() {
	      return this.muted;
	    }
	  }, {
	    key: "setCameraId",
	    value: function setCameraId(cameraId) {
	      if (this.cameraId == cameraId) {
	        return;
	      }
	      this.cameraId = cameraId;
	      if (this.ready && this.videoEnabled) {
	        main_core.Runtime.debounce(this.replaceLocalMediaStream, 100, this)();
	      }
	    }
	  }, {
	    key: "setMicrophoneId",
	    value: function setMicrophoneId(microphoneId) {
	      if (this.microphoneId == microphoneId) {
	        return;
	      }
	      this.microphoneId = microphoneId;
	      if (this.ready) {
	        main_core.Runtime.debounce(this.replaceLocalMediaStream, 100, this)();
	      }
	    }
	  }, {
	    key: "getCurrentMicrophoneId",
	    value: function getCurrentMicrophoneId() {
	      if (!this.localStreams['main']) {
	        return this.microphoneId;
	      }
	      var audioTracks = this.localStreams['main'].getAudioTracks();
	      if (audioTracks.length > 0) {
	        var audioTrackSettings = audioTracks[0].getSettings();
	        return audioTrackSettings.deviceId;
	      } else {
	        return this.microphoneId;
	      }
	    }
	  }, {
	    key: "useHdVideo",
	    value: function useHdVideo(flag) {
	      this.videoHd = flag === true;
	    }
	  }, {
	    key: "sendRecordState",
	    value: function sendRecordState(recordState) {
	      recordState.senderId = this.userId;
	      if (!_classPrivateMethodGet(this, _changeRecordState, _changeRecordState2).call(this, recordState)) {
	        return false;
	      }
	      var users = [this.userId].concat(this.users);
	      this.signaling.sendRecordState(users, this.recordState);
	    }
	  }, {
	    key: "stopSendingStream",
	    value: function stopSendingStream(tag) {
	      //todo: implement
	    }
	  }, {
	    key: "allowVideoFrom",
	    value: function allowVideoFrom(userList) {
	      //todo: implement
	    }
	  }, {
	    key: "inviteUsers",
	    /**
	     * Invites users to participate in the call.
	     **/
	    value: function inviteUsers() {
	      var _this4 = this;
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var users = main_core.Type.isArray(config.users) ? config.users : Object.keys(this.peers);
	      this.ready = true;
	      if (config.localStream instanceof MediaStream && !this.localStreams["main"]) {
	        this.localStreams["main"] = config.localStream;
	      }
	      this.getLocalMediaStream("main", true).then(function () {
	        return _this4.signaling.inviteUsers({
	          userIds: users,
	          video: _this4.videoEnabled ? 'Y' : 'N'
	        });
	      }).then(function () {
	        _this4.state = CallState.Connected;
	        _this4.runCallback(CallEvent.onJoin, {
	          local: true
	        });
	        for (var i = 0; i < users.length; i++) {
	          var userId = Number(users[i]);
	          if (!_this4.peers[userId]) {
	            _this4.peers[userId] = _this4.createPeer(userId);
	            _this4.runCallback(CallEvent.onUserInvited, {
	              userId: userId
	            });
	          }
	          _this4.peers[userId].onInvited();
	          _this4.scheduleRepeatInvite();
	        }
	      })["catch"](function (e) {
	        console.error(e);
	        _this4.runCallback(CallEvent.onCallFailure, e);
	      });
	    }
	  }, {
	    key: "scheduleRepeatInvite",
	    value: function scheduleRepeatInvite() {
	      clearTimeout(this.reinviteTimeout);
	      this.reinviteTimeout = setTimeout(this.repeatInviteUsers.bind(this), reinvitePeriod);
	    }
	  }, {
	    key: "repeatInviteUsers",
	    value: function repeatInviteUsers() {
	      var _this5 = this;
	      clearTimeout(this.reinviteTimeout);
	      if (!this.ready) {
	        return;
	      }
	      var usersToRepeatInvite = [];
	      for (var userId in this.peers) {
	        if (this.peers.hasOwnProperty(userId) && this.peers[userId].calculatedState === UserState.Calling) {
	          usersToRepeatInvite.push(userId);
	        }
	      }
	      if (usersToRepeatInvite.length === 0) {
	        return;
	      }
	      this.signaling.inviteUsers({
	        userIds: usersToRepeatInvite,
	        video: this.videoEnabled ? 'Y' : 'N',
	        isRepeated: 'Y'
	      }).then(function () {
	        return _this5.scheduleRepeatInvite();
	      });
	    }
	  }, {
	    key: "getMediaConstraints",
	    value: function getMediaConstraints() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var audio = {};
	      var video = options.videoEnabled ? {} : false;
	      var hdVideo = !!options.hdVideo;
	      var supportedConstraints = navigator.mediaDevices.getSupportedConstraints ? navigator.mediaDevices.getSupportedConstraints() : {};
	      if (this.microphoneId) {
	        audio.deviceId = {
	          ideal: this.microphoneId
	        };
	      }
	      if (!this.enableMicAutoParameters) {
	        if (supportedConstraints.echoCancellation) {
	          audio.echoCancellation = false;
	        }
	        if (supportedConstraints.noiseSuppression) {
	          audio.noiseSuppression = false;
	        }
	        if (supportedConstraints.autoGainControl) {
	          audio.autoGainControl = false;
	        }
	      }
	      if (video) {
	        //video.aspectRatio = 1.7777777778;
	        if (this.cameraId) {
	          video.deviceId = {
	            exact: this.cameraId
	          };
	        }
	        if (hdVideo) {
	          video.width = {
	            max: 1920,
	            min: 1280
	          };
	          video.height = {
	            max: 1080,
	            min: 720
	          };
	        } else {
	          video.width = {
	            ideal: 640
	          };
	          video.height = {
	            ideal: 360
	          };
	        }
	      }
	      return {
	        audio: audio,
	        video: video
	      };
	    }
	  }, {
	    key: "getUserMedia",
	    /**
	     * Recursively tries to get user media stream with array of constraints
	     *
	     * @param constraintsArray array of constraints objects
	     * @returns {Promise}
	     */
	    value: function getUserMedia(constraintsArray) {
	      return new Promise(function (resolve, reject) {
	        var currentConstraints = constraintsArray[0];
	        navigator.mediaDevices.getUserMedia(currentConstraints).then(function (stream) {
	          resolve(stream);
	        }, function (error) {
	          this.log("getUserMedia error: ", error);
	          this.log("Current constraints", currentConstraints);
	          if (constraintsArray.length > 1) {
	            this.getUserMedia(constraintsArray.slice(1)).then(function (stream) {
	              resolve(stream);
	            }, function (error) {
	              reject(error);
	            });
	          } else {
	            this.log("Last fallback constraints used, failing");
	            reject(error);
	          }
	        }.bind(this));
	      }.bind(this));
	    }
	  }, {
	    key: "getLocalMediaStream",
	    value: function getLocalMediaStream(tag, fallbackToAudio) {
	      var _this6 = this;
	      if (!main_core.Type.isStringFilled(tag)) {
	        tag = 'main';
	      }
	      if (this.localStreams[tag]) {
	        return Promise.resolve(this.localStreams[tag]);
	      }
	      this.log("Requesting access to media devices");
	      return new Promise(function (resolve, reject) {
	        var constraintsArray = [];
	        if (_this6.videoEnabled) {
	          if (_this6.videoHd) {
	            constraintsArray.push(_this6.getMediaConstraints({
	              videoEnabled: true,
	              hdVideo: true
	            }));
	          }
	          constraintsArray.push(_this6.getMediaConstraints({
	            videoEnabled: true,
	            hdVideo: false
	          }));
	          if (fallbackToAudio) {
	            constraintsArray.push(_this6.getMediaConstraints({
	              videoEnabled: false
	            }));
	          }
	        } else {
	          constraintsArray.push(_this6.getMediaConstraints({
	            videoEnabled: false
	          }));
	        }
	        _this6.getUserMedia(constraintsArray).then(function (stream) {
	          _this6.log("Local media stream received");
	          _this6.localStreams[tag] = stream;
	          _this6.runCallback(CallEvent.onLocalMediaReceived, {
	            tag: tag,
	            stream: stream
	          });
	          if (tag === 'main') {
	            _this6.attachVoiceDetection();
	            if (_this6.muted) {
	              var audioTracks = stream.getAudioTracks();
	              if (audioTracks[0]) {
	                audioTracks[0].enabled = false;
	              }
	            }
	          }
	          if (_this6.deviceList.length === 0) {
	            navigator.mediaDevices.enumerateDevices().then(function (deviceList) {
	              _this6.deviceList = deviceList;
	              _this6.runCallback(CallEvent.onDeviceListUpdated, {
	                deviceList: _this6.deviceList
	              });
	            });
	          }
	          resolve(_this6.localStreams[tag]);
	        })["catch"](function (e) {
	          _this6.log("Could not get local media stream.", e);
	          _this6.log("Request constraints: .", constraintsArray);
	          _this6.runCallback("onLocalMediaError", {
	            tag: tag,
	            error: e
	          });
	          reject(e);
	        });
	      });
	    }
	  }, {
	    key: "startMediaCapture",
	    value: function startMediaCapture() {
	      return this.getLocalMediaStream();
	    }
	  }, {
	    key: "attachVoiceDetection",
	    value: function attachVoiceDetection() {
	      if (this.voiceDetection) {
	        this.voiceDetection.destroy();
	      }
	      if (this.microphoneLevelInterval) {
	        clearInterval(this.microphoneLevelInterval);
	      }
	      try {
	        this.voiceDetection = new SimpleVAD({
	          mediaStream: this.localStreams['main'],
	          onVoiceStarted: this.onLocalVoiceStarted.bind(this),
	          onVoiceStopped: this.onLocalVoiceStopped.bind(this)
	        });
	        this.microphoneLevelInterval = setInterval(function () {
	          this.microphoneLevel = this.voiceDetection.currentVolume;
	        }.bind(this), 200);
	      } catch (e) {
	        this.log('Could not attach voice detection to media stream');
	      }
	    }
	  }, {
	    key: "getDisplayMedia",
	    value: function getDisplayMedia() {
	      return new Promise(function (resolve, reject) {
	        if (window["BXDesktopSystem"]) {
	          navigator.mediaDevices.getUserMedia({
	            video: {
	              mandatory: {
	                chromeMediaSource: 'screen',
	                maxWidth: 1920,
	                maxHeight: 1080,
	                maxFrameRate: 5
	              }
	            }
	          }).then(function (stream) {
	            resolve(stream);
	          }, function (error) {
	            reject(error);
	          });
	        } else if (navigator.mediaDevices.getDisplayMedia) {
	          navigator.mediaDevices.getDisplayMedia({
	            video: {
	              width: {
	                max: 1920
	              },
	              height: {
	                max: 1080
	              },
	              frameRate: {
	                max: 5
	              }
	            }
	          }).then(function (stream) {
	            resolve(stream);
	          }, function (error) {
	            reject(error);
	          });
	        } else {
	          console.error("Screen sharing is not supported");
	          reject("Screen sharing is not supported");
	        }
	      });
	    }
	  }, {
	    key: "startScreenSharing",
	    value: function startScreenSharing(changeSource) {
	      var _this7 = this;
	      changeSource = !!changeSource;
	      if (this.localStreams["screen"] && !changeSource) {
	        return;
	      }
	      this.getDisplayMedia().then(function (stream) {
	        _this7.localStreams["screen"] = stream;
	        stream.getVideoTracks().forEach(function (track) {
	          track.addEventListener("ended", function () {
	            return _this7.stopScreenSharing();
	          });
	        });
	        _this7.runCallback(CallEvent.onUserScreenState, {
	          userId: _this7.userId,
	          screenState: true
	        });
	        if (_this7.ready) {
	          for (var userId in _this7.peers) {
	            if (_this7.peers[userId].calculatedState === UserState.Connected) {
	              _this7.peers[userId].sendMedia();
	            }
	          }
	        }
	      })["catch"](function (e) {
	        _this7.log(e);
	      });
	    }
	  }, {
	    key: "stopScreenSharing",
	    value: function stopScreenSharing() {
	      if (!this.localStreams["screen"]) {
	        return;
	      }
	      Util$1.stopMediaStream(this.localStreams["screen"]);
	      this.localStreams["screen"] = null;
	      this.runCallback(CallEvent.onUserScreenState, {
	        userId: this.userId,
	        screenState: false
	      });
	      for (var userId in this.peers) {
	        if (this.peers[userId].calculatedState === UserState.Connected) {
	          this.peers[userId].sendMedia();
	        }
	      }
	    }
	  }, {
	    key: "isScreenSharingStarted",
	    value: function isScreenSharingStarted() {
	      return this.localStreams["screen"] instanceof MediaStream;
	    }
	  }, {
	    key: "onLocalVoiceStarted",
	    value: function onLocalVoiceStarted() {
	      this.talking = true;
	      this.sendTalkingState();
	    }
	  }, {
	    key: "onLocalVoiceStopped",
	    value: function onLocalVoiceStopped() {
	      this.talking = false;
	      this.sendTalkingState();
	    }
	  }, {
	    key: "sendTalkingState",
	    value: function sendTalkingState() {
	      if (this.talking && !this.muted) {
	        this.runCallback(CallEvent.onUserVoiceStarted, {
	          userId: this.userId,
	          local: true
	        });
	        this.signaling.sendVoiceStarted({
	          userId: this.users
	        });
	      } else {
	        this.runCallback(CallEvent.onUserVoiceStopped, {
	          userId: this.userId,
	          local: true
	        });
	        this.signaling.sendVoiceStopped({
	          userId: this.users
	        });
	      }
	    }
	  }, {
	    key: "sendCustomMessage",
	    value: function sendCustomMessage(message) {
	      this.signaling.sendCustomMessage({
	        userId: this.users,
	        message: message
	      });
	    }
	    /**
	     * @param {Object} config
	     * @param {bool} [config.useVideo]
	     * @param {bool} [config.enableMicAutoParameters]
	     * @param {MediaStream} [config.localStream]
	     */
	  }, {
	    key: "answer",
	    value: function answer(config) {
	      var _this8 = this;
	      if (!main_core.Type.isPlainObject(config)) {
	        config = {};
	      }
	      /*if(this.direction !== Direction.Incoming)
	      {
	      	throw new Error('Only incoming call could be answered');
	      }*/

	      this.ready = true;
	      this.videoEnabled = config.useVideo === true;
	      this.enableMicAutoParameters = config.enableMicAutoParameters !== false;
	      if (config.localStream instanceof MediaStream) {
	        this.localStreams["main"] = config.localStream;
	      }
	      return new Promise(function (resolve, reject) {
	        _this8.getLocalMediaStream("main", true).then(function () {
	          _this8.state = CallState.Connected;
	          _this8.runCallback(CallEvent.onJoin, {
	            local: true
	          });
	          return _this8.sendAnswer();
	        }).then(function () {
	          return resolve();
	        })["catch"](function (e) {
	          _this8.runCallback(CallEvent.onCallFailure, e);
	          reject(e);
	        });
	      });
	    }
	  }, {
	    key: "sendAnswer",
	    value: function sendAnswer() {
	      this.signaling.sendAnswer();
	    }
	  }, {
	    key: "decline",
	    value: function decline(code, reason) {
	      var _this9 = this;
	      this.ready = false;
	      var data = {
	        callId: this.id,
	        callInstanceId: this.instanceId
	      };
	      if (typeof code != 'undefined') {
	        data.code = code;
	      }
	      if (typeof reason != 'undefined') {
	        data.reason = reason;
	      }
	      CallEngine.getRestClient().callMethod(ajaxActions.decline, data).then(function () {
	        _this9.destroy();
	      });
	    }
	  }, {
	    key: "hangup",
	    value: function hangup() {
	      var _this10 = this;
	      if (!this.ready) {
	        var error = new Error("Hangup in wrong state");
	        this.log(error);
	        return Promise.reject(error);
	      }
	      var tempError = new Error();
	      tempError.name = "Call stack:";
	      this.log("Hangup received \n" + tempError.stack);
	      this.ready = false;
	      this.state = CallState.Proceeding;
	      return new Promise(function (resolve, reject) {
	        for (var userId in _this10.peers) {
	          _this10.peers[userId].disconnect();
	        }
	        _this10.runCallback(CallEvent.onLeave, {
	          local: true
	        });
	        _this10.signaling.sendHangup({
	          userId: _this10.users
	        }).then(function () {
	          return resolve();
	        })["catch"](function (e) {
	          return reject(e);
	        });
	      });
	    }
	  }, {
	    key: "pingUsers",
	    value: function pingUsers() {
	      if (this.ready) {
	        this.signaling.sendPingToUsers({
	          userId: this.users.concat(this.userId)
	        });
	      }
	    }
	  }, {
	    key: "pingBackend",
	    value: function pingBackend() {
	      if (this.ready) {
	        this.signaling.sendPingToBackend();
	      }
	    }
	  }, {
	    key: "getState",
	    value: function getState() {}
	  }, {
	    key: "replaceLocalMediaStream",
	    value: function replaceLocalMediaStream() {
	      var _this11 = this;
	      var tag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "main";
	      if (this.localStreams[tag]) {
	        Util$1.stopMediaStream(this.localStreams[tag]);
	        this.localStreams[tag] = null;
	      }
	      return new Promise(function (resolve, reject) {
	        _this11.getLocalMediaStream(tag).then(function () {
	          if (_this11.ready) {
	            for (var userId in _this11.peers) {
	              if (_this11.peers[userId].isReady()) {
	                _this11.peers[userId].replaceMediaStream(tag);
	              }
	            }
	          }
	          resolve();
	        })["catch"](function (e) {
	          console.error('Could not get access to hardware; don\'t really know what to do. error:', e);
	          reject(e);
	        });
	      });
	    }
	  }, {
	    key: "sendAllStreams",
	    value: function sendAllStreams(userId) {
	      if (!this.peers[userId]) {
	        return;
	      }
	      if (!this.peers[userId].isReady()) {
	        return;
	      }
	      for (var tag in this.localStreams) {
	        if (this.localStreams[tag]) {
	          this.peers[userId].sendMedia();
	        }
	      }
	    }
	  }, {
	    key: "isAnyoneParticipating",
	    value: function isAnyoneParticipating() {
	      for (var userId in this.peers) {
	        if (this.peers[userId].isParticipating()) {
	          return true;
	        }
	      }
	      return false;
	    }
	  }, {
	    key: "getParticipatingUsers",
	    value: function getParticipatingUsers() {
	      var result = [];
	      for (var userId in this.peers) {
	        if (this.peers[userId].isParticipating()) {
	          result.push(userId);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "addJoinedUsers",
	    /**
	     * Adds users, invited by you or someone else
	     * @param {Number[]} users
	     */
	    value: function addJoinedUsers(users) {
	      for (var i = 0; i < users.length; i++) {
	        var userId = Number(users[i]);
	        if (userId == this.userId || this.peers[userId]) {
	          continue;
	        }
	        this.peers[userId] = this.createPeer(userId);
	        if (!this.users.includes(userId)) {
	          this.users.push(userId);
	        }
	      }
	    }
	  }, {
	    key: "addInvitedUsers",
	    /**
	     * Adds users, invited by you or someone else
	     * @param {Number[]} users
	     */
	    value: function addInvitedUsers(users) {
	      for (var i = 0; i < users.length; i++) {
	        var userId = Number(users[i]);
	        if (userId == this.userId) {
	          continue;
	        }
	        if (this.peers[userId]) {
	          if (this.peers[userId].calculatedState === UserState.Failed || this.peers[userId].calculatedState === UserState.Idle) {
	            this.peers[userId].onInvited();
	          }
	        } else {
	          this.peers[userId] = this.createPeer(userId);
	          this.runCallback(CallEvent.onUserInvited, {
	            userId: userId
	          });
	          this.peers[userId].onInvited();
	        }
	        if (!this.users.includes(userId)) {
	          this.users.push(userId);
	        }
	      }
	    }
	  }, {
	    key: "__onPullEvent",
	    value: function __onPullEvent(command, params, extra) {
	      var handlers = {
	        'Call::answer': _classPrivateMethodGet(this, _onPullEventAnswer, _onPullEventAnswer2).bind(this),
	        'Call::hangup': _classPrivateMethodGet(this, _onPullEventHangup, _onPullEventHangup2).bind(this),
	        'Call::ping': _classPrivateMethodGet(this, _onPullEventPing, _onPullEventPing2).bind(this),
	        'Call::negotiationNeeded': _classPrivateMethodGet(this, _onPullEventNegotiationNeeded, _onPullEventNegotiationNeeded2).bind(this),
	        'Call::connectionOffer': _classPrivateMethodGet(this, _onPullEventConnectionOffer, _onPullEventConnectionOffer2).bind(this),
	        'Call::connectionAnswer': _classPrivateMethodGet(this, _onPullEventConnectionAnswer, _onPullEventConnectionAnswer2).bind(this),
	        'Call::iceCandidate': _classPrivateMethodGet(this, _onPullEventIceCandidate, _onPullEventIceCandidate2).bind(this),
	        'Call::voiceStarted': _classPrivateMethodGet(this, _onPullEventVoiceStarted, _onPullEventVoiceStarted2).bind(this),
	        'Call::voiceStopped': _classPrivateMethodGet(this, _onPullEventVoiceStopped, _onPullEventVoiceStopped2).bind(this),
	        'Call::microphoneState': _classPrivateMethodGet(this, _onPullEventMicrophoneState, _onPullEventMicrophoneState2).bind(this),
	        'Call::cameraState': _classPrivateMethodGet(this, _onPullEventCameraState, _onPullEventCameraState2).bind(this),
	        'Call::videoPaused': _classPrivateMethodGet(this, _onPullEventVideoPaused, _onPullEventVideoPaused2).bind(this),
	        'Call::recordState': _classPrivateMethodGet(this, _onPullEventRecordState, _onPullEventRecordState2).bind(this),
	        'Call::usersJoined': _classPrivateMethodGet(this, _onPullEventUsersJoined, _onPullEventUsersJoined2).bind(this),
	        'Call::usersInvited': _classPrivateMethodGet(this, _onPullEventUsersInvited, _onPullEventUsersInvited2).bind(this),
	        'Call::userInviteTimeout': _classPrivateMethodGet(this, _onPullEventUserInviteTimeout, _onPullEventUserInviteTimeout2).bind(this),
	        'Call::associatedEntityReplaced': _classPrivateMethodGet(this, _onPullEventAssociatedEntityReplaced, _onPullEventAssociatedEntityReplaced2).bind(this),
	        'Call::finish': _classPrivateMethodGet(this, _onPullEventFinish, _onPullEventFinish2).bind(this),
	        'Call::repeatAnswer': _classPrivateMethodGet(this, _onPullEventRepeatAnswer, _onPullEventRepeatAnswer2).bind(this),
	        'Call::customMessage': _classPrivateMethodGet(this, _onPullEventCallCustomMessage, _onPullEventCallCustomMessage2).bind(this)
	      };
	      if (handlers[command]) {
	        if (command === 'Call::ping') {
	          if (params.senderId != this.userId || params.instanceId != this.instanceId) {
	            this.log("Signaling: ping from user " + params.senderId);
	          }
	        } else {
	          this.log("Signaling: " + command + "; Parameters: " + JSON.stringify(params));
	        }
	        handlers[command].call(this, params);
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      var tempError = new Error();
	      tempError.name = "Call stack:";
	      this.log("Call destroy \n" + tempError.stack);

	      // stop sending media streams
	      for (var userId in this.peers) {
	        if (this.peers[userId]) {
	          this.peers[userId].destroy();
	        }
	      }
	      // stop media streams
	      for (var tag in this.localStreams) {
	        if (this.localStreams[tag]) {
	          Util$1.stopMediaStream(this.localStreams[tag]);
	          this.localStreams[tag] = null;
	        }
	      }
	      if (this.voiceDetection) {
	        this.voiceDetection.destroy();
	        this.voiceDetection = null;
	      }

	      // remove all event listeners
	      window.removeEventListener("unload", this._onUnloadHandler);
	      clearInterval(this.pingUsersInterval);
	      clearInterval(this.pingBackendInterval);
	      clearInterval(this.microphoneLevelInterval);
	      clearTimeout(this.reinviteTimeout);
	      return babelHelpers.get(babelHelpers.getPrototypeOf(PlainCall.prototype), "destroy", this).call(this);
	    }
	  }, {
	    key: "provider",
	    get: function get() {
	      return Provider.Plain;
	    }
	  }]);
	  return PlainCall;
	}(AbstractCall);
	function _changeRecordState2(params) {
	  if (params.action !== View.RecordState.Started && this.recordState.userId != params.senderId) {
	    return false;
	  }
	  if (params.action === View.RecordState.Started) {
	    if (this.recordState.state !== View.RecordState.Stopped) {
	      return false;
	    }
	    this.recordState.state = View.RecordState.Started;
	    this.recordState.userId = params.senderId;
	    this.recordState.date.start = params.date;
	    this.recordState.date.pause = [];
	  } else if (params.action === View.RecordState.Paused) {
	    if (this.recordState.state !== View.RecordState.Started) {
	      return false;
	    }
	    this.recordState.state = View.RecordState.Paused;
	    this.recordState.date.pause.push({
	      start: params.date,
	      finish: null
	    });
	  } else if (params.action === View.RecordState.Resumed) {
	    if (this.recordState.state !== View.RecordState.Paused) {
	      return false;
	    }
	    this.recordState.state = View.RecordState.Started;
	    var pauseElement = this.recordState.date.pause.find(function (element) {
	      return element.finish === null;
	    });
	    if (pauseElement) {
	      pauseElement.finish = params.date;
	    }
	  } else if (params.action === View.RecordState.Stopped) {
	    this.recordState.state = View.RecordState.Stopped;
	    this.recordState.userId = 0;
	    this.recordState.date.start = null;
	    this.recordState.date.pause = [];
	  }
	  return true;
	}
	function _onPullEventUsersJoined2(params) {
	  if (!this.ready) {
	    return;
	  }
	  var users = params.users;
	  this.addJoinedUsers(users);
	}
	function _onPullEventUsersInvited2(params) {
	  if (!this.ready) {
	    return;
	  }
	  var users = params.users;
	  this.addInvitedUsers(users);
	}
	function _onPullEventUserInviteTimeout2(params) {
	  this.log('__onPullEventUserInviteTimeout', params);
	  var failedUserId = params.failedUserId;
	  if (this.peers[failedUserId]) {
	    this.peers[failedUserId].onInviteTimeout(false);
	  }
	}
	function _onPullEventAnswer2(params) {
	  var senderId = Number(params.senderId);
	  if (senderId == this.userId) {
	    return _classPrivateMethodGet(this, _onPullEventAnswerSelf, _onPullEventAnswerSelf2).call(this, params);
	  }
	  if (!this.ready) {
	    return;
	  }
	  if (!this.peers[senderId]) {
	    return;
	  }
	  if (this.peers[senderId].isReady()) {
	    this.log("Received answer for user " + senderId + " in ready state, ignoring");
	    return;
	  }
	  this.peers[senderId].setSignalingConnected(true);
	  this.peers[senderId].setReady(true);
	  this.peers[senderId].isLegacyMobile = params.isLegacyMobile === true;
	  if (this.ready) {
	    this.sendAllStreams(senderId);
	  }
	}
	function _onPullEventAnswerSelf2(params) {
	  if (params.callInstanceId === this.instanceId) {
	    return;
	  }
	  if (this.ready) {
	    this.log("Received remote self-answer in ready state, ignoring");
	    return;
	  }

	  // call was answered elsewhere
	  this.log("Call was answered elsewhere");
	  this.runCallback(CallEvent.onJoin, {
	    local: false
	  });
	}
	function _onPullEventHangup2(params) {
	  var senderId = params.senderId;
	  if (this.userId == senderId) {
	    if (this.instanceId != params.callInstanceId) {
	      // self hangup elsewhere
	      this.runCallback(CallEvent.onLeave, {
	        local: false
	      });
	    }
	    return;
	  }
	  if (!this.peers[senderId]) {
	    return;
	  }
	  this.peers[senderId].disconnect(params.code);
	  this.peers[senderId].setReady(false);
	  if (params.code == 603) {
	    this.peers[senderId].setDeclined(true);
	  }
	  if (!this.isAnyoneParticipating()) {
	    this.hangup();
	  }
	}
	function _onPullEventPing2(params) {
	  if (params.callInstanceId == this.instanceId) {
	    // ignore self ping
	    return;
	  }
	  var peer = this.peers[params.senderId];
	  if (!peer) {
	    return;
	  }
	  peer.setSignalingConnected(true);
	}
	function _onPullEventNegotiationNeeded2(params) {
	  if (!this.ready) {
	    return;
	  }
	  var peer = this.peers[params.senderId];
	  if (!peer) {
	    return;
	  }
	  peer.setReady(true);
	  if (params.restart) {
	    peer.reconnect();
	  } else {
	    peer.onNegotiationNeeded();
	  }
	}
	function _onPullEventConnectionOffer2(params) {
	  if (!this.ready) {
	    return;
	  }
	  var peer = this.peers[params.senderId];
	  if (!peer) {
	    return;
	  }
	  peer.setReady(true);
	  peer.setUserAgent(params.userAgent);
	  peer.setConnectionOffer(params.connectionId, params.sdp, params.tracks);
	}
	function _onPullEventConnectionAnswer2(params) {
	  if (!this.ready) {
	    return;
	  }
	  var peer = this.peers[params.senderId];
	  if (!peer) {
	    return;
	  }
	  var connectionId = params.connectionId;
	  peer.setUserAgent(params.userAgent);
	  peer.setConnectionAnswer(connectionId, params.sdp, params.tracks);
	}
	function _onPullEventIceCandidate2(params) {
	  if (!this.ready) {
	    return;
	  }
	  var peer = this.peers[params.senderId];
	  var candidates;
	  if (!peer) {
	    return;
	  }
	  try {
	    candidates = params.candidates;
	    for (var i = 0; i < candidates.length; i++) {
	      peer.addIceCandidate(params.connectionId, candidates[i]);
	    }
	  } catch (e) {
	    this.log('Error parsing serialized candidate: ', e);
	  }
	}
	function _onPullEventVoiceStarted2(params) {
	  this.runCallback(CallEvent.onUserVoiceStarted, {
	    userId: params.senderId
	  });
	}
	function _onPullEventVoiceStopped2(params) {
	  this.runCallback(CallEvent.onUserVoiceStopped, {
	    userId: params.senderId
	  });
	}
	function _onPullEventMicrophoneState2(params) {
	  this.runCallback(CallEvent.onUserMicrophoneState, {
	    userId: params.senderId,
	    microphoneState: params.microphoneState
	  });
	}
	function _onPullEventCameraState2(params) {
	  this.runCallback(CallEvent.onUserCameraState, {
	    userId: params.senderId,
	    cameraState: params.cameraState
	  });
	}
	function _onPullEventVideoPaused2(params) {
	  var peer = this.peers[params.senderId];
	  if (!peer) {
	    return;
	  }
	  this.runCallback(CallEvent.onUserVideoPaused, {
	    userId: params.senderId,
	    videoPaused: params.videoPaused
	  });
	  peer.holdOutgoingVideo(!!params.videoPaused);
	}
	function _onPullEventRecordState2(params) {
	  this.runCallback(CallEvent.onUserRecordState, {
	    userId: params.senderId,
	    recordState: params.recordState
	  });
	}
	function _onPullEventAssociatedEntityReplaced2(params) {
	  if (params.call && params.call.ASSOCIATED_ENTITY) {
	    this.associatedEntity = params.call.ASSOCIATED_ENTITY;
	  }
	}
	function _onPullEventFinish2() {
	  this.destroy();
	}
	function _onPullEventRepeatAnswer2() {
	  if (this.ready) {
	    this.signaling.sendAnswer({
	      userId: this.userId
	    }, true);
	  }
	}
	function _onPullEventCallCustomMessage2(params) {
	  this.runCallback(CallEvent.onCustomMessage, {
	    message: params.message
	  });
	}
	function _onPeerStateChanged2(e) {
	  var _this19 = this;
	  this.runCallback(CallEvent.onUserStateChanged, e);
	  if (e.state == UserState.Failed || e.state == UserState.Unavailable) {
	    if (!this.isAnyoneParticipating()) {
	      this.hangup().then(this.destroy.bind(this))["catch"](function () {
	        //this.runCallback(Event.onCallFailure, e);
	        _this19.destroy();
	      });
	    }
	  } else if (e.state == UserState.Connected) {
	    this.signaling.sendMicrophoneState(e.userId, !this.muted);
	    this.signaling.sendCameraState(e.userId, this.videoEnabled);
	    this.wasConnected = true;
	  }
	}
	function _onPeerInviteTimeout2(e) {
	  if (!this.ready) {
	    return;
	  }
	  this.signaling.sendUserInviteTimeout({
	    userId: this.users,
	    failedUserId: e.userId
	  });
	}
	function _onPeerRTCStatsReceived2(e) {
	  this.runCallback(CallEvent.onRTCStatsReceived, e);
	}
	function _onUnload2() {
	  if (!this.ready) {
	    return;
	  }
	  CallEngine.getRestClient().callMethod(ajaxActions.hangup, {
	    callId: this.id,
	    callInstanceId: this.instanceId
	  });
	  for (var userId in this.peers) {
	    this.peers[userId].disconnect();
	  }
	}
	var _sendPullEvent = /*#__PURE__*/new WeakSet();
	var _runRestAction = /*#__PURE__*/new WeakSet();
	var Signaling = /*#__PURE__*/function () {
	  function Signaling(params) {
	    babelHelpers.classCallCheck(this, Signaling);
	    _classPrivateMethodInitSpec(this, _runRestAction);
	    _classPrivateMethodInitSpec(this, _sendPullEvent);
	    this.call = params.call;
	  }
	  babelHelpers.createClass(Signaling, [{
	    key: "isIceTricklingAllowed",
	    value: function isIceTricklingAllowed() {
	      return CallEngine.getPullClient().isPublishingSupported();
	    }
	  }, {
	    key: "inviteUsers",
	    value: function inviteUsers(data) {
	      return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.invite, data);
	    }
	  }, {
	    key: "sendAnswer",
	    value: function sendAnswer(data, repeated) {
	      if (repeated && CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.answer, data);
	      } else {
	        return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.answer, data);
	      }
	    }
	  }, {
	    key: "sendConnectionOffer",
	    value: function sendConnectionOffer(data) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.connectionOffer, data);
	      } else {
	        return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.connectionOffer, data);
	      }
	    }
	  }, {
	    key: "sendConnectionAnswer",
	    value: function sendConnectionAnswer(data) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.connectionAnswer, data);
	      } else {
	        return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.connectionAnswer, data);
	      }
	    }
	  }, {
	    key: "sendIceCandidate",
	    value: function sendIceCandidate(data) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.iceCandidate, data);
	      } else {
	        return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.iceCandidate, data);
	      }
	    }
	  }, {
	    key: "sendNegotiationNeeded",
	    value: function sendNegotiationNeeded(data) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.negotiationNeeded, data);
	      } else {
	        return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.negotiationNeeded, data);
	      }
	    }
	  }, {
	    key: "sendVoiceStarted",
	    value: function sendVoiceStarted(data) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.voiceStarted, data);
	      }
	    }
	  }, {
	    key: "sendVoiceStopped",
	    value: function sendVoiceStopped(data) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.voiceStopped, data);
	      }
	    }
	  }, {
	    key: "sendMicrophoneState",
	    value: function sendMicrophoneState(users, microphoneState) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.microphoneState, {
	          userId: users,
	          microphoneState: microphoneState
	        }, 0);
	      }
	    }
	  }, {
	    key: "sendCameraState",
	    value: function sendCameraState(users, cameraState) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.cameraState, {
	          userId: users,
	          cameraState: cameraState
	        }, 0);
	      }
	    }
	  }, {
	    key: "sendRecordState",
	    value: function sendRecordState(users, recordState) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        return _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.recordState, {
	          userId: users,
	          recordState: recordState
	        }, 0);
	      }
	    }
	  }, {
	    key: "sendPingToUsers",
	    value: function sendPingToUsers(data) {
	      if (CallEngine.getPullClient().isPublishingEnabled()) {
	        _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.ping, data, 5);
	      }
	    }
	  }, {
	    key: "sendCustomMessage",
	    value: function sendCustomMessage(data) {
	      if (CallEngine.getPullClient().isPublishingEnabled()) {
	        _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.customMessage, data, 5);
	      }
	    }
	  }, {
	    key: "sendPingToBackend",
	    value: function sendPingToBackend() {
	      var retransmit = !CallEngine.getPullClient().isPublishingEnabled();
	      _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.ping, {
	        retransmit: retransmit
	      });
	    }
	  }, {
	    key: "sendUserInviteTimeout",
	    value: function sendUserInviteTimeout(data) {
	      if (CallEngine.getPullClient().isPublishingEnabled()) {
	        _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.userInviteTimeout, data, 0);
	      }
	    }
	  }, {
	    key: "sendHangup",
	    value: function sendHangup(data) {
	      if (CallEngine.getPullClient().isPublishingSupported()) {
	        _classPrivateMethodGet(this, _sendPullEvent, _sendPullEvent2).call(this, pullEvents.hangup, data, 3600);
	        data.retransmit = false;
	        return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.hangup, data);
	      } else {
	        data.retransmit = true;
	        return _classPrivateMethodGet(this, _runRestAction, _runRestAction2).call(this, ajaxActions.hangup, data);
	      }
	    }
	  }]);
	  return Signaling;
	}();
	function _sendPullEvent2(eventName, data, expiry) {
	  expiry = expiry || 5;
	  if (!data.userId) {
	    throw new Error('userId is not found in data');
	  }
	  if (!main_core.Type.isArray(data.userId)) {
	    data.userId = [data.userId];
	  }
	  data.callInstanceId = this.call.instanceId;
	  data.senderId = this.call.userId;
	  data.callId = this.call.id;
	  data.requestId = Util$1.getUuidv4();
	  if (eventName == 'Call::ping') {
	    this.call.log('Sending p2p signaling event ' + eventName);
	  } else {
	    this.call.log('Sending p2p signaling event ' + eventName + '; ' + JSON.stringify(data));
	  }
	  CallEngine.getPullClient().sendMessage(data.userId, 'im', eventName, data, expiry);
	}
	function _runRestAction2(signalName, data) {
	  if (!main_core.Type.isPlainObject(data)) {
	    data = {};
	  }
	  data.callId = this.call.id;
	  data.callInstanceId = this.call.instanceId;
	  data.requestId = Util$1.getUuidv4();
	  if (signalName == 'Call::ping') {
	    this.call.log('Sending ajax-based signaling event ' + signalName);
	  } else {
	    this.call.log('Sending ajax-based signaling event ' + signalName + '; ' + JSON.stringify(data));
	  }
	  return CallEngine.getRestClient().callMethod(signalName, data)["catch"](function (e) {
	    console.error(e);
	  });
	}
	var _createPeerConnection = /*#__PURE__*/new WeakSet();
	var _onPeerConnectionIceConnectionStateChange = /*#__PURE__*/new WeakSet();
	var _onPeerConnectionIceGatheringStateChange = /*#__PURE__*/new WeakSet();
	var _onPeerConnectionSignalingStateChange = /*#__PURE__*/new WeakSet();
	var _onPeerConnectionNegotiationNeeded = /*#__PURE__*/new WeakSet();
	var _onPeerConnectionTrack = /*#__PURE__*/new WeakSet();
	var _onPeerConnectionRemoveStream = /*#__PURE__*/new WeakSet();
	var _onVideoTrackMuted = /*#__PURE__*/new WeakSet();
	var _onVideoTrackUnMuted = /*#__PURE__*/new WeakSet();
	var _onVideoTrackEnded = /*#__PURE__*/new WeakSet();
	var _updateTracks = /*#__PURE__*/new WeakSet();
	var _onLostSignalingConnection = /*#__PURE__*/new WeakSet();
	var Peer = /*#__PURE__*/function () {
	  function Peer(params) {
	    babelHelpers.classCallCheck(this, Peer);
	    _classPrivateMethodInitSpec(this, _onLostSignalingConnection);
	    _classPrivateMethodInitSpec(this, _updateTracks);
	    _classPrivateMethodInitSpec(this, _onVideoTrackEnded);
	    _classPrivateMethodInitSpec(this, _onVideoTrackUnMuted);
	    _classPrivateMethodInitSpec(this, _onVideoTrackMuted);
	    _classPrivateMethodInitSpec(this, _onPeerConnectionRemoveStream);
	    _classPrivateMethodInitSpec(this, _onPeerConnectionTrack);
	    _classPrivateMethodInitSpec(this, _onPeerConnectionNegotiationNeeded);
	    _classPrivateMethodInitSpec(this, _onPeerConnectionSignalingStateChange);
	    _classPrivateMethodInitSpec(this, _onPeerConnectionIceGatheringStateChange);
	    _classPrivateMethodInitSpec(this, _onPeerConnectionIceConnectionStateChange);
	    _classPrivateMethodInitSpec(this, _createPeerConnection);
	    this.call = params.call;
	    this.userId = params.userId;
	    this.ready = params.ready === true;
	    this.calling = false;
	    this.inviteTimeout = false;
	    this.declined = false;
	    this.busy = false;
	    this.signalingConnected = params.signalingConnected === true;
	    this.failureReason = '';
	    this.userAgent = '';
	    this.isFirefox = false;
	    this.isChrome = false;
	    this.isLegacyMobile = params.isLegacyMobile === true;

	    /*sums up from signaling, ready and connection states*/
	    this.calculatedState = this.calculateState();
	    this.localStreams = {
	      main: null,
	      screen: null
	    };
	    this.pendingIceCandidates = {};
	    this.localIceCandidates = [];
	    this.trackList = {};
	    this.callbacks = {
	      onStateChanged: main_core.Type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
	      onInviteTimeout: main_core.Type.isFunction(params.onInviteTimeout) ? params.onInviteTimeout : BX.DoNothing,
	      onMediaReceived: main_core.Type.isFunction(params.onMediaReceived) ? params.onMediaReceived : BX.DoNothing,
	      onMediaStopped: main_core.Type.isFunction(params.onMediaStopped) ? params.onMediaStopped : BX.DoNothing,
	      onRTCStatsReceived: main_core.Type.isFunction(params.onRTCStatsReceived) ? params.onRTCStatsReceived : BX.DoNothing,
	      onNetworkProblem: main_core.Type.isFunction(params.onNetworkProblem) ? params.onNetworkProblem : BX.DoNothing
	    };

	    // intervals and timeouts
	    this.answerTimeout = null;
	    this.callingTimeout = null;
	    this.connectionTimeout = null;
	    this.signalingConnectionTimeout = null;
	    this.candidatesTimeout = null;
	    this.statsInterval = null;
	    this.connectionOfferReplyTimeout = null;
	    this.negotiationNeededReplyTimeout = null;
	    this.reconnectAfterDisconnectTimeout = null;
	    this.connectionAttempt = 0;
	    this.hasStun = false;
	    this.hasTurn = false;
	    this._outgoingVideoTrack = null;
	    Object.defineProperty(this, 'outgoingVideoTrack', {
	      get: function get() {
	        return this._outgoingVideoTrack;
	      },
	      set: function set(track) {
	        if (this._outgoingVideoTrack) {
	          this._outgoingVideoTrack.stop();
	        }
	        this._outgoingVideoTrack = track;
	        if (this._outgoingVideoTrack) {
	          this._outgoingVideoTrack.enabled = !this.outgoingVideoHoldState;
	        }
	      }
	    });
	    this._outgoingScreenTrack = null;
	    Object.defineProperty(this, 'outgoingScreenTrack', {
	      get: function get() {
	        return this._outgoingScreenTrack;
	      },
	      set: function set(track) {
	        if (this._outgoingScreenTrack) {
	          this._outgoingScreenTrack.stop();
	        }
	        this._outgoingScreenTrack = track;
	        if (this._outgoingScreenTrack) {
	          this._outgoingScreenTrack.enabled = !this.outgoingVideoHoldState;
	        }
	      }
	    });
	    this._incomingAudioTrack = null;
	    this._incomingVideoTrack = null;
	    this._incomingScreenTrack = null;
	    Object.defineProperty(this, 'incomingAudioTrack', {
	      get: this._mediaGetter('_incomingAudioTrack'),
	      set: this._mediaSetter('_incomingAudioTrack', 'audio')
	    });
	    Object.defineProperty(this, 'incomingVideoTrack', {
	      get: this._mediaGetter('_incomingVideoTrack'),
	      set: this._mediaSetter('_incomingVideoTrack', 'video')
	    });
	    Object.defineProperty(this, 'incomingScreenTrack', {
	      get: this._mediaGetter('_incomingScreenTrack'),
	      set: this._mediaSetter('_incomingScreenTrack', 'screen')
	    });
	    this.outgoingVideoHoldState = false;

	    // event handlers
	    this._onPeerConnectionIceCandidateHandler = this._onPeerConnectionIceCandidate.bind(this);
	    this._onPeerConnectionIceConnectionStateChangeHandler = _classPrivateMethodGet(this, _onPeerConnectionIceConnectionStateChange, _onPeerConnectionIceConnectionStateChange2).bind(this);
	    this._onPeerConnectionIceGatheringStateChangeHandler = _classPrivateMethodGet(this, _onPeerConnectionIceGatheringStateChange, _onPeerConnectionIceGatheringStateChange2).bind(this);
	    this._onPeerConnectionSignalingStateChangeHandler = _classPrivateMethodGet(this, _onPeerConnectionSignalingStateChange, _onPeerConnectionSignalingStateChange2).bind(this);
	    //this._onPeerConnectionNegotiationNeededHandler = this._onPeerConnectionNegotiationNeeded.bind(this);
	    this._onPeerConnectionTrackHandler = _classPrivateMethodGet(this, _onPeerConnectionTrack, _onPeerConnectionTrack2).bind(this);
	    this._onPeerConnectionRemoveStreamHandler = _classPrivateMethodGet(this, _onPeerConnectionRemoveStream, _onPeerConnectionRemoveStream2).bind(this);
	    this._updateTracksDebounced = main_core.Runtime.debounce(_classPrivateMethodGet(this, _updateTracks, _updateTracks2).bind(this), 50);
	    this._waitTurnCandidatesTimeout = null;
	  }
	  babelHelpers.createClass(Peer, [{
	    key: "_mediaGetter",
	    value: function _mediaGetter(trackVariable) {
	      return function () {
	        return this[trackVariable];
	      }.bind(this);
	    }
	  }, {
	    key: "_mediaSetter",
	    value: function _mediaSetter(trackVariable, kind) {
	      return function (track) {
	        if (this[trackVariable] != track) {
	          this[trackVariable] = track;
	          if (track) {
	            this.callbacks.onMediaReceived({
	              userId: this.userId,
	              kind: kind,
	              track: track
	            });
	          } else {
	            this.callbacks.onMediaStopped({
	              userId: this.userId,
	              kind: kind
	            });
	          }
	        }
	      }.bind(this);
	    }
	  }, {
	    key: "sendMedia",
	    value: function sendMedia(skipOffer) {
	      if (!this.peerConnection) {
	        if (!this.isInitiator()) {
	          this.log('waiting for the other side to send connection offer');
	          this.sendNegotiationNeeded(false);
	          return;
	        }
	      }
	      if (!this.peerConnection) {
	        var connectionId = Util$1.getUuidv4();
	        _classPrivateMethodGet(this, _createPeerConnection, _createPeerConnection2).call(this, connectionId);
	      }
	      this.updateOutgoingTracks();
	      this.applyResolutionScale();
	      if (!skipOffer) {
	        this.createAndSendOffer();
	      }
	    }
	  }, {
	    key: "updateOutgoingTracks",
	    value: function updateOutgoingTracks() {
	      if (!this.peerConnection) {
	        return;
	      }
	      var audioTrack;
	      var videoTrack;
	      var screenTrack;
	      if (this.call.localStreams["main"] && this.call.localStreams["main"].getAudioTracks().length > 0) {
	        audioTrack = this.call.localStreams["main"].getAudioTracks()[0];
	      }
	      if (this.call.localStreams["screen"] && this.call.localStreams["screen"].getVideoTracks().length > 0) {
	        screenTrack = this.call.localStreams["screen"].getVideoTracks()[0];
	      }
	      if (this.call.localStreams["main"] && this.call.localStreams["main"].getVideoTracks().length > 0) {
	        videoTrack = this.call.localStreams["main"].getVideoTracks()[0];
	      }
	      this.outgoingVideoTrack = videoTrack ? videoTrack.clone() : null;
	      this.outgoingScreenTrack = screenTrack ? screenTrack.clone() : null;
	      var tracksToSend = [];
	      if (audioTrack) {
	        tracksToSend.push(audioTrack.id + ' (audio)');
	      }
	      if (videoTrack) {
	        tracksToSend.push(videoTrack.id + ' (' + videoTrack.kind + ')');
	      }
	      if (screenTrack) {
	        tracksToSend.push(screenTrack.id + ' (' + screenTrack.kind + ')');
	      }
	      console.log("User: " + this.userId + '; Sending media streams. Tracks: ' + tracksToSend.join('; '));

	      // if video sender found - replace track
	      // if not found - add track
	      if (this.videoSender && this.outgoingVideoTrack) {
	        this.videoSender.replaceTrack(this.outgoingVideoTrack);
	      }
	      if (!this.videoSender && this.outgoingVideoTrack) {
	        this.videoSender = this.peerConnection.addTrack(this.outgoingVideoTrack);
	      }
	      if (this.videoSender && !this.outgoingVideoTrack) {
	        this.peerConnection.removeTrack(this.videoSender);
	        this.videoSender = null;
	      }

	      // if screen sender found - replace track
	      // if not found - add track
	      if (this.screenSender && this.outgoingScreenTrack) {
	        this.screenSender.replaceTrack(this.outgoingScreenTrack);
	      }
	      if (!this.screenSender && this.outgoingScreenTrack) {
	        this.screenSender = this.peerConnection.addTrack(this.outgoingScreenTrack);
	      }
	      if (this.screenSender && !this.outgoingScreenTrack) {
	        this.peerConnection.removeTrack(this.screenSender);
	        this.screenSender = null;
	      }

	      // if audio sender found - replace track
	      // if not found - add track
	      if (this.audioSender && audioTrack) {
	        this.audioSender.replaceTrack(audioTrack);
	      }
	      if (!this.audioSender && audioTrack) {
	        this.audioSender = this.peerConnection.addTrack(audioTrack);
	      }
	      if (this.audioSender && !audioTrack) {
	        this.peerConnection.removeTrack(this.audioSender);
	        this.audioSender = null;
	      }
	    }
	  }, {
	    key: "getSenderMid",
	    value: function getSenderMid(rtpSender) {
	      if (rtpSender === null || !this.peerConnection) {
	        return null;
	      }
	      var transceiver = this.peerConnection.getTransceivers().find(function (transceiver) {
	        return transceiver.sender == rtpSender;
	      });
	      return transceiver ? transceiver.mid : null;
	    }
	  }, {
	    key: "applyResolutionScale",
	    value: function applyResolutionScale(factor) {
	      if (!this.videoSender) {
	        return;
	      }
	      var scaleFactor = factor || (this.screenSender ? 4 : 1);
	      var params = this.videoSender.getParameters();
	      if (params.encodings && params.encodings.length > 0) {
	        params.encodings[0].scaleResolutionDownBy = scaleFactor;
	        //params.encodings[0].maxBitrate = rate;
	        this.videoSender.setParameters(params);
	      }
	    }
	  }, {
	    key: "replaceMediaStream",
	    value: function replaceMediaStream(tag) {
	      if (this.isRenegotiationSupported()) {
	        this.sendMedia();
	      } else {
	        this.localStreams[tag] = this.call.getLocalStream(tag);
	        this.reconnect();
	      }
	    }
	  }, {
	    key: "holdOutgoingVideo",
	    value: function holdOutgoingVideo(holdState) {
	      if (this.outgoingVideoHoldState == holdState) {
	        return;
	      }
	      this.outgoingVideoHoldState = holdState;
	      if (this._outgoingVideoTrack) {
	        this._outgoingVideoTrack.enabled = !this.outgoingVideoHoldState;
	      }
	    }
	  }, {
	    key: "isInitiator",
	    value: function isInitiator() {
	      return this.call.userId < this.userId;
	    }
	  }, {
	    key: "isRenegotiationSupported",
	    value: function isRenegotiationSupported() {
	      return true;
	      // return (Browser.isChrome() && this.isChrome);
	    }
	  }, {
	    key: "setReady",
	    value: function setReady(ready) {
	      this.ready = ready;
	      if (this.ready) {
	        this.declined = false;
	        this.busy = false;
	      }
	      if (this.calling) {
	        clearTimeout(this.callingTimeout);
	        this.calling = false;
	      }
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "isReady",
	    value: function isReady() {
	      return this.ready;
	    }
	  }, {
	    key: "onInvited",
	    value: function onInvited() {
	      this.ready = false;
	      this.inviteTimeout = false;
	      this.declined = false;
	      this.calling = true;
	      if (this.callingTimeout) {
	        clearTimeout(this.callingTimeout);
	      }
	      this.callingTimeout = setTimeout(function () {
	        this.onInviteTimeout(true);
	      }.bind(this), 30000);
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "onInviteTimeout",
	    value: function onInviteTimeout(internal) {
	      clearTimeout(this.callingTimeout);
	      this.calling = false;
	      this.inviteTimeout = true;
	      if (internal) {
	        this.callbacks.onInviteTimeout({
	          userId: this.userId
	        });
	      }
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "setUserAgent",
	    value: function setUserAgent(userAgent) {
	      this.userAgent = userAgent;
	      this.isFirefox = userAgent.toLowerCase().indexOf('firefox') != -1;
	      this.isChrome = userAgent.toLowerCase().indexOf('chrome') != -1;
	      this.isLegacyMobile = userAgent === 'Bitrix Legacy Mobile';
	    }
	  }, {
	    key: "getUserAgent",
	    value: function getUserAgent() {
	      return this.userAgent;
	    }
	  }, {
	    key: "isParticipating",
	    value: function isParticipating() {
	      if (this.calling) {
	        return true;
	      }
	      if (this.declined || this.busy) {
	        return false;
	      }
	      if (this.peerConnection) {
	        // todo: maybe we should check iceConnectionState as well.
	        var iceConnectionState = this.peerConnection.iceConnectionState;
	        if (iceConnectionState == 'checking' || iceConnectionState == 'connected' || iceConnectionState == 'completed') {
	          return true;
	        }
	      }
	      return false;
	    }
	  }, {
	    key: "setSignalingConnected",
	    value: function setSignalingConnected(signalingConnected) {
	      this.signalingConnected = signalingConnected;
	      this.updateCalculatedState();
	      if (this.signalingConnected) {
	        this.refreshSignalingTimeout();
	      } else {
	        this.stopSignalingTimeout();
	      }
	    }
	  }, {
	    key: "isSignalingConnected",
	    value: function isSignalingConnected() {
	      return this.signalingConnected;
	    }
	  }, {
	    key: "setDeclined",
	    value: function setDeclined(declined) {
	      this.declined = declined;
	      if (this.calling) {
	        clearTimeout(this.callingTimeout);
	        this.calling = false;
	      }
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "setBusy",
	    value: function setBusy(busy) {
	      this.busy = busy;
	      if (this.calling) {
	        clearTimeout(this.callingTimeout);
	        this.calling = false;
	      }
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "isDeclined",
	    value: function isDeclined() {
	      return this.declined;
	    }
	  }, {
	    key: "updateCalculatedState",
	    value: function updateCalculatedState() {
	      var calculatedState = this.calculateState();
	      if (this.calculatedState != calculatedState) {
	        this.callbacks.onStateChanged({
	          userId: this.userId,
	          state: calculatedState,
	          previousState: this.calculatedState,
	          isLegacyMobile: this.isLegacyMobile,
	          networkProblem: !this.hasStun || !this.hasTurn
	        });
	        this.calculatedState = calculatedState;
	      }
	    }
	  }, {
	    key: "calculateState",
	    value: function calculateState() {
	      if (this.peerConnection) {
	        if (this.failureReason !== '') {
	          return UserState.Failed;
	        }
	        if (this.peerConnection.iceConnectionState === 'connected' || this.peerConnection.iceConnectionState === 'completed') {
	          return UserState.Connected;
	        }
	        return UserState.Connecting;
	      }
	      if (this.calling) {
	        return UserState.Calling;
	      }
	      if (this.inviteTimeout) {
	        return UserState.Unavailable;
	      }
	      if (this.declined) {
	        return UserState.Declined;
	      }
	      if (this.busy) {
	        return UserState.Busy;
	      }
	      if (this.ready) {
	        return UserState.Ready;
	      }
	      return UserState.Idle;
	    }
	  }, {
	    key: "getSignaling",
	    value: function getSignaling() {
	      return this.call.signaling;
	    }
	  }, {
	    key: "startStatisticsGathering",
	    value: function startStatisticsGathering() {
	      clearInterval(this.statsInterval);
	      this.statsInterval = setInterval(function () {
	        if (!this.peerConnection) {
	          return false;
	        }
	        this.peerConnection.getStats().then(function (stats) {
	          this.callbacks.onRTCStatsReceived({
	            userId: this.userId,
	            stats: stats
	          });
	        }.bind(this));
	      }.bind(this), 1000);
	    }
	  }, {
	    key: "stopStatisticsGathering",
	    value: function stopStatisticsGathering() {
	      clearInterval(this.statsInterval);
	      this.statsInterval = null;
	    }
	  }, {
	    key: "updateCandidatesTimeout",
	    value: function updateCandidatesTimeout() {
	      if (this.candidatesTimeout) {
	        clearTimeout(this.candidatesTimeout);
	      }
	      this.candidatesTimeout = setTimeout(this.sendIceCandidates.bind(this), 500);
	    }
	  }, {
	    key: "sendIceCandidates",
	    value: function sendIceCandidates() {
	      this.log("User " + this.userId + ": sending ICE candidates due to the timeout");
	      this.candidatesTimeout = null;
	      if (this.localIceCandidates.length > 0) {
	        this.getSignaling().sendIceCandidate({
	          userId: this.userId,
	          connectionId: this.peerConnectionId,
	          candidates: this.localIceCandidates
	        });
	        this.localIceCandidates = [];
	      } else {
	        this.log("User " + this.userId + ": ICE candidates pool is empty");
	      }
	    }
	  }, {
	    key: "_destroyPeerConnection",
	    value: function _destroyPeerConnection() {
	      if (!this.peerConnection) {
	        return;
	      }
	      this.log("User " + this.userId + ": Destroying peer connection " + this.peerConnectionId);
	      this.stopStatisticsGathering();
	      this.peerConnection.removeEventListener("icecandidate", this._onPeerConnectionIceCandidateHandler);
	      this.peerConnection.removeEventListener("iceconnectionstatechange", this._onPeerConnectionIceConnectionStateChangeHandler);
	      this.peerConnection.removeEventListener("icegatheringstatechange", this._onPeerConnectionIceGatheringStateChangeHandler);
	      this.peerConnection.removeEventListener("signalingstatechange", this._onPeerConnectionSignalingStateChangeHandler);
	      // this.peerConnection.removeEventListener("negotiationneeded", this._onPeerConnectionNegotiationNeededHandler);
	      this.peerConnection.removeEventListener("track", this._onPeerConnectionTrackHandler);
	      this.peerConnection.removeEventListener("removestream", this._onPeerConnectionRemoveStreamHandler);
	      this.localIceCandidates = [];
	      if (this.pendingIceCandidates[this.peerConnectionId]) {
	        delete this.pendingIceCandidates[this.peerConnectionId];
	      }
	      this.peerConnection.close();
	      this.peerConnection = null;
	      this.peerConnectionId = null;
	      this.videoSender = null;
	      this.audioSender = null;
	      this.incomingAudioTrack = null;
	      this.incomingVideoTrack = null;
	      this.incomingScreenTrack = null;
	    }
	  }, {
	    key: "_onPeerConnectionIceCandidate",
	    value: function _onPeerConnectionIceCandidate(e) {
	      var candidate = e.candidate;
	      this.log("User " + this.userId + ": ICE candidate discovered. Candidate: " + (candidate ? candidate.candidate : candidate));
	      if (candidate) {
	        if (this.getSignaling().isIceTricklingAllowed()) {
	          this.getSignaling().sendIceCandidate({
	            userId: this.userId,
	            connectionId: this.peerConnectionId,
	            candidates: [candidate.toJSON()]
	          });
	        } else {
	          this.localIceCandidates.push(candidate.toJSON());
	          this.updateCandidatesTimeout();
	        }
	        var match = candidate.candidate.match(/typ\s(\w+)?/);
	        if (match) {
	          var type = match[1];
	          if (type == "srflx") {
	            this.hasStun = true;
	          } else if (type == "relay") {
	            this.hasTurn = true;
	          }
	        }
	      }
	    }
	  }, {
	    key: "stopSignalingTimeout",
	    value: function stopSignalingTimeout() {
	      clearTimeout(this.signalingConnectionTimeout);
	    }
	  }, {
	    key: "refreshSignalingTimeout",
	    value: function refreshSignalingTimeout() {
	      clearTimeout(this.signalingConnectionTimeout);
	      this.signalingConnectionTimeout = setTimeout(_classPrivateMethodGet(this, _onLostSignalingConnection, _onLostSignalingConnection2).bind(this), signalingConnectionRefreshPeriod);
	    }
	  }, {
	    key: "_onConnectionOfferReplyTimeout",
	    value: function _onConnectionOfferReplyTimeout(connectionId) {
	      this.log("did not receive connection answer for connection " + connectionId);
	      this.reconnect();
	    }
	  }, {
	    key: "_onNegotiationNeededReplyTimeout",
	    value: function _onNegotiationNeededReplyTimeout() {
	      this.log("did not receive connection offer in time");
	      this.reconnect();
	    }
	  }, {
	    key: "setConnectionOffer",
	    value: function setConnectionOffer(connectionId, sdp, trackList) {
	      this.log("User " + this.userId + ": applying connection offer for connection " + connectionId);
	      clearTimeout(this.negotiationNeededReplyTimeout);
	      this.negotiationNeededReplyTimeout = null;
	      if (!this.call.isReady()) {
	        return;
	      }
	      if (!this.isReady()) {
	        return;
	      }
	      if (trackList) {
	        this.trackList = BX.util.array_flip(trackList);
	      }
	      if (this.peerConnection) {
	        if (this.peerConnectionId !== connectionId) {
	          this._destroyPeerConnection();
	          _classPrivateMethodGet(this, _createPeerConnection, _createPeerConnection2).call(this, connectionId);
	        }
	      } else {
	        _classPrivateMethodGet(this, _createPeerConnection, _createPeerConnection2).call(this, connectionId);
	      }
	      this.applyOfferAndSendAnswer(sdp);
	    }
	  }, {
	    key: "createAndSendOffer",
	    value: function createAndSendOffer(config) {
	      var _this12 = this;
	      var connectionConfig = defaultConnectionOptions;
	      for (var _key in config) {
	        connectionConfig[_key] = config[_key];
	      }
	      this.peerConnection.createOffer(connectionConfig).then(function (offer) {
	        _this12.log("User " + _this12.userId + ": Created connection offer.");
	        _this12.log("Applying local description");
	        return _this12.peerConnection.setLocalDescription(offer);
	      }).then(function () {
	        _this12.sendOffer();
	      });
	    }
	  }, {
	    key: "sendOffer",
	    value: function sendOffer() {
	      var _this13 = this;
	      clearTimeout(this.connectionOfferReplyTimeout);
	      this.connectionOfferReplyTimeout = setTimeout(function () {
	        return _this13._onConnectionOfferReplyTimeout(_this13.peerConnectionId);
	      }, signalingWaitReplyPeriod);
	      this.getSignaling().sendConnectionOffer({
	        userId: this.userId,
	        connectionId: this.peerConnectionId,
	        sdp: this.peerConnection.localDescription.sdp,
	        tracks: {
	          audio: this.getSenderMid(this.audioSender),
	          video: this.getSenderMid(this.videoSender),
	          screen: this.getSenderMid(this.screenSender)
	        },
	        userAgent: navigator.userAgent
	      });
	    }
	  }, {
	    key: "sendNegotiationNeeded",
	    value: function sendNegotiationNeeded(restart) {
	      var _this14 = this;
	      restart = restart === true;
	      clearTimeout(this.negotiationNeededReplyTimeout);
	      this.negotiationNeededReplyTimeout = setTimeout(function () {
	        return _this14._onNegotiationNeededReplyTimeout();
	      }, signalingWaitReplyPeriod);
	      var params = {
	        userId: this.userId
	      };
	      if (restart) {
	        params.restart = true;
	      }
	      this.getSignaling().sendNegotiationNeeded(params);
	    }
	  }, {
	    key: "applyOfferAndSendAnswer",
	    value: function applyOfferAndSendAnswer(sdp) {
	      var _this15 = this;
	      var sessionDescription = new RTCSessionDescription({
	        type: "offer",
	        sdp: sdp
	      });
	      this.log("User: " + this.userId + "; Applying remote offer");
	      this.log("User: " + this.userId + "; Peer ice connection state ", this.peerConnection.iceConnectionState);
	      this.peerConnection.setRemoteDescription(sessionDescription).then(function () {
	        if (_this15.peerConnection.iceConnectionState === 'new') {
	          _this15.sendMedia(true);
	        }
	        return _this15.peerConnection.createAnswer();
	      }).then(function (answer) {
	        _this15.log("Created connection answer.");
	        _this15.log("Applying local description.");
	        return _this15.peerConnection.setLocalDescription(answer);
	      }).then(function () {
	        _this15.applyPendingIceCandidates();
	        _this15.getSignaling().sendConnectionAnswer({
	          userId: _this15.userId,
	          connectionId: _this15.peerConnectionId,
	          sdp: _this15.peerConnection.localDescription.sdp,
	          tracks: {
	            audio: _this15.getSenderMid(_this15.audioSender),
	            video: _this15.getSenderMid(_this15.videoSender),
	            screen: _this15.getSenderMid(_this15.screenSender)
	          },
	          userAgent: navigator.userAgent
	        });
	      })["catch"](function (e) {
	        _this15.failureReason = e.toString();
	        _this15.updateCalculatedState();
	        _this15.log("Could not apply remote offer", e);
	        console.error("Could not apply remote offer", e);
	      });
	    }
	  }, {
	    key: "setConnectionAnswer",
	    value: function setConnectionAnswer(connectionId, sdp, trackList) {
	      var _this16 = this;
	      if (!this.peerConnection || this.peerConnectionId != connectionId) {
	        this.log("Could not apply answer, for unknown connection " + connectionId);
	        return;
	      }
	      if (this.peerConnection.signalingState !== 'have-local-offer') {
	        this.log("Could not apply answer, wrong peer connection signaling state " + this.peerConnection.signalingState);
	        return;
	      }
	      if (trackList) {
	        this.trackList = BX.util.array_flip(trackList);
	      }
	      var sessionDescription = new RTCSessionDescription({
	        type: "answer",
	        sdp: sdp
	      });
	      clearTimeout(this.connectionOfferReplyTimeout);
	      this.log("User: " + this.userId + "; Applying remote answer");
	      this.peerConnection.setRemoteDescription(sessionDescription).then(function () {
	        _this16.applyPendingIceCandidates();
	      })["catch"](function (e) {
	        _this16.failureReason = e.toString();
	        _this16.updateCalculatedState();
	        _this16.log(e);
	      });
	    }
	  }, {
	    key: "addIceCandidate",
	    value: function addIceCandidate(connectionId, candidate) {
	      var _this17 = this;
	      if (!this.peerConnection) {
	        return;
	      }
	      if (this.peerConnectionId != connectionId) {
	        this.log("Error: Candidate for unknown connection " + connectionId);
	        return;
	      }
	      if (this.peerConnection.remoteDescription && this.peerConnection.remoteDescription.type) {
	        this.peerConnection.addIceCandidate(candidate).then(function () {
	          _this17.log("User: " + _this17.userId + "; Added remote ICE candidate: " + (candidate ? candidate.candidate : candidate));
	        })["catch"](function (e) {
	          _this17.log(e);
	        });
	      } else {
	        if (!this.pendingIceCandidates[connectionId]) {
	          this.pendingIceCandidates[connectionId] = [];
	        }
	        this.pendingIceCandidates[connectionId].push(candidate);
	      }
	    }
	  }, {
	    key: "applyPendingIceCandidates",
	    value: function applyPendingIceCandidates() {
	      var _this18 = this;
	      if (!this.peerConnection || !this.peerConnection.remoteDescription.type) {
	        return;
	      }
	      if (main_core.Type.isArray(this.pendingIceCandidates[this.peerConnectionId])) {
	        this.pendingIceCandidates[this.peerConnectionId].forEach(function (candidate) {
	          _this18.peerConnection.addIceCandidate(candidate).then(function () {
	            _this18.log("User: " + _this18.userId + "; Added remote ICE candidate: " + (candidate ? candidate.candidate : candidate));
	          });
	        });
	        this.pendingIceCandidates[this.peerConnectionId] = [];
	      }
	    }
	  }, {
	    key: "onNegotiationNeeded",
	    value: function onNegotiationNeeded() {
	      if (this.peerConnection) {
	        if (this.peerConnection.signalingState == "have-local-offer") {
	          this.sendOffer();
	        } else {
	          this.createAndSendOffer({
	            iceRestart: true
	          });
	        }
	      } else {
	        this.sendMedia();
	      }
	    }
	  }, {
	    key: "reconnect",
	    value: function reconnect() {
	      clearTimeout(this.reconnectAfterDisconnectTimeout);
	      this.connectionAttempt++;
	      if (this.connectionAttempt > 3) {
	        this.log("Error: Too many reconnection attempts, giving up");
	        this.failureReason = "Could not connect to user in time";
	        this.updateCalculatedState();
	        return;
	      }
	      this.log("Trying to restore ICE connection. Attempt " + this.connectionAttempt);
	      if (this.isInitiator()) {
	        this._destroyPeerConnection();
	        this.sendMedia();
	      } else {
	        this.sendNegotiationNeeded(true);
	      }
	    }
	  }, {
	    key: "disconnect",
	    value: function disconnect() {
	      this._destroyPeerConnection();
	    }
	  }, {
	    key: "log",
	    value: function log() {
	      this.call.log.apply(this.call, arguments);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.disconnect();
	      if (this.voiceDetection) {
	        this.voiceDetection.destroy();
	        this.voiceDetection = null;
	      }
	      for (var tag in this.localStreams) {
	        this.localStreams[tag] = null;
	      }
	      this.outgoingVideoTrack = null;
	      this.outgoingScreenTrack = null;
	      this.outgoingVideoHoldState = false;
	      this.incomingAudioTrack = null;
	      this.incomingVideoTrack = null;
	      this.incomingScreenTrack = null;
	      clearTimeout(this.answerTimeout);
	      this.answerTimeout = null;
	      clearTimeout(this.connectionTimeout);
	      this.connectionTimeout = null;
	      clearTimeout(this.signalingConnectionTimeout);
	      this.signalingConnectionTimeout = null;
	      this.callbacks.onStateChanged = BX.DoNothing;
	      this.callbacks.onMediaReceived = BX.DoNothing;
	      this.callbacks.onMediaStopped = BX.DoNothing;
	    }
	  }]);
	  return Peer;
	}();
	function _createPeerConnection2(id) {
	  this.log("User " + this.userId + ": Creating peer connection");
	  var connectionConfig = {
	    "iceServers": [{
	      urls: "stun:" + this.call.turnServer
	    }, {
	      urls: "turn:" + this.call.turnServer,
	      username: this.call.turnServerLogin,
	      credential: this.call.turnServerPassword
	    }]
	    // iceTransportPolicy: 'relay'
	  };

	  this.localIceCandidates = [];
	  this.peerConnection = new RTCPeerConnection(connectionConfig);
	  this.peerConnectionId = id;
	  this.peerConnection.addEventListener("icecandidate", this._onPeerConnectionIceCandidateHandler);
	  this.peerConnection.addEventListener("iceconnectionstatechange", this._onPeerConnectionIceConnectionStateChangeHandler);
	  this.peerConnection.addEventListener("icegatheringstatechange", this._onPeerConnectionIceGatheringStateChangeHandler);
	  this.peerConnection.addEventListener("signalingstatechange", this._onPeerConnectionSignalingStateChangeHandler);
	  // this.peerConnection.addEventListener("negotiationneeded", this._onPeerConnectionNegotiationNeededHandler);
	  this.peerConnection.addEventListener("track", this._onPeerConnectionTrackHandler);
	  this.peerConnection.addEventListener("removestream", this._onPeerConnectionRemoveStreamHandler);
	  this.failureReason = '';
	  this.hasStun = false;
	  this.hasTurn = false;
	  this.updateCalculatedState();
	  this.startStatisticsGathering();
	}
	function _onPeerConnectionIceConnectionStateChange2() {
	  var _this20 = this;
	  this.log("User " + this.userId + ": ICE connection state changed. New state: " + this.peerConnection.iceConnectionState);
	  if (this.peerConnection.iceConnectionState === "connected" || this.peerConnection.iceConnectionState === "completed") {
	    this.connectionAttempt = 0;
	    clearTimeout(this.reconnectAfterDisconnectTimeout);
	    this._updateTracksDebounced();
	  } else if (this.peerConnection.iceConnectionState === "failed") {
	    this.log("ICE connection failed. Trying to restore connection immediately");
	    this.reconnect();
	  } else if (this.peerConnection.iceConnectionState === "disconnected") {
	    this.log("ICE connection lost. Waiting 5 seconds before trying to restore it");
	    clearTimeout(this.reconnectAfterDisconnectTimeout);
	    this.reconnectAfterDisconnectTimeout = setTimeout(function () {
	      return _this20.reconnect();
	    }, 5000);
	  }
	  this.updateCalculatedState();
	}
	function _onPeerConnectionIceGatheringStateChange2(e) {
	  var connection = e.target;
	  this.log("User " + this.userId + ": ICE gathering state changed to : " + connection.iceGatheringState);
	  if (connection.iceGatheringState === 'complete') {
	    this.log("User " + this.userId + ": ICE gathering complete");
	    if (!this.hasStun || !this.hasTurn) {
	      var s = [];
	      if (!this.hasTurn) {
	        s.push("TURN");
	      }
	      if (!this.hasStun) {
	        s.push("STUN");
	      }
	      this.log("Connectivity problem detected: no ICE candidates from " + s.join(" and ") + " servers");
	      console.error("Connectivity problem detected: no ICE candidates from " + s.join(" and ") + " servers");
	      this.callbacks.onNetworkProblem();
	    }
	    if (!this.hasTurn && !this.hasStun) ;
	    if (!this.getSignaling().isIceTricklingAllowed()) {
	      if (this.localIceCandidates.length > 0) {
	        this.getSignaling().sendIceCandidate({
	          userId: this.userId,
	          connectionId: this.peerConnectionId,
	          candidates: this.localIceCandidates
	        });
	        this.localIceCandidates = [];
	      } else {
	        this.log("User " + this.userId + ": ICE candidates already sent");
	      }
	    }
	  }
	}
	function _onPeerConnectionSignalingStateChange2() {
	  this.log("User " + this.userId + " PC signalingState: " + this.peerConnection.signalingState);
	  if (this.peerConnection.signalingState === "stable") {
	    this._updateTracksDebounced();
	  }
	}
	function _onPeerConnectionTrack2(e) {
	  this.log("User " + this.userId + ": media track received: ", e.track.id + " (" + e.track.kind + ")");
	  if (e.track.kind === "video") {
	    e.track.addEventListener("mute", _classPrivateMethodGet(this, _onVideoTrackMuted, _onVideoTrackMuted2).bind(this));
	    e.track.addEventListener("unmute", _classPrivateMethodGet(this, _onVideoTrackUnMuted, _onVideoTrackUnMuted2).bind(this));
	    e.track.addEventListener("ended", _classPrivateMethodGet(this, _onVideoTrackEnded, _onVideoTrackEnded2).bind(this));
	    if (this.trackList[e.track.id] === 'screen') {
	      this.incomingScreenTrack = e.track;
	    } else {
	      this.incomingVideoTrack = e.track;
	    }
	  } else if (e.track.kind === 'audio') {
	    this.incomingAudioTrack = e.track;
	  }
	}
	function _onPeerConnectionRemoveStream2(e) {
	  this.log("User: " + this.userId + "_onPeerConnectionRemoveStream: ", e);
	}
	function _onVideoTrackMuted2() {
	  console.log("Video track muted");
	  //this._updateTracksDebounced();
	}
	function _onVideoTrackUnMuted2() {
	  console.log("Video track unmuted");
	  //this._updateTracksDebounced();
	}
	function _onVideoTrackEnded2() {
	  console.log("Video track ended");
	}
	function _updateTracks2() {
	  var _this21 = this;
	  if (!this.peerConnection) {
	    return null;
	  }
	  var audioTrack = null;
	  var videoTrack = null;
	  var screenTrack = null;
	  this.peerConnection.getTransceivers().forEach(function (tr) {
	    _this21.call.log("[debug] tr direction: " + tr.direction + " currentDirection: " + tr.currentDirection);
	    if (tr.currentDirection === "sendrecv" || tr.currentDirection === "recvonly") {
	      if (tr.receiver && tr.receiver.track) {
	        var track = tr.receiver.track;
	        if (track.kind === 'audio') {
	          audioTrack = track;
	        } else if (track.kind === 'video') {
	          if (_this21.trackList[tr.mid] === 'screen') {
	            screenTrack = track;
	          } else {
	            videoTrack = track;
	          }
	        }
	      }
	    }
	  });
	  this.incomingAudioTrack = audioTrack;
	  this.incomingVideoTrack = videoTrack;
	  this.incomingScreenTrack = screenTrack;
	}
	function _onLostSignalingConnection2() {
	  this.setSignalingConnected(false);
	}

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Implements Call interface
	 * Public methods:
	 * - inviteUsers
	 * - cancel
	 * - answer
	 * - decline
	 * - hangup
	 *
	 * Events:
	 * - onCallStateChanged //not sure about this.
	 * - onUserStateChanged
	 * - onStreamReceived
	 * - onStreamRemoved
	 * - onDestroy
	 */

	var ajaxActions$1 = {
	  invite: 'im.call.invite',
	  cancel: 'im.call.cancel',
	  answer: 'im.call.answer',
	  decline: 'im.call.decline',
	  hangup: 'im.call.hangup',
	  ping: 'im.call.ping'
	};
	var pullEvents$1 = {
	  ping: 'Call::ping',
	  answer: 'Call::answer',
	  hangup: 'Call::hangup',
	  userInviteTimeout: 'Call::userInviteTimeout',
	  repeatAnswer: 'Call::repeatAnswer'
	};
	var clientEvents = {
	  voiceStarted: 'Call::voiceStarted',
	  voiceStopped: 'Call::voiceStopped',
	  microphoneState: 'Call::microphoneState',
	  cameraState: 'Call::cameraState',
	  videoPaused: 'Call::videoPaused',
	  screenState: 'Call::screenState',
	  recordState: 'Call::recordState',
	  floorRequest: 'Call::floorRequest',
	  emotion: 'Call::emotion',
	  customMessage: 'Call::customMessage',
	  showUsers: 'Call::showUsers',
	  showAll: 'Call::showAll',
	  hideAll: 'Call::hideAll',
	  joinRoom: 'Call::joinRoom',
	  leaveRoom: 'Call::leaveRoom',
	  listRooms: 'Call::listRooms',
	  requestRoomSpeaker: 'Call::requestRoomSpeaker'
	};
	var scenarioEvents = {
	  viewerJoined: 'Call::viewerJoined',
	  viewerLeft: 'Call::viewerLeft',
	  joinRoomOffer: 'Call::joinRoomOffer',
	  transferRoomHost: 'Call::transferRoomHost',
	  listRoomsResponse: 'Call::listRoomsResponse',
	  roomUpdated: 'Call::roomUpdated'
	};
	var VoximplantCallEvent = {
	  onCallConference: 'VoximplantCall::onCallConference'
	};
	var pingPeriod$1 = 5000;
	var backendPingPeriod$1 = 25000;
	var reinvitePeriod$1 = 5500;
	var connectionRestoreTime = 15000;

	// const MAX_USERS_WITHOUT_SIMULCAST = 6;
	var _showLocalVideo = /*#__PURE__*/new WeakSet();
	var _hideLocalVideo = /*#__PURE__*/new WeakSet();
	var _onCallConnected = /*#__PURE__*/new WeakSet();
	var _onCallFailed = /*#__PURE__*/new WeakSet();
	var _onPeerStateChanged$1 = /*#__PURE__*/new WeakMap();
	var _onPeerInviteTimeout$1 = /*#__PURE__*/new WeakMap();
	var _onPullEventAnswer$1 = /*#__PURE__*/new WeakMap();
	var _onPullEventHangup$1 = /*#__PURE__*/new WeakMap();
	var _onPullEventUsersJoined$1 = /*#__PURE__*/new WeakMap();
	var _onPullEventUsersInvited$1 = /*#__PURE__*/new WeakMap();
	var _onPullEventUserInviteTimeout$1 = /*#__PURE__*/new WeakMap();
	var _onPullEventPing$1 = /*#__PURE__*/new WeakMap();
	var _onNoPingsReceived = /*#__PURE__*/new WeakMap();
	var _onNoSelfPingsReceived = /*#__PURE__*/new WeakMap();
	var _onPullEventFinish$1 = /*#__PURE__*/new WeakMap();
	var _onPullEventRepeatAnswer$1 = /*#__PURE__*/new WeakMap();
	var _onLocalMediaRendererAdded = /*#__PURE__*/new WeakMap();
	var _onBeforeLocalMediaRendererRemoved = /*#__PURE__*/new WeakMap();
	var _onMicAccessResult = /*#__PURE__*/new WeakMap();
	var _onCallReconnecting = /*#__PURE__*/new WeakMap();
	var _onCallReconnected = /*#__PURE__*/new WeakMap();
	var _onClientReconnecting = /*#__PURE__*/new WeakMap();
	var _onClientReconnected = /*#__PURE__*/new WeakMap();
	var _onCallDisconnected = /*#__PURE__*/new WeakMap();
	var _onWindowUnload = /*#__PURE__*/new WeakMap();
	var _onFatalError = /*#__PURE__*/new WeakMap();
	var _setEndpointForUser = /*#__PURE__*/new WeakSet();
	var _onCallEndpointAdded = /*#__PURE__*/new WeakMap();
	var _onCallStatsReceived = /*#__PURE__*/new WeakMap();
	var _onJoinRoomOffer = /*#__PURE__*/new WeakMap();
	var _onRoomUpdated = /*#__PURE__*/new WeakMap();
	var _onCallMessageReceived = /*#__PURE__*/new WeakMap();
	var VoximplantCall = /*#__PURE__*/function (_AbstractCall) {
	  babelHelpers.inherits(VoximplantCall, _AbstractCall);
	  function VoximplantCall(config) {
	    var _this;
	    babelHelpers.classCallCheck(this, VoximplantCall);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VoximplantCall).call(this, config));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _setEndpointForUser);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onCallFailed);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onCallConnected);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _hideLocalVideo);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _showLocalVideo);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPeerStateChanged$1, {
	      writable: true,
	      value: function value(e) {
	        _this.runCallback(CallEvent.onUserStateChanged, e);
	        if (!_this.ready) {
	          return;
	        }
	        if (e.state == UserState.Failed || e.state == UserState.Unavailable || e.state == UserState.Declined || e.state == UserState.Idle) {
	          if (_this.type == CallType.Instant && !_this.isAnyoneParticipating()) {
	            _this.hangup();
	          }
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPeerInviteTimeout$1, {
	      writable: true,
	      value: function value(e) {
	        if (!_this.ready) {
	          return;
	        }
	        _this.signaling.sendUserInviteTimeout({
	          userId: _this.users,
	          failedUserId: e.userId
	        });
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventAnswer$1, {
	      writable: true,
	      value: function value(params) {
	        var senderId = Number(params.senderId);
	        if (senderId == _this.userId) {
	          return _this.__onPullEventAnswerSelf(params);
	        }
	        if (!_this.peers[senderId]) {
	          _this.peers[senderId] = _this.createPeer(senderId);
	          _this.runCallback(CallEvent.onUserInvited, {
	            userId: senderId
	          });
	        }
	        if (!_this.users.includes(senderId)) {
	          _this.users.push(senderId);
	        }
	        _this.peers[senderId].setReady(true);
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventHangup$1, {
	      writable: true,
	      value: function value(params) {
	        var senderId = params.senderId;
	        if (_this.userId == senderId && _this.instanceId != params.callInstanceId) {
	          // Call declined by the same user elsewhere
	          _this.runCallback(CallEvent.onLeave, {
	            local: false
	          });
	          return;
	        }
	        if (!_this.peers[senderId]) {
	          return;
	        }
	        _this.peers[senderId].setReady(false);
	        if (params.code == 603) {
	          _this.peers[senderId].setDeclined(true);
	        } else if (params.code == 486) {
	          _this.peers[senderId].setBusy(true);
	          console.error("user " + senderId + " is busy");
	        }
	        if (_this.ready && _this.type == CallType.Instant && !_this.isAnyoneParticipating()) {
	          _this.hangup();
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventUsersJoined$1, {
	      writable: true,
	      value: function value(params) {
	        _this.log('__onPullEventUsersJoined', params);
	        var users = params.users;
	        _this.addJoinedUsers(users);
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventUsersInvited$1, {
	      writable: true,
	      value: function value(params) {
	        _this.log('__onPullEventUsersInvited', params);
	        var users = params.users;
	        if (_this.type === CallType.Instant) {
	          _this.addInvitedUsers(users);
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventUserInviteTimeout$1, {
	      writable: true,
	      value: function value(params) {
	        _this.log('__onPullEventUserInviteTimeout', params);
	        var failedUserId = params.failedUserId;
	        if (_this.peers[failedUserId]) {
	          _this.peers[failedUserId].onInviteTimeout(false);
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventPing$1, {
	      writable: true,
	      value: function value(params) {
	        if (params.callInstanceId == _this.instanceId) {
	          // ignore self ping
	          return;
	        }
	        var senderId = Number(params.senderId);
	        if (senderId == _this.userId) {
	          if (!_this.joinedElsewhere) {
	            _this.runCallback(CallEvent.onJoin, {
	              local: false
	            });
	            _this.joinedElsewhere = true;
	          }
	          clearTimeout(_this.lastSelfPingReceivedTimeout);
	          _this.lastSelfPingReceivedTimeout = setTimeout(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onNoSelfPingsReceived), pingPeriod$1 * 2.1);
	        }
	        clearTimeout(_this.lastPingReceivedTimeout);
	        _this.lastPingReceivedTimeout = setTimeout(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onNoPingsReceived), pingPeriod$1 * 2.1);
	        if (_this.peers[senderId]) {
	          _this.peers[senderId].setReady(true);
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onNoPingsReceived, {
	      writable: true,
	      value: function value() {
	        if (!_this.ready) {
	          _this.destroy();
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onNoSelfPingsReceived, {
	      writable: true,
	      value: function value() {
	        _this.runCallback(CallEvent.onLeave, {
	          local: false
	        });
	        _this.joinedElsewhere = false;
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventFinish$1, {
	      writable: true,
	      value: function value() {
	        _this.destroy();
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onPullEventRepeatAnswer$1, {
	      writable: true,
	      value: function value() {
	        if (_this.ready) {
	          _this.signaling.sendAnswer({
	            userId: _this.userId
	          }, true);
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onLocalMediaRendererAdded, {
	      writable: true,
	      value: function value(e) {
	        var renderer = e.renderer;
	        var trackLabel = renderer.stream.getVideoTracks().length > 0 ? renderer.stream.getVideoTracks()[0].label : "";
	        _this.log("__onLocalMediaRendererAdded", renderer.kind, trackLabel);
	        if (renderer.kind === "video") {
	          var tag;
	          if (trackLabel.match(/^screen|window|tab|web-contents-media-stream/i)) {
	            tag = "screen";
	          } else {
	            tag = "main";
	          }
	          _this.screenShared = tag === "screen";
	          _this.runCallback(CallEvent.onLocalMediaReceived, {
	            tag: tag,
	            stream: renderer.stream
	          });
	        } else if (renderer.kind === "sharing") {
	          _this.runCallback(CallEvent.onLocalMediaReceived, {
	            tag: "screen",
	            stream: renderer.stream
	          });
	          _this.screenShared = true;
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onBeforeLocalMediaRendererRemoved, {
	      writable: true,
	      value: function value(e) {
	        var renderer = e.renderer;
	        _this.log("__onBeforeLocalMediaRendererRemoved", renderer.kind);
	        if (renderer.kind === "sharing" && !_this.videoEnabled) {
	          _this.runCallback(CallEvent.onLocalMediaReceived, {
	            tag: "main",
	            stream: new MediaStream()
	          });
	          _this.screenShared = false;
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onMicAccessResult, {
	      writable: true,
	      value: function value(e) {
	        if (e.result) {
	          if (e.stream.getAudioTracks().length > 0) {
	            if (_this.localVAD) {
	              _this.localVAD.destroy();
	            }
	            _this.localVAD = new SimpleVAD({
	              mediaStream: e.stream,
	              onVoiceStarted: function onVoiceStarted() {
	                _this.runCallback(CallEvent.onUserVoiceStarted, {
	                  userId: _this.userId,
	                  local: true
	                });
	              },
	              onVoiceStopped: function onVoiceStopped() {
	                _this.runCallback(CallEvent.onUserVoiceStopped, {
	                  userId: _this.userId,
	                  local: true
	                });
	              }
	            });
	            clearInterval(_this.microphoneLevelInterval);
	            _this.microphoneLevelInterval = setInterval(function () {
	              return _this.microphoneLevel = _this.localVAD.currentVolume;
	            }, 200);
	          }
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onCallReconnecting, {
	      writable: true,
	      value: function value() {
	        _this.reconnectionEventCount++;
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onCallReconnected, {
	      writable: true,
	      value: function value() {
	        _this.reconnectionEventCount--;
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onClientReconnecting, {
	      writable: true,
	      value: function value() {
	        _this.reconnectionEventCount++;
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onClientReconnected, {
	      writable: true,
	      value: function value() {
	        _this.reconnectionEventCount--;
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onCallDisconnected, {
	      writable: true,
	      value: function value(e) {
	        _this.log("__onCallDisconnected", e && e.headers ? {
	          headers: e.headers
	        } : null);
	        _this.sendTelemetryEvent("disconnect");
	        _this.localUserState = UserState.Idle;
	        _this.ready = false;
	        _this.muted = false;
	        _this.joinedAsViewer = false;
	        _this.reinitPeers();
	        _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _hideLocalVideo, _hideLocalVideo2).call(babelHelpers.assertThisInitialized(_this));
	        _this.removeCallEvents();
	        _this.voximplantCall = null;
	        var client = VoxImplant.getInstance();
	        client.enableSilentLogging(false);
	        client.setLoggerCallback(null);
	        _this.state = CallState.Proceeding;
	        _this.runCallback(CallEvent.onLeave, {
	          local: true
	        });
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onWindowUnload, {
	      writable: true,
	      value: function value() {
	        if (_this.ready && _this.voximplantCall) {
	          _this.signaling.sendHangup({
	            userId: _this.users
	          });
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onFatalError, {
	      writable: true,
	      value: function value(error) {
	        if (error && error.call) {
	          delete error.call;
	        }
	        _this.log("onFatalError", error);
	        _this.ready = false;
	        _this.muted = false;
	        _this.localUserState = UserState.Failed;
	        _this.reinitPeers();
	        _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _hideLocalVideo, _hideLocalVideo2).call(babelHelpers.assertThisInitialized(_this)).then(function () {
	          if (_this.voximplantCall) {
	            _this.removeCallEvents();
	            try {
	              _this.voximplantCall.hangup({
	                'X-Reason': 'Fatal error',
	                'X-Error': typeof error === 'string' ? error : error.code || error.name
	              });
	            } catch (e) {
	              _this.log("Voximplant hangup error: ", e);
	              console.error("Voximplant hangup error: ", e);
	            }
	            _this.voximplantCall = null;
	          }
	          if (typeof VoxImplant !== 'undefined') {
	            var client = VoxImplant.getInstance();
	            client.enableSilentLogging(false);
	            client.setLoggerCallback(null);
	          }
	          if (typeof error === "string") {
	            _this.runCallback(CallEvent.onCallFailure, {
	              name: error
	            });
	          } else if (error.name) {
	            _this.runCallback(CallEvent.onCallFailure, error);
	          }
	        });
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onCallEndpointAdded, {
	      writable: true,
	      value: function value(e) {
	        var endpoint = e.endpoint;
	        var userName = endpoint.userName;
	        _this.log("__onCallEndpointAdded (" + userName + ")", e.endpoint);
	        console.log("__onCallEndpointAdded (" + userName + ")", e.endpoint);
	        if (main_core.Type.isStringFilled(userName) && userName.startsWith('user')) {
	          _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _setEndpointForUser, _setEndpointForUser2).call(babelHelpers.assertThisInitialized(_this), userName, endpoint);
	        } else {
	          endpoint.addEventListener(VoxImplant.EndpointEvents.InfoUpdated, function (e) {
	            var endpoint = e.endpoint;
	            var userName = endpoint.userName;
	            _this.log("VoxImplant.EndpointEvents.InfoUpdated (" + userName + ")", e.endpoint);
	            if (main_core.Type.isStringFilled(userName) && userName.startsWith('user')) {
	              _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _setEndpointForUser, _setEndpointForUser2).call(babelHelpers.assertThisInitialized(_this), userName, endpoint);
	            }
	          });
	          _this.log('Unknown endpoint ' + userName);
	          console.warn('Unknown endpoint ' + userName);
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onCallStatsReceived, {
	      writable: true,
	      value: function value(e) {
	        if (_this.logger) {
	          _this.logger.sendStat(transformVoxStats(e.stats, _this.voximplantCall));
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onJoinRoomOffer, {
	      writable: true,
	      value: function value(e) {
	        console.warn("__onJoinRoomOffer", e);
	        _this.updateRoom({
	          id: e.roomId,
	          users: e.users,
	          speaker: e.speaker
	        });
	        _this.runCallback(CallEvent.onJoinRoomOffer, {
	          roomId: e.roomId,
	          users: e.users,
	          initiator: e.initiator,
	          speaker: e.speaker
	        });
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onRoomUpdated, {
	      writable: true,
	      value: function value(e) {
	        var speakerChanged = e.roomId === _this._currentRoomId && _this.rooms[e.roomId] && _this.rooms[e.roomId].speaker != e.speaker;
	        var previousSpeaker = speakerChanged && _this.rooms[e.roomId].speaker;
	        console.log("__onRoomUpdated; speakerChanged: ", speakerChanged);
	        _this.updateRoom({
	          id: e.roomId,
	          users: e.users,
	          speaker: e.speaker
	        });
	        if (e.roomId === _this._currentRoomId && e.users.indexOf(_this.userId) === -1) {
	          _this._currentRoomId = null;
	          _this.runCallback(CallEvent.onLeaveRoom, {
	            roomId: e.roomId
	          });
	        } else if (e.roomId !== _this._currentRoomId && e.users.indexOf(_this.userId) !== -1) {
	          _this._currentRoomId = e.roomId;
	          _this.runCallback(CallEvent.onJoinRoom, {
	            roomId: e.roomId,
	            speaker: _this.currentRoom().speaker,
	            users: _this.currentRoom().users
	          });
	        } else if (speakerChanged) {
	          _this.runCallback(CallEvent.onTransferRoomSpeaker, {
	            roomId: e.roomId,
	            speaker: e.speaker,
	            previousSpeaker: previousSpeaker,
	            initiator: e.initiator
	          });
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onCallMessageReceived, {
	      writable: true,
	      value: function value(e) {
	        var message;
	        var peer;
	        try {
	          message = JSON.parse(e.text);
	        } catch (err) {
	          _this.log("Could not parse scenario message.", err);
	          return;
	        }
	        var eventName = message.eventName;
	        if (eventName === clientEvents.voiceStarted) {
	          // todo: remove after switching to SDK VAD events
	          _this.runCallback(CallEvent.onUserVoiceStarted, {
	            userId: message.senderId
	          });
	        } else if (eventName === clientEvents.voiceStopped) {
	          // todo: remove after switching to SDK VAD events
	          _this.runCallback(CallEvent.onUserVoiceStopped, {
	            userId: message.senderId
	          });
	        } else if (eventName === clientEvents.microphoneState) {
	          _this.runCallback(CallEvent.onUserMicrophoneState, {
	            userId: message.senderId,
	            microphoneState: message.microphoneState === "Y"
	          });
	        } else if (eventName === clientEvents.cameraState) {
	          _this.runCallback(CallEvent.onUserCameraState, {
	            userId: message.senderId,
	            cameraState: message.cameraState === "Y"
	          });
	        } else if (eventName === clientEvents.videoPaused) {
	          _this.runCallback(CallEvent.onUserVideoPaused, {
	            userId: message.senderId,
	            videoPaused: message.videoPaused === "Y"
	          });
	        } else if (eventName === clientEvents.screenState) {
	          _this.runCallback(CallEvent.onUserScreenState, {
	            userId: message.senderId,
	            screenState: message.screenState === "Y"
	          });
	        } else if (eventName === clientEvents.recordState) {
	          _this.runCallback(CallEvent.onUserRecordState, {
	            userId: message.senderId,
	            recordState: message.recordState
	          });
	        } else if (eventName === clientEvents.floorRequest) {
	          _this.runCallback(CallEvent.onUserFloorRequest, {
	            userId: message.senderId,
	            requestActive: message.requestActive === "Y"
	          });
	        } else if (eventName === clientEvents.emotion) {
	          _this.runCallback(CallEvent.onUserEmotion, {
	            userId: message.senderId,
	            toUserId: message.toUserId,
	            emotion: message.emotion
	          });
	        } else if (eventName === clientEvents.customMessage) {
	          _this.runCallback(CallEvent.onCustomMessage, {
	            message: message.message
	          });
	        } else if (eventName === "scenarioLogUrl") {
	          console.warn("scenario log url: " + message.logUrl);
	        } else if (eventName === scenarioEvents.joinRoomOffer) {
	          console.log(message);
	          babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onJoinRoomOffer).call(babelHelpers.assertThisInitialized(_this), message);
	        } else if (eventName === scenarioEvents.roomUpdated) {
	          // console.log(message)
	          babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onRoomUpdated).call(babelHelpers.assertThisInitialized(_this), message);
	        } else if (eventName === scenarioEvents.listRoomsResponse) {
	          if (_this.__resolveListRooms) {
	            _this.__resolveListRooms(message.rooms);
	            delete _this.__resolveListRooms;
	          }
	        } else if (eventName === scenarioEvents.viewerJoined) {
	          console.log("viewer " + message.senderId + " joined");
	          peer = _this.peers[message.senderId];
	          if (peer) {
	            peer.setDirection(EndpointDirection.RecvOnly);
	            peer.setReady(true);
	          }
	        } else if (eventName === scenarioEvents.viewerLeft) {
	          console.log("viewer " + message.senderId + " left");
	          peer = _this.peers[message.senderId];
	          if (peer) {
	            peer.setReady(false);
	          }
	        } else {
	          _this.log("Unknown scenario event " + eventName);
	        }
	      }
	    });
	    _this.videoQuality = Quality.VeryHigh; // initial video quality. will drop on new peers connecting

	    _this.voximplantCall = null;
	    _this.signaling = new Signaling$1({
	      call: babelHelpers.assertThisInitialized(_this)
	    });
	    _this.peers = {};
	    _this.joinedElsewhere = false;
	    _this.joinedAsViewer = false;
	    _this.localVideoShown = false;
	    _this._localUserState = UserState.Idle;
	    _this.clientEventsBound = false;
	    _this._screenShared = false;
	    _this.videoAllowedFrom = UserMnemonic.all;
	    _this.direction = EndpointDirection.SendRecv;
	    _this.microphoneLevelInterval = null;
	    _this.rooms = {};
	    window.addEventListener("unload", babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onWindowUnload));
	    _this.initPeers();
	    _this.pingUsersInterval = setInterval(_this.pingUsers.bind(babelHelpers.assertThisInitialized(_this)), pingPeriod$1);
	    _this.pingBackendInterval = setInterval(_this.pingBackend.bind(babelHelpers.assertThisInitialized(_this)), backendPingPeriod$1);
	    _this.lastPingReceivedTimeout = null;
	    _this.lastSelfPingReceivedTimeout = null;
	    _this.reinviteTimeout = null;

	    // There are two kinds of reconnection events: from call (for media connection) and from client (for signaling).
	    // So we have to use counter to convert these two events to one
	    _this._reconnectionEventCount = 0;
	    _this.pullEventHandlers = {
	      'Call::answer': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventAnswer$1),
	      'Call::hangup': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventHangup$1),
	      'Call::usersJoined': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventUsersJoined$1),
	      'Call::usersInvited': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventUsersInvited$1),
	      'Call::userInviteTimeout': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventUserInviteTimeout$1),
	      'Call::ping': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventPing$1),
	      'Call::finish': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventFinish$1),
	      'Call::repeatAnswer': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onPullEventRepeatAnswer$1)
	    };
	    return _this;
	  }
	  babelHelpers.createClass(VoximplantCall, [{
	    key: "initPeers",
	    value: function initPeers() {
	      var _this2 = this;
	      this.users.forEach(function (userId) {
	        userId = Number(userId);
	        _this2.peers[userId] = _this2.createPeer(userId);
	      });
	    }
	  }, {
	    key: "reinitPeers",
	    value: function reinitPeers() {
	      for (var userId in this.peers) {
	        if (this.peers.hasOwnProperty(userId) && this.peers[userId]) {
	          this.peers[userId].destroy();
	          this.peers[userId] = null;
	        }
	      }
	      this.initPeers();
	    }
	  }, {
	    key: "pingUsers",
	    value: function pingUsers() {
	      if (this.ready) {
	        var users = this.users.concat(this.userId);
	        this.signaling.sendPingToUsers({
	          userId: users
	        }, true);
	      }
	    }
	  }, {
	    key: "pingBackend",
	    value: function pingBackend() {
	      if (this.ready) {
	        this.signaling.sendPingToBackend();
	      }
	    }
	  }, {
	    key: "createPeer",
	    value: function createPeer(userId) {
	      var _this3 = this;
	      var incomingVideoAllowed;
	      if (this.videoAllowedFrom === UserMnemonic.all) {
	        incomingVideoAllowed = true;
	      } else if (this.videoAllowedFrom === UserMnemonic.none) {
	        incomingVideoAllowed = false;
	      } else if (main_core.Type.isArray(this.videoAllowedFrom)) {
	        incomingVideoAllowed = this.videoAllowedFrom.some(function (allowedUserId) {
	          return allowedUserId == userId;
	        });
	      } else {
	        incomingVideoAllowed = true;
	      }
	      return new Peer$1({
	        call: this,
	        userId: userId,
	        ready: userId == this.initiatorId,
	        isIncomingVideoAllowed: incomingVideoAllowed,
	        onMediaReceived: function onMediaReceived(e) {
	          _this3.runCallback(CallEvent.onRemoteMediaReceived, e);
	          if (e.kind === 'video') {
	            _this3.runCallback(CallEvent.onUserVideoPaused, {
	              userId: userId,
	              videoPaused: false
	            });
	          }
	        },
	        onMediaRemoved: function onMediaRemoved(e) {
	          _this3.runCallback(CallEvent.onRemoteMediaStopped, e);
	        },
	        onVoiceStarted: function onVoiceStarted() {
	          // todo: uncomment to switch to SDK VAD events
	          /*this.runCallback(Event.onUserVoiceStarted, {
	          	userId: userId
	          });*/
	        },
	        onVoiceEnded: function onVoiceEnded() {
	          // todo: uncomment to switch to SDK VAD events
	          /*this.runCallback(Event.onUserVoiceStopped, {
	          	userId: userId
	          });*/
	        },
	        onStateChanged: babelHelpers.classPrivateFieldGet(this, _onPeerStateChanged$1),
	        onInviteTimeout: babelHelpers.classPrivateFieldGet(this, _onPeerInviteTimeout$1)
	      });
	    }
	  }, {
	    key: "getUsers",
	    value: function getUsers() {
	      var result = {};
	      for (var userId in this.peers) {
	        result[userId] = this.peers[userId].calculatedState;
	      }
	      return result;
	    }
	  }, {
	    key: "getUserCount",
	    value: function getUserCount() {
	      return Object.keys(this.peers).length;
	    }
	  }, {
	    key: "getClient",
	    value: function getClient() {
	      var _this4 = this;
	      return new Promise(function (resolve, reject) {
	        BX.Voximplant.getClient({
	          restClient: CallEngine.getRestClient()
	        }).then(function (client) {
	          client.enableSilentLogging();
	          client.setLoggerCallback(function (e) {
	            return _this4.log(e.label + ": " + e.message);
	          });
	          _this4.log("User agent: " + navigator.userAgent);
	          _this4.log("Voximplant SDK version: " + VoxImplant.version);
	          _this4.bindClientEvents();
	          resolve(client);
	        })["catch"](reject);
	      });
	    }
	  }, {
	    key: "bindClientEvents",
	    value: function bindClientEvents() {
	      var streamManager = VoxImplant.Hardware.StreamManager.get();
	      if (!this.clientEventsBound) {
	        VoxImplant.getInstance().on(VoxImplant.Events.MicAccessResult, babelHelpers.classPrivateFieldGet(this, _onMicAccessResult));
	        if (VoxImplant.Events.Reconnecting) {
	          VoxImplant.getInstance().on(VoxImplant.Events.Reconnecting, babelHelpers.classPrivateFieldGet(this, _onClientReconnecting));
	          VoxImplant.getInstance().on(VoxImplant.Events.Reconnected, babelHelpers.classPrivateFieldGet(this, _onClientReconnected));
	        }

	        // streamManager.on(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.#onLocalDevicesUpdated);
	        streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, babelHelpers.classPrivateFieldGet(this, _onLocalMediaRendererAdded));
	        streamManager.on(VoxImplant.Hardware.HardwareEvents.MediaRendererUpdated, babelHelpers.classPrivateFieldGet(this, _onLocalMediaRendererAdded));
	        streamManager.on(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, babelHelpers.classPrivateFieldGet(this, _onBeforeLocalMediaRendererRemoved));
	        this.clientEventsBound = true;
	      }
	    }
	  }, {
	    key: "removeClientEvents",
	    value: function removeClientEvents() {
	      if (!('VoxImplant' in window)) {
	        return;
	      }
	      VoxImplant.getInstance().off(VoxImplant.Events.MicAccessResult, babelHelpers.classPrivateFieldGet(this, _onMicAccessResult));
	      if (VoxImplant.Events.Reconnecting) {
	        VoxImplant.getInstance().off(VoxImplant.Events.Reconnecting, babelHelpers.classPrivateFieldGet(this, _onClientReconnecting));
	        VoxImplant.getInstance().off(VoxImplant.Events.Reconnected, babelHelpers.classPrivateFieldGet(this, _onClientReconnected));
	      }
	      var streamManager = VoxImplant.Hardware.StreamManager.get();
	      // streamManager.off(VoxImplant.Hardware.HardwareEvents.DevicesUpdated, this.#onLocalDevicesUpdated);
	      streamManager.off(VoxImplant.Hardware.HardwareEvents.MediaRendererAdded, babelHelpers.classPrivateFieldGet(this, _onLocalMediaRendererAdded));
	      streamManager.off(VoxImplant.Hardware.HardwareEvents.BeforeMediaRendererRemoved, babelHelpers.classPrivateFieldGet(this, _onBeforeLocalMediaRendererRemoved));
	      this.clientEventsBound = false;
	    }
	  }, {
	    key: "setMuted",
	    value: function setMuted(muted) {
	      if (this.muted == muted) {
	        return;
	      }
	      this.muted = muted;
	      if (this.voximplantCall) {
	        if (this.muted) {
	          this.voximplantCall.muteMicrophone();
	        } else {
	          this.voximplantCall.unmuteMicrophone();
	        }
	        this.signaling.sendMicrophoneState(!this.muted);
	      }
	    }
	  }, {
	    key: "isMuted",
	    value: function isMuted() {
	      return this.muted;
	    }
	  }, {
	    key: "setVideoEnabled",
	    value: function setVideoEnabled(videoEnabled) {
	      var _this5 = this;
	      videoEnabled = videoEnabled === true;
	      if (this.videoEnabled == videoEnabled) {
	        return;
	      }
	      this.videoEnabled = videoEnabled;
	      if (this.voximplantCall) {
	        if (videoEnabled) {
	          _classPrivateMethodGet$1(this, _showLocalVideo, _showLocalVideo2).call(this);
	        } else {
	          if (this.localVideoShown) {
	            VoxImplant.Hardware.StreamManager.get().hideLocalVideo().then(function () {
	              _this5.localVideoShown = false;
	              _this5.runCallback(CallEvent.onLocalMediaReceived, {
	                tag: "main",
	                stream: new MediaStream()
	              });
	            });
	          }
	        }
	        this.voximplantCall.sendVideo(this.videoEnabled);
	        this.signaling.sendCameraState(this.videoEnabled);
	      }
	    }
	  }, {
	    key: "setCameraId",
	    value: function setCameraId(cameraId) {
	      var _this6 = this;
	      if (this.cameraId == cameraId) {
	        return;
	      }
	      this.cameraId = cameraId;
	      if (this.voximplantCall) {
	        VoxImplant.Hardware.CameraManager.get().getInputDevices().then(function () {
	          VoxImplant.Hardware.CameraManager.get().setCallVideoSettings(_this6.voximplantCall, _this6.constructCameraParams());
	        });
	      }
	    }
	  }, {
	    key: "setMicrophoneId",
	    value: function setMicrophoneId(microphoneId) {
	      var _this7 = this;
	      if (this.microphoneId == microphoneId) {
	        return;
	      }
	      this.microphoneId = microphoneId;
	      if (this.voximplantCall) {
	        VoxImplant.Hardware.AudioDeviceManager.get().getInputDevices().then(function () {
	          VoxImplant.Hardware.AudioDeviceManager.get().setCallAudioSettings(_this7.voximplantCall, {
	            inputId: _this7.microphoneId
	          });
	        });
	      }
	    }
	  }, {
	    key: "getCurrentMicrophoneId",
	    value: function getCurrentMicrophoneId() {
	      if (this.voximplantCall.peerConnection.impl.getTransceivers) {
	        var transceivers = this.voximplantCall.peerConnection.impl.getTransceivers();
	        if (transceivers.length > 0) {
	          var audioTrack = transceivers[0].sender.track;
	          var audioTrackSettings = audioTrack.getSettings();
	          return audioTrackSettings.deviceId;
	        }
	      }
	      return this.microphoneId;
	    }
	  }, {
	    key: "constructCameraParams",
	    value: function constructCameraParams() {
	      var result = {};
	      if (this.cameraId) {
	        result.cameraId = this.cameraId;
	      }
	      result.videoQuality = this.videoHd ? VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_HD : VoxImplant.Hardware.VideoQuality.VIDEO_SIZE_nHD;
	      result.facingMode = true;
	      return result;
	    }
	  }, {
	    key: "useHdVideo",
	    value: function useHdVideo(flag) {
	      this.videoHd = flag === true;
	    }
	  }, {
	    key: "requestFloor",
	    value: function requestFloor(requestActive) {
	      this.signaling.sendFloorRequest(requestActive);
	    }
	  }, {
	    key: "sendRecordState",
	    value: function sendRecordState(recordState) {
	      this.signaling.sendRecordState(recordState);
	    }
	  }, {
	    key: "sendEmotion",
	    value: function sendEmotion(toUserId, emotion) {
	      this.signaling.sendEmotion(toUserId, emotion);
	    }
	  }, {
	    key: "sendCustomMessage",
	    value: function sendCustomMessage(message, repeatOnConnect) {
	      this.signaling.sendCustomMessage(message, repeatOnConnect);
	    }
	  }, {
	    key: "allowVideoFrom",
	    /**
	     * Updates list of users,
	     * @param {UserMnemonic | int[]} userList
	     */
	    value: function allowVideoFrom(userList) {
	      if (this.videoAllowedFrom == userList) {
	        return;
	      }
	      this.videoAllowedFrom = userList;
	      if (userList === UserMnemonic.all) {
	        this.signaling.sendShowAll();
	        userList = Object.keys(this.peers);
	      } else if (userList === UserMnemonic.none) {
	        this.signaling.sendHideAll();
	        userList = [];
	      } else if (main_core.Type.isArray(userList)) {
	        this.signaling.sendShowUsers(userList);
	      } else {
	        throw new Error("userList is in wrong format");
	      }
	      var users = {};
	      userList.forEach(function (userId) {
	        return users[userId] = true;
	      });
	      for (var userId in this.peers) {
	        if (!this.peers.hasOwnProperty(userId)) {
	          continue;
	        }
	        if (users[userId]) {
	          this.peers[userId].allowIncomingVideo(true);
	        } else {
	          this.peers[userId].allowIncomingVideo(false);
	        }
	      }
	    }
	  }, {
	    key: "startScreenSharing",
	    value: function startScreenSharing() {
	      var _this8 = this;
	      if (!this.voximplantCall) {
	        return;
	      }
	      var showLocalView = !this.videoEnabled;
	      var replaceTrack = this.videoEnabled || this.screenShared;
	      this.voximplantCall.shareScreen(showLocalView, replaceTrack).then(function () {
	        _this8.log("Screen shared");
	        _this8.screenShared = true;
	      })["catch"](function (error) {
	        console.error(error);
	        _this8.log("Screen sharing error:", error);
	      });
	    }
	  }, {
	    key: "stopScreenSharing",
	    value: function stopScreenSharing() {
	      var _this9 = this;
	      if (!this.voximplantCall) {
	        return;
	      }
	      this.voximplantCall.stopSharingScreen().then(function () {
	        _this9.log("Screen is no longer shared");
	        _this9.screenShared = false;
	      });
	    }
	  }, {
	    key: "isScreenSharingStarted",
	    value: function isScreenSharingStarted() {
	      return this.screenShared;
	    }
	  }, {
	    key: "inviteUsers",
	    /**
	     * Invites users to participate in the call.
	     */
	    value: function inviteUsers() {
	      var _this10 = this;
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.ready = true;
	      var users = main_core.Type.isArray(config.users) ? config.users : this.users;
	      this.attachToConference().then(function () {
	        _this10.signaling.sendPingToUsers({
	          userId: users
	        });
	        if (users.length > 0) {
	          return _this10.signaling.inviteUsers({
	            userIds: users,
	            video: _this10.videoEnabled ? 'Y' : 'N'
	          });
	        }
	      }).then(function () {
	        _this10.state = CallState.Connected;
	        _this10.runCallback(CallEvent.onJoin, {
	          local: true
	        });
	        for (var i = 0; i < users.length; i++) {
	          var userId = parseInt(users[i]);
	          if (!_this10.users.includes(userId)) {
	            _this10.users.push(userId);
	          }
	          if (!_this10.peers[userId]) {
	            _this10.peers[userId] = _this10.createPeer(userId);
	            if (_this10.type === CallType.Instant) {
	              _this10.runCallback(CallEvent.onUserInvited, {
	                userId: userId
	              });
	            }
	          }
	          if (_this10.type === CallType.Instant) {
	            _this10.peers[userId].onInvited();
	            _this10.scheduleRepeatInvite();
	          }
	        }
	      })["catch"](function (e) {
	        babelHelpers.classPrivateFieldGet(_this10, _onFatalError).call(_this10, e);
	      });
	    }
	  }, {
	    key: "scheduleRepeatInvite",
	    value: function scheduleRepeatInvite() {
	      var _this11 = this;
	      clearTimeout(this.reinviteTimeout);
	      this.reinviteTimeout = setTimeout(function () {
	        return _this11.repeatInviteUsers();
	      }, reinvitePeriod$1);
	    }
	  }, {
	    key: "repeatInviteUsers",
	    value: function repeatInviteUsers() {
	      var _this12 = this;
	      clearTimeout(this.reinviteTimeout);
	      if (!this.ready) {
	        return;
	      }
	      var usersToRepeatInvite = [];
	      for (var userId in this.peers) {
	        if (this.peers.hasOwnProperty(userId) && this.peers[userId].calculatedState === UserState.Calling) {
	          usersToRepeatInvite.push(userId);
	        }
	      }
	      if (usersToRepeatInvite.length === 0) {
	        return;
	      }
	      var inviteParams = {
	        userIds: usersToRepeatInvite,
	        video: this.videoEnabled ? 'Y' : 'N',
	        isRepeated: 'Y'
	      };
	      this.signaling.inviteUsers(inviteParams).then(function () {
	        return _this12.scheduleRepeatInvite();
	      });
	    }
	  }, {
	    key: "answer",
	    /**
	     * @param {Object} config
	     * @param {bool?} [config.useVideo]
	     * @param {bool?} [config.joinAsViewer]
	     */
	    value: function answer() {
	      var _this13 = this;
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.ready = true;
	      var joinAsViewer = config.joinAsViewer === true;
	      this.videoEnabled = config.useVideo === true;
	      if (!joinAsViewer) {
	        this.signaling.sendAnswer();
	      }
	      this.attachToConference({
	        joinAsViewer: joinAsViewer
	      }).then(function () {
	        _this13.log("Attached to conference");
	        _this13.state = CallState.Connected;
	        _this13.runCallback(CallEvent.onJoin, {
	          local: true
	        });
	      })["catch"](function (err) {
	        babelHelpers.classPrivateFieldGet(_this13, _onFatalError).call(_this13, err);
	      });
	    }
	  }, {
	    key: "decline",
	    value: function decline(code) {
	      this.ready = false;
	      var data = {
	        callId: this.id,
	        callInstanceId: this.instanceId
	      };
	      if (code) {
	        data.code = code;
	      }
	      CallEngine.getRestClient().callMethod(ajaxActions$1.decline, data);
	    }
	  }, {
	    key: "hangup",
	    value: function hangup(code, reason) {
	      if (!this.ready) {
	        var error = new Error("Hangup in wrong state");
	        this.log(error);
	        return;
	      }
	      var tempError = new Error();
	      tempError.name = "Call stack:";
	      this.log("Hangup received \n" + tempError.stack);
	      if (this.localVAD) {
	        this.localVAD.destroy();
	        this.localVAD = null;
	      }
	      clearInterval(this.microphoneLevelInterval);
	      var data = {};
	      this.ready = false;
	      if (typeof code != 'undefined') {
	        data.code = code;
	      }
	      if (typeof reason != 'undefined') {
	        data.reason = reason;
	      }
	      this.state = CallState.Proceeding;
	      this.runCallback(CallEvent.onLeave, {
	        local: true
	      });

	      //clone users and append current user id to send event to all participants of the call
	      data.userId = this.users.slice(0).concat(this.userId);
	      this.signaling.sendHangup(data);
	      this.muted = false;

	      // for future reconnections
	      this.reinitPeers();
	      if (this.voximplantCall) {
	        this.voximplantCall._replaceVideoSharing = false;
	        try {
	          this.voximplantCall.hangup();
	        } catch (e) {
	          this.log("Voximplant hangup error: ", e);
	          console.error("Voximplant hangup error: ", e);
	        }
	      } else {
	        this.log("Tried to hangup, but this.voximplantCall points nowhere");
	        console.error("Tried to hangup, but this.voximplantCall points nowhere");
	      }
	      this.screenShared = false;
	      _classPrivateMethodGet$1(this, _hideLocalVideo, _hideLocalVideo2).call(this);
	    }
	  }, {
	    key: "attachToConference",
	    value: function attachToConference() {
	      var _this14 = this;
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var joinAsViewer = options.joinAsViewer === true;
	      if (this.voximplantCall && this.voximplantCall.state() === "CONNECTED") {
	        if (this.joinedAsViewer === joinAsViewer) {
	          return Promise.resolve();
	        } else {
	          return Promise.reject("Already joined call in another mode");
	        }
	      }
	      return new Promise(function (resolve, reject) {
	        _this14.direction = joinAsViewer ? EndpointDirection.RecvOnly : EndpointDirection.SendRecv;
	        _this14.sendTelemetryEvent("call");
	        _this14.getClient().then(function (voximplantClient) {
	          _this14.localUserState = UserState.Connecting;

	          // workaround to set default video settings before starting call. ugly, but I do not see another way
	          VoxImplant.Hardware.CameraManager.get().setDefaultVideoSettings(_this14.constructCameraParams());
	          if (_this14.microphoneId) {
	            VoxImplant.Hardware.AudioDeviceManager.get().setDefaultAudioSettings({
	              inputId: _this14.microphoneId
	            });
	          }
	          if (_this14.videoEnabled) {
	            _classPrivateMethodGet$1(_this14, _showLocalVideo, _showLocalVideo2).call(_this14);
	          }
	          try {
	            if (joinAsViewer) {
	              _this14.voximplantCall = voximplantClient.joinAsViewer("bx_conf_" + _this14.id, {
	                'X-Direction': EndpointDirection.RecvOnly
	              });
	            } else {
	              _this14.voximplantCall = voximplantClient.callConference({
	                number: "bx_conf_" + _this14.id,
	                video: {
	                  sendVideo: _this14.videoEnabled,
	                  receiveVideo: true
	                },
	                // simulcast: (this.getUserCount() > MAX_USERS_WITHOUT_SIMULCAST),
	                // simulcastProfileName: 'b24',
	                customData: JSON.stringify({
	                  cameraState: _this14.videoEnabled
	                })
	              });
	            }
	          } catch (e) {
	            console.error(e);
	            return reject(e);
	          }
	          _this14.joinedAsViewer = joinAsViewer;
	          if (!_this14.voximplantCall) {
	            _this14.log("Error: could not create voximplant call");
	            return reject({
	              code: "VOX_NO_CALL"
	            });
	          }
	          _this14.runCallback(VoximplantCallEvent.onCallConference, {
	            call: _this14
	          });
	          _this14.bindCallEvents();
	          _this14.voximplantCall.on(VoxImplant.CallEvents.Connected, function () {
	            _classPrivateMethodGet$1(_this14, _onCallConnected, _onCallConnected2).call(_this14);
	            resolve();
	          }, {
	            once: true
	          });
	          _this14.voximplantCall.on(VoxImplant.CallEvents.Failed, function (e) {
	            _classPrivateMethodGet$1(_this14, _onCallFailed, _onCallFailed2).call(_this14, e);
	            reject(e);
	          }, {
	            once: true
	          });
	        })["catch"](babelHelpers.classPrivateFieldGet(_this14, _onFatalError).bind(_this14));
	      });
	    }
	  }, {
	    key: "bindCallEvents",
	    value: function bindCallEvents() {
	      this.voximplantCall.on(VoxImplant.CallEvents.Disconnected, babelHelpers.classPrivateFieldGet(this, _onCallDisconnected));
	      this.voximplantCall.on(VoxImplant.CallEvents.MessageReceived, babelHelpers.classPrivateFieldGet(this, _onCallMessageReceived));
	      if (Util$1.shouldCollectStats()) {
	        this.voximplantCall.on(VoxImplant.CallEvents.CallStatsReceived, babelHelpers.classPrivateFieldGet(this, _onCallStatsReceived));
	      }
	      this.voximplantCall.on(VoxImplant.CallEvents.EndpointAdded, babelHelpers.classPrivateFieldGet(this, _onCallEndpointAdded));
	      if (VoxImplant.CallEvents.Reconnecting) {
	        this.voximplantCall.on(VoxImplant.CallEvents.Reconnecting, babelHelpers.classPrivateFieldGet(this, _onCallReconnecting));
	        this.voximplantCall.on(VoxImplant.CallEvents.Reconnected, babelHelpers.classPrivateFieldGet(this, _onCallReconnected));
	      }
	    }
	  }, {
	    key: "removeCallEvents",
	    value: function removeCallEvents() {
	      if (this.voximplantCall) {
	        this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Disconnected, babelHelpers.classPrivateFieldGet(this, _onCallDisconnected));
	        this.voximplantCall.removeEventListener(VoxImplant.CallEvents.MessageReceived, babelHelpers.classPrivateFieldGet(this, _onCallMessageReceived));
	        if (Util$1.shouldCollectStats()) {
	          this.voximplantCall.removeEventListener(VoxImplant.CallEvents.CallStatsReceived, babelHelpers.classPrivateFieldGet(this, _onCallStatsReceived));
	        }
	        this.voximplantCall.removeEventListener(VoxImplant.CallEvents.EndpointAdded, babelHelpers.classPrivateFieldGet(this, _onCallEndpointAdded));
	        if (VoxImplant.CallEvents.Reconnecting) {
	          this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Reconnecting, babelHelpers.classPrivateFieldGet(this, _onCallReconnecting));
	          this.voximplantCall.removeEventListener(VoxImplant.CallEvents.Reconnected, babelHelpers.classPrivateFieldGet(this, _onCallReconnected));
	        }
	      }
	    }
	  }, {
	    key: "addJoinedUsers",
	    /**
	     * Adds new users to call
	     * @param {Number[]} users
	     */
	    value: function addJoinedUsers(users) {
	      for (var i = 0; i < users.length; i++) {
	        var userId = Number(users[i]);
	        if (userId == this.userId || this.peers[userId]) {
	          continue;
	        }
	        this.peers[userId] = this.createPeer(userId);
	        if (!this.users.includes(userId)) {
	          this.users.push(userId);
	        }
	        this.runCallback(CallEvent.onUserInvited, {
	          userId: userId
	        });
	      }
	    }
	  }, {
	    key: "addInvitedUsers",
	    /**
	     * Adds users, invited by you or someone else
	     * @param {Number[]} users
	     */
	    value: function addInvitedUsers(users) {
	      for (var i = 0; i < users.length; i++) {
	        var userId = Number(users[i]);
	        if (userId == this.userId) {
	          continue;
	        }
	        if (this.peers[userId]) {
	          if (this.peers[userId].calculatedState === UserState.Failed || this.peers[userId].calculatedState === UserState.Idle) {
	            if (this.type === CallType.Instant) {
	              this.peers[userId].onInvited();
	            }
	          }
	        } else {
	          this.peers[userId] = this.createPeer(userId);
	          if (this.type === CallType.Instant) {
	            this.peers[userId].onInvited();
	          }
	        }
	        if (!this.users.includes(userId)) {
	          this.users.push(userId);
	        }
	        this.runCallback(CallEvent.onUserInvited, {
	          userId: userId
	        });
	      }
	    }
	  }, {
	    key: "isAnyoneParticipating",
	    value: function isAnyoneParticipating() {
	      for (var userId in this.peers) {
	        if (this.peers[userId].isParticipating()) {
	          return true;
	        }
	      }
	      return false;
	    }
	  }, {
	    key: "getParticipatingUsers",
	    value: function getParticipatingUsers() {
	      var result = [];
	      for (var userId in this.peers) {
	        if (this.peers[userId].isParticipating()) {
	          result.push(userId);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "updateRoom",
	    value: function updateRoom(roomData) {
	      if (!this.rooms[roomData.id]) {
	        this.rooms[roomData.id] = {
	          id: roomData.id,
	          users: roomData.users,
	          speaker: roomData.speaker
	        };
	      } else {
	        this.rooms[roomData.id].users = roomData.users;
	        this.rooms[roomData.id].speaker = roomData.speaker;
	      }
	    }
	  }, {
	    key: "currentRoom",
	    value: function currentRoom() {
	      return this._currentRoomId ? this.rooms[this._currentRoomId] : null;
	    }
	  }, {
	    key: "isRoomSpeaker",
	    value: function isRoomSpeaker() {
	      return this.currentRoom() ? this.currentRoom().speaker == this.userId : false;
	    }
	  }, {
	    key: "joinRoom",
	    value: function joinRoom(roomId) {
	      this.signaling.sendJoinRoom(roomId);
	    }
	  }, {
	    key: "requestRoomSpeaker",
	    value: function requestRoomSpeaker() {
	      this.signaling.sendRequestRoomSpeaker(this._currentRoomId);
	    }
	  }, {
	    key: "leaveCurrentRoom",
	    value: function leaveCurrentRoom() {
	      this.signaling.sendLeaveRoom(this._currentRoomId);
	    }
	  }, {
	    key: "listRooms",
	    value: function listRooms() {
	      var _this15 = this;
	      return new Promise(function (resolve) {
	        _this15.signaling.sendListRooms();
	        _this15.__resolveListRooms = resolve;
	      });
	    }
	  }, {
	    key: "__onPullEvent",
	    value: function __onPullEvent(command, params, extra) {
	      if (this.pullEventHandlers[command]) {
	        if (command != 'Call::ping') {
	          this.log("Signaling: " + command + "; Parameters: " + JSON.stringify(params));
	        }
	        this.pullEventHandlers[command].call(this, params);
	      }
	    }
	  }, {
	    key: "__onPullEventAnswerSelf",
	    value: function __onPullEventAnswerSelf(params) {
	      if (params.callInstanceId === this.instanceId) {
	        return;
	      }

	      // call was answered elsewhere
	      this.joinedElsewhere = true;
	      this.runCallback(CallEvent.onJoin, {
	        local: false
	      });
	    }
	  }, {
	    key: "sendTelemetryEvent",
	    value: function sendTelemetryEvent(eventName) {
	      Util$1.sendTelemetryEvent({
	        call_id: this.id,
	        user_id: this.userId,
	        kind: "voximplant",
	        event: eventName
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.ready = false;
	      this.joinedAsViewer = false;
	      _classPrivateMethodGet$1(this, _hideLocalVideo, _hideLocalVideo2).call(this);
	      if (this.localVAD) {
	        this.localVAD.destroy();
	        this.localVAD = null;
	      }
	      clearInterval(this.microphoneLevelInterval);
	      if (this.voximplantCall) {
	        this.removeCallEvents();
	        if (this.voximplantCall.state() != "ENDED") {
	          this.voximplantCall.hangup();
	        }
	        this.voximplantCall = null;
	      }
	      for (var userId in this.peers) {
	        if (this.peers.hasOwnProperty(userId) && this.peers[userId]) {
	          this.peers[userId].destroy();
	        }
	      }
	      this.removeClientEvents();
	      clearTimeout(this.lastPingReceivedTimeout);
	      clearTimeout(this.lastSelfPingReceivedTimeout);
	      clearInterval(this.pingUsersInterval);
	      clearInterval(this.pingBackendInterval);
	      window.removeEventListener("unload", babelHelpers.classPrivateFieldGet(this, _onWindowUnload));
	      return babelHelpers.get(babelHelpers.getPrototypeOf(VoximplantCall.prototype), "destroy", this).call(this);
	    }
	  }, {
	    key: "provider",
	    get: function get() {
	      return Provider.Voximplant;
	    }
	  }, {
	    key: "screenShared",
	    get: function get() {
	      return this._screenShared;
	    },
	    set: function set(screenShared) {
	      if (screenShared != this._screenShared) {
	        this._screenShared = screenShared;
	        this.signaling.sendScreenState(this._screenShared);
	      }
	    }
	  }, {
	    key: "localUserState",
	    get: function get() {
	      return this._localUserState;
	    },
	    set: function set(state) {
	      if (state == this._localUserState) {
	        return;
	      }
	      this.runCallback(CallEvent.onUserStateChanged, {
	        userId: this.userId,
	        state: state,
	        previousState: this._localUserState,
	        direction: this.direction
	      });
	      this._localUserState = state;
	    }
	  }, {
	    key: "reconnectionEventCount",
	    get: function get() {
	      return this._reconnectionEventCount;
	    },
	    set: function set(newValue) {
	      if (this._reconnectionEventCount === 0 && newValue > 0) {
	        this.runCallback(CallEvent.onReconnecting);
	      }
	      if (newValue === 0) {
	        this.runCallback(CallEvent.onReconnected);
	      }
	      this._reconnectionEventCount = newValue;
	    }
	  }]);
	  return VoximplantCall;
	}(AbstractCall);
	function _showLocalVideo2() {
	  var _this18 = this;
	  return new Promise(function (resolve) {
	    VoxImplant.Hardware.StreamManager.get().showLocalVideo(false).then(function () {
	      _this18.localVideoShown = true;
	      resolve();
	    }, function () {
	      _this18.localVideoShown = true;
	      resolve();
	    });
	  });
	}
	function _hideLocalVideo2() {
	  var _this19 = this;
	  return new Promise(function (resolve) {
	    if (!('VoxImplant' in window)) {
	      resolve();
	      return;
	    }
	    VoxImplant.Hardware.StreamManager.get().hideLocalVideo().then(function () {
	      _this19.localVideoShown = false;
	      resolve();
	    }, function () {
	      _this19.localVideoShown = false;
	      resolve();
	    });
	  });
	}
	function _onCallConnected2() {
	  this.log("Call connected");
	  this.sendTelemetryEvent("connect");
	  this.localUserState = UserState.Connected;
	  this.voximplantCall.on(VoxImplant.CallEvents.Failed, babelHelpers.classPrivateFieldGet(this, _onCallDisconnected));
	  if (this.muted) {
	    this.voximplantCall.muteMicrophone();
	  }
	  this.signaling.sendMicrophoneState(!this.muted);
	  this.signaling.sendCameraState(this.videoEnabled);
	  if (this.videoAllowedFrom == UserMnemonic.none) {
	    this.signaling.sendHideAll();
	  } else if (main_core.Type.isArray(this.videoAllowedFrom)) {
	    this.signaling.sendShowUsers(this.videoAllowedFrom);
	  }
	}
	function _onCallFailed2(e) {
	  this.log("Could not attach to conference", e);
	  this.sendTelemetryEvent("connect_failure");
	  this.localUserState = UserState.Failed;
	  var client = VoxImplant.getInstance();
	  client.enableSilentLogging(false);
	  client.setLoggerCallback(null);
	}
	function _setEndpointForUser2(userName, endpoint) {
	  // user connected to conference (userName is in format `user${id}`
	  var userId = parseInt(userName.substring(4));
	  if (this.peers[userId]) {
	    this.peers[userId].setEndpoint(endpoint);
	  }
	  this.wasConnected = true;
	}
	babelHelpers.defineProperty(VoximplantCall, "Event", VoximplantCallEvent);
	var _sendPullEvent$1 = /*#__PURE__*/new WeakSet();
	var _sendMessage = /*#__PURE__*/new WeakSet();
	var _runRestAction$1 = /*#__PURE__*/new WeakSet();
	var Signaling$1 = /*#__PURE__*/function () {
	  function Signaling(params) {
	    babelHelpers.classCallCheck(this, Signaling);
	    _classPrivateMethodInitSpec$1(this, _runRestAction$1);
	    _classPrivateMethodInitSpec$1(this, _sendMessage);
	    _classPrivateMethodInitSpec$1(this, _sendPullEvent$1);
	    this.call = params.call;
	  }
	  babelHelpers.createClass(Signaling, [{
	    key: "inviteUsers",
	    value: function inviteUsers(data) {
	      return _classPrivateMethodGet$1(this, _runRestAction$1, _runRestAction2$1).call(this, ajaxActions$1.invite, data);
	    }
	  }, {
	    key: "sendAnswer",
	    value: function sendAnswer(data, repeated) {
	      if (repeated && CallEngine.getPullClient().isPublishingEnabled()) {
	        _classPrivateMethodGet$1(this, _sendPullEvent$1, _sendPullEvent2$1).call(this, pullEvents$1.answer, data);
	      } else {
	        return _classPrivateMethodGet$1(this, _runRestAction$1, _runRestAction2$1).call(this, ajaxActions$1.answer, data);
	      }
	    }
	  }, {
	    key: "sendCancel",
	    value: function sendCancel(data) {
	      return _classPrivateMethodGet$1(this, _runRestAction$1, _runRestAction2$1).call(this, ajaxActions$1.cancel, data);
	    }
	  }, {
	    key: "sendHangup",
	    value: function sendHangup(data) {
	      if (CallEngine.getPullClient().isPublishingEnabled()) {
	        _classPrivateMethodGet$1(this, _sendPullEvent$1, _sendPullEvent2$1).call(this, pullEvents$1.hangup, data);
	        data.retransmit = false;
	        _classPrivateMethodGet$1(this, _runRestAction$1, _runRestAction2$1).call(this, ajaxActions$1.hangup, data);
	      } else {
	        data.retransmit = true;
	        _classPrivateMethodGet$1(this, _runRestAction$1, _runRestAction2$1).call(this, ajaxActions$1.hangup, data);
	      }
	    }
	  }, {
	    key: "sendVoiceStarted",
	    value: function sendVoiceStarted(data) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.voiceStarted, data);
	    }
	  }, {
	    key: "sendVoiceStopped",
	    value: function sendVoiceStopped(data) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.voiceStopped, data);
	    }
	  }, {
	    key: "sendMicrophoneState",
	    value: function sendMicrophoneState(microphoneState) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.microphoneState, {
	        microphoneState: microphoneState ? "Y" : "N"
	      });
	    }
	  }, {
	    key: "sendCameraState",
	    value: function sendCameraState(cameraState) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.cameraState, {
	        cameraState: cameraState ? "Y" : "N"
	      });
	    }
	  }, {
	    key: "sendScreenState",
	    value: function sendScreenState(screenState) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.screenState, {
	        screenState: screenState ? "Y" : "N"
	      });
	    }
	  }, {
	    key: "sendRecordState",
	    value: function sendRecordState(recordState) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.recordState, recordState);
	    }
	  }, {
	    key: "sendFloorRequest",
	    value: function sendFloorRequest(requestActive) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.floorRequest, {
	        requestActive: requestActive ? "Y" : "N"
	      });
	    }
	  }, {
	    key: "sendEmotion",
	    value: function sendEmotion(toUserId, emotion) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.emotion, {
	        toUserId: toUserId,
	        emotion: emotion
	      });
	    }
	  }, {
	    key: "sendCustomMessage",
	    value: function sendCustomMessage(message, repeatOnConnect) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.customMessage, {
	        message: message,
	        repeatOnConnect: !!repeatOnConnect
	      });
	    }
	  }, {
	    key: "sendShowUsers",
	    value: function sendShowUsers(users) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.showUsers, {
	        users: users
	      });
	    }
	  }, {
	    key: "sendShowAll",
	    value: function sendShowAll() {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.showAll, {});
	    }
	  }, {
	    key: "sendHideAll",
	    value: function sendHideAll() {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.hideAll, {});
	    }
	  }, {
	    key: "sendPingToUsers",
	    value: function sendPingToUsers(data) {
	      if (CallEngine.getPullClient().isPublishingEnabled()) {
	        _classPrivateMethodGet$1(this, _sendPullEvent$1, _sendPullEvent2$1).call(this, pullEvents$1.ping, data, 0);
	      }
	    }
	  }, {
	    key: "sendPingToBackend",
	    value: function sendPingToBackend() {
	      _classPrivateMethodGet$1(this, _runRestAction$1, _runRestAction2$1).call(this, ajaxActions$1.ping, {
	        retransmit: false
	      });
	    }
	  }, {
	    key: "sendUserInviteTimeout",
	    value: function sendUserInviteTimeout(data) {
	      if (CallEngine.getPullClient().isPublishingEnabled()) {
	        _classPrivateMethodGet$1(this, _sendPullEvent$1, _sendPullEvent2$1).call(this, pullEvents$1.userInviteTimeout, data, 0);
	      }
	    }
	  }, {
	    key: "sendJoinRoom",
	    value: function sendJoinRoom(roomId) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.joinRoom, {
	        roomId: roomId
	      });
	    }
	  }, {
	    key: "sendLeaveRoom",
	    value: function sendLeaveRoom(roomId) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.leaveRoom, {
	        roomId: roomId
	      });
	    }
	  }, {
	    key: "sendListRooms",
	    value: function sendListRooms() {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.listRooms);
	    }
	  }, {
	    key: "sendRequestRoomSpeaker",
	    value: function sendRequestRoomSpeaker(roomId) {
	      return _classPrivateMethodGet$1(this, _sendMessage, _sendMessage2).call(this, clientEvents.requestRoomSpeaker, {
	        roomId: roomId
	      });
	    }
	  }]);
	  return Signaling;
	}();
	function _sendPullEvent2$1(eventName, data, expiry) {
	  expiry = expiry || 5;
	  if (!data.userId) {
	    throw new Error('userId is not found in data');
	  }
	  if (!main_core.Type.isArray(data.userId)) {
	    data.userId = [data.userId];
	  }
	  if (data.userId.length === 0) {
	    // nobody to send, exit
	    return;
	  }
	  data.callInstanceId = this.call.instanceId;
	  data.senderId = this.call.userId;
	  data.callId = this.call.id;
	  data.requestId = Util$1.getUuidv4();
	  this.call.log('Sending p2p signaling event ' + eventName + '; ' + JSON.stringify(data));
	  CallEngine.getPullClient().sendMessage(data.userId, 'im', eventName, data, expiry);
	}
	function _sendMessage2(eventName, data) {
	  if (!this.call.voximplantCall) {
	    return;
	  }
	  if (!main_core.Type.isPlainObject(data)) {
	    data = {};
	  }
	  data.eventName = eventName;
	  data.requestId = Util$1.getUuidv4();
	  this.call.voximplantCall.sendMessage(JSON.stringify(data));
	}
	function _runRestAction2$1(signalName, data) {
	  if (!main_core.Type.isPlainObject(data)) {
	    data = {};
	  }
	  data.callId = this.call.id;
	  data.callInstanceId = this.call.instanceId;
	  data.requestId = Util$1.getUuidv4();
	  return CallEngine.getRestClient().callMethod(signalName, data);
	}
	var _onEndpointRemoteMediaAdded = /*#__PURE__*/new WeakMap();
	var _onEndpointRemoteMediaRemoved = /*#__PURE__*/new WeakMap();
	var _onEndpointVoiceStart = /*#__PURE__*/new WeakMap();
	var _onEndpointVoiceEnd = /*#__PURE__*/new WeakMap();
	var _onEndpointRemoved = /*#__PURE__*/new WeakMap();
	var Peer$1 = /*#__PURE__*/function () {
	  function Peer(params) {
	    var _this16 = this;
	    babelHelpers.classCallCheck(this, Peer);
	    _classPrivateFieldInitSpec(this, _onEndpointRemoteMediaAdded, {
	      writable: true,
	      value: function value(e) {
	        _this16.log("VoxImplant.EndpointEvents.RemoteMediaAdded", e.mediaRenderer);

	        // voximplant audio auto-play bug workaround:
	        if (e.mediaRenderer.element) {
	          e.mediaRenderer.element.volume = 0;
	          e.mediaRenderer.element.srcObject = null;
	        }
	        _this16.addMediaRenderer(e.mediaRenderer);
	      }
	    });
	    _classPrivateFieldInitSpec(this, _onEndpointRemoteMediaRemoved, {
	      writable: true,
	      value: function value(e) {
	        console.log("VoxImplant.EndpointEvents.RemoteMediaRemoved, ", e.mediaRenderer);
	        //this.log("VoxImplant.EndpointEvents.RemoteMediaRemoved, ", e);
	        _this16.removeMediaRenderer(e.mediaRenderer);
	      }
	    });
	    _classPrivateFieldInitSpec(this, _onEndpointVoiceStart, {
	      writable: true,
	      value: function value() {
	        _this16.callbacks.onVoiceStarted();
	      }
	    });
	    _classPrivateFieldInitSpec(this, _onEndpointVoiceEnd, {
	      writable: true,
	      value: function value() {
	        _this16.callbacks.onVoiceEnded();
	      }
	    });
	    _classPrivateFieldInitSpec(this, _onEndpointRemoved, {
	      writable: true,
	      value: function value(e) {
	        _this16.log("VoxImplant.EndpointEvents.Removed", e);
	        if (_this16.endpoint) {
	          _this16.removeEndpointEventHandlers();
	          _this16.endpoint = null;
	        }
	        if (_this16.stream) {
	          _this16.stream = null;
	        }
	        if (_this16.ready) {
	          _this16.waitForConnectionRestore();
	        }
	        _this16.updateCalculatedState();
	      }
	    });
	    this.userId = params.userId;
	    this.call = params.call;
	    this.ready = !!params.ready;
	    this.calling = false;
	    this.declined = false;
	    this.busy = false;
	    this.inviteTimeout = false;
	    this.endpoint = null;
	    this.direction = params.direction || EndpointDirection.SendRecv;
	    this.stream = null;
	    this.mediaRenderers = [];
	    this.isIncomingVideoAllowed = params.isIncomingVideoAllowed !== false;
	    this.callingTimeout = 0;
	    this.connectionRestoreTimeout = 0;
	    this.callbacks = {
	      onStateChanged: main_core.Type.isFunction(params.onStateChanged) ? params.onStateChanged : BX.DoNothing,
	      onInviteTimeout: main_core.Type.isFunction(params.onInviteTimeout) ? params.onInviteTimeout : BX.DoNothing,
	      onMediaReceived: main_core.Type.isFunction(params.onMediaReceived) ? params.onMediaReceived : BX.DoNothing,
	      onMediaRemoved: main_core.Type.isFunction(params.onMediaRemoved) ? params.onMediaRemoved : BX.DoNothing,
	      onVoiceStarted: main_core.Type.isFunction(params.onVoiceStarted) ? params.onVoiceStarted : BX.DoNothing,
	      onVoiceEnded: main_core.Type.isFunction(params.onVoiceEnded) ? params.onVoiceEnded : BX.DoNothing
	    };
	    this.calculatedState = this.calculateState();
	  }
	  babelHelpers.createClass(Peer, [{
	    key: "setReady",
	    value: function setReady(ready) {
	      ready = !!ready;
	      if (this.ready == ready) {
	        return;
	      }
	      this.ready = ready;
	      this.readyStack = new Error().stack;
	      if (this.calling) {
	        clearTimeout(this.callingTimeout);
	        this.calling = false;
	        this.inviteTimeout = false;
	      }
	      if (this.ready) {
	        this.declined = false;
	        this.busy = false;
	      } else {
	        clearTimeout(this.connectionRestoreTimeout);
	      }
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "setDirection",
	    value: function setDirection(direction) {
	      if (this.direction == direction) {
	        return;
	      }
	      this.direction = direction;
	    }
	  }, {
	    key: "setDeclined",
	    value: function setDeclined(declined) {
	      this.declined = declined;
	      if (this.calling) {
	        clearTimeout(this.callingTimeout);
	        this.calling = false;
	      }
	      if (this.declined) {
	        this.ready = false;
	        this.busy = false;
	      }
	      clearTimeout(this.connectionRestoreTimeout);
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "setBusy",
	    value: function setBusy(busy) {
	      this.busy = busy;
	      if (this.calling) {
	        clearTimeout(this.callingTimeout);
	        this.calling = false;
	      }
	      if (this.busy) {
	        this.ready = false;
	        this.declined = false;
	      }
	      clearTimeout(this.connectionRestoreTimeout);
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "setEndpoint",
	    value: function setEndpoint(endpoint) {
	      this.log("Adding endpoint with " + endpoint.mediaRenderers.length + " media renderers");
	      this.setReady(true);
	      this.inviteTimeout = false;
	      this.declined = false;
	      clearTimeout(this.connectionRestoreTimeout);
	      if (this.endpoint) {
	        this.removeEndpointEventHandlers();
	        this.endpoint = null;
	      }
	      this.endpoint = endpoint;
	      for (var i = 0; i < this.endpoint.mediaRenderers.length; i++) {
	        this.addMediaRenderer(this.endpoint.mediaRenderers[i]);
	        if (this.endpoint.mediaRenderers[i].element) ;
	      }
	      this.bindEndpointEventHandlers();
	    }
	  }, {
	    key: "allowIncomingVideo",
	    value: function allowIncomingVideo(isIncomingVideoAllowed) {
	      if (this.isIncomingVideoAllowed == isIncomingVideoAllowed) {
	        return;
	      }
	      this.isIncomingVideoAllowed = !!isIncomingVideoAllowed;
	    }
	  }, {
	    key: "addMediaRenderer",
	    value: function addMediaRenderer(mediaRenderer) {
	      this.log('Adding media renderer for user' + this.userId, mediaRenderer);
	      this.mediaRenderers.push(mediaRenderer);
	      this.callbacks.onMediaReceived({
	        userId: this.userId,
	        kind: mediaRenderer.kind,
	        mediaRenderer: mediaRenderer
	      });
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "removeMediaRenderer",
	    value: function removeMediaRenderer(mediaRenderer) {
	      this.log('Removing media renderer for user' + this.userId, mediaRenderer);
	      var i = this.mediaRenderers.indexOf(mediaRenderer);
	      if (i >= 0) {
	        this.mediaRenderers.splice(i, 1);
	      }
	      this.callbacks.onMediaRemoved({
	        userId: this.userId,
	        kind: mediaRenderer.kind,
	        mediaRenderer: mediaRenderer
	      });
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "bindEndpointEventHandlers",
	    value: function bindEndpointEventHandlers() {
	      this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, babelHelpers.classPrivateFieldGet(this, _onEndpointRemoteMediaAdded));
	      this.endpoint.addEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, babelHelpers.classPrivateFieldGet(this, _onEndpointRemoteMediaRemoved));
	      this.endpoint.addEventListener(VoxImplant.EndpointEvents.VoiceStart, babelHelpers.classPrivateFieldGet(this, _onEndpointVoiceStart));
	      this.endpoint.addEventListener(VoxImplant.EndpointEvents.VoiceEnd, babelHelpers.classPrivateFieldGet(this, _onEndpointVoiceEnd));
	      this.endpoint.addEventListener(VoxImplant.EndpointEvents.Removed, babelHelpers.classPrivateFieldGet(this, _onEndpointRemoved));
	    }
	  }, {
	    key: "removeEndpointEventHandlers",
	    value: function removeEndpointEventHandlers() {
	      this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaAdded, babelHelpers.classPrivateFieldGet(this, _onEndpointRemoteMediaAdded));
	      this.endpoint.removeEventListener(VoxImplant.EndpointEvents.RemoteMediaRemoved, babelHelpers.classPrivateFieldGet(this, _onEndpointRemoteMediaRemoved));
	      this.endpoint.removeEventListener(VoxImplant.EndpointEvents.VoiceStart, babelHelpers.classPrivateFieldGet(this, _onEndpointVoiceStart));
	      this.endpoint.removeEventListener(VoxImplant.EndpointEvents.VoiceEnd, babelHelpers.classPrivateFieldGet(this, _onEndpointVoiceEnd));
	      this.endpoint.removeEventListener(VoxImplant.EndpointEvents.Removed, babelHelpers.classPrivateFieldGet(this, _onEndpointRemoved));
	    }
	  }, {
	    key: "calculateState",
	    value: function calculateState() {
	      if (this.endpoint) {
	        return UserState.Connected;
	      }
	      if (this.calling) {
	        return UserState.Calling;
	      }
	      if (this.inviteTimeout) {
	        return UserState.Unavailable;
	      }
	      if (this.declined) {
	        return UserState.Declined;
	      }
	      if (this.busy) {
	        return UserState.Busy;
	      }
	      if (this.ready) {
	        return UserState.Ready;
	      }
	      return UserState.Idle;
	    }
	  }, {
	    key: "updateCalculatedState",
	    value: function updateCalculatedState() {
	      var calculatedState = this.calculateState();
	      if (this.calculatedState != calculatedState) {
	        this.callbacks.onStateChanged({
	          userId: this.userId,
	          state: calculatedState,
	          previousState: this.calculatedState,
	          direction: this.direction
	        });
	        this.calculatedState = calculatedState;
	      }
	    }
	  }, {
	    key: "isParticipating",
	    value: function isParticipating() {
	      return (this.calling || this.ready || this.endpoint) && !this.declined;
	    }
	  }, {
	    key: "waitForConnectionRestore",
	    value: function waitForConnectionRestore() {
	      clearTimeout(this.connectionRestoreTimeout);
	      this.connectionRestoreTimeout = setTimeout(this.onConnectionRestoreTimeout.bind(this), connectionRestoreTime);
	    }
	  }, {
	    key: "onInvited",
	    value: function onInvited() {
	      var _this17 = this;
	      this.ready = false;
	      this.inviteTimeout = false;
	      this.declined = false;
	      this.calling = true;
	      clearTimeout(this.connectionRestoreTimeout);
	      if (this.callingTimeout) {
	        clearTimeout(this.callingTimeout);
	      }
	      this.callingTimeout = setTimeout(function () {
	        return _this17.onInviteTimeout(true);
	      }, 30000);
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "onInviteTimeout",
	    value: function onInviteTimeout(internal) {
	      clearTimeout(this.callingTimeout);
	      if (!this.calling) {
	        return;
	      }
	      this.calling = false;
	      this.inviteTimeout = true;
	      if (internal) {
	        this.callbacks.onInviteTimeout({
	          userId: this.userId
	        });
	      }
	      this.updateCalculatedState();
	    }
	  }, {
	    key: "onConnectionRestoreTimeout",
	    value: function onConnectionRestoreTimeout() {
	      if (this.endpoint || !this.ready) {
	        return;
	      }
	      this.log("Done waiting for connection restoration");
	      this.setReady(false);
	    }
	  }, {
	    key: "log",
	    value: function log() {
	      this.call.log.apply(this.call, arguments);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.stream) {
	        Util$1.stopMediaStream(this.stream);
	        this.stream = null;
	      }
	      if (this.endpoint) {
	        this.removeEndpointEventHandlers();
	        this.endpoint = null;
	      }
	      this.callbacks.onStateChanged = BX.DoNothing;
	      this.callbacks.onInviteTimeout = BX.DoNothing;
	      this.callbacks.onMediaReceived = BX.DoNothing;
	      this.callbacks.onMediaRemoved = BX.DoNothing;
	      clearTimeout(this.callingTimeout);
	      clearTimeout(this.connectionRestoreTimeout);
	      this.callingTimeout = null;
	      this.connectionRestoreTimeout = null;
	    }
	  }]);
	  return Peer;
	}();
	var transformVoxStats = function transformVoxStats(s, voximplantCall) {
	  var result = {
	    connection: s.connection,
	    outboundAudio: [],
	    outboundVideo: [],
	    inboundAudio: [],
	    inboundVideo: []
	  };
	  var endpoints = {};
	  if (voximplantCall.getEndpoints) {
	    voximplantCall.getEndpoints().forEach(function (endpoint) {
	      return endpoints[endpoint.id] = endpoint;
	    });
	  }
	  if (!result.connection.timestamp) {
	    result.connection.timestamp = Date.now();
	  }
	  for (var trackId in s.outbound) {
	    if (!s.outbound.hasOwnProperty(trackId)) {
	      continue;
	    }
	    var statGroup = s.outbound[trackId];
	    for (var i = 0; i < statGroup.length; i++) {
	      var stat = statGroup[i];
	      stat.trackId = trackId;
	      if ('audioLevel' in stat) {
	        result.outboundAudio.push(stat);
	      } else {
	        result.outboundVideo.push(stat);
	      }
	    }
	  }
	  var _loop = function _loop() {
	    if (!s.inbound.hasOwnProperty(_trackId)) {
	      return "continue";
	    }
	    var stat = s.inbound[_trackId];
	    if (!('endpoint' in stat)) {
	      return "continue";
	    }
	    stat.trackId = _trackId;
	    if ('audioLevel' in stat) {
	      result.inboundAudio.push(stat);
	    } else {
	      if (endpoints[stat.endpoint]) {
	        var videoRenderer = endpoints[stat.endpoint].mediaRenderers.find(function (r) {
	          return r.id == stat.trackId;
	        });
	        if (videoRenderer && videoRenderer.element) {
	          stat.actualHeight = videoRenderer.element.videoHeight;
	          stat.actualWidth = videoRenderer.element.videoWidth;
	        }
	      }
	      result.inboundVideo.push(stat);
	    }
	  };
	  for (var _trackId in s.inbound) {
	    var _ret = _loop();
	    if (_ret === "continue") continue;
	  }
	  return result;
	};

	var CallStub = /*#__PURE__*/function () {
	  function CallStub(config) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, CallStub);
	    this.callId = config.callId;
	    this.lifetime = config.lifetime || 120;
	    this.callbacks = {
	      onDelete: main_core.Type.isFunction(config.onDelete) ? config.onDelete : BX.DoNothing
	    };
	    this.deleteTimeout = setTimeout(function () {
	      _this.callbacks.onDelete({
	        callId: _this.callId
	      });
	    }, this.lifetime * 1000);
	  }
	  babelHelpers.createClass(CallStub, [{
	    key: "__onPullEvent",
	    value: function __onPullEvent(command, params, extra) {
	      // do nothing
	    }
	  }, {
	    key: "isAnyoneParticipating",
	    value: function isAnyoneParticipating() {
	      return false;
	    }
	  }, {
	    key: "addEventListener",
	    value: function addEventListener() {
	      return false;
	    }
	  }, {
	    key: "removeEventListener",
	    value: function removeEventListener() {
	      return false;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      clearTimeout(this.deleteTimeout);
	      this.callbacks.onDelete = BX.DoNothing;
	    }
	  }]);
	  return CallStub;
	}();

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var CallState = {
	  Idle: 'Idle',
	  Proceeding: 'Proceeding',
	  Connected: 'Connected',
	  Finished: 'Finished'
	};
	var UserState = {
	  Idle: 'Idle',
	  Busy: 'Busy',
	  Calling: 'Calling',
	  Unavailable: 'Unavailable',
	  Declined: 'Declined',
	  Ready: 'Ready',
	  Connecting: 'Connecting',
	  Connected: 'Connected',
	  Failed: 'Failed'
	};
	var EndpointDirection = {
	  SendOnly: 'send',
	  RecvOnly: 'recv',
	  SendRecv: 'sendrecv'
	};
	var CallType = {
	  Instant: 1,
	  Permanent: 2
	};
	var Provider = {
	  Plain: 'Plain',
	  Voximplant: 'Voximplant'
	};
	var Direction = {
	  Incoming: 'Incoming',
	  Outgoing: 'Outgoing'
	};
	var Quality = {
	  VeryHigh: "very_high",
	  High: "high",
	  Medium: "medium",
	  Low: "low",
	  VeryLow: "very_low"
	};
	var UserMnemonic = {
	  all: 'all',
	  none: 'none'
	};
	var CallEvent = {
	  onUserInvited: 'onUserInvited',
	  onUserStateChanged: 'onUserStateChanged',
	  onUserMicrophoneState: 'onUserMicrophoneState',
	  onUserCameraState: 'onUserCameraState',
	  onUserVideoPaused: 'onUserVideoPaused',
	  onUserScreenState: 'onUserScreenState',
	  onUserRecordState: 'onUserRecordState',
	  onUserVoiceStarted: 'onUserVoiceStarted',
	  onUserVoiceStopped: 'onUserVoiceStopped',
	  onUserFloorRequest: 'onUserFloorRequest',
	  // request for a permission to speak
	  onUserEmotion: 'onUserEmotion',
	  onCustomMessage: 'onCustomMessage',
	  onLocalMediaReceived: 'onLocalMediaReceived',
	  onLocalMediaStopped: 'onLocalMediaStopped',
	  onMicrophoneLevel: 'onMicrophoneLevel',
	  onDeviceListUpdated: 'onDeviceListUpdated',
	  onRTCStatsReceived: 'onRTCStatsReceived',
	  onCallFailure: 'onCallFailure',
	  onRemoteMediaReceived: 'onRemoteMediaReceived',
	  onRemoteMediaStopped: 'onRemoteMediaStopped',
	  onNetworkProblem: 'onNetworkProblem',
	  onReconnecting: 'onReconnecting',
	  onReconnected: 'onReconnected',
	  onJoin: 'onJoin',
	  onLeave: 'onLeave',
	  onJoinRoomOffer: 'onJoinRoomOffer',
	  onJoinRoom: 'onJoinRoom',
	  onLeaveRoom: 'onLeaveRoom',
	  onListRooms: 'onListRooms',
	  onUpdateRoom: 'onUpdateRoom',
	  onTransferRoomSpeakerRequest: 'onTransferRoomSpeakerRequest',
	  onTransferRoomSpeaker: 'onTransferRoomSpeaker',
	  onDestroy: 'onDestroy'
	};
	var ajaxActions$2 = {
	  createCall: 'im.call.create',
	  createChildCall: 'im.call.createChildCall',
	  getPublicChannels: 'pull.channel.public.list',
	  getCall: 'im.call.get'
	};
	var _instantiateCall = /*#__PURE__*/new WeakSet();
	var _onPullEvent = /*#__PURE__*/new WeakSet();
	var _onPullClientEvent = /*#__PURE__*/new WeakSet();
	var _onPullIncomingCall = /*#__PURE__*/new WeakSet();
	var _onUnknownCallPing = /*#__PURE__*/new WeakSet();
	var _onCallDestroy = /*#__PURE__*/new WeakSet();
	var _isCallAppInitialized = /*#__PURE__*/new WeakSet();
	var _getCallFactory = /*#__PURE__*/new WeakSet();
	var Engine = /*#__PURE__*/function () {
	  function Engine() {
	    babelHelpers.classCallCheck(this, Engine);
	    _classPrivateMethodInitSpec$2(this, _getCallFactory);
	    _classPrivateMethodInitSpec$2(this, _isCallAppInitialized);
	    _classPrivateMethodInitSpec$2(this, _onCallDestroy);
	    _classPrivateMethodInitSpec$2(this, _onUnknownCallPing);
	    _classPrivateMethodInitSpec$2(this, _onPullIncomingCall);
	    _classPrivateMethodInitSpec$2(this, _onPullClientEvent);
	    _classPrivateMethodInitSpec$2(this, _onPullEvent);
	    _classPrivateMethodInitSpec$2(this, _instantiateCall);
	    babelHelpers.defineProperty(this, "handlers", {
	      'Call::incoming': _classPrivateMethodGet$2(this, _onPullIncomingCall, _onPullIncomingCall2).bind(this)
	    });
	    this.debugFlag = false;
	    this.calls = {};
	    this.userId = Number(BX.message('USER_ID'));
	    this.siteId = '';
	    this.unknownCalls = {};
	    this.restClient = null;
	    this.pullClient = null;
	    this.init();
	  }
	  babelHelpers.createClass(Engine, [{
	    key: "init",
	    value: function init() {
	      BX.addCustomEvent("onPullEvent-im", _classPrivateMethodGet$2(this, _onPullEvent, _onPullEvent2).bind(this));
	      BX.addCustomEvent("onPullClientEvent-im", _classPrivateMethodGet$2(this, _onPullClientEvent, _onPullClientEvent2).bind(this));
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return this.siteId || BX.message('SITE_ID') || '';
	    }
	  }, {
	    key: "setSiteId",
	    value: function setSiteId(siteId) {
	      this.siteId = siteId;
	    }
	  }, {
	    key: "getCurrentUserId",
	    value: function getCurrentUserId() {
	      return this.userId;
	    }
	  }, {
	    key: "setCurrentUserId",
	    value: function setCurrentUserId(userId) {
	      this.userId = Number(userId);
	    }
	  }, {
	    key: "setRestClient",
	    value: function setRestClient(restClient) {
	      this.restClient = restClient;
	    }
	  }, {
	    key: "setPullClient",
	    value: function setPullClient(pullClient) {
	      this.pullClient = pullClient;
	    }
	  }, {
	    key: "getRestClient",
	    value: function getRestClient() {
	      return this.restClient || BX.rest;
	    }
	  }, {
	    key: "getPullClient",
	    value: function getPullClient() {
	      return this.pullClient || BX.PULL;
	    }
	  }, {
	    key: "getLogService",
	    value: function getLogService() {
	      return BX.message("call_log_service");
	    }
	  }, {
	    key: "createCall",
	    value: function createCall(config) {
	      var _this = this;
	      return new Promise(function (resolve, reject) {
	        var callType = config.type || CallType.Instant;
	        var callProvider = config.provider || _this.getDefaultProvider();
	        if (config.joinExisting) {
	          for (var callId in _this.calls) {
	            if (_this.calls.hasOwnProperty(callId)) {
	              var call = _this.calls[callId];
	              if (call.provider == config.provider && call.associatedEntity.type == config.entityType && call.associatedEntity.id == config.entityId) {
	                _this.log(callId, "Found existing call, attaching to it");
	                return resolve({
	                  call: call,
	                  isNew: false
	                });
	              }
	            }
	          }
	        }
	        var callParameters = {
	          type: callType,
	          provider: callProvider,
	          entityType: config.entityType,
	          entityId: config.entityId,
	          joinExisting: !!config.joinExisting,
	          userIds: main_core.Type.isArray(config.userIds) ? config.userIds : []
	        };
	        _this.getRestClient().callMethod(ajaxActions$2.createCall, callParameters).then(function (response) {
	          if (response.error()) {
	            var error = response.error().getError();
	            return reject({
	              code: error.error,
	              message: error.error_description
	            });
	          }
	          var createCallResponse = response.data();
	          if (createCallResponse.userData) {
	            Util$1.setUserData(createCallResponse.userData);
	          }
	          if (createCallResponse.publicChannels) {
	            _this.getPullClient().setPublicIds(Object.values(createCallResponse.publicChannels));
	          }
	          var callFields = createCallResponse.call;
	          if (_this.calls[callFields['ID']]) {
	            if (_this.calls[callFields['ID']] instanceof CallStub) {
	              _this.calls[callFields['ID']].destroy();
	            } else {
	              console.error("Call " + callFields['ID'] + " already exists");
	              return resolve({
	                call: _this.calls[callFields['ID']],
	                isNew: false
	              });
	            }
	          }
	          var callFactory = _classPrivateMethodGet$2(_this, _getCallFactory, _getCallFactory2).call(_this, callFields['PROVIDER']);
	          var call = callFactory.createCall({
	            id: parseInt(callFields['ID']),
	            instanceId: Util$1.getUuidv4(),
	            direction: Direction.Outgoing,
	            users: createCallResponse.users,
	            videoEnabled: config.videoEnabled === true,
	            enableMicAutoParameters: config.enableMicAutoParameters !== false,
	            associatedEntity: callFields.ASSOCIATED_ENTITY,
	            type: callFields.TYPE,
	            startDate: callFields.START_DATE,
	            events: {
	              onDestroy: _classPrivateMethodGet$2(_this, _onCallDestroy, _onCallDestroy2).bind(_this)
	            },
	            debug: config.debug === true,
	            logToken: createCallResponse.logToken
	          });
	          _this.calls[callFields['ID']] = call;
	          if (createCallResponse.isNew) {
	            _this.log(call.id, "Creating new call");
	          } else {
	            _this.log(call.id, "Server returned existing call, attaching to it");
	          }
	          BX.onCustomEvent(window, "CallEvents::callCreated", [{
	            call: call
	          }]);
	          resolve({
	            call: call,
	            isNew: createCallResponse.isNew
	          });
	        })["catch"](function (error) {
	          if (main_core.Type.isFunction(error.error)) {
	            error = error.error().getError();
	          }
	          reject({
	            code: error.error,
	            message: error.error_description
	          });
	        });
	      });
	    }
	  }, {
	    key: "createChildCall",
	    value: function createChildCall(parentId, newProvider, newUsers) {
	      var _this2 = this;
	      if (!this.calls[parentId]) {
	        return Promise.reject('Parent call is not found');
	      }
	      return new Promise(function (resolve) {
	        var parentCall = _this2.calls[parentId];
	        var callParameters = {
	          parentId: parentId,
	          newProvider: newProvider,
	          newUsers: newUsers
	        };
	        _this2.getRestClient().callMethod(ajaxActions$2.createChildCall, callParameters, function (response) {
	          var createCallResponse = response.data();
	          var callFields = createCallResponse.call;
	          var callFactory = _classPrivateMethodGet$2(_this2, _getCallFactory, _getCallFactory2).call(_this2, callFields['PROVIDER']);
	          var call = callFactory.createCall({
	            id: parseInt(callFields['ID']),
	            instanceId: Util$1.getUuidv4(),
	            parentId: callFields['PARENT_ID'],
	            direction: Direction.Outgoing,
	            users: createCallResponse.users,
	            videoEnabled: parentCall.isVideoEnabled(),
	            enableMicAutoParameters: parentCall.enableMicAutoParameters !== false,
	            associatedEntity: callFields.ASSOCIATED_ENTITY,
	            type: callFields.TYPE,
	            startDate: callFields.START_DATE,
	            events: {
	              onDestroy: _classPrivateMethodGet$2(_this2, _onCallDestroy, _onCallDestroy2).bind(_this2)
	            },
	            logToken: createCallResponse.logToken
	          });
	          _this2.calls[callFields['ID']] = call;
	          BX.onCustomEvent(window, "CallEvents::callCreated", [{
	            call: call
	          }]);
	          resolve({
	            call: call,
	            isNew: createCallResponse.isNew
	          });
	        });
	      });
	    }
	  }, {
	    key: "getCallWithId",
	    value: function getCallWithId(id) {
	      var _this3 = this;
	      if (this.calls[id]) {
	        return Promise.resolve({
	          call: this.calls[id],
	          isNew: false
	        });
	      }
	      return new Promise(function (resolve, reject) {
	        _this3.getRestClient().callMethod(ajaxActions$2.getCall, {
	          callId: id
	        }).then(function (answer) {
	          var data = answer.data();
	          resolve({
	            call: _classPrivateMethodGet$2(_this3, _instantiateCall, _instantiateCall2).call(_this3, data.call, data.users, data.logToken),
	            isNew: false
	          });
	        })["catch"](function (error) {
	          console.error(error);
	          if (main_core.Type.isFunction(error.error)) {
	            error = error.error().getError();
	          }
	          reject({
	            code: error.error,
	            message: error.error_description
	          });
	        });
	      });
	    }
	  }, {
	    key: "getDefaultProvider",
	    value: function getDefaultProvider() {
	      return Provider.Plain;
	    }
	  }, {
	    key: "getConferencePageTag",
	    value: function getConferencePageTag(chatDialogId) {
	      return "conference-open-" + chatDialogId;
	    }
	  }, {
	    key: "debug",
	    value: function debug() {
	      var debugFlag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.debugFlag = !!debugFlag;
	      return this.debugFlag;
	    }
	  }, {
	    key: "log",
	    value: function log() {
	      var text = Util$1.getLogMessage.call(Util$1, arguments);
	      if (BX.desktop && BX.desktop.ready()) {
	        BX.desktop.log(BX.message('USER_ID') + '.video.log', text);
	      }
	      if (this.debugFlag) {
	        if (console) {
	          var a = ['Call log [' + Util$1.getTimeForLog() + ']: '];
	          console.log.apply(this, a.concat(Array.prototype.slice.call(arguments)));
	        }
	      }
	    }
	  }, {
	    key: "getAllowedVideoQuality",
	    value: function getAllowedVideoQuality(participantsCount) {
	      if (participantsCount < 5) {
	        return Quality.VeryHigh;
	      } else if (participantsCount < 10) {
	        return Quality.High;
	      } else if (participantsCount < 16) {
	        return Quality.Medium;
	      } else if (participantsCount < 32) {
	        return Quality.Low;
	      } else {
	        return Quality.VeryLow;
	      }
	    }
	  }]);
	  return Engine;
	}();
	function _instantiateCall2(callFields, users, logToken) {
	  if (this.calls[callFields['ID']]) {
	    console.error("Call " + callFields['ID'] + " already exists");
	    return this.calls[callFields['ID']];
	  }
	  var callFactory = _classPrivateMethodGet$2(this, _getCallFactory, _getCallFactory2).call(this, callFields['PROVIDER']);
	  var call = callFactory.createCall({
	    id: parseInt(callFields['ID']),
	    instanceId: Util$1.getUuidv4(),
	    initiatorId: parseInt(callFields['INITIATOR_ID']),
	    parentId: callFields['PARENT_ID'],
	    direction: callFields['INITIATOR_ID'] == this.userId ? Direction.Outgoing : Direction.Incoming,
	    users: users,
	    associatedEntity: callFields.ASSOCIATED_ENTITY,
	    type: callFields.TYPE,
	    startDate: callFields['START_DATE'],
	    logToken: logToken,
	    events: {
	      onDestroy: _classPrivateMethodGet$2(this, _onCallDestroy, _onCallDestroy2).bind(this)
	    }
	  });
	  this.calls[callFields['ID']] = call;
	  BX.onCustomEvent(window, "CallEvents::callCreated", [{
	    call: call
	  }]);
	  return call;
	}
	function _onPullEvent2(command, params, extra) {
	  var _this4 = this;
	  if (command.startsWith('Call::')) {
	    if (params.publicIds) {
	      this.getPullClient().setPublicIds(Object.values(params.publicIds));
	    }
	    if (params.userData) {
	      Util$1.setUserData(params.userData);
	    }
	  }
	  if (this.handlers[command]) {
	    this.handlers[command].call(this, params, extra);
	  } else if (command.startsWith('Call::') && (params['call'] || params['callId'])) {
	    var callId = params['call'] ? params['call']['ID'] : params['callId'];
	    if (this.calls[callId]) {
	      this.calls[callId].__onPullEvent(command, params, extra);
	    } else if (command === 'Call::ping') {
	      _classPrivateMethodGet$2(this, _onUnknownCallPing, _onUnknownCallPing2).call(this, params, extra).then(function (result) {
	        if (result && _this4.calls[callId]) {
	          _this4.calls[callId].__onPullEvent(command, params, extra);
	        }
	      });
	    }
	  }
	}
	function _onPullClientEvent2(command, params, extra) {
	  var _this5 = this;
	  if (command.startsWith('Call::') && params['callId']) {
	    var callId = params['callId'];
	    if (this.calls[callId]) {
	      this.calls[callId].__onPullEvent(command, params, extra);
	    } else if (command === 'Call::ping') {
	      _classPrivateMethodGet$2(this, _onUnknownCallPing, _onUnknownCallPing2).call(this, params, extra).then(function (result) {
	        if (result && _this5.calls[callId]) {
	          _this5.calls[callId].__onPullEvent(command, params, extra);
	        }
	      });
	    }
	  }
	}
	function _onPullIncomingCall2(params, extra) {
	  if (extra.server_time_ago > 30) {
	    console.error("Call was started too long time ago");
	    return;
	  }
	  var callFields = params.call;
	  var callId = parseInt(callFields.ID);
	  var call;
	  if (params.publicIds) {
	    this.getPullClient().setPublicIds(Object.values(params.publicIds));
	  }
	  if (params.userData) {
	    Util$1.setUserData(params.userData);
	  }
	  if (this.calls[callId]) {
	    call = this.calls[callId];
	  } else {
	    var callFactory = _classPrivateMethodGet$2(this, _getCallFactory, _getCallFactory2).call(this, callFields.PROVIDER);
	    call = callFactory.createCall({
	      id: callId,
	      instanceId: Util$1.getUuidv4(),
	      parentId: callFields.PARENT_ID || null,
	      callFromMobile: params.isLegacyMobile === true,
	      direction: Direction.Incoming,
	      users: params.users,
	      initiatorId: params.senderId,
	      associatedEntity: callFields.ASSOCIATED_ENTITY,
	      type: callFields.TYPE,
	      startDate: callFields.START_DATE,
	      logToken: params.logToken,
	      events: {
	        onDestroy: _classPrivateMethodGet$2(this, _onCallDestroy, _onCallDestroy2).bind(this)
	      }
	    });
	    this.calls[callId] = call;
	    BX.onCustomEvent(window, "CallEvents::callCreated", [{
	      call: call
	    }]);
	  }
	  call.addInvitedUsers(params.invitedUsers);
	  if (call) {
	    BX.onCustomEvent(window, "CallEvents::incomingCall", [{
	      call: call,
	      video: params.video === true,
	      isLegacyMobile: params.isLegacyMobile === true
	    }]);
	  }
	  this.log(call.id, "Incoming call " + call.id);
	}
	function _onUnknownCallPing2(params, extra) {
	  var _this6 = this;
	  var callId = Number(params.callId);
	  if (extra.server_time_ago > 10) {
	    this.log(callId, "Error: Ping was sent too long time ago");
	    return Promise.resolve(false);
	  }
	  if (!_classPrivateMethodGet$2(this, _isCallAppInitialized, _isCallAppInitialized2).call(this)) {
	    return Promise.resolve(false);
	  }
	  if (this.unknownCalls[callId]) {
	    return Promise.resolve(false);
	  }
	  this.unknownCalls[callId] = true;
	  if (params.userData) {
	    Util$1.setUserData(params.userData);
	  }
	  return new Promise(function (resolve) {
	    _this6.getCallWithId(callId).then(function () {
	      _this6.unknownCalls[callId] = false;
	      resolve(true);
	    })["catch"](function (error) {
	      _this6.unknownCalls[callId] = false;
	      _this6.log(callId, "Error: Could not instantiate call", error);
	      resolve(false);
	    });
	  });
	}
	function _onCallDestroy2(e) {
	  var _this7 = this;
	  var callId = e.call.id;
	  this.calls[callId] = new CallStub({
	    callId: callId,
	    onDelete: function onDelete() {
	      if (_this7.calls[callId]) {
	        delete _this7.calls[callId];
	      }
	    }
	  });
	  BX.onCustomEvent(window, "CallEvents::callDestroyed", [{
	    callId: e.call.id
	  }]);
	}
	function _isCallAppInitialized2() {
	  if ('BXIM' in window && 'init' in window.BXIM) {
	    return BXIM.init;
	  } else if (BX.Messenger && BX.Messenger.Application && BX.Messenger.Application.conference) {
	    return BX.Messenger.Application.conference.inited;
	  }

	  //TODO: support new chat
	  return true;
	}
	function _getCallFactory2(providerType) {
	  if (providerType == Provider.Plain) {
	    return PlainCallFactory;
	  } else if (providerType == Provider.Voximplant) {
	    return VoximplantCallFactory;
	  }
	  throw new Error("Unknown call provider type " + providerType);
	}
	var PlainCallFactory = /*#__PURE__*/function () {
	  function PlainCallFactory() {
	    babelHelpers.classCallCheck(this, PlainCallFactory);
	  }
	  babelHelpers.createClass(PlainCallFactory, null, [{
	    key: "createCall",
	    value: function createCall(config) {
	      return new PlainCall(config);
	    }
	  }]);
	  return PlainCallFactory;
	}();
	var VoximplantCallFactory = /*#__PURE__*/function () {
	  function VoximplantCallFactory() {
	    babelHelpers.classCallCheck(this, VoximplantCallFactory);
	  }
	  babelHelpers.createClass(VoximplantCallFactory, null, [{
	    key: "createCall",
	    value: function createCall(config) {
	      return new VoximplantCall(config);
	    }
	  }]);
	  return VoximplantCallFactory;
	}();
	var CallEngine = new Engine();

	var blankAvatar = '/bitrix/js/im/images/blank.gif';
	var userData = {};
	var usersInProcess = {};
	function updateUserData(callId, users) {
	  var usersToUpdate = [];
	  for (var i = 0; i < users.length; i++) {
	    if (userData.hasOwnProperty(users[i])) {
	      continue;
	    }
	    usersToUpdate.push(users[i]);
	  }
	  var result = new Promise(function (resolve, reject) {
	    if (usersToUpdate.length === 0) {
	      return resolve();
	    }
	    CallEngine.getRestClient().callMethod("im.call.getUsers", {
	      callId: callId,
	      userIds: usersToUpdate
	    }).then(function (response) {
	      var result = main_core.Type.isPlainObject(response.answer.result) ? response.answer.result : {};
	      users.forEach(function (userId) {
	        if (result[userId]) {
	          userData[userId] = result[userId];
	        }
	        delete usersInProcess[userId];
	      });
	      resolve();
	    })["catch"](function (error) {
	      reject(error.answer);
	    });
	  });
	  for (var _i = 0; _i < usersToUpdate.length; _i++) {
	    usersInProcess[usersToUpdate[_i]] = result;
	  }
	  return result;
	}
	function setUserData(userData) {
	  for (var userId in userData) {
	    userData[userId] = userData[userId];
	  }
	}
	var getDateForLog = function getDateForLog() {
	  var d = new Date();
	  return d.getFullYear() + "-" + lpad(d.getMonth() + 1, 2, '0') + "-" + lpad(d.getDate(), 2, '0') + " " + lpad(d.getHours(), 2, '0') + ":" + lpad(d.getMinutes(), 2, '0') + ":" + lpad(d.getSeconds(), 2, '0') + "." + d.getMilliseconds();
	};
	var getTimeForLog = function getTimeForLog() {
	  var d = new Date();
	  return lpad(d.getHours(), 2, '0') + ":" + lpad(d.getMinutes(), 2, '0') + ":" + lpad(d.getSeconds(), 2, '0') + "." + d.getMilliseconds();
	};
	function lpad(str, length, chr) {
	  str = str.toString();
	  chr = chr || ' ';
	  if (str.length > length) {
	    return str;
	  }
	  var result = '';
	  for (var i = 0; i < length - str.length; i++) {
	    result += chr;
	  }
	  return result + str;
	}
	function getUser(callId, userId) {
	  return new Promise(function (resolve, reject) {
	    if (userData.hasOwnProperty(userId)) {
	      return resolve(userData[userId]);
	    } else if (usersInProcess.hasOwnProperty(userId)) {
	      usersInProcess[userId].then(function () {
	        return resolve(userData[userId]);
	      });
	    } else {
	      updateUserData(callId, [userId]).then(function () {
	        return resolve(userData[userId]);
	      });
	    }
	  });
	}
	function getUserCached(userId) {
	  return userData.hasOwnProperty(userId) ? userData[userId] : null;
	}
	function getUsers(callId, users) {
	  return new Promise(function (resolve, reject) {
	    updateUserData(callId, users).then(function () {
	      var result = {};
	      users.forEach(function (userId) {
	        return result[userId] = userData[userId] || {};
	      });
	      return resolve(result);
	    });
	  });
	}
	function getUserName(callId, userId) {
	  return new Promise(function (resolve, reject) {
	    if (userData.hasOwnProperty(userId)) {
	      return resolve(userData[userId].name ? userData[userId].name : '');
	    } else if (usersInProcess.hasOwnProperty(userId)) {
	      usersInProcess[userId].then(function () {
	        return resolve(userData[userId].name ? userData[userId].name : '');
	      });
	    } else {
	      updateUserData(callId, [userId]).then(function () {
	        return resolve(userData[userId].name ? userData[userId].name : '');
	      });
	    }
	  });
	}
	function getUserAvatar(callId, userId) {
	  return new Promise(function (resolve, reject) {
	    if (userData.hasOwnProperty(userId)) {
	      return resolve(userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : '');
	    } else if (usersInProcess.hasOwnProperty(userId)) {
	      usersInProcess[userId].then(function () {
	        return resolve(userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : '');
	      });
	    } else {
	      updateUserData(callId, [userId]).then(function () {
	        return resolve(userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : '');
	      });
	    }
	  });
	}
	function getUserAvatars(callId, users) {
	  return new Promise(function (resolve, reject) {
	    updateUserData(callId, users).then(function () {
	      var result = {};
	      users.forEach(function (userId) {
	        result[userId] = userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : '';
	      });
	      return resolve(result);
	    });
	  });
	}
	function isAvatarBlank(url) {
	  return isBlank(url);
	}
	function getCustomMessage(message, userData) {
	  var messageText;
	  if (!main_core.Type.isPlainObject(userData)) {
	    userData = {};
	  }
	  if (userData.gender && BX.message.hasOwnProperty(message + '_' + userData.gender)) {
	    messageText = BX.message(message + '_' + userData.gender);
	  } else {
	    messageText = BX.message(message);
	  }
	  userData = convertKeysToUpper(userData);
	  return messageText.replace(/#.+?#/gm, function (match) {
	    var placeHolder = match.substr(1, match.length - 2);
	    return userData.hasOwnProperty(placeHolder) ? userData[placeHolder] : match;
	  });
	}
	function convertKeysToUpper(obj) {
	  var result = BX.util.objectClone(obj);
	  for (var k in result) {
	    var u = k.toUpperCase();
	    if (u != k) {
	      result[u] = result[k];
	      delete result[k];
	    }
	  }
	  return result;
	}
	function appendChildren(parent, children) {
	  children.forEach(function (child) {
	    return parent.appendChild(child);
	  });
	}
	function containsVideoTrack(stream) {
	  if (!(stream instanceof MediaStream)) {
	    return false;
	  }
	  return stream.getVideoTracks().length > 0;
	}
	function hasHdVideo(stream) {
	  if (!(stream instanceof MediaStream) || stream.getVideoTracks().length === 0) {
	    return false;
	  }
	  var videoTrack = stream.getVideoTracks()[0];
	  var trackSettings = videoTrack.getSettings();
	  return trackSettings.width >= 1280;
	}
	function findBestElementSize(width, height, userCount, minWidth, minHeight) {
	  minWidth = minWidth || 0;
	  minHeight = minHeight || 0;
	  var bestFilledArea = 0;
	  for (var i = 1; i <= userCount; i++) {
	    var area = getFilledArea(width, height, userCount, i);
	    if (area.area > bestFilledArea && area.elementWidth > minWidth && area.elementHeight > minHeight) {
	      bestFilledArea = area.area;
	      var bestWidth = area.elementWidth;
	      var bestHeight = area.elementHeight;
	    }
	    if (area.area < bestFilledArea) {
	      break;
	    }
	  }
	  if (bestFilledArea === 0) {
	    bestWidth = minWidth;
	    bestHeight = minHeight;
	  }
	  return {
	    width: bestWidth,
	    height: bestHeight
	  };
	}
	function getFilledArea(width, height, userCount, rowCount) {
	  var columnCount = Math.ceil(userCount / rowCount);
	  var maxElementWidth = Math.floor(width / columnCount);
	  var maxElementHeight = Math.floor(height / rowCount);
	  var ratio = maxElementHeight / maxElementWidth;
	  var neededRatio = 9 / 16;
	  var expectedElementHeight;
	  var expectedElementWidth;
	  if (ratio < neededRatio) {
	    expectedElementHeight = maxElementHeight;
	    expectedElementWidth = Math.floor(maxElementWidth * (ratio / neededRatio));
	  } else {
	    expectedElementWidth = maxElementWidth;
	    expectedElementHeight = Math.floor(maxElementHeight * (neededRatio / ratio));
	  }

	  //console.log(expectedElementWidth + 'x' + expectedElementHeight)
	  var area = expectedElementWidth * expectedElementHeight * userCount;
	  return {
	    area: area,
	    elementWidth: expectedElementWidth,
	    elementHeight: expectedElementHeight
	  };
	}
	var isWebRTCSupported = function isWebRTCSupported() {
	  return typeof webkitRTCPeerConnection != 'undefined' || typeof mozRTCPeerConnection != 'undefined' || typeof RTCPeerConnection != 'undefined';
	};
	var isCallServerAllowed = function isCallServerAllowed() {
	  return BX.message('call_server_enabled') === 'Y';
	};
	var isFeedbackAllowed = function isFeedbackAllowed() {
	  return BX.message('call_allow_feedback') === 'Y';
	};
	var shouldCollectStats = function shouldCollectStats() {
	  return BX.message('call_collect_stats') === 'Y';
	};
	var shouldShowDocumentButton = function shouldShowDocumentButton() {
	  return BX.message('call_docs_status') !== 'N' || BX.message('call_resumes_status') !== 'N';
	};
	var getDocumentsArticleCode = function getDocumentsArticleCode() {
	  if (!BX.message('call_docs_status').startsWith('L')) {
	    return false;
	  }
	  return BX.message('call_docs_status').substr(2);
	};
	var getResumesArticleCode = function getResumesArticleCode() {
	  if (!BX.message('call_resumes_status').startsWith('L')) {
	    return false;
	  }
	  return BX.message('call_resumes_status').substr(2);
	};
	var getUserLimit = function getUserLimit() {
	  if (isCallServerAllowed()) {
	    return parseInt(BX.message('call_server_max_users'));
	  }
	  return parseInt(BX.message('turn_server_max_users'));
	};
	function getLogMessage() {
	  var text = getDateForLog();
	  for (var i = 0; i < arguments.length; i++) {
	    if (arguments[i] instanceof Error) {
	      text = arguments[i].message + "\n" + arguments[i].stack;
	    } else {
	      try {
	        text = text + ' | ' + (babelHelpers["typeof"](arguments[i]) == 'object' ? JSON.stringify(arguments[i]) : arguments[i]);
	      } catch (e) {
	        text = text + ' | (circular structure)';
	      }
	    }
	  }
	  return text;
	}
	var getUuidv4 = function getUuidv4() {
	  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
	    var r = Math.random() * 16 | 0,
	      v = c == 'x' ? r : r & 0x3 | 0x8;
	    return v.toString(16);
	  });
	};
	function reportConnectionResult(callId, connectionResult) {
	  BX.ajax.runAction("im.call.reportConnection", {
	    data: {
	      callId: callId,
	      connectionResult: connectionResult
	    }
	  });
	}
	function sendTelemetryEvent(options) {
	  var url = (document.location.protocol == "https:" ? "https://" : "http://") + "bitrix.info/bx_stat";
	  var req = new XMLHttpRequest();
	  req.open("POST", url, true);
	  req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	  req.withCredentials = true;
	  options.op = "call";
	  options.d = document.location.host;
	  var query = BX.util.buildQueryString(options);
	  req.send(query);
	}
	var isDesktop = function isDesktop() {
	  return typeof BXDesktopSystem != "undefined" || typeof BXDesktopWindow != "undefined";
	};
	var getBrowserForStatistics = function getBrowserForStatistics() {
	  if (BX.browser.IsOpera()) {
	    return 'opera';
	  }
	  if (BX.browser.IsChrome()) {
	    return 'chrome';
	  }
	  if (BX.browser.IsFirefox()) {
	    return 'firefox';
	  }
	  if (BX.browser.IsSafari()) {
	    return 'safari';
	  }
	  return 'other';
	};
	function isBlank(url) {
	  return typeof url !== "string" || url == "" || url.endsWith(blankAvatar);
	}
	function stopMediaStream(mediaStream) {
	  if (!mediaStream instanceof MediaStream) {
	    return;
	  }
	  mediaStream.getTracks().forEach(function (track) {
	    track.stop();
	  });
	}
	var Util$1 = {
	  updateUserData: updateUserData,
	  setUserData: setUserData,
	  getDateForLog: getDateForLog,
	  getTimeForLog: getTimeForLog,
	  lpad: lpad,
	  getUser: getUser,
	  getUserCached: getUserCached,
	  getUsers: getUsers,
	  getUserName: getUserName,
	  getUserAvatar: getUserAvatar,
	  getUserAvatars: getUserAvatars,
	  isAvatarBlank: isAvatarBlank,
	  getCustomMessage: getCustomMessage,
	  convertKeysToUpper: convertKeysToUpper,
	  appendChildren: appendChildren,
	  containsVideoTrack: containsVideoTrack,
	  hasHdVideo: hasHdVideo,
	  findBestElementSize: findBestElementSize,
	  getFilledArea: getFilledArea,
	  isWebRTCSupported: isWebRTCSupported,
	  isCallServerAllowed: isCallServerAllowed,
	  isFeedbackAllowed: isFeedbackAllowed,
	  shouldCollectStats: shouldCollectStats,
	  shouldShowDocumentButton: shouldShowDocumentButton,
	  getDocumentsArticleCode: getDocumentsArticleCode,
	  getResumesArticleCode: getResumesArticleCode,
	  getUserLimit: getUserLimit,
	  getLogMessage: getLogMessage,
	  getUuidv4: getUuidv4,
	  reportConnectionResult: reportConnectionResult,
	  sendTelemetryEvent: sendTelemetryEvent,
	  isDesktop: isDesktop,
	  getBrowserForStatistics: getBrowserForStatistics,
	  isBlank: isBlank,
	  stopMediaStream: stopMediaStream
	};

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var Events$1 = {
	  onClose: 'onClose',
	  onDestroy: 'onDestroy',
	  onButtonClick: 'onButtonClick'
	};
	var InternalEvents = {
	  setHasCamera: "CallNotification::setHasCamera",
	  contentReady: "CallNotification::contentReady",
	  onButtonClick: "CallNotification::onButtonClick"
	};
	var _subscribeEvents = /*#__PURE__*/new WeakSet();
	var _onContentReady = /*#__PURE__*/new WeakMap();
	var IncomingNotification = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(IncomingNotification, _EventEmitter);
	  function IncomingNotification(_config) {
	    var _this;
	    babelHelpers.classCallCheck(this, IncomingNotification);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IncomingNotification).call(this));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _subscribeEvents);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _onContentReady, {
	      writable: true,
	      value: function value() {
	        _this.contentReady = true;
	        _this.sendPostponedEvents();
	      }
	    });
	    _this.setEventNamespace('BX.Call.IncomingNotification');
	    _this.popup = null;
	    _this.window = null;
	    _this.callerAvatar = main_core.Type.isStringFilled(_config.callerAvatar) ? _config.callerAvatar : "";
	    if (Util$1.isAvatarBlank(_this.callerAvatar)) {
	      _this.callerAvatar = "";
	    }
	    _this.callerName = _config.callerName;
	    _this.callerType = _config.callerType;
	    _this.callerColor = _config.callerColor;
	    _this.video = _config.video;
	    _this.hasCamera = _config.hasCamera === true;
	    _this.zIndex = _config.zIndex;
	    _this.contentReady = false;
	    _this.postponedEvents = [];
	    _classPrivateMethodGet$3(babelHelpers.assertThisInitialized(_this), _subscribeEvents, _subscribeEvents2).call(babelHelpers.assertThisInitialized(_this), _config);
	    if (BX.desktop) {
	      BX.desktop.addCustomEvent(InternalEvents.onButtonClick, function (e) {
	        return _this.emit(Events$1.onButtonClick, e);
	      });
	      BX.desktop.addCustomEvent(InternalEvents.contentReady, babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _onContentReady));
	    }
	    return _this;
	  }
	  babelHelpers.createClass(IncomingNotification, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;
	      if (BX.desktop) {
	        var params = {
	          video: this.video,
	          hasCamera: this.hasCamera,
	          callerAvatar: this.callerAvatar,
	          callerName: this.callerName,
	          callerType: this.callerType,
	          callerColor: this.callerColor
	        };
	        if (this.window) {
	          this.window.BXDesktopWindow.ExecuteCommand("show");
	        } else {
	          this.window = BXDesktopSystem.ExecuteCommand('topmost.show.html', BX.desktop.getHtmlPage("", "window.callNotification = new BX.Call.IncomingNotificationContent(" + JSON.stringify(params) + "); window.callNotification.showInDesktop();"));
	        }
	      } else {
	        this.content = new IncomingNotificationContent({
	          video: this.video,
	          hasCamera: this.hasCamera,
	          callerAvatar: this.callerAvatar,
	          callerName: this.callerName,
	          callerType: this.callerType,
	          callerColor: this.callerColor,
	          onClose: function onClose() {
	            return _this2.emit(Events$1.onClose);
	          },
	          onDestroy: function onDestroy() {
	            return _this2.emit(Events$1.onDestroy);
	          },
	          onButtonClick: function onButtonClick(e) {
	            return _this2.emit(Events$1.onButtonClick, Object.assign({}, e.data));
	          }
	        });
	        this.createPopup(this.content.render());
	        this.popup.show();
	      }
	    }
	  }, {
	    key: "createPopup",
	    value: function createPopup(content) {
	      var _this3 = this;
	      this.popup = new main_popup.Popup({
	        id: "bx-messenger-call-notify",
	        bindElement: null,
	        targetContainer: document.body,
	        content: content,
	        closeIcon: false,
	        noAllPaddings: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        closeByEsc: false,
	        draggable: {
	          restrict: false
	        },
	        overlay: {
	          backgroundColor: 'black',
	          opacity: 30
	        },
	        events: {
	          onPopupClose: function onPopupClose() {
	            return _this3.emit(Events$1.onClose);
	          },
	          onPopupDestroy: function onPopupDestroy() {
	            return _this3.popup = null;
	          }
	        }
	      });
	    }
	  }, {
	    key: "setHasCamera",
	    value: function setHasCamera(hasCamera) {
	      if (this.window) {
	        // desktop; send event to the window
	        if (this.contentReady) {
	          BX.desktop.onCustomEvent(InternalEvents.setHasCamera, [hasCamera]);
	        } else {
	          this.postponedEvents.push({
	            name: InternalEvents.setHasCamera,
	            params: [hasCamera]
	          });
	        }
	      } else if (this.content) {
	        this.content.setHasCamera(hasCamera);
	      }
	    }
	  }, {
	    key: "sendPostponedEvents",
	    value: function sendPostponedEvents() {
	      this.postponedEvents.forEach(function (event) {
	        BX.desktop.onCustomEvent(event.name, event.params);
	      });
	      this.postponedEvents = [];
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.popup) {
	        this.popup.close();
	      }
	      if (this.window) {
	        this.window.BXDesktopWindow.ExecuteCommand("hide");
	      }
	      this.emit(Events$1.onClose);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.popup) {
	        this.popup.destroy();
	        this.popup = null;
	      }
	      if (this.window) {
	        this.window.BXDesktopWindow.ExecuteCommand("close");
	        this.window = null;
	      }
	      if (this.content) {
	        this.content.destroy();
	        this.content = null;
	      }
	      if (BX.desktop) {
	        BX.desktop.removeCustomEvents(InternalEvents.onButtonClick);
	        BX.desktop.removeCustomEvents(InternalEvents.contentReady);
	      }
	      this.emit(Events$1.onDestroy);
	      this.unsubscribeAll(Events$1.onButtonClick);
	      this.unsubscribeAll(Events$1.onClick);
	      this.unsubscribeAll(Events$1.onDestroy);
	    }
	  }]);
	  return IncomingNotification;
	}(main_core_events.EventEmitter);
	function _subscribeEvents2(config) {
	  var eventKeys = Object.keys(Events$1);
	  for (var _i = 0, _eventKeys = eventKeys; _i < _eventKeys.length; _i++) {
	    var eventName = _eventKeys[_i];
	    if (main_core.Type.isFunction(config[eventName])) {
	      this.subscribe(Events$1[eventName], config[eventName]);
	    }
	  }
	}
	babelHelpers.defineProperty(IncomingNotification, "Events", Events$1);
	var _subscribeEvents3 = /*#__PURE__*/new WeakSet();
	var _onAnswerButtonClick = /*#__PURE__*/new WeakSet();
	var _onAnswerWithVideoButtonClick = /*#__PURE__*/new WeakSet();
	var _onDeclineButtonClick = /*#__PURE__*/new WeakSet();
	var IncomingNotificationContent = /*#__PURE__*/function (_EventEmitter2) {
	  babelHelpers.inherits(IncomingNotificationContent, _EventEmitter2);
	  function IncomingNotificationContent(_config2) {
	    var _this4;
	    babelHelpers.classCallCheck(this, IncomingNotificationContent);
	    _this4 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IncomingNotificationContent).call(this));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this4), _onDeclineButtonClick);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this4), _onAnswerWithVideoButtonClick);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this4), _onAnswerButtonClick);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this4), _subscribeEvents3);
	    _this4.setEventNamespace('BX.Call.IncomingNotificationContent');
	    _this4.video = !!_config2.video;
	    _this4.hasCamera = !!_config2.hasCamera;
	    _this4.callerAvatar = _config2.callerAvatar || '';
	    _this4.callerName = _config2.callerName || BX.message('IM_M_CALL_VIDEO_HD');
	    _this4.callerType = _config2.callerType || 'chat';
	    _this4.callerColor = _config2.callerColor || '';
	    _this4.elements = {
	      root: null,
	      avatar: null,
	      buttons: {
	        answerVideo: null
	      }
	    };
	    _classPrivateMethodGet$3(babelHelpers.assertThisInitialized(_this4), _subscribeEvents3, _subscribeEvents4).call(babelHelpers.assertThisInitialized(_this4), _config2);
	    if (BX.desktop) {
	      BX.desktop.addCustomEvent(InternalEvents.setHasCamera, function (hasCamera) {
	        return _this4.setHasCamera(hasCamera);
	      });
	      BX.desktop.onCustomEvent("main", InternalEvents.contentReady, []);
	    }
	    return _this4;
	  }
	  babelHelpers.createClass(IncomingNotificationContent, [{
	    key: "render",
	    value: function render() {
	      var backgroundImage = this.callerAvatar || '/bitrix/js/im/images/default-call-background.png';
	      var callerPrefix;
	      if (this.video) {
	        if (this.callerType === 'private') {
	          callerPrefix = BX.message("IM_M_VIDEO_CALL_FROM");
	        } else {
	          callerPrefix = BX.message("IM_M_VIDEO_CALL_FROM_CHAT");
	        }
	      } else {
	        if (this.callerType === 'private') {
	          callerPrefix = BX.message("IM_M_CALL_FROM");
	        } else {
	          callerPrefix = BX.message("IM_M_CALL_FROM_CHAT");
	        }
	      }
	      var avatarClass = '';
	      var avatarImageClass = '';
	      var avatarImageStyles;
	      if (this.callerAvatar) {
	        avatarImageStyles = {
	          backgroundImage: "url('" + this.callerAvatar + "')",
	          backgroundColor: '#fff',
	          backgroundSize: 'cover'
	        };
	      } else {
	        var callerType = this.callerType === 'private' ? 'user' : this.callerType;
	        avatarClass = 'bx-messenger-panel-avatar-' + callerType;
	        avatarImageStyles = {
	          backgroundColor: this.callerColor || '#525252',
	          backgroundSize: '40px',
	          backgroundPosition: 'center center'
	        };
	        avatarImageClass = 'bx-messenger-panel-avatar-img-default';
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-call-window"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-background"
	          },
	          style: {
	            backgroundImage: "url('" + backgroundImage + "')"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-background-blur"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-background-gradient"
	          },
	          style: {
	            backgroundImage: "url('/bitrix/js/im/images/call-background-gradient.png')"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-bottom-background"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-body"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-call-window-top"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-call-window-photo"
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-call-window-photo-left " + avatarClass
	                },
	                children: [this.elements.avatar = main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-window-photo-block " + avatarImageClass
	                  },
	                  style: avatarImageStyles
	                })]
	              })]
	            }), main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-call-window-title"
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-call-window-title-block"
	                },
	                children: [main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-overlay-title-caller-prefix"
	                  },
	                  text: callerPrefix
	                }), main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-overlay-title-caller"
	                  },
	                  text: main_core.Text.decode(this.callerName)
	                })]
	              })]
	            })]
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-call-window-bottom"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-call-window-buttons"
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-call-window-buttons-block"
	                },
	                children: [this.elements.buttons.answerVideo = main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-window-button" + (!this.hasCamera ? " bx-messenger-call-window-button-disabled" : "")
	                  },
	                  children: [main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-camera"
	                    }
	                  }), main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-text"
	                    },
	                    text: BX.message("IM_M_CALL_BTN_ANSWER_VIDEO")
	                  })],
	                  events: {
	                    click: _classPrivateMethodGet$3(this, _onAnswerWithVideoButtonClick, _onAnswerWithVideoButtonClick2).bind(this)
	                  }
	                }), main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-window-button"
	                  },
	                  children: [main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-phone-up"
	                    }
	                  }), main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-text"
	                    },
	                    text: BX.message("IM_M_CALL_BTN_ANSWER")
	                  })],
	                  events: {
	                    click: _classPrivateMethodGet$3(this, _onAnswerButtonClick, _onAnswerButtonClick2).bind(this)
	                  }
	                }), main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-window-button bx-messenger-call-window-button-danger"
	                  },
	                  children: [main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-phone-down"
	                    }
	                  }), main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-text"
	                    },
	                    text: BX.message("IM_M_CALL_BTN_DECLINE")
	                  })],
	                  events: {
	                    click: _classPrivateMethodGet$3(this, _onDeclineButtonClick, _onDeclineButtonClick2).bind(this)
	                  }
	                })]
	              })]
	            })]
	          })]
	        })]
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "showInDesktop",
	    value: function showInDesktop() {
	      // Workaround to prevent incoming call window from hanging.
	      // Without it, there is a possible scenario, when BXDesktopWindow.ExecuteCommand("close") is executed too early
	      // (if invite window is closed before appearing), which leads to hanging of the window
	      if (!window.opener.BXIM.callController.callNotification) {
	        BXDesktopWindow.ExecuteCommand("close");
	        return;
	      }
	      this.render();
	      document.body.appendChild(this.elements.root);
	      BX.desktop.setWindowPosition({
	        X: STP_CENTER,
	        Y: STP_VCENTER,
	        Width: 351,
	        Height: 510
	      });
	    }
	  }, {
	    key: "setHasCamera",
	    value: function setHasCamera(hasCamera) {
	      this.hasCamera = !!hasCamera;
	      if (this.elements.buttons.answerVideo) {
	        this.elements.buttons.answerVideo.classList.toggle("bx-messenger-call-window-button-disabled", !this.hasCamera);
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (BX.desktop) {
	        BX.desktop.removeCustomEvents(InternalEvents.setHasCamera);
	      }
	      this.unsubscribeAll(Events$1.onButtonClick);
	      this.unsubscribeAll(Events$1.onClick);
	      this.unsubscribeAll(Events$1.onDestroy);
	    }
	  }]);
	  return IncomingNotificationContent;
	}(main_core_events.EventEmitter);
	function _subscribeEvents4(config) {
	  var eventKeys = Object.keys(Events$1);
	  for (var _i2 = 0, _eventKeys2 = eventKeys; _i2 < _eventKeys2.length; _i2++) {
	    var eventName = _eventKeys2[_i2];
	    if (main_core.Type.isFunction(config[eventName])) {
	      this.subscribe(Events$1[eventName], config[eventName]);
	    }
	  }
	}
	function _onAnswerButtonClick2() {
	  if (BX.desktop) {
	    BXDesktopWindow.ExecuteCommand("close");
	    BX.desktop.onCustomEvent("main", InternalEvents.onButtonClick, [{
	      button: 'answer',
	      video: false
	    }]);
	  } else {
	    this.emit(Events$1.onButtonClick, {
	      button: 'answer',
	      video: false
	    });
	  }
	}
	function _onAnswerWithVideoButtonClick2() {
	  if (!this.hasCamera) {
	    return;
	  }
	  if (BX.desktop) {
	    BXDesktopWindow.ExecuteCommand("close");
	    BX.desktop.onCustomEvent("main", InternalEvents.onButtonClick, [{
	      button: 'answer',
	      video: true
	    }]);
	  } else {
	    this.emit(Events$1.onButtonClick, {
	      button: 'answer',
	      video: true
	    });
	  }
	}
	function _onDeclineButtonClick2() {
	  if (BX.desktop) {
	    BXDesktopWindow.ExecuteCommand("close");
	    BX.desktop.onCustomEvent("main", InternalEvents.onButtonClick, [{
	      button: 'decline'
	    }]);
	  } else {
	    this.emit(Events$1.onButtonClick, {
	      button: 'decline'
	    });
	  }
	}

	var Events$2 = {
	  onButtonClick: "ConferenceNotification::onButtonClick"
	};

	/**
	 *
	 * @param {Object} config
	 * @param {string} config.callerName
	 * @param {string} config.callerAvatar
	 * @param {number} config.zIndex
	 * @param {function} config.onClose
	 * @param {function} config.onDestroy
	 * @param {function} config.onButtonClick
	 * @constructor
	 */
	var ConferenceNotifications = /*#__PURE__*/function () {
	  function ConferenceNotifications(config) {
	    babelHelpers.classCallCheck(this, ConferenceNotifications);
	    this.popup = null;
	    this.window = null;
	    this.callerAvatar = main_core.Type.isStringFilled(config.callerAvatar) ? config.callerAvatar : "";
	    this.zIndex = config.zIndex;
	    if (Util$1.isAvatarBlank(this.callerAvatar)) {
	      this.callerAvatar = "";
	    }
	    this.callerName = config.callerName;
	    this.callerColor = config.callerColor;
	    this.callbacks = {
	      onClose: main_core.Type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
	      onDestroy: main_core.Type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
	      onButtonClick: main_core.Type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
	    };
	    this._onContentButtonClickHandler = this._onContentButtonClick.bind(this);
	    if (BX.desktop) {
	      BX.desktop.addCustomEvent(Events$2.onButtonClick, this._onContentButtonClickHandler);
	    }
	  }
	  babelHelpers.createClass(ConferenceNotifications, [{
	    key: "show",
	    value: function show() {
	      if (BX.desktop) {
	        var params = {
	          callerAvatar: this.callerAvatar,
	          callerName: this.callerName,
	          callerColor: this.callerColor
	        };
	        if (this.window) {
	          this.window.BXDesktopWindow.ExecuteCommand("show");
	        } else {
	          this.window = BXDesktopSystem.ExecuteCommand('topmost.show.html', BX.desktop.getHtmlPage("", "window.conferenceNotification = new BX.Call.NotificationConferenceContent(" + JSON.stringify(params) + "); window.conferenceNotification.showInDesktop();"));
	        }
	      } else {
	        this.content = new NotificationConferenceContent({
	          callerAvatar: this.callerAvatar,
	          callerName: this.callerName,
	          callerColor: this.callerColor,
	          onClose: this.callbacks.onClose,
	          onDestroy: this.callbacks.onDestroy,
	          onButtonClick: this.callbacks.onButtonClick
	        });
	        this.createPopup(this.content.render());
	        this.popup.show();
	      }
	    }
	  }, {
	    key: "createPopup",
	    value: function createPopup(content) {
	      this.popup = new main_popup.Popup({
	        id: "bx-messenger-call-notify",
	        targetContainer: document.body,
	        content: content,
	        closeIcon: false,
	        noAllPaddings: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        closeByEsc: false,
	        draggable: {
	          restrict: false
	        },
	        overlay: {
	          backgroundColor: 'black',
	          opacity: 30
	        },
	        events: {
	          onPopupClose: function () {
	            this.callbacks.onClose();
	          }.bind(this),
	          onPopupDestroy: function () {
	            this.popup = null;
	          }.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.popup) {
	        this.popup.close();
	      }
	      if (this.window) {
	        this.window.BXDesktopWindow.ExecuteCommand("hide");
	      }
	      this.callbacks.onClose();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.popup) {
	        this.popup.destroy();
	        this.popup = null;
	      }
	      if (this.window) {
	        this.window.BXDesktopWindow.ExecuteCommand("close");
	        this.window = null;
	      }
	      if (BX.desktop) {
	        BX.desktop.removeCustomEvents(Events$2.onButtonClick);
	      }
	      this.callbacks.onDestroy();
	    }
	  }, {
	    key: "_onContentButtonClick",
	    value: function _onContentButtonClick(e) {
	      this.callbacks.onButtonClick(e);
	    }
	  }]);
	  return ConferenceNotifications;
	}();
	var NotificationConferenceContent = /*#__PURE__*/function () {
	  function NotificationConferenceContent(config) {
	    babelHelpers.classCallCheck(this, NotificationConferenceContent);
	    this.callerAvatar = config.callerAvatar || '';
	    this.callerName = config.callerName || BX.message('IM_CL_USER');
	    this.callerColor = config.callerColor || '#525252';
	    this.elements = {
	      root: null,
	      avatar: null
	    };
	    this.callbacks = {
	      onClose: main_core.Type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
	      onDestroy: main_core.Type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
	      onButtonClick: main_core.Type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
	    };
	  }
	  babelHelpers.createClass(NotificationConferenceContent, [{
	    key: "render",
	    value: function render() {
	      var backgroundImage = this.callerAvatar || '/bitrix/js/im/images/default-call-background.png';
	      var avatarImageStyles;
	      if (this.callerAvatar) {
	        avatarImageStyles = {
	          backgroundImage: "url('" + this.callerAvatar + "')",
	          backgroundColor: '#fff',
	          backgroundSize: 'cover'
	        };
	      } else {
	        avatarImageStyles = {
	          backgroundImage: "url('" + (this.callerAvatar || "/bitrix/js/im/images/default-avatar-videoconf-big.png") + "')",
	          backgroundColor: this.callerColor,
	          backgroundSize: '80px',
	          backgroundRepeat: 'no-repeat',
	          backgroundPosition: 'center center'
	        };
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-call-window"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-background"
	          },
	          style: {
	            backgroundImage: 'url(' + backgroundImage + ')'
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-background-blur"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-background-gradient"
	          },
	          style: {
	            backgroundImage: "url('/bitrix/js/im/images/call-background-gradient.png')"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-bottom-background"
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-window-body"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-call-window-top"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-call-window-photo"
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-call-window-photo-left"
	                },
	                children: [this.elements.avatar = main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-window-photo-block"
	                  },
	                  style: avatarImageStyles
	                })]
	              })]
	            }), main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-call-window-title"
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-call-window-title-block"
	                },
	                children: [main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-overlay-title-caller-prefix"
	                  },
	                  text: BX.message("IM_M_VIDEO_CALL_FROM")
	                }), main_core.Dom.create("div", {
	                  text: main_core.Text.encode(this.callerName),
	                  props: {
	                    className: "bx-messenger-call-overlay-title-caller"
	                  }
	                })]
	              })]
	            })]
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-call-window-bottom"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-call-window-buttons"
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-call-window-buttons-block"
	                },
	                children: [main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-window-button"
	                  },
	                  children: [main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-camera"
	                    }
	                  }), main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-text"
	                    },
	                    text: BX.message("IM_M_CALL_BTN_ANSWER_CONFERENCE")
	                  })],
	                  events: {
	                    click: this._onAnswerConferenceButtonClick.bind(this)
	                  }
	                }), main_core.Dom.create("div", {
	                  props: {
	                    className: "bx-messenger-call-window-button bx-messenger-call-window-button-danger"
	                  },
	                  children: [main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-phone-down"
	                    }
	                  }), main_core.Dom.create("div", {
	                    props: {
	                      className: "bx-messenger-call-window-button-text"
	                    },
	                    text: BX.message("IM_M_CALL_BTN_SKIP_CONFERENCE")
	                  })],
	                  events: {
	                    click: this._onSkipConferenceButtonClick.bind(this)
	                  }
	                })]
	              })]
	            })]
	          })]
	        })]
	      });
	      return this.elements.root;
	    }
	  }, {
	    key: "showInDesktop",
	    value: function showInDesktop() {
	      this.render();
	      document.body.appendChild(this.elements.root);
	      BX.desktop.setWindowPosition({
	        X: STP_CENTER,
	        Y: STP_VCENTER,
	        Width: 351,
	        Height: 510
	      });
	    }
	  }, {
	    key: "_onAnswerConferenceButtonClick",
	    value: function _onAnswerConferenceButtonClick(e) {
	      if (BX.desktop) {
	        BXDesktopWindow.ExecuteCommand("close");
	        BX.desktop.onCustomEvent("main", Events$2.onButtonClick, [{
	          button: 'answerConference'
	        }]);
	      } else {
	        this.callbacks.onButtonClick({
	          button: 'answerConference'
	        });
	      }
	    }
	  }, {
	    key: "_onSkipConferenceButtonClick",
	    value: function _onSkipConferenceButtonClick(e) {
	      if (BX.desktop) {
	        BXDesktopWindow.ExecuteCommand("close");
	        BX.desktop.onCustomEvent("main", Events$2.onButtonClick, [{
	          button: 'skipConference'
	        }]);
	      } else {
	        this.callbacks.onButtonClick({
	          button: 'skipConference'
	        });
	      }
	    }
	  }]);
	  return NotificationConferenceContent;
	}();

	var Events$3 = {
	  onBackToCallClick: "FloatingScreenshare::onBackToCallClick",
	  onStopSharingClick: "FloatingScreenshare::onStopSharingClick",
	  onChangeScreenClick: "FloatingScreenshare::onChangeScreenClick"
	};
	var POPUP_WIDTH = 291;
	var POPUP_HEIGHT = 81;
	var POPUP_OFFSET_X = 80;
	var POPUP_OFFSET_Y = 80;

	/**
	 *
	 * @param {object} config
	 * @constructor
	 */
	var FloatingScreenShare = /*#__PURE__*/function () {
	  function FloatingScreenShare(config) {
	    babelHelpers.classCallCheck(this, FloatingScreenShare);
	    if (babelHelpers["typeof"](config) !== "object") {
	      config = {};
	    }
	    this.desktop = config.desktop || BX.desktop;
	    this.darkMode = config.darkMode || false;
	    this.window = null;
	    this.sharedWindowX = null;
	    this.sharedWindowY = null;
	    this.sharedWindowHeight = null;
	    this.sharedWindowWidth = null;
	    this.title = '';
	    this.app = '';
	    this.screens = [];
	    this.screenToUse = null;
	    this.callbacks = {
	      onBackToCallClick: main_core.Type.isFunction(config.onBackToCallClick) ? config.onBackToCallClick : BX.DoNothing,
	      onStopSharingClick: main_core.Type.isFunction(config.onStopSharingClick) ? config.onStopSharingClick : BX.DoNothing,
	      onChangeScreenClick: main_core.Type.isFunction(config.onChangeScreenClick) ? config.onChangeScreenClick : BX.DoNothing
	    };
	    this._onBackToCallClickHandler = this._onBackToCallClick.bind(this);
	    this._onStopSharingClickHandler = this._onStopSharingClick.bind(this);
	    this._onChangeScreenClickHandler = this._onChangeScreenClick.bind(this);
	    this.bindEventHandlers();
	  }
	  babelHelpers.createClass(FloatingScreenShare, [{
	    key: "bindEventHandlers",
	    value: function bindEventHandlers() {
	      this.desktop.addCustomEvent(Events$3.onBackToCallClick, this._onBackToCallClickHandler);
	      this.desktop.addCustomEvent(Events$3.onStopSharingClick, this._onStopSharingClickHandler);
	      this.desktop.addCustomEvent(Events$3.onChangeScreenClick, this._onChangeScreenClickHandler);
	    }
	  }, {
	    key: "saveExistingScreens",
	    value: function saveExistingScreens() {
	      var _this = this;
	      return new Promise(function (resolve, reject) {
	        if (_this.screens.length > 0) {
	          return resolve();
	        }
	        BXDesktopSystem.ListScreenMedia(function (result) {
	          result.forEach(function (item) {
	            if (item.id.slice(0, 6) === 'screen') {
	              _this.screens.push({
	                id: item.id,
	                x: item.x,
	                y: item.y,
	                width: item.width,
	                height: item.height
	              });
	            }
	          });
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "_onBackToCallClick",
	    value: function _onBackToCallClick() {
	      this.callbacks.onBackToCallClick();
	    }
	  }, {
	    key: "_onStopSharingClick",
	    value: function _onStopSharingClick() {
	      this.close();
	      this.callbacks.onStopSharingClick();
	    }
	  }, {
	    key: "_onChangeScreenClick",
	    value: function _onChangeScreenClick() {
	      this.callbacks.onChangeScreenClick();
	    }
	  }, {
	    key: "setSharingData",
	    value: function setSharingData(data) {
	      var _this2 = this;
	      return this.saveExistingScreens().then(function () {
	        _this2.sharedWindowX = data.x + 10;
	        _this2.sharedWindowY = data.y + 10;
	        _this2.sharedWindowWidth = data.width;
	        _this2.sharedWindowHeight = data.height;
	        _this2.title = data.title;
	        _this2.app = data.app;
	        for (var i = 0; i < _this2.screens.length; i++) {
	          if (_this2.sharedWindowX >= _this2.screens[i].x && _this2.sharedWindowX <= _this2.screens[i].x + _this2.screens[i].width && _this2.sharedWindowY >= _this2.screens[i].y && _this2.sharedWindowY <= _this2.screens[i].y + _this2.screens[i].height) {
	            _this2.screenToUse = _this2.screens[i];
	            break;
	          }
	        }
	        if (!_this2.screenToUse && _this2.screens.length > 0) {
	          _this2.screenToUse = _this2.screens[0];
	        }
	      })["catch"](function (error) {
	        console.log('save existing screens error', error);
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.desktop) {
	        return;
	      }
	      if (this.window) {
	        this.window.BXDesktopWindow.ExecuteCommand("show");
	      } else {
	        var params = {
	          title: this.title,
	          app: this.app,
	          sharedWindowX: this.sharedWindowX,
	          sharedWindowY: this.sharedWindowY,
	          sharedWindowWidth: this.sharedWindowWidth,
	          sharedWindowHeight: this.sharedWindowHeight,
	          screenToUse: this.screenToUse,
	          darkMode: this.darkMode
	        };
	        this.window = BXDesktopSystem.ExecuteCommand('topmost.show.html', this.desktop.getHtmlPage("", "window.FSSC = new BX.Call.FloatingScreenShareContent(" + JSON.stringify(params) + ");"));
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (!this.window || !this.window.document) {
	        return false;
	      }
	      this.window.BXDesktopWindow.ExecuteCommand("hide");
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (!this.window || !this.window.document) {
	        return false;
	      }
	      this.window.BXDesktopWindow.ExecuteCommand("close");
	      this.window = null;
	      this.visible = false;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.window) {
	        this.window.BXDesktopWindow.ExecuteCommand("close");
	        this.window = null;
	      }
	      this.desktop.removeCustomEvents(Events$3.onBackToCallClick);
	      this.desktop.removeCustomEvents(Events$3.onStopSharingClick);
	      this.desktop.removeCustomEvents(Events$3.onChangeScreenClick);
	    }
	  }]);
	  return FloatingScreenShare;
	}();
	var FloatingScreenShareContent = /*#__PURE__*/function () {
	  function FloatingScreenShareContent(config) {
	    babelHelpers.classCallCheck(this, FloatingScreenShareContent);
	    this.title = config.title || '';
	    this.app = config.app || '';
	    this.sharedWindowX = config.sharedWindowX || 0;
	    this.sharedWindowY = config.sharedWindowY || 0;
	    this.sharedWindowHeight = config.sharedWindowHeight || 0;
	    this.sharedWindowWidth = config.sharedWindowWidth || 0;
	    this.screenToUse = config.screenToUse || null;
	    this.darkMode = config.darkMode || false;
	    this.elements = {
	      container: null
	    };
	    this.render();
	    this.adjustWindow(POPUP_WIDTH, POPUP_HEIGHT);
	  }
	  babelHelpers.createClass(FloatingScreenShareContent, [{
	    key: "render",
	    value: function render() {
	      var title = this.app ? this.app + ' - ' + this.title : this.title;
	      this.elements.container = main_core.Dom.create("div", {
	        props: {
	          className: 'bx-messenger-call-floating-screenshare-wrap' + (this.darkMode ? ' dark-mode' : '')
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: 'bx-messenger-call-floating-screenshare-top'
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: 'bx-messenger-call-floating-screenshare-top-icon'
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: 'bx-messenger-call-floating-screenshare-top-text',
	              title: title
	            },
	            text: title
	          })]
	        }), main_core.Dom.create("div", {
	          props: {
	            className: 'bx-messenger-call-floating-screenshare-bottom'
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: 'bx-messenger-call-floating-screenshare-bottom-left'
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: 'bx-messenger-call-floating-screenshare-back-icon'
	              }
	            }), main_core.Dom.create("div", {
	              props: {
	                className: 'bx-messenger-call-floating-screenshare-back-text'
	              },
	              text: BX.message('IM_M_CALL_SCREENSHARE_BACK_TO_CALL')
	            })],
	            events: {
	              click: this.onBackToCallClick.bind(this)
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: 'bx-messenger-call-floating-screenshare-bottom-center'
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: 'bx-messenger-call-floating-screenshare-change-screen-icon'
	              }
	            }), main_core.Dom.create("div", {
	              props: {
	                className: 'bx-messenger-call-floating-screenshare-change-screen-text'
	              },
	              text: BX.message('IM_M_CALL_SCREENSHARE_CHANGE_SCREEN')
	            })],
	            events: {
	              click: this.onChangeScreenClick.bind(this)
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: 'bx-messenger-call-floating-screenshare-bottom-right'
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: 'bx-messenger-call-floating-screenshare-stop-icon'
	              }
	            }), main_core.Dom.create("div", {
	              props: {
	                className: 'bx-messenger-call-floating-screenshare-stop-text'
	              },
	              text: BX.message('IM_M_CALL_SCREENSHARE_STOP')
	            })],
	            events: {
	              click: this.onStopSharingClick.bind(this)
	            }
	          })]
	        })]
	      });
	      document.body.appendChild(this.elements.container);
	      document.body.classList.add('bx-messenger-call-floating-screenshare');
	    }
	  }, {
	    key: "onBackToCallClick",
	    value: function onBackToCallClick() {
	      this.dispatchEvent(Events$3.onBackToCallClick, []);
	    }
	  }, {
	    key: "onChangeScreenClick",
	    value: function onChangeScreenClick() {
	      this.dispatchEvent(Events$3.onChangeScreenClick, []);
	    }
	  }, {
	    key: "onStopSharingClick",
	    value: function onStopSharingClick() {
	      this.dispatchEvent(Events$3.onStopSharingClick, []);
	    }
	  }, {
	    key: "adjustWindow",
	    value: function adjustWindow(width, height) {
	      if (!this.screenToUse) {
	        return;
	      }
	      var blockOffset = 22;
	      var popupPadding = 22;
	      var leftBlockWidth = document.querySelector('.bx-messenger-call-floating-screenshare-bottom-left').scrollWidth;
	      var centerBlockWidth = document.querySelector('.bx-messenger-call-floating-screenshare-bottom-center').scrollWidth;
	      var rightBlockWidth = document.querySelector('.bx-messenger-call-floating-screenshare-bottom-right').scrollWidth;
	      var fullWidth = leftBlockWidth + centerBlockWidth + rightBlockWidth + 2 * blockOffset + 2 * popupPadding;
	      if (fullWidth > POPUP_WIDTH) {
	        width = fullWidth;
	      }
	      this.elements.container.style.width = width + "px";
	      this.elements.container.style.height = height + "px";
	      BXDesktopWindow.SetProperty("minClientSize", {
	        Width: width,
	        Height: height
	      });
	      BXDesktopWindow.SetProperty("resizable", false);
	      BXDesktopWindow.SetProperty("closable", false);
	      BXDesktopWindow.SetProperty("title", BX.message('IM_M_CALL_SCREENSHARE_TITLE'));
	      BXDesktopWindow.SetProperty("position", {
	        X: this.screenToUse.x + this.screenToUse.width - width - POPUP_OFFSET_X,
	        Y: this.screenToUse.y + POPUP_OFFSET_Y,
	        Width: width,
	        Height: height,
	        Mode: STP_FRONT
	      });
	    }
	  }, {
	    key: "dispatchEvent",
	    value: function dispatchEvent(name, params) {
	      var convertedParams = {};
	      for (var i = 0; i < params.length; i++) {
	        convertedParams[i] = params[i];
	      }
	      var mainWindow = opener ? opener : top;
	      mainWindow.BXWindows.forEach(function (windowItem) {
	        if (windowItem && windowItem.name !== '' && windowItem.BXDesktopWindow && windowItem.BXDesktopWindow.DispatchCustomEvent) {
	          windowItem.BXDesktopWindow.DispatchCustomEvent(name, convertedParams);
	        }
	      });
	      mainWindow.BXDesktopWindow.DispatchCustomEvent(name, convertedParams);
	    }
	  }]);
	  return FloatingScreenShareContent;
	}();

	var CallHint = /*#__PURE__*/function () {
	  function CallHint(options) {
	    babelHelpers.classCallCheck(this, CallHint);
	    this.popup = null;
	    this.title = BX.prop.getString(options, "title", main_core.Text.encode(BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING")));
	    this.icon = BX.prop.getString(options, "icon", "mic");
	    this.bindElement = BX.prop.getElementNode(options, "bindElement", null);
	    this.targetContainer = BX.prop.getElementNode(options, "targetContainer", null);
	    this.callFolded = BX.prop.getBoolean(options, "callFolded", false);
	    this.autoCloseDelay = BX.prop.getInteger(options, "autoCloseDelay", 5000);
	    this.buttonsLayout = BX.prop.getString(options, "buttonsLayout", "right");
	    this.buttons = BX.prop.getArray(options, "buttons", []);
	    this.callbacks = {
	      onClose: BX.prop.getFunction(options, "onClose", BX.DoNothing)
	    };
	    this.autoCloseTimeout = 0;
	  }
	  babelHelpers.createClass(CallHint, [{
	    key: "show",
	    value: function show() {
	      var _this = this;
	      clearTimeout(this.autoCloseTimeout);
	      if (this.autoCloseDelay > 0) {
	        this.autoCloseTimeout = setTimeout(function () {
	          return _this.onAutoClose();
	        }, this.autoCloseDelay);
	      }
	      if (this.popup) {
	        this.popup.show();
	        return;
	      }
	      this.popup = new main_popup.Popup({
	        bindElement: this.bindElement,
	        targetContainer: this.targetContainer,
	        content: this.render(),
	        padding: 0,
	        contentPadding: 14,
	        // height: this.getPopupHeight(),
	        className: 'bx-call-view-popup-call-hint',
	        contentBackground: 'unset',
	        maxWidth: 600,
	        angle: this.bindElement ? {
	          position: 'bottom',
	          offset: 20
	        } : null,
	        events: {
	          onClose: function onClose() {
	            return _this.popup.destroy();
	          },
	          onDestroy: function onDestroy() {
	            return _this.popup = null;
	          }
	        }
	      });
	      this.popup.show();
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-view-popup-call-hint-body layout-" + this.buttonsLayout
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-popup-call-hint-icon " + this.icon
	          }
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-popup-call-hint-middle-block"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-popup-call-hint-text"
	            },
	            html: this.getPopupMessage()
	          }), this.buttonsLayout == "bottom" ? this.renderButtons() : null]
	        }), this.buttonsLayout == "right" ? this.renderButtons() : null, main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-popup-call-hint-close"
	          },
	          events: {
	            click: function click() {
	              _this2.callbacks.onClose();
	              if (_this2.popup) {
	                _this2.popup.close();
	              }
	            }
	          }
	        })]
	      });
	    }
	  }, {
	    key: "renderButtons",
	    value: function renderButtons() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-view-popup-call-hint-buttons-container"
	        },
	        children: this.buttons.map(function (button) {
	          return button.render();
	        })
	      });
	    }
	  }, {
	    key: "getPopupMessage",
	    value: function getPopupMessage() {
	      if (!Util$1.isDesktop()) {
	        return this.title;
	      }
	      var hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_HOTKEY");
	      if (this.callFolded) {
	        var hotkey = BX.browser.IsMac() ? 'Shift + &#8984; + A' : 'Ctrl + Shift + A';
	        hotKeyMessage = BX.message("IM_CALL_MIC_MUTED_WHILE_TALKING_FOLDED_CALL_HOTKEY").replace('#HOTKEY#', hotkey);
	      }
	      hotKeyMessage = '<span class="bx-call-view-popup-call-hint-text-hotkey">' + hotKeyMessage + '</span>';
	      return this.title + '<br>' + hotKeyMessage;
	    }
	    /**
	     * Returns height in pixels for the popup.
	     * The height depends on the hotkey hint (hint appears only in the desktop app).
	     *
	     * @returns {number}
	     */
	  }, {
	    key: "getPopupHeight",
	    value: function getPopupHeight() {
	      return Util$1.isDesktop() ? 60 : 54;
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.popup) {
	        this.popup.close();
	        this.callbacks.onClose();
	      }
	    }
	  }, {
	    key: "onAutoClose",
	    value: function onAutoClose() {
	      this.close();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.popup) {
	        this.popup.destroy();
	      }
	      clearTimeout(this.autoCloseTimeout);
	    }
	  }]);
	  return CallHint;
	}();

	var Events$4 = {
	  onActionClick: 'onActionClick',
	  onClose: 'onClose'
	};
	var PromoPopup = /*#__PURE__*/function () {
	  function PromoPopup(options) {
	    babelHelpers.classCallCheck(this, PromoPopup);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.promoCode = main_core.Type.isStringFilled(options.promoCode) ? options.promoCode : '';
	    this.bindElement = options.bindElement;
	    this.elements = {
	      root: null
	    };
	    this.popup = null;
	    this.dontShowAgain = false;
	    this.eventEmitter = new main_core_events.EventEmitter(this, "BX.Call.PromoPopup");
	    if (options.events) {
	      this.subscribeToEvents(options.events);
	    }
	  }
	  babelHelpers.createClass(PromoPopup, [{
	    key: "subscribeToEvents",
	    value: function subscribeToEvents(events) {
	      for (var eventName in events) {
	        if (events.hasOwnProperty(eventName)) {
	          this.eventEmitter.subscribe(eventName, events[eventName]);
	        }
	      }
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-promo-container"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-promo-content"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-promo-icon-section"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-call-promo-icon"
	              }
	            })]
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-promo-text-section"
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-call-promo-title"
	              },
	              text: BX.message("IM_CALL_DOCUMENT_PROMO_TITLE")
	            }), main_core.Dom.create("div", {
	              props: {
	                className: "bx-call-promo-text"
	              },
	              html: BX.message("IM_CALL_DOCUMENT_PROMO_TEXT")
	            }), main_core.Dom.create("div", {
	              props: {
	                className: "bx-call-promo-refuse"
	              },
	              children: [main_core.Dom.create("input", {
	                attrs: {
	                  type: "checkbox"
	                },
	                props: {
	                  className: "bx-call-promo-refuse-checkbox",
	                  id: "bx-call-promo-refuse-checkbox"
	                },
	                events: {
	                  change: this.onCheckboxChange.bind(this)
	                }
	              }), main_core.Dom.create("label", {
	                attrs: {
	                  "for": "bx-call-promo-refuse-checkbox"
	                },
	                props: {
	                  className: "bx-call-promo-refuse-text"
	                },
	                text: BX.message("IM_CALL_DOCUMENT_PROMO_DONT_SHOW_AGAIN")
	              })]
	            })]
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-promo-button-section"
	            },
	            children: [main_core.Dom.create("button", {
	              props: {
	                className: "bx-call-promo-button bx-call-promo-button-action ui-btn ui-btn-round"
	              },
	              text: BX.message("IM_CALL_DOCUMENT_PROMO_ACTION"),
	              events: {
	                click: this.onActionClick.bind(this)
	              }
	            }), main_core.Dom.create("button", {
	              props: {
	                className: "bx-call-promo-button bx-call-promo-button-action-close ui-btn ui-btn-round"
	              },
	              text: BX.message("IM_CALL_DOCUMENT_PROMO_ACTION_CLOSE"),
	              events: {
	                click: this.close.bind(this)
	              }
	            })]
	          })]
	        })]
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.elements.root) {
	        this.render();
	      }
	      this.createPopup();
	      this.popup.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (!this.popup) {
	        return false;
	      }
	      this.popup.close();
	    }
	  }, {
	    key: "createPopup",
	    value: function createPopup() {
	      this.popup = new main_popup.Popup({
	        id: 'bx-call-promo-popup',
	        bindElement: this.bindElement,
	        targetContainer: document.body,
	        content: this.elements.root,
	        cacheable: false,
	        closeIcon: true,
	        bindOptions: {
	          position: "top"
	        },
	        angle: {
	          position: "bottom",
	          offset: 49
	        },
	        className: 'bx-call-promo-popup',
	        contentBackground: 'unset',
	        events: {
	          onPopupClose: this.onPopupClose.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "onPopupClose",
	    value: function onPopupClose() {
	      this.popup.destroy();
	      this.destroy();
	    }
	  }, {
	    key: "onCheckboxChange",
	    value: function onCheckboxChange(event) {
	      this.dontShowAgain = event.currentTarget.checked;
	    }
	  }, {
	    key: "onActionClick",
	    value: function onActionClick() {
	      this.eventEmitter.emit(Events$4.onActionClick);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.eventEmitter.emit(Events$4.onClose, {
	        dontShowAgain: this.dontShowAgain
	      });
	      this.eventEmitter.unsubscribeAll(Events$4.onClose);
	      this.eventEmitter = null;
	      this.elements = null;
	    }
	  }]);
	  return PromoPopup;
	}();
	PromoPopup.Events = Events$4;
	var PromoPopup3D = /*#__PURE__*/function () {
	  function PromoPopup3D(options) {
	    babelHelpers.classCallCheck(this, PromoPopup3D);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.callView = options.callView;
	    this.bindElement = options.bindElement;
	    this.popup = null;
	    options.events = main_core.Type.isPlainObject(options.events) ? options.events : {};
	    this.events = {
	      onActionClick: options.events.onActionClick ? options.events.onActionClick : function () {},
	      onClose: options.events.onClose ? options.events.onClose : function () {}
	    };
	  }
	  babelHelpers.createClass(PromoPopup3D, [{
	    key: "show",
	    value: function show() {
	      this.createPopup();
	      this.popup.show();
	      BX.bind(BX('promo-popup-3d-button'), "click", this.openWindow.bind(this));
	    }
	  }, {
	    key: "openWindow",
	    value: function openWindow() {
	      var _this = this;
	      BackgroundDialog.open({
	        tab: 'mask'
	      });
	      setTimeout(function () {
	        return _this.close();
	      }, 100);
	    }
	  }, {
	    key: "openLearningPopup",
	    value: function openLearningPopup() {
	      var _this2 = this;
	      var bindElement = BX('bx-messenger-videocall-panel-item-with-arrow-camera');
	      if (!bindElement) {
	        return true;
	      }
	      var title = BX.message('IM_PROMO_3DAVATAR_30112022_LEARNING_TITLE');
	      var description = BX.message('IM_PROMO_3DAVATAR_30112022_LEARNING_TEXT');
	      var content = "\n\t\t\t<div class=\"promo-popup-3d-learning-content\">\n\t\t\t\t<h4 class=\"ui-typography-heading-h4 promo-popup-3d-learning-content__title\">".concat(title, "</h4>\n\t\t\t\t<p class=\"promo-popup-3d-learning-content__description\">").concat(description, "</p>\n\t\t\t</div>\n\t\t");
	      this.popup = new main_popup.Popup({
	        id: 'bx-call-promo-learning-popup',
	        bindElement: bindElement,
	        targetContainer: document.body,
	        content: content,
	        cacheable: false,
	        closeIcon: true,
	        autoHide: true,
	        closeByEsc: true,
	        bindOptions: {
	          position: "top",
	          forceTop: -100,
	          forceLeft: 100,
	          forceBindPosition: true
	        },
	        angle: {
	          position: "top",
	          offset: 49
	        },
	        className: 'bx-call-promo-popup-learn',
	        contentBackground: 'unset',
	        events: {
	          onPopupClose: function onPopupClose() {
	            _this2.events.onClose();
	          }
	        }
	      });
	      this.popup.show();
	      this.callView.subscribe(View.Event.onDeviceSelectorShow, function () {
	        return _this2.popup ? _this2.popup.close() : '';
	      });
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (!this.popup) {
	        return false;
	      }
	      this.popup.close();
	    }
	  }, {
	    key: "createPopup",
	    value: function createPopup() {
	      var title = BX.message('IM_PROMO_3DAVATAR_30112022_TITLE');
	      var description = BX.message('IM_PROMO_3DAVATAR_30112022_TEXT');
	      var btnText = BX.message('IM_PROMO_3DAVATAR_30112022_BUTTON');
	      var content = "\n\t\t\t<div class=\"promo-popup-3d-content\">\n\t\t\t\t<div class=\"promo-popup-3d-content__masks-container\">\n\t\t\t\t\t<div class=\"promo-popup-3d-content__mask --left-2 --bear\"></div>\n\t\t\t\t\t<div class=\"promo-popup-3d-content__mask --left-1 --pole-bear\"></div>\n\t\t\t\t\t<div class=\"promo-popup-3d-content__mask --center --fox\"></div>\n\t\t\t\t\t<div class=\"promo-popup-3d-content__mask --right-1 --santa\"></div>\n\t\t\t\t\t<div class=\"promo-popup-3d-content__mask --right-2 --owl\"></div>\n\t\t\t\t</div>\n\t\t\t\t<h3 class=\"ui-typography-heading-h2 promo-popup-3d-content__title\">".concat(title, "</h3>\n\t\t\t\t<p class=\"promo-popup-3d-content__description\">").concat(description, "</p>\n\t\t\t\t<div class=\"promo-popup-3d-content__actions-btn\">\n\t\t\t\t\t<span class=\"ui-btn btn-primary ui-btn-lg ui-btn-round ui-btn-primary\" id=\"promo-popup-3d-button\">").concat(btnText, "</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t");
	      this.popup = new main_popup.Popup({
	        id: 'bx-call-promo-popup-3d',
	        bindElement: this.bindElement,
	        targetContainer: document.body,
	        content: content,
	        cacheable: false,
	        closeIcon: true,
	        overlay: {
	          backgroundColor: '#000',
	          opacity: 40
	        },
	        width: 531,
	        minHeight: 481,
	        bindOptions: {
	          position: "top"
	        },
	        className: 'bx-call-promo-popup-3d-masks',
	        events: {
	          onPopupClose: this.onPopupClose.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "onPopupClose",
	    value: function onPopupClose() {
	      this.popup.destroy();
	      this.openLearningPopup();
	    }
	  }]);
	  return PromoPopup3D;
	}();
	PromoPopup3D.Events = Events$4;

	var Events$5 = {
	  onClose: 'onClose',
	  onDestroy: 'onDestroy',
	  onCloseClicked: 'onCloseClicked'
	};
	var Sidebar = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Sidebar, _EventEmitter);
	  function Sidebar(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Sidebar);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Sidebar).call(this));
	    _this.setEventNamespace("BX.Call.SideBar");
	    _this.container = options.container;
	    _this.width = BX.prop.getInteger(options, 'width', 200);
	    _this.elements = {
	      root: null,
	      close: null,
	      contentContainer: null
	    };
	    if (options.events) {
	      for (var eventName in options.events) {
	        if (options.events.hasOwnProperty(eventName)) {
	          _this.subscribe(eventName, options.events[eventName]);
	        }
	      }
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Sidebar, [{
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      if (this.elements.root) {
	        return this.elements.root;
	      }
	      this.elements.root = main_core.Dom.create("div", {
	        props: {
	          className: "bx-messenger-call-sidebar-root"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-sidebar-labels"
	          },
	          style: {
	            top: '39px' /*'17px'*/
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-messenger-call-sidebar-label"
	            },
	            style: {
	              maxWidth: '40px'
	            },
	            children: [main_core.Dom.create("div", {
	              props: {
	                className: "bx-messenger-call-sidebar-label-icon-box"
	              },
	              attrs: {
	                title: BX.message("IM_M_CALL_BTN_CLOSE")
	              },
	              children: [main_core.Dom.create("div", {
	                props: {
	                  className: "bx-messenger-call-sidebar-label-icon bx-messenger-call-sidebar-label-icon-close"
	                }
	              })]
	            })],
	            events: {
	              click: function click() {
	                return _this2.emit(Events$5.onCloseClicked);
	              }
	            }
	          })]
	        }), this.elements.contentContainer = main_core.Dom.create("div", {
	          props: {
	            className: "bx-messenger-call-sidebar-content-container"
	          }
	        })]
	      });
	      this.elements.root.style.setProperty('--sidebar-width', this.width + 'px');
	      return this.elements.root;
	    }
	  }, {
	    key: "setWidth",
	    value: function setWidth(width) {
	      if (this.width == width) {
	        return;
	      }
	      this.width = width;
	      this.elements.root.style.setProperty('--sidebar-width', this.width + 'px');
	    }
	  }, {
	    key: "open",
	    value: function open(animation) {
	      var _this3 = this;
	      animation = animation !== false;
	      return new Promise(function (resolve) {
	        _this3.container.appendChild(_this3.render());
	        if (animation) {
	          _this3.elements.root.classList.add('opening');
	          _this3.elements.root.addEventListener('animationend', function () {
	            _this3.elements.root.classList.remove('opening');
	            resolve();
	          }, {
	            once: true
	          });
	        } else {
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "close",
	    value: function close(animation) {
	      var _this4 = this;
	      animation = animation !== false;
	      return new Promise(function (resolve) {
	        if (animation) {
	          _this4.elements.root.classList.add('closing');
	          _this4.elements.root.addEventListener('animationend', function () {
	            _this4.container.removeChild(_this4.elements.root);
	            _this4.emit(Events$5.onClose);
	            resolve();
	          }, {
	            once: true
	          });
	        } else {
	          _this4.container.removeChild(_this4.elements.root);
	          _this4.emit(Events$5.onClose);
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "toggleHidden",
	    value: function toggleHidden(hidden) {
	      this.elements.root.classList.toggle('hidden', hidden);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.emit(Events$5.onDestroy);
	      this.unsubscribeAll(Events$5.onClose);
	      this.unsubscribeAll(Events$5.onDestroy);
	      this.eventEmitter = null;
	      this.elements = null;
	      this.container = null;
	    }
	  }]);
	  return Sidebar;
	}(main_core_events.EventEmitter);

	var WebScreenSharePopup = /*#__PURE__*/function () {
	  function WebScreenSharePopup(options) {
	    babelHelpers.classCallCheck(this, WebScreenSharePopup);
	    this.popup = null;
	    this.options = options || {};
	    this.callbacks = {
	      onClose: main_core.Type.isFunction(this.options.onClose) ? this.options.onClose : BX.DoNothing,
	      onStopSharingClick: main_core.Type.isFunction(this.options.onStopSharingClick) ? this.options.onStopSharingClick : BX.DoNothing
	    };
	  }
	  babelHelpers.createClass(WebScreenSharePopup, [{
	    key: "show",
	    value: function show() {
	      var _this = this;
	      if (this.popup) {
	        this.popup.show();
	        return;
	      }
	      var popupWidth = 400;
	      this.popup = new main_popup.Popup({
	        bindElement: this.options.bindElement,
	        targetContainer: this.options.targetContainer,
	        content: this.render(),
	        padding: 0,
	        contentPadding: 0,
	        height: 38,
	        width: popupWidth,
	        offsetTop: -15,
	        offsetLeft: this.options.bindElement.offsetWidth / 2 - popupWidth / 2 + this.options.bindElement.offsetWidth / 2,
	        className: 'bx-call-view-popup-web-screenshare',
	        contentBackground: 'unset',
	        angle: {
	          position: 'bottom',
	          offset: popupWidth / 2 - 10
	        },
	        cacheable: false,
	        events: {
	          onDestroy: function onDestroy() {
	            return _this.popup = null;
	          }
	        }
	      });
	      this.popup.show();
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      return main_core.Dom.create("div", {
	        props: {
	          className: "bx-call-view-popup-web-screenshare-body"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-popup-web-screenshare-left"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-popup-web-screenshare-icon-screen"
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-popup-web-screenshare-text"
	            },
	            text: BX.message("IM_CALL_WEB_SCREENSHARE_STATUS")
	          })]
	        }), main_core.Dom.create("div", {
	          props: {
	            className: "bx-call-view-popup-web-screenshare-right"
	          },
	          children: [main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-popup-web-screenshare-stop ui-btn ui-btn-primary ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-icon-stop"
	            },
	            text: BX.message("IM_CALL_WEB_SCREENSHARE_STOP"),
	            events: {
	              click: function click() {
	                return _this2.callbacks.onStopSharingClick();
	              }
	            }
	          }), main_core.Dom.create("div", {
	            props: {
	              className: "bx-call-view-popup-web-screenshare-close"
	            },
	            events: {
	              click: function click() {
	                _this2.popup.close();
	                _this2.callbacks.onClose();
	              }
	            }
	          })]
	        })]
	      });
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.popup) {
	        this.popup.close();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.popup) {
	        this.popup.destroy();
	      }
	    }
	  }]);
	  return WebScreenSharePopup;
	}();

	var StrategyType = {
	  AllowAll: 'AllowAll',
	  AllowNone: 'AllowNone',
	  OnlySpeaker: 'OnlySpeaker',
	  CurrentlyTalking: 'CurrentlyTalking'
	};
	var HOLD_VIDEO_SECONDS = 20;
	var VideoStrategy = /*#__PURE__*/function () {
	  function VideoStrategy(config) {
	    babelHelpers.classCallCheck(this, VideoStrategy);
	    this.call = config.call;
	    this.callView = config.callView;
	    this.strategyType = config.strategyType || StrategyType.AllowAll;

	    // event handlers
	    this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
	    this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
	    this.onCallViewSetCentralUserHandler = this.onCallViewSetCentralUser.bind(this);
	    this.onCallViewLayoutChangeHandler = this.onCallViewLayoutChange.bind(this);
	    this.users = {};
	    this.init();
	  }
	  babelHelpers.createClass(VideoStrategy, [{
	    key: "init",
	    value: function init() {
	      if (this.strategyType === StrategyType.AllowAll) {
	        this.call.allowVideoFrom(UserMnemonic.all);
	      } else if (this.strategyType === StrategyType.AllowNone) {
	        this.call.allowVideoFrom(UserMnemonic.none);
	      }
	      this.bindEvents();
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      this.call.addEventListener(CallEvent.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
	      this.call.addEventListener(CallEvent.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
	      this.callView.subscribe(View.Event.onSetCentralUser, this.onCallViewSetCentralUserHandler);
	      this.callView.subscribe(View.Event.onLayoutChange, this.onCallViewLayoutChangeHandler);
	    }
	  }, {
	    key: "removeEvents",
	    value: function removeEvents() {
	      if (this.call) {
	        this.call.removeEventListener(CallEvent.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
	        this.call.removeEventListener(CallEvent.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
	      }
	      if (this.callView) {
	        this.callView.unsubscribe(View.Event.onSetCentralUser, this.onCallViewSetCentralUserHandler);
	        this.callView.unsubscribe(View.Event.onLayoutChange, this.onCallViewLayoutChangeHandler);
	      }
	    }
	  }, {
	    key: "setType",
	    value: function setType(strategyType) {
	      if (strategyType == this.strategyType) {
	        return;
	      }
	      this.strategyType = strategyType;
	      this.applyVideoLimit();
	    }
	  }, {
	    key: "applyVideoLimit",
	    value: function applyVideoLimit() {
	      if (this.strategyType === StrategyType.AllowAll) {
	        this.call.allowVideoFrom(UserMnemonic.all);
	      } else if (this.strategyType === StrategyType.AllowNone) {
	        this.call.allowVideoFrom(UserMnemonic.none);
	      } else if (this.strategyType === StrategyType.CurrentlyTalking) {
	        var talkingUsers = this.getActiveUsers();
	        console.log("talking users", talkingUsers);
	        if (talkingUsers.length === 0) {
	          this.call.allowVideoFrom(UserMnemonic.none);
	        } else {
	          this.call.allowVideoFrom(this.getActiveUsers());
	        }
	      }
	    }
	  }, {
	    key: "getActiveUsers",
	    /**
	     * return int[]
	     */
	    value: function getActiveUsers() {
	      var result = [];
	      for (var userId in this.users) {
	        var user = this.users[userId];
	        if (user.active) {
	          result.push(user.id);
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "onUserActiveChanged",
	    value: function onUserActiveChanged() {
	      if (this.strategyType == StrategyType.CurrentlyTalking) {
	        this.applyVideoLimit();
	      }
	    }
	  }, {
	    key: "onCallUserVoiceStarted",
	    value: function onCallUserVoiceStarted(data) {
	      var userId = data.userId;
	      if (!this.users[userId]) {
	        this.users[userId] = new User({
	          id: userId,
	          onActiveChanged: this.onUserActiveChanged.bind(this)
	        });
	      }
	      this.users[userId].setTalking(true);
	    }
	  }, {
	    key: "onCallUserVoiceStopped",
	    value: function onCallUserVoiceStopped(data) {
	      var userId = data.userId;
	      if (!this.users[userId]) {
	        this.users[userId] = new User({
	          id: userId,
	          onActiveChanged: this.onUserActiveChanged.bind(this)
	        });
	      }
	      this.users[userId].setTalking(false);
	    }
	  }, {
	    key: "onCallViewSetCentralUser",
	    value: function onCallViewSetCentralUser(event) {
	      var userId = event.data.userId;
	      if (this.strategyType === StrategyType.OnlySpeaker) {
	        this.call.allowVideoFrom([userId]);
	      }
	    }
	  }, {
	    key: "onCallViewLayoutChange",
	    value: function onCallViewLayoutChange(event) {}
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.removeEvents();
	      this.call = null;
	      this.callView = null;
	      for (var userId in this.users) {
	        if (this.users.hasOwnProperty(userId)) {
	          this.users[userId].destroy();
	        }
	      }
	      this.users = {};
	    }
	  }]);
	  return VideoStrategy;
	}();
	babelHelpers.defineProperty(VideoStrategy, "Type", main_core.Type);
	var User = /*#__PURE__*/function () {
	  function User(config) {
	    babelHelpers.classCallCheck(this, User);
	    this.id = config.id;
	    this.talking = false;
	    this.sharing = false;
	    this.active = false;
	    this.callbacks = {
	      onActiveChanged: main_core.Type.isFunction(config.onActiveChanged) ? config.onActiveChanged : BX.DoNothing
	    };
	    this.turnOffVideoTimeout = null;
	  }
	  babelHelpers.createClass(User, [{
	    key: "setTalking",
	    value: function setTalking(talking) {
	      if (this.talking == talking) {
	        return;
	      }
	      this.talking = talking;
	      if (this.talking) {
	        this.cancelTurnOffVideo();
	        this.updateActive();
	      } else {
	        this.scheduleTurnOffVideo();
	      }
	    }
	  }, {
	    key: "setSharing",
	    value: function setSharing(sharing) {
	      if (this.sharing == sharing) {
	        return;
	      }
	      this.sharing = sharing;
	      if (this.sharing) {
	        this.cancelTurnOffVideo();
	        this.updateActive();
	      } else {
	        this.scheduleTurnOffVideo();
	      }
	    }
	  }, {
	    key: "updateActive",
	    value: function updateActive() {
	      var newActive = !!(this.sharing || this.talking || this.turnOffVideoTimeout);
	      if (newActive != this.active) {
	        this.active = newActive;
	      }
	      this.callbacks.onActiveChanged({
	        userId: this.id,
	        active: this.active
	      });
	    }
	  }, {
	    key: "scheduleTurnOffVideo",
	    value: function scheduleTurnOffVideo() {
	      var _this = this;
	      clearTimeout(this.turnOffVideoTimeout);
	      this.turnOffVideoTimeout = setTimeout(function () {
	        _this.turnOffVideoTimeout = null;
	        _this.updateActive();
	      }, HOLD_VIDEO_SECONDS * 1000);
	    }
	  }, {
	    key: "cancelTurnOffVideo",
	    value: function cancelTurnOffVideo() {
	      clearTimeout(this.turnOffVideoTimeout);
	      this.turnOffVideoTimeout = null;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.callbacks.onActiveChanged = BX.DoNothing;
	      clearTimeout(this.turnOffVideoTimeout);
	    }
	  }]);
	  return User;
	}();

	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var Events$6 = {
	  onViewStateChanged: 'onViewStateChanged',
	  onOpenVideoConference: 'onOpenVideoConference',
	  onPromoViewed: 'onPromoViewed',
	  onCallJoined: 'onCallJoined',
	  onCallLeft: 'onCallLeft',
	  onCallDestroyed: 'onCallDestroyed'
	};
	var ViewState = {
	  Opened: 'Opened',
	  Closed: 'Closed',
	  Folded: 'Folded'
	};
	var DocumentType = {
	  Resume: 'resume',
	  Blank: 'blank'
	};
	var FeatureState = {
	  Enabled: 'enabled',
	  Disabled: 'disabled',
	  Limited: 'limited'
	};
	var DOC_EDITOR_WIDTH = 961;
	var DOC_TEMPLATE_WIDTH = 328;
	var DOC_CREATED_EVENT = 'CallController::documentCreated';
	var DOCUMENT_PROMO_CODE = 'im:call-document:16102021:web';
	var DOCUMENT_PROMO_DELAY = 5 * 60 * 1000; // 5 minutes
	var FILE_TYPE_DOCX = 'docx';
	var FILE_TYPE_XLSX = 'xlsx';
	var FILE_TYPE_PPTX = 'pptx';
	var MASK_PROMO_CODE = 'im:mask:06122022:desktop';
	var MASK_PROMO_DELAY = 5 * 60 * 1000; // 5 minutes
	var _subscribeEvents$1 = /*#__PURE__*/new WeakSet();
	var _getCallDetail = /*#__PURE__*/new WeakSet();
	var CallController = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(CallController, _EventEmitter);
	  function CallController(_config) {
	    var _this;
	    babelHelpers.classCallCheck(this, CallController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CallController).call(this));
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _getCallDetail);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _subscribeEvents$1);
	    _this.setEventNamespace('BX.Call.Controller');
	    var needInit = BX.prop.getBoolean(_config, "init", true);
	    _this.language = _config.language || 'en';
	    _this.incomingVideoStrategyType = _config.incomingVideoStrategyType || VideoStrategy.Type.AllowAll;
	    _this.formatRecordDate = _config.formatRecordDate || 'd.m.Y';
	    _this.messengerFacade = _config.messengerFacade;
	    _this.inited = false;
	    _this.container = null;
	    _this.docEditor = null;
	    _this.docEditorIframe = null;
	    _this.maxEditorWidth = DOC_TEMPLATE_WIDTH;
	    _this.docCreatedForCurrentCall = false;
	    _this.folded = false;
	    _this.childCall = null;
	    _this.invitePopup = null;
	    /** @var {VideoStrategy} this.currentCall */
	    _this.videoStrategy = null;
	    _this.isHttps = window.location.protocol === "https:";
	    _this.callWithLegacyMobile = false;
	    _this.featureScreenSharing = FeatureState.Enabled;
	    _this.featureRecord = FeatureState.Enabled;
	    _this.callRecordState = View.RecordState.Stopped;
	    _this.callRecordType = View.RecordType.None;
	    _this.autoCloseCallView = true;
	    _this.talkingUsers = {};
	    _this._callViewState = ViewState.Closed;

	    // event handlers
	    _this._onCallUserInvitedHandler = _this._onCallUserInvited.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallDestroyHandler = _this._onCallDestroy.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserStateChangedHandler = _this._onCallUserStateChanged.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserMicrophoneStateHandler = _this._onCallUserMicrophoneState.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserCameraStateHandler = _this._onCallUserCameraState.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserVideoPausedHandler = _this._onCallUserVideoPaused.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallLocalMediaReceivedHandler = _this._onCallLocalMediaReceived.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallLocalMediaStoppedHandler = _this._onCallLocalMediaStopped.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallLocalCameraFlipHandler = _this._onCallLocalCameraFlip.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallLocalCameraFlipInDesktopHandler = _this._onCallLocalCameraFlipInDesktop.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallRemoteMediaReceivedHandler = _this._onCallRemoteMediaReceived.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallRemoteMediaStoppedHandler = _this._onCallRemoteMediaStopped.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserVoiceStartedHandler = _this._onCallUserVoiceStarted.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserVoiceStoppedHandler = _this._onCallUserVoiceStopped.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserScreenStateHandler = _this._onCallUserScreenState.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallUserRecordStateHandler = _this._onCallUserRecordState.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onCallUserFloorRequestHandler = _this._onCallUserFloorRequest.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallFailureHandler = _this._onCallFailure.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onNetworkProblemHandler = _this._onNetworkProblem.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onMicrophoneLevelHandler = _this._onMicrophoneLevel.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onReconnectingHandler = _this._onReconnecting.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onReconnectedHandler = _this._onReconnected.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCustomMessageHandler = _this._onCustomMessage.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onJoinRoomOfferHandler = _this._onJoinRoomOffer.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onJoinRoomHandler = _this._onJoinRoom.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onLeaveRoomHandler = _this._onLeaveRoom.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onTransferRoomSpeakerHandler = _this._onTransferRoomSpeaker.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallLeaveHandler = _this._onCallLeave.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onCallJoinHandler = _this._onCallJoin.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onBeforeUnloadHandler = _this._onBeforeUnload.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onImTabChangeHandler = _this._onImTabChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onUpdateChatCounterHandler = _this._onUpdateChatCounter.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onChildCallFirstMediaHandler = _this._onChildCallFirstMedia.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onWindowFocusHandler = _this._onWindowFocus.bind(babelHelpers.assertThisInitialized(_this));
	    _this._onWindowBlurHandler = _this._onWindowBlur.bind(babelHelpers.assertThisInitialized(_this));
	    if (BX.desktop && false) {
	      _this.floatingWindow = new FloatingVideo({
	        onMainAreaClick: _this._onFloatingVideoMainAreaClick.bind(babelHelpers.assertThisInitialized(_this)),
	        onButtonClick: _this._onFloatingVideoButtonClick.bind(babelHelpers.assertThisInitialized(_this))
	      });
	      _this.floatingWindowUser = 0;
	    }
	    _this.showFloatingWindowTimeout = 0;
	    _this.hideIncomingCallTimeout = 0;
	    if (BX.desktop) {
	      _this.floatingScreenShareWindow = new FloatingScreenShare({
	        darkMode: _this.messengerFacade.isThemeDark(),
	        onBackToCallClick: _this._onFloatingScreenShareBackToCallClick.bind(babelHelpers.assertThisInitialized(_this)),
	        onStopSharingClick: _this._onFloatingScreenShareStopClick.bind(babelHelpers.assertThisInitialized(_this)),
	        onChangeScreenClick: _this._onFloatingScreenShareChangeScreenClick.bind(babelHelpers.assertThisInitialized(_this))
	      });
	    }
	    _this.showFloatingScreenShareWindowTimeout = 0;
	    _this.mutePopup = null;
	    _this.allowMutePopup = true;
	    _this.webScreenSharePopup = null;
	    _this.feedbackPopup = null;
	    _this.resizeObserver = new BX.ResizeObserver(_this._onResize.bind(babelHelpers.assertThisInitialized(_this)));
	    if (needInit) {
	      _this.init();
	      _classPrivateMethodGet$4(babelHelpers.assertThisInitialized(_this), _subscribeEvents$1, _subscribeEvents2$1).call(babelHelpers.assertThisInitialized(_this), _config);
	    }
	    return _this;
	  }
	  babelHelpers.createClass(CallController, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      BX.addCustomEvent(window, "CallEvents::incomingCall", this.onIncomingCall.bind(this));
	      Hardware.subscribe(Hardware.Events.deviceChanged, this._onDeviceChange.bind(this));
	      Hardware.subscribe(Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipHandler);
	      if (BX.desktop && this.floatingWindow) {
	        window.addEventListener("blur", this._onWindowBlurHandler);
	        window.addEventListener("focus", this._onWindowFocusHandler);
	        BX.desktop.addCustomEvent("BXForegroundChanged", function (focus) {
	          if (focus) {
	            _this2._onWindowFocus();
	          } else {
	            _this2._onWindowBlur();
	          }
	        });
	      }
	      if (BX.desktop && this.floatingScreenShareWindow) {
	        BX.desktop.addCustomEvent("BXScreenMediaSharing", function (id, title, x, y, width, height, app) {
	          _this2.floatingScreenShareWindow.close();
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
	            console.error('setSharingData error', error);
	          });
	        });
	        window.addEventListener("blur", this._onWindowBlurHandler);
	        window.addEventListener("focus", this._onWindowFocusHandler);
	        BX.desktop.addCustomEvent("BXForegroundChanged", function (focus) {
	          if (focus) {
	            _this2._onWindowFocus();
	          } else {
	            _this2._onWindowBlur();
	          }
	        });
	      }
	      if (BX.desktop) {
	        BX.desktop.addCustomEvent(Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipInDesktopHandler);
	      }
	      if (window['VoxImplant']) {
	        VoxImplant.getInstance().addEventListener(VoxImplant.Events.MicAccessResult, this.voxMicAccessResult.bind(this));
	      }
	      window.addEventListener("beforeunload", this._onBeforeUnloadHandler);
	      BX.addCustomEvent("OnDesktopTabChange", this._onImTabChangeHandler);
	      BX.addCustomEvent(window, "onImUpdateCounterMessage", this._onUpdateChatCounter.bind(this));
	      BX.garbage(this.destroy, this);
	      this.inited = true;
	    }
	    /**
	     * Workaround to get current microphoneId
	     * @param e
	     */
	  }, {
	    key: "voxMicAccessResult",
	    value: function voxMicAccessResult(e) {
	      if (e.stream && e.stream.getAudioTracks().length > 0 && this.callView) {
	        this.callView.microphoneId = e.stream.getAudioTracks()[0].getSettings().deviceId;
	      }
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
	    key: "getActiveCallUsers",
	    value: function getActiveCallUsers() {
	      var userStates = this.currentCall.getUsers();
	      var activeUsers = [];
	      for (var userId in userStates) {
	        if (userStates.hasOwnProperty(userId)) {
	          if (userStates[userId] === UserState.Connected || userStates[userId] === UserState.Connecting || userStates[userId] === UserState.Calling) {
	            activeUsers.push(userId);
	          }
	        }
	      }
	      return activeUsers;
	    }
	  }, {
	    key: "updateFloatingWindowContent",
	    value: function updateFloatingWindowContent() {
	      var _this3 = this;
	      if (!this.floatingWindow || !this.currentCall) {
	        return;
	      }
	      this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);
	      Util$1.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then(function (result) {
	        _this3.floatingWindow.setAvatars(result);
	      });
	    }
	  }, {
	    key: "onIncomingCall",
	    value: function onIncomingCall(e) {
	      var _this4 = this;
	      console.warn("incoming.call", e);
	      /** @var {PlainCall|VoximplantCall} newCall */
	      var newCall = e.call;
	      var isCurrentCallActive = this.currentCall && (this.callView || this.callNotification);
	      this.callWithLegacyMobile = e.isLegacyMobile === true;
	      if (!isCurrentCallActive) {
	        if (this.callView) {
	          return;
	        }
	        this.checkDesktop().then(function () {
	          // don't wait for init here to speedup process
	          Hardware.init();
	          if (_this4.currentCall || newCall.state == CallState.Finished) {
	            return;
	          }
	          _this4.currentCall = newCall;
	          _this4.bindCallEvents();
	          _this4.updateFloatingWindowContent();
	          if (_this4.currentCall.associatedEntity.type === 'chat' && _this4.currentCall.associatedEntity.advanced['chatType'] === 'videoconf') {
	            if (_this4.isConferencePageOpened(_this4.currentCall.associatedEntity.id)) {
	              // conference page is already opened, do nothing
	              _this4.removeCallEvents();
	              _this4.currentCall = null;
	            } else {
	              _this4.showIncomingConference();
	            }
	          } else {
	            var video = e.video === true;
	            _this4.showIncomingCall({
	              video: video
	            });
	            Hardware.init().then(function () {
	              if (!Hardware.hasCamera()) {
	                if (video) {
	                  _this4.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
	                }
	                if (_this4.callNotification) {
	                  _this4.callNotification.setHasCamera(false);
	                }
	              }
	            });
	          }
	        }, function (error) {
	          if (_this4.currentCall) {
	            _this4.removeVideoStrategy();
	            _this4.removeCallEvents();
	            _this4.currentCall = null;
	          }
	          console.error(error);
	          _this4.log(error);
	          if (!_this4.isHttps) {
	            _this4.showNotification(BX.message("IM_CALL_INCOMING_ERROR_HTTPS_REQUIRED"));
	          } else {
	            _this4.showNotification(BX.message("IM_CALL_INCOMING_UNSUPPORTED_BROWSER"));
	          }
	        });
	      } else {
	        if (newCall.id == this.currentCall.id) ; else if (newCall.parentId == this.currentCall.id) {
	          if (!this.childCall) {
	            this.childCall = newCall;
	            this.childCall.users.forEach(function (userId) {
	              return _this4.callView.addUser(userId, UserState.Calling);
	            });
	            this.updateCallViewUsers(newCall.id, this.childCall.users);
	            this.answerChildCall();
	          }
	        } else {
	          // send busy
	          newCall.decline(486);
	          return false;
	        }
	      }
	    }
	  }, {
	    key: "bindCallEvents",
	    value: function bindCallEvents() {
	      this.currentCall.addEventListener(CallEvent.onUserInvited, this._onCallUserInvitedHandler);
	      this.currentCall.addEventListener(CallEvent.onDestroy, this._onCallDestroyHandler);
	      this.currentCall.addEventListener(CallEvent.onUserStateChanged, this._onCallUserStateChangedHandler);
	      this.currentCall.addEventListener(CallEvent.onUserMicrophoneState, this._onCallUserMicrophoneStateHandler);
	      this.currentCall.addEventListener(CallEvent.onUserCameraState, this._onCallUserCameraStateHandler);
	      this.currentCall.addEventListener(CallEvent.onUserVideoPaused, this._onCallUserVideoPausedHandler);
	      this.currentCall.addEventListener(CallEvent.onUserScreenState, this._onCallUserScreenStateHandler);
	      this.currentCall.addEventListener(CallEvent.onUserRecordState, this._onCallUserRecordStateHandler);
	      this.currentCall.addEventListener(CallEvent.onUserFloorRequest, this.onCallUserFloorRequestHandler);
	      this.currentCall.addEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
	      this.currentCall.addEventListener(CallEvent.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
	      this.currentCall.addEventListener(CallEvent.onRemoteMediaReceived, this._onCallRemoteMediaReceivedHandler);
	      this.currentCall.addEventListener(CallEvent.onRemoteMediaStopped, this._onCallRemoteMediaStoppedHandler);
	      this.currentCall.addEventListener(CallEvent.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
	      this.currentCall.addEventListener(CallEvent.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
	      this.currentCall.addEventListener(CallEvent.onCallFailure, this._onCallFailureHandler);
	      this.currentCall.addEventListener(CallEvent.onNetworkProblem, this._onNetworkProblemHandler);
	      this.currentCall.addEventListener(CallEvent.onMicrophoneLevel, this._onMicrophoneLevelHandler);
	      this.currentCall.addEventListener(CallEvent.onReconnecting, this._onReconnectingHandler);
	      this.currentCall.addEventListener(CallEvent.onReconnected, this._onReconnectedHandler);
	      this.currentCall.addEventListener(CallEvent.onCustomMessage, this._onCustomMessageHandler);
	      this.currentCall.addEventListener(CallEvent.onJoinRoomOffer, this._onJoinRoomOfferHandler);
	      this.currentCall.addEventListener(CallEvent.onJoinRoom, this._onJoinRoomHandler);
	      this.currentCall.addEventListener(CallEvent.onLeaveRoom, this._onLeaveRoomHandler);
	      this.currentCall.addEventListener(CallEvent.onTransferRoomSpeaker, this._onTransferRoomSpeakerHandler);
	      this.currentCall.addEventListener(CallEvent.onJoin, this._onCallJoinHandler);
	      this.currentCall.addEventListener(CallEvent.onLeave, this._onCallLeaveHandler);
	    }
	  }, {
	    key: "removeCallEvents",
	    value: function removeCallEvents() {
	      this.currentCall.removeEventListener(CallEvent.onUserInvited, this._onCallUserInvitedHandler);
	      this.currentCall.removeEventListener(CallEvent.onDestroy, this._onCallDestroyHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserStateChanged, this._onCallUserStateChangedHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserMicrophoneState, this._onCallUserMicrophoneStateHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserCameraState, this._onCallUserCameraStateHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserVideoPaused, this._onCallUserVideoPausedHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserScreenState, this._onCallUserScreenStateHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserRecordState, this._onCallUserRecordStateHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserFloorRequest, this.onCallUserFloorRequestHandler);
	      this.currentCall.removeEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
	      this.currentCall.removeEventListener(CallEvent.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
	      this.currentCall.removeEventListener(CallEvent.onRemoteMediaReceived, this._onCallRemoteMediaReceivedHandler);
	      this.currentCall.removeEventListener(CallEvent.onRemoteMediaStopped, this._onCallRemoteMediaStoppedHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
	      this.currentCall.removeEventListener(CallEvent.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
	      this.currentCall.removeEventListener(CallEvent.onCallFailure, this._onCallFailureHandler);
	      this.currentCall.removeEventListener(CallEvent.onNetworkProblem, this._onNetworkProblemHandler);
	      this.currentCall.removeEventListener(CallEvent.onMicrophoneLevel, this._onMicrophoneLevelHandler);
	      this.currentCall.removeEventListener(CallEvent.onReconnecting, this._onReconnectingHandler);
	      this.currentCall.removeEventListener(CallEvent.onReconnected, this._onReconnectedHandler);
	      this.currentCall.removeEventListener(CallEvent.onCustomMessage, this._onCustomMessageHandler);
	      this.currentCall.removeEventListener(CallEvent.onJoin, this._onCallJoinHandler);
	      this.currentCall.removeEventListener(CallEvent.onLeave, this._onCallLeaveHandler);
	    }
	  }, {
	    key: "bindCallViewEvents",
	    value: function bindCallViewEvents() {
	      this.callView.setCallback(View.Event.onShow, this._onCallViewShow.bind(this));
	      this.callView.setCallback(View.Event.onClose, this._onCallViewClose.bind(this));
	      this.callView.setCallback(View.Event.onDestroy, this._onCallViewDestroy.bind(this));
	      this.callView.setCallback(View.Event.onButtonClick, this._onCallViewButtonClick.bind(this));
	      this.callView.setCallback(View.Event.onBodyClick, this._onCallViewBodyClick.bind(this));
	      this.callView.setCallback(View.Event.onReplaceCamera, this._onCallViewReplaceCamera.bind(this));
	      this.callView.setCallback(View.Event.onReplaceMicrophone, this._onCallViewReplaceMicrophone.bind(this));
	      this.callView.setCallback(View.Event.onSetCentralUser, this._onCallViewSetCentralUser.bind(this));
	      this.callView.setCallback(View.Event.onChangeHdVideo, this._onCallViewChangeHdVideo.bind(this));
	      this.callView.setCallback(View.Event.onChangeMicAutoParams, this._onCallViewChangeMicAutoParams.bind(this));
	      this.callView.setCallback(View.Event.onChangeFaceImprove, this._onCallViewChangeFaceImprove.bind(this));
	      this.callView.setCallback(View.Event.onOpenAdvancedSettings, this._onCallViewOpenAdvancedSettings.bind(this));
	      this.callView.setCallback(View.Event.onReplaceSpeaker, this._onCallViewReplaceSpeaker.bind(this));
	    }
	  }, {
	    key: "updateCallViewUsers",
	    value: function updateCallViewUsers(callId, userList) {
	      var _this5 = this;
	      Util$1.getUsers(callId, userList).then(function (userData) {
	        if (_this5.callView) {
	          _this5.callView.updateUserData(userData);
	        }
	      });
	    }
	  }, {
	    key: "createVideoStrategy",
	    value: function createVideoStrategy() {
	      if (this.videoStrategy) {
	        this.videoStrategy.destroy();
	      }
	      var strategyType = this.incomingVideoStrategyType;
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
	    key: "setFeatureScreenSharing",
	    value: function setFeatureScreenSharing(enable) {
	      this.featureScreenSharing = enable;
	    }
	  }, {
	    key: "setFeatureRecord",
	    value: function setFeatureRecord(enable) {
	      this.featureRecord = enable;
	    }
	  }, {
	    key: "setVideoStrategyType",
	    value: function setVideoStrategyType(type) {
	      if (this.videoStrategy) {
	        this.videoStrategy.setType(type);
	      }
	    }
	  }, {
	    key: "createContainer",
	    value: function createContainer() {
	      this.container = BX.create("div", {
	        props: {
	          className: "bx-messenger-call-overlay"
	        }
	      });
	      var externalContainer = this.messengerFacade.getContainer();
	      externalContainer.insertBefore(this.container, externalContainer.firstChild);
	      externalContainer.classList.add("bx-messenger-call");
	    }
	  }, {
	    key: "removeContainer",
	    value: function removeContainer() {
	      if (this.container) {
	        main_core.Dom.remove(this.container);
	        this.container = null;
	        this.messengerFacade.getContainer().classList.remove("bx-messenger-call");
	      }
	    }
	  }, {
	    key: "answerChildCall",
	    value: function answerChildCall() {
	      this.removeCallEvents();
	      this.removeVideoStrategy();
	      this.childCall.addEventListener(CallEvent.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);
	      this.childCall.addEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
	      this.childCall.answer({
	        useVideo: this.currentCall.isVideoEnabled()
	      });
	    }
	  }, {
	    key: "_onChildCallFirstMedia",
	    value: function _onChildCallFirstMedia(e) {
	      this.log("Finishing one-to-one call, switching to group call");
	      var previousRecordType = View.RecordType.None;
	      if (this.isRecording()) {
	        previousRecordType = this.callRecordType;
	        BXDesktopSystem.CallRecordStop();
	        this.callRecordState = View.RecordState.Stopped;
	        this.callRecordType = View.RecordType.None;
	        this.callView.setRecordState(this.callView.getDefaultRecordState());
	        this.callView.setButtonActive('record', false);
	      }
	      this.callView.showButton('floorRequest');
	      if (this.callView) {
	        if ("track" in e) {
	          this.callView.setUserMedia(e.userId, e.kind, e.track);
	        }
	        if ("mediaRenderer" in e && e.mediaRenderer.kind === "audio") {
	          this.callView.setUserMedia(e.userId, 'audio', e.mediaRenderer.stream.getAudioTracks()[0]);
	        }
	        if ("mediaRenderer" in e && (e.mediaRenderer.kind === "video" || e.mediaRenderer.kind === "sharing")) {
	          this.callView.setVideoRenderer(e.userId, e.mediaRenderer);
	        }
	      }
	      this.childCall.removeEventListener(CallEvent.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);
	      this.removeCallEvents();
	      var oldCall = this.currentCall;
	      oldCall.hangup();
	      this.currentCall = this.childCall;
	      this.childCall = null;
	      if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id) {
	        this.messengerFacade.openMessenger(this.currentCall.associatedEntity.id);
	      }
	      if (oldCall.muted) {
	        this.currentCall.setMuted(true);
	      }
	      this.bindCallEvents();
	      this.createVideoStrategy();
	      if (previousRecordType !== View.RecordType.None) {
	        this._startRecordCall(previousRecordType);
	      }
	    }
	  }, {
	    key: "checkDesktop",
	    value: function checkDesktop() {
	      if (main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager')) {
	        return new Promise(function (resolve) {
	          BX.Messenger.v2.Lib.DesktopManager.getInstance().checkRunStatus().then(function () {})["catch"](function () {
	            return resolve();
	          });
	        });
	      }
	      if (main_core.Reflection.getClass('BX.desktopUtils')) {
	        return new Promise(function (resolve) {
	          BX.desktopUtils.runningCheck(function () {}, function () {
	            return resolve();
	          });
	        });
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "isMutedPopupAllowed",
	    value: function isMutedPopupAllowed() {
	      if (!this.allowMutePopup || !this.currentCall) {
	        return false;
	      }
	      var currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
	      return !currentRoom || currentRoom.speaker == this.userId;
	    }
	  }, {
	    key: "isConferencePageOpened",
	    value: function isConferencePageOpened(dialogId) {
	      var tagPresent = im_lib_localstorage.LocalStorage.get(CallEngine.getSiteId(), CallEngine.getCurrentUserId(), CallEngine.getConferencePageTag(dialogId), 'N');
	      return tagPresent === 'Y';
	    }
	    /**
	     * @param {Object} params
	     * @param {bool} [params.video = false]
	     */
	  }, {
	    key: "showIncomingCall",
	    value: function showIncomingCall(params) {
	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }
	      params.video = params.video === true;
	      if (this.feedbackPopup) {
	        this.feedbackPopup.close();
	      }
	      var allowVideo = this.callWithLegacyMobile ? params.video === true : true;
	      this.callNotification = new IncomingNotification({
	        callerName: this.currentCall.associatedEntity.name,
	        callerAvatar: this.currentCall.associatedEntity.avatar,
	        callerType: this.currentCall.associatedEntity.advanced.chatType,
	        callerColor: this.currentCall.associatedEntity.avatarColor,
	        video: params.video,
	        hasCamera: allowVideo,
	        zIndex: this.messengerFacade.getDefaultZIndex() + 200,
	        onClose: this._onCallNotificationClose.bind(this),
	        onDestroy: this._onCallNotificationDestroy.bind(this),
	        onButtonClick: this._onCallNotificationButtonClick.bind(this)
	      });
	      this.callNotification.show();
	      this.scheduleCancelNotification();
	      this.messengerFacade.repeatSound('ringtone', 3500, true);
	    }
	  }, {
	    key: "showIncomingConference",
	    value: function showIncomingConference() {
	      this.callNotification = new ConferenceNotifications({
	        zIndex: this.messengerFacade.getDefaultZIndex() + 200,
	        callerName: this.currentCall.associatedEntity.name,
	        callerAvatar: this.currentCall.associatedEntity.avatar,
	        callerColor: this.currentCall.associatedEntity.avatarColor,
	        onClose: this._onCallNotificationClose.bind(this),
	        onDestroy: this._onCallNotificationDestroy.bind(this),
	        onButtonClick: this._onCallConferenceNotificationButtonClick.bind(this)
	      });
	      this.callNotification.show();
	      this.scheduleCancelNotification();
	      this.messengerFacade.repeatSound('ringtone', 3500, true);
	    }
	  }, {
	    key: "scheduleCancelNotification",
	    value: function scheduleCancelNotification() {
	      var _this6 = this;
	      clearTimeout(this.hideIncomingCallTimeout);
	      this.hideIncomingCallTimeout = setTimeout(function () {
	        if (_this6.callNotification) {
	          _this6.callNotification.close();
	        }
	        if (_this6.currentCall) {
	          _this6.removeVideoStrategy();
	          _this6.removeCallEvents();
	          _this6.currentCall = null;
	        }
	      }, 30 * 1000);
	    }
	  }, {
	    key: "showNotification",
	    value: function showNotification(notificationText, actions) {
	      if (!actions) {
	        actions = [];
	      }
	      BX.UI.Notification.Center.notify({
	        content: main_core.Text.encode(notificationText),
	        position: "top-right",
	        autoHideDelay: 5000,
	        closeButton: true,
	        actions: actions
	      });
	    }
	  }, {
	    key: "showNetworkProblemNotification",
	    value: function showNetworkProblemNotification(notificationText) {
	      BX.UI.Notification.Center.notify({
	        content: main_core.Text.encode(notificationText),
	        position: "top-right",
	        autoHideDelay: 5000,
	        closeButton: true,
	        actions: [{
	          title: BX.message("IM_M_CALL_HELP"),
	          events: {
	            click: function click(event, balloon) {
	              top.BX.Helper.show('redirect=detail&code=12723718');
	              balloon.close();
	            }
	          }
	        }]
	      });
	    }
	  }, {
	    key: "showUnsupportedNotification",
	    value: function showUnsupportedNotification() {
	      var messageBox;
	      if (BX.desktop && BX.desktop.apiReady) {
	        messageBox = new ui_dialogs_messagebox.MessageBox({
	          message: BX.message('IM_CALL_DESKTOP_TOO_OLD'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          okCaption: BX.message('IM_M_CALL_BTN_UPDATE'),
	          cancelCaption: BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
	          onOk: function onOk() {
	            var url = main_core.Browser.isMac() ? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg" : "http://dl.bitrix24.com/b24/bitrix24_desktop.exe";
	            window.open(url, "desktopApp");
	            return true;
	          }
	        });
	      } else {
	        messageBox = new ui_dialogs_messagebox.MessageBox({
	          message: BX.message('IM_CALL_WEBRTC_USE_CHROME_OR_DESKTOP'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          okCaption: BX.message("IM_CALL_DETAILS"),
	          cancelCaption: BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
	          onOk: function onOk() {
	            top.BX.Helper.show("redirect=detail&code=11387752");
	            return true;
	          }
	        });
	      }
	      messageBox.show();
	    }
	  }, {
	    key: "isUserAgentSupported",
	    value: function isUserAgentSupported() {
	      if (BX.desktop && BX.desktop.apiReady) {
	        return BX.desktop.enableInVersion(48);
	      }
	      if ('VoxImplant' in window) {
	        return VoxImplant.getInstance().isRTCsupported();
	      }
	      return Util$1.isWebRTCSupported();
	    }
	  }, {
	    key: "getBlockedButtons",
	    value: function getBlockedButtons() {
	      var result = ['record'];
	      if (!this.messengerFacade.showUserSelector) {
	        result.push('add');
	      }
	      return result;
	    }
	  }, {
	    key: "startCall",
	    value: function startCall(dialogId, video) {
	      var _this7 = this;
	      if (!this.isUserAgentSupported()) {
	        this.showUnsupportedNotification();
	        return;
	      }
	      if (this.callView || this.currentCall) {
	        return;
	      }
	      if (this.feedbackPopup) {
	        this.feedbackPopup.close();
	      }
	      var provider = Provider.Plain;
	      if (Util$1.isCallServerAllowed() && dialogId.toString().startsWith("chat")) {
	        provider = Provider.Voximplant;
	      }
	      var debug1 = +new Date();
	      this.messengerFacade.openMessenger(dialogId).then(function () {
	        return Hardware.init();
	      }).then(function () {
	        _this7.createContainer();
	        var hiddenButtons = [];
	        if (provider === Provider.Plain) {
	          hiddenButtons.push('floorRequest');
	        }
	        if (!Util$1.shouldShowDocumentButton()) {
	          hiddenButtons.push('document');
	        }
	        _this7.callView = new View({
	          container: _this7.container,
	          baseZIndex: _this7.messengerFacade.getDefaultZIndex(),
	          showChatButtons: true,
	          userLimit: Util$1.getUserLimit(),
	          language: _this7.language,
	          layout: dialogId.toString().startsWith("chat") ? View.Layout.Grid : View.Layout.Centered,
	          microphoneId: Hardware.defaultMicrophone,
	          showShareButton: _this7.featureScreenSharing !== FeatureState.Disabled,
	          showRecordButton: _this7.featureRecord !== FeatureState.Disabled,
	          hiddenButtons: hiddenButtons,
	          blockedButtons: _this7.getBlockedButtons()
	        });
	        _this7.bindCallViewEvents();
	        if (video && !Hardware.hasCamera()) {
	          _this7.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
	          video = false;
	        }
	        return CallEngine.createCall({
	          entityType: 'chat',
	          entityId: dialogId,
	          provider: provider,
	          videoEnabled: !!video,
	          enableMicAutoParameters: Hardware.enableMicAutoParameters,
	          joinExisting: true
	        });
	      }).then(function (e) {
	        var debug2 = +new Date();
	        _this7.currentCall = e.call;
	        _this7.log("Call creation time: " + (debug2 - debug1) / 1000 + " seconds");
	        _this7.currentCall.useHdVideo(Hardware.preferHdQuality);
	        if (Hardware.defaultMicrophone) {
	          _this7.currentCall.setMicrophoneId(Hardware.defaultMicrophone);
	        }
	        if (Hardware.defaultCamera) {
	          _this7.currentCall.setCameraId(Hardware.defaultCamera);
	        }
	        if (_this7.currentCall.associatedEntity && _this7.currentCall.associatedEntity.id) {
	          if (_this7.messengerFacade.getCurrentDialogId() != _this7.currentCall.associatedEntity.id) {
	            _this7.messengerFacade.openMessenger(_this7.currentCall.associatedEntity.id);
	          }
	        }
	        _this7.autoCloseCallView = true;
	        _this7.bindCallEvents();
	        _this7.createVideoStrategy();
	        _this7.callView.appendUsers(_this7.currentCall.getUsers());
	        _this7.callView.show();
	        _this7.updateCallViewUsers(_this7.currentCall.id, _this7.getCallUsers(true));
	        _this7.showDocumentPromo();
	        _this7.showMaskPromo();
	        if (e.isNew) {
	          _this7.log("Inviting users");
	          _this7.currentCall.inviteUsers();
	          _this7.messengerFacade.repeatSound('dialtone', 5000, true);
	        } else {
	          _this7.log("Joining existing call");
	          _this7.currentCall.answer({
	            useVideo: video
	          });
	        }
	      })["catch"](function (error) {
	        console.error(error);
	        var errorCode;
	        if (typeof error == "string") {
	          errorCode = error;
	        } else if (babelHelpers["typeof"](error) == "object" && error.code) {
	          errorCode = error.code == 'access_denied' ? 'ACCESS_DENIED' : error.code;
	        } else {
	          errorCode = 'UNKNOWN_ERROR';
	        }
	        _this7._onCallFailure({
	          code: errorCode,
	          message: error.message || ""
	        });
	      });
	    }
	  }, {
	    key: "joinCall",
	    value: function joinCall(callId, video, options) {
	      var _this8 = this;
	      var joinAsViewer = BX.prop.getBoolean(options, "joinAsViewer", false);
	      if (!this.isUserAgentSupported()) {
	        this.showUnsupportedNotification();
	        return;
	      }
	      if (this.callView || this.currentCall) {
	        return;
	      }
	      var isGroupCall;
	      this.log("Joining call " + callId);
	      CallEngine.getCallWithId(callId).then(function (result) {
	        _this8.currentCall = result.call;
	        isGroupCall = _this8.currentCall.associatedEntity.id.toString().startsWith("chat");
	        return _this8.messengerFacade.openMessenger();
	      }).then(function () {
	        return Hardware.init();
	      }).then(function () {
	        _this8.createContainer();
	        var hiddenButtons = [];
	        if (_this8.currentCall instanceof PlainCall) {
	          hiddenButtons.push('floorRequest');
	        }
	        if (!Util$1.shouldShowDocumentButton()) {
	          hiddenButtons.push('document');
	        }
	        _this8.callView = new View({
	          container: _this8.container,
	          baseZIndex: _this8.messengerFacade.getDefaultZIndex(),
	          showChatButtons: true,
	          userLimit: Util$1.getUserLimit(),
	          language: _this8.language,
	          layout: isGroupCall ? View.Layout.Grid : View.Layout.Centered,
	          showRecordButton: _this8.featureRecord !== FeatureState.Disabled,
	          microphoneId: Hardware.defaultMicrophone,
	          hiddenButtons: hiddenButtons,
	          blockedButtons: _this8.getBlockedButtons()
	        });
	        _this8.autoCloseCallView = true;
	        _this8.bindCallViewEvents();
	        _this8.callView.appendUsers(_this8.currentCall.getUsers());
	        _this8.updateCallViewUsers(_this8.currentCall.id, _this8.getCallUsers(true));
	        _this8.callView.show();
	        _this8.showDocumentPromo();
	        _this8.currentCall.useHdVideo(Hardware.preferHdQuality);
	        if (Hardware.defaultMicrophone) {
	          _this8.currentCall.setMicrophoneId(Hardware.defaultMicrophone);
	        }
	        if (Hardware.defaultCamera) {
	          _this8.currentCall.setCameraId(Hardware.defaultCamera);
	        }
	        _this8.bindCallEvents();
	        _this8.createVideoStrategy();
	        if (video && !Hardware.hasCamera()) {
	          _this8.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
	          video = false;
	        }
	        if (_this8.getCallUsers(true).length > _this8.getMaxActiveMicrophonesCount()) {
	          _this8.currentCall.setMuted(true);
	          _this8.callView.setMuted(true);
	          _this8.showAutoMicMuteNotification();
	        }
	        _this8.currentCall.answer({
	          useVideo: !!video,
	          joinAsViewer: joinAsViewer
	        });
	      });
	    }
	  }, {
	    key: "leaveCurrentCall",
	    value: function leaveCurrentCall() {
	      if (this.callView) {
	        this.callView.releaseLocalMedia();
	      }
	      if (this.currentCall) {
	        this.currentCall.hangup();
	      }
	      if (this.callView) {
	        this.callView.close();
	      }
	    }
	  }, {
	    key: "hasActiveCall",
	    value: function hasActiveCall() {
	      return this.currentCall && this.currentCall.isAnyoneParticipating() || this.callView;
	    }
	  }, {
	    key: "hasVisibleCall",
	    value: function hasVisibleCall() {
	      return !!(this.callView && this.callView.visible && this.callView.size == View.Size.Full);
	    }
	  }, {
	    key: "canRecord",
	    value: function canRecord() {
	      return BX.desktop && BX.desktop.getApiVersion() >= 54;
	    }
	  }, {
	    key: "isRecording",
	    value: function isRecording() {
	      return this.canRecord() && this.callRecordState != View.RecordState.Stopped;
	    }
	  }, {
	    key: "useDevicesInCurrentCall",
	    value: function useDevicesInCurrentCall(deviceList) {
	      if (!this.currentCall || !this.currentCall.ready) {
	        return;
	      }
	      for (var i = 0; i < deviceList.length; i++) {
	        var deviceInfo = deviceList[i];
	        switch (deviceInfo.kind) {
	          case "audioinput":
	            this.currentCall.setMicrophoneId(deviceInfo.deviceId);
	            break;
	          case "videoinput":
	            this.currentCall.setCameraId(deviceInfo.deviceId);
	            break;
	          case "audiooutput":
	            if (this.callView) {
	              this.callView.setSpeakerId(deviceInfo.deviceId);
	            }
	            break;
	        }
	      }
	    }
	  }, {
	    key: "removeDevicesFromCurrentCall",
	    value: function removeDevicesFromCurrentCall(deviceList) {
	      if (!this.currentCall || !this.currentCall.ready) {
	        return;
	      }
	      for (var i = 0; i < deviceList.length; i++) {
	        var deviceInfo = deviceList[i];
	        switch (deviceInfo.kind) {
	          case "audioinput":
	            if (this.currentCall.microphoneId == deviceInfo.deviceId) {
	              var microphoneIds = Object.keys(Hardware.microphoneList);
	              this.currentCall.setMicrophoneId(microphoneIds.length > 0 ? microphoneIds[0] : "");
	            }
	            break;
	          case "videoinput":
	            if (this.currentCall.cameraId == deviceInfo.deviceId) {
	              var cameraIds = Object.keys(Hardware.cameraList);
	              this.currentCall.setCameraId(cameraIds.length > 0 ? cameraIds[0] : "");
	            }
	            break;
	          case "audiooutput":
	            if (this.callView && this.callView.speakerId == deviceInfo.deviceId) {
	              var speakerIds = Object.keys(Hardware.audioOutputList);
	              this.callView.setSpeakerId(speakerIds.length > 0 ? speakerIds[0] : "");
	            }
	            break;
	        }
	      }
	    }
	  }, {
	    key: "showChat",
	    value: function showChat() {
	      var _this9 = this;
	      if (BX.desktop && this.floatingWindow) {
	        this.detached = true;
	        this.callView.hide();
	        this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);
	        Util$1.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then(function (result) {
	          _this9.floatingWindow.setAvatars(result);
	          _this9.floatingWindow.show();
	        });
	        this.container.style.width = 0;
	      } else {
	        this.fold(main_core.Text.decode(this.currentCall.associatedEntity.name));
	      }
	    }
	  }, {
	    key: "fold",
	    value: function fold(foldedCallTitle) {
	      if (this.folded || BX.desktop && this.floatingWindow) {
	        return;
	      }
	      if (!foldedCallTitle && this.currentCall) {
	        foldedCallTitle = main_core.Text.decode(this.currentCall.associatedEntity.name);
	      }
	      this.folded = true;
	      this.resizeObserver.unobserve(this.container);
	      this.container.classList.add('bx-messenger-call-overlay-folded');
	      this.callView.setTitle(foldedCallTitle);
	      this.callView.setSize(View.Size.Folded);
	      this.callViewState = ViewState.Folded;
	      if (this.sidebar) {
	        this.sidebar.toggleHidden(true);
	      }
	      this.closePromo();
	      BX.onCustomEvent(this, "CallController::onFold", {});
	    }
	  }, {
	    key: "setCallEditorMaxWidth",
	    value: function setCallEditorMaxWidth(maxWidth) {
	      if (maxWidth != this.maxEditorWidth) {
	        this.maxEditorWidth = maxWidth;
	        this._onResize();
	      }
	    }
	  }, {
	    key: "findCallEditorWidth",
	    value: function findCallEditorWidth() {
	      var containerWidth = this.container.clientWidth;
	      var editorWidth = containerWidth < this.maxEditorWidth + View.MIN_WIDTH ? containerWidth - View.MIN_WIDTH : this.maxEditorWidth;
	      var callWidth = containerWidth - editorWidth;
	      return {
	        callWidth: callWidth,
	        editorWidth: editorWidth
	      };
	    }
	  }, {
	    key: "showDocumentsMenu",
	    value: function showDocumentsMenu() {
	      var _this10 = this;
	      var targetNodeWidth = this.callView.buttons.document.elements.root.offsetWidth;
	      var resumesArticleCode = Util$1.getResumesArticleCode();
	      var documentsArticleCode = Util$1.getDocumentsArticleCode();
	      var menuItems = [{
	        text: BX.message('IM_M_CALL_MENU_CREATE_RESUME'),
	        onclick: function onclick() {
	          _this10.documentsMenu.close();
	          _this10.maybeShowDocumentEditor({
	            type: DocumentType.Resume
	          }, resumesArticleCode);
	        }
	      }, {
	        text: BX.message('IM_M_CALL_MENU_CREATE_FILE'),
	        items: [{
	          text: BX.message('IM_M_CALL_MENU_CREATE_FILE_DOC'),
	          onclick: function onclick() {
	            _this10.documentsMenu.close();
	            _this10.maybeShowDocumentEditor({
	              type: DocumentType.Blank,
	              typeFile: FILE_TYPE_DOCX
	            }, documentsArticleCode);
	          }
	        }, {
	          text: BX.message('IM_M_CALL_MENU_CREATE_FILE_XLS'),
	          onclick: function onclick() {
	            _this10.documentsMenu.close();
	            _this10.maybeShowDocumentEditor({
	              type: DocumentType.Blank,
	              typeFile: FILE_TYPE_XLSX
	            }, documentsArticleCode);
	          }
	        }, {
	          text: BX.message('IM_M_CALL_MENU_CREATE_FILE_PPT'),
	          onclick: function onclick() {
	            _this10.documentsMenu.close();
	            _this10.maybeShowDocumentEditor({
	              type: DocumentType.Blank,
	              typeFile: FILE_TYPE_PPTX
	            }, documentsArticleCode);
	          }
	        }]
	      }];
	      if (!resumesArticleCode) {
	        menuItems.push({
	          text: BX.message('IM_M_CALL_MENU_OPEN_LAST_RESUME'),
	          cacheable: true,
	          items: [{
	            id: "loading",
	            text: BX.message('IM_M_CALL_MENU_LOADING_RESUME_LIST')
	          }],
	          events: {
	            onSubMenuShow: function onSubMenuShow(e) {
	              return _this10.buildPreviousResumesSubmenu(e.target);
	            }
	          }
	        });
	      }
	      this.documentsMenu = new BX.PopupMenuWindow({
	        angle: false,
	        bindElement: this.callView.buttons.document.elements.root,
	        targetContainer: this.container,
	        offsetTop: -15,
	        bindOptions: {
	          position: "top"
	        },
	        cacheable: false,
	        subMenuOptions: {
	          maxWidth: 450
	        },
	        events: {
	          onShow: function onShow(event) {
	            var popup = event.getTarget();
	            popup.getPopupContainer().style.display = 'block'; // bad hack

	            var offsetLeft = targetNodeWidth / 2 - popup.getPopupContainer().offsetWidth / 2;
	            popup.setOffset({
	              offsetLeft: offsetLeft + 40
	            });
	            popup.setAngle({
	              offset: popup.getPopupContainer().offsetWidth / 2 - 17
	            });
	          },
	          onDestroy: function onDestroy() {
	            return _this10.documentsMenu = null;
	          }
	        },
	        items: menuItems
	      });
	      this.documentsMenu.show();
	    }
	  }, {
	    key: "buildPreviousResumesSubmenu",
	    value: function buildPreviousResumesSubmenu(menuItem) {
	      var _this11 = this;
	      BX.ajax.runAction('disk.api.integration.messengerCall.listResumesInChatByCall', {
	        data: {
	          callId: this.currentCall.id
	        }
	      }).then(function (response) {
	        var resumeList = response.data.resumes;
	        if (resumeList.length > 0) {
	          resumeList.forEach(function (resume) {
	            menuItem.getSubMenu().addMenuItem({
	              id: resume.id,
	              text: resume.object.createDate + ': ' + resume.object.name,
	              onclick: function onclick() {
	                _this11.documentsMenu.close();
	                _this11.viewDocumentByLink(resume.links.view);
	              }
	            });
	          });
	        } else {
	          menuItem.getSubMenu().addMenuItem({
	            id: 'nothing',
	            text: BX.message('IM_M_CALL_MENU_NO_RESUME'),
	            disabled: true
	          });
	        }
	        menuItem.getSubMenu().removeMenuItem('loading');
	        menuItem.adjustSubMenu();
	      });
	    }
	  }, {
	    key: "maybeShowDocumentEditor",
	    value: function maybeShowDocumentEditor(options, articleCode) {
	      if (articleCode) {
	        if (articleCode) {
	          BX.UI.InfoHelper.show(articleCode);
	          return;
	        }
	      }
	      this.showDocumentEditor(options);
	    }
	  }, {
	    key: "showDocumentEditor",
	    value: function showDocumentEditor(options) {
	      var _this12 = this;
	      options = options || {};
	      var openAnimation = true;
	      if (this.sidebar) {
	        if (options.force) {
	          this.sidebar.close(false);
	          this.sidebar.destroy();
	          this.sidebar = null;
	          openAnimation = false;
	        } else {
	          return;
	        }
	      }
	      if (this.callView) {
	        this.callView.setButtonActive('document', true);
	      }
	      clearTimeout(this.showPromoPopupTimeout);
	      this._createAndOpenSidebarWithIframe("about:blank", openAnimation);
	      BX.loadExt('disk.onlyoffice-im-integration').then(function () {
	        var docEditor = new BX.Disk.OnlyOfficeImIntegration.CreateDocument({
	          dialog: {
	            id: _this12.currentCall.associatedEntity.id
	          },
	          call: {
	            id: _this12.currentCall.id
	          },
	          delegate: {
	            setMaxWidth: _this12.setCallEditorMaxWidth.bind(_this12),
	            onDocumentCreated: _this12._onDocumentCreated.bind(_this12)
	          }
	        });
	        var promiseGetUrl;
	        if (options.type === DocumentType.Resume) {
	          promiseGetUrl = docEditor.getIframeUrlForTemplates();
	        } else if (options.type === DocumentType.Blank) {
	          promiseGetUrl = docEditor.getIframeUrlForCreate({
	            typeFile: options.typeFile
	          });
	        } else {
	          promiseGetUrl = docEditor.getIframeUrl({
	            viewerItem: options.viewerItem
	          });
	        }
	        promiseGetUrl.then(function (url) {
	          _this12.docEditorIframe.src = url;
	        })["catch"](function (e) {
	          console.error(e);
	          _this12.closeDocumentEditor();
	          alert(BX.message("IM_F_ERROR"));
	        });
	        _this12.docEditor = docEditor;
	      })["catch"](function (error) {
	        console.error(error);
	        _this12.closeDocumentEditor();
	        alert(BX.message("IM_F_ERROR"));
	      });
	      this.resizeObserver.observe(this.container);
	    }
	  }, {
	    key: "closeDocumentEditor",
	    value: function closeDocumentEditor() {
	      var _this13 = this;
	      return new Promise(function (resolve) {
	        if (_this13.docEditor && _this13.docEditorIframe) {
	          _this13.docEditor.onCloseIframe(_this13.docEditorIframe);
	        }
	        if (_this13.container) {
	          _this13.resizeObserver.unobserve(_this13.container);
	        }
	        if (_this13.callView) {
	          _this13.callView.setButtonActive('document', false);
	          _this13.callView.removeMaxWidth();
	        }
	        if (!_this13.sidebar) {
	          return resolve();
	        }
	        var oldSidebar = _this13.sidebar;
	        _this13.sidebar = null;
	        oldSidebar.close().then(function () {
	          _this13.docEditor = null;
	          _this13.docEditorIframe = null;
	          oldSidebar.destroy();
	          _this13.maxEditorWidth = _this13.docCreatedForCurrentCall ? DOC_EDITOR_WIDTH : DOC_TEMPLATE_WIDTH;
	          if (!_this13.callView) {
	            _this13.removeContainer();
	            resolve();
	          }
	        });
	      });
	    }
	  }, {
	    key: "viewDocumentByLink",
	    value: function viewDocumentByLink(url) {
	      if (this.sidebar) {
	        return;
	      }
	      if (this.callView) {
	        this.callView.setButtonActive('document', true);
	      }
	      this.maxEditorWidth = DOC_EDITOR_WIDTH;
	      this._createAndOpenSidebarWithIframe(url);
	    }
	  }, {
	    key: "_createAndOpenSidebarWithIframe",
	    value: function _createAndOpenSidebarWithIframe(url, animation) {
	      var _this14 = this;
	      animation = animation === true;
	      var result = this.findCallEditorWidth();
	      var callWidth = result.callWidth;
	      var editorWidth = result.editorWidth;
	      this.callView.setMaxWidth(callWidth);
	      this.sidebar = new Sidebar({
	        container: this.container,
	        width: editorWidth,
	        events: {
	          onCloseClicked: this.onSideBarCloseClicked.bind(this)
	        }
	      });
	      this.sidebar.open(animation);
	      var loader$$1 = new BX.Loader({
	        target: this.sidebar.elements.contentContainer
	      });
	      loader$$1.show();
	      var docEditorIframe = BX.create("iframe", {
	        attrs: {
	          src: url,
	          frameborder: "0"
	        },
	        style: {
	          display: "none",
	          border: "0",
	          margin: "0",
	          width: "100%",
	          height: "100%"
	        }
	      });
	      docEditorIframe.addEventListener('load', function () {
	        loader$$1.destroy();
	        docEditorIframe.style.display = 'block';
	      }, {
	        once: true
	      });
	      docEditorIframe.addEventListener('error', function (error) {
	        console.error(error);
	        _this14.closeDocumentEditor();
	        alert(BX.message("IM_F_ERROR"));
	      });
	      this.sidebar.elements.contentContainer.appendChild(docEditorIframe);
	      this.docEditorIframe = docEditorIframe;
	    }
	  }, {
	    key: "_onDocumentCreated",
	    value: function _onDocumentCreated() {
	      this.docCreatedForCurrentCall = true;
	      if (this.currentCall) {
	        this.currentCall.sendCustomMessage(DOC_CREATED_EVENT, true);
	      }
	    }
	  }, {
	    key: "onSideBarCloseClicked",
	    value: function onSideBarCloseClicked() {
	      this.closeDocumentEditor();
	    }
	  }, {
	    key: "_ensureDocumentEditorClosed",
	    value: function _ensureDocumentEditorClosed() {
	      var _this15 = this;
	      return new Promise(function (resolve, reject) {
	        if (!_this15.sidebar) {
	          return resolve();
	        }
	        var messageBox = new ui_dialogs_messagebox.MessageBox({
	          message: BX.message('IM_CALL_CLOSE_DOCUMENT_EDITOR_TO_ANSWER'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          okCaption: BX.message('IM_CALL_CLOSE_DOCUMENT_EDITOR_YES'),
	          cancelCaption: BX.message('IM_CALL_CLOSE_DOCUMENT_EDITOR_NO'),
	          onOk: function onOk() {
	            _this15.closeDocumentEditor().then(function () {
	              return resolve();
	            });
	            return true;
	          },
	          onCancel: function onCancel() {
	            reject();
	            return true;
	          }
	        });
	        messageBox.show();
	      });
	    }
	  }, {
	    key: "onDocumentPromoActionClicked",
	    value: function onDocumentPromoActionClicked() {
	      this.closePromo();
	      var articleCode = Util$1.getResumesArticleCode();
	      if (articleCode) {
	        BX.UI.InfoHelper.show(articleCode); //@see \Bitrix\Disk\Integration\MessengerCall::getInfoHelperCodeForDocuments()
	        return;
	      }
	      this.showDocumentEditor({
	        type: DocumentType.Resume
	      });
	    }
	  }, {
	    key: "onDocumentPromoClosed",
	    value: function onDocumentPromoClosed(e) {
	      var data = e.getData();
	      if (data.dontShowAgain) {
	        this.emit(Events$6.onPromoViewed, {
	          code: DOCUMENT_PROMO_CODE
	        });
	      }
	      this.documentPromoPopup = null;
	    }
	  }, {
	    key: "unfold",
	    value: function unfold() {
	      if (this.detached) {
	        this.container.style.removeProperty('width');
	        this.callView.show();
	        this.detached = false;
	        if (this.floatingWindow) {
	          this.floatingWindow.hide();
	        }
	      }
	      if (this.folded) {
	        this.folded = false;
	        this.container.classList.remove('bx-messenger-call-overlay-folded');
	        this.callView.setSize(View.Size.Full);
	        this.callViewState = ViewState.Opened;
	        if (this.sidebar) {
	          this.sidebar.toggleHidden(false);
	          this.resizeObserver.observe(this.container);
	        }
	      }
	      BX.onCustomEvent(this, "CallController::onUnfold", {});
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
	      if (this.messengerFacade.isSliderFocused()) {
	        BX.SidePanel.Instance.enterFullScreen();
	      } else {
	        if (main_core.Browser.isChrome() || main_core.Browser.isSafari()) {
	          document.body.webkitRequestFullScreen();
	        } else if (main_core.Browser.isFirefox()) {
	          document.body.requestFullscreen();
	        }
	      }
	    }
	  }, {
	    key: "exitFullScreen",
	    value: function exitFullScreen() {
	      if (this.messengerFacade.isSliderFocused()) {
	        BX.SidePanel.Instance.exitFullScreen();
	      } else {
	        if (document.cancelFullScreen) {
	          document.cancelFullScreen();
	        } else if (document.mozCancelFullScreen) {
	          document.mozCancelFullScreen();
	        } else if (document.webkitCancelFullScreen) {
	          document.webkitCancelFullScreen();
	        } else if (document.exitFullscreen) {
	          document.exitFullscreen();
	        }
	      }
	    }
	  }, {
	    key: "showDocumentPromo",
	    value: function showDocumentPromo() {
	      var _this16 = this;
	      if (!this.callView || !this.currentCall || !Util$1.shouldShowDocumentButton()) {
	        return false;
	      }
	      if (!this.messengerFacade.isPromoRequired(DOCUMENT_PROMO_CODE)) {
	        return false;
	      }
	      var documentButton = this.callView.buttons.document.elements.root;
	      var bindElement = documentButton.querySelector('.bx-messenger-videocall-panel-icon');
	      if (!bindElement) {
	        return false;
	      }
	      this.documentPromoPopup = new PromoPopup({
	        bindElement: bindElement,
	        promoCode: DOCUMENT_PROMO_CODE,
	        events: {
	          onActionClick: this.onDocumentPromoActionClicked.bind(this),
	          onClose: this.onDocumentPromoClosed.bind(this)
	        }
	      });
	      this.showPromoPopupTimeout = setTimeout(function () {
	        if (_this16.folded) {
	          return false;
	        }
	        _this16.documentPromoPopup.show();
	      }, DOCUMENT_PROMO_DELAY);
	    }
	  }, {
	    key: "showMaskPromo",
	    value: function showMaskPromo() {
	      var _this17 = this;
	      if (!this.callView || !this.currentCall || !BackgroundDialog.isMaskAvailable()) {
	        return false;
	      }
	      if (!this.messengerFacade.isPromoRequired(MASK_PROMO_CODE)) {
	        return false;
	      }
	      this.maskPromoPopup = new PromoPopup3D({
	        callView: this.callView,
	        events: {
	          onClose: function onClose(e) {
	            _this17.emit(Events$6.onPromoViewed, {
	              code: MASK_PROMO_CODE
	            });
	            _this17.maskPromoPopup = null;
	          }
	        }
	      });
	      this.showPromoPopup3dTimeout = setTimeout(function () {
	        if (!_this17.folded) {
	          _this17.maskPromoPopup.show();
	        }
	      }, MASK_PROMO_DELAY);
	    }
	  }, {
	    key: "closePromo",
	    value: function closePromo() {
	      if (this.documentPromoPopup) {
	        this.documentPromoPopup.close();
	      }
	      if (this.maskPromoPopup) {
	        this.maskPromoPopup.close();
	      }
	      clearTimeout(this.showPromoPopupTimeout);
	      clearTimeout(this.showPromoPopup3dTimeout);
	    }
	  }, {
	    key: "_startRecordCall",
	    value: function _startRecordCall(type) {
	      this.callView.setButtonActive('record', true);
	      this.callRecordType = type;
	      this.currentCall.sendRecordState({
	        action: View.RecordState.Started,
	        date: new Date()
	      });
	      this.callRecordState = View.RecordState.Started;
	    } // event handlers
	  }, {
	    key: "_onCallNotificationClose",
	    value: function _onCallNotificationClose() {
	      clearTimeout(this.hideIncomingCallTimeout);
	      this.messengerFacade.stopRepeatSound('ringtone');
	      if (this.callNotification) {
	        this.callNotification.destroy();
	      }
	    }
	  }, {
	    key: "_onCallNotificationDestroy",
	    value: function _onCallNotificationDestroy() {
	      this.callNotification = null;
	    }
	  }, {
	    key: "_onCallNotificationButtonClick",
	    value: function _onCallNotificationButtonClick(e) {
	      var data = e.data;
	      clearTimeout(this.hideIncomingCallTimeout);
	      this.callNotification.close();
	      switch (data.button) {
	        case "answer":
	          this._onAnswerButtonClick(data.video);
	          break;
	        case "decline":
	          if (this.currentCall) {
	            this.removeVideoStrategy();
	            this.removeCallEvents();
	            this.currentCall.decline();
	            this.currentCall = null;
	          }
	          break;
	      }
	    }
	  }, {
	    key: "_onAnswerButtonClick",
	    value: function _onAnswerButtonClick(withVideo) {
	      var _this18 = this;
	      if (BX.desktop) {
	        BX.desktop.windowCommand("show");
	      }
	      if (!this.isUserAgentSupported()) {
	        this.log("Error: unsupported user agent");
	        this.removeVideoStrategy();
	        this.removeCallEvents();
	        this.currentCall.decline();
	        this.currentCall = null;
	        this.showUnsupportedNotification();
	        return;
	      }
	      if (this.callView) {
	        this.callView.destroy();
	      }
	      var dialogId = this.currentCall.associatedEntity && this.currentCall.associatedEntity.id ? this.currentCall.associatedEntity.id : false;
	      var isGroupCall = dialogId.toString().startsWith("chat");
	      this._ensureDocumentEditorClosed().then(function () {
	        return _this18.messengerFacade.openMessenger(dialogId);
	      }).then(function () {
	        return Hardware.init();
	      }).then(function () {
	        _this18.createContainer();
	        var hiddenButtons = [];
	        if (_this18.currentCall instanceof PlainCall) {
	          hiddenButtons.push('floorRequest');
	        }
	        if (!Util$1.shouldShowDocumentButton()) {
	          hiddenButtons.push('document');
	        }
	        _this18.callView = new View({
	          container: _this18.container,
	          baseZIndex: _this18.messengerFacade.getDefaultZIndex(),
	          users: _this18.currentCall.users,
	          userStates: _this18.currentCall.getUsers(),
	          showChatButtons: true,
	          showRecordButton: _this18.featureRecord !== FeatureState.Disabled,
	          userLimit: Util$1.getUserLimit(),
	          layout: isGroupCall ? View.Layout.Grid : View.Layout.Centered,
	          microphoneId: Hardware.defaultMicrophone,
	          blockedButtons: _this18.getBlockedButtons(),
	          hiddenButtons: hiddenButtons
	        });
	        _this18.autoCloseCallView = true;
	        if (_this18.callWithLegacyMobile) {
	          _this18.callView.blockAddUser();
	        }
	        _this18.bindCallViewEvents();
	        _this18.updateCallViewUsers(_this18.currentCall.id, _this18.getCallUsers(true));
	        _this18.callView.show();
	        _this18.showDocumentPromo();
	        _this18.showMaskPromo();
	        _this18.currentCall.useHdVideo(Hardware.preferHdQuality);
	        if (Hardware.defaultMicrophone) {
	          _this18.currentCall.setMicrophoneId(Hardware.defaultMicrophone);
	        }
	        if (Hardware.defaultCamera) {
	          _this18.currentCall.setCameraId(Hardware.defaultCamera);
	        }
	        if (_this18.getCallUsers(true).length > _this18.getMaxActiveMicrophonesCount()) {
	          _this18.currentCall.setMuted(true);
	          _this18.callView.setMuted(true);
	          _this18.showAutoMicMuteNotification();
	        }
	        _this18.currentCall.answer({
	          useVideo: withVideo && Hardware.hasCamera(),
	          enableMicAutoParameters: Hardware.enableMicAutoParameters
	        });
	        _this18.createVideoStrategy();
	      });
	    }
	  }, {
	    key: "_onCallConferenceNotificationButtonClick",
	    value: function _onCallConferenceNotificationButtonClick(e) {
	      clearTimeout(this.hideIncomingCallTimeout);
	      this.callNotification.close();
	      switch (e.button) {
	        case "answerConference":
	          if (this.currentCall && 'id' in this.currentCall.associatedEntity) {
	            var _dialogId = this.currentCall.associatedEntity.id.toString();
	            if (_dialogId.startsWith('chat')) {
	              _dialogId = _dialogId.substring(4);
	            }
	            this.emit(Events$6.onOpenVideoConference, {
	              dialogId: _dialogId
	            });
	          }
	          break;
	        case "skipConference":
	          if (this.currentCall) {
	            this.removeVideoStrategy();
	            this.removeCallEvents();
	            this.currentCall.decline();
	            this.currentCall = null;
	          }
	          break;
	      }
	    }
	  }, {
	    key: "_onCallViewShow",
	    value: function _onCallViewShow() {
	      this.callView.setButtonCounter("chat", this.messengerFacade.getMessageCount());
	      this.callViewState = ViewState.Opened;
	    }
	  }, {
	    key: "_onCallViewClose",
	    value: function _onCallViewClose() {
	      this.callView.destroy();
	      this.callViewState = ViewState.Closed;
	      if (this.floatingWindow) {
	        this.floatingWindow.close();
	      }
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.close();
	      }
	      if (this.documentsMenu) {
	        this.documentsMenu.close();
	      }
	      if (BX.desktop) {
	        BX.desktop.closeWindow('callBackground');
	      }
	      this.closePromo();
	      this._closeReconnectionBaloon();
	    }
	  }, {
	    key: "_onCallViewDestroy",
	    value: function _onCallViewDestroy() {
	      this.callView = null;
	      this.folded = false;
	      this.autoCloseCallView = true;
	      if (this.sidebar) {
	        BX.adjust(this.container, {
	          style: {
	            backgroundColor: "rgba(0, 0, 0, 0.5)",
	            backdropFilter: "blur(5px)"
	          }
	        });
	      } else {
	        this.removeContainer();
	        this.maxEditorWidth = DOC_TEMPLATE_WIDTH;
	      }
	    }
	  }, {
	    key: "_onCallViewBodyClick",
	    value: function _onCallViewBodyClick() {
	      if (this.folded) {
	        this.unfold();
	      }
	    }
	  }, {
	    key: "_onCallViewButtonClick",
	    value: function _onCallViewButtonClick(e) {
	      var buttonName = e.buttonName;
	      var handlers = {
	        hangup: this._onCallViewHangupButtonClick.bind(this),
	        close: this._onCallViewCloseButtonClick.bind(this),
	        inviteUser: this._onCallViewInviteUserButtonClick.bind(this),
	        toggleMute: this._onCallViewToggleMuteButtonClick.bind(this),
	        toggleScreenSharing: this._onCallViewToggleScreenSharingButtonClick.bind(this),
	        record: this._onCallViewRecordButtonClick.bind(this),
	        toggleVideo: this._onCallViewToggleVideoButtonClick.bind(this),
	        toggleSpeaker: this._onCallViewToggleSpeakerButtonClick.bind(this),
	        showChat: this._onCallViewShowChatButtonClick.bind(this),
	        floorRequest: this._onCallViewFloorRequestButtonClick.bind(this),
	        showHistory: this._onCallViewShowHistoryButtonClick.bind(this),
	        fullscreen: this._onCallViewFullScreenButtonClick.bind(this),
	        document: this._onCallViewDocumentButtonClick.bind(this),
	        microphoneSideIcon: this._onCallViewMicrophoneSideIconClick.bind(this)
	      };
	      if (main_core.Type.isFunction(handlers[buttonName])) {
	        handlers[buttonName].call(this, e);
	      }
	    }
	  }, {
	    key: "_onCallViewHangupButtonClick",
	    value: function _onCallViewHangupButtonClick() {
	      this.leaveCurrentCall();
	    }
	  }, {
	    key: "_onCallViewCloseButtonClick",
	    value: function _onCallViewCloseButtonClick() {
	      if (this.callView) {
	        this.callView.close();
	      }
	    }
	  }, {
	    key: "_onCallViewShowChatButtonClick",
	    value: function _onCallViewShowChatButtonClick() {
	      this.messengerFacade.openMessenger(this.currentCall.associatedEntity.id);
	      this.showChat();
	    }
	  }, {
	    key: "_onCallViewFloorRequestButtonClick",
	    value: function _onCallViewFloorRequestButtonClick() {
	      var _this19 = this;
	      var floorState = this.callView.getUserFloorRequestState(CallEngine.getCurrentUserId());
	      var talkingState = this.callView.getUserTalking(CallEngine.getCurrentUserId());
	      this.callView.setUserFloorRequestState(CallEngine.getCurrentUserId(), !floorState);
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
	    /**
	     * Returns list of users, that are not currently connected
	     * @return {Array}
	     * @private
	     */
	  }, {
	    key: "_getDisconnectedUsers",
	    value: function _getDisconnectedUsers() {
	      var result = [];
	      var userStates = this.currentCall.getUsers();
	      for (var userId in userStates) {
	        if (userStates[userId] !== UserState.Connected) {
	          var userData = Util$1.getUserCached(userId);
	          if (userData) {
	            result.push(userData);
	          }
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "_closeReconnectionBaloon",
	    value: function _closeReconnectionBaloon() {
	      if (this.reconnectionBaloon) {
	        this.reconnectionBaloon.close();
	        this.reconnectionBaloon = null;
	      }
	    }
	  }, {
	    key: "_onCallViewInviteUserButtonClick",
	    value: function _onCallViewInviteUserButtonClick(e) {
	      var _this20 = this;
	      if (!this.messengerFacade.showUserSelector) {
	        return;
	      }
	      var userStates = this.currentCall ? this.currentCall.getUsers() : {};
	      var idleUsers = this.currentCall ? this._getDisconnectedUsers() : [];
	      this.messengerFacade.showUserSelector({
	        viewElement: this.callView.container,
	        bindElement: e.node,
	        zIndex: this.messengerFacade.getDefaultZIndex() + 200,
	        darkMode: this.messengerFacade.isThemeDark(),
	        idleUsers: idleUsers,
	        allowNewUsers: Object.keys(userStates).length < Util$1.getUserLimit() - 1,
	        onDestroy: this._onInvitePopupDestroy.bind(this),
	        onSelect: this._onInvitePopupSelect.bind(this)
	      }).then(function (inviteCloser) {
	        _this20.invitePopup = inviteCloser;
	        _this20.callView.setHotKeyTemporaryBlock(true);
	      });
	    }
	  }, {
	    key: "_onCallViewToggleMuteButtonClick",
	    value: function _onCallViewToggleMuteButtonClick(e) {
	      var currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
	      if (currentRoom && currentRoom.speaker != this.userId && !e.muted) {
	        this.currentCall.requestRoomSpeaker();
	        return;
	      }
	      this.currentCall.setMuted(e.muted);
	      this.callView.setMuted(e.muted);
	      if (this.floatingWindow) {
	        this.floatingWindow.setAudioMuted(e.muted);
	      }
	      if (this.mutePopup) {
	        this.mutePopup.close();
	      }
	      if (!e.muted) {
	        this.allowMutePopup = true;
	      }
	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordMute(e.muted);
	      }
	    }
	  }, {
	    key: "_onCallViewRecordButtonClick",
	    value: function _onCallViewRecordButtonClick(event) {
	      var _this21 = this;
	      if (event.recordState === View.RecordState.Started) {
	        if (this.featureRecord === FeatureState.Limited) {
	          this.messengerFacade.openHelpArticle('call_record');
	          return;
	        }
	        if (this.featureRecord === FeatureState.Disabled) {
	          return;
	        }
	        if (this.canRecord()) {
	          var forceRecord = BX.prop.getBoolean(event, "forceRecord", View.RecordType.None);
	          if (forceRecord !== View.RecordType.None) {
	            this._startRecordCall(forceRecord);
	          } else if (BX.desktop && BX.desktop.enableInVersion(55)) {
	            if (!this.callRecordMenu) {
	              this.callRecordMenu = new BX.PopupMenuWindow({
	                bindElement: event.node,
	                targetContainer: this.callView.container,
	                items: [{
	                  text: BX.message('IM_M_CALL_MENU_RECORD_VIDEO'),
	                  onclick: function onclick(event, item) {
	                    _this21._startRecordCall(View.RecordType.Video);
	                    item.getMenuWindow().close();
	                  }
	                }, {
	                  text: BX.message('IM_M_CALL_MENU_RECORD_AUDIO'),
	                  onclick: function onclick(event, item) {
	                    _this21._startRecordCall(View.RecordType.Audio);
	                    item.getMenuWindow().close();
	                  }
	                }],
	                autoHide: true,
	                angle: {
	                  position: "top",
	                  offset: 80
	                },
	                offsetTop: 0,
	                offsetLeft: -25,
	                events: {
	                  onPopupClose: function onPopupClose() {
	                    return _this21.callRecordMenu.destroy();
	                  },
	                  onPopupDestroy: function onPopupDestroy() {
	                    return _this21.callRecordMenu = null;
	                  }
	                }
	              });
	            }
	            this.callRecordMenu.toggle();
	            return;
	          }
	          this.callView.setButtonActive('record', true);
	        } else {
	          if (window.BX.Helper) {
	            window.BX.Helper.show("redirect=detail&code=12398134");
	          }
	          return;
	        }
	      } else if (event.recordState === View.RecordState.Paused) {
	        if (this.canRecord()) {
	          BXDesktopSystem.CallRecordPause(true);
	        }
	      } else if (event.recordState === View.RecordState.Resumed) {
	        if (this.canRecord()) {
	          BXDesktopSystem.CallRecordPause(false);
	        }
	      } else if (event.recordState === View.RecordState.Stopped) {
	        this.callView.setButtonActive('record', false);
	      }
	      this.currentCall.sendRecordState({
	        action: event.recordState,
	        date: new Date()
	      });
	      this.callRecordState = event.recordState;
	    }
	  }, {
	    key: "_onCallViewToggleScreenSharingButtonClick",
	    value: function _onCallViewToggleScreenSharingButtonClick() {
	      if (this.featureScreenSharing === FeatureState.Limited) {
	        this.messengerFacade.openHelpArticle('call_screen_sharing');
	        return;
	      }
	      if (this.featureScreenSharing === FeatureState.Disabled) {
	        return;
	      }
	      if (this.currentCall.isScreenSharingStarted()) {
	        if (this.floatingScreenShareWindow) {
	          this.floatingScreenShareWindow.close();
	        }
	        if (this.webScreenSharePopup) {
	          this.webScreenSharePopup.close();
	        }
	        if (this.documentPromoPopup) {
	          this.documentPromoPopup.close();
	        }
	        this.currentCall.stopScreenSharing();
	        if (this.isRecording()) {
	          BXDesktopSystem.CallRecordStopSharing();
	        }
	      } else {
	        this.currentCall.startScreenSharing();
	        CallEngine.getRestClient().callMethod("im.call.onShareScreen", {
	          callId: this.currentCall.id
	        });
	      }
	    }
	  }, {
	    key: "_onCallViewToggleVideoButtonClick",
	    value: function _onCallViewToggleVideoButtonClick(e) {
	      if (!Hardware.initialized) {
	        return;
	      }
	      if (e.video && Object.values(Hardware.cameraList).length === 0) {
	        return;
	      }
	      if (!e.video) {
	        this.callView.releaseLocalMedia();
	      }
	      this.currentCall.setVideoEnabled(e.video);
	    }
	  }, {
	    key: "_onCallViewToggleSpeakerButtonClick",
	    value: function _onCallViewToggleSpeakerButtonClick(e) {
	      var currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
	      if (currentRoom && currentRoom.speaker != this.userId) {
	        alert("only room speaker can turn on sound");
	        return;
	      }
	      this.callView.muteSpeaker(!e.speakerMuted);
	      if (e.fromHotKey) {
	        BX.UI.Notification.Center.notify({
	          content: BX.message(this.callView.speakerMuted ? 'IM_M_CALL_MUTE_SPEAKERS_OFF' : 'IM_M_CALL_MUTE_SPEAKERS_ON'),
	          position: "top-right",
	          autoHideDelay: 3000,
	          closeButton: true
	        });
	      }
	    }
	  }, {
	    key: "_onCallViewMicrophoneSideIconClick",
	    value: function _onCallViewMicrophoneSideIconClick() {
	      var currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
	      if (currentRoom) {
	        this.toggleRoomMenu(this.callView.buttons.microphone.elements.icon);
	      } else {
	        this.toggleRoomListMenu(this.callView.buttons.microphone.elements.icon);
	      }
	    }
	  }, {
	    key: "_onCallViewShowHistoryButtonClick",
	    value: function _onCallViewShowHistoryButtonClick() {
	      this.messengerFacade.openHistory(this.currentCall.associatedEntity.id);
	    }
	  }, {
	    key: "_onCallViewFullScreenButtonClick",
	    value: function _onCallViewFullScreenButtonClick() {
	      if (this.folded) {
	        this.unfold();
	      }
	      this.toggleFullScreen();
	    }
	  }, {
	    key: "_onCallViewDocumentButtonClick",
	    value: function _onCallViewDocumentButtonClick() {
	      this.sidebar ? this.closeDocumentEditor() : this.showDocumentsMenu();
	    }
	  }, {
	    key: "_onCallViewReplaceCamera",
	    value: function _onCallViewReplaceCamera(e) {
	      if (this.currentCall) {
	        this.currentCall.setCameraId(e.deviceId);
	      }

	      // update default camera
	      Hardware.defaultCamera = e.deviceId;
	    }
	  }, {
	    key: "_onCallViewReplaceMicrophone",
	    value: function _onCallViewReplaceMicrophone(e) {
	      if (this.currentCall) {
	        this.currentCall.setMicrophoneId(e.deviceId);
	      }
	      if (this.currentCall instanceof VoximplantCall) {
	        this.callView.setMicrophoneId(e.deviceId);
	      }

	      // update default microphone
	      Hardware.defaultMicrophone = e.deviceId;
	    }
	  }, {
	    key: "_onCallViewReplaceSpeaker",
	    value: function _onCallViewReplaceSpeaker(e) {
	      Hardware.defaultSpeaker = e.deviceId;
	    }
	  }, {
	    key: "_onCallViewChangeHdVideo",
	    value: function _onCallViewChangeHdVideo(e) {
	      Hardware.preferHdQuality = e.allowHdVideo;
	    }
	  }, {
	    key: "_onCallViewChangeMicAutoParams",
	    value: function _onCallViewChangeMicAutoParams(e) {
	      Hardware.enableMicAutoParameters = e.allowMicAutoParams;
	    }
	  }, {
	    key: "_onCallViewChangeFaceImprove",
	    value: function _onCallViewChangeFaceImprove(e) {
	      if (typeof BX.desktop === 'undefined') {
	        return;
	      }
	      BX.desktop.cameraSmoothingStatus(e.faceImproveEnabled);
	    }
	  }, {
	    key: "_onCallViewOpenAdvancedSettings",
	    value: function _onCallViewOpenAdvancedSettings() {
	      this.messengerFacade.openSettings({
	        onlyPanel: 'hardware'
	      });
	    }
	  }, {
	    key: "_onCallViewSetCentralUser",
	    value: function _onCallViewSetCentralUser(e) {
	      if (e.stream && this.floatingWindow) {
	        this.floatingWindowUser = e.userId;
	        //this.floatingWindow.setStream(e.stream);
	      }
	    }
	  }, {
	    key: "_onCallUserInvited",
	    value: function _onCallUserInvited(e) {
	      if (this.callView) {
	        this.updateCallViewUsers(this.currentCall.id, [e.userId]);
	        this.callView.addUser(e.userId);
	      }
	    }
	  }, {
	    key: "_onCallDestroy",
	    value: function _onCallDestroy() {
	      var callDetails;
	      if (this.currentCall) {
	        this.removeVideoStrategy();
	        this.removeCallEvents();
	        callDetails = _classPrivateMethodGet$4(this, _getCallDetail, _getCallDetail2).call(this, this.currentCall);
	        this.currentCall = null;
	      }
	      this.callWithLegacyMobile = false;
	      if (this.callNotification) {
	        this.callNotification.close();
	      }
	      if (this.invitePopup) {
	        this.invitePopup.close();
	      }
	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordStop();
	      }
	      this.callRecordState = View.RecordState.Stopped;
	      this.callRecordType = View.RecordType.None;
	      if (this.callRecordMenu) {
	        this.callRecordMenu.close();
	      }
	      if (this.callView && this.autoCloseCallView) {
	        this.callView.close();
	      }
	      if (this.floatingWindow) {
	        this.floatingWindow.close();
	      }
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.close();
	      }
	      if (this.webScreenSharePopup) {
	        this.webScreenSharePopup.close();
	      }
	      if (this.mutePopup) {
	        this.mutePopup.close();
	      }
	      if (BX.desktop) {
	        BX.desktop.closeWindow('callBackground');
	      }
	      this.closePromo();
	      this.allowMutePopup = true;
	      this.docCreatedForCurrentCall = false;
	      this._closeReconnectionBaloon();
	      this.messengerFacade.stopRepeatSound('dialtone');
	      this.messengerFacade.stopRepeatSound('ringtone');
	      this.emit(Events$6.onCallDestroyed, {
	        callDetails: callDetails
	      });
	    }
	  }, {
	    key: "_onCallUserStateChanged",
	    value: function _onCallUserStateChanged(e) {
	      var _this22 = this;
	      setTimeout(this.updateFloatingWindowContent.bind(this), 100);
	      if (this.callView) {
	        this.callView.setUserState(e.userId, e.state);
	        if (e.isLegacyMobile) {
	          this.callView.blockAddUser();
	          this.callView.blockSwitchCamera();
	          this.callView.blockScreenSharing();
	          this.callView.disableMediaSelection();
	          this.callView.updateButtons();
	        }
	      }
	      if (e.state == UserState.Connecting || e.state == UserState.Connected) {
	        this.messengerFacade.stopRepeatSound('dialtone');
	      }
	      if (e.state == UserState.Connected) {
	        if (!e.isLegacyMobile) {
	          this.callView.unblockButtons(['camera', 'floorRequest', 'screen']);
	        }
	        if (this.callRecordState === View.RecordState.Stopped) {
	          this.callView.unblockButtons(['record']);
	        }

	        /*Util.getUser(e.userId).then(function(userData)
	        	{
	        		this.showNotification(Util.getCustomMessage("IM_M_CALL_USER_CONNECTED", {
	        			gender: userData.gender,
	        			name: userData.name
	        		}));
	        	}.bind(this));*/
	      } else if (e.state == UserState.Idle && e.previousState == UserState.Connected) ; else if (e.state == UserState.Failed) {
	        if (e.networkProblem) {
	          this.showNetworkProblemNotification(BX.message("IM_M_CALL_TURN_UNAVAILABLE"));
	        } else {
	          Util$1.getUser(this.currentCall.id, e.userId).then(function (userData) {
	            _this22.showNotification(Util$1.getCustomMessage("IM_M_CALL_USER_FAILED", {
	              gender: userData.gender,
	              name: userData.name
	            }));
	          });
	        }
	      } else if (e.state == UserState.Declined) {
	        Util$1.getUser(this.currentCall.id, e.userId).then(function (userData) {
	          _this22.showNotification(Util$1.getCustomMessage("IM_M_CALL_USER_DECLINED", {
	            gender: userData.gender,
	            name: userData.name
	          }));
	        });
	      } else if (e.state == UserState.Busy) {
	        Util$1.getUser(this.currentCall.id, e.userId).then(function (userData) {
	          _this22.showNotification(Util$1.getCustomMessage("IM_M_CALL_USER_BUSY", {
	            gender: userData.gender,
	            name: userData.name
	          }));
	        });
	      }
	    }
	  }, {
	    key: "_onCallUserMicrophoneState",
	    value: function _onCallUserMicrophoneState(e) {
	      if (!this.callView) {
	        return;
	      }
	      this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
	    }
	  }, {
	    key: "_onCallUserCameraState",
	    value: function _onCallUserCameraState(e) {
	      if (!this.callView) {
	        return;
	      }
	      this.callView.setUserCameraState(e.userId, e.cameraState);
	    }
	  }, {
	    key: "_onCallUserVideoPaused",
	    value: function _onCallUserVideoPaused(e) {
	      if (!this.callView) {
	        return;
	      }
	      this.callView.setUserVideoPaused(e.userId, e.videoPaused);
	    }
	  }, {
	    key: "_onCallLocalMediaReceived",
	    value: function _onCallLocalMediaReceived(e) {
	      this.log("Received local media stream " + e.tag);
	      if (this.callView) {
	        var flipVideo = e.tag == "main" ? Hardware.enableMirroring : false;
	        this.callView.setLocalStream(e.stream);
	        this.callView.flipLocalVideo(flipVideo);
	        this.callView.setButtonActive("screen", e.tag == "screen");
	        if (e.tag == "screen") {
	          if (!BX.desktop) {
	            this.showWebScreenSharePopup();
	          }
	          this.callView.blockSwitchCamera();
	          this.callView.updateButtons();
	        } else {
	          if (this.floatingScreenShareWindow) {
	            this.floatingScreenShareWindow.close();
	          }
	          if (this.webScreenSharePopup) {
	            this.webScreenSharePopup.close();
	          }
	          if (this.isRecording()) {
	            BXDesktopSystem.CallRecordStopSharing();
	          }
	          if (!this.currentCall.callFromMobile) {
	            this.callView.unblockSwitchCamera();
	            this.callView.updateButtons();
	          }
	        }
	      }
	      if (this.currentCall && this.currentCall.videoEnabled && e.stream.getVideoTracks().length === 0) {
	        this.showNotification(BX.message("IM_CALL_CAMERA_ERROR_FALLBACK_TO_MIC"));
	        this.currentCall.setVideoEnabled(false);
	      }
	    }
	  }, {
	    key: "_onCallLocalCameraFlip",
	    value: function _onCallLocalCameraFlip(e) {
	      this._onCallLocalCameraFlipInDesktop(e.data.enableMirroring);
	    }
	  }, {
	    key: "_onCallLocalCameraFlipInDesktop",
	    value: function _onCallLocalCameraFlipInDesktop(e) {
	      if (this.callView) {
	        this.callView.flipLocalVideo(e);
	      }
	    }
	  }, {
	    key: "_onCallLocalMediaStopped",
	    value: function _onCallLocalMediaStopped(e) {
	      // do nothing
	    }
	  }, {
	    key: "_onCallRemoteMediaReceived",
	    value: function _onCallRemoteMediaReceived(e) {
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
	    key: "_onCallRemoteMediaStopped",
	    value: function _onCallRemoteMediaStopped(e) {
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
	    key: "_onCallUserVoiceStarted",
	    value: function _onCallUserVoiceStarted(e) {
	      if (e.local) {
	        if (this.currentCall.muted && this.isMutedPopupAllowed()) {
	          this.showMicMutedNotification();
	        }
	        return;
	      }
	      this.talkingUsers[e.userId] = true;
	      if (this.callView) {
	        this.callView.setUserTalking(e.userId, true);
	        this.callView.setUserFloorRequestState(e.userId, false);
	      }
	      if (this.floatingWindow) {
	        this.floatingWindow.setTalking(Object.keys(this.talkingUsers).map(function (id) {
	          return Number(id);
	        }));
	      }
	    }
	  }, {
	    key: "_onCallUserVoiceStopped",
	    value: function _onCallUserVoiceStopped(e) {
	      if (e.local) {
	        return;
	      }
	      if (this.talkingUsers[e.userId]) {
	        delete this.talkingUsers[e.userId];
	      }
	      if (this.callView) {
	        this.callView.setUserTalking(e.userId, false);
	      }
	      if (this.floatingWindow) {
	        this.floatingWindow.setTalking(Object.keys(this.talkingUsers).map(function (id) {
	          return Number(id);
	        }));
	      }
	    }
	  }, {
	    key: "_onCallUserScreenState",
	    value: function _onCallUserScreenState(e) {
	      if (this.callView) {
	        this.callView.setUserScreenState(e.userId, e.screenState);
	      }
	      if (e.userId == CallEngine.getCurrentUserId()) {
	        this.callView.setButtonActive("screen", e.screenState);
	        if (e.screenState) {
	          if (!BX.desktop) {
	            this.showWebScreenSharePopup();
	          }
	          this.callView.blockSwitchCamera();
	        } else {
	          if (this.floatingScreenShareWindow) {
	            this.floatingScreenShareWindow.close();
	          }
	          if (this.webScreenSharePopup) {
	            this.webScreenSharePopup.close();
	          }
	          if (this.isRecording()) {
	            BXDesktopSystem.CallRecordStopSharing();
	          }
	          if (!this.currentCall.callFromMobile) {
	            this.callView.unblockSwitchCamera();
	            this.callView.updateButtons();
	          }
	        }
	        this.callView.updateButtons();
	      }
	    }
	  }, {
	    key: "_onCallUserRecordState",
	    value: function _onCallUserRecordState(event) {
	      this.callRecordState = event.recordState.state;
	      this.callView.setRecordState(event.recordState);
	      if (!this.canRecord() || event.userId != BX.message['USER_ID']) {
	        return true;
	      }
	      if (event.recordState.state === View.RecordState.Started && event.recordState.userId == BX.message['USER_ID']) {
	        var windowId = View.RecordSource.Chat;
	        var _dialogId2 = this.currentCall.associatedEntity.id;
	        var dialogName = this.currentCall.associatedEntity.name;
	        var callId = this.currentCall.id;
	        var callDate = BX.date.format(this.formatRecordDate);
	        var fileName = BX.message('IM_CALL_RECORD_NAME');
	        if (fileName) {
	          fileName = fileName.replace('#CHAT_TITLE#', dialogName).replace('#CALL_ID#', callId).replace('#DATE#', callDate);
	        } else {
	          fileName = "call_record_" + this.currentCall.id;
	        }
	        CallEngine.getRestClient().callMethod("im.call.onStartRecord", {
	          callId: this.currentCall.id
	        });
	        BXDesktopSystem.CallRecordStart({
	          windowId: windowId,
	          fileName: fileName,
	          callId: callId,
	          callDate: callDate,
	          dialogId: _dialogId2,
	          dialogName: dialogName,
	          video: this.callRecordType !== View.RecordType.Audio,
	          muted: this.currentCall.isMuted(),
	          cropTop: 72,
	          cropBottom: 73,
	          shareMethod: 'im.disk.record.share'
	        });
	      } else if (event.recordState.state === View.RecordState.Stopped) {
	        BXDesktopSystem.CallRecordStop();
	      }
	      return true;
	    }
	  }, {
	    key: "_onCallUserFloorRequest",
	    value: function _onCallUserFloorRequest(e) {
	      if (this.callView) {
	        this.callView.setUserFloorRequestState(e.userId, e.requestActive);
	      }
	    }
	  }, {
	    key: "_onCallFailure",
	    value: function _onCallFailure(e) {
	      var errorCode = e.code || e.name || e.error;
	      console.error("Call failure: ", e);
	      var errorMessage;
	      if (e.name == "VoxConnectionError" || e.name == "AuthResult") {
	        Util$1.reportConnectionResult(e.call.id, false);
	      }
	      if (e.name == "AuthResult" || errorCode == "AUTHORIZE_ERROR") {
	        errorMessage = BX.message("IM_CALL_ERROR_AUTHORIZATION");
	      } else if (e.name == "Failed" && errorCode == 403) {
	        errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
	      } else if (errorCode == "ERROR_UNEXPECTED_ANSWER") {
	        errorMessage = BX.message("IM_CALL_ERROR_UNEXPECTED_ANSWER");
	      } else if (errorCode == "BLANK_ANSWER_WITH_ERROR_CODE") {
	        errorMessage = BX.message("IM_CALL_ERROR_BLANK_ANSWER");
	      } else if (errorCode == "BLANK_ANSWER") {
	        errorMessage = BX.message("IM_CALL_ERROR_BLANK_ANSWER");
	      } else if (errorCode == "ACCESS_DENIED") {
	        errorMessage = BX.message("IM_CALL_ERROR_ACCESS_DENIED");
	      } else if (errorCode == "NO_WEBRTC") {
	        errorMessage = this.isHttps ? BX.message("IM_CALL_NO_WEBRT") : BX.message("IM_CALL_ERROR_HTTPS_REQUIRED");
	      } else if (errorCode == "UNKNOWN_ERROR") {
	        errorMessage = BX.message("IM_CALL_ERROR_UNKNOWN");
	      } else if (errorCode == "NETWORK_ERROR") {
	        errorMessage = BX.message("IM_CALL_ERROR_NETWORK");
	      } else if (errorCode == "NotAllowedError") {
	        errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
	      } else {
	        //errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
	        errorMessage = BX.message("IM_CALL_ERROR_UNKNOWN_WITH_CODE").replace("#ERROR_CODE#", errorCode);
	      }
	      if (this.callView) {
	        this.callView.showFatalError({
	          text: errorMessage
	        });
	      } else {
	        this.showNotification(errorMessage);
	      }
	      this.messengerFacade.stopRepeatSound('dialtone');
	      this.autoCloseCallView = false;
	      if (this.currentCall) {
	        this.removeVideoStrategy();
	        this.removeCallEvents();
	        this.currentCall.destroy();
	        this.currentCall = null;
	      }
	    }
	  }, {
	    key: "_onNetworkProblem",
	    value: function _onNetworkProblem() {
	      this.showNetworkProblemNotification(BX.message("IM_M_CALL_TURN_UNAVAILABLE"));
	    }
	  }, {
	    key: "_onMicrophoneLevel",
	    value: function _onMicrophoneLevel(e) {
	      if (this.callView) {
	        this.callView.setMicrophoneLevel(e.level);
	      }
	    }
	  }, {
	    key: "_onReconnecting",
	    value: function _onReconnecting() {
	      // todo: restore after fixing balloon resurrection issue
	      return false;

	      // noinspection UnreachableCodeJS
	      if (this.reconnectionBaloon) {
	        return;
	      }
	      this.reconnectionBaloon = BX.UI.Notification.Center.notify({
	        content: main_core.Text.encode(BX.message('IM_CALL_RECONNECTING')),
	        autoHide: false,
	        position: "top-right",
	        closeButton: false
	      });
	    }
	  }, {
	    key: "_onReconnected",
	    value: function _onReconnected() {
	      // todo: restore after fixing balloon resurrection issue
	      return false;

	      // noinspection UnreachableCodeJS
	      this._closeReconnectionBaloon();
	    }
	  }, {
	    key: "_onCustomMessage",
	    value: function _onCustomMessage(event) {
	      // there will be no more template selector in this call
	      if (event.message === DOC_CREATED_EVENT) {
	        this.docCreatedForCurrentCall = true;
	        this.maxEditorWidth = DOC_EDITOR_WIDTH;
	      }
	    }
	  }, {
	    key: "_onJoinRoomOffer",
	    value: function _onJoinRoomOffer(event) {
	      console.log("_onJoinRoomOffer", event);
	      if (!event.initiator && !this.currentCall.currentRoom()) {
	        this.currentCall.joinRoom(event.roomId);
	        this.showRoomJoinedPopup(true, event.speaker == this.userId, event.users);
	      }
	    }
	  }, {
	    key: "_onJoinRoom",
	    value: function _onJoinRoom(event) {
	      console.log("_onJoinRoom", event);
	      if (event.speaker == this.userId) {
	        this.callView.setRoomState(View.RoomState.Speaker);
	      } else {
	        this.currentCall.setMuted(true);
	        this.callView.setMuted(true);
	        this.callView.muteSpeaker(true);
	        this.callView.setRoomState(View.RoomState.NonSpeaker);
	      }
	    }
	  }, {
	    key: "_onLeaveRoom",
	    value: function _onLeaveRoom() {
	      // this.callView.setRoomState(View.RoomState.None);
	      this.callView.setRoomState(View.RoomState.Speaker);
	      this.callView.muteSpeaker(false);
	    }
	  }, {
	    key: "_onTransferRoomSpeaker",
	    value: function _onTransferRoomSpeaker(event) {
	      console.log("_onTransferRoomSpeaker", event);
	      if (event.speaker == this.userId) {
	        this.currentCall.setMuted(false);
	        this.callView.setMuted(false);
	        this.callView.setRoomState(View.RoomState.Speaker);
	        if (event.initiator == this.userId) {
	          this.callView.muteSpeaker(false);
	          this.showMicTakenFromPopup(event.previousSpeaker);
	        }
	      } else {
	        this.currentCall.setMuted(true);
	        this.callView.setMuted(true);
	        this.callView.muteSpeaker(true);
	        this.callView.setRoomState(View.RoomState.NonSpeaker);
	        this.showMicTakenByPopup(event.speaker);
	      }
	    }
	  }, {
	    key: "_onCallJoin",
	    value: function _onCallJoin(e) {
	      if (e.local) {
	        // self answer
	        if (this.currentCall && this.currentCall instanceof VoximplantCall) {
	          Util$1.reportConnectionResult(this.currentCall.id, true);
	        }
	        return;
	      }
	      // remote answer, stop ringing and hide incoming cal notification
	      if (this.currentCall) {
	        this.removeVideoStrategy();
	        this.removeCallEvents();
	        this.currentCall = null;
	      }
	      if (this.callView) {
	        this.callView.close();
	      }
	      if (this.callNotification) {
	        this.callNotification.close();
	      }
	      if (this.invitePopup) {
	        this.invitePopup.close();
	      }
	      if (this.floatingWindow) {
	        this.floatingWindow.close();
	      }
	      if (this.mutePopup) {
	        this.mutePopup.close();
	      }
	      this.messengerFacade.stopRepeatSound('dialtone');
	    }
	  }, {
	    key: "_onCallLeave",
	    value: function _onCallLeave(e) {
	      console.log("_onCallLeave", e);
	      if (!e.local && this.currentCall && this.currentCall.ready) {
	        this.log(new Error("received remote leave with active call!"));
	        return;
	      }
	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordStop();
	      }
	      this.callRecordState = View.RecordState.Stopped;
	      this.callRecordType = View.RecordType.None;
	      this.docCreatedForCurrentCall = false;
	      var callDetails;
	      if (this.currentCall && this.currentCall.associatedEntity) {
	        this.removeVideoStrategy();
	        this.removeCallEvents();
	        callDetails = _classPrivateMethodGet$4(this, _getCallDetail, _getCallDetail2).call(this, this.currentCall);
	        this.currentCall = null;
	      }
	      if (this.callView) {
	        this.callView.close();
	      }
	      if (this.invitePopup) {
	        this.invitePopup.close();
	      }
	      if (this.floatingWindow) {
	        this.floatingWindow.close();
	      }
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.close();
	      }
	      if (this.webScreenSharePopup) {
	        this.webScreenSharePopup.close();
	      }
	      if (this.callNotification) {
	        this.callNotification.close();
	      }
	      if (this.mutePopup) {
	        this.mutePopup.close();
	      }
	      this.allowMutePopup = true;
	      if (BX.desktop) {
	        BX.desktop.closeWindow('callBackground');
	      }
	      this.closePromo();
	      this._closeReconnectionBaloon();
	      this.messengerFacade.stopRepeatSound('dialtone');
	      this.messengerFacade.stopRepeatSound('ringtone');
	      this.emit(Events$6.onCallLeft, {
	        callDetails: callDetails
	      });
	    }
	  }, {
	    key: "_onInvitePopupDestroy",
	    value: function _onInvitePopupDestroy() {
	      this.invitePopup = null;
	      this.callView.setHotKeyTemporaryBlock(false);
	    }
	  }, {
	    key: "_onInvitePopupSelect",
	    value: function _onInvitePopupSelect(e) {
	      var _this23 = this;
	      this.invitePopup.close();
	      if (!this.currentCall) {
	        return;
	      }
	      var userId = e.user.id;
	      if (Util$1.isCallServerAllowed() && this.currentCall instanceof PlainCall) {
	        // trying to switch to the server version of the call
	        this.removeVideoStrategy();
	        this.removeCallEvents();
	        CallEngine.createChildCall(this.currentCall.id, Provider.Voximplant, [userId]).then(function (e) {
	          _this23.childCall = e.call;
	          _this23.childCall.addEventListener(CallEvent.onRemoteMediaReceived, _this23._onChildCallFirstMediaHandler);
	          _this23.childCall.addEventListener(CallEvent.onLocalMediaReceived, _this23._onCallLocalMediaReceivedHandler);
	          _this23.childCall.useHdVideo(Hardware.preferHdQuality);
	          if (_this23.currentCall.microphoneId) {
	            _this23.childCall.setMicrophoneId(_this23.currentCall.microphoneId);
	          }
	          if (_this23.currentCall.cameraId) {
	            _this23.childCall.setCameraId(_this23.currentCall.cameraId);
	          }
	          _this23.childCall.inviteUsers({
	            users: _this23.childCall.users
	          });
	        });
	        this.callView.addUser(userId, UserState.Calling);
	        this.callView.updateUserData(babelHelpers.defineProperty({}, userId, e.user));
	      } else {
	        var currentUsers = this.currentCall.getUsers();
	        if (Object.keys(currentUsers).length < Util$1.getUserLimit() - 1 || currentUsers.hasOwnProperty(userId)) {
	          this.currentCall.inviteUsers({
	            users: [userId]
	          });
	        }
	      }
	    }
	  }, {
	    key: "_onWindowFocus",
	    value: function _onWindowFocus() {
	      if (!this.detached) {
	        clearTimeout(this.showFloatingWindowTimeout);
	        clearTimeout(this.showFloatingScreenShareWindowTimeout);
	        if (this.floatingWindow) {
	          this.floatingWindow.hide();
	        }
	        if (this.floatingScreenShareWindow) {
	          this.floatingScreenShareWindow.hide();
	        }
	      }
	    }
	  }, {
	    key: "_onWindowBlur",
	    value: function _onWindowBlur() {
	      var _this24 = this;
	      clearTimeout(this.showFloatingWindowTimeout);
	      clearTimeout(this.showFloatingScreenShareWindowTimeout);
	      if (this.currentCall && this.floatingWindow && this.callView) {
	        this.showFloatingWindowTimeout = setTimeout(function () {
	          if (_this24.currentCall && _this24.floatingWindow && _this24.callView) {
	            _this24.floatingWindow.setTitle(_this24.currentCall.associatedEntity.name);
	            Util$1.getUserAvatars(_this24.currentCall.id, _this24.getActiveCallUsers()).then(function (result) {
	              _this24.floatingWindow.setAvatars(result);
	              _this24.floatingWindow.show();
	            });
	          }
	        }, 300);
	      }
	      if (this.currentCall && this.floatingScreenShareWindow && this.callView && this.currentCall.isScreenSharingStarted()) {
	        this.showFloatingScreenShareWindowTimeout = setTimeout(function () {
	          if (_this24.currentCall && _this24.floatingScreenShareWindow && _this24.callView && _this24.currentCall.isScreenSharingStarted()) {
	            _this24.floatingScreenShareWindow.show();
	          }
	        }, 300);
	      }
	    }
	  }, {
	    key: "_onBeforeUnload",
	    value: function _onBeforeUnload(e) {
	      if (this.floatingWindow) {
	        this.floatingWindow.close();
	      }
	      if (this.callNotification) {
	        this.callNotification.close();
	      }
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.close();
	      }
	      if (this.hasActiveCall()) {
	        e.preventDefault();
	        e.returnValue = '';
	      }
	    }
	  }, {
	    key: "_onImTabChange",
	    value: function _onImTabChange(currentTab) {
	      if (currentTab === "notify" && this.currentCall && this.callView) {
	        this.fold(main_core.Text.decode(this.currentCall.associatedEntity.name));
	      }
	    }
	  }, {
	    key: "_onUpdateChatCounter",
	    value: function _onUpdateChatCounter(counter) {
	      if (!this.currentCall || !this.currentCall.associatedEntity || !this.currentCall.associatedEntity.id || !this.callView) {
	        return;
	      }
	      this.callView.setButtonCounter("chat", counter);
	    }
	  }, {
	    key: "_onDeviceChange",
	    value: function _onDeviceChange(e) {
	      var _this25 = this;
	      if (!this.currentCall || !this.currentCall.ready) {
	        return;
	      }
	      var added = e.data.added;
	      var removed = e.data.removed;
	      if (added.length > 0) {
	        this.log("New devices: ", added);
	        BX.UI.Notification.Center.notify({
	          content: BX.message("IM_CALL_DEVICES_FOUND") + "<br><ul>" + added.map(function (deviceInfo) {
	            return "<li>" + deviceInfo.label;
	          }) + "</ul>",
	          position: "top-right",
	          autoHideDelay: 10000,
	          closeButton: true,
	          //category: "call-device-change",
	          actions: [{
	            title: BX.message("IM_CALL_DEVICES_CLOSE"),
	            events: {
	              click: function click(event, balloon) {
	                return balloon.close();
	              }
	            }
	          }]
	        });
	        setTimeout(function () {
	          return _this25.useDevicesInCurrentCall(added);
	        }, 500);
	      }
	      if (removed.length > 0) {
	        this.log("Removed devices: ", removed);
	        BX.UI.Notification.Center.notify({
	          content: BX.message("IM_CALL_DEVICES_DETACHED") + "<br><ul>" + removed.map(function (deviceInfo) {
	            return "<li>" + deviceInfo.label;
	          }) + "</ul>",
	          position: "top-right",
	          autoHideDelay: 10000,
	          closeButton: true,
	          //category: "call-device-change",
	          actions: [{
	            title: BX.message("IM_CALL_DEVICES_CLOSE"),
	            events: {
	              click: function click(event, balloon) {
	                balloon.close();
	              }
	            }
	          }]
	        });
	        setTimeout(function () {
	          return _this25.removeDevicesFromCurrentCall(removed);
	        }, 500);
	      }
	    }
	  }, {
	    key: "_onFloatingVideoMainAreaClick",
	    value: function _onFloatingVideoMainAreaClick() {
	      BX.desktop.windowCommand("show");
	      BX.desktop.changeTab("im");
	      if (!this.currentCall) {
	        return;
	      }
	      if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id) {
	        this.messengerFacade.openMessenger(this.currentCall.associatedEntity.id);
	      } else if (!this.messengerFacade.isMessengerOpen()) {
	        this.messengerFacade.openMessenger();
	      }
	      if (this.detached) {
	        this.container.style.removeProperty('width');
	        this.callView.show();
	        this.detached = false;
	      }
	    }
	  }, {
	    key: "_onFloatingVideoButtonClick",
	    value: function _onFloatingVideoButtonClick(e) {
	      switch (e.buttonName) {
	        case "toggleMute":
	          this._onCallViewToggleMuteButtonClick(e);
	          break;
	        case "hangup":
	          this._onCallViewHangupButtonClick();
	          break;
	      }
	    }
	  }, {
	    key: "_onFloatingScreenShareBackToCallClick",
	    value: function _onFloatingScreenShareBackToCallClick() {
	      BX.desktop.windowCommand("show");
	      BX.desktop.changeTab("im");
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.hide();
	      }
	    }
	  }, {
	    key: "_onFloatingScreenShareStopClick",
	    value: function _onFloatingScreenShareStopClick() {
	      BX.desktop.windowCommand("show");
	      BX.desktop.changeTab("im");
	      this.currentCall.stopScreenSharing();
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.close();
	      }
	      if (this.isRecording()) {
	        BXDesktopSystem.CallRecordStopSharing();
	      }
	    }
	  }, {
	    key: "_onFloatingScreenShareChangeScreenClick",
	    value: function _onFloatingScreenShareChangeScreenClick() {
	      if (this.currentCall) {
	        this.currentCall.startScreenSharing(true);
	      }
	    }
	  }, {
	    key: "_onResize",
	    value: function _onResize() {
	      if (this.sidebar && this.callView) {
	        var result = this.findCallEditorWidth();
	        var callWidth = result.callWidth;
	        var editorWidth = result.editorWidth;
	        this.callView.setMaxWidth(callWidth);
	        this.sidebar.setWidth(editorWidth);
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.floatingWindow) {
	        this.floatingWindow.destroy();
	        this.floatingWindow = null;
	      }
	      if (this.floatingScreenShareWindow) {
	        this.floatingScreenShareWindow.destroy();
	        this.floatingScreenShareWindow = null;
	      }
	      if (this.resizeObserver) {
	        this.resizeObserver.disconnect();
	        this.resizeObserver = null;
	      }
	      Hardware.unsubscribe(Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipHandler);
	    }
	  }, {
	    key: "log",
	    value: function log() {
	      if (this.currentCall) {
	        var arr = [this.currentCall.id];
	        CallEngine.log.apply(CallEngine, arr.concat(Array.prototype.slice.call(arguments)));
	      } else {
	        CallEngine.log.apply(CallEngine, arguments);
	      }
	    }
	  }, {
	    key: "test",
	    value: function test() {
	      var _this26 = this;
	      var users = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [473, 464];
	      var videoOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
	        width: 320,
	        height: 180
	      };
	      var audioOptions = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      this.messengerFacade.openMessenger().then(function () {
	        return videoOptions || audioOptions ? Hardware.init() : null;
	      }).then(function () {
	        _this26.createContainer();
	        var hiddenButtons = ['floorRequest'];
	        if (!Util$1.shouldShowDocumentButton()) {
	          hiddenButtons.push('document');
	        }
	        _this26.callView = new View({
	          container: _this26.container,
	          baseZIndex: _this26.messengerFacade.getDefaultZIndex(),
	          showChatButtons: true,
	          userLimit: 48,
	          language: _this26.language,
	          layout: View.Layout.Grid,
	          hiddenButtons: hiddenButtons,
	          blockedButtons: _this26.getBlockedButtons()
	        });
	        _this26.lastUserId = 1;
	        _this26.callView.setCallback('onButtonClick', function (e) {
	          return _this26._onTestCallViewButtonClick(e);
	        });
	        //this.callView.blockAddUser();
	        _this26.callView.setCallback(View.Event.onUserClick, function (e) {
	          if (!e.stream) {
	            _this26.callView.setUserState(e.userId, UserState.Connected);
	            _this26.callView.setUserMedia(e.userId, 'video', _this26.stream2.getVideoTracks()[0]);
	          }
	        });
	        _this26.callView.setUiState(View.UiState.Connected);
	        _this26.callView.setCallback(View.Event.onBodyClick, _this26._onCallViewBodyClick.bind(_this26));
	        _this26.callView.setCallback('onShow', _this26._onCallViewShow.bind(_this26));
	        _this26.callView.setCallback('onClose', _this26._onCallViewClose.bind(_this26));
	        _this26.callView.setCallback('onReplaceMicrophone', function (e) {
	          console.log("onReplaceMicrophone", e);
	        });
	        _this26.callView.setCallback('onReplaceCamera', function (e) {
	          console.log("onReplaceCamera", e);
	        });
	        _this26.callView.setCallback('onReplaceSpeaker', function (e) {
	          console.log("onReplaceSpeaker", e);
	        });
	        _this26.callView.setCallback(View.Event.onOpenAdvancedSettings, function (e) {
	          console.log("onOpenAdvancedSettings", e);
	          _this26._onCallViewOpenAdvancedSettings();
	        });
	        _this26.callView.show();
	        if (audioOptions || videoOptions) {
	          return navigator.mediaDevices.getUserMedia({
	            audio: audioOptions,
	            video: videoOptions
	          });
	        } else {
	          return new MediaStream();
	        }
	      }).then(function (s) {
	        _this26.stream = s;
	        _this26.callView.setLocalStream(_this26.stream);
	        users.forEach(function (userId) {
	          return _this26.callView.addUser(userId, UserState.Connected);
	        });
	        if (audioOptions !== false) {
	          _this26.vad = new SimpleVAD({
	            mediaStream: _this26.stream
	          });
	          setInterval(function () {
	            return _this26.callView.setMicrophoneLevel(_this26.vad.currentVolume);
	          }, 100);
	        }
	        if (videoOptions) {
	          return navigator.mediaDevices.getUserMedia({
	            audio: false,
	            video: {
	              width: 320,
	              height: 180
	            }
	          });
	        } else {
	          return new MediaStream();
	        }
	      }).then(function (s2) {
	        _this26.stream2 = s2;
	        /*users.forEach(function(userId)
	        	 {
	        		this.callView.setUserMedia(userId, 'video', stream2.getVideoTracks()[0]);
	        	},this);*/

	        _this26.callView.setUserMedia(users[0], 'video', _this26.stream2.getVideoTracks()[0]);
	        BX.rest.callMethod('im.user.list.get', {
	          'ID': users.concat(_this26.userId),
	          'AVATAR_HR': 'Y'
	        }).then(function (response) {
	          return _this26.callView.updateUserData(response.data());
	        });
	      });
	    }
	  }, {
	    key: "_onTestCallViewButtonClick",
	    value: function _onTestCallViewButtonClick(e) {
	      console.log(e.buttonName);
	      switch (e.buttonName) {
	        case "hangup":
	        case "close":
	          this.callView.close();
	          break;
	        case "inviteUser":
	          /*this.lastUserId++;
	          BX.rest.callMethod('im.user.list.get', {
	          	'ID': [this.lastUserId],
	          	'AVATAR_HR': 'Y'
	          }).then((response) => this.callView.updateUserData(response.data()))
	          	this.callView.addUser(this.lastUserId, UserState.Connecting);*/

	          this._onCallViewInviteUserButtonClick(e);
	          //this.callView.setStream(lastUserId, stream2);
	          break;
	        case "fullscreen":
	          this.toggleFullScreen();
	          break;
	        case "record":
	          this._onCallViewRecordButtonClick(e);
	          break;
	        case "floorRequest":
	          this._onCallViewFloorRequestButtonClick(e);
	          break;
	        case "showChat":
	          this.fold("asd \"asd\"");
	          break;
	        case "toggleScreenSharing":
	          this.callView.setUserMedia(464, 'screen', this.stream2.getVideoTracks()[0]);

	          /*setTimeout(function()
	          	{
	          		this.callView.setUserScreenState(464, true);
	          	}.bind(this), 0);*/
	          break;
	        case "returnToCall":
	          break;
	        case "document":
	          this._onCallViewDocumentButtonClick();
	          break;
	      }
	    }
	  }, {
	    key: "testIncoming",
	    value: function testIncoming(hasCamera) {
	      var _this27 = this;
	      this.callNotification = new IncomingNotification({
	        callerName: "this.currentCall.associatedEntity.name",
	        callerAvatar: "this.currentCall.associatedEntity.avatar",
	        callerType: "this.currentCall.associatedEntity.advanced.chatType",
	        callerColor: "",
	        video: true,
	        hasCamera: !!hasCamera,
	        zIndex: this.messengerFacade.getDefaultZIndex() + 200,
	        onClose: this._onCallNotificationClose.bind(this),
	        onDestroy: this._onCallNotificationDestroy.bind(this),
	        onButtonClick: function onButtonClick(e) {
	          console.log('button pressed', e.data);
	          _this27.callNotification.close();
	        }
	      });
	      this.callNotification.show();
	    }
	  }, {
	    key: "getMaxActiveMicrophonesCount",
	    value: function getMaxActiveMicrophonesCount() {
	      return 4;
	    }
	  }, {
	    key: "showMicMutedNotification",
	    value: function showMicMutedNotification() {
	      var _this28 = this;
	      if (this.mutePopup || !this.callView) {
	        return;
	      }
	      this.mutePopup = new CallHint({
	        callFolded: this.folded,
	        bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
	        targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
	        icon: 'mic-off',
	        buttons: [this.createUnmuteButton()],
	        onClose: function onClose() {
	          _this28.allowMutePopup = false;
	          _this28.mutePopup.destroy();
	          _this28.mutePopup = null;
	        }
	      });
	      this.mutePopup.show();
	    }
	  }, {
	    key: "showAutoMicMuteNotification",
	    value: function showAutoMicMuteNotification() {
	      var _this29 = this;
	      if (this.mutePopup || !this.callView) {
	        return;
	      }
	      this.mutePopup = new CallHint({
	        callFolded: this.folded,
	        bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
	        targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
	        title: main_core.Text.encode(BX.message("IM_CALL_MIC_AUTO_MUTED")),
	        icon: 'mic-off',
	        buttons: [this.createUnmuteButton()],
	        onClose: function onClose() {
	          _this29.mutePopup.destroy();
	          _this29.mutePopup = null;
	        }
	      });
	      this.mutePopup.show();
	    }
	  }, {
	    key: "createUnmuteButton",
	    value: function createUnmuteButton() {
	      var _this30 = this;
	      return new BX.UI.Button({
	        baseClass: "ui-btn ui-btn-icon-mic",
	        text: BX.message("IM_CALL_UNMUTE_MIC"),
	        size: BX.UI.Button.Size.EXTRA_SMALL,
	        color: BX.UI.Button.Color.LIGHT_BORDER,
	        noCaps: true,
	        round: true,
	        events: {
	          click: function click() {
	            _this30._onCallViewToggleMuteButtonClick({
	              muted: false
	            });
	            _this30.mutePopup.destroy();
	            _this30.mutePopup = null;
	          }
	        }
	      });
	    }
	  }, {
	    key: "toggleRoomMenu",
	    value: function toggleRoomMenu(bindElement) {
	      var _this31 = this;
	      if (this.roomMenu) {
	        this.roomMenu.destroy();
	        return;
	      }
	      var roomSpeaker = this.currentCall.currentRoom().speaker;
	      var speakerModel = this.callView.userRegistry.get(roomSpeaker);
	      this.roomMenu = new main_popup.Menu({
	        targetContainer: this.container,
	        bindElement: bindElement,
	        items: [{
	          text: BX.message("IM_CALL_SOUND_PLAYS_VIA"),
	          disabled: true
	        }, {
	          html: "<div class=\"bx-messenger-videocall-room-menu-avatar\" style=\"--avatar: url('".concat(main_core.Text.encode(speakerModel.avatar), "')\"></div>").concat(main_core.Text.encode(speakerModel.name))
	        }, {
	          delimiter: true
	        }, {
	          text: BX.message("IM_CALL_LEAVE_ROOM"),
	          onclick: function onclick() {
	            _this31.currentCall.leaveCurrentRoom();
	            _this31.roomMenu.close();
	          }
	        }, {
	          delimiter: true
	        }, {
	          text: BX.message("IM_CALL_HELP"),
	          onclick: function onclick() {
	            _this31.showRoomHelp();
	            _this31.roomMenu.close();
	          }
	        }],
	        events: {
	          onDestroy: function onDestroy() {
	            return _this31.roomMenu = null;
	          }
	        }
	      });
	      this.roomMenu.show();
	    }
	  }, {
	    key: "toggleRoomListMenu",
	    value: function toggleRoomListMenu(bindElement) {
	      var _this32 = this;
	      if (this.roomListMenu) {
	        this.roomListMenu.destroy();
	        return;
	      }
	      this.currentCall.listRooms().then(function (roomList) {
	        _this32.roomListMenu = new BX.PopupMenuWindow({
	          targetContainer: _this32.container,
	          bindElement: bindElement,
	          items: _this32.prepareRoomListMenuItems(roomList),
	          events: {
	            onDestroy: function onDestroy() {
	              return _this32.roomListMenu = null;
	            }
	          }
	        });
	        _this32.roomListMenu.show();
	      });
	    }
	  }, {
	    key: "prepareRoomListMenuItems",
	    value: function prepareRoomListMenuItems(roomList) {
	      var _menuItems,
	        _this33 = this;
	      var menuItems = [{
	        text: BX.message("IM_CALL_JOIN_ROOM"),
	        disabled: true
	      }, {
	        delimiter: true
	      }];
	      menuItems = (_menuItems = menuItems).concat.apply(_menuItems, babelHelpers.toConsumableArray(roomList.map(function (room) {
	        return {
	          text: _this33.getRoomDescription(room),
	          onclick: function onclick() {
	            if (_this33.currentCall && _this33.currentCall.joinRoom) {
	              _this33.currentCall.joinRoom(room.id);
	            }
	            _this33.roomListMenu.destroy();
	          }
	        };
	      })));
	      menuItems.push({
	        delimiter: true
	      });
	      menuItems.push({
	        text: BX.message("IM_CALL_HELP"),
	        onclick: function onclick() {
	          _this33.showRoomHelp();
	          _this33.roomMenu.close();
	        }
	      });
	      return menuItems;
	    }
	  }, {
	    key: "showRoomHelp",
	    value: function showRoomHelp() {
	      BX.loadExt('ui.dialogs.messagebox').then(function () {
	        BX.UI.Dialogs.MessageBox.alert(BX.message("IM_CALL_HELP_TEXT"), BX.message("IM_CALL_HELP"));
	      });
	    }
	  }, {
	    key: "getRoomDescription",
	    value: function getRoomDescription(roomFields) {
	      var _this34 = this;
	      var userNames = roomFields.userList.map(function (userId) {
	        var userModel = _this34.callView.userRegistry.get(userId);
	        return userModel.name;
	      });
	      var result = BX.message("IM_CALL_ROOM_DESCRIPTION");
	      result = result.replace("#ROOM_ID#", roomFields.id);
	      result = result.replace("#PARTICIPANTS_LIST#", userNames.join(", "));
	      return result;
	    }
	  }, {
	    key: "showRoomJoinedPopup",
	    value: function showRoomJoinedPopup(isAuto, isSpeaker, userIdList) {
	      var _this35 = this;
	      if (this.roomJoinedPopup || !this.callView) {
	        return;
	      }
	      var title;
	      if (!isAuto) {
	        title = BX.message("IM_CALL_ROOM_JOINED_MANUALLY") + "<p>" + BX.message("IM_CALL_ROOM_JOINED_P2") + "</p>";
	      } else {
	        var userNames = userIdList.filter(function (userId) {
	          return userId != _this35.userId;
	        }).map(function (userId) {
	          var userModel = _this35.callView.userRegistry.get(userId);
	          return userModel.name;
	        });
	        var usersInRoom = userNames.join(", ");
	        if (isSpeaker) {
	          title = BX.Text.encode(BX.message("IM_CALL_ROOM_JOINED_AUTO_SPEAKER").replace("#PARTICIPANTS_LIST#", usersInRoom));
	        } else {
	          title = BX.Text.encode(BX.message("IM_CALL_ROOM_JOINED_AUTO").replace("#PARTICIPANTS_LIST#", usersInRoom));
	          title += "<p>" + BX.Text.encode(BX.message("IM_CALL_ROOM_JOINED_P2")) + "</p>";
	        }
	      }
	      this.roomJoinedPopup = new CallHint({
	        callFolded: this.folded,
	        bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
	        targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
	        title: title,
	        buttonsLayout: "bottom",
	        autoCloseDelay: 0,
	        buttons: [new ui_buttons.Button({
	          baseClass: "ui-btn",
	          text: BX.message("IM_CALL_ROOM_JOINED_UNDERSTOOD"),
	          size: ui_buttons.Button.Size.EXTRA_SMALL,
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          noCaps: true,
	          round: true,
	          events: {
	            click: function click() {
	              _this35.roomJoinedPopup.destroy();
	              _this35.roomJoinedPopup = null;
	            }
	          }
	        }), new ui_buttons.Button({
	          text: BX.message("IM_CALL_ROOM_WRONG_ROOM"),
	          size: ui_buttons.Button.Size.EXTRA_SMALL,
	          color: ui_buttons.Button.Color.LINK,
	          noCaps: true,
	          round: true,
	          events: {
	            click: function click() {
	              _this35.roomJoinedPopup.destroy();
	              _this35.roomJoinedPopup = null;
	              _this35.currentCall.leaveCurrentRoom();
	            }
	          }
	        })],
	        onClose: function onClose() {
	          _this35.roomJoinedPopup.destroy();
	          _this35.roomJoinedPopup = null;
	        }
	      });
	      this.roomJoinedPopup.show();
	    }
	  }, {
	    key: "showMicTakenFromPopup",
	    value: function showMicTakenFromPopup(fromUserId) {
	      var _this36 = this;
	      if (this.micTakenFromPopup || !this.callView) {
	        return;
	      }
	      var userModel = this.callView.userRegistry.get(fromUserId);
	      var title = BX.message("IM_CALL_ROOM_MIC_TAKEN_FROM").replace('#USER_NAME#', userModel.name);
	      this.micTakenFromPopup = new CallHint({
	        callFolded: this.folded,
	        bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
	        targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
	        title: BX.Text.encode(title),
	        buttonsLayout: "right",
	        autoCloseDelay: 5000,
	        buttons: [
	          /*new Button({
	          		text: BX.message("IM_CALL_ROOM_DETAILS"),
	          		size: Button.Size.SMALL,
	          		color: Button.Color.LINK,
	          		noCaps: true,
	          		round: true,
	          		events: {
	          			click: () => {this.micTakenFromPopup.destroy(); this.micTakenFromPopup = null;}
	          		}
	          	}),*/
	        ],
	        onClose: function onClose() {
	          _this36.micTakenFromPopup.destroy();
	          _this36.micTakenFromPopup = null;
	        }
	      });
	      this.micTakenFromPopup.show();
	    }
	  }, {
	    key: "showMicTakenByPopup",
	    value: function showMicTakenByPopup(byUserId) {
	      var _this37 = this;
	      if (this.micTakenByPopup || !this.callView) {
	        return;
	      }
	      var userModel = this.callView.userRegistry.get(byUserId);
	      this.micTakenByPopup = new CallHint({
	        callFolded: this.folded,
	        bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
	        targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
	        title: BX.Text.encode(BX.message("IM_CALL_ROOM_MIC_TAKEN_BY").replace('#USER_NAME#', userModel.name)),
	        buttonsLayout: "right",
	        autoCloseDelay: 5000,
	        buttons: [
	          /*new Button({
	          		text: BX.message("IM_CALL_ROOM_DETAILS"),
	          		size: Button.Size.SMALL,
	          		color: Button.Color.LINK,
	          		noCaps: true,
	          		round: true,
	          		events: {
	          			click: () => {this.micTakenByPopup.destroy(); this.micTakenByPopup = null;}
	          		}
	          	}),*/
	        ],
	        onClose: function onClose() {
	          _this37.micTakenByPopup.destroy();
	          _this37.micTakenByPopup = null;
	        }
	      });
	      this.micTakenByPopup.show();
	    }
	  }, {
	    key: "showWebScreenSharePopup",
	    value: function showWebScreenSharePopup() {
	      var _this38 = this;
	      if (this.webScreenSharePopup) {
	        this.webScreenSharePopup.show();
	        return;
	      }
	      this.webScreenSharePopup = new WebScreenSharePopup({
	        bindElement: this.callView.buttons.screen.elements.root,
	        targetContainer: this.callView.container,
	        onClose: function onClose() {
	          _this38.webScreenSharePopup.destroy();
	          _this38.webScreenSharePopup = null;
	        },
	        onStopSharingClick: function onStopSharingClick() {
	          _this38._onCallViewToggleScreenSharingButtonClick();
	          _this38.webScreenSharePopup.destroy();
	          _this38.webScreenSharePopup = null;
	        }
	      });
	      this.webScreenSharePopup.show();
	    }
	  }, {
	    key: "showFeedbackPopup",
	    value: function showFeedbackPopup(callDetails) {
	      if (!callDetails) {
	        if (this.lastCallDetails) {
	          callDetails = this.lastCallDetails;
	        } else {
	          console.error('Could not show feedback without call');
	        }
	      }
	      BX.loadExt('ui.feedback.form').then(function () {
	        BX.UI.Feedback.Form.open({
	          id: 'call_feedback_' + Math.random(),
	          forms: [{
	            zones: ['ru'],
	            id: 406,
	            sec: '9lhjhn',
	            lang: 'ru'
	          }],
	          presets: {
	            call_id: callDetails.id || 0,
	            call_amount: callDetails.userCount || 0
	          }
	        });
	      });
	    }
	  }, {
	    key: "showFeedbackPopup_",
	    value: function showFeedbackPopup_(callDetails) {
	      var _this39 = this;
	      if (this.feedbackPopup) {
	        return;
	      }
	      if (!callDetails) {
	        callDetails = this.lastCallDetails;
	      }
	      var darkMode = this.messengerFacade.isThemeDark();
	      if (!main_core.Type.isPlainObject(callDetails)) {
	        return;
	      }
	      BX.loadExt('im.component.call-feedback').then(function () {
	        var _Popup;
	        var vueInstance;
	        _this39.feedbackPopup = new main_popup.Popup((_Popup = {
	          id: 'im-call-feedback',
	          content: '',
	          titleBar: BX.message('IM_CALL_QUALITY_FEEDBACK'),
	          closeIcon: true,
	          noAllPaddings: true,
	          cacheable: false,
	          background: darkMode ? '#3A414B' : null,
	          darkMode: darkMode,
	          closeByEsc: true,
	          autoHide: true
	        }, babelHelpers.defineProperty(_Popup, "cacheable", false), babelHelpers.defineProperty(_Popup, "events", {
	          onPopupDestroy: function onPopupDestroy() {
	            if (vueInstance) {
	              vueInstance.$destroy();
	            }
	            _this39.feedbackPopup = null;
	          }
	        }), _Popup));
	        var template = '<bx-im-component-call-feedback ' + '@feedbackSent="onFeedbackSent" ' + ':darkMode="darkMode" ' + ':callDetails="callDetails" />';
	        vueInstance = BX.Vue.createApp({
	          template: template,
	          data: function data() {
	            return {
	              darkMode: darkMode,
	              callDetails: callDetails
	            };
	          },
	          methods: {
	            onFeedbackSent: function onFeedbackSent() {
	              setTimeout(function () {
	                if (_this39.feedbackPopup) {
	                  _this39.feedbackPopup.close();
	                }
	              }, 1500);
	            }
	          }
	        });
	        vueInstance.mount('#' + _this39.feedbackPopup.getContentContainer().id);
	        _this39.feedbackPopup.show();
	      });
	    }
	  }, {
	    key: "userId",
	    get: function get() {
	      return Number(BX.message('USER_ID'));
	    }
	  }, {
	    key: "callViewState",
	    get: function get() {
	      return this._callViewState;
	    },
	    set: function set(newState) {
	      if (this.callViewState == newState) {
	        return;
	      }
	      this._callViewState = newState;
	      this.emit(Events$6.onViewStateChanged, {
	        callViewState: newState
	      });
	    }
	  }]);
	  return CallController;
	}(main_core_events.EventEmitter);
	function _subscribeEvents2$1(config) {
	  var eventKeys = Object.keys(Events$6);
	  for (var _i = 0, _eventKeys = eventKeys; _i < _eventKeys.length; _i++) {
	    var eventName = _eventKeys[_i];
	    if (main_core.Type.isFunction(config.events[eventName])) {
	      this.subscribe(Events$6[eventName], config.events[eventName]);
	    }
	  }
	}
	function _getCallDetail2(call) {
	  return {
	    id: call.id,
	    provider: call.provider,
	    chatId: call.associatedEntity.id,
	    userCount: call.users.length,
	    browser: Util$1.getBrowserForStatistics(),
	    isMobile: main_core.Browser.isMobile(),
	    isConference: false,
	    wasConnected: call.wasConnected
	  };
	}
	babelHelpers.defineProperty(CallController, "FeatureState", FeatureState);
	babelHelpers.defineProperty(CallController, "Events", Events$6);
	babelHelpers.defineProperty(CallController, "ViewState", ViewState);
	babelHelpers.defineProperty(CallController, "DocumentType", DocumentType);

	applyHacks();

	// compatibility
	BX.CallEngine = CallEngine;

	exports.BackgroundDialog = BackgroundDialog;
	exports.Controller = CallController;
	exports.Engine = CallEngine;
	exports.Event = CallEvent;
	exports.Hint = CallHint;
	exports.EndpointDirection = EndpointDirection;
	exports.FloatingScreenShare = FloatingScreenShare;
	exports.FloatingScreenShareContent = FloatingScreenShareContent;
	exports.IncomingNotificationContent = IncomingNotificationContent;
	exports.NotificationConferenceContent = NotificationConferenceContent;
	exports.Hardware = Hardware;
	exports.Provider = Provider;
	exports.Type = CallType;
	exports.UserState = UserState;
	exports.Util = Util$1;
	exports.VideoStrategy = VideoStrategy;
	exports.View = View;
	exports.WebScreenSharePopup = WebScreenSharePopup;

}((this.BX.Call = this.BX.Call || {}),BX.Messenger.Lib,BX.UI,BX.UI.Dialogs,BX.UI,BX.Event,BX.Main,BX,BX,BX,BX,BX.Messenger.Lib));
//# sourceMappingURL=call.bundle.js.map
