;(function() {
	'use strict';

	BX.namespace('BX');


	/**
	 * Implements extended ResizeObserverEntry interface
	 * @see https://wicg.github.io/ResizeObserver/#resize-observer-entry-interface
	 *
	 * Includes additional method isActive from ResizeObservation
	 * @see https://wicg.github.io/ResizeObserver/#resize-observation-interface
	 *
	 * @param {HTMLElement} target
	 *
	 * @property {HTMLElement} target
	 * @property {BX.ResizeObserverItemRect} contentRect
	 *
	 * @constructor
	 */
	BX.ResizeObserverItem = function(target)
	{
		this.target = target;
		this.contentRect = BX.ResizeObserverItemRect.createFromElement(target);
	};



	BX.ResizeObserverItem.prototype = {
		/**
		 * Implements method from ResizeObservation object
		 * @see https://wicg.github.io/ResizeObserver/#resize-observation-interface
		 *
		 * @return {boolean}
		 */
		isActive: function()
		{
			var currentRect = BX.ResizeObserverItemRect.createFromElement(this.target);
			var isActive = false;

			if (this.contentRect.width !== currentRect.width ||
				this.contentRect.height !== currentRect.height)
			{
				isActive = true;
				this.contentRect = currentRect;
			}

			return isActive;
		}
	}
})();