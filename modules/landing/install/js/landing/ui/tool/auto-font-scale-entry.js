;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");

	var createRangeFromNode = BX.Landing.Utils.createRangeFromNode;
	var isNumber = BX.Landing.Utils.isNumber;
	var rect = BX.Landing.Utils.rect;


	/**
	 * Implements interface for works with adaptive fonts
	 * @param {HTMLElement} element
	 */
	BX.Landing.UI.Tool.autoFontScaleEntry = function(element)
	{
		this.element = element;
		this.minSize = 8;
		this.maxSize = parseInt(BX.style(element, "font-size"));
		this.maxLetterSpacing = parseFloat(BX.style(element, "letter-spacing"));
		this.maxLetterSpacing = isNumber(this.maxLetterSpacing) ? this.maxLetterSpacing : 0;
		this.minLetterSpacing = 0;
		this.paddings = 30;
		this.changed = false;
	};


	BX.Landing.UI.Tool.autoFontScaleEntry.prototype = {
		/**
		 * Sets font size
		 * @param {number} size
		 */
		setFontSize: function(size)
		{
			size = Math.min(Math.max(size, this.minSize), this.maxSize);
			this.element.style.setProperty('font-size', size + "px", "important");
		},


		/**
		 * Sets letter spacing value
		 * @param {number} value
		 */
		setLetterSpacing: function(value)
		{
			value = Math.min(Math.max(value, this.minLetterSpacing), this.maxLetterSpacing);
			this.element.style.setProperty('letterSpacing', value + "px");
		},


		/**
		 * Resets font size style
		 */
		resetSize: function()
		{
			this.element.style.setProperty('fontSize', null);
			this.element.style.setProperty('letterSpacing', null);
			this.element.style.setProperty('display', null);
		},


		/**
		 * Adjust font size
		 */
		adjust: function()
		{
			if (this.changed || this.getRangeWidth() > this.getParentWidth())
			{
				this.changed = true;

				var fontSize = this.getParentWidth() * this.getFontSizeRatio();
				var letterSpacing = this.getParentWidth() * this.getLetterSpacingRatio();

				this.setFontSize(fontSize - letterSpacing);
				this.setLetterSpacing(letterSpacing);
			}

			if (!this.changed && this.maxSize > 40 && BX.width(window) <= 600)
			{
				this.setFontSize(this.getParentWidth() * this.getBaseFontSizeRatio());
			}
		},


		/**
		 * Gets current font size
		 * @return {number} - pixels
		 */
		getCurrentSize: function()
		{
			return parseInt(BX.style(this.element, "font-size"));
		},


		/**
		 * Gets size ration
		 * @return {number}
		 */
		getFontSizeRatio: function()
		{
			if (isNumber(this.ratio))
			{
				return this.ratio;
			}

			this.ratio = this.maxSize / this.getRangeWidth();

			return this.ratio;
		},


		/**
		 * Gets letter spacing ratio
		 * @return {number}
		 */
		getLetterSpacingRatio: function()
		{
			if (isNumber(this.letterSpacingRatio))
			{
				return this.letterSpacingRatio;
			}

			this.letterSpacingRatio = this.maxLetterSpacing / this.getRangeWidth();

			return this.letterSpacingRatio;
		},


		/**
		 * Gets base font size ratio
		 * @return {number}
		 */
		getBaseFontSizeRatio: function()
		{
			if (isNumber(this.baseFontSizeRatio))
			{
				return this.baseFontSizeRatio;
			}

			this.baseFontSizeRatio = this.getCurrentSize() / (600 - this.paddings);

			return this.baseFontSizeRatio;
		},


		/**
		 * Gets range width
		 * @return {number}
		 */
		getRangeWidth: function()
		{
			return rect(createRangeFromNode(this.element)).width;
		},


		/**
		 * Gets parent width
		 * @return {number}
		 */
		getParentWidth: function()
		{
			return Math.min(rect(this.element).width, (BX.width(window) - this.paddings));
		}
	}
})();