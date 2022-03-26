import {Tag, Text} from 'main.core';

export type ContentTableCellOptions = {
	content: string | HTMLElement,
	head: boolean,
};

export class ContentTableCell
{
	constructor(options: ContentTableCellOptions = {})
	{
		this.options = {...options};
	}

	render(): HTMLTableCellElement
	{
		return Tag.render`
			<div class="landing-ui-content-table-cell">
				${Text.encode(this.options.content)}
			</div>
		`;
	}
}