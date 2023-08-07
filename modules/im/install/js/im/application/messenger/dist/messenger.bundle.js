/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,im_controller,im_provider_rest,ui_vue,ui_vue_vuex,im_lib_utils,im_component_recent,im_component_dialog,im_component_textarea,pull_component_status,main_core_events,ui_entitySelector,im_const,im_eventHandler) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Search = /*#__PURE__*/function () {
	  function Search() {
	    var _this = this;
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Search);
	    if (babelHelpers["typeof"](params.store) === 'object' && params.store) {
	      this.store = params.store;
	    }
	    this.dialog = new BX.UI.EntitySelector.Dialog({
	      targetNode: params.targetNode,
	      enableSearch: true,
	      context: 'IM_CHAT_SEARCH',
	      multiple: false,
	      entities: [{
	        id: 'user',
	        filters: [{
	          id: 'im.userDataFilter'
	        }]
	      }, {
	        id: 'department'
	      }, {
	        id: 'im-chat',
	        options: {
	          searchableChatTypes: ['C', 'L', 'O']
	        }
	      }, {
	        id: 'im-bot',
	        options: {
	          searchableBotTypes: ['H', 'B', 'S', 'N']
	        }
	      }],
	      events: {
	        'Item:onSelect': function ItemOnSelect(event) {
	          return _this.onItemSelect(event);
	        },
	        'onLoad': function onLoad(event) {
	          return _this.fillStore(event);
	        }
	      }
	    });
	  }
	  babelHelpers.createClass(Search, [{
	    key: "onItemSelect",
	    value: function onItemSelect(event) {
	      this.dialog.deselectAll();
	      var item = event.getData().item;
	      var dialogId = this.getDialogIdByItem(item);
	      if (!dialogId) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.open, {
	        id: dialogId,
	        $event: event
	      });
	    }
	  }, {
	    key: "fillStore",
	    value: function fillStore(event) {
	      var dialog = event.getTarget();
	      var items = dialog.getItems();
	      var users = [];
	      var dialogues = [];
	      items.forEach(function (item) {
	        var customData = item.getCustomData();
	        var entityId = item.getEntityId();
	        if (entityId === 'user' || entityId === 'im-bot') {
	          var dialogId = customData.get('imUser')['ID'];
	          if (!dialogId) {
	            return;
	          }
	          users.push(_objectSpread({
	            dialogId: dialogId
	          }, customData.get('imUser')));
	        } else if (entityId === 'im-chat') {
	          var _dialogId = 'chat' + customData.get('imChat')['ID'];
	          if (!_dialogId) {
	            return;
	          }
	          dialogues.push(_objectSpread({
	            dialogId: _dialogId
	          }, customData.get('imChat')));
	        }
	      });
	      this.store.dispatch('users/set', users);
	      this.store.dispatch('dialogues/set', dialogues);
	    }
	  }, {
	    key: "getDialogIdByItem",
	    value: function getDialogIdByItem(item) {
	      switch (item.getEntityId()) {
	        case 'user':
	        case 'im-bot':
	          return item.getCustomData().get('imUser')['ID'];
	        case 'im-chat':
	          return 'chat' + item.getCustomData().get('imChat')['ID'];
	      }
	      return null;
	    }
	  }, {
	    key: "open",
	    value: function open() {
	      this.dialog.show();
	    }
	  }]);
	  return Search;
	}();

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	ui_vue.BitrixVue.component('bx-im-application-messenger', {
	  props: {
	    userId: {
	      type: Number,
	      "default": 0
	    }
	  },
	  data: function data() {
	    return {
	      selectedDialogId: 0,
	      notificationsSelected: false,
	      textareaHeight: 120
	    };
	  },
	  computed: _objectSpread$1({
	    DeviceType: function DeviceType() {
	      return im_const.DeviceType;
	    },
	    textareaHeightStyle: function textareaHeightStyle() {
	      return {
	        flex: "0 0 ".concat(this.textareaHeight, "px")
	      };
	    },
	    isDialog: function isDialog() {
	      return im_lib_utils.Utils.dialog.isChatId(this.selectedDialogId);
	    },
	    chatId: function chatId() {
	      if (this.application) {
	        return this.application.dialog.chatId;
	      }
	      return 0;
	    },
	    dialogId: function dialogId() {
	      if (this.application) {
	        return this.application.dialog.dialogId;
	      }
	      return 0;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases(['IM_DIALOG_', 'IM_UTILS_', 'IM_MESSENGER_DIALOG_', 'IM_QUOTE_'], this);
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  created: function created() {
	    this.initEventHandlers();
	    this.searchPopup = null;
	    this.subscribeToEvents();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.unsubscribeEvents();
	    this.destroyHandlers();
	  },
	  methods: {
	    // region handlers
	    initEventHandlers: function initEventHandlers() {
	      this.textareaDragHandler = this.getTextareaDragHandler();
	      this.readingHandler = new im_eventHandler.ReadingHandler(this.$Bitrix);
	      this.reactionHandler = new im_eventHandler.ReactionHandler(this.$Bitrix);
	      this.quoteHandler = new im_eventHandler.QuoteHandler(this.$Bitrix);
	      this.textareaHandler = new im_eventHandler.TextareaHandler(this.$Bitrix);
	      this.sendMessageHandler = new im_eventHandler.SendMessageHandler(this.$Bitrix);
	      this.textareaUploadHandler = new im_eventHandler.TextareaUploadHandler(this.$Bitrix);
	      this.dialogActionHandler = new im_eventHandler.DialogActionHandler(this.$Bitrix);
	    },
	    destroyHandlers: function destroyHandlers() {
	      this.textareaDragHandler.destroy();
	      this.readingHandler.destroy();
	      this.reactionHandler.destroy();
	      this.quoteHandler.destroy();
	      this.textareaHandler.destroy();
	      this.textareaUploadHandler.destroy();
	      this.dialogActionHandler.destroy();
	    },
	    getTextareaDragHandler: function getTextareaDragHandler() {
	      var _this = this,
	        _TextareaDragHandler;
	      return new im_eventHandler.TextareaDragHandler((_TextareaDragHandler = {}, babelHelpers.defineProperty(_TextareaDragHandler, im_eventHandler.TextareaDragHandler.events.onHeightChange, function (_ref) {
	        var data = _ref.data;
	        var newHeight = data.newHeight;
	        if (_this.textareaHeight !== newHeight) {
	          _this.textareaHeight = newHeight;
	        }
	      }), babelHelpers.defineProperty(_TextareaDragHandler, im_eventHandler.TextareaDragHandler.events.onStopDrag, function () {
	        main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	          chatId: _this.chatId,
	          force: true
	        });
	      }), _TextareaDragHandler));
	    },
	    // endregion handlers
	    openSearch: function openSearch() {
	      if (!this.searchPopup) {
	        this.searchPopup = new Search({
	          targetNode: document.querySelector('#bx-im-next-layout-recent-search-input'),
	          store: this.$store
	        });
	      }
	      this.searchPopup.open();
	    },
	    openMessenger: function openMessenger(dialogId) {
	      dialogId = dialogId.toString();
	      if (dialogId === 'notify') {
	        this.selectedDialogId = 0;
	        this.notificationsSelected = true;
	      } else {
	        this.selectedDialogId = dialogId;
	        this.notificationsSelected = false;
	      }
	    },
	    // region events
	    subscribeToEvents: function subscribeToEvents() {
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.open, this.onOpenMessenger);
	    },
	    unsubscribeEvents: function unsubscribeEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.open, this.onOpenMessenger);
	    },
	    onOpenMessenger: function onOpenMessenger(_ref2) {
	      var data = _ref2.data;
	      this.openMessenger(data.id);
	    },
	    onTextareaStartDrag: function onTextareaStartDrag(event) {
	      this.textareaDragHandler.onStartDrag(event, this.textareaHeight);
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.setBlur, true);
	    } // endregion events
	  },
	  // language=Vue
	  template: "\n\t  \t<div class=\"bx-im-next-layout\">\n\t\t\t<div class=\"bx-im-next-layout-recent\">\n\t\t\t\t<div class=\"bx-im-next-layout-recent-search\">\n\t\t\t\t\t<div class=\"bx-im-next-layout-recent-search-input\" id=\"bx-im-next-layout-recent-search-input\" @click=\"openSearch\">Search</div>  \n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-next-layout-recent-list\">\n\t\t\t\t\t<bx-im-component-recent/>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-next-layout-dialog\" v-if=\"selectedDialogId\">\n\t\t\t\t<div class=\"bx-im-next-layout-dialog-header\">\n\t\t\t\t\t<div class=\"bx-im-header-title\">Dialog: {{selectedDialogId}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-next-layout-dialog-messages\">\n\t\t\t\t  \t<bx-pull-component-status/>\n\t\t\t\t\t<bx-im-component-dialog\n\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t:dialogId=\"selectedDialogId\"\n\t\t\t\t\t\t:showMessageUserName=\"isDialog\"\n\t\t\t\t\t\t:showMessageAvatar=\"isDialog\"\n\t\t\t\t\t />\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-next-layout-dialog-textarea\" :style=\"textareaHeightStyle\" ref=\"textarea\">\n\t\t\t\t  \t<div class=\"bx-im-next-layout-dialog-textarea-handle\" @mousedown=\"onTextareaStartDrag\" @touchstart=\"onTextareaStartDrag\"></div>\n\t\t\t\t\t<bx-im-component-textarea\n\t\t\t\t\t\t:siteId=\"application.common.siteId\"\n\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t:dialogId=\"selectedDialogId\"\n\t\t\t\t\t\t:writesEventLetter=\"3\"\n\t\t\t\t\t\t:enableEdit=\"true\"\n\t\t\t\t\t\t:enableCommand=\"false\"\n\t\t\t\t\t\t:enableMention=\"false\"\n\t\t\t\t\t\t:enableFile=\"true\"\n\t\t\t\t\t\t:autoFocus=\"application.device.type !== DeviceType.mobile\"\n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-next-layout-notify\" v-else-if=\"notificationsSelected\">\n\t\t\t\t<bx-im-component-notifications :darkTheme=\"false\"/>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-next-layout-notify\" v-else>\n\t\t\t\t<div class=\"bx-messenger-box-hello-wrap\">\n\t\t\t\t  <div class=\"bx-messenger-box-hello\">{{ $Bitrix.Loc.getMessage('IM_M_EMPTY') }}</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix Im
	 * Messenger application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var MessengerApplication = /*#__PURE__*/function () {
	  /* region 01. Initialize */
	  function MessengerApplication() {
	    var _this = this;
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MessengerApplication);
	    babelHelpers.defineProperty(this, "inited", false);
	    babelHelpers.defineProperty(this, "initPromise", null);
	    babelHelpers.defineProperty(this, "initPromiseResolver", null);
	    babelHelpers.defineProperty(this, "vueInstance", null);
	    babelHelpers.defineProperty(this, "controller", null);
	    babelHelpers.defineProperty(this, "rootNode", null);
	    this.initPromise = new Promise(function (resolve) {
	      _this.initPromiseResolver = resolve;
	    });
	    this.params = params;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.initCore().then(function () {
	      return _this.initComponent();
	    }).then(function () {
	      return _this.initComplete();
	    });
	  }
	  babelHelpers.createClass(MessengerApplication, [{
	    key: "initCore",
	    value: function initCore() {
	      var _this2 = this;
	      return new Promise(function (resolve) {
	        im_application_core.Core.ready().then(function (controller) {
	          _this2.controller = controller;
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "initComponent",
	    value: function initComponent() {
	      var _this3 = this;
	      this.setInitialApplicationInfo();
	      this.setDialogRestHandler();
	      this.setApplicationDialogInfo();
	      return this.controller.createVue(this, {
	        el: this.rootNode,
	        data: function data() {
	          return {
	            userId: _this3.getUserId()
	          };
	        },
	        // language=Vue
	        template: "<bx-im-application-messenger :userId=\"userId\" />"
	      }).then(function (vue) {
	        _this3.vueInstance = vue;
	        return Promise.resolve();
	      });
	    }
	  }, {
	    key: "initComplete",
	    value: function initComplete() {
	      this.inited = true;
	      this.initPromiseResolver(this);
	    }
	  }, {
	    key: "ready",
	    value: function ready() {
	      if (this.inited) {
	        return Promise.resolve(this);
	      }
	      return this.initPromise;
	    }
	    /* endregion 01. Initialize */
	    /* region 02. Methods */
	  }, {
	    key: "setInitialApplicationInfo",
	    value: function setInitialApplicationInfo() {
	      this.controller.getStore().commit('application/set', {
	        dialog: {
	          dialogId: this.getDialogId()
	        },
	        options: {
	          quoteEnable: true,
	          autoplayVideo: true,
	          darkBackground: false
	        }
	      });
	    }
	  }, {
	    key: "setApplicationDialogInfo",
	    value: function setApplicationDialogInfo() {
	      var dialog = this.controller.getStore().getters['dialogues/get'](this.getDialogId());
	      if (!dialog) {
	        return false;
	      }
	      this.controller.getStore().commit('application/set', {
	        dialog: {
	          chatId: dialog.chatId,
	          diskFolderId: dialog.diskFolderId || 0
	        }
	      });
	    }
	  }, {
	    key: "setDialogRestHandler",
	    value: function setDialogRestHandler() {
	      this.controller.addRestAnswerHandler(im_provider_rest.DialogRestHandler.create({
	        store: this.controller.getStore(),
	        controller: this.controller,
	        context: this
	      }));
	    }
	  }, {
	    key: "getUserId",
	    value: function getUserId() {
	      var userId = this.params.userId || this.getLocalize('USER_ID');
	      return userId ? Number.parseInt(userId, 10) : 0;
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.params.dialogId ? this.params.dialogId.toString() : "0";
	    }
	  }, {
	    key: "getHost",
	    value: function getHost() {
	      return location.origin || '';
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return 's1';
	    }
	  }, {
	    key: "addLocalize",
	    value: function addLocalize(phrases) {
	      return this.controller.addLocalize(phrases);
	    }
	  }, {
	    key: "getLocalize",
	    value: function getLocalize(name) {
	      return this.controller.getLocalize(name);
	    } /* endregion 02. Methods */
	  }]);
	  return MessengerApplication;
	}();

	exports.MessengerApplication = MessengerApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX.Messenger,BX.Messenger.Provider.Rest,BX,BX,BX.Messenger.Lib,BX.Messenger,BX.Messenger,window,window,BX.Event,BX.UI.EntitySelector,BX.Messenger.Const,BX.Messenger.EventHandler));
//# sourceMappingURL=messenger.bundle.js.map
