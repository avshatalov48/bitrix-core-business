this.BX = this.BX || {};
(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Logger class
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var Logger =
	/*#__PURE__*/
	function () {
	  function Logger() {
	    babelHelpers.classCallCheck(this, Logger);
	    this.enabled = null;
	  }

	  babelHelpers.createClass(Logger, [{
	    key: "enable",
	    value: function enable() {
	      this.enabled = true;

	      if (typeof window.localStorage !== 'undefined') {
	        try {
	          window.localStorage.setItem('bx-messenger-logger', 'enable');
	        } catch (e) {}
	      }

	      return this.enabled;
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      this.enabled = false;

	      if (typeof window.localStorage !== 'undefined') {
	        try {
	          window.localStorage.removeItem('bx-messenger-logger');
	        } catch (e) {}
	      }

	      return this.enabled;
	    }
	  }, {
	    key: "isEnabled",
	    value: function isEnabled() {
	      if (this.enabled === null) {
	        if (typeof window.localStorage !== 'undefined') {
	          try {
	            this.enabled = window.localStorage.getItem('bx-messenger-logger') === 'enable';
	          } catch (e) {}
	        }
	      }

	      return this.enabled === true;
	    }
	  }, {
	    key: "log",
	    value: function log() {
	      if (this.isEnabled()) {
	        var _console;

	        (_console = console).log.apply(_console, arguments);
	      }
	    }
	  }, {
	    key: "info",
	    value: function info() {
	      if (this.isEnabled()) {
	        var _console2;

	        (_console2 = console).info.apply(_console2, arguments);
	      }
	    }
	  }, {
	    key: "warn",
	    value: function warn() {
	      if (this.isEnabled()) {
	        var _console3;

	        (_console3 = console).warn.apply(_console3, arguments);
	      }
	    }
	  }, {
	    key: "error",
	    value: function error() {
	      if (this.isEnabled()) {
	        var _console4;

	        (_console4 = console).error.apply(_console4, arguments);
	      }
	    }
	  }]);
	  return Logger;
	}();

	var logger = new Logger();

	exports.Logger = logger;

}((this.BX.Messenger = this.BX.Messenger || {})));
//# sourceMappingURL=logger.bundle.js.map
