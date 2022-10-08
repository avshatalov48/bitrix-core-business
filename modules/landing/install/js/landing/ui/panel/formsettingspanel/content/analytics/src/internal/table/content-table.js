import 'ui.design-tokens';

import {Tag} from 'main.core';
import {ContentTableCell} from './content-table-cell';
import {ContentTableRow} from './content-table-row';
import type {ContentTableCellOptions} from './content-table-cell';
import type {ContentTableRowOptions} from './content-table-row';

import './css/style.css';

type ContentTableOptions = {
	title: string,
	columns: Array<ContentTableCellOptions>,
	rows: Array<ContentTableRowOptions>
};

export class ContentTable
{
	constructor(options: ContentTableOptions)
	{
		this.options = {...options};
		this.headRow = new ContentTableRow({
			columns: this.options.columns.map((columnOptions) => {
				return new ContentTableCell(columnOptions);
			}),
			head: true,
		});
		this.rows = this.options.rows.map((rowOptions) => {
			return new ContentTableRow({
				columns: rowOptions.columns.map((cellOptions) => {
					return new ContentTableCell(cellOptions);
				}),
			});
		});
	}

	getTitleLayout(): HTMLDivElement | string
	{
		if (Type.isStringFilled(this.options.title))
		{
			return Tag.render`
				<div class="landing-ui-content-table-title">${this.options.title}</div>
			`;
		}

		return '';
	}

	render(): HTMLTableElement
	{
		return Tag.render`
			<div class="landing-ui-content-table-wrapper">
				
				<div class="landing-ui-content-table">
					${this.headRow.render()}
					${this.rows.map((row) => row.render())}
				</div>
			</div>
		`;
	}
}