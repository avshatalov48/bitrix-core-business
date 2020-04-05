;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var isFunction = BX.Landing.Utils.isFunction;

	/**
	 * Implements interface for works with cache
	 * @constructor
	 */
	BX.Landing.Cache = function()
	{
		this.store = [];
	};

	BX.Landing.Cache.globalStore = {};


	/**
	 * Gets cached data from global storage
	 * @param {string} key - Use BX.Landing.Utils.hash function for make hash from any objects
	 * @param {*|function} [defaultValue]
	 * @return {*}
	 */
	BX.Landing.Cache.get = function(key, defaultValue)
	{
		if (key in BX.Landing.Cache.globalStore)
		{
			return BX.Landing.Cache.globalStore[key];
		}

		if (isFunction(defaultValue))
		{
			return defaultValue();
		}

		return defaultValue;
	};


	/**
	 * Sets value to global cache storage
	 * @param {string} key - Use BX.Landing.Utils.hash function for make hash from any objects
	 * @param {*} value
	 */
	BX.Landing.Cache.set = function(key, value)
	{
		BX.Landing.Cache.globalStore[key] = value;
	};


	/**
	 * Checks has key in global cache storage
	 * @param {string} key - Use BX.Landing.Utils.hash function for make hash from any objects
	 * @return {boolean}
	 */
	BX.Landing.Cache.has = function(key)
	{
		return key in BX.Landing.Cache.globalStore;
	};


	BX.Landing.Cache.prototype = {
		/**
		 * Adds entry to store
		 * @param args
		 * @param value
		 */
		add: function(args, value)
		{
			if (!this.has(args))
			{
				this.store.push(new BX.Landing.Cache.Entry(args, value));
			}
		},


		/**
		 * Sets cache entry
		 * @param args
		 * @param value
		 */
		set: function(args, value)
		{
			this.store = this.store.filter(function(entry) {
				return !entry.has(args);
			});

			this.add(args, value);
		},


		/**
		 * Checks that store has entry with args
		 * @param args
		 * @return {Boolean}
		 */
		has: function(args)
		{
			return this.store.some(function(entry) {
				return entry.has(args);
			});
		},


		/**
		 * Gets entry from cache store
		 * @param args
		 * @return {?Entry}
		 */
		get: function(args)
		{
			var entry = this.store.find(function(entry) {
				return entry.has(args);
			});

			if (entry)
			{
				return entry.value;
			}

			return null;
		},


		/**
		 * Clears store
		 */
		clear: function()
		{
			this.store = [];
		}
	};
})();