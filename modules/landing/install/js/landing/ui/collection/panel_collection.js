;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Collection");


	/**
	 * Implements interface for works with collection of panels
	 *
	 * @extends {BX.Landing.Collection.BaseCollection}
	 *
	 * @constructor
	 */
	BX.Landing.UI.Collection.PanelCollection = function()
	{
		BX.Landing.Collection.BaseCollection.apply(this, arguments);
	};


	BX.Landing.UI.Collection.PanelCollection.prototype = {
		constructor: BX.Landing.UI.Collection.PanelCollection,
		__proto__: BX.Landing.Collection.BaseCollection.prototype,


		/**
		 * Gets panel by id
		 * @param {string} id
		 * @return {?BX.Landing.UI.Panel.BasePanel}
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
		 * @param {BX.Landing.UI.Panel.BasePanel} panel
		 */
		add: function(panel)
		{
			if (!!panel && panel instanceof BX.Landing.UI.Panel.BasePanel && !this.contains(panel))
			{
				this.push(panel);
			}
		}
	};
})();