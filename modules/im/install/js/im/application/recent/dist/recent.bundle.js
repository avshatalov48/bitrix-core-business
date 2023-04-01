this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,ui_vue) {
	'use strict';

	/**
	 * Bitrix Im
	 * Core application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var RecentApplication = /*#__PURE__*/function () {
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
	  return RecentApplication;
	}();

	exports.RecentApplication = RecentApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX));
//# sourceMappingURL=recent.bundle.js.map
