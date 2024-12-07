import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class SpanNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'span',
			convert({ node }: ConvertCallbackOptions): HTMLElement {
				return Dom.create({
					tag: 'span',
					attrs: node.getAttributes(),
				});
			},
			...options,
		});
	}
}
