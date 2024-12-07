import { Type } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';
import { createImageNode } from '../../helpers/create-image-node';
import { validateImageUrl } from '../../helpers/validate-image-url';

export class ImageNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'img',
			convert({ node }: ConvertCallbackOptions): HTMLSpanElement {
				// [img]{url}[/img]
				// [img width={width} height={height}]{url}[/img]
				const src = node.getContent().trim();
				let width = Number(node.getAttribute('width'));
				let height = Number(node.getAttribute('height'));
				width = Type.isNumber(width) && width > 0 ? Math.round(width) : null;
				height = Type.isNumber(height) && height > 0 ? Math.round(height) : null;

				if (validateImageUrl(src))
				{
					return createImageNode({ src, width, height });
				}

				return document.createTextNode(node.toString());
			},
			...options,
		});
	}
}
