this.BX = this.BX || {};
this.BX.Lists = this.BX.Lists || {};
(function (exports,main_core) {
	'use strict';

	var LockStatus = function LockStatus(options) {
	  babelHelpers.classCallCheck(this, LockStatus);
	  options = babelHelpers.objectSpread({}, {
	    widgetContainerId: ""
	  }, options);
	  this.widgetContainer = document.getElementById(options.widgetContainerId);

	  if (main_core.Type.isDomNode(this.widgetContainer)) {
	    // eslint-ignore-next-line
	    BX.UI.Hint.init(this.widgetContainer);
	  }
	};

	exports.LockStatus = LockStatus;

}((this.BX.Lists.Widget = this.BX.Lists.Widget || {}),BX));
//# sourceMappingURL=script.js.map
