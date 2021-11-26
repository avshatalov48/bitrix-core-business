this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,main_core) {
	'use strict';

	var Url = /*#__PURE__*/function () {
	  function Url() {
	    babelHelpers.classCallCheck(this, Url);
	  }

	  babelHelpers.createClass(Url, null, [{
	    key: "getCurrentUrl",
	    value: function getCurrentUrl() {
	      return window.location.protocol + "//" + window.location.hostname + (window.location.port != '' ? ':' + window.location.port : '') + window.location.pathname + window.location.search;
	    }
	  }, {
	    key: "addLinkParam",
	    value: function addLinkParam(link, name, value) {
	      if (!link.length) {
	        return '?' + name + '=' + value;
	      }

	      link = main_core.Uri.removeParam(link, name);

	      if (link.indexOf('?') != -1) {
	        return link + '&' + name + '=' + value;
	      }

	      return link + '?' + name + '=' + value;
	    }
	  }]);
	  return Url;
	}();

	var Pool = /*#__PURE__*/function () {
	  function Pool() {
	    babelHelpers.classCallCheck(this, Pool);
	    this.pool = {};
	  }

	  babelHelpers.createClass(Pool, [{
	    key: "add",
	    value: function add(cmd, index, fields) {
	      if (!this.pool.hasOwnProperty(index)) {
	        this.pool[index] = [];
	      }

	      this.pool[index].push(babelHelpers.defineProperty({}, cmd, {
	        fields: fields
	      }));
	    }
	  }, {
	    key: "get",
	    value: function get() {
	      return this.pool;
	    }
	  }, {
	    key: "clean",
	    value: function clean() {
	      this.pool = {};
	    }
	  }, {
	    key: "isEmpty",
	    value: function isEmpty() {
	      return Object.keys(this.pool).length === 0;
	    }
	  }]);
	  return Pool;
	}();

	var Timer = /*#__PURE__*/function () {
	  function Timer() {
	    babelHelpers.classCallCheck(this, Timer);
	    this.list = [];
	  }

	  babelHelpers.createClass(Timer, [{
	    key: "add",
	    value: function add(fields) {
	      if (!fields.hasOwnProperty('index')) {
	        return false;
	      }

	      this.list[fields.index] = {
	        id: fields.id
	      };
	    }
	  }, {
	    key: "get",
	    value: function get(index) {
	      if (!this.list[index] || this.list[index].length <= 0) {
	        return {};
	      }

	      return this.list[index];
	    }
	  }, {
	    key: "delete",
	    value: function _delete(fields) {
	      this.list.splice(fields.index, 1);
	    }
	  }, {
	    key: "clean",
	    value: function clean(fields) {
	      var timer = this.get(fields.index);
	      clearTimeout(timer.id);
	      this.delete({
	        index: fields.index
	      });
	    }
	  }, {
	    key: "create",
	    value: function create(time) {
	      var index = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'default';
	      var callback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      this.clean({
	        index: index
	      });
	      index = index == null ? 'default' : index;
	      callback = typeof callback === 'function' ? callback : function () {};
	      var timer = setTimeout(callback, time);
	      var item = {
	        id: timer,
	        index: index
	      };
	      this.add(item);
	    }
	  }, {
	    key: "isEmpty",
	    value: function isEmpty() {
	      return this.list.length === 0;
	    }
	  }]);
	  return Timer;
	}();

	var Basket = /*#__PURE__*/function () {
	  function Basket() {
	    babelHelpers.classCallCheck(this, Basket);
	  }

	  babelHelpers.createClass(Basket, null, [{
	    key: "toFixed",
	    value: function toFixed(quantity, measureRatio) {
	      var availableQuantity = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	      var precisionFactor = Math.pow(10, 6);
	      var reminder = (quantity / measureRatio - (quantity / measureRatio).toFixed(0)).toFixed(5),
	          remain;

	      if (parseFloat(reminder) === 0) {
	        return quantity;
	      }

	      if (measureRatio !== 0 && measureRatio !== 1) {
	        remain = quantity * precisionFactor % (measureRatio * precisionFactor) / precisionFactor;

	        if (measureRatio > 0 && remain > 0) {
	          if (remain >= measureRatio / 2 && (availableQuantity === 0 || quantity + measureRatio - remain <= availableQuantity)) {
	            quantity += (measureRatio * precisionFactor - remain * precisionFactor) / precisionFactor;
	          } else {
	            quantity = (quantity * precisionFactor - remain * precisionFactor) / precisionFactor;
	          }
	        }
	      }

	      return quantity;
	    } // isRatioFloat(value)
	    // {
	    // 	return parseInt(value) !== parseFloat(value)
	    // }

	  }, {
	    key: "isValueFloat",
	    value: function isValueFloat(value) {
	      return parseInt(value) !== parseFloat(value);
	    }
	  }, {
	    key: "roundValue",
	    value: function roundValue(value) {
	      if (Basket.isValueFloat(value)) {
	        return Basket.roundFloatValue(value);
	      } else {
	        return parseInt(value, 10);
	      }
	    }
	  }, {
	    key: "roundFloatValue",
	    value: function roundFloatValue(value) {
	      var precision = 6;
	      var precisionFactor = Math.pow(10, precision);
	      return Math.round(parseFloat(value) * precisionFactor) / precisionFactor;
	    }
	  }]);
	  return Basket;
	}();

	var History = /*#__PURE__*/function () {
	  function History(options) {
	    babelHelpers.classCallCheck(this, History);
	    this.location = options.location;
	    this.params = options.params;
	  }

	  babelHelpers.createClass(History, [{
	    key: "build",
	    value: function build() {
	      var path = this.location;
	      var params = this.params;

	      try {
	        for (var name in params) {
	          if (!params.hasOwnProperty(name)) {
	            continue;
	          }

	          path = Url.addLinkParam(path, name, params[name]);
	        }
	      } catch (e) {}

	      return path;
	    }
	  }], [{
	    key: "pushState",
	    value: function pushState(location, params) {
	      var url = new History({
	        location: location,
	        params: params
	      }).build();
	      window.history.pushState(null, null, url);
	      return url;
	    }
	  }]);
	  return History;
	}();

	exports.Url = Url;
	exports.Pool = Pool;
	exports.Timer = Timer;
	exports.Basket = Basket;
	exports.History = History;

}((this.BX.Sale.Checkout.Lib = this.BX.Sale.Checkout.Lib || {}),BX));
//# sourceMappingURL=registry.bundle.js.map
