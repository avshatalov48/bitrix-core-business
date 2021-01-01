this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_collection_basecollection) {
	'use strict';

	/**
	 * @memberOf BX.Landing.Collection
	 */

	var CardCollection = /*#__PURE__*/function (_BaseCollection) {
	  babelHelpers.inherits(CardCollection, _BaseCollection);

	  function CardCollection() {
	    babelHelpers.classCallCheck(this, CardCollection);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CardCollection).apply(this, arguments));
	  }

	  babelHelpers.createClass(CardCollection, [{
	    key: "getByNode",
	    value: function getByNode(node) {
	      return this.find(function (card) {
	        return card.node === node;
	      });
	    }
	  }, {
	    key: "getBySelector",
	    value: function getBySelector(selector) {
	      return this.find(function (card) {
	        return card.selector === selector;
	      });
	    }
	  }, {
	    key: "add",
	    value: function add(card) {
	      if (!!card && card instanceof BX.Landing.Block.Card) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(CardCollection.prototype), "add", this).call(this, card);
	      }
	    }
	  }, {
	    key: "matches",
	    value: function matches(selector) {
	      return this.filter(function (item) {
	        return item.node.matches(selector);
	      });
	    }
	  }, {
	    key: "notMatches",
	    value: function notMatches(selector) {
	      return this.filter(function (item) {
	        return !item.node.matches(selector);
	      });
	    }
	  }]);
	  return CardCollection;
	}(landing_collection_basecollection.BaseCollection);

	exports.CardCollection = CardCollection;

}((this.BX.Landing.Collection = this.BX.Landing.Collection || {}),BX.Landing.Collection));
//# sourceMappingURL=cardcollection.bundle.js.map
