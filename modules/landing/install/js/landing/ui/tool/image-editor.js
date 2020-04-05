;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");

	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var removeCustomEvent = BX.Landing.Utils.removeCustomEvent;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;
	var isString = BX.Landing.Utils.isString;
	var isFunction = BX.Landing.Utils.isFunction;
	var proxy = BX.Landing.Utils.proxy;


	/**
	 * Implements interface for works with Adobe WEB SDK Image editor
	 * Implements singleton design pattern. Don't use as constructor
	 * use BX.Landing.UI.Tool.ImageEditor.getInstance() for
	 * gets instance of BX.Landing.UI.Tool.ImageEditor
	 * @constructor
	 */
	BX.Landing.UI.Tool.ImageEditor = function()
	{
		this.editor = new Aviary.Feather({
			apiKey: "b365916b2f9244a6bffcae8f9993b35e",
			apiVersion: 3,
			theme: "light",
			language: this.getLang(),
			onSave: onSave.bind(null, this),
			onError: onError.bind(null, this),
			onClose: onClose.bind(null, this)
		});
	};


	/**
	 * Handles save event
	 * @param {BX.Landing.UI.Tool.ImageEditor} editor
	 * @param {?string} imageId
	 * @param {string} url
	 */
	function onSave(editor, imageId, url)
	{
		editor.editor.close();
		editor.resolver(url);

		fireCustomEvent(editor, "BX.Landing.UI.Tool.ImageEditor:save", [url]);
	}


	/**
	 * Handles error event
	 * @param {BX.Landing.UI.Tool.ImageEditor} editor
	 * @param {object} error
	 */
	function onError(editor, error)
	{
		console.error(error);
		fireCustomEvent(editor, "BX.Landing.UI.Tool.ImageEditor:error", [error]);
	}


	/**
	 * Handles editor close event
	 * @param {BX.Landing.UI.Tool.ImageEditor} editor
	 * @param {boolean} dirty - tells whether the editor was closed with unsaved changes.
	 */
	function onClose(editor, dirty)
	{
		fireCustomEvent(editor, "BX.Landing.UI.Tool.ImageEditor:close", [dirty]);
	}


	/**
	 * @param value
	 * @param paramName
	 */
	function shouldBeString(value, paramName)
	{
		if (!isString(value))
		{
			throw new TypeError(paramName + " should be a string");
		}
	}


	/**
	 * @param value
	 * @param paramName
	 */
	function shouldBeFunction(value, paramName)
	{
		if (!isFunction(value))
		{
			throw new TypeError(paramName + " should be a function");
		}
	}


	/**
	 * @param eventName
	 * @return {*}
	 */
	function prepareEventName(eventName)
	{
		if (!eventName.includes("BX.Landing.UI.Tool.ImageEditor"))
		{
			return "BX.Landing.UI.Tool.ImageEditor:" + eventName;
		}

		return eventName;
	}


	/**
	 * Gets instance of BX.Landing.UI.Tool.ImageEditor
	 * @return {BX.Landing.UI.Tool.ImageEditor}
	 */
	BX.Landing.UI.Tool.ImageEditor.getInstance = function()
	{
		return (
			BX.Landing.UI.Tool.ImageEditor.instance ||
			(BX.Landing.UI.Tool.ImageEditor.instance = new BX.Landing.UI.Tool.ImageEditor())
		);
	};



	BX.Landing.UI.Tool.ImageEditor.prototype = {
		/**
		 * Opens image editor
		 *
		 * @param {object} options
		 *
		 * More information about available options
		 * @see https://developers.aviary.com/docs/web/setup-guide#constructor-config
		 *
		 * @return {Promise}
		 */
		edit: function(options)
		{
			return new Promise(function(resolve) {
				this.resolver = resolve;
				this.editor.launch(options);
			}.bind(this));
		},


		/**
		 * Adds event handler
		 * @param {string} event
		 * @param {function} handler
		 * @param {object} [context]
		 * @throws {TypeError}
		 */
		on: function(event, handler, context)
		{
			if (!isString(event))
			{
				throw new TypeError("Event should be a string");
			}

			if (!isFunction(handler))
			{
				throw new TypeError("Handler should be a function");
			}

			event = prepareEventName(event);
			onCustomEvent(this, event, proxy(handler, context));
		},


		/**
		 * Removes event handler
		 * @param {string} event
		 * @param {function} handler
		 * @param {object} [context]
		 */
		off: function(event, handler, context)
		{
			if (!isString(event))
			{
				throw new TypeError("Event should be a string");
			}

			if (!isFunction(handler))
			{
				throw new TypeError("Handler should be a function");
			}

			event = prepareEventName(event);
			removeCustomEvent(this, event, proxy(handler, context));
		},


		/**
		 * Gets current language code
		 * @return {string}
		 */
		getLang: function()
		{
			return "en";
		}
	}
})();