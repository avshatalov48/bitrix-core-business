import {
	$isLineBreakNode,
	$isParagraphNode,
	type LexicalNode,
	type ElementNode,
} from 'ui.lexical.core';

export function trimLineBreaks(nodes: Array<LexicalNode | ElementNode>): Array<LexicalNode | ElementNode>
{
	const trimmedNodes = [...nodes];
	const firstNode: LexicalNode | ElementNode = trimmedNodes[0];
	const lastNode: LexicalNode | ElementNode = trimmedNodes[trimmedNodes.length - 1];
	if ($isLineBreakNode(firstNode) || ($isParagraphNode(firstNode) && firstNode.isEmpty()))
	{
		trimmedNodes.splice(0, 1);
	}

	if ($isLineBreakNode(lastNode) || ($isParagraphNode(lastNode) && lastNode.isEmpty()))
	{
		trimmedNodes.splice(-1, 1);
	}

	return trimmedNodes;
}
