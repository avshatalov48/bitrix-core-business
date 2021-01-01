this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core) {
	'use strict';

	function fetchEventsFromOptions(options) {
	  if (main_core.Type.isPlainObject(options)) {
	    return Object.entries(options).reduce(function (acc, _ref) {
	      var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          key = _ref2[0],
	          value = _ref2[1];

	      if (main_core.Type.isString(key) && key.startsWith('on') && main_core.Type.isFunction(value)) {
	        acc[key] = value;
	      }

	      return acc;
	    }, {});
	  }

	  return {};
	}

	exports.fetchEventsFromOptions = fetchEventsFromOptions;

}((this.BX.Landing.UI.Component = this.BX.Landing.UI.Component || {}),BX));
//# sourceMappingURL=internal.bundle.js.map
