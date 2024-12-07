import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class UnderlineNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'u',
			convert({ node }: ConvertCallbackOptions): HTMLElement {
				return Dom.create({
					tag: 'u',
					attrs: {
						...node.getAttributes(),
						className: 'ui-typography-text-underline',
					},
				});
			},
			...options,
		});
	}
}
