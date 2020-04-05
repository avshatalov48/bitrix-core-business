;(function() {
	"use strict";

	BX.namespace("BX.Landing.Collection");

	var isFunction = BX.Landing.Utils.isFunction;
	var isEmpty = BX.Landing.Utils.isEmpty;

	/**
	 * Implements base interface for works with collection
	 *
	 * @extends {Array}
	 * @constructor
	 */
	BX.Landing.Collection.BaseCollection = function()
	{
		Array.apply(this, arguments);
	};


	/**
	 * Creates collection from array
	 *
	 * @param {Array} list
	 * @return {BX.Landing.Collection.BaseCollection}
	 */
	BX.Landing.Collection.BaseCollection.createFromArray = function(list)
	{
		var collection = new BX.Landing.Collection.BaseCollection();

		if (BX.type.isArray(list))
		{
			list.forEach(collection.push, collection);
		}

		return collection;
	};


	BX.Landing.Collection.BaseCollection.prototype = {
		constructor: BX.Landing.Collection.BaseCollection,
		__proto__: Array.prototype,


		/**
		 * Adds unique item
		 * @param {*} item
		 */
		add: function(item)
		{
			if (!this.contains(item))
			{
				this.push(item);
			}
		},

		fetchAttrs: function()
		{
			var collection = new BX.Landing.Collection.BaseCollection();
			this.fetchFields().forEach(function(item) {
				if (item.type === "attr")
				{
					collection.push.apply(collection, item.fields);
				}
			});

			return collection;
		},


		/**
		 * Gets item by index
		 * @param {int} index
		 * @return {*}
		 */
		getByIndex: function(index)
		{
			return this[index];
		},


		/**
		 * Removes item from collection
		 * @param {*} item
		 */
		remove: function(item)
		{
			var index = this.getIndex(item);

			if (index > -1)
			{
				this.splice(index, 1);
			}
		},


		/**
		 * Gets item index in collection
		 * @param {*} item
		 */
		getIndex: function(item)
		{
			return this.indexOf(item);
		},


		/**
		 * Checks that collection contains this item
		 * @param {*} item
		 */
		contains: function(item)
		{
			return this.getIndex(item) !== -1;
		},


		/**
		 * Checks that some item is changed
		 * @return {boolean|Boolean}
		 */
		isChanged: function()
		{
			return this.some(function(item) { return item.isChanged(); });
		},


		/**
		 * Gets nodes value
		 * @return {object}
		 */
		fetchValues: function()
		{
			var values = {};

			this.forEach(function(item) {
				if (item.selector.split("@")[1] !== "-1")
				{
					if (isFunction(item.getAttrValue))
					{
						values[item.selector] = item.getAttrValue();
					}
					else
					{
						values[item.selector] = item.getValue();
					}
				}
			});

			return values;
		},

		/**
		 * Gets nodes value
		 * @return {object}
		 */
		fetchAdditionalValues: function()
		{
			return this.reduce(function(result, item) {
				if (item.selector.split("@")[1] !== "-1" && item.getAdditionalValue)
				{
					var values = item.getAdditionalValue();

					if (!isEmpty(values))
					{
						result[item.selector] = values;
					}
				}

				return result;
			}, {});
		},


		/**
		 * Fetches changes items
		 * @return {Array.<*>}
		 */
		fetchChanges: function()
		{
			var result = new BX.Landing.Collection.BaseCollection();

			this.forEach(function(item) {
				if ("isChanged" in item && "getValue" in item && item.isChanged())
				{
					result.add(item);
				}
			});

			return result;
		},


		/**
		 * Clears this collection
		 */
		clear: function()
		{
			this.splice(0, this.length);
		},

		toArray: function()
		{
			return this.map(function(item) {
				return item;
			})
		},


		filter: function(callback, context) {
			var result = new this.constructor;
			this.forEach(function(item) {
				if (callback.apply(context, arguments))
				{
					result.push(item);
				}
			});

			return result;
		},

		/**
		 * Gets by id
		 * @param {string|number} id
		 * @return {*}
		 */
		get: function(id)
		{
			var result = null;
			this.some(function(item) {
				// noinspection EqualityComparisonWithCoercionJS
				if (item.id == id)
				{
					result = item;
					return true;
				}
			});

			return result;
		}
	};
})();