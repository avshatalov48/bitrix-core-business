;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements base interface wor works with buttons panel
	 *
	 * @extends {BX.Landing.UI.Panel.BasePanel}
	 *
	 * @param {?string} id
	 * @param {?string} [className] - Class name for panel layout
	 *
	 * @property {BX.Landing.UI.Collection.ButtonCollection} buttons
	 * @constructor
	 */
	BX.Landing.UI.Panel.BaseButtonPanel = function(id, className)
	{
		BX.Landing.UI.Panel.BasePanel.apply(this, arguments);
		if (className)
		{
			this.layout.classList.add(className);
		}
		this.buttons = new BX.Landing.UI.Collection.ButtonCollection();
	};


	BX.Landing.UI.Panel.BaseButtonPanel.prototype = {
		constructor: BX.Landing.UI.Panel.BaseButtonPanel,
		__proto__: BX.Landing.UI.Panel.BasePanel.prototype,


		/**
		 * Adds button into panel
		 * @param {BX.Landing.UI.Button.BaseButton} button
		 */
		addButton: function(button)
		{
			if (!!button && button instanceof BX.Landing.UI.Button.BaseButton)
			{
				if (!this.getButton(button.id))
				{
					this.buttons.push(button);
					this.appendContent(button.layout);
				}
			}
		},


		/**
		 * Adds an button to the beginning of an button collection
		 * @param {BX.Landing.UI.Button.BaseButton} button
		 */
		prependButton: function(button)
		{
			if (!!button && button instanceof BX.Landing.UI.Button.BaseButton)
			{
				if (!this.getButton(button.id))
				{
					this.buttons.unshift(button);
					this.prependContent(button.layout);
				}
			}
		},


		/**
		 * Removes button from panel
		 * @param {string} id - Button id
		 */
		removeButton: function(id)
		{
			var button = this.buttons.get(id);

			if (!!button)
			{
				this.buttons.remove(button);
				BX.remove(button.layout);
			}
		},


		/**
		 * Gets button by button id
		 * @param {string} id - Button id
		 * @return {?BX.Landing.UI.Button.BaseButton}
		 */
		getButton: function(id)
		{
			return this.buttons.get(id);
		}
	}
})();