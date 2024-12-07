import { Dom } from 'main.core';
import type { BeforeConvertCallbackOptions } from 'ui.bbcode.formatter';
import { NodeFormatter, type NodeFormatterOptions } from 'ui.bbcode.formatter';
import type { BBCodeNode } from 'ui.bbcode.model';
import { normalizeTextNodes } from '../../helpers/normalize-text-nodes';

export class TableDataCellNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'td',
			convert(): HTMLTableElement {
				return Dom.create({
					tag: 'td',
					attrs: {
						classname: 'ui-typography-table-cell',
					},
				});
			},
			before({ node }: BeforeConvertCallbackOptions): BBCodeNode {
				return normalizeTextNodes(node);
			},
			...options,
		});
	}
}
