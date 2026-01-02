import { Dom, Runtime, Type, Text } from 'main.core';
import { PageObject } from 'landing.pageobject';

type HighlightItem = {
	node: HTMLElement,
	highlight: HTMLDivElement, // layout
}

/**
 * Implements interface for works with highlights
 * Implements singleton pattern
 * @memberOf BX.Landing.UI
 */
export class Highlight
{
	layout: HTMLDivElement;
	isSingle: boolean = false;
	localStore: ?BX.Landing.Collection.BaseCollection = null;

	static getInstance(): Highlight
	{
		if (!Highlight.instance)
		{
			Highlight.instance = new Highlight();
		}

		return Highlight.instance;
	}

	/**
	 * Collection of HighlightItem elements
	 * @type {null}
	 */
	static highlightsStore = null;

	static get highlights(): BX.Landing.Collection.BaseCollection
	{
		if (!Highlight.highlightsStore)
		{
			Highlight.highlightsStore = new BX.Landing.Collection.BaseCollection();
		}

		return Highlight.highlightsStore;
	}

	static defaultBorderColor = '#2fc6f6';
	static defaultBorderWidth = 2;
	static defaultBackground = 'rgba(47, 198, 246, .15)';

	constructor()
	{
		this.id = Text.getRandom(8);
		this.layout = Dom.create('div');

		Dom.addClass(this.layout, 'landing-highlight-border');
		Dom.style(this.layout, {
			position: 'absolute',
			top: 0,
			left: 0,
			right: 0,
			bottom: 0,
			'z-index': 9999,
			opacity: '.4',
			'pointer-events': 'none',
			transform: 'translateZ(0)',
			'background-color': 'rgba(47, 198, 246, .15)',
		});
		this.setBorder(Highlight.defaultBorderColor, Highlight.defaultBorderWidth);
		this.setBackground(Highlight.defaultBackground);
	}

	/**
	 * If true - this highlight will not depend on others.
	 * You should full control (show and hide) them! Use carefully
	 * @param flag
	 * @returns {BX.Landing.UI.Highlight}
	 */
	setSingleMode(flag: boolean = true): Highlight
	{
		if (flag)
		{
			this.localStore = new BX.Landing.Collection.BaseCollection();
		}
		this.isSingle = flag;

		return this;
	}

	/**
	 * Set border color
	 * @param {string} color - HEX string
	 * @param {number} width - border width in px, just number
	 * @returns {BX.Landing.UI.Highlight}
	 */
	setBorder(color: string, width: ?number = Highlight.defaultBorderWidth): Highlight
	{
		Dom.style(this.layout, {
			border: `${width}px ${color} dashed`,
		});

		return this;
	}

	/**
	 * @param {string} bg - any valid color string (hex, rgba etc). Remember about opacity!
	 * @returns {BX.Landing.UI.Highlight}
	 */
	setBackground(bg: string): Highlight
	{
		Dom.style(this.layout, {
			'background-color': bg,
		});

		return this;
	}

	/**
	 * Shows highlight for node
	 * @param {HTMLElement|HTMLElement[]} node
	 * @param {object} [rect]
	 */
	show(node, rect)
	{
		this.hide();
		if (Type.isArray(node))
		{
			node.forEach((element) => {
				this.#highlightNode(element);
			});
		}
		else if (Type.isDomNode(node))
		{
			this.#highlightNode(node, rect);
		}
	}

	/**
	 * @private
	 * @param node
	 * @param {object} rect
	 */
	#highlightNode(node, rect)
	{
		const highlight = Runtime.clone(this.layout);

		if (rect)
		{
			BX.DOM.write(() => {
				Dom.style(highlight, {
					position: 'fixed',
					width: `${rect.width}px`,
					height: `${rect.height}px`,
					top: `${rect.top}px`,
					left: `${rect.left}px`,
					right: `${rect.right}px`,
					bottom: `${rect.bottom}px`,
				});
			});

			const editor = PageObject.getEditorWindow();
			if (editor)
			{
				BX.DOM.write(() => {
					Dom.append(highlight, editor.document.body);
				});
			}
		}
		else
		{
			BX.DOM.write(() => {
				Dom.append(highlight, node);
			});
		}

		BX.DOM.write(() => {
			Dom.style(node, {
				position: 'relative',
				userSelect: 'none',
				cursor: 'pointer',
			});
		});

		if (this.isSingle)
		{
			this.localStore.add({node, highlight});
		}
		else
		{
			Highlight.highlights.add({node, highlight});
		}
	}

	/**
	 * Hides highlight for all nodes
	 * @param force - if true - remove highlight immediately, without requestAnimationFrame
	 */
	hide(force: boolean = false)
	{
		const store = this.isSingle ? this.localStore : Highlight.highlights;
		store.forEach((item: HighlightItem) => {
			if (force)
			{
				this.#hideNode(item);
			}
			else
			{
				BX.DOM.write(this.#hideNode.bind(this, item));
			}
		});

		store.clear();
	}

	#hideNode(item: HighlightItem)
	{
		Dom.remove(item.highlight);
		Dom.style(item.node, {
			position: '',
			userSelect: '',
			cursor: '',
		});
	}
}
