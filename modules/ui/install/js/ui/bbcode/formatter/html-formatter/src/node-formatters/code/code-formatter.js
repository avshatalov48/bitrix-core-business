import { Dom } from 'main.core';
import {
	NodeFormatter,
	type NodeFormatterOptions,
	type BeforeConvertCallbackOptions,
	type ConvertCallbackOptions,
} from 'ui.bbcode.formatter';

import { type BBCodeNode } from 'ui.bbcode.model';
import { CodeParser, type CodeToken } from 'ui.code-parser';
import { normalizeLineBreaks } from '../../helpers/normalize-line-breaks';

export class CodeNodeFormatter extends NodeFormatter
{
	#codeParser: CodeParser = new CodeParser();

	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'code',
			before({ node }: BeforeConvertCallbackOptions): BBCodeNode {
				return normalizeLineBreaks(node);
			},
			convert({ node }: ConvertCallbackOptions): HTMLElement {
				const content = node.getTextContent();

				return Dom.create({
					tag: 'code',
					attrs: {
						className: 'ui-typography-code',
					},
					dataset: {
						decorator: true,
					},
					children: getCodeTokenNodes(this.#codeParser.parse(content)),
				});
			},
			...options,
		});
	}
}

function getCodeTokenNodes(tokens: Array<CodeToken>): Array<Text | HTMLElement>
{
	const nodes: Array<Text | HTMLElement>[] = [];
	tokens.forEach((token: CodeToken): void => {
		const partials: string[] = token.content.split(/([\t\n])/);
		const partialsLength: number = partials.length;
		for (let i = 0; i < partialsLength; i++)
		{
			const part: string = partials[i];
			if (part === '\n' || part === '\r\n')
			{
				nodes.push(document.createElement('br'));
			}
			else if (part === '\t')
			{
				nodes.push(document.createTextNode('\t'));
			}
			else if (part.length > 0)
			{
				const span = document.createElement('span');
				span.className = `ui-typography-token-${token.type}`;
				span.textContent = part;
				nodes.push(span);
			}
		}
	});

	return nodes;
}
