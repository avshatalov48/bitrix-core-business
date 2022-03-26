this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,im_provider_rest,promise,pull_client,ui_vue,im_lib_logger,im_lib_utils,im_component_recent,im_component_dialog,im_component_textarea,pull_component_status,im_const,im_mixin,main_core_events) {
    'use strict';

    /**
     * Bitrix Im
     * Application Dialog view
     *
     * @package bitrix
     * @subpackage im
     * @copyright 2001-2020 Bitrix
     */
    ui_vue.BitrixVue.component('bx-im-application-dialog', {
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
          dialogId: 0
        };
      },
      created: function created() {
        this.dialogId = this.initialDialogId;
        main_core_events.EventEmitter.subscribe('openMessenger', this.onOpenMessenger);
      },
      beforeDestroy: function beforeDestroy() {
        main_core_events.EventEmitter.unsubscribe('openMessenger', this.onOpenMessenger);
      },
      computed: {
        DeviceType: function DeviceType() {
          return im_const.DeviceType;
        },
        isDialog: function isDialog() {
          return im_lib_utils.Utils.dialog.isChatId(this.dialogId);
        },
        isEnableGesture: function isEnableGesture() {
          return false;
        },
        isEnableGestureQuoteFromRight: function isEnableGestureQuoteFromRight() {
          return this.isEnableGesture && true;
        }
      },
      methods: {
        onOpenMessenger: function onOpenMessenger(_ref) {
          var event = _ref.data;
          this.dialogId = event.id;
        },
        logEvent: function logEvent(name) {
          for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
            params[_key - 1] = arguments[_key];
          }

          im_lib_logger.Logger.info.apply(im_lib_logger.Logger, [name].concat(params));
        }
      },
      // language=Vue
      template: "\n\t  \t<div style=\"display: flex;\">\n\t\t\t<div class=\"bx-mobilechat\">\n\t\t\t\t<div class=\"bx-mobilechat-dialog-title\">Dialog: {{dialogId}}</div>\n\t\t\t\t<bx-pull-component-status/>\n\t\t\t\t<bx-im-component-dialog\n\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t:enableGestureMenu=\"isEnableGesture\"\n\t\t\t\t\t:enableGestureQuote=\"isEnableGesture\"\n\t\t\t\t\t:enableGestureQuoteFromRight=\"isEnableGestureQuoteFromRight\"\n\t\t\t\t\t:showMessageUserName=\"isDialog\"\n\t\t\t\t\t:showMessageAvatar=\"isDialog\"\n\t\t\t\t />\n\t\t\t\t<bx-im-component-textarea\n\t\t\t\t\t:siteId=\"application.common.siteId\"\n\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t:writesEventLetter=\"3\"\n\t\t\t\t\t:enableEdit=\"true\"\n\t\t\t\t\t:enableCommand=\"false\"\n\t\t\t\t\t:enableMention=\"false\"\n\t\t\t\t\t:enableFile=\"true\"\n\t\t\t\t\t:autoFocus=\"application.device.type !== DeviceType.mobile\"\n\t\t\t\t/>\n\t\t\t</div>\n\t\t</div>\n\t"
    });

    /**
     * Bitrix Im
     * Dialog application
     *
     * @package bitrix
     * @subpackage im
     * @copyright 2001-2020 Bitrix
     */
    var DialogApplication = /*#__PURE__*/function () {
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
            // language=Vue
            template: "<bx-im-application-dialog :userId=\"userId\" :initialDialogId=\"dialogId\"/>"
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

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX.Messenger.Provider.Rest,BX,BX,BX,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger,BX.Messenger,window,window,BX.Messenger.Const,BX.Messenger.Mixin,BX.Event));
//# sourceMappingURL=dialog.bundle.js.map
