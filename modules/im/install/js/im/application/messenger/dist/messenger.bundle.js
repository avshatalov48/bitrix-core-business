this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,im_provider_rest,promise,ui_vue,im_lib_logger,im_lib_utils,ui_entitySelector,im_component_recent,im_component_dialog,im_component_textarea,pull_component_status,im_const,im_mixin,main_core_events,main_core) {
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

	      main_core_events.EventEmitter.emit('openMessenger', {
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

	/**
	 * Bitrix Im
	 * Application Messenger view
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	ui_vue.BitrixVue.component('bx-im-application-messenger', {
	  props: {
	    userId: {
	      "default": 0
	    },
	    initialDialogId: {
	      "default": '0'
	    }
	  },
	  mixins: [im_mixin.DialogCore, im_mixin.DialogReadMessages, im_mixin.DialogQuoteMessage, im_mixin.DialogClickOnCommand, im_mixin.DialogClickOnMention, im_mixin.DialogClickOnUserName, im_mixin.DialogClickOnMessageMenu, im_mixin.DialogClickOnMessageRetry, im_mixin.DialogClickOnUploadCancel, im_mixin.DialogClickOnReadList, im_mixin.DialogSetMessageReaction, im_mixin.DialogOpenMessageReactionList, im_mixin.DialogClickOnKeyboardButton, im_mixin.DialogClickOnChatTeaser, im_mixin.DialogClickOnDialog, im_mixin.TextareaCore, im_mixin.TextareaUploadFile],
	  data: function data() {
	    return {
	      dialogId: 0,
	      notify: false,
	      textareaDrag: false,
	      textareaHeight: 120,
	      textareaMinimumHeight: 120,
	      textareaMaximumHeight: im_lib_utils.Utils.device.isMobile() ? 200 : 400,
	      search: null
	    };
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe('openMessenger', this.onOpenMessenger);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe('openMessenger', this.onOpenMessenger);
	    this.onTextareaDragEventRemove();
	  },
	  computed: {
	    DeviceType: function DeviceType() {
	      return im_const.DeviceType;
	    },
	    textareaHeightStyle: function textareaHeightStyle(state) {
	      return {
	        flex: '0 0 ' + this.textareaHeight + 'px'
	      };
	    },
	    isDialog: function isDialog() {
	      return im_lib_utils.Utils.dialog.isChatId(this.dialogId);
	    },
	    isEnableGesture: function isEnableGesture() {
	      return false;
	    },
	    isEnableGestureQuoteFromRight: function isEnableGestureQuoteFromRight() {
	      return this.isEnableGesture && true;
	    },
	    localizeEmptyChat: function localizeEmptyChat() {
	      return main_core.Loc.getMessage('IM_M_EMPTY');
	    }
	  },
	  methods: {
	    openSearch: function openSearch() {
	      if (!this.search) {
	        this.search = new Search({
	          targetNode: document.getElementById('bx-im-next-layout-recent-search-input'),
	          store: this.$store
	        });
	      }

	      this.search.open();
	    },
	    openMessenger: function openMessenger(dialogId) {
	      dialogId = dialogId.toString();

	      if (dialogId === 'notify') {
	        this.dialogId = 0;
	        this.notify = true;
	      } else {
	        this.notify = false;
	        this.dialogId = dialogId;
	      }
	    },
	    onOpenMessenger: function onOpenMessenger(_ref) {
	      var event = _ref.data;
	      this.openMessenger(event.id);
	    },
	    onTextareaStartDrag: function onTextareaStartDrag(event) {
	      if (this.textareaDrag) {
	        return;
	      }

	      im_lib_logger.Logger.log('Livechat: textarea drag started');
	      this.textareaDrag = true;
	      event = event.changedTouches ? event.changedTouches[0] : event;
	      this.textareaDragCursorStartPoint = event.clientY;
	      this.textareaDragHeightStartPoint = this.textareaHeight;
	      this.onTextareaDragEventAdd();
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.setBlur, true);
	    },
	    onTextareaContinueDrag: function onTextareaContinueDrag(event) {
	      if (!this.textareaDrag) {
	        return;
	      }

	      event = event.changedTouches ? event.changedTouches[0] : event;
	      this.textareaDragCursorControlPoint = event.clientY;
	      var textareaHeight = Math.max(Math.min(this.textareaDragHeightStartPoint + this.textareaDragCursorStartPoint - this.textareaDragCursorControlPoint, this.textareaMaximumHeight), this.textareaMinimumHeight);
	      im_lib_logger.Logger.log('Livechat: textarea drag', 'new: ' + textareaHeight, 'curr: ' + this.textareaHeight);

	      if (this.textareaHeight !== textareaHeight) {
	        this.textareaHeight = textareaHeight;
	      }
	    },
	    onTextareaStopDrag: function onTextareaStopDrag() {
	      if (!this.textareaDrag) {
	        return;
	      }

	      im_lib_logger.Logger.log('Livechat: textarea drag ended');
	      this.textareaDrag = false;
	      this.onTextareaDragEventRemove();
	      this.$store.commit('widget/common', {
	        textareaHeight: this.textareaHeight
	      });
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	        chatId: this.chatId,
	        force: true
	      });
	    },
	    onTextareaDragEventAdd: function onTextareaDragEventAdd() {
	      document.addEventListener('mousemove', this.onTextareaContinueDrag);
	      document.addEventListener('touchmove', this.onTextareaContinueDrag);
	      document.addEventListener('touchend', this.onTextareaStopDrag);
	      document.addEventListener('mouseup', this.onTextareaStopDrag);
	      document.addEventListener('mouseleave', this.onTextareaStopDrag);
	    },
	    onTextareaDragEventRemove: function onTextareaDragEventRemove() {
	      document.removeEventListener('mousemove', this.onTextareaContinueDrag);
	      document.removeEventListener('touchmove', this.onTextareaContinueDrag);
	      document.removeEventListener('touchend', this.onTextareaStopDrag);
	      document.removeEventListener('mouseup', this.onTextareaStopDrag);
	      document.removeEventListener('mouseleave', this.onTextareaStopDrag);
	    },
	    logEvent: function logEvent(name) {
	      for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        params[_key - 1] = arguments[_key];
	      }

	      im_lib_logger.Logger.info.apply(im_lib_logger.Logger, [name].concat(params));
	    }
	  },
	  // language=Vue
	  template: "\n\t  \t<div class=\"bx-im-next-layout\">\n\t\t\t<div class=\"bx-im-next-layout-recent\">\n\t\t\t\t<div class=\"bx-im-next-layout-recent-search\">\n\t\t\t\t\t<div class=\"bx-im-next-layout-recent-search-input\" id=\"bx-im-next-layout-recent-search-input\" @click=\"openSearch\">Search</div>  \n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-next-layout-recent-list\">\n\t\t\t\t\t<bx-im-component-recent/>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-next-layout-dialog\" v-if=\"dialogId\">\n\t\t\t\t<div class=\"bx-im-next-layout-dialog-header\">\n\t\t\t\t\t<div class=\"bx-im-header-title\">Dialog: {{dialogId}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-next-layout-dialog-messages\">\n\t\t\t\t  \t<bx-pull-component-status/>\n\t\t\t\t\t<bx-im-component-dialog\n\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t:enableGestureMenu=\"isEnableGesture\"\n\t\t\t\t\t\t:enableGestureQuote=\"isEnableGesture\"\n\t\t\t\t\t\t:enableGestureQuoteFromRight=\"isEnableGestureQuoteFromRight\"\n\t\t\t\t\t\t:showMessageUserName=\"isDialog\"\n\t\t\t\t\t\t:showMessageAvatar=\"isDialog\"\n\t\t\t\t\t />\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-next-layout-dialog-textarea\" :style=\"textareaHeightStyle\" ref=\"textarea\">\n\t\t\t\t  \t<div class=\"bx-im-next-layout-dialog-textarea-handle\" @mousedown=\"onTextareaStartDrag\" @touchstart=\"onTextareaStartDrag\"></div>\n\t\t\t\t\t<bx-im-component-textarea\n\t\t\t\t\t\t:siteId=\"application.common.siteId\"\n\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t:writesEventLetter=\"3\"\n\t\t\t\t\t\t:enableEdit=\"true\"\n\t\t\t\t\t\t:enableCommand=\"false\"\n\t\t\t\t\t\t:enableMention=\"false\"\n\t\t\t\t\t\t:enableFile=\"true\"\n\t\t\t\t\t\t:autoFocus=\"application.device.type !== DeviceType.mobile\"\n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-next-layout-notify\" v-else-if=\"notify\">\n\t\t\t\t<bx-im-component-notifications :darkTheme=\"false\"/>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-next-layout-notify\" v-else>\n\t\t\t\t<div class=\"bx-messenger-box-hello-wrap\">\n\t\t\t\t  <div class=\"bx-messenger-box-hello\">{{localizeEmptyChat}}</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\n\t\t</div>\n\t"
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
	    this.inited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    this.template = null;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.event = new ui_vue.VueVendorV2();
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

	      return new Promise(function (resolve, reject) {
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

	      console.log('2. initComponent');
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
	      this.controller.addRestAnswerHandler(im_provider_rest.DialogRestHandler.create({
	        store: this.controller.getStore(),
	        controller: this.controller,
	        context: this
	      }));
	      var dialog = this.controller.getStore().getters['dialogues/get'](this.controller.application.getDialogId());

	      if (dialog) {
	        this.controller.getStore().commit('application/set', {
	          dialog: {
	            chatId: dialog.chatId,
	            diskFolderId: dialog.diskFolderId || 0
	          }
	        });
	      }

	      return this.controller.createVue(this, {
	        el: this.rootNode,
	        data: function data() {
	          return {
	            userId: _this3.getUserId(),
	            dialogId: _this3.getDialogId()
	          };
	        },
	        // language=Vue
	        template: "<bx-im-application-messenger :userId=\"userId\" :initialDialogId=\"dialogId\"/>"
	      }).then(function (vue) {
	        _this3.template = vue;
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "initComplete",
	    value: function initComplete() {
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
	    /* endregion 01. Initialize */

	    /* region 02. Methods */

	  }, {
	    key: "getUserId",
	    value: function getUserId() {
	      var userId = this.params.userId || this.getLocalize('USER_ID');
	      return userId ? parseInt(userId) : 0;
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
	    /* endregion 03. Utils */

	  }]);
	  return MessengerApplication;
	}();

	exports.MessengerApplication = MessengerApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX.Messenger.Provider.Rest,BX,BX,BX.Messenger.Lib,BX.Messenger.Lib,BX.UI.EntitySelector,BX.Messenger,BX.Messenger,window,window,BX.Messenger.Const,BX.Messenger.Mixin,BX.Event,BX));
//# sourceMappingURL=messenger.bundle.js.map
