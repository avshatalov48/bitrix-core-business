/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_controller) {
	'use strict';

	/**
	 * Bitrix Im
	 * Core application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var CoreApplication = /*#__PURE__*/function () {
	  function CoreApplication() {
	    babelHelpers.classCallCheck(this, CoreApplication);
	    this.controller = new im_controller.Controller();
	  }
	  babelHelpers.createClass(CoreApplication, [{
	    key: "ready",
	    value: function ready() {
	      return this.controller.ready();
	    }
	  }]);
	  return CoreApplication;
	}();
	var Core = new CoreApplication();

	exports.Core = Core;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger));
//# sourceMappingURL=core.bundle.js.map
