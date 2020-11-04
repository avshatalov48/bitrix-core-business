this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var defaultOptions = {
	  params: {
	    type: 'EXTERNAL'
	  }
	};

	var optionsKey = Symbol('options');
	/**
	 * @memberOf BX.Landing
	 */

	var Env = /*#__PURE__*/function () {
	  babelHelpers.createClass(Env, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      return Env.instance || Env.createInstance();
	    }
	  }, {
	    key: "createInstance",
	    value: function createInstance() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      Env.instance = new Env(options);
	      var parentEnv = main_core.Reflection.getClass('parent.BX.Landing.Env');

	      if (parentEnv) {
	        parentEnv.instance = Env.instance;
	      }

	      return Env.instance;
	    }
	  }]);

	  function Env() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Env);
	    this[optionsKey] = Object.seal(main_core.Runtime.merge(defaultOptions, options));
	  }

	  babelHelpers.createClass(Env, [{
	    key: "getOptions",
	    value: function getOptions() {
	      return babelHelpers.objectSpread({}, this[optionsKey]);
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(options) {
	      this[optionsKey] = main_core.Runtime.merge(this[optionsKey], options);
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.getOptions().params.type;
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return this.getOptions().site_id || -1;
	    }
	  }, {
	    key: "getLandingEditorUrl",
	    value: function getLandingEditorUrl() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var envOptions = this.getOptions();
	      var urlMask = envOptions.params.sef_url.landing_view;
	      var siteId = options.site ? options.site : envOptions.site_id;
	      return urlMask.replace('#site_show#', siteId).replace('#landing_edit#', options.landing);
	    }
	  }]);
	  return Env;
	}();
	babelHelpers.defineProperty(Env, "instance", null);

	exports.Env = Env;

}((this.BX.Landing = this.BX.Landing || {}),BX));
//# sourceMappingURL=env.bundle.js.map
