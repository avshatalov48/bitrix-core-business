import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class StrikethroughNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 's',
			convert({ node }: ConvertCallbackOptions): HTMLElement {
				return Dom.create({
					tag: 's',
					attrs: {
						...node.getAttributes(),
						className: 'ui-typography-text-strikethrough',
					},
				});
			},
			...options,
		});
	}
}
