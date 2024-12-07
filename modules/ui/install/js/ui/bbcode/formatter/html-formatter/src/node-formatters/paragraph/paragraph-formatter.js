import { Dom } from 'main.core';
import type { BeforeConvertCallbackOptions } from 'ui.bbcode.formatter';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';
import type { BBCodeNode } from 'ui.bbcode.model';
import { normalizeLineBreaks } from '../../helpers/normalize-line-breaks';

export class ParagraphNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'p',
			convert({ node }: ConvertCallbackOptions): HTMLParagraphElement {
				return Dom.create({
					tag: node.getName(),
					attrs: {
						className: 'ui-typography-paragraph',
					},
				});
			},
			before({ node }: BeforeConvertCallbackOptions): BBCodeNode {
				return normalizeLineBreaks(node);
			},
			...options,
		});
	}
}
