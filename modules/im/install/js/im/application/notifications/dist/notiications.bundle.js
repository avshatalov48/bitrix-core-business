this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,ui_vue,ui_vue_vuex,im_lib_logger,im_const,im_lib_utils) {
	'use strict';

	/**
	 * Bitrix im
	 * Notifications vue component
	 *
	 * @package bitrix
	 * @subpackage mobile
	 * @copyright 2001-2019 Bitrix
	 */
	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */

	ui_vue.Vue.component('bx-im-component-notifications', {
	  props: {},
	  data: function data() {
	    return {};
	  },
	  created: function created() {},
	  computed: {},
	  methods: {},
	  template: "\n\t\t<div>\n\t\tBX-IM-COMPONENT-NOTIFICATIONS\n<!--\t\t\t<bx-im-view-list-recent-->\n<!--\t\t\t\t:recentData=\"recentData\"-->\n<!--\t\t\t\t@scroll=\"onScroll\"-->\n<!--\t\t\t\t@click=\"onClick\"-->\n<!--\t\t\t\t@rightClick=\"onRightClick\"-->\n<!--\t\t\t/>-->\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix Im
	 * Core application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var NotificationsApplication =
	/*#__PURE__*/
	function () {
	  /* region 01. Initialize */
	  function NotificationsApplication() {
	    var _this = this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, NotificationsApplication);
	    this.inited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    this.template = null;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.templateTemp = null;
	    this.eventBus = new ui_vue.VueVendorV2();
	    this.initCore().then(function () {
	      return _this.initParams();
	    }).then(function () {
	      return _this.initComponent();
	    }).then(function () {
	      return _this.initComplete();
	    });
	  }

	  babelHelpers.createClass(NotificationsApplication, [{
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
	        template: "<bx-im-component-notifications/>"
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
	  return NotificationsApplication;
	}();

	exports.NotificationsApplication = NotificationsApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX,BX,BX.Messenger.Lib,BX.Messenger.Const,BX.Messenger.Lib));
//# sourceMappingURL=notiications.bundle.js.map
