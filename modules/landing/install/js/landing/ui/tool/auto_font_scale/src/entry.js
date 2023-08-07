import {Dom, Type} from 'main.core';

export default class Entry
{
	static STEP_SIZE_PERCENTS = 10;

	static MIN_SIZE = 12;
	static MIN_LINE_HEIGHT = 1.4;
	static MIN_LETTER_SPACING = 0;

	static WIDTH_RESET_CLASS = 'scroll-width-reset';

	element: HTMLElement;

	startSize: number;
	currentSize: number;

	letterSpacingRatio: number;
	currentLetterSpacing: number;

	startLineHeight: number;
	currentLineHeight: number;

	prevWidth: number;
	maxHeight: number = 0;

	intervalId: number;

	constructor(element: HTMLElement)
	{
		this.element = element;

		Entry.MIN_SIZE = 8;
		this.startSize = parseInt(Dom.style(element, "font-size"));
		this.currentSize = this.startSize;

		this.letterSpacingRatio = Dom.style(element, "letter-spacing");
		this.letterSpacingRatio = parseFloat(this.letterSpacingRatio) || 0;
		this.letterSpacingRatio /= this.startSize;
		this.currentLetterSpacing = Entry.MIN_LETTER_SPACING;

		this.startLineHeight = parseFloat(Dom.style(element, "line-height"));
		this.startLineHeight = Type.isNumber(this.startLineHeight)
			? (this.startLineHeight / this.startSize).toFixed(1)
			: Entry.MIN_LINE_HEIGHT;
		this.currentLineHeight = this.startLineHeight;

		this.calcCurrentWidth();
		this.calcMaxHeight();
	}

	/**
	 * Calculate max height by parent element
	 */
	calcMaxHeight()
	{
		this.maxHeight = document.documentElement.clientHeight * 0.9;
		if (this.element.offsetParent)
		{
			// todo: need check parent height if it has, f.e. min-height: 75vh?
			this.maxHeight = Math.min(this.maxHeight, this.element.offsetParent.clientHeight);
		}
	}

	calcCurrentWidth()
	{
		Dom.addClass(this.element, Entry.WIDTH_RESET_CLASS);
		this.prevWidth = this.element.clientWidth;
		Dom.removeClass(this.element, Entry.WIDTH_RESET_CLASS);
	}

	/**
	 * Resets font size style
	 */
	resetSize()
	{
		this.element.style.setProperty('font-size', null);
		this.element.style.setProperty('letter-spacing', null);
		this.element.style.setProperty('line-height', null);

		this.currentSize = this.startSize;
		this.currentLineHeight = this.startLineHeight;
		this.currentLetterSpacing = this.letterSpacingRatio * this.startSize;
	}

	/**
	 * Check needed and adjust
	 */
	adjust()
	{
		this.calcMaxHeight();

		if (this.isNeedDecrease())
		{
			this.decreaseSize();
		}
		else if (this.isNeedIncrease())
		{
			this.increaseSize();
		}
	}

	/**
	 * Check if need decrease size
	 * @return {boolean}
	 */
	isNeedDecrease(): boolean
	{
		const fullScrollWidth = this.element.scrollWidth;
		Dom.addClass(this.element, Entry.WIDTH_RESET_CLASS);
		const res = (
			(
				fullScrollWidth > this.element.clientWidth
				|| this.element.offsetHeight > this.maxHeight
			)
			&& this.currentSize > Entry.MIN_SIZE
		);
		Dom.removeClass(this.element, Entry.WIDTH_RESET_CLASS);

		return res;
	}

	/**
	 * Decrease size step-by-step, until text is big
	 */
	decreaseSize(): void
	{
		let newSize = this.currentSize - (this.currentSize * Entry.STEP_SIZE_PERCENTS / 100);
		newSize = Math.floor(newSize);
		newSize = Math.max(Entry.MIN_SIZE, newSize);

		if (this.currentSize !== newSize)
		{
			if (!this.intervalId)
			{
				this.intervalId = setInterval(() =>
				{
					if (this.isNeedDecrease())
					{
						this.decreaseSize();
					}
					else
					{
						this.finishAdjust();
					}
				});
			}

			this.setFontSize(newSize);
		}


	}

	/**
	 * Check if need increase size
	 * @return {boolean}
	 */
	isNeedIncrease(): boolean
	{
		if (this.isNeedDecrease())
		{
			return false;
		}

		Dom.addClass(this.element, Entry.WIDTH_RESET_CLASS);
		const res = (
			this.element.clientWidth > this.prevWidth
			&& (this.element.clientWidth - this.prevWidth) > (this.element.clientWidth * Entry.STEP_SIZE_PERCENTS / 100)
			&& this.currentSize < this.startSize
		);
		Dom.removeClass(this.element, Entry.WIDTH_RESET_CLASS);

		return res;
	}

	/**
	 * Increase size step-by-step, until text is small
	 */
	increaseSize(): void
	{
		let newSize = this.currentSize + (this.currentSize * Entry.STEP_SIZE_PERCENTS / (100 - Entry.STEP_SIZE_PERCENTS));
		newSize = Math.ceil(newSize);
		newSize = Math.min(this.startSize, newSize);

		if (this.currentSize !== newSize)
		{
			if (!this.intervalId)
			{
				this.intervalId = setInterval(() =>
				{
					if (this.isNeedIncrease())
					{
						this.increaseSize();
					}
					else if (this.isNeedDecrease())
					{
						// one step correction if size big
						this.decreaseSize();
						this.finishAdjust();
					}
					else
					{
						this.finishAdjust();
					}
				});
			}

			this.setFontSize(newSize);
		}
	}

	/**
	 * Stop increase or decrease processing
	 */
	finishAdjust()
	{
		clearInterval(this.intervalId);
		this.intervalId = null;
		this.calcCurrentWidth();
	}

	/**
	 * Set (if needed) new font size, calculate and set new letter spacing and line height
	 * @param size
	 */
	setFontSize(size: number)
	{
		if (
			size !== this.currentSize
			&& size <= this.startSize
		)
		{
			this.currentSize = size;
			this.element.style.setProperty('font-size', this.currentSize + "px", "important");

			// LINE HEIGHT correction
			if (this.startLineHeight > Entry.MIN_LINE_HEIGHT)
			{
				let newLineHeight =
					Entry.MIN_LINE_HEIGHT
					+ ((this.startLineHeight - Entry.MIN_LINE_HEIGHT) * size / this.startSize)
				;
				newLineHeight = newLineHeight.toFixed(1);
				newLineHeight = Math.max(newLineHeight, Entry.MIN_LINE_HEIGHT);

				if (
					newLineHeight <= this.startLineHeight
					&& newLineHeight !== this.currentLineHeight
				)
				{
					this.element.style.setProperty('line-height', newLineHeight, "important");
					this.currentLineHeight = newLineHeight;
				}
			}

			// LETTER SPACING correction
			if (this.letterSpacingRatio > Entry.MIN_LETTER_SPACING)
			{
				let newLetterSpacing = this.letterSpacingRatio * size;
				newLetterSpacing = Math.round(newLetterSpacing);

				if (newLetterSpacing !== this.currentLetterSpacing)
				{
					this.element.style.setProperty('letter-spacing', newLetterSpacing + 'px', "important");
					this.currentLetterSpacing = newLetterSpacing;
				}
			}
		}
	}
}