;(function() {
	'use strict';

	BX.namespace('BX');


	/**
	 * Resize observer collections of items
	 * @extends {BX.ResizeObserverCollection}
	 * @constructor
	 */
	BX.ResizeObserverItemCollection = function()
	{
		BX.ResizeObserverCollection.apply(this, arguments);
	};


	BX.ResizeObserverItemCollection.prototype = {
		constructor: BX.ResizeObserverItemCollection,
		__proto__: BX.ResizeObserverCollection.prototype,


		/**
		 * Checks has instance for element
		 * @param {HTMLElement} element
		 * @return {boolean}
		 */
		hasTarget: function(element)
		{
			return this.some(function(item) {
				return item.target === element;
			});
		},


		/**
		 * Removes element from collection
		 * @param {HTMLElement} element
		 */
		removeTarget: function(element)
		{
			this.items = this.filter(function(item) {
				return item.target !== element;
			});
		},


		/**
		 * Gets active items
		 * @return {BX.ResizeObserverItem[]}
		 */
		getActive: function()
		{
			return this.filter(function(item) {
				return item.isActive();
			});
		}
	}
})();