this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_collection_basecollection) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Collection
	 */

	var ButtonCollection = /*#__PURE__*/function (_BaseCollection) {
	  babelHelpers.inherits(ButtonCollection, _BaseCollection);

	  function ButtonCollection() {
	    babelHelpers.classCallCheck(this, ButtonCollection);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ButtonCollection).apply(this, arguments));
	  }

	  babelHelpers.createClass(ButtonCollection, [{
	    key: "add",
	    value: function add(button) {
	      if (!!button && button instanceof BX.Landing.UI.Button.BaseButton) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(ButtonCollection.prototype), "add", this).call(this, button);
	      }
	    }
	  }, {
	    key: "getByValue",
	    value: function getByValue(value) {
	      return this.find(function (button) {
	        return "".concat(button.layout.value) === "".concat(value);
	      });
	    }
	  }, {
	    key: "getActive",
	    value: function getActive() {
	      return this.find(function (button) {
	        return button.isActive();
	      });
	    }
	  }, {
	    key: "getByNode",
	    value: function getByNode(node) {
	      return this.find(function (button) {
	        return button.layout === node;
	      });
	    }
	  }]);
	  return ButtonCollection;
	}(landing_collection_basecollection.BaseCollection);

	exports.ButtonCollection = ButtonCollection;

}((this.BX.Landing.UI.Collection = this.BX.Landing.UI.Collection || {}),BX.Landing.Collection));
//# sourceMappingURL=buttoncollection.bundle.js.map
