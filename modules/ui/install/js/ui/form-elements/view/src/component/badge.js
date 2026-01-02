import { Tag, Type } from 'main.core';

export type BadgeOptions = {
	text: string,
}

export class Badge
{
	#text: string;

	constructor(options: BadgeOptions)
	{
		this.#text = Type.isStringFilled(options.text) ? options.text : '';
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="ui-section__field-badge ui-counter ui-counter-md --air --style-filled-extra">
				<div class="ui-counter-inner">${this.#text}</div>
			</div>
		`;
	}
}
