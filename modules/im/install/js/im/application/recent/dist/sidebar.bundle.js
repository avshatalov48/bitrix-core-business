this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,ui_vue,ui_vue_vuex,im_tools_logger,im_const,im_utils) {
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

	ui_vue.Vue.component('bx-messenger-sidebar', {
	  data: function data() {
	    return {};
	  },
	  created: function created() {},
	  computed: babelHelpers.objectSpread({}, ui_vue_vuex.Vuex.mapState({
	    recent: function recent(state) {
	      return state.recent.collection;
	    }
	  })),
	  methods: {},
	  template: "\n\t\t<div>\n\t\t\t<bx-messenger-list-sidebar\n\t\t\t\t:recentData=\"recent\"\n\t\t\t/>\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix Im
	 * Core application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var SidebarApplication =
	/*#__PURE__*/
	function () {
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
	    im_application_core.Core.ready().then(function (result) {
	      return _this.initParams(result);
	    }).then(function () {
	      return _this.initComponent();
	    }).then(function () {
	      return _this.initComplete();
	    });
	  }

	  babelHelpers.createClass(SidebarApplication, [{
	    key: "initParams",
	    value: function initParams(controller) {
	      this.controller = controller;
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initComponent",
	    value: function initComponent() {
	      var _this2 = this;

	      return this.controller.createVue(this, {
	        el: this.rootNode,
	        template: "<bx-messenger-sidebar/>"
	      }).then(function (vue) {
	        _this2.template = vue;
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      }); // .then(() => this.controller.createVue(this, {
	      // 	el: this.rootNodeTemp,
	      // 	template: `<bx-messenger-list-recent/>`,
	      // }))
	      // .then(vue => {
	      // 	this.templateTemp = vue;
	      // 	return new Promise((resolve, reject) => resolve());
	      // });
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
	  return SidebarApplication;
	}();

	exports.SidebarApplication = SidebarApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX,BX,BX.Messenger,BX.Messenger.Const,BX.Messenger));
//# sourceMappingURL=sidebar.bundle.js.map
