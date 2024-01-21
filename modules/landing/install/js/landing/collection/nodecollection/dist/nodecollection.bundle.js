this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_collection_basecollection) {
	'use strict';

	/**
	 * @memberOf BX.Landing.Collection
	 */
	var NodeCollection = /*#__PURE__*/function (_BaseCollection) {
	  babelHelpers.inherits(NodeCollection, _BaseCollection);
	  function NodeCollection() {
	    babelHelpers.classCallCheck(this, NodeCollection);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NodeCollection).apply(this, arguments));
	  }
	  babelHelpers.createClass(NodeCollection, [{
	    key: "getByNode",
	    value: function getByNode(node) {
	      return this.find(function (item) {
	        return item.node === node;
	      });
	    }
	  }, {
	    key: "getBySelector",
	    value: function getBySelector(selector) {
	      return this.find(function (item) {
	        return item.selector === selector;
	      });
	    }
	  }, {
	    key: "add",
	    value: function add(node) {
	      if (!!node && node instanceof BX.Landing.Node.Base) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(NodeCollection.prototype), "add", this).call(this, node);
	      }
	    }
	  }, {
	    key: "matches",
	    value: function matches(selector) {
	      return this.filter(function (item) {
	        return item.node && selector.indexOf(':') === -1 && item.node.matches(selector);
	      });
	    }
	  }, {
	    key: "notMatches",
	    value: function notMatches(selector) {
	      return this.filter(function (item) {
	        return item.node && !item.node.matches(selector);
	      });
	    }
	  }, {
	    key: "getVisible",
	    value: function getVisible() {
	      return this.filter(function (item) {
	        return !item.hidden;
	      });
	    }
	  }]);
	  return NodeCollection;
	}(landing_collection_basecollection.BaseCollection);

	exports.NodeCollection = NodeCollection;

}((this.BX.Landing.Collection = this.BX.Landing.Collection || {}),BX.Landing.Collection));
//# sourceMappingURL=nodecollection.bundle.js.map
