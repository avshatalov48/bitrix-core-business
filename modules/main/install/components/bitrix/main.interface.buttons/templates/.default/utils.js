;(function() {
	'use strict';

	BX.namespace('BX.Buttons');

	/**
	 * BX.Main.interfaceButtons Utils
	 * @type {{getByClass: BX.Buttons.Utils.getByClass, getByTag: BX.Buttons.Utils.getByTag, getBySelector: BX.Buttons.Utils.getBySelector}}
	 */
	BX.Buttons.Utils = {
		/**
		 * Gets elements by className
		 * @param {HTMLElement|HTMLDocument} rootElement
		 * @param {string} className
		 * @param {boolean} [all = false] Gets all elements
		 * @returns {HTMLElement|Array}
		 */
		getByClass: function(rootElement, className, all)
		{
			var result = [];

			if (className)
			{
				result = (rootElement || document.body).getElementsByClassName(className);

				if (!all)
				{
					result = result.length ? result[0] : null;
				}
				else
				{
					result = [].slice.call(result);
				}
			}

			return result;
		},

		/**
		 * Gets elements by by element tag name
		 * @param {HTMLElement|HTMLDocument} rootElement
		 * @param {string} tag
		 * @param {boolean} [all = false] Gets all elements
		 * @returns {HTMLElement|Array}
		 */
		getByTag: function(rootElement, tag, all)
		{
			var result = [];

			if (tag)
			{
				result = (rootElement || document.body).getElementsByTagName(tag);

				if (!all)
				{
					result = result.length ? result[0] : null;
				}
				else
				{
					result = [].slice.call(result);
				}
			}

			return result;
		},

		/**
		 * Gets elements by css selector
		 * @param {HTMLElement|HTMLDocument} rootElement
		 * @param {string} selector
		 * @param {boolean} [all = false] Gets all elements
		 * @returns {HTMLElement|Array}
		 */
		getBySelector: function(rootElement, selector, all)
		{
			var result = [];

			if (selector)
			{
				if (!all)
				{
					result = (rootElement || document.body).querySelector(selector);
				}
				else
				{
					result = (rootElement || document.body).querySelectorAll(selector);
					result = [].slice.call(result);
				}
			}

			return result;
		}
	}
})();