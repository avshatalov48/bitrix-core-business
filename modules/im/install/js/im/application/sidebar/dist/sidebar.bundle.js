this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,ui_vue,ui_vue_vuex) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */
	ui_vue.BitrixVue.component('bx-im-component-sidebar', {
	  data: function data() {
	    return {};
	  },
	  created: function created() {},
	  computed: _objectSpread(_objectSpread({}, ui_vue_vuex.Vuex.mapState({
	    recent: function recent(state) {
	      return state.recent.collection.general;
	    },
	    pinned: function pinned(state) {
	      return state.recent.collection.pinned;
	    }
	  })), {}, {
	    recentData: function recentData() {
	      return [].concat(babelHelpers.toConsumableArray(this.recent), babelHelpers.toConsumableArray(this.pinned));
	    }
	  }),
	  methods: {
	    getController: function getController() {
	      return this.$Bitrix.Data.get('controller');
	    },
	    getStore: function getStore() {
	      return this.getController().store;
	    },
	    onScroll: function onScroll(event) {
	      if (this.oneScreenRemaining(event)) {
	        this.getController().recent.loadMore();
	      }
	    },
	    onClick: function onClick(event) {
	      this.getController().recent.openOldDialog(event);
	    },
	    onRightClick: function onRightClick(event) {
	      this.getController().recent.openOldContextMenu(event);
	    },
	    oneScreenRemaining: function oneScreenRemaining(event) {
	      return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
	    }
	  },
	  template: "\n\t\t\t<div class=\"sidebar-wrap\">\n\t\t\t\t<bx-im-view-list-sidebar\n\t\t\t\t\t:recentData=\"recentData\"\n\t\t\t\t\t@scroll=\"onScroll\"\n\t\t\t\t\t@click=\"onClick\"\n\t\t\t\t\t@rightClick=\"onRightClick\"\n\t\t\t\t/>\n\t\t\t</div>\n\t\t"
	});

	/**
	 * Bitrix Im
	 * Core application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var SidebarApplication = /*#__PURE__*/function () {
	  /* region 01. Initialize */

	  function SidebarApplication() {
	    var _this = this;
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, SidebarApplication);
	    this.inited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    this.template = null;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.templateTemp = null;
	    this.rootNodeTemp = this.params.nodeTemp || document.createElement('div');
	    this.eventBus = new ui_vue.VueVendorV2();
	    this.initCore().then(function () {
	      return _this.initParams();
	    }).then(function () {
	      return _this.initComponent();
	    }).then(function () {
	      return _this.initComplete();
	    });
	  }
	  babelHelpers.createClass(SidebarApplication, [{
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
	    value: function initParams() {
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
	        template: "<bx-im-component-sidebar/>"
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
	    } /* endregion 02. Event Bus */
	  }]);
	  return SidebarApplication;
	}();

	exports.SidebarApplication = SidebarApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX,BX));
//# sourceMappingURL=sidebar.bundle.js.map
