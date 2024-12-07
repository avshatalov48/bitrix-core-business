import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions } from 'ui.bbcode.formatter';

export class TableRowNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'tr',
			convert(): HTMLTableElement {
				return Dom.create({
					tag: 'tr',
					attrs: {
						classname: 'ui-typography-table-row',
					},
				});
			},
			...options,
		});
	}
}
