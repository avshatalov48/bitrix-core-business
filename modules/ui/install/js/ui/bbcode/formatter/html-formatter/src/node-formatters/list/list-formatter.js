import { Dom } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class ListNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions)
	{
		super({
			name: 'list',
			convert({ node }: ConvertCallbackOptions): HTMLUListElement | HTMLOListElement {
				const tagName = node.getValue() === '1' ? 'ol' : 'ul';

				return Dom.create({
					tag: tagName,
					attrs: {
						...node.getAttributes(),
						className: `ui-typography-${tagName}`,
					},
				});
			},
			...options,
		});
	}
}
