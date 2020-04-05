;(function() {
	"use strict";

	if (
		(!BX || !!BX && typeof BX.namespace !== "function") ||
		(!!BX && typeof BX.loadExt === "function")
	)
	{
		return;
	}

	BX.namespace("BX");

	var CONTROLLER = "main.bitrix.main.controller.loadext.getextensions";
	var RESPONSE_STATUS_SUCCESS = "success";

	/**
	 * @type {Object.<String, BX.LoadExt.Extension>}
	 */
	var initialized = {};

	/**
	 * @typedef {Object} runActionResponse
	 * @property {string} status
	 * @property {Object[]} data
	 * @property {Object[]} errors
	 */
	/**
	 * Makes request
	 *
	 * @param {Object} data
	 * @return {Promise<runActionResponse>}
	 */
	function request(data)
	{
		return new Promise(function(resolve) {
			BX.ajax
				.runAction(CONTROLLER, {
					data: data
				})
				.then(resolve);
		});
	}

	/**
	 * @typedef {Array.<BX.LoadExt.Extension>} extensionsCollection
	 */
	/**
	 * Prepares extensions
	 *
	 * @param {runActionResponse} response
	 * @return {extensionsCollection}
	 */
	function prepareExtensions(response)
	{
		if (response.status !== RESPONSE_STATUS_SUCCESS)
		{
			response.errors.map(console.warn);
			return [];
		}

		return response.data.map(function(item) {
			return (
				getInitialized(item.extension) ||
				(initialized[item.extension] = new BX.LoadExt.Extension(item))
			);
		});
	}

	/**
	 * Loads extensions
	 * @param extensions
	 * @return {Promise<extensionsCollection>}
	 */
	function loadExtensions(extensions)
	{
		return Promise.all(
			extensions.map(function(item) {
				return item.load();
			})
		);
	}

	/**
	 * Gets initialized extension
	 *
	 * @param {string} extensionName
	 * @return {?BX.LoadExt.Extension}
	 */
	function getInitialized(extensionName)
	{
		return initialized[extensionName];
	}

	/**
	 * Checks that this extension is initialized
	 * @param {String} extensionName
	 * @return {boolean}
	 */
	function isInitialized(extensionName)
	{
		return extensionName in initialized;
	}

	/**
	 * Makes iterable
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
	 * Loads extension
	 *
	 * @param {String|String[]} extension - Extension name
	 * if you want to include it before load the extension
	 *
	 * @example
	 *
	 * BX.loadExt("main.loader").then(function() {
	 *     // Use extension here
	 *	   // var loader = new BX.Loader();
	 *	   // ...
	 * });
	 *
	 * @return {Promise<extensionsCollection>}
	 */
	BX.loadExt = function(extension)
	{
		extension = makeIterable(extension);

		var isAllInitialized = extension.every(isInitialized);

		if (isAllInitialized)
		{
			var initializedExtensions = extension.map(getInitialized);
			return loadExtensions(initializedExtensions);
		}

		return request({extension: extension})
			.then(prepareExtensions)
			.then(loadExtensions);
	};
})();