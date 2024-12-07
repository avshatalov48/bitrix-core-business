import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class ItalicNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'i',
			convert({ node }: ConvertCallbackOptions): HTMLElement {
				return Dom.create({
					tag: 'i',
					attrs: {
						...node.getAttributes(),
						className: 'ui-typography-text-italic',
					},
				});
			},
			...options,
		});
	}
}
