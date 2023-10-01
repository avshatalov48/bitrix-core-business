/* eslint-disable */
(function (exports) {
	'use strict';

	const BizProcActivity = window.BizProcActivity;
	var _checkFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkFields");
	class DelayActivity extends BizProcActivity {
	  constructor() {
	    super();
	    Object.defineProperty(this, _checkFields, {
	      value: _checkFields2
	    });
	    this.Type = 'DelayActivity';
	    this.CheckFields = babelHelpers.classPrivateFieldLooseBase(this, _checkFields)[_checkFields].bind(this);
	  }
	}
	function _checkFields2() {
	  return !!this.Properties.TimeoutDuration || !!this.Properties.TimeoutTime;
	}

	exports.DelayActivity = DelayActivity;

}((this.window = this.window || {})));
//# sourceMappingURL=delayactivity.js.map
