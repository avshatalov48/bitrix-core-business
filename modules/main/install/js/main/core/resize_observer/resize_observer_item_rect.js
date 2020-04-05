;(function() {
	'use strict';

	BX.namespace('BX');


	/**
	 * Custom ClientRect object
	 * @param {ClientRect} clientRect
	 *
	 * @property {int} bottom
	 * @property {int} height
	 * @property {int} left
	 * @property {int} right
	 * @property {int} top
	 * @property {int} width
	 *
	 * @constructor
	 */
	BX.ResizeObserverItemRect = function(clientRect)
	{
		this.bottom = Math.ceil(clientRect.bottom);
		this.height = Math.ceil(clientRect.height);
		this.left = Math.ceil(clientRect.left);
		this.right = Math.ceil(clientRect.right);
		this.top = Math.ceil(clientRect.top);
		this.width = Math.ceil(clientRect.width);
	};


	/**
	 * Creates from html element
	 * @param {HTMLElement} element
	 * @return {BX.ResizeObserverItemRect}
	 */
	BX.ResizeObserverItemRect.createFromElement = function(element)
	{
		return new BX.ResizeObserverItemRect(element.getBoundingClientRect());
	};


	BX.ResizeObserverItemRect.prototype = {
		/**
		 * Converts this object to string
		 * @return {string}
		 */
		toString: function()
		{
			return JSON.stringify(this);
		}
	};
})();