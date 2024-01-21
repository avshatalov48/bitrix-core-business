import { Tag, Dom, Text, Type } from 'main.core';

export class Row
{
	#id: string;
	#node: HTMLElement;
	#content: HTMLElement;
	#separatorClass: string = '';
	#className: string;
	#isHidden: boolean

	constructor(params)
	{
		this.#id = Type.isNil(params.id) ? 'row_' + Text.getRandom(8) : params.id;
		this.#content = params.content;
		this.#separatorClass = params.separator === 'top' ? '--top-separator' : (params.separator === 'bottom' ? '--bottom-separator' : '');
		this.#className = Type.isStringFilled(params.className) ? params.className : '';
		this.#isHidden = params.isHidden === true;
	}

	render(): HTMLElement
	{
		if (this.#node)
		{
			return this.#node;
		}

		this.#node = Tag.render`
		<div class="ui-section__row ${this.#separatorClass} ${this.#className}" ${this.#isHidden ? 'hidden' : ''}>
				${this.#content}
			</div>
		`;

		return this.#node;
	}

	append(content: HTMLElement)
	{
		Dom.append(content, this.render());
	}

	renderTo(targetNode: HTMLElement): HTMLElement
	{
		if (!Type.isDomNode(targetNode))
		{
			throw new Error('Target node must be HTMLElement');
		}

		return Dom.append(this.render(), targetNode);
	}

	hide()
	{
		Dom.hide(this.render());
	}

	show()
	{
		Dom.show(this.render());
	}

	isHidden()
	{
		return this.#isHidden;
	}

	toggle()
	{
		Dom.toggle(this.render());
	}
}
