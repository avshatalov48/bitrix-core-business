;(function() {
	'use strict';

	BX.namespace('BX');


	/**
	 * Collection of resize observers
	 * @constructor
	 */
	BX.ResizeObserverCollection = function()
	{
		this.items = [];
	};


	BX.ResizeObserverCollection.prototype = {
		/**
		 * Adds item to collection
		 * @param {BX.ResizeObserver} item
		 */
		push: function(item)
		{
			this.items.push(item);
		},


		/**
		 * Implements Array.forEach
		 * @param {function} callback
		 * @param {object} [context]
		 */
		forEach: function(callback, context)
		{
			this.items.forEach(callback, context);
		},


		/**
		 * Implements Array.filter
		 * @param {function} callback
		 * @param {object} [context]
		 * @return {BX.ResizeObserver[]}
		 */
		filter: function(callback, context)
		{
			return this.items.filter(callback, context);
		},


		/**
		 * Implements Array.map
		 * @param {function} callback
		 * @param {object} [context]
		 * @return {BX.ResizeObserver[]}
		 */
		map: function(callback, context)
		{
			return this.items.map(callback, context);
		},


		/**
		 * Implements Array.some
		 * @param callback
		 * @param context
		 * @return {boolean}
		 */
		some: function(callback, context)
		{
			return this.items.some(callback, context);
		},


		/**
		 * Implements Array.every
		 * @param callback
		 * @param context
		 * @return {boolean}
		 */
		every: function(callback, context)
		{
			return this.items.every(callback, context);
		}
	};
})();