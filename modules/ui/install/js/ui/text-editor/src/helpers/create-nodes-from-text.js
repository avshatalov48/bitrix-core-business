import { Type } from 'main.core';
import { $createLineBreakNode, $createTabNode, $createTextNode, type TextNode } from 'ui.lexical.core';

export function $createNodesFromText(text: string): TextNode[]
{
	if (!Type.isStringFilled(text))
	{
		return [];
	}

	const nodes = [];
	const parts = text.split(/(\r?\n|\t)/);
	const length = parts.length;
	for (let i = 0; i < length; i++)
	{
		const part = parts[i];
		if (part === '\n' || part === '\r\n')
		{
			nodes.push($createLineBreakNode());
		}
		else if (part === '\t')
		{
			nodes.push($createTabNode());
		}
		else
		{
			nodes.push($createTextNode(part));
		}
	}

	return nodes;
}
