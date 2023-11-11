/* eslint-disable */
(function (exports) {
	'use strict';

	/**
	 * Bitrix integration with external Vue DevTools
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var BitrixVueDevTools = /*#__PURE__*/function () {
	  function BitrixVueDevTools(params) {
	    babelHelpers.classCallCheck(this, BitrixVueDevTools);
	    this.host = 'http://localhost';
	    this.port = '8098';
	    this.script = null;
	    this.changeToast = false;
	    if (!navigator.userAgent.toLowerCase().includes('chrome') && !navigator.userAgent.toLowerCase().includes('firefox')) {
	      this.changeToast = true;
	      console.info("Install the Vue Remote Devtools application for a better development experience: https://github.com/vuejs/vue-devtools/blob/master/shells/electron/\n" + "For connect to localhost use %cBX.VueDevTools.connect();%c for remote host %cBX.VueDevTools.connect('__devtools_ip_address__');", "font-weight: bold", "font-weight: initial", "font-weight: bold", "font-weight: initial");
	    }
	  }
	  babelHelpers.createClass(BitrixVueDevTools, [{
	    key: "connect",
	    value: function connect(address) {
	      if (this.script) {
	        document.body.removeChild(this.script);
	      }
	      if (address) {
	        this.setUrl(address);
	      }
	      window.__VUE_DEVTOOLS_HOST__ = this.host;
	      window.__VUE_DEVTOOLS_PORT__ = this.port;
	      this.script = document.createElement('script');
	      if (this.changeToast) {
	        this.script.addEventListener('load', this.load.bind(this));
	      }
	      this.script.src = __VUE_DEVTOOLS_HOST__ + ':' + __VUE_DEVTOOLS_PORT__;
	      document.body.appendChild(this.script);
	      return true;
	    }
	  }, {
	    key: "reconnect",
	    value: function reconnect() {
	      this.connect();
	    }
	  }, {
	    key: "setUrl",
	    value: function setUrl() {
	      var address = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'localhost';
	      if (!address.startsWith('http')) {
	        address = 'http://' + address;
	      }
	      var parts = address.split(':');
	      if (parts.length > 2) {
	        this.host = parts.slice(0, 2).join(':');
	        this.port = parts[2];
	      } else {
	        this.host = address;
	        this.port = '8098';
	      }
	      return this;
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      var _this = this;
	      window.__VUE_DEVTOOLS_TOAST__ = new Proxy(window.__VUE_DEVTOOLS_TOAST__, {
	        apply: function apply(target, thisArg, argumentsList) {
	          if (argumentsList[0].toString().toLowerCase().includes('disconnect')) {
	            console.info('%cDevTools:%c try to reconnect, if vue-devtools is not running, run and call %cBX.VueDevTools.reconnect();', "font-weight: bold", "font-weight: initial", "font-weight: bold");
	            setTimeout(function () {
	              return _this.reconnect();
	            }, 5000);
	          }
	          return target.apply(thisArg, argumentsList);
	        }
	      });
	    }
	  }]);
	  return BitrixVueDevTools;
	}();
	var VueDevTools = new BitrixVueDevTools();

	exports.VueDevTools = VueDevTools;

}((this.BX = this.BX || {})));
//# sourceMappingURL=devtools.bundle.js.map
