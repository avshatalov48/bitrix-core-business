/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Timer manager
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var Timer = /*#__PURE__*/function () {
	  function Timer() {
	    babelHelpers.classCallCheck(this, Timer);
	    this.list = {};
	    this.updateInterval = 1000;
	    clearInterval(this.updateIntervalId);
	    this.updateIntervalId = setInterval(this.worker.bind(this), this.updateInterval);
	  }
	  babelHelpers.createClass(Timer, [{
	    key: "start",
	    value: function start(name) {
	      var id = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'default';
	      var time = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
	      var callback = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
	      var callbackParams = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};
	      id = id == null ? 'default' : id;
	      time = parseFloat(time);
	      if (isNaN(time) || time <= 0) {
	        return false;
	      }
	      time = time * 1000;
	      if (typeof this.list[name] === 'undefined') {
	        this.list[name] = {};
	      }
	      this.list[name][id] = {
	        'dateStop': new Date().getTime() + time,
	        'callback': typeof callback === 'function' ? callback : function () {},
	        'callbackParams': callbackParams
	      };
	      return true;
	    }
	  }, {
	    key: "has",
	    value: function has(name) {
	      var id = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'default';
	      id = id == null ? 'default' : id;
	      if (id.toString().length <= 0 || typeof this.list[name] === 'undefined') {
	        return false;
	      }
	      return !!this.list[name][id];
	    }
	  }, {
	    key: "stop",
	    value: function stop(name) {
	      var id = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'default';
	      var skipCallback = arguments.length > 2 ? arguments[2] : undefined;
	      id = id == null ? 'default' : id;
	      if (id.toString().length <= 0 || typeof this.list[name] === 'undefined') {
	        return false;
	      }
	      if (!this.list[name][id]) {
	        return true;
	      }
	      if (skipCallback !== true) {
	        this.list[name][id]['callback'](id, this.list[name][id]['callbackParams']);
	      }
	      delete this.list[name][id];
	      return true;
	    }
	  }, {
	    key: "stopAll",
	    value: function stopAll(skipCallback) {
	      for (var name in this.list) {
	        if (this.list.hasOwnProperty(name)) {
	          for (var id in this.list[name]) {
	            if (this.list[name].hasOwnProperty(id)) {
	              this.stop(name, id, skipCallback);
	            }
	          }
	        }
	      }
	      return true;
	    }
	  }, {
	    key: "worker",
	    value: function worker() {
	      for (var name in this.list) {
	        if (!this.list.hasOwnProperty(name)) {
	          continue;
	        }
	        for (var id in this.list[name]) {
	          if (!this.list[name].hasOwnProperty(id) || this.list[name][id]['dateStop'] > new Date()) {
	            continue;
	          }
	          this.stop(name, id);
	        }
	      }
	      return true;
	    }
	  }, {
	    key: "clean",
	    value: function clean() {
	      clearInterval(this.updateIntervalId);
	      this.stopAll(true);
	      return true;
	    }
	  }]);
	  return Timer;
	}();

	exports.Timer = Timer;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {})));
//# sourceMappingURL=timer.bundle.js.map
