import { $isParagraphNode, $isTextNode, $isLineBreakNode, type ElementNode } from 'ui.lexical.core';

export function $isParagraphEmpty(node: ElementNode): boolean
{
	if (!$isParagraphNode(node))
	{
		return false;
	}

	if (node.isEmpty())
	{
		return true;
	}

	return node.getChildren().every((child) => {
		return (
			$isLineBreakNode(child)
			|| ($isTextNode(child) && /^\s*$/.test(child.getTextContent()))
		);
	});
}
