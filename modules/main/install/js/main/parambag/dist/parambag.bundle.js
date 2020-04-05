(function (exports) {
	'use strict';

	var ParamBag =
	/*#__PURE__*/
	function () {
	  function ParamBag() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ParamBag);

	    if (!!params && babelHelpers.typeof(params) === 'object') {
	      this.params = new Map(Object.entries(params));
	    } else {
	      this.params = new Map();
	    }
	  }

	  babelHelpers.createClass(ParamBag, [{
	    key: "getParam",
	    value: function getParam(key) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (this.params.has(key)) {
	        return this.params.get(key);
	      }

	      return defaultValue;
	    }
	  }, {
	    key: "setParam",
	    value: function setParam(key, value) {
	      this.params.set(key, value);
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.params.clear();
	    }
	  }], [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new ParamBag(params);
	    }
	  }]);
	  return ParamBag;
	}();

	exports.ParamBag = ParamBag;

}((this.BX = this.BX || {})));
//# sourceMappingURL=parambag.bundle.js.map
