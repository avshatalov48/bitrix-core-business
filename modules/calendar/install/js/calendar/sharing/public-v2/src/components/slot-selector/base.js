import { EventEmitter } from 'main.core.events';
import { Tag, Dom } from 'main.core';

type BaseOptions = {
	isHiddenOnStart: boolean,
};
export default class Base
{
	#wrapNode;
	#isHiddenOnStart;
	constructor(options: BaseOptions)
	{
		this.#wrapNode = null;
		this.#isHiddenOnStart = options.isHiddenOnStart;
		this.#bindEvents();
	}

	getContent(): HTMLElement
	{
		return Tag.render`
			<div></div>
		`;
	}

	getType()
	{
		return 'base';
	}

	render(): HTMLElement
	{
		Dom.append(this.getContent(), this.#getWrapNode());

		return this.#wrapNode;
	}

	#getWrapNode()
	{
		if (!this.#wrapNode)
		{
			this.#wrapNode = Tag.render`<div class="calendar-pub__slots-wrap"></div>`;
			if (this.#isHiddenOnStart)
			{
				Dom.addClass(this.#wrapNode, '--hidden');
			}
		}

		return this.#wrapNode;
	}

	#bindEvents()
	{
		EventEmitter.subscribe('selectorTypeChange', (ev) => {
			if (ev.data === this.getType())
			{
				this.#show();
			}
			else
			{
				this.#hide();
			}
		});
	}

	#hide()
	{
		Dom.addClass(this.#wrapNode, '--hidden');
	}

	#show()
	{
		Dom.removeClass(this.#wrapNode, '--hidden');
	}
}
