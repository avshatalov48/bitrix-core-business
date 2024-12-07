import { Dom } from 'main.core';
import type { BeforeConvertCallbackOptions } from 'ui.bbcode.formatter';
import { NodeFormatter, type NodeFormatterOptions } from 'ui.bbcode.formatter';
import type { BBCodeNode } from 'ui.bbcode.model';
import { normalizeTextNodes } from '../../helpers/normalize-text-nodes';

export class TableHeadCellNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'th',
			convert(): HTMLTableElement {
				return Dom.create({
					tag: 'th',
					attrs: {
						classname: 'ui-typography-table-cell ui-typography-table-cell-header',
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
