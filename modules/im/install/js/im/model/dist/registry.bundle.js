/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_lib_logger,main_core_events,im_lib_utils,ui_vue,ui_vue_vuex,main_core,im_const) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Application model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var ApplicationModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(ApplicationModel, _VuexBuilderModel);
	  function ApplicationModel() {
	    babelHelpers.classCallCheck(this, ApplicationModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ApplicationModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(ApplicationModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'application';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        common: {
	          host: this.getVariable('common.host', location.protocol + '//' + location.host),
	          siteId: this.getVariable('common.siteId', 'default'),
	          userId: this.getVariable('common.userId', 0),
	          languageId: this.getVariable('common.languageId', 'en')
	        },
	        dialog: {
	          dialogId: this.getVariable('dialog.dialogId', '0'),
	          chatId: this.getVariable('dialog.chatId', 0),
	          diskFolderId: this.getVariable('dialog.diskFolderId', 0),
	          messageLimit: this.getVariable('dialog.messageLimit', 20),
	          enableReadMessages: this.getVariable('dialog.enableReadMessages', true),
	          messageExtraCount: 0
	        },
	        disk: {
	          enabled: false,
	          maxFileSize: 5242880
	        },
	        call: {
	          serverEnabled: false,
	          maxParticipants: 24
	        },
	        mobile: {
	          keyboardShow: false
	        },
	        device: {
	          type: this.getVariable('device.type', im_const.DeviceType.desktop),
	          orientation: this.getVariable('device.orientation', im_const.DeviceOrientation.portrait)
	        },
	        options: {
	          quoteEnable: this.getVariable('options.quoteEnable', true),
	          quoteFromRight: this.getVariable('options.quoteFromRight', true),
	          autoplayVideo: this.getVariable('options.autoplayVideo', true),
	          darkBackground: this.getVariable('options.darkBackground', false),
	          showSmiles: false
	        },
	        error: {
	          active: false,
	          code: '',
	          description: ''
	        }
	      };
	    }
	  }, {
	    key: "getStateSaveException",
	    value: function getStateSaveException() {
	      return Object.assign({
	        common: this.getVariable('saveException.common', null),
	        dialog: this.getVariable('saveException.dialog', null),
	        mobile: this.getVariable('saveException.mobile', null),
	        device: this.getVariable('saveException.device', null),
	        error: this.getVariable('saveException.error', null)
	      });
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this = this;
	      return {
	        set: function set(store, payload) {
	          store.commit('set', _this.validate(payload));
	        },
	        showSmiles: function showSmiles(store, payload) {
	          store.commit('showSmiles');
	        },
	        hideSmiles: function hideSmiles(store, payload) {
	          store.commit('hideSmiles');
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this2 = this;
	      return {
	        set: function set(state, payload) {
	          var hasChange = false;
	          for (var group in payload) {
	            if (!payload.hasOwnProperty(group)) {
	              continue;
	            }
	            for (var field in payload[group]) {
	              if (!payload[group].hasOwnProperty(field)) {
	                continue;
	              }
	              state[group][field] = payload[group][field];
	              hasChange = true;
	            }
	          }
	          if (hasChange && _this2.isSaveNeeded(payload)) {
	            _this2.saveState(state);
	          }
	        },
	        increaseDialogExtraCount: function increaseDialogExtraCount(state) {
	          var payload = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	          var _payload$count = payload.count,
	            count = _payload$count === void 0 ? 1 : _payload$count;
	          state.dialog.messageExtraCount += count;
	        },
	        decreaseDialogExtraCount: function decreaseDialogExtraCount(state) {
	          var payload = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	          var _payload$count2 = payload.count,
	            count = _payload$count2 === void 0 ? 1 : _payload$count2;
	          var newCounter = state.dialog.messageExtraCount - count;
	          if (newCounter <= 0) {
	            newCounter = 0;
	          }
	          state.dialog.messageExtraCount = newCounter;
	        },
	        clearDialogExtraCount: function clearDialogExtraCount(state) {
	          state.dialog.messageExtraCount = 0;
	        },
	        showSmiles: function showSmiles(state) {
	          state.options.showSmiles = true;
	        },
	        hideSmiles: function hideSmiles(state) {
	          state.options.showSmiles = false;
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var result = {};
	      if (babelHelpers["typeof"](fields.common) === 'object' && fields.common) {
	        result.common = {};
	        if (typeof fields.common.userId === 'number') {
	          result.common.userId = fields.common.userId;
	        }
	        if (typeof fields.common.languageId === 'string') {
	          result.common.languageId = fields.common.languageId;
	        }
	      }
	      if (babelHelpers["typeof"](fields.dialog) === 'object' && fields.dialog) {
	        result.dialog = {};
	        if (typeof fields.dialog.dialogId === 'number') {
	          result.dialog.dialogId = fields.dialog.dialogId.toString();
	          result.dialog.chatId = 0;
	        } else if (typeof fields.dialog.dialogId === 'string') {
	          result.dialog.dialogId = fields.dialog.dialogId;
	          if (typeof fields.dialog.chatId !== 'number') {
	            var chatId = fields.dialog.dialogId;
	            if (chatId.startsWith('chat')) {
	              chatId = fields.dialog.dialogId.substr(4);
	            }
	            chatId = parseInt(chatId);
	            result.dialog.chatId = !isNaN(chatId) ? chatId : 0;
	            fields.dialog.chatId = result.dialog.chatId;
	          }
	        }
	        if (typeof fields.dialog.chatId === 'number') {
	          result.dialog.chatId = fields.dialog.chatId;
	        }
	        if (typeof fields.dialog.diskFolderId === 'number') {
	          result.dialog.diskFolderId = fields.dialog.diskFolderId;
	        }
	        if (typeof fields.dialog.messageLimit === 'number') {
	          result.dialog.messageLimit = fields.dialog.messageLimit;
	        }
	        if (typeof fields.dialog.messageExtraCount === 'number') {
	          result.dialog.messageExtraCount = fields.dialog.messageExtraCount;
	        }
	        if (typeof fields.dialog.enableReadMessages === 'boolean') {
	          result.dialog.enableReadMessages = fields.dialog.enableReadMessages;
	        }
	      }
	      if (babelHelpers["typeof"](fields.disk) === 'object' && fields.disk) {
	        result.disk = {};
	        if (typeof fields.disk.enabled === 'boolean') {
	          result.disk.enabled = fields.disk.enabled;
	        }
	        if (typeof fields.disk.maxFileSize === 'number') {
	          result.disk.maxFileSize = fields.disk.maxFileSize;
	        }
	      }
	      if (babelHelpers["typeof"](fields.call) === 'object' && fields.call) {
	        result.call = {};
	        if (typeof fields.call.serverEnabled === 'boolean') {
	          result.call.serverEnabled = fields.call.serverEnabled;
	        }
	        if (typeof fields.call.maxParticipants === 'number') {
	          result.call.maxParticipants = fields.call.maxParticipants;
	        }
	      }
	      if (babelHelpers["typeof"](fields.mobile) === 'object' && fields.mobile) {
	        result.mobile = {};
	        if (typeof fields.mobile.keyboardShow === 'boolean') {
	          result.mobile.keyboardShow = fields.mobile.keyboardShow;
	        }
	      }
	      if (babelHelpers["typeof"](fields.device) === 'object' && fields.device) {
	        result.device = {};
	        if (typeof fields.device.type === 'string' && typeof im_const.DeviceType[fields.device.type] !== 'undefined') {
	          result.device.type = fields.device.type;
	        }
	        if (typeof fields.device.orientation === 'string' && typeof im_const.DeviceOrientation[fields.device.orientation] !== 'undefined') {
	          result.device.orientation = fields.device.orientation;
	        }
	      }
	      if (babelHelpers["typeof"](fields.error) === 'object' && fields.error) {
	        if (typeof fields.error.active === 'boolean') {
	          result.error = {
	            active: fields.error.active,
	            code: fields.error.code.toString() || '',
	            description: fields.error.description.toString() || ''
	          };
	        }
	      }
	      return result;
	    }
	  }]);
	  return ApplicationModel;
	}(ui_vue_vuex.VuexBuilderModel);

	/**
	 * Bitrix Messenger
	 * Call Application model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var ConferenceModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(ConferenceModel, _VuexBuilderModel);
	  function ConferenceModel() {
	    babelHelpers.classCallCheck(this, ConferenceModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConferenceModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(ConferenceModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'conference';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        common: {
	          inited: false,
	          passChecked: true,
	          showChat: false,
	          userCount: 0,
	          messageCount: 0,
	          userInCallCount: 0,
	          state: im_const.ConferenceStateType.preparation,
	          callEnded: false,
	          showSmiles: false,
	          error: '',
	          conferenceTitle: '',
	          alias: '',
	          permissionsRequested: false,
	          conferenceStarted: null,
	          conferenceStartDate: null,
	          joinWithVideo: null,
	          userReadyToJoin: false,
	          isBroadcast: false,
	          users: [],
	          usersInCall: [],
	          presenters: [],
	          rightPanelMode: im_const.ConferenceRightPanelMode.hidden
	        },
	        user: {
	          id: -1,
	          hash: ''
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      return {
	        showChat: function showChat(store, payload) {
	          if (typeof payload.newState !== 'boolean') {
	            return false;
	          }
	          store.commit('showChat', payload);
	        },
	        changeRightPanelMode: function changeRightPanelMode(store, payload) {
	          if (!im_const.ConferenceRightPanelMode[payload.mode]) {
	            return false;
	          }
	          store.commit('changeRightPanelMode', payload);
	        },
	        setPermissionsRequested: function setPermissionsRequested(store, payload) {
	          if (typeof payload.status !== 'boolean') {
	            return false;
	          }
	          store.commit('setPermissionsRequested', payload);
	        },
	        setPresenters: function setPresenters(store, payload) {
	          if (!Array.isArray(payload.presenters)) {
	            payload.presenters = [payload.presenters];
	          }
	          store.commit('setPresenters', payload);
	        },
	        setUsers: function setUsers(store, payload) {
	          if (!Array.isArray(payload.users)) {
	            payload.users = [payload.users];
	          }
	          store.commit('setUsers', payload);
	        },
	        removeUsers: function removeUsers(store, payload) {
	          if (!Array.isArray(payload.users)) {
	            payload.users = [payload.users];
	          }
	          store.commit('removeUsers', payload);
	        },
	        setUsersInCall: function setUsersInCall(store, payload) {
	          if (!Array.isArray(payload.users)) {
	            payload.users = [payload.users];
	          }
	          store.commit('setUsersInCall', payload);
	        },
	        removeUsersInCall: function removeUsersInCall(store, payload) {
	          if (!Array.isArray(payload.users)) {
	            payload.users = [payload.users];
	          }
	          store.commit('removeUsersInCall', payload);
	        },
	        setConferenceTitle: function setConferenceTitle(store, payload) {
	          if (typeof payload.conferenceTitle !== 'string') {
	            return false;
	          }
	          store.commit('setConferenceTitle', payload);
	        },
	        setBroadcastMode: function setBroadcastMode(store, payload) {
	          if (typeof payload.broadcastMode !== 'boolean') {
	            return false;
	          }
	          store.commit('setBroadcastMode', payload);
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this = this;
	      return {
	        common: function common(state, payload) {
	          if (typeof payload.inited === 'boolean') {
	            state.common.inited = payload.inited;
	          }
	          if (typeof payload.passChecked === 'boolean') {
	            state.common.passChecked = payload.passChecked;
	          }
	          if (typeof payload.userCount === 'number' || typeof payload.userCount === 'string') {
	            state.common.userCount = parseInt(payload.userCount);
	          }
	          if (typeof payload.messageCount === 'number' || typeof payload.messageCount === 'string') {
	            state.common.messageCount = parseInt(payload.messageCount);
	          }
	          if (typeof payload.userInCallCount === 'number' || typeof payload.userInCallCount === 'string') {
	            state.common.userInCallCount = parseInt(payload.userInCallCount);
	          }
	          if (typeof payload.componentError === 'string') {
	            state.common.componentError = payload.componentError;
	          }
	          if (typeof payload.isBroadcast === 'boolean') {
	            state.common.isBroadcast = payload.isBroadcast;
	          }
	          if (Array.isArray(payload.presenters)) {
	            state.common.presenters = payload.presenters;
	          }
	        },
	        user: function user(state, payload) {
	          if (typeof payload.id === 'number') {
	            state.user.id = payload.id;
	          }
	          if (typeof payload.hash === 'string' && payload.hash !== state.user.hash) {
	            state.user.hash = payload.hash;
	          }
	          if (_this.isSaveNeeded({
	            user: payload
	          })) {
	            _this.saveState(state);
	          }
	        },
	        showChat: function showChat(state, _ref) {
	          var newState = _ref.newState;
	          state.common.showChat = newState;
	        },
	        changeRightPanelMode: function changeRightPanelMode(state, _ref2) {
	          var mode = _ref2.mode;
	          state.common.rightPanelMode = mode;
	        },
	        setPermissionsRequested: function setPermissionsRequested(state, payload) {
	          state.common.permissionsRequested = payload.status;
	        },
	        startCall: function startCall(state, payload) {
	          state.common.state = im_const.ConferenceStateType.call;
	          state.common.callEnded = false;
	        },
	        endCall: function endCall(state, payload) {
	          state.common.state = im_const.ConferenceStateType.preparation;
	          state.common.callEnded = true;
	        },
	        returnToPreparation: function returnToPreparation(state, payload) {
	          state.common.state = im_const.ConferenceStateType.preparation;
	        },
	        toggleSmiles: function toggleSmiles(state, payload) {
	          state.common.showSmiles = !state.common.showSmiles;
	        },
	        setError: function setError(state, payload) {
	          if (typeof payload.errorCode === 'string') {
	            state.common.error = payload.errorCode;
	          }
	        },
	        setConferenceTitle: function setConferenceTitle(state, payload) {
	          state.common.conferenceTitle = payload.conferenceTitle;
	        },
	        setBroadcastMode: function setBroadcastMode(state, payload) {
	          state.common.isBroadcast = payload.broadcastMode;
	        },
	        setAlias: function setAlias(state, payload) {
	          if (typeof payload.alias === 'string') {
	            state.common.alias = payload.alias;
	          }
	        },
	        setJoinType: function setJoinType(state, payload) {
	          if (typeof payload.joinWithVideo === 'boolean') {
	            state.common.joinWithVideo = payload.joinWithVideo;
	          }
	        },
	        setConferenceStatus: function setConferenceStatus(state, payload) {
	          if (typeof payload.conferenceStarted === 'boolean') {
	            state.common.conferenceStarted = payload.conferenceStarted;
	          }
	        },
	        setConferenceStartDate: function setConferenceStartDate(state, payload) {
	          if (payload.conferenceStartDate instanceof Date) {
	            state.common.conferenceStartDate = payload.conferenceStartDate;
	          }
	        },
	        setUserReadyToJoin: function setUserReadyToJoin(state, payload) {
	          state.common.userReadyToJoin = true;
	        },
	        setPresenters: function setPresenters(state, payload) {
	          if (payload.replace) {
	            state.common.presenters = payload.presenters;
	          } else {
	            payload.presenters.forEach(function (presenter) {
	              presenter = parseInt(presenter);
	              if (!state.common.presenters.includes(presenter)) {
	                state.common.presenters.push(presenter);
	              }
	            });
	          }
	        },
	        setUsers: function setUsers(state, payload) {
	          payload.users.forEach(function (user) {
	            user = parseInt(user);
	            if (!state.common.users.includes(user)) {
	              state.common.users.push(user);
	            }
	          });
	        },
	        removeUsers: function removeUsers(state, payload) {
	          state.common.users = state.common.users.filter(function (user) {
	            return !payload.users.includes(parseInt(user));
	          });
	        },
	        setUsersInCall: function setUsersInCall(state, payload) {
	          payload.users.forEach(function (user) {
	            user = parseInt(user);
	            if (!state.common.usersInCall.includes(user)) {
	              state.common.usersInCall.push(user);
	            }
	          });
	        },
	        removeUsersInCall: function removeUsersInCall(state, payload) {
	          state.common.usersInCall = state.common.usersInCall.filter(function (user) {
	            return !payload.users.includes(parseInt(user));
	          });
	        }
	      };
	    }
	  }, {
	    key: "getStateSaveException",
	    value: function getStateSaveException() {
	      return {
	        common: {
	          inited: null,
	          state: null,
	          showSmiles: null,
	          userCount: null,
	          messageCount: null,
	          userInCallCount: null,
	          error: null,
	          conferenceTitle: null,
	          alias: null,
	          conferenceStarted: null,
	          conferenceStartDate: null,
	          joinWithVideo: null,
	          userReadyToJoin: null,
	          rightPanelMode: null,
	          presenters: null,
	          users: null
	        }
	      };
	    }
	  }]);
	  return ConferenceModel;
	}(ui_vue_vuex.VuexBuilderModel);

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var IntersectionType = {
	  empty: 'empty',
	  equal: 'equal',
	  none: 'none',
	  found: 'found',
	  foundReverse: 'foundReverse'
	};
	var MessagesModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(MessagesModel, _VuexBuilderModel);
	  function MessagesModel() {
	    babelHelpers.classCallCheck(this, MessagesModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MessagesModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(MessagesModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'messages';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        created: 0,
	        collection: {},
	        mutationType: {},
	        saveMessageList: {},
	        saveFileList: {},
	        saveUserList: {},
	        host: this.getVariable('host', location.protocol + '//' + location.host)
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        templateId: 0,
	        templateType: 'message',
	        placeholderType: 0,
	        id: 0,
	        chatId: 0,
	        authorId: 0,
	        date: new Date(),
	        text: "",
	        textConverted: "",
	        params: {
	          TYPE: 'default',
	          COMPONENT_ID: 'bx-im-view-message'
	        },
	        push: false,
	        unread: false,
	        sending: false,
	        error: false,
	        retry: false,
	        blink: false
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;
	      return {
	        getMutationType: function getMutationType(state) {
	          return function (chatId) {
	            if (!state.mutationType[chatId]) {
	              return {
	                initialType: im_const.MutationType.none,
	                appliedType: im_const.MutationType.none
	              };
	            }
	            return state.mutationType[chatId];
	          };
	        },
	        getLastId: function getLastId(state) {
	          return function (chatId) {
	            if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	              return null;
	            }
	            var lastId = 0;
	            for (var i = 0; i < state.collection[chatId].length; i++) {
	              var element = state.collection[chatId][i];
	              if (element.push || element.sending || element.id.toString().startsWith('temporary')) {
	                continue;
	              }
	              if (lastId < element.id) {
	                lastId = element.id;
	              }
	            }
	            return lastId ? lastId : null;
	          };
	        },
	        getMessage: function getMessage(state) {
	          return function (chatId, messageId) {
	            if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	              return null;
	            }
	            for (var index = state.collection[chatId].length - 1; index >= 0; index--) {
	              if (state.collection[chatId][index].id === messageId) {
	                return state.collection[chatId][index];
	              }
	            }
	            return null;
	          };
	        },
	        get: function get(state) {
	          return function (chatId) {
	            if (!state.collection[chatId] || state.collection[chatId].length <= 0) {
	              return [];
	            }
	            return state.collection[chatId];
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState();
	          };
	        },
	        getSaveFileList: function getSaveFileList(state) {
	          return function (params) {
	            return state.saveFileList;
	          };
	        },
	        getSaveUserList: function getSaveUserList(state) {
	          return function (params) {
	            return state.saveUserList;
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;
	      return {
	        add: function add(store, payload) {
	          var result = _this2.validate(Object.assign({}, payload));
	          result.params = Object.assign({}, _this2.getElementState().params, result.params);
	          if (payload.id) {
	            if (store.state.collection[payload.chatId]) {
	              var countMessages = store.state.collection[payload.chatId].length - 1;
	              for (var index = countMessages; index >= 0; index--) {
	                var message = store.state.collection[payload.chatId][index];
	                if (message.templateId === payload.id) {
	                  return;
	                }
	              }
	            }
	            result.id = payload.id;
	          } else {
	            result.id = 'temporary' + new Date().getTime() + store.state.created;
	          }
	          result.templateId = result.id;
	          result.unread = false;
	          store.commit('add', Object.assign({}, _this2.getElementState(), result));
	          if (payload.sending !== false) {
	            store.dispatch('actionStart', {
	              id: result.id,
	              chatId: result.chatId
	            });
	          }
	          return result.id;
	        },
	        actionStart: function actionStart(store, payload) {
	          if (/^\d+$/.test(payload.id)) {
	            payload.id = parseInt(payload.id);
	          }
	          payload.chatId = parseInt(payload.chatId);
	          ui_vue.Vue.nextTick(function () {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                sending: true
	              }
	            });
	          });
	        },
	        actionError: function actionError(store, payload) {
	          if (/^\d+$/.test(payload.id)) {
	            payload.id = parseInt(payload.id);
	          }
	          payload.chatId = parseInt(payload.chatId);
	          ui_vue.Vue.nextTick(function () {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                sending: false,
	                error: true,
	                retry: payload.retry !== false
	              }
	            });
	          });
	        },
	        actionFinish: function actionFinish(store, payload) {
	          if (/^\d+$/.test(payload.id)) {
	            payload.id = parseInt(payload.id);
	          }
	          payload.chatId = parseInt(payload.chatId);
	          ui_vue.Vue.nextTick(function () {
	            store.commit('update', {
	              id: payload.id,
	              chatId: payload.chatId,
	              fields: {
	                sending: false,
	                error: false,
	                retry: false
	              }
	            });
	          });
	        },
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (message) {
	              return _this2.prepareMessage(message, {
	                host: store.state.host
	              });
	            });
	          } else {
	            var result = _this2.prepareMessage(payload, {
	              host: store.state.host
	            });
	            (payload = []).push(result);
	          }
	          store.commit('set', {
	            insertType: im_const.MutationType.set,
	            data: payload
	          });
	          return 'set is done';
	        },
	        addPlaceholders: function addPlaceholders(store, payload) {
	          if (payload.placeholders instanceof Array) {
	            payload.placeholders = payload.placeholders.map(function (message) {
	              return _this2.prepareMessage(message, {
	                host: store.state.host
	              });
	            });
	          } else {
	            return false;
	          }
	          var insertType = payload.requestMode === 'history' ? im_const.MutationType.setBefore : im_const.MutationType.setAfter;
	          if (insertType === im_const.MutationType.setBefore) {
	            payload.placeholders = payload.placeholders.reverse();
	          }
	          store.commit('set', {
	            insertType: insertType,
	            data: payload.placeholders
	          });
	          return payload.placeholders[0].id;
	        },
	        clearPlaceholders: function clearPlaceholders(store, payload) {
	          store.commit('clearPlaceholders', payload);
	        },
	        updatePlaceholders: function updatePlaceholders(store, payload) {
	          if (payload.data instanceof Array) {
	            payload.data = payload.data.map(function (message) {
	              return _this2.prepareMessage(message, {
	                host: store.state.host
	              });
	            });
	          } else {
	            return false;
	          }
	          store.commit('updatePlaceholders', payload);
	          return true;
	        },
	        setAfter: function setAfter(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (message) {
	              return _this2.prepareMessage(message);
	            });
	          } else {
	            var result = _this2.prepareMessage(payload);
	            (payload = []).push(result);
	          }
	          store.commit('set', {
	            insertType: im_const.MutationType.setAfter,
	            data: payload
	          });
	        },
	        setBefore: function setBefore(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (message) {
	              return _this2.prepareMessage(message);
	            });
	          } else {
	            var result = _this2.prepareMessage(payload);
	            (payload = []).push(result);
	          }
	          store.commit('set', {
	            insertType: im_const.MutationType.setBefore,
	            data: payload
	          });
	        },
	        update: function update(store, payload) {
	          if (/^\d+$/.test(payload.id)) {
	            payload.id = parseInt(payload.id);
	          }
	          if (/^\d+$/.test(payload.chatId)) {
	            payload.chatId = parseInt(payload.chatId);
	          }
	          store.commit('initCollection', {
	            chatId: payload.chatId
	          });
	          if (!store.state.collection[payload.chatId]) {
	            return false;
	          }
	          var index = store.state.collection[payload.chatId].findIndex(function (el) {
	            return el.id === payload.id;
	          });
	          if (index < 0) {
	            return false;
	          }
	          var result = _this2.validate(Object.assign({}, payload.fields));
	          if (result.params) {
	            result.params = Object.assign({}, _this2.getElementState().params, store.state.collection[payload.chatId][index].params, result.params);
	          }
	          store.commit('update', {
	            id: payload.id,
	            chatId: payload.chatId,
	            index: index,
	            fields: result
	          });
	          if (payload.fields.blink) {
	            setTimeout(function () {
	              store.commit('update', {
	                id: payload.id,
	                chatId: payload.chatId,
	                fields: {
	                  blink: false
	                }
	              });
	            }, 1000);
	          }
	          return true;
	        },
	        "delete": function _delete(store, payload) {
	          if (!(payload.id instanceof Array)) {
	            payload.id = [payload.id];
	          }
	          payload.id = payload.id.map(function (id) {
	            if (/^\d+$/.test(id)) {
	              id = parseInt(id);
	            }
	            return id;
	          });
	          store.commit('delete', {
	            chatId: payload.chatId,
	            elements: payload.id
	          });
	          return true;
	        },
	        clear: function clear(store, payload) {
	          payload.chatId = parseInt(payload.chatId);
	          if (payload.keepPlaceholders) {
	            store.commit('clearMessages', {
	              chatId: payload.chatId
	            });
	          } else {
	            store.commit('clear', {
	              chatId: payload.chatId
	            });
	          }
	          return true;
	        },
	        applyMutationType: function applyMutationType(store, payload) {
	          payload.chatId = parseInt(payload.chatId);
	          store.commit('applyMutationType', {
	            chatId: payload.chatId
	          });
	          return true;
	        },
	        readMessages: function readMessages(store, payload) {
	          payload.readId = parseInt(payload.readId) || 0;
	          payload.chatId = parseInt(payload.chatId);
	          if (typeof store.state.collection[payload.chatId] === 'undefined') {
	            return {
	              count: 0
	            };
	          }
	          var count = 0;
	          for (var index = store.state.collection[payload.chatId].length - 1; index >= 0; index--) {
	            var element = store.state.collection[payload.chatId][index];
	            if (!element.unread) continue;
	            if (payload.readId === 0 || element.id <= payload.readId) {
	              count++;
	            }
	          }
	          store.commit('readMessages', {
	            chatId: payload.chatId,
	            readId: payload.readId
	          });
	          return {
	            count: count
	          };
	        },
	        unreadMessages: function unreadMessages(store, payload) {
	          payload.unreadId = parseInt(payload.unreadId) || 0;
	          payload.chatId = parseInt(payload.chatId);
	          if (typeof store.state.collection[payload.chatId] === 'undefined' || !payload.unreadId) {
	            return {
	              count: 0
	            };
	          }
	          var count = 0;
	          for (var index = store.state.collection[payload.chatId].length - 1; index >= 0; index--) {
	            var element = store.state.collection[payload.chatId][index];
	            if (element.unread) continue;
	            if (element.id >= payload.unreadId) {
	              count++;
	            }
	          }
	          store.commit('unreadMessages', {
	            chatId: payload.chatId,
	            unreadId: payload.unreadId
	          });
	          return {
	            count: count
	          };
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;
	      return {
	        initCollection: function initCollection(state, payload) {
	          return _this3.initCollection(state, payload);
	        },
	        add: function add(state, payload) {
	          _this3.initCollection(state, {
	            chatId: payload.chatId
	          });
	          state.collection[payload.chatId].push(payload);
	          state.saveMessageList[payload.chatId].push(payload.id);
	          state.created += 1;
	          state.collection[payload.chatId].sort(function (a, b) {
	            return a.id - b.id;
	          });
	          _this3.saveState(state, payload.chatId);
	          im_lib_logger.Logger.warn('Messages model: saving state after add');
	        },
	        clearPlaceholders: function clearPlaceholders(state, payload) {
	          if (!state.collection[payload.chatId]) {
	            return false;
	          }
	          state.collection[payload.chatId] = state.collection[payload.chatId].filter(function (element) {
	            return !element.id.toString().startsWith('placeholder');
	          });
	        },
	        updatePlaceholders: function updatePlaceholders(state, payload) {
	          var firstPlaceholderId = "placeholder".concat(payload.firstMessage);
	          var firstPlaceholderIndex = state.collection[payload.chatId].findIndex(function (message) {
	            return message.id === firstPlaceholderId;
	          });
	          // Logger.warn('firstPlaceholderIndex', firstPlaceholderIndex);
	          if (firstPlaceholderIndex >= 0) {
	            var _state$collection$pay;
	            // Logger.warn('before delete', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
	            state.collection[payload.chatId].splice(firstPlaceholderIndex, payload.amount);
	            // Logger.warn('after delete', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
	            (_state$collection$pay = state.collection[payload.chatId]).splice.apply(_state$collection$pay, [firstPlaceholderIndex, 0].concat(babelHelpers.toConsumableArray(payload.data)));
	            // Logger.warn('after add', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
	          }

	          state.collection[payload.chatId].sort(function (a, b) {
	            return a.id - b.id;
	          });
	          im_lib_logger.Logger.warn('Messages model: saving state after updating placeholders');
	          _this3.saveState(state, payload.chatId);
	        },
	        set: function set(state, payload) {
	          im_lib_logger.Logger.warn('Messages model: set mutation', payload);
	          var chats = [];
	          var chatsSave = [];
	          var isPush = false;
	          payload.data = MessagesModel.getPayloadWithTempMessages(state, payload);
	          var initialType = payload.insertType;
	          if (payload.insertType === im_const.MutationType.set) {
	            payload.insertType = im_const.MutationType.setAfter;
	            var elements = {};
	            payload.data.forEach(function (element) {
	              if (!elements[element.chatId]) {
	                elements[element.chatId] = [];
	              }
	              elements[element.chatId].push(element.id);
	            });
	            var _loop = function _loop(chatId) {
	              if (!elements.hasOwnProperty(chatId)) return "continue";
	              _this3.initCollection(state, {
	                chatId: chatId
	              });
	              im_lib_logger.Logger.warn('Messages model: messages before adding from request - ', state.collection[chatId].length);
	              if (state.saveMessageList[chatId].length > elements[chatId].length || elements[chatId].length < im_const.StorageLimit.messages) {
	                state.collection[chatId] = state.collection[chatId].filter(function (element) {
	                  return elements[chatId].includes(element.id);
	                });
	                state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(function (id) {
	                  return elements[chatId].includes(id);
	                });
	              }
	              im_lib_logger.Logger.warn('Messages model: cache length', state.saveMessageList[chatId].length);
	              var intersection = _this3.manageCacheBeforeSet(babelHelpers.toConsumableArray(state.saveMessageList[chatId].reverse()), elements[chatId]);
	              im_lib_logger.Logger.warn('Messages model: set intersection with cache', intersection);
	              if (intersection.type === IntersectionType.none) {
	                if (intersection.foundElements.length > 0) {
	                  state.collection[chatId] = state.collection[chatId].filter(function (element) {
	                    return !intersection.foundElements.includes(element.id);
	                  });
	                  state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(function (id) {
	                    return !intersection.foundElements.includes(id);
	                  });
	                }
	                im_lib_logger.Logger.warn('Messages model: no intersection - removing cache');
	                _this3.removeIntersectionCacheElements = state.collection[chatId].map(function (element) {
	                  return element.id;
	                });
	                state.collection[chatId] = state.collection[chatId].filter(function (element) {
	                  return !_this3.removeIntersectionCacheElements.includes(element.id);
	                });
	                state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(function (id) {
	                  return !_this3.removeIntersectionCacheElements.includes(id);
	                });
	                _this3.removeIntersectionCacheElements = [];
	              } else if (intersection.type === IntersectionType.foundReverse) {
	                im_lib_logger.Logger.warn('Messages model: found reverse intersection');
	                payload.insertType = im_const.MutationType.setBefore;
	                payload.data = payload.data.reverse();
	              }
	            };
	            for (var chatId in elements) {
	              var _ret = _loop(chatId);
	              if (_ret === "continue") continue;
	            }
	          }
	          im_lib_logger.Logger.warn('Messages model: adding messages to model', payload.data);
	          var _iterator = _createForOfIteratorHelper(payload.data),
	            _step;
	          try {
	            var _loop2 = function _loop2() {
	              var element = _step.value;
	              _this3.initCollection(state, {
	                chatId: element.chatId
	              });
	              var index = state.collection[element.chatId].findIndex(function (localMessage) {
	                if (MessagesModel.isTemporaryMessage(localMessage)) {
	                  return localMessage.templateId === element.templateId;
	                }
	                return localMessage.id === element.id;
	              });
	              if (index > -1) {
	                state.collection[element.chatId][index] = Object.assign(state.collection[element.chatId][index], element);
	              } else if (payload.insertType === im_const.MutationType.setBefore) {
	                state.collection[element.chatId].unshift(element);
	              } else if (payload.insertType === im_const.MutationType.setAfter) {
	                state.collection[element.chatId].push(element);
	              }
	              chats.push(element.chatId);
	              if (_this3.store.getters['dialogues/canSaveChat'] && _this3.store.getters['dialogues/canSaveChat'](element.chatId)) {
	                chatsSave.push(element.chatId);
	              }
	            };
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              _loop2();
	            }
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	          chats = babelHelpers.toConsumableArray(new Set(chats));
	          chatsSave = babelHelpers.toConsumableArray(new Set(chatsSave));
	          isPush = payload.data.every(function (element) {
	            return element.push === true;
	          });
	          im_lib_logger.Logger.warn('Is it fake push message?', isPush);
	          chats.forEach(function (chatId) {
	            state.collection[chatId].sort(function (a, b) {
	              return a.id - b.id;
	            });
	            if (!isPush) {
	              //send event that messages are ready and we can start reading etc
	              im_lib_logger.Logger.warn('setting messagesSet = true for chatId = ', chatId);
	              setTimeout(function () {
	                main_core_events.EventEmitter.emit(im_const.EventType.dialog.messagesSet, {
	                  chatId: chatId
	                });
	                main_core_events.EventEmitter.emit(im_const.EventType.dialog.readVisibleMessages, {
	                  chatId: chatId
	                });
	              }, 100);
	            }
	          });
	          if (initialType !== im_const.MutationType.setBefore) {
	            chatsSave.forEach(function (chatId) {
	              im_lib_logger.Logger.warn('Messages model: saving state after set');
	              _this3.saveState(state, chatId);
	            });
	          }
	        },
	        update: function update(state, payload) {
	          _this3.initCollection(state, {
	            chatId: payload.chatId
	          });
	          var index = -1;
	          if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index]) {
	            index = payload.index;
	          } else {
	            index = state.collection[payload.chatId].findIndex(function (el) {
	              return el.id === payload.id;
	            });
	          }
	          if (index >= 0) {
	            var isSaveState = state.saveMessageList[payload.chatId].includes(state.collection[payload.chatId][index].id) || payload.fields.id && !payload.fields.id.toString().startsWith('temporary') && state.collection[payload.chatId][index].id.toString().startsWith('temporary');
	            state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], payload.fields);
	            if (isSaveState) {
	              im_lib_logger.Logger.warn('Messages model: saving state after update');
	              _this3.saveState(state, payload.chatId);
	            }
	          }
	        },
	        "delete": function _delete(state, payload) {
	          _this3.initCollection(state, {
	            chatId: payload.chatId
	          });
	          state.collection[payload.chatId] = state.collection[payload.chatId].filter(function (element) {
	            return !payload.elements.includes(element.id);
	          });
	          if (state.saveMessageList[payload.chatId].length > 0) {
	            var _iterator2 = _createForOfIteratorHelper(payload.elements),
	              _step2;
	            try {
	              for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	                var id = _step2.value;
	                if (state.saveMessageList[payload.chatId].includes(id)) {
	                  im_lib_logger.Logger.warn('Messages model: saving state after delete');
	                  _this3.saveState(state, payload.chatId);
	                  break;
	                }
	              }
	            } catch (err) {
	              _iterator2.e(err);
	            } finally {
	              _iterator2.f();
	            }
	          }
	        },
	        clear: function clear(state, payload) {
	          _this3.initCollection(state, {
	            chatId: payload.chatId
	          });
	          state.collection[payload.chatId] = [];
	          state.saveMessageList[payload.chatId] = [];
	        },
	        clearMessages: function clearMessages(state, payload) {
	          _this3.initCollection(state, {
	            chatId: payload.chatId
	          });
	          state.collection[payload.chatId] = state.collection[payload.chatId].filter(function (element) {
	            return element.id.toString().startsWith('placeholder');
	          });
	          state.saveMessageList[payload.chatId] = [];
	        },
	        applyMutationType: function applyMutationType(state, payload) {
	          if (typeof state.mutationType[payload.chatId] === 'undefined') {
	            ui_vue.Vue.set(state.mutationType, payload.chatId, {
	              applied: false,
	              initialType: im_const.MutationType.none,
	              appliedType: im_const.MutationType.none,
	              scrollStickToTop: 0,
	              scrollMessageId: 0
	            });
	          }
	          state.mutationType[payload.chatId].applied = true;
	        },
	        readMessages: function readMessages(state, payload) {
	          _this3.initCollection(state, {
	            chatId: payload.chatId
	          });
	          var saveNeeded = false;
	          for (var index = state.collection[payload.chatId].length - 1; index >= 0; index--) {
	            var element = state.collection[payload.chatId][index];
	            if (!element.unread) continue;
	            if (payload.readId === 0 || element.id <= payload.readId) {
	              state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], {
	                unread: false
	              });
	              saveNeeded = true;
	            }
	          }
	          if (saveNeeded) {
	            im_lib_logger.Logger.warn('Messages model: saving state after reading');
	            _this3.saveState(state, payload.chatId);
	          }
	        },
	        unreadMessages: function unreadMessages(state, payload) {
	          _this3.initCollection(state, {
	            chatId: payload.chatId
	          });
	          var saveNeeded = false;
	          for (var index = state.collection[payload.chatId].length - 1; index >= 0; index--) {
	            var element = state.collection[payload.chatId][index];
	            if (element.unread) continue;
	            if (element.id >= payload.unreadId) {
	              state.collection[payload.chatId][index] = Object.assign(state.collection[payload.chatId][index], {
	                unread: true
	              });
	              saveNeeded = true;
	            }
	          }
	          if (saveNeeded) {
	            im_lib_logger.Logger.warn('Messages model: saving state after unreading');
	            _this3.saveState(state, payload.chatId);
	            _this3.updateSubordinateStates();
	          }
	        }
	      };
	    }
	  }, {
	    key: "initCollection",
	    value: function initCollection(state, payload) {
	      if (typeof payload.chatId === 'undefined') {
	        return false;
	      }
	      if (typeof payload.chatId === 'undefined' || typeof state.collection[payload.chatId] !== 'undefined') {
	        return true;
	      }
	      ui_vue.Vue.set(state.collection, payload.chatId, payload.messages ? [].concat(payload.messages) : []);
	      ui_vue.Vue.set(state.saveMessageList, payload.chatId, []);
	      ui_vue.Vue.set(state.saveFileList, payload.chatId, []);
	      ui_vue.Vue.set(state.saveUserList, payload.chatId, []);
	      return true;
	    }
	  }, {
	    key: "prepareMessage",
	    value: function prepareMessage(message) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = this.validate(Object.assign({}, message), options);
	      result.params = Object.assign({}, this.getElementState().params, result.params);
	      if (!result.templateId) {
	        result.templateId = result.id;
	      }
	      return Object.assign({}, this.getElementState(), result);
	    }
	  }, {
	    key: "manageCacheBeforeSet",
	    value: function manageCacheBeforeSet(cache, elements) {
	      var recursive = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      im_lib_logger.Logger.warn('manageCacheBeforeSet', cache, elements);
	      var result = {
	        type: IntersectionType.empty,
	        foundElements: [],
	        noneElements: []
	      };
	      if (!cache || cache.length <= 0) {
	        return result;
	      }
	      var _iterator3 = _createForOfIteratorHelper(elements),
	        _step3;
	      try {
	        for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	          var id = _step3.value;
	          if (cache.includes(id)) {
	            if (result.type === IntersectionType.empty) {
	              result.type = IntersectionType.found;
	            }
	            result.foundElements.push(id);
	          } else {
	            if (result.type === IntersectionType.empty) {
	              result.type = IntersectionType.none;
	            }
	            result.noneElements.push(id);
	          }
	        }
	      } catch (err) {
	        _iterator3.e(err);
	      } finally {
	        _iterator3.f();
	      }
	      if (result.type === IntersectionType.found && cache.length === elements.length && result.foundElements.length === elements.length) {
	        result.type = IntersectionType.equal;
	      } else if (result.type === IntersectionType.none && !recursive && result.foundElements.length > 0) {
	        var reverseResult = this.manageCacheBeforeSet(cache.reverse(), elements.reverse(), true);
	        if (reverseResult.type === IntersectionType.found) {
	          reverseResult.type = IntersectionType.foundReverse;
	          return reverseResult;
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "updateSaveLists",
	    value: function updateSaveLists(state, chatId) {
	      if (!this.isSaveAvailable()) {
	        return true;
	      }
	      if (!chatId || !this.store.getters['dialogues/canSaveChat'] || !this.store.getters['dialogues/canSaveChat'](chatId)) {
	        return false;
	      }
	      this.initCollection(state, {
	        chatId: chatId
	      });
	      var count = 0;
	      var saveMessageList = [];
	      var saveFileList = [];
	      var saveUserList = [];
	      var dialog = this.store.getters['dialogues/getByChatId'](chatId);
	      if (dialog && dialog.type === 'private') {
	        saveUserList.push(parseInt(dialog.dialogId));
	      }
	      var readCounter = 0;
	      for (var index = state.collection[chatId].length - 1; index >= 0; index--) {
	        if (state.collection[chatId][index].id.toString().startsWith('temporary')) {
	          continue;
	        }
	        if (!state.collection[chatId][index].unread) {
	          readCounter++;
	        }
	        if (count >= im_const.StorageLimit.messages && readCounter >= 50) {
	          break;
	        }
	        saveMessageList.unshift(state.collection[chatId][index].id);
	        count++;
	      }
	      saveMessageList = saveMessageList.slice(0, im_const.StorageLimit.messages);
	      state.collection[chatId].filter(function (element) {
	        return saveMessageList.includes(element.id);
	      }).forEach(function (element) {
	        if (element.authorId > 0) {
	          saveUserList.push(element.authorId);
	        }
	        if (element.params.FILE_ID instanceof Array) {
	          saveFileList = element.params.FILE_ID.concat(saveFileList);
	        }
	      });
	      state.saveMessageList[chatId] = saveMessageList;
	      state.saveFileList[chatId] = babelHelpers.toConsumableArray(new Set(saveFileList));
	      state.saveUserList[chatId] = babelHelpers.toConsumableArray(new Set(saveUserList));
	      return true;
	    }
	  }, {
	    key: "getSaveTimeout",
	    value: function getSaveTimeout() {
	      return 150;
	    }
	  }, {
	    key: "saveState",
	    value: function saveState(state, chatId) {
	      if (!this.updateSaveLists(state, chatId)) {
	        return false;
	      }
	      babelHelpers.get(babelHelpers.getPrototypeOf(MessagesModel.prototype), "saveState", this).call(this, function () {
	        var storedState = {
	          collection: {},
	          saveMessageList: {},
	          saveUserList: {},
	          saveFileList: {}
	        };
	        var _loop3 = function _loop3(chatId) {
	          if (!state.saveMessageList.hasOwnProperty(chatId)) {
	            return "continue";
	          }
	          if (!state.collection[chatId]) {
	            return "continue";
	          }
	          if (!storedState.collection[chatId]) {
	            storedState.collection[chatId] = [];
	          }
	          state.collection[chatId].filter(function (element) {
	            return state.saveMessageList[chatId].includes(element.id);
	          }).forEach(function (element) {
	            if (element.templateType !== 'placeholder') {
	              storedState.collection[chatId].push(element);
	            }
	          });
	          im_lib_logger.Logger.warn('Cache after updating', storedState.collection[chatId]);
	          storedState.saveMessageList[chatId] = state.saveMessageList[chatId];
	          storedState.saveFileList[chatId] = state.saveFileList[chatId];
	          storedState.saveUserList[chatId] = state.saveUserList[chatId];
	        };
	        for (var _chatId in state.saveMessageList) {
	          var _ret2 = _loop3(_chatId);
	          if (_ret2 === "continue") continue;
	        }
	        return storedState;
	      });
	    }
	  }, {
	    key: "updateSubordinateStates",
	    value: function updateSubordinateStates() {
	      this.store.dispatch('users/saveState');
	      this.store.dispatch('files/saveState');
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields, options) {
	      var result = {};
	      if (typeof fields.id === "number") {
	        result.id = fields.id;
	      } else if (typeof fields.id === "string") {
	        if (fields.id.startsWith('temporary') || fields.id.startsWith('placeholder') || im_lib_utils.Utils.types.isUuidV4(fields.id)) {
	          result.id = fields.id;
	        } else {
	          result.id = parseInt(fields.id);
	        }
	      }
	      if (typeof fields.uuid === "string") {
	        result.templateId = fields.uuid;
	      } else if (typeof fields.templateId === "number") {
	        result.templateId = fields.templateId;
	      } else if (typeof fields.templateId === "string") {
	        if (fields.templateId.startsWith('temporary') || im_lib_utils.Utils.types.isUuidV4(fields.templateId)) {
	          result.templateId = fields.templateId;
	        } else {
	          result.templateId = parseInt(fields.templateId);
	        }
	      }
	      if (typeof fields.templateType === "string") {
	        result.templateType = fields.templateType;
	      }
	      if (typeof fields.placeholderType === "number") {
	        result.placeholderType = fields.placeholderType;
	      }
	      if (typeof fields.chat_id !== 'undefined') {
	        fields.chatId = fields.chat_id;
	      }
	      if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	        result.chatId = parseInt(fields.chatId);
	      }
	      if (typeof fields.date !== "undefined") {
	        result.date = im_lib_utils.Utils.date.cast(fields.date);
	      }

	      // previous P&P format
	      if (typeof fields.textLegacy === "string" || typeof fields.textLegacy === "number") {
	        if (typeof fields.text === "string" || typeof fields.text === "number") {
	          result.text = fields.text.toString();
	        }
	        result.textConverted = this.convertToHtml({
	          text: fields.textLegacy.toString(),
	          isConverted: true
	        });
	        if (typeof fields.text === "string" || typeof fields.text === "number") {
	          result.text = fields.text;
	        }
	      } else
	        // modern format
	        {
	          if (typeof fields.text_converted !== 'undefined') {
	            fields.textConverted = fields.text_converted;
	          }
	          if (typeof fields.textConverted === "string" || typeof fields.textConverted === "number") {
	            result.textConverted = fields.textConverted.toString();
	          }
	          if (typeof fields.text === "string" || typeof fields.text === "number") {
	            result.text = fields.text.toString();
	            var isConverted = typeof result.textConverted !== 'undefined';
	            result.textConverted = this.convertToHtml({
	              text: isConverted ? result.textConverted : result.text,
	              isConverted: isConverted
	            });
	          }
	        }
	      if (typeof fields.senderId !== 'undefined') {
	        fields.authorId = fields.senderId;
	      } else if (typeof fields.author_id !== 'undefined') {
	        fields.authorId = fields.author_id;
	      }
	      if (typeof fields.authorId === "number" || typeof fields.authorId === "string") {
	        if (fields.system === true || fields.system === 'Y') {
	          result.authorId = 0;
	        } else {
	          result.authorId = parseInt(fields.authorId);
	        }
	      }
	      if (babelHelpers["typeof"](fields.params) === "object" && fields.params !== null) {
	        var params = this.validateParams(fields.params, options);
	        if (params) {
	          result.params = params;
	        }
	      }
	      if (typeof fields.push === "boolean") {
	        result.push = fields.push;
	      }
	      if (typeof fields.sending === "boolean") {
	        result.sending = fields.sending;
	      }
	      if (typeof fields.unread === "boolean") {
	        result.unread = fields.unread;
	      }
	      if (typeof fields.blink === "boolean") {
	        result.blink = fields.blink;
	      }
	      if (typeof fields.error === "boolean" || typeof fields.error === "string") {
	        result.error = fields.error;
	      }
	      if (typeof fields.retry === "boolean") {
	        result.retry = fields.retry;
	      }
	      return result;
	    }
	  }, {
	    key: "validateParams",
	    value: function validateParams(params, options) {
	      var result = {};
	      try {
	        for (var field in params) {
	          if (!params.hasOwnProperty(field)) {
	            continue;
	          }
	          if (field === 'COMPONENT_ID') {
	            if (typeof params[field] === "string" && BX.Vue.isComponent(params[field])) {
	              result[field] = params[field];
	            }
	          } else if (field === 'LIKE') {
	            if (params[field] instanceof Array) {
	              result['REACTION'] = {
	                like: params[field].map(function (element) {
	                  return parseInt(element);
	                })
	              };
	            }
	          } else if (field === 'CHAT_LAST_DATE') {
	            result[field] = im_lib_utils.Utils.date.cast(params[field]);
	          } else if (field === 'AVATAR') {
	            if (params[field]) {
	              result[field] = params[field].startsWith('http') ? params[field] : options.host + params[field];
	            }
	          } else if (field === 'NAME') {
	            if (params[field]) {
	              result[field] = params[field];
	            }
	          } else if (field === 'LINK_ACTIVE') {
	            if (params[field]) {
	              result[field] = params[field].map(function (userId) {
	                return parseInt(userId);
	              });
	            }
	          } else if (field === 'ATTACH') {
	            result[field] = params[field];
	          } else {
	            result[field] = params[field];
	          }
	        }
	      } catch (e) {}
	      var hasResultElements = false;
	      for (var _field in result) {
	        if (!result.hasOwnProperty(_field)) {
	          continue;
	        }
	        hasResultElements = true;
	        break;
	      }
	      return hasResultElements ? result : null;
	    }
	  }, {
	    key: "convertToHtml",
	    value: function convertToHtml() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var _params$quote = params.quote,
	        quote = _params$quote === void 0 ? true : _params$quote,
	        _params$image = params.image,
	        image = _params$image === void 0 ? true : _params$image,
	        _params$text = params.text,
	        text = _params$text === void 0 ? '' : _params$text,
	        _params$isConverted = params.isConverted,
	        isConverted = _params$isConverted === void 0 ? false : _params$isConverted,
	        _params$enableBigSmil = params.enableBigSmile,
	        enableBigSmile = _params$enableBigSmil === void 0 ? true : _params$enableBigSmil;
	      text = text.trim();
	      if (!isConverted) {
	        text = text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	      }
	      if (text.startsWith('/me')) {
	        text = "<i>".concat(text.substr(4), "</i>");
	      } else if (text.startsWith('/loud')) {
	        text = "<b>".concat(text.substr(6), "</b>");
	      }
	      var quoteSign = "&gt;&gt;";
	      if (quote && text.indexOf(quoteSign) >= 0) {
	        var textPrepare = text.split(isConverted ? "<br />" : "\n");
	        for (var i = 0; i < textPrepare.length; i++) {
	          if (textPrepare[i].startsWith(quoteSign)) {
	            textPrepare[i] = textPrepare[i].replace(quoteSign, '<div class="bx-im-message-content-quote"><div class="bx-im-message-content-quote-wrap">');
	            while (++i < textPrepare.length && textPrepare[i].startsWith(quoteSign)) {
	              textPrepare[i] = textPrepare[i].replace(quoteSign, '');
	            }
	            textPrepare[i - 1] += '</div></div><br>';
	          }
	        }
	        text = textPrepare.join("<br />");
	      }
	      text = text.replace(/\n/gi, '<br />');
	      text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

	      //text = this.decodeBbCode(text, false, enableBigSmile);
	      text = im_lib_utils.Utils.text.decodeBbCode(text, enableBigSmile);
	      if (quote) {
	        text = text.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\](?: #(?:(?:chat)?\d+|\d+:\d+)\/\d+)?<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, p4, offset) {
	          return (offset > 0 ? '<br>' : '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\"><div class=\"bx-im-message-content-quote-name\"><span class=\"bx-im-message-content-quote-name-text\">" + p1 + "</span><span class=\"bx-im-message-content-quote-name-time\">" + p2 + "</span></div>" + p3 + "</div></div><br />";
	        });
	        text = text.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, offset) {
	          return (offset > 0 ? '<br>' : '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\">" + p1 + "</div></div><br />";
	        });
	      }
	      if (image) {
	        var changed = false;
	        text = text.replace(/<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>/gi, function (whole, aInner, text, offset) {
	          if (!text.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i) || text.indexOf("/docs/pub/") > 0 || text.indexOf("logout=yes") > 0) {
	            return whole;
	          } else {
	            changed = true;
	            return (offset > 0 ? '<br />' : '') + '<a' + aInner + ' target="_blank" class="bx-im-element-file-image"><img src="' + text + '" class="bx-im-element-file-image-source-text" onerror="BX.Messenger.Model.MessagesModel.hideErrorImage(this)"></a></span>';
	          }
	        });
	        if (changed) {
	          text = text.replace(/<\/span>(\n?)<br(\s\/?)>/gi, '</span>').replace(/<br(\s\/?)>(\n?)<br(\s\/?)>(\n?)<span/gi, '<br /><span');
	        }
	      }
	      if (enableBigSmile) {
	        text = text.replace(/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/, function doubleSmileSize(match, start, width, middle, height, end) {
	          return start + parseInt(width, 10) * 1.7 + middle + parseInt(height, 10) * 1.7 + end;
	        });
	      }
	      if (text.substr(-6) == '<br />') {
	        text = text.substr(0, text.length - 6);
	      }
	      text = text.replace(/<br><br \/>/gi, '<br />');
	      text = text.replace(/<br \/><br>/gi, '<br />');
	      return text;
	    }
	  }, {
	    key: "decodeBbCode",
	    value: function decodeBbCode(text) {
	      var textOnly = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var enableBigSmile = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      return MessagesModel.decodeBbCode({
	        text: text,
	        textOnly: textOnly,
	        enableBigSmile: enableBigSmile
	      });
	    }
	  }, {
	    key: "decodeAttach",
	    value: function decodeAttach(item) {
	      var _this4 = this;
	      if (Array.isArray(item)) {
	        item.forEach(function (arrayElement) {
	          arrayElement = _this4.decodeAttach(arrayElement);
	        });
	      } else if (babelHelpers["typeof"](item) === 'object' && item !== null) {
	        for (var prop in item) {
	          if (item.hasOwnProperty(prop)) {
	            item[prop] = this.decodeAttach(item[prop]);
	          }
	        }
	      } else {
	        if (typeof item === 'string') {
	          item = im_lib_utils.Utils.text.htmlspecialcharsback(item);
	        }
	      }
	      return item;
	    }
	  }], [{
	    key: "decodeBbCode",
	    value: function decodeBbCode() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var text = params.text,
	        _params$textOnly = params.textOnly,
	        textOnly = _params$textOnly === void 0 ? false : _params$textOnly,
	        _params$enableBigSmil2 = params.enableBigSmile,
	        enableBigSmile = _params$enableBigSmil2 === void 0 ? true : _params$enableBigSmil2;
	      var putReplacement = [];
	      text = text.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/gi, function (whole) {
	        var id = putReplacement.length;
	        putReplacement.push(whole);
	        return '####REPLACEMENT_PUT_' + id + '####';
	      });
	      var sendReplacement = [];
	      text = text.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/gi, function (whole) {
	        var id = sendReplacement.length;
	        sendReplacement.push(whole);
	        return '####REPLACEMENT_SEND_' + id + '####';
	      });
	      var codeReplacement = [];
	      text = text.replace(/\[CODE\]\n?([\s\S]*?)\[\/CODE\]/ig, function (whole, text) {
	        var id = codeReplacement.length;
	        codeReplacement.push(text);
	        return '####REPLACEMENT_CODE_' + id + '####';
	      });
	      text = text.replace(/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/gi, function (whole, link, text) {
	        var tag = document.createElement('a');
	        tag.href = im_lib_utils.Utils.text.htmlspecialcharsback(link);
	        tag.target = '_blank';
	        tag.text = im_lib_utils.Utils.text.htmlspecialcharsback(text);
	        var allowList = ["http:", "https:", "ftp:", "file:", "tel:", "callto:", "mailto:", "skype:", "viber:"];
	        if (allowList.indexOf(tag.protocol) <= -1) {
	          return whole;
	        }
	        return tag.outerHTML;
	      });
	      text = text.replace(/\[url\]([^\]]+)\[\/url\]/gi, function (whole, link) {
	        link = im_lib_utils.Utils.text.htmlspecialcharsback(link);
	        var tag = document.createElement('a');
	        tag.href = link;
	        tag.target = '_blank';
	        tag.text = link;
	        var allowList = ["http:", "https:", "ftp:", "file:", "tel:", "callto:", "mailto:", "skype:", "viber:"];
	        if (allowList.indexOf(tag.protocol) <= -1) {
	          return whole;
	        }
	        return tag.outerHTML;
	      });
	      text = text.replace(/\[LIKE\]/gi, '<span class="bx-smile bx-im-smile-like"></span>');
	      text = text.replace(/\[DISLIKE\]/gi, '<span class="bx-smile bx-im-smile-dislike"></span>');
	      text = text.replace(/\[BR\]/gi, '<br/>');
	      text = text.replace(/\[([buis])\](.*?)\[(\/[buis])\]/gi, function (whole, open, inner, close) {
	        return '<' + open + '>' + inner + '<' + close + '>';
	      }); // TODO tag USER

	      // this code needs to be ported to im/install/js/im/view/message/body/src/body.js:229
	      text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/gi, function (whole, openlines, chatId, inner) {
	        return openlines ? inner : '<span class="bx-im-mention" data-type="CHAT" data-value="chat' + chatId + '">' + inner + '</span>';
	      }); // TODO tag CHAT
	      text = text.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/gi, function (whole, number, text) {
	        return '<span class="bx-im-mention" data-type="CALL" data-value="' + im_lib_utils.Utils.text.htmlspecialchars(number) + '">' + text + '</span>';
	      }); // TODO tag CHAT

	      text = text.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/gi, function (whole, historyId, text) {
	        return text;
	      }); // TODO tag PCH

	      var textElementSize = 0;
	      if (enableBigSmile) {
	        textElementSize = text.replace(/\[icon\=([^\]]*)\]/gi, '').trim().length;
	      }
	      text = text.replace(/\[icon\=([^\]]*)\]/gi, function (whole) {
	        var url = whole.match(/icon\=(\S+[^\s.,> )\];\'\"!?])/i);
	        if (url && url[1]) {
	          url = url[1];
	        } else {
	          return '';
	        }
	        var attrs = {
	          'src': url,
	          'border': 0
	        };
	        var size = whole.match(/size\=(\d+)/i);
	        if (size && size[1]) {
	          attrs['width'] = size[1];
	          attrs['height'] = size[1];
	        } else {
	          var width = whole.match(/width\=(\d+)/i);
	          if (width && width[1]) {
	            attrs['width'] = width[1];
	          }
	          var height = whole.match(/height\=(\d+)/i);
	          if (height && height[1]) {
	            attrs['height'] = height[1];
	          }
	          if (attrs['width'] && !attrs['height']) {
	            attrs['height'] = attrs['width'];
	          } else if (attrs['height'] && !attrs['width']) {
	            attrs['width'] = attrs['height'];
	          } else if (attrs['height'] && attrs['width']) ; else {
	            attrs['width'] = 20;
	            attrs['height'] = 20;
	          }
	        }
	        attrs['width'] = attrs['width'] > 100 ? 100 : attrs['width'];
	        attrs['height'] = attrs['height'] > 100 ? 100 : attrs['height'];
	        if (enableBigSmile && textElementSize === 0 && attrs['width'] === attrs['height'] && attrs['width'] === 20) {
	          attrs['width'] = 40;
	          attrs['height'] = 40;
	        }
	        var title = whole.match(/title\=(.*[^\s\]])/i);
	        if (title && title[1]) {
	          title = title[1];
	          if (title.indexOf('width=') > -1) {
	            title = title.substr(0, title.indexOf('width='));
	          }
	          if (title.indexOf('height=') > -1) {
	            title = title.substr(0, title.indexOf('height='));
	          }
	          if (title.indexOf('size=') > -1) {
	            title = title.substr(0, title.indexOf('size='));
	          }
	          if (title) {
	            attrs['title'] = im_lib_utils.Utils.text.htmlspecialchars(title).trim();
	            attrs['alt'] = attrs['title'];
	          }
	        }
	        var attributes = '';
	        for (var name in attrs) {
	          if (attrs.hasOwnProperty(name)) {
	            attributes += name + '="' + attrs[name] + '" ';
	          }
	        }
	        return '<img class="bx-smile bx-icon" ' + attributes + '>';
	      });
	      sendReplacement.forEach(function (value, index) {
	        text = text.replace('####REPLACEMENT_SEND_' + index + '####', value);
	      });
	      text = text.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/gi, function (match) {
	        return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/gi, function (whole, command, text) {
	          var html = '';
	          text = text ? text : command;
	          command = (command ? command : text).replace('<br />', '\n');
	          if (!textOnly && text) {
	            text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
	            text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);
	            command = command.split('####REPLACEMENT_PUT_').join('####REPLACEMENT_SP_');
	            html = '<!--IM_COMMAND_START-->' + '<span class="bx-im-message-command-wrap">' + '<span class="bx-im-message-command" data-entity="send">' + text + '</span>' + '<span class="bx-im-message-command-data">' + command + '</span>' + '</span>' + '<!--IM_COMMAND_END-->';
	          } else {
	            html = text;
	          }
	          return html;
	        });
	      });
	      putReplacement.forEach(function (value, index) {
	        text = text.replace('####REPLACEMENT_PUT_' + index + '####', value);
	      });
	      text = text.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/gi, function (match) {
	        return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/gi, function (whole, command, text) {
	          var html = '';
	          text = text ? text : command;
	          command = (command ? command : text).replace('<br />', '\n');
	          if (!textOnly && text) {
	            text = text.replace(/<([\w]+)[^>]*>(.*?)<\/\1>/i, "$2", text);
	            text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);
	            html = '<!--IM_COMMAND_START-->' + '<span class="bx-im-message-command-wrap">' + '<span class="bx-im-message-command" data-entity="put">' + text + '</span>' + '<span class="bx-im-message-command-data">' + command + '</span>' + '</span>' + '<!--IM_COMMAND_END-->';
	          } else {
	            html = text;
	          }
	          return html;
	        });
	      });
	      codeReplacement.forEach(function (code, index) {
	        text = text.replace('####REPLACEMENT_CODE_' + index + '####', !textOnly ? '<div class="bx-im-message-content-code">' + code + '</div>' : code);
	      });
	      if (sendReplacement.length > 0) {
	        do {
	          sendReplacement.forEach(function (value, index) {
	            text = text.replace('####REPLACEMENT_SEND_' + index + '####', value);
	          });
	        } while (text.includes('####REPLACEMENT_SEND_'));
	      }
	      text = text.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');
	      if (putReplacement.length > 0) {
	        do {
	          putReplacement.forEach(function (value, index) {
	            text = text.replace('####REPLACEMENT_PUT_' + index + '####', value);
	          });
	        } while (text.includes('####REPLACEMENT_PUT_'));
	      }
	      return text;
	    }
	  }, {
	    key: "hideErrorImage",
	    value: function hideErrorImage(element) {
	      if (element.parentNode && element.parentNode) {
	        element.parentNode.innerHTML = '<a href="' + element.src + '" target="_blank">' + element.src + '</a>';
	      }
	      return true;
	    }
	  }, {
	    key: "isTemporaryMessage",
	    value: function isTemporaryMessage(element) {
	      return element.id && (im_lib_utils.Utils.types.isUuidV4(element.id) || element.id.toString().startsWith('temporary'));
	    }
	  }, {
	    key: "getPayloadWithTempMessages",
	    value: function getPayloadWithTempMessages(state, payload) {
	      var payloadData = babelHelpers.toConsumableArray(payload.data);
	      if (!im_lib_utils.Utils.platform.isBitrixMobile()) {
	        return payloadData;
	      }
	      if (!payload.data || payload.data.length <= 0) {
	        return payloadData;
	      }

	      // consider that in the payload we have messages only for one chat, so we get the value from the first message.
	      var payloadChatId = payload.data[0].chatId;
	      if (!state.collection[payloadChatId]) {
	        return payloadData;
	      }
	      state.collection[payloadChatId].forEach(function (message) {
	        if (MessagesModel.isTemporaryMessage(message) && !MessagesModel.existsInPayload(payload, message.templateId) && MessagesModel.doesTaskExist(message)) {
	          payloadData.push(message);
	        }
	      });
	      return payloadData;
	    }
	  }, {
	    key: "existsInPayload",
	    value: function existsInPayload(payload, templateId) {
	      return payload.data.find(function (payloadMessage) {
	        return payloadMessage.templateId === templateId;
	      });
	    }
	  }, {
	    key: "doesTaskExist",
	    value: function doesTaskExist(message) {
	      if (Array.isArray(message.params.FILE_ID)) {
	        var foundUploadTasks = false;
	        message.params.FILE_ID.forEach(function (fileId) {
	          if (!foundUploadTasks) {
	            foundUploadTasks = window.imDialogUploadTasks.find(function (task) {
	              return task.taskId.split('|')[1] === fileId;
	            });
	          }
	        });
	        return !!foundUploadTasks;
	      }
	      if (message.templateId) {
	        var foundMessageTask = window.imDialogMessagesTasks.find(function (task) {
	          return task.taskId.split('|')[1] === message.templateId;
	        });
	        return !!foundMessageTask;
	      }
	      return false;
	    }
	  }]);
	  return MessagesModel;
	}(ui_vue_vuex.VuexBuilderModel);

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }
	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var DialoguesModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(DialoguesModel, _VuexBuilderModel);
	  function DialoguesModel() {
	    babelHelpers.classCallCheck(this, DialoguesModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DialoguesModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(DialoguesModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'dialogues';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        collection: {},
	        saveDialogList: [],
	        saveChatList: []
	      };
	    }
	  }, {
	    key: "getStateSaveException",
	    value: function getStateSaveException() {
	      return {
	        host: null
	      };
	    }
	  }, {
	    key: "getElementStateSaveException",
	    value: function getElementStateSaveException() {
	      return {
	        writingList: null,
	        quoteId: null
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        dialogId: '0',
	        chatId: 0,
	        counter: 0,
	        userCounter: 0,
	        messageCount: 0,
	        unreadId: 0,
	        lastMessageId: 0,
	        managerList: [],
	        readedList: [],
	        writingList: [],
	        muteList: [],
	        textareaMessage: "",
	        quoteId: 0,
	        editId: 0,
	        init: false,
	        name: "",
	        owner: 0,
	        extranet: false,
	        avatar: "",
	        color: "#17A3EA",
	        type: "chat",
	        entityType: "",
	        entityId: "",
	        entityData1: "",
	        entityData2: "",
	        entityData3: "",
	        dateCreate: new Date(),
	        restrictions: {
	          avatar: true,
	          extend: true,
	          leave: true,
	          leaveOwner: true,
	          rename: true,
	          send: true,
	          userList: true,
	          mute: true,
	          call: true
	        },
	        "public": {
	          code: '',
	          link: ''
	        }
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;
	      return {
	        get: function get(state) {
	          return function (dialogId) {
	            if (!state.collection[dialogId]) {
	              return null;
	            }
	            return state.collection[dialogId];
	          };
	        },
	        getByChatId: function getByChatId(state) {
	          return function (chatId) {
	            chatId = parseInt(chatId);
	            for (var dialogId in state.collection) {
	              if (!state.collection.hasOwnProperty(dialogId)) {
	                continue;
	              }
	              if (state.collection[dialogId].chatId === chatId) {
	                return state.collection[dialogId];
	              }
	            }
	            return null;
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState();
	          };
	        },
	        getQuoteId: function getQuoteId(state) {
	          return function (dialogId) {
	            if (!state.collection[dialogId]) {
	              return 0;
	            }
	            return state.collection[dialogId].quoteId;
	          };
	        },
	        getEditId: function getEditId(state) {
	          return function (dialogId) {
	            if (!state.collection[dialogId]) {
	              return 0;
	            }
	            return state.collection[dialogId].editId;
	          };
	        },
	        canSaveChat: function canSaveChat(state) {
	          return function (chatId) {
	            if (/^\d+$/.test(chatId)) {
	              chatId = parseInt(chatId);
	            }
	            return state.saveChatList.includes(parseInt(chatId));
	          };
	        },
	        canSaveDialog: function canSaveDialog(state) {
	          return function (dialogId) {
	            return state.saveDialogList.includes(dialogId.toString());
	          };
	        },
	        isPrivateDialog: function isPrivateDialog(state) {
	          return function (dialogId) {
	            dialogId = dialogId.toString();
	            return state.collection[dialogId.toString()] && state.collection[dialogId].type === 'private';
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;
	      return {
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (dialog) {
	              return Object.assign({}, _this2.validate(Object.assign({}, dialog), {
	                host: store.state.host
	              }), {
	                init: true
	              });
	            });
	          } else {
	            var result = [];
	            result.push(Object.assign({}, _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            }), {
	              init: true
	            }));
	            payload = result;
	          }
	          store.commit('set', payload);
	        },
	        update: function update(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }
	          store.commit('update', {
	            dialogId: payload.dialogId,
	            fields: _this2.validate(Object.assign({}, payload.fields), {
	              host: store.state.host
	            })
	          });
	          return true;
	        },
	        "delete": function _delete(store, payload) {
	          store.commit('delete', payload.dialogId);
	          return true;
	        },
	        updateWriting: function updateWriting(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }
	          var index = store.state.collection[payload.dialogId].writingList.findIndex(function (el) {
	            return el.userId === payload.userId;
	          });
	          if (payload.action) {
	            if (index >= 0) {
	              return true;
	            } else {
	              var writingList = [].concat(store.state.collection[payload.dialogId].writingList);
	              writingList.unshift({
	                userId: payload.userId,
	                userName: payload.userName
	              });
	              store.commit('update', {
	                actionName: 'updateWriting/1',
	                dialogId: payload.dialogId,
	                fields: _this2.validate({
	                  writingList: writingList
	                }, {
	                  host: store.state.host
	                })
	              });
	            }
	          } else {
	            if (index >= 0) {
	              var _writingList = store.state.collection[payload.dialogId].writingList.filter(function (el) {
	                return el.userId !== payload.userId;
	              });
	              store.commit('update', {
	                actionName: 'updateWriting/2',
	                dialogId: payload.dialogId,
	                fields: _this2.validate({
	                  writingList: _writingList
	                }, {
	                  host: store.state.host
	                })
	              });
	              return true;
	            } else {
	              return true;
	            }
	          }
	          return false;
	        },
	        updateReaded: function updateReaded(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }
	          var readedList = store.state.collection[payload.dialogId].readedList.filter(function (el) {
	            return el.userId !== payload.userId;
	          });
	          if (payload.action) {
	            readedList.push({
	              userId: payload.userId,
	              userName: payload.userName || '',
	              messageId: payload.messageId,
	              date: payload.date || new Date()
	            });
	          }
	          store.commit('update', {
	            actionName: 'updateReaded',
	            dialogId: payload.dialogId,
	            fields: _this2.validate({
	              readedList: readedList
	            }, {
	              host: store.state.host
	            })
	          });
	          return false;
	        },
	        increaseCounter: function increaseCounter(store, payload) {
	          var _store$rootState$appl;
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }
	          var counter = store.state.collection[payload.dialogId].counter;
	          if (counter === 100) {
	            return true;
	          }
	          var increasedCounter = counter + payload.count;
	          if (increasedCounter > 100) {
	            increasedCounter = 100;
	          }
	          var userId = (_store$rootState$appl = store.rootState.application) === null || _store$rootState$appl === void 0 ? void 0 : _store$rootState$appl.common.userId;
	          var dialogMuted = userId && store.state.collection[payload.dialogId].muteList.includes(userId);
	          store.commit('update', {
	            actionName: 'increaseCounter',
	            dialogId: payload.dialogId,
	            dialogMuted: dialogMuted,
	            fields: {
	              counter: increasedCounter,
	              previousCounter: counter
	            }
	          });
	          return false;
	        },
	        decreaseCounter: function decreaseCounter(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }
	          var counter = store.state.collection[payload.dialogId].counter;
	          if (counter === 100) {
	            return true;
	          }
	          var decreasedCounter = counter - payload.count;
	          if (decreasedCounter < 0) {
	            decreasedCounter = 0;
	          }
	          var unreadId = payload.unreadId > store.state.collection[payload.dialogId].unreadId ? payload.unreadId : store.state.collection[payload.dialogId].unreadId;
	          if (store.state.collection[payload.dialogId].unreadId !== unreadId || store.state.collection[payload.dialogId].counter !== decreasedCounter) {
	            var _store$rootState$appl2;
	            var previousCounter = store.state.collection[payload.dialogId].counter;
	            if (decreasedCounter === 0) {
	              unreadId = 0;
	            }
	            var userId = (_store$rootState$appl2 = store.rootState.application) === null || _store$rootState$appl2 === void 0 ? void 0 : _store$rootState$appl2.common.userId;
	            var dialogMuted = userId && store.state.collection[payload.dialogId].muteList.includes(userId);
	            store.commit('update', {
	              actionName: 'decreaseCounter',
	              dialogId: payload.dialogId,
	              dialogMuted: dialogMuted,
	              fields: {
	                counter: decreasedCounter,
	                previousCounter: previousCounter,
	                unreadId: unreadId
	              }
	            });
	          }
	          return false;
	        },
	        increaseMessageCounter: function increaseMessageCounter(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }
	          var currentCounter = store.state.collection[payload.dialogId].messageCount;
	          store.commit('update', {
	            actionName: 'increaseMessageCount',
	            dialogId: payload.dialogId,
	            fields: {
	              messageCount: currentCounter + payload.count
	            }
	          });
	        },
	        saveDialog: function saveDialog(store, payload) {
	          if (typeof store.state.collection[payload.dialogId] === 'undefined' || store.state.collection[payload.dialogId].init === false) {
	            return true;
	          }
	          store.commit('saveDialog', {
	            dialogId: payload.dialogId,
	            chatId: payload.chatId
	          });
	          return false;
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;
	      return {
	        initCollection: function initCollection(state, payload) {
	          _this3.initCollection(state, payload);
	        },
	        saveDialog: function saveDialog(state, payload) {
	          // TODO if payload.dialogId is IMOL, skip update this flag
	          if (!(payload.chatId > 0 && payload.dialogId.length > 0)) {
	            return false;
	          }
	          var saveDialogList = state.saveDialogList.filter(function (element) {
	            return element !== payload.dialogId;
	          });
	          saveDialogList.unshift(payload.dialogId);
	          saveDialogList = saveDialogList.slice(0, im_const.StorageLimit.dialogues);
	          if (state.saveDialogList.join(',') === saveDialogList.join(',')) {
	            return true;
	          }
	          state.saveDialogList = saveDialogList;
	          var saveChatList = state.saveChatList.filter(function (element) {
	            return element !== payload.chatId;
	          });
	          saveChatList.unshift(payload.chatId);
	          state.saveChatList = saveChatList.slice(0, im_const.StorageLimit.dialogues);
	          _this3.saveState(state);
	        },
	        set: function set(state, payload) {
	          var _iterator = _createForOfIteratorHelper$1(payload),
	            _step;
	          try {
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              var element = _step.value;
	              _this3.initCollection(state, {
	                dialogId: element.dialogId
	              });
	              state.collection[element.dialogId] = Object.assign(_this3.getElementState(), state.collection[element.dialogId], element);
	            }

	            // TODO if payload.dialogId is IMOL, skip update cache
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	          _this3.saveState(state);
	        },
	        update: function update(state, payload) {
	          _this3.initCollection(state, payload);
	          state.collection[payload.dialogId] = Object.assign(state.collection[payload.dialogId], payload.fields);

	          // TODO if payload.dialogId is IMOL, skip update cache
	          _this3.saveState(state);
	        },
	        "delete": function _delete(state, payload) {
	          delete state.collection[payload.dialogId];

	          // TODO if payload.dialogId is IMOL, skip update cache
	          _this3.saveState(state);
	        }
	      };
	    }
	  }, {
	    key: "initCollection",
	    value: function initCollection(state, payload) {
	      if (typeof state.collection[payload.dialogId] !== 'undefined') {
	        return true;
	      }
	      ui_vue.Vue.set(state.collection, payload.dialogId, this.getElementState());
	      if (payload.fields) {
	        state.collection[payload.dialogId] = Object.assign(state.collection[payload.dialogId], this.validate(Object.assign({}, payload.fields), {
	          host: state.host
	        }));
	      }
	      return true;
	    }
	  }, {
	    key: "getSaveTimeout",
	    value: function getSaveTimeout() {
	      return 100;
	    }
	  }, {
	    key: "saveState",
	    value: function saveState() {
	      var _this4 = this;
	      var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      if (!this.isSaveAvailable()) {
	        return true;
	      }
	      babelHelpers.get(babelHelpers.getPrototypeOf(DialoguesModel.prototype), "saveState", this).call(this, function () {
	        var storedState = {
	          collection: {},
	          saveDialogList: [].concat(state.saveDialogList),
	          saveChatList: [].concat(state.saveChatList)
	        };
	        state.saveDialogList.forEach(function (dialogId) {
	          if (!state.collection[dialogId]) return false;
	          storedState.collection[dialogId] = Object.assign(_this4.getElementState(), _this4.cloneState(state.collection[dialogId], _this4.getElementStateSaveException()));
	        });
	        return storedState;
	      });
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = {};
	      options.host = options.host || this.getState().host;
	      if (typeof fields.dialog_id !== 'undefined') {
	        fields.dialogId = fields.dialog_id;
	      }
	      if (typeof fields.dialogId === "number" || typeof fields.dialogId === "string") {
	        result.dialogId = fields.dialogId.toString();
	      }
	      if (typeof fields.chat_id !== 'undefined') {
	        fields.chatId = fields.chat_id;
	      } else if (typeof fields.id !== 'undefined') {
	        fields.chatId = fields.id;
	      }
	      if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	        result.chatId = parseInt(fields.chatId);
	      }
	      if (typeof fields.quoteId === "number") {
	        result.quoteId = parseInt(fields.quoteId);
	      }
	      if (typeof fields.editId === "number") {
	        result.editId = parseInt(fields.editId);
	      }
	      if (typeof fields.counter === "number" || typeof fields.counter === "string") {
	        result.counter = parseInt(fields.counter);
	      }
	      if (typeof fields.user_counter === "number" || typeof fields.user_counter === "string") {
	        result.userCounter = parseInt(fields.user_counter);
	      }
	      if (typeof fields.userCounter === "number" || typeof fields.userCounter === "string") {
	        result.userCounter = parseInt(fields.userCounter);
	      }
	      if (typeof fields.message_count === "number" || typeof fields.message_count === "string") {
	        result.messageCount = parseInt(fields.message_count);
	      }
	      if (typeof fields.messageCount === "number" || typeof fields.messageCount === "string") {
	        result.messageCount = parseInt(fields.messageCount);
	      }
	      if (typeof fields.unread_id !== 'undefined') {
	        fields.unreadId = fields.unread_id;
	      }
	      if (typeof fields.unreadId === "number" || typeof fields.unreadId === "string") {
	        result.unreadId = parseInt(fields.unreadId);
	      }
	      if (typeof fields.last_message_id !== 'undefined') {
	        fields.lastMessageId = fields.last_message_id;
	      }
	      if (typeof fields.lastMessageId === "number" || typeof fields.lastMessageId === "string") {
	        result.lastMessageId = parseInt(fields.lastMessageId);
	      }
	      if (typeof fields.readed_list !== 'undefined') {
	        fields.readedList = fields.readed_list;
	      }
	      if (typeof fields.readedList !== 'undefined') {
	        result.readedList = [];
	        if (fields.readedList instanceof Array) {
	          fields.readedList.forEach(function (element) {
	            var record = {};
	            if (typeof element.user_id !== 'undefined') {
	              element.userId = element.user_id;
	            }
	            if (typeof element.user_name !== 'undefined') {
	              element.userName = element.user_name;
	            }
	            if (typeof element.message_id !== 'undefined') {
	              element.messageId = element.message_id;
	            }
	            if (!element.userId || !element.userName || !element.messageId) {
	              return false;
	            }
	            record.userId = parseInt(element.userId);
	            record.userName = element.userName.toString();
	            record.messageId = parseInt(element.messageId);
	            record.date = im_lib_utils.Utils.date.cast(element.date);
	            result.readedList.push(record);
	          });
	        }
	      }
	      if (typeof fields.writing_list !== 'undefined') {
	        fields.writingList = fields.writing_list;
	      }
	      if (typeof fields.writingList !== 'undefined') {
	        result.writingList = [];
	        if (fields.writingList instanceof Array) {
	          fields.writingList.forEach(function (element) {
	            var record = {};
	            if (!element.userId) {
	              return false;
	            }
	            record.userId = parseInt(element.userId);
	            record.userName = im_lib_utils.Utils.text.htmlspecialcharsback(element.userName);
	            result.writingList.push(record);
	          });
	        }
	      }
	      if (typeof fields.manager_list !== 'undefined') {
	        fields.managerList = fields.manager_list;
	      }
	      if (typeof fields.managerList !== 'undefined') {
	        result.managerList = [];
	        if (fields.managerList instanceof Array) {
	          fields.managerList.forEach(function (userId) {
	            userId = parseInt(userId);
	            if (userId > 0) {
	              result.managerList.push(userId);
	            }
	          });
	        }
	      }
	      if (typeof fields.mute_list !== 'undefined') {
	        fields.muteList = fields.mute_list;
	      }
	      if (typeof fields.muteList !== 'undefined') {
	        result.muteList = [];
	        if (fields.muteList instanceof Array) {
	          fields.muteList.forEach(function (userId) {
	            userId = parseInt(userId);
	            if (userId > 0) {
	              result.muteList.push(userId);
	            }
	          });
	        } else if (babelHelpers["typeof"](fields.muteList) === 'object') {
	          Object.entries(fields.muteList).forEach(function (entry) {
	            if (entry[1] === true) {
	              var userId = parseInt(entry[0]);
	              if (userId > 0) {
	                result.muteList.push(userId);
	              }
	            }
	          });
	        }
	      }
	      if (typeof fields.textareaMessage !== 'undefined') {
	        result.textareaMessage = fields.textareaMessage.toString();
	      }
	      if (typeof fields.title !== 'undefined') {
	        fields.name = fields.title;
	      }
	      if (typeof fields.name === "string" || typeof fields.name === "number") {
	        result.name = im_lib_utils.Utils.text.htmlspecialcharsback(fields.name.toString());
	      }
	      if (typeof fields.owner !== 'undefined') {
	        fields.ownerId = fields.owner;
	      }
	      if (typeof fields.ownerId === "number" || typeof fields.ownerId === "string") {
	        result.ownerId = parseInt(fields.ownerId);
	      }
	      if (typeof fields.extranet === "boolean") {
	        result.extranet = fields.extranet;
	      }
	      if (typeof fields.avatar === 'string') {
	        var avatar;
	        if (!fields.avatar || fields.avatar.endsWith('/js/im/images/blank.gif')) {
	          avatar = '';
	        } else if (fields.avatar.startsWith('http')) {
	          avatar = fields.avatar;
	        } else {
	          avatar = options.host + fields.avatar;
	        }
	        if (avatar) {
	          result.avatar = encodeURI(avatar);
	        }
	      }
	      if (typeof fields.color === "string") {
	        result.color = fields.color.toString();
	      }
	      if (typeof fields.type === "string") {
	        result.type = fields.type.toString();
	      }
	      if (typeof fields.entity_type !== 'undefined') {
	        fields.entityType = fields.entity_type;
	      }
	      if (typeof fields.entityType === "string") {
	        result.entityType = fields.entityType.toString();
	      }
	      if (typeof fields.entity_id !== 'undefined') {
	        fields.entityId = fields.entity_id;
	      }
	      if (typeof fields.entityId === "string" || typeof fields.entityId === "number") {
	        result.entityId = fields.entityId.toString();
	      }
	      if (typeof fields.entity_data_1 !== 'undefined') {
	        fields.entityData1 = fields.entity_data_1;
	      }
	      if (typeof fields.entityData1 === "string") {
	        result.entityData1 = fields.entityData1.toString();
	      }
	      if (typeof fields.entity_data_2 !== 'undefined') {
	        fields.entityData2 = fields.entity_data_2;
	      }
	      if (typeof fields.entityData2 === "string") {
	        result.entityData2 = fields.entityData2.toString();
	      }
	      if (typeof fields.entity_data_3 !== 'undefined') {
	        fields.entityData3 = fields.entity_data_3;
	      }
	      if (typeof fields.entityData3 === "string") {
	        result.entityData3 = fields.entityData3.toString();
	      }
	      if (typeof fields.date_create !== 'undefined') {
	        fields.dateCreate = fields.date_create;
	      }
	      if (typeof fields.dateCreate !== "undefined") {
	        result.dateCreate = im_lib_utils.Utils.date.cast(fields.dateCreate);
	      }
	      if (typeof fields.dateLastOpen !== "undefined") {
	        result.dateLastOpen = im_lib_utils.Utils.date.cast(fields.dateLastOpen);
	      }
	      if (babelHelpers["typeof"](fields.restrictions) === 'object' && fields.restrictions) {
	        result.restrictions = {};
	        if (typeof fields.restrictions.avatar === 'boolean') {
	          result.restrictions.avatar = fields.restrictions.avatar;
	        }
	        if (typeof fields.restrictions.extend === 'boolean') {
	          result.restrictions.extend = fields.restrictions.extend;
	        }
	        if (typeof fields.restrictions.leave === 'boolean') {
	          result.restrictions.leave = fields.restrictions.leave;
	        }
	        if (typeof fields.restrictions.leave_owner === 'boolean') {
	          result.restrictions.leaveOwner = fields.restrictions.leave_owner;
	        }
	        if (typeof fields.restrictions.rename === 'boolean') {
	          result.restrictions.rename = fields.restrictions.rename;
	        }
	        if (typeof fields.restrictions.send === 'boolean') {
	          result.restrictions.send = fields.restrictions.send;
	        }
	        if (typeof fields.restrictions.user_list === 'boolean') {
	          result.restrictions.userList = fields.restrictions.user_list;
	        }
	        if (typeof fields.restrictions.mute === 'boolean') {
	          result.restrictions.mute = fields.restrictions.mute;
	        }
	        if (typeof fields.restrictions.call === 'boolean') {
	          result.restrictions.call = fields.restrictions.call;
	        }
	      }
	      if (babelHelpers["typeof"](fields["public"]) === 'object' && fields["public"]) {
	        result["public"] = {};
	        if (typeof fields["public"].code === 'string') {
	          result["public"].code = fields["public"].code;
	        }
	        if (typeof fields["public"].link === 'string') {
	          result["public"].link = fields["public"].link;
	        }
	      }
	      return result;
	    }
	  }]);
	  return DialoguesModel;
	}(ui_vue_vuex.VuexBuilderModel);

	function _createForOfIteratorHelper$2(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$2(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$2(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$2(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$2(o, minLen); }
	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var UsersModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(UsersModel, _VuexBuilderModel);
	  function UsersModel() {
	    babelHelpers.classCallCheck(this, UsersModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UsersModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(UsersModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'users';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      this.startOnlineCheckInterval();
	      return {
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        collection: {},
	        onlineList: [],
	        mobileOnlineList: [],
	        absentList: []
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var _params$id = params.id,
	        id = _params$id === void 0 ? 0 : _params$id,
	        _params$name = params.name,
	        name = _params$name === void 0 ? this.getVariable('default.name', '') : _params$name,
	        _params$firstName = params.firstName,
	        firstName = _params$firstName === void 0 ? this.getVariable('default.name', '') : _params$firstName,
	        _params$lastName = params.lastName,
	        lastName = _params$lastName === void 0 ? '' : _params$lastName;
	      return {
	        id: id,
	        name: name,
	        firstName: firstName,
	        lastName: lastName,
	        workPosition: "",
	        color: "#048bd0",
	        avatar: "",
	        gender: "M",
	        birthday: false,
	        isBirthday: false,
	        extranet: false,
	        network: false,
	        bot: false,
	        connector: false,
	        externalAuthId: "default",
	        status: "online",
	        idle: false,
	        lastActivityDate: false,
	        mobileLastDate: false,
	        isOnline: false,
	        isMobileOnline: false,
	        absent: false,
	        isAbsent: false,
	        departments: [],
	        phones: {
	          workPhone: "",
	          personalMobile: "",
	          personalPhone: "",
	          innerPhone: ""
	        },
	        init: false
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;
	      return {
	        get: function get(state) {
	          return function (userId) {
	            var getTemporary = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	            userId = parseInt(userId);
	            if (userId <= 0) {
	              if (getTemporary) {
	                userId = 0;
	              } else {
	                return null;
	              }
	            }
	            if (!getTemporary && (!state.collection[userId] || !state.collection[userId].init)) {
	              return null;
	            }
	            if (!state.collection[userId]) {
	              return _this.getElementState({
	                id: userId
	              });
	            }
	            return state.collection[userId];
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState(params);
	          };
	        },
	        getList: function getList(state) {
	          return function (userList) {
	            var result = [];
	            if (!Array.isArray(userList)) {
	              return null;
	            }
	            userList.forEach(function (id) {
	              if (state.collection[id]) {
	                result.push(state.collection[id]);
	              } else {
	                result.push(_this.getElementState({
	                  id: id
	                }));
	              }
	            });
	            return result;
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;
	      return {
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (user) {
	              return Object.assign({}, _this2.getElementState(), _this2.validate(Object.assign({}, user), {
	                host: store.state.host
	              }), {
	                init: true
	              });
	            });
	          } else {
	            var result = [];
	            result.push(Object.assign({}, _this2.getElementState(), _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            }), {
	              init: true
	            }));
	            payload = result;
	          }
	          store.commit('set', payload);
	        },
	        update: function update(store, payload) {
	          payload.id = parseInt(payload.id);
	          if (typeof store.state.collection[payload.id] === 'undefined' || store.state.collection[payload.id].init === false) {
	            return true;
	          }
	          store.commit('update', {
	            id: payload.id,
	            fields: _this2.validate(Object.assign({}, payload.fields), {
	              host: store.state.host
	            })
	          });
	          return true;
	        },
	        "delete": function _delete(store, payload) {
	          store.commit('delete', payload.id);
	          return true;
	        },
	        saveState: function saveState(store, payload) {
	          store.commit('saveState', {});
	          return true;
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;
	      return {
	        set: function set(state, payload) {
	          var _iterator = _createForOfIteratorHelper$2(payload),
	            _step;
	          try {
	            var _loop = function _loop() {
	              var element = _step.value;
	              _this3.initCollection(state, {
	                id: element.id
	              });
	              state.collection[element.id] = Object.assign(state.collection[element.id], element);
	              var status = im_lib_utils.Utils.user.getOnlineStatus(element);
	              if (status.isOnline) {
	                state.collection[element.id].isOnline = true;
	                _this3.addToOnlineList(state, element.id);
	              }
	              var mobileStatus = im_lib_utils.Utils.user.isMobileActive(element);
	              if (mobileStatus) {
	                state.collection[element.id].isMobileOnline = true;
	                _this3.addToMobileOnlineList(state, element.id);
	              }
	              if (element.birthday) {
	                var today = im_lib_utils.Utils.date.format(new Date(), "d-m");
	                if (element.birthday === today) {
	                  state.collection[element.id].isBirthday = true;
	                  var timeToNextMidnight = _this3.getTimeToNextMidnight();
	                  setTimeout(function () {
	                    state.collection[element.id].isBirthday = false;
	                  }, timeToNextMidnight);
	                }
	              }
	              if (element.absent) {
	                element.isAbsent = true;
	                if (!state.absentList.includes(element.id)) {
	                  _this3.addToAbsentList(state, element.id);
	                  var _timeToNextMidnight = _this3.getTimeToNextMidnight();
	                  var timeToNextDay = 1000 * 60 * 60 * 24;
	                  setTimeout(function () {
	                    setInterval(function () {
	                      return _this3.startAbsentCheckInterval(state);
	                    }, timeToNextDay);
	                  }, _timeToNextMidnight);
	                }
	              }
	              _this3.saveState(state);
	            };
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              _loop();
	            }
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	        },
	        update: function update(state, payload) {
	          _this3.initCollection(state, payload);
	          if (typeof payload.fields.lastActivityDate !== 'undefined' && state.collection[payload.id].lastActivityDate) {
	            var lastActivityDate = state.collection[payload.id].lastActivityDate.getTime();
	            var newActivityDate = payload.fields.lastActivityDate.getTime();
	            if (newActivityDate > lastActivityDate) {
	              var status = im_lib_utils.Utils.user.getOnlineStatus(payload.fields);
	              if (status.isOnline) {
	                state.collection[payload.id].isOnline = true;
	                _this3.addToOnlineList(state, payload.fields.id);
	              }
	            }
	          }
	          if (typeof payload.fields.mobileLastDate !== 'undefined' && state.collection[payload.id].mobileLastDate !== payload.fields.mobileLastDate) {
	            var mobileStatus = im_lib_utils.Utils.user.isMobileActive(payload.fields);
	            if (mobileStatus) {
	              state.collection[payload.id].isMobileOnline = true;
	              _this3.addToMobileOnlineList(state, payload.fields.id);
	            }
	          }
	          state.collection[payload.id] = Object.assign(state.collection[payload.id], payload.fields);
	          _this3.saveState(state);
	        },
	        "delete": function _delete(state, payload) {
	          delete state.collection[payload.id];
	          _this3.saveState(state);
	        },
	        saveState: function saveState(state, payload) {
	          _this3.saveState(state);
	        }
	      };
	    }
	  }, {
	    key: "initCollection",
	    value: function initCollection(state, payload) {
	      if (typeof state.collection[payload.id] !== 'undefined') {
	        return true;
	      }
	      ui_vue.Vue.set(state.collection, payload.id, this.getElementState());
	      return true;
	    }
	  }, {
	    key: "getSaveUserList",
	    value: function getSaveUserList() {
	      if (!this.db) {
	        return [];
	      }
	      if (!this.store.getters['messages/getSaveUserList']) {
	        return [];
	      }
	      var list = this.store.getters['messages/getSaveUserList']();
	      if (!list) {
	        return [];
	      }
	      return list;
	    }
	  }, {
	    key: "getSaveTimeout",
	    value: function getSaveTimeout() {
	      return 250;
	    }
	  }, {
	    key: "saveState",
	    value: function saveState(state) {
	      var _this4 = this;
	      if (!this.isSaveAvailable()) {
	        return false;
	      }
	      babelHelpers.get(babelHelpers.getPrototypeOf(UsersModel.prototype), "saveState", this).call(this, function () {
	        var list = _this4.getSaveUserList();
	        if (!list) {
	          return false;
	        }
	        var storedState = {
	          collection: {}
	        };
	        var exceptionList = {
	          absent: true,
	          idle: true,
	          mobileLastDate: true,
	          lastActivityDate: true
	        };
	        for (var chatId in list) {
	          if (!list.hasOwnProperty(chatId)) {
	            continue;
	          }
	          list[chatId].forEach(function (userId) {
	            if (!state.collection[userId]) {
	              return false;
	            }
	            storedState.collection[userId] = _this4.cloneState(state.collection[userId], exceptionList);
	          });
	        }
	        return storedState;
	      });
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = {};
	      options.host = options.host || this.getState().host;
	      if (typeof fields.id === "number" || typeof fields.id === "string") {
	        result.id = parseInt(fields.id);
	      }
	      if (typeof fields.first_name !== "undefined") {
	        fields.firstName = im_lib_utils.Utils.text.htmlspecialcharsback(fields.first_name);
	      }
	      if (typeof fields.last_name !== "undefined") {
	        fields.lastName = im_lib_utils.Utils.text.htmlspecialcharsback(fields.last_name);
	      }
	      if (typeof fields.name === "string" || typeof fields.name === "number") {
	        fields.name = im_lib_utils.Utils.text.htmlspecialcharsback(fields.name.toString());
	        result.name = fields.name;
	      }
	      if (typeof fields.firstName === "string" || typeof fields.firstName === "number") {
	        result.firstName = im_lib_utils.Utils.text.htmlspecialcharsback(fields.firstName.toString());
	      }
	      if (typeof fields.lastName === "string" || typeof fields.lastName === "number") {
	        result.lastName = im_lib_utils.Utils.text.htmlspecialcharsback(fields.lastName.toString());
	      }
	      if (typeof fields.work_position !== "undefined") {
	        fields.workPosition = fields.work_position;
	      }
	      if (typeof fields.workPosition === "string" || typeof fields.workPosition === "number") {
	        result.workPosition = fields.workPosition.toString();
	      }
	      if (typeof fields.color === "string") {
	        result.color = fields.color;
	      }
	      if (typeof fields.avatar === 'string') {
	        var avatar;
	        if (!fields.avatar || fields.avatar.endsWith('/js/im/images/blank.gif')) {
	          avatar = '';
	        } else if (fields.avatar.startsWith('http')) {
	          avatar = fields.avatar;
	        } else {
	          avatar = options.host + fields.avatar;
	        }
	        if (avatar) {
	          result.avatar = encodeURI(avatar);
	        }
	      }
	      if (typeof fields.gender !== 'undefined') {
	        result.gender = fields.gender === 'F' ? 'F' : 'M';
	      }
	      if (typeof fields.birthday === "string") {
	        result.birthday = fields.birthday;
	      }
	      if (typeof fields.extranet === "boolean") {
	        result.extranet = fields.extranet;
	      }
	      if (typeof fields.network === "boolean") {
	        result.network = fields.network;
	      }
	      if (typeof fields.bot === "boolean") {
	        result.bot = fields.bot;
	      }
	      if (typeof fields.connector === "boolean") {
	        result.connector = fields.connector;
	      }
	      if (typeof fields.external_auth_id !== "undefined") {
	        fields.externalAuthId = fields.external_auth_id;
	      }
	      if (typeof fields.externalAuthId === "string" && fields.externalAuthId) {
	        result.externalAuthId = fields.externalAuthId;
	      }
	      if (typeof fields.status === "string") {
	        result.status = fields.status;
	      }
	      if (typeof fields.idle !== "undefined") {
	        result.idle = im_lib_utils.Utils.date.cast(fields.idle, false);
	      }
	      if (typeof fields.last_activity_date !== "undefined") {
	        fields.lastActivityDate = fields.last_activity_date;
	      }
	      if (typeof fields.lastActivityDate !== "undefined") {
	        result.lastActivityDate = im_lib_utils.Utils.date.cast(fields.lastActivityDate, false);
	      }
	      if (typeof fields.mobile_last_date !== "undefined") {
	        fields.mobileLastDate = fields.mobile_last_date;
	      }
	      if (typeof fields.mobileLastDate !== "undefined") {
	        result.mobileLastDate = im_lib_utils.Utils.date.cast(fields.mobileLastDate, false);
	      }
	      if (typeof fields.absent !== "undefined") {
	        result.absent = im_lib_utils.Utils.date.cast(fields.absent, false);
	      }
	      if (typeof fields.departments !== 'undefined') {
	        result.departments = [];
	        if (fields.departments instanceof Array) {
	          fields.departments.forEach(function (departmentId) {
	            departmentId = parseInt(departmentId);
	            if (departmentId > 0) {
	              result.departments.push(departmentId);
	            }
	          });
	        }
	      }
	      if (babelHelpers["typeof"](fields.phones) === 'object' && fields.phones) {
	        result.phones = {};
	        if (typeof fields.phones.work_phone !== "undefined") {
	          fields.phones.workPhone = fields.phones.work_phone;
	        }
	        if (typeof fields.phones.workPhone === 'string' || typeof fields.phones.workPhone === 'number') {
	          result.phones.workPhone = fields.phones.workPhone.toString();
	        }
	        if (typeof fields.phones.personal_mobile !== "undefined") {
	          fields.phones.personalMobile = fields.phones.personal_mobile;
	        }
	        if (typeof fields.phones.personalMobile === 'string' || typeof fields.phones.personalMobile === 'number') {
	          result.phones.personalMobile = fields.phones.personalMobile.toString();
	        }
	        if (typeof fields.phones.personal_phone !== "undefined") {
	          fields.phones.personalPhone = fields.phones.personal_phone;
	        }
	        if (typeof fields.phones.personalPhone === 'string' || typeof fields.phones.personalPhone === 'number') {
	          result.phones.personalPhone = fields.phones.personalPhone.toString();
	        }
	        if (typeof fields.phones.inner_phone !== "undefined") {
	          fields.phones.innerPhone = fields.phones.inner_phone;
	        }
	        if (typeof fields.phones.innerPhone === 'string' || typeof fields.phones.innerPhone === 'number') {
	          result.phones.innerPhone = fields.phones.innerPhone.toString();
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "addToOnlineList",
	    value: function addToOnlineList(state, id) {
	      if (!state.onlineList.includes(id)) {
	        state.onlineList.push(id);
	      }
	    }
	  }, {
	    key: "addToMobileOnlineList",
	    value: function addToMobileOnlineList(state, id) {
	      if (!state.mobileOnlineList.includes(id)) {
	        state.mobileOnlineList.push(id);
	      }
	    }
	  }, {
	    key: "addToAbsentList",
	    value: function addToAbsentList(state, id) {
	      if (!state.absentList.includes(id)) {
	        state.absentList.push(id);
	      }
	    }
	  }, {
	    key: "getTimeToNextMidnight",
	    value: function getTimeToNextMidnight() {
	      var nextMidnight = new Date(new Date().setHours(24, 0, 0)).getTime();
	      return nextMidnight - new Date();
	    }
	  }, {
	    key: "startAbsentCheckInterval",
	    value: function startAbsentCheckInterval(state) {
	      var _iterator2 = _createForOfIteratorHelper$2(state.absentList),
	        _step2;
	      try {
	        var _loop2 = function _loop2() {
	          var userId = _step2.value;
	          var user = state.collection[userId];
	          if (!user) {
	            return "continue";
	          }
	          var currentTime = new Date().getTime();
	          var absentEnd = new Date(state.collection[userId].absent).getTime();
	          if (absentEnd <= currentTime) {
	            state.absentList = state.absentList.filter(function (element) {
	              return element !== userId;
	            });
	            user.isAbsent = false;
	          }
	        };
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var _ret = _loop2();
	          if (_ret === "continue") continue;
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	    }
	  }, {
	    key: "startOnlineCheckInterval",
	    value: function startOnlineCheckInterval() {
	      var _this5 = this;
	      var intervalTime = 60000;
	      setInterval(function () {
	        var _iterator3 = _createForOfIteratorHelper$2(_this5.store.state.users.onlineList),
	          _step3;
	        try {
	          var _loop3 = function _loop3() {
	            var userId = _step3.value;
	            var user = _this5.store.state.users.collection[userId];
	            if (!user) {
	              return "continue";
	            }
	            var status = im_lib_utils.Utils.user.getOnlineStatus(user);
	            if (status.isOnline) {
	              user.isOnline = true;
	            } else {
	              user.isOnline = false;
	              _this5.store.state.users.onlineList = _this5.store.state.users.onlineList.filter(function (element) {
	                return element !== userId;
	              });
	            }
	          };
	          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	            var _ret2 = _loop3();
	            if (_ret2 === "continue") continue;
	          }
	        } catch (err) {
	          _iterator3.e(err);
	        } finally {
	          _iterator3.f();
	        }
	        var _iterator4 = _createForOfIteratorHelper$2(_this5.store.state.users.mobileOnlineList),
	          _step4;
	        try {
	          var _loop4 = function _loop4() {
	            var userId = _step4.value;
	            var user = _this5.store.state.users.collection[userId];
	            if (!user) {
	              return "continue";
	            }
	            var mobileStatus = im_lib_utils.Utils.user.isMobileActive(user);
	            if (mobileStatus) {
	              user.isMobileOnline = true;
	            } else {
	              user.isMobileOnline = false;
	              _this5.store.state.users.mobileOnlineList = _this5.store.state.users.mobileOnlineList.filter(function (element) {
	                return element !== userId;
	              });
	            }
	          };
	          for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
	            var _ret3 = _loop4();
	            if (_ret3 === "continue") continue;
	          }
	        } catch (err) {
	          _iterator4.e(err);
	        } finally {
	          _iterator4.f();
	        }
	      }, intervalTime);
	    }
	  }]);
	  return UsersModel;
	}(ui_vue_vuex.VuexBuilderModel);

	function _createForOfIteratorHelper$3(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$3(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$3(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$3(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$3(o, minLen); }
	function _arrayLikeToArray$3(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var FilesModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(FilesModel, _VuexBuilderModel);
	  function FilesModel() {
	    babelHelpers.classCallCheck(this, FilesModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FilesModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(FilesModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'files';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        created: 0,
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        collection: {},
	        index: {}
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var _params$id = params.id,
	        id = _params$id === void 0 ? 0 : _params$id,
	        _params$chatId = params.chatId,
	        chatId = _params$chatId === void 0 ? 0 : _params$chatId,
	        _params$name = params.name,
	        name = _params$name === void 0 ? this.getVariable('default.name', '') : _params$name;
	      return {
	        id: id,
	        chatId: chatId,
	        name: name,
	        templateId: id,
	        date: new Date(),
	        type: 'file',
	        extension: "",
	        icon: "empty",
	        size: 0,
	        image: false,
	        status: im_const.FileStatus.done,
	        progress: 100,
	        authorId: 0,
	        authorName: "",
	        urlPreview: "",
	        urlShow: "",
	        urlDownload: "",
	        init: false,
	        viewerAttrs: {}
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;
	      return {
	        get: function get(state) {
	          return function (chatId, fileId) {
	            var getTemporary = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	            if (!chatId || !fileId) {
	              return null;
	            }
	            if (!state.index[chatId] || !state.index[chatId][fileId]) {
	              return null;
	            }
	            if (!getTemporary && !state.index[chatId][fileId].init) {
	              return null;
	            }
	            return state.index[chatId][fileId];
	          };
	        },
	        getList: function getList(state) {
	          return function (chatId) {
	            if (!state.index[chatId]) {
	              return null;
	            }
	            return state.index[chatId];
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState(params);
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;
	      return {
	        add: function add(store, payload) {
	          var result = _this2.validate(Object.assign({}, payload), {
	            host: store.state.host
	          });
	          if (payload.id) {
	            result.id = payload.id;
	          } else {
	            result.id = 'temporary' + new Date().getTime() + store.state.created;
	          }
	          result.templateId = result.id;
	          result.init = true;
	          store.commit('add', Object.assign({}, _this2.getElementState(), result));
	          return result.id;
	        },
	        set: function set(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (file) {
	              var result = _this2.validate(Object.assign({}, file), {
	                host: store.state.host
	              });
	              result.templateId = result.id;
	              return Object.assign({}, _this2.getElementState(), result, {
	                init: true
	              });
	            });
	          } else {
	            var result = _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            });
	            result.templateId = result.id;
	            payload = [];
	            payload.push(Object.assign({}, _this2.getElementState(), result, {
	              init: true
	            }));
	          }
	          store.commit('set', {
	            insertType: im_const.MutationType.setAfter,
	            data: payload
	          });
	        },
	        setBefore: function setBefore(store, payload) {
	          if (payload instanceof Array) {
	            payload = payload.map(function (file) {
	              var result = _this2.validate(Object.assign({}, file), {
	                host: store.state.host
	              });
	              result.templateId = result.id;
	              return Object.assign({}, _this2.getElementState(), result, {
	                init: true
	              });
	            });
	          } else {
	            var result = _this2.validate(Object.assign({}, payload), {
	              host: store.state.host
	            });
	            result.templateId = result.id;
	            payload = [];
	            payload.push(Object.assign({}, _this2.getElementState(), result, {
	              init: true
	            }));
	          }
	          store.commit('set', {
	            actionName: 'setBefore',
	            insertType: im_const.MutationType.setBefore,
	            data: payload
	          });
	        },
	        update: function update(store, payload) {
	          var result = _this2.validate(Object.assign({}, payload.fields), {
	            host: store.state.host
	          });
	          store.commit('initCollection', {
	            chatId: payload.chatId
	          });
	          var index = store.state.collection[payload.chatId].findIndex(function (el) {
	            return el.id === payload.id;
	          });
	          if (index < 0) {
	            return false;
	          }
	          store.commit('update', {
	            id: payload.id,
	            chatId: payload.chatId,
	            index: index,
	            fields: result
	          });
	          if (payload.fields.blink) {
	            setTimeout(function () {
	              store.commit('update', {
	                id: payload.id,
	                chatId: payload.chatId,
	                fields: {
	                  blink: false
	                }
	              });
	            }, 1000);
	          }
	          return true;
	        },
	        "delete": function _delete(store, payload) {
	          store.commit('delete', {
	            id: payload.id,
	            chatId: payload.chatId
	          });
	          return true;
	        },
	        saveState: function saveState(store, payload) {
	          store.commit('saveState', {});
	          return true;
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;
	      return {
	        initCollection: function initCollection(state, payload) {
	          _this3.initCollection(state, payload);
	        },
	        add: function add(state, payload) {
	          _this3.initCollection(state, payload);
	          state.collection[payload.chatId].push(payload);
	          state.index[payload.chatId][payload.id] = payload;
	          state.created += 1;
	          _this3.saveState(state);
	        },
	        set: function set(state, payload) {
	          var _iterator = _createForOfIteratorHelper$3(payload.data),
	            _step;
	          try {
	            var _loop = function _loop() {
	              var element = _step.value;
	              _this3.initCollection(state, {
	                chatId: element.chatId
	              });
	              var index = state.collection[element.chatId].findIndex(function (el) {
	                return el.id === element.id;
	              });
	              if (index > -1) {
	                delete element.templateId;
	                state.collection[element.chatId][index] = Object.assign(state.collection[element.chatId][index], element);
	              } else if (payload.insertType === im_const.MutationType.setBefore) {
	                state.collection[element.chatId].unshift(element);
	              } else {
	                state.collection[element.chatId].push(element);
	              }
	              state.index[element.chatId][element.id] = element;
	              _this3.saveState(state);
	            };
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              _loop();
	            }
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	        },
	        update: function update(state, payload) {
	          _this3.initCollection(state, payload);
	          var index = -1;
	          if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index]) {
	            index = payload.index;
	          } else {
	            index = state.collection[payload.chatId].findIndex(function (el) {
	              return el.id === payload.id;
	            });
	          }
	          if (index >= 0) {
	            delete payload.fields.templateId;
	            var element = Object.assign(state.collection[payload.chatId][index], payload.fields);
	            state.collection[payload.chatId][index] = element;
	            state.index[payload.chatId][element.id] = element;
	            _this3.saveState(state);
	          }
	        },
	        "delete": function _delete(state, payload) {
	          _this3.initCollection(state, payload);
	          state.collection[payload.chatId] = state.collection[payload.chatId].filter(function (element) {
	            return element.id !== payload.id;
	          });
	          delete state.index[payload.chatId][payload.id];
	          _this3.saveState(state);
	        },
	        saveState: function saveState(state, payload) {
	          _this3.saveState(state);
	        }
	      };
	    }
	  }, {
	    key: "initCollection",
	    value: function initCollection(state, payload) {
	      if (typeof state.collection[payload.chatId] !== 'undefined') {
	        return true;
	      }
	      ui_vue.Vue.set(state.collection, payload.chatId, []);
	      ui_vue.Vue.set(state.index, payload.chatId, {});
	      return true;
	    }
	  }, {
	    key: "getLoadedState",
	    value: function getLoadedState(state) {
	      if (!state || babelHelpers["typeof"](state) !== 'object') {
	        return state;
	      }
	      if (babelHelpers["typeof"](state.collection) !== 'object') {
	        return state;
	      }
	      state.index = {};
	      var _loop2 = function _loop2(chatId) {
	        if (!state.collection.hasOwnProperty(chatId)) {
	          return "continue";
	        }
	        state.index[chatId] = {};
	        state.collection[chatId].filter(function (file) {
	          return file != null;
	        }).forEach(function (file) {
	          state.index[chatId][file.id] = file;
	        });
	      };
	      for (var chatId in state.collection) {
	        var _ret = _loop2(chatId);
	        if (_ret === "continue") continue;
	      }
	      return state;
	    }
	  }, {
	    key: "getSaveFileList",
	    value: function getSaveFileList() {
	      if (!this.db) {
	        return [];
	      }
	      if (!this.store.getters['messages/getSaveFileList']) {
	        return [];
	      }
	      var list = this.store.getters['messages/getSaveFileList']();
	      if (!list) {
	        return [];
	      }
	      return list;
	    }
	  }, {
	    key: "getSaveTimeout",
	    value: function getSaveTimeout() {
	      return 250;
	    }
	  }, {
	    key: "saveState",
	    value: function saveState(state) {
	      var _this4 = this;
	      if (!this.isSaveAvailable()) {
	        return false;
	      }
	      babelHelpers.get(babelHelpers.getPrototypeOf(FilesModel.prototype), "saveState", this).call(this, function () {
	        var list = _this4.getSaveFileList();
	        if (!list) {
	          return false;
	        }
	        var storedState = {
	          collection: {}
	        };
	        var _loop3 = function _loop3(chatId) {
	          if (!list.hasOwnProperty(chatId)) {
	            return "continue";
	          }
	          list[chatId].forEach(function (fileId) {
	            if (!state.index[chatId]) {
	              return false;
	            }
	            if (!state.index[chatId][fileId]) {
	              return false;
	            }
	            if (!storedState.collection[chatId]) {
	              storedState.collection[chatId] = [];
	            }
	            storedState.collection[chatId].push(state.index[chatId][fileId]);
	          });
	        };
	        for (var chatId in list) {
	          var _ret2 = _loop3(chatId);
	          if (_ret2 === "continue") continue;
	        }
	        return storedState;
	      });
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = {};
	      options.host = options.host || this.getState().host;
	      if (typeof fields.id === "number") {
	        result.id = fields.id;
	      } else if (typeof fields.id === "string") {
	        if (fields.id.startsWith('temporary')) {
	          result.id = fields.id;
	        } else {
	          result.id = parseInt(fields.id);
	        }
	      }
	      if (typeof fields.templateId === "number") {
	        result.templateId = fields.templateId;
	      } else if (typeof fields.templateId === "string") {
	        if (fields.templateId.startsWith('temporary')) {
	          result.templateId = fields.templateId;
	        } else {
	          result.templateId = parseInt(fields.templateId);
	        }
	      }
	      if (typeof fields.chatId === "number" || typeof fields.chatId === "string") {
	        result.chatId = parseInt(fields.chatId);
	      }
	      if (typeof fields.date !== "undefined") {
	        result.date = im_lib_utils.Utils.date.cast(fields.date);
	      }
	      if (typeof fields.type === "string") {
	        result.type = fields.type;
	      }
	      if (typeof fields.extension === "string") {
	        result.extension = fields.extension.toString();
	        if (result.type === 'image') {
	          result.icon = 'img';
	        } else if (result.type === 'video') {
	          result.icon = 'mov';
	        } else {
	          result.icon = FilesModel.getIconType(result.extension);
	        }
	      }
	      if (typeof fields.name === "string" || typeof fields.name === "number") {
	        result.name = fields.name.toString();
	      }
	      if (typeof fields.size === "number" || typeof fields.size === "string") {
	        result.size = parseInt(fields.size);
	      }
	      if (typeof fields.image === 'boolean') {
	        result.image = false;
	      } else if (babelHelpers["typeof"](fields.image) === 'object' && fields.image) {
	        result.image = {
	          width: 0,
	          height: 0
	        };
	        if (typeof fields.image.width === "string" || typeof fields.image.width === "number") {
	          result.image.width = parseInt(fields.image.width);
	        }
	        if (typeof fields.image.height === "string" || typeof fields.image.height === "number") {
	          result.image.height = parseInt(fields.image.height);
	        }
	        if (result.image.width <= 0 || result.image.height <= 0) {
	          result.image = false;
	        }
	      }
	      if (typeof fields.status === "string" && typeof im_const.FileStatus[fields.status] !== 'undefined') {
	        result.status = fields.status;
	      }
	      if (typeof fields.progress === "number" || typeof fields.progress === "string") {
	        result.progress = parseInt(fields.progress);
	      }
	      if (typeof fields.authorId === "number" || typeof fields.authorId === "string") {
	        result.authorId = parseInt(fields.authorId);
	      }
	      if (typeof fields.authorName === "string" || typeof fields.authorName === "number") {
	        result.authorName = fields.authorName.toString();
	      }
	      if (typeof fields.urlPreview === 'string') {
	        if (!fields.urlPreview || fields.urlPreview.startsWith('http') || fields.urlPreview.startsWith('bx') || fields.urlPreview.startsWith('file') || fields.urlPreview.startsWith('blob')) {
	          result.urlPreview = fields.urlPreview;
	        } else {
	          result.urlPreview = options.host + fields.urlPreview;
	        }
	      }
	      if (typeof fields.urlDownload === 'string') {
	        if (!fields.urlDownload || fields.urlDownload.startsWith('http') || fields.urlDownload.startsWith('bx') || fields.urlPreview.startsWith('file')) {
	          result.urlDownload = fields.urlDownload;
	        } else {
	          result.urlDownload = options.host + fields.urlDownload;
	        }
	      }
	      if (typeof fields.urlShow === 'string') {
	        if (!fields.urlShow || fields.urlShow.startsWith('http') || fields.urlShow.startsWith('bx') || fields.urlShow.startsWith('file')) {
	          result.urlShow = fields.urlShow;
	        } else {
	          result.urlShow = options.host + fields.urlShow;
	        }
	      }
	      if (babelHelpers["typeof"](fields.viewerAttrs) === 'object') {
	        if (result.type === 'image' && !im_lib_utils.Utils.platform.isBitrixMobile()) {
	          result.viewerAttrs = fields.viewerAttrs;
	        }
	        if (result.type === 'video' && !im_lib_utils.Utils.platform.isBitrixMobile() && result.size > FilesModel.maxDiskFileSize) {
	          result.viewerAttrs = fields.viewerAttrs;
	        }
	      }
	      return result;
	    }
	  }], [{
	    key: "getType",
	    value: function getType(type) {
	      type = type.toString().toLowerCase().split('.').splice(-1)[0];
	      switch (type) {
	        case 'png':
	        case 'jpe':
	        case 'jpg':
	        case 'jpeg':
	        case 'gif':
	        case 'heic':
	        case 'bmp':
	        case 'webp':
	          return im_const.FileType.image;
	        case 'mp4':
	        case 'mkv':
	        case 'webm':
	        case 'mpeg':
	        case 'hevc':
	        case 'avi':
	        case '3gp':
	        case 'flv':
	        case 'm4v':
	        case 'ogg':
	        case 'wmv':
	        case 'mov':
	          return im_const.FileType.video;
	        case 'mp3':
	          return im_const.FileType.audio;
	      }
	      return im_const.FileType.file;
	    }
	  }, {
	    key: "getIconType",
	    value: function getIconType(extension) {
	      var icon = 'empty';
	      switch (extension.toString()) {
	        case 'png':
	        case 'jpe':
	        case 'jpg':
	        case 'jpeg':
	        case 'gif':
	        case 'heic':
	        case 'bmp':
	        case 'webp':
	          icon = 'img';
	          break;
	        case 'mp4':
	        case 'mkv':
	        case 'webm':
	        case 'mpeg':
	        case 'hevc':
	        case 'avi':
	        case '3gp':
	        case 'flv':
	        case 'm4v':
	        case 'ogg':
	        case 'wmv':
	        case 'mov':
	          icon = 'mov';
	          break;
	        case 'txt':
	          icon = 'txt';
	          break;
	        case 'doc':
	        case 'docx':
	          icon = 'doc';
	          break;
	        case 'xls':
	        case 'xlsx':
	          icon = 'xls';
	          break;
	        case 'php':
	          icon = 'php';
	          break;
	        case 'pdf':
	          icon = 'pdf';
	          break;
	        case 'ppt':
	        case 'pptx':
	          icon = 'ppt';
	          break;
	        case 'rar':
	          icon = 'rar';
	          break;
	        case 'zip':
	        case '7z':
	        case 'tar':
	        case 'gz':
	        case 'gzip':
	          icon = 'zip';
	          break;
	        case 'set':
	          icon = 'set';
	          break;
	        case 'conf':
	        case 'ini':
	        case 'plist':
	          icon = 'set';
	          break;
	      }
	      return icon;
	    }
	  }]);
	  return FilesModel;
	}(ui_vue_vuex.VuexBuilderModel);
	babelHelpers.defineProperty(FilesModel, "maxDiskFileSize", 5242880);

	/**
	 * Bitrix Messenger
	 * Recent model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var RecentModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(RecentModel, _VuexBuilderModel);
	  function RecentModel() {
	    babelHelpers.classCallCheck(this, RecentModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RecentModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(RecentModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'recent';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        collection: []
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        id: 0,
	        templateId: '',
	        template: im_const.TemplateTypes.item,
	        chatType: im_const.ChatTypes.chat,
	        sectionCode: im_const.RecentSection.general,
	        avatar: '',
	        color: '#048bd0',
	        title: '',
	        lines: {
	          id: 0,
	          status: 0
	        },
	        message: {
	          id: 0,
	          text: '',
	          date: new Date(),
	          senderId: 0,
	          status: im_const.MessageStatus.received
	        },
	        counter: 0,
	        pinned: false,
	        chatId: 0,
	        userId: 0
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;
	      return {
	        get: function get(state) {
	          return function (dialogId) {
	            if (main_core.Type.isNumber(dialogId)) {
	              dialogId = dialogId.toString();
	            }
	            var currentItem = _this.findItem(dialogId);
	            if (currentItem) {
	              return currentItem;
	            }
	            return false;
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;
	      return {
	        set: function set(store, payload) {
	          var result = [];
	          if (payload instanceof Array) {
	            result = payload.map(function (recentItem) {
	              return _this2.prepareItem(recentItem, {
	                host: store.state.host
	              });
	            });
	          }
	          if (result.length === 0) {
	            return false;
	          }
	          result.forEach(function (element) {
	            var existingItem = _this2.findItem(element.id);
	            if (existingItem) {
	              store.commit('update', {
	                index: existingItem.index,
	                fields: element
	              });
	            } else {
	              store.commit('add', {
	                fields: element
	              });
	            }
	          });
	          store.state.collection.sort(_this2.sortListByMessageDate);
	        },
	        addPlaceholders: function addPlaceholders(store, payload) {
	          payload.forEach(function (element) {
	            store.commit('addPlaceholder', {
	              fields: element
	            });
	          });
	        },
	        updatePlaceholders: function updatePlaceholders(store, payload) {
	          payload.items = payload.items.map(function (element) {
	            return _this2.prepareItem(element);
	          });
	          payload.items.forEach(function (element, index) {
	            var placeholderId = 'placeholder' + (payload.firstMessage + index);
	            var existingPlaceholder = _this2.findItem(placeholderId, 'templateId');
	            var existingItem = _this2.findItem(element.id);
	            if (existingItem) {
	              store.commit('update', {
	                index: existingItem.index,
	                fields: element
	              });
	              store.commit('delete', {
	                index: existingPlaceholder.index
	              });
	            } else {
	              store.commit('update', {
	                index: existingPlaceholder.index,
	                fields: element
	              });
	            }
	          });
	        },
	        update: function update(store, payload) {
	          if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify') {
	            payload.id = parseInt(payload.id);
	          }
	          var existingItem = _this2.findItem(payload.id);
	          if (!existingItem) {
	            return false;
	          }
	          payload.fields = _this2.validate(Object.assign({}, payload.fields));
	          store.commit('update', {
	            index: existingItem.index,
	            fields: payload.fields
	          });
	          store.state.collection.sort(_this2.sortListByMessageDate);
	        },
	        pin: function pin(store, payload) {
	          if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify') {
	            payload.id = parseInt(payload.id);
	          }
	          var existingItem = _this2.findItem(payload.id);
	          if (!existingItem) {
	            return false;
	          }
	          store.commit('update', {
	            index: existingItem.index,
	            fields: Object.assign({}, existingItem.element, {
	              pinned: payload.action
	            })
	          });
	          store.state.collection.sort(_this2.sortListByMessageDate);
	        },
	        clearPlaceholders: function clearPlaceholders(store) {
	          store.commit('clearPlaceholders');
	        },
	        "delete": function _delete(store, payload) {
	          if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify') {
	            payload.id = parseInt(payload.id);
	          }
	          var existingItem = _this2.findItem(payload.id);
	          if (!existingItem) {
	            return false;
	          }
	          store.commit('delete', {
	            index: existingItem.index
	          });
	          store.state.collection.sort(_this2.sortListByMessageDate);
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;
	      return {
	        add: function add(state, payload) {
	          state.collection.push(Object.assign({}, _this3.getElementState(), payload.fields));
	        },
	        update: function update(state, payload) {
	          state.collection.splice(payload.index, 1, Object.assign({}, state.collection[payload.index], payload.fields));
	        },
	        "delete": function _delete(state, payload) {
	          state.collection.splice(payload.index, 1);
	        },
	        addPlaceholder: function addPlaceholder(state, payload) {
	          state.collection.push(Object.assign({}, _this3.getElementState(), payload.fields));
	        },
	        clearPlaceholders: function clearPlaceholders(state) {
	          state.collection = state.collection.filter(function (element) {
	            return !element.id.toString().startsWith('placeholder');
	          });
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = {};
	      if (main_core.Type.isNumber(fields.id)) {
	        result.id = fields.id.toString();
	      }
	      if (main_core.Type.isStringFilled(fields.id)) {
	        result.id = fields.id;
	      }
	      if (main_core.Type.isString(fields.templateId)) {
	        result.templateId = fields.templateId;
	      }
	      if (main_core.Type.isString(fields.template)) {
	        result.template = fields.template;
	      }
	      if (main_core.Type.isString(fields.type)) {
	        if (fields.type === im_const.ChatTypes.chat) {
	          if (fields.chat.type === im_const.ChatTypes.open) {
	            result.chatType = im_const.ChatTypes.open;
	          } else if (fields.chat.type === im_const.ChatTypes.chat) {
	            result.chatType = im_const.ChatTypes.chat;
	          }
	        } else if (fields.type === im_const.ChatTypes.user) {
	          result.chatType = im_const.ChatTypes.user;
	        } else if (fields.type === im_const.ChatTypes.notification) {
	          result.chatType = im_const.ChatTypes.notification;
	          fields.title = 'Notifications';
	        } else {
	          result.chatType = im_const.ChatTypes.chat;
	        }
	      }
	      if (main_core.Type.isString(fields.avatar)) {
	        var avatar;
	        if (!fields.avatar || fields.avatar.endsWith('/js/im/images/blank.gif')) {
	          avatar = '';
	        } else if (fields.avatar.startsWith('http')) {
	          avatar = fields.avatar;
	        } else {
	          avatar = options.host + fields.avatar;
	        }
	        if (avatar) {
	          result.avatar = encodeURI(avatar);
	        }
	      }
	      if (main_core.Type.isString(fields.color)) {
	        result.color = fields.color;
	      }
	      if (main_core.Type.isString(fields.title)) {
	        result.title = fields.title;
	      }
	      if (main_core.Type.isPlainObject(fields.message)) {
	        var message = {};
	        if (main_core.Type.isNumber(fields.message.id)) {
	          message.id = fields.message.id;
	        }
	        if (main_core.Type.isString(fields.message.text)) {
	          var _options = {};
	          if (fields.message.withAttach) {
	            _options.WITH_ATTACH = true;
	          } else if (fields.message.withFile) {
	            _options.WITH_FILE = true;
	          }
	          message.text = im_lib_utils.Utils.text.purify(fields.message.text, _options);
	        }
	        if (main_core.Type.isDate(fields.message.date) || main_core.Type.isString(fields.message.date)) {
	          message.date = fields.message.date;
	        }
	        if (main_core.Type.isNumber(fields.message.author_id)) {
	          message.senderId = fields.message.author_id;
	        }
	        if (main_core.Type.isNumber(fields.message.senderId)) {
	          message.senderId = fields.message.senderId;
	        }
	        if (main_core.Type.isStringFilled(fields.message.status)) {
	          message.status = fields.message.status;
	        }
	        result.message = message;
	      }
	      if (main_core.Type.isNumber(fields.counter)) {
	        result.counter = fields.counter;
	      }
	      if (main_core.Type.isBoolean(fields.pinned)) {
	        result.pinned = fields.pinned;
	      }
	      if (main_core.Type.isNumber(fields.chatId)) {
	        result.chatId = fields.chatId;
	      }
	      if (main_core.Type.isNumber(fields.userId)) {
	        result.userId = fields.userId;
	      }
	      return result;
	    }
	  }, {
	    key: "sortListByMessageDate",
	    value: function sortListByMessageDate(a, b) {
	      if (a.message && b.message) {
	        var timestampA = new Date(a.message.date).getTime();
	        var timestampB = new Date(b.message.date).getTime();
	        return timestampB - timestampA;
	      }
	    }
	  }, {
	    key: "prepareItem",
	    value: function prepareItem(item) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = this.validate(Object.assign({}, item));
	      return Object.assign({}, this.getElementState(), result, options);
	    }
	  }, {
	    key: "findItem",
	    value: function findItem(value) {
	      var key = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'id';
	      var result = {};
	      if (key === 'id' && main_core.Type.isNumber(value)) {
	        value = value.toString();
	      }
	      var elementIndex = this.store.state.recent.collection.findIndex(function (element, index) {
	        return element[key] === value;
	      });
	      if (elementIndex !== -1) {
	        result.index = elementIndex;
	        result.element = this.store.state.recent.collection[elementIndex];
	        return result;
	      }
	      return false;
	    }
	  }]);
	  return RecentModel;
	}(ui_vue_vuex.VuexBuilderModel);

	//raw input object for validation

	function _createForOfIteratorHelper$4(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$4(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$4(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$4(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$4(o, minLen); }
	function _arrayLikeToArray$4(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var NotificationsModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(NotificationsModel, _VuexBuilderModel);
	  function NotificationsModel() {
	    babelHelpers.classCallCheck(this, NotificationsModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NotificationsModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(NotificationsModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'notifications';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        collection: [],
	        searchCollection: [],
	        chat_id: 0,
	        total: 0,
	        host: this.getVariable('host', location.protocol + '//' + location.host),
	        unreadCounter: 0,
	        schema: {}
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {
	        id: 0,
	        authorId: 0,
	        date: new Date(),
	        text: '',
	        sectionCode: im_const.NotificationTypesCodes.simple,
	        textConverted: '',
	        title: '',
	        unread: false,
	        display: true,
	        settingName: 'im|default'
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;
	      return {
	        get: function get(state) {
	          return function () {
	            return state.collection;
	          };
	        },
	        getById: function getById(state) {
	          return function (notificationId) {
	            if (main_core.Type.isString(notificationId)) {
	              notificationId = parseInt(notificationId);
	            }
	            var existingItem = _this.findItemInArr(state.collection, notificationId);
	            if (!existingItem.element) {
	              return false;
	            }
	            return existingItem.element;
	          };
	        },
	        getSearchItemById: function getSearchItemById(state) {
	          return function (notificationId) {
	            if (main_core.Type.isString(notificationId)) {
	              notificationId = parseInt(notificationId);
	            }
	            var existingItem = _this.findItemInArr(state.searchCollection, notificationId);
	            if (!existingItem.element) {
	              return false;
	            }
	            return existingItem.element;
	          };
	        },
	        getBlank: function getBlank(state) {
	          return function (params) {
	            return _this.getElementState();
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;
	      return {
	        set: function set(store, payload) {
	          var result = {
	            notification: []
	          };
	          if (payload.notification instanceof Array) {
	            result.notification = payload.notification.map(function (notification) {
	              return _this2.prepareNotification(notification, {
	                host: store.state.host
	              });
	            });
	          }
	          if (main_core.Type.isNumber(payload.total) || main_core.Type.isString(payload.total)) {
	            result.total = parseInt(payload.total);
	          }
	          store.commit('set', result);
	        },
	        setSearchResults: function setSearchResults(store, payload) {
	          var result = {
	            notification: []
	          };
	          if (!(payload.notification instanceof Array)) {
	            return false;
	          }

	          // we don't need validation for the local results
	          if (payload.type === 'local') {
	            result.notification = payload.notification;
	          } else {
	            result.notification = payload.notification.map(function (notification) {
	              return _this2.prepareNotification(notification, {
	                host: store.state.host
	              });
	            });
	          }
	          store.commit('setSearchResults', {
	            data: result
	          });
	        },
	        deleteSearchResults: function deleteSearchResults(store, payload) {
	          store.commit('deleteSearchResults');
	        },
	        setCounter: function setCounter(store, payload) {
	          if (main_core.Type.isNumber(payload.unreadTotal) || main_core.Type.isString(payload.unreadTotal)) {
	            var unreadCounter = parseInt(payload.unreadTotal);
	            store.commit('setCounter', unreadCounter);
	          }
	        },
	        setTotal: function setTotal(store, payload) {
	          if (main_core.Type.isNumber(payload.total) || main_core.Type.isString(payload.total)) {
	            store.commit('setTotal', payload.total);
	          }
	        },
	        add: function add(store, payload) {
	          var addItem = _this2.prepareNotification(payload.data, {
	            host: store.state.host
	          });
	          addItem.unread = true;
	          var existingItem = _this2.findItemInArr(store.state.collection, addItem.id);
	          if (!existingItem.element) {
	            store.commit('add', {
	              data: addItem
	            });
	            store.commit('setTotal', store.state.total + 1);
	          } else {
	            store.commit('update', {
	              index: existingItem.index,
	              fields: Object.assign({}, payload.fields)
	            });
	          }
	        },
	        updatePlaceholders: function updatePlaceholders(store, payload) {
	          if (payload.items instanceof Array) {
	            payload.items = payload.items.map(function (notification) {
	              return _this2.prepareNotification(notification);
	            });
	          } else {
	            return false;
	          }
	          store.commit('updatePlaceholders', payload);
	          return true;
	        },
	        clearPlaceholders: function clearPlaceholders(store, payload) {
	          store.commit('clearPlaceholders', payload);
	        },
	        update: function update(store, payload) {
	          var existingItem = _this2.findItemInArr(store.state.collection, payload.id);
	          if (existingItem.element) {
	            store.commit('update', {
	              index: existingItem.index,
	              fields: Object.assign({}, payload.fields)
	            });
	          }
	          if (payload.searchMode) {
	            var existingItemInSearchCollection = _this2.findItemInArr(store.state.searchCollection, payload.id);
	            if (existingItemInSearchCollection.element) {
	              store.commit('update', {
	                searchCollection: true,
	                index: existingItemInSearchCollection.index,
	                fields: Object.assign({}, payload.fields)
	              });
	            }
	          }
	        },
	        read: function read(store, payload) {
	          var _iterator = _createForOfIteratorHelper$4(payload.ids),
	            _step;
	          try {
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              var notificationId = _step.value;
	              var existingItem = _this2.findItemInArr(store.state.collection, notificationId);
	              if (!existingItem.element) {
	                return false;
	              }
	              store.commit('read', {
	                index: existingItem.index,
	                action: !payload.action
	              });
	            }
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	        },
	        readAll: function readAll(store, payload) {
	          store.commit('readAll');
	        },
	        "delete": function _delete(store, payload) {
	          var existingItem = _this2.findItemInArr(store.state.collection, payload.id);
	          if (existingItem.element) {
	            store.commit('delete', {
	              searchCollection: false,
	              index: existingItem.index
	            });
	            store.commit('setTotal', store.state.total - 1);
	          }
	          if (payload.searchMode) {
	            var existingItemInSearchCollection = _this2.findItemInArr(store.state.searchCollection, payload.id);
	            if (existingItemInSearchCollection.element) {
	              store.commit('delete', {
	                searchCollection: true,
	                index: existingItemInSearchCollection.index
	              });
	            }
	          }
	        },
	        deleteAll: function deleteAll(store, payload) {
	          store.commit('deleteAll');
	        },
	        setSchema: function setSchema(store, payload) {
	          store.commit('setSchema', {
	            data: payload.data
	          });
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;
	      return {
	        set: function set(state, payload) {
	          state.total = payload.hasOwnProperty('total') ? payload.total : state.total;
	          if (!payload.hasOwnProperty('notification') || !main_core.Type.isArray(payload.notification)) {
	            return;
	          }
	          var _iterator2 = _createForOfIteratorHelper$4(payload.notification),
	            _step2;
	          try {
	            for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	              var element = _step2.value;
	              var existingItem = _this3.findItemInArr(state.collection, element.id);
	              if (!existingItem.element) {
	                state.collection.push(element);
	              } else {
	                // we trust unread status of existing item to prevent notifications blinking while init loading.
	                if (element.unread !== state.collection[existingItem.index].unread) {
	                  element.unread = state.collection[existingItem.index].unread;
	                  state.unreadCounter = element.unread === true ? state.unreadCounter + 1 : state.unreadCounter - 1;
	                }
	                state.collection[existingItem.index] = Object.assign(state.collection[existingItem.index], element);
	              }
	            }
	          } catch (err) {
	            _iterator2.e(err);
	          } finally {
	            _iterator2.f();
	          }
	          state.collection.sort(_this3.sortByType);
	        },
	        setSearchResults: function setSearchResults(state, payload) {
	          var _iterator3 = _createForOfIteratorHelper$4(payload.data.notification),
	            _step3;
	          try {
	            for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	              var element = _step3.value;
	              var existingItem = _this3.findItemInArr(state.searchCollection, element.id);
	              if (!existingItem.element) {
	                state.searchCollection.push(element);
	              } else {
	                state.searchCollection[existingItem.index] = Object.assign(state.searchCollection[existingItem.index], element);
	              }
	            }
	          } catch (err) {
	            _iterator3.e(err);
	          } finally {
	            _iterator3.f();
	          }
	        },
	        deleteAll: function deleteAll(state, payload) {
	          state.collection = [];
	        },
	        deleteSearchResults: function deleteSearchResults(state, payload) {
	          state.searchCollection = [];
	        },
	        add: function add(state, payload) {
	          var firstNotificationIndex = null;
	          if (payload.data.sectionCode === im_const.NotificationTypesCodes.confirm) {
	            //new confirms should always add to the beginning of the collection
	            state.collection.unshift(payload.data);
	          } else
	            //if (payload.data.sectionCode === NotificationTypesCodes.simple)
	            {
	              for (var index = 0; state.collection.length > index; index++) {
	                if (state.collection[index].sectionCode === im_const.NotificationTypesCodes.simple) {
	                  firstNotificationIndex = index;
	                  break;
	                }
	              }

	              //if we didn't find any simple notification and its index, then add new one to the end.
	              if (firstNotificationIndex === null) {
	                state.collection.push(payload.data);
	              } else
	                //otherwise, put it right before first simple notification.
	                {
	                  state.collection.splice(firstNotificationIndex, 0, payload.data);
	                }
	            }
	          state.collection.sort(_this3.sortByType);
	        },
	        update: function update(state, payload) {
	          var collectionName = payload.searchCollection ? 'searchCollection' : 'collection';
	          ui_vue.Vue.set(state[collectionName], payload.index, Object.assign({}, state[collectionName][payload.index], payload.fields));
	        },
	        "delete": function _delete(state, payload) {
	          var collectionName = payload.searchCollection ? 'searchCollection' : 'collection';
	          state[collectionName].splice(payload.index, 1);
	        },
	        read: function read(state, payload) {
	          state.collection[payload.index].unread = payload.action;
	        },
	        readAll: function readAll(state, payload) {
	          for (var index = 0; state.collection.length > index; index++) {
	            state.collection[index].unread = false;
	          }
	        },
	        updatePlaceholders: function updatePlaceholders(state, payload) {
	          var collectionName = payload.searchCollection ? 'searchCollection' : 'collection';
	          payload.items.forEach(function (element, index) {
	            var placeholderId = "placeholder".concat(payload.firstItem + index);
	            var existingPlaceholderIndex = state[collectionName].findIndex(function (notification) {
	              return notification.id === placeholderId;
	            });
	            var existingMessageIndex = state[collectionName].findIndex(function (notification) {
	              return notification.id === element.id;
	            });
	            if (existingMessageIndex >= 0) {
	              state[collectionName][existingMessageIndex] = Object.assign(state[collectionName][existingMessageIndex], element);
	              state[collectionName].splice(existingPlaceholderIndex, 1);
	            } else {
	              state[collectionName].splice(existingPlaceholderIndex, 1, Object.assign({}, element));
	            }
	          });
	          state[collectionName].sort(_this3.sortByType);
	        },
	        clearPlaceholders: function clearPlaceholders(state, payload) {
	          state.collection = state.collection.filter(function (element) {
	            return !element.id.toString().startsWith('placeholder');
	          });
	          state.searchCollection = state.searchCollection.filter(function (element) {
	            return !element.id.toString().startsWith('placeholder');
	          });
	        },
	        setCounter: function setCounter(state, payload) {
	          state.unreadCounter = payload;
	        },
	        setTotal: function setTotal(state, payload) {
	          state.total = payload;
	        },
	        setSchema: function setSchema(state, payload) {
	          state.schema = payload.data;
	        }
	      };
	    } /* region Validation */
	  }, {
	    key: "validate",
	    value: function validate(fields, options) {
	      var result = {};
	      if (main_core.Type.isString(fields.id) || main_core.Type.isNumber(fields.id)) {
	        result.id = fields.id;
	      }
	      if (!main_core.Type.isNil(fields.date)) {
	        result.date = im_lib_utils.Utils.date.cast(fields.date);
	      }
	      if (main_core.Type.isString(fields.text) || main_core.Type.isNumber(fields.text)) {
	        result.text = fields.text.toString();
	        result.textConverted = NotificationsModel.decodeText(result.text);
	      }
	      if (main_core.Type.isNumber(fields.author_id)) {
	        if (fields.system === true || fields.system === 'Y') {
	          result.authorId = 0;
	        } else {
	          result.authorId = fields.author_id;
	        }
	      }
	      if (main_core.Type.isNumber(fields.userId)) {
	        result.authorId = fields.userId;
	      }
	      if (main_core.Type.isObjectLike(fields.params)) {
	        var params = this.validateParams(fields.params);
	        if (params) {
	          result.params = params;
	        }
	      }
	      if (!main_core.Type.isNil(fields.notify_buttons)) {
	        result.notifyButtons = JSON.parse(fields.notify_buttons);
	      }

	      //p&p format
	      if (!main_core.Type.isNil(fields.buttons)) {
	        result.notifyButtons = fields.buttons.map(function (button) {
	          return {
	            COMMAND: 'notifyConfirm',
	            COMMAND_PARAMS: "".concat(result.id, "|").concat(button.VALUE),
	            TEXT: "".concat(button.TITLE),
	            TYPE: 'BUTTON',
	            DISPLAY: 'LINE',
	            BG_COLOR: button.VALUE === 'Y' ? '#8bc84b' : '#ef4b57',
	            TEXT_COLOR: '#fff'
	          };
	        });
	      }
	      if (fields.notify_type === im_const.NotificationTypesCodes.confirm || fields.type === im_const.NotificationTypesCodes.confirm) {
	        result.sectionCode = im_const.NotificationTypesCodes.confirm;
	      } else if (fields.type === im_const.NotificationTypesCodes.placeholder) {
	        result.sectionCode = im_const.NotificationTypesCodes.placeholder;
	      }
	      if (!main_core.Type.isNil(fields.notify_read)) {
	        result.unread = fields.notify_read === 'N';
	      }

	      //p&p format
	      if (!main_core.Type.isNil(fields.read)) {
	        result.unread = fields.read === 'N'; //?
	      }

	      if (main_core.Type.isString(fields.setting_name)) {
	        result.settingName = fields.setting_name;
	      }

	      // rest format
	      if (main_core.Type.isString(fields.notify_title) && fields.notify_title.length > 0) {
	        result.title = fields.notify_title;
	      }

	      // p&p format
	      if (main_core.Type.isString(fields.title) && fields.title.length > 0) {
	        result.title = fields.title;
	      }
	      return result;
	    }
	  }, {
	    key: "validateParams",
	    value: function validateParams(params) {
	      var result = {};
	      try {
	        for (var field in params) {
	          if (!params.hasOwnProperty(field)) {
	            continue;
	          }
	          if (field === 'COMPONENT_ID') {
	            if (main_core.Type.isString(params[field]) && BX.Vue.isComponent(params[field])) {
	              result[field] = params[field];
	            }
	          } else if (field === 'LIKE') {
	            if (params[field] instanceof Array) {
	              result['REACTION'] = {
	                like: params[field].map(function (element) {
	                  return parseInt(element);
	                })
	              };
	            }
	          } else if (field === 'CHAT_LAST_DATE') {
	            result[field] = im_lib_utils.Utils.date.cast(params[field]);
	          } else if (field === 'AVATAR') {
	            if (params[field]) {
	              result[field] = params[field].startsWith('http') ? params[field] : options.host + params[field];
	            }
	          } else if (field === 'NAME') {
	            if (params[field]) {
	              result[field] = params[field];
	            }
	          } else {
	            result[field] = params[field];
	          }
	        }
	      } catch (e) {}
	      var hasResultElements = false;
	      for (var _field in result) {
	        if (!result.hasOwnProperty(_field)) {
	          continue;
	        }
	        hasResultElements = true;
	        break;
	      }
	      return hasResultElements ? result : null;
	    }
	    /* endregion Validation */
	    /* region Internal helpers */
	  }, {
	    key: "prepareNotification",
	    value: function prepareNotification(notification) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var result = this.validate(Object.assign({}, notification));
	      return Object.assign({}, this.getElementState(), result, options);
	    }
	  }, {
	    key: "findItemInArr",
	    value: function findItemInArr(arr, value) {
	      var key = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'id';
	      var result = {};
	      var elementIndex = arr.findIndex(function (element, index) {
	        return element[key] === value;
	      });
	      if (elementIndex !== -1) {
	        result.index = elementIndex;
	        result.element = arr[elementIndex];
	      }
	      return result;
	    }
	  }, {
	    key: "sortByType",
	    value: function sortByType(a, b) {
	      if (a.sectionCode === im_const.NotificationTypesCodes.confirm && b.sectionCode !== im_const.NotificationTypesCodes.confirm) {
	        return -1;
	      } else if (a.sectionCode !== im_const.NotificationTypesCodes.confirm && b.sectionCode === im_const.NotificationTypesCodes.confirm) {
	        return 1;
	      } else {
	        return b.id - a.id;
	      }
	    } /* endregion Internal helpers */
	  }], [{
	    key: "decodeText",
	    value: function decodeText(text) {
	      text = main_core.Text.decode(text.toString());
	      text = im_lib_utils.Utils.text.decode(text, {
	        skipImages: true
	      });
	      var Parser = main_core.Reflection.getClass('BX.Messenger.v2.Lib.Parser');
	      if (Parser) {
	        text = Parser.decodeSmileForLegacyCore(text, {
	          enableBigSmile: false
	        });
	      }
	      if (!im_lib_utils.Utils.platform.isBitrixDesktop()) {
	        text = text.replace(/<a(.*?)>(.*?)<\/a>/gi, function (whole, anchor, innerText) {
	          return "<a ".concat(anchor.replace('target="_blank"', 'target="_self"'), " class=\"bx-im-notifications-item-link\">").concat(innerText, "</a>");
	        });
	      }
	      return text;
	    }
	  }]);
	  return NotificationsModel;
	}(ui_vue_vuex.VuexBuilderModel);

	/**
	 * Bitrix Messenger
	 * Call Application model (Vuex Builder model)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var CallModel = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(CallModel, _VuexBuilderModel);
	  function CallModel() {
	    babelHelpers.classCallCheck(this, CallModel);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CallModel).apply(this, arguments));
	  }
	  babelHelpers.createClass(CallModel, [{
	    key: "getName",
	    value: function getName() {
	      return 'call';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return {
	        users: {}
	      };
	    }
	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return {
	        id: params.id ? params.id : 0,
	        state: im_const.ConferenceUserState.Idle,
	        talking: false,
	        pinned: false,
	        cameraState: false,
	        microphoneState: false,
	        screenState: false,
	        floorRequestState: false
	      };
	    }
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      var _this = this;
	      return {
	        getUser: function getUser(state) {
	          return function (userId) {
	            userId = parseInt(userId, 10);
	            if (!state.users[userId]) {
	              return _this.getElementState({
	                id: userId
	              });
	            }
	            return state.users[userId];
	          };
	        },
	        getBlankUser: function getBlankUser(state) {
	          return function (userId) {
	            userId = parseInt(userId, 10);
	            return _this.getElementState({
	              id: userId
	            });
	          };
	        }
	      };
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      var _this2 = this;
	      return {
	        updateUser: function updateUser(store, payload) {
	          payload.id = parseInt(payload.id, 10);
	          payload.fields = Object.assign({}, _this2.validate(payload.fields));
	          store.commit('updateUser', payload);
	        },
	        unpinUser: function unpinUser(store, payload) {
	          store.commit('unpinUser');
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      var _this3 = this;
	      return {
	        updateUser: function updateUser(state, payload) {
	          if (!state.users[payload.id]) {
	            ui_vue.Vue.set(state.users, payload.id, Object.assign(_this3.getElementState(), payload.fields, {
	              id: payload.id
	            }));
	          } else {
	            state.users[payload.id] = Object.assign(state.users[payload.id], payload.fields);
	          }
	        },
	        unpinUser: function unpinUser(state, payload) {
	          var pinnedUser = Object.values(state.users).find(function (user) {
	            return user.pinned === true;
	          });
	          if (pinnedUser) {
	            state.users[pinnedUser.id].pinned = false;
	          }
	        }
	      };
	    }
	  }, {
	    key: "validate",
	    value: function validate(payload) {
	      var result = {};
	      if (main_core.Type.isNumber(payload.id) || main_core.Type.isString(payload.id)) {
	        result.id = parseInt(payload.id, 10);
	      }
	      if (im_const.ConferenceUserState[payload.state]) {
	        result.state = payload.state;
	      }
	      if (main_core.Type.isBoolean(payload.talking)) {
	        result.talking = payload.talking;
	      }
	      if (main_core.Type.isBoolean(payload.pinned)) {
	        result.pinned = payload.pinned;
	      }
	      if (main_core.Type.isBoolean(payload.cameraState)) {
	        result.cameraState = payload.cameraState;
	      }
	      if (main_core.Type.isBoolean(payload.microphoneState)) {
	        result.microphoneState = payload.microphoneState;
	      }
	      if (main_core.Type.isBoolean(payload.screenState)) {
	        result.screenState = payload.screenState;
	      }
	      if (main_core.Type.isBoolean(payload.floorRequestState)) {
	        result.floorRequestState = payload.floorRequestState;
	      }
	      return result;
	    }
	  }, {
	    key: "getStateSaveException",
	    value: function getStateSaveException() {
	      return {
	        users: false
	      };
	    }
	  }]);
	  return CallModel;
	}(ui_vue_vuex.VuexBuilderModel);

	exports.ApplicationModel = ApplicationModel;
	exports.ConferenceModel = ConferenceModel;
	exports.MessagesModel = MessagesModel;
	exports.DialoguesModel = DialoguesModel;
	exports.UsersModel = UsersModel;
	exports.FilesModel = FilesModel;
	exports.RecentModel = RecentModel;
	exports.NotificationsModel = NotificationsModel;
	exports.CallModel = CallModel;

}((this.BX.Messenger.Model = this.BX.Messenger.Model || {}),BX.Messenger.Lib,BX.Event,BX.Messenger.Lib,BX,BX,BX,BX.Messenger.Const));
//# sourceMappingURL=registry.bundle.js.map
