import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class BoldNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'b',
			convert({ node }: ConvertCallbackOptions): HTMLElement {
				return Dom.create({
					tag: 'b',
					attrs: {
						...node.getAttributes(),
						className: 'ui-typography-text-bold',
					},
				});
			},
			...options,
		});
	}
}
