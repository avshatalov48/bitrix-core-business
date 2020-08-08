this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,ui_vue,ui_vue_vuex,im_view_list_recent,im_lib_logger,im_const,im_lib_utils) {
    'use strict';

    /**
     * Bitrix im
     * Sidebar vue component
     *
     * @package bitrix
     * @subpackage mobile
     * @copyright 2001-2019 Bitrix
     */
    /**
     * @notice Do not mutate or clone this component! It is under development.
     */

    ui_vue.Vue.component('bx-im-component-recent', {
      props: {
        hasDialog: false
      },
      data: function data() {
        return {};
      },
      created: function created() {},
      computed: babelHelpers.objectSpread({}, ui_vue_vuex.Vuex.mapState({
        recent: function recent(state) {
          return state.recent.collection.general;
        },
        pinned: function pinned(state) {
          return state.recent.collection.pinned;
        }
      }), {
        recentData: function recentData() {
          return [].concat(babelHelpers.toConsumableArray(this.recent), babelHelpers.toConsumableArray(this.pinned));
        }
      }),
      methods: {
        getController: function getController() {
          return this.$root.$bitrixController;
        },
        getStore: function getStore() {
          return this.$root.$bitrixController.store;
        },
        onScroll: function onScroll(event) {
          if (this.oneScreenRemaining(event)) {
            this.getController().recent.loadMore();
          }
        },
        onClick: function onClick(event) {
          if (this.hasDialog) {
            ui_vue.Vue.event.$emit('openMessenger', event);
          } else {
            this.getController().recent.openOldDialog(event);
          }
        },
        onRightClick: function onRightClick(event) {
          this.getController().recent.openOldContextMenu(event);
        },
        oneScreenRemaining: function oneScreenRemaining(event) {
          return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
        }
      },
      template: "\n\t\t<div class=\"recent-wrap\">\n\t\t\t<bx-im-view-list-recent\n\t\t\t\t:recentData=\"recentData\"\n\t\t\t\t@scroll=\"onScroll\"\n\t\t\t\t@click=\"onClick\"\n\t\t\t\t@rightClick=\"onRightClick\"\n\t\t\t/>\n\t\t</div>\n\t"
    });

    /**
     * Bitrix Im
     * Core application
     *
     * @package bitrix
     * @subpackage im
     * @copyright 2001-2020 Bitrix
     */
    var RecentApplication =
    /*#__PURE__*/
    function () {
      /* region 01. Initialize */
      function RecentApplication() {
        var _this = this;

        var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        babelHelpers.classCallCheck(this, RecentApplication);
        this.inited = false;
        this.initPromise = new BX.Promise();
        this.params = params;
        this.template = null;
        this.rootNode = this.params.node || document.createElement('div');
        this.isMessenger = params.hasDialog === true;
        this.templateTemp = null;
        this.rootNodeTemp = this.params.nodeTemp || document.createElement('div');
        this.eventBus = new ui_vue.VueVendorV2();
        this.initCore().then(function (result) {
          return _this.initParams(result);
        }).then(function () {
          return _this.initComponent();
        }).then(function () {
          return _this.initComplete();
        });
      }

      babelHelpers.createClass(RecentApplication, [{
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
        key: "initParams",
        value: function initParams(controller) {
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
      }, {
        key: "initComponent",
        value: function initComponent() {
          var _this3 = this;

          return this.controller.createVue(this, {
            el: this.rootNode,
            template: "<bx-im-component-recent :hasDialog=\"".concat(this.isMessenger, "\"/>")
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
          return this.requestData();
        }
      }, {
        key: "requestData",
        value: function requestData() {
          this.controller.recent.drawPlaceholders();
          this.controller.recent.getRecentData();
          return new Promise(function (resolve, reject) {
            return resolve();
          });
        }
      }, {
        key: "ready",
        value: function ready() {
          if (this.inited) {
            var promise = new BX.Promise();
            promise.resolve(this);
            return promise;
          }

          return this.initPromise;
        }
        /* endregion 01. Initialize */

        /* region 02. Event Bus */

      }, {
        key: "emit",
        value: function emit(eventName) {
          var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
          this.eventBus.$emit(eventName, params);
          return true;
        }
      }, {
        key: "listen",
        value: function listen(eventName, callback) {
          if (typeof callback !== 'function') {
            return false;
          }

          this.eventBus.$on(eventName, callback);
          return true;
        }
        /* endregion 02. Event Bus */

      }]);
      return RecentApplication;
    }();

    exports.RecentApplication = RecentApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX,BX,window,BX.Messenger.Lib,BX.Messenger.Const,BX.Messenger.Lib));
//# sourceMappingURL=recent.bundle.js.map
