;(function() {
	"use strict";

	BX.namespace("BX.Landing.Collection");


	/**
	 * Implements interface for works with cards collection
	 *
	 * @extends {BX.Landing.Collection.BaseCollection}
	 * @constructor
	 */
	BX.Landing.Collection.CardCollection = function()
	{
		BX.Landing.Collection.BaseCollection.apply(this, arguments);
	};


	BX.Landing.Collection.CardCollection.prototype = {
		constructor: BX.Landing.Collection.CardCollection,
		__proto__: BX.Landing.Collection.BaseCollection.prototype,

		/**
		 * Gets card by node
		 * @param {HTMLElement} node
		 * @return {?BX.Landing.Block.Card}
		 */
		getByNode: function(node)
		{
			var result = null;
			this.some(function(item) {
				if (item.node === node)
				{
					result = item;
					return true;
				}
			});

			return result;
		},


		/**
		 * Gets card by node
		 * @param {string} selector
		 * @return {?BX.Landing.Block.Card}
		 */
		getBySelector: function(selector)
		{
			var result = null;
			this.some(function(item) {
				if (item.selector === selector)
				{
					result = item;
					return true;
				}
			});

			return result;
		},


		/**
		 * Adds card to collection
		 * @param {BX.Landing.Block.Card} card
		 */
		add: function(card)
		{
			if (!!card && card instanceof BX.Landing.Block.Card && !this.contains(card))
			{
				this.push(card);
			}
		},

		/**
		 * Gets nodes matching selector
		 * @param selector
		 * @return {Array|Array.<BX.Landing.Block.Node>}
		 */
		matches: function(selector)
		{
			return this.filter(function(item) { return item.node.matches(selector); });
		},


		/**
		 * Gets nodes not matching selector
		 * @param selector
		 * @return {Array|Array.<BX.Landing.Block.Node>}
		 */
		notMatches: function(selector)
		{
			return this.filter(function(item) { return !item.node.matches(selector); });
		}
	};
})();