import { type BBCodeElementNode, type BBCodeScheme } from 'ui.bbcode.model';
import { trimLineBreaks } from './trim-line-breaks';

export function normalizeLineBreaks(node: BBCodeElementNode): BBCodeElementNode
{
	const scheme: BBCodeScheme = node.getScheme();
	const children: Array<BBCodeElementNode> = trimLineBreaks(node.getChildren(), scheme);

	node.setChildren(children);

	// to avoid a height collapsing for empty elements
	if (children.length === 0 || (!scheme.isNewLine(children.at(-1)) && /^\s*$/.test(node.getTextContent())))
	{
		node.appendChild(scheme.createNewLine());
	}

	return node;
}
