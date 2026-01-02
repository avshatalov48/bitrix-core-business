import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';
import { type BBCodeNode } from 'ui.bbcode.model';

export class ListItemNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions)
	{
		super({
			name: '*',
			convert({ node }: ConvertCallbackOptions): HTMLLIElement {
				const nested = node.getChildren().some((child) => child.getName() === 'list');

				return Dom.create({
					tag: 'li',
					attrs: {
						...node.getAttributes(),
						className: `ui-typography-li${nested ? ' --nested' : ''}`,
					},
				});
			},
			...options,
		});
	}
}
