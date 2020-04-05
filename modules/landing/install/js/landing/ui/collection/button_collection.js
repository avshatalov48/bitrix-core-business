;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Collection");


	/**
	 * Implements interface for works with collection of buttons
	 *
	 * @extends {BX.Landing.Collection.BaseCollection}
	 *
	 * @constructor
	 */
	BX.Landing.UI.Collection.ButtonCollection = function()
	{
		BX.Landing.Collection.BaseCollection.apply(this, arguments);
	};


	BX.Landing.UI.Collection.ButtonCollection.prototype = {
		constructor: BX.Landing.UI.Collection.ButtonCollection,
		__proto__: BX.Landing.Collection.BaseCollection.prototype,


		/**
		 * Gets panel by id
		 * @param {string} id
		 * @return {?BX.Landing.UI.Button.BaseButton}
		 */
		get: function(id)
		{
			var result = null;
			this.some(function(item) {
				if (item.id === id)
				{
					result = item;
					return true;
				}
			});

			return result;
		},


		/**
		 * Adds panel to collection
		 * @param {BX.Landing.UI.Button.BaseButton} button
		 */
		add: function(button)
		{
			if (!!button && button instanceof BX.Landing.UI.Button.BaseButton && !this.contains(button))
			{
				this.push(button);
			}
		},


		/**
		 * Gets button by value
		 * @param {*} value
		 * @return {?BX.Landing.UI.Button.BaseButton}
		 */
		getByValue: function(value)
		{
			return this.find(function(button) {
				// noinspection EqualityComparisonWithCoercionJS
				return button.layout.value == value;
			});
		},


		/**
		 * Gets active button
		 * @return {?BX.Landing.UI.Button.BaseButton}
		 */
		getActive: function()
		{
			return this.find(function(button) {
				return button.isActive();
			});
		},


		/**
		 * Gets button bu node
		 * @param node
		 * @return {?BX.Landing.UI.Button.BaseButton}
		 */
		getByNode: function(node)
		{
			return this.find(function(button) {
				return button.layout === node;
			});
		}
	};
})();