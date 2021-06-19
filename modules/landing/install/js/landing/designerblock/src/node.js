import {Dom, Event} from 'main.core';

export type NodeType = {
	element: HTMLElement,
	selector: string,
	type: ?string,
	className: ?string,
	cardSelector: ?string,
	onHover: ?() => {}
};

export class Node
{
	element: HTMLElement;
	selector: string;
	cardSelector: string;
	pseudoElement: boolean;
	onHover: ?() => {};

	constructor(options: NodeType)
	{
		this.element = options.element;
		this.selector = options.selector;
		this.cardSelector = options.cardSelector;
		this.onHover = options.onHover;
		this.pseudoElement = Dom.hasClass(this.element, 'landing-designer-block-pseudo-last');

		Event.bind(this.element, 'mouseover', this.onMouseOver.bind(this));

		if (options.className)
		{
			Dom.addClass(this.element, options.className);
		}
	}

	isPseudoElement(): boolean
	{
		return this.pseudoElement;
	}

	getSelector(): string
	{
		return (this.cardSelector ? this.cardSelector + ' ' : '') + this.selector;
	}

	getCardSelector(): string
	{
		return this.cardSelector;
	}

	getOriginalSelector(): string
	{
		return this.selector;
	}

	getElement(): HTMLElement
	{
		return this.element;
	}

	onMouseOver(event: Event)
	{
		event.stopPropagation();
		this.onHover(this);
	}
}
