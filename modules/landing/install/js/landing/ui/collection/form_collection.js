;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Collection");


	/**
	 * Implements interface for works with collection of forms
	 *
	 * @extends {BX.Landing.Collection.BaseCollection}
	 *
	 * @constructor
	 */
	BX.Landing.UI.Collection.FormCollection = function()
	{
		BX.Landing.Collection.BaseCollection.apply(this, arguments);
	};


	BX.Landing.UI.Collection.FormCollection.prototype = {
		constructor: BX.Landing.UI.Collection.FormCollection,
		__proto__: BX.Landing.Collection.BaseCollection.prototype,


		/**
		 * Fetches fields from forms in collection
		 * @return {BX.Landing.Collection.BaseCollection}
		 */
		fetchFields: function()
		{
			var collection = new BX.Landing.Collection.BaseCollection();
			this.forEach(function(item) {
				collection.push.apply(collection, item.fields);
			});

			return collection;
		},

		/**
		 * Gets changed forms
		 * @return {BX.Landing.UI.Collection.FormCollection}
		 */
		fetchChanges: function()
		{
			var collection = new BX.Landing.UI.Collection.FormCollection();
			this.forEach(function(form) {
				if (form.isChanged())
				{
					collection.push(form);
				}
			});

			return collection;
		}
	};
})();