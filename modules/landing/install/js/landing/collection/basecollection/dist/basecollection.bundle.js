this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @memberOf BX.Landing.Collection
	 */

	var BaseCollection = /*#__PURE__*/function (_Array) {
	  babelHelpers.inherits(BaseCollection, _Array);

	  function BaseCollection() {
	    babelHelpers.classCallCheck(this, BaseCollection);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseCollection).apply(this, arguments));
	  }

	  babelHelpers.createClass(BaseCollection, [{
	    key: "add",
	    value: function add(value) {
	      if (!this.includes(value)) {
	        this.push(value);
	      }
	    }
	  }, {
	    key: "remove",
	    value: function remove(value) {
	      var index = this.getIndex(value);

	      if (index > -1) {
	        this.splice(index, 1);
	      }
	    }
	  }, {
	    key: "getIndex",
	    value: function getIndex(value) {
	      return this.indexOf(value);
	    }
	    /**
	     * @deprecated
	     * @see this.includes()
	     */

	  }, {
	    key: "contains",
	    value: function contains(value) {
	      return this.includes(value);
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      return this.some(function (item) {
	        return item.isChanged();
	      });
	    }
	  }, {
	    key: "fetchValues",
	    value: function fetchValues() {
	      return this.reduce(function (acc, item) {
	        if (!item.selector.startsWith('-1')) {
	          if (main_core.Type.isFunction(item.getAttrValue)) {
	            acc[item.selector] = item.getAttrValue();
	          } else {
	            acc[item.selector] = item.getValue();
	          }
	        }

	        return acc;
	      }, {});
	    }
	  }, {
	    key: "fetchAdditionalValues",
	    value: function fetchAdditionalValues() {
	      return this.reduce(function (acc, item) {
	        if (!item.selector.startsWith('-1') && item.getAdditionalValue) {
	          var values = item.getAdditionalValue();

	          if (!main_core.Type.isNil(values)) {
	            acc[item.selector] = values;
	          }
	        }

	        return acc;
	      }, {});
	    }
	  }, {
	    key: "fetchChanges",
	    value: function fetchChanges() {
	      return this.filter(function (item) {
	        return 'isChanged' in item && 'getValue' in item && item.isChanged();
	      });
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.splice(0, this.length);
	    }
	  }, {
	    key: "toArray",
	    value: function toArray() {
	      return babelHelpers.toConsumableArray(this);
	    }
	  }, {
	    key: "get",
	    value: function get(id) {
	      return this.find(function (item) {
	        return "".concat(item.id) === "".concat(id);
	      });
	    }
	  }, {
	    key: "getByLayout",
	    value: function getByLayout(layout) {
	      return this.find(function (item) {
	        return main_core.Type.isObject(item) && item.layout === layout;
	      });
	    }
	  }]);
	  return BaseCollection;
	}( /*#__PURE__*/babelHelpers.wrapNativeSuper(Array));

	exports.BaseCollection = BaseCollection;

}((this.BX.Landing.Collection = this.BX.Landing.Collection || {}),BX));
//# sourceMappingURL=basecollection.bundle.js.map
