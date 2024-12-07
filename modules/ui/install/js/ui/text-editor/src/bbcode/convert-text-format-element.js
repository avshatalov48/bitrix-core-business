import {
	$isTextNode,
	type LexicalNode,
} from 'ui.lexical.core';

import { type BBCodeElementNode } from 'ui.bbcode.model';
import type { BBCodeConversionOutput } from './types';

const nodeNameToTextFormat: Object<string, string> = {
	b: 'bold',
	strong: 'bold',
	i: 'italic',
	em: 'italic',
	s: 'strikethrough',
	del: 'strikethrough',
	u: 'underline',
	sub: 'subscript',
	sup: 'superscript',
};

export function convertTextFormatElement(node: BBCodeElementNode): BBCodeConversionOutput
{
	const format: string = nodeNameToTextFormat[node.getName()];
	if (format === undefined)
	{
		return { node: null };
	}

	return {
		forChild: (lexicalNode: LexicalNode): LexicalNode => {
			if ($isTextNode(lexicalNode) && !lexicalNode.hasFormat(format))
			{
				lexicalNode.toggleFormat(format);
			}

			return lexicalNode;
		},
		node: null,
	};
}
