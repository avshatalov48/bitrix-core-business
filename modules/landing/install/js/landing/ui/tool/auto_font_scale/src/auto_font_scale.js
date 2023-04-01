import {Type, Runtime} from 'main.core';
import Entry from './entry';

const bind = BX.Landing.Utils.bind;
const slice = BX.Landing.Utils.slice;
const onCustomEvent = BX.Landing.Utils.onCustomEvent;
let lastWidth = BX.width(window);

export class AutoFontScale
{
	static WIDTH_LIMIT: number = 768;

	/**
	 * Checks than need adjust
	 * @return {boolean}
	 */
	static isNeedAdjust()
	{
		return BX.width(window) <= AutoFontScale.WIDTH_LIMIT;
	}

	/**
	 * Checks that window resize
	 * @return {boolean}
	 */
	static isResized()
	{
		const result = lastWidth !== BX.width(window);
		lastWidth = BX.width(window);

		return result;
	}

	/**
	 * Implements interface for works with responsive texts
	 * @param {HTMLElement[]} elements
	 */
	constructor(elements: [HTMLElement])
	{
		this.entries = elements.map(this.createEntry, this);

		this.onResize = Runtime.debounce(this.onResize, 500);
		bind(window, "resize", this.onResize.bind(this, false));
		bind(window, "orientationchange", this.onResize.bind(this, true));

		onCustomEvent("BX.Landing.Block:init", this.onAddBlock.bind(this));
		this.adjust(true);
	}

	onResize(forceAdjust)
	{
		this.adjust(forceAdjust);
	}

	/**
	 * Adjusts text
	 * @param {boolean} [forceAdjust]
	 */
	adjust(forceAdjust)
	{
		if (forceAdjust === true || AutoFontScale.isResized())
		{
			const needAdjust = AutoFontScale.isNeedAdjust();
			this.entries.forEach(entry =>
			{
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
	}

	/**
	 * Creates entry
	 * @param {HTMLElement} element
	 * @return {Entry}
	 */
	createEntry(element: HTMLElement)
	{
		return new Entry(element);
	}

	/**
	 * Adds elements
	 * @param {HTMLElement[]} elements
	 */
	addElements(elements)
	{
		elements.forEach(element => {
			const containsElement = this.entries.some(entry =>
			{
				return entry.element === element;
			});

			if (!containsElement)
			{
				this.entries.push(this.createEntry(element));
			}
		}, this);
	}

	/**
	 * Handles add block event
	 * @param {BX.Landing.Event.Block} event
	 */
	onAddBlock(event)
	{
		const elements = slice(event.block.querySelectorAll("h1, h2, h3, h4, h5, [data-auto-font-scale]"));
		this.addElements(elements);
	}
}