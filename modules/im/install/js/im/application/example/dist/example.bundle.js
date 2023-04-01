this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,ui_vue,im_lib_logger) {
	'use strict';

	/**
	 * Bitrix Im
	 * Core application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var ExampleApplication = /*#__PURE__*/function () {
	  /* region 01. Initialize */

	  function ExampleApplication() {
	    var _this = this;
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ExampleApplication);
	    this.inited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.template = null;
	    this.eventBus = new ui_vue.VueVendorV2();
	    im_application_core.Core.ready().then(function (result) {
	      return _this.initParams(result);
	    }).then(function () {
	      return _this.initComponent();
	    }).then(function () {
	      return _this.initComplete();
	    });
	  }
	  babelHelpers.createClass(ExampleApplication, [{
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
	        template: "<div>test2 {{store.application.common.host}}</div>",
	        computed: {
	          store: function store() {
	            return this.$store.state;
	          }
	        }
	      }).then(function (vue) {
	        _this2.template = vue;
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
	      im_lib_logger.Logger.log('Requested data!');
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
	  return ExampleApplication;
	}();

	exports.ExampleApplication = ExampleApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX,BX.Messenger.Lib));
//# sourceMappingURL=example.bundle.js.map
