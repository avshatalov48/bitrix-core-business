import { Tag, Dom, Text, Type } from 'main.core';

export class SeparatorRow
{
	#id: string;
	#isHidden: boolean;
	#node: HTMLElement;
	#className: string;

	constructor(params)
	{
		params = Type.isNil(params) ? {} : params;
		this.#isHidden = params.isHidden === true;
		this.#id = Type.isNil(params.id) ? 'row_' + Text.getRandom(8) : params.id;
		this.#className = Type.isStringFilled(params.className) ? params.className : '';
	}

	render(): HTMLElement
	{
		if (this.#node)
		{
			return this.#node;
		}

		this.#node = Tag.render`
			<div class="ui-section__separator-row ${this.#className}" ${this.#isHidden ? 'hidden' : ''}></div>
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

	toggle()
	{
		Dom.toggle(this.render());
	}
}
