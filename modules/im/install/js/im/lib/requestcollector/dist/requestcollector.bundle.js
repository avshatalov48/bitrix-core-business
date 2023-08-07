/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Rest Request Collector
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var RequestCollector = /*#__PURE__*/function () {
	  function RequestCollector() {
	    babelHelpers.classCallCheck(this, RequestCollector);
	    this.list = {};
	  }
	  babelHelpers.createClass(RequestCollector, [{
	    key: "register",
	    value: function register(name, xhr) {
	      this.list[name] = xhr;
	      return true;
	    }
	  }, {
	    key: "unregister",
	    value: function unregister(name) {
	      var abort = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      if (this.list[name]) {
	        if (abort) {
	          this.list[name].abort();
	        }
	        delete this.list[name];
	      }
	    }
	  }, {
	    key: "get",
	    value: function get(name) {
	      return this.list[name] ? this.list[name] : null;
	    }
	  }, {
	    key: "abort",
	    value: function abort(name) {
	      if (this.list[name]) {
	        this.list[name].abort();
	      }
	      return true;
	    }
	  }, {
	    key: "cleaner",
	    value: function cleaner() {
	      for (var name in this.list) {
	        if (this.list.hasOwnProperty(name)) {
	          this.unregister(name, true);
	        }
	      }
	    }
	  }]);
	  return RequestCollector;
	}();

	exports.RequestCollector = RequestCollector;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {})));
//# sourceMappingURL=requestcollector.bundle.js.map
