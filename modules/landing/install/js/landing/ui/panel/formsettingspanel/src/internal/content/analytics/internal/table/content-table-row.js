import {Tag} from 'main.core';
import type {ContentTableCell} from './content-table-cell';

export type ContentTableRowOptions = {
	columns: Array<ContentTableCell>,
	head?: boolean,
};

export class ContentTableRow
{
	constructor(options: ContentTableRowOptions)
	{
		this.options = {...options};
	}

	render(): HTMLTableRowElement
	{
		const headClass = this.options.head ? ' landing-ui-content-table-row-head' : '';

		return Tag.render`
			<div class="landing-ui-content-table-row${headClass}">
				${this.options.columns.map((cell) => cell.render())}
			</div>
		`;
	}
}