;(function() {
	"use strict";

	BX.namespace("BX.Landing.Collection");


	/**
	 * @extends {BX.Landing.Collection.BaseCollection}
	 * @constructor
	 */
	BX.Landing.Collection.BlockCollection = function()
	{
		BX.Landing.Collection.BaseCollection.apply(this, arguments);
	};


	BX.Landing.Collection.BlockCollection.prototype = {
		constructor: BX.Landing.Collection.BlockCollection,
		__proto__: BX.Landing.Collection.BaseCollection.prototype,


		/**
		 * Gets block by node
		 * @param {HTMLElement} node
		 * @return {?BX.Landing.Block}
		 */
		getByNode: function(node)
		{
			var result = null;
			this.some(function(block) {
				if (block.node === node)
				{
					result = block;
					return true;
				}
			});

			return result;
		},


		/**
		 * Gets block by child node
		 * @param {HTMLElement} child
		 * @return {?BX.Landing.Block}
		 */
		getByChildNode: function(child)
		{
			var result = null;
			this.some(function(block) {
				if (block.node.contains(child))
				{
					result = block;
					return true;
				}
			});

			return result;
		}
	};

})();