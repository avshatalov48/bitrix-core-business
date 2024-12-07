import { BBCodeElementNode, BBCodeScheme } from 'ui.bbcode.model';
import { wrapTextNodes } from './wrap-text-nodes';

export function normalizeTextNodes(node: BBCodeElementNode): BBCodeElementNode
{
	const scheme: BBCodeScheme = node.getScheme();
	const children: Array<BBCodeElementNode> = wrapTextNodes(node.getChildren(), scheme);
	node.setChildren(children);

	return node;
}
