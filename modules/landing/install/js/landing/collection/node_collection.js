;(function() {
	"use strict";

	BX.namespace("BX.Landing.Collection");


	/**
	 * Implements interface for works with nodes collection
	 *
	 * @extends {BX.Landing.Collection.BaseCollection}
	 * @constructor
	 */
	BX.Landing.Collection.NodeCollection = function()
	{
		BX.Landing.Collection.BaseCollection.apply(this, arguments);
	};


	BX.Landing.Collection.NodeCollection.prototype = {
		constructor: BX.Landing.Collection.NodeCollection,
		__proto__: BX.Landing.Collection.BaseCollection.prototype,


		/**
		 * Gets node by node
		 * @param {HTMLElement} node
		 * @return {?BX.Landing.Block.Node}
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
		 * Gets node by node
		 * @param {string} selector
		 * @return {?BX.Landing.Block.Node}
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
		 * Adds node to collection
		 * @param {BX.Landing.Block.Node} node
		 */
		add: function(node)
		{
			if (!!node && node instanceof BX.Landing.Block.Node && !this.contains(node))
			{
				this.push(node);
			}
		},


		/**
		 * Gets nodes matching selector
		 * @param selector
		 * @return {Array|Array.<BX.Landing.Block.Node>}
		 */
		matches: function(selector)
		{
			var result = new BX.Landing.Collection.NodeCollection();
			this.forEach(function(item) {
				if (item.node.matches(selector))
				{
					result.push(item);
				}
			});

			return result;
		},


		/**
		 * Gets nodes not matching selector
		 * @param selector
		 * @return {BX.Landing.Collection.NodeCollection.<BX.Landing.Block.Node>}
		 */
		notMatches: function(selector)
		{
			var result = new BX.Landing.Collection.NodeCollection();
			this.forEach(function(item) {
				if (!item.node.matches(selector))
				{
					result.push(item);
				}
			});

			return result;
		}
	};
})();