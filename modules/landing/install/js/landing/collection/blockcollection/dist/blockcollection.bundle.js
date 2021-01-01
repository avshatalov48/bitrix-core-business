this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_collection_basecollection) {
	'use strict';

	/**
	 * @memberOf BX.Landing.Collection
	 */

	var BlockCollection = /*#__PURE__*/function (_BaseCollection) {
	  babelHelpers.inherits(BlockCollection, _BaseCollection);

	  function BlockCollection() {
	    babelHelpers.classCallCheck(this, BlockCollection);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BlockCollection).apply(this, arguments));
	  }

	  babelHelpers.createClass(BlockCollection, [{
	    key: "getByNode",
	    value: function getByNode(node) {
	      return this.find(function (block) {
	        return block.node === node;
	      });
	    }
	  }, {
	    key: "getByChildNode",
	    value: function getByChildNode(node) {
	      return this.find(function (block) {
	        return block.node.contains(node);
	      });
	    }
	  }]);
	  return BlockCollection;
	}(landing_collection_basecollection.BaseCollection);

	exports.BlockCollection = BlockCollection;

}((this.BX.Landing.Collection = this.BX.Landing.Collection || {}),BX.Landing.Collection));
//# sourceMappingURL=blockcollection.bundle.js.map
