import {Dom, Runtime, Type} from 'main.core';
import {PageObject} from 'landing.pageobject';

/**
 * Implements interface for works with highlights
 * Implements singleton pattern
 * @memberOf BX.Landing.UI
 */
export class Highlight
{
	constructor()
	{
		this.layout = Dom.create('div');
		Dom.addClass(this.layout, 'landing-highlight-border');

		Dom.style(this.layout, {
			position: 'absolute',
			border: '2px #fe541e dashed',
			top: 0,
			left: 0,
			right: 0,
			bottom: 0,
			'z-index': 9999,
			opacity: '.4',
			'pointer-events': 'none',
			transform: 'translateZ(0)',
		});
	}

	static getInstance()
	{
		if (!Highlight.instance)
		{
			Highlight.instance = new Highlight();
		}

		return Highlight.instance;
	}

	static highlightsStore = null;
	static get highlights()
	{
		if (!Highlight.highlightsStore)
		{
			Highlight.highlightsStore = new BX.Landing.Collection.BaseCollection();
		}

		return Highlight.highlightsStore;
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
				this.highlightNode(element);
			});
		}
		else if (Type.isDomNode(node))
		{
			this.highlightNode(node, rect);
		}
	}

	/**
	 * Hides highlight for all nodes
	 */
	// eslint-disable-next-line class-methods-use-this
	hide()
	{
		Highlight.highlights.forEach((item) => {
			BX.DOM.write(() => {
				Dom.remove(item.highlight);
				item.node.style.position = '';
				item.node.style.userSelect = '';
				item.node.style.cursor = '';
			});
		});

		Highlight.highlights.clear();
	}

	/**
	 * @private
	 * @param node
	 * @param {object} rect
	 */
	highlightNode(node, rect)
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

			PageObject.getInstance().view().then((frame) => {
				BX.DOM.write(() => {
					Dom.append(highlight, frame.contentDocument.body);
				});
			});
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

		Highlight.highlights.add({node, highlight});
	}
}