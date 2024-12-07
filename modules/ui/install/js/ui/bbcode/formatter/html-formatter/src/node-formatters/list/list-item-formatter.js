import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class ListItemNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions)
	{
		super({
			name: '*',
			convert({ node }: ConvertCallbackOptions): HTMLLIElement {
				return Dom.create({
					tag: 'li',
					attrs: {
						...node.getAttributes(),
						className: 'ui-typography-li',
					},
				});
			},
			...options,
		});
	}
}
