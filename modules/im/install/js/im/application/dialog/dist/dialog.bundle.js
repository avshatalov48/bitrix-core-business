this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,im_provider_rest,promise,pull_client,ui_vue,im_lib_logger,im_lib_utils) {
	'use strict';

	/**
	 * Bitrix Im
	 * Application Dialog view
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */

	ui_vue.Vue.component('bx-im-application-dialog', {
	  props: {
	    userId: {
	      default: 0
	    },
	    dialogId: {
	      default: '0'
	    }
	  },
	  data: function data() {
	    return {
	      realDialogId: 0
	    };
	  },
	  created: function created() {
	    var _this = this;

	    this.realDialogId = this.dialogId;
	    ui_vue.Vue.event.$on('openMessenger', function (data) {
	      var metaPress = data.$event.ctrlKey || data.$event.metaKey;

	      if (_this.$root.$bitrixApplication.params.place === 2) {
	        if (metaPress) {
	          _this.realDialogId = data.id;
	        }
	      } else {
	        if (!metaPress) {
	          _this.realDialogId = data.id;
	        }
	      }
	    });
	  },
	  computed: {
	    isDialog: function isDialog() {
	      return im_lib_utils.Utils.dialog.isChatId(this.realDialogId);
	    },
	    isEnableGesture: function isEnableGesture() {
	      return false;
	    },
	    isEnableGestureQuoteFromRight: function isEnableGestureQuoteFromRight() {
	      return this.isEnableGesture && true;
	    }
	  },
	  methods: {
	    logEvent: function logEvent(name) {
	      for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        params[_key - 1] = arguments[_key];
	      }

	      im_lib_logger.Logger.info.apply(im_lib_logger.Logger, [name].concat(params));
	    }
	  },
	  template: "\n\t\t<div class=\"bx-mobilechat\">\n\t\t\t<div class=\"bx-mobilechat-dialog-title\">Dialog: {{realDialogId}}</div>\n\t\t\t<bx-pull-component-status/>\n\t\t\t<bx-im-component-dialog\n\t\t\t\t:userId=\"userId\" \n\t\t\t\t:dialogId=\"realDialogId\"\n\t\t\t\t:enableGestureMenu=\"isEnableGesture\"\n\t\t\t\t:enableGestureQuote=\"isEnableGesture\"\n\t\t\t\t:enableGestureQuoteFromRight=\"isEnableGestureQuoteFromRight\"\n\t\t\t\t:showMessageUserName=\"isDialog\"\n\t\t\t\t:showMessageAvatar=\"isDialog\"\n\t\t\t\t@clickByCommand=\"logEvent('clickByCommand', $event)\"\n\t\t\t\t@clickByMention=\"logEvent('clickByMention', $event)\"\n\t\t\t\t@clickByUserName=\"logEvent('clickByUserName', $event)\"\n\t\t\t\t@clickByMessageMenu=\"logEvent('clickByMessageMenu', $event)\"\n\t\t\t\t@clickByMessageRetry=\"logEvent('clickByMessageRetry', $event)\"\n\t\t\t\t@clickByUploadCancel=\"logEvent('clickByUploadCancel', $event)\"\n\t\t\t\t@clickByReadedList=\"logEvent('clickByReadedList', $event)\"\n\t\t\t\t@setMessageReaction=\"logEvent('setMessageReaction', $event)\"\n\t\t\t\t@openMessageReactionList=\"logEvent('openMessageReactionList', $event)\"\n\t\t\t\t@clickByKeyboardButton=\"logEvent('clickByKeyboardButton', $event)\"\n\t\t\t\t@clickByChatTeaser=\"logEvent('clickByChatTeaser', $event)\"\n\t\t\t\t@click=\"logEvent('click', $event)\"\n\t\t\t />\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix Im
	 * Dialog application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var DialogApplication =
	/*#__PURE__*/
	function () {
	  /* region 01. Initialize */
	  function DialogApplication() {
	    var _this = this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, DialogApplication);
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

	  babelHelpers.createClass(DialogApplication, [{
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
	        template: "<bx-im-application-dialog :userId=\"userId\" :dialogId=\"dialogId\"/>"
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
	  return DialogApplication;
	}();

	exports.DialogApplication = DialogApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX.Messenger.Provider.Rest,BX,BX,BX,BX.Messenger.Lib,BX.Messenger.Lib));
//# sourceMappingURL=dialog.bundle.js.map
