import { Dom, Type, Loc } from 'main.core';
import {
	NodeFormatter,
	type AfterCallbackOptions,
	type NodeFormatterOptions,
	type ConvertCallbackOptions,
	type BeforeConvertCallbackOptions,
} from 'ui.bbcode.formatter';

import { normalizeTextNodes } from '../../helpers/normalize-text-nodes';
import type { BBCodeNode } from 'ui.bbcode.model';

export class SpoilerNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'spoiler',
			convert({ node }: ConvertCallbackOptions): HTMLDetailsElement {
				const value = node.getValue().trim();
				const title = Type.isStringFilled(value) ? value : Loc.getMessage('HTML_FORMATTER_SPOILER_TITLE');

				return Dom.create({
					tag: 'details',
					attrs: {
						className: 'ui-typography-spoiler ui-icon-set__scope',
					},
					children: [
						Dom.create({
							tag: 'summary',
							attrs: {
								className: 'ui-typography-spoiler-title',
							},
							text: title,
						}),
					],
				});
			},
			before({ node }: BeforeConvertCallbackOptions): BBCodeNode {
				return normalizeTextNodes(node);
			},
			after({ element }: AfterCallbackOptions): HTMLElement {
				const [summary, ...content] = element.children;

				element.appendChild(summary);
				element.appendChild(
					Dom.create({
						tag: 'div',
						attrs: {
							className: 'ui-typography-spoiler-content',
						},
						dataset: {
							spoilerContent: 'true',
						},
						children: [
							...content,
						],
					}),
				);

				return element;
			},
			...options,
		});
	}
}
