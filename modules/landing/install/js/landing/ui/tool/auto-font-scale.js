;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");

	var bind = BX.Landing.Utils.bind;
	var slice = BX.Landing.Utils.slice;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var lastWidth = BX.width(window);


	/**
	 * Checks than need adjust
	 * @return {boolean}
	 */
	function isNeedAdjust()
	{
		return BX.width(window) < 1100;
	}


	/**
	 * Checks that window resize
	 * @return {boolean}
	 */
	function isResized()
	{
		var result = lastWidth !== BX.width(window);
		lastWidth = BX.width(window);
		return result;
	}


	/**
	 * Implements interface for works with responsive texts
	 * @param {HTMLElement[]} elements
	 */
	BX.Landing.UI.Tool.autoFontScale = function(elements)
	{
		this.entries = elements.map(this.createEntry, this);
		bind(window, "resize", this.onResize.bind(this, false));
		bind(window, "orientationchange", this.onResize.bind(this, true));
		onCustomEvent("BX.Landing.Block:init", this.onAddBlock.bind(this));
		this.adjust(true);
	};


	BX.Landing.UI.Tool.autoFontScale.prototype = {
		onResize: function(forceAdjust)
		{
			this.adjust(forceAdjust);

			// Fallback for sliders
			clearTimeout(this.falbackTimeoutId);
			this.falbackTimeoutId = setTimeout(function() {
				this.adjust(true);
			}.bind(this), 250);
		},


		/**
		 * Adjusts text
		 * @param {boolean} [forceAdjust]
		 */
		adjust: function(forceAdjust)
		{
			if (forceAdjust === true || isResized())
			{
				var needAdjust = isNeedAdjust();
				this.entries.forEach(function(entry) {
					if (needAdjust)
					{
						entry.adjust();
					}
					else
					{
						entry.resetSize();
					}
				});
			}
		},


		/**
		 * Creates entry
		 * @param {HTMLElement} element
		 * @return {BX.Landing.UI.Tool.autoFontScaleEntry}
		 */
		createEntry: function(element)
		{
			return new BX.Landing.UI.Tool.autoFontScaleEntry(element);
		},


		/**
		 * Adds elements
		 * @param {HTMLElement[]} elements
		 */
		addElements: function(elements)
		{
			elements.forEach(function(element) {
				var containsElement = this.entries.some(function(entry) {
					return entry.element === element;
				});

				if (!containsElement)
				{
					this.entries.push(this.createEntry(element));
				}
			}, this);
		},


		/**
		 * Handles add block event
		 * @param {BX.Landing.Event.Block} event
		 */
		onAddBlock: function(event)
		{
			var elements = slice(event.block.querySelectorAll("h1, h2, h3, h4, h5, [data-auto-font-scale]"));
			this.addElements(elements);
		}
	}
})();