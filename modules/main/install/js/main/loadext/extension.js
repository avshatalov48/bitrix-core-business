;(function() {
	"use strict";

	if (
		(!BX || !!BX && typeof BX.namespace !== "function") ||
		(!!BX && !!BX.LoadExt && !!BX.LoadExt.Extension)
	)
	{
		return;
	}

	BX.namespace("BX.LoadExt");

	var STATE_SCHEDULED = "scheduled";
	var STATE_LOADED = "loaded";
	var STATE_LOAD = "load";
	var STATE_ERROR = "error";

	/**
	 * Reduces inline scripts
	 *
	 * @param {Array} accumulator
	 * @param {Object} item
	 * @return {Array.<String>}
	 */
	function inlineScriptsReducer(accumulator, item)
	{
		return (item.isInternal && accumulator.push(item.JS)), accumulator;
	}

	/**
	 * Reduces external scripts
	 *
	 * @param {Array} accumulator
	 * @param {Object} item
	 * @return {Array.<String>}
	 */
	function externalScriptsReducer(accumulator, item)
	{
		return (!item.isInternal && accumulator.push(item.JS)), accumulator;
	}

	/**
	 * Prepares result
	 *
	 * @param {String} html
	 * @return {{SCRIPT: Object[], STYLE: String[]}}
	 */
	function prepareResult(html)
	{
		return BX.type.isString(html) ? BX.processHTML(html) : {SCRIPT: [], STYLE: []};
	}

	/**
	 * Makes iterable
	 *
	 * @param {String|String[]} value
	 * @return {String[]}
	 */
	function makeIterable(value)
	{
		if (BX.type.isArray(value))
		{
			return value;
		}

		if (BX.type.isString(value))
		{
			return [value];
		}

		return [];
	}

	/**
	 * Loads all items
	 *
	 * @param {String[]|String} items
	 * @return {Promise}
	 */
	function loadAll(items)
	{
		items = makeIterable(items);

		if (!items.length)
		{
			return Promise.resolve();
		}

		return new Promise(function(resolve) {
			BX.load(items, resolve);
		}.bind(this))
	}

	/**
	 * Implements interface for works with extension
	 *
	 * @param {Object} data
	 * @return {TypeError}
	 * @constructor
	 */
	BX.LoadExt.Extension = function(data)
	{
		if (!BX.type.isPlainObject(data))
		{
			return new TypeError("data is not object");
		}

		this.name = data.extension;
		this.state = data.html ? STATE_SCHEDULED : STATE_ERROR;
		var result = prepareResult(data.html);
		this.inlineScripts = result.SCRIPT.reduce(inlineScriptsReducer, []);
		this.externalScripts = result.SCRIPT.reduce(externalScriptsReducer, []);
		this.externalStyles = result.STYLE;
	};

	BX.LoadExt.Extension.prototype = {
		/**
		 * Loads extension assets
		 *
		 * @return {Promise<BX.LoadExt.Extension>}
		 */
		load: function()
		{
			if (this.state === STATE_ERROR)
			{
				this.loadPromise = this.loadPromise || Promise.resolve(this);
				console.warn("Extension", this.name, "not found");
			}

			if (!this.loadPromise && this.state)
			{
				this.state = STATE_LOAD;
				this.inlineScripts.forEach(BX.evalGlobal);

				this.loadPromise = Promise
					.all([
						loadAll(this.externalScripts),
						loadAll(this.externalStyles)
					])
					.then(function() {
						this.state = STATE_LOADED;
						return this;
					}.bind(this));
			}

			return this.loadPromise;
		}
	}
})();