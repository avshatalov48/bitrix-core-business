import { type BBCodeElementNode, BBCodeNode } from 'ui.bbcode.model';

export function shouldWrapInParagraph(node: BBCodeNode | BBCodeElementNode): boolean
{
	return node.getType() !== BBCodeNode.ELEMENT_NODE || node.hasGroup('#inline') || node.hasGroup('#inlineBlock');
}
